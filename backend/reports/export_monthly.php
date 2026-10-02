<?php

session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(401);
    exit("Unauthorized");
}

require_once __DIR__ . "/../config/database.php";

/*
|--------------------------------------------------------------------------
| Selected Month
|--------------------------------------------------------------------------
| Example:
| ?month=2026-09
|--------------------------------------------------------------------------
*/

$selectedMonth = trim((string) ($_GET["month"] ?? date("Y-m")));

if (!preg_match("/^\d{4}-\d{2}$/", $selectedMonth)) {
    $selectedMonth = date("Y-m");
}

$year = (int) substr($selectedMonth, 0, 4);
$selectedMonthNumber = (int) substr($selectedMonth, 5, 2);

/*
|--------------------------------------------------------------------------
| Date Range - SELECTED MONTH ONLY
|--------------------------------------------------------------------------
*/

$yearStart = $selectedMonth . "-01";
$yearEnd = date("Y-m-t", strtotime($selectedMonth . "-01"));

/*
|--------------------------------------------------------------------------
| Money Helper
|--------------------------------------------------------------------------
*/

$money = static function ($value): float {
    $value = str_replace(",", "", (string) $value);

    return is_numeric($value) ? (float) $value : 0.0;
};

/*
|--------------------------------------------------------------------------
| Owner Account Names
|--------------------------------------------------------------------------
*/

$ownerAccountNames = [];

$ownerNamesResult = mysqli_query(
    $conn,
    "SELECT account_holder_id, account_name FROM account_holders"
);

if ($ownerNamesResult) {
    while ($row = mysqli_fetch_assoc($ownerNamesResult)) {
        $ownerAccountNames[(string) $row["account_holder_id"]] =
            $row["account_name"];
    }
}

/*
|--------------------------------------------------------------------------
| Get Reports - SELECTED MONTH ONLY
|--------------------------------------------------------------------------
*/

$reports = [];

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        report_date,
        pos_sales_total,
        telegram_declared_total,
        notes
    FROM daily_reports
    WHERE report_date BETWEEN ? AND ?
      AND status <> 'voided'
    ORDER BY report_date, report_id
    "
);

mysqli_stmt_bind_param($stmt, "ss", $yearStart, $yearEnd);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {

    $row["payload"] = json_decode($row["notes"] ?? "", true);

    $row["payload"] = is_array($row["payload"])
        ? $row["payload"]
        : [];

    $reports[] = $row;
}

mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| Only Selected Month
|--------------------------------------------------------------------------
*/

$months = [$selectedMonthNumber];

$blank = array_fill_keys($months, 0.0);

/*
|--------------------------------------------------------------------------
| Rows
|--------------------------------------------------------------------------
*/

$rows = [

    "gross" => $blank,
    "pos" => $blank,
    "nonPos" => $blank,
    "add" => $blank,
    "grabGross" => $blank,
    "grab" => $blank,

    "kitchenSale" => $blank,
    "barSale" => $blank,
    "corkage" => $blank,
    "serviceCharge" => $blank,
    "giftCheckSale" => $blank,
    "otherProducts" => $blank,

    "unpaid" => $blank,
    "marketing" => $blank,

    "cashRemitted" => $blank,
    "cards" => $blank,
    "bpi" => $blank,
    "easwest" => $blank,
    "giftcheck" => $blank,
    "cheque" => $blank,
];

$owners = [];

$dailyGross = [];

/*
|--------------------------------------------------------------------------
| Payment Fields
|--------------------------------------------------------------------------
*/

$cardFields = [
    "gcashQrph",
    "paymaya",
    "amex",
    "visa",
    "mastercard",
    "bancnet",
    "jcb"
];

$otherPaymentFields = [
    "otherCash",
    "otherMayaTerminal",
    "otherBpiNooma",
    "otherEastwestNooma",
    "otherGiftCheck",
    "otherCheques"
];

$marketingFields = [
    "marketingExpensesF",
    "djRonald",
    "ejVelez",
    "guestDJ",
    "marketingOthers",
    "djsTalentFee",
    "bouncersFee"
];

/*
|--------------------------------------------------------------------------
| Process Reports
|--------------------------------------------------------------------------
*/

foreach ($reports as $report) {

    $payload = $report["payload"];

    $month = (int) date(
        "n",
        strtotime($report["report_date"])
    );

    /*
    |--------------------------------------------------------------------------
    | Card Payments
    |--------------------------------------------------------------------------
    */

    $cards = 0.0;

    foreach ($cardFields as $field) {
        $cards += $money($payload[$field] ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Owner Accounts
    |--------------------------------------------------------------------------
    */

    $ownerTotal = 0.0;

    foreach (($payload["ownerAccounts"] ?? []) as $accountId => $amount) {

        $amount = $money($amount);

        $ownerTotal += $amount;

        $name =
            $ownerAccountNames[(string) $accountId]
            ?? ("Owner account #" . $accountId);

        if (!isset($owners[$name])) {
            $owners[$name] = $blank;
        }

        $owners[$name][$month] += $amount;
    }

    /*
    |--------------------------------------------------------------------------
    | Unpaid Accounts
    |--------------------------------------------------------------------------
    */

    $unpaid = $money(
        $payload["unpaidAccountAmount"] ?? 0
    );

    foreach ($payload as $field => $value) {

        if (
            preg_match(
                '/^unpaidAccountAmount_\d+$/',
                (string) $field
            )
        ) {
            $unpaid += $money($value);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Other Payments
    |--------------------------------------------------------------------------
    */

    $otherPayments = 0.0;

    foreach ($otherPaymentFields as $field) {
        $otherPayments += $money(
            $payload[$field] ?? 0
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Marketing
    |--------------------------------------------------------------------------
    */

    $marketing = $money(
        $payload["totalMarketingExpenses"] ?? 0
    );

    if ($marketing == 0.0) {

        foreach ($marketingFields as $field) {

            $marketing += $money(
                $payload[$field] ?? 0
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Collection
    |--------------------------------------------------------------------------
    */

    $collection =
        $money($payload["cashRemitted"] ?? 0)
        + $cards
        + $money($payload["bpi"] ?? 0)
        + $money($payload["easwest"] ?? 0)
        + $money($payload["giftcheck"] ?? 0)
        + $money($payload["cheque"] ?? 0)
        + $ownerTotal
        + $unpaid
        + $otherPayments;

    /*
    |--------------------------------------------------------------------------
    | Grab Sales
    |--------------------------------------------------------------------------
    */

    $grab =
        isset($payload["onlineTips"])
        && $payload["onlineTips"] !== ""

        ? $money($payload["onlineTips"])

        : round(
            $money($payload["grabSaleGross"] ?? 0) * 0.73,
            2
        );

    $grabGross = $money($payload["grabSaleGross"] ?? 0);
    /*
    |--------------------------------------------------------------------------
    | Sales Channel
    |--------------------------------------------------------------------------
    */

    $savedChannel = strtoupper(
        trim((string) ($payload["saleChannel"] ?? ""))
    );

    $isNonPos =
        $savedChannel === "NON POS"
        ||
        (
            $savedChannel === ""
            &&
            (float) $report["pos_sales_total"] <= 0
        );

    /*
    |--------------------------------------------------------------------------
    | Non-POS Fallback
    |--------------------------------------------------------------------------
    */

    if ($isNonPos && $collection == 0.0) {

        $collection = $money(
            $report["telegram_declared_total"] ?? 0
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Sales Breakdown
    |--------------------------------------------------------------------------
    */

    if ($isNonPos) {

        $rows["nonPos"][$month] += $collection;

    } else {

        $rows["pos"][$month] += $collection;
    }

    /*
    |--------------------------------------------------------------------------
    | Gross Sales
    |--------------------------------------------------------------------------
    */

    $rows["grabGross"][$month] += $grabGross;
    $rows["grab"][$month] += $grab;

    $rows["gross"][$month] +=
        $collection + $grabGross;

    /*
    |--------------------------------------------------------------------------
    | Sales Components
    |--------------------------------------------------------------------------
    */

    foreach (
        [
            "kitchenSale",
            "barSale",
            "corkage",
            "serviceCharge",
            "giftCheckSale",
            "otherProducts",
            "cashRemitted",
            "bpi",
            "easwest",
            "giftcheck",
            "cheque"
        ] as $field
    ) {

        $rows[$field][$month] +=
            $money($payload[$field] ?? 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Cards
    |--------------------------------------------------------------------------
    */

    $rows["cards"][$month] += $cards;

    /*
    |--------------------------------------------------------------------------
    | Unpaid
    |--------------------------------------------------------------------------
    */

    $rows["unpaid"][$month] += $unpaid;

    /*
    |--------------------------------------------------------------------------
    | Marketing
    |--------------------------------------------------------------------------
    */

    $rows["marketing"][$month] += $marketing;

    /*
    |--------------------------------------------------------------------------
    | Daily Gross
    |--------------------------------------------------------------------------
    */

    $dailyGross[$report["report_date"]] =
        ($dailyGross[$report["report_date"]] ?? 0.0)
        + $collection
        + $grabGross;
}

/*
|--------------------------------------------------------------------------
| Sort Owner Accounts
|--------------------------------------------------------------------------
*/

ksort($owners);

/*
|--------------------------------------------------------------------------
| Weekday Calculation
|--------------------------------------------------------------------------
*/

$weekdayNames = [
    1 => "MON",
    2 => "TUE",
    3 => "WED",
    4 => "THU",
    5 => "FRI",
    6 => "SAT",
    7 => "SUN"
];

$weekdayTotals = array_fill_keys(
    array_keys($weekdayNames),
    0.0
);

$weekdayDays = array_fill_keys(
    array_keys($weekdayNames),
    0
);

foreach ($dailyGross as $date => $gross) {

    $weekday = (int) date(
        "N",
        strtotime($date)
    );

    $weekdayTotals[$weekday] += $gross;

    $weekdayDays[$weekday]++;
}

/*
|--------------------------------------------------------------------------
| Formatting Functions
|--------------------------------------------------------------------------
*/

function cell(float $value): string
{
    return abs($value) < 0.005
        ? "-"
        : number_format(
            $value,
            2,
            ".",
            ""
        );
}

function monthRow(
    string $label,
    array $values,
    string $labelClass = "label"
): string {

    $html =
        '<tr>
            <td class="number total">'
        . cell(array_sum($values))
        . '</td>';

    $html .=
        '<td class="' .
        htmlspecialchars($labelClass) .
        '">'
        . htmlspecialchars($label)
        . '</td>';

    foreach ($values as $value) {

        $html .=
            '<td class="number">'
            . cell($value)
            . '</td>';
    }

    $html .= "</tr>";

    return $html;
}

/*
|--------------------------------------------------------------------------
| Excel Download
|--------------------------------------------------------------------------
*/

$monthName = strtoupper(
    date(
        "F",
        strtotime($selectedMonth . "-01")
    )
);

header(
    "Content-Type: application/vnd.ms-excel; charset=utf-8"
);

header(
    "Content-Disposition: attachment; filename=monthly_report_"
    . $selectedMonth
    . ".xls"
);

?>

<style>

body {
    font-family: Arial, sans-serif;
    color: #111;
}

table {
    border-collapse: collapse;
}

td,
th {
    padding: 4px 8px;
    font-size: 11px;
    white-space: nowrap;
}

.header th {
    background: #1f3b57;
    color: #fff;
    font-weight: bold;
}

.gross td {
    font-weight: bold;
    border-bottom: 2px solid #111;
}

.section {
    font-weight: bold;
    padding-top: 12px;
}

.channel {
    background: #e5e7eb;
    font-weight: bold;
}

.label {
    font-weight: bold;
}

.indent {
    padding-left: 24px;
}

.total {
    font-weight: bold;
}

.number {
    text-align: right;
    mso-number-format: "#,##0.00";
}

</style>

<table>

    <!-- HEADER -->

    <tr class="header" style="width: 100px;">

        <th>
            <?= $year ?>
        </th>

        <th>
            MONTH
        </th>

        <?php foreach ($months as $month): ?>

            <th>
                <?= sprintf("[%02d]", $month) ?>
                <?= strtoupper(
                    date(
                        "M",
                        mktime(
                            0,
                            0,
                            0,
                            $month,
                            1,
                            $year
                        )
                    )
                ) ?>
            </th>

        <?php endforeach; ?>

    </tr>

    <!-- TOTAL GROSS SALES -->

    <tr class="gross">

        <td class="number">
            <?= cell(array_sum($rows["gross"])) ?>
        </td>

        <td>
            TOTAL GROSS SALES
        </td>

        <?php foreach ($months as $month): ?>

            <td class="number">
                <?= cell($rows["gross"][$month]) ?>
            </td>

        <?php endforeach; ?>

    </tr>

    <!-- BREAKDOWN -->

    <tr>
        <td colspan="3" class="section">
            BREAKDOWN:
        </td>
    </tr>

    <?= monthRow(
        "POS SALES",
        $rows["pos"],
        "channel"
    ) ?>

    <?= monthRow(
        "NON POS SALES",
        $rows["nonPos"],
        "channel"
    ) ?>

    <?= monthRow(
        "GRAB GROSS SALES",
        $rows["grabGross"],
        "channel"
    ) ?>
    <?= monthRow(
        "GRAB SALES",
        $rows["grab"],
        "channel"
    ) ?>

    <tr>
        <td colspan="3"></td>
    </tr>

    <!-- SALES -->

    <?= monthRow(
        "Kitchen Sales",
        $rows["kitchenSale"]
    ) ?>

    <?= monthRow(
        "Bar Sales",
        $rows["barSale"]
    ) ?>

    <?= monthRow(
        "Corkage",
        $rows["corkage"]
    ) ?>

    <?= monthRow(
        "Service Charge",
        $rows["serviceCharge"]
    ) ?>

    <?= monthRow(
        "Gift Check Sales",
        $rows["giftCheckSale"]
    ) ?>

    <?= monthRow(
        "Other Products",
        $rows["otherProducts"]
    ) ?>

    <!-- OWNER ACCOUNTS -->

    <tr>
        <td></td>
        <td colspan="2" class="section">
            OWNER'S ACCT
        </td>
    </tr>

    <?php foreach ($owners as $name => $values): ?>

        <?= monthRow(
            $name,
            $values,
            "indent"
        ) ?>

    <?php endforeach; ?>

    <tr>
        <td colspan="3"></td>
    </tr>

    <!-- UNPAID -->

    <?= monthRow(
        "UNPAID ACCTS",
        $rows["unpaid"]
    ) ?>

    <tr>
        <td colspan="3"></td>
    </tr>

    <!-- MARKETING -->

    <?= monthRow(
        "ADS AND MRKTG",
        $rows["marketing"]
    ) ?>

    <tr>
        <td colspan="3"></td>
    </tr>

    <!-- PAYMENTS -->

    <?= monthRow(
        "CASH REMITTED",
        $rows["cashRemitted"]
    ) ?>

    <?= monthRow(
        "CARD PAYMENTS",
        $rows["cards"]
    ) ?>

    <?= monthRow(
        "DIRECT BT BPI NOOMA",
        $rows["bpi"]
    ) ?>

    <?= monthRow(
        "DIRECT BT EASTWEST NOOMA",
        $rows["easwest"]
    ) ?>

    <?= monthRow(
        "NOOMA GIFT CHECK",
        $rows["giftcheck"]
    ) ?>

    <?= monthRow(
        "CHEQUE PAYMENT",
        $rows["cheque"]
    ) ?>

    <tr>
        <td colspan="3"></td>
    </tr>

    <!-- WEEKDAY -->

    <tr>
        <td style="width: 56px;"></td>
        <td class="label">WEEKDAYS AVERAGE SALES</td>
        <?php foreach ($weekdayNames as $weekday => $name): ?>
            <?php if ($weekday <= 4): ?>
                <td class="label" style="text-align: center;"><?= $name ?></td>
            <?php else: ?>
                <td></td>
            <?php endif; ?>
        <?php endforeach; ?>
    </tr>
    <tr>
        <td style="width: 56px;"></td>
        <td class="label"></td>
        <?php foreach ($weekdayNames as $weekday => $name): ?>
            <td class="number">
                <?= $weekday <= 4
                    ? cell($weekdayDays[$weekday] > 0
                        ? $weekdayTotals[$weekday] / $weekdayDays[$weekday]
                        : 0.0)
                    : "" ?>
            </td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <td style="width: 56px;"></td>
        <td class="label"></td>
        <?php foreach ([5, 6, 7] as $weekday): ?>
            <td class="label" style="text-align: center;"><?= $weekdayNames[$weekday] ?></td>
        <?php endforeach; ?>
        <?php for ($weekday = 0; $weekday < 4; $weekday++): ?>
            <td></td>
        <?php endfor; ?>
    </tr>
    <tr>
        <td style="width: 56px;"></td>
        <td class="label">AVERAGE SALES</td>
        <?php foreach ([5, 6, 7] as $weekday): ?>
            <td class="number">
                <?= cell($weekdayDays[$weekday] > 0
                        ? $weekdayTotals[$weekday] / $weekdayDays[$weekday]
                        : 0.0) ?>
            </td>
        <?php endforeach; ?>
        <?php for ($weekday = 0; $weekday < 4; $weekday++): ?>
            <td></td>
        <?php endfor; ?>
    </tr>

</table>    