<?php
session_start();

if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"]) || !in_array($_SESSION["role"], ["admin", "manager", "cashier"], true)) {
    header("Location: ../Login/login.php");
    exit;
}

require_once __DIR__ . "/../../backend/config/database.php";

// Handle AJAX Request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ajax_action"])) {
    header('Content-Type: application/json');
    $action =$_POST["ajax_action"];
    $response = ["success" => false, "message" => "Invalid action."];

    if ($action === "create") {
        $fullName = trim((string) ($_POST["full_name"] ?? ""));
        $role =$_POST["role"] ?? "employee";
        $isActive = isset($_POST["is_active"]) ? 1 : 0;

        if ($fullName === "") {
            $response = ["success" => false, "message" => "Employee name is required."];
        } else {
            $username = "emp_" . time() . "_" . mt_rand(1000, 9999);
            $randomPassword = password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);$defaultDays = 0;

            $insertStmt = mysqli_prepare($conn, "INSERT INTO users (full_name, username, password_hash, role, is_active, number_of_days) VALUES (?, ?, ?, ?, ?, ?)");
            if ($insertStmt) {
                mysqli_stmt_bind_param($insertStmt, "ssssii", $fullName,$username, $randomPassword,$role, $isActive,$defaultDays);
                if (mysqli_stmt_execute($insertStmt)) {$response = ["success" => true, "message" => "Employee record created successfully."];
                } else {
                    $response = ["success" => false, "message" => "Could not create employee record."];
                }
                mysqli_stmt_close($insertStmt);
            } else {
                $response = ["success" => false, "message" => "Unable to prepare employee record insertion."];
            }
        }
    } elseif ($action === "update") {
        $userId = (int) ($_POST["user_id"] ?? 0);
        $fullName = trim((string) ($_POST["full_name"] ?? ""));
        $role =$_POST["role"] ?? "employee";
        $isActive = isset($_POST["is_active"]) ? 1 : 0;

        if ($userId <= 0 ||$fullName === "") {
            $response = ["success" => false, "message" => "Employee name is required."];
        } else {
            $updateStmt = mysqli_prepare($conn, "UPDATE users SET full_name = ?, role = ?, is_active = ? WHERE user_id = ?");
            if ($updateStmt) {
                mysqli_stmt_bind_param($updateStmt, "ssii", $fullName,$role, $isActive,$userId);
                if (mysqli_stmt_execute($updateStmt)) {$response = ["success" => true, "message" => "Employee record updated successfully."];
                } else {
                    $response = ["success" => false, "message" => "Could not update employee record."];
                }
                mysqli_stmt_close($updateStmt);
            } else {
                $response = ["success" => false, "message" => "Unable to prepare employee record update."];
            }
        }
    } elseif ($action === "toggle") {
        $userId = (int) ($_POST["user_id"] ?? 0);
        if ($userId > 0) {
            $toggleStmt = mysqli_prepare($conn, "UPDATE users SET is_active = NOT is_active WHERE user_id = ?");
            if ($toggleStmt) {
                mysqli_stmt_bind_param($toggleStmt, "i", $userId);
                mysqli_stmt_execute($toggleStmt);
                mysqli_stmt_close($toggleStmt);$response = ["success" => true, "message" => "Employee status updated."];
            } else {
                $response = ["success" => false, "message" => "Could not update employee status."];
            }
        }
    } elseif ($action === "delete") {
        $userId = (int) ($_POST["user_id"] ?? 0);
        if ($userId > 0) {
            $deleteStmt = mysqli_prepare($conn, "DELETE FROM users WHERE user_id = ? AND user_id <> ?");
            if ($deleteStmt) {
                $currentUserId = (int) ($_SESSION["user_id"] ?? 0);
                mysqli_stmt_bind_param($deleteStmt, "ii", $userId,$currentUserId);
                if (mysqli_stmt_execute($deleteStmt) && mysqli_stmt_affected_rows($deleteStmt) > 0) {$response = ["success" => true, "message" => "Employee account deleted successfully."];
                } else {
                    $response = ["success" => false, "message" => "Delete failed. The account may be protected or already removed."];
                }
                mysqli_stmt_close($deleteStmt);
            } else {
                $response = ["success" => false, "message" => "Could not delete employee account."];
            }
        }
    }

    // Return updated list data for DOM refresh
    if ($response["success"]) {
        $employeeList = [];
        $employeeQuery = mysqli_query($conn, "SELECT user_id, full_name, role, is_active, created_at FROM users WHERE role = 'employee' ORDER BY is_active DESC, SUBSTRING_INDEX(full_name, ' ', -1) ASC, full_name ASC");
        if ($employeeQuery) {
            while ($row = mysqli_fetch_assoc($employeeQuery)) {
                $employeeList[] =$row;
            }
        }
        $response["employeeList"] = $employeeList;
        $response["totalEmployees"] = count($employeeList);$activeCount = 0;
        foreach ($employeeList as$e) {
            if ((int)$e["is_active"] === 1) {
                $activeCount++;
            }
        }
        $response["activeEmployees"] = $activeCount;
    }

    echo json_encode($response);
    exit;
}

// Helper function to format name as "Last Name, First Name"
function formatLastNameFirst($fullName) {
    $fullName = trim($fullName);
    if ($fullName === '') return '';
    $parts = preg_split('/\s+/',$fullName);
    if (count($parts) <= 1) {
        return $fullName;
    }
    $lastName = array_pop($parts);
    $firstNames = implode(' ',$parts);
    return $lastName . ', ' .$firstNames;
}

// Fetch initial data for regular load
$editEmployee = null;
if (isset($_GET["edit_user_id"])) {
    $editEmployeeId = (int)$_GET["edit_user_id"];
    if ($editEmployeeId > 0) {
        $editStmt = mysqli_prepare($conn, "SELECT user_id, full_name, role, is_active FROM users WHERE user_id = ? LIMIT 1");
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

$employeeList = [];
$employeeQuery = mysqli_query($conn, "SELECT user_id, full_name, role, is_active, created_at FROM users WHERE role = 'employee' ORDER BY is_active DESC, SUBSTRING_INDEX(full_name, ' ', -1) ASC, full_name ASC");
if ($employeeQuery) {
    while ($row = mysqli_fetch_assoc($employeeQuery)) {
        $employeeList[] =$row;
    }
}

$totalEmployees = count($employeeList);$activeEmployees = 0;
foreach ($employeeList as$e) {
    if ((int)$e["is_active"] === 1) {
        $activeEmployees++;
    }
}

$page_title = "Employee Accounts";
$page_description = "Manage employee accounts and roles";
$full_name =$_SESSION["full_name"] ?? "Administrator";
$username =$_SESSION["username"] ?? "admin";
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
            <p class="text-sm text-gray-500 mt-1">Manage staff profiles, access roles, and account status.</p>
        </div>

        <div class="grid md:grid-cols-2 gap-5 mb-6">
            <div class="bg-white rounded-2xl border p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Employees</p>
                <h3 id="total-employees-count" class="text-3xl font-bold mt-2"><?= number_format($totalEmployees) ?></h3>
            </div>
            <div class="bg-white rounded-2xl border p-5 shadow-sm">
                <p class="text-sm text-gray-500">Active Accounts</p>
                <h3 id="active-employees-count" class="text-3xl font-bold mt-2 text-emerald-600"><?= number_format($activeEmployees) ?></h3>
            </div>
        </div>

        <div class="bg-white border rounded-2xl shadow-sm p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 id="form-title" class="text-lg font-bold"><?= $editEmployee ? "Edit Employee Record" : "Add New Employee" ?></h3>
                <a id="cancel-edit-btn" href="employee.php" class="px-3 py-2 text-sm rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 <?= $editEmployee ? "" : "hidden" ?>">Cancel Edit</a>
            </div>

            <form id="employee-form" class="grid md:grid-cols-2 xl:grid-cols-3 gap-4">
                <input type="hidden" id="form-action" name="ajax_action" value="<?= $editEmployee ? "update" : "create" ?>">
                <input type="hidden" id="form-user-id" name="user_id" value="<?= $editEmployee ? (int) $editEmployee["user_id"] : "" ?>">

                <div class="xl:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                    <input type="text" id="full_name" name="full_name" required
                        value="<?= htmlspecialchars($editEmployee["full_name"] ?? "") ?>"
                        placeholder="Enter full name"
                        class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select id="role" name="role" class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php $selectedRole =$editEmployee["role"] ?? "employee"; ?>
                        <option value="employee" <?= $selectedRole === "employee" ? "selected" : "" ?>>Employee</option>
                        <option value="cashier" <?= $selectedRole === "cashier" ? "selected" : "" ?>>Cashier</option>
                        <option value="manager" <?= $selectedRole === "manager" ? "selected" : "" ?>>Manager</option>
                        <option value="admin" <?= $selectedRole === "admin" ? "selected" : "" ?>>Admin</option>
                    </select>
                </div>

                <div class="md:col-span-2 xl:col-span-3 flex items-center justify-between gap-3 pt-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" id="is_active" name="is_active" value="1" <?= $editEmployee ? ((int)($editEmployee["is_active"] ?? 0) === 1 ? "checked" : "") : "checked" ?> class="h-4 w-4 text-blue-600 border-gray-300 rounded">
                        Active account
                    </label>
                    <button type="submit" id="submit-btn" class="px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700">
                        <?= $editEmployee ? "Update Employee" : "Create Employee" ?>
                    </button>
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
                            <th class="px-5 py-3">Role</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Created</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employee-table-body" class="divide-y">
                        <?php if (empty($employeeList)): ?>
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-gray-500">No employee accounts found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($employeeList as$employee): ?>
                                <?php $isActive = (int)$employee["is_active"] === 1; ?>
                                <tr class="hover:bg-gray-50" data-id="<?= (int)$employee["user_id"] ?>">
                                    <td class="px-5 py-4 font-semibold employee-name"><?= htmlspecialchars(formatLastNameFirst($employee["full_name"])) ?></td>
                                    <td class="px-5 py-4 employee-role">
                                        <span class="inline-flex px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold uppercase">
                                            <?= htmlspecialchars(ucfirst($employee["role"])) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 employee-status">
                                        <span class="inline-flex px-2.5 py-1 rounded-full <?= $isActive ? "bg-emerald-100 text-emerald-700" : "bg-gray-200 text-gray-700" ?> text-xs font-semibold">
                                            <?= $isActive ? "Active" : "Inactive" ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4"><?= htmlspecialchars($employee["created_at"] ?? "") ?></td>
                                    <td class="px-5 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <button type="button" onclick="editEmployeeData(<?= (int)$employee["user_id"] ?>, '<?= htmlspecialchars($employee["full_name"], ENT_QUOTES) ?>', '<?= htmlspecialchars($employee["role"], ENT_QUOTES) ?>', <?= $isActive ? 1 : 0 ?>)" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-medium">Edit</button>

                                            <button type="button" onclick="sendAjaxAction('toggle', <?= (int)$employee["user_id"] ?>)" class="px-3 py-2 rounded-lg <?= $isActive ? "bg-yellow-100 text-yellow-700 hover:bg-yellow-200" : "bg-emerald-100 text-emerald-700 hover:bg-emerald-200" ?> text-xs font-medium">
                                                <?= $isActive ? "Disable" : "Enable" ?>
                                            </button>

                                            <button type="button" onclick="if(confirm('Delete this employee account?')) sendAjaxAction('delete', <?= (int)$employee["user_id"] ?>)" class="px-3 py-2 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 text-xs font-medium">Delete</button>
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

<script>
function editEmployeeData(id, fullName, role, isActive) {
    document.getElementById('form-title').innerText = "Edit Employee Record";
    document.getElementById('form-action').value = "update";
    document.getElementById('form-user-id').value = id;
    document.getElementById('full_name').value = fullName;
    document.getElementById('role').value = role;
    document.getElementById('is_active').checked = isActive === 1;
    document.getElementById('submit-btn').innerText = "Update Employee";
    document.getElementById('cancel-edit-btn').classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('form-title').innerText = "Add New Employee";
    document.getElementById('form-action').value = "create";
    document.getElementById('form-user-id').value = "";
    document.getElementById('full_name').value = "";
    document.getElementById('role').value = "employee";
    document.getElementById('is_active').checked = true;
    document.getElementById('submit-btn').innerText = "Create Employee";
    document.getElementById('cancel-edit-btn').classList.add('hidden');
}

function sendAjaxAction(action, userId) {
    const formData = new URLSearchParams();
    formData.append('ajax_action', action);
    formData.append('user_id', userId);

    fetch('employee.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (window.showToast) window.showToast(data.message, 'success');
            updateTableAndCounts(data);
            resetForm();
        } else {
            if (window.showToast) window.showToast(data.message, 'error');
        }
    })
    .catch(err => {
        if (window.showToast) window.showToast('An error occurred.', 'error');
    });
}

document.getElementById('employee-form').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new URLSearchParams(new FormData(this));

    fetch('employee.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: formData.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            if (window.showToast) window.showToast(data.message, 'success');
            updateTableAndCounts(data);
            resetForm();
        } else {
            if (window.showToast) window.showToast(data.message, 'error');
        }
    })
    .catch(err => {
        if (window.showToast) window.showToast('An error occurred.', 'error');
    });
});

function formatLastNameFirstJS(fullName) {
    const trimmed = fullName.trim();
    if (!trimmed) return '';
    const parts = trimmed.split(/\s+/);
    if (parts.length <= 1) return trimmed;
    const lastName = parts.pop();
    const firstNames = parts.join(' ');
    return `${lastName}, ${firstNames}`;
}

function updateTableAndCounts(data) {
    document.getElementById('total-employees-count').innerText = data.totalEmployees.toLocaleString();
    document.getElementById('active-employees-count').innerText = data.activeEmployees.toLocaleString();

    const tbody = document.getElementById('employee-table-body');
    tbody.innerHTML = '';

    if (!data.employeeList || data.employeeList.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="px-5 py-8 text-center text-gray-500">No employee accounts found.</td></tr>`;
        return;
    }

    // Sort employeeList alphabetically by last name on the frontend
    data.employeeList.sort((a, b) => {
        const getLastName = (name) => {
            const parts = name.trim().split(/\s+/);
            return parts[parts.length - 1] || '';
        };
        return getLastName(a.full_name).localeCompare(getLastName(b.full_name));
    });

    data.employeeList.forEach(emp => {
        const isActive = parseInt(emp.is_active) === 1;
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        tr.setAttribute('data-id', emp.user_id);

        const formattedName = formatLastNameFirstJS(emp.full_name);

        tr.innerHTML = `
            <td class="px-5 py-4 font-semibold employee-name">${escapeHtml(formattedName)}</td>
            <td class="px-5 py-4 employee-role">
                <span class="inline-flex px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold uppercase">
                    ${escapeHtml(emp.role.charAt(0).toUpperCase() + emp.role.slice(1))}
                </span>
            </td>
            <td class="px-5 py-4 employee-status">
                <span class="inline-flex px-2.5 py-1 rounded-full ${isActive ? "bg-emerald-100 text-emerald-700" : "bg-gray-200 text-gray-700"} text-xs font-semibold">
                    ${isActive ? "Active" : "Inactive"}
                </span>
            </td>
            <td class="px-5 py-4">${escapeHtml(emp.created_at || '')}</td>
            <td class="px-5 py-4">
                <div class="flex items-center justify-end gap-2">
                    <button type="button" onclick="editEmployeeData(${emp.user_id}, '${escapeHtml(emp.full_name).replace(/'/g, "\\'")}', '${emp.role}', ${isActive ? 1 : 0})" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs font-medium">Edit</button>

                    <button type="button" onclick="sendAjaxAction('toggle', ${emp.user_id})" class="px-3 py-2 rounded-lg ${isActive ? "bg-yellow-100 text-yellow-700 hover:bg-yellow-200" : "bg-emerald-100 text-emerald-700 hover:bg-emerald-200"} text-xs font-medium">
                        ${isActive ? "Disable" : "Enable"}
                    </button>

                    <button type="button" onclick="if(confirm('Delete this employee account?')) sendAjaxAction('delete', ${emp.user_id})" class="px-3 py-2 rounded-lg bg-red-100 text-red-700 hover:bg-red-200 text-xs font-medium">Delete</button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>
</body>
</html>