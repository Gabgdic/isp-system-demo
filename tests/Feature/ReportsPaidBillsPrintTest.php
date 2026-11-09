<?php

use App\Models\BillingRecord;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('paid bills print report includes all rows for the selected month and search', function () {
    $admin = User::create([
        'username' => 'paid-bills-print-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    $month = now()->subMonth()->startOfMonth();
    for ($index = 1; $index <= 12; $index++) {
        $subscriber = Subscriber::create([
            'full_name' => "Print Subscriber {$index}",
            'username' => "print-subscriber-{$index}",
            'password' => Hash::make('Password123!'),
            'monthly_fee' => 100,
            'status' => 'active',
        ]);

        BillingRecord::create([
            'subscriber_id' => $subscriber->id,
            'billing_month' => $month->month,
            'billing_year' => $month->year,
            'amount' => 100,
            'due_date' => $month->copy()->endOfMonth()->toDateString(),
            'status' => 'paid',
            'paid_at' => $month->copy()->addDays(10),
        ]);
    }

    $otherMonthSubscriber = Subscriber::create([
        'full_name' => 'Other Month Subscriber',
        'username' => 'other-month-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 100,
        'status' => 'active',
    ]);
    $otherMonth = $month->copy()->subMonth();
    BillingRecord::create([
        'subscriber_id' => $otherMonthSubscriber->id,
        'billing_month' => $otherMonth->month,
        'billing_year' => $otherMonth->year,
        'amount' => 100,
        'due_date' => $otherMonth->copy()->endOfMonth()->toDateString(),
        'status' => 'paid',
        'paid_at' => $otherMonth->copy()->addDays(10),
    ]);

    $this->actingAs($admin)
        ->get(route('reports'))
        ->assertOk()
        ->assertSee('onclick="printPaidBills()"', false)
        ->assertSee('SMS Notification Logs');

    $this->get(route('reports.paid-bills.print', ['period' => $month->format('Y-m')]))
        ->assertOk()
        ->assertSee('<title>Paid Bills Report</title>', false)
        ->assertSee('Print Subscriber 12');

    $this->get(route('reports.paid-bills.print', ['period' => $month->format('Y-m')]))
        ->assertOk()
        ->assertSee('<title>Paid Bills Report</title>', false)
        ->assertSee('Period: ' . $month->format('F Y'))
        ->assertSee('12 paid payments')
        ->assertSee('Print Subscriber 12')
        ->assertDontSee('print-subscriber-12')
        ->assertDontSee('Other Month Subscriber');

    $this->get(route('reports.paid-bills.print', [
        'period' => $month->format('Y-m'),
        'search' => 'Print Subscriber 12',
    ]))
        ->assertOk()
        ->assertSee('1 paid payment')
        ->assertSee('Print Subscriber 12')
        ->assertDontSee('Print Subscriber 11');
});
