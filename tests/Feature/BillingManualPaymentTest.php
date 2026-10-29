<?php

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createManualPaymentTestAdmin(): User
{
    return User::create([
        'username' => 'billing-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);
}

function createManualPaymentTestSubscriber(string $username): Subscriber
{
    return Subscriber::create([
        'full_name' => 'Billing Test Subscriber',
        'username' => $username,
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 100,
        'status' => 'active',
    ]);
}

function createManualPaymentTestBill(Subscriber $subscriber, int $month, string $status = 'unpaid', float $amount = 100): BillingRecord
{
    return BillingRecord::create([
        'subscriber_id' => $subscriber->id,
        'billing_month' => $month,
        'billing_year' => now()->year,
        'amount' => $amount,
        'due_date' => today()->toDateString(),
        'status' => $status,
    ]);
}

test('admin can select subscribers and record each full outstanding balance', function () {
    $this->actingAs(createManualPaymentTestAdmin());
    $subscriberOne = createManualPaymentTestSubscriber('billing-subscriber-one');
    $subscriberTwo = createManualPaymentTestSubscriber('billing-subscriber-two');
    $billOne = createManualPaymentTestBill($subscriberOne, now()->month, amount: 100);
    $billTwo = createManualPaymentTestBill($subscriberOne, now()->subMonth()->month, amount: 50);
    createManualPaymentTestBill($subscriberTwo, now()->month, amount: 75);

    $this->get(route('billing'))
        ->assertOk()
        ->assertSee('Record Manual Payment')
        ->assertSee('full outstanding balances')
        ->assertSee('₱150.00')
        ->assertSee('₱75.00')
        ->assertSee('name="subscriber_ids[]"', false);

    $this->post(route('billing.manual-payments.store'), [
        'subscriber_ids' => [$subscriberOne->id, $subscriberTwo->id],
    ])
        ->assertRedirect()
        ->assertSessionHas('success', 'Manual payments recorded for 2 subscriber(s).');

    expect($billOne->fresh()->status)->toBe('paid')
        ->and($billOne->fresh()->paid_at)->not->toBeNull()
        ->and($billTwo->fresh()->status)->toBe('paid');

    $paymentOne = PaymentSubmission::where('subscriber_id', $subscriberOne->id)->sole();
    expect($paymentOne->status)->toBe('approved')
        ->and($paymentOne->amount_paid)->toBe('150.00')
        ->and($paymentOne->payment_screenshot)->toBeNull()
        ->and($paymentOne->reference_number)->toStartWith('ADMIN-' . $subscriberOne->id . '-')
        ->and($paymentOne->admin_note)->toBe('Outstanding balance manually recorded by admin.');

    $this->assertDatabaseHas('payment_submission_billings', [
        'payment_submission_id' => $paymentOne->id,
        'billing_record_id' => $billOne->id,
        'amount_applied' => 100,
    ]);
    $this->assertDatabaseHas('payment_submission_billings', [
        'payment_submission_id' => $paymentOne->id,
        'billing_record_id' => $billTwo->id,
        'amount_applied' => 50,
    ]);
    expect(PaymentSubmission::where('subscriber_id', $subscriberTwo->id)->sole()->amount_paid)->toBe('75.00');
});

test('admin cannot manually pay a bill awaiting payment verification', function () {
    $this->actingAs(createManualPaymentTestAdmin());
    $subscriber = createManualPaymentTestSubscriber('pending-subscriber');
    $bill = createManualPaymentTestBill($subscriber, now()->month, 'pending');

    $this->post(route('billing.manual-payments.store'), [
        'subscriber_ids' => [$subscriber->id],
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('subscriber_ids');

    expect($bill->fresh()->status)->toBe('pending');
    $this->assertDatabaseCount('payment_submissions', 0);
});
