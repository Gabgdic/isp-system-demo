<?php

use App\Models\BillingRecord;
use App\Models\PaymentSubmission;
use App\Models\PaymentSubmissionBilling;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('paid bill receipt and payment proof open from report modals', function () {
    $admin = User::create([
        'username' => 'reports-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    $subscriber = Subscriber::create([
        'full_name' => 'Reports Subscriber',
        'username' => 'reports-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 100,
        'status' => 'active',
    ]);

    $bill = BillingRecord::create([
        'subscriber_id' => $subscriber->id,
        'billing_month' => now()->month,
        'billing_year' => now()->year,
        'amount' => 100,
        'due_date' => today()->toDateString(),
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    PaymentSubmission::create([
        'billing_record_id' => $bill->id,
        'subscriber_id' => $subscriber->id,
        'amount_paid' => 100,
        'reference_number' => 'REPORT-REF-001',
        'payment_screenshot' => 'payment-proofs/report-proof.jpg',
        'status' => 'approved',
        'reviewed_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('reports'))
        ->assertOk()
        ->assertSee('id="reportReceiptModal"', false)
        ->assertSee('id="report-receipt-frame"', false)
        ->assertSee('onclick="printReportReceipt()"', false)
        ->assertSee('id="paymentProofModal"', false)
        ->assertSee('class="open-report-receipt', false)
        ->assertSee(route('billing.payment-receipt.preview', PaymentSubmission::first()), false)
        ->assertSee('class="open-payment-proof', false)
        ->assertSee('payment-proofs/report-proof.jpg');

    $this->get(route('billing.receipt.preview', $bill))
        ->assertOk()
        ->assertSee('Official Receipt', false)
        ->assertSee('receipt-card', false)
        ->assertSee('Reports Subscriber');
});

test('soa preview and download use the same printable document', function () {
    $admin = User::create([
        'username' => 'soa-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    $subscriber = Subscriber::create([
        'full_name' => 'SOA Subscriber',
        'username' => 'soa-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 100,
        'status' => 'active',
    ]);

    $this->actingAs($admin);

    $this->get(route('billing'))
        ->assertOk()
        ->assertSee('id="soa-preview-frame"', false)
        ->assertSee('id="receipt-preview-frame"', false)
        ->assertSee("printPreviewFrame('soa-preview-frame')", false)
        ->assertSee("printPreviewFrame('receipt-preview-frame')", false);

    $this->get(route('billing.soa.preview', $subscriber))
        ->assertOk()
        ->assertSee('<title>Statement of Account - SOA Subscriber</title>', false)
        ->assertSee('soa-card', false)
        ->assertSee('Total Amount Due');

    $this->get(route('billing.soa.download', $subscriber))
        ->assertOk()
        ->assertSee('<title>Statement of Account - SOA Subscriber</title>', false)
        ->assertSee('soa-card', false)
        ->assertSee('Total Amount Due');
});

test('report groups an outstanding balance payment and its receipt lists every covered bill', function () {
    $admin = User::create([
        'username' => 'grouped-report-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    $subscriber = Subscriber::create([
        'full_name' => 'Grouped Payment Subscriber',
        'username' => 'grouped-payment-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 100,
        'status' => 'active',
    ]);

    $firstPeriod = now()->subMonths(2);
    $secondPeriod = now()->subMonth();
    $firstBill = BillingRecord::create([
        'subscriber_id' => $subscriber->id,
        'billing_month' => $firstPeriod->month,
        'billing_year' => $firstPeriod->year,
        'amount' => 100,
        'due_date' => $firstPeriod->copy()->endOfMonth()->toDateString(),
        'status' => 'paid',
        'paid_at' => now(),
    ]);
    $secondBill = BillingRecord::create([
        'subscriber_id' => $subscriber->id,
        'billing_month' => $secondPeriod->month,
        'billing_year' => $secondPeriod->year,
        'amount' => 200,
        'due_date' => $secondPeriod->copy()->endOfMonth()->toDateString(),
        'status' => 'paid',
        'paid_at' => now(),
    ]);

    $payment = PaymentSubmission::create([
        'subscriber_id' => $subscriber->id,
        'amount_paid' => 300,
        'reference_number' => 'GROUPED-REF-001',
        'payment_screenshot' => 'payment-proofs/grouped-proof.jpg',
        'status' => 'approved',
        'reviewed_at' => now(),
    ]);

    PaymentSubmissionBilling::create([
        'payment_submission_id' => $payment->id,
        'billing_record_id' => $firstBill->id,
        'amount_applied' => 100,
    ]);
    PaymentSubmissionBilling::create([
        'payment_submission_id' => $payment->id,
        'billing_record_id' => $secondBill->id,
        'amount_applied' => 200,
    ]);

    $this->actingAs($admin)
        ->get(route('reports'))
        ->assertOk()
        ->assertSee('1 paid payment found')
        ->assertSee('₱300.00')
        ->assertSee($firstPeriod->format('M Y') . ', ' . $secondPeriod->format('M Y'))
        ->assertSee(route('billing.payment-receipt.preview', $payment), false);

    $this->get(route('reports', ['period' => $firstPeriod->format('Y-m')]))
        ->assertOk()
        ->assertSee('1 paid payment found')
        ->assertSee($firstPeriod->format('M Y') . ', ' . $secondPeriod->format('M Y'));

    $this->get(route('billing.payment-receipt.preview', $payment))
        ->assertOk()
        ->assertSee('GROUPED-REF-001')
        ->assertSee('Billing Period(s)')
        ->assertSee('Subscription Fee - ' . $firstPeriod->format('F Y'))
        ->assertSee('Subscription Fee - ' . $secondPeriod->format('F Y'))
        ->assertSee('₱300.00');
});
