@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="space-y-8">

    <!-- Header -->
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
            <p class="text-slate-500 mt-1">
                Welcome back, {{ Auth::user()->username ?? 'Admin' }}. Here is your system overview.
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-3xl px-5 py-4 shadow-sm">
            <p class="text-xs text-slate-500 font-medium">Estimated Monthly Revenue</p>
            <h2 class="text-2xl font-bold text-slate-900 mt-1">
                ₱{{ number_format($estimatedMonthlyRevenue, 2) }}
            </h2>
        </div>
    </div>

    <!-- Main Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

        <!-- Total Subscribers -->
        <div class="dashboard-card bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <p class="card-label">Total Subscribers</p>
            <h2 class="card-value">{{ $totalSubscribers }}</h2>
        </div>

        <!-- Active Subscribers -->
        <div class="dashboard-card bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <p class="card-label">Active Subscribers</p>
            <h2 class="card-value text-green-600">{{ $activeSubscribers }}</h2>
        </div>

        <!-- Disconnected Subscribers -->
        <div class="dashboard-card bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <p class="card-label">Disconnected</p>
            <h2 class="card-value text-red-600">{{ $disconnectedSubscribers }}</h2>
        </div>

        <!-- Active Plans -->
        <div class="dashboard-card bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <p class="card-label">Active Plans</p>
            <h2 class="card-value">{{ $activePlans }}</h2>
        </div>

    </div>

    <!-- Status Overview & Recent Subscribers -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Subscriber Status (static, no hover) -->
        <div class="dashboard-card p-6 bg-white border border-slate-200 rounded-3xl shadow-sm">
            <h2 class="text-lg font-bold text-slate-900">Subscriber Status</h2>
            <p class="text-sm text-slate-500 mt-1">Quick status breakdown.</p>

            <div class="mt-6 space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-slate-700">Active</span>
                        <span class="text-slate-500">{{ $activeSubscribers }}</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill bg-green-500" style="width: {{ $totalSubscribers > 0 ? ($activeSubscribers / $totalSubscribers) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-slate-700">Inactive</span>
                        <span class="text-slate-500">{{ $inactiveSubscribers }}</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill bg-yellow-500" style="width: {{ $totalSubscribers > 0 ? ($inactiveSubscribers / $totalSubscribers) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-slate-700">Disconnected</span>
                        <span class="text-slate-500">{{ $disconnectedSubscribers }}</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill bg-red-500" style="width: {{ $totalSubscribers > 0 ? ($disconnectedSubscribers / $totalSubscribers) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Subscribers (interactive, hover enabled) -->
        <div class="dashboard-card p-6 bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-bold text-slate-900">Recent Subscribers</h2>
                <a href="{{ route('subscriber') }}" class="text-sm font-semibold text-slate-700 hover:text-slate-900">View all</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold">Subscriber</th>
                            <th class="px-6 py-4 text-left font-semibold">Plan</th>
                            <th class="px-6 py-4 text-left font-semibold">Monthly Fee</th>
                            <th class="px-6 py-4 text-left font-semibold">Status</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @forelse($recentSubscribers as $subscriber)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-6 py-4">{{ $subscriber->full_name }}</td>
                            <td class="px-6 py-4">{{ $subscriber->plan_name ?? 'No plan' }}</td>
                            <td class="px-6 py-4 font-semibold">₱{{ number_format($subscriber->monthly_fee, 2) }}</td>
                            <td class="px-6 py-4">{{ ucfirst($subscriber->status) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-slate-500">No subscribers yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<style>
.dashboard-card {
    background: white;
    border-radius: 24px;
    padding: 24px;
}

.card-value {
    font-size: 32px;
    font-weight: 800;
    color: #0f172a;
    margin-top: 8px;
}

.progress-track {
    width: 100%;
    height: 10px;
    background: #e2e8f0;
    border-radius: 999px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 999px;
    transition: width 0.5s ease;
}
</style>

@endsection