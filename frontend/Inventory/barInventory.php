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
    <main class="p-6">

       <!-- STATISTICS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
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
            <div class="p-6 border-b border-gray-200 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900"><?= htmlspecialchars($page_title) ?></h3>
                    <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($page_description) ?></p>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <form method="get" class="flex w-full sm:w-auto items-center gap-2">
                        <label for="inventoryDateFilter" class="text-sm text-gray-600 whitespace-nowrap">Date:</label>
                        <input
                            type="date"
                            id="inventoryDateFilter"
                            name="inventory_date"
                            value="<?= htmlspecialchars($inventoryDateFilter, ENT_QUOTES, "UTF-8") ?>"
                            onchange="this.form.submit()"
                            class="w-full sm:w-auto px-3 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                        <?php if ($inventoryDateFilter !== ""): ?>
                            <a href="<?= htmlspecialchars(strtok($_SERVER["REQUEST_URI"], "?"), ENT_QUOTES, "UTF-8") ?>" class="text-sm text-blue-600 hover:underline whitespace-nowrap">All dates</a>
                        <?php endif; ?>
                    </form>
                    <!-- SEARCH BOX -->
                    <div class="relative w-full sm:w-72">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input type="text" id="inventorySearch" placeholder="Search inventory items..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition">
                    </div>

                    <!-- OPEN MODAL BUTTON -->
                    <button onclick="openAddModal()" type="button" class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm rounded-xl transition shadow-xs gap-2 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add New Item
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table id="inventoryTable" class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-6 py-4 font-semibold text-gray-600">Item Description</th>
                            <th class="text-center px-6 py-4 font-semibold text-gray-600">Quantity</th>
                            <th class="text-center px-6 py-4 font-semibold text-gray-600">Unit</th>
                            <th class="text-center px-6 py-4 font-semibold text-gray-600">Beginning</th>
                            <th class="text-center px-6 py-4 font-semibold text-gray-600">Purchases</th>
                            <th class="text-center px-6 py-4 font-semibold text-gray-600">Sold</th>
                            <th class="text-center px-6 py-4 font-semibold text-gray-600">Used</th>
                            <th class="text-center px-6 py-4 font-semibold text-gray-600">Ending</th>
                            <th class="text-left px-6 py-4 font-semibold text-gray-600">Remarks</th>
                            <th class="text-center px-6 py-4 font-semibold text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inventoryTableBody" class="divide-y divide-gray-100">
                        <?php if (empty($inventoryItems)): ?>
                            <tr id="noInventoryRow" data-inventory-empty-row>
                                <td colspan="12" class="px-6 py-8 text-center text-gray-500">No inventory items found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($inventoryItems as $item): ?>
                                <tr class="inventory-row hover:bg-gray-50" data-inventory-id="<?= (int) $item["inventory_id"] ?>" data-inventory-date="<?= htmlspecialchars((string) $item["inventory_date"], ENT_QUOTES, "UTF-8") ?>" data-quantity="<?= htmlspecialchars((string) $item["quantity"], ENT_QUOTES, "UTF-8") ?>" data-created-at="<?= htmlspecialchars((string) $item["created_at"], ENT_QUOTES, "UTF-8") ?>">
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-gray-900 item-desc"><?= htmlspecialchars($item["item_description"]) ?></div>
                                        <div class="text-xs text-gray-500">Inventory date: <?= htmlspecialchars((string) $item["inventory_date"]) ?></div>
                                    </td>
                                    <td class="px-6 py-4 text-center font-bold text-gray-900"><?= number_format((float) $item["quantity"], 2) ?></td>
                                    <td class="px-6 py-4 text-center text-gray-600 item-unit"><?= htmlspecialchars($item["unit"]) ?></td>
                                    <td class="px-6 py-4 text-center text-gray-600"><?= number_format((float) $item["beginning"], 2) ?></td>
                                    <td class="px-6 py-4 text-center text-green-600 font-medium">+<?= number_format((float) $item["sold"], 2) ?></td>
                                    <td class="px-6 py-4 text-center text-red-600 font-medium">-<?= number_format((float) $item["purchases"], 2) ?></td>
                                    <td class="px-6 py-4 text-center text-orange-600 font-medium">-<?= number_format((float) $item["used"], 2) ?></td>
                                    <td class="px-6 py-4 text-center font-bold text-blue-600"><?= number_format((float) $item["ending"], 2) ?></td>
                                    <td class="px-6 py-4 text-gray-600 text-xs italic item-remarks"><?= htmlspecialchars($item["remarks"] ?? "—") ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="inline-flex items-center gap-2">
                                            <button type="button" onclick='openUpdateModal(<?= htmlspecialchars(json_encode($item, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP), ENT_QUOTES, "UTF-8") ?>)' class="p-1.5 bg-gray-100 hover:bg-blue-50 text-gray-600 hover:text-blue-600 rounded-lg transition" title="Edit" aria-label="Edit <?= htmlspecialchars($item["item_description"]) ?>">
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
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <tr id="noSearchResultsRow" class="hidden">
                            <td colspan="12" class="px-6 py-8 text-center text-gray-500">No matching inventory items found.</td>
                        </tr>
                    </tbody>
                </table>
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
function filterInventoryRows() {
    if (!searchInput) {
        return;
    }

    const query = searchInput.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.inventory-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const visible = row.textContent.toLowerCase().includes(query);
        row.style.display = visible ? '' : 'none';
        if (visible) {
            visibleCount++;
        }
    });

    const noSearchRow = document.getElementById('noSearchResultsRow');
    if (noSearchRow) {
        noSearchRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
    }
}

if (searchInput) {
    searchInput.addEventListener('input', filterInventoryRows);
}

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
    row.className = 'inventory-row hover:bg-gray-50';
    row.dataset.inventoryId = item.inventory_id;
    row.dataset.inventoryDate = item.inventory_date;
    row.dataset.quantity = item.quantity;
    row.dataset.createdAt = item.created_at || new Date().toISOString();

    const descriptionCell = document.createElement('td');
    descriptionCell.className = 'px-6 py-4';
    const description = document.createElement('div');
    description.className = 'font-semibold text-gray-900 item-desc';
    description.textContent = item.item_description;
    const metadata = document.createElement('div');
    metadata.className = 'text-xs text-gray-500';
    metadata.append('Counted by: ');
    const countedBy = document.createElement('span');
    countedBy.className = 'counted-by';
    countedBy.textContent = item.counted_by;
    metadata.append(countedBy, ' (');
    const inventoryDate = document.createElement('span');
    inventoryDate.className = 'inv-date';
    inventoryDate.textContent = item.inventory_date;
    metadata.append(inventoryDate, ')');
    descriptionCell.append(description, metadata);
    row.appendChild(descriptionCell);
    row.appendChild(createInventoryCell('px-6 py-4 text-gray-600 item-department', item.department));
    row.appendChild(createInventoryCell('px-6 py-4 text-gray-600 item-type', item.type));
    row.appendChild(createInventoryCell('px-6 py-4 text-center font-bold text-gray-900', formatInventoryNumber(item.quantity)));
    row.appendChild(createInventoryCell('px-6 py-4 text-center text-gray-600 item-unit', item.unit));
    row.appendChild(createInventoryCell('px-6 py-4 text-center text-gray-600', formatInventoryNumber(item.beginning)));
    row.appendChild(createInventoryCell('px-6 py-4 text-center text-green-600 font-medium', '+' + formatInventoryNumber(item.purchases)));
    row.appendChild(createInventoryCell('px-6 py-4 text-center text-red-600 font-medium', '-' + formatInventoryNumber(item.sold_used)));
    row.appendChild(createInventoryCell('px-6 py-4 text-center text-orange-600 font-medium', '-0.00'));
    row.appendChild(createInventoryCell('px-6 py-4 text-center font-bold text-blue-600', formatInventoryNumber(item.ending)));
    row.appendChild(createInventoryCell('px-6 py-4 text-gray-600 text-xs italic item-remarks', item.remarks || '—'));

    const actionsCell = document.createElement('td');
    actionsCell.className = 'px-6 py-4 text-center';
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
            emptyCell.colSpan = 12;
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

            updateInventoryTable(payload.item || { inventory_id: payload.inventory_id }, payload.action);
            window.showToast(payload.message || 'Inventory updated successfully.');
            if (payload.action === 'add') {
                form.reset();
                closeAddModal();
            } else if (payload.action === 'update') {
                closeUpdateModal();
            } else if (payload.action === 'delete') {
                closeDeleteModal();
            }
        } catch (error) {
            window.showToast(error.message || 'Could not save inventory changes.', 'error');
        } finally {
            button.disabled = false;
        }
    });
});
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