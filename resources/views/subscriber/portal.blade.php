<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscriber Portal - {{ $settings->system_name ?? 'Client Area' }}</title>
    <script>
        try {
            if (localStorage.getItem('admin-theme') === 'dark') {
                document.documentElement.classList.add('dark');
            }
        } catch (error) {}
    </script>

    @if($settings && $settings->system_logo)
        <link rel="icon" type="image/png" href="{{ asset('storage/' . $settings->system_logo) }}">
        <link rel="shortcut icon" href="{{ asset('storage/' . $settings->system_logo) }}">
    @else
        <link rel="icon" type="image/png" href="{{ asset('appicon.png') }}">
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            @page {
                size: A4 portrait;
                margin: 0;
            }

            /* Hide non-printable UI elements */
            html, body, body * {
                visibility: hidden !important;
                background: #ffffff !important;
            }

            /* Display modal contents explicitly */
            #soaModal:not(.hidden),
            #soaModal:not(.hidden) *,
            #receiptModal:not(.hidden),
            #receiptModal:not(.hidden) * {
                visibility: visible !important;
            }

            /* Modal Layout Wrapper */
            #soaModal:not(.hidden),
            #receiptModal:not(.hidden) {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                height: 100% !important;
                min-height: 297mm !important;
                margin: 0 !important;
                padding: 0 !important;
                display: block !important;
                background: #ffffff !important;
            }

            #soaModal:not(.hidden) > div,
            #receiptModal:not(.hidden) > div {
                display: block !important;
                height: 100% !important;
                min-height: 297mm !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            /* Uniform Printable Container Box */
            .printable-soa,
            .printable-receipt {
                position: relative !important;
                width: 100% !important;
                max-width: 100% !important;
                height: 100% !important;
                min-height: 280mm !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                box-sizing: border-box !important;
                margin: 0 auto !important;
                padding: 15mm 15mm 12mm 15mm !important;
                border: none !important;
                box-shadow: none !important;
                background: #ffffff !important;
                page-break-inside: avoid;
            }

            /* Content wrapper to force natural layout spacing */
            .printable-soa-content,
            .printable-receipt-content {
                display: flex !important;
                flex-direction: column !important;
                gap: 1.25rem !important;
            }

            /* Maintain Layout Grids */
            .printable-soa .flex, .printable-receipt .flex { display: flex !important; }
            .printable-soa .grid, .printable-receipt .grid { display: grid !important; }

            /* Hide action buttons and non-printable elements */
            .no-print,
            .no-print *,
            .backdrop-blur-sm {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                width: 0 !important;
                overflow: hidden !important;
            }
        }
    </style>
</head>

<body class="min-h-screen bg-slate-100 font-sans text-slate-800 antialiased py-4 sm:py-8 px-3 sm:px-6">

    <!-- APP CONTAINER: RESPONSIVE DESKTOP & MOBILE WRAPPER -->
    <div class="max-w-6xl mx-auto bg-slate-50 min-h-[90vh] rounded-2xl sm:rounded-3xl shadow-xl sm:border sm:border-slate-200 overflow-hidden flex flex-col pb-8">

        <!-- HEADER SECTION -->
        <div class="bg-slate-800 text-white px-6 py-6 rounded-b-3xl shadow-md relative">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                
                <!-- User Profile Header -->
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="h-14 w-14 rounded-full overflow-hidden bg-slate-700 border-2 border-slate-500 flex items-center justify-center font-bold text-xl text-white shadow-inner">
                            @if($subscriber->profile_photo)
                                <img src="{{ asset('storage/' . $subscriber->profile_photo) }}" class="w-full h-full object-cover" alt="Profile">
                            @else
                                {{ strtoupper(substr($subscriber->full_name, 0, 1)) }}
                            @endif
                        </div>
                        <button onclick="openEditProfileModal()" class="absolute -bottom-1 -right-1 bg-blue-600 text-white p-1.5 rounded-full shadow hover:bg-blue-500 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                    </div>

                    <div>
                        <h1 class="text-lg sm:text-xl font-bold leading-tight truncate text-white">
                            {{ $subscriber->full_name }}
                        </h1>
                        <p class="text-xs text-slate-300 font-medium mt-0.5">
                            Username: <span class="text-slate-100 font-bold">{{ $subscriber->username }}</span>
                        </p>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-xs text-slate-300">Status:</span>
                            @if($subscriber->status === 'active')
                                <span class="text-xs font-bold text-emerald-400 bg-emerald-950/50 border border-emerald-500/30 px-2 py-0.5 rounded-full uppercase tracking-wider">Active</span>
                            @elseif($subscriber->status === 'inactive')
                                <span class="text-xs font-bold text-amber-400 bg-amber-950/50 border border-amber-500/30 px-2 py-0.5 rounded-full uppercase tracking-wider">Inactive</span>
                            @else
                                <span class="text-xs font-bold text-red-400 bg-red-950/50 border border-red-500/30 px-2 py-0.5 rounded-full uppercase tracking-wider">Disconnected</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Header Logout Button -->
                <div class="self-end sm:self-center flex items-center gap-2">
                    <button
                        id="adminThemeToggle"
                        type="button"
                        aria-label="Enable dark mode"
                        aria-pressed="false"
                        title="Enable dark mode"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-700/80 text-slate-200 transition hover:bg-slate-600"
                    >
                        <svg data-theme-icon="moon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.5 15.5A8.5 8.5 0 018.5 3.5a8.5 8.5 0 1012 12z"/>
                        </svg>
                        <svg data-theme-icon="sun" class="hidden h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <circle cx="12" cy="12" r="4" stroke-width="2"/>
                            <path stroke-linecap="round" stroke-width="2" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/>
                        </svg>
                    </button>
                    <form method="POST" action="{{ route('subscriber.logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-700/80 hover:bg-red-600/90 text-slate-200 text-xs font-semibold transition" title="Sign Out">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- PAYMENT STATUS NOTIFICATIONS -->
        <div class="px-4 sm:px-8 pt-4 space-y-3">
            
            <!-- 1. RECENTLY ACCEPTED PAYMENT NOTICE BANNER -->
            @if(isset($recentlyAcceptedPayments) && $recentlyAcceptedPayments->isNotEmpty())
                @foreach($recentlyAcceptedPayments as $accepted)
                    <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm text-xs text-emerald-900 flex items-start justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-1.5 font-bold text-emerald-700">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Payment Accepted & Approved
                            </div>
                            <p class="text-slate-600">
                                Your payment submission of <strong class="text-emerald-800">₱{{ number_format($accepted->amount_paid ?? $accepted->amount, 2) }}</strong> (Ref: <span class="font-mono font-bold">{{ $accepted->reference_number ?? 'N/A' }}</span>) has been verified and processed successfully.
                            </p>
                        </div>
                        <span class="text-[10px] text-emerald-600 whitespace-nowrap font-medium">
                            {{ $accepted->reviewed_at ? \Carbon\Carbon::parse($accepted->reviewed_at)->format('M d, Y') : ($accepted->updated_at ? \Carbon\Carbon::parse($accepted->updated_at)->format('M d, Y') : '') }}
                        </span>
                    </div>
                @endforeach
            @endif

            <!-- 2. REJECTED PAYMENT NOTICE BANNER -->
            @if(isset($rejectedPayments) && $rejectedPayments->isNotEmpty())
                @foreach($rejectedPayments as $rejected)
                    <div class="rounded-2xl border border-red-200 bg-red-50 p-4 shadow-sm text-xs text-red-800">
                        <div class="flex items-center justify-between font-bold mb-1">
                            <span class="flex items-center gap-1.5 text-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Payment Submission Rejected
                            </span>
                            <span class="text-[10px] text-red-500 font-normal">
                                {{ $rejected->reviewed_at ? \Carbon\Carbon::parse($rejected->reviewed_at)->format('M d, Y h:i A') : '' }}
                            </span>
                        </div>
                        <p class="mt-1">
                            <strong class="font-bold">Reason:</strong> {{ $rejected->admin_note ?? 'No specific reason provided by administrator.' }}
                        </p>
                        <div class="mt-2 text-[11px] text-red-600 font-medium">
                            Ref No: <span class="font-mono font-bold">{{ $rejected->reference_number }}</span> — Amount: <span class="font-bold">₱{{ number_format($rejected->amount_paid, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <!-- MAIN RESPONSIVE CONTENT GRID -->
        <div class="px-4 sm:px-8 pt-4 space-y-6 flex-1">

            <!-- ALERTS -->
            @if(session('success'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3.5 text-xs font-semibold text-emerald-700 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl border border-red-200 bg-red-50 p-3.5 text-xs font-semibold text-red-700 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 p-3.5 text-xs text-red-700 shadow-sm">
                    <p class="font-bold mb-1">Please review the errors below:</p>
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- QUICK ACTION NAVIGATION -->
            <div class="grid grid-cols-3 gap-3 sm:gap-6 text-center max-w-xl mx-auto">
                <button 
                    @if($outstandingBalance > 0 && !$pendingBills->count()) onclick="openOutstandingPaymentModal()" @endif 
                    class="flex flex-col items-center group focus:outline-none {{ ($outstandingBalance <= 0 || $pendingBills->count()) ? 'opacity-50 cursor-not-allowed' : '' }}"
                >
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-blue-500 text-white flex items-center justify-center shadow-md group-hover:scale-105 group-active:scale-95 transition-all p-3.5">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 mt-2 leading-tight">Pay Bill</span>
                </button>

                <button onclick="openBillingHistoryModal()" class="flex flex-col items-center group focus:outline-none">
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-indigo-500 text-white flex items-center justify-center shadow-md group-hover:scale-105 group-active:scale-95 transition-all p-3.5">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 mt-2 leading-tight">History</span>
                </button>

                <button
                    type="button"
                    @if($soaAvailable) onclick="openSOAModal()" @else disabled title="Available when an unpaid bill is due within five days" @endif
                    class="flex flex-col items-center group focus:outline-none {{ $soaAvailable ? '' : 'opacity-50 cursor-not-allowed' }}"
                >
                    <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl bg-emerald-500 text-white flex items-center justify-center shadow-md group-hover:scale-105 group-active:scale-95 transition-all p-3.5">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <span class="text-xs font-bold text-slate-700 mt-2 leading-tight">SOA</span>
                </button>
            </div>

            <!-- TWO COLUMN DESKTOP GRID -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- DASHBOARD SUMMARY CARDS -->
                <div class="lg:col-span-2 bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Monthly Fee</span>
                        <span class="text-lg font-black text-slate-800 mt-1 block">₱{{ number_format($subscriber->monthly_fee, 2) }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Due Balance</span>
                        <span class="text-lg font-black mt-1 block {{ $outstandingBalance > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                            ₱{{ number_format($outstandingBalance, 2) }}
                        </span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Package Plan</span>
                        <span class="text-sm font-extrabold text-slate-800 mt-1 block truncate">{{ $subscriber->plan_name ?? 'Standard Plan' }}</span>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Due Date</span>
                        <span class="text-sm font-extrabold text-slate-800 mt-1 block">
                            {{ isset($currentBill) && $currentBill && $currentBill->due_date ? \Carbon\Carbon::parse($currentBill->due_date)->format('M d, Y') : 'N/A' }}
                        </span>
                    </div>
                </div>

                <!-- PAYMENT STATUS CARD -->
                <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm flex flex-col justify-center">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Account Notice</h3>
                    @if($pendingBills->count())
                        <div class="flex items-center gap-2 text-amber-800">
                            <span class="h-2.5 w-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                            <span class="font-bold text-xs">Payment Under Review</span>
                        </div>
                        <p class="text-slate-500 mt-1 text-xs leading-relaxed">
                            Your submitted payment is currently being verified by the admin.
                        </p>
                    @elseif($outstandingBalance > 0)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2 text-red-700">
                                <span class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
                                <span class="font-bold text-xs">{{ $payableBills->count() }} Month(s) Outstanding</span>
                            </div>
                            <button onclick="openOutstandingPaymentModal()" class="bg-slate-900 text-white px-3.5 py-1.5 rounded-xl text-xs font-bold shadow hover:bg-slate-800 transition">
                                Pay Now
                            </button>
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-emerald-700">
                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-xs">Account is Up to Date</span>
                        </div>
                    @endif
                </div>

            </div>

            <!-- SUBSCRIBER INFORMATION SECTION -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm space-y-3">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100 pb-2">
                    Subscriber Details
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                    <div class="flex justify-between items-center bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-400 font-medium">Contact Phone:</span>
                        <span class="font-bold text-slate-700">{{ $subscriber->phone_number ?? 'N/A' }}</span>
                    </div>

                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                        <span class="text-slate-400 font-medium block mb-0.5">Service Address:</span>
                        <span class="font-semibold text-slate-700 leading-snug block">
                            {{ $subscriber->address ?? 'No address provided' }}
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>


    <!-- ==================== MODALS SECTION ==================== -->

    <!-- MODAL: BILLING HISTORY -->
    <div id="billingHistoryModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closeBillingHistoryModal()"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl p-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h2 class="text-base font-extrabold text-slate-900">Billing & Payment History</h2>
                    <button onclick="closeBillingHistoryModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold">✕</button>
                </div>

                <div class="max-h-[60vh] overflow-y-auto divide-y divide-slate-100 space-y-3 pr-1">
                    @forelse($billingRecords as $bill)
                        @php
                            $billStatus = strtolower($bill->status ?? 'unpaid');
                            $billingMonth = \Carbon\Carbon::create($bill->billing_year, $bill->billing_month, 1)->format('F Y');
                            
                            $allocation = $bill->paymentAllocations->first();
                            $submission = $allocation ? $allocation->paymentSubmission : null;
                            
                            $refNo = $submission->reference_number ?? ($bill->reference_number ?? 'N/A');
                            $paidDate = $bill->paid_at 
                                ? \Carbon\Carbon::parse($bill->paid_at)->format('M d, Y') 
                                : ($bill->updated_at ? \Carbon\Carbon::parse($bill->updated_at)->format('M d, Y') : 'N/A');
                            $screenshot = $submission->payment_screenshot ?? $bill->payment_screenshot ?? null;
                        @endphp
                        <div class="pt-3 first:pt-0">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="text-sm font-extrabold text-slate-800">{{ $billingMonth }}</h4>
                                    <p class="text-[11px] text-slate-400 mt-0.5">
                                        Due: {{ $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') : 'N/A' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-black text-slate-900 block">₱{{ number_format($bill->amount, 2) }}</span>
                                    
                                    <div class="mt-1 flex items-center justify-end gap-1.5">
                                        @if($billStatus === 'paid')
                                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-200 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">Paid</span>
                                            
                                            <button type="button" 
                                                onclick="openReceiptModal({
                                                    id: '{{ $bill->id }}',
                                                    receipt_no: '#REC-{{ str_pad($bill->id, 6, "0", STR_PAD_LEFT) }}',
                                                    month: '{{ $billingMonth }}',
                                                    amount: '₱{{ number_format($bill->amount, 2) }}',
                                                    ref: '{{ $refNo }}',
                                                    date: '{{ $paidDate }}',
                                                    screenshot: '{{ $screenshot ? asset("storage/" . $screenshot) : "" }}'
                                                })" 
                                                class="bg-slate-900 hover:bg-slate-800 text-white px-2.5 py-0.5 rounded-full text-[10px] font-bold shadow-sm transition">
                                                View Receipt
                                            </button>
                                        @elseif($billStatus === 'pending')
                                            <span class="bg-amber-50 text-amber-700 border border-amber-200 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">Under Review</span>
                                        @else
                                            <span class="bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase">Unpaid</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="py-8 text-center text-xs text-slate-400">No billing records found.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: OFFICIAL RECEIPT PREVIEW -->
    <div id="receiptModal" class="fixed inset-0 z-[110] hidden overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm no-print" onclick="closeReceiptModal()"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl p-8 border border-slate-200 printable-receipt my-6">
                
                <!-- TOP & MAIN CONTENT (Flex Container) -->
                <div class="printable-receipt-content">
                    
                    <!-- ACTION BAR (Hidden when printed) -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 no-print">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Official Receipt Preview</span>
                        <div class="flex items-center gap-2">
                            <button onclick="window.print()" class="text-xs font-bold bg-slate-900 text-white px-3.5 py-1.5 rounded-xl transition hover:bg-slate-800">Print Receipt</button>
                            <button onclick="closeReceiptModal()" class="w-7 h-7 rounded-full bg-slate-100 text-slate-500 font-bold text-xs flex items-center justify-center">✕</button>
                        </div>
                    </div>

                    <!-- BRANDING & RECEIPT HEADER -->
                    <div class="flex items-center justify-between pb-6 border-b border-slate-200">
                        <div class="flex items-center gap-4">
                            @if(isset($settings->system_logo) && $settings->system_logo)
                                <img src="{{ asset('storage/' . $settings->system_logo) }}" alt="Logo" class="h-12 w-auto object-contain">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white font-black flex items-center justify-center text-lg shadow-sm">
                                    {{ strtoupper(substr($settings->system_name ?? 'K', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <h2 class="text-base font-black tracking-wide uppercase text-slate-900">
                                    {{ $settings->system_name ?? 'KRNDS Network Services' }}
                                </h2>
                                <p class="text-xs text-slate-500 font-medium mt-0.5">
                                    {{ $settings->soa_location ?? 'Zone 1, Sogod, Southern Leyte' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-block bg-emerald-600 text-white font-black text-[10px] px-3 py-1 rounded-lg tracking-widest uppercase shadow-xs">
                                Official Receipt
                            </span>
                            <p id="receiptNo" class="text-xs font-mono font-bold text-slate-700 mt-2">#REC-000000</p>
                            <p class="text-[11px] font-semibold text-slate-500 mt-0.5">
                                Payment Date: <span id="receiptDate" class="text-slate-800">--</span>
                            </p>
                        </div>
                    </div>

                    <!-- INFORMATION GRID (SUBSCRIBER & PAYMENT DETAILS) -->
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="border border-slate-200 rounded-xl p-3.5 bg-slate-50/60">
                            <h3 class="text-[10px] font-black uppercase text-slate-400 tracking-wider mb-2">RECEIVED FROM:</h3>
                            <p class="text-slate-500">Subscriber: <span class="font-bold text-slate-900">{{ $subscriber->full_name }}</span></p>
                            <p class="text-slate-500">Address: <span class="font-semibold text-slate-800">{{ $subscriber->address ?? 'N/A' }}</span></p>
                        </div>

                        <div class="border border-slate-200 rounded-xl p-3.5 bg-slate-50/60">
                            <h3 class="text-[10px] font-black uppercase text-slate-400 tracking-wider mb-2">PAYMENT DETAILS:</h3>
                            <p class="text-slate-500">Billing Period: <span id="receiptMonth" class="font-bold text-slate-900">--</span></p>
                            <p class="text-slate-500">Status: <span class="font-bold text-emerald-600 uppercase">Paid / Cleared</span></p>
                        </div>
                    </div>

                    <!-- ITEMIZED TABLE BREAKDOWN -->
                    <div class="border border-slate-200 rounded-xl overflow-hidden text-xs">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-100/80 font-bold border-b border-slate-200 text-slate-700 uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-2.5 px-4">Description</th>
                                    <th class="py-2.5 px-4 text-center">Ref No.</th>
                                    <th class="py-2.5 px-4 text-right">Amount Paid</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-800 font-medium">
                                <tr>
                                    <td class="py-3 px-4 font-semibold text-slate-900">
                                        Subscription Fee — <span id="receiptMonthTable">--</span>
                                    </td>
                                    <td id="receiptRef" class="py-3 px-4 text-center text-slate-600 font-mono font-bold">--</td>
                                    <td id="receiptAmount" class="py-3 px-4 text-right font-bold text-slate-900">₱0.00</td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-slate-50 font-black text-xs text-slate-900 border-t-2 border-slate-300">
                                <tr>
                                    <td colspan="2" class="py-3 px-4 text-right uppercase tracking-wider">Total Amount Paid</td>
                                    <td id="receiptTotalAmount" class="py-3 px-4 text-right text-sm font-extrabold text-emerald-600">₱0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- PROOF OF PAYMENT SCREENSHOT ATTACHMENT (Hidden on print via no-print) -->
                    <div id="receiptProofWrapper" class="hidden space-y-1.5 border-t border-slate-100 pt-3 no-print">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Uploaded Payment Screenshot</span>
                        <div class="flex max-h-[70vh] items-center justify-center rounded-xl border border-slate-200 bg-slate-100 p-2">
                            <img id="receiptProofImg" src="" alt="Proof of payment" class="block h-auto w-auto max-h-[66vh] max-w-full object-contain">
                        </div>
                    </div>

                    <!-- ACKNOWLEDGMENT NOTE -->
                    <div class="p-3.5 rounded-xl bg-slate-50/50 border border-slate-200/80 text-[11px] text-slate-600 leading-relaxed text-center">
                        This document serves as an official proof of payment for the monthly network service subscription fee indicated above.
                    </div>

                </div>

                <!-- FOOTER SECTION (Anchored at page bottom) -->
                <div class="mt-6 pt-3 border-t border-slate-200 text-center">
                    <p class="text-xs font-bold text-slate-700 tracking-wide">
                        Thank you for being a valued subscriber of {{ $settings->system_name ?? 'KRNDS Network Services' }}!
                    </p>
                </div>

            </div>
        </div>
    </div>

    @if($soaAvailable)
    <!-- MODAL: STATEMENT OF ACCOUNT (SOA PREVIEW) -->
    <div id="soaModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm no-print" onclick="closeSOAModal()"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative w-full max-w-2xl bg-white rounded-3xl shadow-2xl p-8 border border-slate-200 printable-soa my-8">
                
                <!-- TOP CONTENT CONTAINER (KEEPS HEADER AND BODY TOGETHER) -->
                <div class="printable-soa-content">

                    <!-- ACTION BAR -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 no-print">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Statement of Account Preview</span>
                        <div class="flex items-center gap-2">
                            <button onclick="window.print()" class="text-xs font-bold text-white bg-slate-900 px-4 py-2 rounded-xl transition hover:bg-slate-800">Print Statement</button>
                            <button onclick="closeSOAModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold">✕</button>
                        </div>
                    </div>

                    <!-- BRANDING HEADER -->
                    <div class="flex items-center justify-between pb-6 border-b border-slate-200">
                        <div class="flex items-center gap-4">
                            @if(isset($settings->system_logo) && $settings->system_logo)
                                <img src="{{ asset('storage/' . $settings->system_logo) }}" alt="Logo" class="h-12 w-auto object-contain">
                            @else
                                <div class="w-10 h-10 rounded-xl bg-slate-900 text-white font-black flex items-center justify-center text-lg shadow-sm">
                                    {{ strtoupper(substr($settings->system_name ?? 'K', 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <h1 class="text-base font-black tracking-wide uppercase text-slate-900">
                                    {{ $settings->system_name ?? 'KRNDS Network Services' }}
                                </h1>
                                <p class="text-xs text-slate-500 font-medium mt-0.5">
                                    {{ $settings->soa_location ?? 'Zone 1, Sogod, Southern Leyte' }}
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-block bg-slate-900 text-white font-black text-[10px] px-3 py-1 rounded-lg tracking-widest uppercase shadow-xs">
                                Statement of Account
                            </span>
                            <p class="text-[11px] font-semibold text-slate-500 mt-1.5">
                                Date Issued: <span class="text-slate-800">{{ \Carbon\Carbon::now()->format('M d, Y') }}</span>
                            </p>
                        </div>
                    </div>

                    <!-- BILL TO & ACCOUNT SUMMARY GRID -->
                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div class="border border-slate-200 rounded-xl p-3.5 bg-slate-50/60">
                            <h2 class="text-[10px] font-black uppercase text-slate-400 tracking-wider mb-2">BILL TO:</h2>
                            <div class="space-y-1">
                                <p class="text-slate-500">Subscriber Name: <span class="font-bold text-slate-900">{{ $subscriber->full_name }}</span></p>
                                <p class="text-slate-500">Service Address: <span class="font-semibold text-slate-800">{{ $subscriber->address ?? 'N/A' }}</span></p>
                            </div>
                        </div>

                        <div class="border border-slate-200 rounded-xl p-3.5 bg-slate-50/60">
                            <h2 class="text-[10px] font-black uppercase text-slate-400 tracking-wider mb-2">ACCOUNT SUMMARY:</h2>
                            <div class="space-y-1">
                                <p class="text-slate-500">Plan Subscription: <span class="font-bold text-slate-900">{{ $subscriber->plan_name ?? 'N/A' }}</span></p>
                                <p class="text-slate-500">Monthly Rate: <span class="font-bold text-slate-900">₱{{ number_format($subscriber->monthly_fee, 2) }}</span></p>
                            </div>
                        </div>
                    </div>

                    <!-- BILLING TABLE -->
                    <div class="border border-slate-200 rounded-xl overflow-hidden text-xs">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-100/80 font-bold border-b border-slate-200 text-slate-700 uppercase tracking-wider text-[10px]">
                                <tr>
                                    <th class="py-2.5 px-4">Billing Period</th>
                                    <th class="py-2.5 px-4">Due Date</th>
                                    <th class="py-2.5 px-4 text-right">Amount Due</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-800 font-medium">
                                @forelse($payableBills as $bill)
                                    @php
                                        $period = \Carbon\Carbon::create($bill->billing_year, $bill->billing_month, 1)->format('F Y');
                                        $dueDate = $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') : 'N/A';
                                    @endphp
                                    <tr class="hover:bg-slate-50/50">
                                        <td class="py-3 px-4 font-semibold text-slate-900">{{ $period }}</td>
                                        <td class="py-3 px-4 text-slate-600">{{ $dueDate }}</td>
                                        <td class="py-3 px-4 text-right font-bold text-slate-900">₱{{ number_format($bill->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="py-6 px-4 text-center text-slate-400">
                                            No outstanding bills at this time.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="bg-slate-50 font-black text-xs text-slate-900 border-t-2 border-slate-300">
                                    <td colspan="2" class="py-3 px-4 text-right uppercase tracking-wider">Total Amount Due</td>
                                    <td class="py-3 px-4 text-right text-sm font-extrabold text-red-600">
                                        ₱{{ number_format($outstandingBalance, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- REMINDER STATEMENT -->
                    <div class="p-3.5 rounded-xl bg-slate-50/50 border border-slate-200/80 text-xs text-slate-600 leading-relaxed text-justify whitespace-pre-line">
                        {{ $settings->soa_body_statement ?? "You are hereby reminded of your monthly subscription due for the billing period stated above. We humbly request that payment be settled on or before the due date to avoid any possible service interruption.\n\nIf payment has already been made before receiving this notice, please disregard this billing statement. We truly appreciate your prompt payment and continued support." }}
                    </div>

                    <!-- AUTHORIZED SIGNATORY -->
                    <div class="pt-4 border-t border-slate-200 flex justify-between items-end">
                        <div>
                            <p class="text-[11px] font-medium text-slate-500">If you have any questions regarding this statement, please contact our support.</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs font-semibold text-slate-500 mb-1">Authorized Signatory:</p>
                            <div class="relative min-h-[45px] flex items-end justify-end">
                                @if(isset($settings->authorized_signature) && $settings->authorized_signature)
                                    <img src="{{ asset('storage/' . $settings->authorized_signature) }}" alt="Signature" class="h-12 object-contain mb-1">
                                @else
                                    <span class="text-xs font-bold text-slate-300 italic mb-1">(SGD)</span>
                                @endif
                            </div>
                            <p class="text-xs font-black text-slate-900 uppercase tracking-wide">
                                {{ $settings->authorized_signatory ?? 'JURISH A. SY' }}
                            </p>
                            <p class="text-[11px] font-medium text-slate-500">
                                {{ $settings->signatory_title ?? 'Proprietor' }}
                            </p>
                        </div>
                    </div>

                </div>

                <!-- BOTTOM FOOTER (ANCHORS TO PAGE BOTTOM) -->
                <div class="mt-6 pt-3 border-t border-slate-100 text-center">
                    <p class="text-xs font-bold text-slate-700 tracking-wide">
                        Thank you for being a valued subscriber of {{ $settings->system_name ?? 'KRNDS Network Services' }}!
                    </p>
                </div>

            </div>
        </div>
    </div>

    @endif
    <!-- MODAL: EDIT PROFILE WITH PHOTO PREVIEW -->
    <div id="editProfileModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closeEditProfileModal()"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl p-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                    <h2 class="text-base font-extrabold text-slate-900">Edit Profile</h2>
                    <button onclick="closeEditProfileModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold">✕</button>
                </div>

                <form method="POST" action="{{ route('subscriber.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Full Name</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $subscriber->full_name) }}" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold">
                    </div>

                    <!-- PROFILE PHOTO INPUT & REAL-TIME PREVIEW -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Profile Photo</label>
                        <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/*" onchange="previewProfileImage(event)" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-slate-100 file:font-bold">
                        
                        <!-- Preview Box -->
                        <div id="profilePhotoPreviewBox" class="mt-3 flex flex-col items-center justify-center p-3 border border-dashed border-slate-300 rounded-2xl bg-slate-50">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2 block">Photo Preview</span>
                            <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-slate-300 shadow-sm bg-slate-200 flex items-center justify-center">
                                <img id="profilePhotoPreviewImg" 
                                    src="{{ $subscriber->profile_photo ? asset('storage/' . $subscriber->profile_photo) : '' }}" 
                                    alt="Profile Preview" 
                                    class="w-full h-full object-cover {{ $subscriber->profile_photo ? '' : 'hidden' }}">
                                <span id="profilePhotoPlaceholder" class="text-xs text-slate-400 font-bold {{ $subscriber->profile_photo ? 'hidden' : '' }}">
                                    No Image
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- CHANGE PASSWORD SECTION WITH SHOW/HIDE TOGGLES & STRENGTH METER -->
                    <div class="border-t border-slate-100 pt-3 space-y-3">
                        <span class="text-xs font-bold text-slate-900 block">Change Password <span class="text-slate-400 font-normal">(Optional)</span></span>
                        
                        <!-- Current Password -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Current Password</label>
                            <div class="relative">
                                <input type="password" name="current_password" id="currentPassword" placeholder="••••••••" class="w-full rounded-xl border border-slate-200 pl-3 pr-10 py-2 text-xs font-semibold">
                                <button type="button" onclick="togglePasswordVisibility('currentPassword', 'currentPasswordIcon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                    <svg id="currentPasswordIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">New Password</label>
                            <div class="relative">
                                <input type="password" name="new_password" id="newPassword" oninput="validatePasswordStrength()" placeholder="••••••••" class="w-full rounded-xl border border-slate-200 pl-3 pr-10 py-2 text-xs font-semibold">
                                <button type="button" onclick="togglePasswordVisibility('newPassword', 'newPasswordIcon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                    <svg id="newPasswordIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                            
                            <!-- PASSWORD STRENGTH METER & CHECKLIST -->
                            <div id="strengthMeterWrapper" class="hidden mt-2 space-y-1.5">
                                <div class="flex items-center justify-between text-[10px] font-bold">
                                    <span class="text-slate-400">Strength:</span>
                                    <span id="strengthText" class="text-slate-600">Weak</span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden flex">
                                    <div id="strengthBar" class="h-full w-0 transition-all duration-300 rounded-full bg-red-500"></div>
                                </div>

                                <ul class="text-[10px] space-y-1 pt-1 text-slate-500 font-medium">
                                    <li id="reqLength" class="flex items-center gap-1.5">
                                        <span class="status-icon text-slate-300">✕</span> At least 8 characters
                                    </li>
                                    <li id="reqUpper" class="flex items-center gap-1.5">
                                        <span class="status-icon text-slate-300">✕</span> One uppercase letter (A-Z)
                                    </li>
                                    <li id="reqNumber" class="flex items-center gap-1.5">
                                        <span class="status-icon text-slate-300">✕</span> One number (0-9)
                                    </li>
                                    <li id="reqSpecial" class="flex items-center gap-1.5">
                                        <span class="status-icon text-slate-300">✕</span> One special character (@$!%*?&)
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Confirm New Password -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-1">Confirm New Password</label>
                            <div class="relative">
                                <input type="password" name="new_password_confirmation" id="confirmPassword" oninput="checkPasswordMatch()" placeholder="••••••••" class="w-full rounded-xl border border-slate-200 pl-3 pr-10 py-2 text-xs font-semibold">
                                <button type="button" onclick="togglePasswordVisibility('confirmPassword', 'confirmPasswordIcon')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 focus:outline-none">
                                    <svg id="confirmPasswordIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </button>
                            </div>
                            <span id="matchFeedback" class="text-[10px] font-bold mt-1 block hidden"></span>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="button" onclick="closeEditProfileModal()" class="flex-1 rounded-xl border border-slate-200 py-2 text-xs font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="flex-1 rounded-xl bg-slate-900 py-2 text-xs font-bold text-white shadow">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: PAY OUTSTANDING BALANCE WITH PAYMENT PROOF PREVIEW -->
    <div id="outstandingPaymentModal" class="fixed inset-0 z-[100] hidden overflow-y-auto" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" onclick="closeOutstandingPaymentModal()"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl p-6 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-3">
                    <h2 class="text-base font-extrabold text-slate-900">Pay Outstanding Balance</h2>
                    <button onclick="closeOutstandingPaymentModal()" class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center font-bold">✕</button>
                </div>

                <form method="POST" action="{{ route('subscriber.payment.submit') }}" enctype="multipart/form-data" class="space-y-3 text-xs">
                    @csrf
                    <div class="rounded-2xl bg-slate-900 p-4 text-white space-y-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">GCash Payment Target</span>
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-[10px] text-slate-400">Account Name</p>
                                <p class="font-extrabold text-white">{{ $settings->gcash_name ?? 'Not configured' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] text-slate-400">GCash Number</p>
                                <p class="font-black text-white text-sm">{{ $settings->gcash_number ?? 'Not configured' }}</p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Amount Due</label>
                        <input type="hidden" name="amount_paid" value="{{ number_format($outstandingBalance, 2, '.', '') }}">
                        <input type="text" value="₱{{ number_format($outstandingBalance, 2) }}" readonly class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 font-black text-slate-800">
                    </div>

                    <div>
                        <label for="reference_number" class="block font-bold text-slate-700 mb-1">GCash Reference No.</label>
                        <input type="text" id="reference_number" name="reference_number" required placeholder="Enter reference number" class="w-full rounded-xl border border-slate-200 px-3 py-2 font-semibold">
                    </div>

                    <!-- PAYMENT PROOF INPUT & REAL-TIME PREVIEW -->
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Proof of Payment</label>
                        <input type="file" id="paymentProofInput" name="payment_screenshot" accept="image/*" required onchange="previewPaymentProof(event)" class="w-full text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:bg-slate-100 file:font-bold">
                        
                        <!-- Preview Box -->
                        <div id="paymentProofPreviewBox" class="mt-2 hidden border border-slate-200 rounded-xl overflow-hidden bg-slate-50 p-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-1">Payment Proof Preview:</span>
                            <div class="max-h-48 overflow-hidden rounded-lg border border-slate-200 bg-slate-100">
                                <img id="paymentProofPreviewImg" src="" alt="Proof Preview" class="w-full h-full object-contain">
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-2">
                        <button type="button" onclick="closeOutstandingPaymentModal()" class="flex-1 rounded-xl border border-slate-200 py-2.5 font-bold text-slate-600">Cancel</button>
                        <button type="submit" class="flex-1 rounded-xl bg-slate-900 py-2.5 font-bold text-white shadow">Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JAVASCRIPT CONTROLLERS & IMAGE PREVIEW HANDLERS -->
    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a10.05 10.05 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>';
            } else {
                input.type = 'password';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
            }
        }

        function validatePasswordStrength() {
            const password = document.getElementById('newPassword').value;
            const meterWrapper = document.getElementById('strengthMeterWrapper');
            const bar = document.getElementById('strengthBar');
            const text = document.getElementById('strengthText');

            if (password.length === 0) {
                meterWrapper.classList.add('hidden');
                checkPasswordMatch();
                return;
            }

            meterWrapper.classList.remove('hidden');

            const hasLength = password.length >= 8;
            const hasUpper = /[A-Z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[@$!%*?&]/.test(password);

            updateRequirementStatus('reqLength', hasLength);
            updateRequirementStatus('reqUpper', hasUpper);
            updateRequirementStatus('reqNumber', hasNumber);
            updateRequirementStatus('reqSpecial', hasSpecial);

            let score = 0;
            if (hasLength) score++;
            if (hasUpper) score++;
            if (hasNumber) score++;
            if (hasSpecial) score++;

            if (score <= 1) {
                bar.style.width = '25%';
                bar.className = 'h-full transition-all duration-300 rounded-full bg-red-500';
                text.innerText = 'Weak';
                text.className = 'text-red-600';
            } else if (score === 2) {
                bar.style.width = '50%';
                bar.className = 'h-full transition-all duration-300 rounded-full bg-amber-500';
                text.innerText = 'Fair';
                text.className = 'text-amber-600';
            } else if (score === 3) {
                bar.style.width = '75%';
                bar.className = 'h-full transition-all duration-300 rounded-full bg-blue-500';
                text.innerText = 'Good';
                text.className = 'text-blue-600';
            } else {
                bar.style.width = '100%';
                bar.className = 'h-full transition-all duration-300 rounded-full bg-emerald-500';
                text.innerText = 'Strong';
                text.className = 'text-emerald-600';
            }

            checkPasswordMatch();
        }

        function updateRequirementStatus(elementId, isValid) {
            const el = document.getElementById(elementId);
            const icon = el.querySelector('.status-icon');

            if (isValid) {
                el.classList.remove('text-slate-500');
                el.classList.add('text-emerald-600', 'font-bold');
                icon.innerText = '✓';
                icon.className = 'status-icon text-emerald-600';
            } else {
                el.classList.remove('text-emerald-600', 'font-bold');
                el.classList.add('text-slate-500');
                icon.innerText = '✕';
                icon.className = 'status-icon text-slate-300';
            }
        }

        function checkPasswordMatch() {
            const password = document.getElementById('newPassword').value;
            const confirm = document.getElementById('confirmPassword').value;
            const feedback = document.getElementById('matchFeedback');

            if (confirm.length === 0) {
                feedback.classList.add('hidden');
                return;
            }

            feedback.classList.remove('hidden');

            if (password === confirm) {
                feedback.innerText = '✓ Passwords match';
                feedback.className = 'text-[10px] font-bold mt-1 block text-emerald-600';
            } else {
                feedback.innerText = '✕ Passwords do not match';
                feedback.className = 'text-[10px] font-bold mt-1 block text-red-600';
            }
        }

        function previewProfileImage(event) {
            const file = event.target.files[0];
            const img = document.getElementById('profilePhotoPreviewImg');
            const placeholder = document.getElementById('profilePhotoPlaceholder');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    img.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        function previewPaymentProof(event) {
            const file = event.target.files[0];
            const box = document.getElementById('paymentProofPreviewBox');
            const img = document.getElementById('paymentProofPreviewImg');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    img.src = e.target.result;
                    box.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                box.classList.add('hidden');
            }
        }

        function openBillingHistoryModal() {
            document.getElementById('billingHistoryModal')?.classList.remove('hidden');
        }
        function closeBillingHistoryModal() {
            document.getElementById('billingHistoryModal')?.classList.add('hidden');
        }

        function openReceiptModal(data) {
            document.getElementById('receiptNo').innerText = data.receipt_no || '#REC-000000';
            document.getElementById('receiptMonth').innerText = data.month || '--';
            document.getElementById('receiptMonthTable').innerText = data.month || '--';
            document.getElementById('receiptAmount').innerText = data.amount || '₱0.00';
            document.getElementById('receiptTotalAmount').innerText = data.amount || '₱0.00';
            document.getElementById('receiptRef').innerText = data.ref || 'N/A';
            document.getElementById('receiptDate').innerText = data.date || 'N/A';

            const proofWrapper = document.getElementById('receiptProofWrapper');
            const proofImg = document.getElementById('receiptProofImg');

            if (data.screenshot) {
                proofImg.src = data.screenshot;
                proofWrapper.classList.remove('hidden');
            } else {
                proofWrapper.classList.add('hidden');
            }

            document.getElementById('receiptModal')?.classList.remove('hidden');
        }
        function closeReceiptModal() {
            document.getElementById('receiptModal')?.classList.add('hidden');
        }

        function openSOAModal() {
            document.getElementById('soaModal')?.classList.remove('hidden');
        }
        function closeSOAModal() {
            document.getElementById('soaModal')?.classList.add('hidden');
        }

        function openEditProfileModal() {
            document.getElementById('editProfileModal')?.classList.remove('hidden');
        }
        function closeEditProfileModal() {
            document.getElementById('editProfileModal')?.classList.add('hidden');
        }

        function openOutstandingPaymentModal() {
            document.getElementById('outstandingPaymentModal')?.classList.remove('hidden');
        }
        function closeOutstandingPaymentModal() {
            document.getElementById('outstandingPaymentModal')?.classList.add('hidden');
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeReceiptModal();
                closeBillingHistoryModal();
                closeSOAModal();
                closeEditProfileModal();
                closeOutstandingPaymentModal();
            }
        });
    </script>
</body>
</html>