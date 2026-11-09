<?php

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use App\Models\Subscriber;
use App\Models\SubscriberDisconnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

$createSubscriberWithPastDueBills = function (string $status = 'active', string $billStatus = 'unpaid') {
    $subscriber = Subscriber::create([
        'full_name' => 'Test Subscriber',
        'username' => 'test-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 100,
        'status' => $status,
    ]);

    foreach ([now()->subMonths(2), now()->subMonth()] as $date) {
        $billingMonth = $date->copy()->startOfMonth();

        BillingRecord::create([
            'subscriber_id' => $subscriber->id,
            'billing_month' => $billingMonth->month,
            'billing_year' => $billingMonth->year,
            'amount' => 100,
            'due_date' => $billingMonth->copy()->endOfMonth()->toDateString(),
            'status' => $billStatus,
        ]);
    }

    return $subscriber;
};

test('monthly billing disconnects subscribers with two overdue bills and skips a new bill', function () use ($createSubscriberWithPastDueBills) {
    $subscriber = $createSubscriberWithPastDueBills();

    Artisan::call('billing:generate-monthly');

    expect($subscriber->fresh()->status)->toBe('disconnected');
    expect($subscriber->fresh()->disconnected_at)->not->toBeNull();
    expect(SubscriberDisconnection::where('subscriber_id', $subscriber->id)->count())->toBe(1);
    expect(BillingRecord::where('subscriber_id', $subscriber->id)
        ->where('billing_month', now()->month)
        ->where('billing_year', now()->year)
        ->exists())->toBeFalse();
});

test('disconnected subscribers cannot log in or continue using an existing portal session', function () use ($createSubscriberWithPastDueBills) {
    $subscriber = $createSubscriberWithPastDueBills();

    $loginResponse = $this->post(route('login.post'), [
        'username' => $subscriber->username,
        'password' => 'Password123!',
    ]);

    $loginResponse->assertRedirect(route('login'))
        ->assertSessionHas('error', "Your account has been disconnected due to overdue payments. Please visit your Internet Provider's Office to settle your outstanding balance and request reactivation.");
    $loginResponse->assertSessionMissing('subscriber_logged_in');
    expect($subscriber->fresh()->status)->toBe('disconnected');

    $portalResponse = $this->withSession([
        'subscriber_logged_in' => true,
        'subscriber_id' => $subscriber->id,
    ])->get(route('subscriber.portal'));

    $portalResponse->assertRedirect(route('login'))
        ->assertSessionHas('error', "Your account has been disconnected due to overdue payments. Please visit your Internet Provider's Office to settle your outstanding balance and request reactivation.");
    $portalResponse->assertSessionMissing('subscriber_logged_in');
});

test('admin can record all past-due bills and reactivate only after payment', function () use ($createSubscriberWithPastDueBills) {
    $subscriber = $createSubscriberWithPastDueBills('disconnected', 'overdue');
    $admin = User::create([
        'username' => 'test-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin);

    $this->post(route('subscriber.reactivate', $subscriber->id))
        ->assertRedirect(route('subscriber'))
        ->assertSessionHas('error');
    expect($subscriber->fresh()->status)->toBe('disconnected');

    $this->post(route('subscriber.record-past-due-payment', $subscriber->id))
        ->assertRedirect(route('subscriber'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('payment_submissions', [
        'subscriber_id' => $subscriber->id,
        'status' => 'approved',
        'amount_paid' => 200,
        'payment_screenshot' => null,
    ]);
    $this->assertDatabaseCount('payment_submission_billings', 2);
    expect(BillingRecord::where('subscriber_id', $subscriber->id)->where('status', 'paid')->count())->toBe(2);
    expect($subscriber->fresh()->status)->toBe('disconnected');

    $this->post(route('subscriber.reactivate', $subscriber->id))
        ->assertRedirect(route('subscriber'))
        ->assertSessionHas('success');
    expect($subscriber->fresh()->status)->toBe('active')
        ->and($subscriber->fresh()->disconnected_at)->not->toBeNull();
});

test('billing history displays the subscriber disconnection date', function () {
    $subscriber = Subscriber::create([
        'full_name' => 'History Test Subscriber',
        'username' => 'history-test-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 100,
        'status' => 'active',
    ]);
    foreach ([
        now()->setDate(2026, 9, 15)->setTime(14, 30),
        now()->setDate(2026, 10, 15)->setTime(14, 30),
    ] as $disconnectedAt) {
        SubscriberDisconnection::create([
            'subscriber_id' => $subscriber->id,
            'disconnected_at' => $disconnectedAt,
        ]);
    }

    $subscriber->disconnected_at = now()->setDate(2026, 10, 15)->setTime(14, 30);
    $subscriber->save();

    BillingRecord::create([
        'subscriber_id' => $subscriber->id,
        'billing_month' => now()->month,
        'billing_year' => now()->year,
        'amount' => 100,
        'due_date' => now()->addDays(10)->toDateString(),
        'status' => 'unpaid',
    ]);

    $this->withSession([
        'subscriber_logged_in' => true,
        'subscriber_id' => $subscriber->id,
    ])->get(route('subscriber.portal'))
        ->assertOk()
        ->assertSee('Disconnection history')
        ->assertSee('September 2026')
        ->assertSee('October 2026')
        ->assertSee('Sep 15, 2026 2:30 PM')
        ->assertSee('Oct 15, 2026 2:30 PM');
});