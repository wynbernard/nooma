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

$firstReportResult = mysqli_query($conn, "SELECT MIN(report_date) AS first_report_date FROM daily_reports WHERE status <> 'voided'");
$firstReport = $firstReportResult ? mysqli_fetch_assoc($firstReportResult) : null;
$defaultStartDate = $firstReport["first_report_date"] ?? date("Y-01-01");

// Default to all available report history through the current month.
$start_date = $_GET["start_date"] ?? $defaultStartDate;
$end_date = $_GET["end_date"] ?? date("Y-m-t");

// Query to get service charges within date range
$stmt = mysqli_prepare($conn, "
    SELECT 
        dr.report_id,
        dr.report_date,
        dr.notes
    FROM daily_reports dr
    WHERE dr.status <> 'voided'
    AND dr.report_date BETWEEN ? AND ?
    ORDER BY dr.report_date DESC, dr.report_id DESC
");

mysqli_stmt_bind_param($stmt, "ss", $start_date, $end_date);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$totalServiceCharge = 0;
$tipAmount = 0;
$totalTransactions = 0;
$monthlyTotals = [];

while ($row = mysqli_fetch_assoc($result)) {
    $payload = json_decode($row["notes"] ?? "", true);
    $serviceCharge = 0;
    
    if (is_array($payload)) {
        // Extract service charge from the notes
        $serviceCharge = (float) str_replace(",", "", (string) ($payload["serviceCharge"] ?? 0));
    }
    
    $saleTipAmount = round($serviceCharge * 0.50, 2);
    $totalServiceCharge += $serviceCharge;
    $tipAmount += $saleTipAmount;
    $totalTransactions++;

    $monthKey = date("Y-m", strtotime($row["report_date"]));
    if (!isset($monthlyTotals[$monthKey])) {
        $monthlyTotals[$monthKey] = [
            "month" => date("F Y", strtotime($row["report_date"])),
            "transactions" => 0,
            "service_charge" => 0,
            "tip_amount" => 0
        ];
    }
    $monthlyTotals[$monthKey]["transactions"]++;
    $monthlyTotals[$monthKey]["service_charge"] += $serviceCharge;
    $monthlyTotals[$monthKey]["tip_amount"] += $saleTipAmount;
}

mysqli_stmt_close($stmt);
ksort($monthlyTotals);
$monthlyTotals = array_values($monthlyTotals);

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
            <div class="bg-white border border-gray-100 shadow-sm rounded-2xl p-4 flex flex-wrap items-center gap-4">
                <!-- Start Date Input -->
                <div class="flex-1 min-w-[200px]">
                    <label for="start_date" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">Start Date</label>
                    <div class="relative">
                        <input 
                            type="date" 
                            id="start_date" 
                            name="start_date" 
                            value="<?= htmlspecialchars($start_date) ?>"
                            onchange="filterByDateRange()"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none transition-all cursor-pointer"
                        >
                    </div>
                </div>

                <!-- End Date Input -->
                <div class="flex-1 min-w-[200px]">
                    <label for="end_date" class="block text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1.5">End Date</label>
                    <div class="relative">
                        <input 
                            type="date" 
                            id="end_date" 
                            name="end_date" 
                            value="<?= htmlspecialchars($end_date) ?>"
                            onchange="filterByDateRange()"
                            class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm text-gray-800 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none transition-all cursor-pointer"
                        >
                    </div>
                </div>

                <!-- Optional: Reset / Clear Filter Button -->
                <div class="self-end">
                    <button 
                        type="button" 
                        onclick="resetDateFilter()"
                        class="px-3.5 py-2.5 text-sm font-medium text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-all flex items-center gap-1.5"
                        title="Reset Dates"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Reset
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
                <h3 class="text-2xl font-bold mt-2"><?= $totalTransactions ?></h3>
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
                            <th class="px-5 py-3">Month</th>
                            <th class="px-5 py-3 text-right">Transactions</th>
                            <th class="px-5 py-3 text-right">Service Charge</th>
                            <th class="px-5 py-3 text-right">Tips (50%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php if (empty($monthlyTotals)): ?>
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-500">
                                    No reports found in the selected date range.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($monthlyTotals as $month): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-5 py-4 font-semibold">
                                        <?= htmlspecialchars($month["month"]) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <?= number_format($month["transactions"]) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold">
                                        ₱<?= number_format($month["service_charge"], 2) ?>
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold text-green-700">
                                        ₱<?= number_format($month["tip_amount"], 2) ?>
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
function resetDateFilter() {
    window.location.href = "tip.php";
}

function filterByDateRange() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    
    if (!startDate || !endDate) {
        window.showToast('Please select both start and end dates', 'error');
        return;
    }
    
    if (startDate > endDate) {
        window.showToast('Start date must be before end date', 'error');
        return;
    }
    
    // Redirect with date parameters
    window.location.href = `tip.php?start_date=${startDate}&end_date=${endDate}`;
}
</script>
</body>
</html> 