@extends('layouts.app')

@section('title', 'Billing')

@section('content')

<div class="space-y-8">

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-slate-900">
            Billing
        </h1>
        <p class="text-slate-500 mt-1">
            Review subscriber bills and verify payment submissions.
        </p>
    </div>

    <!-- Payment Verification -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        Payment Verification
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">
                        Review payment screenshots and payment details before approving.
                    </p>
                </div>

                <span class="inline-flex items-center justify-center bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full px-3 py-1 text-xs font-bold">
                    {{ $pendingPayments->count() }} Pending
                </span>
            </div>
        </div>

        @if($pendingPayments->count())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">
                                Subscriber
                            </th>
                            <th class="px-6 py-4 text-left font-semibold">
                                Bills Covered
                            </th>
                            <th class="px-6 py-4 text-left font-semibold">
                                Amount Paid
                            </th>
                            <th class="px-6 py-4 text-left font-semibold">
                                Reference
                            </th>
                            <th class="px-6 py-4 text-left font-semibold">
                                Submitted
                            </th>
                            <th class="px-6 py-4 text-right font-semibold">
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach($pendingPayments as $payment)
                            <tr class="hover:bg-slate-50 transition">
                                <!-- Subscriber -->
                                <td class="px-6 py-5">
                                    <div class="font-semibold text-slate-900">
                                        {{ $payment->subscriber->full_name }}
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1">
                                        {{ $payment->subscriber->username }}
                                    </div>
                                </td>

                                <!-- Bills Covered -->
                                <td class="px-6 py-5 text-slate-700">
                                    @if($payment->billingAllocations->count())
                                        <div class="space-y-1">
                                            @foreach($payment->billingAllocations as $allocation)
                                                @if($allocation->billingRecord)
                                                    <div class="text-sm font-semibold">
                                                        {{ \Carbon\Carbon::create(
                                                            $allocation->billingRecord->billing_year,
                                                            $allocation->billingRecord->billing_month,
                                                            1
                                                        )->format('F Y') }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @elseif($payment->billingRecord)
                                        <span class="text-sm font-semibold">
                                            {{ \Carbon\Carbon::create(
                                                $payment->billingRecord->billing_year,
                                                $payment->billingRecord->billing_month,
                                                1
                                            )->format('F Y') }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">
                                            No billing records
                                        </span>
                                    @endif
                                </td>

                                <!-- Amount Paid -->
                                <td class="px-6 py-5 font-bold text-slate-900">
                                    ₱{{ number_format($payment->amount_paid, 2) }}
                                </td>

                                <!-- Reference -->
                                <td class="px-6 py-5">
                                    <span class="font-mono text-xs bg-slate-100 px-2 py-1 rounded-lg break-all">
                                        {{ $payment->reference_number }}
                                    </span>
                                </td>

                                <!-- Submitted -->
                                <td class="px-6 py-5 text-slate-500">
                                    {{ $payment->created_at->format('M d, Y h:i A') }}
                                </td>

                                <!-- Action -->
                                <td class="px-6 py-5 text-right">
                                    <button
                                        type="button"
                                        onclick="openPaymentModal({{ $payment->id }})"
                                        class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-xs font-semibold transition"
                                    >
                                        Review
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-16 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-green-50 text-green-600 flex items-center justify-center">
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
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                </div>

                <h3 class="mt-4 text-lg font-bold text-slate-900">
                    No pending payments
                </h3>

                <p class="text-sm text-slate-500 mt-1">
                    There are currently no payments waiting for verification.
                </p>
            </div>
        @endif
    </div>

    <!-- Review Modals -->
    @foreach($pendingPayments as $payment)
        <div
            id="payment-modal-{{ $payment->id }}"
            class="hidden fixed inset-0 z-50 overflow-y-auto"
        >
            <div
                class="fixed inset-0 bg-slate-900/60"
                onclick="closePaymentModal({{ $payment->id }})"
            ></div>

            <div class="relative min-h-screen flex items-center justify-center p-4">
                <div class="relative bg-white w-full max-w-2xl rounded-3xl shadow-2xl overflow-hidden">
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-slate-900">
                                Review Payment
                            </h3>
                            <p class="text-sm text-slate-500 mt-1">
                                Verify the payment before approving.
                            </p>
                        </div>

                        <button
                            type="button"
                            onclick="closePaymentModal({{ $payment->id }})"
                            class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center"
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

                    <!-- Modal Body -->
                    <div class="p-6 space-y-6">
                        <!-- Subscriber -->
                        <div class="bg-slate-50 rounded-2xl p-4">
                            <p class="text-xs text-slate-400 font-semibold uppercase tracking-wider">
                                Subscriber
                            </p>
                            <p class="font-bold text-slate-900 mt-1">
                                {{ $payment->subscriber->full_name }}
                            </p>
                            <p class="text-sm text-slate-500">
                                {{ $payment->subscriber->username }}
                            </p>
                        </div>

                        <!-- Bills Covered -->
                        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-4">
                            <p class="text-xs text-slate-500 font-semibold uppercase tracking-wider">
                                Bills Covered
                            </p>

                            <div class="mt-3 space-y-2">
                                @if($payment->billingAllocations->count())
                                    @foreach($payment->billingAllocations as $allocation)
                                        @if($allocation->billingRecord)
                                            <div class="flex items-center justify-between gap-4">
                                                <span class="font-semibold text-slate-800">
                                                    {{ \Carbon\Carbon::create(
                                                        $allocation->billingRecord->billing_year,
                                                        $allocation->billingRecord->billing_month,
                                                        1
                                                    )->format('F Y') }}
                                                </span>

                                                <span class="font-bold text-slate-900">
                                                    ₱{{ number_format(
                                                        $allocation->amount_applied,
                                                        2
                                                    ) }}
                                                </span>
                                            </div>
                                        @endif
                                    @endforeach

                                    <div class="flex items-center justify-between gap-4 pt-3 border-t border-slate-200">
                                        <span class="font-bold text-slate-700">
                                            Total Covered
                                        </span>

                                        <span class="font-black text-slate-900">
                                            ₱{{ number_format(
                                                $payment->billingAllocations->sum(
                                                    'amount_applied'
                                                ),
                                                2
                                            ) }}
                                        </span>
                                    </div>
                                @elseif($payment->billingRecord)
                                    <div class="flex items-center justify-between gap-4">
                                        <span class="font-semibold text-slate-800">
                                            {{ \Carbon\Carbon::create(
                                                $payment->billingRecord->billing_year,
                                                $payment->billingRecord->billing_month,
                                                1
                                            )->format('F Y') }}
                                        </span>

                                        <span class="font-bold text-slate-900">
                                            ₱{{ number_format(
                                                $payment->billingRecord->amount,
                                                2
                                            ) }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-slate-400">
                                        No billing records found.
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-slate-400">
                                    Amount Paid
                                </p>
                                <p class="font-bold text-slate-900 mt-1">
                                    ₱{{ number_format(
                                        $payment->amount_paid,
                                        2
                                    ) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-xs text-slate-400">
                                    Reference Number
                                </p>
                                <p class="font-mono font-bold text-slate-900 mt-1 break-all">
                                    {{ $payment->reference_number }}
                                </p>
                            </div>
                        </div>

                        <!-- Screenshot -->
                        <div>
                            <p class="text-sm font-bold text-slate-900 mb-3">
                                Payment Screenshot
                            </p>

                            <div class="bg-slate-100 rounded-2xl p-3">
                                <img
                                    src="{{ asset(
                                        'storage/' .
                                        $payment->payment_screenshot
                                    ) }}"
                                    alt="Payment Screenshot"
                                    class="max-h-[500px] w-full object-contain rounded-xl"
                                >
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <!-- Approve -->
                            <form
                                method="POST"
                                action="{{ route(
                                    'billing.payment.approve',
                                    $payment->id
                                ) }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    onclick="return confirm(
                                        'Are you sure you want to approve this payment?'
                                    )"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-semibold transition"
                                >
                                    Approve Payment
                                </button>
                            </form>

                            <!-- Reject -->
                            <button
                                type="button"
                                onclick="showRejectForm({{ $payment->id }})"
                                class="w-full bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl font-semibold transition"
                            >
                                Reject Payment
                            </button>
                        </div>

                        <!-- Reject Form -->
                        <div
                            id="reject-form-{{ $payment->id }}"
                            class="hidden border-t border-slate-200 pt-5"
                        >
                            <form
                                method="POST"
                                action="{{ route(
                                    'billing.payment.reject',
                                    $payment->id
                                ) }}"
                                class="space-y-4"
                            >
                                @csrf

                                <div>
                                    <label
                                        class="block text-sm font-semibold text-slate-700 mb-2"
                                    >
                                        Admin Note
                                    </label>

                                    <textarea
                                        name="admin_note"
                                        rows="3"
                                        placeholder="Reason for rejecting this payment..."
                                        class="w-full border border-slate-300 rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-slate-400 focus:border-slate-400 outline-none"
                                    ></textarea>
                                </div>

                                <div class="flex gap-3">
                                    <button
                                        type="submit"
                                        class="flex-1 bg-red-600 hover:bg-red-700 text-white py-3 rounded-xl font-semibold transition"
                                    >
                                        Confirm Rejection
                                    </button>

                                    <button
                                        type="button"
                                        onclick="hideRejectForm({{ $payment->id }})"
                                        class="px-5 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl font-semibold transition"
                                    >
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Billing Records -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200">
            <h2 class="text-lg font-bold text-slate-900">
                Billing Records
            </h2>
            <p class="text-sm text-slate-500 mt-1">
                All subscriber billing records, including unpaid previous months.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">
                            Subscriber
                        </th>
                        <th class="px-6 py-4 text-left font-semibold">
                            Billing Month
                        </th>
                        <th class="px-6 py-4 text-left font-semibold">
                            Amount
                        </th>
                        <th class="px-6 py-4 text-left font-semibold">
                            Due Date
                        </th>
                        <th class="px-6 py-4 text-left font-semibold">
                            Status
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($billingRecords as $bill)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-900">
                                    {{ $bill->subscriber->full_name }}
                                </div>
                                <div class="text-xs text-slate-500">
                                    {{ $bill->subscriber->username }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-slate-700">
                                {{ \Carbon\Carbon::create(
                                    $bill->billing_year,
                                    $bill->billing_month,
                                    1
                                )->format('F Y') }}
                            </td>

                            <td class="px-6 py-4 font-semibold">
                                ₱{{ number_format($bill->amount, 2) }}
                            </td>

                            <td class="px-6 py-4 text-slate-600">
                                {{ $bill->due_date?->format('M d, Y') ?? '—' }}
                            </td>

                            <td class="px-6 py-4">
                                @if($bill->status === 'paid')
                                    <span class="inline-flex bg-green-50 text-green-700 border border-green-200 px-3 py-1 rounded-full text-xs font-bold">
                                        Paid
                                    </span>
                                @elseif($bill->status === 'pending')
                                    <span class="inline-flex bg-yellow-50 text-yellow-700 border border-yellow-200 px-3 py-1 rounded-full text-xs font-bold">
                                        Pending Verification
                                    </span>
                                @elseif($bill->status === 'overdue')
                                    <span class="inline-flex bg-orange-50 text-orange-700 border border-orange-200 px-3 py-1 rounded-full text-xs font-bold">
                                        Overdue
                                    </span>
                                @else
                                    <span class="inline-flex bg-red-50 text-red-700 border border-red-200 px-3 py-1 rounded-full text-xs font-bold">
                                        Unpaid
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="5"
                                class="px-6 py-12 text-center text-slate-500"
                            >
                                No billing records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($billingRecords->hasPages())
            <div class="px-6 py-4 border-t border-slate-200">
                {{ $billingRecords->links() }}
            </div>
        @endif
    </div>

</div>

<script>
function openPaymentModal(id) {
    const modal = document.getElementById(
        'payment-modal-' + id
    );

    if (!modal) {
        return;
    }

    modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');
}

function closePaymentModal(id) {
    const modal = document.getElementById(
        'payment-modal-' + id
    );

    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
}

function showRejectForm(id) {
    const form = document.getElementById(
        'reject-form-' + id
    );

    if (form) {
        form.classList.remove('hidden');
    }
}

function hideRejectForm(id) {
    const form = document.getElementById(
        'reject-form-' + id
    );

    if (form) {
        form.classList.add('hidden');
    }
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document
            .querySelectorAll('[id^="payment-modal-"]')
            .forEach(function(modal) {
                modal.classList.add('hidden');
            });

        document.body.classList.remove('overflow-hidden');
    }
});
</script>

@endsection