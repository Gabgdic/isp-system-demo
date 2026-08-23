<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Subscriber Portal - {{ $settings->system_name ?? 'Client Area' }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-50 font-sans antialiased text-slate-900">

    <!-- ====================================================== -->
    <!-- NAVBAR -->
    <!-- ====================================================== -->

    <nav class="bg-slate-900 text-white px-4 py-3 sm:px-6 sm:py-4 sticky top-0 z-50 flex justify-between items-center shadow-md">

        <div class="flex items-center gap-2.5 min-w-0">

            @if($settings && $settings->system_logo)

                <div class="bg-white p-1 rounded-lg flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 shadow-sm border border-slate-700/50 flex-shrink-0">

                    <img
                        src="{{ asset('storage/' . $settings->system_logo) }}"
                        class="h-full w-full object-contain"
                        alt="Logo"
                    >

                </div>

            @endif

            <span class="font-bold text-base sm:text-lg tracking-wide truncate max-w-[180px] sm:max-w-none">
                {{ $settings->system_name ?? 'Client Area' }}
            </span>

        </div>


        <form
            method="POST"
            action="{{ route('subscriber.logout') }}"
            class="flex-shrink-0"
        >

            @csrf

            <button
                type="submit"
                class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 sm:px-4 sm:py-2 rounded-xl text-xs sm:text-sm font-semibold transition shadow-md flex items-center gap-1.5"
            >

                <svg
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
                    />
                </svg>

                <span class="hidden xs:inline">
                    Sign Out
                </span>

            </button>

        </form>

    </nav>


    <!-- ====================================================== -->
    <!-- MAIN -->
    <!-- ====================================================== -->

    <main class="max-w-md mx-auto px-4 py-6 sm:max-w-5xl sm:py-10">


        <!-- ================================================== -->
        <!-- ALERTS -->
        <!-- ================================================== -->

        @if(session('success'))

            <div class="mb-5 bg-green-50 border border-green-200 text-green-700 rounded-2xl p-4 text-sm font-medium">

                {{ session('success') }}

            </div>

        @endif


        @if(session('error'))

            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-2xl p-4 text-sm font-medium">

                {{ session('error') }}

            </div>

        @endif


        @if($errors->any())

            <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-2xl p-4 text-sm">

                <p class="font-bold mb-2">
                    Please check the following:
                </p>

                <ul class="list-disc list-inside space-y-1">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <!-- ================================================== -->
        <!-- PROFILE -->
        <!-- ================================================== -->

        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm mb-5 flex flex-col items-center text-center sm:flex-row sm:text-left sm:p-6 gap-4 sm:gap-6">


            <!-- Profile Photo -->

            <div class="w-20 h-20 rounded-2xl bg-slate-800 border border-slate-200 overflow-hidden flex-shrink-0 flex items-center justify-center font-bold text-2xl text-white shadow-inner">

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


            <!-- User Info -->

            <div class="flex-grow min-w-0 w-full">

                <div class="flex flex-col items-center sm:items-start gap-1">

                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-800 tracking-tight truncate w-full">
                        {{ $subscriber->full_name }}
                    </h1>

                    <p class="text-slate-500 text-xs sm:text-sm">

                        Username:

                        <span class="font-semibold text-slate-700">
                            {{ $subscriber->username }}
                        </span>

                    </p>

                </div>


                <!-- Status -->

                <div class="mt-3 sm:mt-2">

                    @if($subscriber->status === 'active')

                        <span class="inline-flex bg-green-50 text-green-700 font-bold px-3 py-1 rounded-full text-[10px] uppercase tracking-wider border border-green-200">
                            Active Connection
                        </span>

                    @elseif($subscriber->status === 'inactive')

                        <span class="inline-flex bg-amber-50 text-amber-700 font-bold px-3 py-1 rounded-full text-[10px] uppercase tracking-wider border border-amber-200">
                            Inactive
                        </span>

                    @else

                        <span class="inline-flex bg-red-50 text-red-700 font-bold px-3 py-1 rounded-full text-[10px] uppercase tracking-wider border border-red-200">
                            Disconnected
                        </span>

                    @endif

                </div>

            </div>

        </div>


        <!-- ================================================== -->
        <!-- SUBSCRIPTION + INSTALLATION -->
        <!-- ================================================== -->

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">


            <!-- Subscription -->

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">
                    Subscription Package
                </h2>


                <div class="space-y-4">

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

                        <span class="text-2xl font-black text-slate-900 tracking-tight">
                            ₱{{ number_format($subscriber->monthly_fee, 2) }}
                        </span>

                    </div>

                </div>

            </div>


            <!-- Installation -->

            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">

                <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4">
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


        <!-- ================================================== -->
        <!-- OUTSTANDING BALANCE -->
        <!-- ================================================== -->

        @php

            $outstandingBills = $billingRecords->filter(function ($bill) {
                return in_array(strtolower($bill->status), ['unpaid', 'overdue']);
            });

            $pendingBills = $billingRecords->filter(function ($bill) {
                return strtolower($bill->status) === 'pending';
            });

            $paidBills = $billingRecords->filter(function ($bill) {
                return strtolower($bill->status) === 'paid';
            });

            $outstandingBalance = $outstandingBills->sum(function ($bill) {
                return (float) $bill->amount;
            });

        @endphp


        <div class="mt-5 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">


            <div class="px-5 py-5 border-b border-slate-100">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                    <div>

                        <h2 class="text-sm font-bold text-slate-400 uppercase tracking-wider">
                            Outstanding Balance
                        </h2>

                        <p class="text-3xl sm:text-4xl font-black text-slate-900 tracking-tight mt-1">
                            ₱{{ number_format($outstandingBalance, 2) }}
                        </p>

                    </div>


                    @if($outstandingBalance > 0)

                        <span class="inline-flex items-center justify-center gap-1.5 bg-red-50 text-red-700 border border-red-200 px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider">

                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full"></span>

                            {{ $outstandingBills->count() }}
                            {{ $outstandingBills->count() === 1 ? 'Month' : 'Months' }}
                            Outstanding

                        </span>

                    @elseif($pendingBills->count())

                        <span class="inline-flex items-center justify-center gap-1.5 bg-amber-50 text-amber-700 border border-amber-200 px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider">

                            <span class="w-1.5 h-1.5 bg-amber-500 rounded-full"></span>

                            Payment Under Review

                        </span>

                    @else

                        <span class="inline-flex items-center justify-center gap-1.5 bg-green-50 text-green-700 border border-green-200 px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider">

                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>

                            Account Up to Date

                        </span>

                    @endif

                </div>


                @if($outstandingBalance > 0)

                    <p class="text-xs text-slate-500 mt-3 leading-relaxed">

                        Your outstanding balance includes all unpaid billing months.
                        Each payment is applied to a specific billing month.

                    </p>

                @elseif($pendingBills->count())

                    <p class="text-xs text-slate-500 mt-3 leading-relaxed">

                        You currently have payment submissions waiting for administrator verification.

                    </p>

                @else

                    <p class="text-xs text-slate-500 mt-3 leading-relaxed">

                        You have no outstanding monthly bills at this time.

                    </p>

                @endif

            </div>


            <!-- GCash Information -->

            @if($outstandingBalance > 0)

                <div class="p-5">

                    <div class="bg-slate-900 rounded-2xl p-5 text-white">

                        <div class="flex items-start justify-between gap-4">

                            <div>

                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                    GCash Payment
                                </p>

                                <p class="text-sm text-slate-300 mt-1">
                                    Send your payment using the account below.
                                </p>

                            </div>

                            <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center flex-shrink-0">

                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 8c-2.21 0-4 1.343-4 3s1.79 3 4 3 4-1.343 4-3-1.79-3-4-3Zm0 0V5m0 9v5m-7-7a7 7 0 1 0 14 0"
                                    />
                                </svg>

                            </div>

                        </div>


                        <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <div class="bg-white/5 rounded-xl p-3">

                                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                                    Account Name
                                </p>

                                <p class="text-sm font-extrabold mt-1 break-words">
                                    {{ $settings->gcash_name ?? 'Not configured' }}
                                </p>

                            </div>


                            <div class="bg-white/5 rounded-xl p-3">

                                <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                                    GCash Number
                                </p>

                                <p class="text-sm font-extrabold mt-1">
                                    {{ $settings->gcash_number ?? 'Not configured' }}
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            @endif

        </div>


        <!-- ================================================== -->
        <!-- BILLING HISTORY -->
        <!-- ================================================== -->

        <div class="mt-5 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">


            <!-- Header -->

            <div class="px-5 py-5 border-b border-slate-100">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">

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


            <!-- Bills -->

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

                        $hasPendingSubmission = $bill->paymentSubmissions
                            ->where('status', 'pending')
                            ->isNotEmpty();

                    @endphp


                    <div class="p-5">


                        <!-- Bill Top -->

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">


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

                                        Due
                                        {{ \Carbon\Carbon::parse($bill->due_date)->format('M d, Y') }}

                                    </p>

                                @endif

                            </div>


                            <div class="text-left sm:text-right">

                                <p class="text-xs text-slate-400 font-medium">
                                    Amount
                                </p>

                                <p class="text-xl font-black text-slate-900">
                                    ₱{{ number_format($billAmount, 2) }}
                                </p>

                            </div>

                        </div>


                        <!-- Bill Actions -->

                        @if($billStatus === 'unpaid' || $billStatus === 'overdue')

                            <div class="mt-4">

                                <button
                                    type="button"
                                    onclick="openPaymentModal({{ $bill->id }})"
                                    class="w-full bg-slate-900 hover:bg-slate-800 text-white py-3.5 px-5 rounded-xl font-bold text-sm transition shadow-sm flex items-center justify-center gap-2"
                                >

                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke="currentColor"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 6v12m-4-3 4 3 4-3M6 4h12"
                                        />
                                    </svg>

                                    Pay {{ $billingMonth }}

                                </button>

                            </div>


                        @elseif($billStatus === 'pending')

                            <div class="mt-4 bg-amber-50 border border-amber-200 rounded-xl p-4">

                                <div class="flex gap-3">

                                    <div class="flex-shrink-0">

                                        <div class="w-9 h-9 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center">

                                            <svg
                                                class="w-5 h-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke="currentColor"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4l2 2m6-2a8 8 0 1 1-16 0 8 8 0 0 1 16 0Z"
                                                />
                                            </svg>

                                        </div>

                                    </div>


                                    <div>

                                        <p class="font-bold text-amber-800 text-sm">
                                            Payment Under Review
                                        </p>

                                        <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                                            Your payment for {{ $billingMonth }} has been submitted and is waiting for administrator verification.
                                        </p>

                                    </div>

                                </div>

                            </div>


                        @elseif($billStatus === 'paid')

                            <div class="mt-4 bg-green-50 border border-green-200 rounded-xl p-4">

                                <div class="flex gap-3">

                                    <div class="flex-shrink-0">

                                        <div class="w-9 h-9 rounded-full bg-green-100 text-green-600 flex items-center justify-center">

                                            <svg
                                                class="w-5 h-5"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke="currentColor"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="m5 12 4 4L19 6"
                                                />
                                            </svg>

                                        </div>

                                    </div>


                                    <div>

                                        <p class="font-bold text-green-800 text-sm">
                                            Payment Verified
                                        </p>


                                        @if($bill->paid_at)

                                            <p class="text-xs text-green-700 mt-1">
                                                Paid on
                                                {{ \Carbon\Carbon::parse($bill->paid_at)->format('M d, Y h:i A') }}
                                            </p>

                                        @else

                                            <p class="text-xs text-green-700 mt-1">
                                                This billing month has been paid.
                                            </p>

                                        @endif

                                    </div>

                                </div>

                            </div>

                        @endif

                    </div>

                @empty

                    <div class="px-5 py-12 text-center">

                        <div class="w-14 h-14 mx-auto rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">

                            <svg
                                class="w-7 h-7"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 14h6m-6-4h6m2-5H7a2 2 0 00-2 2v14l3-3h9a2 2 0 002-2V7a2 2 0 00-2-2Z"
                                />
                            </svg>

                        </div>

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


    <!-- ====================================================== -->
    <!-- PAYMENT MODALS -->
    <!-- ====================================================== -->

    @foreach($billingRecords as $bill)

        @php

            $billStatus = strtolower($bill->status ?? 'unpaid');

        @endphp


        @if(in_array($billStatus, ['unpaid', 'overdue']))

            @php

                $billingMonth = \Carbon\Carbon::create(
                    $bill->billing_year,
                    $bill->billing_month,
                    1
                )->format('F Y');

                $billAmount = (float) $bill->amount;

            @endphp


            <div
                id="paymentModal-{{ $bill->id }}"
                class="fixed inset-0 z-[100] hidden"
                aria-labelledby="paymentModalTitle-{{ $bill->id }}"
                role="dialog"
                aria-modal="true"
            >

                <!-- Backdrop -->

                <div
                    class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                    onclick="closePaymentModal({{ $bill->id }})"
                ></div>


                <!-- Wrapper -->

                <div class="relative min-h-screen flex items-end sm:items-center justify-center p-0 sm:p-4">

                    <!-- Modal -->

                    <div class="relative w-full sm:max-w-lg bg-white rounded-t-3xl sm:rounded-3xl shadow-2xl overflow-hidden max-h-[95vh] overflow-y-auto">


                        <!-- Header -->

                        <div class="px-5 py-5 border-b border-slate-100 flex items-center justify-between">

                            <div>

                                <h2
                                    id="paymentModalTitle-{{ $bill->id }}"
                                    class="text-lg font-extrabold text-slate-900"
                                >
                                    Pay {{ $billingMonth }}
                                </h2>

                                <p class="text-xs text-slate-500 mt-1">
                                    Submit your GCash payment for this billing month.
                                </p>

                            </div>


                            <button
                                type="button"
                                onclick="closePaymentModal({{ $bill->id }})"
                                class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition"
                            >

                                <svg
                                    class="w-5 h-5"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
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


                        <!-- Form -->

                        <form
                            method="POST"
                            action="{{ route('subscriber.payment.submit') }}"
                            enctype="multipart/form-data"
                            class="p-5 space-y-5"
                        >

                            @csrf


                            <!-- IMPORTANT: Specific Billing Record -->

                            <input
                                type="hidden"
                                name="billing_record_id"
                                value="{{ $bill->id }}"
                            >


                            <!-- Amount Due -->

                            <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">

                                <div class="flex items-center justify-between gap-4">

                                    <div>

                                        <span class="text-xs text-slate-400 font-medium block">
                                            Amount Due
                                        </span>

                                        <span class="text-2xl font-black text-slate-900">
                                            ₱{{ number_format($billAmount, 2) }}
                                        </span>

                                    </div>


                                    <div class="text-right">

                                        <span class="text-xs text-slate-400 font-medium block">
                                            Billing Period
                                        </span>

                                        <span class="text-sm font-bold text-slate-700">
                                            {{ $billingMonth }}
                                        </span>

                                    </div>

                                </div>

                            </div>


                            <!-- GCash Instructions -->

                            <div class="bg-slate-900 text-white rounded-2xl p-4">

                                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">
                                    Send Payment To
                                </p>


                                <div class="mt-3">

                                    <p class="text-[10px] uppercase tracking-wider text-slate-400 font-bold">
                                        GCash Account Name
                                    </p>

                                    <p class="text-base font-extrabold mt-1">
                                        {{ $settings->gcash_name ?? 'Not configured' }}
                                    </p>

                                </div>


                                <div class="mt-3">

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
                                        ₱{{ number_format($billAmount, 2) }}
                                    </strong>
                                    to this GCash account.

                                    After payment, upload your receipt and enter the reference number below.

                                </p>

                            </div>


                            <!-- Amount Paid -->

                            <div>

                                <label
                                    for="amount_paid_{{ $bill->id }}"
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
                                        id="amount_paid_{{ $bill->id }}"
                                        name="amount_paid"
                                        value="{{ old('amount_paid', number_format($billAmount, 2, '.', '')) }}"
                                        min="{{ number_format($billAmount, 2, '.', '') }}"
                                        step="0.01"
                                        required
                                        class="w-full pl-9 pr-4 py-3 rounded-xl border border-slate-200 bg-white text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-slate-200"
                                        placeholder="0.00"
                                    >

                                </div>


                                <p class="text-xs text-slate-400 mt-1.5">
                                    Enter the amount you actually sent through GCash.
                                </p>

                            </div>


                            <!-- Reference Number -->

                            <div>

                                <label
                                    for="reference_number_{{ $bill->id }}"
                                    class="block text-sm font-bold text-slate-700 mb-2"
                                >
                                    GCash Reference Number
                                </label>


                                <input
                                    type="text"
                                    id="reference_number_{{ $bill->id }}"
                                    name="reference_number"
                                    value="{{ old('reference_number') }}"
                                    required
                                    maxlength="100"
                                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-slate-200"
                                    placeholder="Enter your GCash reference number"
                                >


                                <p class="text-xs text-slate-400 mt-1.5">
                                    You can find the reference number on your GCash payment receipt.
                                </p>

                            </div>


                            <!-- Screenshot -->

                            <div>

                                <label
                                    for="payment_screenshot_{{ $bill->id }}"
                                    class="block text-sm font-bold text-slate-700 mb-2"
                                >
                                    Proof of Payment
                                </label>


                                <label
                                    for="payment_screenshot_{{ $bill->id }}"
                                    class="block border-2 border-dashed border-slate-200 hover:border-slate-400 rounded-2xl p-5 cursor-pointer transition text-center"
                                >

                                    <svg
                                        class="w-8 h-8 mx-auto text-slate-400 mb-2"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke="currentColor"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2 1.586-1.586a2 2 0 012.828 0L20 14m-6-8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2Z"
                                        />
                                    </svg>


                                    <span class="text-sm font-semibold text-slate-700 block">
                                        Choose GCash screenshot
                                    </span>


                                    <span
                                        id="fileName_{{ $bill->id }}"
                                        class="text-xs text-slate-400 block mt-1"
                                    >
                                        JPG, JPEG, PNG, or WEBP
                                    </span>


                                    <input
                                        type="file"
                                        id="payment_screenshot_{{ $bill->id }}"
                                        name="payment_screenshot"
                                        accept="image/jpeg,image/png,image/jpg,image/webp"
                                        required
                                        class="hidden"
                                        onchange="showFileName({{ $bill->id }}, this)"
                                    >

                                </label>

                            </div>


                            <!-- Verification -->

                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">

                                <div class="flex gap-3">

                                    <svg
                                        class="w-5 h-5 text-amber-600 flex-shrink-0"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke="currentColor"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 9v2m0 4h.01M10.29 3.86 1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0Z"
                                        />
                                    </svg>


                                    <div class="text-xs text-amber-800 leading-relaxed">

                                        Your payment will be reviewed by the administrator.

                                        This billing month will only be marked as
                                        <strong>Paid</strong>
                                        after your payment has been approved.

                                    </div>

                                </div>

                            </div>


                            <!-- Actions -->

                            <div class="flex flex-col-reverse sm:flex-row gap-3 pt-1">

                                <button
                                    type="button"
                                    onclick="closePaymentModal({{ $bill->id }})"
                                    class="flex-1 px-5 py-3 rounded-xl border border-slate-200 text-slate-700 font-bold text-sm hover:bg-slate-50 transition"
                                >
                                    Cancel
                                </button>


                                <button
                                    type="submit"
                                    class="flex-1 px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm transition shadow-sm"
                                >
                                    Submit Payment
                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>

        @endif

    @endforeach


    <!-- ====================================================== -->
    <!-- SCRIPTS -->
    <!-- ====================================================== -->

    <script>

        function openPaymentModal(id) {

            const modal = document.getElementById('paymentModal-' + id);

            if (!modal) {
                return;
            }

            modal.classList.remove('hidden');

            document.body.classList.add('overflow-hidden');

        }


        function closePaymentModal(id) {

            const modal = document.getElementById('paymentModal-' + id);

            if (!modal) {
                return;
            }

            modal.classList.add('hidden');

            document.body.classList.remove('overflow-hidden');

        }


        function showFileName(id, input) {

            const fileName = document.getElementById('fileName_' + id);

            if (!fileName) {
                return;
            }

            if (input.files && input.files.length > 0) {

                fileName.textContent = input.files[0].name;

                fileName.classList.remove('text-slate-400');
                fileName.classList.add('text-slate-700');

            } else {

                fileName.textContent = 'JPG, JPEG, PNG, or WEBP';

                fileName.classList.remove('text-slate-700');
                fileName.classList.add('text-slate-400');

            }

        }


        document.addEventListener('keydown', function(event) {

            if (event.key === 'Escape') {

                document
                    .querySelectorAll('[id^="paymentModal-"]')
                    .forEach(function(modal) {

                        modal.classList.add('hidden');

                    });

                document.body.classList.remove('overflow-hidden');

            }

        });

    </script>


    <style>

        @media (max-width: 340px) {

            .xs\:inline {
                display: none !important;
            }

        }

    </style>

</body>
</html>