<?php

use App\Models\BillingRecord;
use App\Models\Subscriber;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('subscriber billing history displays the total number of bills', function () {
    $subscriber = Subscriber::create([
        'full_name' => 'History Test Subscriber',
        'username' => 'history-test',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 500,
        'status' => 'active',
    ]);
    $subscriber->created_at = Carbon::now()->subMonths(5);
    $subscriber->save();

    foreach ([now()->subMonth(), now()] as $month) {
        BillingRecord::create([
            'subscriber_id' => $subscriber->id,
            'billing_month' => $month->month,
            'billing_year' => $month->year,
            'amount' => 500,
            'due_date' => $month->copy()->addDays(10)->toDateString(),
            'status' => 'unpaid',
        ]);
    }

    $this->withSession([
        'subscriber_logged_in' => true,
        'subscriber_id' => $subscriber->id,
    ])->get(route('subscriber.portal'))
        ->assertOk()
        ->assertSee('2 bills')
        ->assertSee('All bills on your account')
        ->assertSee('5 months')
        ->assertSee('Thank you for being a valued subscriber!')
        ->assertSee('Contact Phone:')
        ->assertSee('name="first_name"', false)
        ->assertSee('name="middle_name"', false)
        ->assertSee('name="surname"', false)
        ->assertSee('name="suffix"', false)
        ->assertSee('name="facebook_name"', false)
        ->assertSee('name="phone_number"', false);
});

test('subscriber can update phone number and Facebook name from the portal', function () {
    $subscriber = Subscriber::create([
        'full_name' => 'Profile Update Subscriber',
        'first_name' => 'Profile',
        'middle_name' => null,
        'surname' => 'Update',
        'suffix' => 'N/A',
        'username' => 'profile-update-test',
        'password' => Hash::make('Password123!'),
        'phone_number' => '09111111111',
        'facebook_name' => 'Old Facebook Name',
        'monthly_fee' => 500,
        'status' => 'active',
    ]);

    $this->withSession([
        'subscriber_logged_in' => true,
        'subscriber_id' => $subscriber->id,
    ])->post(route('subscriber.profile.update'), [
        'first_name' => 'Updated',
        'middle_name' => 'Portal',
        'surname' => 'Subscriber',
        'suffix' => 'Jr.',
        'facebook_name' => 'Updated Facebook Name',
        'phone_number' => '09222222222',
    ])->assertRedirect()
        ->assertSessionHas('success', 'Profile updated successfully.');

    expect($subscriber->fresh()->facebook_name)->toBe('Updated Facebook Name')
        ->and($subscriber->fresh()->phone_number)->toBe('09222222222')
        ->and($subscriber->fresh()->first_name)->toBe('Updated')
        ->and($subscriber->fresh()->middle_name)->toBe('Portal')
        ->and($subscriber->fresh()->surname)->toBe('Subscriber')
        ->and($subscriber->fresh()->suffix)->toBe('Jr.')
        ->and($subscriber->fresh()->full_name)->toBe('Updated Portal Subscriber Jr.');
});
