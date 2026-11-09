<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use App\Models\SmsLog;
use App\Models\SubscriberOfficeVisitRequest;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $currentMonth = now()->startOfMonth();
        $period = $request->input('period', 'overall');
        if (!is_string($period)) {
            $period = 'overall';
        }
        $selectedMonth = $this->resolveSelectedMonth($period);
        $selectedYear = $this->resolveSelectedYear($period);
        if ($period !== 'overall' && $selectedMonth === null && $selectedYear === null) {
            $period = 'overall';
        }

        $startMonth = $currentMonth->copy();
        $firstBillingRecord = BillingRecord::orderBy('billing_year')
            ->orderBy('billing_month')
            ->first(['billing_year', 'billing_month']);

        if ($firstBillingRecord) {
            $billingStart = Carbon::create(
                $firstBillingRecord->billing_year,
                $firstBillingRecord->billing_month,
                1
            )->startOfMonth();

            if ($billingStart->lessThan($startMonth)) {
                $startMonth = $billingStart;
            }
        }

        $firstSubmissionDate = PaymentSubmission::min('created_at');
        if ($firstSubmissionDate) {
            $submissionStart = Carbon::parse($firstSubmissionDate)->startOfMonth();

            if ($submissionStart->lessThan($startMonth)) {
                $startMonth = $submissionStart;
            }
        }

        $firstSmsLogCreatedAt = SmsLog::min('created_at');
        $firstSmsLogSentAt = SmsLog::whereNotNull('sent_at')->min('sent_at');
        foreach ([$firstSmsLogCreatedAt, $firstSmsLogSentAt] as $firstSmsLogDate) {
            if (!$firstSmsLogDate) {
                continue;
            }

            $smsLogStart = Carbon::parse($firstSmsLogDate)->startOfMonth();

            if ($smsLogStart->lessThan($startMonth)) {
                $startMonth = $smsLogStart;
            }
        }

        $monthOptions = [];
        $monthCursor = $startMonth->copy();
        while ($monthCursor->lessThanOrEqualTo($currentMonth)) {
            $monthOptions[$monthCursor->format('Y-m')] = $monthCursor->format('M Y');
            $monthCursor->addMonth();
        }
        $yearOptions = collect(array_keys($monthOptions))
            ->map(fn (string $month): string => substr($month, 0, 4))
            ->unique()
            ->sortDesc()
            ->values();

        $approvedPaymentsQuery = PaymentSubmission::where('status', 'approved');
        $pendingPaymentsQuery = PaymentSubmission::where('status', 'pending');
        $billingMonthQuery = BillingRecord::query();
        if ($selectedMonth) {
            $approvedPaymentsQuery
                ->whereYear('reviewed_at', $selectedMonth->year)
                ->whereMonth('reviewed_at', $selectedMonth->month);
            $pendingPaymentsQuery
                ->whereYear('created_at', $selectedMonth->year)
                ->whereMonth('created_at', $selectedMonth->month);
            $billingMonthQuery
                ->where('billing_year', $selectedMonth->year)
                ->where('billing_month', $selectedMonth->month);
        } elseif ($selectedYear) {
            $approvedPaymentsQuery->whereYear('reviewed_at', $selectedYear);
            $pendingPaymentsQuery->whereYear('created_at', $selectedYear);
            $billingMonthQuery->where('billing_year', $selectedYear);
        }
        $smsLogs = $this->smsLogsForPeriod($selectedMonth, $selectedYear);
        $officeVisitRequests = SubscriberOfficeVisitRequest::with('subscriber')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get();
        $reportPeriodLabel = $selectedMonth?->format('F Y') ?? ($selectedYear ?? 'Overall');

        $search = trim($request->input('search', ''));
        $paidBillRows = $this->paidBillRowsForPeriod($selectedMonth, $selectedYear, $search);

        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $paidBillRecords = new LengthAwarePaginator(
            $paidBillRows->forPage($currentPage, $perPage)->values(),
            $paidBillRows->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($request->ajax()) {
            return view('admin.partials.paid_bills_table', compact('paidBillRecords'))->render();
        }

        $totalRevenue = (clone $approvedPaymentsQuery)->sum('amount_paid');
        $approvedPayments = (clone $approvedPaymentsQuery)->count();
        $pendingPayments = $pendingPaymentsQuery->count();

        $outstandingBalance = (clone $billingMonthQuery)
            ->whereIn('status', ['unpaid', 'overdue', 'pending'])
            ->sum('amount');
        $paidBillsCount = (clone $billingMonthQuery)
            ->where('status', 'paid')
            ->count();
        $pendingBills = (clone $billingMonthQuery)
            ->where('status', 'pending')
            ->count();
        $unpaidBills = (clone $billingMonthQuery)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->count();

        if ($selectedMonth) {
            $chartMonths = collect([$selectedMonth->copy()]);
        } elseif ($selectedYear) {
            $lastMonth = $selectedYear === now()->year ? now()->month : 12;
            $chartMonths = collect(range(1, $lastMonth))
                ->map(fn (int $month) => Carbon::create($selectedYear, $month, 1));
        } else {
            $chartMonths = collect(array_keys($monthOptions))->map(
                fn ($month) => Carbon::createFromFormat('!Y-m', $month)
            );
        }

        $trendLabels = [];
        $trendRevenue = [];
        $trendOutstanding = [];

        foreach ($chartMonths as $month) {
            $trendLabels[] = $month->format('M Y');
            $trendRevenue[] = (float) PaymentSubmission::where('status', 'approved')
                ->whereYear('reviewed_at', $month->year)
                ->whereMonth('reviewed_at', $month->month)
                ->sum('amount_paid');
            $trendOutstanding[] = (float) BillingRecord::where('billing_year', $month->year)
                ->where('billing_month', $month->month)
                ->whereIn('status', ['unpaid', 'overdue', 'pending'])
                ->sum('amount');
        }

        return view('admin.reports', compact(
            'period',
            'monthOptions',
            'yearOptions',
            'search',
            'paidBillRecords',
            'totalRevenue',
            'approvedPayments',
            'pendingPayments',
            'outstandingBalance',
            'paidBillsCount',
            'pendingBills',
            'unpaidBills',
            'smsLogs',
            'officeVisitRequests',
            'reportPeriodLabel',
            'trendLabels',
            'trendRevenue',
            'trendOutstanding'
        ));
    }

    public function printSmsLogs(Request $request)
    {
        $period = $request->input('period', 'overall');
        if (!is_string($period)) {
            $period = 'overall';
        }
        $selectedMonth = $this->resolveSelectedMonth($period);
        $selectedYear = $this->resolveSelectedYear($period);
        $reportPeriodLabel = $selectedMonth?->format('F Y') ?? ($selectedYear ?? 'Overall');
        $smsLogs = $this->smsLogsForPeriod($selectedMonth, $selectedYear);

        return view('admin.partials.sms_logs_print', compact('smsLogs', 'reportPeriodLabel'));
    }

    public function printPaidBills(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $period = $request->input('period', 'overall');
        if (!is_string($period)) {
            $period = 'overall';
        }

        $selectedMonth = $this->resolveSelectedMonth($period);
        $selectedYear = $this->resolveSelectedYear($period);
        $reportPeriodLabel = $selectedMonth?->format('F Y') ?? ($selectedYear ?? 'Overall');
        $search = trim($request->input('search', ''));
        $paidBillRows = $this->paidBillRowsForPeriod($selectedMonth, $selectedYear, $search);

        return view('admin.partials.paid_bills_print', compact('paidBillRows', 'reportPeriodLabel'));
    }

    private function resolveSelectedMonth(string $period): ?Carbon
    {
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
            return null;
        }

        $selectedMonth = Carbon::createFromFormat('!Y-m', $period);

        return $selectedMonth->greaterThan(now()->startOfMonth())
            ? null
            : $selectedMonth;
    }

    private function resolveSelectedYear(string $period): ?int
    {
        if (!preg_match('/^\d{4}$/', $period)) {
            return null;
        }

        $selectedYear = (int) $period;

        return $selectedYear <= now()->year ? $selectedYear : null;
    }

    private function smsLogsForPeriod(?Carbon $selectedMonth, ?int $selectedYear)
    {
        $query = SmsLog::with('subscriber')
            ->orderByDesc('sent_at')
            ->orderByDesc('created_at');

        if ($selectedMonth || $selectedYear) {
            $periodStart = $selectedMonth
                ? $selectedMonth->copy()->startOfMonth()
                : Carbon::create($selectedYear, 1, 1)->startOfYear();
            $periodEnd = $selectedMonth
                ? $selectedMonth->copy()->endOfMonth()
                : Carbon::create($selectedYear, 12, 31)->endOfDay();

            $query->where(function ($query) use ($periodStart, $periodEnd) {
                $query->whereBetween('sent_at', [$periodStart, $periodEnd])
                    ->orWhere(function ($query) use ($periodStart, $periodEnd) {
                        $query->whereNull('sent_at')
                            ->whereBetween('created_at', [$periodStart, $periodEnd]);
                    });
            });
        }

        return $query->get();
    }

    private function paidBillRowsForPeriod(?Carbon $selectedMonth, ?int $selectedYear, string $search)
    {
        $paidPaymentRows = PaymentSubmission::with([
            'subscriber',
            'billingRecord',
            'billingAllocations.billingRecord',
        ])
            ->where('status', 'approved')
            ->get()
            ->map(function (PaymentSubmission $payment) use ($selectedMonth, $selectedYear, $search) {
                $bills = $payment->billingAllocations
                    ->filter(fn ($allocation) => $allocation->billingRecord?->status === 'paid')
                    ->map(fn ($allocation) => (object) [
                        'bill' => $allocation->billingRecord,
                        'amount' => (float) $allocation->amount_applied,
                    ])
                    ->values();

                if ($bills->isEmpty() && $payment->billingRecord?->status === 'paid') {
                    $bills->push((object) [
                        'bill' => $payment->billingRecord,
                        'amount' => (float) $payment->billingRecord->amount,
                    ]);
                }

                if ($bills->isEmpty()) {
                    return null;
                }

                if ($selectedMonth && !$bills->contains(fn ($item) =>
                    (int) $item->bill->billing_year === $selectedMonth->year
                    && (int) $item->bill->billing_month === $selectedMonth->month
                )) {
                    return null;
                }
                if ($selectedYear && !$bills->contains(fn ($item) =>
                    (int) $item->bill->billing_year === $selectedYear
                )) {
                    return null;
                }

                $periods = $bills->map(fn ($item) => Carbon::create(
                    $item->bill->billing_year,
                    $item->bill->billing_month,
                    1
                )->format('M Y'))->unique()->implode(', ');
                $subscriber = $payment->subscriber;
                $searchable = implode(' ', [
                    $subscriber?->full_name,
                    $subscriber?->username,
                    $payment->reference_number,
                    number_format((float) $payment->amount_paid, 2, '.', ''),
                    $periods,
                ]);

                if ($search !== '' && stripos($searchable, $search) === false) {
                    return null;
                }

                return (object) [
                    'payment' => $payment,
                    'subscriber' => $subscriber,
                    'bills' => $bills,
                    'amount' => (float) $payment->amount_paid,
                    'paid_at' => $payment->reviewed_at
                        ?? $bills->map(fn ($item) => $item->bill->paid_at)->filter()->max(),
                    'periods' => $periods,
                ];
            })
            ->filter()
            ->values();

        $legacyPaidRows = BillingRecord::with('subscriber')
            ->where('status', 'paid')
            ->whereDoesntHave('paymentAllocations.paymentSubmission', fn ($query) => $query->where('status', 'approved'))
            ->whereDoesntHave('paymentSubmissions', fn ($query) => $query->where('status', 'approved'))
            ->get()
            ->filter(function (BillingRecord $bill) use ($selectedMonth, $selectedYear, $search) {
                if ($selectedMonth
                    && ((int) $bill->billing_year !== $selectedMonth->year
                        || (int) $bill->billing_month !== $selectedMonth->month)
                ) {
                    return false;
                }
                if ($selectedYear && (int) $bill->billing_year !== $selectedYear) {
                    return false;
                }

                $period = Carbon::create($bill->billing_year, $bill->billing_month, 1)->format('M Y');
                $searchable = implode(' ', [
                    $bill->subscriber?->full_name,
                    $bill->subscriber?->username,
                    $bill->amount,
                    $period,
                ]);

                return $search === '' || stripos($searchable, $search) !== false;
            })
            ->map(function (BillingRecord $bill) {
                return (object) [
                    'payment' => null,
                    'subscriber' => $bill->subscriber,
                    'bills' => collect([(object) ['bill' => $bill, 'amount' => (float) $bill->amount]]),
                    'amount' => (float) $bill->amount,
                    'paid_at' => $bill->paid_at,
                    'periods' => Carbon::create($bill->billing_year, $bill->billing_month, 1)->format('M Y'),
                    'legacy_bill' => $bill,
                ];
            });

        return $paidPaymentRows
            ->concat($legacyPaidRows)
            ->sortByDesc(fn ($row) => $row->paid_at?->timestamp ?? 0)
            ->values();
    }
}