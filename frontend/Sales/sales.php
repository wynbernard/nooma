
<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

if (($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../login.php");
    exit;
}

$full_name = $_SESSION["full_name"] ?? "Administrator";
$username = $_SESSION["username"] ?? "admin";

$page_title = "Sales";
$page_description = "Manage sales and transactions";

require_once __DIR__ . "/../../backend/config/database.php";

$ownerAccountNames = [];
$ownerAccountNamesResult = mysqli_query($conn, "SELECT account_holder_id, account_name FROM account_holders WHERE account_type = 'owner'");
if ($ownerAccountNamesResult) {
    while ($ownerAccount = mysqli_fetch_assoc($ownerAccountNamesResult)) {
        $ownerAccountNames[(string) $ownerAccount["account_holder_id"]] = $ownerAccount["account_name"];
    }
}

$isPaidNote = static function ($note): bool {
    return preg_match('/(?:^|[^a-z])paid(?:$|[^a-z])/i', (string) $note) === 1;
};

$sales = [];
$salesResult = mysqli_query($conn, "
    SELECT
        dr.report_id,
        dr.report_number,
        dr.report_date,
        dr.shift_name,
        COALESCE(bar_sales.bar_sales_total, 0) AS bar_sales_total,
        COALESCE(kitchen_sales.kitchen_sales_total, 0) AS kitchen_sales_total,
        dr.total_tables,
        dr.total_pax,
        dr.telegram_declared_total,
        dr.pos_sales_total,
        dr.status,
        dr.notes,
        u.full_name,
        COALESCE(payments.payment_total, 0) AS payment_total
    FROM daily_reports dr
    INNER JOIN users u ON u.user_id = dr.cashier_id
    LEFT JOIN (
        SELECT report_id, SUM(amount) AS bar_sales_total
        FROM report_sales
        WHERE sales_type = 'bar'
        GROUP BY report_id
    ) bar_sales ON bar_sales.report_id = dr.report_id
    LEFT JOIN (
        SELECT report_id, SUM(amount) AS kitchen_sales_total
        FROM report_sales
        WHERE sales_type = 'kitchen'
        GROUP BY report_id
    ) kitchen_sales ON kitchen_sales.report_id = dr.report_id
    LEFT JOIN (
        SELECT report_id, SUM(amount) AS payment_total
        FROM report_payments
        GROUP BY report_id
    ) payments ON payments.report_id = dr.report_id
    ORDER BY dr.report_date DESC, dr.report_id DESC
");

if ($salesResult) {
    while ($row = mysqli_fetch_assoc($salesResult)) {
        $sales[] = $row;
    }
}

$nonPosSales = 0.0;
$nonPosTransactions = 0;
foreach ($sales as $sale) {
    $savedFields = json_decode($sale["notes"] ?? "", true);
    $savedChannel = is_array($savedFields)
        ? strtoupper(trim((string) ($savedFields["saleChannel"] ?? "")))
        : "";
    $isNonPos = $savedChannel === "NON POS" || (float) $sale["pos_sales_total"] <= 0;
    if ($isNonPos) {
        $nonPosSales += (float) $sale["telegram_declared_total"];
        $nonPosTransactions++;
    }
}

// Calculate overall statistics (not just current page)
$statsResult = mysqli_query($conn, "
    SELECT
        SUM(dr.pos_sales_total) as total_sales,
        COUNT(*) as total_transactions
    FROM daily_reports dr
");

$totalSales = 0;
$totalTransactions = 0;

if ($statsResult) {
    $stats = mysqli_fetch_assoc($statsResult);
    $totalSales = (float) ($stats['total_sales'] ?? 0);
    $totalTransactions = (int) ($stats['total_transactions'] ?? 0);
}
mysqli_free_result($statsResult);

$existingSaleTypes = [];
$existingSaleTypesResult = mysqli_query($conn, "SELECT report_id, report_date, shift_name FROM daily_reports");
if ($existingSaleTypesResult) {
    while ($row = mysqli_fetch_assoc($existingSaleTypesResult)) {
        $existingSaleTypes[] = [
            "report_id" => (int) $row["report_id"],
            "date" => $row["report_date"],
            "type" => $row["shift_name"]
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Sales - Nooma</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .filter-unpaid .sale-row[data-has-unpaid="false"],
        .filter-unpaid .sale-details-row[data-has-unpaid="false"] {
            display: none !important;
        }
        .filter-pos .sale-row[data-sale-channel="non-pos"],
        .filter-pos .sale-details-row[data-sale-channel="non-pos"],
        .filter-non-pos .sale-row[data-sale-channel="pos"],
        .filter-non-pos .sale-details-row[data-sale-channel="pos"] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-900">

<?php include "../components/sidebar.php"; ?>

<div class="lg:ml-64 min-h-screen">

    <?php include "../components/navbar.php"; ?>

    <main class="p-4 md:p-6">

        <!-- PAGE HEADER -->
        <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
            <div>
                <h2 class="text-2xl font-bold">Sales Management</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Record and manage your sales transactions.
                </p>
            </div>

            <div class="text-sm text-gray-500">
                <?= date("F d, Y") ?>
            </div>
        </div>


        <!-- SALES SUMMARY -->
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5 mb-6">

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Total Sales</p>
                <h3 id="totalSales"
                    class="text-2xl font-bold mt-2">(₱)<?= number_format($totalSales, 2) ?></h3>
            </div>

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Transactions</p>
                <h3 id="totalTransactions"
                    class="text-2xl font-bold mt-2"><?= $totalTransactions ?></h3>  
            </div>

            <div class="bg-white border rounded-2xl p-5 shadow-sm">
                <p class="text-sm text-gray-500">Non-POS Sales</p>
                <h3 id="nonPosSalesTotal"
                    class="text-2xl font-bold mt-2 text-gray-900">₱<?= number_format($nonPosSales, 2) ?></h3>
                <p class="text-xs text-gray-500 mt-1"><?= number_format($nonPosTransactions) ?> Non-POS reports</p>
            </div>

        </div>

        <!-- SALES TABLE -->
       <section class="bg-white border border-gray-100 rounded-2xl shadow-sm mb-6 overflow-hidden">
            <!-- Header & Controls Bar -->
            <div class="p-5 md:p-6 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 tracking-tight">Saved Sales</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Recently recorded daily sales reports and performance analytics.</p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <!-- Search Input -->
                    <div class="relative">
                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">
                            🔍
                        </span>
                        <input
                            type="search"
                            id="searchSales"
                            placeholder="Search sales..."
                            autocomplete="off"
                            class="w-60 border border-gray-200 rounded-xl pl-10 pr-4 py-2 text-sm text-gray-800 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none transition-all"
                        >
                    </div>

                    <!-- Date Filter -->
                    <input
                        type="date"
                        id="filterSalesDate"
                        class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm font-medium text-gray-700 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none transition-all cursor-pointer"
                    >

                    <!-- Channel Filter Select -->
                    <select 
                        id="filterSalesChannel" 
                        aria-label="Filter sales channel"
                        class="border border-gray-200 rounded-xl px-3.5 py-2 text-sm font-medium text-gray-700 bg-gray-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 outline-none transition-all cursor-pointer"
                    >
                        <option value="all">All channels</option>
                        <option value="pos">POS only</option>
                        <option value="non-pos">Non POS only</option>
                    </select>

                    <!-- Add Sale Button -->
                    <button 
                        type="button" 
                        id="openSaleModal"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm shadow-blue-500/20 transition-all flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Add Sale
                    </button>
                </div>
            </div>

            <!-- Table Section -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left border-collapse">
                    <thead class="bg-gray-50/75 text-gray-400 uppercase text-[11px] font-semibold tracking-wider border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-3.5">Date</th>
                            <th class="px-6 py-3.5">Shift</th>
                            <th class="px-6 py-3.5">Cash Remitted</th>
                            <th class="px-6 py-3.5">Kitchen Sale</th>
                            <th class="px-6 py-3.5">Tables / Pax</th>
                            <th class="px-6 py-3.5">Sales</th>
                            <th class="px-6 py-3.5">Unpaid Accounts</th>
                            <th class="px-6 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="salesTableBody" class="divide-y divide-gray-100 text-gray-600">
                        <?php if (empty($sales)): ?>
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400 font-medium">
                                    No saved sales found. Try adjusting your filters or add a new record.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sales as $sale): ?>
                                <?php
                                $saleFields = [];
                                $shortOver = 0.0;
                                $cashRemitted = 0.0;
                                $ownerImages = [];
                                $unpaidSaleAccounts = [];
                                $savedFields = json_decode($sale["notes"] ?? "", true);
                                $saleChannel = "pos";
                                if (is_array($savedFields)) {
                                    $cashRemitted = (float) str_replace(",", "", (string) ($savedFields["cashRemitted"] ?? 0));
                                    $saleChannel = strtoupper(trim((string) ($savedFields["saleChannel"] ?? ""))) === "NON POS"
                                        ? "non-pos"
                                        : ((float) $sale["pos_sales_total"] > 0 ? "pos" : "non-pos");
                                    foreach ($savedFields as $fieldName => $fieldValue) {
                                        if ($fieldName !== "ownerAccounts" && !is_array($fieldValue) && !preg_match('/^owner(Note|Image)_/', $fieldName)) {
                                            $saleFields[$fieldName] = $fieldValue;
                                        }
                                    }
                                    $declaredGrandTotal = (float) str_replace(",", "", (string) ($savedFields["grandTotal"] ?? $sale["telegram_declared_total"] ?? 0));
                                    $totalBasedOnPayments = (float) str_replace(",", "", (string) ($savedFields["totalSalesBasedOnPayment"] ?? $sale["payment_total"] ?? 0));
                                    $shortOver = $declaredGrandTotal - $totalBasedOnPayments;
                                    foreach (($savedFields["ownerAccounts"] ?? []) as $accountId => $amount) {
                                        $note = $savedFields["ownerNote_" . $accountId] ?? "";
                                        $image = $savedFields["ownerImage_" . $accountId] ?? "";
                                        $value = $amount;
                                        if (!empty($note)) {
                                            $value .= " (" . htmlspecialchars($note) . ")";
                                        }
                                        $amountValue = (float) str_replace(",", "", (string) $amount);
                                        if (stripos((string) $note, "unpaid") !== false && !$isPaidNote($note)) {
                                            if ($amountValue > 0) {
                                                $unpaidSaleAccounts[] = [
                                                    "type" => "Owner",
                                                    "name" => $ownerAccountNames[(string) $accountId] ?? ("Owner account #" . $accountId),
                                                    "amount" => $amountValue
                                                ];
                                            }
                                        }
                                        $saleFields["Owner account #" . $accountId] = $value;
                                        if (is_string($image) && preg_match('#^uploads/owner-accounts/[A-Za-z0-9._-]+$#', $image)) {
                                            $ownerImages[$accountId] = $image;
                                        }
                                    }

                                    $unpaidAccountNames = [];
                                    if (!empty($savedFields["unpaidAccountName"])) {
                                        $unpaidAccountNames[""] = $savedFields["unpaidAccountName"];
                                    }
                                    foreach ($savedFields as $fieldName => $fieldValue) {
                                        if (preg_match('/^unpaidAccountName_(\d+)$/', $fieldName, $matches)) {
                                            $unpaidAccountNames[$matches[1]] = $fieldValue;
                                        }
                                    }
                                    foreach ($unpaidAccountNames as $accountIndex => $accountName) {
                                        $amountField = $accountIndex === "" ? "unpaidAccountAmount" : "unpaidAccountAmount_" . $accountIndex;
                                        $noteField = $accountIndex === "" ? "unpaidAccountNote" : "unpaidAccountNote_" . $accountIndex;
                                        $accountAmount = (float) str_replace(",", "", (string) ($savedFields[$amountField] ?? 0));
                                        $accountNote = $savedFields[$noteField] ?? "";
                                        $accountName = trim((string) $accountName);
                                        if ($accountName !== "" && $accountAmount > 0 && !$isPaidNote($accountNote)) {
                                            $unpaidSaleAccounts[] = [
                                                "type" => "Account",
                                                "name" => $accountName,
                                                "amount" => $accountAmount
                                            ];
                                        }
                                    }
                                }
                                $hasUnpaidAccount = !empty($unpaidSaleAccounts);
                                $rowClass = $hasUnpaidAccount ? "bg-red-50/60 hover:bg-red-50" : "hover:bg-gray-50/50";
                                ?>
                                <tr class="sale-row transition-colors <?= $rowClass ?>"
                                    data-has-unpaid="<?= $hasUnpaidAccount ? 'true' : 'false' ?>"
                                    data-sale-channel="<?= $saleChannel ?>"
                                    data-sale-date="<?= htmlspecialchars($sale["report_date"]) ?>">
                                    
                                    <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                        <?= date("M d, Y", strtotime($sale["report_date"])) ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-800">
                                            <?= htmlspecialchars($sale["shift_name"]) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">
                                        ₱<?= number_format((float) $cashRemitted, 2) ?>
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">
                                        ₱<?= number_format((float) $sale["kitchen_sales_total"], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                                        <span class="font-medium text-gray-800"><?= (int) $sale["total_tables"] ?></span> <span class="text-gray-400">/</span> <?= (int) $sale["total_pax"] ?> pax
                                    </td>
                                    <td class="px-6 py-4 font-semibold text-gray-900 whitespace-nowrap">
                                        ₱<?= number_format((float) $sale["payment_total"], 2) ?>
                                    </td>
                                    <td class="px-6 py-4 min-w-[220px]">
                                        <?php if (empty($unpaidSaleAccounts)): ?>
                                            <span class="text-gray-400">-</span>
                                        <?php else: ?>
                                            <div class="space-y-1.5">
                                                <?php foreach ($unpaidSaleAccounts as $unpaidAccount): ?>
                                                    <div class="flex items-start justify-between gap-3">
                                                        <span class="text-xs text-gray-700 break-words">
                                                            <span class="text-gray-400"><?= htmlspecialchars($unpaidAccount["type"]) ?>:</span>
                                                            <?= htmlspecialchars($unpaidAccount["name"]) ?>
                                                        </span>
                                                        <span class="shrink-0 text-xs font-semibold text-amber-800">₱<?= number_format($unpaidAccount["amount"], 2) ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-right whitespace-nowrap space-x-1.5">
                                        <button type="button"
                                                class="edit-sale px-3 py-1.5 text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-lg text-xs font-semibold transition-all"
                                                data-report-id="<?= (int) $sale["report_id"] ?>"
                                                data-date="<?= htmlspecialchars($sale["report_date"], ENT_QUOTES) ?>"
                                                data-shift="<?= htmlspecialchars($sale["shift_name"], ENT_QUOTES) ?>"
                                                data-tables="<?= (int) $sale["total_tables"] ?>"
                                                data-pax="<?= (int) $sale["total_pax"] ?>"
                                                data-declared-total="<?= htmlspecialchars($sale["telegram_declared_total"], ENT_QUOTES) ?>"
                                                data-pos-sales-total="<?= htmlspecialchars($sale["pos_sales_total"], ENT_QUOTES) ?>">
                                            Edit
                                        </button>
                                        <button type="button"
                                                class="delete-sale px-3 py-1.5 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg text-xs font-semibold transition-all"
                                                data-report-id="<?= (int) $sale["report_id"] ?>">
                                            Delete
                                        </button>
                                        <button type="button"
                                                class="toggle-sale-details px-3 py-1.5 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg text-xs font-semibold transition-all"
                                                aria-expanded="false"
                                                data-target="sale-details-<?= (int) $sale["report_id"] ?>">
                                            Details
                                        </button>
                                    </td>
                                </tr>

                                <!-- Expandable Details Row -->
                                <tr id="sale-details-<?= (int) $sale["report_id"] ?>" 
                                    class="sale-details-row hidden bg-gray-50" 
                                    data-has-unpaid="<?= $hasUnpaidAccount ? 'true' : 'false' ?>" 
                                    data-sale-channel="<?= $saleChannel ?>">
                                    
                                    <td colspan="7" class="px-5 py-5">
                                        <div class="bg-white border rounded-xl p-4">
                                            
                                            <!-- Header Section with Search Box -->
                                            <div class="flex flex-wrap items-center justify-between gap-3 mb-3 pb-3 border-b border-gray-100">
                                                <div>
                                                    <p class="font-semibold text-gray-900">Detailed Sale & Account Breakdown</p>
                                                    <p class="text-xs text-gray-500">Report ID: #<?= (int) $sale["report_id"] ?></p>
                                                </div>
                                                
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <!-- Search Input -->
                                                    <div class="relative">
                                                        <span class="absolute inset-y-0 left-0 flex items-center pl-2.5 pointer-events-none text-gray-400">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                                            </svg>
                                                        </span>
                                                        <input type="text" 
                                                            id="search-details-<?= (int) $sale["report_id"] ?>" 
                                                            onkeyup="filterSaleDetails(<?= (int) $sale["report_id"] ?>)" 
                                                            placeholder="Search details..." 
                                                            class="pl-8 pr-3 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all w-44 sm:w-52">
                                                    </div>

                                                    <span class="text-xs font-semibold px-2.5 py-1.5 bg-gray-100 rounded-md text-gray-700">
                                                        Channel: <?= htmlspecialchars($saleChannel) ?>
                                                    </span>
                                                </div>
                                            </div>

                                            <?php 
                                                // Filter out fields that are empty, null, or blank strings
                                                $filteredFields = array_filter($saleFields, function($val) {
                                                    return $val !== null && trim((string)$val) !== '';
                                                });
                                                
                                                // Filter out images where path is empty
                                                $filteredImages = array_filter($ownerImages, function($path) {
                                                    return $path !== null && trim((string)$path) !== '';
                                                });
                                            ?>

                                            <?php if (empty($filteredFields) && empty($filteredImages)): ?>
                                                <!-- Empty State -->
                                                <p class="text-xs text-gray-400 py-4 text-center">
                                                    No saved form details or attachments are available for this sale.
                                                </p>
                                            <?php else: ?>
                                                <!-- Cards Grid Container -->
                                                <div id="grid-container-<?= (int) $sale["report_id"] ?>" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                                    
                                                    <!-- Sale Fields Cards (Only with values) -->
                                                    <?php foreach ($filteredFields as $fieldName => $fieldValue): ?>
                                                        <div class="detail-card border rounded-lg p-3 bg-white" data-search-text="<?= strtolower(htmlspecialchars(ucwords(preg_replace("/(?<!^)[A-Z]/", " $0", $fieldName)) . ' ' . $fieldValue)) ?>">
                                                            <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">
                                                                <?= htmlspecialchars(ucwords(preg_replace("/(?<!^)[A-Z]/", " $0", $fieldName))) ?>
                                                            </p>
                                                            <p class="text-sm font-semibold text-gray-800 mt-1 break-words">
                                                                <?= htmlspecialchars((string) $fieldValue) ?>
                                                            </p>
                                                        </div>
                                                    <?php endforeach; ?>

                                                    <!-- Owner Images Cards (Only with values) -->
                                                    <?php foreach ($filteredImages as $accountId => $imagePath): ?>
                                                        <div class="detail-card border rounded-lg p-3 bg-white flex flex-col justify-between" data-search-text="<?= strtolower('owner account #' . $accountId . ' image') ?>">
                                                            <div>
                                                                <p class="text-xs text-gray-500 uppercase tracking-wide font-medium">
                                                                    Owner Account #<?= htmlspecialchars((string) $accountId) ?> Image
                                                                </p>
                                                            </div>
                                                            <div class="mt-2">
                                                                <a href="../../<?= htmlspecialchars($imagePath) ?>" 
                                                                target="_blank" 
                                                                rel="noopener noreferrer" 
                                                                class="inline-block overflow-hidden rounded-lg border border-gray-200 hover:opacity-90 transition-opacity">
                                                                    <img src="../../<?= htmlspecialchars($imagePath) ?>" 
                                                                        alt="Owner account attachment" 
                                                                        class="h-16 w-16 object-cover">
                                                                </a>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>

                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php include "sale-modal.php"; ?>

    </main>
    <script>
        const kitchenSale = document.getElementById("kitchenSale");
        const barSale = document.getElementById("barSale");
        const corkage = document.getElementById("corkage");
        const totalSale = document.getElementById("totalSale");
        const grandTotal = document.getElementById("grandTotal");
        const serviceCharge = document.getElementById("serviceCharge");

        function isNonPOS() {
            const saleChannel = document.getElementById("saleChannel");
            return saleChannel && saleChannel.value === "Non POS";
        }

        function parseAmount(value) {
            return parseFloat(String(value || "0").replace(/,/g, "")) || 0;
        }

        function calculateNonPosTotalSale() {
            if (!totalSale) {
                return;
            }
            const giftCheckSale = document.getElementById("giftCheckSale");
            const otherProducts = document.getElementById("otherProducts");
            const total = parseAmount(kitchenSale && kitchenSale.value)
                + parseAmount(barSale && barSale.value)
                + parseAmount(corkage && corkage.value)
                + parseAmount(giftCheckSale && giftCheckSale.value)
                + parseAmount(otherProducts && otherProducts.value);
            totalSale.value = total.toFixed(2);

            if (grandTotal) {
                grandTotal.value = (total + parseAmount(serviceCharge && serviceCharge.value)).toFixed(2);
            }

            setTimeout(() => {
                if (typeof calculateShortOver === "function") {
                    calculateShortOver();
                }
            }, 0);
        }

        function calculateKitchenSale() {
            if (isNonPOS()) {
                calculateNonPosTotalSale();
                return;
            }

            const grandTotalValue = parseAmount(grandTotal.value);
            const bar = parseAmount(barSale.value);
            const corkageValue = parseAmount(corkage.value);
            const service = parseAmount(serviceCharge.value);

            const kitchen = grandTotalValue - bar - corkageValue - service;
            const total = grandTotalValue - service;

            kitchenSale.value = kitchen.toFixed(2);
            totalSale.value = total.toFixed(2);
        }

        const nonPosTotalForm = document.getElementById("salesForm");
        const recalcTotalSale = () => calculateKitchenSale();
        if (nonPosTotalForm) {
            ["input", "keyup", "change"].forEach(eventName => {
                nonPosTotalForm.addEventListener(eventName, event => {
                    const field = event.target;
                    if (!field || !field.id) {
                        return;
                    }
                    if (field.classList.contains("js-nonpos-total-input") ||
                        ["kitchenSale", "barSale", "giftCheckSale", "otherProducts", "grandTotal", "corkage", "serviceCharge"].includes(field.id)) {
                        recalcTotalSale();
                    }
                });
            });
        }
        [grandTotal, barSale, corkage, serviceCharge, kitchenSale].forEach(input => {
            if (input) {
                input.addEventListener("input", recalcTotalSale);
                input.addEventListener("keyup", recalcTotalSale);
                input.addEventListener("change", recalcTotalSale);
            }
        });
        ["giftCheckSale", "otherProducts"].forEach(id => {
            const input = document.getElementById(id);
            if (input) {
                input.addEventListener("input", recalcTotalSale);
                input.addEventListener("keyup", recalcTotalSale);
                input.addEventListener("change", recalcTotalSale);
            }
        });

        calculateKitchenSale();


        const paymentFields = [
            "cashRemitted",
            "gcashQrph",
            "paymaya",
            "amex",
            "visa",
            "mastercard",
            "bancnet",
            "jcb",
            "bpi",
            "easwest",
            "giftcheck",
            "cheque"
        ];


const totalPayment = document.getElementById("totalPayment");

function calculateTotalPayment() {
    let total = 0;

    paymentFields.forEach(id => {
        const input = document.getElementById(id);

        if (input) {
            total += parseFloat(input.value.replace(/,/g, "")) || 0;
        }
    });

    // Display with comma and 2 decimal places
    totalPayment.value = total.toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Update total immediately while typing
paymentFields.forEach(id => {
    const input = document.getElementById(id);

    if (input) {
        input.addEventListener("input", calculateTotalPayment);
    }
});

// Calculate when page loads
calculateTotalPayment();

const otherPaymentFields = [
    "otherCash",
    "otherMayaTerminal",
    "otherBpiNooma",
    "otherEastwestNooma",
    "otherGiftCheck",
    "otherCheques"
];

const totalOtherPayments = document.getElementById("totalOtherPayments");

function calculateTotalOtherPayments() {
    let total = 0;

    otherPaymentFields.forEach(id => {
        const input = document.getElementById(id);

        if (input) {
            total += parseFloat(String(input.value || "0").replace(/,/g, "")) || 0;
        }
    });

    // Display with comma and 2 decimal places
    if (totalOtherPayments) {
        totalOtherPayments.value = total.toLocaleString("en-PH", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    if (typeof calculateTotalSalesBasedOnPayment === "function") {
        calculateTotalSalesBasedOnPayment();
    }
}

// Update total immediately while typing
otherPaymentFields.forEach(id => {
    const input = document.getElementById(id);

    if (input) {
        input.addEventListener("input", calculateTotalOtherPayments);
    }
});

// Calculate when page loads
document.addEventListener('DOMContentLoaded', function() {
    calculateTotalOtherPayments();
});

const ownerInputs = document.querySelectorAll(".owner-account");
const totalOwnerAccounts = document.getElementById("totalOwnerAccounts");

function getOwnerAmount(value) {
    return parseFloat(value.replace(/,/g, "")) || 0;
}

function calculateOwnerAccounts() {

    let total = 0;

    document.querySelectorAll(".owner-account").forEach(input => {
        total += getOwnerAmount(input.value);
    });

    totalOwnerAccounts.value = total.toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}


// Format only when leaving the input
document.querySelectorAll(".owner-account").forEach(input => {

    input.addEventListener("input", function () {

        const raw = this.value.replace(/,/g, "");
        const isNegative = raw.startsWith("-");
        const sanitized = raw.replace(/-/g, "").replace(/[^\d.]/g, "");

        let value = sanitized;
        const parts = value.split(".");

        if (parts.length > 2) {
            value = parts[0] + "." + parts.slice(1).join("");
        }

        this.value = isNegative && value !== "" ? "-" + value : value;

        calculateOwnerAccounts();
    });


    input.addEventListener("blur", function () {

        if (this.value !== "") {

            const number = parseFloat(
                this.value.replace(/,/g, "")
            );

            if (!isNaN(number)) {

                this.value = number.toFixed(2);

            }

        }

        calculateOwnerAccounts();

    });

});
// Initial calculation
calculateOwnerAccounts();


function calculateMarketingExpenses() {

    let total = 0;

    document.querySelectorAll(".marketing-expense").forEach(input => {
        total += parseFloat(input.value.replace(/,/g, "")) || 0;
    });

    document.getElementById("totalMarketingExpenses").value =
        total.toLocaleString("en-PH", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
}

document.querySelectorAll(".marketing-expense").forEach(input => {

    input.addEventListener("input", function () {

        const raw = this.value.replace(/,/g, "");
        const isNegative = raw.startsWith("-");
        let value = raw.replace(/-/g, "").replace(/[^\d.]/g, "");

        const parts = value.split(".");

        if (parts.length > 2) {
            value = parts[0] + "." + parts.slice(1).join("");
        }

        this.value = isNegative && value !== "" ? "-" + value : value;

        calculateMarketingExpenses();
    });

    input.addEventListener("blur", function () {

        const number = parseFloat(this.value.replace(/,/g, ""));

        if (this.value !== "" && !isNaN(number)) {
            this.value = number.toFixed(2);
        }

        calculateMarketingExpenses();
    });

});

calculateMarketingExpenses();

const totalMarketingExpenses = document.getElementById("totalMarketingExpenses");
const totalSalesDeduction = document.getElementById("totalSalesDeduction");
const totalDeductionsAndExpenses = document.getElementById("totalDeductionsAndExpenses");
const totalExpenses = document.getElementById("totalExpenses");
const totalSalesBasedOnPayment = document.getElementById("totalSalesBasedOnPayment");
const grossSale = document.getElementById("grossSale");
const cashRemittedShortage = document.getElementById("cashRemittedShortage");
const cashSales = document.getElementById("cashSales");
const grabSaleGross = document.getElementById("grabSaleGross");
const onlineTips = document.getElementById("onlineTips");
const reconShortOver = document.getElementById("reconShortOver");
const unpaidAccounts = document.getElementById("unpaidAccounts");
const ownersDiscount = document.getElementById("ownersDiscount");
const marketingOwnersDiscount = document.getElementById("marketingOwnersDiscount");
const paidAccounts = document.getElementById("paidAccounts");
const foodBeverageSummary = document.getElementById("foodBeverageSummary");
const djsTalentFee = document.getElementById("djsTalentFee");
const bouncersFee = document.getElementById("bouncersFee");
const others = document.getElementById("others");
const unpaidAccountsContainer = document.getElementById("unpaidAccountsContainer");
const addUnpaidAccountButton = document.getElementById("addUnpaidAccount");
let unpaidAccountRowIndex = 1;

function addUnpaidAccountRow(name = "", amount = "", note = "") {
    const index = unpaidAccountRowIndex++;
    const row = document.createElement("div");
    row.className = "grid md:grid-cols-3 gap-4 unpaid-account-row";
    row.dataset.index = String(index);
    row.innerHTML = `
        <div>
            <label for="unpaidAccountName_${index}" class="block text-sm font-medium mb-2">Unpaid Account Name</label>
            <input type="text" id="unpaidAccountName_${index}" name="unpaid_account_name_${index}"
                   placeholder="e.g., Juan Dela Cruz" value="${String(name).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\"/g, "&quot;")}"
                   class="w-full border rounded-xl px-4 py-3 bg-white">
        </div>
        <div>
            <label for="unpaidAccountAmount_${index}" class="block text-sm font-medium mb-2">Unpaid Account Amount</label>
            <div class="flex gap-2">
                <input type="text" id="unpaidAccountAmount_${index}" name="unpaid_account_amount_${index}"
                       placeholder="0.00" inputmode="decimal" value="${String(amount).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\"/g, "&quot;")}"
                       class="w-full border rounded-xl px-4 py-3 bg-white">
                <button type="button" class="remove-unpaid-account px-3 py-2 bg-white border border-red-200 text-red-600 rounded-xl" aria-label="Remove unpaid account">&times;</button>
            </div>
        </div>
        <div>
            <label for="unpaidAccountNote_${index}" class="block text-sm font-medium mb-2">Notes</label>
            <input type="text" id="unpaidAccountNote_${index}" name="unpaid_account_note_${index}"
                   placeholder="Add notes..." value="${String(note).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/\"/g, "&quot;")}"
                   class="w-full border rounded-xl px-4 py-3 bg-white">
        </div>`;
    unpaidAccountsContainer.appendChild(row);
}

function calculateUnpaidAccountsTotal() {
    const total = Array.from(unpaidAccountsContainer.querySelectorAll(".unpaid-account-row"))
        .reduce((sum, row) => {
            const amountInput = row.querySelector("input[id^='unpaidAccountAmount']");
            return sum + parseMoney(amountInput ? amountInput.value : 0);
        }, 0);
    unpaidAccounts.value = formatMoney(total);
    calculateTotalSalesBasedOnPayment();
}

function resetUnpaidAccountRows() {
    unpaidAccountsContainer.querySelectorAll(".unpaid-account-row[data-index]:not([data-index=\"0\"]) ").forEach(row => row.remove());
    // clear the first row's amount and note inputs
    const firstAmountInput = unpaidAccountsContainer.querySelector("input[id^='unpaidAccountAmount']");
    if (firstAmountInput) firstAmountInput.value = "";
    const firstNoteInput = unpaidAccountsContainer.querySelector("input[id^='unpaidAccountNote']");
    if (firstNoteInput) firstNoteInput.value = "";
    unpaidAccountRowIndex = 1;
    calculateUnpaidAccountsTotal();
}

addUnpaidAccountButton.addEventListener("click", () => addUnpaidAccountRow());
unpaidAccountsContainer.addEventListener("click", event => {
    if (event.target.closest(".remove-unpaid-account")) {
        event.target.closest(".unpaid-account-row").remove();
        calculateUnpaidAccountsTotal();
    }
});

// Real-time total: delegate input events from all amount fields in the container
unpaidAccountsContainer.addEventListener("input", event => {
    if (event.target.id && event.target.id.startsWith("unpaidAccountAmount")) {
        sanitizeMoneyInput(event.target);
        calculateUnpaidAccountsTotal();
    }
});

function parseMoney(value) {
    return parseFloat(String(value || "0").replace(/,/g, "")) || 0;
}

function formatMoney(value) {
    return value.toLocaleString("en-PH", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function calculateSalesDeduction() {
    const salesDeductionTotal = Array.from(document.querySelectorAll(".sales-deduction"))
        .reduce((sum, input) => sum + parseMoney(input.value), 0);
    const total = salesDeductionTotal + parseMoney(totalExpenses.value);

    totalSalesDeduction.value = formatMoney(total);
    totalDeductionsAndExpenses.value = formatMoney(total);
    updateGrossSale();
}

function calculateTotalExpenses() {
    const total = [
        marketingOwnersDiscount,
        foodBeverageSummary,
        djsTalentFee,
        bouncersFee,
        others
    ].reduce((sum, input) => sum + parseMoney(input.value), 0);

    totalExpenses.value = formatMoney(total);
    calculateSalesDeduction();
}

function updateGrossSale() {
    const totalBasedOnPayment = parseMoney(totalSalesBasedOnPayment.value);
    const totalDeduction = parseMoney(totalSalesDeduction.value);

    grossSale.value = formatMoney(totalBasedOnPayment + totalDeduction);
}

function calculateCashRemittedShortage() {
    const cashRemittedAmount = parseMoney(document.getElementById("cashRemitted").value);
    const posCashAmount = parseMoney(cashSales.value);

    cashRemittedShortage.value = formatMoney(cashRemittedAmount - posCashAmount);
}

function calculateOnlineTips() {
    const grabGrossAmount = parseMoney(grabSaleGross.value);
    onlineTips.value = formatMoney(grabGrossAmount * 0.73);
}

function calculateTotalSalesBasedOnPayment() {
    if (!totalSalesBasedOnPayment) {
        return;
    }

    marketingOwnersDiscount.value = ownersDiscount.value || "0.00";
    calculateTotalExpenses();

    const ownerAccountSum = Array.from(document.querySelectorAll(".owner-account"))
        .reduce((sum, input) => sum + parseMoney(input.value), 0);

    const posPaymentSum = parseMoney(totalPayment.value);
    const otherPaymentSum = parseMoney(totalOtherPayments ? totalOtherPayments.value : 0);
    const paymentSum = isNonPOS() ? posPaymentSum + otherPaymentSum : posPaymentSum;
    const accountAdjustments = parseMoney(ownersDiscount.value);
    const paidAccount = parseMoney(paidAccounts.value);
    const marketingExpensesSum = parseMoney(totalMarketingExpenses.value);

    totalSalesBasedOnPayment.value = formatMoney(
        paymentSum + ownerAccountSum + accountAdjustments + paidAccount + marketingExpensesSum
    );

    updateGrossSale();
    calculateShortOver();
}

function updateShortOverBadge() {
    const statusEl = document.getElementById("shortOverStatus");
    if (!statusEl || !reconShortOver) {
        return;
    }

    const difference = parseMoney(reconShortOver.value);

    if (Math.abs(difference) < 0.005) {
        statusEl.textContent = "Balanced";
        statusEl.className = "inline-flex items-center rounded-full border border-gray-300 bg-gray-100 px-2.5 py-1 text-[11px] font-semibold tracking-wide text-gray-700";
        statusEl.classList.remove("hidden");
        return;
    }

    if (difference > 0) {
        statusEl.textContent = `Excess: ${formatMoney(difference)}`;
        statusEl.className = "inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold tracking-wide text-emerald-700";
    } else {
        statusEl.textContent = `Short: ${formatMoney(Math.abs(difference))}`;
        statusEl.className = "inline-flex items-center rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-[11px] font-semibold tracking-wide text-red-700";
    }

    statusEl.classList.remove("hidden");
}

function calculateShortOver() {
    const declaredGrandTotal = parseMoney(grandTotal.value);
    const totalBasedOnPayments = parseMoney(totalSalesBasedOnPayment.value);
    const unpaid = parseMoney(unpaidAccounts.value);

    reconShortOver.value = formatMoney(declaredGrandTotal - totalBasedOnPayments - unpaid );
    updateShortOverBadge();
}

function sanitizeMoneyInput(input) {
    const raw = String(input.value || "").replace(/,/g, "");
    const isNegative = raw.startsWith("-");
    let value = raw.replace(/-/g, "").replace(/[^\d.]/g, "");

    const parts = value.split(".");
    if (parts.length > 2) {
        value = parts[0] + "." + parts.slice(1).join("");
    }

    input.value = isNegative && value !== "" ? "-" + value : value;
}

[...document.querySelectorAll(".owner-account, .marketing-expense"), unpaidAccounts, ownersDiscount, paidAccounts]
    .filter(Boolean)
    .forEach(input => {
        input.addEventListener("input", () => {
            sanitizeMoneyInput(input);
            if (input.classList.contains("owner-account")) {
                calculateOwnerAccounts();
            }
            if (input.classList.contains("marketing-expense")) {
                calculateMarketingExpenses();
            }
            calculateTotalSalesBasedOnPayment();
        });
    });

document.querySelectorAll(".sales-deduction").forEach(input => {
    ["input", "change", "blur"].forEach(eventName => input.addEventListener(eventName, () => {
        sanitizeMoneyInput(input);
        calculateSalesDeduction();
        updateGrossSale();
    }));
});

[djsTalentFee, bouncersFee, others].forEach(input => {
    ["input", "change", "blur"].forEach(eventName => input.addEventListener(eventName, () => {
        sanitizeMoneyInput(input);
        calculateTotalExpenses();
    }));
});

paymentFields.forEach(id => {
    const input = document.getElementById(id);
    if (input) {
        input.addEventListener("input", calculateTotalSalesBasedOnPayment);
    }
});

otherPaymentFields.forEach(id => {
    const input = document.getElementById(id);
    if (input) {
        input.addEventListener("input", calculateTotalSalesBasedOnPayment);
    }
});

[document.getElementById("cashRemitted"), cashSales].forEach(input => {
    if (input) {
        ["input", "change", "blur"].forEach(eventName => {
            input.addEventListener(eventName, calculateCashRemittedShortage);
        });
    }
});

[grabSaleGross].forEach(input => {
    ["input", "change", "blur"].forEach(eventName => {
        input.addEventListener(eventName, () => {
            sanitizeMoneyInput(input);
            calculateOnlineTips();
        });
    });
});

totalOwnerAccounts.addEventListener("input", calculateTotalSalesBasedOnPayment);
grandTotal.addEventListener("input", calculateShortOver);

calculateSalesDeduction();
calculateTotalSalesBasedOnPayment();
calculateTotalExpenses();
updateGrossSale();
calculateCashRemittedShortage();
calculateOnlineTips();
        // Show/hide Non POS-only fields based on Sales Channel
        const saleChannelSelect = document.getElementById("saleChannel");
        const giftCheckSaleContainer = document.getElementById("giftCheckSaleContainer");
        const otherProductsContainer = document.getElementById("otherProductsContainer");

        function toggleNonPosFields() {
            const isNonPos = saleChannelSelect && saleChannelSelect.value === "Non POS";
            if (giftCheckSaleContainer) giftCheckSaleContainer.classList.toggle("hidden", !isNonPos);
            if (otherProductsContainer) otherProductsContainer.classList.toggle("hidden", !isNonPos);

            if (kitchenSale) {
                kitchenSale.readOnly = !isNonPos;
                kitchenSale.classList.toggle("bg-gray-100", !isNonPos);
            }
            if (grandTotal) {
                grandTotal.readOnly = isNonPos;
                grandTotal.classList.toggle("bg-gray-100", isNonPos);
            }

            const totalSaleLabel = document.querySelector("label[for='totalSale']");
            if (totalSaleLabel) {
                totalSaleLabel.textContent = isNonPos
                    ? "Total Sale (Ks + Bs + Corkage + Gift Check + Other Products)"
                    : "Total Sale (Ks + Bs + Corkage)";
            }

            // Fields that are required only for POS
            const posRequiredFields = [
                'saleDate',
                'pax',
                'tableNumber',
                'saleType',
                'barSale',
                'totalSale',
                'grandTotal'
            ];

            // Update required attributes based on sales channel
            posRequiredFields.forEach(function (id) {
                const field = document.getElementById(id);
                if (!field) return;

                if (isNonPos) {
                    field.removeAttribute('required');
                    field.setCustomValidity("");
                } else {
                    field.setAttribute('required', 'required');
                }
            });

            if (isNonPos) {
                const duplicateError = document.getElementById("saleDuplicateError");
                const dateField = document.getElementById("saleDate");
                const typeField = document.getElementById("saleType");
                if (duplicateError) {
                    duplicateError.classList.add("hidden");
                }
                if (dateField) {
                    dateField.classList.remove("border-red-500");
                    dateField.setCustomValidity("");
                }
                if (typeField) {
                    typeField.classList.remove("border-red-500");
                    typeField.setCustomValidity("");
                }
            }

            // Clear values when switching back to POS
            if (!isNonPos) {
                const giftCheckInput = document.getElementById("giftCheckSale");
                const otherProductsInput = document.getElementById("otherProducts");
                if (giftCheckInput) giftCheckInput.value = "";
                if (otherProductsInput) otherProductsInput.value = "";
            }

            calculateKitchenSale();
        }

        if (saleChannelSelect) {
            saleChannelSelect.addEventListener("change", toggleNonPosFields);
            toggleNonPosFields(); // run on page load
        }

        const saleModal = document.getElementById("saleModal");
        const openSaleModal = document.getElementById("openSaleModal");
        const closeSaleModal = document.getElementById("closeSaleModal");
        document.querySelectorAll(".toggle-sale-details").forEach(button => {
            button.addEventListener("click", () => {
                const details = document.getElementById(button.dataset.target);
                const isHidden = details.classList.toggle("hidden");
                button.setAttribute("aria-expanded", String(!isHidden));
                button.textContent = isHidden ? "Details" : "Hide Details";
            });
        });

        function setSaleModalVisibility(isVisible) {
            saleModal.classList.toggle("hidden", !isVisible);
            document.body.classList.toggle("overflow-hidden", isVisible);
        }

        let ignoreSaleModalCloseUntil = 0;
        function pauseSaleModalClose(ms = 1500) {
            ignoreSaleModalCloseUntil = Date.now() + ms;
        }
        function shouldIgnoreSaleModalClose() {
            return Date.now() < ignoreSaleModalCloseUntil;
        }

        openSaleModal.addEventListener("click", () => {
            resetSaleFormForCreate();
            setSaleModalVisibility(true);
        });
        closeSaleModal.addEventListener("click", () => setSaleModalVisibility(false));

        saleModal.addEventListener("mousedown", event => {
            if (event.target === saleModal && !shouldIgnoreSaleModalClose()) {
                setSaleModalVisibility(false);
            }
        });

        document.addEventListener("keydown", event => {
            if (event.key === "Escape" && !saleModal.classList.contains("hidden") && !shouldIgnoreSaleModalClose()) {
                setSaleModalVisibility(false);
            }
        });

        const salesForm = document.getElementById("salesForm");
        const saleDate = document.getElementById("saleDate");
        const saleType = document.getElementById("saleType");
        const saleDuplicateError = document.getElementById("saleDuplicateError");
        const existingSaleTypes = <?= json_encode($existingSaleTypes, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        let editingReportId = null;

        function ownerImageUrl(path) {
            if (!path) {
                return "";
            }
            if (/^https?:\/\//i.test(path) || path.startsWith("blob:") || path.startsWith("data:")) {
                return path;
            }
            return `../../${path.replace(/^\/+/, "")}`;
        }

        function setOwnerImagePreview(accountId, path, labelText) {
            const field = salesForm.querySelector(`.owner-image-field[data-account-id="${accountId}"]`);
            const hidden = document.getElementById(`ownerImage_${accountId}`);
            if (!field || !hidden) {
                return;
            }
            const preview = field.querySelector(".owner-image-preview");
            const image = preview.querySelector("img");
            const label = field.querySelector(".owner-image-label");
            hidden.value = path || "";
            if (path) {
                image.src = ownerImageUrl(path);
                preview.classList.remove("hidden");
                preview.classList.add("flex");
                label.textContent = labelText || "Image attached";
                label.classList.remove("text-gray-500");
                label.classList.add("text-gray-800");
            } else {
                image.src = "";
                preview.classList.add("hidden");
                preview.classList.remove("flex");
                label.textContent = "Attach image";
                label.classList.add("text-gray-500");
                label.classList.remove("text-gray-800");
            }
        }

        function resetOwnerImagePreviews() {
            salesForm.querySelectorAll(".owner-image-field").forEach(field => {
                setOwnerImagePreview(field.dataset.accountId, "");
                const fileInput = field.querySelector(".owner-image-file");
                if (fileInput) {
                    fileInput.value = "";
                }
            });
        }

        function resetSaleFormForCreate() {
            editingReportId = null;
            salesForm.reset();
            resetUnpaidAccountRows();
            resetOwnerImagePreviews();
            document.getElementById("saleModalTitle").textContent = "Create New Sale";
            saleDuplicateError.classList.add("hidden");
            saleDate.classList.remove("border-red-500");
            saleType.classList.remove("border-red-500");
            saleDate.setCustomValidity("");
            saleType.setCustomValidity("");
            calculateKitchenSale();
            calculateTotalPayment();
            calculateTotalOtherPayments();
            calculateOwnerAccounts();
            calculateMarketingExpenses();
            calculateTotalSalesBasedOnPayment();
            toggleNonPosFields();
        }

        function setFormValue(id, value) {
            const input = document.getElementById(id);
            if (input && value !== undefined && value !== null) {
                input.value = value;
            }
        }

        function populateSaleForm(payload) {
            resetUnpaidAccountRows();
            resetOwnerImagePreviews();
            Object.keys(payload)
                .filter(key => /^unpaidAccountName_\d+$/.test(key))
                .sort((firstKey, secondKey) => Number(firstKey.split("_").pop()) - Number(secondKey.split("_").pop()))
                .forEach(nameKey => {
                    const index = nameKey.split("_").pop();
                    addUnpaidAccountRow(payload[nameKey], payload[`unpaidAccountAmount_${index}`] || "", payload[`unpaidAccountNote_${index}`] || "");
                });
            
            // Load existing notes into the first row if they exist
            if (payload["unpaidAccountNote"]) {
                const firstNoteInput = document.getElementById("unpaidAccountNote");
                if (firstNoteInput) {
                    firstNoteInput.value = payload["unpaidAccountNote"];
                }
            }

            Object.entries(payload).forEach(([key, value]) => {
                if (key === "ownerAccounts" && value && typeof value === "object") {
                    Object.entries(value).forEach(([accountId, amount]) => setFormValue(`owner_${accountId}`, amount));
                    return;
                }
                const imageMatch = key.match(/^ownerImage_(\d+)$/);
                if (imageMatch) {
                    setOwnerImagePreview(imageMatch[1], value);
                    return;
                }
                setFormValue(key, value);
            });
            toggleNonPosFields();
            calculateKitchenSale();
            calculateTotalPayment();
            calculateTotalOtherPayments();
            calculateOwnerAccounts();
            calculateMarketingExpenses();
            calculateTotalSalesBasedOnPayment();
            calculateSalesDeduction();
            calculateTotalExpenses();
            calculateCashRemittedShortage();
            calculateOnlineTips();
            calculateKitchenSale();
        }

        document.querySelectorAll(".edit-sale").forEach(button => {
            button.addEventListener("click", async () => {
                try {
                    const response = await fetch(`../../backend/sales/get_sale.php?report_id=${encodeURIComponent(button.dataset.reportId)}`);
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        throw new Error(result.message || "The sale could not be loaded.");
                    }
                    editingReportId = button.dataset.reportId;
                    document.getElementById("saleModalTitle").textContent = "Edit Sale";
                    populateSaleForm(result.payload);
                    setSaleModalVisibility(true);
                } catch (error) {
                    window.showToast(error.message, "error");
                }
            });
        });

        document.querySelectorAll(".delete-sale").forEach(button => {
            button.addEventListener("click", async () => {
                if (!confirm("Delete this sale and all of its payment details?")) {
                    return;
                }

                const formData = new FormData();
                formData.append("report_id", button.dataset.reportId);
                try {
                    const response = await fetch("../../backend/sales/delete_sale.php", { method: "POST", body: formData });
                    const result = await response.json();
                    if (!response.ok || !result.success) {
                        throw new Error(result.message || "The sale could not be deleted.");
                    }
                    window.showToast(result.message || "Sale deleted successfully.");
                    setTimeout(() => window.location.reload(), 1400);
                } catch (error) {
                    window.showToast(error.message, "error");
                }
            });
        });

        function validateSaleDateAndType() {
            if (isNonPOS() || editingReportId) {
                saleDuplicateError.classList.add("hidden");
                saleDate.classList.remove("border-red-500");
                saleType.classList.remove("border-red-500");
                saleDate.setCustomValidity("");
                saleType.setCustomValidity("");
                return true;
            }

            const duplicate = existingSaleTypes.some(sale =>
                sale.report_id !== Number(editingReportId) &&
                sale.date === saleDate.value && sale.type === saleType.value
            );

            saleDuplicateError.classList.toggle("hidden", !duplicate);
            saleDate.classList.toggle("border-red-500", duplicate);
            saleType.classList.toggle("border-red-500", duplicate);
            saleDate.setCustomValidity(duplicate ? "A sale with this date and sale type already exists." : "");
            saleType.setCustomValidity(duplicate ? "A sale with this date and sale type already exists." : "");

            return !duplicate;
        }

        saleDate.addEventListener("input", validateSaleDateAndType);
        saleDate.addEventListener("change", validateSaleDateAndType);
        saleType.addEventListener("change", validateSaleDateAndType);
        if (saleChannelSelect) {
            saleChannelSelect.addEventListener("change", validateSaleDateAndType);
        }

        salesForm.addEventListener("change", event => {
            const fileInput = event.target.closest(".owner-image-file");
            if (!fileInput) {
                return;
            }
            const field = fileInput.closest(".owner-image-field");
            const file = fileInput.files && fileInput.files[0];
            if (!field) {
                return;
            }
            if (!file) {
                return;
            }
            const hasImageType = file.type.startsWith("image/");
            const hasImageExtension = /\.(jpe?g|png|gif|webp)$/i.test(file.name);
            if ((!hasImageType && !hasImageExtension) || file.size > 5 * 1024 * 1024) {
                fileInput.value = "";
                window.showToast("Please attach an image up to 5MB.", "error");
                return;
            }
            setOwnerImagePreview(field.dataset.accountId, URL.createObjectURL(file), file.name);
        });

        salesForm.addEventListener("click", event => {
            const pickButton = event.target.closest(".pick-owner-image");
            if (pickButton) {
                const field = pickButton.closest(".owner-image-field");
                const fileInput = field && field.querySelector(".owner-image-file");
                if (!fileInput) {
                    return;
                }
                pauseSaleModalClose();
                fileInput.click();
                return;
            }

            const clearButton = event.target.closest(".clear-owner-image");
            if (!clearButton) {
                return;
            }
            const field = clearButton.closest(".owner-image-field");
            if (!field) {
                return;
            }
            const fileInput = field.querySelector(".owner-image-file");
            if (fileInput) {
                fileInput.value = "";
            }
            setOwnerImagePreview(field.dataset.accountId, "");
        });

        salesForm.addEventListener("submit", async function (event) {
            event.preventDefault();

            if (!validateSaleDateAndType() || !salesForm.checkValidity()) {
                salesForm.reportValidity();
                return;
            }

            const payload = {};
            salesForm.querySelectorAll("input, select, textarea").forEach(input => {
                if (input.id && input.type !== "file") {
                    payload[input.id] = input.value;
                }
            });

            // Collect unpaid account notes
            const unpaidAccountNotes = {};
            unpaidAccountsContainer.querySelectorAll(".unpaid-account-row").forEach(row => {
                const index = row.dataset.index;
                const noteInput = row.querySelector(`input[id^='unpaidAccountNote']`);
                if (noteInput && noteInput.value) {
                    unpaidAccountNotes[index] = noteInput.value;
                }
            });
            // Store notes with the same naming pattern as other fields
            Object.entries(unpaidAccountNotes).forEach(([index, note]) => {
                payload[`unpaidAccountNote_${index}`] = note;
            });

            const ownerAccounts = {};
            salesForm.querySelectorAll(".owner-account").forEach(input => {
                const match = input.name.match(/owner_account\[(\d+)\]/);
                if (match) {
                    ownerAccounts[match[1]] = input.value;
                }
            });
            payload.ownerAccounts = ownerAccounts;
            Object.keys(payload).forEach(key => {
                if (!key.startsWith("ownerImage_")) {
                    return;
                }
                if (!payload[key] || /^(blob:|data:)/i.test(payload[key])) {
                    delete payload[key];
                }
            });

            const formData = new FormData();
            formData.append("sales_payload", JSON.stringify(payload));
            salesForm.querySelectorAll(".owner-image-file").forEach(input => {
                if (input.files && input.files[0]) {
                    formData.append(input.name, input.files[0]);
                }
            });

            try {
                const endpoint = editingReportId
                    ? "../../backend/sales/update_sale.php"
                    : "../../backend/sales/create_sale.php";
                if (editingReportId) {
                    formData.append("report_id", editingReportId);
                }
                const response = await fetch(endpoint, {
                    method: "POST",
                    body: formData
                });
                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || "The sale could not be saved.");
                }

                window.showToast(editingReportId ? result.message : `${result.message} Reference: ${result.report_number}`);
                setTimeout(() => window.location.reload(), 1400);
            } catch (error) {
                window.showToast(error.message, "error");
            }
        });

        const filterUnpaidOwners = document.getElementById("filterUnpaidOwners");
        const filterSalesChannel = document.getElementById("filterSalesChannel");
        const salesTableBody = document.getElementById("salesTableBody");
        if (filterUnpaidOwners && salesTableBody) {
            filterUnpaidOwners.addEventListener("change", function() {
                salesTableBody.classList.toggle("filter-unpaid", this.checked);
            });
        }
        if (filterSalesChannel && salesTableBody) {
            filterSalesChannel.addEventListener("change", function() {
                salesTableBody.classList.remove("filter-pos", "filter-non-pos");
                if (this.value !== "all") {
                    salesTableBody.classList.add(`filter-${this.value}`);
                }
            });
        }

        // search box of the table 
        if (filterSalesChannel && salesTableBody) {
            filterSalesChannel.addEventListener("change", function() {
                salesTableBody.classList.remove("filter-pos", "filter-non-pos");

                if (this.value !== "all") {
                    salesTableBody.classList.add(`filter-${this.value}`);
                }
            });
        }

        // ============================================================
        // SEARCH SALES
        // ============================================================

        const searchSales = document.getElementById("searchSales");

        if (searchSales && salesTableBody) {

            searchSales.addEventListener("input", function () {

                const searchValue = this.value
                    .trim()
                    .toLowerCase();

                const saleRows = salesTableBody.querySelectorAll(
                    ".sale-row"
                );

                saleRows.forEach(row => {

                    const reportId = row
                        .querySelector(".edit-sale")
                        ?.dataset.reportId || "";

                    const rowText = row.textContent
                        .toLowerCase();

                    const detailsId = row
                        .querySelector(".toggle-sale-details")
                        ?.dataset.target;

                    let detailsText = "";

                    if (detailsId) {

                        const detailsRow =
                            document.getElementById(detailsId);

                        if (detailsRow) {
                            detailsText =
                                detailsRow.textContent.toLowerCase();
                        }

                    }

                    const searchableText =
                        `${reportId} ${rowText} ${detailsText}`;

                    const matches =
                        searchValue === "" ||
                        searchableText.includes(searchValue);

                    row.dataset.searchMatch =
                        matches ? "true" : "false";

                    // Find the matching details row
                    if (detailsId) {

                        const detailsRow =
                            document.getElementById(detailsId);

                        if (detailsRow) {

                            detailsRow.dataset.searchMatch =
                                matches ? "true" : "false";

                            if (!matches) {
                                detailsRow.classList.add("hidden");
                            }

                        }

                    }

                    row.classList.toggle(
                        "hidden",
                        !matches
                    );

                });

                updateNoSearchResults();

            });

        }


        // ------------------------------------------------------------
        // NO SEARCH RESULTS MESSAGE
        // ------------------------------------------------------------

        function updateNoSearchResults() {

            if (!salesTableBody) {
                return;
            }

            const saleRows =
                salesTableBody.querySelectorAll(".sale-row");
            const visibleRows =
                Array.from(saleRows).filter(row =>
                    !row.classList.contains("hidden")
                );

            let noResultsRow =
                document.getElementById("noSalesSearchResults");


            if (visibleRows.length === 0) {

                if (!noResultsRow) {

                    noResultsRow =
                        document.createElement("tr");

                    noResultsRow.id =
                        "noSalesSearchResults";

                    noResultsRow.innerHTML = `
                        <td
                            colspan="7"
                            class="px-5 py-8 text-center text-gray-500"
                        >
                            No sales found matching your search.
                        </td>
                    `;

                    salesTableBody.appendChild(
                        noResultsRow
                    );
                }

                noResultsRow.classList.remove("hidden");

            } else {

                if (noResultsRow) {
                    noResultsRow.classList.add("hidden");
                }

            }

        }
        // const filterSalesDate = document.getElementById("filterSalesDate");
        // const filterSalesChannel = document.getElementById("filterSalesChannel");
        // const salesTableBody = document.getElementById("salesTableBody");

        function applySalesFilters() {

            if (!salesTableBody) {
                return;
            }

            const searchValue = searchSales
                ? searchSales.value.trim().toLowerCase()
                : "";

            const selectedDate = filterSalesDate
                ? filterSalesDate.value
                : "";

            const selectedChannel = filterSalesChannel
                ? filterSalesChannel.value
                : "all";

            const saleRows =
                salesTableBody.querySelectorAll(".sale-row");

            let visibleCount = 0;

            saleRows.forEach(row => {

                const rowChannel =
                    row.dataset.saleChannel || "pos";

                const rowDate =
                    row.dataset.saleDate || "";

                const rowText =
                    row.textContent.toLowerCase();

                const reportId =
                    row.querySelector(".edit-sale")
                        ?.dataset.reportId || "";

                // Search text
                const matchesSearch =
                    searchValue === "" ||
                    `${reportId} ${rowText}`.includes(searchValue);

                // Date filter
                const matchesDate =
                    selectedDate === "" ||
                    rowDate === selectedDate;

                // Channel filter
                const matchesChannel =
                    selectedChannel === "all" ||
                    rowChannel === selectedChannel;

                const shouldShow =
                    matchesSearch &&
                    matchesDate &&
                    matchesChannel;

                row.classList.toggle(
                    "hidden",
                    !shouldShow
                );

                // Details row
                const detailsButton =
                    row.querySelector(".toggle-sale-details");

                if (detailsButton) {

                    const detailsId =
                        detailsButton.dataset.target;

                    const detailsRow =
                        document.getElementById(detailsId);

                    if (detailsRow) {

                        detailsRow.classList.toggle(
                            "hidden",
                            !shouldShow ||
                            detailsRow.dataset.open !== "true"
                        );
                    }
                }

                if (shouldShow) {
                    visibleCount++;
                }
            });

            // No results message
            let noResultsRow =
                document.getElementById("noSalesSearchResults");

            if (visibleCount === 0 && saleRows.length > 0) {

                if (!noResultsRow) {

                    noResultsRow =
                        document.createElement("tr");

                    noResultsRow.id =
                        "noSalesSearchResults";

                    noResultsRow.innerHTML = `
                        <td colspan="7"
                            class="px-5 py-10 text-center">

                            <div class="text-gray-400 text-3xl mb-2">
                                🔍
                            </div>

                            <p class="font-medium text-gray-600">
                                No sales found
                            </p>

                            <p class="text-sm text-gray-400 mt-1">
                                Try another date, search term, or channel.
                            </p>

                        </td>
                    `;

                    salesTableBody.appendChild(noResultsRow);
                }

                noResultsRow.classList.remove("hidden");

            } else {

                if (noResultsRow) {
                    noResultsRow.classList.add("hidden");
                }
            }
        }
        if (searchSales) {
                searchSales.addEventListener(
                    "input",
                    applySalesFilters
                );
            }

            if (filterSalesDate) {
                filterSalesDate.addEventListener(
                    "change",
                    applySalesFilters
                );
            }

            if (filterSalesChannel) {
                filterSalesChannel.addEventListener(
                    "change",
                    applySalesFilters
                );
            }

            function filterSaleDetails(reportId) {
    const input = document.getElementById('search-details-' + reportId);
    const filter = input.value.toLowerCase().trim();
    const container = document.getElementById('grid-container-' + reportId);
    
    if (!container) return;
    
    const cards = container.getElementsByClassName('detail-card');
    
    for (let i = 0; i < cards.length; i++) {
        const searchText = cards[i].getAttribute('data-search-text') || '';
        if (searchText.includes(filter)) {
            cards[i].style.display = "";
        } else {
            cards[i].style.display = "none";
        }
    }
}
    </script>
    <?php include "../components/footer.php"; ?>

</div>

</body>
</html>
