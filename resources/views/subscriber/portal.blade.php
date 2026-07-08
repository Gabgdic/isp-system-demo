<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriber Portal - {{ $settings->system_name ?? 'Client Area' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-900">

    <!-- Mobile-Optimized Navbar Layout -->
    <nav class="bg-slate-900 text-white px-4 py-3 sm:px-6 sm:py-4 sticky top-0 z-50 flex justify-between items-center shadow-md">
        <div class="flex items-center gap-2.5">
            @if($settings && $settings->system_logo)
                <!-- White container background to make the logo visible -->
                <div class="bg-white p-1 rounded-lg flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 shadow-sm border border-slate-700/50">
                    <img src="{{ asset('storage/' . $settings->system_logo) }}" class="h-full w-full object-contain" alt="Logo">
                </div>
            @endif
            <span class="font-bold text-base sm:text-lg tracking-wide truncate max-w-[180px] sm:max-w-none">
                {{ $settings->system_name ?? 'Client Area' }}
            </span>
        </div>
        
        <form method="POST" action="{{ route('subscriber.logout') }}" class="flex-shrink-0">
            @csrf
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold transition shadow-md flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                </svg>
                <span class="hidden xs:inline">Sign Out</span>
            </button>
        </form>
    </nav>

    <!-- Main Mobile Container -->
    <main class="max-w-md mx-auto px-4 py-6 sm:max-w-4xl sm:py-10">
        
        <!-- Compact, High-Impact Profile Card -->
        <div class="bg-white rounded-2xl p-5 border border-slate-150 shadow-sm mb-5 flex flex-col items-center text-center sm:flex-row sm:text-left sm:p-6 gap-4 sm:gap-6">
            <!-- Profile Photo/Initials Container -->
            <div class="w-20 h-20 rounded-2xl bg-slate-800 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center font-bold text-2xl text-white shadow-inner">
                @if($subscriber->profile_photo)
                    <img src="{{ asset('storage/' . $subscriber->profile_photo) }}" class="w-full h-full object-cover" alt="Profile">
                @else
                    {{ strtoupper(substr($subscriber->full_name, 0, 1)) }}
                @endif
            </div>
            
            <!-- User Info Details -->
            <div class="flex-grow min-w-0 w-full">
                <div class="flex flex-col items-center sm:items-start gap-1">
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight truncate w-full">
                        {{ $subscriber->full_name }}
                    </h1>
                    <p class="text-slate-500 text-xs sm:text-sm">
                        Username: <span class="font-semibold text-slate-700">{{ $subscriber->username }}</span>
                    </p>
                </div>
                
                <!-- Status Badge -->
                <div class="mt-3 sm:mt-2">
                    @if($subscriber->status === 'active')
                        <span class="inline-flex bg-green-50 text-green-700 font-bold px-3 py-1 rounded-full text-[10px] uppercase tracking-wider border border-green-200">Active Connection</span>
                    @elseif($subscriber->status === 'inactive')
                        <span class="inline-flex bg-amber-50 text-amber-700 font-bold px-3 py-1 rounded-full text-[10px] uppercase tracking-wider border border-amber-200">Inactive</span>
                    @else
                        <span class="inline-flex bg-red-50 text-red-700 font-bold px-3 py-1 rounded-full text-[10px] uppercase tracking-wider border border-red-200">Disconnected</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Touch-Friendly Dashboard Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <!-- Plan Details Block -->
            <div class="bg-white rounded-2xl border border-slate-150 shadow-sm p-5 flex flex-col justify-between">
                <div>
                    <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v6a2 2 0 012-2m14-8V7a2 2 0 00-2-2H5a2 2 0 00-2 2v4"></path>
                        </svg>
                        Subscription Package
                    </h2>
                    <div class="space-y-4">
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Plan Type</span>
                            <span class="text-lg font-bold text-slate-800">{{ $subscriber->plan_name ?? 'No assigned plan' }}</span>
                        </div>
                        <div>
                            <span class="text-xs text-slate-400 block font-medium">Monthly Bill</span>
                            <span class="text-2xl font-black text-slate-900 tracking-tight">₱{{ number_format($subscriber->monthly_fee, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Installation Details Block -->
            <div class="bg-white rounded-2xl border border-slate-150 shadow-sm p-5">
                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider flex items-center gap-2 mb-4">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Installation Details
                </h2>
                <div class="space-y-3.5">
                    <div>
                        <span class="text-xs text-slate-400 font-medium block">Contact Number</span>
                        <span class="text-sm font-semibold text-slate-700">{{ $subscriber->phone_number ?? 'N/A' }}</span>
                    </div>
                    <div class="border-t border-slate-100 pt-3">
                        <span class="text-xs text-slate-400 font-medium block">Service Address</span>
                        <span class="text-sm font-semibold text-slate-700 block leading-relaxed break-words">
                            {{ $subscriber->address ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
            
        </div>
    </main>

    <style>
        @media (max-width: 340px) {
            .xs\:inline { display: none !important; }
        }
    </style>
</body>
</html>