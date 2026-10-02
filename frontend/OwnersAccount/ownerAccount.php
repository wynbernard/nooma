<?php

session_start();

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

require_once __DIR__ . "/../../backend/config/database.php";

$ownerMessage = "";
$ownerMessageType = "success";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";
    $accountId = (int) ($_POST["account_holder_id"] ?? 0);
    $accountName = trim((string) ($_POST["account_name"] ?? ""));

    if ($action === "create" || $action === "update") {
        if ($accountName === "") {
            $ownerMessage = "Owner name is required.";
            $ownerMessageType = "error";
        } elseif ($action === "create") {
            $stmt = mysqli_prepare($conn, "INSERT INTO account_holders (account_name, account_type, is_active) VALUES (?, 'owner', 1)");
            mysqli_stmt_bind_param($stmt, "s", $accountName);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $ownerMessage = "Owner account added successfully.";
        } elseif ($accountId > 0) {
            $stmt = mysqli_prepare($conn, "UPDATE account_holders SET account_name = ? WHERE account_holder_id = ? AND account_type = 'owner'");
            mysqli_stmt_bind_param($stmt, "si", $accountName, $accountId);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $ownerMessage = "Owner account updated successfully.";
        }
    } elseif ($action === "toggle" && $accountId > 0) {
        $stmt = mysqli_prepare($conn, "UPDATE account_holders SET is_active = NOT is_active WHERE account_holder_id = ? AND account_type = 'owner'");
        mysqli_stmt_bind_param($stmt, "i", $accountId);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $ownerMessage = "Owner account status updated.";
    }
}

$owner_accounts = [];
$ownerResult = mysqli_query($conn, "SELECT account_holder_id, account_name, account_type, is_active, created_at FROM account_holders WHERE account_type = 'owner' ORDER BY is_active DESC, account_name ASC");
if ($ownerResult) {
    while ($row = mysqli_fetch_assoc($ownerResult)) {
        $owner_accounts[] = $row;
    }
}

// Calculate total owner account amounts from all sales
$totalOwnerAccountAmount = 0.0;
$totalOwnerAccountsResult = mysqli_query($conn, "SELECT SUM(ra.amount) as total FROM report_accounts ra INNER JOIN account_holders ah ON ah.account_holder_id = ra.account_holder_id WHERE ah.account_type = 'owner' AND ra.account_category = 'owners_account'");
if ($totalOwnerAccountsResult) {
    $row = mysqli_fetch_assoc($totalOwnerAccountsResult);
    $totalOwnerAccountAmount = (float) ($row["total"] ?? 0);
}
mysqli_free_result($totalOwnerAccountsResult);

// Calculate individual owner account totals
$ownerAccountTotals = [];
foreach ($owner_accounts as $account) {
    $accountId = (int) $account["account_holder_id"];
    $accountTotalStmt = mysqli_prepare($conn, "SELECT SUM(ra.amount) as total FROM report_accounts ra WHERE ra.account_holder_id = ? AND ra.account_category = 'owners_account'");
    mysqli_stmt_bind_param($accountTotalStmt, "i", $accountId);
    mysqli_stmt_execute($accountTotalStmt);
    $accountTotalResult = mysqli_stmt_get_result($accountTotalStmt);
    if ($accountTotalResult) {
        $row = mysqli_fetch_assoc($accountTotalResult);
        $ownerAccountTotals[$accountId] = (float) ($row["total"] ?? 0);
    }
    mysqli_stmt_close($accountTotalStmt);
}

$full_name = $_SESSION["full_name"] ?? "Administrator";
$username = $_SESSION["username"] ?? "admin";


// Page information
$page_title = "Dashboard";
$page_description = "Overview of your business";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title><?= htmlspecialchars($page_title) ?> - Nooma</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../Assets/js/ownerAccount.js"></script>

</head>


<body class="bg-gray-50 text-gray-900">


<!-- SIDEBAR -->

<?php include "../Components/sidebar.php"; ?>


<!-- MAIN -->

<div class="lg:ml-64 min-h-screen">


    <!-- NAVBAR -->

    <?php include "../Components/navbar.php"; ?>


    <!-- CONTENT -->

    <main class="p-6">

       <!-- OWNER'S ACCOUNT -->

        <div class="bg-white rounded-2xl border border-gray-200 p-6">

            <!-- Header -->
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800">
                    Owner's Account
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Manage business owners and their contributed capital.
                </p>
            </div>

            <!-- Total Summary Card -->
            <div class="mb-6 bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Owner Accounts</p>
                        <p class="text-3xl font-bold mt-1">₱<?= number_format($totalOwnerAccountAmount, 2) ?></p>
                    </div>
                    <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                        <span class="text-2xl">💰</span>
                    </div>
                </div>
            </div>

            <?php if ($ownerMessage !== ""): ?>
                <div class="mb-5 rounded-xl border px-4 py-3 <?= $ownerMessageType === "error" ? "border-red-200 bg-red-50 text-red-700" : "border-emerald-200 bg-emerald-50 text-emerald-700" ?>">
                    <?= htmlspecialchars($ownerMessage) ?>
                </div>
            <?php endif; ?>

            <form method="post" class="mb-6 flex flex-wrap items-end gap-3 border-b pb-6">
                <input type="hidden" name="action" value="create">
                <div class="flex-1 min-w-64">
                    <label for="newOwnerName" class="block text-sm font-semibold text-gray-700 mb-2">Owner Name</label>
                    <input type="text" id="newOwnerName" name="account_name" required placeholder="Enter owner name"
                           class="w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <button type="submit" class="px-5 py-3 rounded-xl bg-blue-600 text-white font-semibold hover:bg-blue-700">Add Owner</button>
            </form>

            <div class="overflow-x-auto mb-8">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-4 py-3">Owner Name</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Total Amount</th>
                            <th class="px-4 py-3">Created</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($owner_accounts as $account): ?>
                            <?php $editFormId = "edit-owner-" . (int) $account["account_holder_id"]; ?>
                            <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <form id="<?= $editFormId ?>" method="post">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="account_holder_id" value="<?= (int) $account["account_holder_id"] ?>">
                                            <input type="text" name="account_name" required value="<?= htmlspecialchars($account["account_name"]) ?>"
                                                   class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                        </form>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold <?= $account["is_active"] ? "bg-emerald-100 text-emerald-700" : "bg-gray-100 text-gray-600" ?>">
                                            <?= $account["is_active"] ? "Active" : "Inactive" ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-right">
                                        ₱<?= number_format($ownerAccountTotals[$account["account_holder_id"]] ?? 0, 2) ?>
                                    </td>
                                    <td class="px-4 py-3 text-gray-500"><?= htmlspecialchars(date("M j, Y", strtotime($account["created_at"]))) ?></td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <button type="submit" form="<?= $editFormId ?>" class="px-3 py-2 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700">Save</button>
                                        <form method="post" class="inline">
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="account_holder_id" value="<?= (int) $account["account_holder_id"] ?>">
                                            <button type="submit" class="ml-2 px-3 py-2 rounded-lg <?= $account["is_active"] ? "bg-red-100 text-red-700 hover:bg-red-200" : "bg-emerald-100 text-emerald-700 hover:bg-emerald-200" ?> text-xs font-semibold">
                                                <?= $account["is_active"] ? "Deactivate" : "Activate" ?>
                                            </button>
                                        </form>
                                    </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="bg-blue-50 border-t-2 border-blue-200">
                        <tr>
                            <td colspan="2" class="px-4 py-3 font-bold text-blue-700">Total Owner Accounts</td>
                            <td class="px-4 py-3 font-bold text-right text-blue-700">₱<?= number_format($totalOwnerAccountAmount, 2) ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>


            <!-- Owner List -->
            <div id="ownerList" class="hidden">
                <?php if (!empty($owner_accounts)): ?>
                    <?php foreach ($owner_accounts as $account): ?>
                        <div class="owner-row grid md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                >
                                    Owner Name
                                </label>

                                <input
                                    type="text"
                                    name="owner_name[]"
                                    value="<?= htmlspecialchars($account['account_name']) ?>"
                                    placeholder="Enter owner name"
                                    class="w-full border border-gray-300 rounded-xl px-4 py-3
                                        focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                >
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-semibold text-gray-700 mb-2"
                                >
                                    Amount
                                </label>

                                <input
                                    type="text"
                                    name="owner_amount[]"
                                    placeholder="0.00"
                                    inputmode="decimal"
                                    class="owner-amount w-full border border-gray-300 rounded-xl px-4 py-3
                                        focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    required
                                >
                            </div>

                            <div class="flex items-end">
                                <button
                                    type="button"
                                    onclick="removeOwner(this)"
                                    class="w-full md:w-auto px-5 py-3 rounded-xl
                                        bg-red-500 text-white
                                        hover:bg-red-600 transition"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="owner-row grid md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label
                                for="ownerName"
                                class="block text-sm font-semibold text-gray-700 mb-2"
                            >
                                Owner Name
                            </label>

                            <input
                                type="text"
                                name="owner_name[]"
                                placeholder="Enter owner name"
                                class="w-full border border-gray-300 rounded-xl px-4 py-3
                                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                        </div>

                        <div>
                            <label
                                class="block text-sm font-semibold text-gray-700 mb-2"
                            >
                                Amount
                            </label>

                            <input
                                type="text"
                                name="owner_amount[]"
                                placeholder="0.00"
                                inputmode="decimal"
                                class="owner-amount w-full border border-gray-300 rounded-xl px-4 py-3
                                    focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                        </div>

                        <div class="flex items-end">
                            <button
                                type="button"
                                onclick="removeOwner(this)"
                                class="w-full md:w-auto px-5 py-3 rounded-xl
                                    bg-red-500 text-white
                                    hover:bg-red-600 transition"
                            >
                                Remove
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>


            <!-- Add Owner -->
            <div class="hidden mb-6">

                <button
                    type="button"
                    onclick="addOwner()"
                    class="px-5 py-3 rounded-xl
                        bg-blue-600 text-white
                        hover:bg-blue-700 transition"
                >
                    + Add Owner
                </button>

            </div>


            <!-- Summary -->
            <div class="hidden border-t border-gray-200 pt-6">

                <div class="grid md:grid-cols-2 gap-4">

                    <!-- Number of Owners -->
                    <div>

                        <label
                            class="block text-sm font-semibold text-gray-700 mb-2"
                        >
                            Number of Owners
                        </label>

                        <input
                            type="text"
                            id="ownerCount"
                            value="1"
                            readonly
                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                                bg-gray-100 font-semibold"
                        >

                    </div>

                    <!-- Total Capital -->
                    <div>

                        <label
                            class="block text-sm font-semibold text-gray-700 mb-2"
                        >
                            Total Owner Capital
                        </label>

                        <input
                            type="text"
                            id="totalOwnerAmount"
                            value="0.00"
                            readonly
                            class="w-full border border-gray-300 rounded-xl px-4 py-3
                                bg-gray-100 font-bold text-gray-800"
                        >

                    </div>

                </div>

            </div>

        </div>


<!-- OWNER ACCOUNT JAVASCRIPT -->

    </main>


<!-- FOOTER -->

<?php include "../Components/footer.php"; ?>

