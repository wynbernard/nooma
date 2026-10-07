<?php 
$current_page = basename($_SERVER['PHP_SELF']); 
?>

<aside 
    id="sidebar"
    class="fixed left-0 top-0 z-40 w-64 h-screen bg-white border-r border-gray-200/80 
           transform -translate-x-full lg:translate-x-0 transition-transform duration-300 
           ease-in-out flex flex-col justify-between"
>

    <div>

        <!-- LOGO -->
        <div class="h-20 flex items-center px-6 border-b border-gray-100">
            <a href="admin.php" class="flex items-center gap-3 group">

                <div class="w-10 h-10 bg-gradient-to-tr from-blue-600 to-indigo-600 
                            rounded-xl shadow-md shadow-blue-500/20 
                            flex items-center justify-center 
                            transition-transform group-hover:scale-105">

                    <span class="text-white text-lg font-bold tracking-wider">
                        N
                    </span>

                </div>

                <div class="flex flex-col">
                    <span class="text-base font-bold text-gray-900 tracking-tight">
                        Nooma
                    </span>

                    <span class="text-[10px] font-medium text-gray-400 
                                 uppercase tracking-widest">
                        Admin Portal
                    </span>
                </div>

            </a>
        </div>


        <!-- NAVIGATION -->
        <nav class="p-4 space-y-1.5 overflow-y-auto max-h-[calc(100vh-5rem)]">

            <!-- MAIN MENU -->
            <p class="px-3 pt-2 pb-2 text-[11px] font-bold text-gray-400 
                      uppercase tracking-wider">
                Main Menu
            </p>


            <!-- DASHBOARD -->
            <?php $isActive = ($current_page === 'admin.php'); ?>

            <a href="../Dashboard/admin.php"
               class="group relative flex items-center gap-3 px-3.5 py-2.5 rounded-xl 
                      text-xs font-medium transition-all duration-200
                      <?= $isActive 
                          ? 'bg-blue-50/80 text-blue-600 font-semibold' 
                          : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>">

                <?php if ($isActive): ?>
                    <div class="absolute left-0 inset-y-1.5 w-1 bg-blue-600 rounded-r-full"></div>
                <?php endif; ?>

                <svg class="w-4 h-4 <?= $isActive ? 'text-blue-600' : 'text-gray-400' ?>"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10h14V10">
                    </path>

                </svg>

                <span>Dashboard</span>

            </a>


            <!-- ================= SALES ================= -->

            <?php
            $salesPages = [
                'sales.php',
                'compared.php',
                'ownerAccount.php',
                'dailySummary.php',
                'summary.php',
                'monthlySummary.php',
                'UnpaidAccount.php'
            ];

            $salesActive = in_array($current_page, $salesPages);
            ?>

            <button
                type="button"
                onclick="toggleMenu('salesMenu', 'salesArrow')"
                class="w-full group flex items-center justify-between 
                       gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium 
                       transition-all duration-200
                       <?= $salesActive
                           ? 'bg-blue-50/80 text-blue-600 font-semibold'
                           : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>"
            >

                <div class="flex items-center gap-3">

                    <svg class="w-4 h-4 
                               <?= $salesActive 
                                   ? 'text-blue-600' 
                                   : 'text-gray-400' ?>"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M3 3v18h18M7 16l4-4 3 3 5-6">
                        </path>

                    </svg>

                    <span>Sales</span>

                </div>


                <!-- ARROW -->
                <svg id="salesArrow"
                     class="w-4 h-4 transition-transform duration-200 
                            <?= $salesActive ? 'rotate-180' : '' ?>"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 9l-7 7-7-7">
                    </path>

                </svg>

            </button>


            <!-- SALES SUBMENU -->
            <div
                id="salesMenu"
                class="<?= $salesActive ? '' : 'hidden' ?> ml-7 mt-1 space-y-1"
            >

                <a href="../Sales/sales.php"
                   class="block px-3 py-2 rounded-lg text-xs
                   <?= $current_page === 'sales.php'
                       ? 'bg-blue-50 text-blue-600 font-semibold'
                       : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' ?>">
                    Sales
                </a>


                <a href="../Compared_sale/compared.php"
                   class="block px-3 py-2 rounded-lg text-xs
                   <?= $current_page === 'compared.php'
                       ? 'bg-blue-50 text-blue-600 font-semibold'
                       : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' ?>">
                    Sales Comparison
                </a>


                <a href="../OwnersAccount/ownerAccount.php"
                   class="block px-3 py-2 rounded-lg text-xs
                   <?= $current_page === 'ownerAccount.php'
                       ? 'bg-blue-50 text-blue-600 font-semibold'
                       : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' ?>">
                    Owner Account
                </a>


                <a href="../Daily_Summary/dailySummary.php"
                   class="block px-3 py-2 rounded-lg text-xs
                   <?= $current_page === 'dailySummary.php'
                       ? 'bg-blue-50 text-blue-600 font-semibold'
                       : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' ?>">
                    Compiled Daily
                </a>


                <a href="../Summary/summary.php"
                   class="block px-3 py-2 rounded-lg text-xs
                   <?= $current_page === 'summary.php'
                       ? 'bg-blue-50 text-blue-600 font-semibold'
                       : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' ?>">
                    Summary
                </a>


                <a href="../Monthly_summary/monthlySummary.php"
                   class="block px-3 py-2 rounded-lg text-xs
                   <?= $current_page === 'monthlySummary.php'
                       ? 'bg-blue-50 text-blue-600 font-semibold'
                       : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' ?>">
                    Monthly Summary
                </a>


                <a href="../UnpaidAccount/UnpaidAccount.php"
                   class="block px-3 py-2 rounded-lg text-xs
                   <?= $current_page === 'UnpaidAccount.php'
                       ? 'bg-blue-50 text-blue-600 font-semibold'
                       : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' ?>">
                    Unpaid Accounts
                </a>

            </div>


            <!-- ================= INVENTORY ================= -->

            <?php
            $inventoryPages = [
                'inventory.php'
            ];

            $inventoryActive = in_array($current_page, $inventoryPages);
            ?>


            <button
                type="button"
                onclick="toggleMenu('inventoryMenu', 'inventoryArrow')"
                class="w-full group flex items-center justify-between 
                       gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium 
                       transition-all duration-200
                       <?= $inventoryActive
                           ? 'bg-blue-50/80 text-blue-600 font-semibold'
                           : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>"
            >

                <div class="flex items-center gap-3">

                    <svg class="w-4 h-4
                               <?= $inventoryActive
                                   ? 'text-blue-600'
                                   : 'text-gray-400' ?>"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4">
                        </path>

                    </svg>

                    <span>Inventory</span>

                </div>


                <svg id="inventoryArrow"
                     class="w-4 h-4 transition-transform duration-200
                            <?= $inventoryActive ? 'rotate-180' : '' ?>"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 9l-7 7-7-7">
                    </path>

                </svg>

            </button>


            <!-- INVENTORY SUBMENU -->
            <div
                id="inventoryMenu"
                class="<?= $inventoryActive ? '' : 'hidden' ?> ml-7 mt-1 space-y-1"
            >

                <a href="../Inventory/barInventory.php"
                   class="block px-3 py-2 rounded-lg text-xs
                   <?= $current_page === 'inventory.php'
                       ? 'bg-blue-50 text-blue-600 font-semibold'
                       : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' ?>">
                    Bar Inventory
                </a>
            </div>


            <!-- ================= EMPLOYEE ================= -->

            <?php
            $employeePages = [
                'employee.php' // Change/add page filenames here if needed
            ];

            $employeeActive = in_array($current_page, $employeePages);
            ?>


            <button
                type="button"
                onclick="toggleMenu('employeeMenu', 'employeeArrow')"
                class="w-full group flex items-center justify-between 
                       gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium 
                       transition-all duration-200
                       <?= $employeeActive
                           ? 'bg-blue-50/80 text-blue-600 font-semibold'
                           : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>"
            >

                <div class="flex items-center gap-3">

                    <svg class="w-4 h-4
                               <?= $employeeActive
                                   ? 'text-blue-600'
                                   : 'text-gray-400' ?>"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>

                    </svg>

                    <span>Employee</span>

                </div>


                <svg id="employeeArrow"
                     class="w-4 h-4 transition-transform duration-200
                            <?= $employeeActive ? 'rotate-180' : '' ?>"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 9l-7 7-7-7">
                    </path>

                </svg>

            </button>


            <!-- EMPLOYEE SUBMENU -->
            <div
                id="employeeMenu"
                class="<?= $employeeActive ? '' : 'hidden' ?> ml-7 mt-1 space-y-1"
            >

                <a href="../Employee/employee.php"
                   class="block px-3 py-2 rounded-lg text-xs
                   <?= $current_page === 'employee.php'
                       ? 'bg-blue-50 text-blue-600 font-semibold'
                       : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' ?>">
                    Employee Management
                </a>
            </div>


            <!-- ================= TIP ================= -->

            <?php
            $tipPages = [
                'tip.php'
            ];

            $tipActive = in_array($current_page, $tipPages);
            ?>


            <button
                type="button"
                onclick="toggleMenu('tipMenu', 'tipArrow')"
                class="w-full group flex items-center justify-between 
                       gap-3 px-3.5 py-2.5 rounded-xl text-xs font-medium 
                       transition-all duration-200
                       <?= $tipActive
                           ? 'bg-blue-50/80 text-blue-600 font-semibold'
                           : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' ?>"
            >

                <div class="flex items-center gap-3">

                    <svg class="w-4 h-4
                               <?= $tipActive
                                   ? 'text-blue-600'
                                   : 'text-gray-400' ?>"
                         fill="none"
                         stroke="currentColor"
                         viewBox="0 0 24 24">

                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>

                    </svg>

                    <span>Tip</span>

                </div>


                <svg id="tipArrow"
                     class="w-4 h-4 transition-transform duration-200
                            <?= $tipActive ? 'rotate-180' : '' ?>"
                     fill="none"
                     stroke="currentColor"
                     viewBox="0 0 24 24">

                    <path stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M19 9l-7 7-7-7">
                    </path>

                </svg>

            </button>


            <!-- TIP SUBMENU -->
            <div
                id="tipMenu"
                class="<?= $tipActive ? '' : 'hidden' ?> ml-7 mt-1 space-y-1"
            >

                <a href="../Tip/tip.php"
                   class="block px-3 py-2 rounded-lg text-xs
                   <?= $current_page === 'tip.php'
                       ? 'bg-blue-50 text-blue-600 font-semibold'
                       : 'text-gray-500 hover:bg-gray-50 hover:text-gray-800' ?>">
                    Tip Management
                </a>
            </div>

        </nav>

    </div>


    <!-- ADMIN -->
    <div class="p-4 border-t border-gray-100">

        <div class="flex items-center gap-3 px-3 py-2 
                    bg-gray-50/80 rounded-xl border border-gray-100">

            <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-700 
                        font-bold flex items-center justify-center text-xs">
                AD
            </div>

            <div class="flex flex-col min-w-0">

                <span class="text-xs font-semibold text-gray-800 truncate">
                    Administrator
                </span>

                <span class="text-[10px] text-gray-400 truncate">
                    Active Session
                </span>

            </div>

        </div>

    </div>

</aside>


<!-- OVERLAY -->
<div id="overlay"
     onclick="toggleSidebar()"
     class="fixed inset-0 bg-gray-900/40 backdrop-blur-xs z-30 
            hidden lg:hidden transition-opacity duration-300">
</div>


<!-- DROPDOWN JAVASCRIPT -->
<script>

function toggleMenu(menuId, arrowId)
{
    const menu = document.getElementById(menuId);
    const arrow = document.getElementById(arrowId);

    menu.classList.toggle('hidden');
    arrow.classList.toggle('rotate-180');
}

</script>