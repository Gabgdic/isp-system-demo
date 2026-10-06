<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriber Portal - {{ $settings->system_name ?? 'Client Area' }}</title>

    @if($settings && $settings->system_logo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $settings->system_logo) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $settings->system_logo) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('appicon.png') }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            body * {
                visibility: hidden !important;
            }
            .printable-soa, .printable-soa * {
                visibility: visible !important;
            }
            .printable-soa {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-slate-50 font-sans antialiased text-slate-900">

    <!-- NAVBAR -->
    <nav class="sticky top-0 z-50 flex h-14 items-center justify-between bg-slate-900 px-3 text-white shadow-sm sm:px-5">
        <div class="flex items-center gap-2.5 min-w-0">
            @if($settings && $settings->system_logo)
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-700/50 bg-white p-1 shadow-sm">
                    <img
                        src="{{ asset('storage/' . $settings->system_logo) }}"
                        class="h-full w-full object-contain"
                        alt="Logo"
                    >
                </div>
            @endif

            <span class="max-w-[170px] truncate text-sm font-bold tracking-wide sm:max-w-none sm:text-base">
                {{ $settings->system_name ?? 'Client Area' }}
            </span>
        </div>

        <form method="POST" action="{{ route('subscriber.logout') }}" class="flex-shrink-0">
            @csrf
            <button
                type="submit"
                class="flex items-center gap-1.5 rounded-lg bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:bg-red-700 sm:px-3 sm:py-2"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                    />
                </svg>
                <span class="hidden sm:inline">Sign Out</span>
            </button>
        </form>
    </nav>

    <!-- MAIN -->
    <main class="mx-auto w-full max-w-5xl px-3 py-4 sm:px-5 sm:py-6">

        <!-- ALERTS -->
        @if(session('success'))
            <div class="mb-3 rounded-xl border border-green-200 bg-green-50 p-3 text-xs font-medium text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-3 rounded-xl border border-red-200 bg-red-50 p-3 text-xs font-medium text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-3 rounded-xl border border-red-200 bg-red-50 p-3 text-xs text-red-700">
                <p class="font-bold mb-2">Please check the following:</p>
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- PROFILE -->
        <div class="mb-3 flex flex-col items-center gap-3 rounded-xl border border-slate-200 bg-white p-4 text-center shadow-sm sm:flex-row sm:gap-4 sm:p-4 sm:text-left">
            <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-slate-800 text-xl font-bold text-white shadow-inner sm:h-16 sm:w-16">
                @if($subscriber->profile_photo)
                    <img
                        src="{{ asset('storage/' . $subscriber->profile_photo) }}"
                        class="w-full h-full object-cover"
                        alt="Profile"
                    >
                @else
                    {{ strtoupper(substr($subscriber->full_name, 0, 1)) }}
                @endif
            </div>

            <div class="flex-grow min-w-0 w-full">
                <div class="flex flex-col items-center sm:items-start gap-1">
                    <h1 class="w-full truncate text-lg font-extrabold tracking-tight text-slate-800 sm:text-xl">
                        {{ $subscriber->full_name }}
                    </h1>
                    <p class="text-slate-500 text-xs sm:text-sm">
                        Username:
                        <span class="font-semibold text-slate-700">
                            {{ $subscriber->username }}
                        </span>
                    </p>
                </div>

                <div class="mt-2 flex items-center justify-between sm:justify-start gap-2">
                    @if($subscriber->status === 'active')
                        <span class="inline-flex rounded-full border border-green-200 bg-green-50 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-green-700">
                            Active Connection
                        </span>
                    @elseif($subscriber->status === 'inactive')
                        <span class="inline-flex rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-amber-700">
                            Inactive
                        </span>
                    @else
                        <span class="inline-flex rounded-full border border-red-200 bg-red-50 px-2.5 py-1 text-[9px] font-bold uppercase tracking-wider text-red-700">
                            Disconnected
                        </span>
                    @endif

                    <button
                        type="button"
                        onclick="openEditProfileModal()"
                        class="text-xs font-bold text-blue-600 hover:text-blue-800 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100 transition"
                    >
                        Edit Profile
                    </button>
                </div>
            </div>
        </div>

        <!-- SUBSCRIPTION AND INSTALLATION -->
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <!-- Subscription -->
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    Subscription Package
                </h2>

                <div class="space-y-3">
                    <div>
                        <span class="text-xs text-slate-400 block font-medium">
                            Plan Type
                        </span>
                        <span class="text-lg font-bold text-slate-800">
                            {{ $subscriber->plan_name ?? 'No assigned plan' }}
                        </span>
                    </div>

                    <div>
                        <span class="text-xs text-slate-400 block font-medium">
                            Monthly Fee
                        </span>
                        <span class="text-xl font-black tracking-tight text-slate-900 sm:text-2xl">
                            ₱{{ number_format($subscriber->monthly_fee, 2) }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Installation -->
            <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h2 class="mb-3 text-[11px] font-bold uppercase tracking-wider text-slate-400">
                    Installation Details
                </h2>

                <div class="space-y-3.5">
                    <div>
                        <span class="text-xs text-slate-400 font-medium block">
                            Contact Number
                        </span>
                        <span class="text-sm font-semibold text-slate-700">
                            {{ $subscriber->phone_number ?? 'N/A' }}
                        </span>
                    </div>

                    <div class="border-t border-slate-100 pt-3">
                        <span class="text-xs text-slate-400 font-medium block">
                            Service Address
                        </span>
                        <span class="text-sm font-semibold text-slate-700 block leading-relaxed break-words">
                            {{ $subscriber->address ?? 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- OUTSTANDING BALANCE -->
        <div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3.5">
                <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider">
                            Outstanding Balance
                        </h2>

                        <p class="mt-0.5 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl">
                            ₱{{ number_format($outstandingBalance, 2) }}
                        </p>
                    </div>

                    @if($pendingBills->count())
                        <span class="inline-flex items-center justify-center gap-1.5 bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                            Payment Under Review
                        </span>
                    @elseif($outstandingBalance > 0)
                        <span class="inline-flex items-center justify-center gap-1.5 bg-red-50 text-red-700 border border-red-200 px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                            {{ $payableBills->count() }}
                            {{ $payableBills->count() === 1 ? 'Month' : 'Months' }}
                            Outstanding
                        </span>
                    @else
                        <span class="inline-flex items-center justify-center gap-1.5 bg-green-50 text-green-700 border border-green-200 px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                            Account Up to Date
                        </span>
                    @endif
                </div>

                @if($pendingBills->count())
                    <p class="mt-2 text-[11px] leading-relaxed text-slate-500">
                        Your payment is currently waiting for administrator verification.
                    </p>
                @elseif($outstandingBalance > 0)
                    <p class="mt-2 text-[11px] leading-relaxed text-slate-500">
                        Your outstanding balance includes all unpaid and overdue billing months.
                        You can pay the full outstanding balance in one payment.
                    </p>
                @else
                    <p class="mt-2 text-[11px] leading-relaxed text-slate-500">
                        You have no outstanding balance at this time.
                    </p>
                @endif
            </div>

            <!-- GCash Payment -->
            @if($outstandingBalance > 0 && !$pendingBills->count())
                <div class="p-4">
                    <div class="rounded-xl bg-slate-900 p-4 text-white">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                    GCash Payment
                                </p>
                                <p class="text-sm text-slate-300 mt-1">
                                    Pay your full outstanding balance using the account below.
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-white/5 p-2.5">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                                    Account Name
                                </p>
                                <p class="text-sm font-extrabold mt-1 break-words">
                                    {{ $settings->gcash_name ?? 'Not configured' }}
                                </p>
                            </div>

                            <div class="rounded-lg bg-white/5 p-2.5">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                                    GCash Number
                                </p>
                                <p class="text-sm font-extrabold mt-1">
                                    {{ $settings->gcash_number ?? 'Not configured' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 border-t border-white/10 pt-3">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <p class="text-xs text-slate-400">
                                        Amount to Pay
                                    </p>
                                    <p class="mt-0.5 text-xl font-black">
                                        ₱{{ number_format($outstandingBalance, 2) }}
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    onclick="openOutstandingPaymentModal()"
                                    class="rounded-lg bg-white px-3 py-2.5 text-xs font-bold text-slate-900 transition hover:bg-slate-100 sm:px-4 sm:text-sm"
                                >
                                    Pay Outstanding Balance
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- BILLING HISTORY -->
        <div class="mt-3 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-4 py-3.5">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider">
                            Billing History
                        </h2>
                        <p class="text-xs text-slate-500 mt-1">
                            All generated monthly billing records.
                        </p>
                    </div>

                    <div class="text-xs text-slate-400">
                        {{ $billingRecords->count() }}
                        {{ $billingRecords->count() === 1 ? 'billing record' : 'billing records' }}
                    </div>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($billingRecords as $bill)
                    @php
                        $billStatus = strtolower($bill->status ?? 'unpaid');
                        $billingMonth = \Carbon\Carbon::create(
                            $bill->billing_year,
                            $bill->billing_month,
                            1
                        )->format('F Y');
                        $billAmount = (float) $bill->amount;
                    @endphp

                    <div class="p-4">
                        <div class="flex flex-col gap-2.5 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="text-base font-extrabold text-slate-800">
                                        {{ $billingMonth }}
                                    </h3>

                                    @if($billStatus === 'paid')
                                        <span class="inline-flex items-center gap-1.5 bg-green-50 text-green-700 border border-green-200 px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                                            Paid
                                        </span>
                                    @elseif($billStatus === 'pending')
                                        <span class="inline-flex items-center gap-1.5 bg-amber-50 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>
                                            Under Review
                                        </span>
                                    @elseif($billStatus === 'overdue')
                                        <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-700 border border-red-200 px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>
                                            Overdue
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-600 border border-slate-200 px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider">
                                            <span class="w-1.5 h-1.5 bg-slate-400 rounded-full"></span>
                                            Unpaid
                                        </span>
                                    @endif
                                </div>

                                @if($bill->due_date)
                                    <p class="text-xs text-slate-400 mt-1">
                                        Due {{ \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') }}
                                    </p>
                                @endif
                            </div>

                            <div class="text-left sm:text-right">
                                <p class="text-xs text-slate-400 font-medium">
                                    Amount
                                </p>
                                <p class="text-lg font-black text-slate-900 sm:text-xl">
                                    ₱{{ number_format($billAmount, 2) }}
                                </p>
                            </div>
                        </div>

                        @if($billStatus === 'pending')
                            <div class="mt-2.5 rounded-lg border border-amber-200 bg-amber-50 p-3">
                                <div class="flex gap-3">
                                    <div>
                                        <p class="font-bold text-amber-800 text-sm">
                                            Payment Under Review
                                        </p>
                                        <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                                            This billing month is included in your submitted outstanding-balance payment and is waiting for administrator verification.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @elseif($billStatus === 'paid')
                            <div class="mt-2.5 rounded-lg border border-green-200 bg-green-50 p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <p class="font-bold text-green-800 text-sm">
                                        Payment Verified
                                    </p>

                                    @if($bill->paid_at)
                                        <p class="text-xs text-green-700 mt-1">
                                            Paid on {{ \Carbon\Carbon::parse($bill->paid_at)->format('M d, Y h:i A') }}
                                        </p>
                                    @else
                                        <p class="text-xs text-green-700 mt-1">
                                            This billing month has been paid.
                                        </p>
                                    @endif
                                </div>

                                <div>
                                    <button 
                                        type="button" 
                                        onclick="openSOAModal()" 
                                        class="inline-flex items-center gap-1.5 bg-white text-green-800 hover:bg-green-100 border border-green-300 font-bold px-3 py-1.5 rounded-lg text-xs shadow-sm transition"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                        View SOA / Receipt
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-4 py-8 text-center">
                        <h3 class="mt-4 text-base font-bold text-slate-800">
                            No Billing Records
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Your billing records will appear here.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </main>

    <!-- STATEMENT OF ACCOUNT (SOA) MODAL -->
    <div
        id="soaModal"
        class="fixed inset-0 z-[100] hidden overflow-y-auto"
        aria-labelledby="soaModalTitle"
        role="dialog"
        aria-modal="true"
    >
        <div
            class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity no-print"
            onclick="closeSOAModal()"
        ></div>

        <div class="relative min-h-screen flex items-center justify-center p-2 sm:p-4">
            <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl p-6 sm:p-8 border border-slate-200 printable-soa my-8">
                
                <!-- Action Bar (Hidden when printing) -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6 no-print">
                    <button
                        type="button"
                        onclick="printSOA()"
                        class="inline-flex items-center gap-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 px-4 py-2 rounded-lg shadow-sm transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Print Statement
                    </button>

                    <button
                        type="button"
                        onclick="closeSOAModal()"
                        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition"
                    >
                        ✕
                    </button>
                </div>

                <!-- DOCUMENT HEADER -->
                <div class="text-center mb-6 pb-4 border-b border-slate-200">
                    <h1 class="text-xl font-black tracking-wider uppercase text-slate-900">
                        {{ $settings->system_name ?? 'KRNDS Network Services' }}
                    </h1>
                    <p class="text-xs text-slate-500 font-semibold tracking-widest uppercase mt-0.5">
                        Statement of Account
                    </p>
                </div>

                <!-- BILL TO & ACCOUNT SUMMARY -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div class="border border-slate-200 rounded-lg p-3.5 bg-slate-50/50">
                        <h2 class="text-[11px] font-black uppercase text-slate-400 tracking-wider mb-2">
                            BILL TO:
                        </h2>
                        <div class="space-y-1 text-xs">
                            <p class="text-slate-500">
                                Client Name: <span class="font-bold text-slate-900">{{ $subscriber->full_name }}</span>
                            </p>
                            <p class="text-slate-500">
                                Address: <span class="font-semibold text-slate-800">{{ $subscriber->address ?? 'N/A' }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="border border-slate-200 rounded-lg p-3.5 bg-slate-50/50">
                        <h2 class="text-[11px] font-black uppercase text-slate-400 tracking-wider mb-2">
                            ACCOUNT SUMMARY
                        </h2>
                        <div class="space-y-1 text-xs">
                            <p class="text-slate-500">
                                Plan Subscription: <span class="font-bold text-slate-900">{{ $subscriber->plan_name ?? 'N/A' }}</span>
                            </p>
                            <p class="text-slate-500">
                                Plan Amount: <span class="font-bold text-slate-900">Php {{ number_format($subscriber->monthly_fee, 2) }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- BILLING TABLE -->
                <div class="overflow-hidden border border-slate-200 rounded-lg mb-6">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 uppercase tracking-wider font-bold text-[11px]">
                                <th class="py-2.5 px-4">Billing Period</th>
                                <th class="py-2.5 px-4">Due Date</th>
                                <th class="py-2.5 px-4 text-right">Amount Due</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 text-slate-800 font-medium">
                            @forelse($payableBills as $bill)
                                @php
                                    $period = \Carbon\Carbon::create($bill->billing_year, $bill->billing_month, 1)->format('F Y');
                                    $dueDate = $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('F d, Y') : 'N/A';
                                @endphp
                                <tr>
                                    <td class="py-2.5 px-4 font-semibold">{{ $period }}</td>
                                    <td class="py-2.5 px-4 text-slate-600">{{ $dueDate }}</td>
                                    <td class="py-2.5 px-4 text-right font-bold">Php {{ number_format($bill->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-4 px-4 text-center text-slate-400">
                                        No outstanding bills at this time.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="bg-slate-50 font-black text-xs text-slate-900 border-t-2 border-slate-300">
                                <td colspan="2" class="py-3 px-4 text-right uppercase tracking-wider">TOTAL</td>
                                <td class="py-3 px-4 text-right text-sm">
                                    Php {{ number_format($outstandingBalance, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- REMINDER STATEMENT -->
                <div class="mb-8 space-y-2 text-xs text-slate-600 leading-relaxed text-justify">
                    <p>
                        You are hereby reminded of your monthly subscription due for the billing period stated above. We humbly request that payment be settled on or before the due date to avoid any possible service interruption.
                    </p>
                    <p>
                        If payment has already been made before receiving this notice, please disregard this billing statement. We truly appreciate your prompt payment and continued support.
                    </p>
                </div>

                <!-- SIGNATORY -->
                <div class="mt-8 pt-4 flex justify-between items-end">
                    <div>
                        <p class="text-xs font-semibold text-slate-500 mb-1">Authorized Signatory:</p>
                        <div class="relative min-h-[50px] flex items-end">
                            @if(isset($settings->authorized_signature) && $settings->authorized_signature)
                                <img src="{{ asset('storage/' . $settings->authorized_signature) }}" alt="Signature" class="h-14 object-contain mb-1">
                            @else
                                <span class="text-xs font-bold text-slate-400 italic mb-1">(SGD)</span>
                            @endif
                        </div>
                        <p class="text-sm font-black text-slate-900 uppercase">
                            {{ $settings->authorized_signatory ?? 'JURISH A. SY' }}
                        </p>
                        <p class="text-xs font-medium text-slate-500">
                            {{ $settings->signatory_title ?? 'Proprietor' }}
                        </p>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="mt-10 border-t border-slate-200 pt-4 text-center">
                    <p class="text-xs font-bold text-slate-700 tracking-wide">
                        Thank you for being a valued subscriber of {{ $settings->system_name ?? 'KRNDS Network Services' }}!
                    </p>
                </div>

            </div>
        </div>
    </div>

    <!-- EDIT PROFILE MODAL -->
    <div
        id="editProfileModal"
        class="fixed inset-0 z-[100] hidden"
        aria-labelledby="editProfileModalTitle"
        role="dialog"
        aria-modal="true"
    >
        <div
            class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
            onclick="closeEditProfileModal()"
        ></div>

        <div class="relative min-h-screen flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div class="relative max-h-[92vh] w-full overflow-y-auto rounded-t-2xl bg-white shadow-2xl sm:max-w-md sm:rounded-2xl">
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3.5">
                    <h2 id="editProfileModalTitle" class="text-lg font-extrabold text-slate-900">
                        Edit Profile
                    </h2>
                    <button
                        type="button"
                        onclick="closeEditProfileModal()"
                        class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition"
                    >
                        ✕
                    </button>
                </div>

                <form
                    method="POST"
                    action="{{ route('subscriber.profile.update') }}"
                    enctype="multipart/form-data"
                    class="space-y-4 p-4"
                >
                    @csrf

                    <div>
                        <label for="full_name" class="block text-xs font-bold text-slate-700 mb-1">
                            Full Name
                        </label>
                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            value="{{ old('full_name', $subscriber->full_name) }}"
                            required
                            class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300"
                        >
                    </div>

                    <div>
                        <label for="profile_photo" class="block text-xs font-bold text-slate-700 mb-1">
                            Profile Picture
                        </label>
                        <input
                            type="file"
                            id="profile_photo"
                            name="profile_photo"
                            accept="image/jpeg,image/png,image/jpg,image/webp"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer"
                        >
                        <p class="text-[10px] text-slate-400 mt-1">Max file size: 4MB (JPG, PNG, WEBP)</p>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button
                            type="button"
                            onclick="closeEditProfileModal()"
                            class="flex-1 rounded-lg border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="flex-1 rounded-lg bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- OUTSTANDING PAYMENT MODAL -->
    @if($outstandingBalance > 0 && !$pendingBills->count())
        <div
            id="outstandingPaymentModal"
            class="fixed inset-0 z-[100] hidden"
            aria-labelledby="outstandingPaymentModalTitle"
            role="dialog"
            aria-modal="true"
        >
            <div
                class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                onclick="closeOutstandingPaymentModal()"
            ></div>

            <div class="relative min-h-screen flex items-end sm:items-center justify-center p-0 sm:p-4">
                <div class="relative max-h-[92vh] w-full overflow-y-auto rounded-t-2xl bg-white shadow-2xl sm:max-w-lg sm:rounded-2xl">
                    <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3.5">
                        <div>
                            <h2
                                id="outstandingPaymentModalTitle"
                                class="text-lg font-extrabold text-slate-900"
                            >
                                Pay Outstanding Balance
                            </h2>

                            <p class="text-xs text-slate-500 mt-1">
                                Submit one GCash payment for your outstanding bills.
                            </p>
                        </div>

                        <button
                            type="button"
                            onclick="closeOutstandingPaymentModal()"
                            class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition"
                        >
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24">
                                <path
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M6 6l12 12M18 6 6 18"
                                />
                            </svg>
                        </button>
                    </div>

                    <form
                        method="POST"
                        action="{{ route('subscriber.payment.submit') }}"
                        enctype="multipart/form-data"
                        class="space-y-3.5 p-4"
                    >
                        @csrf

                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                            <div class="flex items-center justify-between gap-4">
                                <div>
                                    <span class="text-xs text-slate-400 font-medium block">
                                        Outstanding Balance
                                    </span>

                                    <span class="text-2xl font-black text-slate-900">
                                        ₱{{ number_format($outstandingBalance, 2) }}
                                    </span>
                                </div>

                                <div class="text-right">
                                    <span class="text-xs text-slate-400 font-medium block">
                                        Billing Months
                                    </span>

                                    <span class="text-sm font-bold text-slate-700">
                                        {{ $payableBills->count() }}
                                        {{ $payableBills->count() === 1 ? 'Month' : 'Months' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="overflow-hidden rounded-xl border border-slate-200">
                            <div class="border-b border-slate-200 bg-slate-50 px-3 py-2.5">
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                                    Bills Covered by This Payment
                                </p>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @foreach($payableBills as $bill)
                                    @php
                                        $coveredMonth = \Carbon\Carbon::create(
                                            $bill->billing_year,
                                            $bill->billing_month,
                                            1
                                        )->format('F Y');
                                    @endphp

                                    <div class="flex items-center justify-between gap-3 px-3 py-2.5">
                                        <span class="text-sm font-semibold text-slate-700">
                                            {{ $coveredMonth }}
                                        </span>

                                        <span class="text-sm font-bold text-slate-900">
                                            ₱{{ number_format($bill->amount, 2) }}
                                        </span>
                                    </div>
                                @endforeach

                                <div class="flex items-center justify-between gap-3 bg-slate-50 px-3 py-2.5">
                                    <span class="text-sm font-bold text-slate-700">
                                        Total
                                    </span>

                                    <span class="text-base font-black text-slate-900">
                                        ₱{{ number_format($outstandingBalance, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl bg-slate-900 p-3.5 text-white">
                            <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                Send Payment To
                            </p>

                            <div class="mt-2">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                                    GCash Account Name
                                </p>

                                <p class="text-base font-extrabold mt-1 break-words">
                                    {{ $settings->gcash_name ?? 'Not configured' }}
                                </p>
                            </div>

                            <div class="mt-2">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                                    GCash Number
                                </p>

                                <p class="text-xl font-black tracking-wide mt-1">
                                    {{ $settings->gcash_number ?? 'Not configured' }}
                                </p>
                            </div>

                            <p class="text-xs text-slate-300 mt-3 leading-relaxed">
                                Send exactly
                                <strong>
                                    ₱{{ number_format($outstandingBalance, 2) }}
                                </strong>
                                to this GCash account before submitting your payment.
                            </p>
                        </div>

                        <div>
                            <label
                                for="amount_paid"
                                class="block text-sm font-bold text-slate-700 mb-2"
                            >
                                Amount Paid
                            </label>

                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold">
                                    ₱
                                </span>

                                <input
                                    type="number"
                                    id="amount_paid"
                                    name="amount_paid"
                                    value="{{ old('amount_paid', number_format($outstandingBalance, 2, '.', '')) }}"
                                    min="{{ number_format($outstandingBalance, 2, '.', '') }}"
                                    max="{{ number_format($outstandingBalance, 2, '.', '') }}"
                                    step="0.01"
                                    required
                                    readonly
                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-2.5 pl-8 pr-3 text-sm font-semibold text-slate-800 focus:outline-none"
                                >
                            </div>

                            <p class="text-xs text-slate-400 mt-1.5">
                                The full outstanding balance must be paid.
                            </p>
                        </div>

                        <div>
                            <label
                                for="reference_number"
                                class="block text-sm font-bold text-slate-700 mb-2"
                            >
                                GCash Reference Number
                            </label>

                            <input
                                type="text"
                                id="reference_number"
                                name="reference_number"
                                value="{{ old('reference_number') }}"
                                required
                                maxlength="255"
                                class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-200"
                                placeholder="Enter your GCash reference number"
                            >

                            <p class="text-xs text-slate-400 mt-1.5">
                                You can find the reference number on your GCash payment receipt.
                            </p>
                        </div>

                        <div>
                            <label
                                for="payment_screenshot"
                                class="block text-sm font-bold text-slate-700 mb-2"
                            >
                                Proof of Payment
                            </label>

                            <label
                                for="payment_screenshot"
                                class="block cursor-pointer rounded-xl border-2 border-dashed border-slate-200 p-4 text-center transition hover:border-slate-400"
                            >

                                <span class="text-sm font-semibold text-slate-700 block">
                                    Choose GCash screenshot
                                </span>

                                <span
                                    id="fileName"
                                    class="text-xs text-slate-400 block mt-1"
                                >
                                    JPG, JPEG, PNG, or WEBP (Max 4MB)
                                </span>

                                <input
                                    type="file"
                                    id="payment_screenshot"
                                    name="payment_screenshot"
                                    accept="image/jpeg,image/png,image/jpg,image/webp"
                                    required
                                    class="hidden"
                                    onchange="showFileName(this)"
                                >
                            </label>
                        </div>

                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3">
                            <div class="flex"><div class="text-xs text-amber-800 leading-relaxed">
                                    Your payment will be reviewed by the administrator.
                                    All billing months included in this payment will only be
                                    marked as <strong>Paid</strong> after approval.
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse gap-2 pt-0.5 sm:flex-row">
                            <button
                                type="button"
                                onclick="closeOutstandingPaymentModal()"
                                class="flex-1 rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                            >
                                Cancel
                            </button>

                            <button
                                type="submit"
                                class="flex-1 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800"
                            >
                                Submit Payment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- SCRIPTS -->
    <script>
        function openSOAModal() {
            const modal = document.getElementById('soaModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeSOAModal() {
            const modal = document.getElementById('soaModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        function printSOA() {
            window.print();
        }

        function openEditProfileModal() {
            const modal = document.getElementById('editProfileModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }
        }

        function closeEditProfileModal() {
            const modal = document.getElementById('editProfileModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        function openOutstandingPaymentModal() {
            const modal = document.getElementById('outstandingPaymentModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeOutstandingPaymentModal() {
            const modal = document.getElementById('outstandingPaymentModal');
            if (!modal) return;
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        function showFileName(input) {
            const fileName = document.getElementById('fileName');
            if (!fileName) return;

            if (input.files && input.files.length > 0) {
                fileName.textContent = input.files[0].name;
                fileName.classList.remove('text-slate-400');
                fileName.classList.add('text-slate-700');
            } else {
                fileName.textContent = 'JPG, JPEG, PNG, or WEBP (Max 4MB)';
                fileName.classList.remove('text-slate-700');
                fileName.classList.add('text-slate-400');
            }
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeSOAModal();
                closeOutstandingPaymentModal();
                closeEditProfileModal();
            }
        });
    </script>

</body>
</html>