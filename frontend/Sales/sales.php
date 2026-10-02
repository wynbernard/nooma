
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

$sales = [];
$salesResult = mysqli_query($conn, "
    SELECT
        dr.report_id,
        dr.report_number,
        dr.report_date,
        dr.shift_name,
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

// Calculate overall statistics (not just current page)
$statsResult = mysqli_query($conn, "
    SELECT
        SUM(dr.pos_sales_total) as total_sales,
        COUNT(*) as total_transactions,
        SUM(CASE WHEN COALESCE(payments.payment_total, 0) < dr.telegram_declared_total THEN 1 ELSE 0 END) as pending_payments
    FROM daily_reports dr
    LEFT JOIN (
        SELECT report_id, SUM(amount) AS payment_total
        FROM report_payments
        GROUP BY report_id
    ) payments ON payments.report_id = dr.report_id
");

$totalSales = 0;
$totalTransactions = 0;
$pendingPayments = 0;

if ($statsResult) {
    $stats = mysqli_fetch_assoc($statsResult);
    $totalSales = (float) ($stats['total_sales'] ?? 0);
    $totalTransactions = (int) ($stats['total_transactions'] ?? 0);
    $pendingPayments = (int) ($stats['pending_payments'] ?? 0);
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
                <p class="text-sm text-gray-500">Pending Payments</p>
                <h3 id="pendingPayments"
                    class="text-2xl font-bold mt-2 text-orange-500"><?= $pendingPayments ?></h3>
            </div>

        </div>

        <!-- SALES TABLE -->
        <section class="bg-white border rounded-2xl shadow-sm mb-6 overflow-hidden">
            <div class="p-5 md:p-6 border-b flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold">Saved Sales</h3>
                    <p class="text-sm text-gray-500 mt-1">Recently recorded daily sales reports.</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- <label class="flex items-center gap-2 text-sm font-medium text-gray-700 bg-gray-50 border px-3 py-2 rounded-xl cursor-pointer hover:bg-gray-100">
                        <input type="checkbox" id="filterUnpaidOwners" class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                        Show unpaid owners only
                    </label> -->
                    <!-- SEARCH SALES -->
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                                🔍
                            </span>

                            <input
                                type="search"
                                id="searchSales"
                                placeholder="Search sales..."
                                autocomplete="off"
                                class="w-64 border rounded-xl pl-10 pr-4 py-2.5 text-sm
                                    text-gray-700 bg-white
                                    focus:ring-2 focus:ring-blue-500
                                    focus:border-blue-500 outline-none"
                            >
                        </div>
                         <input
                                type="date"
                                id="filterSalesDate"
                                class="border rounded-xl px-3 py-2.5 text-sm text-gray-700 bg-white
                                    focus:ring-2 focus:ring-blue-500
                                    focus:border-blue-500 outline-none"
                            >

                    <select id="filterSalesChannel" aria-label="Filter sales channel"
                            class="border rounded-xl px-3 py-2 text-sm font-medium text-gray-700 bg-white focus:ring-blue-500 focus:border-blue-500">
                        <option value="all">All channels</option>
                        <option value="pos">POS only</option>
                        <option value="non-pos">Non POS only</option>
                    </select>
                    <button type="button" id="openSaleModal"
                            class="px-4 py-2.5 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700">
                        + Add Sale
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <!-- <th class="px-5 py-3">Report</th> -->
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Shift</th>
                            <th class="px-5 py-3">Tables / Pax</th>
                            <th class="px-5 py-3">Sales</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="salesTableBody" class="divide-y">
                        <?php if (empty($sales)): ?>
                            <tr>
                                <td colspan="8" class="px-5 py-8 text-center text-gray-500">
                                    No saved sales yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($sales as $sale): ?>
                                <?php
                                $saleFields = [];
                                $hasUnpaidOwnerAccount = false;
                                $shortOver = 0.0;
                                $ownerImages = [];
                                $savedFields = json_decode($sale["notes"] ?? "", true);
                                $saleChannel = "pos";
                                if (is_array($savedFields)) {
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
                                            if (stripos($note, "unpaid") !== false) {
                                                $hasUnpaidOwnerAccount = true;
                                            }
                                        }
                                        $saleFields["Owner account #" . $accountId] = $value;
                                        if (is_string($image) && preg_match('#^uploads/owner-accounts/[A-Za-z0-9._-]+$#', $image)) {
                                            $ownerImages[$accountId] = $image;
                                        }
                                    }
                                }
                                $rowClass = $hasUnpaidOwnerAccount ? "bg-red-50 hover:bg-red-100" : "hover:bg-gray-50";
                                ?>
                                <tr class="sale-row <?= $rowClass ?>"
                                    data-has-unpaid="<?= $hasUnpaidOwnerAccount ? 'true' : 'false' ?>"
                                    data-sale-channel="<?= $saleChannel ?>"
                                    data-sale-date="<?= htmlspecialchars($sale["report_date"]) ?>">
                                    <!-- <td class="px-5 py-4 font-semibold">
                                        <?= htmlspecialchars($sale["report_number"]) ?>
                                        <?php if ($hasUnpaidOwnerAccount): ?>
                                            <span class="ml-2 inline-flex items-center justify-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 border border-red-200">
                                                Unpaid Owner
                                            </span>
                                        <?php endif; ?>
                                    </td> -->
                                    <td class="px-5 py-4">
                                        <?= date("F d, Y", strtotime($sale["report_date"])) ?>
                                    </td>
                                    <td class="px-5 py-4"><?= htmlspecialchars($sale["shift_name"]) ?></td>
                                    <td class="px-5 py-4">
                                        <?= (int) $sale["total_tables"] ?> / <?= (int) $sale["total_pax"] ?>
                                    </td>
                                    <td class="px-5 py-4 font-semibold">
                                        ₱<?= number_format((float) $sale["payment_total"], 2) ?>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-50 text-yellow-700">
                                            <?= htmlspecialchars(ucfirst($sale["status"])) ?>
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <button type="button"
                                                class="edit-sale px-3 py-1.5 text-blue-700 bg-blue-50 rounded-lg font-medium hover:bg-blue-100"
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
                                                class="delete-sale ml-2 px-3 py-1.5 text-red-700 bg-red-50 rounded-lg font-medium hover:bg-red-100"
                                                data-report-id="<?= (int) $sale["report_id"] ?>">
                                            Delete
                                        </button>
                                        <button type="button"
                                                class="toggle-sale-details ml-2 px-3 py-1.5 text-gray-700 bg-gray-100 rounded-lg font-medium hover:bg-gray-200"
                                                aria-expanded="false"
                                                data-target="sale-details-<?= (int) $sale["report_id"] ?>">
                                            Details
                                        </button>
                                    </td>
                                </tr>
                                <tr id="sale-details-<?= (int) $sale["report_id"] ?>" class="sale-details-row hidden bg-gray-50" data-has-unpaid="<?= $hasUnpaidOwnerAccount ? 'true' : 'false' ?>" data-sale-channel="<?= $saleChannel ?>">
                                    <td colspan="8" class="px-5 py-5">
                                        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                            <?php if (empty($saleFields)): ?>
                                                <p class="sm:col-span-2 lg:col-span-4 text-sm text-gray-500">
                                                    No saved form details are available for this sale.
                                                </p>
                                            <?php else: ?>
                                                <?php foreach ($saleFields as $fieldName => $fieldValue): ?>
                                                    <div class="bg-white border rounded-lg p-3">
                                                        <p class="text-xs text-gray-500">
                                                            <?= htmlspecialchars(ucwords(preg_replace("/(?<!^)[A-Z]/", " $0", $fieldName))) ?>
                                                        </p>
                                                        <p class="mt-1 font-semibold break-words">
                                                            <?= htmlspecialchars((string) $fieldValue) ?>
                                                        </p>
                                                    </div>
                                                <?php endforeach; ?>
                                                <?php foreach ($ownerImages as $accountId => $imagePath): ?>
                                                    <div class="bg-white border rounded-lg p-3">
                                                        <p class="text-xs text-gray-500">
                                                            Owner Account #<?= htmlspecialchars((string) $accountId) ?> Image
                                                        </p>
                                                        <a href="../../<?= htmlspecialchars($imagePath) ?>" target="_blank" rel="noopener noreferrer" class="mt-2 inline-block">
                                                            <img src="../../<?= htmlspecialchars($imagePath) ?>" alt="Owner account attachment" class="h-24 w-24 object-cover rounded-lg border">
                                                        </a>
                                                    </div>
                                                <?php endforeach; ?>
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

        // Remove commas and invalid characters
        this.value = this.value
            .replace(/,/g, "")
            .replace(/[^\d.]/g, "");

        // Prevent multiple decimal points
        const parts = this.value.split(".");

        if (parts.length > 2) {
            this.value = parts[0] + "." + parts.slice(1).join("");
        }

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

        let value = this.value
            .replace(/,/g, "")
            .replace(/[^\d.]/g, "");

        const parts = value.split(".");

        if (parts.length > 2) {
            value = parts[0] + "." + parts.slice(1).join("");
        }

        this.value = value;

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

function calculateShortOver() {
    const declaredGrandTotal = parseMoney(grandTotal.value);
    const totalBasedOnPayments = parseMoney(totalSalesBasedOnPayment.value);
    const unpaid = parseMoney(unpaidAccounts.value);

    reconShortOver.value = formatMoney(declaredGrandTotal - totalBasedOnPayments - unpaid );
}

function sanitizeMoneyInput(input) {
    input.value = input.value
        .replace(/,/g, "")
        .replace(/[^\d.]/g, "");

    const parts = input.value.split(".");
    if (parts.length > 2) {
        input.value = parts[0] + "." + parts.slice(1).join("");
    }
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
                    alert(error.message);
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
                    window.location.reload();
                } catch (error) {
                    alert(error.message);
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
                alert("Please attach an image up to 5MB.");
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

                alert(editingReportId ? result.message : `${result.message} Reference: ${result.report_number}`);
                window.location.reload();
            } catch (error) {
                alert(error.message);
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
                            colspan="8"
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
                        <td colspan="8"
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
    </script>
    <?php include "../components/footer.php"; ?>

</div>

</body>
</html>
