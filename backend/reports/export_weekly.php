<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(401);
    exit("Unauthorized");
}

require_once __DIR__ . "/../config/database.php";

$dateStart = trim((string) ($_GET["date_start"] ?? $_GET["week_start"] ?? ""));
$dateEnd = trim((string) ($_GET["date_end"] ?? ""));
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $dateStart)) {
    $dateStart = date("Y-m-d", strtotime("monday this week"));
}
if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $dateEnd) || $dateEnd < $dateStart) {
    $dateEnd = date("Y-m-d", strtotime($dateStart . " +6 days"));
}

$money = static function ($value): float {
    $value = str_replace(",", "", (string) $value);
    return is_numeric($value) ? (float) $value : 0.0;
};

$reports = [];
$stmt = mysqli_prepare($conn, "SELECT report_id, report_number, report_date, shift_name, total_pax, telegram_declared_total, pos_sales_total, notes FROM daily_reports WHERE report_date BETWEEN ? AND ? AND status <> 'voided'");
mysqli_stmt_bind_param($stmt, "ss", $dateStart, $dateEnd);
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
$qrToJcbByReport = [];
if ($reportIds) {
    $idList = implode(",", $reportIds);
    $paymentResult = mysqli_query($conn, "SELECT pm.method_name, pm.payment_method_id, SUM(rp.amount) AS total FROM report_payments rp INNER JOIN payment_methods pm ON pm.payment_method_id = rp.payment_method_id WHERE rp.report_id IN ({$idList}) GROUP BY rp.payment_method_id, pm.method_name ORDER BY pm.display_order");
    while ($row = mysqli_fetch_assoc($paymentResult)) {
        $payments[$row["method_name"]] = (float) $row["total"];
        // Also store by payment method ID for easier access
        $payments["id_" . $row["payment_method_id"]] = (float) $row["total"];
    }
    $qrToJcbResult = mysqli_query($conn, "SELECT report_id, SUM(amount) AS total FROM report_payments WHERE report_id IN ({$idList}) AND payment_method_id BETWEEN 2 AND 8 GROUP BY report_id");
    while ($row = mysqli_fetch_assoc($qrToJcbResult)) {
        $qrToJcbByReport[(int) $row["report_id"]] = (float) $row["total"];
    }
    $accountResult = mysqli_query($conn, "SELECT ah.account_holder_id, ah.account_name, SUM(ra.amount) AS total FROM report_accounts ra INNER JOIN account_holders ah ON ah.account_holder_id = ra.account_holder_id WHERE ra.report_id IN ({$idList}) AND ra.account_category = 'owners_account' GROUP BY ra.account_holder_id, ah.account_name ORDER BY ah.account_name");
    while ($row = mysqli_fetch_assoc($accountResult)) {
        $ownerAccounts[$row["account_name"]] = (float) $row["total"];
        $ownerAccountIds[$row["account_name"]] = $row["account_holder_id"];
    }
}

$unpaidOwnerAccountIds = [];
$unpaidOwnerAmounts = [];
$unpaidNonOwnerAccounts = [];
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
        $accountName = trim((string) $accountName);
        $accountAmount = $money($payload[$amountField] ?? 0);
        if ($accountName !== "" && $accountAmount > 0) {
            $unpaidNonOwnerAccounts[$accountName] = ($unpaidNonOwnerAccounts[$accountName] ?? 0) + $accountAmount;
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
}

$declaredTotal = array_sum(array_map(static fn (array $row): float => (float) $row["telegram_declared_total"], $reports));
$nonPos = 0.0;
$posCashRemittedTotal = 0.0;
$nonPosCashRemitted = 0.0;
$posCardSalesTotal = 0.0;
$nonPosCardSalesTotal = 0.0;
$posQrToJcbTotal = 0.0;
$nonPosQrToJcbTotal = 0.0;
$posShortOver = 0.0;
$nonPosShortOver = 0.0;
$posOtherPaymentsTotal = 0.0;
$nonPosOtherPaymentsTotal = 0.0;
$posUnpaidNonOwnerTotal = 0.0;
$posBreakdownTotals = [];
$nonPosBreakdownTotals = [];
$nonPosOwnerAccounts = [];
$nonPosUnpaidOwnerAmounts = [];
$cardPaymentFields = ["gcashQrph", "paymaya", "amex", "visa", "mastercard", "bancnet", "jcb"];
$breakdownFields = ["bpi", "easwest", "giftcheck", "cheque"];
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
        $nonPosQrToJcbTotal += $qrToJcbByReport[(int) $report["report_id"]] ?? 0.0;
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
        foreach ($breakdownFields as $field) {
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
        $posQrToJcbTotal += $qrToJcbByReport[(int) $report["report_id"]] ?? 0.0;
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
        foreach ($breakdownFields as $field) {
            $posBreakdownTotals[$field] = ($posBreakdownTotals[$field] ?? 0) + $money($report["payload"][$field] ?? 0);
        }
    }
}
$grabNet = $payloadTotals["onlineTips"] ?? 0.0;
$tipCollectedTotal = ($payloadTotals["cashSales"] ?? 0.0) + ($payloadTotals["onlineTips"] ?? 0.0);
$paymentTotal = array_sum($payments);
$ownerTotal = array_sum($ownerAccounts);
$nonPosOwnerTotal = array_sum($nonPosOwnerAccounts) + array_sum($nonPosUnpaidOwnerAmounts);
$ownerAccountTotal = $ownerTotal - $nonPosOwnerTotal;
$shortOver = $payloadTotals["reconShortOver"] ?? 0.0;
$cashSalesTotal = (float) ($payments["Cash"] ?? ($payments["Cash Sales"] ?? 0));
$cardSalesTotal = 0.0;
foreach (["GCash + QR PH", "Gcash + QRPH", "PayMaya", "Maya Terminal", "AMEX", "Visa", "Mastercard", "BancNet", "JCB"] as $cardMethod) {
    $cardSalesTotal += (float) ($payments[$cardMethod] ?? 0);
}
$otherPaymentsTotal = ($payloadTotals["paidAccounts"] ?? 0)
    + ($payloadTotals["advancePayments"] ?? 0)
    + ($payments["id_13"] ?? 0)
    + ($payments["id_14"] ?? 0)
    + ($payments["id_15"] ?? 0)
    + ($payments["id_16"] ?? 0)
    + ($payments["id_17"] ?? 0)
    + ($payments["id_18"] ?? 0);
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
$totalPos = $posCashRemittedTotal
    + $posCardSalesTotal
    + $money($posBreakdownTotals["bpi"] ?? 0)
    + $money($posBreakdownTotals["easwest"] ?? 0)
    + $money($posBreakdownTotals["giftcheck"] ?? 0)
    + $money($posBreakdownTotals["cheque"] ?? 0)
    + $ownerAccountTotal
    + $posUnpaidNonOwnerTotal
    + $posShortOver;
$totalGrossSales = $totalPos + $nonPos + $grabNet;
$forDeposit = $posCashRemittedTotal + $nonPosCashRemitted + $otherPaymentsTotal;
$unsettled = $weekendCardSales;

function peso(float $amount): string {
    // Using HTML entity code &#8369; prevents Excel from converting symbol fonts to East Asian characters
    return "&#8369;" . number_format($amount, 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<style>
body { font-family: Arial, sans-serif; color: #111; background: #fff; padding: 20px; }
table { border-collapse: collapse; table-layout: fixed; }
table.layout { width: 1470px; }
table.main { width: 1100px; border: 2px solid #111; }
th, td { border: 1px solid #777; padding: 6px 8px; font-size: 11px; font-family: Arial, sans-serif; }
.header { background: #d9e8f7; font-weight: bold; text-align: center; } 
.section { background: #f5f5f5; color: #9d2929; font-weight: bold; }
.label { color: #9d2929; font-weight: bold; } 
.collection { background: #d9f0df; font-weight: bold; }
.total { background: #d9e8f7; font-weight: bold; } 
.number { text-align: right; vertical-align: middle; white-space: nowrap; }
.layout-spacer { width: 24px; border: 0; }
.side-cell { width: 346px; vertical-align: top; border: 0; padding: 0; }
.side { width: 100%; border: 2px solid #111; background: #fff; }
.unpaid-badge { background: #ffe6e6; color: #9d2929; padding: 2px 6px; border-radius: 4px; font-size: 9px; margin-left: 6px; }
</style>
</head>
<body>
<table class="layout">
<tr>
<td style="vertical-align: top; border: 0; padding: 0;">
<table class="main">
    <colgroup>
        <col style="width: 250px;">
        <col style="width: 150px;">
        <col style="width: 150px;">
        <col style="width: 150px;">
        <col style="width: 150px;">
        <col style="width: 250px;">
    </colgroup>
    <tr class="header">
        <th colspan="2" style="text-align: left;">
            <div style="font-size: 9px; color: #555;">TOTAL GROSS SALES:</div>
            <div style="font-size: 14px; color: #9d2929;"><?= peso($totalGrossSales) ?></div>
        </th>
        <th>
            <div style="font-size: 9px; color: #555;">POS</div>
            <div><?= peso($totalPos) ?></div>
        </th>
        <th>
            <div style="font-size: 9px; color: #555;">NON POS</div>
            <div><?= peso($nonPos) ?></div>
        </th>
        <th>
            <div style="font-size: 9px; color: #555;">GRAB SALES (NET)</div>
            <div><?= peso($grabNet) ?></div>
        </th>
        <th>
            <div style="font-size: 9px; color: #555;">OTHER PAYMENTS</div>
            <div><?= peso($otherPaymentsTotal) ?></div>
        </th>
        <th colspan="2" class="collection" style="text-align: right;">
            <div style="font-size: 9px; color: #555;">TOTAL COLLECTION</div>
            <div style="font-size: 14px; color: #116633;"><?= peso($posCashRemittedTotal + $nonPosCashRemitted + $otherPaymentsTotal + $cardSalesTotal + ($payloadTotals["bpi"] ?? 0) + ($payloadTotals["easwest"] ?? 0) + ($payloadTotals["giftcheck"] ?? 0) + ($payloadTotals["cheque"] ?? 0) + $grabNet) ?></div>
        </th>
    </tr>
    <tr><th colspan="8" class="section" style="text-align: center;"><?= htmlspecialchars(strtoupper(date("F j, Y", strtotime($dateStart)) . " TO " . date("F j, Y", strtotime($dateEnd)))) ?></th></tr>
    
    <tr><td colspan="8" class="section">BREAKDOWN:</td></tr>
    <?php
    $exportRows = [
        ["CASH SALES", $posCashRemittedTotal, $nonPosCashRemitted, $posCashRemittedTotal + $nonPosCashRemitted + $otherPaymentsTotal],
        ["CARD SALE(MAYA TERMINAL)", $posCardSalesTotal, $nonPosCardSalesTotal, $cardSalesTotal],
        ["DIRECT BT BPI NOOMA", $posBreakdownTotals["bpi"] ?? 0, $nonPosBreakdownTotals["bpi"] ?? 0, $payloadTotals["bpi"] ?? 0],
        ["DIRECT BT EWB NOOMA", $posBreakdownTotals["easwest"] ?? 0, $nonPosBreakdownTotals["easwest"] ?? 0, $payloadTotals["easwest"] ?? 0],
        ["GIFT CHECK PAYMENTS", $posBreakdownTotals["giftcheck"] ?? 0, $nonPosBreakdownTotals["giftcheck"] ?? 0, $payloadTotals["giftcheck"] ?? 0],
        ["CHEQUE PAYMENTS", $posBreakdownTotals["cheque"] ?? 0, $nonPosBreakdownTotals["cheque"] ?? 0, $payloadTotals["cheque"] ?? 0],
        ["OTHER PAYMENTS", $posOtherPaymentsTotal, $nonPosOtherPaymentsTotal, $otherPaymentsTotal],
    ];
    foreach ($exportRows as [$rowLabel, $posAmount, $nonPosAmount, $rightAmount]):
    ?>
        <tr>
            <td colspan="2" class="label"><?= $rowLabel ?></td>
            <td class="number"><?= peso($posAmount) ?></td>
            <td class="number"><?= peso($nonPosAmount) ?></td>
            <td colspan="3"></td>
            <td class="collection number"><?= peso($rightAmount) ?></td>
        </tr>
    <?php endforeach; ?>

    <tr><td colspan="8" class="section">RECEIVABLES:</td></tr>
    <tr>
        <td colspan="2" class="label">OWNER'S ACCT (SUM)</td>
        <td class="number"><?= peso($ownerAccountTotal) ?></td>
        <td class="number"><?= peso($nonPosOwnerTotal) ?></td>
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
        ?>
        <tr>
            <td colspan="2" style="padding-left: 20px;">
                <?= htmlspecialchars(strtoupper($label)) ?>
                <?php if ($isUnpaid): ?><span class="unpaid-badge">UNPAID</span><?php endif; ?>
            </td>
            <td class="number"><?= peso($posOwnerAmount) ?></td>
            <td class="number"><?= peso($nonPosOwnerAmount) ?></td>
            <td colspan="4"></td>
        </tr>
    <?php endforeach; ?>
    <tr>
        <td colspan="2" class="label">UNPAID (SUM)</td>
        <td class="number"><?= peso(array_sum($unpaidNonOwnerAccounts)) ?></td>
        <td class="number"><?= peso(array_sum($unpaidOwnerAmounts)) ?></td>
        <td colspan="4"></td>
    </tr>
    <?php foreach ($unpaidNonOwnerAccounts as $label => $amount): ?>
        <tr>
            <td colspan="2" style="padding-left: 20px;">
                <?= htmlspecialchars(strtoupper($label)) ?>
            </td>
            <td class="number"><?= peso($amount) ?></td>
            <td colspan="5"></td>
        </tr>
    <?php endforeach; ?>

    <tr><td colspan="8" class="section">OTHER PAYMENTS:</td></tr>
    <?php foreach (["paidAccounts" => "PAID ACCTS (CURRENT YEAR)", "advancePayments" => "ADVANCE PAYMENTS"] as $field => $label): ?>
        <tr>
            <td colspan="2" class="label"><?= $label ?></td>
            <td class="number"><?= peso($payloadTotals[$field] ?? 0) ?></td>
            <td colspan="5"></td>
        </tr>
    <?php endforeach; ?>
    <?php
    $otherPaymentMethods = [
        "id_13" => "OTHER CASH",
        "id_14" => "CARD (MAYA TERMINAL)",
        "id_15" => "DIRECT BT BPI NOOMA (OTHER)",
        "id_16" => "DIRECT BT EASTWEST NOOMA (OTHER)",
        "id_17" => "GIFT CHECK (OTHER)",
        "id_18" => "CHEQUES (OTHER)"
    ];
    foreach ($otherPaymentMethods as $id => $label):
        $amount = $payments[$id] ?? 0;
        if ($amount > 0):
    ?>
        <tr>
            <td colspan="2" class="label"><?= $label ?></td>
            <td class="number"><?= peso($amount) ?></td>
            <td colspan="5"></td>
        </tr>
    <?php endif; endforeach; ?>
    <tr>
        <td colspan="2" class="label">TIPS COLLECTED</td>
        <!-- <td class="number"><?= peso($tipCollectedTotal) ?></td> -->
        <td colspan="5"></td>
    </tr>
    <tr>
        <td colspan="2" class="label">SHORT / OVER</td>
        <td class="number" style="color: #9d2929;"><?= peso($shortOver) ?></td>
        <td class="number" style="color: #9d2929;"><?= peso($nonPosShortOver) ?></td>
        <td colspan="4"></td>
    </tr>
    <tr class="total">
        <th colspan="2" style="text-align: left;">TOTAL GROSS SALES:</th>
        <th>POS<br><?= peso($totalPos) ?></th>
        <th>NON POS<br><?= peso($nonPos) ?></th>
        <th colspan="2">GRAB SALES<br><?= peso($grabNet) ?></th>
        <th colspan="2"></th>
    </tr>
</table>
</td>
<td class="layout-spacer"></td>
<td class="side-cell">
<table class="side">
    <tr>
        <th style="text-align: left; padding: 10px;">FOR DEPOSIT:</th>
        <td class="number" style="padding: 10px; font-size: 13px; color: #116633;"><?= peso($forDeposit) ?></td>
    </tr>
    <tr>
        <th style="text-align: left; padding: 10px;">UNSETTLED:</th>
        <td class="number" style="padding: 10px; font-size: 13px; color: #cc5500;"><?= peso($unsettled) ?></td>
    </tr>
</table>
</td>
</tr>
</table>
</body>
</html>