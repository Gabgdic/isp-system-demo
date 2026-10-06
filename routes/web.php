<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SubscriberPortalController;
use App\Http\Controllers\PaymentSubmissionController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\ReportsController;

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
    Route::post('/billing/plans/update/{id}', [BillingController::class, 'updatePlan'])
        ->name('billing.plans.update');
    Route::post('/billing/plans/delete/{id}', [BillingController::class, 'deletePlan'])
        ->name('billing.plans.delete');

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
        
    });
});

// ==========================
// Subscriber Portal Routes
// ==========================
Route::group(['middleware' => [function ($request, $next) {
    if (!session()->has('subscriber_logged_in') || !session()->has('subscriber_id')) {
        return redirect()->route('login')->with('error', 'Please sign in to access your portal.');
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

    // New Profile Update Route
    Route::post('/portal/profile/update', [
        SubscriberPortalController::class,
        'updateProfile'
    ])->name('subscriber.profile.update');

    Route::post('/subscriber/payment/submit', [
        PaymentSubmissionController::class,
        'store'
    ])->name('subscriber.payment.submit');

    Route::post('/subscriber/logout', function (\Illuminate\Http\Request $request) {
        session()->forget(['subscriber_logged_in', 'subscriber_id']);
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Signed out successfully.');
    })->name('subscriber.logout');
});