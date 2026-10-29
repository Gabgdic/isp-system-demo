@extends('layouts.app')

@section('title', 'Billing')

@section('content')

<div id="page-content" class="space-y-4 max-w-7xl mx-auto opacity-0 translate-y-2 transition-all duration-500 ease-out">

    <!-- Header & Alerts -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Billing</h1>
            <p class="text-sm text-slate-500 mt-0.5">
                Review subscriber bills, verify payment submissions, and manage subscription plans.
            </p>
        </div>

        <!-- Inline Alerts -->
        @if(session('success'))
            <div class="px-4 py-2 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm font-medium">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="px-4 py-2 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="px-4 py-2 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium">
                <span class="font-bold">Errors:</span> {{ implode(', ', $errors->all()) }}
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 h-[300px]">

        <!-- PAYMENT VERIFICATION -->
        <div class="lg:col-span-7 bg-slate-50 border border-slate-300 rounded-2xl shadow-sm flex flex-col h-full overflow-hidden">

            <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between gap-3 bg-slate-50/50 flex-shrink-0">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Payment Verification</h2>
                    <p class="text-xs text-slate-500">
                        Review submitted payment screenshots before approving.
                    </p>
                </div>

                <span class="inline-flex items-center justify-center bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full px-3 py-1 text-xs font-bold whitespace-nowrap">
                    {{ $pendingPayments->count() }} Pending
                </span>
            </div>

            <!-- Scrollable Content Area -->
            <div class="flex-1 overflow-y-auto">
                @if($pendingPayments->count())
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 sticky top-0 z-10 border-b border-slate-100">
                            <tr>
                                <th class="px-4 py-2.5 text-left font-semibold">Subscriber</th>
                                <th class="px-4 py-2.5 text-left font-semibold">Bills Covered</th>
                                <th class="px-4 py-2.5 text-left font-semibold">Amount</th>
                                <th class="px-4 py-2.5 text-left font-semibold">Reference</th>
                                <th class="px-4 py-2.5 text-right font-semibold">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @foreach($pendingPayments as $payment)
                                <tr class="hover:bg-slate-100 transition">
                                    <td class="px-4 py-3">
                                        <div class="font-semibold text-slate-900">
                                            {{ $payment->subscriber->full_name ?? 'N/A' }}
                                        </div>
                                        <div class="text-xs text-slate-500">
                                            {{ $payment->subscriber->username ?? 'N/A' }}
                                        </div>
                                    </td>

                                    <td class="px-4 py-3 text-slate-700">
                                        @if($payment->billingAllocations->count())
                                            <div class="space-y-0.5">
                                                @foreach($payment->billingAllocations as $allocation)
                                                    @if($allocation->billingRecord)
                                                        <div class="text-xs font-medium">
                                                            {{ \Carbon\Carbon::create(
                                                                $allocation->billingRecord->billing_year,
                                                                $allocation->billingRecord->billing_month,
                                                                1
                                                            )->format('M Y') }}
                                                        </div>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @elseif($payment->billingRecord)
                                            <span class="text-xs font-medium">
                                                {{ \Carbon\Carbon::create(
                                                    $payment->billingRecord->billing_year,
                                                    $payment->billingRecord->billing_month,
                                                    1
                                                )->format('M Y') }}
                                            </span>
                                        @else
                                            <span class="text-slate-400 text-xs">No records</span>
                                        @endif
                                    </td>

                                    <td class="px-4 py-3 font-bold text-slate-900 whitespace-nowrap">
                                        ₱{{ number_format($payment->amount_paid, 2) }}
                                    </td>

                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs bg-slate-100 px-2 py-1 rounded-md break-all">
                                            {{ $payment->reference_number }}
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 text-right">
                                        <button
                                            type="button"
                                            onclick="openPaymentModal({{ $payment->id }})"
                                            class="bg-slate-900 hover:bg-slate-800 text-white px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                                        >
                                            Review
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="h-full flex flex-col items-center justify-center p-6 text-center">
                        <div class="w-10 h-10 rounded-full bg-green-50 text-green-600 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-bold text-slate-900">No pending payments</h3>
                        <p class="text-xs text-slate-500 mt-0.5">All submissions are verified.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- SUBSCRIPTION PLANS -->
        <div class="lg:col-span-5 bg-slate-50 border border-slate-300 rounded-2xl shadow-sm flex flex-col h-full overflow-hidden">
            <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between gap-3 bg-slate-50/50 flex-shrink-0">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Subscription Plans</h2>
                    <p class="text-xs text-slate-500">Create & modify plans.</p>
                </div>

                <button onclick="openCreatePlanModal()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-semibold shadow-sm hover:bg-slate-800 transition">
                    <span class="text-base leading-none">+</span> Add Plan
                </button>
            </div>

            <div class="flex-1 overflow-y-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 sticky top-0 z-10 border-b border-slate-100">
                        <tr>
                            <th class="px-4 py-2.5 text-left font-semibold">Plan</th>
                            <th class="px-4 py-2.5 text-left font-semibold">Price</th>
                            <th class="px-4 py-2.5 text-right font-semibold">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($plans as $plan)
                            <tr class="hover:bg-slate-100 transition">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800 leading-tight">{{ $plan->plan_name }}</p>
                                    <p class="text-xs text-slate-400 truncate max-w-[120px] mt-0.5">{{ $plan->description ?? 'N/A' }}</p>
                                </td>

                                <td class="px-4 py-3 font-bold text-slate-800 whitespace-nowrap">
                                    ₱{{ number_format($plan->price, 2) }}
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                            onclick="openEditPlanModal(this)"
                                            data-id="{{ $plan->id }}"
                                            data-plan-name="{{ $plan->plan_name }}"
                                            data-price="{{ $plan->price }}"
                                            data-description="{{ $plan->description }}"
                                            class="p-1 rounded-lg text-slate-400 hover:text-slate-900 hover:bg-slate-100 transition"
                                            title="Edit">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>

                                        <form action="{{ route('billing.plans.delete', $plan->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Delete this plan?')"
                                            class="inline">
                                            @csrf
                                            <button type="submit" class="p-1 rounded-lg text-slate-400 hover:text-red-600 hover:bg-slate-100 transition" title="Delete">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="px-4 py-8 text-center text-slate-400 text-sm">
                                    No plans found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- BILLING RECORDS (Bottom Container using AJAX Pagination) -->
    <div id="billing-records-container" class="bg-slate-50 border border-slate-300 rounded-2xl shadow-sm flex flex-col h-[360px] overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-slate-50/50 flex-shrink-0">
            <div>
                <h2 class="text-base font-bold text-slate-900">Billing Records</h2>
                <p class="text-xs text-slate-500">Track subscriber invoices, payment status, and statements.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                <button
                    type="button"
                    onclick="openManualPaymentModal()"
                    class="inline-flex items-center justify-center px-3 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold whitespace-nowrap"
                >
                    Record Manual Payment
                </button>
                <div class="relative w-full sm:w-72">
                    <input
                        type="text"
                        id="billingRecordsSearch"
                        value="{{ $search ?? '' }}"
                        placeholder="Search subscriber, status, or period..."
                        class="form-input pr-10"
                        autocomplete="off"
                    >
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.35-4.35m2.35-5.65a8 8 0 1 1-16 0 8 8 0 0 1 16 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div id="billing-records-content" class="flex flex-col flex-1 min-h-0">
            @include('admin.partials.billing_records_table')
        </div>
    </div>

</div>

<!-- MANUAL PAYMENT MODAL -->
<div id="manualPaymentModal" class="hidden fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white w-full max-w-2xl rounded-2xl shadow-2xl border border-slate-200 flex flex-col max-h-[90vh]">
        <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-slate-900">Record Manual Payments</h3>
                <p class="text-xs text-slate-500 mt-1">Select subscribers to record their full outstanding balances.</p>
            </div>
            <button type="button" onclick="closeManualPaymentModal()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500">✕</button>
        </div>

        <form method="POST" action="{{ route('billing.manual-payments.store') }}" class="flex flex-col min-h-0">
            @csrf
            <div class="px-5 py-3 flex items-center justify-between border-b border-slate-100">
                <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600">
                    <input id="manualPaymentSelectAll" type="checkbox" class="rounded border-slate-300" onchange="toggleAllManualPaymentSubscribers(this)">
                    Select all
                </label>
                <span id="manualPaymentSelectedTotal" class="text-sm font-bold text-slate-900">Selected total: ₱0.00</span>
            </div>

            <div class="p-5 overflow-y-auto space-y-2">
                @forelse($manualPaymentSubscribers as $manualPaymentSubscriber)
                    @php($subscriberOutstanding = $manualPaymentSubscriber->billingRecords->sum(fn ($bill) => (float) $bill->amount))
                    <label class="flex items-center justify-between gap-4 p-3 rounded-xl border border-slate-200 hover:bg-slate-50 cursor-pointer">
                        <span class="flex items-center gap-3 min-w-0">
                            <input
                                type="checkbox"
                                name="subscriber_ids[]"
                                value="{{ $manualPaymentSubscriber->id }}"
                                data-outstanding="{{ number_format($subscriberOutstanding, 2, '.', '') }}"
                                class="manual-payment-subscriber rounded border-slate-300"
                                onchange="updateManualPaymentTotal()"
                            >
                            <span class="min-w-0">
                                <span class="block text-sm font-semibold text-slate-900 truncate">{{ $manualPaymentSubscriber->full_name }}</span>
                                <span class="block text-xs text-slate-500">{{ $manualPaymentSubscriber->username }} · {{ $manualPaymentSubscriber->billingRecords->count() }} outstanding {{ \Illuminate\Support\Str::plural('bill', $manualPaymentSubscriber->billingRecords->count()) }}</span>
                            </span>
                        </span>
                        <span class="font-bold text-slate-900 whitespace-nowrap">₱{{ number_format($subscriberOutstanding, 2) }}</span>
                    </label>
                @empty
                    <p class="py-8 text-center text-sm text-slate-500">No subscribers have unpaid or overdue bills.</p>
                @endforelse
            </div>

            <div class="px-5 py-4 border-t border-slate-200 flex justify-end gap-2">
                <button type="button" onclick="closeManualPaymentModal()" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold">Cancel</button>
                <button id="submitManualPayments" type="submit" disabled onclick="return confirmManualPayments()" class="px-4 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold">Record Selected</button>
            </div>
        </form>
    </div>
</div>

<!-- STATEMENT OF ACCOUNT (SOA) PREVIEW MODAL -->
<div id="soaModal" class="hidden fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm items-center justify-center p-4">
    <div class="relative bg-white w-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden my-8 border border-slate-200 flex flex-col max-h-[92vh]">
        <!-- Action Header -->
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 no-print">
            <h3 class="text-sm font-bold text-slate-900">Statement of Account Preview</h3>
            <div class="flex items-center gap-2">
                <button type="button" onclick="printPreviewFrame('soa-preview-frame')" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 px-3 py-1.5 rounded-lg shadow-sm transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </button>
                <button type="button" onclick="closeSoaModal()" class="w-7 h-7 rounded-lg bg-slate-200/60 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs">
                    ✕
                </button>
            </div>
        </div>

        <div class="flex-1 min-h-0 bg-slate-100 p-2 sm:p-4">
            <iframe id="soa-preview-frame" title="Printable Statement of Account" class="h-[75vh] w-full rounded-lg border border-slate-200 bg-white"></iframe>
        </div>
    </div>
</div>

<!-- OFFICIAL RECEIPT PREVIEW MODAL -->
<div id="receiptModal" class="hidden fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm items-center justify-center p-4">
    <div class="relative bg-white w-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden my-8 border border-slate-200 flex flex-col max-h-[92vh]">
        <!-- Action Header -->
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 no-print">
            <h3 class="text-sm font-bold text-slate-900">Official Receipt Preview</h3>
            <div class="flex items-center gap-2">
                <button type="button" onclick="printPreviewFrame('receipt-preview-frame')" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 px-3 py-1.5 rounded-lg shadow-sm transition">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </button>
                <button type="button" onclick="closeReceiptModal()" class="w-7 h-7 rounded-lg bg-slate-200/60 hover:bg-slate-200 text-slate-600 flex items-center justify-center text-xs">
                    ✕
                </button>
            </div>
        </div>

        <div class="flex-1 min-h-0 bg-slate-100 p-2 sm:p-4">
            <iframe id="receipt-preview-frame" title="Printable Official Receipt" class="h-[75vh] w-full rounded-lg border border-slate-200 bg-white"></iframe>
        </div>
    </div>
</div>

<!-- PAYMENT REVIEW MODALS -->
@foreach($pendingPayments as $payment)
    <div id="payment-modal-{{ $payment->id }}" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" onclick="closePaymentModal({{ $payment->id }})"></div>
        <div class="relative min-h-screen flex items-center justify-center p-4">
            <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden my-8">
                <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Review Payment</h3>
                        <p class="text-xs text-slate-500">Verify details before approving.</p>
                    </div>
                    <button type="button" onclick="closePaymentModal({{ $payment->id }})" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center text-sm">
                        ✕
                    </button>
                </div>

                <div class="p-5 space-y-4 text-sm max-h-[80vh] overflow-y-auto">
                    <div class="bg-slate-50 rounded-xl p-3">
                        <p class="text-xs text-slate-400 font-semibold uppercase">Subscriber</p>
                        <p class="font-bold text-slate-900 mt-0.5 text-base">{{ $payment->subscriber->full_name ?? 'N/A' }}</p>
                        <p class="text-xs text-slate-500">{{ $payment->subscriber->username ?? 'N/A' }}</p>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-xl p-3">
                        <p class="text-xs text-slate-500 font-semibold uppercase mb-1.5">Bills Covered</p>
                        @if($payment->billingAllocations->count())
                            @foreach($payment->billingAllocations as $allocation)
                                @if($allocation->billingRecord)
                                    <div class="flex items-center justify-between py-1">
                                        <span class="font-semibold text-slate-800">
                                            {{ \Carbon\Carbon::create($allocation->billingRecord->billing_year, $allocation->billingRecord->billing_month, 1)->format('F Y') }}
                                        </span>
                                        <span class="font-bold text-slate-900">₱{{ number_format($allocation->amount_applied, 2) }}</span>
                                    </div>
                                @endif
                            @endforeach
                        @elseif($payment->billingRecord)
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-slate-800">
                                    {{ \Carbon\Carbon::create($payment->billingRecord->billing_year, $payment->billingRecord->billing_month, 1)->format('F Y') }}
                                </span>
                                <span class="font-bold text-slate-900">₱{{ number_format($payment->billingRecord->amount, 2) }}</span>
                            </div>
                        @else
                            <span class="text-slate-400 text-xs">No records found.</span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-slate-400 uppercase">Amount Paid</p>
                            <p class="font-bold text-slate-900 text-base">₱{{ number_format($payment->amount_paid, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 uppercase">Reference Number</p>
                            <p class="font-mono font-bold text-slate-900 break-all">{{ $payment->reference_number }}</p>
                        </div>
                    </div>

                    <div>
                        <p class="font-bold text-slate-900 mb-1.5">Screenshot</p>
                        <div class="bg-slate-100 rounded-xl p-2">
                            <img src="{{ asset('storage/' . $payment->payment_screenshot) }}" alt="Screenshot" class="max-h-[300px] w-full object-contain rounded-lg">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <form method="POST" action="{{ route('billing.payment.approve', $payment->id) }}">
                            @csrf
                            <button type="submit" onclick="return confirm('Approve this payment?')" class="w-full bg-green-600 hover:bg-green-700 text-white py-2.5 rounded-xl font-semibold transition">
                                Approve
                            </button>
                        </form>
                        <button type="button" onclick="showRejectForm({{ $payment->id }})" class="w-full bg-red-600 hover:bg-red-700 text-white py-2.5 rounded-xl font-semibold transition">
                            Reject
                        </button>
                    </div>

                    <div id="reject-form-{{ $payment->id }}" class="hidden border-t border-slate-200 pt-3">
                        <form method="POST" action="{{ route('billing.payment.reject', $payment->id) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Rejection Note</label>
                                <textarea name="admin_note" rows="2" placeholder="Reason..." class="w-full border border-slate-300 rounded-xl p-2.5 text-sm focus:ring-2 focus:ring-slate-400 outline-none"></textarea>
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded-xl font-semibold">Confirm</button>
                                <button type="button" onclick="hideRejectForm({{ $payment->id }})" class="px-4 py-2 bg-slate-100 text-slate-700 rounded-xl font-semibold">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endforeach

<!-- CREATE PLAN MODAL -->
<div id="createPlanModal" class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-5 border border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900">Add Subscription Plan</h2>
            <button onclick="closeCreatePlanModal()" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200">✕</button>
        </div>
        <form action="{{ route('billing.plans.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="form-label">Plan Name <span class="text-red-500">*</span></label>
                <input type="text" name="plan_name" class="form-input" placeholder="Basic Plan" required>
            </div>
            <div>
                <label class="form-label">Price <span class="text-red-500">*</span></label>
                <input type="number" name="price" step="0.01" min="0" class="form-input" placeholder="700.00" required>
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="2" class="form-input resize-none" placeholder="Optional description"></textarea>
            </div>
            <button class="w-full py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition">Save Plan</button>
        </form>
    </div>
</div>

<!-- EDIT PLAN MODAL -->
<div id="editPlanModal" class="hidden fixed inset-0 z-[100] bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-5 border border-slate-200">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-slate-900">Edit Subscription Plan</h2>
            <button onclick="closeEditPlanModal()" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200">✕</button>
        </div>
        <form id="editPlanForm" method="POST" class="space-y-3">
            @csrf
            @method('PUT')
            <div>
                <label class="form-label">Plan Name <span class="text-red-500">*</span></label>
                <input id="edit_plan_name" type="text" name="plan_name" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Price <span class="text-red-500">*</span></label>
                <input id="edit_price" type="number" name="price" step="0.01" min="0" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Description</label>
                <textarea id="edit_description" name="description" rows="2" class="form-input resize-none"></textarea>
            </div>
            <button class="w-full py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition">Save Changes</button>
        </form>
    </div>
</div>

<style>
.form-label {
    display: block;
    margin-bottom: 3px;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
}

.form-input {
    width: 100%;
    padding: 8px 12px;
    border-radius: 10px;
    border: 1px solid #cbd5e1;
    color: #334155;
    outline: none;
    transition: all 0.2s ease;
    background: white;
    font-size: 13px;
}

.form-input:focus {
    border-color: #0f172a;
    box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.08);
}

</style>

<script>
// Trigger page load fade-in animation
document.addEventListener('DOMContentLoaded', function() {
    const pageContent = document.getElementById('page-content');
    if (pageContent) {
        requestAnimationFrame(() => {
            pageContent.classList.remove('opacity-0', 'translate-y-2');
            pageContent.classList.add('opacity-100', 'translate-y-0');
        });
    }
});

document.addEventListener('click', function(e) {
    const link = e.target.closest('#billing-records-container a');
    if (link && link.href) {
        e.preventDefault();
        fetchBillingRecords(link.href);
    }
});

let billingSearchTimeout;
document.addEventListener('input', function(e) {
    const searchInput = e.target.closest('#billingRecordsSearch');
    if (!searchInput) return;

    clearTimeout(billingSearchTimeout);
    billingSearchTimeout = setTimeout(() => {
        const url = new URL(@json(route('billing')), window.location.origin);
        const search = searchInput.value.trim();

        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }

        fetchBillingRecords(url.toString());
    }, 300);
});

function fetchBillingRecords(url) {
    const container = document.getElementById('billing-records-content');
    container.innerHTML = `
        <div class="flex flex-col items-center justify-center h-full text-slate-400 text-xs py-12 gap-2">
            <svg class="animate-spin h-6 w-6 text-slate-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span>Loading records...</span>
        </div>
    `;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        container.innerHTML = html;
    })
    .catch(error => {
        console.error('Error updating billing records:', error);
        container.innerHTML = `<p class="text-xs text-red-500 text-center py-6">Failed to load billing records.</p>`;
    });
}

function openPaymentModal(id) {
    const modal = document.getElementById('payment-modal-' + id);
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
}

function closePaymentModal(id) {
    const modal = document.getElementById('payment-modal-' + id);
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

function openManualPaymentModal() {
    const modal = document.getElementById('manualPaymentModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeManualPaymentModal() {
    const modal = document.getElementById('manualPaymentModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function toggleAllManualPaymentSubscribers(selectAll) {
    document.querySelectorAll('.manual-payment-subscriber').forEach((checkbox) => {
        checkbox.checked = selectAll.checked;
    });
    updateManualPaymentTotal();
}

function updateManualPaymentTotal() {
    const checkboxes = Array.from(document.querySelectorAll('.manual-payment-subscriber'));
    const selected = checkboxes.filter((checkbox) => checkbox.checked);
    const total = selected.reduce((sum, checkbox) => sum + Number(checkbox.dataset.outstanding || 0), 0);
    const totalElement = document.getElementById('manualPaymentSelectedTotal');
    const submitButton = document.getElementById('submitManualPayments');
    const selectAll = document.getElementById('manualPaymentSelectAll');

    totalElement.textContent = `Selected total: ₱${total.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    submitButton.disabled = selected.length === 0;
    selectAll.checked = checkboxes.length > 0 && selected.length === checkboxes.length;
    selectAll.indeterminate = selected.length > 0 && selected.length < checkboxes.length;
}

function confirmManualPayments() {
    const selectedCount = document.querySelectorAll('.manual-payment-subscriber:checked').length;
    return selectedCount > 0 && confirm(`Record full outstanding balances for ${selectedCount} selected subscriber(s)?`);
}

function showRejectForm(id) {
    const form = document.getElementById('reject-form-' + id);
    if (form) form.classList.remove('hidden');
}

function hideRejectForm(id) {
    const form = document.getElementById('reject-form-' + id);
    if (form) form.classList.add('hidden');
}

function openCreatePlanModal() {
    const modal = document.getElementById('createPlanModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeCreatePlanModal() {
    const modal = document.getElementById('createPlanModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openEditPlanModal(button) {
    const modal = document.getElementById('editPlanModal');
    const form = document.getElementById('editPlanForm');

    form.action = '/billing/plans/update/' + button.dataset.id;
    document.getElementById('edit_plan_name').value = button.dataset.planName || '';
    document.getElementById('edit_price').value = button.dataset.price || '';
    document.getElementById('edit_description').value = button.dataset.description || '';

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditPlanModal() {
    const modal = document.getElementById('editPlanModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openSoaModal(subscriberId) {
    const modal = document.getElementById('soaModal');
    document.getElementById('soa-preview-frame').src = `/billing/soa/preview/${subscriberId}`;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeSoaModal() {
    const modal = document.getElementById('soaModal');
    document.getElementById('soa-preview-frame').src = 'about:blank';
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function openReceiptModal(subscriberId, recordId) {
    const modal = document.getElementById('receiptModal');
    document.getElementById('receipt-preview-frame').src = `/billing/receipt/preview/${recordId}`;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function closeReceiptModal() {
    const modal = document.getElementById('receiptModal');
    document.getElementById('receipt-preview-frame').src = 'about:blank';
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function printPreviewFrame(frameId) {
    const previewFrame = document.getElementById(frameId);
    if (!previewFrame.contentWindow || previewFrame.contentDocument?.readyState !== 'complete') {
        alert('The preview is still loading. Please try again.');
        return;
    }

    previewFrame.contentWindow.focus();
    previewFrame.contentWindow.print();
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('[id^="payment-modal-"]').forEach(modal => modal.classList.add('hidden'));
        closeCreatePlanModal();
        closeEditPlanModal();
        closeSoaModal();
        document.body.classList.remove('overflow-hidden');
    }
});
</script>

@endsection