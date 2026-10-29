<?php

use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

test('admin dashboard displays the redesigned billing and system overview', function () {
    $admin = User::create([
        'username' => 'dashboard-admin',
        'password' => Hash::make('Password123!'),
        'role' => 'admin',
    ]);

    Subscriber::create([
        'full_name' => 'Dashboard Subscriber',
        'username' => 'dashboard-subscriber',
        'password' => Hash::make('Password123!'),
        'monthly_fee' => 500,
        'status' => 'active',
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Management dashboard')
        ->assertSee('billing overview')
        ->assertSee('Pending review')
        ->assertSee('System overview')
        ->assertSee('Manage subscribers')
        ->assertSee('Estimated monthly recurring revenue')
        ->assertSee(route('subscriber'))
        ->assertDontSee('Review billing')
        ->assertDontSee('At a glance')
        ->assertDontSee('Your network')
        ->assertDontSee('Administrators')
        ->assertDontSee('<svg class="h-5 w-5"', false);
});
