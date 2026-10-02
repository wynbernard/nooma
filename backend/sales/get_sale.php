<?php

session_start();
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "GET" || empty($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "You must be logged in to view a sale."]);
    exit;
}

require_once __DIR__ . "/../config/database.php";

$reportId = (int) ($_GET["report_id"] ?? 0);
if ($reportId <= 0) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Invalid sale selected."]);
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT report_date, shift_name, total_tables, total_pax, telegram_declared_total, pos_sales_total, notes FROM daily_reports WHERE report_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $reportId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$report = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$report) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Sale not found."]);
    exit;
}

$payload = [];
if (!empty($report["notes"])) {
    $savedPayload = json_decode($report["notes"], true);
    if (is_array($savedPayload)) {
        $payload = $savedPayload;
    }
}

$payload["saleDate"] = $report["report_date"];
$savedChannel = strtoupper(trim((string) ($payload["saleChannel"] ?? "")));
$shiftName = (string) ($report["shift_name"] ?? "");
if ($savedChannel === "NON POS" || stripos($shiftName, "Non POS") === 0) {
    $payload["saleChannel"] = "Non POS";
    $savedType = trim((string) ($payload["saleType"] ?? ""));
    if (!in_array($savedType, ["Lunch", "Closing"], true)) {
        if (preg_match("/Lunch|Closing/", $shiftName, $shiftMatch)) {
            $savedType = $shiftMatch[0];
        } else {
            $savedType = "";
        }
    }
    $payload["saleType"] = $savedType;
} else {
    $payload["saleType"] = $shiftName;
    $payload["saleChannel"] = ((float) $report["pos_sales_total"] > 0) ? "POS" : "Non POS";
}
$payload["tableNumber"] = (string) $report["total_tables"];
$payload["pax"] = (string) $report["total_pax"];
if (($payload["saleChannel"] ?? "") !== "Non POS") {
    $payload["grandTotal"] = $report["telegram_declared_total"];
    $payload["totalSale"] = $report["pos_sales_total"];
} else {
    if (!isset($payload["grandTotal"]) || $payload["grandTotal"] === "" || $payload["grandTotal"] === null) {
        $payload["grandTotal"] = $report["telegram_declared_total"];
    }
}
$payload["ownerAccounts"] = [];

$result = mysqli_query($conn, "SELECT account_holder_id, amount FROM report_accounts WHERE report_id = " . $reportId . " AND account_category = 'owners_account'");
while ($row = mysqli_fetch_assoc($result)) {
    $payload["ownerAccounts"][(string) $row["account_holder_id"]] = $row["amount"];
}

$salesFields = [
    "kitchen" => "kitchenSale",
    "bar" => "barSale",
    "corkage" => "corkage",
    "service_charge" => "serviceCharge",
    "grab_gross" => "grabSaleGross"
];
$result = mysqli_query($conn, "SELECT sales_type, amount FROM report_sales WHERE report_id = " . $reportId);
while ($row = mysqli_fetch_assoc($result)) {
    if (isset($salesFields[$row["sales_type"]])) {
        $payload[$salesFields[$row["sales_type"]]] = $row["amount"];
    } elseif ($row["sales_type"] === "tip") {
        $payload["cashSales"] = $row["amount"];
        $payload["onlineTips"] = "0.00";
    }
}

$paymentFields = [
    1 => "cashRemitted", 2 => "gcashQrph", 3 => "paymaya", 4 => "amex", 5 => "visa", 6 => "mastercard",
    7 => "bancnet", 8 => "jcb", 9 => "bpi", 10 => "easwest", 11 => "giftcheck", 12 => "cheque",
    13 => "otherCash", 14 => "otherMayaTerminal", 15 => "otherBpiNooma", 16 => "otherEastwestNooma",
    17 => "otherGiftCheck", 18 => "otherCheques"
];
$result = mysqli_query($conn, "SELECT payment_method_id, amount FROM report_payments WHERE report_id = " . $reportId);
while ($row = mysqli_fetch_assoc($result)) {
    if (isset($paymentFields[(int) $row["payment_method_id"]])) {
        $payload[$paymentFields[(int) $row["payment_method_id"]]] = $row["amount"];
    }
}

$expenseFields = [1 => "marketingExpensesF", 2 => "djRonald", 3 => "ejVelez", 4 => "guestDJ", 9 => "djsTalentFee", 10 => "bouncersFee"];
$result = mysqli_query($conn, "SELECT expense_category_id, amount FROM report_expenses WHERE report_id = " . $reportId . " ORDER BY expense_id");
while ($row = mysqli_fetch_assoc($result)) {
    $categoryId = (int) $row["expense_category_id"];
    if (isset($expenseFields[$categoryId])) {
        $payload[$expenseFields[$categoryId]] = $row["amount"];
    } elseif ($categoryId === 5) {
        $payload["marketingOthers"] = $row["amount"];
    }
}

$result = mysqli_query($conn, "SELECT discount_type_id, deduction_type, amount FROM report_deductions WHERE report_id = " . $reportId);
while ($row = mysqli_fetch_assoc($result)) {
    if ($row["deduction_type"] === "refund") {
        $payload["refund"] = $row["amount"];
    } elseif ((int) $row["discount_type_id"] === 1) {
        $payload["pwdDiscount"] = $row["amount"];
    } elseif ((int) $row["discount_type_id"] === 2) {
        $payload["seniorCitizenDiscount"] = $row["amount"];
    } elseif ((int) $row["discount_type_id"] === 3) {
        $payload["specialCustomerDiscount"] = $row["amount"];
    }
}

echo json_encode(["success" => true, "payload" => $payload]);
