<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../Login/login.php");
    exit;
}

$full_name = $_SESSION["full_name"] ?? "Administrator";
$username = $_SESSION["username"] ?? "admin";
$page_title = "Monthly Summary";
$page_description = "Monthly sales and collection report";

require_once __DIR__ . "/../../backend/config/database.php";

$selectedMonth = trim((string) ($_GET["month"] ?? date("Y-m")));
if (!preg_match("/^\d{4}-\d{2}$/", $selectedMonth)) {
    $selectedMonth = date("Y-m");
}

$monthStart = $selectedMonth . "-01";
$monthEnd = date("Y-m-t", strtotime($monthStart));
$money = static function ($value): float {
    $value = str_replace(",", "", (string) $value);
    return is_numeric($value) ? (float) $value : 0.0;
};

$reports = [];
$stmt = mysqli_prepare($conn, "
    SELECT dr.report_date, dr.telegram_declared_total, dr.pos_sales_total, dr.notes,
           COALESCE(payments.payment_total, 0) AS payment_total
    FROM daily_reports dr
    LEFT JOIN (
        SELECT report_id, SUM(amount) AS payment_total
        FROM report_payments
        GROUP BY report_id
    ) payments ON payments.report_id = dr.report_id
    WHERE dr.report_date BETWEEN ? AND ? AND dr.status <> 'voided'
    ORDER BY dr.report_date, dr.report_id
");
mysqli_stmt_bind_param($stmt, "ss", $monthStart, $monthEnd);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $row["payload"] = json_decode($row["notes"] ?? "", true);
    $row["payload"] = is_array($row["payload"]) ? $row["payload"] : [];
    $reports[] = $row;
}
mysqli_stmt_close($stmt);

$ownerAccountNames = [];
$ownerNamesResult = mysqli_query($conn, "SELECT account_holder_id, account_name FROM account_holders WHERE account_type = 'owner'");
if ($ownerNamesResult) {
    while ($ownerRow = mysqli_fetch_assoc($ownerNamesResult)) {
        $ownerAccountNames[(string) $ownerRow["account_holder_id"]] = $ownerRow["account_name"];
    }
}

$monthly = [
    "reports" => 0,
    "declared" => 0.0,
    "pos" => 0.0,
    "nonPos" => 0.0,
    "payments" => 0.0,
];
$fieldTotals = [];
foreach ($reports as $report) {
    $savedChannel = strtoupper(trim((string) ($report["payload"]["saleChannel"] ?? "")));
    $channel = $savedChannel === "NON POS"
        ? "NON POS"
        : ((float) $report["pos_sales_total"] > 0 ? "POS" : "NON POS");
    $declared = $money($report["telegram_declared_total"]);
    $payments = $money($report["payment_total"]);
    if ($channel === "POS") {
        $pos = $money($report["payload"]["reconShortOver"] ?? 0)
             + $money($report["payload"]["paidAccounts"] ?? 0)
             + $money($report["payload"]["advancePayments"] ?? 0)
             + $money($report["payment_total"])
             + $money($report["payload"]["bpi"] ?? 0)
             + $money($report["payload"]["easwest"] ?? 0)
             + $money($report["payload"]["giftcheck"] ?? 0)
             + $money($report["payload"]["cheque"] ?? 0)
             + array_sum(array_map($money, (array) ($report["payload"]["ownerAccounts"] ?? [])));
    } else {
        $pos = 0.0;
    }
    $nonPos = $channel === "NON POS" ? $declared : 0.0;

    $monthly["reports"]++;
    $monthly["declared"] += $declared;
    $monthly["pos"] += $pos;
    $monthly["nonPos"] += $nonPos;
    $monthly["payments"] += $payments;

    foreach ($report["payload"] as $field => $value) {
        if ($field === "ownerAccounts" || in_array($field, ["saleDate", "saleType"], true) || !is_scalar($value)) {
            continue;
        }
        $numericValue = str_replace(",", "", (string) $value);
        if (!is_numeric($numericValue)) {
            continue;
        }
        $fieldTotals[$field] = ($fieldTotals[$field] ?? 0) + $money($numericValue);
    }
    foreach (($report["payload"]["ownerAccounts"] ?? []) as $accountId => $amount) {
        $field = $ownerAccountNames[(string) $accountId] ?? "Owner account #" . $accountId;
        $fieldTotals[$field] = ($fieldTotals[$field] ?? 0) + $money($amount);
    }
}

function peso(float $amount): string {
    return "₱" . number_format($amount, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Summary - Nooma</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<?php include "../Components/sidebar.php"; ?>
<div class="lg:ml-64 min-h-screen">
    <?php include "../Components/navbar.php"; ?>
    <main class="p-4 md:p-6">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold">Monthly Summary</h2>
                <p class="text-sm text-gray-500 mt-1">One monthly total with all saved field details.</p>
            </div>
            <div class="flex items-end gap-2">
                <form method="get" class="flex items-end gap-2">
                <div>
                    <label for="summaryMonth" class="block text-xs font-medium text-gray-500 mb-1">Month</label>
                    <input type="month" id="summaryMonth" name="month" value="<?= htmlspecialchars($selectedMonth) ?>"
                           class="border rounded-xl px-3 py-2 bg-white" onchange="this.form.submit()">
                </div>
                </form>
                <a href="../../backend/reports/export_monthly.php?month=<?= urlencode($selectedMonth) ?>"
                   download="monthly_report_<?= htmlspecialchars(substr($selectedMonth, 0, 4)) ?>.xls"
                   class="px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700">
                    Download Excel
                </a>
            </div>
        </div>

        <div class="grid sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-6">
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Reports</p>
                <p class="text-2xl font-bold mt-2"><?= $monthly["reports"] ?></p>
            </div>
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Declared Total</p>
                <p class="text-2xl font-bold mt-2"><?= peso($monthly["declared"]) ?></p>
            </div>
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">POS SALE</p>
                <p class="text-2xl font-bold mt-2"><?= peso($monthly["pos"]) ?></p>
            </div>
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Non POS</p>
                <p class="text-2xl font-bold mt-2"><?= peso($monthly["nonPos"]) ?></p>
            </div>
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Collected Payments</p>
                <p class="text-2xl font-bold mt-2"><?= peso($monthly["payments"]) ?></p>
            </div>
        </div>

        <section class="bg-white border rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b">
                <h3 class="text-lg font-bold"><?= htmlspecialchars(date("F Y", strtotime($monthStart))) ?> Monthly Total</h3>
                <p class="text-sm text-gray-500 mt-1">One row for the selected month.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Reports</th>
                            <th class="px-5 py-3">POS SALE</th>
                            <th class="px-5 py-3">Non POS</th>
                            <th class="px-5 py-3">Grab Sale</th>
                            <th class="px-5 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if ($monthly["reports"] === 0): ?>
                            <tr><td colspan="7" class="px-5 py-8 text-center text-gray-500">No reports found for this month.</td></tr>
                        <?php else: ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 font-semibold"><?= htmlspecialchars(date("F Y", strtotime($monthStart))) ?></td>
                                <td class="px-5 py-4"><?= $monthly["reports"] ?></td>
                                <td class="px-5 py-4"><?= peso($monthly["pos"]) ?></td>
                                <td class="px-5 py-4"><?= peso($monthly["nonPos"]) ?></td>
                                <td class="px-5 py-4 font-semibold"><?= peso($fieldTotals["onlineTips"] ?? 0) ?></td>
                                <td class="px-5 py-4">
                                    <button type="button" class="monthly-details-button px-3 py-1.5 text-blue-700 bg-blue-50 rounded-lg font-medium hover:bg-blue-100"
                                            data-target="monthlyFieldDetails" aria-expanded="false">
                                        Details
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="monthlyFieldDetails" class="hidden bg-white border rounded-2xl shadow-sm overflow-hidden mt-6">
            <div class="p-5 border-b">
                <h3 class="text-lg font-bold">All Field Totals</h3>
                <p class="text-sm text-gray-500 mt-1">Sum of every numeric saved field for the selected month.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr><th class="px-5 py-3">Field</th><th class="px-5 py-3">Monthly Total</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if (!$fieldTotals): ?>
                            <tr><td colspan="2" class="px-5 py-8 text-center text-gray-500">No field data found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($fieldTotals as $field => $total): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3 font-medium"><?= htmlspecialchars(ucwords(preg_replace("/(?<!^)[A-Z]/", " $0", $field))) ?></td>
                                    <td class="px-5 py-3 font-semibold"><?= peso($total) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <script>
        document.querySelectorAll(".monthly-details-button").forEach(button => {
            button.addEventListener("click", () => {
                const details = document.getElementById(button.dataset.target);
                const isHidden = details.classList.toggle("hidden");
                button.setAttribute("aria-expanded", String(!isHidden));
                button.textContent = isHidden ? "Details" : "Hide Details";
            });
        });
    </script>
    <?php include "../Components/footer.php"; ?>
</div>
</body>
</html>
