<?php

session_start();


// ============================================================
// AUTHENTICATION
// ============================================================

// Make sure user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}


// Only admin can access this page
if (($_SESSION["role"] ?? "") !== "admin") {
    header("Location: ../login.php");
    exit;
}


$full_name = $_SESSION["full_name"] ?? "Administrator";
$username = $_SESSION["username"] ?? "admin";


// ============================================================
// PAGE INFORMATION
// ============================================================

$page_title = "Sales Comparison";
$page_description = "Compare StoreHub totals and adjustments with Nooma";


require_once __DIR__ . "/../../backend/config/database.php";
require_once __DIR__ . "/../../backend/reports/refresh_sales_comparison.php";
$comparisonResults = $_SESSION["sales_comparison_results"] ?? [];
$productImportResult = $_SESSION["sales_product_import_result"] ?? null;
unset($_SESSION["sales_product_import_result"]);
$comparisonError = isset($_GET["refresh_comparison"])
    ? ""
    : ($_SESSION["sales_comparison_error"] ?? "");
unset($_SESSION["sales_comparison_error"]);

try {
    if (is_array($comparisonResults) && $comparisonResults) {
        $comparisonResults = refresh_sales_comparison($conn, $comparisonResults);
        $_SESSION["sales_comparison_results"] = $comparisonResults;
    }
} catch (Throwable $error) {
    error_log("Live sales comparison refresh failed: " . $error->getMessage());
    $comparisonError = "Could not refresh Nooma comparison values. Please reload the page and try again.";
    $comparisonResults = [];
}

if (isset($_GET["refresh_comparison"])) {
    header("Content-Type: application/json; charset=utf-8");
    if ($comparisonError !== "") {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => $comparisonError]);
    } else {
        echo json_encode(["success" => true, "results" => $comparisonResults]);
    }
    exit;
}

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
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-900">
<!-- ============================================================
     SIDEBAR
============================================================ -->
<?php include "../Components/sidebar.php"; ?>
<!-- ============================================================
     MAIN
============================================================ -->
<div class="lg:ml-64 min-h-screen">
    <!-- ========================================================
         NAVBAR
    ========================================================= -->
    <?php include "../Components/navbar.php"; ?>
    <!-- ========================================================
         CONTENT
    ========================================================= -->
    <main class="p-4 md:p-5">
        <!-- ====================================================
             PAGE HEADER
        ===================================================== -->
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-900">
                Sales Comparison
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Compare Grand Total, Total Discount, and Service Charge with Nooma by date or report range.
            </p>
            <?php if ($comparisonError !== ""): ?>
                <p class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                    <?= htmlspecialchars($comparisonError) ?>
                </p>
            <?php endif; ?>
            <?php if (is_array($productImportResult)): ?>
                <p class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                    Product import complete: <?= (int) $productImportResult["added"] ?> added to inventory with Excel items sold recorded in Purchases,
                    <?= (int) ($productImportResult["updated"] ?? 0) ?> same-date items updated without adding duplicates.
                </p>
            <?php endif; ?>
        </div>
        <!-- ====================================================
             UPLOAD CARD
        ===================================================== -->

        <div
            class="bg-white border border-gray-200
                   rounded-2xl shadow-sm p-4 mb-4"
        >
            <!-- HEADER -->

            <div class="flex items-center gap-3 mb-4">


                <div
                    class="w-12 h-12 bg-blue-100
                           rounded-xl flex items-center
                           justify-center"
                >

                    <svg
                        class="w-6 h-6 text-blue-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >

                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M3 7h4l2-2h6l2 2h4a2 2 0 012 2v10a2 2 0 01-2 2H3a2 2 0 01-2-2V9a2 2 0 012-2z"
                        />
                    </svg>
                </div>
                <div>
                    <h2
                        class="text-lg font-bold text-gray-900"
                    >
                        Compare StoreHub Sales Report
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Upload a daily sales report or a Sales by Product report. Product reports add missing inventory items and compare Excel Total Sales with Nooma Grand Total less Service Charge.
                    </p>
                </div>
            </div>



            <!-- =================================================
                 UPLOAD FORM
            ================================================== -->

            <form
                id="inventoryUploadForm"
                action="../../backend/reports/compare_sales.php"
                method="POST"
                enctype="multipart/form-data"
            >


                <!-- =================================================
                     DROP ZONE
                ================================================== -->

                <div
                    id="dropZone"
                    class="border-2 border-dashed
                           border-gray-300 rounded-2xl
                           p-6 md:p-8 text-center
                           cursor-pointer
                           hover:border-blue-400
                           hover:bg-blue-50/50
                           transition"
                >


                    <input
                        type="file"
                        id="inventoryFile"
                        accept=".xlsx,.csv"
                        class="hidden"
                        required
                    >


                    <div
                        class="flex flex-col
                               items-center"
                    >


                        <!-- UPLOAD ICON -->

                        <div
                            class="w-16 h-16 bg-gray-100
                                   rounded-full
                                   flex items-center
                                   justify-center mb-4"
                        >

                            <svg
                                class="w-8 h-8 text-gray-500"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >

                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M12 16V4m0 0L8 8m4-4l4 4M4 16v3a1 1 0 001 1h14a1 1 0 001-1v-3"
                                />

                            </svg>

                        </div>



                        <p
                            class="text-lg font-semibold
                                   text-gray-700"
                        >
                            Click to select a sales report
                        </p>


                        <p class="text-sm text-gray-500 mt-1">
                            or drag and drop the file here
                        </p>


                        <p class="text-xs text-gray-400 mt-3">
                            Supported formats:
                            .xlsx, .csv
                        </p>


                    </div>


                </div>



                <!-- =================================================
                     SELECTED FILE
                ================================================== -->

                <div
                    id="selectedFileContainer"
                    class="hidden mt-4"
                >

                    <div
                        class="flex items-center
                               justify-between
                               bg-gray-50
                               border border-gray-200
                               rounded-xl p-4"
                    >


                        <div class="flex items-center gap-3">


                            <!-- FILE ICON -->

                            <div
                                class="w-10 h-10
                                       bg-green-100
                                       rounded-lg
                                       flex items-center
                                       justify-center"
                            >

                                <span
                                    class="text-green-600
                                           font-bold text-xs"
                                >
                                    FILE
                                </span>

                            </div>



                            <!-- FILE INFORMATION -->

                            <div>

                                <p
                                    id="selectedFileName"
                                    class="font-semibold
                                           text-gray-800
                                           break-all"
                                ></p>


                                <p
                                    id="selectedFileSize"
                                    class="text-xs
                                           text-gray-500 mt-1"
                                ></p>

                            </div>


                        </div>



                        <!-- REMOVE -->

                        <button
                            type="button"
                            id="removeFile"
                            class="ml-4 text-red-500
                                   hover:text-red-700
                                   text-sm
                                   font-semibold
                                   whitespace-nowrap"
                        >
                            Remove
                        </button>


                    </div>

                </div>



                <!-- =================================================
                     UPLOAD BUTTON
                ================================================== -->

                <div
                    class="flex justify-end mt-4"
                >

                    <button
                        type="submit"
                        id="uploadButton"
                        disabled
                        class="px-6 py-3
                               bg-blue-600
                               text-white
                               rounded-xl
                               font-semibold
                               hover:bg-blue-700
                               disabled:bg-gray-300
                               disabled:cursor-not-allowed
                               transition"
                    >
                        Compare Daily Totals
                    </button>

                </div>


                <input type="hidden" id="dailyTotalsJson" name="daily_totals_json" value="">
                <input type="hidden" id="productInventoryJson" name="product_inventory_json" value="">

            </form>


        </div>

        <div id="productPreviewContainer" class="hidden bg-white border border-gray-200 rounded-2xl shadow-sm p-4 mb-4" aria-live="polite">
            <h2 class="font-bold text-gray-900">Product inventory import preview</h2>
            <p id="productPreviewSummary" class="text-sm text-gray-500 mt-1"></p>
            <div class="overflow-x-auto mt-3 max-h-72">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-gray-50 text-left text-gray-600">
                        <tr>
                            <th class="py-2 pr-4">Product</th>
                            <th class="py-2 pr-4">Department</th>
                            <th class="py-2 text-right">Items Sold</th>
                            <th class="py-2 text-right">Total Sales PHP</th>
                            <th class="py-2 text-right">Total Discount PHP</th>
                        </tr>
                    </thead>
                    <tbody id="productPreviewRows" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>

        <div id="extractedDataContainer" class="hidden bg-white border border-gray-200 rounded-xl p-4 mb-4" aria-live="polite">
            <div class="mb-3">
                <h2 class="font-bold text-gray-900">Extracted Excel Data</h2>
                <p class="text-sm text-gray-500 mt-1">Review Grand Total, Total Discount, and Service Charge before comparing with Nooma.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-200 text-left text-gray-600">
                        <tr>
                            <th class="py-2 pr-4 font-semibold">Date / Report Range</th>
                            <th class="py-2 text-right font-semibold">Excel Total Sale</th>
                            <th class="py-2 text-right font-semibold">Total Discount</th>
                            <th class="py-2 text-right font-semibold">Service Charge</th>
                        </tr>
                    </thead>
                    <tbody id="extractedDataRows" class="divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>
        <!-- ====================================================
             RECENT UPLOADS
        ===================================================== -->
        <div
            class="bg-white
                   border border-gray-200
                   rounded-2xl
                   shadow-sm
                   overflow-hidden"
        >
            <!-- HEADER -->
            <div
                class="p-4
                       border-b
                       border-gray-200"
            >
                <h2
                    class="text-lg
                           font-bold
                           text-gray-900"
                >
                    Sales Comparison Results
                </h2>
                <p
                    class="text-sm
                           text-gray-500 mt-1"
                >
                    Grand Total, Total Discount, and Service Charge compared with the matching Nooma values.
                </p>
                <p id="comparisonRefreshError" class="hidden mt-2 text-sm text-red-600" role="alert">
                    Could not refresh Nooma totals. The displayed values may be out of date.
                </p>
            </div>
            <!-- TABLE -->
            <div
                class="overflow-x-auto"
            >
                <table class="w-full text-sm">
                    <thead
                        class="bg-gray-50
                               border-b
                               border-gray-200"
                    >
                        <tr>
                            <th
                                class="text-left
                                       px-6 py-4
                                       font-semibold
                                       text-gray-600"
                            >
                                Date
                            </th>
                            <th
                                class="text-left
                                       px-6 py-4
                                       font-semibold
                                       text-gray-600"
                            >
                                Field
                            </th>
                            <th
                                class="text-left
                                       px-6 py-4
                                       font-semibold
                                       text-gray-600"
                            >
                                Excel Total
                            </th>
                            <th
                                class="text-center
                                       px-6 py-4
                                       font-semibold
                                       text-gray-600"
                            >
                                Nooma Total
                            </th>
                            <th
                                class="text-right
                                       px-6 py-4
                                       font-semibold
                                       text-gray-600"
                            >
                                Difference
                            </th>
                            <th class="text-right px-6 py-4 font-semibold text-gray-600">Result</th>
                        </tr>
                    </thead>
                    <tbody
                        id="comparisonResultsBody"
                        data-refresh-enabled="<?= $comparisonResults ? "true" : "false" ?>"
                        class="divide-y
                               divide-gray-100"
                    >
                        <?php if ($comparisonResults): ?>
                            <?php foreach ($comparisonResults as $comparison): ?>
                                <?php
                                $matched = $comparison["status"] === "Matched";
                                $hasExcelTotal = $comparison["excel_total"] !== null;
                                $hasNoomaTotal = $comparison["nooma_total"] !== null;
                                $statusClass = $matched
                                    ? "text-green-700"
                                    : ($comparison["status"] === "Difference" ? "text-amber-700" : "text-gray-500");
                                ?>
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-800">
                                        <?= htmlspecialchars(date("M j, Y", strtotime($comparison["date"]))) ?>
                                        <?php if (!empty($comparison["date_to"])): ?>
                                            - <?= htmlspecialchars(date("M j, Y", strtotime($comparison["date_to"]))) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 font-medium text-gray-700">
                                        <?= htmlspecialchars($comparison["metric"]) ?>
                                    </td>
                                    <td class="px-6 py-4 text-right tabular-nums">
                                        <?= $hasExcelTotal ? number_format((float) $comparison["excel_total"], 2) : "—" ?>
                                    </td>
                                    <td class="px-6 py-4 text-right tabular-nums">
                                        <?= $hasNoomaTotal ? number_format((float) $comparison["nooma_total"], 2) : "—" ?>
                                    </td>
                                    <td class="px-6 py-4 text-right tabular-nums">
                                        <?= $hasExcelTotal && $hasNoomaTotal ? number_format((float) $comparison["difference"], 2) : "—" ?>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="font-semibold <?= $statusClass ?>">
                                            <?= htmlspecialchars($comparison["status"]) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                    No comparison results yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <!-- ========================================================
         FOOTER
    ========================================================= -->

    <?php include "../Components/footer.php"; ?>
</div>



<!-- ============================================================
     JAVASCRIPT
============================================================ -->

<script>

const comparisonResultsBody = document.getElementById("comparisonResultsBody");
const comparisonRefreshError = document.getElementById("comparisonRefreshError");

function formatComparisonAmount(amount) {
    return amount === null || amount === undefined
        ? "—"
        : Number(amount).toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
}

function formatComparisonDate(dateValue) {
    const [year, month, day] = dateValue.split("-").map(Number);
    return new Date(year, month - 1, day).toLocaleDateString("en-US", {
        month: "short",
        day: "numeric",
        year: "numeric"
    });
}

function renderComparisonResults(results) {
    comparisonResultsBody.replaceChildren();

    if (!results.length) {
        const row = document.createElement("tr");
        const cell = document.createElement("td");
        cell.colSpan = 6;
        cell.className = "px-6 py-10 text-center text-gray-400";
        cell.textContent = "No comparison results yet.";
        row.appendChild(cell);
        comparisonResultsBody.appendChild(row);
        return;
    }

    results.forEach(comparison => {
        const row = document.createElement("tr");
        const dateCell = document.createElement("td");
        dateCell.className = "px-6 py-4 font-medium text-gray-800";
        dateCell.textContent = formatComparisonDate(comparison.date)
            + (comparison.date_to ? ` - ${formatComparisonDate(comparison.date_to)}` : "");
        row.appendChild(dateCell);

        const metricCell = document.createElement("td");
        metricCell.className = "px-6 py-4 font-medium text-gray-700";
        metricCell.textContent = comparison.metric;
        row.appendChild(metricCell);

        [comparison.excel_total, comparison.nooma_total, comparison.difference].forEach(amount => {
            const cell = document.createElement("td");
            cell.className = "px-6 py-4 text-right tabular-nums";
            cell.textContent = formatComparisonAmount(amount);
            row.appendChild(cell);
        });

        const statusCell = document.createElement("td");
        statusCell.className = "px-6 py-4 text-center";
        const status = document.createElement("span");
        status.className = "font-semibold " + (
            comparison.status === "Matched"
                ? "text-green-700"
                : (comparison.status === "Difference" ? "text-amber-700" : "text-gray-500")
        );
        status.textContent = comparison.status;
        statusCell.appendChild(status);
        row.appendChild(statusCell);
        comparisonResultsBody.appendChild(row);
    });
}

async function refreshComparisonResults() {
    try {
        const refreshUrl = new URL(window.location.href);
        refreshUrl.search = "?refresh_comparison=1";
        const response = await fetch(refreshUrl, {
            headers: { "Accept": "application/json" },
            cache: "no-store"
        });
        const payload = await response.json();
        if (!response.ok || !payload.success || !Array.isArray(payload.results)) {
            throw new Error(payload.message || "Could not refresh Nooma comparison values.");
        }
        renderComparisonResults(payload.results);
        comparisonRefreshError.classList.add("hidden");
    } catch (error) {
        comparisonRefreshError.textContent =
            "Could not refresh Nooma totals. The displayed values may be out of date.";
        comparisonRefreshError.classList.remove("hidden");
        console.error("Live sales comparison refresh failed:", error);
    }
}

if (comparisonResultsBody.dataset.refreshEnabled === "true") {
    window.setInterval(refreshComparisonResults, 10000);
}

const dropZone =
    document.getElementById("dropZone");

const inventoryFile =
    document.getElementById("inventoryFile");

const selectedFileContainer =
    document.getElementById("selectedFileContainer");

const selectedFileName =
    document.getElementById("selectedFileName");

const selectedFileSize =
    document.getElementById("selectedFileSize");

const removeFile =
    document.getElementById("removeFile");

const uploadButton =
    document.getElementById("uploadButton");

const inventoryUploadForm =
    document.getElementById("inventoryUploadForm");

const dailyTotalsJson =
    document.getElementById("dailyTotalsJson");

const productInventoryJson =
    document.getElementById("productInventoryJson");

const extractedDataContainer =
    document.getElementById("extractedDataContainer");

const extractedDataRows =
    document.getElementById("extractedDataRows");

const productPreviewContainer =
    document.getElementById("productPreviewContainer");

const productPreviewSummary =
    document.getElementById("productPreviewSummary");

const productPreviewRows =
    document.getElementById("productPreviewRows");


// ============================================================
// OPEN FILE SELECTOR
// ============================================================

dropZone.addEventListener("click", function () {
    inventoryFile.click();
});


// ============================================================
// FILE SELECTED
// ============================================================

inventoryFile.addEventListener("change", function () {
    if (this.files.length > 0) {
        showSelectedFile(this.files[0]);
    }
});


// ============================================================
// DRAG OVER
// ============================================================

dropZone.addEventListener("dragover", function (event) {
    event.preventDefault();
    this.classList.add(
        "border-blue-500",
        "bg-blue-50"
    );
});


// ============================================================
// DRAG LEAVE
// ============================================================

dropZone.addEventListener("dragleave", function () {
    this.classList.remove(
        "border-blue-500",
        "bg-blue-50"
    );
});


// ============================================================
// DROP FILE
// ============================================================

dropZone.addEventListener("drop", function (event) {
    event.preventDefault();
    this.classList.remove(
        "border-blue-500",
        "bg-blue-50"
    );
    const files =
        event.dataTransfer.files;
    if (files.length > 0) {
        inventoryFile.files = files;
        showSelectedFile(files[0]);
    }
});


// ============================================================
// SHOW SELECTED FILE
// ============================================================

async function showSelectedFile(file) {
    const allowedExtensions = [
        "xlsx",
        "csv"
    ];
    const extension =
        file.name
            .split(".")
            .pop()
            .toLowerCase();
    if (!allowedExtensions.includes(extension)) {
        window.showToast("Please select an XLSX or CSV file.", "error");
        inventoryFile.value = "";
        selectedFileContainer.classList.add(
            "hidden"
        );
        dailyTotalsJson.value = "";
        productInventoryJson.value = "";
        extractedDataContainer.classList.add("hidden");
        productPreviewContainer.classList.add("hidden");
        uploadButton.disabled = true;
        return;
    }
    selectedFileName.textContent =
        file.name;
    selectedFileSize.textContent =
        formatFileSize(file.size);
    selectedFileContainer.classList.remove(
        "hidden"
    );
    dailyTotalsJson.value = "";
    productInventoryJson.value = "";
    extractedDataRows.replaceChildren();
    productPreviewRows.replaceChildren();
    extractedDataContainer.classList.add("hidden");
    productPreviewContainer.classList.add("hidden");
    uploadButton.disabled = true;
    uploadButton.textContent = "Extracting...";

    try {
        const workbook = XLSX.read(await file.arrayBuffer(), { type: "array", cellDates: true });
        const productReport = productInventoryFromWorkbook(workbook, file.name);
        if (inventoryFile.files[0] !== file) {
            return;
        }

        if (productReport) {
            productInventoryJson.value = JSON.stringify(productReport);
            renderProductPreview(productReport);
            uploadButton.textContent = "Import Products & Compare";
        } else {
            const dailyTotals = dailySalesFromWorkbook(workbook, file.name);
            dailyTotalsJson.value = JSON.stringify(dailyTotals);
            renderExtractedData(dailyTotals);
            uploadButton.textContent = "Compare Daily Totals";
        }
        uploadButton.disabled = false;
    } catch (error) {
        if (inventoryFile.files[0] !== file) {
            return;
        }
        window.showToast(error.message || "Could not read the sales report.", "error");
        uploadButton.disabled = true;
        uploadButton.textContent = "Compare Daily Totals";
    }
}

function renderProductPreview(productReport) {
    productPreviewRows.replaceChildren();
    const totalItemsSold = productReport.products.reduce((sum, product) => sum + product.items_sold, 0);
    const totalSales = productReport.products.reduce((sum, product) => sum + product.total_sales, 0);
    const totalDiscount = productReport.products.reduce((sum, product) => sum + product.total_discount, 0);
    productPreviewSummary.textContent =
        `${productReport.products.length} products for ${productReport.date}; ` +
        `${totalItemsSold.toLocaleString()} items sold; Excel Total Sales PHP ` +
        totalSales.toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 }) +
        `; Excel Total Discount PHP ${totalDiscount.toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}` +
        ". Excel items sold will be recorded in Purchases; existing products for this date will be updated instead of duplicated.";

    productReport.products.forEach((product) => {
        const row = document.createElement("tr");
        [
            product.name,
            product.department,
            product.items_sold.toLocaleString(),
            product.total_sales.toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 }),
            product.total_discount.toLocaleString("en-PH", { minimumFractionDigits: 2, maximumFractionDigits: 2 })
        ].forEach((value, index) => {
            const cell = document.createElement("td");
            cell.className = index < 2 ? "py-2 pr-4" : "py-2 text-right tabular-nums";
            cell.textContent = value;
            row.appendChild(cell);
        });
        productPreviewRows.appendChild(row);
    });
    productPreviewContainer.classList.remove("hidden");
}

function renderExtractedData(dailyTotals) {
    extractedDataRows.replaceChildren();

    dailyTotals.forEach((entry) => {
        const row = document.createElement("tr");
        const dateCell = document.createElement("td");
        const dateLabel = entry.start_date && entry.end_date
            ? `${entry.start_date} - ${entry.end_date}`
            : entry.date;

        dateCell.className = "py-2 pr-4 font-medium text-gray-800";
        dateCell.textContent = dateLabel;
        row.appendChild(dateCell);
        [entry.grand_total, entry.total_discount, entry.service_charge].forEach((amount) => {
            const amountCell = document.createElement("td");
            amountCell.className = "py-2 text-right tabular-nums";
            amountCell.textContent = amount === null
                ? "Not in Excel"
                : Number(amount).toLocaleString("en-PH", {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            row.appendChild(amountCell);
        });
        extractedDataRows.appendChild(row);
    });

    extractedDataContainer.classList.remove("hidden");
}


// ============================================================
// REMOVE FILE
// ============================================================

removeFile.addEventListener("click", function () {
    inventoryFile.value = "";
    selectedFileContainer.classList.add(
        "hidden"
    );
    dailyTotalsJson.value = "";
    productInventoryJson.value = "";
    extractedDataRows.replaceChildren();
    productPreviewRows.replaceChildren();
    extractedDataContainer.classList.add("hidden");
    productPreviewContainer.classList.add("hidden");
    uploadButton.disabled = true;
    uploadButton.textContent = "Compare Daily Totals";
});


// ============================================================
// FORMAT FILE SIZE
// ============================================================

function formatFileSize(bytes) {
    if (bytes === 0) {
        return "0 Bytes";
    }
    const units = [
        "Bytes",
        "KB",
        "MB",
        "GB"
    ];
    const index =
        Math.floor(
            Math.log(bytes) /
            Math.log(1024)
        );
    return (
        parseFloat(
            (
                bytes /
                Math.pow(1024, index)
            ).toFixed(2)
        )
        +
        " " +
        units[index]
    );
}


// ============================================================
// FORM SUBMIT
// ============================================================

inventoryUploadForm.addEventListener(
    "submit",
    function () {
        if (!inventoryFile.files.length) {
            window.showToast("Please select an inventory file.", "error");
            return;
        }
        uploadButton.disabled = true;
        uploadButton.textContent =
            "Comparing...";
    }
);
function normalizeHeader(value) {
    return String(value ?? "")
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, " ")
        .trim();
}

function formatDateParts(year, month, day) {
    const date = new Date(Date.UTC(year, month - 1, day));
    if (date.getUTCFullYear() !== year || date.getUTCMonth() !== month - 1 || date.getUTCDate() !== day) {
        return null;
    }
    return `${year}-${String(month).padStart(2, "0")}-${String(day).padStart(2, "0")}`;
}

function parseExcelDate(value) {
    if (value instanceof Date && !Number.isNaN(value.getTime())) {
        return formatDateParts(value.getUTCFullYear(), value.getUTCMonth() + 1, value.getUTCDate());
    }

    if (typeof value === "number" && Number.isFinite(value)) {
        const parts = XLSX.SSF.parse_date_code(value);
        return parts ? formatDateParts(parts.y, parts.m, parts.d) : null;
    }

    const text = String(value ?? "").trim();
    let match = text.match(/(\d{4})[-/](\d{1,2})[-/](\d{1,2})/);
    if (match) {
        return formatDateParts(Number(match[1]), Number(match[2]), Number(match[3]));
    }

    match = text.match(/(\d{1,2})[-/](\d{1,2})[-/](\d{4})/);
    if (match) {
        return formatDateParts(Number(match[3]), Number(match[1]), Number(match[2]));
    }

    const parsed = new Date(text);
    return Number.isNaN(parsed.getTime())
        ? null
        : formatDateParts(parsed.getFullYear(), parsed.getMonth() + 1, parsed.getDate());
}

function parseSingleReportDate(value) {
    if (typeof value === "string") {
        const dateTokens = value.match(/\d{4}[-/]\d{1,2}[-/]\d{1,2}|\d{1,2}[-/]\d{1,2}[-/]\d{4}/g) || [];
        if (dateTokens.length > 1) {
            return null;
        }
    }

    const date = parseExcelDate(value);
    return date && Number(date.slice(0, 4)) >= 2000 ? date : null;
}

function parseSalesAmount(value) {
    if (typeof value === "number") {
        return Number.isFinite(value) ? value : null;
    }

    let text = String(value ?? "").trim();
    if (!text) {
        return null;
    }

    const isNegative = /^\(.*\)$/.test(text);
    text = text.replace(/[^0-9.+-]/g, "");
    if (!/\d/.test(text)) {
        return null;
    }
    const amount = Number(text);
    if (!Number.isFinite(amount)) {
        return null;
    }
    return isNegative ? -Math.abs(amount) : amount;
}

function salesColumnIndexes(headers) {
    const columnAliases = {
        grand_total: ["total sale", "total sales", "total sale php", "total sales php", "grand total", "grand total php"],
        total_discount: ["total discount", "total discount php", "total discounts", "discount total", "total discount amount", "total sales deduction"],
        service_charge: ["service charge", "service charge php", "service charges", "service charge total"]
    };

    return Object.fromEntries(Object.entries(columnAliases).map(([field, aliases]) => [
        field,
        aliases.map((alias) => headers.findIndex((header) => normalizeHeader(header) === alias))
            .find((column) => column !== -1) ?? -1
    ]));
}

function productInventoryFromWorkbook(workbook, filename) {
    const aliases = {
        name: ["product name", "item name", "product"],
        department: ["product category", "category", "department"],
        itemsSold: ["total items sold", "items sold", "quantity sold"],
        totalSales: ["total sales php", "total sales", "sales amount"],
        totalDiscount: ["total discount php", "total discount", "discount amount"]
    };

    for (const sheetName of workbook.SheetNames) {
        const rows = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], {
            header: 1,
            raw: true,
            defval: null
        });
        for (let headerRow = 0; headerRow < Math.min(rows.length, 40); headerRow += 1) {
            const headers = rows[headerRow] || [];
            const columns = Object.fromEntries(Object.entries(aliases).map(([field, names]) => [
                field,
                names.map((name) => headers.findIndex((header) => normalizeHeader(header) === name))
                    .find((column) => column !== -1) ?? -1
            ]));
            if (Object.values(columns).some((column) => column === -1)) {
                continue;
            }

            const filenameDates = Array.from(
                String(filename ?? "").matchAll(/\d{4}[-/]\d{1,2}[-/]\d{1,2}/g),
                (match) => parseSingleReportDate(match[0])
            ).filter(Boolean);
            if (filenameDates.length !== 1) {
                throw new Error("The Sales by Product filename must include exactly one report date, such as 2026-10-08.");
            }

            const products = [];
            for (const row of rows.slice(headerRow + 1)) {
                const name = String(row[columns.name] ?? "").trim();
                if (!name || /^(grand )?totals?$/i.test(name)) {
                    continue;
                }

                const category = normalizeHeader(row[columns.department]);
                const department = category === "kitchen"
                    ? "Kitchen"
                    : (category === "bar" ? "Bar" : "");
                const itemsSold = parseSalesAmount(row[columns.itemsSold]);
                const totalSales = parseSalesAmount(row[columns.totalSales]);
                const totalDiscount = parseSalesAmount(row[columns.totalDiscount]);
                if (!department || itemsSold === null || totalSales === null || totalDiscount === null) {
                    throw new Error(`Product "${name}" has an unsupported category or invalid sales values.`);
                }
                products.push({
                    name,
                    department,
                    items_sold: itemsSold,
                    total_sales: totalSales,
                    total_discount: totalDiscount
                });
            }
            if (!products.length) {
                throw new Error("The Sales by Product report has no product rows.");
            }
            return { date: filenameDates[0], products };
        }
    }
    return null;
}

function dailySalesFromWorkbook(workbook, filename) {
    for (const sheetName of workbook.SheetNames) {
        const rows = XLSX.utils.sheet_to_json(workbook.Sheets[sheetName], {
            header: 1,
            raw: true,
            defval: null
        });

        for (let headerRow = 0; headerRow < Math.min(rows.length, 40); headerRow += 1) {
            const headers = rows[headerRow] || [];
            const populatedHeaders = headers.filter((header) => String(header ?? "").trim() !== "").length;
            const dateColumn = headers.findIndex((header) => /(^| )date( |$)/.test(normalizeHeader(header)));
            const amountColumns = salesColumnIndexes(headers);
            if (populatedHeaders < 2 || amountColumns.grand_total === -1) {
                continue;
            }

            const availableAmountColumns = Object.values(amountColumns).filter((column) => column !== -1);
            const hasNumericSalesRows = rows
                .slice(headerRow + 1, headerRow + 21)
                .some((row) => availableAmountColumns.some((column) => parseSalesAmount((row || [])[column]) !== null));
            if (!hasNumericSalesRows) {
                continue;
            }

            const totals = new Map();
            const reportTotals = Object.fromEntries(Object.entries(amountColumns).map(([field, column]) => [
                field,
                column === -1 ? null : 0
            ]));
            let reportAmountCount = 0;
            let currentDate = null;
            for (let priorRowIndex = 0; priorRowIndex < headerRow; priorRowIndex += 1) {    
                const priorDates = new Set((rows[priorRowIndex] || [])
                    .map(parseSingleReportDate)
                    .filter(Boolean));
                if (priorDates.size === 1) {
                    currentDate = Array.from(priorDates)[0];
                } else if (priorDates.size > 1) {
                    currentDate = null;
                }
            }

            for (let rowIndex = headerRow + 1; rowIndex < rows.length; rowIndex += 1) {
                const row = rows[rowIndex] || [];
                if (row.every((cell) => cell === null || String(cell).trim() === "")) {
                    continue;
                }

                const isSummary = row.some((cell) => /^(?:(?:grand )?totals?|sub ?total|total sales):?$/i.test(String(cell ?? "").trim()));
                if (isSummary) {
                    continue;
                }
                const dateValue = dateColumn === -1 ? null : row[dateColumn];
                const parsedDate = parseExcelDate(dateValue);
                if (parsedDate) {
                    currentDate = parsedDate;
                } else if (dateColumn !== -1 && dateValue !== null && String(dateValue).trim() !== "") {
                    currentDate = null;
                }

                if (!parsedDate) {
                    const amountColumnIndexes = new Set(availableAmountColumns);
                    for (let columnIndex = 0; columnIndex < row.length; columnIndex += 1) {
                        if (amountColumnIndexes.has(columnIndex) || columnIndex === dateColumn) {
                            continue;
                        }
                        const rowDate = parseSingleReportDate(row[columnIndex]);
                        if (rowDate) {
                            currentDate = rowDate;
                            break;
                        }
                    }
                }
                const amounts = Object.fromEntries(Object.entries(amountColumns).map(([field, column]) => [
                    field,
                    parseSalesAmount(row[column])
                ]));
                if (Object.values(amounts).some((amount) => amount !== null)) {
                    Object.keys(reportTotals).forEach((field) => {
                        if (reportTotals[field] !== null && amounts[field] !== null) {
                            reportTotals[field] += amounts[field];
                        }
                    });
                    reportAmountCount += 1;
                    if (currentDate) {
                        const dailyTotal = totals.get(currentDate) || Object.fromEntries(Object.entries(amountColumns).map(([field, column]) => [
                            field,
                            column === -1 ? null : 0
                        ]));
                        Object.keys(dailyTotal).forEach((field) => {
                            if (dailyTotal[field] !== null && amounts[field] !== null) {
                                dailyTotal[field] += amounts[field];
                            }
                        });
                        totals.set(currentDate, dailyTotal);
                    }
                }
            }
            if (totals.size) {
                return Array.from(totals, ([date, amounts]) => ({ date, ...amounts }));
            }

            if (reportAmountCount) {
                const filenameDates = Array.from(
                    String(filename ?? "").matchAll(/\d{4}[-/]\d{1,2}[-/]\d{1,2}/g),
                    (match) => parseSingleReportDate(match[0])
                ).filter(Boolean).sort();
                if (filenameDates.length) {
                    return [{
                        start_date: filenameDates[0],
                        end_date: filenameDates[filenameDates.length - 1],
                        ...reportTotals
                    }];
                }
            }
        }
    }
    throw new Error("Could not find an Excel Total Sales or Grand Total column. If the sheet has no row dates, include its date or date range in the filename.");
}
inventoryUploadForm.addEventListener("submit", async function (event) {
    event.preventDefault();
    if (!inventoryFile.files.length) {
        return;
    }
    try {
        const file = inventoryFile.files[0];
        const workbook = XLSX.read(await file.arrayBuffer(), { type: "array", cellDates: true });
        const productReport = productInventoryFromWorkbook(workbook, file.name);
        if (productReport) {
            productInventoryJson.value = JSON.stringify(productReport);
            dailyTotalsJson.value = "";
        } else {
            const dailyTotals = dailySalesFromWorkbook(workbook, file.name);
            dailyTotalsJson.value = JSON.stringify(dailyTotals);
            productInventoryJson.value = "";
        }
        inventoryUploadForm.submit();
    } catch (error) {
        window.showToast(error.message || "Could not read the sales report.", "error");
        uploadButton.disabled = false;
        uploadButton.textContent = "Compare Daily Totals";
    }
});
</script>
</body>
</html>