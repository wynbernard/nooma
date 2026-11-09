<?php
session_start();

if (empty($_SESSION["user_id"])) {
    http_response_code(401);
    exit("You must be logged in to download inventory records.");
}

if (($_SESSION["role"] ?? "") !== "admin") {
    http_response_code(403);
    exit("You are not authorized to download inventory records.");
}

$department = trim((string) ($_GET["department"] ?? ""));
if (!in_array($department, ["Kitchen", "Bar", "Promo"], true)) {
    http_response_code(400);
    exit("Choose a valid inventory department.");
}

$inventoryDate = trim((string) ($_GET["inventory_date"] ?? ""));
$date = DateTimeImmutable::createFromFormat("!Y-m-d", $inventoryDate);
if ($date === false || $date->format("Y-m-d") !== $inventoryDate) {
    http_response_code(400);
    exit("Choose a valid inventory date.");
}

require_once __DIR__ . "/../config/database.php";

$stmt = mysqli_prepare($conn, "
    SELECT inventory_id, inventory_date, counted_by, department, type, item_description,
           quantity, unit, beginning, purchases, sold_used, ending, remarks
    FROM inventory
    WHERE department = ? AND inventory_date = ?
    ORDER BY inventory_id DESC
");
if (!$stmt) {
    throw new RuntimeException("Could not prepare inventory export: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ss", $department, $inventoryDate);
if (!mysqli_stmt_execute($stmt)) {
    throw new RuntimeException("Could not load inventory export: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
if (!$result) {
    throw new RuntimeException("Could not read inventory export: " . mysqli_stmt_error($stmt));
}

$filenameDate = $inventoryDate !== "" ? $inventoryDate : date("Y-m-d");
$filename = strtolower($department) . "-inventory-" . $filenameDate . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header('Content-Disposition: attachment; filename="' . $filename . '"');
header("X-Content-Type-Options: nosniff");

$output = fopen("php://output", "wb");
if ($output === false) {
    throw new RuntimeException("Could not open inventory export output.");
}

$write = static function (string $content) use ($output): void {
    if (fwrite($output, $content) === false) {
        throw new RuntimeException("Could not write inventory export output.");
    }
};
$escapeXml = static fn ($value): string => htmlspecialchars((string) ($value ?? ""), ENT_XML1 | ENT_QUOTES, "UTF-8");
$writeStringCell = static function ($value) use ($write, $escapeXml): void {
    $write('<Cell><Data ss:Type="String">' . $escapeXml($value) . '</Data></Cell>');
};
$writeNumberCell = static function ($value) use ($write, $escapeXml): void {
    $write('<Cell><Data ss:Type="Number">' . $escapeXml($value) . '</Data></Cell>');
};

$headers = [
    "Inventory ID",
    "Date",
    "Counted By",
    "Department",
    "Type",
    "Item",
    "Quantity",
    "Unit",
    "Beginning",
    "Purchases",
    "Sold / Used",
    "Ending",
    "Remarks"
];
$columnWidths = [82, 82, 150, 88, 125, 245, 82, 82, 88, 88, 88, 88, 220];

$write('<?xml version="1.0" encoding="UTF-8"?>');
$write('<?mso-application progid="Excel.Sheet"?>');
$write('<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">');
$write('<Styles><Style ss:ID="Header"><Font ss:Bold="1"/><Interior ss:Color="#D9EAF7" ss:Pattern="Solid"/></Style></Styles>');
$write('<Worksheet ss:Name="Inventory"><Table>');
foreach ($columnWidths as $width) {
    $write('<Column ss:Width="' . $width . '"/>');
}
$write('<Row ss:StyleID="Header">');
foreach ($headers as $header) {
    $writeStringCell($header);
}
$write('</Row>');

while ($row = mysqli_fetch_assoc($result)) {
    $write("<Row>");
    $writeNumberCell($row["inventory_id"]);
    $writeStringCell($row["inventory_date"]);
    $writeStringCell($row["counted_by"]);
    $writeStringCell($row["department"]);
    $writeStringCell($row["type"]);
    $writeStringCell($row["item_description"]);
    $writeNumberCell($row["quantity"]);
    $writeStringCell($row["unit"]);
    $writeNumberCell($row["beginning"]);
    $writeNumberCell($row["purchases"]);
    $writeNumberCell($row["sold_used"]);
    $writeNumberCell($row["ending"]);
    $writeStringCell($row["remarks"]);
    $write("</Row>");
}

$write('</Table><WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel"><FreezePanes/><FrozenNoSplit/><SplitHorizontal>1</SplitHorizontal><TopRowBottomPane>1</TopRowBottomPane><ActivePane>2</ActivePane></WorksheetOptions></Worksheet></Workbook>');
fclose($output);
mysqli_free_result($result);
mysqli_stmt_close($stmt);
