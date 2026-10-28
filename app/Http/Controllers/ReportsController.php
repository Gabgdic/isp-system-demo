<?php

namespace App\Http\Controllers;

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
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

        $totalRevenue = (clone $approvedPaymentsQuery)->sum('amount_paid');
        $approvedPayments = (clone $approvedPaymentsQuery)->count();
        $pendingPayments = $pendingPaymentsQuery->count();

        $outstandingBalance = (clone $billingMonthQuery)
            ->whereIn('status', ['unpaid', 'overdue', 'pending'])
            ->sum('amount');
        $paidBills = (clone $billingMonthQuery)
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
            'totalRevenue',
            'approvedPayments',
            'pendingPayments',
            'outstandingBalance',
            'paidBills',
            'pendingBills',
            'unpaidBills',
            'trendLabels',
            'trendRevenue',
            'trendOutstanding'
        ));
    }
}