<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
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
        $selectedMonth = null;

        if ($period !== 'overall') {
            if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
                $period = 'overall';
            } else {
                $selectedMonth = Carbon::createFromFormat('!Y-m', $period);

                if ($selectedMonth->greaterThan($currentMonth)) {
                    $period = 'overall';
                    $selectedMonth = null;
                }
            }
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

        $monthOptions = [];
        $monthCursor = $startMonth->copy();
        while ($monthCursor->lessThanOrEqualTo($currentMonth)) {
            $monthOptions[$monthCursor->format('Y-m')] = $monthCursor->format('M Y');
            $monthCursor->addMonth();
        }

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
        }

        $search = trim($request->input('search', ''));
        $paidPaymentRows = PaymentSubmission::with([
            'subscriber',
            'billingRecord',
            'billingAllocations.billingRecord',
        ])
            ->where('status', 'approved')
            ->get()
            ->map(function (PaymentSubmission $payment) use ($selectedMonth, $search) {
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

        $paidBillRows = BillingRecord::with('subscriber')
            ->where('status', 'paid')
            ->whereDoesntHave('paymentAllocations.paymentSubmission', fn ($query) => $query->where('status', 'approved'))
            ->whereDoesntHave('paymentSubmissions', fn ($query) => $query->where('status', 'approved'))
            ->get()
            ->filter(function (BillingRecord $bill) use ($selectedMonth, $search) {
                if ($selectedMonth
                    && ((int) $bill->billing_year !== $selectedMonth->year
                        || (int) $bill->billing_month !== $selectedMonth->month)
                ) {
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

        $paidBillRows = $paidPaymentRows
            ->concat($paidBillRows)
            ->sortByDesc(fn ($row) => $row->paid_at?->timestamp ?? 0)
            ->values();

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

        $chartMonths = $selectedMonth
            ? collect([$selectedMonth->copy()])
            : collect(array_keys($monthOptions))->map(
                fn ($month) => Carbon::createFromFormat('!Y-m', $month)
            );

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
            'search',
            'paidBillRecords',
            'totalRevenue',
            'approvedPayments',
            'pendingPayments',
            'outstandingBalance',
            'paidBillsCount',
            'pendingBills',
            'unpaidBills',
            'trendLabels',
            'trendRevenue',
            'trendOutstanding'
        ));
    }
}