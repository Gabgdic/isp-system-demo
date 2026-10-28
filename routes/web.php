<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\SubscriberPortalController;
use App\Http\Controllers\PaymentSubmissionController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ReportsController;
use App\Models\Subscriber;
use Illuminate\Http\Request;

// Guest Routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Protected Routes
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Subscriber
    Route::get('/subscriber', [SubscriberController::class, 'index'])->name('subscriber');
    Route::post('/subscriber/store', [SubscriberController::class, 'store'])->name('subscriber.store');
    Route::put('/subscriber/update/{id}', [SubscriberController::class, 'update'])->name('subscriber.update');
    Route::post('/subscriber/{id}/record-past-due-payment', [SubscriberController::class, 'recordPastDuePayment'])->name('subscriber.record-past-due-payment');
    Route::post('/subscriber/{id}/reactivate', [SubscriberController::class, 'reactivate'])->name('subscriber.reactivate');
    Route::post('/subscriber/delete/{id}', [SubscriberController::class, 'destroy'])->name('subscriber.delete');

    // Billing
    Route::get('/billing', [BillingController::class, 'index'])->name('billing');

    // SOA and OR Modal
    Route::get('/billing/soa/preview/{subscriberId}', [BillingController::class, 'soaPreview'])
        ->name('billing.soa.preview');
    Route::get('/billing/soa/download/{subscriberId}', [BillingController::class, 'soaDownload'])
        ->name('billing.soa.download');
    Route::get('/billing/receipt/preview/{billingRecord}', [BillingController::class, 'previewReceipt'])
        ->name('billing.receipt.preview');

    Route::post('/billing/payment/approve/{id}', [BillingController::class, 'approve'])
        ->name('billing.payment.approve');
    Route::post('/billing/payment/reject/{id}', [BillingController::class, 'reject'])
        ->name('billing.payment.reject');
    Route::post('/billing/plans/store', [BillingController::class, 'storePlan'])
        ->name('billing.plans.store');
    Route::put('/billing/plans/update/{id}', [BillingController::class, 'updatePlan'])
        ->name('billing.plans.update');
    Route::post('/billing/plans/delete/{id}', [BillingController::class, 'deletePlan'])
        ->name('billing.plans.delete');

    // Manual Trigger Route for Monthly Billing & Status Recalculation
    Route::post('/billing/generate-monthly', function () {
        Artisan::call('billing:generate-monthly');
        return back()->with('success', 'Monthly billing generated and subscriber statuses updated successfully!');
    })->name('billing.generate.monthly');

    // Reports
    Route::get('/reports', [ReportsController::class, 'index'])
        ->name('reports');
    Route::get('/reports/soa/{subscriber_id}', [ReportsController::class, 'generateSOA'])
        ->name('reports.soa');


    // ==================
    // Super Admin Only
    // ==================
    Route::group(['middleware' => [function ($request, $next) {
        if (auth()->user()?->role !== 'super_admin') {
            abort(403, 'Unauthorized action. Super Admin access required.');
        }
        return $next($request);
    }]], function () {

        // Admin
        Route::get('/admin', [AdminController::class, 'index'])->name('admin');
        Route::post('/admin/store', [AdminController::class, 'store'])->name('admin.store');
        Route::put('/admin/update', [AdminController::class, 'update'])->name('admin.update');
        Route::put('/admin/update-role/{id}', [AdminController::class, 'updateRole'])->name('admin.updateRole');
        Route::post('/admin/delete/{id}', [AdminController::class, 'destroy'])->name('admin.delete');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
        Route::post('/settings/system/update', [SettingsController::class, 'updateSystem'])->name('settings.system.update');
        Route::post('/admin/settings/soa', [SettingsController::class, 'updateSoa'])->name('settings.soa.update');
        
    });
});

// ==========================
// Subscriber Portal Routes
// ==========================
Route::group(['middleware' => [function ($request, $next) {
    if (!session()->has('subscriber_logged_in') || !session()->has('subscriber_id')) {
        return redirect()->route('login')->with('error', 'Please sign in to access your portal.');
    }

    $subscriber = Subscriber::find(session('subscriber_id'));

    if (!$subscriber) {
        session()->forget(['subscriber_logged_in', 'subscriber_id']);
        return redirect()->route('login')->with('error', 'Please sign in to access your portal.');
    }

    $subscriber->syncOverdueStatus();

    if ($subscriber->status === 'disconnected') {
        session()->forget(['subscriber_logged_in', 'subscriber_id']);
        return redirect()->route('login')->with('error', 'Your account is disconnected. Please visit the administrator after paying your overdue balance.');
    }

    return $next($request);
}]], function () {

    Route::get('/portal', [
        SubscriberPortalController::class,
        'index'
    ])->name('subscriber.portal');

    // New SOA Route for Subscribers
    Route::get('/portal/soa', [
        SubscriberPortalController::class,
        'generateSOA'
    ])->name('subscriber.soa');

    // Profile Update Routes 
    Route::post('/portal/profile/update', [
        SubscriberPortalController::class,
        'updateProfile'
    ])->name('subscriber.profile.update');

    Route::get('/portal/profile/update', function () {
        return redirect()->route('subscriber.portal');
    });

    Route::post('/subscriber/payment/submit', [
        PaymentSubmissionController::class,
        'store'
    ])->name('subscriber.payment.submit');

    Route::post('/subscriber/logout', function (Request $request) {
        session()->forget(['subscriber_logged_in', 'subscriber_id']);
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Signed out successfully.');
    })->name('subscriber.logout');
});