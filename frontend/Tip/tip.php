<?php
session_start();

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../Login/login.php");
    exit;
}

$full_name = $_SESSION["full_name"] ?? "Administrator";
$username = $_SESSION["username"] ?? "admin";
$page_title = "Tips";
$page_description = "View and manage tips from service charges";

require_once __DIR__ . "/../../backend/config/database.php";

// Get date range from GET parameters or default to current month
$start_date = $_GET['start_date'] ?? date('Y-m-01'); // First day of current month
$end_date = $_GET['end_date'] ?? date('Y-m-t'); // Last day of current month

// Query to get service charges within date range
$stmt = mysqli_prepare($conn, "
    SELECT 
        dr.report_id,
        dr.report_date,
        dr.shift_name,
        dr.notes,
        u.full_name as cashier_name
    FROM daily_reports dr
    INNER JOIN users u ON u.user_id = dr.cashier_id
    WHERE dr.status <> 'voided'
    AND dr.report_date BETWEEN ? AND ?
    ORDER BY dr.report_date DESC, dr.report_id DESC
");

mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$totalServiceCharge = 0;
$salesData = [];

while ($row = mysqli_fetch_assoc($result)) {
    $payload = json_decode($row["notes"] ?? "", true);
    $serviceCharge = 0;
    
    if (is_array($payload)) {
        // Extract service charge from the notes
        $serviceCharge = (float) str_replace(",", "", (string) ($payload["serviceCharge"] ?? 0));
    }
    
    if ($serviceCharge > 0) {
        $totalServiceCharge += $serviceCharge;
        $salesData[] = [
            "report_id" => $row["report_id"],
            "report_date" => $row["report_date"],
            "shift_name" => $row["shift_name"],
            "cashier_name" => $row["cashier_name"],
            "service_charge" => $serviceCharge
        ];
    }
}

mysqli_stmt_close($stmt);

// Calculate 50% of total service charge (tip amount)
$tipAmount = $totalServiceCharge * 0.50;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tips - Nooma</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<?php include "../Components/sidebar.php"; ?>
<div class="lg:ml-64 min-h-screen">
    <?php include "../Components/navbar.php"; ?>
    <main class="p-4 md:p-6">
        <div class="mb-6">
            <h2 class="text-2xl font-bold">Tips Management</h2>
            <p class="text-sm text-gray-500 mt-1">View tips from service charges (50% of total service charge)</p>
        </div>

        <!-- Date Range Filter -->
        <div class="bg-white border rounded-2xl shadow-sm p-5 mb-6">
            <div class="flex flex-wrap items-center gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input 
                        type="date" 
                        id="start_date" 
                        name="start_date" 
                        value="<?= htmlspecialchars($start_date) ?>"
                        class="border rounded-xl px-3 py-2 text-sm text-gray-700 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                    >
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input 
                        type="date" 
                        id="end_date" 
                        name="end_date" 
                        value="<?= htmlspecialchars($end_date) ?>"
                        class="border rounded-xl px-3 py-2 text-sm text-gray-700 bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none"
                    >
                </div>
                <div class="self-end">
                    <button 
                        type="button" 
                        onclick="filterByDateRange()"
                        class="px-4 py-2 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700"
                    >
                        Filter
                    </button>
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">
            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Service Charge</p>
                <h3 class="text-2xl font-bold mt-2">₱<?= number_format($totalServiceCharge, 2) ?></h3>
            </div>

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Tips (50% of Service Charge)</p>
                <h3 class="text-2xl font-bold mt-2 text-green-600">₱<?= number_format($tipAmount, 2) ?></h3>
            </div>

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Number of Transactions</p>
                <h3 class="text-2xl font-bold mt-2"><?= count($salesData) ?></h3>
            </div>
        </div>

        <!-- Tips Table -->
        <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
            <div class="p-5 md:p-6 border-b">
                <h3 class="text-lg font-bold">Service Charge Breakdown</h3>
                <p class="text-sm text-gray-500 mt-1">Individual service charges within the selected date range</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Shift</th>
                            <th class="px-5 py-3">Cashier</th>
                            <th class="px-5 py-3 text-right">Service Charge</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if (empty($salesData)): ?>
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-500">
                                    No service charges found in the selected date range.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($salesData as $sale): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4">
                                        <?= date("F d, Y", strtotime($sale["report_date"])) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?= htmlspecialchars($sale["shift_name"]) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <?= htmlspecialchars($sale["cashier_name"]) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold">
                                        ₱<?= number_format($sale["service_charge"], 2) ?>
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
function filterByDateRange() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (!startDate || !endDate) {
        alert('Please select both start and end dates');
        return;
    }
    
    if (startDate > endDate) {
        alert('Start date must be before end date');
        return;
    }
    
    // Redirect with date parameters
    window.location.href = `tip.php?start_date=${startDate}&end_date=${endDate}`;
}
</script>
</body>
</html> 