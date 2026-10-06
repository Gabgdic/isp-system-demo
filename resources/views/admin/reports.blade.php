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
                class="border border-slate-300 rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white focus:ring-2 focus:ring-slate-300 outline-none"
            >
                <option value="monthly" {{ $period === 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="yearly" {{ $period === 'yearly' ? 'selected' : '' }}>Yearly</option>
            </select>
        </form>
    </div>

    <!-- Top KPI Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Revenue -->
        <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Total Revenue</p>
            <p class="text-xl font-bold text-slate-900 mt-1">₱{{ number_format($totalRevenue, 2) }}</p>
        </div>

        <!-- Approved Payments -->
        <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Approved Payments</p>
            <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($approvedPayments) }}</p>
        </div>

        <!-- Pending -->
        <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Pending Payments</p>
            <p class="text-xl font-bold text-slate-900 mt-1">{{ number_format($pendingPayments) }}</p>
        </div>

        <!-- Outstanding -->
        <div class="bg-white border border-slate-200 rounded-xl p-3 shadow-sm">
            <p class="text-xs font-medium text-slate-500">Outstanding Balance</p>
            <p class="text-xl font-bold text-slate-900 mt-1">₱{{ number_format($outstandingBalance, 2) }}</p>
        </div>
    </div>

    <!-- Main Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-3">

        <!-- Line Chart: Revenue vs Outstanding (2 Columns) -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl p-4 shadow-sm flex flex-col justify-between">
            <div class="mb-2">
                <h2 class="text-sm font-bold text-slate-900">Outstanding vs Revenue</h2>
                <p class="text-xs text-slate-500">Collected revenue vs unpaid balance</p>
            </div>
            <div class="relative h-[200px] w-full">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart: Billing Status (1 Column) -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm flex flex-col justify-between">
            <div class="mb-2">
                <h2 class="text-sm font-bold text-slate-900">Billing Breakdown</h2>
                <p class="text-xs text-slate-500">Distribution of subscriber bills</p>
            </div>
            <div class="relative h-[200px] w-full">
                <canvas id="billingStatusChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Recent Payments Table Section -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-4 py-2.5 border-b border-slate-100 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-900">Recent Approved Payments</h2>
            <span class="text-xs text-slate-500">Total Records: {{ $paidBills + $pendingBills + $unpaidBills }}</span>
        </div>

        @if($recentPayments->count())
            <div class="overflow-x-auto max-h-48 overflow-y-auto">
                <table class="w-full text-xs text-left">
                    <thead class="bg-slate-50 text-slate-500 sticky top-0 bg-white">
                        <tr>
                            <th class="px-4 py-2 font-semibold">Subscriber</th>
                            <th class="px-4 py-2 font-semibold">Billing Period</th>
                            <th class="px-4 py-2 font-semibold">Amount</th>
                            <th class="px-4 py-2 font-semibold">Reference</th>
                            <th class="px-4 py-2 font-semibold">Approved Date</th>
                            <th class="px-4 py-2 font-semibold text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentPayments as $payment)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-2">
                                    <div class="font-medium text-slate-900">{{ $payment->subscriber->full_name }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $payment->subscriber->username }}</div>
                                </td>
                                <td class="px-4 py-2 text-slate-600">
                                    @if($payment->billingRecord)
                                        {{ \Carbon\Carbon::createFromDate($payment->billingRecord->billing_year, $payment->billingRecord->billing_month, 1)->format('M Y') }}
                                    @elseif($payment->billingAllocations && $payment->billingAllocations->count() > 0)
                                        Multiple Months
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-2 font-bold text-slate-900">
                                    ₱{{ number_format($payment->amount_paid, 2) }}
                                </td>
                                <td class="px-4 py-2">
                                    <span class="font-mono text-[10px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-600">
                                        {{ $payment->reference_number }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-slate-500">
                                    {{ optional($payment->reviewed_at)->format('M d, Y h:i A') ?? '—' }}
                                </td>
                                <td class="px-4 py-2 text-center">
                                    <button type="button"
                                        onclick="openSOAModal({{ $payment->subscriber_id }})"
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 px-2.5 py-1 rounded-md border border-blue-100 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                        </svg>
                                        SOA
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-xs text-slate-400">
                                    No approved payments available.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-4 py-6 text-center text-xs text-slate-400">
                No approved payments available.
            </div>
        @endif
    </div>

</div>

<!-- STATEMENT OF ACCOUNT (SOA) MODAL -->
<div
    id="soaModal"
    class="fixed inset-0 z-[100] hidden overflow-y-auto"
    aria-labelledby="soaModalTitle"
    role="dialog"
    aria-modal="true"
>
    <div
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm transition-opacity no-print"
        onclick="closeSOAModal()"
    ></div>

    <div class="relative min-h-screen flex items-center justify-center p-2 sm:p-4">
        <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl p-6 sm:p-8 border border-slate-200 printable-soa my-8">
            
            <!-- Action Bar (Hidden when printing) -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-4 mb-6 no-print">
                <button
                    type="button"
                    onclick="window.print()"
                    class="inline-flex items-center gap-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 px-4 py-2 rounded-lg shadow-sm transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print Statement
                </button>

                <button
                    type="button"
                    onclick="closeSOAModal()"
                    class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center transition"
                >
                    ✕
                </button>
            </div>

            <!-- DOCUMENT HEADER -->
            <div class="text-center mb-6 pb-4 border-b border-slate-200">
                <h1 class="text-xl font-black tracking-wider uppercase text-slate-900">
                    {{ $settings->system_name ?? 'KRNDS Network Services' }}
                </h1>
                <p class="text-xs text-slate-500 font-semibold tracking-widest uppercase mt-0.5">
                    Statement of Account
                </p>
            </div>

            <!-- BILL TO & ACCOUNT SUMMARY -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                <div class="border border-slate-200 rounded-lg p-3.5 bg-slate-50/50">
                    <h2 class="text-[11px] font-black uppercase text-slate-400 tracking-wider mb-2">
                        BILL TO:
                    </h2>
                    <div class="space-y-1 text-xs">
                        <p class="text-slate-500">
                            Client Name: <span id="soaFullName" class="font-bold text-slate-900">—</span>
                        </p>
                        <p class="text-slate-500">
                            Address: <span id="soaAddress" class="font-semibold text-slate-800">—</span>
                        </p>
                    </div>
                </div>

                <div class="border border-slate-200 rounded-lg p-3.5 bg-slate-50/50">
                    <h2 class="text-[11px] font-black uppercase text-slate-400 tracking-wider mb-2">
                        ACCOUNT SUMMARY
                    </h2>
                    <div class="space-y-1 text-xs">
                        <p class="text-slate-500">
                            Plan Subscription: <span id="soaPlanName" class="font-bold text-slate-900">—</span>
                        </p>
                        <p class="text-slate-500">
                            Plan Amount: <span id="soaMonthlyFee" class="font-bold text-slate-900">—</span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- BILLING TABLE -->
            <div class="overflow-hidden border border-slate-200 rounded-lg mb-6">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100 border-b border-slate-200 text-slate-700 uppercase tracking-wider font-bold text-[11px]">
                            <th class="py-2.5 px-4">Billing Period</th>
                            <th class="py-2.5 px-4">Due Date</th>
                            <th class="py-2.5 px-4 text-right">Amount Due</th>
                        </tr>
                    </thead>
                    <tbody id="soaBillsTableBody" class="divide-y divide-slate-200 text-slate-800 font-medium">
                        <!-- Dynamic bill rows inserted via JS -->
                    </tbody>
                    <tfoot>
                        <tr class="bg-slate-50 font-black text-xs text-slate-900 border-t-2 border-slate-300">
                            <td colspan="2" class="py-3 px-4 text-right uppercase tracking-wider">TOTAL OUTSTANDING</td>
                            <td id="soaTotalOutstanding" class="py-3 px-4 text-right text-sm">
                                Php 0.00
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- REMINDER STATEMENT -->
            <div class="mb-8 space-y-2 text-xs text-slate-600 leading-relaxed text-justify">
                <p>
                    You are hereby reminded of your monthly subscription due for the billing period stated above. We humbly request that payment be settled on or before the due date to avoid any possible service interruption.
                </p>
                <p>
                    If payment has already been made before receiving this notice, please disregard this billing statement. We truly appreciate your prompt payment and continued support.
                </p>
            </div>

            <!-- SIGNATORY -->
            <div class="mt-8 pt-4 flex justify-between items-end">
                <div>
                    <p class="text-xs font-semibold text-slate-500 mb-1">Authorized Signatory:</p>
                    <div class="relative min-h-[50px] flex items-end">
                        @if(isset($settings->authorized_signature) && $settings->authorized_signature)
                            <img src="{{ asset('storage/' . $settings->authorized_signature) }}" alt="Signature" class="h-14 object-contain mb-1">
                        @else
                            <span class="text-xs font-bold text-slate-400 italic mb-1">(SGD)</span>
                        @endif
                    </div>
                    <p class="text-sm font-black text-slate-900 uppercase">
                        {{ $settings->authorized_signatory ?? 'JURISH A. SY' }}
                    </p>
                    <p class="text-xs font-medium text-slate-500">
                        {{ $settings->signatory_title ?? 'Proprietor' }}
                    </p>
                </div>
            </div>

            <!-- FOOTER -->
            <div class="mt-10 border-t border-slate-200 pt-4 text-center">
                <p class="text-xs font-bold text-slate-700 tracking-wide">
                    Thank you for being a valued subscriber of {{ $settings->system_name ?? 'KRNDS Network Services' }}!
                </p>
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

    @media print {
        body * {
            visibility: hidden !important;
        }
        .printable-soa, .printable-soa * {
            visibility: visible !important;
        }
        .printable-soa {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 100% !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .no-print {
            display: none !important;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const subscribersData = @json($subscribers);

function openSOAModal(subscriberId) {
    const subscriber = subscribersData.find(s => s.id === subscriberId);
    if (!subscriber) return;

    document.getElementById('soaFullName').textContent = subscriber.full_name || 'N/A';
    document.getElementById('soaAddress').textContent = subscriber.address || 'N/A';
    document.getElementById('soaPlanName').textContent = subscriber.plan_name || 'N/A';
    document.getElementById('soaMonthlyFee').textContent = 'Php ' + Number(subscriber.monthly_fee || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const tableBody = document.getElementById('soaBillsTableBody');
    tableBody.innerHTML = '';

    const unpaidBills = (subscriber.billing_records || []).filter(b => ['unpaid', 'overdue', 'pending'].includes(b.status.toLowerCase()));

    let totalOutstanding = 0;

    if (unpaidBills.length > 0) {
        unpaidBills.forEach(bill => {
            const amount = parseFloat(bill.amount) || 0;
            totalOutstanding += amount;

            const date = new Date(bill.billing_year, bill.billing_month - 1, 1);
            const periodStr = date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
            const dueDateStr = bill.due_date ? new Date(bill.due_date).toLocaleDateString('en-US', { month: 'long', day: '2-digit', year: 'numeric' }) : 'N/A';

            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="py-2.5 px-4 font-semibold">${periodStr}</td>
                <td class="py-2.5 px-4 text-slate-600">${dueDateStr}</td>
                <td class="py-2.5 px-4 text-right font-bold">Php ${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
            `;
            tableBody.appendChild(row);
        });
    } else {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td colspan="3" class="py-4 px-4 text-center text-slate-400">
                No outstanding bills at this time.
            </td>
        `;
        tableBody.appendChild(row);
    }

    document.getElementById('soaTotalOutstanding').textContent = 'Php ' + totalOutstanding.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const modal = document.getElementById('soaModal');
    if (modal) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }
}

function closeSOAModal() {
    const modal = document.getElementById('soaModal');
    if (modal) {
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeSOAModal();
    }
});

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