<header
    class="h-20 bg-white border-b border-gray-200
           flex items-center justify-between px-6"
>

    <!-- LEFT SIDE -->

    <div class="flex items-center gap-4">

        <!-- Mobile Menu -->

        <button
            onclick="toggleSidebar()"
            class="lg:hidden p-2 rounded-lg
                   hover:bg-gray-100"
        >
            ☰
        </button>


        <div>

            <h1 class="text-xl font-bold">
                <?= htmlspecialchars($page_title ?? 'Dashboard') ?>
            </h1>

            <p class="text-sm text-gray-500">
                <?= htmlspecialchars(
                    $page_description ?? 'Overview of your business'
                ) ?>
            </p>

        </div>

    </div>


    <!-- RIGHT SIDE -->

    <div class="flex items-center gap-3">


        <!-- NOTIFICATION -->

        <button
            class="relative w-10 h-10 rounded-xl
                   hover:bg-gray-100"
        >

            🔔

            <span
                class="absolute top-2 right-2
                       w-2 h-2 bg-red-500 rounded-full"
            ></span>

        </button>


        <!-- PROFILE DROPDOWN -->

        <div class="relative">

            <!-- Profile Button -->

            <button
                onclick="toggleProfileMenu()"
                class="flex items-center gap-3
                       px-2 py-1.5 rounded-xl
                       hover:bg-gray-50 transition"
            >

                <!-- Avatar -->

                <div
                    class="w-10 h-10 bg-blue-600 text-white
                           rounded-full flex items-center
                           justify-center font-semibold"
                >
                    <?= strtoupper(substr($full_name, 0, 1)) ?>
                </div>


                <!-- User Info -->

                <div class="hidden sm:block text-left">

                    <p class="text-sm font-semibold">
                        <?= htmlspecialchars($full_name) ?>
                    </p>

                    <p class="text-xs text-gray-500">
                        Administrator
                    </p>

                </div>


                <!-- Arrow -->

                <span class="hidden sm:block text-gray-400 text-xs">
                    ▼
                </span>

            </button>


            <!-- DROPDOWN -->

            <div
                id="profileMenu"
                class="hidden absolute right-0 mt-3
                       w-64 bg-white rounded-2xl
                       border border-gray-100
                       shadow-lg z-50 overflow-hidden"
            >

                <!-- PROFILE HEADER -->

                <div class="px-4 py-4 border-b">

                    <div class="flex items-center gap-3">

                        <div
                            class="w-11 h-11 bg-blue-600
                                   text-white rounded-full
                                   flex items-center
                                   justify-center
                                   font-semibold"
                        >
                            <?= strtoupper(substr($full_name, 0, 1)) ?>
                        </div>

                        <div class="min-w-0">

                            <p class="font-semibold truncate">
                                <?= htmlspecialchars($full_name) ?>
                            </p>

                            <p class="text-xs text-gray-500 truncate">
                                <?= htmlspecialchars($username ?? 'admin') ?>
                            </p>

                        </div>

                    </div>

                </div>


                <!-- MENU -->

                <div class="p-2">

                    <!-- Profile -->

                    <a
                        href="profile.php"
                        class="flex items-center gap-3
                               px-3 py-2.5 rounded-xl
                               text-sm text-gray-700
                               hover:bg-gray-50"
                    >

                        <span>👤</span>

                        <span>
                            My Profile
                        </span>

                    </a>


                    <!-- Settings -->

                    <a
                        href="settings.php"
                        class="flex items-center gap-3
                               px-3 py-2.5 rounded-xl
                               text-sm text-gray-700
                               hover:bg-gray-50"
                    >

                        <span>⚙</span>

                        <span>
                            Settings
                        </span>

                    </a>

                </div>


                <!-- LOGOUT -->

                <div class="border-t p-2">

                    <a
                        href="../../backend/auth/logout.php"
                        class="flex items-center gap-3
                               px-3 py-2.5 rounded-xl
                               text-sm text-red-600
                               hover:bg-red-50"
                    >

                        <span>↪</span>

                        <span>
                            Logout
                        </span>

                    </a>

                </div>

            </div>

        </div>

    </div>

</header>

<?php include __DIR__ . "/alert.php"; ?>

<script>

function toggleProfileMenu() {

    const menu = document.getElementById("profileMenu");

    menu.classList.toggle("hidden");

}
function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("overlay");

    sidebar.classList.toggle("-translate-x-full");
    overlay.classList.toggle("hidden");
}


// Close profile menu when clicking outside

document.addEventListener("click", function(event) {

    const menu = document.getElementById("profileMenu");

    const button = event.target.closest(
        'button[onclick="toggleProfileMenu()"]'
    );

    if (!button && !menu.contains(event.target)) {
        menu.classList.add("hidden");
    }

});

</script>
