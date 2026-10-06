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
                <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-900 text-white text-xs font-semibold tracking-wider uppercase">
                <th class="px-6 py-4">Date</th>
                <th class="px-6 py-4">Shift</th>
                <th class="px-6 py-4">Account Type</th>
                <th class="px-6 py-4">Account Name</th>
                <th class="px-6 py-4 text-right">Amount</th>
                <th class="px-6 py-4">Notes</th>
                <th class="px-6 py-4 text-center">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-sm text-slate-700">
            <?php if (empty($unpaidAccounts)): ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center space-y-3">
                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-slate-500">No unpaid accounts found.</p>
                        </div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($unpaidAccounts as $account): ?>
                    <tr class="transition-colors hover:bg-slate-50/80 group">
                        <!-- Date -->
                        <td class="px-6 py-4 font-medium text-slate-900 whitespace-nowrap">
                            <?= date("F d, Y", strtotime($account["report_date"])) ?>
                        </td>

                        <!-- Shift -->
                        <td class="px-6 py-4 text-slate-600 whitespace-nowrap">
                            <?= htmlspecialchars($account["shift_name"]) ?>
                        </td>

                        <!-- Account Type Badge -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($account["account_type"] === "owner"): ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-50 text-purple-700 border border-purple-200/60">
                                    Owner
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60">
                                    <?= ucfirst($account["account_type"]) ?>
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Account Name -->
                        <td class="px-6 py-4 font-medium text-slate-900">
                            <?php if ($account["account_type"] === "owner"): ?>
                                <?= htmlspecialchars($ownerAccountNames[$account["account_id"]] ?? "Owner account #" . $account["account_id"]) ?>
                            <?php else: ?>
                                <?= htmlspecialchars($account["account_name"]) ?>
                            <?php endif; ?>
                        </td>

                        <!-- Amount -->
                        <td class="px-6 py-4 text-right font-bold text-slate-900 whitespace-nowrap">
                            ₱<?= number_format($account["amount"], 2) ?>
                        </td>

                        <!-- Notes -->
                        <td class="px-6 py-4 text-slate-600 max-w-xs truncate">
                            <?php if ($account["account_type"] === "owner" && !empty($account["note"])): ?>
                                <?= htmlspecialchars($account["note"]) ?>
                            <?php else: ?>
                                <span class="text-slate-300">-</span>
                            <?php endif; ?>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <button 
                                type="button"
                                class="inline-flex items-center justify-center px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-xl shadow-sm hover:shadow transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 active:scale-95"
                                data-report-id="<?= (int) $account["report_id"] ?>"
                                data-account-type="<?= htmlspecialchars($account["account_type"]) ?>"
                                <?php if ($account["account_type"] === "owner"): ?>
                                    data-account-id="<?= (int) $account["account_id"] ?>"
                                <?php else: ?>
                                    data-field-index="<?= htmlspecialchars($account["field_index"]) ?>"
                                <?php endif; ?>
                            >
                                <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                </svg>
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