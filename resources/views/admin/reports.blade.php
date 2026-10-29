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
            <input type="hidden" name="search" value="{{ $search }}">
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
            <p class="text-xs font-medium text-slate-500">Approved Payment Submissions</p>
            <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($approvedPayments) }}</p>
            <p class="text-[11px] text-slate-500 mt-1">Counted by approval date</p>
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

    <section class="bg-slate-50 border border-slate-300 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-sm font-bold text-slate-900">Paid Bills</h2>
                <p class="text-xs text-slate-500">Payments with the bills they cover, paid date, and receipt.</p>
            </div>
            <label class="sr-only" for="paid-bills-search">Search paid bills</label>
            <input
                id="paid-bills-search"
                type="search"
                value="{{ $search }}"
                placeholder="Search subscriber, period, amount, or reference..."
                autocomplete="off"
                class="w-full sm:w-72 border border-slate-300 rounded-lg px-3 py-2 text-xs text-slate-700 bg-white focus:ring-2 focus:ring-slate-300 outline-none"
            >
        </div>
        <div id="paid-bills-content" aria-live="polite">
            @include('admin.partials.paid_bills_table', ['paidBillRecords' => $paidBillRecords])
        </div>
    </section>

</div>

<div id="reportReceiptModal" class="hidden fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm items-center justify-center p-4" onclick="if (event.target === this) closeReportReceiptModal()">
    <div class="relative bg-white w-full max-w-5xl rounded-2xl shadow-2xl overflow-hidden my-8 border border-slate-200 flex flex-col max-h-[92vh]">
        <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
            <h3 class="text-sm font-bold text-slate-900">Official Receipt</h3>
            <div class="flex items-center gap-2">
                <button type="button" onclick="printReportReceipt()" class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold">Print</button>
                <button type="button" onclick="closeReportReceiptModal()" aria-label="Close receipt" class="w-7 h-7 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-600 text-xs">✕</button>
            </div>
        </div>
        <div class="flex-1 min-h-0 bg-slate-100 p-2 sm:p-4">
            <iframe id="report-receipt-frame" title="Printable Official Receipt" class="h-[75vh] w-full rounded-lg border border-slate-200 bg-white"></iframe>
        </div>
    </div>
</div>

<div id="paymentProofModal" class="hidden fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm items-center justify-center p-4" onclick="if (event.target === this) closePaymentProofModal()">
    <div class="relative bg-white w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden border border-slate-200">
        <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
            <h3 class="text-sm font-bold text-slate-900">Payment Proof</h3>
            <button type="button" onclick="closePaymentProofModal()" aria-label="Close payment proof" class="w-7 h-7 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-600 text-xs">✕</button>
        </div>
        <div class="p-4 bg-slate-100 flex items-center justify-center max-h-[80vh] overflow-auto">
            <img id="payment-proof-image" src="" alt="Submitted payment proof" class="max-w-full max-h-[72vh] object-contain rounded-lg">
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
    const paidBillsContent = document.getElementById('paid-bills-content');
    const paidBillsSearch = document.getElementById('paid-bills-search');
    let paidBillsSearchTimer;
    let paidBillsRequestId = 0;

    const loadPaidBills = (url, historyMode = 'replace') => {
        const requestId = ++paidBillsRequestId;
        paidBillsContent.innerHTML = '<p class="text-xs text-slate-500 text-center py-8">Loading paid bills...</p>';

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Request failed with status ' + response.status);
                }
                return response.text();
            })
            .then(html => {
                if (requestId !== paidBillsRequestId) {
                    return;
                }
                paidBillsContent.innerHTML = html;
                if (historyMode === 'push') {
                    window.history.pushState(null, '', url);
                } else if (historyMode === 'replace') {
                    window.history.replaceState(null, '', url);
                }
            })
            .catch(error => {
                if (requestId !== paidBillsRequestId) {
                    return;
                }
                console.error('Error loading paid bills:', error);
                paidBillsContent.innerHTML = '<p class="text-xs text-red-600 text-center py-8">Unable to load paid bills. Please try again.</p>';
            });
    };

    const paidBillsUrl = () => {
        const url = new URL(window.location.href);
        const search = paidBillsSearch.value.trim();

        url.searchParams.delete('page');
        if (search) {
            url.searchParams.set('search', search);
        } else {
            url.searchParams.delete('search');
        }

        return url;
    };

    paidBillsSearch.addEventListener('input', function () {
        window.clearTimeout(paidBillsSearchTimer);
        paidBillsSearchTimer = window.setTimeout(() => {
            loadPaidBills(paidBillsUrl().toString());
        }, 300);
    });

    paidBillsContent.addEventListener('click', function (event) {
        const receiptButton = event.target.closest('.open-report-receipt');
        if (receiptButton) {
            openReportReceiptModal(receiptButton.dataset.receiptUrl);
            return;
        }

        const proofButton = event.target.closest('.open-payment-proof');
        if (proofButton) {
            openPaymentProofModal(proofButton.dataset.proofUrl);
            return;
        }

        const link = event.target.closest('a');
        if (!link || link.target === '_blank' || !link.href) {
            return;
        }

        event.preventDefault();
        loadPaidBills(link.href, 'push');
    });

    window.addEventListener('popstate', function () {
        const url = new URL(window.location.href);
        paidBillsSearch.value = url.searchParams.get('search') || '';
        loadPaidBills(url.toString(), false);
    });

    window.openReportReceiptModal = function (url) {
        const modal = document.getElementById('reportReceiptModal');
        document.getElementById('report-receipt-frame').src = url;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    };

    window.printReportReceipt = function () {
        const previewFrame = document.getElementById('report-receipt-frame');
        if (!previewFrame.contentWindow || previewFrame.contentDocument?.readyState !== 'complete') {
            alert('The receipt preview is still loading. Please try again.');
            return;
        }

        previewFrame.contentWindow.focus();
        previewFrame.contentWindow.print();
    };

    window.closeReportReceiptModal = function () {
        const modal = document.getElementById('reportReceiptModal');
        document.getElementById('report-receipt-frame').src = 'about:blank';
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    window.openPaymentProofModal = function (url) {
        document.getElementById('payment-proof-image').src = url;
        const modal = document.getElementById('paymentProofModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    };

    window.closePaymentProofModal = function () {
        document.getElementById('payment-proof-image').src = '';
        const modal = document.getElementById('paymentProofModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeReportReceiptModal();
            closePaymentProofModal();
        }
    });

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
                        {{ $paidBillsCount }},
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