<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../Login/login.php");
    exit;
}

$full_name = $_SESSION["full_name"] ?? "Administrator";
$username = $_SESSION["username"] ?? "admin";
$page_title = "Summary";
$page_description = "Weekly sales and collection report";
require_once __DIR__ . "/../../backend/config/database.php";

$weekStart = trim((string) ($_GET["date_start"] ?? ""));
$weekEnd = trim((string) ($_GET["date_end"] ?? ""));
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $weekStart)) {
    $weekStart = date("Y-m-d", strtotime("monday this week"));
}
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $weekEnd) || $weekEnd < $weekStart) {
    $weekEnd = date("Y-m-d", strtotime($weekStart . " +6 days"));
}

$money = static function ($value): float {
    $value = str_replace(",", "", (string) $value);
    return is_numeric($value) ? (float) $value : 0.0;
};
$sum = static function (array $rows, string $key) use ($money): float {
    return array_reduce($rows, static fn (float $total, array $row): float => $total + $money($row[$key] ?? 0), 0.0);
};

$reports = [];
$stmt = mysqli_prepare($conn, "SELECT report_id, report_number, report_date, shift_name, total_tables, total_pax, telegram_declared_total, pos_sales_total, notes FROM daily_reports WHERE report_date BETWEEN ? AND ? AND status <> 'voided' ORDER BY report_date, FIELD(shift_name, 'Lunch', 'Closing'), report_id");
mysqli_stmt_bind_param($stmt, "ss", $weekStart, $weekEnd);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $row["payload"] = json_decode($row["notes"] ?? "", true);
    $row["payload"] = is_array($row["payload"]) ? $row["payload"] : [];
    $reports[] = $row;
}
mysqli_stmt_close($stmt);

$reportIds = array_map(static fn (array $report): int => (int) $report["report_id"], $reports);
$payments = [];
$ownerAccounts = [];
$ownerAccountIds = [];
if ($reportIds) {
    $idList = implode(",", $reportIds);
    $paymentsResult = mysqli_query($conn, "SELECT pm.method_name, pm.payment_method_id, SUM(rp.amount) AS total FROM report_payments rp INNER JOIN payment_methods pm ON pm.payment_method_id = rp.payment_method_id WHERE rp.report_id IN ({$idList}) GROUP BY rp.payment_method_id, pm.method_name ORDER BY pm.display_order");
    while ($row = mysqli_fetch_assoc($paymentsResult)) {
        $payments[$row["method_name"]] = (float) $row["total"];
        // Also store by payment method ID for easier access
        $payments["id_" . $row["payment_method_id"]] = (float) $row["total"];
    }

    $accountsResult = mysqli_query($conn, "SELECT ah.account_holder_id, ah.account_name, SUM(ra.amount) AS total FROM report_accounts ra INNER JOIN account_holders ah ON ah.account_holder_id = ra.account_holder_id WHERE ra.report_id IN ({$idList}) AND ra.account_category = 'owners_account' GROUP BY ra.account_holder_id, ah.account_name ORDER BY ah.account_name");
    while ($row = mysqli_fetch_assoc($accountsResult)) {
        $ownerAccounts[$row["account_name"]] = (float) $row["total"];
        $ownerAccountIds[$row["account_name"]] = $row["account_holder_id"];
    }
}

$unpaidOwnerAccountIds = [];
$unpaidOwnerAmounts = []; // keyed by account_holder_id
$unpaidNonOwnerAccounts = [];
$unpaidNonOwnerNotes = []; // Store notes for non-owner accounts
$ownerPaidTotal = 0.0;
$payloadTotals = [];
foreach ($reports as $report) {
    $payload = $report["payload"];
    $unpaidAccountNames = [];
    if (!empty($payload["unpaidAccountName"])) {
        $unpaidAccountNames["unpaidAccountName"] = $payload["unpaidAccountName"];
    }
    foreach ($payload as $field => $value) {
        if (preg_match('/^unpaidAccountName_(\d+)$/', $field)) {
            $unpaidAccountNames[$field] = $value;
        }
    }
    foreach ($unpaidAccountNames as $nameField => $accountName) {
        $accountIndex = $nameField === "unpaidAccountName" ? "" : substr($nameField, strlen("unpaidAccountName_"));
        $amountField = $accountIndex === "" ? "unpaidAccountAmount" : "unpaidAccountAmount_" . $accountIndex;
        $noteField = $accountIndex === "" ? "unpaidAccountNote" : "unpaidAccountNote_" . $accountIndex;
        $accountName = trim((string) $accountName);
        $accountAmount = $money($payload[$amountField] ?? 0);
        $accountNote = $payload[$noteField] ?? "";
        
        if ($accountName !== "" && $accountAmount > 0) {
            $unpaidNonOwnerAccounts[$accountName] = ($unpaidNonOwnerAccounts[$accountName] ?? 0) + $accountAmount;
            // Store note separately
            if ($accountNote) {
                $unpaidNonOwnerNotes[$accountName] = $accountNote;
            }
        }
    }
    foreach ($payload as $field => $value) {
        if (preg_match('/^ownerNote_(\d+)$/', $field, $matches)) {
            $aid = $matches[1];
            $reportOwnerAccounts = $payload["ownerAccounts"] ?? [];
            $ownerAmount = $money($reportOwnerAccounts[$aid] ?? 0);
            if (stripos($value, "unpaid") !== false) {
                $unpaidOwnerAccountIds[$aid] = true;
                $unpaidOwnerAmounts[$aid] = ($unpaidOwnerAmounts[$aid] ?? 0) + $ownerAmount;
            }
        }
        if ($field === "ownerAccounts" || !is_scalar($value) || !is_numeric(str_replace(",", "", (string) $value)) || in_array($field, ["pax", "tableNumber"], true)) {
            continue;
        }
        $payloadTotals[$field] = ($payloadTotals[$field] ?? 0) + $money($value);
    }

    foreach (($payload["ownerAccounts"] ?? []) as $accountId => $amount) {
        $ownerAmount = $money($amount);
        $note = $payload["ownerNote_" . $accountId] ?? "";
        if (stripos($note, "unpaid") !== false) {
            continue;
        }
        $ownerPaidTotal += $ownerAmount;
    }
}

$declaredTotal = $sum($reports, "telegram_declared_total");
$nonPos = 0.0;
$nonPosCashRemitted = 0.0;
$posCashRemittedTotal = 0.0;
$posShortOver = 0.0;
$nonPosShortOver = 0.0;
$posOtherPaymentsTotal = 0.0;
$nonPosOtherPaymentsTotal = 0.0;
$posUnpaidNonOwnerTotal = 0.0;
$nonPosCardSalesTotal = 0.0;
$posCardSalesTotal = 0.0;
$nonPosBreakdownTotals = [];
$posBreakdownTotals = [];
$nonPosOwnerAccounts = [];
$nonPosUnpaidOwnerAmounts = [];
$cardPaymentFields = ["gcashQrph", "paymaya", "amex", "visa", "mastercard", "bancnet", "jcb"];
$weekendCardSales = 0.0;
foreach ($reports as $report) {
    $reportDay = (int) date("N", strtotime($report["report_date"]));
    if ($reportDay >= 5) {
        foreach ($cardPaymentFields as $field) {
            $weekendCardSales += $money($report["payload"][$field] ?? 0);
        }
    }

    $savedChannel = strtoupper(trim((string) ($report["payload"]["saleChannel"] ?? "")));
    $channel = $savedChannel === "NON POS"
        ? "NON POS"
        : ((float) $report["pos_sales_total"] > 0 ? "POS" : "NON POS");
    if ($channel === "NON POS") {
        $nonPos += $money($report["telegram_declared_total"]);
        $nonPosCashRemitted += $money($report["payload"]["cashRemitted"] ?? 0);
        $nonPosShortOver += $money($report["payload"]["reconShortOver"] ?? 0);
        $nonPosOtherPaymentsTotal += $money($report["payload"]["paidAccounts"] ?? 0) + $money($report["payload"]["advancePayments"] ?? 0)
            + $money($report["payload"]["otherCash"] ?? 0)
            + $money($report["payload"]["otherMayaTerminal"] ?? 0)
            + $money($report["payload"]["otherBpiNooma"] ?? 0)
            + $money($report["payload"]["otherEastwestNooma"] ?? 0)
            + $money($report["payload"]["otherGiftCheck"] ?? 0)
            + $money($report["payload"]["otherCheques"] ?? 0);
        foreach ($cardPaymentFields as $field) {
            $nonPosCardSalesTotal += $money($report["payload"][$field] ?? 0);
        }
        foreach (["bpi", "easwest", "giftcheck", "cheque"] as $field) {
            $nonPosBreakdownTotals[$field] = ($nonPosBreakdownTotals[$field] ?? 0) + $money($report["payload"][$field] ?? 0);
        }
        foreach (($report["payload"]["ownerAccounts"] ?? []) as $accountId => $amount) {
            $ownerAmount = $money($amount);
            $ownerNote = $report["payload"]["ownerNote_" . $accountId] ?? "";
            if (stripos($ownerNote, "unpaid") !== false) {
                $nonPosUnpaidOwnerAmounts[$accountId] = ($nonPosUnpaidOwnerAmounts[$accountId] ?? 0) + $ownerAmount;
            } else {
                $nonPosOwnerAccounts[$accountId] = ($nonPosOwnerAccounts[$accountId] ?? 0) + $ownerAmount;
            }
        }
    } else {
        $posCashRemittedTotal += $money($report["payload"]["cashRemitted"] ?? 0);
        $posShortOver += $money($report["payload"]["reconShortOver"] ?? 0);
        $posOtherPaymentsTotal += $money($report["payload"]["paidAccounts"] ?? 0) + $money($report["payload"]["advancePayments"] ?? 0)
            + $money($report["payload"]["otherCash"] ?? 0)
            + $money($report["payload"]["otherMayaTerminal"] ?? 0)
            + $money($report["payload"]["otherBpiNooma"] ?? 0)
            + $money($report["payload"]["otherEastwestNooma"] ?? 0)
            + $money($report["payload"]["otherGiftCheck"] ?? 0)
            + $money($report["payload"]["otherCheques"] ?? 0);
        $payload = $report["payload"];
        $unpaidAccountNames = [];
        if (!empty($payload["unpaidAccountName"])) {
            $unpaidAccountNames["unpaidAccountName"] = $payload["unpaidAccountName"];
        }
        foreach ($payload as $field => $value) {
            if (preg_match('/^unpaidAccountName_(\d+)$/', $field)) {
                $unpaidAccountNames[$field] = $value;
            }
        }
        foreach ($unpaidAccountNames as $nameField => $accountName) {
            $accountIndex = $nameField === "unpaidAccountName" ? "" : substr($nameField, strlen("unpaidAccountName_"));
            $amountField = $accountIndex === "" ? "unpaidAccountAmount" : "unpaidAccountAmount_" . $accountIndex;
            $accountName = trim((string) $accountName);
            $accountAmount = $money($payload[$amountField] ?? 0);
            if ($accountName !== "" && $accountAmount > 0) {
                $posUnpaidNonOwnerTotal += $accountAmount;
            }
        }
        foreach ($cardPaymentFields as $field) {
            $posCardSalesTotal += $money($report["payload"][$field] ?? 0);
        }
        foreach (["bpi", "easwest", "giftcheck", "cheque"] as $field) {
            $posBreakdownTotals[$field] = ($posBreakdownTotals[$field] ?? 0) + $money($report["payload"][$field] ?? 0);
        }
    }
}
$grabNet = $money($payloadTotals["onlineTips"] ?? 0);
$paymentTotal = array_sum($payments) + $ownerPaidTotal;
$cashSalesTotal = $payments["Cash"] ?? ($payments["Cash Sales"] ?? 0.0);
$cardSalesTotal = 0.0;
foreach (["GCash + QR PH", "Gcash + QRPH", "PayMaya", "AMEX", "Visa", "Mastercard", "BancNet", "JCB"] as $paymentMethod) {
    $cardSalesTotal += (float) ($payments[$paymentMethod] ?? 0.0);
}
$otherCashTotal = $payments["id_13"] ?? 0.0;
$mayaTerminalSalesTotal = $payments["id_14"] ?? 0.0;
$cardSalesTotal += $mayaTerminalSalesTotal;
$ownerTotal = array_sum($ownerAccounts);
$nonPosOwnerTotal = array_sum($nonPosOwnerAccounts) + array_sum($nonPosUnpaidOwnerAmounts);
$ownerAccountTotal = $ownerTotal - $nonPosOwnerTotal;
$shortOver = $payloadTotals["reconShortOver"] ?? 0.0;

// Calculate non-pos total as: cash + card + breakdown + owner accounts + unpaid - short over
$nonPos = $nonPosCashRemitted
    + $nonPosCardSalesTotal
    + $money($nonPosBreakdownTotals["bpi"] ?? 0)
    + $money($nonPosBreakdownTotals["easwest"] ?? 0)
    + $money($nonPosBreakdownTotals["giftcheck"] ?? 0)
    + $money($nonPosBreakdownTotals["cheque"] ?? 0)
    + array_sum($nonPosOwnerAccounts)
    + array_sum($nonPosUnpaidOwnerAmounts)
    - $nonPosShortOver;
if ($nonPos < 0) {
    $nonPos = abs($nonPos);
}
$otherPaymentsTotal = $money($payloadTotals["paidAccounts"] ?? 0)
    + $money($payloadTotals["advancePayments"] ?? 0)
    + ($payments["id_15"] ?? 0)  // Direct BT BPI Nooma (Other)
    + ($payments["id_16"] ?? 0)  // Direct BT EastWest Nooma (Other)
    + ($payments["id_17"] ?? 0)  // Gift Check (Other)
    + ($payments["id_18"] ?? 0); // Cheques (Other)
$depositOtherPaymentsTotal = $otherCashTotal + $mayaTerminalSalesTotal + $otherPaymentsTotal;
$posBreakdownTotal = $cashSalesTotal
    + $cardSalesTotal
    + $money($payloadTotals["bpi"] ?? 0)
    + $money($payloadTotals["easwest"] ?? 0)
    + $money($payloadTotals["giftcheck"] ?? 0)
    + $money($payloadTotals["cheque"] ?? 0);
$posSales = $posShortOver 
    + $posOtherPaymentsTotal 
    + $posCashRemittedTotal 
    + $posCardSalesTotal
    + $posUnpaidNonOwnerTotal
    + $money($posBreakdownTotals["bpi"] ?? 0)
    + $money($posBreakdownTotals["easwest"] ?? 0)
    + $money($posBreakdownTotals["giftcheck"] ?? 0)
    + $money($posBreakdownTotals["cheque"] ?? 0)
    + ($ownerTotal - $nonPosOwnerTotal);
$totalGrossSales = $posSales + $nonPos + $grabNet;

$forDeposit = $payments['Cash Sales'] ?? ($payments['CASH SALES'] ?? $paymentTotal);
$unsettled = $weekendCardSales;

function peso(float $amount): string {
    return "₱" . number_format($amount, 2);
}
function reportLabel(string $field): string {
    return ucwords(preg_replace("/(?<!^)[A-Z]/", " $0", $field));
}

$totalPos = $posCashRemittedTotal + $posCardSalesTotal + $money($posBreakdownTotals["bpi"] ?? 0) + $money($posBreakdownTotals["easwest"] ?? 0) + $money($posBreakdownTotals["giftcheck"] ?? 0) + $money($posBreakdownTotals["cheque"] ?? 0) + $ownerAccountTotal + $posUnpaidNonOwnerTotal + $posShortOver;
$totalGrossSales = $totalPos + $nonPos + $grabNet;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Summary - Nooma</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<?php include "../Components/sidebar.php"; ?>
<div class="lg:ml-64 min-h-screen">
    <?php include "../Components/navbar.php"; ?>
    <main class="p-4 md:p-6">
        <div class="flex flex-wrap items-end justify-between gap-4 mb-6">
            <div><h2 class="text-2xl font-bold">Weekly Summary</h2><p class="text-sm text-gray-500 mt-1">Spreadsheet-style weekly collection report.</p></div>
            <div class="flex flex-wrap items-end gap-2">
                <form method="get" class="flex flex-wrap items-end gap-2"><div><label for="dateStart" class="block text-xs font-medium text-gray-500 mb-1">From</label><input type="date" id="dateStart" name="date_start" value="<?= htmlspecialchars($weekStart) ?>" class="border rounded-xl px-3 py-2 bg-white" onchange="this.form.submit()"></div><div><label for="dateEnd" class="block text-xs font-medium text-gray-500 mb-1">To</label><input type="date" id="dateEnd" name="date_end" value="<?= htmlspecialchars($weekEnd) ?>" class="border rounded-xl px-3 py-2 bg-white" onchange="this.form.submit()"></div></form>
                <a href="../../backend/reports/export_weekly.php?date_start=<?= urlencode($weekStart) ?>&date_end=<?= urlencode($weekEnd) ?>"
                   download="weekly_summary_<?= htmlspecialchars($weekStart) ?>_to_<?= htmlspecialchars($weekEnd) ?>.xls"
                   class="px-4 py-2.5 bg-emerald-600 text-white rounded-xl font-semibold hover:bg-emerald-700">
                    Download Excel
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">
            <!-- MAIN SPREADSHEET TABLE -->
            <section class="xl:col-span-3 bg-white border-2 border-gray-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px] text-xs border-collapse">
                        <thead>
                            <tr class="bg-blue-100 border-b-2 border-gray-800 text-center">
                                <th colspan="2" class="border-r border-gray-800 px-3 py-2 text-left">
                                    <div class="text-[10px] text-gray-600 font-semibold">TOTAL GROSS SALES:</div>
                                    <div class="text-sm font-bold text-red-700"><?= peso($totalGrossSales) ?></div>
                                </th>
                                <th class="border-r border-gray-800 px-2 py-2">
                                    <div class="text-[10px] text-gray-600 font-semibold">POS</div>
                                    <div class="font-bold"><?= peso($totalPos) ?></div>
                                </th>
                                <th class="border-r border-gray-800 px-2 py-2">
                                    <div class="text-[10px] text-gray-600 font-semibold">NON POS</div>
                                    <div class="font-bold"><?= peso($nonPos) ?></div>
                                </th>
                                <th class="border-r border-gray-800 px-2 py-2">
                                    <div class="text-[10px] text-gray-600 font-semibold">GRAB SALES (NET)</div>
                                    <div class="font-bold"><?= peso($grabNet) ?></div>
                                </th>
                                <th class="border-r border-gray-800 px-2 py-2">
                                    <div class="text-[10px] text-gray-600 font-semibold">OTHER PAYMENTS</div>
                                    <div class="font-bold"><?= peso($otherPaymentsTotal) ?></div>
                                </th>
                                <th colspan="2" class="bg-emerald-200 px-3 py-2 text-right">
                                    <div class="text-[10px] text-gray-700 font-bold">TOTAL COLLECTION</div>
                                    <div class="text-sm font-bold text-emerald-900"><?= peso($posCashRemittedTotal + $nonPosCashRemitted + $otherCashTotal + $otherPaymentsTotal + $cardSalesTotal + ($payloadTotals["bpi"] ?? 0) + ($payloadTotals["easwest"] ?? 0) + ($payloadTotals["giftcheck"] ?? 0) + ($payloadTotals["cheque"] ?? 0) + $grabNet) ?></div>
                                </th>
                            </tr>
                            <tr class="border-b-2 border-gray-800 bg-gray-100">
                                <th colspan="8" class="px-4 py-1.5 text-center font-bold tracking-wide">
                                    <?= strtoupper(date("F j, Y", strtotime($weekStart))) ?> TO <?= strtoupper(date("F j, Y", strtotime($weekEnd))) ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- BREAKDOWN -->
                           <tr class="border-b border-gray-400 bg-gray-50">
                                <td colspan="8" class="px-4 py-1.5 font-bold text-red-700">
                                    BREAKDOWN:
                                </td>
                            </tr>
                            <tr class="border-b border-gray-200">
                                <td colspan="2" class="px-4 py-1.5 font-semibold text-red-700 border-r border-gray-200">
                                    CASH SALES
                                </td>
                                <td class="px-4 py-1.5 text-right font-semibold border-r border-gray-200">
                                    <?= peso($posCashRemittedTotal) ?>
                                </td>
                                <td class="px-4 py-1.5 text-right font-semibold border-r border-gray-200">
                                    <?= peso($nonPosCashRemitted) ?>
                                </td> 
                               <td colspan="3" class="border-r border-gray-200 text-right">
                                    <?= peso($otherCashTotal) ?>
                                </td>
                                <td class="px-4 py-1.5 text-right bg-emerald-50 font-semibold">
                                    <?= peso($posCashRemittedTotal + $nonPosCashRemitted + $otherCashTotal) ?>
                                </td>
                                
                            </tr>
                            <tr class="border-b border-gray-200">
                                <td colspan="2" class="px-4 py-1.5 font-semibold text-red-700 border-r border-gray-200">
                                    CARD SALES(MAYA TERMINAL)
                                </td>
                                <td class="px-4 py-1.5 text-right font-semibold border-r border-gray-200">
                                    <?= peso($posCardSalesTotal) ?>
                                </td>
                                <td class="px-4 py-1.5 text-right font-semibold border-r border-gray-200">
                                    <?= peso($nonPosCardSalesTotal) ?>
                                </td>
                                <td colspan="3" class="border-r border-gray-200 text-right">
                                    <?= peso($mayaTerminalSalesTotal) ?>
                                </td>
                                <td class="px-4 py-1.5 text-right bg-emerald-50 font-semibold">
                                    <?= peso($cardSalesTotal) ?>
                                </td>
                            </tr>
                            <tr class="border-b border-gray-200">
                                <td colspan="2" class="px-4 py-1.5 font-semibold text-red-700 border-r border-gray-200">
                                   DIRECT BT BPI NOOMA
                                </td>
                                <td class="px-4 py-1.5 text-right font-semibold border-r border-gray-200">
                                    <?= peso($posBreakdownTotals["bpi"] ?? 0) ?>
                                </td>
                                <td class="px-4 py-1.5 text-right font-semibold border-r border-gray-200">
                                    <?= peso($nonPosBreakdownTotals["bpi"] ?? 0) ?>
                                </td>
                                <td colspan="3" class="border-r border-gray-200 text-right">
                                    <?= peso($payments["id_15"] ?? 0) ?>
                                </td>
                                <td class="px-4 py-1.5 text-right bg-emerald-50 font-semibold">
                                    <?= peso(($payloadTotals["bpi"] ?? 0) + ($payments["id_15"] ?? 0)) ?>
                                </td>
                            </tr>
                             <tr class="border-b border-gray-200">
                                <td colspan="2" class="px-4 py-1.5 font-semibold text-red-700 border-r border-gray-200">
                                   DIRECT BT EWB NOOMA
                                </td>
                                <td class="px-4 py-1.5 text-right font-semibold border-r border-gray-200">
                                    <?= peso($posBreakdownTotals["easwest"] ?? 0) ?>
                                </td>
                                <td class="px-4 py-1.5 text-right font-semibold border-r border-gray-200">
                                    <?= peso($nonPosBreakdownTotals["easwest"] ?? 0) ?>
                                </td>
                                <td colspan="3" class="border-r border-gray-200 text-right">
                                    <?= peso($payments["id_16"] ?? 0) ?>
                                </td>
                                <td class="px-4 py-1.5 text-right bg-emerald-50 font-semibold">
                                    <?= peso(($payloadTotals["easwest"] ?? 0) + ($payments["id_16"] ?? 0)) ?>
                                </td>
                            </tr>
                             <tr class="border-b border-gray-200">
                                <td colspan="2" class="px-4 py-1.5 font-semibold text-red-700 border-r border-gray-200">
                                   GIFT CHECK PAYMENTS
                                </td>
                                <td class="px-4 py-1.5 text-right font-semibold border-r border-gray-200">
                                    <?= peso($posBreakdownTotals["giftcheck"] ?? 0) ?>
                                </td>
                                <td class="px-4 py-1.5 text-right font-semibold border-r border-gray-200">
                                    <?= peso($nonPosBreakdownTotals["giftcheck"] ?? 0) ?>
                                </td>
                                <td colspan="3" class="border-r border-gray-200 text-right">
                                    <?= peso($payments["id_17"] ?? 0) ?>
                                </td>
                                <td class="px-4 py-1.5 text-right bg-emerald-50 font-semibold">
                                    <?= peso(($payloadTotals["giftcheck"] ?? 0) + ($payments["id_17"] ?? 0)) ?>
                                </td>
                            </tr>
                            <tr class="border-b border-gray-200">
                                <td colspan="2" class="px-4 py-1.5 font-semibold text-red-700 border-r border-gray-200">
                                    CHEQUE PAYMENTS
                                </td>
                                <td class="px-4 py-1.5 text-right font-semibold border-r border-gray-200">
                                    <?= peso($posBreakdownTotals["cheque"] ?? 0) ?>
                                </td>
                                <td class="px-4 py-1.5 text-right font-semibold border-r border-gray-200">
                                    <?= peso($nonPosBreakdownTotals["cheque"] ?? 0) ?>
                                </td>
                                <td colspan="3" class="border-r border-gray-200 text-right">
                                    <?= peso($payments["id_18"] ?? 0) ?>
                                </td>
                                <td class="px-4 py-1.5 text-right bg-emerald-50 font-semibold">
                                    <?= peso(($payloadTotals["cheque"] ?? 0) + ($payments["id_18"] ?? 0)) ?>
                                </td>
                            </tr>

                            <!-- RECEIVABLES -->
                            <tr><td colspan="8" class="px-4 pt-4 pb-1 font-bold text-red-700">RECEIVABLES:</td></tr>
                            <!-- <?php 
                            $unpaidOwnerTotal = 0;
                            foreach ($ownerAccounts as $label => $amount) {
                                $accountId = $ownerAccountIds[$label] ?? null;
                                if ($accountId && !empty($unpaidOwnerAccountIds[$accountId])) {
                                    $unpaidOwnerTotal += $amount;
                                }
                            }
                            $ownerAccountTotal = $ownerTotal - $nonPosOwnerTotal;
                            ?> -->
                            <tr class="border-b border-gray-200">
                                <td colspan="2" class="px-4 py-1 font-semibold text-red-700 border-r border-gray-200">OWNER'S ACCT (SUM)</td>
                                <td class="px-4 py-1 text-right font-semibold border-r border-gray-200"><?= peso($ownerAccountTotal) ?></td>
                                <td class="px-4 py-1 text-right font-semibold border-r border-gray-200"><?= peso($nonPosOwnerTotal) ?></td>
                                <td colspan="4"></td>
                            </tr>
                            <?php foreach ($ownerAccounts as $label => $amount): ?>
                                <?php
                                $accountId = $ownerAccountIds[$label] ?? null;
                                $isUnpaid = $accountId && !empty($unpaidOwnerAccountIds[$accountId]);
                                $unpaidAmount = $unpaidOwnerAmounts[$accountId] ?? 0;
                                $displayAmount = $isUnpaid ? $unpaidAmount : $amount;
                                $nonPosOwnerAmount = $isUnpaid
                                    ? ($nonPosUnpaidOwnerAmounts[$accountId] ?? 0)
                                    : ($nonPosOwnerAccounts[$accountId] ?? 0);
                                $posOwnerAmount = $displayAmount - $nonPosOwnerAmount;
                                $statusLabel = $isUnpaid ? "UNPAID" : "PAID";
                                $statusClasses = $isUnpaid
                                    ? "bg-red-100 text-red-800 border-red-200"
                                    : "bg-emerald-100 text-emerald-800 border-emerald-200";
                                ?>
                                <tr class="border-b border-gray-200 <?= $isUnpaid ? "bg-red-50" : "bg-emerald-50" ?>">
                                    <td colspan="2" class="px-4 py-1 pl-8 border-r border-gray-200">
                                        <?= htmlspecialchars(strtoupper($label)) ?>
                                        <span class="ml-2 inline-flex items-center justify-center px-1.5 py-0.5 rounded text-[10px] font-bold <?= $statusClasses ?>"></span>
                                    </td>
                                    <td class="px-4 py-1 text-right border-r border-gray-200"><?= peso($posOwnerAmount) ?></td>
                                    <td class="px-4 py-1 text-right border-r border-gray-200"><?= peso($nonPosOwnerAmount) ?></td>
                                    <td colspan="4"></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="border-b border-gray-200">
                                <td colspan="2" class="px-4 py-1 font-semibold text-red-700 border-r border-gray-200">UNPAID (SUM)</td>
                                <td class="px-4 py-1 text-right border-r border-gray-200"><?= peso(array_sum($unpaidNonOwnerAccounts)) ?></td>
                                <td class="px-4 py-1 text-right border-r border-gray-200"><?= peso(array_sum($unpaidOwnerAmounts)) ?></td>
                                <td colspan="4"></td>
                            </tr>
                            <?php foreach ($unpaidNonOwnerAccounts as $label => $amount): ?>
                                <tr class="border-b border-red-200 bg-red-50">
                                    <td colspan="2" class="px-4 py-1 pl-8 border-r border-red-200">
                                        <?= htmlspecialchars(strtoupper($label)) ?>
                                        <?php if (!empty($unpaidNonOwnerNotes[$label])): ?>
                                            <div class="text-xs text-gray-500 mt-1">
                                                Note: <?= htmlspecialchars($unpaidNonOwnerNotes[$label]) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-1 text-right font-semibold border-r border-red-200"><?= peso($amount) ?></td>
                                    <td colspan="5"></td>
                                </tr>
                            <?php endforeach; ?>

                            <!-- C/O MARKETING -->
                            <!-- <tr><td colspan="8" class="px-4 pt-4 pb-1 font-bold text-red-700">C/O MARKETING:</td></tr>
                            <?php foreach ($payloadTotals as $field => $amount): ?>
                                <?php if (in_array($field, ["unpaidAccounts", "paidAccounts", "ownersDiscount", "cashSales", "grabSaleGross", "onlineTips", "grandTotal", "totalSale"], true)) continue; ?>
                                <tr class="border-b border-gray-200">
                                    <td colspan="2" class="px-4 py-1 font-semibold text-red-700 border-r border-gray-200"><?= htmlspecialchars(strtoupper(reportLabel($field))) ?></td>
                                    <td class="px-4 py-1 text-right border-r border-gray-200"><?= peso($amount) ?></td>
                                    <td colspan="5"></td>
                                </tr>
                            <?php endforeach; ?> -->

                            <!-- OTHER PAYMENTS -->
                            <tr><td colspan="8" class="px-4 pt-4 pb-1 font-bold text-red-700">OTHER PAYMENTS:</td></tr>
                            <?php foreach (["paidAccounts" => "PAID ACCTS (CURRENT YEAR)", "advancePayments" => "ADVANCE PAYMENTS" , "tipcollected" => "TIPS COLLECTED"] as $field => $label): ?>
                                <tr class="border-b border-gray-200">
                                    <td colspan="2" class="px-4 py-1 font-semibold text-red-700 border-r border-gray-200"><?= $label ?></td>
                                    <td class="px-4 py-1 text-right border-r border-gray-200"><?= peso($payloadTotals[$field] ?? 0) ?></td>
                                    <td colspan="5"></td>
                                </tr>
                            <?php endforeach; ?>

                            <tr class="border-b border-gray-200">
                                <td colspan="2" class="px-4 py-1 font-semibold text-red-700 border-r border-gray-200">SHORT / OVER</td>
                                <td class="px-4 py-1 text-right font-semibold text-red-600 border-r border-gray-200"><?= peso($posShortOver) ?></td>
                                <td class="px-4 py-1 text-right font-semibold text-red-600 border-r border-gray-200"><?= peso($nonPosShortOver) ?></td>
                                <td colspan="3" class="border-r border-gray-200"></td>
                            </tr>

                            <!-- FOOTER TOTAL GROSS SALES -->
                            <tr class="bg-blue-100 border-t-2 border-gray-800 font-bold">
                                <td colspan="2" class="px-3 py-2 border-r border-gray-800">TOTAL GROSS SALES:</td>
                                <td class="px-2 py-2 text-right border-r border-gray-800">POS<br><?= peso($totalPos) ?></td>
                                <td class="px-2 py-2 text-right border-r border-gray-800">NON POS<br><?= peso($nonPos) ?></td>
                                <td class="px-2 py-2 text-right border-r border-gray-800">GRAB SALES<br><?= peso($grabNet) ?></td>
                                <td colspan="2"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- SIDE PANEL (FOR DEPOSIT / UNSETTLED) -->
            <section class="bg-white border-2 border-gray-800 p-4 shadow-sm h-fit space-y-4">
                <div class="flex justify-between items-center border-b pb-2">
                    <span class="text-xs font-bold text-gray-700">FOR DEPOSIT:</span>
                    <span class="text-sm font-bold text-emerald-700"><?= peso($posCashRemittedTotal + $nonPosCashRemitted + $depositOtherPaymentsTotal) ?></span>
                </div>
                <div class="flex justify-between items-center border-b pb-2">
                    <span class="text-xs font-bold text-gray-700">UNSETTLED:</span>
                    <span class="text-sm font-bold text-orange-600"><?= peso($unsettled) ?></span>
                </div>
            </section>
        </div>
    </main>
    <?php include "../Components/footer.php"; ?>
</div>
</body>
</html>