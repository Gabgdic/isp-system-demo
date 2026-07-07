<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    @php
        $systemSettings = \App\Models\SystemSetting::first();
    @endphp

    <title>{{ $systemSettings->system_name ?? 'Client Area' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    @if($systemSettings && $systemSettings->system_logo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $systemSettings->system_logo) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $systemSettings->system_logo) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('appicon.png') }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-slate-100 text-slate-800">

    <!-- Top Navbar -->
    <nav class="fixed top-0 left-0 right-0 z-50 h-16 bg-white/90 backdrop-blur-xl border-b border-slate-200 shadow-sm">
        <div class="h-full flex items-center justify-between px-4 sm:px-6">

            <!-- Left -->
            <div class="flex items-center gap-3">
                <button onclick="toggleSidebar()"
                    class="lg:hidden inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M5 7h14M5 12h14M5 17h14"/>
                    </svg>
                </button>

                @php
                    $systemSettings = \App\Models\SystemSetting::first();
                @endphp

                <div class="flex items-center gap-3">
                    @if($systemSettings && $systemSettings->system_logo)
                        <img src="{{ asset('storage/' . $systemSettings->system_logo) }}"
                        class="w-10 h-10 object-contain"
                        alt="System Logo">
                    @else
                        <div class="w-10 h-10 rounded-2xl bg-slate-900 text-white flex items-center justify-center shadow-md font-bold">
                            {{ strtoupper(substr($systemSettings->system_name ?? 'S', 0, 1)) }}
                        </div>
                    @endif

                    <div>
                        <h1 class="text-sm sm:text-base font-bold text-slate-900 leading-tight">
                            {{ $systemSettings->system_name ?? 'Client Area' }}
                        </h1>
                        <p class="hidden sm:block text-xs text-slate-500">
                            Management Dashboard
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-right">
                    <p class="text-sm font-semibold text-slate-800">
                        {{ Auth::user()->username ?? 'Guest' }}
                    </p>
                    <p class="text-xs text-slate-500">
                        {{ Auth::user()?->role === 'super_admin' ? 'Super Admin' : 'Administrator' }}
                    </p>
                </div>

                @if(Auth::user()?->profile_photo)
                  <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}"
                        class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow"
                        alt="Profile Photo">
               @else
                  <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center font-bold shadow">
                     {{ strtoupper(substr(Auth::user()->username ?? 'G', 0, 1)) }}
                  </div>
               @endif
            </div>
        </div>
    </nav>

    <!-- Mobile Overlay -->
    <div id="sidebarOverlay"
        onclick="toggleSidebar()"
        class="fixed inset-0 z-30 bg-black/40 hidden lg:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar"
        class="group fixed top-16 left-0 z-40 h-[calc(100vh-4rem)] w-72 lg:w-20 lg:hover:w-72
               -translate-x-full lg:translate-x-0 bg-white border-r border-slate-200 shadow-sm
               transition-all duration-300 ease-in-out overflow-hidden">

        <div class="h-full flex flex-col justify-between px-3 py-5">

            <ul class="space-y-2">

                <!-- Dashboard -->
                <li>
                    <a href="{{ route('dashboard') }}"
                        class="nav-link {{ request()->routeIs('dashboard') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M5 3a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2H5Zm14 18a2 2 0 0 0 2-2v-2a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h4ZM5 11a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2v-6a2 2 0 0 0-2-2H5Zm14 2a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h4Z"/>
                        </svg>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>

                <!-- Billing -->
                <li>
                    <a href="{{ route('billing') }}"
                        class="nav-link {{ request()->routeIs('billing') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M8 7V6a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1h-1M3 18v-7a1 1 0 0 1 1-1h11a1 1 0 0 1 1 1v7a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-3.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z"/>
                        </svg>
                        <span class="nav-text">Billing</span>
                    </a>
                </li>

                <!-- Subscriber -->
                <li>
                    <a href="{{ route('subscriber') }}"
                        class="nav-link {{ request()->routeIs('subscriber') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.7 15h4.3c.4 0 .7-.4.7-.9V3.9c0-.5-.3-.9-.7-.9H6.7c-.6 0-1 .4-1 1v4m11 7v-3h3v3h-3Zm-3 6H6.7c-.6 0-1-.4-1-1 0-1.7 1.3-3 3-3h3c1.7 0 3 1.3 3 3 0 .6-.4 1-1 1Zm-1-9.5A2.5 2.5 0 1 1 10.2 9a2.5 2.5 0 0 1 2.5 2.5Z"/>
                        </svg>
                        <span class="nav-text">Subscriber</span>
                    </a>
                </li>

                <!-- ONLY SUPER ADMIN CAN VIEW ADMIN MANAGEMENT & SETTINGS -->
                @if(Auth::user()?->role === 'super_admin')
                    <!-- Admin -->
                    <li>
                        <a href="{{ route('admin') }}"
                            class="nav-link {{ request()->routeIs('admin*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="nav-icon" fill="none" viewBox="0 0 24 24">
                                <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M16 19h4a1 1 0 0 0 1-1v-1a3 3 0 0 0-3-3h-2m-2-4a3 3 0 1 0 0-4M3 18v-1a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Zm8-10a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                            </svg>
                            <span class="nav-text">Admin</span>
                        </a>
                    </li>

                    <!-- Settings -->
                    <li>
                        <a href="{{ route('settings') }}"
                            class="nav-link {{ request()->routeIs('settings*') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                            <svg class="nav-icon" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M10.8 5a3 3 0 0 0-5.7 0H4a1 1 0 1 0 0 2h1.2a3 3 0 0 0 5.7 0H20a1 1 0 1 0 0-2h-9.2ZM4 11h9.2a3 3 0 0 1 5.7 0H20a1 1 0 1 1 0 2h-1.2a3 3 0 0 1-5.7 0H4a1 1 0 1 1 0-2Zm1.2 6H4a1 1 0 1 0 0 2h1.2a3 3 0 0 0 5.7 0H20a1 1 0 1 0 0-2h-9.2a3 3 0 0 0-5.7 0Z"/>
                            </svg>
                            <span class="nav-text">Settings</span>
                        </a>
                    </li>
                @endif

                <!-- Reports -->
                <li>
                    <a href="{{ route('reports') }}"
                        class="nav-link {{ request()->routeIs('reports') ? 'bg-slate-900 text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                        <svg class="nav-icon" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M8 17v-6m4 6V7m4 10v-4"/>
                        </svg>
                        <span class="nav-text">Reports</span>
                    </a>
                </li>
            </ul>

            <!-- Logout -->
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit"
                    class="nav-link w-full text-red-500 hover:bg-red-50 hover:text-red-600">
                    <svg class="nav-icon" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2"/>
                    </svg>
                    <span class="nav-text">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="pt-16 lg:ml-20 min-h-screen transition-all duration-300">
        <div class="p-4 sm:p-6 lg:p-8">
            @yield('content')
        </div>
    </main>

    <style>
        .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            min-height: 48px;
            padding: 12px 14px;
            border-radius: 16px;
            transition: all 0.25s ease;
            white-space: nowrap;
        }

        .nav-link:hover {
            transform: translateX(4px);
        }

        .nav-icon {
            width: 22px;
            height: 22px;
            flex-shrink: 0;
        }

        .nav-text {
            font-size: 14px;
            font-weight: 600;
            opacity: 1;
            transition: all 0.25s ease;
        }

        @media (min-width: 1024px) {
            .nav-text {
                opacity: 0;
                transform: translateX(-8px);
                width: 0;
                overflow: hidden;
            }

            .group:hover .nav-text {
                opacity: 1;
                transform: translateX(0);
                width: auto;
            }
        }
    </style>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');

            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
    </script>

</body>
</html>