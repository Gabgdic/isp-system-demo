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
    Route::get('/billing', [BillingController::class, 'index'])
        ->name('billing');
    Route::post('/billing/payment/{id}/approve', [BillingController::class, 'approve'])
        ->name('billing.payment.approve');
    Route::post('/billing/payment/{id}/reject', [BillingController::class, 'reject'])
        ->name('billing.payment.reject');

    // Reports
    Route::get('/reports', function () {
        return view('admin.reports');
    })->name('reports');


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
        Route::post('/settings/plans/store', [SettingsController::class, 'storePlan'])->name('settings.plans.store');
        Route::put('/settings/plans/update/{id}', [SettingsController::class, 'updatePlan'])->name('settings.plans.update');
        Route::post('/settings/plans/delete/{id}', [SettingsController::class, 'deletePlan'])->name('settings.plans.delete');
        
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
