```php
<?php

session_start();

require_once "../config/database.php";


// Only allow POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../../login.php");
    exit;
}


// Get form data
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";


// Validate
if ($username === "" || $password === "") {

    $_SESSION["login_error"] = "Please enter your username and password.";

    header("Location: ../../login.php");
    exit;
}


// Find user
$sql = "
    SELECT
        user_id,
        username,
        password_hash,
        full_name,
        role,
        is_active
    FROM users
    WHERE username = ?
    LIMIT 1
";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    error_log("Login prepare error: " . mysqli_error($conn));

    $_SESSION["login_error"] = "Something went wrong. Please try again.";

    header("Location: ../../frontend/Login/login.php");
    exit;
}


// Bind username
mysqli_stmt_bind_param(
    $stmt,
    "s",
    $username
);


// Execute
mysqli_stmt_execute($stmt);


// Get result
$result = mysqli_stmt_get_result($stmt);


// Get user
$user = mysqli_fetch_assoc($result);


// Close statement
mysqli_stmt_close($stmt);


// User not found
if (!$user) {

    $_SESSION["login_error"] = "Invalid username or password.";

    header("Location: ../../frontend/Login/login.php");
    exit;
}


// Check account status
if (!$user["is_active"]) {

    $_SESSION["login_error"] = "Your account is inactive.";

    header("Location: ../../frontend/Login/login.php");
    exit;
}


// Verify password
if ($password !== $user["password_hash"]) {

    $_SESSION["login_error"] = "Invalid username or password.";

    header("Location: ../../frontend/Login/login.php");
    exit;
}


// Regenerate session ID
session_regenerate_id(true);


// Store user information
$_SESSION["user_id"] = $user["user_id"];
$_SESSION["username"] = $user["username"];
$_SESSION["full_name"] = $user["full_name"];
$_SESSION["role"] = $user["role"];


// Redirect based on role
switch ($user["role"]) {

    case "admin":

        header("Location: ../../frontend/Dashboard/admin.php");

        break;


    case "manager":

        header("Location: ../../dashboard/manager.php");

        break;


    case "cashier":

        header("Location: ../../frontend/Dashboard/admin.php");

        break;


    default:

        header("Location: ../../dashboard/index.php");

        break;
}


exit;

