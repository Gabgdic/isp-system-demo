<?php

use App\Models\Subscriber;
use App\Models\User;
use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('admin dashboard displays the redesigned billing and system overview', function () {
    $admin = User::create([
        'username' => 'dashboard-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    Subscriber::create([
        'full_name' => 'Dashboard Subscriber',
        'username' => 'dashboard-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 500,
        'status' => 'active',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Management dashboard')
        ->assertSee('billing overview')
        ->assertSee('Pending review')
        ->assertSee('System overview')
        ->assertSee('Manage subscribers')
        ->assertSee('Estimated monthly recurring revenue')
        ->assertSee(route('subscriber'))
        ->assertDontSee('Review billing')
        ->assertDontSee('At a glance')
        ->assertDontSee('Your network')
        ->assertDontSee('Administrators')
        ->assertDontSee('<svg class="h-5 w-5"', false);
});

test('dashboard verified payments and revenue only include payments approved this month', function () {
    $admin = User::create([
        'username' => 'dashboard-payment-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);
    $subscriber = Subscriber::create([
        'full_name' => 'Dashboard Payment Subscriber',
        'username' => 'dashboard-payment-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 500,
        'status' => 'active',
    ]);

    foreach ([
        ['amount' => 125, 'reviewed_at' => now()->startOfMonth()->addDays(2)],
        ['amount' => 275, 'reviewed_at' => now()->startOfMonth()->addDays(4)],
        ['amount' => 900, 'reviewed_at' => now()->subMonth()->startOfMonth()],
    ] as $index => $paymentData) {
        $bill = BillingRecord::create([
            'subscriber_id' => $subscriber->id,
            'billing_month' => $index + 1,
            'billing_year' => now()->year,
            'amount' => $paymentData['amount'],
            'due_date' => now()->addMonth()->toDateString(),
            'status' => 'paid',
        ]);

        PaymentSubmission::create([
            'billing_record_id' => $bill->id,
            'subscriber_id' => $subscriber->id,
            'amount_paid' => $paymentData['amount'],
            'reference_number' => 'DASHBOARD-VERIFIED-' . $index,
            'payment_screenshot' => 'payment-proofs/dashboard-' . $index . '.jpg',
            'status' => 'approved',
            'reviewed_at' => $paymentData['reviewed_at'],
        ]);
    }

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Verified this month')
        ->assertSee('data-testid="verified-payments-count" class="mt-4 text-3xl font-black tracking-tight text-slate-900">2</p>', false)
        ->assertSee('data-testid="monthly-revenue" class="mt-4 text-2xl font-black tracking-tight text-slate-900">₱400.00</p>', false)
        ->assertDontSee('hover:-translate-y-0.5')
        ->assertDontSee('hover:shadow-md');
});
