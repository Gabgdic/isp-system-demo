@extends('layouts.app')

@section('content')

<div class="space-y-6">

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-heading">Dashboard</h1>
        <p class="text-body">Welcome back! Here's your overview.</p>
    </div>

    <!-- Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="bg-white p-6 rounded-base border border-default shadow-sm">
            <h2 class="text-sm text-body">Total Users</h2>
            <p class="text-3xl font-bold text-heading mt-2">120</p>
        </div>

        <div class="bg-white p-6 rounded-base border border-default shadow-sm">
            <h2 class="text-sm text-body">Active Sessions</h2>
            <p class="text-3xl font-bold text-heading mt-2">45</p>
        </div>

        <div class="bg-white p-6 rounded-base border border-default shadow-sm">
            <h2 class="text-sm text-body">Revenue</h2>
            <p class="text-3xl font-bold text-heading mt-2">₱12,500</p>
        </div>

    </div>

    <!-- Table -->
    <div class="bg-white p-6 rounded-base border border-default shadow-sm">
        <h2 class="text-xl font-bold text-heading mb-4">Recent Activity</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-body border-b border-default">
                        <th class="py-2">User</th>
                        <th class="py-2">Action</th>
                        <th class="py-2">Date</th>
                    </tr>
                </thead>
                <tbody class="text-body">
                    <tr class="border-b border-default">
                        <td class="py-2">John Doe</td>
                        <td class="py-2">Logged in</td>
                        <td class="py-2">March 21, 2026</td>
                    </tr>
                    <tr class="border-b border-default">
                        <td class="py-2">Jane Smith</td>
                        <td class="py-2">Updated profile</td>
                        <td class="py-2">March 20, 2026</td>
                    </tr>
                    <tr>
                        <td class="py-2">Mark Lee</td>
                        <td class="py-2">Registered</td>
                        <td class="py-2">March 19, 2026</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

@endsection