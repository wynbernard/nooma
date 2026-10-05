```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nooma - Simple. Smart. Powerful.</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        pointly: "#2563EB"
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-white text-gray-900">

    <?php include __DIR__ . "/frontend/Components/alert.php"; ?>

    <!-- NAVBAR -->
    <nav class="fixed top-0 left-0 right-0 z-50 bg-white/90 backdrop-blur border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-18 py-4">

                <!-- Logo -->
                <a href="#" class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center">
                        <span class="text-white font-bold text-xl">P</span>
                    </div>

                    <span class="text-xl font-bold tracking-tight">
                        Nooma
                    </span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="#features"
                       class="text-sm text-gray-600 hover:text-blue-600 transition">
                        Features
                    </a>

                    <a href="#about"
                       class="text-sm text-gray-600 hover:text-blue-600 transition">
                        About
                    </a>

                    <a href="#contact"
                       class="text-sm text-gray-600 hover:text-blue-600 transition">
                        Contact
                    </a>
                </div>

                <!-- Buttons -->
                <div class="hidden md:flex items-center gap-3">
                    <a href="frontend/Login/login.php"
                       class="px-5 py-2.5 text-sm font-medium text-gray-700 hover:text-blue-600">
                        Login
                    </a>

                    <a href="register.php"
                       class="px-5 py-2.5 bg-blue-600 text-white rounded-lg text-sm font-semibold hover:bg-blue-700 transition shadow-sm">
                        Get Started
                    </a>
                </div>

                <!-- Mobile Button -->
                <button onclick="toggleMenu()"
                        class="md:hidden p-2 text-gray-600">
                    ☰
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="mobileMenu" class="hidden md:hidden pb-5">
                <div class="flex flex-col gap-4 pt-3">
                    <a href="#features" class="text-gray-600">Features</a>
                    <a href="#about" class="text-gray-600">About</a>
                    <a href="#contact" class="text-gray-600">Contact</a>

                    <div class="flex gap-3 pt-2">
                        <a href="frontend/Login/login.php"
                           class="flex-1 text-center py-2.5 border rounded-lg">
                            Login
                        </a>

                        <a href="register.php"
                           class="flex-1 text-center py-2.5 bg-blue-600 text-white rounded-lg">
                            Get Started
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>


    <!-- HERO -->
    <section class="pt-36 pb-20 lg:pt-44 lg:pb-28">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">

            <div class="grid lg:grid-cols-2 gap-16 items-center">

                <!-- Hero Text -->
                <div>

                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-full text-sm font-medium mb-6">
                        <span class="w-2 h-2 bg-blue-600 rounded-full"></span>
                        Smart Point of Sale System
                    </div>

                    <h1 class="text-5xl lg:text-7xl font-bold tracking-tight leading-tight">
                        Run your business
                        <span class="text-blue-600">smarter.</span>
                    </h1>

                    <p class="mt-6 text-lg text-gray-600 leading-relaxed max-w-xl">
                        Nooma helps businesses manage sales, inventory,
                        payments, and daily operations from one simple
                        and powerful platform.
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row gap-4">

                        <a href="register.php"
                           class="px-7 py-3.5 bg-blue-600 text-white rounded-xl font-semibold text-center hover:bg-blue-700 transition shadow-lg shadow-blue-600/20">
                            Get Started
                        </a>

                        <a href="#features"
                           class="px-7 py-3.5 border border-gray-200 rounded-xl font-semibold text-center hover:bg-gray-50 transition">
                            Explore Features
                        </a>

                    </div>

                    <div class="mt-8 flex items-center gap-6 text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <span class="text-green-500">✓</span>
                            Easy to use
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-green-500">✓</span>
                            Secure
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-green-500">✓</span>
                            Fast
                        </div>
                    </div>

                </div>


                <!-- Dashboard Preview -->
                <div class="relative">

                    <div class="absolute -inset-6 bg-blue-100/60 blur-3xl rounded-full"></div>

                    <div class="relative bg-white border border-gray-200 rounded-2xl shadow-2xl overflow-hidden">

                        <!-- Browser Header -->
                        <div class="h-12 bg-gray-50 border-b flex items-center px-5 gap-2">
                            <span class="w-3 h-3 bg-red-400 rounded-full"></span>
                            <span class="w-3 h-3 bg-yellow-400 rounded-full"></span>
                            <span class="w-3 h-3 bg-green-400 rounded-full"></span>
                        </div>

                        <!-- Dashboard -->
                        <div class="p-6">

                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <p class="text-sm text-gray-500">
                                        Today's Sales
                                    </p>

                                    <h3 class="text-3xl font-bold mt-1">
                                        ₱48,920
                                    </h3>
                                </div>

                                <div class="bg-green-50 text-green-600 px-3 py-1.5 rounded-lg text-sm font-medium">
                                    +18.4%
                                </div>
                            </div>

                            <!-- Cards -->
                            <div class="grid grid-cols-3 gap-3">

                                <div class="bg-blue-50 rounded-xl p-4">
                                    <p class="text-xs text-gray-500">
                                        Transactions
                                    </p>
                                    <p class="text-xl font-bold mt-2">
                                        248
                                    </p>
                                </div>

                                <div class="bg-purple-50 rounded-xl p-4">
                                    <p class="text-xs text-gray-500">
                                        Products
                                    </p>
                                    <p class="text-xl font-bold mt-2">
                                        1,284
                                    </p>
                                </div>

                                <div class="bg-green-50 rounded-xl p-4">
                                    <p class="text-xs text-gray-500">
                                        Customers
                                    </p>
                                    <p class="text-xl font-bold mt-2">
                                        536
                                    </p>
                                </div>

                            </div>

                            <!-- Fake Chart -->
                            <div class="mt-6">

                                <div class="flex justify-between mb-4">
                                    <p class="font-semibold">
                                        Sales Overview
                                    </p>

                                    <p class="text-sm text-gray-400">
                                        This Week
                                    </p>
                                </div>

                                <div class="flex items-end gap-3 h-36">

                                    <div class="flex-1 bg-blue-100 rounded-t-lg h-[40%]"></div>

                                    <div class="flex-1 bg-blue-200 rounded-t-lg h-[55%]"></div>

                                    <div class="flex-1 bg-blue-300 rounded-t-lg h-[48%]"></div>

                                    <div class="flex-1 bg-blue-400 rounded-t-lg h-[72%]"></div>

                                    <div class="flex-1 bg-blue-500 rounded-t-lg h-[65%]"></div>

                                    <div class="flex-1 bg-blue-600 rounded-t-lg h-[88%]"></div>

                                    <div class="flex-1 bg-blue-700 rounded-t-lg h-[100%]"></div>

                                </div>

                            </div>

                        </div>
                    </div>

                </div>

            </div>
        </div>
    </section>


    <!-- FEATURES -->
    <section id="features" class="py-24 bg-gray-50">

        <div class="max-w-7xl mx-auto px-6 lg:px-8">

            <div class="text-center max-w-2xl mx-auto">

                <p class="text-blue-600 font-semibold text-sm uppercase tracking-wider">
                    Features
                </p>

                <h2 class="text-4xl font-bold mt-3">
                    Everything you need to run your business
                </h2>

                <p class="mt-4 text-gray-600">
                    Powerful tools designed to make your daily business
                    operations easier and more organized.
                </p>

            </div>


            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 mt-14">

                <!-- Feature -->
                <div class="bg-white p-7 rounded-2xl border border-gray-100 hover:shadow-lg transition">

                    <div class="w-12 h-12 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center text-2xl">
                        🛒
                    </div>

                    <h3 class="text-xl font-semibold mt-5">
                        Fast Checkout
                    </h3>

                    <p class="text-gray-600 mt-3 leading-relaxed">
                        Process customer transactions quickly with
                        an intuitive POS interface.
                    </p>

                </div>


                <!-- Feature -->
                <div class="bg-white p-7 rounded-2xl border border-gray-100 hover:shadow-lg transition">

                    <div class="w-12 h-12 bg-green-50 text-green-600 rounded-xl flex items-center justify-center text-2xl">
                        📦
                    </div>

                    <h3 class="text-xl font-semibold mt-5">
                        Inventory Management
                    </h3>

                    <p class="text-gray-600 mt-3 leading-relaxed">
                        Keep track of your products, stock levels,
                        and inventory movements.
                    </p>

                </div>


                <!-- Feature -->
                <div class="bg-white p-7 rounded-2xl border border-gray-100 hover:shadow-lg transition">

                    <div class="w-12 h-12 bg-purple-50 text-purple-600 rounded-xl flex items-center justify-center text-2xl">
                        📊
                    </div>

                    <h3 class="text-xl font-semibold mt-5">
                        Sales Reports
                    </h3>

                    <p class="text-gray-600 mt-3 leading-relaxed">
                        Understand your business with detailed
                        sales and performance reports.
                    </p>

                </div>


                <!-- Feature -->
                <div class="bg-white p-7 rounded-2xl border border-gray-100 hover:shadow-lg transition">

                    <div class="w-12 h-12 bg-yellow-50 text-yellow-600 rounded-xl flex items-center justify-center text-2xl">
                        💳
                    </div>

                    <h3 class="text-xl font-semibold mt-5">
                        Multiple Payments
                    </h3>

                    <p class="text-gray-600 mt-3 leading-relaxed">
                        Support different payment methods and
                        keep transactions organized.
                    </p>

                </div>


                <!-- Feature -->
                <div class="bg-white p-7 rounded-2xl border border-gray-100 hover:shadow-lg transition">

                    <div class="w-12 h-12 bg-red-50 text-red-600 rounded-xl flex items-center justify-center text-2xl">
                        🔐
                    </div>

                    <h3 class="text-xl font-semibold mt-5">
                        Secure Access
                    </h3>

                    <p class="text-gray-600 mt-3 leading-relaxed">
                        Protect your business data with user
                        accounts and role-based access.
                    </p>

                </div>


                <!-- Feature -->
                <div class="bg-white p-7 rounded-2xl border border-gray-100 hover:shadow-lg transition">

                    <div class="w-12 h-12 bg-indigo-50 text-indigo-600 rounded-xl flex items-center justify-center text-2xl">
                        ☁️
                    </div>

                    <h3 class="text-xl font-semibold mt-5">
                        Data Backup
                    </h3>

                    <p class="text-gray-600 mt-3 leading-relaxed">
                        Keep important business information backed
                        up and ready when you need it.
                    </p>

                </div>

            </div>

        </div>

    </section>


    <!-- ABOUT -->
    <section id="about" class="py-24">

        <div class="max-w-7xl mx-auto px-6 lg:px-8">

            <div class="grid lg:grid-cols-2 gap-16 items-center">

                <div>

                    <p class="text-blue-600 font-semibold text-sm uppercase tracking-wider">
                        Why Nooma?
                    </p>

                    <h2 class="text-4xl font-bold mt-3">
                        Less complexity.
                        <span class="text-blue-600">
                            More productivity.
                        </span>
                    </h2>

                    <p class="mt-6 text-gray-600 leading-relaxed">
                        Nooma is designed to simplify the way businesses
                        handle everyday sales and operations.
                    </p>

                    <div class="mt-8 space-y-5">

                        <div class="flex gap-4">
                            <div class="text-blue-600 text-xl">✓</div>
                            <div>
                                <h4 class="font-semibold">
                                    Simple Interface
                                </h4>
                                <p class="text-gray-500 mt-1">
                                    Easy for cashiers and staff to learn.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="text-blue-600 text-xl">✓</div>
                            <div>
                                <h4 class="font-semibold">
                                    Organized Data
                                </h4>
                                <p class="text-gray-500 mt-1">
                                    Keep your business information in one place.
                                </p>
                            </div>
                        </div>

                        <div class="flex gap-4">
                            <div class="text-blue-600 text-xl">✓</div>
                            <div>
                                <h4 class="font-semibold">
                                    Built for Growth
                                </h4>
                                <p class="text-gray-500 mt-1">
                                    Designed to grow alongside your business.
                                </p>
                            </div>
                        </div>

                    </div>

                </div>


                <div class="bg-gray-50 rounded-3xl p-10">

                    <div class="bg-white rounded-2xl shadow-xl p-7">

                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-sm text-gray-500">
                                    Monthly Revenue
                                </p>

                                <p class="text-3xl font-bold mt-2">
                                    ₱1,284,500
                                </p>
                            </div>

                            <div class="text-green-600 font-semibold">
                                +24%
                            </div>

                        </div>

                        <div class="mt-8 space-y-4">

                            <div class="flex justify-between text-sm">
                                <span>Sales</span>
                                <span class="font-semibold">₱820,000</span>
                            </div>

                            <div class="w-full bg-gray-100 h-2 rounded-full">
                                <div class="bg-blue-600 h-2 rounded-full w-[80%]"></div>
                            </div>

                            <div class="flex justify-between text-sm pt-3">
                                <span>Online Payments</span>
                                <span class="font-semibold">₱310,500</span>
                            </div>

                            <div class="w-full bg-gray-100 h-2 rounded-full">
                                <div class="bg-purple-500 h-2 rounded-full w-[60%]"></div>
                            </div>

                            <div class="flex justify-between text-sm pt-3">
                                <span>Other Payments</span>
                                <span class="font-semibold">₱154,000</span>
                            </div>

                            <div class="w-full bg-gray-100 h-2 rounded-full">
                                <div class="bg-green-500 h-2 rounded-full w-[35%]"></div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- CTA -->
    <section class="py-24">

        <div class="max-w-6xl mx-auto px-6">

            <div class="bg-blue-600 rounded-3xl px-8 py-16 lg:px-16 text-center text-white">

                <h2 class="text-4xl lg:text-5xl font-bold">
                    Ready to simplify your business?
                </h2>

                <p class="mt-5 text-blue-100 text-lg max-w-2xl mx-auto">
                    Start managing your sales and business operations
                    with Nooma today.
                </p>

                <div class="mt-8">

                    <a href="register.php"
                       class="inline-block px-8 py-3.5 bg-white text-blue-600 rounded-xl font-semibold hover:bg-gray-100 transition">
                        Get Started
                    </a>

                </div>

            </div>

        </div>

    </section>


    <!-- FOOTER -->
    <footer id="contact" class="border-t border-gray-100">

        <div class="max-w-7xl mx-auto px-6 lg:px-8 py-10">

            <div class="flex flex-col md:flex-row justify-between gap-6">

                <div>
                    <div class="flex items-center gap-3">

                        <div class="w-9 h-9 bg-blue-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold">
                                P
                            </span>
                        </div>

                        <span class="font-bold text-lg">
                            Nooma
                        </span>

                    </div>

                    <p class="text-sm text-gray-500 mt-3">
                        Simple. Smart. Powerful.
                    </p>
                </div>


                <div class="flex gap-8 text-sm text-gray-500">

                    <a href="#features" class="hover:text-blue-600">
                        Features
                    </a>

                    <a href="#about" class="hover:text-blue-600">
                        About
                    </a>

                    <a href="login.php" class="hover:text-blue-600">
                        Login
                    </a>

                </div>

            </div>

            <div class="border-t border-gray-100 mt-8 pt-6 text-sm text-gray-400">
                © 2026 Nooma. All rights reserved.
            </div>

        </div>

    </footer>


    <!-- MOBILE MENU SCRIPT -->
    <script>
        function toggleMenu() {
            const menu = document.getElementById("mobileMenu");
            menu.classList.toggle("hidden");
        }
    </script>

</body>
</html>
```
