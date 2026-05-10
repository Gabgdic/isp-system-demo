<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->system_name ?? 'Client Area' }}</title>

    @if($settings && $settings->system_logo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $settings->system_logo) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $settings->system_logo) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('appicon.png') }}">
    @endif

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="antialiased min-h-screen bg-gradient-to-br from-slate-100 via-white to-slate-200">

    <main class="min-h-screen flex items-center justify-center px-5 py-8">

        <div class="w-full max-w-5xl grid grid-cols-1 md:grid-cols-2 gap-10 items-center">

            <!-- Left Section -->
            <div class="hidden lg:flex flex-col items-center justify-center bg-transparent px-12 py-14 text-center">

                <div class="mb-8 flex justify-center">
                    @if($settings && $settings->system_logo)
                        <img src="{{ asset('storage/' . $settings->system_logo) }}"
                            class="w-28 h-28 object-contain"
                            alt="System Logo">
                    @else
                        <div class="w-24 h-24 rounded-3xl bg-slate-900 flex items-center justify-center shadow-xl text-white text-4xl font-bold">
                            {{ strtoupper(substr($settings->system_name ?? 'C', 0, 1)) }}
                        </div>
                    @endif
                </div>

                <h1 class="text-6xl font-extrabold text-slate-900 leading-none tracking-tight">
                    {{ $settings->system_name ?? 'Client Area' }}
                </h1>

                <p class="mt-6 text-lg text-slate-500 max-w-md leading-relaxed">
                    Welcome to the Automated Internet Subscription Management System.
                </p>

            </div>

            <!-- Right Side -->
            <section class="flex justify-center">

                <div class="w-full max-w-sm md:max-w-md bg-white border border-slate-200 rounded-3xl shadow-xl p-7 md:p-9">

                    <!-- Mobile Logo -->
                    <div class="lg:hidden text-center mb-6">
                        <div class="flex justify-center mb-4">
                            @if($settings && $settings->system_logo)
                                <img src="{{ asset('storage/' . $settings->system_logo) }}"
                                    class="w-20 h-20 object-contain"
                                    alt="System Logo">
                            @else
                                <div class="w-20 h-20 rounded-3xl bg-slate-900 flex items-center justify-center shadow-xl text-white text-3xl font-bold">
                                    {{ strtoupper(substr($settings->system_name ?? 'C', 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <h1 class="text-xl font-extrabold text-slate-900">
                            {{ $settings->system_name ?? 'Client Area' }}
                        </h1>
                    </div>

                    <div class="text-center mb-7">
                        <h2 class="text-2xl md:text-3xl font-bold text-slate-800">
                            Sign In
                        </h2>

                        <p class="text-sm text-slate-500 mt-2">
                            Please sign in to continue.
                        </p>
                    </div>

                    @if(session('error'))
                        <div class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="username" class="block text-sm font-medium text-slate-700 mb-2">
                                Username
                            </label>

                            <input
                                type="text"
                                name="username"
                                id="username"
                                value="{{ old('username') }}"
                                placeholder="Enter your username"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm text-slate-700 placeholder-slate-400 outline-none transition focus:border-slate-700 focus:ring-4 focus:ring-slate-200"
                                required
                            />
                        </div>

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
                                required
                            />
                        </div>

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