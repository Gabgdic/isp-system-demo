<?php

use App\Models\BillingRecord;
use App\Models\Subscriber;
use App\Models\SubscriberOfficeVisitRequest;
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
        ->assertSee('name="phone_number"', false)
        ->assertSee('Request Visit')
        ->assertSee('Request payment reduction')
        ->assertSee('bring or show your Statement of Account (SOA)');
});

test('subscriber can submit a payment reduction request with an office visit date', function () {
    $subscriber = Subscriber::create([
        'full_name' => 'Office Visit Subscriber',
        'username' => 'office-visit-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 500,
        'status' => 'active',
    ]);

    $this->withSession([
        'subscriber_logged_in' => true,
        'subscriber_id' => $subscriber->id,
    ])->post(route('subscriber.office-visit-requests.store'), [
        'request_type' => 'payment_reduction',
        'visit_date' => now()->addDays(3)->toDateString(),
    ])->assertRedirect(route('subscriber.portal'))
        ->assertSessionHas('success');

    $request = SubscriberOfficeVisitRequest::query()->sole();
    expect($request->subscriber_id)->toBe($subscriber->id)
        ->and($request->request_type)->toBe('payment_reduction')
        ->and($request->visit_date->toDateString())->toBe(now()->addDays(3)->toDateString())
        ->and($request->status)->toBe('pending');
});

test('portal displays office visit request review and reschedule updates', function () {
    $subscriber = Subscriber::create([
        'full_name' => 'Office Visit Notice Subscriber',
        'username' => 'office-visit-portal-notice',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 500,
        'status' => 'active',
    ]);

    SubscriberOfficeVisitRequest::create([
        'subscriber_id' => $subscriber->id,
        'request_type' => 'payment_reduction',
        'visit_date' => '2026-12-10',
        'status' => 'accepted',
    ]);
    SubscriberOfficeVisitRequest::create([
        'subscriber_id' => $subscriber->id,
        'request_type' => 'payment_reduction',
        'visit_date' => '2026-12-15',
        'status' => 'rescheduled',
    ]);

    $this->withSession([
        'subscriber_logged_in' => true,
        'subscriber_id' => $subscriber->id,
    ])->get(route('subscriber.portal'))
        ->assertOk()
        ->assertSee('id="subscriberNotificationsToggle"', false)
        ->assertSee('id="subscriberNotificationsPanel"', false)
        ->assertSee('Notifications')
        ->assertSee('Accepted')
        ->assertSee('Please visit the ISP office on Dec 10, 2026')
        ->assertSee('Rescheduled')
        ->assertSee('Your new ISP office visit date is Dec 15, 2026')
        ->assertDontSee('Office Visit Request Updates');
});

test('subscriber cannot request an office visit for a past date', function () {
    $subscriber = Subscriber::create([
        'full_name' => 'Office Visit Subscriber',
        'username' => 'office-visit-past-date',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 500,
        'status' => 'active',
    ]);

    $this->withSession([
        'subscriber_logged_in' => true,
        'subscriber_id' => $subscriber->id,
    ])->from(route('subscriber.portal'))
        ->post(route('subscriber.office-visit-requests.store'), [
            'request_type' => 'payment_reduction',
            'visit_date' => now()->subDay()->toDateString(),
        ])->assertRedirect(route('subscriber.portal'))
        ->assertSessionHasErrors('visit_date');

    expect(SubscriberOfficeVisitRequest::count())->toBe(0);
});

test('portal fills a missing bill due date from the subscribers signup day', function () {
    $subscriber = Subscriber::create([
        'full_name' => 'Missing Due Date Subscriber',
        'username' => 'missing-due-date',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 500,
        'status' => 'active',
    ]);
    $subscriber->created_at = Carbon::parse('2026-10-06');
    $subscriber->save();

    $bill = BillingRecord::create([
        'subscriber_id' => $subscriber->id,
        'billing_month' => 11,
        'billing_year' => 2026,
        'amount' => 500,
        'due_date' => null,
        'status' => 'unpaid',
    ]);

    $this->withSession([
        'subscriber_logged_in' => true,
        'subscriber_id' => $subscriber->id,
    ])->get(route('subscriber.portal'))
        ->assertOk()
        ->assertSee('Dec 06, 2026')
        ->assertDontSee('Uploaded Payment Screenshot');

    expect($bill->fresh()->due_date->toDateString())->toBe('2026-12-06');
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
