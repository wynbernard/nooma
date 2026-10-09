<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../../frontend/Login/login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../frontend/Compared_sale/compared.php");
    exit;
}

require_once __DIR__ . "/../config/database.php";

$redirect = "../../frontend/Compared_sale/compared.php";
$grandTotalExpression = "CASE WHEN JSON_VALID(dr.notes) THEN COALESCE(
            CAST(JSON_UNQUOTE(JSON_EXTRACT(dr.notes, '$.grandTotal')) AS DECIMAL(12,2)),
            dr.telegram_declared_total
        ) ELSE dr.telegram_declared_total END";
$totalDiscountExpression = "COALESCE(discounts.total_discount, 0)";
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
$discountJoin = "LEFT JOIN (
        SELECT report_id, SUM(amount) AS total_discount
        FROM report_deductions
        WHERE deduction_type = 'discount'
        GROUP BY report_id
    ) discounts ON discounts.report_id = dr.report_id";
$posOnlyCondition = "AND UPPER(TRIM(CASE WHEN JSON_VALID(dr.notes) THEN COALESCE(
            JSON_UNQUOTE(JSON_EXTRACT(dr.notes, '$.saleChannel')),
            ''
        ) ELSE '' END)) <> 'NON POS'
        AND UPPER(TRIM(COALESCE(dr.shift_name, ''))) NOT LIKE 'NON POS%'";
$metricDefinitions = [
    "grand_total" => ["label" => "Excel Total Sale / Nooma Grand Total", "expression" => $grandTotalExpression],
    "total_discount" => ["label" => "Total Discount", "expression" => $totalDiscountExpression],
    "service_charge" => ["label" => "Service Charge", "expression" => $serviceChargeExpression]
];
$productSalesMetric = "Product Total Sales / Nooma Grand Total less Service Charge";
$productDiscountMetric = "Product Total Discount / Nooma Total Discount";
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
$productInventoryJson = trim((string) ($_POST["product_inventory_json"] ?? ""));
if ($productInventoryJson !== "") {
    $productReport = json_decode($productInventoryJson, true);
    $reportDate = is_array($productReport) ? (string) ($productReport["date"] ?? "") : "";
    $dateObject = DateTime::createFromFormat("!Y-m-d", $reportDate);
    $products = is_array($productReport) ? ($productReport["products"] ?? null) : null;
    if (!$dateObject || $dateObject->format("Y-m-d") !== $reportDate
        || !is_array($products) || !$products || count($products) > 2000) {
        $_SESSION["sales_comparison_error"] = "The Sales by Product report date or product data is invalid.";
        header("Location: {$redirect}");
        exit;
    }

    $productsByKey = [];
    $excelTotalSales = 0.0;
    $excelTotalDiscount = 0.0;
    foreach ($products as $product) {
        if (!is_array($product)) {
            $_SESSION["sales_comparison_error"] = "The Sales by Product report contains an invalid product row.";
            header("Location: {$redirect}");
            exit;
        }
        $name = trim((string) ($product["name"] ?? ""));
        $department = trim((string) ($product["department"] ?? ""));
        $itemsSold = $product["items_sold"] ?? null;
        $totalSales = $product["total_sales"] ?? null;
        $totalDiscount = $product["total_discount"] ?? null;
        if ($name === "" || strlen($name) > 255
            || !in_array($department, ["Kitchen", "Bar"], true)
            || !is_numeric($itemsSold) || !is_finite((float) $itemsSold) || (float) $itemsSold < 0
            || !is_numeric($totalSales) || !is_finite((float) $totalSales)
            || !is_numeric($totalDiscount) || !is_finite((float) $totalDiscount)) {
            $_SESSION["sales_comparison_error"] = "The Sales by Product report contains invalid product names, categories, or amounts.";
            header("Location: {$redirect}");
            exit;
        }
        $totalSales = (float) $totalSales;
        $productKey = strtolower($department) . "|" . strtolower($name);
        if (!isset($productsByKey[$productKey])) {
            $productsByKey[$productKey] = [
                "name" => $name,
                "department" => $department,
                "items_sold" => 0.0
            ];
        }
        $productsByKey[$productKey]["items_sold"] += (float) $itemsSold;
        $excelTotalSales += $totalSales;
        $excelTotalDiscount += (float) $totalDiscount;
    }
    $validatedProducts = array_values($productsByKey);

    $existingProductsStatement = mysqli_prepare($conn, "SELECT inventory_id, department, item_description FROM inventory WHERE inventory_date = ?");
    if (!$existingProductsStatement) {
        error_log("Sales product inventory lookup preparation failed: " . mysqli_error($conn));
        $_SESSION["sales_comparison_error"] = "Could not check existing inventory products.";
        header("Location: {$redirect}");
        exit;
    }
    mysqli_stmt_bind_param($existingProductsStatement, "s", $reportDate);
    if (!mysqli_stmt_execute($existingProductsStatement)) {
        error_log("Sales product inventory lookup failed: " . mysqli_stmt_error($existingProductsStatement));
        mysqli_stmt_close($existingProductsStatement);
        $_SESSION["sales_comparison_error"] = "Could not check existing inventory products.";
        header("Location: {$redirect}");
        exit;
    }
    $existingProductsResult = mysqli_stmt_get_result($existingProductsStatement);
    $existingProducts = [];
    while ($existingProduct = mysqli_fetch_assoc($existingProductsResult)) {
        $key = strtolower(trim((string) $existingProduct["department"]))
            . "|" . strtolower(trim((string) $existingProduct["item_description"]));
        $existingProducts[$key] = (int) $existingProduct["inventory_id"];
    }
    mysqli_stmt_close($existingProductsStatement);

    $addedProducts = 0;
    $updatedProducts = 0;
    if (!mysqli_begin_transaction($conn)) {
        error_log("Sales product inventory transaction could not start: " . mysqli_error($conn));
        $_SESSION["sales_comparison_error"] = "Could not start the product inventory import.";
        header("Location: {$redirect}");
        exit;
    }
    try {
        $insertProduct = mysqli_prepare($conn, "INSERT INTO inventory
            (inventory_date, counted_by, department, type, item_description, quantity, unit, beginning, purchases, sold_used, ending, remarks)
            VALUES (?, ?, ?, 'Product', ?, 0, 'pcs', 0, ?, 0, 0, ?)");
        if (!$insertProduct) {
            throw new RuntimeException("Could not prepare inventory product insert: " . mysqli_error($conn));
        }
        $updateProduct = mysqli_prepare($conn, "UPDATE inventory SET purchases = ? WHERE inventory_id = ?");
        if (!$updateProduct) {
            throw new RuntimeException("Could not prepare inventory purchases update: " . mysqli_error($conn));
        }
        $countedBy = (string) ($_SESSION["full_name"] ?? "Administrator");
        $remarks = "Imported from Sales by Product report " . $reportDate . ".";
        foreach ($validatedProducts as $product) {
            $normalizedName = strtolower(trim($product["department"]))
                . "|" . strtolower(trim($product["name"]));
            if (isset($existingProducts[$normalizedName])) {
                $productItemsSold = $product["items_sold"];
                $inventoryId = $existingProducts[$normalizedName];
                mysqli_stmt_bind_param($updateProduct, "di", $productItemsSold, $inventoryId);
                if (!mysqli_stmt_execute($updateProduct)) {
                    throw new RuntimeException("Could not update inventory purchases: " . mysqli_stmt_error($updateProduct));
                }
                $updatedProducts++;
                continue;
            }
            $productName = $product["name"];
            $department = $product["department"];
            $productItemsSold = $product["items_sold"];
            mysqli_stmt_bind_param($insertProduct, "ssssds", $reportDate, $countedBy, $department, $productName, $productItemsSold, $remarks);
            if (!mysqli_stmt_execute($insertProduct)) {
                throw new RuntimeException("Could not add inventory product: " . mysqli_stmt_error($insertProduct));
            }
            $existingProducts[$normalizedName] = (int) mysqli_insert_id($conn);
            $addedProducts++;
        }
        mysqli_stmt_close($insertProduct);
        mysqli_stmt_close($updateProduct);

        $noomaStatement = mysqli_prepare($conn, "SELECT
                SUM({$grandTotalExpression}) AS grand_total,
                SUM({$totalDiscountExpression}) AS total_discount,
                SUM({$serviceChargeExpression}) AS service_charge,
                COUNT(*) AS report_count
            FROM daily_reports dr
            {$serviceChargeJoin}
            {$discountJoin}
            WHERE dr.status <> 'voided'
                {$posOnlyCondition}
                AND dr.report_date = ?");
        if (!$noomaStatement) {
            throw new RuntimeException("Could not prepare Nooma product sales comparison: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($noomaStatement, "s", $reportDate);
        if (!mysqli_stmt_execute($noomaStatement)) {
            throw new RuntimeException("Could not load Nooma product sales comparison: " . mysqli_stmt_error($noomaStatement));
        }
        $noomaResult = mysqli_stmt_get_result($noomaStatement);
        if (!$noomaResult) {
            throw new RuntimeException("Could not read Nooma product sales comparison: " . mysqli_stmt_error($noomaStatement));
        }
        $noomaTotals = mysqli_fetch_assoc($noomaResult);
        mysqli_stmt_close($noomaStatement);
        if (!mysqli_commit($conn)) {
            throw new RuntimeException("Could not commit inventory product import: " . mysqli_error($conn));
        }
    } catch (Throwable $error) {
        mysqli_rollback($conn);
        error_log("Sales product import failed: " . $error->getMessage());
        $_SESSION["sales_comparison_error"] = "Could not import the Sales by Product report. Verify the inventory database migration and try again.";
        header("Location: {$redirect}");
        exit;
    }

    $hasNoomaTotal = (int) ($noomaTotals["report_count"] ?? 0) > 0;
    $noomaAdjustedTotal = $hasNoomaTotal
        ? round((float) $noomaTotals["grand_total"] - (float) $noomaTotals["service_charge"], 2)
        : null;
    $difference = $noomaAdjustedTotal === null ? null : round($excelTotalSales - $noomaAdjustedTotal, 2);
    $_SESSION["sales_product_import_result"] = [
        "added" => $addedProducts,
        "updated" => $updatedProducts
    ];
    $_SESSION["sales_comparison_results"] = [[
        "date" => $reportDate,
        "date_to" => null,
        "metric" => $productSalesMetric,
        "excel_total" => round($excelTotalSales, 2),
        "nooma_total" => $noomaAdjustedTotal,
        "difference" => $difference,
        "status" => $noomaAdjustedTotal === null
            ? "No Nooma report"
            : (abs($difference) < 0.01 ? "Matched" : "Difference")
    ], [
        "date" => $reportDate,
        "date_to" => null,
        "metric" => $productDiscountMetric,
        "excel_total" => round($excelTotalDiscount, 2),
        "nooma_total" => $hasNoomaTotal ? (float) $noomaTotals["total_discount"] : null,
        "difference" => $hasNoomaTotal ? round($excelTotalDiscount - (float) $noomaTotals["total_discount"], 2) : null,
        "status" => !$hasNoomaTotal
            ? "No Nooma report"
            : (abs(round($excelTotalDiscount - (float) $noomaTotals["total_discount"], 2)) < 0.01 ? "Matched" : "Difference")
    ]];
    header("Location: {$redirect}");
    exit;
}

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
        {$discountJoin}
        WHERE dr.status <> 'voided'
            {$posOnlyCondition}
            AND dr.report_date BETWEEN ? AND ?");
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
    {$discountJoin}
    WHERE dr.status <> 'voided'
        {$posOnlyCondition}
        AND dr.report_date IN ({$placeholders})
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