<?php
session_start();

if (!isset($_SESSION["user_id"]) ||($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../Login/login.php");
    exit;
}

$full_name =$_SESSION["full_name"] ?? "Administrator";
$username =$_SESSION["username"] ?? "admin";
$page_title = "Tips";
$page_description = "View and manage tips from service charges";

require_once __DIR__ . "/../../backend/config/database.php";

$tipMessage = (string) ($_SESSION["tip_message"] ?? "");
$tipMessageType = ($_SESSION["tip_message_type"] ?? "success") === "error" ? "error" : "success";
unset($_SESSION["tip_message"], $_SESSION["tip_message_type"]);

if (empty($_SESSION["tip_csrf_token"])) {
    $_SESSION["tip_csrf_token"] = bin2hex(random_bytes(32));
}

$firstReportResult = mysqli_query($conn, "SELECT MIN(report_date) AS first_report_date FROM daily_reports WHERE status <> 'voided'");
$firstReport = $firstReportResult ? mysqli_fetch_assoc($firstReportResult) : null;
$defaultStartDate =$firstReport["first_report_date"] ?? date("Y-01-01");

// Default to all available report history through the current month.
$start_date = $_GET["start_date"] ?? $defaultStartDate;
$end_date =$_GET["end_date"] ?? date("Y-m-t");
$legacyAdditionalServiceCharge = $_GET["additional_service_charge"] ?? null;
$additionalTipValue = $_GET["additional_tip_amount"] ?? (
    is_scalar($legacyAdditionalServiceCharge) && is_numeric($legacyAdditionalServiceCharge)
        ? (string) round(max(0, (float) $legacyAdditionalServiceCharge) * 0.50, 2)
        : "0"
);
$additionalTipAmount = is_scalar($additionalTipValue) && is_numeric($additionalTipValue)
    ? max(0, (float) $additionalTipValue)
    : 0.0;
$additionalTipAmount = is_finite($additionalTipAmount) ? $additionalTipAmount : 0.0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "reset_employee_days") {
    $csrfToken = (string) ($_POST["csrf_token"] ?? "");
    if (!hash_equals($_SESSION["tip_csrf_token"], $csrfToken)) {
        $tipMessage = "The request expired. Please try again.";
        $tipMessageType = "error";
    } elseif (!mysqli_query($conn, "UPDATE users SET number_of_days = 0 WHERE role = 'employee' AND is_active = 1")) {
        error_log("Employee days reset failed: " . mysqli_error($conn));
        $tipMessage = "Could not reset employee days.";
        $tipMessageType = "error";
    } else {
        $tipMessage = "All active employee days have been reset.";
        $tipMessageType = "success";
    }

    if (str_contains($_SERVER["HTTP_ACCEPT"] ?? "", "application/json")) {
        header("Content-Type: application/json; charset=utf-8");
        if ($tipMessageType === "error") {
            http_response_code(400);
        }
        echo json_encode([
            "success" => $tipMessageType !== "error",
            "message" => $tipMessage,
        ]);
        exit;
    }

    $_SESSION["tip_message"] = $tipMessage;
    $_SESSION["tip_message_type"] = $tipMessageType;
    $redirectQuery = http_build_query([
        "start_date" => $_POST["start_date"] ?? $start_date,
        "end_date" => $_POST["end_date"] ?? $end_date,
        "additional_tip_amount" => $_POST["additional_tip_amount"] ?? $additionalTipAmount,
    ]);
    header("Location: tip.php?" . $redirectQuery);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["action"] ?? "") === "update_employee_days") {
    $userId = (int) ($_POST["user_id"] ?? 0);
    $numberOfDays = filter_var($_POST["number_of_days"] ?? null, FILTER_VALIDATE_INT);
    $csrfToken = (string) ($_POST["csrf_token"] ?? "");

    if (!hash_equals($_SESSION["tip_csrf_token"], $csrfToken)) {
        $tipMessage = "The request expired. Please try again.";
        $tipMessageType = "error";
    } elseif ($userId <= 0 || $numberOfDays === false || $numberOfDays < 0) {
        $tipMessage = "Enter a valid non-negative number of days.";
        $tipMessageType = "error";
    } else {
        $updateDaysStmt = mysqli_prepare($conn, "UPDATE users SET number_of_days = ? WHERE user_id = ? AND role = 'employee'");
        if (!$updateDaysStmt) {
            error_log("Could not prepare employee days update: " . mysqli_error($conn));
            $tipMessage = "Could not update the employee's number of days.";
            $tipMessageType = "error";
        } else {
            mysqli_stmt_bind_param($updateDaysStmt, "ii", $numberOfDays, $userId);
            if (mysqli_stmt_execute($updateDaysStmt)) {
                $tipMessage = "Employee number of days updated.";
            } else {
                error_log("Employee days update failed: " . mysqli_stmt_error($updateDaysStmt));
                $tipMessage = "Could not update the employee's number of days.";
                $tipMessageType = "error";
            }
            mysqli_stmt_close($updateDaysStmt);
        }
    }

    $_SESSION["tip_message"] = $tipMessage;
    $_SESSION["tip_message_type"] = $tipMessageType;
    if (str_contains($_SERVER["HTTP_ACCEPT"] ?? "", "application/json")) {
        unset($_SESSION["tip_message"], $_SESSION["tip_message_type"]);
        header("Content-Type: application/json; charset=utf-8");
        if ($tipMessageType === "error") {
            http_response_code(400);
        }
        echo json_encode([
            "success" => $tipMessageType !== "error",
            "message" => $tipMessage,
            "number_of_days" => $numberOfDays === false ? null : $numberOfDays,
        ]);
        exit;
    }

    $redirectQuery = http_build_query([
        "start_date" => $_POST["start_date"] ?? $start_date,
        "end_date" => $_POST["end_date"] ?? $end_date,
        "additional_tip_amount" => $_POST["additional_tip_amount"] ?? $additionalTipAmount,
    ]);
    header("Location: tip.php?" . $redirectQuery);
    exit;
}

$loadTipTotals = static function (mysqli $conn, string $startDate, string $endDate): array {
    $statement = mysqli_prepare($conn, "
        SELECT
            dr.report_id,
            dr.report_date,
            CASE WHEN JSON_VALID(dr.notes) THEN COALESCE(
                CAST(JSON_UNQUOTE(JSON_EXTRACT(dr.notes, '$.serviceCharge')) AS DECIMAL(12,2)),
                service_charges.service_charge_total,
                0
            ) ELSE COALESCE(service_charges.service_charge_total, 0) END AS service_charge
        FROM daily_reports dr
        LEFT JOIN (
            SELECT report_id, SUM(amount) AS service_charge_total
            FROM report_sales
            WHERE sales_type = 'service_charge'
            GROUP BY report_id
        ) service_charges ON service_charges.report_id = dr.report_id
        WHERE dr.status <> 'voided'
            AND dr.report_date BETWEEN ? AND ?
        ORDER BY dr.report_date DESC, dr.report_id DESC
    ");
    if (!$statement) {
        throw new RuntimeException("Could not prepare the service charge query: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($statement, "ss", $startDate, $endDate);
    if (!mysqli_stmt_execute($statement)) {
        $message = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        throw new RuntimeException("Could not load service charge totals: " . $message);
    }
    $result = mysqli_stmt_get_result($statement);
    if (!$result) {
        $message = mysqli_stmt_error($statement);
        mysqli_stmt_close($statement);
        throw new RuntimeException("Could not read service charge totals: " . $message);
    }

    $totals = [
        "service_charge" => 0.0,
        "tip_amount" => 0.0,
        "transactions" => 0,
        "monthly" => []
    ];
    while ($row = mysqli_fetch_assoc($result)) {
        $serviceCharge = (float)$row["service_charge"];
        $saleTipAmount = round($serviceCharge * 0.50, 2);
        $totals["service_charge"] += $serviceCharge;
        $totals["tip_amount"] += $saleTipAmount;
        $totals["transactions"]++;

        $monthKey = date("Y-m", strtotime($row["report_date"]));
        if (!isset($totals["monthly"][$monthKey])) {
            $totals["monthly"][$monthKey] = [
                "month" => date("F Y", strtotime($row["report_date"])),
                "transactions" => 0,
                "service_charge" => 0.0,
                "tip_amount" => 0.0
            ];
        }
        $totals["monthly"][$monthKey]["transactions"]++;
        $totals["monthly"][$monthKey]["service_charge"] += $serviceCharge;
        $totals["monthly"][$monthKey]["tip_amount"] += $saleTipAmount;
    }
    mysqli_stmt_close($statement);

    ksort($totals["monthly"]);
    $totals["monthly"] = array_values($totals["monthly"]);
    return $totals;
};

if (isset($_GET["refresh_tip_totals"])) {
    header("Content-Type: application/json; charset=utf-8");
    try {
        echo json_encode([
            "success" => true,
            "totals" => $loadTipTotals($conn, $start_date,$end_date)
        ]);
    } catch (Throwable $error) {
        error_log("Live tip totals refresh failed: " . $error->getMessage());
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Could not refresh service charge totals."]);
    }
    exit;
}

$tipTotals = $loadTipTotals($conn, $start_date,$end_date);
$totalServiceCharge =$tipTotals["service_charge"];
$tipAmount =$tipTotals["tip_amount"];
$totalTransactions =$tipTotals["transactions"];
$monthlyTotals =$tipTotals["monthly"];
$tipAmount += $additionalTipAmount;

$employeeTipBreakdown = [];
$employeeResult = mysqli_query($conn, "
    SELECT user_id, full_name, role, number_of_days
    FROM users
    WHERE is_active = 1
      AND role = 'employee'
    ORDER BY full_name ASC
");
if ($employeeResult) {$employees = [];
    while ($employee = mysqli_fetch_assoc($employeeResult)) {$employees[] = [
            "user_id" => (int) $employee["user_id"],
            "full_name" => (string) $employee["full_name"],
            "role" => ucfirst((string) $employee["role"]),
            "number_of_days" => (int) $employee["number_of_days"],
        ];
    }
    $totalDutyDays = array_sum(array_column($employees, "number_of_days"));

    foreach ($employees as $employee) {
        $sharePercent = $totalDutyDays > 0
            ? ($employee["number_of_days"] / $totalDutyDays) * 100
            : 0.0;
        $employeeTipAmount = $totalDutyDays > 0
            ? round(($tipAmount / $totalDutyDays) * $employee["number_of_days"], 2)
            : 0.0;
        $employeeTipBreakdown[] = [
            "user_id" => $employee["user_id"],
            "full_name" => $employee["full_name"],
            "role" => $employee["role"],
            "number_of_days" => $employee["number_of_days"],
            "share_percent" => $sharePercent,
            "tip_amount" => $employeeTipAmount,
        ];
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tips - Nooma</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<?php include "../Components/sidebar.php"; ?>
<div class="lg:ml-64 min-h-screen">
    <?php include "../Components/navbar.php"; ?>
    <main class="p-4 md:p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold">Tips Management</h2>
            <p class="text-sm text-gray-500 mt-1">View tips from service charges (50% of total service charge)</p>
        </div>

        <!-- Date Range Filter -->
        <div class="bg-white border rounded-2xl shadow-sm p-5 mb-6">
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-4 flex flex-wrap items-center gap-4">
                <!-- Start Date Input -->
                <div class="flex-1 min-w-[200px]">
                    <label for="start_date" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Start Date</label>
                    <div class="relative">
                        <input 
                            type="date" 
                            id="start_date" 
                            name="start_date" 
                            value="<?= htmlspecialchars($start_date) ?>"
                            onchange="filterByDateRange()"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none transition-all cursor-pointer"
                        >
                    </div>
                </div>

                <!-- End Date Input -->
                <div class="flex-1 min-w-[200px]">
                    <label for="end_date" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">End Date</label>
                    <div class="relative">
                        <input 
                            type="date" 
                            id="end_date" 
                            name="end_date" 
                            value="<?= htmlspecialchars($end_date) ?>"
                            onchange="filterByDateRange()"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none transition-all cursor-pointer"
                        >
                    </div>
                </div>

                <!-- Optional: Reset / Clear Filter Button -->
                <div class="self-end">
                    <button 
                        type="button" 
                        onclick="resetDateFilter()"
                        class="px-3.5 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-all flex items-center gap-1.5"
                        title="Reset Dates"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Service Charge</p>
                <h3 id="totalServiceCharge" class="text-2xl font-bold mt-2">₱<?= number_format($totalServiceCharge, 2) ?></h3>
            </div>

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Tips (50% of Service Charge + Additional Tip)</p>
                <h3 id="tipAmountSummary" class="text-2xl font-bold mt-2 text-green-600">₱<?= number_format($tipAmount, 2) ?></h3>
            </div>

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Number of Transactions</p>
                <h3 id="totalTransactions" class="text-2xl font-bold mt-2"><?= $totalTransactions ?></h3>
            </div>
        </div>

        <!-- Employee Tip Division -->
        <div class="bg-white border rounded-2xl shadow-sm overflow-hidden mb-6">
            <div class="p-5 md:p-6 border-b flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold">Employee Tip Division</h3>
                    <p class="text-sm text-gray-500 mt-1">50% of service charge is allocated to tips, divided by each employee's days of duty.</p>
                </div>
                <div class="w-full sm:w-auto flex flex-col sm:flex-row sm:items-end gap-3">
                    <div class="w-full sm:w-56">
                        <label for="additional_tip_amount"
                            class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">
                            Additional Tip Amount
                        </label>
                        <input
                            type="number"
                            id="additional_tip_amount"
                            name="additional_tip_amount"
                            step="0.01"
                            min="0"
                            value="<?= htmlspecialchars((string) $additionalTipAmount) ?>"
                            placeholder="Enter amount"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none transition-all"
                        >
                    </div>
                    <form method="post" data-reset-employee-days>
                        <input type="hidden" name="action" value="reset_employee_days">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["tip_csrf_token"]) ?>">
                        <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                        <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                        <input type="hidden" name="additional_tip_amount" value="<?= htmlspecialchars((string) $additionalTipAmount) ?>">
                        <button type="submit" class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-100 transition-colors">
                            Reset All Days
                        </button>
                    </form>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">Employee</th>
                            <th class="px-5 py-3 text-right">Number of Days</th>
                            <th class="px-5 py-3 text-right">Share %</th>
                            <th class="px-5 py-3 text-right">Tip Amount</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTipBreakdownBody" class="divide-y">
                        <?php if (empty($employeeTipBreakdown)): ?>
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-gray-500">
                                    No active employees found for tip distribution.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($employeeTipBreakdown as$employee): ?>
                                <tr class="hover:bg-gray-50" data-employee-tip-row>
                                    <td class="px-5 py-4 font-semibold">
                                        <?= htmlspecialchars($employee["full_name"]) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <form method="post" data-update-employee-days class="flex flex-col sm:flex-row items-end sm:items-center justify-end gap-2">
                                            <input type="hidden" name="action" value="update_employee_days">
                                            <input type="hidden" name="user_id" value="<?= (int) $employee["user_id"] ?>">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["tip_csrf_token"]) ?>">
                                            <input type="hidden" name="start_date" value="<?= htmlspecialchars($start_date) ?>">
                                            <input type="hidden" name="end_date" value="<?= htmlspecialchars($end_date) ?>">
                                            <input type="hidden" name="additional_tip_amount" value="<?= htmlspecialchars((string) $additionalTipAmount) ?>">
                                            <input
                                                type="number"
                                                name="number_of_days"
                                                data-employee-days
                                                min="0"
                                                step="1"
                                                required
                                                value="<?= (int) $employee["number_of_days"] ?>"
                                                aria-label="Number of days for <?= htmlspecialchars($employee["full_name"]) ?>"
                                                class="w-24 border border-gray-200 rounded-lg px-2.5 py-2 text-right"
                                            >
                                            <button type="submit" class="px-3 py-2 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700">
                                                Update
                                            </button>
                                        </form>
                                    </td>
                                    <td class="px-5 py-4 text-right employee-share-percent">
                                        <?= number_format($employee["share_percent"], 2) ?>%
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold text-green-700 employee-tip-amount">
                                        ₱<?= number_format($employee["tip_amount"], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tips Table -->
        <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 md:p-6 border-b">
                <h3 class="text-lg font-bold">Service Charge Breakdown</h3>
                <p class="text-sm text-gray-500 mt-1">Individual service charges within the selected date range</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">Month</th>
                            <th class="px-5 py-3 text-right">Transactions</th>
                            <th class="px-5 py-3 text-right">Service Charge</th>
                            <th class="px-5 py-3 text-right">Tips (50%)</th>
                        </tr>
                    </thead>
                    <tbody id="monthlyTotalsBody" class="divide-y">
                        <?php if (empty($monthlyTotals)): ?>
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-500">
                                    No reports found in the selected date range.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($monthlyTotals as$month): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4 font-semibold">
                                        <?= htmlspecialchars($month["month"]) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <?= number_format($month["transactions"]) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold">
                                        ₱<?= number_format($month["service_charge"], 2) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold text-green-700">
                                        ₱<?= number_format($month["tip_amount"], 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
function resetDateFilter() {
    window.location.href = "tip.php";
}

function filterByDateRange() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    const additionalTipAmount =
        document.getElementById('additional_tip_amount').value || 0;

    if (!startDate || !endDate) {
        window.showToast('Please select both start and end dates', 'error');
        return;
    }

    if (startDate > endDate) {
        window.showToast('Start date must be before end date', 'error');
        return;
    }

    window.location.href =
        `tip.php?start_date=${startDate}` +
        `&end_date=${endDate}` +
        `&additional_tip_amount=${additionalTipAmount}`;
}

let baseServiceChargeTotal = <?= json_encode($totalServiceCharge) ?>;
let baseTipAmount = <?= json_encode($tipAmount - $additionalTipAmount) ?>;

function updateEmployeeTipShares(totalTipAmount) {
    const employeeRows = document.querySelectorAll('[data-employee-tip-row]');
    const totalDutyDays = Array.from(employeeRows).reduce((total, row) => {
        const daysInput = row.querySelector('[data-employee-days]');
        const days = daysInput ? Number.parseInt(daysInput.value, 10) : 0;
        return total + (Number.isFinite(days) && days > 0 ? days : 0);
    }, 0);

    employeeRows.forEach(row => {
        const daysInput = row.querySelector('[data-employee-days]');
        const days = daysInput ? Math.max(0, Number.parseInt(daysInput.value, 10) || 0) : 0;
        const sharePercent = totalDutyDays > 0 ? (days / totalDutyDays) * 100 : 0;
        const employeeTipAmount = totalDutyDays > 0 ? (totalTipAmount / totalDutyDays) * days : 0;
        const amountCell = row.querySelector('.employee-tip-amount');
        const shareCell = row.querySelector('.employee-share-percent');

        if (amountCell) {
            amountCell.textContent = '₱' + employeeTipAmount.toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        if (shareCell) {
            shareCell.textContent = sharePercent.toLocaleString('en-PH', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }) + '%';
        }
    });
}

function updateTotalServiceCharge() {
    const additionalTipInput = document.getElementById('additional_tip_amount');
    const enteredTip = parseFloat(additionalTipInput.value);
    const additionalTipAmount = Number.isFinite(enteredTip) && enteredTip > 0 ? enteredTip : 0;
    const totalServiceCharge = baseServiceChargeTotal;
    const totalTipAmount = baseTipAmount + additionalTipAmount;

    document.getElementById('totalServiceCharge').textContent =
        '₱' + totalServiceCharge.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    document.getElementById('tipAmountSummary').textContent =
        '₱' + totalTipAmount.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });

    updateEmployeeTipShares(totalTipAmount);
}

function renderMonthlyTotals(monthlyTotals) {
    const body = document.getElementById('monthlyTotalsBody');
    body.replaceChildren();
    if (!monthlyTotals.length) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 4;
        cell.className = 'px-5 py-8 text-center text-gray-500';
        cell.textContent = 'No reports found in the selected date range.';
        row.appendChild(cell);
        body.appendChild(row);
        return;
    }

    monthlyTotals.forEach(month => {
        const row = document.createElement('tr');
        row.className = 'hover:bg-gray-50';
        [
            [month.month, 'px-5 py-4 font-semibold'],
            [Number(month.transactions).toLocaleString(), 'px-5 py-4 text-right'],
            [`₱${Number(month.service_charge).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`, 'px-5 py-4 text-right font-semibold'],
            [`₱${Number(month.tip_amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`, 'px-5 py-4 text-right font-semibold text-green-700']
        ].forEach(([value, className]) => {
            const cell = document.createElement('td');
            cell.className = className;
            cell.textContent = value;
            row.appendChild(cell);
        });
        body.appendChild(row);
    });
}

let tipTotalsRefreshInProgress = false;
async function refreshTipTotals() {
    if (tipTotalsRefreshInProgress || document.visibilityState === 'hidden') {
        return;
    }

    tipTotalsRefreshInProgress = true;
    try {
        const refreshUrl = new URL(window.location.href);
        refreshUrl.searchParams.set('refresh_tip_totals', '1');
        const response = await fetch(refreshUrl, {
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        });
        const payload = await response.json();
        if (!response.ok || !payload.success || !payload.totals) {
            throw new Error(payload.message || 'Could not refresh service charge totals.');
        }

        baseServiceChargeTotal = Number(payload.totals.service_charge);
        baseTipAmount = Number(payload.totals.tip_amount);
        document.getElementById('totalTransactions').textContent =
            Number(payload.totals.transactions).toLocaleString();
        renderMonthlyTotals(payload.totals.monthly);
        updateTotalServiceCharge();
    } catch (error) {
        console.error('Live tip totals refresh failed:', error);
    } finally {
        tipTotalsRefreshInProgress = false;
    }
}

const additionalTipAmountInput = document.getElementById('additional_tip_amount');
additionalTipAmountInput.addEventListener('input', () => {
    document.querySelectorAll('form[data-update-employee-days] input[name="additional_tip_amount"]')
        .forEach(input => {
            input.value = additionalTipAmountInput.value;
        });
        document.querySelector('form[data-reset-employee-days] input[name="additional_tip_amount"]').value =
            additionalTipAmountInput.value;
        updateTotalServiceCharge();
    });
document.querySelector('[data-reset-employee-days]').addEventListener('submit', async event => {
    event.preventDefault();
    if (!window.confirm('Reset the number of days for all active employees to zero?')) {
        return;
    }

    const form = event.currentTarget;
    const button = form.querySelector('button[type="submit"]');
    button.disabled = true;
    try {
        const response = await fetch(window.location.href, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'Accept': 'application/json' },
            cache: 'no-store'
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) {
            throw new Error(payload.message || 'Could not reset employee days.');
        }

        document.querySelectorAll('[data-employee-days]').forEach(input => {
            input.value = 0;
        });
        updateTotalServiceCharge();
        window.showToast(payload.message || 'All active employee days have been reset.');
    } catch (error) {
        window.showToast(error.message || 'Could not reset employee days.', 'error');
    } finally {
        button.disabled = false;
    }
});

document.querySelectorAll('[data-employee-days]').forEach(input => {
    input.addEventListener('input', updateTotalServiceCharge);
});

document.querySelectorAll('[data-update-employee-days]').forEach(form => {
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        if (!button || button.disabled) {
            return;
        }

        button.disabled = true;
        const originalButtonText = button.textContent;
        button.textContent = 'Saving...';

        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Could not update employee number of days.');
            }

            form.querySelector('[name="number_of_days"]').value = payload.number_of_days;
            window.showToast(payload.message || 'Employee number of days updated.');
        } catch (error) {
            window.showToast(error.message || 'Could not update employee number of days.', 'error');
        } finally {
            button.disabled = false;
            button.textContent = originalButtonText;
        }
    });
});

window.setInterval(refreshTipTotals, 5000);
document.addEventListener('visibilitychange', refreshTipTotals);
window.addEventListener('focus', refreshTipTotals);
</script>
<?php if ($tipMessage !== ""): ?>
    <script>
        window.showToast(<?= json_encode($tipMessage, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($tipMessageType) ?>);
    </script>
<?php endif; ?>
</body>
</html>