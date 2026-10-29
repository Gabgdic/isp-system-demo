<?php

namespace App\Http\Controllers;

use App\Models\Subscriber;
use App\Models\SubscriptionPlan;
use App\Models\SystemSetting;
use App\Models\BillingRecord;
use App\Models\PaymentSubmission;

class DashboardController extends Controller
{
    public function index()
    {
        $totalSubscribers = Subscriber::count();
        $activeSubscribers = Subscriber::where('status', 'active')->count();
        $inactiveSubscribers = Subscriber::where('status', 'inactive')->count();
        $disconnectedSubscribers = Subscriber::where('status', 'disconnected')->count();

        // Count all available subscription plans without status filtering
        $activePlans = SubscriptionPlan::count();
        $estimatedMonthlyRevenue = Subscriber::where('status', 'active')->sum('monthly_fee');

        $thisMonthPaidBills = BillingRecord::where('billing_year', now()->year)
            ->where('billing_month', now()->month)
            ->where('status', 'paid')
            ->count();
        $thisMonthUnpaidBills = BillingRecord::where('billing_year', now()->year)
            ->where('billing_month', now()->month)
            ->whereIn('status', ['unpaid', 'overdue'])
            ->count();
        $pendingVerifications = PaymentSubmission::where('status', 'pending')->count();
        $verifiedPayments = PaymentSubmission::where('status', 'approved')->count();
        $thisMonthRevenue = PaymentSubmission::where('status', 'approved')
            ->whereYear('reviewed_at', now()->year)
            ->whereMonth('reviewed_at', now()->month)
            ->sum('amount_paid');

        $systemSetting = SystemSetting::first();

        return view('admin.dashboard', compact(
            'totalSubscribers',
            'activeSubscribers',
            'inactiveSubscribers',
            'disconnectedSubscribers',
            'activePlans',
            'estimatedMonthlyRevenue',
            'thisMonthPaidBills',
            'thisMonthUnpaidBills',
            'pendingVerifications',
            'verifiedPayments',
            'thisMonthRevenue',
            'systemSetting'
        ));
    }
}