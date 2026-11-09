<?php

use App\Models\BillingRecord;
use App\Models\Subscriber;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createSubscriberWithSignupDate(string $date): Subscriber
{
    $subscriber = Subscriber::create([
        'full_name' => 'Billing Test Subscriber',
        'username' => 'billing-' . str_replace('-', '', $date),
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 100,
        'status' => 'active',
    ]);

    $subscriber->created_at = Carbon::parse($date);
    $subscriber->save();

    return $subscriber;
}

test('generates the bill on the subscriber signup day', function () {
    $this->travelTo(now()->setDate(2026, 6, 15)->startOfDay());
    $subscriber = createSubscriberWithSignupDate('2026-01-15');

    Artisan::call('billing:generate-monthly');

    $this->assertDatabaseHas('billing_records', [
        'subscriber_id' => $subscriber->id,
        'billing_month' => 6,
        'billing_year' => 2026,
        'status' => 'unpaid',
    ]);
    expect($subscriber->billingRecords()->first()->due_date->toDateString())->toBe('2026-07-15');
});

test('sets the due date to the subscriber signup day in the month after the billing period', function (string $now, string $signupDate, string $dueDate) {
    $this->travelTo(Carbon::parse($now)->startOfDay());
    $subscriber = createSubscriberWithSignupDate($signupDate);
    $billingDate = Carbon::parse($now);

    Artisan::call('billing:generate-monthly');

    $this->assertDatabaseHas('billing_records', [
        'subscriber_id' => $subscriber->id,
        'billing_month' => $billingDate->month,
        'billing_year' => $billingDate->year,
    ]);
    expect($subscriber->billingRecords()->first()->due_date->toDateString())->toBe($dueDate);
})->with([
    'January bill for day-31 signup is due February 28' => ['2026-01-31', '2025-01-31', '2026-02-28'],
    'leap-year January bill for day-31 signup is due February 29' => ['2024-01-31', '2023-01-31', '2024-02-29'],
    'March bill for day-31 signup is due April 30' => ['2026-03-31', '2025-01-31', '2026-04-30'],
    'November bill for day-6 subscriber is due December 6' => ['2026-11-09', '2026-10-06', '2026-12-06'],
    'November bill for day-7 subscriber is due December 7' => ['2026-11-09', '2026-10-07', '2026-12-07'],
    'December bill for day-31 subscriber is due January 31' => ['2026-12-31', '2025-01-31', '2027-01-31'],
    'new November signup is due the following December' => ['2026-11-09', '2026-11-09', '2026-12-09'],
]);

test('does not generate a bill before the subscriber signup day', function () {
    $this->travelTo(now()->setDate(2026, 6, 14)->startOfDay());
    $subscriber = createSubscriberWithSignupDate('2026-01-15');

    Artisan::call('billing:generate-monthly');

    expect(BillingRecord::where('subscriber_id', $subscriber->id)->exists())->toBeFalse();
});

test('generates the bill when the command runs after the signup day', function () {
    $this->travelTo(Carbon::parse('2026-06-20')->startOfDay());
    $subscriber = createSubscriberWithSignupDate('2026-01-15');

    Artisan::call('billing:generate-monthly');

    $this->assertDatabaseHas('billing_records', [
        'subscriber_id' => $subscriber->id,
        'billing_month' => 6,
        'billing_year' => 2026,
        'status' => 'unpaid',
    ]);
    expect($subscriber->billingRecords()->first()->due_date->toDateString())->toBe('2026-07-15');
});

test('updates the due date on an existing bill to the subscriber signup day', function () {
    $this->travelTo(Carbon::parse('2026-06-20')->startOfDay());
    $subscriber = createSubscriberWithSignupDate('2026-01-15');
    $record = BillingRecord::create([
        'subscriber_id' => $subscriber->id,
        'billing_month' => 6,
        'billing_year' => 2026,
        'amount' => 100,
        'due_date' => '2026-06-30',
        'status' => 'unpaid',
    ]);

    Artisan::call('billing:generate-monthly');

    expect($record->fresh()->due_date->toDateString())->toBe('2026-07-15');
});

test('updates existing billing records for their own billing months', function () {
    $this->travelTo(Carbon::parse('2026-10-29')->startOfDay());
    $subscriber = createSubscriberWithSignupDate('2026-01-15');
    $record = BillingRecord::create([
        'subscriber_id' => $subscriber->id,
        'billing_month' => 8,
        'billing_year' => 2026,
        'amount' => 100,
        'due_date' => '2026-08-31',
        'status' => 'paid',
        'paid_at' => '2026-08-31 12:00:00',
    ]);

    Artisan::call('billing:generate-monthly');

    expect($record->fresh()->due_date->toDateString())->toBe('2026-09-15');
    expect($record->fresh()->status)->toBe('paid');
});
