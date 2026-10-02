<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - Nooma</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-50">

    <div class="min-h-screen flex">

        <!-- LEFT SIDE -->
        <div class="hidden lg:flex lg:w-1/2 bg-blue-600 text-white p-12
                    items-center justify-center">

            <div class="max-w-lg">

                <div class="flex items-center gap-3 mb-10">
                    <div class="w-12 h-12 bg-white rounded-xl
                                flex items-center justify-center">
                        <span class="text-blue-600 text-2xl font-bold">
                            N
                        </span>
                    </div>

                    <span class="text-2xl font-bold">
                        Nooma
                    </span>
                </div>

                <h1 class="text-5xl font-bold leading-tight">
                    Manage your business
                    <span class="text-blue-200">
                        smarter.
                    </span>
                </h1>

                <p class="mt-6 text-blue-100 text-lg leading-relaxed">
                    Manage sales, inventory, payments, and daily
                    business operations from one simple platform.
                </p>

                <div class="mt-10 space-y-4">

                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-full
                                    flex items-center justify-center">
                            ✓
                        </div>

                        <span>
                            Fast and simple checkout
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-full
                                    flex items-center justify-center">
                            ✓
                        </div>

                        <span>
                            Track your inventory
                        </span>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-500 rounded-full
                                    flex items-center justify-center">
                            ✓
                        </div>

                        <span>
                            Detailed sales reports
                        </span>
                    </div>

                </div>

            </div>

        </div>


        <!-- RIGHT SIDE -->
        <div class="w-full lg:w-1/2 flex items-center justify-center
                    px-6 py-12">

            <div class="w-full max-w-md">

                <!-- MOBILE LOGO -->
                <div class="flex lg:hidden items-center justify-center
                            gap-3 mb-10">

                    <div class="w-11 h-11 bg-blue-600 rounded-xl
                                flex items-center justify-center">

                        <span class="text-white text-xl font-bold">
                            P
                        </span>

                    </div>

                    <span class="text-2xl font-bold">
                        Nooma
                    </span>

                </div>


                <!-- LOGIN HEADER -->
                <div class="text-center lg:text-left">

                    <h2 class="text-3xl font-bold text-gray-900">
                        Welcome back
                    </h2>

                    <p class="mt-2 text-gray-500">
                        Sign in to your Nooma account.
                    </p>

                </div>


                <!-- ERROR -->
                <?php if (!empty($error)): ?>

                    <div class="mt-6 p-4 bg-red-50 border border-red-200
                                text-red-700 rounded-xl text-sm">

                        <?= htmlspecialchars($error) ?>

                    </div>

                <?php endif; ?>


                <!-- LOGIN FORM -->
                <form action="../../backend/auth/login_action.php"
                      method="POST"
                      class="mt-8 space-y-5">

                    <!-- USERNAME -->
                    <div>

                        <label for="username"
                               class="block text-sm font-medium
                                      text-gray-700 mb-2">

                            Username

                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            required
                            autocomplete="username"
                            placeholder="Enter your username"
                            class="w-full px-4 py-3 border border-gray-300
                                   rounded-xl outline-none
                                   focus:ring-2 focus:ring-blue-500
                                   focus:border-blue-500 transition"
                        >

                    </div>


                    <!-- PASSWORD -->
                    <div>

                        <div class="flex justify-between mb-2">

                            <label for="password"
                                   class="block text-sm font-medium
                                          text-gray-700">

                                Password

                            </label>

                        </div>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            class="w-full px-4 py-3 border border-gray-300
                                   rounded-xl outline-none
                                   focus:ring-2 focus:ring-blue-500
                                   focus:border-blue-500 transition"
                        >

                    </div>


                    <!-- REMEMBER -->
                    <div class="flex items-center">

                        <input
                            type="checkbox"
                            id="remember"
                            name="remember"
                            class="w-4 h-4 text-blue-600
                                   border-gray-300 rounded"
                        >

                        <label for="remember"
                               class="ml-2 text-sm text-gray-600">

                            Remember me

                        </label>

                    </div>


                    <!-- BUTTON -->
                    <button
                        type="submit"
                        class="w-full py-3.5 bg-blue-600
                               text-white rounded-xl font-semibold
                               hover:bg-blue-700
                               active:bg-blue-800
                               transition">

                        Sign In

                    </button>

                </form>


                <!-- REGISTER -->
                <p class="mt-8 text-center text-sm text-gray-500">

                    Don't have an account?

                    <a href="register.php"
                       class="text-blue-600 font-semibold
                              hover:text-blue-700">

                        Create account

                    </a>

                </p>


                <!-- BACK -->
                <div class="mt-6 text-center">

                    <a href="index.php"
                       class="text-sm text-gray-500
                              hover:text-blue-600">

                        ← Back to Nooma

                    </a>

                </div>

            </div>

        </div>

    </div>

</body>
</html>