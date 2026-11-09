<?php
// Include backend controller logic
include "../../backend/inventory/bar_inventory.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <main class="p-4 sm:p-6 space-y-6">

        <?php if ($inventorySyncMessage !== ""): ?>
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                <?= htmlspecialchars($inventorySyncMessage, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>
        <?php if ($inventorySyncError !== ""): ?>
            <div class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                <?= htmlspecialchars($inventorySyncError, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

       <!-- STATISTICS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4" aria-label="Inventory summary">
            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Items</p>
                        <h3 id="totalItemsCount" class="text-2xl font-bold text-gray-900 mt-2"><?= number_format($totalItemsCount) ?></h3>
                        <p class="text-sm text-blue-600 mt-2">Unique catalog records</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Stock Qty</p>
                        <h3 id="totalStockQuantity" class="text-2xl font-bold text-gray-900 mt-2"><?= number_format($totalStockQuantity, 2) ?></h3>
                        <p class="text-sm text-green-600 mt-2">Combined inventory stock</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a3 3 0 006 0M9 5h6M9 13h6M9 17h4" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">Low Stock Alerts</p>
                        <h3 id="lowStockCount" class="text-2xl font-bold text-gray-900 mt-2"><?= number_format($lowStockCount) ?></h3>
                        <p class="text-sm text-orange-600 mt-2">Items &le; 5 quantity</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500">New This Week</p>
                        <h3 id="recentAdditionsCount" class="text-2xl font-bold text-gray-900 mt-2"><?= number_format($recentAdditionsCount) ?></h3>
                        <p class="text-sm text-purple-600 mt-2">Added past 7 days</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        <!-- INVENTORY TABLE -->
        <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
            <!-- HEADER WITH SEARCH & ADD BUTTON -->
            <div class="p-5 sm:p-6 border-b border-gray-200 flex flex-col xl:flex-row xl:items-center justify-between gap-5">
                <div>
                    <h1 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($page_title) ?></h1>
                    <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($page_description) ?>.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:flex items-end gap-3">
                    <form id="inventorySyncForm" method="post" action="../../api/extraction.php" class="flex min-w-0 items-end gap-2">
                        <input type="hidden" name="inventory_sync" value="1">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION["inventory_sync_csrf_token"], ENT_QUOTES, "UTF-8") ?>">
                        <input type="hidden" name="return_page" value="<?= htmlspecialchars($inventoryReturnPage, ENT_QUOTES, "UTF-8") ?>">
                        <div class="min-w-0 flex-1">
                            <label for="inventorySyncDate" class="block text-xs font-semibold text-gray-600 mb-1">Transaction date</label>
                            <input
                                type="date"
                                id="inventorySyncDate"
                                name="sync_date"
                                value="<?= htmlspecialchars($inventoryDateFilter !== "" ? $inventoryDateFilter : date("Y-m-d"), ENT_QUOTES, "UTF-8") ?>"
                                required
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                        <button id="inventorySyncButton" type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 disabled:cursor-wait text-white font-semibold text-sm rounded-xl transition whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M20 20v-5h-5M5 9a7 7 0 0112-3l3 3M19 15a7 7 0 01-12 3l-3-3" />
                            </svg>
                            Sync date
                        </button>
                    </form>
                    <form method="get" class="min-w-0">
                        <label for="inventoryDateFilter" class="block text-xs font-semibold text-gray-600 mb-1">Show inventory date</label>
                        <div class="flex items-center gap-2">
                            <input
                                type="date"
                                id="inventoryDateFilter"
                                name="inventory_date"
                                value="<?= htmlspecialchars($inventoryDateFilter, ENT_QUOTES, "UTF-8") ?>"
                                onchange="this.form.submit()"
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                            <?php if ($inventoryDateFilter !== ""): ?>
                                <a href="<?= htmlspecialchars(strtok($_SERVER["REQUEST_URI"], "?"), ENT_QUOTES, "UTF-8") ?>" class="text-sm font-medium text-blue-700 hover:text-blue-900 whitespace-nowrap">All dates</a>
                            <?php endif; ?>
                        </div>
                    </form>
                    <!-- SEARCH BOX -->
                    <div class="relative sm:col-span-2 xl:w-64">
                        <label for="inventorySearch" class="block text-xs font-semibold text-gray-600 mb-1">Find an item</label>
                        <span class="absolute bottom-0 left-0 flex h-[42px] items-center pl-3.5 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="search" id="inventorySearch" placeholder="Name or unit" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>

                    <!-- OPEN MODAL BUTTON -->
                    <button onclick="openAddModal()" type="button" class="sm:col-span-2 xl:col-span-1 inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl transition shadow-sm gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Item
                    </button>
                    <form method="get" action="../../backend/inventory/export_inventory.php" class="sm:col-span-2 xl:col-span-1 flex min-w-0 items-end gap-2">
                        <input type="hidden" name="department" value="<?= htmlspecialchars($inventoryDepartment, ENT_QUOTES, "UTF-8") ?>">
                        <div class="min-w-0 flex-1">
                            <label for="inventoryExportDate" class="block text-xs font-semibold text-gray-600 mb-1">Excel export date</label>
                            <input
                                type="date"
                                id="inventoryExportDate"
                                name="inventory_date"
                                value="<?= htmlspecialchars($inventoryDateFilter !== "" ? $inventoryDateFilter : date("Y-m-d"), ENT_QUOTES, "UTF-8") ?>"
                                required
                                class="w-full px-3 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-sm rounded-xl transition shadow-sm gap-2 whitespace-nowrap">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m0 12l-4-4m4 4l4-4M4 20h16" />
                            </svg>
                            Download Excel
                        </button>
                    </form>
                </div>
            </div>

            <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-b border-gray-200">
                <p id="inventoryResultsCount" class="text-sm font-medium text-gray-700" aria-live="polite">
                    <?= number_format(count($inventoryItems)) ?> <?= count($inventoryItems) === 1 ? "item" : "items" ?>
                </p>
                <p class="hidden sm:block text-xs text-gray-500">Scroll horizontally to see all columns</p>
            </div>
            <div class="overflow-x-auto">
                <table id="inventoryTable" class="w-full min-w-[900px] text-sm">
                    <thead class="sticky top-0 z-10 bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th scope="col" class="text-left px-5 py-3.5 font-semibold text-gray-700 min-w-[220px]">Item</th>
                            <th scope="col" class="text-right px-4 py-3.5 font-semibold text-gray-700">Quantity</th>
                            <th scope="col" class="text-center px-4 py-3.5 font-semibold text-gray-700">Unit</th>
                            <th scope="col" class="text-right px-4 py-3.5 font-semibold text-gray-700">Beginning</th>
                            <th scope="col" class="text-right px-4 py-3.5 font-semibold text-gray-700">Purchases</th>
                            <th scope="col" class="text-right px-4 py-3.5 font-semibold text-gray-700">Sold</th>
                            <th scope="col" class="text-right px-4 py-3.5 font-semibold text-gray-700">Used</th>
                            <th scope="col" class="text-right px-4 py-3.5 font-semibold text-gray-700">Ending</th>
                            <th scope="col" class="text-center px-4 py-3.5 font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryTableBody" class="divide-y divide-gray-100">
                        <?php if (empty($inventoryItems)): ?>
                            <tr id="noInventoryRow" data-inventory-empty-row>
                                <td colspan="9" class="px-6 py-8 text-center text-gray-500">No inventory items found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inventoryItems as $item): ?>
                                <tr class="inventory-row border-b border-gray-100 odd:bg-white even:bg-gray-50/70 hover:bg-blue-50/60 transition-colors" data-inventory-id="<?= (int) $item["inventory_id"] ?>" data-inventory-date="<?= htmlspecialchars((string) $item["inventory_date"], ENT_QUOTES, "UTF-8") ?>" data-quantity="<?= htmlspecialchars((string) $item["quantity"], ENT_QUOTES, "UTF-8") ?>" data-created-at="<?= htmlspecialchars((string) $item["created_at"], ENT_QUOTES, "UTF-8") ?>">
                                    <td class="px-5 py-4">
                                        <div class="font-semibold text-gray-900 item-desc"><?= htmlspecialchars($item["item_description"]) ?></div>
                                        <?php if (count($item["source_items"] ?? []) > 1): ?>
                                            <div class="mt-1 text-xs text-blue-700"><?= count($item["source_items"]) ?> records combined across dates</div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 text-right font-semibold tabular-nums whitespace-nowrap">
                                        <?php if ((float) $item["quantity"] <= 5): ?>
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-amber-800" title="Low stock" aria-label="Low stock: <?= htmlspecialchars(number_format((float) $item["quantity"], 2), ENT_QUOTES, "UTF-8") ?>">
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500" aria-hidden="true"></span>
                                                <?= number_format((float) $item["quantity"], 2) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-900"><?= number_format((float) $item["quantity"], 2) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-4 text-center text-gray-600 item-unit whitespace-nowrap"><?= htmlspecialchars($item["unit"]) ?></td>
                                    <td class="px-4 py-4 text-right text-gray-700 tabular-nums whitespace-nowrap"><?= number_format((float) $item["beginning"], 2) ?></td>
                                    <td class="px-4 py-4 text-right text-emerald-700 font-medium tabular-nums whitespace-nowrap">+<?= number_format((float) $item["purchases"], 2) ?></td>
                                    <td class="px-4 py-4 text-right text-rose-700 font-medium tabular-nums whitespace-nowrap">-<?= number_format((float) $item["sold"], 2) ?></td>
                                    <td class="px-4 py-4 text-right text-orange-700 font-medium tabular-nums whitespace-nowrap">-<?= number_format((float) $item["used"], 2) ?></td>
                                    <td class="px-4 py-4 text-right text-blue-700 font-bold tabular-nums whitespace-nowrap"><?= number_format((float) $item["ending"], 2) ?></td>
                                    <td class="px-4 py-4 text-center">
                                        <?php if (count($item["source_items"] ?? []) <= 1): ?>
                                            <div class="inline-flex items-center gap-2">
                                                <button type="button" onclick='openUpdateModal(<?= htmlspecialchars(json_encode($item["source_items"][0] ?? $item, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, "UTF-8") ?>)' class="p-1.5 bg-gray-100 hover:bg-blue-50 text-gray-600 hover:text-blue-600 rounded-lg transition" title="Edit" aria-label="Edit <?= htmlspecialchars($item["item_description"]) ?>">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button type="button" onclick="openDeleteModal(<?= (int) $item["inventory_id"] ?>, <?= htmlspecialchars(json_encode($item["item_description"], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, "UTF-8") ?>)" class="p-1.5 bg-gray-100 hover:bg-red-50 text-gray-600 hover:text-red-600 rounded-lg transition" title="Delete" aria-label="Delete <?= htmlspecialchars($item["item_description"]) ?>">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        <?php else: ?>
                                            <details class="relative text-left">
                                                <summary class="cursor-pointer list-none rounded-lg border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">Manage entries</summary>
                                                <div class="absolute right-0 z-20 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-3 shadow-lg">
                                                    <?php foreach ($item["source_items"] as $sourceItem): ?>
                                                        <div class="flex items-center justify-between gap-3 py-2 border-b border-gray-100 last:border-0">
                                                            <span class="min-w-0">
                                                                <span class="block text-xs font-medium text-gray-800"><?= htmlspecialchars((string) $sourceItem["inventory_date"]) ?></span>
                                                                <span class="block truncate text-xs text-gray-500"><?= htmlspecialchars((string) $sourceItem["counted_by"]) ?></span>
                                                            </span>
                                                            <span class="inline-flex shrink-0 gap-1">
                                                                <button type="button" onclick='openUpdateModal(<?= htmlspecialchars(json_encode($sourceItem, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, "UTF-8") ?>)' class="rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-700 hover:bg-blue-50 hover:text-blue-700" aria-label="Edit <?= htmlspecialchars($item["item_description"]) ?>">Edit</button>
                                                                <button type="button" onclick="openDeleteModal(<?= (int) $sourceItem["inventory_id"] ?>, <?= htmlspecialchars(json_encode($item["item_description"], JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, "UTF-8") ?>)" class="rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-700 hover:bg-red-50 hover:text-red-700" aria-label="Delete <?= htmlspecialchars($item["item_description"]) ?>">Delete</button>
                                                            </span>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </details>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr id="noSearchResultsRow" class="hidden">
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">No matching inventory items found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-t border-gray-200 bg-white">
                <label for="inventoryPageSize" class="flex items-center gap-2 text-sm text-gray-600">
                    Rows per page
                    <select id="inventoryPageSize" class="rounded-lg border border-gray-300 bg-white px-2.5 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </label>
                <nav class="flex items-center justify-between sm:justify-end gap-3" aria-label="Inventory pages">
                    <button id="inventoryPreviousPage" type="button" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                        Previous
                    </button>
                    <span id="inventoryPageIndicator" class="min-w-[5rem] text-center text-sm text-gray-600" aria-live="polite">Page 1 of 1</span>
                    <button id="inventoryNextPage" type="button" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                        Next
                    </button>
                </nav>
            </div>
        </div>

    </main>
</div>

<!-- INCLUDE THE SEPARATED MODAL COMPONENT -->
<?php include "KitchenInventoryModal.php"; ?>

<script>
// Modal Toggle Functions
function openAddModal() {
    document.getElementById('addInventoryModal').classList.remove('hidden');
}

function closeAddModal() {
    document.getElementById('addInventoryModal').classList.add('hidden');
}

function openUpdateModal(item) {
    const fields = [
        'inventory_id',
        'inventory_date',
        'counted_by',
        'department',
        'type',
        'item_description',
        'quantity',
        'unit',
        'beginning',
        'purchases',
        'sold_used',
        'ending',
        'remarks'
    ];

    fields.forEach(field => {
        const input = document.getElementById(`update_${field}`);
        if (input) {
            input.value = item[field] ?? '';
        }
    });

    document.getElementById('updateInventoryModal').classList.remove('hidden');
}

function closeUpdateModal() {
    document.getElementById('updateInventoryModal').classList.add('hidden');
}

function openDeleteModal(inventoryId, itemDescription) {
    document.getElementById('delete_inventory_id').value = inventoryId;
    document.getElementById('delete_item_name').textContent = itemDescription;
    document.getElementById('deleteInventoryModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteInventoryModal').classList.add('hidden');
}

window.onclick = function(event) {
    [
        ['addInventoryModal', closeAddModal],
        ['updateInventoryModal', closeUpdateModal],
        ['deleteInventoryModal', closeDeleteModal]
    ].forEach(([modalId, closeModal]) => {
        if (event.target === document.getElementById(modalId)) {
            closeModal();
        }
    });
}

// Live Search Filter Script
const searchInput = document.getElementById('inventorySearch');
const inventoryPageSizeSelect = document.getElementById('inventoryPageSize');
const inventoryPreviousPage = document.getElementById('inventoryPreviousPage');
const inventoryNextPage = document.getElementById('inventoryNextPage');
const inventoryPageIndicator = document.getElementById('inventoryPageIndicator');
let currentInventoryPage = 1;

function filterInventoryRows() {
    if (!searchInput) {
        return;
    }

    const query = searchInput.value.toLowerCase().trim();
    const allRows = Array.from(document.querySelectorAll('.inventory-row'));
    const matchingRows = allRows.filter(row => row.textContent.toLowerCase().includes(query));
    const pageSize = Number(inventoryPageSizeSelect.value);
    const totalPages = Math.max(1, Math.ceil(matchingRows.length / pageSize));
    currentInventoryPage = Math.min(currentInventoryPage, totalPages);
    const startIndex = (currentInventoryPage - 1) * pageSize;
    const endIndex = Math.min(startIndex + pageSize, matchingRows.length);
    const visibleRows = new Set(matchingRows.slice(startIndex, endIndex));

    allRows.forEach(row => {
        row.style.display = visibleRows.has(row) ? '' : 'none';
    });

    const noSearchRow = document.getElementById('noSearchResultsRow');
    if (noSearchRow) {
        noSearchRow.classList.toggle('hidden', matchingRows.length > 0 || allRows.length === 0);
    }

    const resultsCount = document.getElementById('inventoryResultsCount');
    if (resultsCount) {
        const itemLabel = matchingRows.length === 1 ? 'item' : 'items';
        resultsCount.textContent = matchingRows.length === 0
            ? (query ? `No matching ${itemLabel}` : `0 ${itemLabel}`)
            : `Showing ${startIndex + 1}–${endIndex} of ${matchingRows.length} ${itemLabel}`;
    }

    inventoryPageIndicator.textContent = `Page ${currentInventoryPage} of ${totalPages}`;
    inventoryPreviousPage.disabled = currentInventoryPage === 1;
    inventoryNextPage.disabled = currentInventoryPage === totalPages;
}

if (searchInput) {
    searchInput.addEventListener('input', () => {
        currentInventoryPage = 1;
        filterInventoryRows();
    });
}
inventoryPageSizeSelect.addEventListener('change', () => {
    currentInventoryPage = 1;
    filterInventoryRows();
});
inventoryPreviousPage.addEventListener('click', () => {
    if (currentInventoryPage > 1) {
        currentInventoryPage--;
        filterInventoryRows();
    }
});
inventoryNextPage.addEventListener('click', () => {
    currentInventoryPage++;
    filterInventoryRows();
});
filterInventoryRows();

// Chart Script
const inventoryChartData = <?= json_encode($inventoryChartData, JSON_UNESCAPED_SLASHES) ?>;
const currentInventoryDepartment = <?= json_encode($inventoryDepartment, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const currentInventoryDateFilter = <?= json_encode($inventoryDateFilter, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const inventoryOverviewChart = document.getElementById("inventoryOverviewChart");
const chartContainerInner = document.getElementById("chartContainerInner");
let inventoryOverviewChartInstance = null;

if (inventoryOverviewChart) {
    const totalItems = inventoryChartData.labels.length;
    const pixelWidthPerItem = 80;
    const calculatedWidth = Math.max(700, totalItems * pixelWidthPerItem);
    chartContainerInner.style.width = calculatedWidth + "px";

    const ctx = inventoryOverviewChart.getContext("2d");
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, "rgba(59, 130, 246, 0.25)");
    gradient.addColorStop(1, "rgba(59, 130, 246, 0.0)");

    inventoryOverviewChartInstance = new Chart(inventoryOverviewChart, {
        type: "bar",
        data: {
            labels: inventoryChartData.labels,
            datasets: [{
                label: "Quantity",
                data: inventoryChartData.values,
                backgroundColor: gradient,
                borderColor: "#3b82f6",
                borderWidth: 2,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: "rgba(15, 23, 42, 0.9)",
                    titleColor: "#f8fafc",
                    bodyColor: "#e2e8f0",
                    padding: 12,
                    callbacks: {
                        label: function(context) {
                            return " Stock Qty: " + Number(context.raw).toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: { size: 11 },
                        color: "#94a3b8"
                    },
                    grid: { color: "rgba(226, 232, 240, 0.8)" }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        font: { size: 11 },
                        color: "#94a3b8",
                        maxRotation: 25,
                        minRotation: 0
                    }
                }
            }
        }
    });
}

function formatInventoryNumber(value) {
    return Number(value || 0).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function createInventoryCell(className, value) {
    const cell = document.createElement('td');
    cell.className = className;
    cell.textContent = value;
    return cell;
}

function createInventoryRow(item) {
    const row = document.createElement('tr');
    row.className = 'inventory-row border-b border-gray-100 odd:bg-white even:bg-gray-50/70 hover:bg-blue-50/60 transition-colors';
    row.dataset.inventoryId = item.inventory_id;
    row.dataset.inventoryDate = item.inventory_date;
    row.dataset.quantity = item.quantity;
    row.dataset.createdAt = item.created_at || new Date().toISOString();

    const descriptionCell = document.createElement('td');
    descriptionCell.className = 'px-5 py-4';
    const description = document.createElement('div');
    description.className = 'font-semibold text-gray-900 item-desc';
    description.textContent = item.item_description;
    descriptionCell.append(description);
    row.appendChild(descriptionCell);
    const quantityCell = document.createElement('td');
    quantityCell.className = 'px-4 py-4 text-right font-semibold tabular-nums whitespace-nowrap';
    const quantity = Number(item.quantity || 0);
    if (quantity <= 5) {
        const lowStockLabel = document.createElement('span');
        lowStockLabel.className = 'inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-2.5 py-1 text-amber-800';
        lowStockLabel.title = 'Low stock';
        lowStockLabel.appendChild(document.createElement('span'));
        lowStockLabel.firstChild.className = 'h-1.5 w-1.5 rounded-full bg-amber-500';
        lowStockLabel.firstChild.setAttribute('aria-hidden', 'true');
        lowStockLabel.append(formatInventoryNumber(quantity));
        quantityCell.appendChild(lowStockLabel);
    } else {
        quantityCell.textContent = formatInventoryNumber(quantity);
        quantityCell.classList.add('text-gray-900');
    }
    row.appendChild(quantityCell);
    row.appendChild(createInventoryCell('px-4 py-4 text-center text-gray-600 item-unit whitespace-nowrap', item.unit));
    row.appendChild(createInventoryCell('px-4 py-4 text-right text-gray-700 tabular-nums whitespace-nowrap', formatInventoryNumber(item.beginning)));
    row.appendChild(createInventoryCell('px-4 py-4 text-right text-emerald-700 font-medium tabular-nums whitespace-nowrap', '+' + formatInventoryNumber(item.purchases)));
    row.appendChild(createInventoryCell('px-4 py-4 text-right text-rose-700 font-medium tabular-nums whitespace-nowrap', '-' + formatInventoryNumber(item.sold_used)));
    row.appendChild(createInventoryCell('px-4 py-4 text-right text-orange-700 font-medium tabular-nums whitespace-nowrap', '-' + formatInventoryNumber(item.used)));
    row.appendChild(createInventoryCell('px-4 py-4 text-right text-blue-700 font-bold tabular-nums whitespace-nowrap', formatInventoryNumber(item.ending)));
    const actionsCell = document.createElement('td');
    actionsCell.className = 'px-4 py-4 text-center';
    const actions = document.createElement('div');
    actions.className = 'inline-flex items-center gap-2';
    const editButton = document.createElement('button');
    editButton.type = 'button';
    editButton.className = 'p-1.5 bg-gray-100 hover:bg-blue-50 text-gray-600 hover:text-blue-600 rounded-lg transition';
    editButton.title = 'Edit';
    editButton.setAttribute('aria-label', `Edit ${item.item_description}`);
    editButton.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>';
    editButton.addEventListener('click', () => openUpdateModal(item));
    const deleteButton = document.createElement('button');
    deleteButton.type = 'button';
    deleteButton.className = 'p-1.5 bg-gray-100 hover:bg-red-50 text-gray-600 hover:text-red-600 rounded-lg transition';
    deleteButton.title = 'Delete';
    deleteButton.setAttribute('aria-label', `Delete ${item.item_description}`);
    deleteButton.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>';
    deleteButton.addEventListener('click', () => openDeleteModal(item.inventory_id, item.item_description));
    actions.append(editButton, deleteButton);
    actionsCell.appendChild(actions);
    row.appendChild(actionsCell);
    return row;
}

function updateInventorySummary() {
    const rows = Array.from(document.querySelectorAll('.inventory-row'));
    const totalQuantity = rows.reduce((sum, row) => sum + Number(row.dataset.quantity || 0), 0);
    const lowStock = rows.filter(row => Number(row.dataset.quantity || 0) <= 5).length;
    const recentCutoff = Date.now() - 7 * 24 * 60 * 60 * 1000;
    const recentAdditions = rows.filter(row => {
        const createdAt = Date.parse(row.dataset.createdAt || '');
        return Number.isFinite(createdAt) && createdAt >= recentCutoff;
    }).length;

    document.getElementById('totalItemsCount').textContent = rows.length.toLocaleString();
    document.getElementById('totalStockQuantity').textContent = formatInventoryNumber(totalQuantity);
    document.getElementById('lowStockCount').textContent = lowStock.toLocaleString();
    document.getElementById('recentAdditionsCount').textContent = recentAdditions.toLocaleString();

    if (inventoryOverviewChartInstance) {
        const chartRows = rows.slice(0, 10);
        inventoryOverviewChartInstance.data.labels = chartRows.map(row => row.querySelector('.item-desc').textContent);
        inventoryOverviewChartInstance.data.datasets[0].data =
            chartRows.map(row => Number(row.dataset.quantity || 0));
        inventoryOverviewChartInstance.update();
    }
}

function updateInventoryTable(item, action) {
    const tableBody = document.getElementById('inventoryTableBody');
    if (action === 'add') {
        currentInventoryPage = 1;
    }
    const existingRow = Array.from(tableBody.querySelectorAll('.inventory-row'))
        .find(row => Number(row.dataset.inventoryId) === Number(item.inventory_id));

    if (action === 'delete' || item.department !== currentInventoryDepartment
        || (currentInventoryDateFilter && item.inventory_date !== currentInventoryDateFilter)) {
        existingRow?.remove();
    } else {
        if (action === 'update' && existingRow) {
            item.created_at = existingRow.dataset.createdAt;
        }
        const newRow = createInventoryRow(item);
        if (action === 'add' || !existingRow) {
            tableBody.prepend(newRow);
        } else {
            existingRow.replaceWith(newRow);
        }
    }

    let emptyRow = tableBody.querySelector('[data-inventory-empty-row]');
    const inventoryRows = tableBody.querySelectorAll('.inventory-row');
    if (inventoryRows.length === 0) {
        if (!emptyRow) {
            emptyRow = document.createElement('tr');
            emptyRow.id = 'noInventoryRow';
            emptyRow.dataset.inventoryEmptyRow = '';
            const emptyCell = createInventoryCell('px-6 py-8 text-center text-gray-500', 'No inventory items found.');
            emptyCell.colSpan = 9;
            emptyRow.appendChild(emptyCell);
            tableBody.prepend(emptyRow);
        }
    } else {
        emptyRow?.remove();
    }

    filterInventoryRows();
    updateInventorySummary();
}

document.querySelectorAll('form[data-inventory-form]').forEach(form => {
    form.addEventListener('submit', async event => {
        event.preventDefault();
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Could not save inventory changes.');
            }

            try {
                sessionStorage.setItem('inventoryMutationMessage', JSON.stringify({
                    message: payload.message || 'Inventory updated successfully.',
                    type: 'success'
                }));
            } catch (storageError) {
                console.error('Could not retain the inventory success message after reload.', storageError);
            }
            window.location.reload();
        } catch (error) {
            window.showToast(error.message || 'Could not save inventory changes.', 'error');
        } finally {
            button.disabled = false;
        }
    });
});

const inventorySavedDates = new Set(<?= json_encode($inventorySavedDates, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
let inventoryCheckedDates = new Set();
try {
    const checkedDates = JSON.parse(sessionStorage.getItem('inventoryCheckedDates') || '[]');
    if (Array.isArray(checkedDates)) {
        inventoryCheckedDates = new Set(checkedDates.filter(date => /^\d{4}-\d{2}-\d{2}$/.test(date)));
    }
} catch (storageError) {
    console.error('Could not read the completed inventory sync dates.', storageError);
}
let inventorySyncInProgress = false;

function markInventoryDateChecked(syncDate) {
    inventoryCheckedDates.add(syncDate);
    try {
        sessionStorage.setItem('inventoryCheckedDates', JSON.stringify(Array.from(inventoryCheckedDates)));
    } catch (storageError) {
        console.error('Could not retain the completed inventory sync date.', storageError);
    }
}

async function requestInventorySync(syncDate) {
    const form = document.getElementById('inventorySyncForm');
    const formData = new FormData(form);
    formData.set('sync_date', syncDate);

    const response = await fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: { 'Accept': 'application/json' },
        cache: 'no-store'
    });
    const payload = await response.json();
    if (!response.ok || !payload.success) {
        throw new Error(payload.message || `Could not sync inventory for ${syncDate}.`);
    }
    return payload;
}

function reloadAfterInventorySync(message, type = 'success') {
    try {
        sessionStorage.setItem('inventoryMutationMessage', JSON.stringify({ message, type }));
    } catch (storageError) {
        console.error('Could not retain the sync result after reload.', storageError);
        window.showToast(message, type);
    }
    window.location.reload();
}

function getMissingInventoryDates() {
    const now = new Date();
    const today = [
        now.getFullYear(),
        String(now.getMonth() + 1).padStart(2, '0'),
        String(now.getDate()).padStart(2, '0')
    ].join('-');
    const savedDates = Array.from(inventorySavedDates).sort();
    const firstDate = savedDates.length > 0
        ? savedDates[0]
        : today;
    const [startYear, startMonth, startDay] = firstDate.split('-').map(Number);
    const [endYear, endMonth, endDay] = today.split('-').map(Number);
    const currentDate = new Date(Date.UTC(startYear, startMonth - 1, startDay));
    const endDate = new Date(Date.UTC(endYear, endMonth - 1, endDay));
    const missingDates = [];

    while (currentDate <= endDate) {
        const dateString = currentDate.toISOString().slice(0, 10);
        if (!inventorySavedDates.has(dateString) && !inventoryCheckedDates.has(dateString)) {
            missingDates.push(dateString);
        }
        currentDate.setUTCDate(currentDate.getUTCDate() + 1);
    }

    return missingDates;
}

async function syncStoreHubInventory() {
    if (inventorySyncInProgress) {
        return;
    }

    const dateInput = document.getElementById('inventorySyncDate');
    const syncButton = document.getElementById('inventorySyncButton');
    inventorySyncInProgress = true;
    syncButton.disabled = true;
    dateInput.disabled = true;
    syncButton.textContent = 'Syncing...';

    try {
        const payload = await requestInventorySync(dateInput.value);
        if (payload.skipped) {
            markInventoryDateChecked(dateInput.value);
            window.showToast(payload.message || 'Inventory for this date is already saved.');
            return;
        }

        markInventoryDateChecked(dateInput.value);
        reloadAfterInventorySync(payload.message || 'StoreHub inventory synced successfully.');
    } catch (error) {
        window.showToast(error.message || 'Could not sync StoreHub transactions.', 'error');
    } finally {
        inventorySyncInProgress = false;
        syncButton.disabled = false;
        dateInput.disabled = false;
        syncButton.textContent = 'Sync date';
    }
}

async function syncMissingInventoryDates() {
    if (inventorySyncInProgress) {
        return;
    }

    const missingDates = getMissingInventoryDates();
    if (missingDates.length === 0) {
        return;
    }

    const dateInput = document.getElementById('inventorySyncDate');
    const syncButton = document.getElementById('inventorySyncButton');
    inventorySyncInProgress = true;
    syncButton.disabled = true;
    dateInput.disabled = true;
    syncButton.textContent = `Syncing 0/${missingDates.length}...`;
    let syncedCount = 0;
    let failedDate = '';
    let failureMessage = '';

    try {
        for (let index = 0; index < missingDates.length; index += 1) {
            const syncDate = missingDates[index];
            syncButton.textContent = `Syncing ${index + 1}/${missingDates.length}...`;
            const payload = await requestInventorySync(syncDate);
            markInventoryDateChecked(syncDate);
            if (!payload.skipped) {
                const hasSavedProducts = Array.isArray(payload.inventory) && payload.inventory.length > 0;
                if (hasSavedProducts) {
                    inventorySavedDates.add(syncDate);
                    syncedCount += 1;
                }
            } else {
                inventorySavedDates.add(syncDate);
            }
        }

        if (syncedCount > 0) {
            reloadAfterInventorySync(`Caught up inventory: ${syncedCount} missing date${syncedCount === 1 ? '' : 's'} synced.`);
            return;
        }
    } catch (error) {
        failedDate = missingDates.find(date => !inventoryCheckedDates.has(date)) || '';
        failureMessage = error.message || 'Could not sync missing inventory dates.';
    } finally {
        inventorySyncInProgress = false;
        syncButton.disabled = false;
        dateInput.disabled = false;
        syncButton.textContent = 'Sync date';
    }

    if (failureMessage) {
        const message = failedDate
            ? `Automatic catch-up stopped on ${failedDate}: ${failureMessage}`
            : failureMessage;
        if (syncedCount > 0) {
            reloadAfterInventorySync(message, 'error');
            return;
        }
        window.showToast(message, 'error');
    }
}

document.getElementById('inventorySyncForm').addEventListener('submit', event => {
    event.preventDefault();
    syncStoreHubInventory();
});
document.getElementById('inventorySyncDate').addEventListener('change', syncStoreHubInventory);
syncMissingInventoryDates();
window.setInterval(syncMissingInventoryDates, 7 * 60 * 60 * 1000);
</script>

<script>
try {
    const inventoryMutationMessage = sessionStorage.getItem('inventoryMutationMessage');
    if (inventoryMutationMessage) {
        sessionStorage.removeItem('inventoryMutationMessage');
        let result;
        try {
            result = JSON.parse(inventoryMutationMessage);
        } catch {
            result = { message: inventoryMutationMessage, type: 'success' };
        }
        window.showToast(result.message, result.type || 'success');
    }
} catch (storageError) {
    console.error('Could not read the previous inventory sync result.', storageError);
}
</script>

<?php if ($success_message !== "" || $error_message !== ""): ?>
    <script>
        window.showToast(
            <?= json_encode($success_message !== "" ? $success_message : $error_message, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
            <?= json_encode($error_message !== "" ? "error" : "success") ?>
        );
    </script>
<?php endif; ?>

<?php include "../Components/footer.php"; ?>
</body>
</html>