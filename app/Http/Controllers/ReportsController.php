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
        $period = $request->get('period', 'monthly');

        if (!in_array($period, ['monthly', 'yearly'])) {
            $period = 'monthly';
        }

        $totalRevenue = PaymentSubmission::where(
            'status',
            'approved'
        )->sum('amount_paid');

        $approvedPayments = PaymentSubmission::where(
            'status',
            'approved'
        )->count();

        $pendingPayments = PaymentSubmission::where(
            'status',
            'pending'
        )->count();

        $outstandingBalance = BillingRecord::whereIn('status', [
            'unpaid',
            'overdue',
            'pending',
        ])->sum('amount');

        $paidBills = BillingRecord::where(
            'status',
            'paid'
        )->count();

        $pendingBills = BillingRecord::where(
            'status',
            'pending'
        )->count();

        $unpaidBills = BillingRecord::whereIn('status', [
            'unpaid',
            'overdue',
        ])->count();

        $trendLabels = [];
        $trendRevenue = [];
        $trendOutstanding = [];

        if ($period === 'monthly') {
            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()
                    ->startOfMonth()
                    ->subMonths($i);

                $trendLabels[] = $date->format('M Y');

                $revenue = PaymentSubmission::where(
                        'status',
                        'approved'
                    )
                    ->whereYear(
                        'reviewed_at',
                        $date->year
                    )
                    ->whereMonth(
                        'reviewed_at',
                        $date->month
                    )
                    ->sum('amount_paid');

                $outstanding = BillingRecord::where(
                        'billing_year',
                        $date->year
                    )
                    ->where(
                        'billing_month',
                        $date->month
                    )
                    ->whereIn('status', [
                        'unpaid',
                        'overdue',
                        'pending',
                    ])
                    ->sum('amount');

                $trendRevenue[] = (float) $revenue;
                $trendOutstanding[] = (float) $outstanding;
            }
        } else {
            for ($i = 4; $i >= 0; $i--) {
                $year = now()->year - $i;

                $trendLabels[] = (string) $year;

                $revenue = PaymentSubmission::where(
                        'status',
                        'approved'
                    )
                    ->whereYear('reviewed_at', $year)
                    ->sum('amount_paid');

                $outstanding = BillingRecord::where(
                        'billing_year',
                        $year
                    )
                    ->whereIn('status', [
                        'unpaid',
                        'overdue',
                        'pending',
                    ])
                    ->sum('amount');

                $trendRevenue[] = (float) $revenue;
                $trendOutstanding[] = (float) $outstanding;
            }
        }

        // Updated to load both billingRecord and billingAllocations.billingRecord
        $recentPayments = PaymentSubmission::with([
            'subscriber',
            'billingRecord',
            'billingAllocations.billingRecord',
        ])
            ->where('status', 'approved')
            ->latest('reviewed_at')
            ->take(5)
            ->get();

        return view('admin.reports', compact(
            'period',
            'totalRevenue',
            'approvedPayments',
            'pendingPayments',
            'outstandingBalance',
            'paidBills',
            'pendingBills',
            'unpaidBills',
            'trendLabels',
            'trendRevenue',
            'trendOutstanding',
            'recentPayments'
        ));
    }
}