<?php

function refresh_sales_comparison(mysqli $conn, array $comparisons): array
{
    if (!$comparisons) {
        return [];
    }

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

    $dailyDates = [];
    $ranges = [];
    foreach ($comparisons as $comparison) {
        if (!is_array($comparison) || empty($comparison["date"])) {
            continue;
        }
        $date = (string) $comparison["date"];
        $dateTo = (string) ($comparison["date_to"] ?? "");
        $dateObject = DateTime::createFromFormat("!Y-m-d", $date);
        if (!$dateObject || $dateObject->format("Y-m-d") !== $date) {
            throw new RuntimeException("The saved sales comparison contains an invalid date.");
        }

        if ($dateTo === "") {
            $dailyDates[$date] = $date;
            continue;
        }
        $dateToObject = DateTime::createFromFormat("!Y-m-d", $dateTo);
        if (!$dateToObject || $dateToObject->format("Y-m-d") !== $dateTo || $dateTo < $date) {
            throw new RuntimeException("The saved sales comparison contains an invalid date range.");
        }
        $ranges[$date . "|" . $dateTo] = [$date, $dateTo];
    }

    $totalsByDate = [];
    if ($dailyDates) {
        $dates = array_values($dailyDates);
        $placeholders = implode(",", array_fill(0, count($dates), "?"));
        $sql = "SELECT dr.report_date,
                SUM({$grandTotalExpression}) AS grand_total,
                SUM({$totalDiscountExpression}) AS total_discount,
                SUM({$serviceChargeExpression}) AS service_charge,
                COUNT(*) AS report_count
            FROM daily_reports dr
            {$serviceChargeJoin}
            {$discountJoin}
            WHERE dr.status <> 'voided'
                {$posOnlyCondition}
                AND dr.report_date IN ({$placeholders})
            GROUP BY dr.report_date";
        $statement = mysqli_prepare($conn, $sql);
        if (!$statement) {
            throw new RuntimeException("Could not prepare the live sales comparison query: " . mysqli_error($conn));
        }

        $bindValues = [str_repeat("s", count($dates))];
        foreach ($dates as $index => $_date) {
            $bindValues[] = &$dates[$index];
        }
        call_user_func_array([$statement, "bind_param"], $bindValues);
        if (!mysqli_stmt_execute($statement)) {
            $message = mysqli_stmt_error($statement);
            mysqli_stmt_close($statement);
            throw new RuntimeException("Could not refresh daily Nooma totals: " . $message);
        }
        $result = mysqli_stmt_get_result($statement);
        if (!$result) {
            $message = mysqli_stmt_error($statement);
            mysqli_stmt_close($statement);
            throw new RuntimeException("Could not read daily Nooma totals: " . $message);
        }
        while ($row = mysqli_fetch_assoc($result)) {
            $totalsByDate[$row["report_date"]] = $row;
        }
        mysqli_stmt_close($statement);
    }

    $totalsByRange = [];
    foreach ($ranges as $key => [$startDate, $endDate]) {
        $sql = "SELECT
                SUM({$grandTotalExpression}) AS grand_total,
                SUM({$totalDiscountExpression}) AS total_discount,
                SUM({$serviceChargeExpression}) AS service_charge,
                COUNT(*) AS report_count
            FROM daily_reports dr
            {$serviceChargeJoin}
            {$discountJoin}
            WHERE dr.status <> 'voided'
                {$posOnlyCondition}
                AND dr.report_date BETWEEN ? AND ?";
        $statement = mysqli_prepare($conn, $sql);
        if (!$statement) {
            throw new RuntimeException("Could not prepare the live sales comparison query: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($statement, "ss", $startDate, $endDate);
        if (!mysqli_stmt_execute($statement)) {
            $message = mysqli_stmt_error($statement);
            mysqli_stmt_close($statement);
            throw new RuntimeException("Could not refresh range Nooma totals: " . $message);
        }
        $result = mysqli_stmt_get_result($statement);
        if (!$result) {
            $message = mysqli_stmt_error($statement);
            mysqli_stmt_close($statement);
            throw new RuntimeException("Could not read range Nooma totals: " . $message);
        }
        $totalsByRange[$key] = mysqli_fetch_assoc($result);
        mysqli_stmt_close($statement);
    }

    $metricFields = [
        "Excel Total Sale / Nooma Grand Total" => "grand_total",
        "Total Discount" => "total_discount",
        "Service Charge" => "service_charge",
        "Product Total Sales / Nooma Grand Total less Service Charge" => "grand_total_less_service_charge",
        "Product Total Discount / Nooma Total Discount" => "total_discount"
    ];
    foreach ($comparisons as &$comparison) {
        if (!is_array($comparison) || !isset($metricFields[$comparison["metric"] ?? ""])) {
            continue;
        }
        $date = (string) $comparison["date"];
        $dateTo = (string) ($comparison["date_to"] ?? "");
        $key = $dateTo === "" ? null : $date . "|" . $dateTo;
        $noomaTotals = $key === null
            ? ($totalsByDate[$date] ?? null)
            : ($totalsByRange[$key] ?? null);
        $hasNoomaTotal = $noomaTotals !== null && (int) ($noomaTotals["report_count"] ?? 0) > 0;
        $excelTotal = $comparison["excel_total"] ?? null;
        $noomaTotal = $hasNoomaTotal
            ? ($metricFields[$comparison["metric"]] === "grand_total_less_service_charge"
                ? (float) $noomaTotals["grand_total"] - (float) $noomaTotals["service_charge"]
                : (float) $noomaTotals[$metricFields[$comparison["metric"]]])
            : null;
        $difference = $excelTotal === null || $noomaTotal === null
            ? null
            : round((float) $excelTotal - $noomaTotal, 2);

        $comparison["nooma_total"] = $noomaTotal;
        $comparison["difference"] = $difference;
        $comparison["status"] = $excelTotal === null
            ? "Not in Excel"
            : ($noomaTotal === null ? "No Nooma report" : (abs($difference) < 0.01 ? "Matched" : "Difference"));
    }
    unset($comparison);

    return $comparisons;
}
