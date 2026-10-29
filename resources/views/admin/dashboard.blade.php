@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 space-y-8 animate-fadeIn">
    <header class="relative isolate overflow-hidden rounded-3xl bg-slate-900 px-6 py-7 sm:px-8 sm:py-9 text-white shadow-lg">
        <div class="absolute -right-16 -top-24 h-72 w-72 rounded-full border-[32px] border-white/5" aria-hidden="true"></div>
        <div class="absolute right-32 -bottom-28 h-56 w-56 rounded-full bg-white/5" aria-hidden="true"></div>
        <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex items-center gap-4 min-w-0">
                @if(!empty($systemSetting->system_logo))
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 p-2 ring-1 ring-white/15">
                        <img src="{{ asset('storage/' . $systemSetting->system_logo) }}" alt="System Logo" class="max-h-full max-w-full object-contain">
                    </div>
                @else
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 text-xl font-black ring-1 ring-white/15">
                        {{ strtoupper(substr($systemSetting->system_name ?? 'S', 0, 1)) }}
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-300">Management dashboard</p>
                    <h1 class="mt-1 text-2xl sm:text-3xl font-black leading-tight break-words">
                        {{ $systemSetting->system_name ?? 'S&B Internet Services' }}
                    </h1>
                    <p class="mt-2 text-sm text-slate-300">
                        Welcome back, <span class="font-semibold text-white">{{ auth()->user()->username ?? 'Admin' }}</span>. Here's your service overview.
                    </p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                <div class="rounded-xl border border-white/10 bg-white/5 px-4 py-2.5">
                    <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">Today</p>
                    <p class="text-sm font-semibold text-white">{{ now()->format('D, F j, Y') }}</p>
                </div>
            </div>
        </div>
    </header>

    <section aria-labelledby="monthly-overview-heading">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
            <div>
                <h2 id="monthly-overview-heading" class="text-xl font-bold text-slate-900">{{ now()->format('F') }} billing overview</h2>
            </div>
            <span class="text-xs font-medium text-slate-500">Current billing period</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            <article class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-sm font-semibold text-slate-600">Paid bills</p>
                <p class="mt-4 text-3xl font-black tracking-tight text-slate-900">{{ number_format($thisMonthPaidBills) }}</p>
                <p class="mt-1 text-xs text-slate-500">Bills paid this month</p>
            </article>
            <article class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-sm font-semibold text-slate-600">Unpaid</p>
                <p class="mt-4 text-3xl font-black tracking-tight text-slate-900">{{ number_format($thisMonthUnpaidBills) }}</p>
                <p class="mt-1 text-xs text-slate-500">Unpaid or overdue bills</p>
            </article>
            <article class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-sm font-semibold text-slate-600">Pending review</p>
                <p class="mt-4 text-3xl font-black tracking-tight text-slate-900">{{ number_format($pendingVerifications) }}</p>
                <p class="mt-1 text-xs text-slate-500">Payment submissions to verify</p>
            </article>
            <article class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-sm font-semibold text-slate-600">Verified payments</p>
                <p class="mt-4 text-3xl font-black tracking-tight text-slate-900">{{ number_format($verifiedPayments) }}</p>
                <p class="mt-1 text-xs text-slate-500">Approved payment submissions</p>
            </article>
            <article class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                <p class="text-sm font-semibold text-slate-600">Revenue this month</p>
                <p class="mt-4 text-2xl font-black tracking-tight text-slate-900">₱{{ number_format((float) $thisMonthRevenue, 2) }}</p>
                <p class="mt-1 text-xs text-slate-500">From approved payments</p>
            </article>
        </div>
    </section>

    <section aria-labelledby="subscriber-overview-heading" class="space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
            <div>
                <h2 id="subscriber-overview-heading" class="mt-1 text-xl font-bold text-slate-900">System overview</h2>
            </div>
            <a href="{{ route('subscriber') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-slate-600 hover:text-slate-900">
                Manage subscribers
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7 7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
            <article class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <p class="text-xs sm:text-sm font-medium text-slate-500">Total Subscribers</p>
                <p class="mt-2 text-2xl sm:text-3xl font-black text-slate-900">{{ number_format($totalSubscribers) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <p class="text-xs sm:text-sm font-medium text-slate-500">Active</p>
                <p class="mt-2 text-2xl sm:text-3xl font-black text-slate-900">{{ number_format($activeSubscribers) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <p class="text-xs sm:text-sm font-medium text-slate-500">Inactive</p>
                <p class="mt-2 text-2xl sm:text-3xl font-black text-slate-900">{{ number_format($inactiveSubscribers) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <p class="text-xs sm:text-sm font-medium text-slate-500">Disconnected</p>
                <p class="mt-2 text-2xl sm:text-3xl font-black text-slate-900">{{ number_format($disconnectedSubscribers) }}</p>
            </article>
            <article class="rounded-2xl border border-slate-200 bg-white p-4 sm:p-5 shadow-sm">
                <p class="text-xs sm:text-sm font-medium text-slate-500">Subscription Plans</p>
                <p class="mt-2 text-2xl sm:text-3xl font-black text-slate-900">{{ number_format($activePlans) }}</p>
            </article>
        </div>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-2xl border border-slate-200 bg-white px-5 py-4 shadow-sm">
            <div>
                <p class="text-sm font-bold text-slate-900">Estimated monthly recurring revenue</p>
                <p class="mt-1 text-xs text-slate-500">Based on the current monthly fees of active subscribers.</p>
            </div>
            <p class="text-2xl font-black tracking-tight text-slate-900">₱{{ number_format((float) $estimatedMonthlyRevenue, 2) }}</p>
        </div>
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