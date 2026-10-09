<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../Login/login.php");
    exit;
}

require_once __DIR__ . "/../../backend/config/database.php";

$start_date = $_GET["start_date"] ?? date("Y-m-01");
$end_date = $_GET["end_date"] ?? date("Y-m-t");
$additionalTipAmount = isset($_GET["additional_tip_amount"]) ? (float) $_GET["additional_tip_amount"] : 0.0;

// Fetch service charge total for the period
$statement = mysqli_prepare($conn, "
    SELECT 
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
    WHERE dr.status <> 'voided' AND dr.report_date BETWEEN ? AND ?
");
mysqli_stmt_bind_param($statement, "ss", $start_date, $end_date);
mysqli_stmt_execute($statement);
$result = mysqli_stmt_get_result($statement);

$totalServiceCharge = 0.0;
while ($row = mysqli_fetch_assoc($result)) {
    $totalServiceCharge += (float) $row["service_charge"];
}
mysqli_stmt_close($statement);

$serviceChargeBonus = round($totalServiceCharge * 0.50, 2);
$totalTipPool = round($serviceChargeBonus + $additionalTipAmount, 2);

// Fetch employees and their days of duty
$employeeResult = mysqli_query($conn, "
    SELECT user_id, full_name, role, COALESCE(number_of_days, 0) as number_of_days
    FROM users
    WHERE is_active = 1 AND role = 'employee'
");

$employees = [];
$totalDays = 0;
while ($emp = mysqli_fetch_assoc($employeeResult)) {
    $days = (int) $emp["number_of_days"];
    $totalDays += $days;
    
    // Parse last name and first name for sorting and display
    $nameParts = explode(" ", trim($emp["full_name"]));
    $lastName = array_pop($nameParts);
    $firstName = implode(" ", $nameParts);
    
    $emp["last_name"] = $lastName;
    $emp["first_name"] = $firstName;
    
    $employees[] = $emp;
}

// Sort employees alphabetically by Last Name
usort($employees, function($a, $b) {
    return strcasecmp($a["last_name"], $b["last_name"]);
});

foreach ($employees as &$emp) {
    $days = (int) $emp["number_of_days"];
    $sharePercent = $totalDays > 0 ? ($days / $totalDays) * 100 : 0;
    $tipAmount = $totalDays > 0 ? round($totalTipPool * ($days / $totalDays), 2) : 0.0;
    
    $emp["share_percent"] = $sharePercent;
    $emp["tip_amount"] = $tipAmount;
    $emp["rate"] = $days > 0 ? ($tipAmount / $days) : 0.0;
}
unset($emp);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SC & Tip Distribution Master Sheet & Slips (Landscape)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fff;
            color: #000;
            margin: 0;
            padding: 10px;
        }
        .no-print {
            text-align: right;
            margin-bottom: 15px;
        }
        .print-btn {
            padding: 10px 20px;
            background: #000;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
            border-radius: 4px;
        }
        
        /* Summary Table Styles (First Page) */
        .page-break {
            page-break-after: always;
            break-after: page;
        }
        .summary-container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
        }
        .summary-header {
            text-align: center;
            font-weight: bold;
            font-size: 15px;
            margin-bottom: 5px;
        }
        .summary-title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            text-align: center;
            font-weight: bold;
        }
        .summary-table th {
            background-color: #f2f2f2;
            font-weight: normal;
        }
        .summary-table .column-header th {
           background-color: #e09b76; /* Medium-dark orange */
            color: #ffffff;
        }
        .text-left {
            text-align: left !important;
        }
        .text-right {
            text-align: right !important;
        }

        /* Slip Grid Styles (3 columns for Landscape) */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            max-width: 100%;
        }
        .slip {
            border: 2px solid #000;
            padding: 4px;
            box-sizing: border-box;
            background: #fff;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .slip-inner {
            border: 1px solid #000;
            padding: 8px 10px;
            box-sizing: border-box;
            font-weight: bold;
        }
        .company-header {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 2px;
        }
        .slip-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 6px;
        }
        .row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
            font-size: 10px;
        }
        .name-row {
            display: flex;
            justify-content: space-between;
            margin-top: 6px;
            margin-bottom: 5px;
            font-size: 10px;
            align-items: flex-start;
        }
        .name-col {
            display: flex;
            flex-direction: column;
        }
        .name-col label {
            font-size: 8px;
            color: #555;
            margin-bottom: 2px;
        }
        .name-col span {
            font-weight: bold;
            font-size: 10px;
        }
        .box-section {
            border: 1px solid #000;
            min-height: 30px;
            margin: 6px 0;
            padding: 4px;
        }
        .box-label {
            font-size: 8px;
            font-weight: bold;
        }
        .amount-line {
            text-align: right;
            font-weight: bold;
            font-size: 11px;
        }
        .footer-note {
            font-size: 7px;
            margin-top: 6px;
        }

        @media print {
            body { padding: 0; }
            .no-print { display: none; }
            .summary-table .column-header th {
                background-color: #e09b76 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .summary-table .bonus-cell {
                background-color: #aadf98 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .summary-table .bonus-amount-cell {
                background-color: #96b3ec !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page {
                size: landscape;
                margin: 8mm;
            }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="print-btn" onclick="window.print()">Print / Save as PDF (Landscape)</button>
</div>

<!-- ================= FIRST PAGE: SUMMARY / MASTER SHEET ================= -->
<div class="summary-container page-break">
    <div class="company-header summary-header">Triple Prime Ventures Group Corp</div>
    <div class="summary-title">SC & TIP DISTRIBUTION SUMMARY SHEET</div>
    
    <table class="summary-table">
        <thead style="font-weight: bold;">
            <!-- Top Header Row -->
            <tr>
                <th colspan="2">15 DAYS</th>
                <th class="bonus-cell" style="background-color: #aadf98;">BONUS: 50%</th>
                <th class="bonus-amount-cell" style="background-color: #96b3ec;"><?= number_format($serviceChargeBonus, 2) ?></th>
                <th colspan="3"></th>
                <th><?= number_format($serviceChargeBonus, 2) ?></th>
                <th>DEDUCTIONS</th>
                <th>REASON</th>
                <th>NET PAY</th>
                <th>TOTAL</th>
            </tr>
            <!-- Second Header Row -->
            <tr>
                <th colspan="2"></th>
                <th class="bonus-cell" style="background-color: #aadf98;">TIP:</th>
                <th class="bonus-amount-cell" style="background-color: #96b3ec;"><?= number_format($additionalTipAmount, 2) ?></th>
                <th colspan="3">TIP:</th>
                <th><?= number_format($additionalTipAmount, 2) ?></th>
                <th colspan="4"></th>
            </tr>
            <!-- Third Header Row -->
            <tr>
                <th style="width: 16%;">LIST OF EMPLOYEES</th>
                <th style="width: 16%;"></th>
                <th style="width: 10%;">DAYS OF DUTY</th>
                <th style="width: 10%;">RATE</th>
                <th style="width: 8%;">DAYS OF DUTY</th>
                <th style="width: 8%;"></th>
                <th style="width: 6%;"></th>
                <th style="width: 8%;"></th>
                <th style="width: 6%;"></th>
                <th style="width: 6%;"></th>
                <th style="width: 6%;"></th>
                <th style="width: 6%;"></th>
            </tr>
            <!-- Fourth Header Row (Column Names) -->
            <tr class="column-header">
                <th class="text-left" style="font-weight: bold; color: #000;">Last Name</th>
                <th class="text-left" style="font-weight: bold; color: #000;">First Name</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $grandTotalDays = 0;
            $grandTotalPay = 0.0;
            foreach ($employees as $emp): 
                $grandTotalDays += (int)$emp["number_of_days"];
                $grandTotalPay += (float)$emp["tip_amount"];
            ?>
                <tr>
                    <td class="text-left"><?= strtoupper($emp["last_name"]) ?></td>
                    <td class="text-left"><?= strtoupper($emp["first_name"]) ?></td>
                    <td><?= (int)$emp["number_of_days"] ?></td>
                    <td class="text-right"><?= number_format($emp["tip_amount"], 2) ?></td>
                    <td><?= (int)$emp["number_of_days"] ?></td>
                    <td></td>
                    <td></td>
                    <td class="text-right"><?= number_format($emp["tip_amount"], 2) ?></td>
                    <td></td>
                    <td></td>
                    <td class="text-right"><?= number_format($emp["tip_amount"], 2) ?></td>
                    <td class="text-right"><?= number_format($emp["tip_amount"], 2) ?></td> 
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr style="background-color: #f9f9f9; font-weight: bold;">
                <td colspan="2" class="text-left">TOTAL DAYS OF DUTY</td>
                <td><?= $grandTotalDays ?></td>
                <td></td>
                <td><?= $grandTotalDays ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="text-right"><?= number_format($grandTotalPay, 2) ?></td>
                <td class="text-right"><?= number_format($grandTotalPay, 2) ?></td>
            </tr>
        </tfoot>
    </table>
</div>

<!-- ================= SUBSEQUENT PAGES: DISTRIBUTION SLIPS (3 columns wide) ================= -->
<div class="grid-container">
    <?php $index = 1; foreach ($employees as $emp): ?>
        <div class="slip">
            <div class="slip-inner">
                <div class="company-header">Triple Prime Ventures Group Corp</div>
                <div class="slip-title">SC & TIP DISTRIBUTION</div>
                
                <div class="row">
                    <div><strong>PERIOD:</strong> <?= date("M. d", strtotime($start_date)) ?> - <?= date("d, Y", strtotime($end_date)) ?></div>
                    <div><strong>DATE:</strong> <?= date("m-d-Y") ?></div>
                </div>
                
                <div class="row">
                    <div><strong>List No:</strong> <?= $index++ ?></div>
                </div>

                <div class="name-row">
                    <div class="name-col" style="flex: 1;">
                        <label>Last Name</label>
                        <span><?= strtoupper($emp["last_name"]) ?></span>
                    </div>
                    <div class="name-col" style="flex: 1;">
                        <label>First Name</label>
                        <span><?= strtoupper($emp["first_name"]) ?></span>
                    </div>
                    <div class="name-col" style="flex: 0.6; text-align: right;">
                        <label>DAYS</label>
                        <span><?= (int)$emp["number_of_days"] ?></span>
                    </div>
                </div>

                <div style="border-top: 1px solid #000; margin-top: 6px; padding-top: 4px;">
                    <div class="row">
                        <span><strong>SC & TIP</strong></span>
                        <span class="amount-line"><?= number_format($emp["tip_amount"], 2) ?></span>
                    </div>
                </div>

                <div class="box-section">
                    <div class="box-label">DEDUCTIONS / NOTE</div>
                </div>

                <div class="row" style="margin-top: 2px;">
                    <span style="font-size: 11px;"><strong>NET PAY:</strong></span>
                    <span class="amount-line" style="border-bottom: 1px solid #000; min-width: 80px;"><?= number_format($emp["tip_amount"], 2) ?></span>
                </div>

                <div class="footer-note">
                    Take up with office immediately. <strong>CONFIDENTIAL!</strong>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>