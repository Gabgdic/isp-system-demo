@extends('layouts.app')

@section('title', 'Reports')

@section('content')

<div class="space-y-4 max-w-full mx-auto animate-fadeIn">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-slate-900">Reports</h1>
            <p class="text-xs text-slate-500">Overview of payments, revenue, and billing performance.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button
                type="button"
                onclick="openSmsLogsModal()"
                aria-haspopup="dialog"
                class="px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-xs font-semibold text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400"
            >SMS Notification Logs</button>
            <button
                type="button"
                onclick="openOfficeVisitRequestsModal()"
                aria-haspopup="dialog"
                class="px-3 py-1.5 rounded-lg border border-yellow-500 bg-yellow-400 text-xs font-bold text-gray-950 shadow-sm hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2"
            >Subscriber Requests ({{ $officeVisitRequests->where('status', 'pending')->count() }})</button>
            <form method="GET" action="{{ route('reports') }}">
                <input type="hidden" name="search" value="{{ $search }}">
                <select
                    name="period"
                    onchange="this.form.submit()"
                    aria-label="Filter reports by year or month"
                    class="min-w-[6rem] border border-slate-300 rounded-lg pl-3 pr-8 py-1.5 text-xs font-semibold text-slate-700 bg-white focus:ring-2 focus:ring-slate-300 outline-none"
                >
                    <option value="overall" {{ $period === 'overall' ? 'selected' : '' }}>Overall</option>
                    <optgroup label="Years">
                        @foreach($yearOptions as $year)
                            <option value="{{ $year }}" {{ $period === $year ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Months">
                    @foreach($monthOptions as $monthValue => $monthLabel)
                        <option value="{{ $monthValue }}" {{ $period === $monthValue ? 'selected' : '' }}>{{ $monthLabel }}</option>
                    @endforeach
                    </optgroup>
                </select>
            </form>
        </div>
    </div>

    @if(session('success') || session('warning') || $errors->any())
        <div class="rounded-xl border {{ session('warning') || $errors->any() ? 'border-amber-200 bg-amber-50 text-amber-900' : 'border-emerald-200 bg-emerald-50 text-emerald-800' }} px-4 py-3 text-xs font-semibold">
            {{ session('success') ?? session('warning') }}
            @if($errors->any())
                <ul class="mt-1 list-inside list-disc font-normal">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

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
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-slate-900">Paid Bills</h2>
                    <p class="text-xs text-slate-500">Payments with the bills they cover, paid date, and receipt.</p>
                </div>
                <button
                    type="button"
                    onclick="printPaidBills()"
                    class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold"
                >Print / Save as PDF</button>
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

<iframe
    id="paidBillsPrintFrame"
    title="Printable paid bills report"
    aria-hidden="true"
    class="fixed left-[-10000px] top-0 h-px w-px border-0"
></iframe>

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

<div id="smsLogsModal" class="hidden fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="sms-logs-title" onclick="if (event.target === this) closeSmsLogsModal()">
    <div class="relative bg-white w-full max-w-3xl rounded-2xl shadow-2xl overflow-hidden border border-slate-200 max-h-[85vh] flex flex-col">
        <div class="px-5 py-3.5 border-b border-slate-200 flex items-center justify-between bg-slate-50">
            <div>
                <h3 id="sms-logs-title" class="text-sm font-bold text-slate-900">SMS Notification Logs</h3>
                <p id="sms-logs-period" class="text-xs text-slate-500">Period: {{ $reportPeriodLabel }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="printSmsLogs()" class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold">Print / Save as PDF</button>
                <button type="button" onclick="closeSmsLogsModal()" aria-label="Close SMS logs" class="w-7 h-7 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-600 text-xs">✕</button>
            </div>
        </div>
        <div class="p-4 overflow-y-auto">
            @include('admin.partials.sms_log_rows', ['smsLogs' => $smsLogs])
        </div>
    </div>
</div>

<div id="officeVisitRequestsModal" class="hidden fixed inset-0 z-[100] bg-slate-900/60 backdrop-blur-sm items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="office-visit-requests-title" onclick="if (event.target === this) closeOfficeVisitRequestsModal()">
    <div class="relative bg-white w-full max-w-6xl rounded-xl shadow-2xl overflow-hidden border border-slate-200 max-h-[90vh] flex flex-col">
        <div class="px-4 py-3 border-b border-slate-200 flex items-center justify-between bg-slate-50">
            <div>
                <h3 id="office-visit-requests-title" class="text-sm font-bold text-slate-900">Subscriber Service Requests</h3>
                <p class="text-[11px] text-slate-500">Review requests below. Subscribers are notified by SMS and in their portal.</p>
            </div>
            <button type="button" onclick="closeOfficeVisitRequestsModal()" aria-label="Close subscriber requests" class="w-7 h-7 shrink-0 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-600 text-xs">✕</button>
        </div>
        <div class="overflow-auto">
            @if($officeVisitRequests->isEmpty())
                <p class="px-4 py-8 text-center text-xs text-slate-500">There are no subscriber requests yet.</p>
            @else
                <table class="w-full min-w-[900px] border-collapse text-left text-[11px]">
                    <thead class="sticky top-0 bg-slate-100 text-[10px] font-bold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th scope="col" class="px-3 py-2.5">Subscriber</th>
                            <th scope="col" class="px-3 py-2.5">Request</th>
                            <th scope="col" class="px-3 py-2.5">Visit / repair date</th>
                            <th scope="col" class="px-3 py-2.5">Submitted</th>
                            <th scope="col" class="px-3 py-2.5">Status</th>
                            <th scope="col" class="px-3 py-2.5">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($officeVisitRequests as $officeVisitRequest)
                            <tr class="align-top hover:bg-slate-50">
                                <td class="px-3 py-2.5">
                                    <p class="font-semibold text-slate-900">{{ $officeVisitRequest->subscriber->full_name ?? 'Subscriber unavailable' }}</p>
                                    <p class="mt-0.5 text-[10px] text-slate-500">{{ $officeVisitRequest->subscriber->username ?? 'N/A' }}</p>
                                </td>
                                <td class="whitespace-nowrap px-3 py-2.5 text-slate-700">
                                    {{ $officeVisitRequest->request_type === 'internet_fix' ? 'Internet repair' : 'Payment reduction' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-2.5 font-medium text-slate-700">
                                    {{ $officeVisitRequest->visit_date?->format('M d, Y') ?? 'Needs scheduling' }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-2.5 text-slate-500">{{ $officeVisitRequest->created_at->format('M d, Y g:i A') }}</td>
                                <td class="px-3 py-2.5">
                                    <span class="office-visit-status office-visit-status-{{ $officeVisitRequest->status }}">
                                        {{ ucfirst($officeVisitRequest->status) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2.5">
                                    @if($officeVisitRequest->status === 'pending')
                                        <div class="flex min-w-[330px] flex-wrap items-center gap-1.5">
                                            @if($officeVisitRequest->request_type === 'internet_fix')
                                                <form method="POST" action="{{ route('reports.office-visit-requests.review', $officeVisitRequest) }}" class="flex items-center gap-1.5">
                                                    @csrf
                                                    <input type="hidden" name="action" value="accept">
                                                    <label class="sr-only" for="repair-date-{{ $officeVisitRequest->id }}">Choose repair date</label>
                                                    <input id="repair-date-{{ $officeVisitRequest->id }}" type="date" name="visit_date" min="{{ today()->toDateString() }}" required class="w-32 rounded-md border border-slate-300 px-1.5 py-1 text-[10px] text-slate-700">
                                                    <button type="submit" class="whitespace-nowrap rounded-md bg-emerald-700 px-2 py-1.5 text-[10px] font-bold text-white hover:bg-emerald-800">Schedule fix</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('reports.office-visit-requests.review', $officeVisitRequest) }}">
                                                    @csrf
                                                    <input type="hidden" name="action" value="accept">
                                                    <button type="submit" class="rounded-md bg-emerald-700 px-2 py-1.5 text-[10px] font-bold text-white hover:bg-emerald-800">Accept</button>
                                                </form>
                                            @endif
                                            <form method="POST" action="{{ route('reports.office-visit-requests.review', $officeVisitRequest) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="reject">
                                                <button type="submit" class="rounded-md border border-red-200 bg-white px-2 py-1.5 text-[10px] font-bold text-red-700 hover:bg-red-50">Reject</button>
                                            </form>
                                            @if($officeVisitRequest->request_type !== 'internet_fix')
                                                <form method="POST" action="{{ route('reports.office-visit-requests.review', $officeVisitRequest) }}" class="flex items-center gap-1.5">
                                                    @csrf
                                                    <input type="hidden" name="action" value="reschedule">
                                                    <label class="sr-only" for="visit-date-{{ $officeVisitRequest->id }}">New visit date</label>
                                                    <input id="visit-date-{{ $officeVisitRequest->id }}" type="date" name="visit_date" min="{{ today()->toDateString() }}" required class="w-32 rounded-md border border-slate-300 px-1.5 py-1 text-[10px] text-slate-700">
                                                    <button type="submit" class="whitespace-nowrap rounded-md bg-blue-700 px-2 py-1.5 text-[10px] font-bold text-white hover:bg-blue-800">Move date</button>
                                                </form>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-400">Reviewed</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

<iframe
    id="smsLogsPrintFrame"
    title="Printable SMS notification logs"
    src="{{ route('reports.sms-logs.print', ['period' => $period]) }}"
    aria-hidden="true"
    class="fixed left-[-10000px] top-0 h-px w-px border-0"
></iframe>

<style>
    .office-visit-status {
        display: inline-flex;
        align-items: center;
        border-radius: 9999px;
        padding: 0.2rem 0.5rem;
        font-size: 10px;
        font-weight: 700;
    }

    .office-visit-status-pending {
        background-color: #fef3c7;
        color: #92400e;
    }

    .office-visit-status-accepted {
        background-color: #d1fae5;
        color: #065f46;
    }

    .office-visit-status-rejected {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .office-visit-status-rescheduled {
        background-color: #dbeafe;
        color: #1e40af;
    }

    html.dark .office-visit-status-pending {
        background-color: #352e1b;
        color: #fcd34d;
    }

    html.dark .office-visit-status-accepted {
        background-color: #183126;
        color: #86efac;
    }

    html.dark .office-visit-status-rejected {
        background-color: #352123;
        color: #fca5a5;
    }

    html.dark .office-visit-status-rescheduled {
        background-color: #1e3a5f;
        color: #bfdbfe;
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

    window.openSmsLogsModal = function () {
        const modal = document.getElementById('smsLogsModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    };

    window.closeSmsLogsModal = function () {
        const modal = document.getElementById('smsLogsModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    window.openOfficeVisitRequestsModal = function () {
        const modal = document.getElementById('officeVisitRequestsModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
    };

    window.closeOfficeVisitRequestsModal = function () {
        const modal = document.getElementById('officeVisitRequestsModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    if (new URLSearchParams(window.location.search).get('show_requests') === '1') {
        window.openOfficeVisitRequestsModal();
        const url = new URL(window.location.href);
        url.searchParams.delete('show_requests');
        window.history.replaceState(
            window.history.state,
            '',
            `${url.pathname}${url.search}${url.hash}`
        );
    }

    window.printPaidBills = function () {
        const printFrame = document.getElementById('paidBillsPrintFrame');
        const url = new URL(@json(route('reports.paid-bills.print')), window.location.origin);
        const currentUrl = new URL(window.location.href);
        const period = currentUrl.searchParams.get('period');
        const search = paidBillsSearch.value.trim();

        if (period) {
            url.searchParams.set('period', period);
        }
        if (search) {
            url.searchParams.set('search', search);
        }

        printFrame.onload = function () {
            printFrame.onload = null;
            if (!printFrame.contentWindow || printFrame.contentDocument?.readyState !== 'complete') {
                alert('The paid bills report is still loading. Please try again.');
                return;
            }
            printFrame.contentWindow.focus();
            printFrame.contentWindow.print();
        };
        printFrame.onerror = function () {
            printFrame.onerror = null;
            alert('Unable to load the paid bills report for printing.');
        };
        printFrame.src = url.toString();
    };

    window.printSmsLogs = function () {
        const printFrame = document.getElementById('smsLogsPrintFrame');
        if (!printFrame.contentWindow || printFrame.contentDocument?.readyState !== 'complete') {
            alert('The SMS logs are still loading. Please try again.');
            return;
        }

        printFrame.contentWindow.focus();
        printFrame.contentWindow.print();
    };

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeReportReceiptModal();
            closePaymentProofModal();
            closeSmsLogsModal();
            closeOfficeVisitRequestsModal();
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