@extends('layouts.app')

@section('title', 'Reports')

@section('content')

<div class="space-y-8">

    <!-- Header -->
    <div>
        <h1 class="text-3xl font-bold text-slate-900">
            Reports
        </h1>

        <p class="text-slate-500 mt-1">
            Overview of payments, revenue, and billing performance.
        </p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Revenue -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Total Revenue
            </p>

            <p class="text-3xl font-bold text-slate-900 mt-2">
                ₱{{ number_format($totalRevenue, 2) }}
            </p>

            <p class="text-xs text-slate-400 mt-2">
                From approved payments
            </p>
        </div>

        <!-- Approved Payments -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Approved Payments
            </p>

            <p class="text-3xl font-bold text-slate-900 mt-2">
                {{ number_format($approvedPayments) }}
            </p>

            <p class="text-xs text-green-600 mt-2">
                Successfully verified
            </p>
        </div>

        <!-- Pending -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Pending Payments
            </p>

            <p class="text-3xl font-bold text-slate-900 mt-2">
                {{ number_format($pendingPayments) }}
            </p>

            <p class="text-xs text-yellow-600 mt-2">
                Waiting for verification
            </p>
        </div>

        <!-- Outstanding -->
        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Outstanding Balance
            </p>

            <p class="text-3xl font-bold text-slate-900 mt-2">
                ₱{{ number_format($outstandingBalance, 2) }}
            </p>

            <p class="text-xs text-red-600 mt-2">
                Unpaid or pending bills
            </p>
        </div>

    </div>

    <!-- Outstanding vs Revenue -->
    <div class="bg-white border border-slate-200 rounded-3xl shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-200">

            <div class="flex flex-col sm:flex-row sm:items-center
                        sm:justify-between gap-4">

                <div>
                    <h2 class="text-lg font-bold text-slate-900">
                        Outstanding vs Revenue
                    </h2>

                    <p class="text-sm text-slate-500 mt-1">
                        Compare collected revenue with outstanding bills over time.
                    </p>
                </div>

                <!-- Graph Filter -->
                <form
                    method="GET"
                    action="{{ route('reports') }}"
                >
                    <select
                        name="period"
                        onchange="this.form.submit()"
                        class="border border-slate-300 rounded-xl px-4 py-2.5
                               text-sm font-semibold text-slate-700 bg-white
                               focus:ring-2 focus:ring-slate-300
                               focus:border-slate-400 outline-none"
                    >
                        <option
                            value="monthly"
                            {{ $period === 'monthly' ? 'selected' : '' }}
                        >
                            Monthly
                        </option>

                        <option
                            value="yearly"
                            {{ $period === 'yearly' ? 'selected' : '' }}
                        >
                            Yearly
                        </option>
                    </select>
                </form>

            </div>
        </div>

        <div class="p-6">
            <div class="relative h-[350px]">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Billing Status + Payment Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Billing Status -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm">

            <div class="px-6 py-5 border-b border-slate-200">

                <h2 class="text-lg font-bold text-slate-900">
                    Billing Status
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Current status of subscriber bills.
                </p>

            </div>

            <div class="p-6">

                <div class="relative h-[300px]">
                    <canvas id="billingStatusChart"></canvas>
                </div>

            </div>

        </div>

        <!-- Payment Summary -->
        <div class="bg-white border border-slate-200 rounded-3xl shadow-sm">

            <div class="px-6 py-5 border-b border-slate-200">

                <h2 class="text-lg font-bold text-slate-900">
                    Payment Summary
                </h2>

                <p class="text-sm text-slate-500 mt-1">
                    Overview of payment verification.
                </p>

            </div>

            <div class="p-6 space-y-4">

                <!-- Approved -->
                <div class="flex items-center justify-between
                            p-4 bg-green-50 rounded-2xl">

                    <div>
                        <p class="text-sm font-semibold text-green-800">
                            Approved Payments
                        </p>

                        <p class="text-xs text-green-600 mt-1">
                            Successfully verified
                        </p>
                    </div>

                    <p class="text-2xl font-bold text-green-700">
                        {{ $approvedPayments }}
                    </p>

                </div>

                <!-- Pending -->
                <div class="flex items-center justify-between
                            p-4 bg-yellow-50 rounded-2xl">

                    <div>
                        <p class="text-sm font-semibold text-yellow-800">
                            Pending Payments
                        </p>

                        <p class="text-xs text-yellow-600 mt-1">
                            Awaiting review
                        </p>
                    </div>

                    <p class="text-2xl font-bold text-yellow-700">
                        {{ $pendingPayments }}
                    </p>

                </div>

                <!-- Outstanding -->
                <div class="flex items-center justify-between
                            p-4 bg-red-50 rounded-2xl">

                    <div>
                        <p class="text-sm font-semibold text-red-800">
                            Outstanding Balance
                        </p>

                        <p class="text-xs text-red-600 mt-1">
                            Unpaid or pending bills
                        </p>
                    </div>

                    <p class="text-2xl font-bold text-red-700">
                        ₱{{ number_format($outstandingBalance, 2) }}
                    </p>

                </div>

                <!-- Total Bills -->
                <div class="flex items-center justify-between
                            p-4 bg-slate-50 rounded-2xl">

                    <div>
                        <p class="text-sm font-semibold text-slate-700">
                            Total Billing Records
                        </p>

                        <p class="text-xs text-slate-500 mt-1">
                            All generated bills
                        </p>
                    </div>

                    <p class="text-2xl font-bold text-slate-800">
                        {{ $paidBills + $pendingBills + $unpaidBills }}
                    </p>

                </div>

            </div>

        </div>

    </div>

    <!-- Recent Payments -->
    <div class="bg-white border border-slate-200 rounded-3xl
                shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-200">

            <h2 class="text-lg font-bold text-slate-900">
                Recent Approved Payments
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Latest payments verified by the administrator.
            </p>

        </div>

        @if($recentPayments->count())

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
                                Reference
                            </th>

                            <th class="px-6 py-4 text-left font-semibold">
                                Approved
                            </th>
                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100">

                        @foreach($recentPayments as $payment)

                            <tr class="hover:bg-slate-50 transition">

                                <td class="px-6 py-4">

                                    <div class="font-semibold text-slate-900">
                                        {{ $payment->subscriber->full_name }}
                                    </div>

                                    <div class="text-xs text-slate-500">
                                        {{ $payment->subscriber->username }}
                                    </div>

                                </td>

                                <td class="px-6 py-4 text-slate-700">

                                    @if($payment->billingRecord)
                                        {{ \Carbon\Carbon::create(
                                            $payment->billingRecord->billing_year,
                                            $payment->billingRecord->billing_month,
                                            1
                                        )->format('F Y') }}
                                    @elseif($payment->billingAllocations->isNotEmpty())
                                        @php
                                            $months = $payment->billingAllocations->map(function($alloc) {
                                                return optional($alloc->billingRecord)->billing_year && optional($alloc->billingRecord)->billing_month
                                                    ? \Carbon\Carbon::create($alloc->billingRecord->billing_year, $alloc->billingRecord->billing_month, 1)->format('M Y')
                                                    : null;
                                            })->filter()->implode(', ');
                                        @endphp
                                        {{ $months ?: 'Multiple Months' }}
                                    @else
                                        —
                                    @endif

                                </td>

                                <td class="px-6 py-4 font-bold text-slate-900">
                                    ₱{{ number_format(
                                        $payment->amount_paid,
                                        2
                                    ) }}
                                </td>

                                <td class="px-6 py-4">

                                    <span class="font-mono text-xs
                                                 bg-slate-100 px-2 py-1
                                                 rounded-lg">
                                        {{ $payment->reference_number }}
                                    </span>

                                </td>

                                <td class="px-6 py-4 text-slate-500">

                                    {{
                                        optional(
                                            $payment->reviewed_at
                                        )->format('M d, Y h:i A')
                                        ?? '—'
                                    }}

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="px-6 py-12 text-center">

                <p class="text-sm text-slate-500">
                    No approved payments yet.
                </p>

            </div>

        @endif

    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Outstanding vs Revenue Chart
    |--------------------------------------------------------------------------
    */

    const revenueCanvas =
        document.getElementById('revenueChart');

    if (revenueCanvas) {

        new Chart(revenueCanvas, {

            type: 'line',

            data: {

                labels: @json($trendLabels),

                datasets: [

                    {
                        label: 'Revenue (₱)',
                        data: @json($trendRevenue),
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 4,
                        yAxisID: 'moneyAxis'
                    },

                    {
                        label: 'Outstanding (₱)',
                        data: @json($trendOutstanding),
                        tension: 0.35,
                        borderWidth: 2,
                        pointRadius: 4,
                        yAxisID: 'moneyAxis'
                    }

                ]

            },

            options: {

                responsive: true,
                maintainAspectRatio: false,

                interaction: {
                    mode: 'index',
                    intersect: false
                },

                plugins: {

                    legend: {
                        position: 'top'
                    },

                    tooltip: {

                        callbacks: {

                            label: function (context) {

                                return ' ' +
                                    context.dataset.label +
                                    ': ₱' +
                                    Number(
                                        context.raw
                                    ).toLocaleString(
                                        'en-PH',
                                        {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        }
                                    );

                            }

                        }

                    }

                },

                scales: {

                    moneyAxis: {

                        type: 'linear',

                        position: 'left',

                        beginAtZero: true,

                        ticks: {

                            callback: function (value) {

                                return '₱' +
                                    Number(
                                        value
                                    ).toLocaleString();

                            }

                        }

                    }

                }

            }

        });

    }

    /*
    |--------------------------------------------------------------------------
    | Billing Status Doughnut
    |--------------------------------------------------------------------------
    */

    const billingCanvas =
        document.getElementById('billingStatusChart');

    if (billingCanvas) {

        new Chart(billingCanvas, {

            type: 'doughnut',

            data: {

                labels: [
                    'Paid',
                    'Pending',
                    'Unpaid / Overdue'
                ],

                datasets: [

                    {
                        data: [
                            {{ $paidBills }},
                            {{ $pendingBills }},
                            {{ $unpaidBills }}
                        ],
                        borderWidth: 2
                    }

                ]

            },

            options: {

                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',

                plugins: {

                    legend: {
                        position: 'bottom'
                    }

                }

            }

        });

    }

});

</script>

@endsection