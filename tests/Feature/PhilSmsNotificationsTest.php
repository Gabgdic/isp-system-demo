<?php

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use App\Models\SmsLog;
use App\Models\Subscriber;
use App\Models\SubscriberOfficeVisitRequest;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\PhilSmsService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function configurePhilSms(): void
{
    config([
        'services.philsms.api_url' => 'https://sms.example.test/send',
        'services.philsms.api_token' => 'test-token',
        'services.philsms.sender_id' => 'ISP-System',
    ]);

    Http::fake([
        '*' => Http::response(['status' => 'success'], 200),
    ]);
}

function createSmsPaymentFixture(): array
{
    $subscriber = Subscriber::create([
        'full_name' => 'SMS Test Subscriber',
        'username' => 'sms-test-subscriber',
        'password' => Hash::make('Password123!'),
        'phone_number' => '09979875337',
        'monthly_fee' => 100,
        'status' => 'active',
    ]);

    $bill = BillingRecord::create([
        'subscriber_id' => $subscriber->id,
        'billing_month' => now()->month,
        'billing_year' => now()->year,
        'amount' => 100,
        'due_date' => today()->addDays(5)->toDateString(),
        'status' => 'pending',
    ]);

    $payment = PaymentSubmission::create([
        'billing_record_id' => $bill->id,
        'subscriber_id' => $subscriber->id,
        'amount_paid' => 100,
        'reference_number' => 'SMS-TEST-001',
        'payment_screenshot' => 'payment-proofs/sms-test.jpg',
        'status' => 'pending',
    ]);

    $admin = User::create([
        'username' => 'sms-test-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    return [$subscriber, $bill, $payment, $admin];
}

test('daily billing command sends the corrected SOA reminder with the configured system name', function () {
    $this->travelTo(Carbon::parse('2026-12-04')->startOfDay());
    configurePhilSms();
    SystemSetting::create([
        'system_name' => 'Test ISP Store',
    ]);

    $subscriber = Subscriber::create([
        'full_name' => 'SMS Test Subscriber',
        'username' => 'sms-reminder-subscriber',
        'password' => Hash::make('Password123!'),
        'phone_number' => '09979875337',
        'monthly_fee' => 100,
        'status' => 'active',
    ]);
    $subscriber->created_at = Carbon::parse('2025-11-09');
    $subscriber->save();
    $bill = BillingRecord::create([
        'subscriber_id' => $subscriber->id,
        'billing_month' => 11,
        'billing_year' => 2026,
        'amount' => 100,
        'due_date' => '2026-12-09',
        'status' => 'unpaid',
    ]);

    Artisan::call('billing:generate-monthly');
    Artisan::call('billing:generate-monthly');

    $expectedMessage = 'Hello SMS Test Subscriber, Test ISP Store: Your Nov 2026 bill for PHP 100.00 is due Dec 09, 2026. View your statement in your subscriber portal: '
        . url('/portal');

    Http::assertSentCount(1);
    Http::assertSent(fn ($request) => $request['recipient'] === '639979875337'
        && $request['message'] === $expectedMessage);
    expect($bill->fresh()->due_date_reminder_sent_at)->not->toBeNull();
    expect(SmsLog::where('notification_type', 'due_date_reminder')
        ->where('status', 'sent')
        ->where('message', $expectedMessage)
        ->count())->toBe(1);
});

test('approving a payment sends an approval SMS to the subscriber', function () {
    configurePhilSms();
    [, , $payment, $admin] = createSmsPaymentFixture();

    $this->actingAs($admin)
        ->post(route('billing.payment.approve', $payment))
        ->assertSessionHas('success', 'Payment approved successfully.');

    Http::assertSent(fn ($request) => $request['recipient'] === '639979875337'
        && str_contains($request['message'], 'We recorded your payment of PHP 100.00.'));
    expect(SmsLog::where('notification_type', 'payment_approved')->where('status', 'sent')->count())->toBe(1);
});

test('rejecting a payment sends the rejection reason by SMS', function () {
    configurePhilSms();
    [, , $payment, $admin] = createSmsPaymentFixture();

    $this->actingAs($admin)
        ->post(route('billing.payment.reject', $payment), [
            'reject_reason' => 'reference_mismatch',
        ])
        ->assertSessionHas('success', 'Payment rejected.');

    Http::assertSent(fn ($request) => $request['recipient'] === '639979875337'
        && str_contains($request['message'], 'We could not accept your payment of PHP 100.00.')
        && str_contains($request['message'], 'Reference number does not match.'));
    expect(SmsLog::where('notification_type', 'payment_rejected')->where('status', 'sent')->count())->toBe(1);
});

test('accepting an office visit request sends an SMS with the accepted date', function () {
    configurePhilSms();
    SystemSetting::create(['system_name' => 'Test ISP Store']);
    $subscriber = Subscriber::create([
        'full_name' => 'Office Visit Subscriber',
        'username' => 'office-visit-sms-accepted',
        'password' => Hash::make('Password123!'),
        'phone_number' => '09979875337',
        'monthly_fee' => 100,
        'status' => 'active',
    ]);
    $visitRequest = SubscriberOfficeVisitRequest::create([
        'subscriber_id' => $subscriber->id,
        'request_type' => 'payment_reduction',
        'visit_date' => '2026-12-10',
        'status' => 'pending',
    ]);
    $admin = User::create([
        'username' => 'office-visit-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post(route('reports.office-visit-requests.review', $visitRequest), [
            'action' => 'accept',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($visitRequest->fresh()->status)->toBe('accepted');
    Http::assertSent(fn ($request) => $request['recipient'] === '639979875337'
        && str_contains($request['message'], 'accepted for Dec 10, 2026')
        && str_contains($request['message'], 'bring or show your SOA'));
    expect(SmsLog::where('notification_type', 'office_visit_accepted')->where('status', 'sent')->count())->toBe(1);
});

test('moving an office visit request updates its date and sends an SMS', function () {
    configurePhilSms();
    $subscriber = Subscriber::create([
        'full_name' => 'Office Visit Subscriber',
        'username' => 'office-visit-sms-moved',
        'password' => Hash::make('Password123!'),
        'phone_number' => '09979875337',
        'monthly_fee' => 100,
        'status' => 'active',
    ]);
    $visitRequest = SubscriberOfficeVisitRequest::create([
        'subscriber_id' => $subscriber->id,
        'request_type' => 'payment_reduction',
        'visit_date' => now()->addDays(3)->toDateString(),
        'status' => 'pending',
    ]);
    $newDate = now()->addDays(8)->toDateString();
    $admin = User::create([
        'username' => 'office-visit-reschedule-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post(route('reports.office-visit-requests.review', $visitRequest), [
            'action' => 'reschedule',
            'visit_date' => $newDate,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($visitRequest->fresh()->status)->toBe('rescheduled')
        ->and($visitRequest->fresh()->visit_date->toDateString())->toBe($newDate);
    Http::assertSent(fn ($request) => $request['recipient'] === '639979875337'
        && str_contains($request['message'], 'moved to ' . Carbon::parse($newDate)->format('M d, Y')));
    expect(SmsLog::where('notification_type', 'office_visit_rescheduled')->where('status', 'sent')->count())->toBe(1);
});

test('rejecting an office visit request updates its status and sends an SMS', function () {
    configurePhilSms();
    $subscriber = Subscriber::create([
        'full_name' => 'Office Visit Subscriber',
        'username' => 'office-visit-sms-rejected',
        'password' => Hash::make('Password123!'),
        'phone_number' => '09979875337',
        'monthly_fee' => 100,
        'status' => 'active',
    ]);
    $visitRequest = SubscriberOfficeVisitRequest::create([
        'subscriber_id' => $subscriber->id,
        'request_type' => 'payment_reduction',
        'visit_date' => now()->addDays(3)->toDateString(),
        'status' => 'pending',
    ]);
    $admin = User::create([
        'username' => 'office-visit-reject-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post(route('reports.office-visit-requests.review', $visitRequest), [
            'action' => 'reject',
        ])
        ->assertRedirect()
        ->assertSessionHas('success', 'The office visit request was rejected. An SMS notification was sent.');

    expect($visitRequest->fresh()->status)->toBe('rejected');
    Http::assertSent(fn ($request) => $request['recipient'] === '639979875337'
        && str_contains($request['message'], 'was not accepted')
        && str_contains($request['message'], 'contact our office'));
    expect(SmsLog::where('notification_type', 'office_visit_rejected')->where('status', 'sent')->count())->toBe(1);
});

test('admin schedules an internet repair request and notifies the subscriber', function () {
    configurePhilSms();
    $subscriber = Subscriber::create([
        'full_name' => 'Internet Fix Subscriber',
        'username' => 'internet-fix-sms-schedule',
        'password' => Hash::make('Password123!'),
        'phone_number' => '09979875337',
        'monthly_fee' => 100,
        'status' => 'active',
    ]);
    $visitRequest = SubscriberOfficeVisitRequest::create([
        'subscriber_id' => $subscriber->id,
        'request_type' => 'internet_fix',
        'visit_date' => null,
        'status' => 'pending',
    ]);
    $repairDate = now()->addDays(5)->toDateString();
    $admin = User::create([
        'username' => 'internet-fix-schedule-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->from(route('reports'))
        ->post(route('reports.office-visit-requests.review', $visitRequest), [
            'action' => 'accept',
        ])
        ->assertRedirect(route('reports'))
        ->assertSessionHasErrors('visit_date');

    $this->actingAs($admin)
        ->post(route('reports.office-visit-requests.review', $visitRequest), [
            'action' => 'accept',
            'visit_date' => $repairDate,
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect($visitRequest->fresh()->status)->toBe('accepted')
        ->and($visitRequest->fresh()->visit_date->toDateString())->toBe($repairDate);
    Http::assertSent(fn ($request) => $request['recipient'] === '639979875337'
        && str_contains($request['message'], 'internet repair request is scheduled for ' . Carbon::parse($repairDate)->format('M d, Y')));

    $this->withSession([
        'subscriber_logged_in' => true,
        'subscriber_id' => $subscriber->id,
    ])->get(route('subscriber.portal'))
        ->assertOk()
        ->assertSee('Internet repair request Accepted')
        ->assertSee('Your internet repair is scheduled for ' . Carbon::parse($repairDate)->format('M d, Y'));
});

test('recording a manual payment sends an approval SMS', function () {
    configurePhilSms();
    $subscriber = Subscriber::create([
        'full_name' => 'SMS Manual Subscriber',
        'username' => 'sms-manual-subscriber',
        'password' => Hash::make('Password123!'),
        'phone_number' => '09979875337',
        'monthly_fee' => 100,
        'status' => 'active',
    ]);
    BillingRecord::create([
        'subscriber_id' => $subscriber->id,
        'billing_month' => now()->month,
        'billing_year' => now()->year,
        'amount' => 100,
        'due_date' => today()->toDateString(),
        'status' => 'unpaid',
    ]);
    $admin = User::create([
        'username' => 'sms-manual-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    $this->actingAs($admin)
        ->post(route('billing.manual-payments.store'), [
            'subscriber_ids' => [$subscriber->id],
        ])
        ->assertSessionHas('success', 'Manual payments recorded for 1 subscriber(s).');

    Http::assertSent(fn ($request) => $request['recipient'] === '639979875337'
        && str_contains($request['message'], 'We recorded your payment of PHP 100.00.'));
});

test('failed SMS requests are logged and return their failure details', function () {
    config([
        'services.philsms.api_url' => 'https://sms.example.test/send',
        'services.philsms.api_token' => 'test-token',
        'services.philsms.sender_id' => 'ISP-System',
    ]);
    Http::fake([
        '*' => Http::response(['status' => 'error', 'message' => 'Recipient rejected'], 422),
    ]);

    $result = app(PhilSmsService::class)->send(
        '09979875337',
        'Test notification',
        'subscriber',
        123,
        'general'
    );

    expect($result['success'])->toBeFalse()
        ->and($result['http_status'])->toBe(422)
        ->and(SmsLog::query()->sole()->status)->toBe('failed')
        ->and(SmsLog::query()->sole()->error_message)->toBe('Recipient rejected')
        ->and(SmsLog::query()->sole()->phone_number)->toBe('639979875337');
});

test('invalid mobile numbers are saved as failed SMS attempts', function () {
    configurePhilSms();

    $result = app(PhilSmsService::class)->send(
        '12345',
        'Test notification',
        'subscriber',
        123,
        'general'
    );

    expect($result['success'])->toBeFalse()
        ->and($result['error'])->toBe('Invalid Philippine mobile number.')
        ->and(SmsLog::query()->sole()->status)->toBe('failed')
        ->and(SmsLog::query()->sole()->phone_number)->toBe('12345');
    Http::assertNothingSent();
});
