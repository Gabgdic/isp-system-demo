<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Area</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="antialiased min-h-screen bg-gradient-to-br from-slate-100 via-white to-slate-200">

    <main class="min-h-screen flex items-center justify-center px-5 py-8">

        <!-- MAIN WRAPPER -->
        <div class="w-full max-w-5xl grid grid-cols-1 md:grid-cols-2 gap-10 items-center">

            <!-- Left Section -->
            <div class="hidden lg:flex flex-col items-center justify-center bg-transparent px-12 py-14 text-center">

                <div class="w-24 h-24 rounded-3xl bg-slate-900 flex items-center justify-center shadow-xl mb-8">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                        ></path>
                    </svg>
                </div>

                <h1 class="text-6xl font-extrabold text-slate-900 leading-none tracking-tight">
                    Title Here
                </h1>

                <p class="mt-6 text-lg text-slate-500 max-w-md leading-relaxed">
                    Welcome to the Automated Internet Subscription Management System.
                </p>

            </div>

            <!-- RIGHT SIDE -->
            <section class="flex justify-center">

                <!-- LOGIN CARD -->
                <div class="w-full max-w-sm md:max-w-md bg-white border border-slate-200 rounded-3xl shadow-xl p-7 md:p-9">

                    <div class="text-center mb-7">
                        <h2 class="text-2xl md:text-3xl font-bold text-slate-800">
                            Sign In
                        </h2>

                        <p class="text-sm text-slate-500 mt-2">
                            Please sign in to continue.
                        </p>
                    </div>

                    <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                        @csrf

                        <!-- Username -->
                        <div>
                            <label for="username" class="block text-sm font-medium text-slate-700 mb-2">
                                Username
                            </label>

                            <input
                                type="text"
                                name="username"
                                id="username"
                                placeholder="Enter your username"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition focus:border-slate-700 focus:ring-4 focus:ring-slate-200"
                            />
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                                Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                placeholder="Enter your password"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition focus:border-slate-700 focus:ring-4 focus:ring-slate-200"
                            />
                        </div>

                        <!-- Button -->
                        <button
                            type="submit"
                            class="w-full bg-slate-900 text-white py-3.5 rounded-xl font-semibold shadow-lg shadow-slate-300/60 hover:bg-slate-700 active:scale-[0.98] transition"
                        >
                            <div class="flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                                    ></path>
                                </svg>

                                <span>Sign In</span>
                            </div>
                        </button>

                    </form>

                </div>

            </section>

        </div>

    </main>

</body>
</html>