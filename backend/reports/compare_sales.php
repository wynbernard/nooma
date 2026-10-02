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
$totalSaleExpression = "CASE WHEN JSON_VALID(notes) THEN COALESCE(
            CAST(JSON_UNQUOTE(JSON_EXTRACT(notes, '$.totalSale')) AS DECIMAL(12,2)),
            pos_sales_total
        ) ELSE pos_sales_total END";
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
    $amount = $period["amount"] ?? null;

    if (count($periodRows) !== 1 || count($dailyTotals) !== 1
        || !$startDateObject || $startDateObject->format("Y-m-d") !== $startDate
        || !$endDateObject || $endDateObject->format("Y-m-d") !== $endDate
        || $startDate > $endDate || !is_numeric($amount) || !is_finite((float) $amount)) {
        $_SESSION["sales_comparison_error"] = "The Excel report range or sales total is invalid.";
        header("Location: {$redirect}");
        exit;
    }

    $statement = mysqli_prepare($conn, "SELECT SUM({$totalSaleExpression}) AS total_sale, COUNT(*) AS report_count
        FROM daily_reports
        WHERE status <> 'voided' AND report_date BETWEEN ? AND ?");
    if (!$statement) {
        error_log("Total Sale comparison range query preparation failed: " . mysqli_error($conn));
        $_SESSION["sales_comparison_error"] = "Could not load Nooma Total Sale values. Please try again.";
        header("Location: {$redirect}");
        exit;
    }

    mysqli_stmt_bind_param($statement, "ss", $startDate, $endDate);
    mysqli_stmt_execute($statement);
    $rangeResult = mysqli_stmt_get_result($statement);
    $rangeRow = mysqli_fetch_assoc($rangeResult);
    mysqli_stmt_close($statement);

    $hasNoomaTotal = (int) ($rangeRow["report_count"] ?? 0) > 0;
    $excelTotal = (float) $amount;
    $noomaTotal = $hasNoomaTotal ? (float) $rangeRow["total_sale"] : null;
    $difference = $hasNoomaTotal ? round($excelTotal - $noomaTotal, 2) : null;
    $_SESSION["sales_comparison_results"] = [[
        "date" => $startDate,
        "date_to" => $endDate,
        "excel_total" => $excelTotal,
        "nooma_total" => $noomaTotal,
        "difference" => $difference,
        "status" => !$hasNoomaTotal ? "No Nooma report" : (abs($difference) < 0.01 ? "Matched" : "Difference")
    ]];
    header("Location: {$redirect}");
    exit;
}

$validatedTotals = [];
foreach ($dailyTotals as $row) {
    if (!is_array($row) || !isset($row["date"], $row["amount"])) {
        continue;
    }

    $date = (string) $row["date"];
    $dateObject = DateTime::createFromFormat("!Y-m-d", $date);
    if (!$dateObject || $dateObject->format("Y-m-d") !== $date || !is_numeric($row["amount"])) {
        continue;
    }

    $amount = (float) $row["amount"];
    if (!is_finite($amount)) {
        continue;
    }

    $validatedTotals[$date] = ($validatedTotals[$date] ?? 0.0) + $amount;
}

if (!$validatedTotals) {
    $_SESSION["sales_comparison_error"] = "The Excel file did not contain valid date and sales amount values.";
    header("Location: {$redirect}");
    exit;
}

ksort($validatedTotals);
$dates = array_keys($validatedTotals);
$placeholders = implode(",", array_fill(0, count($dates), "?"));
$sql = "SELECT report_date, SUM({$totalSaleExpression}) AS total_sale
        FROM daily_reports
        WHERE status <> 'voided' AND report_date IN ({$placeholders})
        GROUP BY report_date";
$statement = mysqli_prepare($conn, $sql);

if (!$statement) {
    error_log("Total Sale comparison query preparation failed: " . mysqli_error($conn));
    $_SESSION["sales_comparison_error"] = "Could not load Nooma Total Sale values. Please try again.";
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
    $noomaTotals[$row["report_date"]] = (float) $row["total_sale"];
}
mysqli_stmt_close($statement);

$comparison = [];
foreach ($validatedTotals as $date => $excelTotal) {
    $hasNoomaTotal = array_key_exists($date, $noomaTotals);
    $noomaTotal = $hasNoomaTotal ? $noomaTotals[$date] : null;
    $difference = $hasNoomaTotal ? round($excelTotal - $noomaTotal, 2) : null;
    $comparison[] = [
        "date" => $date,
        "excel_total" => $excelTotal,
        "nooma_total" => $noomaTotal,
        "difference" => $difference,
        "status" => !$hasNoomaTotal ? "No Nooma report" : (abs($difference) < 0.01 ? "Matched" : "Difference")
    ];
}

$_SESSION["sales_comparison_results"] = $comparison;
header("Location: {$redirect}");
exit;