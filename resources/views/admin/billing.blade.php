@extends('layouts.app')

@section('title', 'Billing')

@section('content')

<div class="space-y-4 max-w-7xl mx-auto animate-fadeIn">

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
        <div class="lg:col-span-7 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col h-full overflow-hidden">

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
                                <tr class="hover:bg-slate-50 transition">
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
        <div class="lg:col-span-5 bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col h-full overflow-hidden">
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
                            <th class="px-4 py-2.5 text-left font-semibold">Status</th>
                            <th class="px-4 py-2.5 text-right font-semibold">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($plans as $plan)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800 leading-tight">{{ $plan->plan_name }}</p>
                                    <p class="text-xs text-slate-400 truncate max-w-[120px] mt-0.5">{{ $plan->description ?? 'N/A' }}</p>
                                </td>

                                <td class="px-4 py-3 font-bold text-slate-800 whitespace-nowrap">
                                    ₱{{ number_format($plan->price, 2) }}
                                </td>

                                <td class="px-4 py-3">
                                    @if($plan->status === 'active')
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-200">
                                            Active
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            Inactive
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-1.5">
                                        <button type="button"
                                            onclick="openEditPlanModal(this)"
                                            data-id="{{ $plan->id }}"
                                            data-plan-name="{{ $plan->plan_name }}"
                                            data-price="{{ $plan->price }}"
                                            data-description="{{ $plan->description }}"
                                            data-status="{{ $plan->status }}"
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
                                <td colspan="4" class="px-4 py-8 text-center text-slate-400 text-sm">
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
    <div id="billing-records-container" class="bg-white border border-slate-200 rounded-2xl shadow-sm flex flex-col h-[360px] overflow-hidden">
        @include('admin.partials.billing_records_table')
    </div>

</div>

<!-- STATEMENT OF ACCOUNT (SOA) PREVIEW MODAL -->
<div id="soaModal" class="hidden fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm items-center justify-center p-4">
    <div class="relative bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden my-8 border border-slate-200 flex flex-col max-h-[90vh] modal-printable-container">
        <!-- Action Header -->
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 no-print">
            <h3 class="text-sm font-bold text-slate-900">Statement of Account Preview</h3>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 px-3 py-1.5 rounded-lg shadow-sm transition">
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

        <!-- AJAX Content Placeholder -->
        <div id="soa-modal-content" class="p-6 overflow-y-auto flex-1">
            <div class="flex items-center justify-center py-12 text-slate-400 text-xs">
                Loading Statement of Account...
            </div>
        </div>
    </div>
</div>

<!-- OFFICIAL RECEIPT PREVIEW MODAL -->
<div id="receiptModal" class="hidden fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm items-center justify-center p-4">
    <div class="relative bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden my-8 border border-slate-200 flex flex-col max-h-[90vh] modal-printable-container">
        <!-- Action Header -->
        <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between bg-slate-50/80 no-print">
            <h3 class="text-sm font-bold text-slate-900">Official Receipt Preview</h3>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-1.5 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 px-3 py-1.5 rounded-lg shadow-sm transition">
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

        <!-- AJAX Content Placeholder -->
        <div id="receipt-modal-content" class="p-6 overflow-y-auto flex-1">
            <div class="flex items-center justify-center py-12 text-slate-400 text-xs">
                Loading Receipt...
            </div>
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
            <div>
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select name="status" class="form-input" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
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
            <div>
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select id="edit_status" name="status" class="form-input" required>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <button class="w-full py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition">Save Changes</button>
        </form>
    </div>
</div>

<style>
.animate-fadeIn {
    animation: fadeIn 0.45s ease-in-out;
}

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

@media print {
    body * {
        visibility: hidden !important;
    }

    #soaModal, #soaModal *,
    #receiptModal, #receiptModal * {
        visibility: visible !important;
    }

    #soaModal, #receiptModal {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        height: auto !important;
        background: white !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    #soaModal .modal-printable-container,
    #receiptModal .modal-printable-container {
        max-width: 100% !important;
        max-height: none !important;
        border: none !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }

    #soaModal .no-print,
    #receiptModal .no-print {
        display: none !important;
    }

    #soa-modal-content,
    #receipt-modal-content {
        padding: 0 !important;
        overflow: visible !important;
    }
}
</style>

<script>
document.addEventListener('click', function(e) {
    const link = e.target.closest('#billing-records-container a');
    if (link && link.href) {
        e.preventDefault();
        fetchBillingRecords(link.href);
    }
});

function fetchBillingRecords(url) {
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('billing-records-container').innerHTML = html;
    })
    .catch(error => console.error('Error updating billing records:', error));
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
    document.getElementById('edit_status').value = button.dataset.status || 'active';

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
    const content = document.getElementById('soa-modal-content');

    content.innerHTML = `
        <div class="flex items-center justify-center py-12 text-slate-400 text-xs">
            Loading Statement of Account...
        </div>
    `;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');

    fetch(`/billing/soa/preview/${subscriberId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.text())
    .then(html => {
        content.innerHTML = html;
    })
    .catch(() => {
        content.innerHTML = `<p class="text-xs text-red-500 text-center py-4">Failed to load Statement of Account.</p>`;
    });
}

function closeSoaModal() {
    const modal = document.getElementById('soaModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function openReceiptModal(subscriberId, recordId) {
    const modal = document.getElementById('receiptModal');
    const content = document.getElementById('receipt-modal-content');

    content.innerHTML = `
        <div class="flex items-center justify-center py-12 text-slate-400 text-xs">
            Loading Receipt...
        </div>
    `;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
    document.body.classList.add('overflow-hidden');

    fetch(`/billing/receipt/preview/${recordId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.text())
    .then(html => {
        content.innerHTML = html;
    })
    .catch(() => {
        content.innerHTML = `<p class="text-xs text-red-500 text-center py-4">Failed to load Receipt.</p>`;
    });
}

function closeReceiptModal() {
    const modal = document.getElementById('receiptModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
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