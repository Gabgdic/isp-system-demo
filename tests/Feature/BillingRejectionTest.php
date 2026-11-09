<?php

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createBillingRejectionFixture(): array
{
    $subscriber = Subscriber::create([
        'full_name' => 'Rejection Test Subscriber',
        'username' => 'rejection-test-subscriber',
        'password' => Hash::make('Password123!'),
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
        'reference_number' => 'REJECTION-TEST-001',
        'payment_screenshot' => 'payment-proofs/rejection-test.jpg',
        'status' => 'pending',
    ]);

    return [$bill, $payment];
}

test('billing rejection form offers corrected predefined reasons', function () {
    $admin = User::create([
        'username' => 'billing-rejection-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);
    createBillingRejectionFixture();

    $this->actingAs($admin)
        ->get(route('billing'))
        ->assertOk()
        ->assertSee('Reference number does not match')
        ->assertSee('Image is blurry or unreadable')
        ->assertSee('Payment was not received')
        ->assertSee('Other (please specify)');
});

test('admin can reject a payment with a predefined reason', function () {
    $admin = User::create([
        'username' => 'billing-rejection-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);
    [$bill, $payment] = createBillingRejectionFixture();

    $this->actingAs($admin)
        ->post(route('billing.payment.reject', $payment), [
            'reject_reason' => 'reference_mismatch',
        ])
        ->assertSessionHas('success', 'Payment rejected.');

    expect($payment->fresh()->status)->toBe('rejected')
        ->and($payment->fresh()->admin_note)->toBe('Reference number does not match.')
        ->and($bill->fresh()->status)->toBe('unpaid');
});

test('other rejection reason requires and saves administrator details', function () {
    $admin = User::create([
        'username' => 'billing-rejection-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);
    [, $payment] = createBillingRejectionFixture();

    $this->actingAs($admin)
        ->from(route('billing'))
        ->post(route('billing.payment.reject', $payment), [
            'reject_reason' => 'other',
        ])
        ->assertRedirect(route('billing'))
        ->assertSessionHasErrors('other_reason');

    $this->post(route('billing.payment.reject', $payment), [
        'reject_reason' => 'other',
        'other_reason' => 'Payment amount does not match the submitted receipt.',
    ])->assertSessionHas('success', 'Payment rejected.');

    expect($payment->fresh()->admin_note)->toBe('Other: Payment amount does not match the submitted receipt.');
});
