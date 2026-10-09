<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Make sure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

// Only admin can access this page
if ($_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit;
}

$full_name = $_SESSION["full_name"] ?? "Administrator";
$username = $_SESSION["username"] ?? "admin";

// Page information
$inventoryDepartment = basename((string) ($_SERVER["PHP_SELF"] ?? "")) === "kitchenInventory.php"
    ? "Kitchen"
    : "Bar";
$page_title = $inventoryDepartment . " Inventory";
$page_description = $inventoryDepartment . " stock register";
$inventoryDateFilter = trim((string) ($_GET["inventory_date"] ?? ""));

require_once __DIR__ . "/../../backend/config/database.php";

$success_message = "";
$error_message = "";
if ($inventoryDateFilter !== "") {
    $filterDateObject = DateTime::createFromFormat("!Y-m-d", $inventoryDateFilter);
    if (!$filterDateObject || $filterDateObject->format("Y-m-d") !== $inventoryDateFilter) {
        $inventoryDateFilter = "";
        $error_message = "Choose a valid inventory date.";
    }
}
$inventoryAction = (string) ($_POST["inventory_action"] ?? "");
$inventoryId = 0;
$department = trim((string) ($_POST["department"] ?? $inventoryDepartment));
if (!in_array($department, ["Kitchen", "Bar"], true)) {
    $error_message = "Choose either Kitchen or Bar for the inventory department.";
}

// ==========================================
// 1. HANDLE ADD INVENTORY REQUEST
// ==========================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && $error_message === "" && (isset($_POST["add_inventory"]) || $inventoryAction === "add")) {
    $inventory_date = $_POST["inventory_date"];
    $counted_by = $_POST["counted_by"];
    $type = $_POST["type"];
    $item_description = $_POST["item_description"];
    $quantity = (float) $_POST["quantity"];
    $unit = $_POST["unit"];
    $beginning = (float) $_POST["beginning"];
    $purchases = (float) $_POST["purchases"];
    $sold_used = (float) $_POST["sold_used"];
    $ending = (float) $_POST["ending"];
    $remarks = $_POST["remarks"];

    $stmt = mysqli_prepare($conn, "INSERT INTO inventory (inventory_date, counted_by, department, type, item_description, quantity, unit, beginning, purchases, sold_used, ending, remarks) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssdsdddds", $inventory_date, $counted_by, $department, $type, $item_description, $quantity, $unit, $beginning, $purchases, $sold_used, $ending, $remarks);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Inventory item added successfully!";
            $inventoryId = mysqli_insert_id($conn);
        } else {
            $error_message = "Error adding item: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Database error: " . mysqli_error($conn);
    }
}

// ==========================================
// 2. HANDLE UPDATE INVENTORY REQUEST
// ==========================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && $error_message === "" && (isset($_POST["update_inventory"]) || $inventoryAction === "update")) {
    $inventory_id = (int) $_POST["inventory_id"];
    $inventory_date = $_POST["inventory_date"];
    $counted_by = $_POST["counted_by"];
    $type = $_POST["type"];
    $item_description = $_POST["item_description"];
    $quantity = (float) $_POST["quantity"];
    $unit = $_POST["unit"];
    $beginning = (float) $_POST["beginning"];
    $purchases = (float) $_POST["purchases"];
    $sold_used = (float) $_POST["sold_used"];
    $ending = (float) $_POST["ending"];
    $remarks = $_POST["remarks"];

    $stmt = mysqli_prepare($conn, "UPDATE inventory SET inventory_date = ?, counted_by = ?, department = ?, type = ?, item_description = ?, quantity = ?, unit = ?, beginning = ?, purchases = ?, sold_used = ?, ending = ?, remarks = ? WHERE inventory_id = ?");
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssdsddddsi", $inventory_date, $counted_by, $department, $type, $item_description, $quantity, $unit, $beginning, $purchases, $sold_used, $ending, $remarks, $inventory_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Inventory item updated successfully!";
        } else {
            $error_message = "Error updating item: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Database error: " . mysqli_error($conn);
    }
}

// ==========================================
// 3. HANDLE DELETE INVENTORY REQUEST
// ==========================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && (isset($_POST["delete_inventory"]) || $inventoryAction === "delete")) {
    $inventory_id = (int) $_POST["inventory_id"];

    $stmt = mysqli_prepare($conn, "DELETE FROM inventory WHERE inventory_id = ?");
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $inventory_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success_message = "Inventory item deleted successfully!";
        } else {
            $error_message = "Error deleting item: " . mysqli_stmt_error($stmt);
        }
        mysqli_stmt_close($stmt);
    } else {
        $error_message = "Database error: " . mysqli_error($conn);
    }
}

if (str_contains($_SERVER["HTTP_ACCEPT"] ?? "", "application/json") && $_SERVER["REQUEST_METHOD"] === "POST") {
    $actionSucceeded = $error_message === "" && $success_message !== "";
    $response = [
        "success" => $actionSucceeded,
        "message" => $actionSucceeded ? $success_message : $error_message,
        "action" => $inventoryAction,
    ];

    if ($actionSucceeded && $inventoryAction !== "delete") {
        $response["item"] = [
            "inventory_id" => $inventoryAction === "add" ? $inventoryId : (int) ($_POST["inventory_id"] ?? 0),
            "inventory_date" => (string) ($_POST["inventory_date"] ?? ""),
            "counted_by" => (string) ($_POST["counted_by"] ?? ""),
            "department" => $department,
            "type" => (string) ($_POST["type"] ?? ""),
            "item_description" => (string) ($_POST["item_description"] ?? ""),
            "quantity" => (float) ($_POST["quantity"] ?? 0),
            "unit" => (string) ($_POST["unit"] ?? ""),
            "beginning" => (float) ($_POST["beginning"] ?? 0),
            "purchases" => (float) ($_POST["purchases"] ?? 0),
            "sold_used" => (float) ($_POST["sold_used"] ?? 0),
            "ending" => (float) ($_POST["ending"] ?? 0),
            "remarks" => (string) ($_POST["remarks"] ?? ""),
            "created_at" => date("Y-m-d H:i:s"),
        ];
    } elseif ($actionSucceeded) {
        $response["inventory_id"] = (int) ($_POST["inventory_id"] ?? 0);
    }

    if (!$actionSucceeded) {
        http_response_code(400);
    }
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($response);
    exit;
}

// ==========================================
// 4. FETCH DATA & COMPUTE METRICS
// ==========================================
$inventoryItems = [];
$inventoryStmt = mysqli_prepare($conn, "
    SELECT inventory_id, inventory_date, counted_by, department, type, item_description, quantity, unit,
           beginning, purchases, sold_used, ending, remarks, created_at, updated_at 
    FROM inventory
    WHERE department = ? AND (? = '' OR inventory_date = ?)
    ORDER BY inventory_id DESC
");

if (!$inventoryStmt) {
    throw new RuntimeException("Could not prepare inventory query: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($inventoryStmt, "sss", $inventoryDepartment, $inventoryDateFilter, $inventoryDateFilter);
if (!mysqli_stmt_execute($inventoryStmt)) {
    throw new RuntimeException("Could not load inventory: " . mysqli_stmt_error($inventoryStmt));
}
$inventoryResult = mysqli_stmt_get_result($inventoryStmt);
if ($inventoryResult) {
    while ($row = mysqli_fetch_assoc($inventoryResult)) {
        $row["sold"] = $row["sold_used"] ?? 0.00; 
        $row["used"] = 0.00;
        $inventoryItems[] = $row;
    }
}
mysqli_stmt_close($inventoryStmt);

$totalItemsCount = count($inventoryItems);
$totalStockQuantity = array_sum(array_map(static fn (array $row): float => (float) $row["quantity"], $inventoryItems));
$lowStockCount = count(array_filter($inventoryItems, static fn (array $row): bool => (float) $row["quantity"] <= 5));
$recentAdditionsCount = count(array_filter($inventoryItems, static fn (array $row): bool => strtotime($row["created_at"]) >= strtotime("-7 days")));

$chartItems = array_slice($inventoryItems, 0, 10);
$inventoryChartData = [
    "labels" => array_map(static fn (array $row): string => $row["item_description"], $chartItems),
    "values" => array_map(static fn (array $row): float => (float) $row["quantity"], $chartItems)
];