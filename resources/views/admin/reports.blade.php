@extends('layouts.app')

@section('title', 'Reports')

@section('content')

<div class="space-y-4 max-w-full mx-auto animate-fadeIn">

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Reports</h1>
            <p class="text-xs text-slate-500">Overview of payments, revenue, and billing performance.</p>
        </div>

        <!-- Graph Filter -->
        <form method="GET" action="{{ route('reports') }}">
            <select
                name="period"
                onchange="this.form.submit()"
                aria-label="Filter reports by month"
                class="border border-slate-300 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white focus:ring-2 focus:ring-slate-300 outline-none"
            >
                <option value="overall" {{ $period === 'overall' ? 'selected' : '' }}>Overall</option>
                @foreach($monthOptions as $monthValue => $monthLabel)
                    <option value="{{ $monthValue }}" {{ $period === $monthValue ? 'selected' : '' }}>{{ $monthLabel }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Top KPI Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Revenue -->
        <div class="bg-slate-50 border border-slate-300 rounded-xl p-3 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Total Revenue</p>
            <p class="text-xl font-bold text-slate-900 mt-1">₱{{ number_format($totalRevenue, 2) }}</p>
        </div>

        <!-- Approved Payments -->
        <div class="bg-slate-50 border border-slate-300 rounded-xl p-3 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Approved Payments</p>
            <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($approvedPayments) }}</p>
        </div>

        <!-- Pending -->
        <div class="bg-slate-50 border border-slate-300 rounded-xl p-3 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Pending Payments</p>
            <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($pendingPayments) }}</p>
        </div>

        <!-- Outstanding -->
        <div class="bg-slate-50 border border-slate-300 rounded-xl p-3 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Outstanding Balance</p>
            <p class="text-xl font-bold text-slate-900 mt-1">₱{{ number_format($outstandingBalance, 2) }}</p>
        </div>
    </div>

    <!-- Main Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">

        <!-- Line Chart: Revenue vs Outstanding (2 Columns) -->
        <div class="lg:col-span-2 bg-slate-50 border border-slate-300 rounded-xl p-4 shadow-sm flex flex-col justify-between">
            <div class="mb-2">
                <h2 class="text-sm font-bold text-slate-900">Outstanding vs Revenue</h2>
                <p class="text-xs text-slate-500">Collected revenue vs unpaid balance</p>
            </div>
            <div class="relative h-[200px] w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart: Billing Status (1 Column) -->
        <div class="bg-slate-50 border border-slate-300 rounded-xl p-4 shadow-sm flex flex-col justify-between">
            <div class="mb-2">
                <h2 class="text-sm font-bold text-slate-900">Billing Breakdown</h2>
                <p class="text-xs text-slate-500">Distribution of subscriber bills</p>
            </div>
            <div class="relative h-[200px] w-full">
                <canvas id="billingStatusChart"></canvas>
            </div>
        </div>

    </div>

</div>

<style>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const applyReportChartTheme = () => {
    const isDark = document.documentElement.classList.contains('dark');
    Chart.defaults.color = isDark ? '#d1d5db' : '#475569';
    Chart.defaults.borderColor = isDark ? '#374151' : '#e2e8f0';
    Chart.defaults.scale.grid.color = isDark ? '#374151' : '#e2e8f0';

    Object.values(Chart.instances).forEach(chart => chart.update());
};

applyReportChartTheme();
document.addEventListener('admin-theme-change', applyReportChartTheme);

document.addEventListener('DOMContentLoaded', function () {
    // Line Chart
    const revenueCanvas = document.getElementById('revenueChart');
    if (revenueCanvas) {
        new Chart(revenueCanvas, {
            type: 'line',
            data: {
                labels: @json($trendLabels),
                datasets: [
                    {
                        label: 'Revenue',
                        data: @json($trendRevenue),
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 3,
                        borderColor: '#0284c7',
                        backgroundColor: '#0284c7',
                        yAxisID: 'moneyAxis'
                    },
                    {
                        label: 'Outstanding',
                        data: @json($trendOutstanding),
                        tension: 0.3,
                        borderWidth: 2,
                        pointRadius: 3,
                        borderColor: '#ef4444',
                        backgroundColor: '#ef4444',
                        yAxisID: 'moneyAxis'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { size: 11 } } }
                },
                scales: {
                    moneyAxis: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            font: { size: 10 },
                            callback: function (value) {
                                return '₱' + Number(value).toLocaleString();
                            }
                        }
                    },
                    x: { ticks: { font: { size: 10 } } }
                }
            }
        });
    }

    // Doughnut Chart
    const billingCanvas = document.getElementById('billingStatusChart');
    if (billingCanvas) {
        new Chart(billingCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Pending', 'Unpaid/Overdue'],
                datasets: [{
                    data: [
                        {{ $paidBills }},
                        {{ $pendingBills }},
                        {{ $unpaidBills }}
                    ],
                    backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10 } } },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                let label = context.label || '';
                                let value = context.raw || 0;
                                let total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                                let percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0%';
                                
                                return `${label}: ${value} (${percentage})`;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

@endsection