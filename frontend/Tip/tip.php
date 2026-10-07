<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../Login/login.php");
    exit;
}

$full_name = $_SESSION["full_name"] ?? "Administrator";
$username = $_SESSION["username"] ?? "admin";
$page_title = "Tips";
$page_description = "View and manage tips from service charges";

require_once __DIR__ . "/../../backend/config/database.php";

$firstReportResult = mysqli_query($conn, "SELECT MIN(report_date) AS first_report_date FROM daily_reports WHERE status <> 'voided'");
$firstReport = $firstReportResult ? mysqli_fetch_assoc($firstReportResult) : null;
$defaultStartDate = $firstReport["first_report_date"] ?? date("Y-01-01");

// Default to all available report history through the current month.
$start_date = $_GET["start_date"] ?? $defaultStartDate;
$end_date = $_GET["end_date"] ?? date("Y-m-t");
$additionalTipValue = $_GET["additional_service_charge"] ?? "0";
$additionalTip = is_scalar($additionalTipValue) && is_numeric($additionalTipValue)
    ? max(0, (float) $additionalTipValue)
    : 0.0;
$additionalTip = is_finite($additionalTip) ? $additionalTip : 0.0;

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
        $serviceCharge = (float) $row["service_charge"];
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
            "totals" => $loadTipTotals($conn, $start_date, $end_date)
        ]);
    } catch (Throwable $error) {
        error_log("Live tip totals refresh failed: " . $error->getMessage());
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Could not refresh service charge totals."]);
    }
    exit;
}

$tipTotals = $loadTipTotals($conn, $start_date, $end_date);
$totalServiceCharge = $tipTotals["service_charge"];
$tipAmount = $tipTotals["tip_amount"];
$totalTransactions = $tipTotals["transactions"];
$monthlyTotals = $tipTotals["monthly"];
$totalServiceCharge += $additionalTip;
$tipAmount += round($additionalTip * 0.50, 2);

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
            <!-- Additional Service Charge -->
            <div class="mt-4">
                <label for="additional_service_charge"
                    class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">
                    TIP
                </label>

                <input
                    type="number"
                    id="additional_service_charge"
                    name="additional_service_charge"
                    step="0.01"
                    min="0"
                    placeholder="Enter amount"
                    class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none transition-all"
                >
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Service Charge</p>
                <h3 id="totalServiceCharge" class="text-2xl font-bold mt-2">₱<?= number_format($totalServiceCharge, 2) ?></h3>
            </div>

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Tips (50% of Service Charge)</p>
                <h3 id="tipAmountSummary" class="text-2xl font-bold mt-2 text-green-600">₱<?= number_format($tipAmount, 2) ?></h3>
            </div>

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Number of Transactions</p>
                <h3 id="totalTransactions" class="text-2xl font-bold mt-2"><?= $totalTransactions ?></h3>
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
                            <?php foreach ($monthlyTotals as $month): ?>
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
    const additionalServiceCharge =
        document.getElementById('additional_service_charge').value || 0;

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
        `&additional_service_charge=${additionalServiceCharge}`;
}

let baseServiceChargeTotal = <?= json_encode($totalServiceCharge - $additionalTip) ?>;
let baseTipAmount = <?= json_encode($tipAmount - round($additionalTip * 0.50, 2)) ?>;

function updateTotalServiceCharge() {
    const additionalTipInput = document.getElementById('additional_service_charge');
    const enteredTip = parseFloat(additionalTipInput.value);
    const additionalTip = Number.isFinite(enteredTip) && enteredTip > 0 ? enteredTip : 0;
    const totalServiceCharge = baseServiceChargeTotal + additionalTip;

    document.getElementById('totalServiceCharge').textContent =
        '₱' + totalServiceCharge.toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    document.getElementById('tipAmountSummary').textContent =
        '₱' + (baseTipAmount + Math.round(additionalTip * 50) / 100).toLocaleString('en-PH', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
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

const additionalServiceChargeInput = document.getElementById('additional_service_charge');
additionalServiceChargeInput.addEventListener('input', updateTotalServiceCharge);
window.setInterval(refreshTipTotals, 5000);
document.addEventListener('visibilitychange', refreshTipTotals);
window.addEventListener('focus', refreshTipTotals);
</script>
</body>
</html> 