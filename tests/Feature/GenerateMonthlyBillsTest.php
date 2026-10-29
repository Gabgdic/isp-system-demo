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

test('generates the bill on the signup day of the month', function () {
    $this->travelTo(now()->setDate(2026, 6, 15)->startOfDay());
    $subscriber = createSubscriberWithSignupDate('2026-01-15');

    Artisan::call('billing:generate-monthly');

    $this->assertDatabaseHas('billing_records', [
        'subscriber_id' => $subscriber->id,
        'billing_month' => 6,
        'billing_year' => 2026,
        'status' => 'unpaid',
    ]);
    expect($subscriber->billingRecords()->first()->due_date->toDateString())->toBe('2026-06-15');
});

test('clamps signup days to the last day of shorter months', function (string $now, string $signupDate, string $dueDate) {
    $this->travelTo(Carbon::parse($now)->startOfDay());
    $subscriber = createSubscriberWithSignupDate($signupDate);
    $billingDate = Carbon::parse($dueDate);

    Artisan::call('billing:generate-monthly');

    $this->assertDatabaseHas('billing_records', [
        'subscriber_id' => $subscriber->id,
        'billing_month' => $billingDate->month,
        'billing_year' => $billingDate->year,
    ]);
    expect($subscriber->billingRecords()->first()->due_date->toDateString())->toBe($dueDate);
})->with([
    '28 day February' => ['2025-02-28', '2025-01-31', '2025-02-28'],
    'leap year February' => ['2024-02-29', '2024-01-31', '2024-02-29'],
    '30 day month' => ['2026-04-30', '2026-01-31', '2026-04-30'],
]);

test('does not generate a bill before the subscriber signup day', function () {
    $this->travelTo(now()->setDate(2026, 6, 14)->startOfDay());
    $subscriber = createSubscriberWithSignupDate('2026-01-15');

    Artisan::call('billing:generate-monthly');

    expect(BillingRecord::where('subscriber_id', $subscriber->id)->exists())->toBeFalse();
});

test('updates the due date on an existing bill to match the signup day', function () {
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

    expect($record->fresh()->due_date->toDateString())->toBe('2026-06-15');
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

    expect($record->fresh()->due_date->toDateString())->toBe('2026-08-15');
    expect($record->fresh()->status)->toBe('paid');
});
