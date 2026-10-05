<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../Login/login.php");
    exit;
}

$full_name = $_SESSION["full_name"] ?? "Administrator";
$username = $_SESSION["username"] ?? "admin";
$page_title = "Unpaid Accounts";
$page_description = "Manage unpaid accounts";

require_once __DIR__ . "/../../backend/config/database.php";

$hasPaidNote = static function ($note): bool {
    return preg_match('/(?:^|[^a-z])paid(?:$|[^a-z])/i', (string) $note) === 1;
};

// Get all sales with unpaid accounts
$unpaidAccounts = [];
$stmt = mysqli_prepare($conn, "SELECT report_id, report_number, report_date, shift_name, notes FROM daily_reports WHERE status <> 'voided' ORDER BY report_date DESC, report_id DESC");
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $payload = json_decode($row["notes"] ?? "", true);
    if (is_array($payload)) {
        // Check for unpaid owner accounts
        foreach (($payload["ownerAccounts"] ?? []) as $accountId => $amount) {
            $note = $payload["ownerNote_" . $accountId] ?? "";
            if (stripos($note, "unpaid") !== false && !$hasPaidNote($note) && $amount > 0) {
                $unpaidAccounts[] = [
                    "report_id" => $row["report_id"],
                    "report_number" => $row["report_number"],
                    "report_date" => $row["report_date"],
                    "shift_name" => $row["shift_name"],
                    "account_type" => "owner",
                    "account_id" => $accountId,
                    "amount" => $amount,
                    "note" => $note
                ];
            }
        }
        
        // Check for unpaid non-owner accounts
        $unpaidAccountNames = [];
        if (!empty($payload["unpaidAccountName"])) {
            $unpaidAccountNames["unpaidAccountName"] = $payload["unpaidAccountName"];
        }
        foreach ($payload as $field => $value) {
            if (preg_match('/^unpaidAccountName_(\d+)$/', $field)) {
                $unpaidAccountNames[$field] = $value;
            }
        }
        
        foreach ($unpaidAccountNames as $nameField => $accountName) {
            $accountIndex = $nameField === "unpaidAccountName" ? "" : substr($nameField, strlen("unpaidAccountName_"));
            $amountField = $accountIndex === "" ? "unpaidAccountAmount" : "unpaidAccountAmount_" . $accountIndex;
            $noteField = $accountIndex === "" ? "unpaidAccountNote" : "unpaidAccountNote_" . $accountIndex;
            $accountName = trim((string) $accountName);
            $accountAmount = (float) str_replace(",", "", (string) ($payload[$amountField] ?? 0));
            $accountNote = $payload[$noteField] ?? "";
            
            if ($accountName !== "" && $accountAmount > 0 && !$hasPaidNote($accountNote)) {
                $unpaidAccounts[] = [
                    "report_id" => $row["report_id"],
                    "report_number" => $row["report_number"],
                    "report_date" => $row["report_date"],
                    "shift_name" => $row["shift_name"],
                    "account_type" => "non-owner",
                    "account_name" => $accountName,
                    "amount" => $accountAmount,
                    "field_index" => $accountIndex
                ];
            }
        }
    }
}
mysqli_stmt_close($stmt);

// Get owner account names for display
$ownerAccountNames = [];
$ownerNamesResult = mysqli_query($conn, "SELECT account_holder_id, account_name FROM account_holders WHERE account_type = 'owner' AND is_active = 1");
if ($ownerNamesResult) {
    while ($row = mysqli_fetch_assoc($ownerNamesResult)) {
        $ownerAccountNames[$row["account_holder_id"]] = $row["account_name"];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unpaid Accounts - Nooma</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<?php include "../Components/sidebar.php"; ?>
<div class="lg:ml-64 min-h-screen">
    <?php include "../Components/navbar.php"; ?>
    <main class="p-4 md:p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold">Unpaid Accounts</h2>
            <p class="text-sm text-gray-500 mt-1">Manage and track unpaid accounts</p>
        </div>

        <div class="bg-white border-2 border-gray-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-blue-100 border-b-2 border-gray-800">
                            <th class="px-5 py-3 text-left">Date</th>
                            <th class="px-5 py-3 text-left">Shift</th>
                            <th class="px-5 py-3 text-left">Account Type</th>
                            <th class="px-5 py-3 text-left">Account Name</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                            <th class="px-5 py-3 text-left">Notes</th>
                            <th class="px-5 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($unpaidAccounts)): ?>
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-gray-500">
                                    No unpaid accounts found.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($unpaidAccounts as $account): ?>
                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <?= date("F d, Y", strtotime($account["report_date"])) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?= htmlspecialchars($account["shift_name"]) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="px-2 py-1 rounded text-xs font-medium 
                                            <?= $account["account_type"] === "owner" ? "bg-purple-100 text-purple-800" : "bg-orange-100 text-orange-800" ?>">
                                            <?= ucfirst($account["account_type"]) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?php if ($account["account_type"] === "owner"): ?>
                                            <?= htmlspecialchars($ownerAccountNames[$account["account_id"]] ?? "Owner account #" . $account["account_id"]) ?>
                                        <?php else: ?>
                                            <?= htmlspecialchars($account["account_name"]) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold">
                                        ₱<?= number_format($account["amount"], 2) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?php if ($account["account_type"] === "owner"): ?>
                                            <?= htmlspecialchars($account["note"]) ?>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        <button 
                                            type="button"
                                            class="mark-as-paid px-3 py-1.5 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700"
                                            data-report-id="<?= (int) $account["report_id"] ?>"
                                            data-account-type="<?= htmlspecialchars($account["account_type"]) ?>"
                                            <?php if ($account["account_type"] === "owner"): ?>
                                                data-account-id="<?= (int) $account["account_id"] ?>"
                                            <?php else: ?>
                                                data-field-index="<?= htmlspecialchars($account["field_index"]) ?>"
                                            <?php endif; ?>
                                        >
                                            Mark as Paid
                                        </button>
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
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.mark-as-paid').forEach(button => {
        button.addEventListener('click', async function() {
            const reportId = this.dataset.reportId;
            const accountType = this.dataset.accountType;
            
            if (!confirm('Are you sure you want to mark this account as paid?')) {
                return;
            }
            
            const formData = new FormData();
            formData.append('report_id', reportId);
            formData.append('account_type', accountType);
            
            if (accountType === 'owner') {
                formData.append('account_id', this.dataset.accountId);
            } else {
                formData.append('field_index', this.dataset.fieldIndex);
            }
            
            try {
                const response = await fetch('../../backend/unpaidAccount/mark_as_paid.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Failed to mark as paid');
                }
                
                window.showToast(result.message || 'Account marked as paid successfully!');
                setTimeout(() => window.location.reload(), 1400);
            } catch (error) {
                window.showToast(error.message, 'error');
            }
        });
    });
});
</script>
</body>
</html>