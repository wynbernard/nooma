<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../Login/login.php");
    exit;
}

$full_name = $_SESSION["full_name"] ?? "Administrator";
$username = $_SESSION["username"] ?? "admin";
$page_title = "Daily Summary";
$page_description = "Compare Lunch and Closing sales performance";

require_once __DIR__ . "/../../backend/config/database.php";

$selectedChannel = strtolower(trim((string) ($_GET["channel"] ?? "all")));
if (!in_array($selectedChannel, ["all", "pos", "non-pos"], true)) {
    $selectedChannel = "all";
}
$channelFilter = "";
$queryParams = [];
if ($selectedChannel !== "all") {
    $channelFilter = " WHERE (
        CASE
            WHEN JSON_VALID(dr.notes) AND UPPER(TRIM(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(dr.notes, '$.saleChannel')), ''))) = 'NON POS' THEN 'non-pos'
            WHEN JSON_VALID(dr.notes) AND UPPER(TRIM(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(dr.notes, '$.saleChannel')), ''))) = 'POS' THEN 'pos'
            WHEN COALESCE(dr.pos_sales_total, 0) > 0 THEN 'pos'
            ELSE 'non-pos'
        END
    ) = ? ";
    $queryParams[] = $selectedChannel;
}
$bindParams = static function ($statement, array $params): void {
    if (!$params) {
        return;
    }
    $bindValues = [str_repeat("s", count($params))];
    foreach ($params as $key => $value) {
        $bindValues[] = &$params[$key];
    }
    call_user_func_array([$statement, "bind_param"], $bindValues);
};

$summary = [
    "Lunch" => ["reports" => 0, "tables" => 0, "pax" => 0, "declared" => 0, "sales" => 0, "payments" => 0],
    "Closing" => ["reports" => 0, "tables" => 0, "pax" => 0, "declared" => 0, "sales" => 0, "payments" => 0]
];

$summarySql = "
    SELECT dr.shift_name,
           COUNT(*) AS reports,
           COALESCE(SUM(dr.total_tables), 0) AS tables_total,
           COALESCE(SUM(dr.total_pax), 0) AS pax_total,
           COALESCE(SUM(dr.telegram_declared_total), 0) AS declared_total,
           COALESCE(SUM(dr.pos_sales_total), 0) AS sales_total,
           COALESCE(SUM(payments.payment_total), 0) AS payments_total
    FROM daily_reports dr
    LEFT JOIN (
        SELECT report_id, SUM(amount) AS payment_total
        FROM report_payments
        GROUP BY report_id
    ) payments ON payments.report_id = dr.report_id
    {$channelFilter}
    GROUP BY dr.shift_name
";

$summaryStmt = mysqli_prepare($conn, $summarySql);
$bindParams($summaryStmt, $queryParams);
mysqli_stmt_execute($summaryStmt);
$summaryResult = mysqli_stmt_get_result($summaryStmt);
while ($row = mysqli_fetch_assoc($summaryResult)) {
    if (isset($summary[$row["shift_name"]])) {
        $summary[$row["shift_name"]] = [
            "reports" => (int) $row["reports"],
            "tables" => (int) $row["tables_total"],
            "pax" => (int) $row["pax_total"],
            "declared" => (float) $row["declared_total"],
            "sales" => (float) $row["sales_total"],
            "payments" => (float) $row["payments_total"]
        ];
    }
}
mysqli_stmt_close($summaryStmt);

$reports = [];
$reportsSql = "
    SELECT dr.report_id, dr.report_number, dr.report_date, dr.shift_name, dr.total_tables,
           dr.total_pax, dr.telegram_declared_total, dr.pos_sales_total, dr.status,
           dr.notes,
           COALESCE(payments.payment_total, 0) AS payment_total
    FROM daily_reports dr
    LEFT JOIN (
        SELECT report_id, SUM(amount) AS payment_total
        FROM report_payments
        GROUP BY report_id
    ) payments ON payments.report_id = dr.report_id
    {$channelFilter}
    ORDER BY dr.report_date DESC, FIELD(dr.shift_name, 'Lunch', 'Closing'), dr.report_id DESC
";
$reportsStmt = mysqli_prepare($conn, $reportsSql);
$bindParams($reportsStmt, $queryParams);
mysqli_stmt_execute($reportsStmt);
$reportsResult = mysqli_stmt_get_result($reportsStmt);
while ($row = mysqli_fetch_assoc($reportsResult)) {
    $reports[] = $row;
}
mysqli_stmt_close($reportsStmt);

$reportDetails = $reports;
$qrToJcbByReport = [];
$paymentsByReport = [];
$ownersByReport = [];
$reportIds = array_map(static fn (array $report): int => (int) $report["report_id"], $reportDetails);
if ($reportIds) {
    $reportIdList = implode(",", $reportIds);
    $qrToJcbResult = mysqli_query($conn, "
        SELECT report_id, SUM(amount) AS total
        FROM report_payments
        WHERE report_id IN ({$reportIdList}) AND payment_method_id BETWEEN 2 AND 8
        GROUP BY report_id
    ");
    while ($row = mysqli_fetch_assoc($qrToJcbResult)) {
        $qrToJcbByReport[(int) $row["report_id"]] = (float) $row["total"];
    }
    $paymentResult = mysqli_query($conn, "
        SELECT rp.report_id, pm.method_name, SUM(rp.amount) AS total
        FROM report_payments rp
        INNER JOIN payment_methods pm ON pm.payment_method_id = rp.payment_method_id
        WHERE rp.report_id IN ({$reportIdList})
        GROUP BY rp.report_id, pm.payment_method_id, pm.method_name
    ");
    while ($row = mysqli_fetch_assoc($paymentResult)) {
        $paymentsByReport[(int) $row["report_id"]][$row["method_name"]] = (float) $row["total"];
    }
    $ownerResult = mysqli_query($conn, "
        SELECT report_id, SUM(amount) AS total
        FROM report_accounts
        WHERE report_id IN ({$reportIdList}) AND account_category = 'owners_account'
        GROUP BY report_id
    ");
    while ($row = mysqli_fetch_assoc($ownerResult)) {
        $ownersByReport[(int) $row["report_id"]] = (float) $row["total"];
    }
}
$money = static function ($value): float {
    $value = str_replace(",", "", (string) $value);
    return is_numeric($value) ? (float) $value : 0.0;
};
$grossForReport = static function (array $report) use ($money, $paymentsByReport, $ownersByReport): float {
    $payload = json_decode($report["notes"] ?? "", true);
    $payload = is_array($payload) ? $payload : [];
    $savedChannel = strtoupper(trim((string) ($payload["saleChannel"] ?? "")));
    $channel = $savedChannel === "NON POS"
        ? "NON POS"
        : ((float) $report["pos_sales_total"] > 0 ? "POS" : "NON POS");
    $payments = $paymentsByReport[(int) $report["report_id"]] ?? [];
    $cashSales = (float) ($payments["Cash"] ?? ($payments["Cash Sales"] ?? 0.0));
    $cardSales = 0.0;
    foreach (["GCash + QR PH", "Gcash + QRPH", "PayMaya", "Maya Terminal", "AMEX", "Visa", "Mastercard", "BancNet", "JCB"] as $method) {
        $cardSales += (float) ($payments[$method] ?? 0.0);
    }
    $breakdownSales = 0.0;
    foreach (["bpi", "easwest", "giftcheck", "cheque"] as $field) {
        $breakdownSales += $money($payload[$field] ?? 0);
    }
    $posSales = $money($payload["reconShortOver"] ?? 0)
        + $money($payload["paidAccounts"] ?? 0)
        + $money($payload["advancePayments"] ?? 0)
        + $cashSales
        + $cardSales
        + $breakdownSales
        + (float) ($ownersByReport[(int) $report["report_id"]] ?? 0.0);
    $nonPosSales = $channel === "NON POS" ? (float) $report["telegram_declared_total"] : 0.0;
    return $posSales + $nonPosSales + $money($payload["onlineTips"] ?? 0);
};
$ownerAccountNames = [];
$accountNamesResult = mysqli_query($conn, "SELECT account_holder_id, account_name FROM account_holders WHERE account_type = 'owner' AND is_active = 1");
if ($accountNamesResult) {
    while ($accountRow = mysqli_fetch_assoc($accountNamesResult)) {
        $ownerAccountNames[(string) $accountRow["account_holder_id"]] = $accountRow["account_name"];
    }
}
$dailyReports = [];
foreach ($reports as $report) {
    $dateKey = $report["report_date"];
    if (!isset($dailyReports[$dateKey])) {
        $dailyReports[$dateKey] = [
            "report_number" => [],
            "report_date" => $dateKey,
            "shift_name" => "Lunch + Closing",
            "total_tables" => 0,
            "total_pax" => 0,
            "telegram_declared_total" => 0,
            "pos_sales_total" => 0,
            "status" => [],
            "payment_total" => 0,
            "gross" => 0.0
        ];
    }
    $dailyReports[$dateKey]["report_number"][] = $report["report_number"];
    $dailyReports[$dateKey]["total_tables"] += (int) $report["total_tables"];
    $dailyReports[$dateKey]["total_pax"] += (int) $report["total_pax"];
    $dailyReports[$dateKey]["telegram_declared_total"] += (float) $report["telegram_declared_total"];
    $dailyReports[$dateKey]["pos_sales_total"] += (float) $report["pos_sales_total"];
    $dailyReports[$dateKey]["payment_total"] += (float) $report["payment_total"];
    $dailyReports[$dateKey]["gross"] += $grossForReport($report);
    $dailyReports[$dateKey]["status"][] = $report["status"];
}
foreach ($dailyReports as &$dailyReport) {
    $dailyReport["report_number"] = implode(" + ", $dailyReport["report_number"]);
    $dailyReport["status"] = implode(", ", array_unique($dailyReport["status"]));
}
unset($dailyReport);
$reports = array_values($dailyReports);

$chartDays = $dailyReports;
ksort($chartDays);
$dailyChart = ["labels" => [], "gross" => []];
foreach ($chartDays as $dateKey => $day) {
    $dailyChart["labels"][] = date("D, M j", strtotime($dateKey));
    $dailyChart["gross"][] = round((float) $day["gross"], 2);
}

$fieldTotals = [];
$fieldOrder = [];
$integerFields = ["pax", "tableNumber"];
$fallbackFields = static function (array $report): array {
    return [
        "pax" => $report["total_pax"],
        "tableNumber" => $report["total_tables"],
        "grandTotal" => $report["telegram_declared_total"],
        "totalSale" => $report["pos_sales_total"]
    ];
};

foreach ($reportDetails as $report) {
    $shiftName = $report["shift_name"];
    if (!in_array($shiftName, ["Lunch", "Closing"], true)) {
        continue;
    }

    $payload = json_decode($report["notes"] ?? "", true);
    $payload = is_array($payload) ? $payload : [];
    $payload = array_merge($fallbackFields($report), $payload);

    foreach ($payload as $fieldName => $fieldValue) {
        if ($fieldName === "saleDate" || $fieldName === "saleType" || $fieldName === "ownerAccounts" || is_array($fieldValue)) {
            continue;
        }
        $numericValue = (float) str_replace(",", "", (string) $fieldValue);
        if (!is_numeric(str_replace(",", "", (string) $fieldValue)) || abs($numericValue) < 0.00001) {
            continue;
        }
        if (!isset($fieldTotals[$fieldName])) {
            $fieldTotals[$fieldName] = ["Lunch" => 0.0, "Closing" => 0.0];
            $fieldOrder[] = $fieldName;
        }
        $fieldTotals[$fieldName][$shiftName] += $numericValue;
    }

    foreach (($payload["ownerAccounts"] ?? []) as $accountId => $amount) {
        $numericAmount = (float) str_replace(",", "", (string) $amount);
        if (!is_numeric(str_replace(",", "", (string) $amount)) || abs($numericAmount) < 0.00001) {
            continue;
        }
        $fieldName = $ownerAccountNames[(string) $accountId] ?? ("Owner account #" . $accountId);
        if (!isset($fieldTotals[$fieldName])) {
            $fieldTotals[$fieldName] = ["Lunch" => 0.0, "Closing" => 0.0];
            $fieldOrder[] = $fieldName;
        }
        $fieldTotals[$fieldName][$shiftName] += $numericAmount;
    }
}

$qrToJcbTotal = 0.0;
$unsettledFridaySunday = 0.0;
$unsettledPaymentFields = ["gcashQrph", "paymaya", "amex", "visa", "mastercard", "bancnet", "jcb"];
foreach ($reportDetails as $report) {
    $amount = (float) ($qrToJcbByReport[(int) $report["report_id"]] ?? 0.0);
    $qrToJcbTotal += $amount;
    if ((int) date("N", strtotime($report["report_date"])) >= 5) {
        $unsettledFridaySunday += $amount;
    }
}

$combined = ["reports" => 0, "tables" => 0, "pax" => 0, "declared" => 0, "sales" => 0, "payments" => 0];
foreach ($summary as $shift) {
    foreach ($combined as $key => $value) {
        $combined[$key] += $shift[$key];
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
    <title>Daily Summary - Nooma</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-900">

<?php include "../Components/sidebar.php"; ?>

<div class="lg:ml-64 min-h-screen">
    <?php include "../Components/navbar.php"; ?>

    <main class="p-4 md:p-6">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold">Daily Summary</h2>
                <p class="text-sm text-gray-500 mt-1">Lunch and Closing totals from saved sales reports.</p>
            </div>
            <form id="summaryFilters" method="get" class="flex items-end gap-2">
                <div>
                    <label for="filterSalesChannel" class="block text-xs font-medium text-gray-500 mb-1">Filter channel</label>
                    <select id="filterSalesChannel" name="channel" aria-label="Filter sales channel"
                            class="border rounded-xl px-3 py-2 text-sm font-medium text-gray-700 bg-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="all" <?= $selectedChannel === "all" ? "selected" : "" ?>>All channels</option>
                        <option value="pos" <?= $selectedChannel === "pos" ? "selected" : "" ?>>POS only</option>
                        <option value="non-pos" <?= $selectedChannel === "non-pos" ? "selected" : "" ?>>Non POS only</option>
                    </select>
                </div>
                <?php if ($selectedChannel !== "all"): ?>
                    <a href="dailySummary.php" class="px-4 py-2.5 border rounded-xl font-semibold bg-white hover:bg-gray-50">Clear filters</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Combined Sales</p>
                <h3 class="text-2xl font-bold mt-2"><?= peso($combined["sales"]) ?></h3>
                <p class="text-xs text-gray-500 mt-1"><?= $combined["reports"] ?> reports / <?= $combined["pax"] ?> pax</p>
            </div>
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Declared Total</p>
                <h3 class="text-2xl font-bold mt-2"><?= peso($combined["declared"]) ?></h3>
                <p class="text-xs text-gray-500 mt-1"><?= $combined["tables"] ?> tables</p>
            </div>
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">QR/GCash to JCB Total</p>
                <h3 class="text-2xl font-bold mt-2"><?= peso($qrToJcbTotal) ?></h3>
                <p class="text-xs text-gray-500 mt-1">All selected reports</p>
            </div>
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Unsettled (Friday-Sunday)</p>
                <h3 class="text-2xl font-bold mt-2 text-orange-600"><?= peso($unsettledFridaySunday) ?></h3>
                <p class="text-xs text-gray-500 mt-1">QR/GCash through JCB</p>
            </div>
        </div>

        <!-- <div class="grid xl:grid-cols-2 gap-5 mb-6">
            <?php foreach (["Lunch" => "blue", "Closing" => "indigo"] as $shiftName => $color): ?>
                <?php $shift = $summary[$shiftName]; ?>
                <section class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold"><?= $shiftName ?> Summary</h3>
                            <p class="text-sm text-gray-500 mt-1"><?= $shift["reports"] ?> saved report<?= $shift["reports"] === 1 ? "" : "s" ?></p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-<?= $color ?>-50 text-<?= $color ?>-700"><?= $shiftName ?></span>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 p-5">
                        <div><p class="text-xs text-gray-500">Sales</p><p class="font-bold mt-1"><?= peso($shift["sales"]) ?></p></div>
                        <div><p class="text-xs text-gray-500">Declared</p><p class="font-bold mt-1"><?= peso($shift["declared"]) ?></p></div>
                        <div><p class="text-xs text-gray-500">Payments</p><p class="font-bold mt-1"><?= peso($shift["payments"]) ?></p></div>
                        <div><p class="text-xs text-gray-500">Tables</p><p class="font-bold mt-1"><?= $shift["tables"] ?></p></div>
                        <div><p class="text-xs text-gray-500">Pax</p><p class="font-bold mt-1"><?= $shift["pax"] ?></p></div>
                        <div><p class="text-xs text-gray-500">Difference</p><p class="font-bold mt-1"><?= peso($shift["declared"] - $shift["payments"]) ?></p></div>
                    </div>
                </section>
            <?php endforeach; ?>
        </div> -->

        <!-- <section class="bg-white border rounded-2xl shadow-sm overflow-hidden mb-6">
            <div class="p-5 border-b">
                <h3 class="text-lg font-bold">All Field Totals</h3>
                <p class="text-sm text-gray-500 mt-1">Sum of every numeric form field by shift.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">Field</th>
                            <th class="px-5 py-3">Lunch</th>
                            <th class="px-5 py-3">Closing</th>
                            <th class="px-5 py-3">Combined</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if (empty($fieldOrder)): ?>
                            <tr><td colspan="4" class="px-5 py-8 text-center text-gray-500">No numeric field data found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($fieldOrder as $fieldName): ?>
                                <?php
                                $lunchTotal = $fieldTotals[$fieldName]["Lunch"];
                                $closingTotal = $fieldTotals[$fieldName]["Closing"];
                                $combinedTotal = $lunchTotal + $closingTotal;
                                $isInteger = in_array($fieldName, $integerFields, true);
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-3 font-medium">
                                        <?= htmlspecialchars(ucwords(preg_replace("/(?<!^)[A-Z]/", " $0", $fieldName))) ?>
                                    </td>
                                    <td class="px-5 py-3"><?= $isInteger ? number_format($lunchTotal, 0) : peso($lunchTotal) ?></td>
                                    <td class="px-5 py-3"><?= $isInteger ? number_format($closingTotal, 0) : peso($closingTotal) ?></td>
                                    <td class="px-5 py-3 font-semibold"><?= $isInteger ? number_format($combinedTotal, 0) : peso($combinedTotal) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section> -->

        <section class="bg-white border rounded-2xl shadow-sm overflow-hidden mb-6">
            <div class="p-5 border-b">
                <h3 class="text-lg font-bold">Total Gross Sales</h3>
                <p class="text-sm text-gray-500 mt-1">Daily total gross sale for each date.</p>
            </div>
            <div class="p-5">
                <?php if (empty($dailyChart["labels"])): ?>
                    <p class="py-10 text-center text-sm text-gray-500">No daily totals to chart.</p>
                <?php else: ?>
                    <div class="h-80">
                        <canvas id="dailyGrossChart"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="bg-white border rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 border-b">
                <h3 class="text-lg font-bold">Report Breakdown</h3>
                <p class="text-sm text-gray-500 mt-1">Every report included in the summary above.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">Week</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Shift</th>
                            <th class="px-5 py-3">Tables</th>
                            <th class="px-5 py-3">Pax</th>
                            <th class="px-5 py-3">Total Gross</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Details</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        <?php if (empty($reports)): ?>
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-gray-500">
                                    No reports found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reports as $report): ?>
                                <?php
                                $dayDetails = array_values(array_filter(
                                    $reportDetails,
                                    static fn (array $detail): bool => $detail["report_date"] === $report["report_date"]
                                ));
                                $combinedDetailFields = [];
                                foreach ($dayDetails as $dayDetail) {
                                    $dayPayload = json_decode($dayDetail["notes"] ?? "", true);
                                    if (!is_array($dayPayload)) {
                                        continue;
                                    }
                                    foreach ($dayPayload as $fieldName => $fieldValue) {
                                        if ($fieldName === "saleDate" || $fieldName === "saleType" || is_array($fieldValue)) {
                                            continue;
                                        }
                                        $numericValue = str_replace(",", "", (string) $fieldValue);
                                        if (!is_numeric($numericValue) || abs((float) $numericValue) < 0.00001) {
                                            continue;
                                        }
                                        $combinedDetailFields[$fieldName] = ($combinedDetailFields[$fieldName] ?? 0) + (float) $numericValue;
                                    }
                                    foreach (($dayPayload["ownerAccounts"] ?? []) as $accountId => $amount) {
                                        $numericAmount = str_replace(",", "", (string) $amount);
                                        if (is_numeric($numericAmount) && abs((float) $numericAmount) >= 0.00001) {
                                            $fieldName = $ownerAccountNames[(string) $accountId] ?? ("Owner account #" . $accountId);
                                            $combinedDetailFields[$fieldName] = ($combinedDetailFields[$fieldName] ?? 0) + (float) $numericAmount;
                                        }
                                    }
                                }
                                $detailsId = "details-" . preg_replace("/[^0-9]/", "", $report["report_date"]);
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4 font-semibold">
                                       <?= date("l", strtotime($report["report_date"])) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?= date("F j, Y", strtotime($report["report_date"])) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?= htmlspecialchars($report["shift_name"]) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?= (int) $report["total_tables"] ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?= (int) $report["total_pax"] ?>
                                    </td>
                                    <td class="px-5 py-4 font-semibold">
                                        <?= peso((float) $report["gross"]) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700">
                                            <?= htmlspecialchars(ucfirst($report["status"])) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <button type="button"
                                                class="summary-details-button px-3 py-1.5 text-blue-700 bg-blue-50 rounded-lg font-medium hover:bg-blue-100"
                                                data-target="<?= htmlspecialchars($detailsId) ?>"
                                                aria-expanded="false">
                                            See Details
                                        </button>
                                    </td>
                                </tr>
                                <tr id="<?= htmlspecialchars($detailsId) ?>" class="hidden bg-gray-50">
                                    <td colspan="8" class="px-5 py-5">
                                        <div class="bg-white border rounded-xl p-4">
                                            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                                <div>
                                                    <p class="font-semibold">Lunch + Closing Combined</p>
                                                    <p class="text-xs text-gray-500"><?= count($dayDetails) ?> report<?= count($dayDetails) === 1 ? "" : "s" ?> combined</p>
                                                </div>
                                                <p class="font-semibold"><?= peso((float) $report["pos_sales_total"]) ?></p>
                                            </div>
                                            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                                <?php foreach ($combinedDetailFields as $fieldName => $fieldValue): ?>
                                                    <div class="border rounded-lg p-2">
                                                        <p class="text-xs text-gray-500">
                                                            <?= htmlspecialchars(ucwords(preg_replace("/(?<!^)[A-Z]/", " $0", $fieldName))) ?>
                                                        </p>
                                                        <p class="text-sm font-medium mt-1"><?= peso((float) $fieldValue) ?></p>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script>
        const summaryFilters = document.getElementById("summaryFilters");
        const filterSalesChannel = document.getElementById("filterSalesChannel");

        filterSalesChannel.addEventListener("change", () => summaryFilters.requestSubmit());

        const dailyChartData = <?= json_encode($dailyChart, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const dailyGrossChart = document.getElementById("dailyGrossChart");

        function pesoValue(value) {
            return "₱" + Number(value).toLocaleString("en-PH", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        if (dailyGrossChart && dailyChartData.labels.length) {
            new Chart(dailyGrossChart, {
                type: "line",
                data: {
                    labels: dailyChartData.labels,
                    datasets: [{
                        label: "Total Gross",
                        data: dailyChartData.gross,
                        borderColor: "#2563eb",
                        backgroundColor: "rgba(37, 99, 235, 0.12)",
                        borderWidth: 2,
                        pointRadius: 3,
                        pointBackgroundColor: "#ffffff",
                        pointBorderColor: "#2563eb",
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: "index", intersect: false },
                    plugins: {
                        legend: { position: "bottom" },
                        tooltip: {
                            callbacks: {
                                label: context => "Total Gross: " + pesoValue(context.raw)
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { callback: value => pesoValue(value) },
                            grid: { color: "rgba(148, 163, 184, 0.2)" }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 12 }
                        }
                    }
                }
            });
        }

        document.querySelectorAll(".summary-details-button").forEach(button => {
            button.addEventListener("click", () => {
                const details = document.getElementById(button.dataset.target);
                const isHidden = details.classList.toggle("hidden");
                button.setAttribute("aria-expanded", String(!isHidden));
                button.textContent = isHidden ? "See Details" : "Hide Details";
            });
        });
    </script>

    <?php include "../Components/footer.php"; ?>
</div>
