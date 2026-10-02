<?php

session_start();
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST" || empty($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "You must be logged in to delete a sale."]);
    exit;
}

require_once __DIR__ . "/../config/database.php";

$reportId = (int) ($_POST["report_id"] ?? 0);
if ($reportId <= 0) {
    http_response_code(422);
    echo json_encode(["success" => false, "message" => "Invalid sale selected."]);
    exit;
}

$stmt = mysqli_prepare($conn, "DELETE FROM daily_reports WHERE report_id = ?");
mysqli_stmt_bind_param($stmt, "i", $reportId);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) !== 1) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "Sale not found."]);
    mysqli_stmt_close($stmt);
    exit;
}

mysqli_stmt_close($stmt);
echo json_encode(["success" => true, "message" => "Sale deleted successfully."]);