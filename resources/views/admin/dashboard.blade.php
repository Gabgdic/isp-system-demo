@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="space-y-8 animate-fadeIn">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold text-slate-900">Dashboard</h1>
            <p class="text-slate-500 mt-1">Welcome back! Here is your system overview.</p>
        </div>

        <div class="px-4 py-3 rounded-2xl bg-white border border-slate-200 shadow-sm">
            <p class="text-xs text-slate-500">Logged in as</p>
            <p class="font-semibold text-slate-800">{{ Auth::user()->username ?? 'Guest' }}</p>
        </div>
    </div>

    <!-- Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Total Users -->
        <div class="dashboard-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Users</p>
                    <h2 class="text-3xl font-bold text-slate-900 mt-2">
                        {{ \App\Models\User::count() }}
                    </h2>
                </div>

                <div class="card-icon bg-blue-50 text-blue-600">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            d="M17 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2m14-10a4 4 0 1 0-8 0m12 10v-2a4 4 0 0 0-3-3.87m-2-8a4 4 0 0 1 0 7.75"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Active Sessions -->
        <div class="dashboard-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Active Sessions</p>
                    <h2 class="text-3xl font-bold text-slate-900 mt-2">45</h2>
                </div>

                <div class="card-icon bg-green-50 text-green-600">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            d="M13 10V3L4 14h7v7l9-11h-7Z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Revenue -->
        <div class="dashboard-card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-slate-500">Revenue</p>
                    <h2 class="text-3xl font-bold text-slate-900 mt-2">₱12,500</h2>
                </div>

                <div class="card-icon bg-yellow-50 text-yellow-600">
                    <svg class="w-7 h-7" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8c-2.2 0-4 .9-4 2s1.8 2 4 2 4 .9 4 2-1.8 2-4 2m0-10v12"/>
                    </svg>
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-900">Recent Activity</h2>
                <p class="text-sm text-slate-500">Latest system activities and updates.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500">
                    <tr>
                        <th class="px-6 py-4 text-left font-semibold">User</th>
                        <th class="px-6 py-4 text-left font-semibold">Action</th>
                        <th class="px-6 py-4 text-left font-semibold">Date</th>
                        <th class="px-6 py-4 text-left font-semibold">Status</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr class="table-row-hover">
                        <td class="px-6 py-4 font-medium">John Doe</td>
                        <td class="px-6 py-4">Logged in</td>
                        <td class="px-6 py-4">March 21, 2026</td>
                        <td class="px-6 py-4">
                            <span class="status-badge bg-green-50 text-green-700">Completed</span>
                        </td>
                    </tr>

                    <tr class="table-row-hover">
                        <td class="px-6 py-4 font-medium">Jane Smith</td>
                        <td class="px-6 py-4">Updated profile</td>
                        <td class="px-6 py-4">March 20, 2026</td>
                        <td class="px-6 py-4">
                            <span class="status-badge bg-blue-50 text-blue-700">Updated</span>
                        </td>
                    </tr>

                    <tr class="table-row-hover">
                        <td class="px-6 py-4 font-medium">Mark Lee</td>
                        <td class="px-6 py-4">Registered</td>
                        <td class="px-6 py-4">March 19, 2026</td>
                        <td class="px-6 py-4">
                            <span class="status-badge bg-slate-100 text-slate-700">New</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

    </div>

</div>

<style>
    .dashboard-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 8px 30px rgba(15, 23, 42, 0.04);
        transition: all 0.25s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.10);
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

    .table-row-hover {
        transition: all 0.2s ease;
    }

    .table-row-hover:hover {
        background: #f8fafc;
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