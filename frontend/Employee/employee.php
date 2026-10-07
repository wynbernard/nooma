<?php
session_start();

if (!isset($_SESSION["user_id"]) || !in_array(($_SESSION["role"] ?? ""), ["admin", "manager", "cashier"], true)) {
    header("Location: ../Login/login.php");
    exit;
}

require_once __DIR__ . "/../../backend/config/database.php";

if (isset($_SESSION["employee_message"])) {
    $employeeMessage = (string) $_SESSION["employee_message"];
    $employeeMessageType = isset($_SESSION["employee_message_type"]) && $_SESSION["employee_message_type"] === "error" ? "error" : "success";
    unset($_SESSION["employee_message"], $_SESSION["employee_message_type"]);
} else {
    $employeeMessage = "";
    $employeeMessageType = "success";
}

$editEmployee = null;

if (isset($_GET["edit_user_id"])) {
    $editEmployeeId = (int) $_GET["edit_user_id"];
    if ($editEmployeeId > 0) {
        $editStmt = mysqli_prepare($conn, "SELECT user_id, full_name, username, role, is_active, number_of_days FROM users WHERE user_id = ? LIMIT 1");
        if ($editStmt) {
            mysqli_stmt_bind_param($editStmt, "i", $editEmployeeId);
            mysqli_stmt_execute($editStmt);
            $editResult = mysqli_stmt_get_result($editStmt);
            if ($editResult && mysqli_num_rows($editResult) > 0) {
                $editEmployee = mysqli_fetch_assoc($editResult);
            }
            mysqli_stmt_close($editStmt);
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $userId = (int) ($_POST["user_id"] ?? 0);
    $fullName = trim((string) ($_POST["full_name"] ?? ""));
    $username = trim((string) ($_POST["username"] ?? ""));
    $role = $_POST["role"] ?? "cashier";
    $password = (string) ($_POST["password"] ?? "");
    $isActive = isset($_POST["is_active"]) ? 1 : 0;
    $numberOfDays = filter_var($_POST["number_of_days"] ?? "0", FILTER_VALIDATE_INT);

    if (!in_array($role, ["admin", "manager", "cashier", "employee"], true)) {
        $role = "cashier";
    }

    if ($action === "create") {
        if ($fullName === "" || $username === "" || $numberOfDays === false || $numberOfDays < 0) {
            $employeeMessage = "Employee name, username, and a valid non-negative number of days are required.";
            $employeeMessageType = "error";
        } elseif ($password === "") {
            $employeeMessage = "Password is required for a new account.";
            $employeeMessageType = "error";
        } else {
            $existsStmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username = ? LIMIT 1");
            if (!$existsStmt) {
                $employeeMessage = "Unable to validate username.";
                $employeeMessageType = "error";
            } else {
                mysqli_stmt_bind_param($existsStmt, "s", $username);
                mysqli_stmt_execute($existsStmt);
                $existsResult = mysqli_stmt_get_result($existsStmt);
                if (mysqli_num_rows($existsResult) > 0) {
                    $employeeMessage = "This username is already in use.";
                    $employeeMessageType = "error";
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $insertStmt = mysqli_prepare($conn, "INSERT INTO users (full_name, username, password_hash, role, is_active, number_of_days) VALUES (?, ?, ?, ?, ?, ?)");
                    if ($insertStmt) {
                        mysqli_stmt_bind_param($insertStmt, "ssssii", $fullName, $username, $hashedPassword, $role, $isActive, $numberOfDays);
                        if (mysqli_stmt_execute($insertStmt)) {
                            $employeeMessage = "Employee account created successfully.";
                        } else {
                            $employeeMessage = "Could not create employee account.";
                            $employeeMessageType = "error";
                        }
                        mysqli_stmt_close($insertStmt);
                    } else {
                        $employeeMessage = "Unable to create employee record.";
                        $employeeMessageType = "error";
                    }
                }
                mysqli_stmt_close($existsStmt);
            }
        }
    } elseif ($action === "update") {
        if ($userId <= 0 || $fullName === "" || $username === "" || $numberOfDays === false || $numberOfDays < 0) {
            $employeeMessage = "Employee name, username, and a valid non-negative number of days are required.";
            $employeeMessageType = "error";
        } else {
            $checkStmt = mysqli_prepare($conn, "SELECT user_id FROM users WHERE username = ? AND user_id <> ? LIMIT 1");
            if (!$checkStmt) {
                $employeeMessage = "Unable to validate username.";
                $employeeMessageType = "error";
            } else {
                mysqli_stmt_bind_param($checkStmt, "si", $username, $userId);
                mysqli_stmt_execute($checkStmt);
                $duplicateResult = mysqli_stmt_get_result($checkStmt);
                if (mysqli_num_rows($duplicateResult) > 0) {
                    $employeeMessage = "This username is already assigned to another employee.";
                    $employeeMessageType = "error";
                } else {
                    if ($password === "") {
                        $updateStmt = mysqli_prepare($conn, "UPDATE users SET full_name = ?, username = ?, role = ?, is_active = ?, number_of_days = ? WHERE user_id = ?");
                        mysqli_stmt_bind_param($updateStmt, "sssiii", $fullName, $username, $role, $isActive, $numberOfDays, $userId);
                    } else {
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $updateStmt = mysqli_prepare($conn, "UPDATE users SET full_name = ?, username = ?, password_hash = ?, role = ?, is_active = ?, number_of_days = ? WHERE user_id = ?");
                        mysqli_stmt_bind_param($updateStmt, "ssssiii", $fullName, $username, $hashedPassword, $role, $isActive, $numberOfDays, $userId);
                    }

                    if ($updateStmt && mysqli_stmt_execute($updateStmt)) {
                        $employeeMessage = "Employee account updated successfully.";
                    } else {
                        $employeeMessage = "Could not update employee account.";
                        $employeeMessageType = "error";
                    }
                    if ($updateStmt) {
                        mysqli_stmt_close($updateStmt);
                    }
                }
                mysqli_stmt_close($checkStmt);
            }
        }
    } elseif ($action === "toggle") {
        if ($userId > 0) {
            $toggleStmt = mysqli_prepare($conn, "UPDATE users SET is_active = NOT is_active WHERE user_id = ?");
            if ($toggleStmt) {
                mysqli_stmt_bind_param($toggleStmt, "i", $userId);
                mysqli_stmt_execute($toggleStmt);
                mysqli_stmt_close($toggleStmt);
                $employeeMessage = "Employee status updated.";
            } else {
                $employeeMessage = "Could not update employee status.";
                $employeeMessageType = "error";
            }
        }
    } elseif ($action === "delete") {
        if ($userId > 0) {
            $deleteStmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ? AND user_id <> ?");
            if ($deleteStmt) {
                $currentUserId = (int) ($_SESSION["user_id"] ?? 0);
                mysqli_stmt_bind_param($deleteStmt, "ii", $userId, $currentUserId);
                if (mysqli_stmt_execute($deleteStmt) && mysqli_stmt_affected_rows($deleteStmt) > 0) {
                    $employeeMessage = "Employee account deleted successfully.";
                } else {
                    $employeeMessage = "Delete failed. The account may be protected or already removed.";
                    $employeeMessageType = "error";
                }
                mysqli_stmt_close($deleteStmt);
            } else {
                $employeeMessage = "Could not delete employee account.";
                $employeeMessageType = "error";
            }
        }
    }

    if (in_array($action, ["create", "update", "toggle", "delete"], true)) {
        $_SESSION["employee_message"] = $employeeMessage !== "" ? $employeeMessage : "Action completed.";
        $_SESSION["employee_message_type"] = $employeeMessageType;
        header("Location: employee.php");
        exit;
    }
}

// Fetch employee accounts for role display and CRUD
$employeeList = [];
$employeeQuery = mysqli_query($conn, "SELECT user_id, full_name, username, role, is_active, number_of_days, created_at FROM users WHERE role IN ('admin', 'manager', 'cashier', 'employee') ORDER BY is_active DESC, full_name ASC");
if ($employeeQuery) {
    while ($row = mysqli_fetch_assoc($employeeQuery)) {
        $employeeList[] = $row;
    }
}

$totalEmployees = count($employeeList);
$activeEmployees = 0;
foreach ($employeeList as $employee) {
    if ((int) $employee["is_active"] === 1) {
        $activeEmployees++;
    }
}

$page_title = "Employee Accounts";
$page_description = "Manage employee accounts and roles";
$full_name = $_SESSION["full_name"] ?? "Administrator";
$username = $_SESSION["username"] ?? "admin";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Nooma</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<?php include __DIR__ . "/../Components/alert.php"; ?>
<?php include "../Components/sidebar.php"; ?>
<div class="lg:ml-64 min-h-screen">
    <?php include "../Components/navbar.php"; ?>

    <main class="p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Employee Management</h2>
            <p class="text-sm text-gray-500 mt-1">Manage staff profiles, usernames, access roles, and account status.</p>
        </div>

        <div class="grid md:grid-cols-3 gap-5 mb-6">
            <div class="bg-white rounded-2xl border p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Employees</p>
                <h3 class="text-3xl font-bold mt-2"><?= number_format($totalEmployees) ?></h3>
            </div>
            <div class="bg-white rounded-2xl border p-5 shadow-sm">
                <p class="text-sm text-gray-500">Active Accounts</p>
                <h3 class="text-3xl font-bold mt-2 text-emerald-600"><?= number_format($activeEmployees) ?></h3>
            </div>
            <div class="bg-white rounded-2xl border p-5 shadow-sm">
                <p class="text-sm text-gray-500">Roles</p>
                <h3 class="text-3xl font-bold mt-2 text-blue-600">3</h3>
            </div>
        </div>

        <div class="bg-white border rounded-2xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold"><?= $editEmployee ? "Edit Employee Account" : "Add New Employee" ?></h3>
                <?php if ($editEmployee): ?>
                    <a href="employee.php" class="px-3 py-2 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel Edit</a>
                <?php endif; ?>
            </div>

            <form method="post" class="grid md:grid-cols-2 xl:grid-cols-5 gap-4">
                <?php if ($editEmployee): ?>
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="user_id" value="<?= (int) $editEmployee["user_id"] ?>">
                <?php else: ?>
                    <input type="hidden" name="action" value="create">
                <?php endif; ?>

                <div class="xl:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" name="full_name" required
                        value="<?= htmlspecialchars($editEmployee["full_name"] ?? "") ?>"
                        placeholder="Enter full name"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                    <input type="text" name="username" required
                        value="<?= htmlspecialchars($editEmployee["username"] ?? "") ?>"
                        placeholder="username"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select name="role" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php $selectedRole = $editEmployee["role"] ?? "cashier"; ?>
                        <option value="admin" <?= $selectedRole === "admin" ? "selected" : "" ?>>Admin</option>
                        <option value="manager" <?= $selectedRole === "manager" ? "selected" : "" ?>>Manager</option>
                        <option value="cashier" <?= $selectedRole === "cashier" ? "selected" : "" ?>>Cashier</option>
                        <option value="employee" <?= $selectedRole === "employee" ? "selected" : "" ?>>Employee</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" name="password" placeholder="<?= $editEmployee ? "Leave blank to keep current password" : "Enter password" ?>"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="number_of_days" class="block text-sm font-medium text-gray-700 mb-2">Number of Days</label>
                    <input type="number" id="number_of_days" name="number_of_days" min="0" step="1" required
                        value="<?= htmlspecialchars((string) ($editEmployee["number_of_days"] ?? "0")) ?>"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="md:col-span-2 xl:col-span-5 flex items-center justify-between gap-3 pt-2">
                    <?php if ($editEmployee): ?>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_active" value="1" <?= (int)($editEmployee["is_active"] ?? 0) === 1 ? "checked" : "" ?> class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            Active account
                        </label>
                        <button type="submit" class="px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700">Update Employee</button>
                    <?php else: ?>
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                            <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                            Active account
                        </label>
                        <button type="submit" class="px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700">Create Employee</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-5 border-b">
                <h3 class="text-lg font-bold">Employee List</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">Name</th>
                            <th class="px-5 py-3">Username</th>
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3 text-right">Number of Days</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Created</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if (empty($employeeList)): ?>
                            <tr>
                                <td colspan="7" class="px-5 py-8 text-center text-gray-500">No employee accounts found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($employeeList as $employee): ?>
                                <?php $isActive = (int) $employee["is_active"] === 1; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4 font-semibold"><?= htmlspecialchars($employee["full_name"]) ?></td>
                                    <td class="px-5 py-4"><?= htmlspecialchars($employee["username"]) ?></td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold uppercase">
                                            <?= htmlspecialchars(ucfirst($employee["role"])) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right font-medium">
                                        <?= number_format((int) $employee["number_of_days"]) ?> days
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex px-2.5 py-1 rounded-full <?= $isActive ? "bg-emerald-100 text-emerald-700" : "bg-gray-200 text-gray-700" ?> text-xs font-semibold">
                                            <?= $isActive ? "Active" : "Inactive" ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4"><?= htmlspecialchars($employee["created_at"] ?? "") ?></td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="employee.php?edit_user_id=<?= (int) $employee["user_id"] ?>" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-medium">Edit</a>

                                            <form method="post" class="inline">
                                                <input type="hidden" name="action" value="toggle">
                                                <input type="hidden" name="user_id" value="<?= (int) $employee["user_id"] ?>">
                                                <button type="submit" class="px-3 py-2 rounded-lg <?= $isActive ? "bg-yellow-100 text-yellow-700 hover:bg-yellow-200" : "bg-emerald-100 text-emerald-700 hover:bg-emerald-200" ?> text-xs font-medium">
                                                    <?= $isActive ? "Disable" : "Enable" ?>
                                                </button>
                                            </form>

                                            <form method="post" class="inline" onsubmit="return confirm('Delete this employee account?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="user_id" value="<?= (int) $employee["user_id"] ?>">
                                                <button type="submit" class="px-3 py-2 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 text-xs font-medium">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php if ($employeeMessage !== ""): ?>
    <script>
        window.showToast(<?= json_encode($employeeMessage, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($employeeMessageType === "error" ? "error" : "success") ?>);
    </script>
<?php endif; ?>
</body>
</html>