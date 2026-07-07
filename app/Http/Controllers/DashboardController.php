<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Subscriber;
use App\Models\SubscriptionPlan;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAdmins = User::where('role', 'admin')->count();

        $totalSubscribers = Subscriber::count();
        $activeSubscribers = Subscriber::where('status', 'active')->count();
        $inactiveSubscribers = Subscriber::where('status', 'inactive')->count();
        $disconnectedSubscribers = Subscriber::where('status', 'disconnected')->count();

        $activePlans = SubscriptionPlan::where('status', 'active')->count();
        $estimatedMonthlyRevenue = Subscriber::where('status', 'active')->sum('monthly_fee');

        $recentSubscribers = Subscriber::latest()->take(5)->get();
        $plans = SubscriptionPlan::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'totalAdmins',
            'totalSubscribers',
            'activeSubscribers',
            'inactiveSubscribers',
            'disconnectedSubscribers',
            'activePlans',
            'estimatedMonthlyRevenue',
            'recentSubscribers',
            'plans',
        ));
    }
}