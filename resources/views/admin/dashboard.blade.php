@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="space-y-8 animate-fadeIn">

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
        <div class="dashboard-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="card-label">Total Subscribers</p>
                    <h2 class="card-value">{{ $totalSubscribers }}</h2>
                </div>

                <div class="card-icon bg-blue-50 text-blue-600">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m14-10a4 4 0 1 0-8 0m12 10v-2a4 4 0 0 0-3-3.87m-2-8a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Subscribers -->
        <div class="dashboard-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="card-label">Active Subscribers</p>
                    <h2 class="card-value text-green-600">{{ $activeSubscribers }}</h2>
                </div>

                <div class="card-icon bg-green-50 text-green-600">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Disconnected Subscribers -->
        <div class="dashboard-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="card-label">Disconnected</p>
                    <h2 class="card-value text-red-600">{{ $disconnectedSubscribers }}</h2>
                </div>

                <div class="card-icon bg-red-50 text-red-600">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Plans -->
        <div class="dashboard-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="card-label">Active Plans</p>
                    <h2 class="card-value">{{ $activePlans }}</h2>
                </div>

                <div class="card-icon bg-purple-50 text-purple-600">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            d="M4 6h16M4 12h16M4 18h7"/>
                    </svg>
                </div>
            </div>
        </div>

    </div>

    <!-- Status Overview -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <!-- Subscriber Status -->
        <div class="xl:col-span-1 bg-white rounded-3xl border border-slate-200 shadow-sm p-6 dashboard-card-soft">
            <h2 class="text-lg font-bold text-slate-900">Subscriber Status</h2>
            <p class="text-sm text-slate-500 mt-1">Quick status breakdown.</p>

            <div class="mt-6 space-y-4">
                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-slate-700">Active</span>
                        <span class="text-slate-500">{{ $activeSubscribers }}</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill bg-green-500"
                            style="width: {{ $totalSubscribers > 0 ? ($activeSubscribers / $totalSubscribers) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-slate-700">Inactive</span>
                        <span class="text-slate-500">{{ $inactiveSubscribers }}</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill bg-yellow-500"
                            style="width: {{ $totalSubscribers > 0 ? ($inactiveSubscribers / $totalSubscribers) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-2">
                        <span class="font-semibold text-slate-700">Disconnected</span>
                        <span class="text-slate-500">{{ $disconnectedSubscribers }}</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill bg-red-500"
                            style="width: {{ $totalSubscribers > 0 ? ($disconnectedSubscribers / $totalSubscribers) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Subscribers -->
        <div class="xl:col-span-2 bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden dashboard-card-soft">
            <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Recent Subscribers</h2>
                    <p class="text-sm text-slate-500 mt-1">Latest registered subscriber accounts.</p>
                </div>

                <a href="{{ route('subscriber') }}"
                    class="text-sm font-semibold text-slate-700 hover:text-slate-900">
                    View all
                </a>
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
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($subscriber->profile_photo)
                                            <img src="{{ asset('storage/' . $subscriber->profile_photo) }}"
                                                class="w-10 h-10 rounded-full object-cover border border-slate-200"
                                                alt="Subscriber Photo">
                                        @else
                                            <div class="w-10 h-10 rounded-full bg-slate-900 text-white flex items-center justify-center text-sm font-bold">
                                                {{ strtoupper(substr($subscriber->full_name ?? 'S', 0, 1)) }}
                                            </div>
                                        @endif

                                        <div>
                                            <p class="font-semibold text-slate-800">{{ $subscriber->full_name }}</p>
                                            <p class="text-xs text-slate-400">{{ '@' . $subscriber->username }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    {{ $subscriber->plan_name ?? 'No plan' }}
                                </td>

                                <td class="px-6 py-4 font-semibold">
                                    ₱{{ number_format($subscriber->monthly_fee, 2) }}
                                </td>

                                <td class="px-6 py-4">
                                    @if($subscriber->status === 'active')
                                        <span class="status-badge bg-green-50 text-green-700">Active</span>
                                    @elseif($subscriber->status === 'inactive')
                                        <span class="status-badge bg-yellow-50 text-yellow-700">Inactive</span>
                                    @else
                                        <span class="status-badge bg-red-50 text-red-700">Disconnected</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-slate-500">
                                    No subscribers yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Bottom Section -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

        <!-- Subscription Plans -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden dashboard-card-soft">
            <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Subscription Plans</h2>
                    <p class="text-sm text-slate-500 mt-1">Current plans from settings.</p>
                </div>

                <a href="{{ route('settings') }}"
                    class="text-sm font-semibold text-slate-700 hover:text-slate-900">
                    Manage
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($plans as $plan)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-slate-50 transition">
                        <div>
                            <p class="font-semibold text-slate-800">{{ $plan->plan_name }}</p>
                            <p class="text-xs text-slate-400">{{ $plan->description ?? 'No description' }}</p>
                        </div>

                        <div class="text-right">
                            <p class="font-bold text-slate-900">₱{{ number_format($plan->price, 2) }}</p>

                            @if($plan->status === 'active')
                                <p class="text-xs font-semibold text-green-600">Active</p>
                            @else
                                <p class="text-xs font-semibold text-slate-400">Inactive</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-10 text-center text-slate-500">
                        No subscription plans yet.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- System Summary -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 dashboard-card-soft">
            <h2 class="text-lg font-bold text-slate-900">System Summary</h2>
            <p class="text-sm text-slate-500 mt-1">Quick admin and account overview.</p>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="summary-box">
                    <p class="summary-label">Admin Accounts</p>
                    <h3 class="summary-value">{{ $totalAdmins }}</h3>
                </div>

                <div class="summary-box">
                    <p class="summary-label">Active Plans</p>
                    <h3 class="summary-value">{{ $activePlans }}</h3>
                </div>

                <div class="summary-box">
                    <p class="summary-label">Inactive Subscribers</p>
                    <h3 class="summary-value">{{ $inactiveSubscribers }}</h3>
                </div>

                <div class="summary-box">
                    <p class="summary-label">Monthly Revenue</p>
                    <h3 class="summary-value text-green-600">
                        ₱{{ number_format($estimatedMonthlyRevenue, 2) }}
                    </h3>
                </div>
            </div>
        </div>

    </div>

</div>

<style>
    .dashboard-card,
    .dashboard-card-soft {
        transition: all 0.25s ease;
    }

    .dashboard-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.04);
    }

    .dashboard-card:hover,
    .dashboard-card-soft:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.08);
    }

    .card-label {
        font-size: 14px;
        font-weight: 600;
        color: #64748b;
    }

    .card-value {
        font-size: 32px;
        font-weight: 800;
        color: #0f172a;
        margin-top: 8px;
    }

    .card-icon {
        width: 56px;
        height: 56px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
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

    .summary-box {
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 18px;
        background: #f8fafc;
    }

    .summary-label {
        font-size: 13px;
        font-weight: 600;
        color: #64748b;
    }

    .summary-value {
        font-size: 24px;
        font-weight: 800;
        color: #0f172a;
        margin-top: 8px;
    }

    .animate-fadeIn {
        animation: fadeIn 0.45s ease-in-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

@endsection