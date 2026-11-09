<?php

use App\Models\SmsLog;
use App\Models\Subscriber;
use App\Models\SubscriberOfficeVisitRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('reports SMS card opens period-filtered notification logs with subscriber and sent date', function () {
    $admin = User::create([
        'username' => 'sms-reports-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    $subscriber = Subscriber::create([
        'full_name' => 'SMS Reports Subscriber',
        'username' => 'sms-reports-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 100,
        'status' => 'active',
    ]);
    $month = now()->subMonth()->startOfMonth();
    $sentAt = $month->copy()->addDays(4)->setTime(14, 30);

    $log = SmsLog::create([
        'recipient_type' => 'subscriber',
        'recipient_id' => $subscriber->id,
        'phone_number' => '639123456789',
        'notification_type' => 'soa_sent',
        'message' => 'Your statement of account is ready.',
        'status' => 'sent',
        'sent_at' => $sentAt,
    ]);
    $log->forceFill([
        'created_at' => $sentAt,
        'updated_at' => $sentAt,
    ])->save();

    SubscriberOfficeVisitRequest::create([
        'subscriber_id' => $subscriber->id,
        'request_type' => 'payment_reduction',
        'visit_date' => now()->addDays(3)->toDateString(),
        'status' => 'pending',
    ]);

    SmsLog::create([
        'recipient_type' => 'subscriber',
        'recipient_id' => $subscriber->id,
        'phone_number' => '639123456789',
        'notification_type' => 'payment_approved',
        'message' => 'Your payment has been approved.',
        'status' => 'sent',
        'sent_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('reports', ['period' => $month->format('Y-m')]))
        ->assertOk()
        ->assertSee('SMS Notification Logs')
        ->assertSee('onclick="openSmsLogsModal()"', false)
        ->assertSee('Subscriber Requests (1)')
        ->assertSee('onclick="openOfficeVisitRequestsModal()"', false)
        ->assertSee('Subscriber Office Visit Requests')
        ->assertSee('Accept requested date')
        ->assertSee('Move visit to this date')
        ->assertSee('Print / Save as PDF')
        ->assertSee('onclick="printSmsLogs()"', false)
        ->assertSee('id="smsLogsPrintFrame"', false)
        ->assertSee('Period: ' . $month->format('F Y'))
        ->assertSee('id="smsLogsModal"', false)
        ->assertSee('Notification: SOA sent to SMS Reports Subscriber', false)
        ->assertSee($sentAt->format('M j, Y g:i A'))
        ->assertDontSee('Notification: Payment Approved sent to SMS Reports Subscriber', false);

    $this->get(route('reports.sms-logs.print', ['period' => $month->format('Y-m')]))
        ->assertOk()
        ->assertSee('<title>SMS Notification Logs</title>', false)
        ->assertSee('Period: ' . $month->format('F Y'))
        ->assertSee('Notification: SOA sent to SMS Reports Subscriber', false)
        ->assertDontSee('Notification: Payment Approved sent to SMS Reports Subscriber', false);
});
