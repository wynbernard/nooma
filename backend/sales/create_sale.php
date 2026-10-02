<?php

session_start();

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "You must be logged in to save a sale."]);
    exit;
}

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/save_owner_images.php";
require_once __DIR__ . "/non_pos_totals.php";

$payload = json_decode($_POST["sales_payload"] ?? "", true);
if (!is_array($payload)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Invalid sales data."]);
    exit;
}

$money = static function ($value): float {
    $value = str_replace(",", "", (string) $value);
    return is_numeric($value) ? round((float) $value, 2) : 0.0;
};

$value = static function (string $key) use ($payload, $money): float {
    return $money($payload[$key] ?? 0);
};

$saleChannel = strtoupper(trim((string) ($payload["saleChannel"] ?? "POS")));
if (!in_array($saleChannel, ["POS", "NON POS"], true)) {
    $saleChannel = "POS";
}

$date = $payload["saleDate"] ?? date("Y-m-d");
if (!preg_match("/^\\d{4}-\\d{2}-\\d{2}$/", $date)) {
    if ($saleChannel === "NON POS") {
        $date = date("Y-m-d");
    } else {
        http_response_code(422);
        echo json_encode(["success" => false, "message" => "Please provide a valid sale date."]);
        exit;
    }
}

$shift = trim((string) ($payload["saleType"] ?? ""));
$cashierId = (int) $_SESSION["user_id"];
if ($saleChannel === "NON POS") {
    if ($shift === "Lunch" || $shift === "Closing") {
        $shift = "Non POS " . $shift;
    } else {
        $shift = "Non POS";
    }

    $existsStmt = mysqli_prepare($conn, "SELECT report_id FROM daily_reports WHERE report_date = ? AND cashier_id = ? AND shift_name = ? LIMIT 1");
    if ($existsStmt) {
        mysqli_stmt_bind_param($existsStmt, "sis", $date, $cashierId, $shift);
        mysqli_stmt_execute($existsStmt);
        mysqli_stmt_store_result($existsStmt);
        if (mysqli_stmt_num_rows($existsStmt) > 0) {
            $shift .= " " . date("His") . random_int(10, 99);
        }
        mysqli_stmt_close($existsStmt);
    }
} elseif (!in_array($shift, ["Lunch", "Closing"], true)) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Please select a valid sale type."]);
    exit;
}

$duplicate = false;
if ($saleChannel === "POS") {
    $duplicateStmt = mysqli_prepare($conn, "SELECT report_id FROM daily_reports WHERE report_date = ? AND shift_name = ? LIMIT 1");
    if (!$duplicateStmt) {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Unable to validate the sale."]);
        exit;
    }
    mysqli_stmt_bind_param($duplicateStmt, "ss", $date, $shift);
    mysqli_stmt_execute($duplicateStmt);
    mysqli_stmt_store_result($duplicateStmt);
    $duplicate = mysqli_stmt_num_rows($duplicateStmt) > 0;
    mysqli_stmt_close($duplicateStmt);
}

if ($duplicate) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "A sale with this date and sale type already exists."]);
    exit;
}

$pax = max(0, (int) ($payload["pax"] ?? 0));
$tableNumber = max(0, (int) preg_replace("/\\D+/", "", (string) ($payload["tableNumber"] ?? "")));
apply_non_pos_sale_totals($payload);
$grandTotal = $value("grandTotal");
$posTotal = $saleChannel === "POS" ? $value("totalSale") : 0.0;
save_owner_account_images($payload);
$notes = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$reportNumber = "SR-" . date("YmdHis") . "-" . random_int(100, 999);
$weekNumber = (int) date("W", strtotime($date));

mysqli_begin_transaction($conn);

try {
    $stmt = mysqli_prepare($conn, "INSERT INTO daily_reports
        (report_number, report_date, shift_name, week_number, cashier_id, total_tables,
         total_pax, telegram_declared_total, pos_sales_total, status, notes)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)");
    if (!$stmt) {
        throw new RuntimeException(mysqli_error($conn));
    }

    $cashierId = (int) $_SESSION["user_id"];
    $totalTables = $tableNumber > 0 ? $tableNumber : 1;
    mysqli_stmt_bind_param(
        $stmt,
        "sssiiiidds",
        $reportNumber,
        $date,
        $shift,
        $weekNumber,
        $cashierId,
        $totalTables,
        $pax,
        $grandTotal,
        $posTotal,
        $notes
    );
    mysqli_stmt_execute($stmt);
    $reportId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    $ownerAccounts = $payload["ownerAccounts"] ?? [];
    if (is_array($ownerAccounts)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO report_accounts
            (report_id, account_holder_id, account_category, transaction_type, amount)
            VALUES (?, ?, 'owners_account', 'charge', ?)");
        foreach ($ownerAccounts as $accountId => $amountValue) {
            $accountId = (int) $accountId;
            $amount = $money($amountValue);
            if ($accountId <= 0 || $amount <= 0) {
                continue;
            }
            mysqli_stmt_bind_param($stmt, "iid", $reportId, $accountId, $amount);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);
    }

    $sales = [
        "kitchen" => $value("kitchenSale"),
        "bar" => $value("barSale"),
        "corkage" => $value("corkage"),
        "service_charge" => $value("serviceCharge"),
        "grab_gross" => $value("grabSaleGross"),
        "tip" => $value("cashSales") + $value("onlineTips")
    ];
    $stmt = mysqli_prepare($conn, "INSERT INTO report_sales (report_id, sales_type, amount) VALUES (?, ?, ?)");
    foreach ($sales as $type => $amount) {
        if ($amount <= 0) {
            continue;
        }
        mysqli_stmt_bind_param($stmt, "isd", $reportId, $type, $amount);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);

    $payments = [
        "cashRemitted" => 1,
        "gcashQrph" => 2,
        "paymaya" => 3,
        "amex" => 4,
        "visa" => 5,
        "mastercard" => 6,
        "bancnet" => 7,
        "jcb" => 8,
        "bpi" => 9,
        "easwest" => 10,
        "giftcheck" => 11,
        "cheque" => 12
    ];
    $stmt = mysqli_prepare($conn, "INSERT INTO report_payments (report_id, payment_method_id, amount) VALUES (?, ?, ?)");
    foreach ($payments as $field => $methodId) {
        $amount = $value($field);
        if ($amount <= 0) {
            continue;
        }
        mysqli_stmt_bind_param($stmt, "iid", $reportId, $methodId, $amount);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);

    // Other payments
    $otherPayments = [
        "otherCash" => 13,
        "otherMayaTerminal" => 14,
        "otherBpiNooma" => 15,
        "otherEastwestNooma" => 16,
        "otherGiftCheck" => 17,
        "otherCheques" => 18
    ];
    $stmt = mysqli_prepare($conn, "INSERT INTO report_payments (report_id, payment_method_id, amount) VALUES (?, ?, ?)");
    foreach ($otherPayments as $field => $methodId) {
        $amount = $value($field);
        if ($amount <= 0) {
            continue;
        }
        mysqli_stmt_bind_param($stmt, "iid", $reportId, $methodId, $amount);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);

    $expenses = [
        "marketingExpensesF" => 1,
        "djRonald" => 2,
        "ejVelez" => 3,
        "guestDJ" => 4,
        "marketingOthers" => 5,
        "djsTalentFee" => 9,
        "bouncersFee" => 10,
        "others" => 5
    ];
    $stmt = mysqli_prepare($conn, "INSERT INTO report_expenses (report_id, expense_category_id, amount) VALUES (?, ?, ?)");
    foreach ($expenses as $field => $categoryId) {
        $amount = $value($field);
        if ($amount <= 0) {
            continue;
        }
        mysqli_stmt_bind_param($stmt, "iid", $reportId, $categoryId, $amount);
        mysqli_stmt_execute($stmt);
    }
    mysqli_stmt_close($stmt);

    $deductions = [
        "pwdDiscount" => 1,
        "seniorCitizenDiscount" => 2,
        "specialCustomerDiscount" => 3,
        "refund" => null
    ];
    foreach ($deductions as $field => $discountTypeId) {
        $amount = $value($field);
        if ($amount <= 0) {
            continue;
        }
        $type = $field === "refund" ? "refund" : "discount";
        if ($discountTypeId === null) {
            $stmt = mysqli_prepare($conn, "INSERT INTO report_deductions (report_id, deduction_type, amount) VALUES (?, 'refund', ?)");
            mysqli_stmt_bind_param($stmt, "id", $reportId, $amount);
        } else {
            $stmt = mysqli_prepare($conn, "INSERT INTO report_deductions (report_id, discount_type_id, deduction_type, amount) VALUES (?, ?, 'discount', ?)");
            mysqli_stmt_bind_param($stmt, "iid", $reportId, $discountTypeId, $amount);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    mysqli_commit($conn);
    echo json_encode(["success" => true, "message" => "Sale saved successfully.", "report_id" => $reportId, "report_number" => $reportNumber]);
} catch (Throwable $error) {
    mysqli_rollback($conn);
    error_log("Create sale error: " . $error->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "The sale could not be saved: " . $error->getMessage()]);
}
