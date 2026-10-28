<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscriber extends Model
{
    protected $fillable = [
        'full_name',
        'username',
        'password',
        'email',
        'phone_number',
        'address',
        'subscription_plan_id', // Added
        'plan_name',
        'monthly_fee',
        'status',
        'profile_photo',
    ];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
    ];

    /**
     * Get the subscription plan associated with the subscriber.
     */
    public function subscriptionPlan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function billingRecords(): HasMany
    {
        return $this->hasMany(BillingRecord::class);
    }

    public function paymentSubmissions(): HasMany
    {
        return $this->hasMany(PaymentSubmission::class);
    }

    public function syncOverdueStatus(): string
    {
        $this->billingRecords()
            ->where('status', 'unpaid')
            ->whereDate('due_date', '<', now()->toDateString())
            ->update(['status' => 'overdue']);

        $overdueCount = $this->billingRecords()
            ->where('status', 'overdue')
            ->count();

        $newStatus = 'active';
        if ($overdueCount >= 2) {
            $newStatus = 'disconnected';
        } elseif ($this->status === 'disconnected') {
            return $this->status;
        } elseif ($overdueCount === 1) {
            $newStatus = 'inactive';
        }

        if ($this->status !== $newStatus) {
            $this->status = $newStatus;
            $this->save();
        }

        return $newStatus;
    }

    public function updateStatusBasedOnBills(): string
    {
        return $this->syncOverdueStatus();
    }
}