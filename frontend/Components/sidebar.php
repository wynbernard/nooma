<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside
    id="sidebar"
    class="fixed left-0 top-0 z-40 w-64 h-screen
           bg-white border-r border-gray-200
           transform -translate-x-full
           lg:translate-x-0
           transition-transform duration-300"
>

    <!-- LOGO -->
    <div class="h-20 flex items-center px-6 border-b">

        <a href="admin.php" class="flex items-center gap-3">

            <div
                class="w-10 h-10 bg-blue-600 rounded-xl
                       flex items-center justify-center"
            >
                <span class="text-white text-xl font-bold">
                    N
                </span>
            </div>

            <span class="text-xl font-bold">
                Nooma
            </span>

        </a>

    </div>


    <!-- NAVIGATION -->
    <nav class="p-4 space-y-1">

        <p class="px-3 pt-2 pb-3 text-xs font-semibold
                  text-gray-400 uppercase tracking-wider">
            Main
        </p>


        <!-- Dashboard -->
        <a
            href="../Dashboard/admin.php"
            class="flex items-center gap-3 px-3 py-3 rounded-xl
            <?= $current_page === 'admin.php'
                ? 'bg-blue-50 text-blue-600 font-medium'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' ?>"
        >
            <span class="text-lg">▦</span>
            Dashboard
        </a>


        <!-- Sales -->
        <a
            href="../Sales/sales.php    "
            class="flex items-center gap-3 px-3 py-3 rounded-xl
            <?= $current_page === 'sales.php'
                ? 'bg-blue-50 text-blue-600 font-medium'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' ?>"
        >
            <span class="text-lg">🛒</span>
            Sales
        </a>

        <!-- Sales Comparison -->
            <a 
                href="../Inventory/inventory.php" 
                class="flex items-center gap-3 px-3 py-3 rounded-xl 
                <?= $current_page === 'inventory.php' 
                    ? 'bg-blue-50 text-blue-600 font-medium' 
                    : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' ?>" 
            >
                <span class="text-lg">📈</span>
                Sales Comparison
            </a>


        <!-- Owners Accounts -->
        <a
            href="../OwnersAccount/ownerAccount.php"
            class="flex items-center gap-3 px-3 py-3 rounded-xl
            <?= $current_page === 'ownerAccount.php'
                ? 'bg-blue-50 text-blue-600 font-medium'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' ?>"
        >
            <span class="text-lg">👤</span> 
            Owner Account
        </a>


        <!-- Daily Summary -->
        <a
            href="../Daily_Summary/dailySummary.php"
            class="flex items-center gap-3 px-3 py-3 rounded-xl
            <?= $current_page === 'dailySummary.php'
                ? 'bg-blue-50 text-blue-600 font-medium'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' ?>"
        >
            <span class="text-lg">▤</span>
            Compiled Daily
        </a>


        <!-- Customers -->
        <a
            href="../Summary/summary.php"
            class="flex items-center gap-3 px-3 py-3 rounded-xl
            <?= $current_page === 'summary.php'
                ? 'bg-blue-50 text-blue-600 font-medium'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' ?>"
        >
            <span class="text-lg">📊</span>
            Summary
        </a>

        <!-- Monthly Summary -->
        <a
            href="../Monthly_summary/monthlySummary.php"
            class="flex items-center gap-3 px-3 py-3 rounded-xl
            <?= $current_page === 'monthlySummary.php'
                ? 'bg-blue-50 text-blue-600 font-medium'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' ?>"
        >
            <span class="text-lg">▥</span>
            Monthly Summary
        </a>


        <!-- <p class="px-3 pt-7 pb-3 text-xs font-semibold
                  text-gray-400 uppercase tracking-wider">
            Management
        </p> --> 

        <!-- Users -->
       <!-- Unpaid Accounts -->
        <a  
            href="../UnpaidAccount/UnpaidAccount.php"  
            class="flex items-center gap-3 px-3 py-3 rounded-xl  
            <?= $current_page === 'UnpaidAccount.php'  
                ? 'bg-blue-50 text-blue-600 font-medium'  
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' ?>"  
        >
            <span class="text-lg">💰</span>  
            Unpaid Accounts  
        </a>


        <!-- Reports -->
        <a
            href="../Tip/tip.php"
            class="flex items-center gap-3 px-3 py-3 rounded-xl
            <?= $current_page === 'tip.php'
                ? 'bg-blue-50 text-blue-600 font-medium'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' ?>"
        >
            <span class="text-lg">📊</span>
            Tip
        </a>


        <!-- Settings -->
        <!-- <a
            href="settings.php"
            class="flex items-center gap-3 px-3 py-3 rounded-xl
            <?= $current_page === 'settings.php'
                ? 'bg-blue-50 text-blue-600 font-medium'
                : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' ?>"
        >
            <span class="text-lg">⚙</span>
            Settings
        </a> -->

    </nav>

</aside>


<!-- MOBILE OVERLAY -->

<div
    id="overlay"
    onclick="toggleSidebar()"
    class="fixed inset-0 bg-black/30 z-30 hidden lg:hidden"
></div>
