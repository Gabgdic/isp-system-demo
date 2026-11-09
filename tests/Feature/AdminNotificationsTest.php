<?php

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use App\Models\Subscriber;
use App\Models\SubscriberDisconnection;
use App\Models\SubscriberOfficeVisitRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('admin notifications include pending payments, subscriber requests, and disconnections', function () {
    $admin = User::create([
        'username' => 'admin-notifications-test',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    $subscriber = Subscriber::create([
        'full_name' => 'Notification Subscriber',
        'username' => 'notification-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 500,
        'status' => 'disconnected',
    ]);

    $bill = BillingRecord::create([
        'subscriber_id' => $subscriber->id,
        'billing_month' => now()->month,
        'billing_year' => now()->year,
        'amount' => 500,
        'due_date' => today(),
        'status' => 'unpaid',
    ]);

    PaymentSubmission::create([
        'billing_record_id' => $bill->id,
        'subscriber_id' => $subscriber->id,
        'amount_paid' => 500,
        'reference_number' => 'ADMIN-NOTIFICATION-001',
        'payment_screenshot' => 'payment-proofs/admin-notification.jpg',
        'status' => 'pending',
    ]);

    SubscriberOfficeVisitRequest::create([
        'subscriber_id' => $subscriber->id,
        'request_type' => 'payment_reduction',
        'visit_date' => now()->addDays(3)->toDateString(),
        'status' => 'pending',
    ]);
    SubscriberOfficeVisitRequest::create([
        'subscriber_id' => $subscriber->id,
        'request_type' => 'internet_fix',
        'visit_date' => null,
        'status' => 'pending',
    ]);

    SubscriberDisconnection::create([
        'subscriber_id' => $subscriber->id,
        'disconnected_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('id="adminNotificationsToggle"', false)
        ->assertSee('id="adminNotificationsPanel"', false)
        ->assertSee('admin-notification-unread-dot', false)
        ->assertSee('4 new notifications')
        ->assertSee('New payment request')
        ->assertSee('Notification Subscriber submitted a payment request for PHP 500.00.')
        ->assertSee('New payment reduction request')
        ->assertSee('New internet repair request')
        ->assertSee('Notification Subscriber requested an internet repair.')
        ->assertSee('Subscriber disconnected')
        ->assertSee('Notification Subscriber was disconnected.');
});
