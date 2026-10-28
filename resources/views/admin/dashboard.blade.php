@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 space-y-8 animate-fadeIn">
    <header class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-200 pb-6">
        <div class="flex items-center gap-4 min-w-0">
            @if(!empty($systemSetting->system_logo))
                <img src="{{ asset('storage/' . $systemSetting->system_logo) }}" alt="System Logo" class="w-14 h-14 object-contain flex-shrink-0">
            @endif
            <div class="min-w-0">
                <h1 class="text-2xl sm:text-3xl font-black text-slate-900 leading-tight break-words">
                    {{ $systemSetting->system_name ?? 'S&B Internet Services' }}
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Welcome back, <span class="font-semibold text-slate-800">{{ auth()->user()->name ?? 'Admin' }}</span>
                </p>
            </div>
        </div>
        <p class="text-sm font-medium text-slate-500">{{ now()->format('F j, Y') }}</p>
    </header>

    <section aria-labelledby="monthly-overview-heading">
        <div class="flex items-baseline justify-between gap-3 mb-4">
            <h2 id="monthly-overview-heading" class="text-lg font-bold text-slate-900">{{ now()->format('F') }} Overview</h2>
            <span class="text-xs font-medium text-slate-500">Billing period</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <article class="bg-white border border-slate-200 rounded-lg p-5">
                <p class="text-sm font-medium text-slate-500">Paid</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($thisMonthPaidBills) }}</p>
                <p class="mt-1 text-xs text-slate-500">Bills this month</p>
            </article>
            <article class="bg-white border border-slate-200 rounded-lg p-5">
                <p class="text-sm font-medium text-slate-500">Unpaid</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($thisMonthUnpaidBills) }}</p>
                <p class="mt-1 text-xs text-slate-500">Unpaid or overdue bills</p>
            </article>
            <article class="bg-white border border-slate-200 rounded-lg p-5">
                <p class="text-sm font-medium text-slate-500">Pending Verifications</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($pendingVerifications) }}</p>
                <p class="mt-1 text-xs text-slate-500">Awaiting review</p>
            </article>
            <article class="bg-white border border-slate-200 rounded-lg p-5">
                <p class="text-sm font-medium text-slate-500">Verified</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($verifiedPayments) }}</p>
                <p class="mt-1 text-xs text-slate-500">Approved payments</p>
            </article>
            <article class="bg-white border border-slate-200 rounded-lg p-5">
                <p class="text-sm font-medium text-slate-500">This Month's Revenue</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">₱{{ number_format((float) $thisMonthRevenue, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">Approved payments</p>
            </article>
        </div>
    </section>

    <section aria-labelledby="subscriber-overview-heading">
        <h2 id="subscriber-overview-heading" class="text-lg font-bold text-slate-900 mb-4">System Overview</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
            <article class="bg-white border border-slate-200 rounded-lg p-5">
                <p class="text-sm font-medium text-slate-500">Total Subscribers</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($totalSubscribers) }}</p>
            </article>
            <article class="bg-white border border-slate-200 rounded-lg p-5">
                <p class="text-sm font-medium text-slate-500">Active</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($activeSubscribers) }}</p>
            </article>
            <article class="bg-white border border-slate-200 rounded-lg p-5">
                <p class="text-sm font-medium text-slate-500">Inactive</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($inactiveSubscribers) }}</p>
            </article>
            <article class="bg-white border border-slate-200 rounded-lg p-5">
                <p class="text-sm font-medium text-slate-500">Disconnected</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($disconnectedSubscribers) }}</p>
            </article>
            <article class="bg-white border border-slate-200 rounded-lg p-5">
                <p class="text-sm font-medium text-slate-500">Subscription Plans</p>
                <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($activePlans) }}</p>
            </article>
        </div>
        <p class="mt-4 text-sm text-slate-500">
            Estimated recurring monthly revenue from active subscribers:
            <span class="font-semibold text-slate-800">₱{{ number_format((float) $estimatedMonthlyRevenue, 2) }}</span>
        </p>
    </section>
</div>

<style>
.animate-fadeIn {
    animation: fadeIn 0.45s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(6px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

@endsection