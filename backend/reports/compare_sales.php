<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../../frontend/Login/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../frontend/Inventory/inventory.php");
    exit;
}

require_once __DIR__ . "/../config/database.php";

$redirect = "../../frontend/Inventory/inventory.php";
$grandTotalExpression = "CASE WHEN JSON_VALID(dr.notes) THEN COALESCE(
            CAST(JSON_UNQUOTE(JSON_EXTRACT(dr.notes, '$.grandTotal')) AS DECIMAL(12,2)),
            dr.telegram_declared_total
        ) ELSE dr.telegram_declared_total END";
$totalDiscountExpression = "CASE WHEN JSON_VALID(dr.notes) THEN COALESCE(
            CAST(JSON_UNQUOTE(JSON_EXTRACT(dr.notes, '$.totalSalesDeduction')) AS DECIMAL(12,2)),
            0
        ) ELSE 0 END";
$serviceChargeExpression = "CASE WHEN JSON_VALID(dr.notes) THEN COALESCE(
            CAST(JSON_UNQUOTE(JSON_EXTRACT(dr.notes, '$.serviceCharge')) AS DECIMAL(12,2)),
            service_charges.service_charge_total,
            0
        ) ELSE COALESCE(service_charges.service_charge_total, 0) END";
$serviceChargeJoin = "LEFT JOIN (
        SELECT report_id, SUM(amount) AS service_charge_total
        FROM report_sales
        WHERE sales_type = 'service_charge'
        GROUP BY report_id
    ) service_charges ON service_charges.report_id = dr.report_id";
$metricDefinitions = [
    "grand_total" => ["label" => "Excel Total Sale / Nooma Grand Total", "expression" => $grandTotalExpression],
    "total_discount" => ["label" => "Total Discount", "expression" => $totalDiscountExpression],
    "service_charge" => ["label" => "Service Charge", "expression" => $serviceChargeExpression]
];
$appendComparisons = static function ($date, $dateTo, $excelTotals, $noomaTotals) use ($metricDefinitions): array {
    $comparisons = [];
    foreach ($metricDefinitions as $field => $definition) {
        $excelTotal = $excelTotals[$field] ?? null;
        $noomaTotal = $noomaTotals === null ? null : (float) $noomaTotals[$field];
        $difference = $excelTotal === null || $noomaTotal === null
            ? null
            : round((float) $excelTotal - $noomaTotal, 2);
        $comparisons[] = [
            "date" => $date,
            "date_to" => $dateTo,
            "metric" => $definition["label"],
            "excel_total" => $excelTotal === null ? null : (float) $excelTotal,
            "nooma_total" => $noomaTotal,
            "difference" => $difference,
            "status" => $excelTotal === null
                ? "Not in Excel"
                : ($noomaTotal === null ? "No Nooma report" : (abs($difference) < 0.01 ? "Matched" : "Difference"))
        ];
    }
    return $comparisons;
};
$dailyTotals = json_decode((string) ($_POST["daily_totals_json"] ?? ""), true);

if (!is_array($dailyTotals) || !$dailyTotals) {
    $_SESSION["sales_comparison_error"] = "No sales rows were found in the Excel file.";
    header("Location: {$redirect}");
    exit;
}

$periodRows = array_values(array_filter(
    $dailyTotals,
    static fn($row) => is_array($row) && isset($row["start_date"], $row["end_date"])
));
if ($periodRows) {
    $period = $periodRows[0];
    $startDate = (string) $period["start_date"];
    $endDate = (string) $period["end_date"];
    $startDateObject = DateTime::createFromFormat("!Y-m-d", $startDate);
    $endDateObject = DateTime::createFromFormat("!Y-m-d", $endDate);
    $hasValidAmounts = true;
    foreach (array_keys($metricDefinitions) as $field) {
        $amount = $period[$field] ?? null;
        if (($field === "grand_total" && !is_numeric($amount))
            || ($amount !== null && (!is_numeric($amount) || !is_finite((float) $amount)))) {
            $hasValidAmounts = false;
            break;
        }
    }

    if (count($periodRows) !== 1 || count($dailyTotals) !== 1
        || !$startDateObject || $startDateObject->format("Y-m-d") !== $startDate
        || !$endDateObject || $endDateObject->format("Y-m-d") !== $endDate
        || $startDate > $endDate || !$hasValidAmounts) {
        $_SESSION["sales_comparison_error"] = "The Excel report range or comparison totals are invalid.";
        header("Location: {$redirect}");
        exit;
    }

    $statement = mysqli_prepare($conn, "SELECT
            SUM({$grandTotalExpression}) AS grand_total,
            SUM({$totalDiscountExpression}) AS total_discount,
            SUM({$serviceChargeExpression}) AS service_charge,
            COUNT(*) AS report_count
        FROM daily_reports dr
        {$serviceChargeJoin}
        WHERE dr.status <> 'voided' AND dr.report_date BETWEEN ? AND ?");
    if (!$statement) {
        error_log("Sales comparison range query preparation failed: " . mysqli_error($conn));
        $_SESSION["sales_comparison_error"] = "Could not load Nooma comparison values. Please try again.";
        header("Location: {$redirect}");
        exit;
    }

    mysqli_stmt_bind_param($statement, "ss", $startDate, $endDate);
    mysqli_stmt_execute($statement);
    $rangeResult = mysqli_stmt_get_result($statement);
    $rangeRow = mysqli_fetch_assoc($rangeResult);
    mysqli_stmt_close($statement);

    $hasNoomaTotal = (int) ($rangeRow["report_count"] ?? 0) > 0;
    $excelTotals = array_intersect_key($period, $metricDefinitions);
    $noomaTotals = $hasNoomaTotal ? array_intersect_key($rangeRow, $metricDefinitions) : null;
    $_SESSION["sales_comparison_results"] = $appendComparisons($startDate, $endDate, $excelTotals, $noomaTotals);
    header("Location: {$redirect}");
    exit;
}

$validatedTotals = [];
foreach ($dailyTotals as $row) {
    if (!is_array($row) || !isset($row["date"])) {
        continue;
    }

    $date = (string) $row["date"];
    $dateObject = DateTime::createFromFormat("!Y-m-d", $date);
    if (!$dateObject || $dateObject->format("Y-m-d") !== $date) {
        continue;
    }

    $amounts = [];
    foreach (array_keys($metricDefinitions) as $field) {
        $amount = $row[$field] ?? null;
        if (($field === "grand_total" && !is_numeric($amount))
            || ($amount !== null && (!is_numeric($amount) || !is_finite((float) $amount)))) {
            continue 2;
        }
        $amounts[$field] = $amount === null ? null : (float) $amount;
    }

    if (!$amounts) {
        continue;
    }

    if (!isset($validatedTotals[$date])) {
        $validatedTotals[$date] = array_fill_keys(array_keys($metricDefinitions), null);
    }
    foreach ($amounts as $field => $amount) {
        if ($amount !== null) {
            $validatedTotals[$date][$field] = ($validatedTotals[$date][$field] ?? 0.0) + $amount;
        }
    }
}

if (!$validatedTotals) {
    $_SESSION["sales_comparison_error"] = "The Excel file did not contain valid dates and Total Sales values.";
    header("Location: {$redirect}");
    exit;
}

ksort($validatedTotals);
$dates = array_keys($validatedTotals);
$placeholders = implode(",", array_fill(0, count($dates), "?"));
$sql = "SELECT
        dr.report_date,
        SUM({$grandTotalExpression}) AS grand_total,
        SUM({$totalDiscountExpression}) AS total_discount,
        SUM({$serviceChargeExpression}) AS service_charge
    FROM daily_reports dr
    {$serviceChargeJoin}
    WHERE dr.status <> 'voided' AND dr.report_date IN ({$placeholders})
    GROUP BY dr.report_date";
$statement = mysqli_prepare($conn, $sql);

if (!$statement) {
    error_log("Sales comparison query preparation failed: " . mysqli_error($conn));
    $_SESSION["sales_comparison_error"] = "Could not load Nooma comparison values. Please try again.";
    header("Location: {$redirect}");
    exit;
}

$types = str_repeat("s", count($dates));
$bindValues = [$types];
foreach ($dates as $index => $date) {
    $bindValues[] = &$dates[$index];
}
call_user_func_array([$statement, "bind_param"], $bindValues);
mysqli_stmt_execute($statement);
$result = mysqli_stmt_get_result($statement);
$noomaTotals = [];
while ($row = mysqli_fetch_assoc($result)) {
    $noomaTotals[$row["report_date"]] = array_intersect_key($row, $metricDefinitions);
}
mysqli_stmt_close($statement);

$comparison = [];
foreach ($validatedTotals as $date => $excelTotal) {
    $hasNoomaTotal = array_key_exists($date, $noomaTotals);
    $comparison = array_merge(
        $comparison,
        $appendComparisons($date, null, $excelTotal, $hasNoomaTotal ? $noomaTotals[$date] : null)
    );
}

$_SESSION["sales_comparison_results"] = $comparison;
header("Location: {$redirect}");
exit;