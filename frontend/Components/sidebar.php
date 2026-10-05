<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- SIDEBAR NAVIGATION -->
<aside
    id="sidebar"
    class="fixed left-0 top-0 z-40 w-64 h-screen bg-white border-r border-gray-200/80 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out flex flex-col justify-between"
>
    <div>
        <!-- LOGO BRANDING -->
        <div class="h-20 flex items-center px-6 border-b border-gray-100">
            <a href="admin.php" class="flex items-center gap-3 group">
                <div class="w-10 h-10 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-xl shadow-md shadow-blue-500/20 flex items-center justify-center transition-transform group-hover:scale-105">
                    <span class="text-white text-lg font-bold tracking-wider">N</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-base font-bold text-gray-900 tracking-tight">Nooma</span>
                    <span class="text-[10px] font-medium text-gray-400 uppercase tracking-widest">Admin Portal</span>
                </div>
            </a>
        </div>

        <!-- NAVIGATION LINKS -->
        <nav class="p-4 space-y-1.5 overflow-y-auto max-h-[calc(100vh-5rem)]">
            <p class="px-3 pt-2 pb-2 text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                Main Menu
            </p>

            <?php
            // Navigation item helper array to keep the code clean and manageable
            $navItems = [
                ['name' => 'Dashboard', 'file' => 'admin.php', 'url' => '../Dashboard/admin.php', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                ['name' => 'Sales', 'file' => 'sales.php', 'url' => '../Sales/sales.php', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                ['name' => 'Sales Comparison', 'file' => 'inventory.php', 'url' => '../Inventory/inventory.php', 'icon' => 'M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z'],
                ['name' => 'Owner Account', 'file' => 'ownerAccount.php', 'url' => '../OwnersAccount/ownerAccount.php', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                ['name' => 'Compiled Daily', 'file' => 'dailySummary.php', 'url' => '../Daily_Summary/dailySummary.php', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                ['name' => 'Summary', 'file' => 'summary.php', 'url' => '../Summary/summary.php', 'icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055zM20.488 9H15V3.512A9.025 9.025 0 0120.488 9z'],
                ['name' => 'Monthly Summary', 'file' => 'monthlySummary.php', 'url' => '../Monthly_summary/monthlySummary.php', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                ['name' => 'Unpaid Accounts', 'file' => 'UnpaidAccount.php', 'url' => '../UnpaidAccount/UnpaidAccount.php', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['name' => 'Tip', 'file' => 'tip.php', 'url' => '../Tip/tip.php', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z']
            ];

            foreach ($navItems as $item): 
                $isActive = ($current_page === $item['file']);
            ?>
                <a
                    href="<?= $item['url'] ?>"
                    class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium transition-all duration-200 <?= $isActive ? 'bg-blue-50/80 text-blue-600 font-semibold shadow-2xs' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>"
                >
                    <!-- Active Left Accent Pill -->
                    <?php if ($isActive): ?>
                        <div class="absolute left-0 inset-y-1.5 w-1 bg-blue-600 rounded-r-full"></div>
                    <?php endif; ?>

                    <!-- Icon -->
                    <svg class="w-4 h-4 transition-transform group-hover:scale-110 <?= $isActive ? 'text-blue-600' : 'text-gray-400 group-hover:text-gray-600' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?= $item['icon'] ?>"></path>
                    </svg>
                    
                    <span class="truncate"><?= $item['name'] ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>

    <!-- User Footer Profile Widget (Optional extra pro touch) -->
    <div class="p-4 border-t border-gray-100">
        <div class="flex items-center gap-3 px-3 py-2 bg-gray-50/80 rounded-xl border border-gray-100">
            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs">
                AD
            </div>
            <div class="flex flex-col min-w-0">
                <span class="text-xs font-semibold text-gray-800 truncate">Administrator</span>
                <span class="text-[10px] text-gray-400 truncate">Active Session</span>
            </div>
        </div>
    </div>
</aside>

<!-- MOBILE BACKDROP OVERLAY -->
<div
    id="overlay"
    onclick="toggleSidebar()"
    class="fixed inset-0 bg-gray-900/40 backdrop-blur-xs z-30 hidden lg:hidden transition-opacity duration-300"
></div>