<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\SubscriberController;
use Illuminate\Support\Facades\Route;

// Guest Routes
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

// Protected Routes
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    // Dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    // Admin
    Route::get('/admin', [AdminController::class, 'index'])
        ->name('admin');
    
    Route::post('/admin/store', [AdminController::class, 'store'])
        ->name('admin.store');

    Route::put('/admin/update', [AdminController::class, 'update'])
        ->name('admin.update');

    Route::post('/admin/delete/{id}', [AdminController::class, 'destroy'])
        ->name('admin.delete');

    // Subscriber
    Route::get('/subscriber', [SubscriberController::class, 'index'])
        ->name('subscriber');

    Route::post('/subscriber/store', [SubscriberController::class, 'store'])
        ->name('subscriber.store');

    Route::put('/subscriber/update/{id}', [SubscriberController::class, 'update'])
        ->name('subscriber.update');

    Route::post('/subscriber/delete/{id}', [SubscriberController::class, 'destroy'])
        ->name('subscriber.delete');

    // Billing
    Route::get('/billing', function () {
        return view('admin.billing');
    })->name('billing');

    // Reports
    Route::get('/reports', function () {
        return view('admin.reports');
    })->name('reports');

    // Settings
    Route::get('/settings', function () {
        return view('admin.settings');
    })->name('settings');

});