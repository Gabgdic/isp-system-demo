<?php

use App\Models\Subscriber;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('admin can create and update a subscriber with structured name and address', function () {
    $admin = User::create([
        'username' => 'subscriber-details-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);
    $plan = SubscriptionPlan::create([
        'plan_name' => 'Basic',
        'price' => 500,
    ]);

    $this->actingAs($admin);

    $this->post(route('subscriber.store'), [
        'first_name' => 'Maria',
        'middle_name' => 'Clara',
        'surname' => 'Dela Cruz',
        'suffix' => 'Jr.',
        'username' => 'maria-dela-cruz',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'facebook_name' => 'Maria Clara Dela Cruz',
        'phone_number' => '09123456789',
        'subscription_plan_id' => $plan->id,
        'barangay' => 'Benit',
        'municipality' => 'Sogod',
        'province' => 'Southern Leyte',
    ])->assertRedirect(route('subscriber'));

    $subscriber = Subscriber::where('username', 'maria-dela-cruz')->firstOrFail();
    expect($subscriber->full_name)->toBe('Maria Clara Dela Cruz Jr.')
        ->and($subscriber->address)->toBe('Benit, Sogod, Southern Leyte')
        ->and($subscriber->facebook_name)->toBe('Maria Clara Dela Cruz');

    $this->put(route('subscriber.update', $subscriber->id), [
        'first_name' => 'Maria',
        'middle_name' => null,
        'surname' => 'Dela Cruz',
        'suffix' => 'N/A',
        'username' => 'maria-dela-cruz',
        'facebook_name' => 'Maria C. Dela Cruz',
        'phone_number' => '09123456789',
        'subscription_plan_id' => $plan->id,
        'barangay' => 'Banahao',
        'municipality' => 'Bontoc',
        'province' => 'Southern Leyte',
    ])->assertRedirect(route('subscriber'));

    expect($subscriber->fresh()->full_name)->toBe('Maria Dela Cruz')
        ->and($subscriber->fresh()->address)->toBe('Banahao, Bontoc, Southern Leyte')
        ->and($subscriber->fresh()->facebook_name)->toBe('Maria C. Dela Cruz');

    $this->put(route('subscriber.update', $subscriber->id), [
        'first_name' => 'Maria',
        'middle_name' => null,
        'surname' => 'Dela Cruz',
        'suffix' => 'N/A',
        'username' => 'maria-dela-cruz',
        'facebook_name' => 'Maria C. Dela Cruz',
        'phone_number' => '09123456789',
        'subscription_plan_id' => $plan->id,
        'barangay' => 'Zone I (Pob.)',
        'municipality' => 'Sogod',
        'province' => 'Southern Leyte',
    ])->assertRedirect(route('subscriber'));

    expect($subscriber->fresh()->address)->toBe('Zone I (Pob.), Sogod, Southern Leyte');

    $this->put(route('subscriber.update', $subscriber->id), [
        'first_name' => 'Maria',
        'middle_name' => null,
        'surname' => 'Dela Cruz',
        'suffix' => 'N/A',
        'username' => 'maria-dela-cruz',
        'facebook_name' => 'Maria C. Dela Cruz',
        'phone_number' => '09123456789',
        'subscription_plan_id' => $plan->id,
        'barangay' => 'Asuncion',
        'municipality' => 'Bontoc',
        'province' => 'Southern Leyte',
    ])->assertSessionHasErrors('barangay');

    $this->get(route('subscriber'))
        ->assertOk()
        ->assertSee('First Name')
        ->assertSee('Facebook Name')
        ->assertSee('Barangay')
        ->assertSee('Benit')
        ->assertSee('Zone V (Pob.)')
        ->assertSee('Banahao');
});

test('subscriber contact schema uses facebook name instead of email', function () {
    expect(Schema::hasColumn('subscribers', 'facebook_name'))->toBeTrue()
        ->and(Schema::hasColumn('subscribers', 'email'))->toBeFalse();
});
