<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscriber extends Model
{
    protected $fillable = [
        'full_name',
        'username',
        'password',
        'phone_number',
        'address',
        'plan_name',
        'monthly_fee',
        'status',
        'profile_photo',
    ];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
    ];

    public function billingRecords(): HasMany
    {
        return $this->hasMany(BillingRecord::class);
    }

    public function paymentSubmissions(): HasMany
    {
        return $this->hasMany(PaymentSubmission::class);
    }
}