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


$full_name = $_SESSION["full_name"] ?? "Administrator";
$username = $_SESSION["username"] ?? "admin";


// Page information
$page_title = "Dashboard";
$page_description = "Overview of your business";

require_once __DIR__ . "/../../backend/config/database.php";

$dashboardMoney = static function ($value): float {
    $value = str_replace(",", "", (string) $value);
    return is_numeric($value) ? (float) $value : 0.0;
};

$dashboardReports = [];
$dashboardResult = mysqli_query($conn, "
    SELECT dr.report_id, dr.report_number, dr.report_date, dr.shift_name,
            dr.telegram_declared_total, dr.pos_sales_total, dr.notes,
           COALESCE(payments.payment_total, 0) AS payment_total
    FROM daily_reports dr
    LEFT JOIN (
        SELECT report_id, SUM(amount) AS payment_total
        FROM report_payments
        GROUP BY report_id
    ) payments ON payments.report_id = dr.report_id
    WHERE dr.status <> 'voided'
    ORDER BY dr.report_date DESC, dr.report_id DESC
");

if ($dashboardResult) {
    while ($row = mysqli_fetch_assoc($dashboardResult)) {
        $row["payload"] = json_decode($row["notes"] ?? "", true);
        $row["payload"] = is_array($row["payload"]) ? $row["payload"] : [];
        $dashboardReports[] = $row;
    }
}

$dashboardPayments = [];
$dashboardOwners = [];
$dashboardReportIds = array_map(static fn (array $row): int => (int) $row["report_id"], $dashboardReports);
if ($dashboardReportIds) {
    $dashboardIdList = implode(",", $dashboardReportIds);
    $paymentResult = mysqli_query($conn, "
        SELECT report_id, pm.method_name, SUM(rp.amount) AS total
        FROM report_payments rp
        INNER JOIN payment_methods pm ON pm.payment_method_id = rp.payment_method_id
        WHERE rp.report_id IN ({$dashboardIdList})
        GROUP BY report_id, pm.payment_method_id, pm.method_name
    ");
    while ($row = mysqli_fetch_assoc($paymentResult)) {
        $dashboardPayments[(int) $row["report_id"]][$row["method_name"]] = (float) $row["total"];
    }

    $ownerResult = mysqli_query($conn, "
        SELECT report_id, SUM(amount) AS total
        FROM report_accounts
        WHERE report_id IN ({$dashboardIdList}) AND account_category = 'owners_account'
        GROUP BY report_id
    ");
    while ($row = mysqli_fetch_assoc($ownerResult)) {
        $dashboardOwners[(int) $row["report_id"]] = (float) $row["total"];
    }
}

foreach ($dashboardReports as &$report) {
    $payload = $report["payload"];
    $savedChannel = strtoupper(trim((string) ($payload["saleChannel"] ?? "")));
    $channel = $savedChannel === "NON POS"
        ? "NON POS"
        : ((float) $report["pos_sales_total"] > 0 ? "POS" : "NON POS");
    $payments = $dashboardPayments[(int) $report["report_id"]] ?? [];
    $cashSales = (float) ($payments["Cash"] ?? ($payments["Cash Sales"] ?? 0.0));
    $cardSales = 0.0;
    foreach (["GCash + QR PH", "Gcash + QRPH", "PayMaya", "Maya Terminal", "AMEX", "Visa", "Mastercard", "BancNet", "JCB"] as $method) {
        $cardSales += (float) ($payments[$method] ?? 0.0);
    }
    $breakdownSales = 0.0;
    foreach (["bpi", "easwest", "giftcheck", "cheque"] as $field) {
        $breakdownSales += $dashboardMoney($payload[$field] ?? 0);
    }
    $posSales = $dashboardMoney($payload["reconShortOver"] ?? 0)
        + $dashboardMoney($payload["paidAccounts"] ?? 0)
        + $dashboardMoney($payload["advancePayments"] ?? 0)
        + $cashSales
        + $cardSales
        + $breakdownSales
        + (float) ($dashboardOwners[(int) $report["report_id"]] ?? 0.0);
    $nonPosSales = $channel === "NON POS" ? (float) $report["telegram_declared_total"] : 0.0;
    $grabNet = $dashboardMoney($payload["onlineTips"] ?? 0);
    $report["gross_sale_total"] = $posSales + $nonPosSales + $grabNet;
}
unset($report);

$dashboardTotalSales = array_sum(array_map(static fn (array $row): float => (float) $row["gross_sale_total"], $dashboardReports));
$dashboardTransactions = count($dashboardReports);
$dashboardNetSales = array_sum(array_map(static fn (array $row): float => (float) $row["pos_sales_total"], $dashboardReports));
$dashboardAverageSale = $dashboardTransactions > 0 ? $dashboardTotalSales / $dashboardTransactions : 0.0;
$today = date("Y-m-d");
$dashboardTodayReports = array_filter($dashboardReports, static fn (array $row): bool => $row["report_date"] === $today);
$dashboardTodaySales = array_sum(array_map(static fn (array $row): float => (float) $row["gross_sale_total"], $dashboardTodayReports));
$dashboardOutstanding = array_sum(array_map(static function (array $row): float {
    return max(0.0, (float) $row["gross_sale_total"] - (float) $row["payment_total"]);
}, $dashboardReports));

$dailySeries = [];
$dailyStart = new DateTimeImmutable("-29 days");
for ($offset = 0; $offset < 30; $offset++) {
    $dateKey = $dailyStart->modify("+{$offset} days")->format("Y-m-d");
    $dailySeries[$dateKey] = 0.0;
}

$weeklySeries = [];
$currentWeekStart = new DateTimeImmutable("monday this week");
for ($offset = 11; $offset >= 0; $offset--) {
    $weekStart = $currentWeekStart->modify("-{$offset} weeks");
    $weeklySeries[$weekStart->format("Y-m-d")] = 0.0;
}

$monthlySeries = [];
$currentMonthStart = new DateTimeImmutable("first day of this month");
for ($offset = 11; $offset >= 0; $offset--) {
    $monthStart = $currentMonthStart->modify("-{$offset} months");
    $monthlySeries[$monthStart->format("Y-m")] = 0.0;
}

foreach ($dashboardReports as $report) {
    $reportDate = new DateTimeImmutable($report["report_date"]);
    $saleAmount = (float) $report["gross_sale_total"];
    $dateKey = $reportDate->format("Y-m-d");
    $weekKey = $reportDate->modify("monday this week")->format("Y-m-d");
    $monthKey = $reportDate->format("Y-m");

    if (isset($dailySeries[$dateKey])) {
        $dailySeries[$dateKey] += $saleAmount;
    }
    if (isset($weeklySeries[$weekKey])) {
        $weeklySeries[$weekKey] += $saleAmount;
    }
    if (isset($monthlySeries[$monthKey])) {
        $monthlySeries[$monthKey] += $saleAmount;
    }
}

$dashboardChartData = [
    "daily" => [
        "labels" => array_map(static fn (string $date): string => date("M j", strtotime($date)), array_keys($dailySeries)),
        "values" => array_values($dailySeries)
    ],
    "weekly" => [
        "labels" => array_map(static fn (string $date): string => "Week of " . date("M j", strtotime($date)), array_keys($weeklySeries)),
        "values" => array_values($weeklySeries)
    ],
    "monthly" => [
        "labels" => array_map(static fn (string $month): string => date("M Y", strtotime($month . "-01")), array_keys($monthlySeries)),
        "values" => array_values($monthlySeries)
    ]
];

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

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

        <!-- PUT YOUR DASHBOARD CONTENT HERE -->

        <!-- Welcome -->
        <div
            class="bg-blue-600 rounded-2xl p-6 md:p-8
                   text-white mb-6"
        >

            <p class="text-blue-100 text-sm">
                Welcome back,
            </p>

            <h2 class="text-3xl font-bold mt-1">
                <?= htmlspecialchars($full_name) ?>
            </h2>

            <p class="text-blue-100 mt-2">
                Here's what's happening with your business today.
            </p>

        </div>


       <!-- STATISTICS -->
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

    <!-- Total Sales -->
    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">
                    Total Sales
                </p>

                <h3 class="text-2xl font-bold text-gray-900 mt-2">
                    ₱<?= number_format($dashboardTotalSales, 2) ?>
                </h3>

                <p class="text-sm text-green-600 mt-2">
                    All recorded sales
                </p>
            </div>

            <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.657 0 3 .895 3 2m-3-2V6m0 12v-2m0 0c-1.657 0-3-.895-3-2m3 2c1.657 0 3-.895 3-2m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>


    <!-- Total Transactions -->
    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">
                    Total Transactions
                </p>

                <h3 class="text-2xl font-bold text-gray-900 mt-2">
                    <?= number_format($dashboardTransactions) ?>
                </h3>

                <p class="text-sm text-green-600 mt-2">
                    Non-voided reports
                </p>
            </div>

            <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6M9 13h6M9 17h4" />
                </svg>
            </div>
        </div>
    </div>


    <!-- Net Sales -->
    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">
                    Net Sales
                </p>

                <h3 class="text-2xl font-bold text-gray-900 mt-2">
                    ₱<?= number_format($dashboardNetSales, 2) ?>
                </h3>

                <p class="text-sm text-green-600 mt-2">
                    POS sales total
                </p>
            </div>

            <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
            </div>
        </div>
    </div>


    <!-- Average Sale -->
    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500">
                    Average Sale
                </p>

                <h3 class="text-2xl font-bold text-gray-900 mt-2">
                    ₱<?= number_format($dashboardAverageSale, 2) ?>
                </h3>

                <p class="text-sm text-green-600 mt-2">
                    Average per report
                </p>
            </div>

            <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-orange-600"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">
                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M12 8v8m-4-4h8m5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
        </div>
    </div>

</div>

<!-- SALES OVERVIEW -->
<div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 mb-6">

    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-lg font-bold text-gray-900">
                Total Gross
            </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Total gross by day, week, or month
            </p>
        </div>

        <select id="salesGraphRange" aria-label="Sales graph range"
                class="border rounded-xl px-3 py-2 text-sm font-semibold text-blue-700 bg-blue-50">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly" selected>Monthly</option>
        </select>
    </div>


    <div class="relative h-72">
        <canvas id="salesOverviewChart"></canvas>
    </div>

    <!-- Static bars retained only as a no-script fallback. -->
    <div class="hidden flex items-end justify-between h-64 gap-3">

        <div class="flex flex-col items-center flex-1 h-full justify-end">
            <div class="w-full bg-blue-500 rounded-t-lg" style="height: 35%;"></div>
            <span class="text-xs text-gray-500 mt-2">Jan</span>
        </div>

        <div class="flex flex-col items-center flex-1 h-full justify-end">
            <div class="w-full bg-blue-500 rounded-t-lg" style="height: 48%;"></div>
            <span class="text-xs text-gray-500 mt-2">Feb</span>
        </div>

        <div class="flex flex-col items-center flex-1 h-full justify-end">
            <div class="w-full bg-blue-500 rounded-t-lg" style="height: 42%;"></div>
            <span class="text-xs text-gray-500 mt-2">Mar</span>
        </div>

        <div class="flex flex-col items-center flex-1 h-full justify-end">
            <div class="w-full bg-blue-500 rounded-t-lg" style="height: 58%;"></div>
            <span class="text-xs text-gray-500 mt-2">Apr</span>
        </div>

        <div class="flex flex-col items-center flex-1 h-full justify-end">
            <div class="w-full bg-blue-500 rounded-t-lg" style="height: 65%;"></div>
            <span class="text-xs text-gray-500 mt-2">May</span>
        </div>

        <div class="flex flex-col items-center flex-1 h-full justify-end">
            <div class="w-full bg-blue-500 rounded-t-lg" style="height: 55%;"></div>
            <span class="text-xs text-gray-500 mt-2">Jun</span>
        </div>

        <div class="flex flex-col items-center flex-1 h-full justify-end">
            <div class="w-full bg-blue-500 rounded-t-lg" style="height: 72%;"></div>
            <span class="text-xs text-gray-500 mt-2">Jul</span>
        </div>

        <div class="flex flex-col items-center flex-1 h-full justify-end">
            <div class="w-full bg-blue-500 rounded-t-lg" style="height: 78%;"></div>
            <span class="text-xs text-gray-500 mt-2">Aug</span>
        </div>

        <div class="flex flex-col items-center flex-1 h-full justify-end">
            <div class="w-full bg-blue-500 rounded-t-lg" style="height: 88%;"></div>
            <span class="text-xs text-gray-500 mt-2">Sep</span>
        </div>

        <div class="flex flex-col items-center flex-1 h-full justify-end">
            <div class="w-full bg-blue-500 rounded-t-lg" style="height: 68%;"></div>
            <span class="text-xs text-gray-500 mt-2">Oct</span>
        </div>

        <div class="flex flex-col items-center flex-1 h-full justify-end">
            <div class="w-full bg-blue-500 rounded-t-lg" style="height: 82%;"></div>
            <span class="text-xs text-gray-500 mt-2">Nov</span>
        </div>

        <div class="flex flex-col items-center flex-1 h-full justify-end">
            <div class="w-full bg-blue-500 rounded-t-lg" style="height: 95%;"></div>
            <span class="text-xs text-gray-500 mt-2">Dec</span>
        </div>

    </div>

</div>


<!-- RECENT TRANSACTIONS -->
<div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">

    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center justify-between">

            <div>
                <h3 class="text-lg font-bold text-gray-900">
                    Recent Transactions
                </h3>

                <p class="text-sm text-gray-500 mt-1">
                    Latest sales transactions
                </p>
            </div>

            <a href="../Sales/sales.php"
               class="text-sm font-semibold text-blue-600 hover:text-blue-700">
                View All
            </a>

        </div>
    </div>


    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead class="bg-gray-50 border-b border-gray-200">

                <tr>

                    <th class="text-left px-6 py-4 font-semibold text-gray-600">
                        Transaction
                    </th>

                    <th class="text-left px-6 py-4 font-semibold text-gray-600">
                        Table
                    </th>

                    <th class="text-left px-6 py-4 font-semibold text-gray-600">
                        Type
                    </th>

                    <th class="text-right px-6 py-4 font-semibold text-gray-600">
                        Amount
                    </th>

                    <th class="text-center px-6 py-4 font-semibold text-gray-600">
                        Status
                    </th>

                </tr>

            </thead>

            <tbody class="divide-y divide-gray-100">
                <?php foreach (array_slice($dashboardReports, 0, 5) as $report): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">
                                #<?= htmlspecialchars($report["report_number"]) ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                <?= htmlspecialchars(date("M j, Y", strtotime($report["report_date"]))) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-600">Report <?= (int) $report["report_id"] ?></td>
                        <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($report["shift_name"]) ?></td>
                        <td class="px-6 py-4 text-right font-semibold">₱<?= number_format((float) $report["gross_sale_total"], 2) ?></td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">Recorded</span>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (false): ?>

                <tr class="hover:bg-gray-50">

                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900">
                            #SALE-1001
                        </div>

                        <div class="text-xs text-gray-500">
                            Today, 10:24 AM
                        </div>
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        Table 05
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        Lunch
                    </td>

                    <td class="px-6 py-4 text-right font-semibold">
                        ₱4,250.00
                    </td>

                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            Paid
                        </span>
                    </td>

                </tr>


                <tr class="hover:bg-gray-50">

                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900">
                            #SALE-1000
                        </div>

                        <div class="text-xs text-gray-500">
                            Today, 9:48 AM
                        </div>
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        Table 02
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        Lunch
                    </td>

                    <td class="px-6 py-4 text-right font-semibold">
                        ₱2,180.00
                    </td>

                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            Paid
                        </span>
                    </td>

                </tr>


                <tr class="hover:bg-gray-50">

                    <td class="px-6 py-4">
                        <div class="font-semibold text-gray-900">
                            #SALE-0999
                        </div>

                        <div class="text-xs text-gray-500">
                            Yesterday, 8:32 PM
                        </div>
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        Table 08
                    </td>

                    <td class="px-6 py-4 text-gray-600">
                        Closing
                    </td>

                    <td class="px-6 py-4 text-right font-semibold">
                        ₱6,420.00
                    </td>

                    <td class="px-6 py-4 text-center">
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-semibold">
                            Pending
                        </span>
                    </td>

                </tr>

                <?php endif; ?>
            </tbody>

        </table>

    </div>

</div>


    </main>


<!-- FOOTER -->

<script>
const dashboardChartData = <?= json_encode($dashboardChartData, JSON_UNESCAPED_SLASHES) ?>;
const salesGraphRange = document.getElementById("salesGraphRange");
const salesOverviewChart = document.getElementById("salesOverviewChart");

function pesoValue(value) {
    return "₱" + Number(value).toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function renderSalesChart(range) {
    const selectedData = dashboardChartData[range];
    if (!selectedData || !salesOverviewChart) {
        return;
    }

    if (window.salesChart) {
        window.salesChart.destroy();
    }

    window.salesChart = new Chart(salesOverviewChart, {
        type: "line",
        data: {
            labels: selectedData.labels,
            datasets: [{
                label: "Total Gross",
                data: selectedData.values,
                borderColor: "#2563eb",
                backgroundColor: "rgba(37, 99, 235, 0.12)",
                borderWidth: 3,
                pointBackgroundColor: "#ffffff",
                pointBorderColor: "#2563eb",
                pointBorderWidth: 2,
                pointRadius: 4,
                tension: 0.35,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { position: "bottom" },
                tooltip: { callbacks: { label: context => "Total Gross: " + pesoValue(context.raw) } }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: value => pesoValue(value) },
                    grid: { color: "rgba(148, 163, 184, 0.2)" }
                },
                x: { grid: { display: false } }
            }
        }
    });
}

salesGraphRange.addEventListener("change", event => renderSalesChart(event.target.value));
renderSalesChart(salesGraphRange.value);
</script>

<?php include "../Components/footer.php"; ?>

