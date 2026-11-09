<?php

use App\Models\BillingRecord;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('billing records can be filtered by one status and billing month', function () {
    $admin = User::create([
        'username' => 'billing-filter-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    foreach ([
        ['name' => 'Paid August Subscriber', 'username' => 'paid-august', 'billing_month' => 8, 'status' => 'paid'],
        ['name' => 'Overdue August Subscriber', 'username' => 'overdue-august', 'billing_month' => 8, 'status' => 'overdue'],
        ['name' => 'Paid September Subscriber', 'username' => 'paid-september', 'billing_month' => 9, 'status' => 'paid'],
    ] as $record) {
        $subscriber = Subscriber::create([
            'full_name' => $record['name'],
            'username' => $record['username'],
            'password' => Hash::make('Password123!'),
            'monthly_fee' => 100,
            'status' => 'active',
        ]);

        BillingRecord::create([
        'subscriber_id' => $subscriber->id,
        'billing_month' => $record['billing_month'],
        'billing_year' => 2026,
        'amount' => 100,
        'due_date' => today()->toDateString(),
        'status' => $record['status'],
        ]);
    }

    $this->actingAs($admin)
        ->get(route('billing', [
            'status' => 'paid',
            'billing_period' => '2026-08',
        ]))
        ->assertOk()
        ->assertSee('value="paid" class="billing-status-filter rounded border-slate-300" checked', false)
        ->assertSee('value="2026-08" selected', false)
    ->assertSee('Aug 2026');

    $this->withHeader('X-Requested-With', 'XMLHttpRequest')
    ->get(route('billing', [
        'status' => 'paid',
        'billing_period' => '2026-08',
    ]))
    ->assertOk()
    ->assertSee('Paid August Subscriber')
    ->assertDontSee('Overdue August Subscriber')
    ->assertDontSee('Paid September Subscriber');

    $this->get(route('billing', [
    'status' => 'overdue',
    'billing_period' => '2026-08',
    ]))
    ->assertOk();

    $this->get(route('billing', [
    'status' => 'overdue',
    'billing_period' => '2026-08',
    ]), ['X-Requested-With' => 'XMLHttpRequest'])
    ->assertOk()
    ->assertSee('Overdue August Subscriber')
    ->assertDontSee('Paid August Subscriber')
    ->assertDontSee('Paid September Subscriber');

    $this->get(route('billing', ['search' => 'paid-august']), [
        'X-Requested-With' => 'XMLHttpRequest',
    ])
        ->assertOk()
        ->assertSee('Paid August Subscriber')
        ->assertDontSee('Overdue August Subscriber');

    $this->get(route('billing', ['search' => '100']), [
        'X-Requested-With' => 'XMLHttpRequest',
    ])
        ->assertOk()
        ->assertDontSee('Paid August Subscriber')
        ->assertDontSee('Overdue August Subscriber')
        ->assertDontSee('Paid September Subscriber');

    $this->get(route('billing', ['status' => 'pending']))
        ->assertSessionHasErrors('status');
});
