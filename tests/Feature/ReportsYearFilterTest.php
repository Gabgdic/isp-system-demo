<?php

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use App\Models\SmsLog;
use App\Models\Subscriber;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('year filter includes report data from that year only', function () {
    $admin = User::create([
        'username' => 'year-filter-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    $currentYearSubscriber = Subscriber::create([
        'full_name' => 'Current Year Subscriber',
        'username' => 'current-year-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 100,
        'status' => 'active',
    ]);
    $previousYearSubscriber = Subscriber::create([
        'full_name' => 'Previous Year Subscriber',
        'username' => 'previous-year-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 100,
        'status' => 'active',
    ]);

    $currentYear = now()->year;
    $previousYear = $currentYear - 1;
    $currentYearBill = BillingRecord::create([
        'subscriber_id' => $currentYearSubscriber->id,
        'billing_month' => 3,
        'billing_year' => $currentYear,
        'amount' => 250,
        'due_date' => Carbon::create($currentYear, 4, 10)->toDateString(),
        'paid_at' => Carbon::create($currentYear, 4, 5),
        'status' => 'paid',
    ]);
    $previousYearBill = BillingRecord::create([
        'subscriber_id' => $previousYearSubscriber->id,
        'billing_month' => 3,
        'billing_year' => $previousYear,
        'amount' => 900,
        'due_date' => Carbon::create($previousYear, 4, 10)->toDateString(),
        'paid_at' => Carbon::create($previousYear, 4, 5),
        'status' => 'paid',
    ]);

    PaymentSubmission::create([
        'billing_record_id' => $currentYearBill->id,
        'subscriber_id' => $currentYearSubscriber->id,
        'amount_paid' => 250,
        'reference_number' => 'YEAR-CURRENT-001',
        'payment_screenshot' => 'payment-proofs/year-current.jpg',
        'status' => 'approved',
        'reviewed_at' => Carbon::create($currentYear, 4, 5),
    ]);
    PaymentSubmission::create([
        'billing_record_id' => $previousYearBill->id,
        'subscriber_id' => $previousYearSubscriber->id,
        'amount_paid' => 900,
        'reference_number' => 'YEAR-PREVIOUS-001',
        'payment_screenshot' => 'payment-proofs/year-previous.jpg',
        'status' => 'approved',
        'reviewed_at' => Carbon::create($previousYear, 4, 5),
    ]);

    SmsLog::create([
        'recipient_type' => 'subscriber',
        'recipient_id' => $currentYearSubscriber->id,
        'phone_number' => '639123456789',
        'notification_type' => 'year_current',
        'message' => 'Current year notification',
        'status' => 'sent',
        'sent_at' => Carbon::create($currentYear, 4, 5),
    ]);
    SmsLog::create([
        'recipient_type' => 'subscriber',
        'recipient_id' => $previousYearSubscriber->id,
        'phone_number' => '639123456789',
        'notification_type' => 'year_previous',
        'message' => 'Previous year notification',
        'status' => 'sent',
        'sent_at' => Carbon::create($previousYear, 4, 5),
    ]);

    $this->actingAs($admin)
        ->get(route('reports', ['period' => (string) $currentYear]))
        ->assertOk()
        ->assertSee('value="' . $currentYear . '" selected', false)
        ->assertSee('Period: ' . $currentYear)
        ->assertSee('₱250.00')
        ->assertDontSee('₱900.00')
        ->assertSee('Current Year Subscriber')
        ->assertDontSee('Previous Year Subscriber')
        ->assertSee('Notification: Year Current sent to Current Year Subscriber', false)
        ->assertDontSee('Notification: Year Previous sent to Previous Year Subscriber', false);

    $this->get(route('reports.sms-logs.print', ['period' => (string) $currentYear]))
        ->assertOk()
        ->assertSee('Period: ' . $currentYear)
        ->assertSee('Notification: Year Current sent to Current Year Subscriber', false)
        ->assertDontSee('Notification: Year Previous sent to Previous Year Subscriber', false);

    $this->get(route('reports.paid-bills.print', ['period' => (string) $currentYear]))
        ->assertOk()
        ->assertSee('Period: ' . $currentYear)
        ->assertSee('Current Year Subscriber')
        ->assertDontSee('Previous Year Subscriber');
});
