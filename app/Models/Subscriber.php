<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Subscriber extends Model
{
    protected $fillable = [
        'first_name',
        'middle_name',
        'surname',
        'suffix',
        'barangay',
        'municipality',
        'province',
        'full_name',
        'username',
        'password',
        'facebook_name',
        'phone_number',
        'address',
        'subscription_plan_id',
        'plan_name',
        'monthly_fee',
        'status',
        'profile_photo',
    ];

    protected $casts = [
        'monthly_fee' => 'decimal:2',
        'disconnected_at' => 'datetime',
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

    public function disconnections(): HasMany
    {
        return $this->hasMany(SubscriberDisconnection::class)
            ->orderByDesc('disconnected_at');
    }

    public function officeVisitRequests(): HasMany
    {
        return $this->hasMany(SubscriberOfficeVisitRequest::class);
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
            if ($newStatus === 'disconnected') {
                $disconnectedAt = now();
                DB::transaction(function () use ($disconnectedAt): void {
                    $this->disconnected_at = $disconnectedAt;
                    $this->status = 'disconnected';
                    $this->save();
                    $this->disconnections()->create([
                        'disconnected_at' => $disconnectedAt,
                    ]);
                });
            } else {
                $this->status = $newStatus;
                $this->save();
            }
        }

        return $newStatus;
    }

    public function updateStatusBasedOnBills(): string
    {
        return $this->syncOverdueStatus();
    }
}