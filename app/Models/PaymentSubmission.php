<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentSubmission extends Model
{
    protected $fillable = [
        'billing_record_id',
        'subscriber_id',
        'amount_paid',
        'reference_number',
        'payment_screenshot',
        'status',
        'admin_note',
        'reviewed_at',
    ];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function billingRecord(): BelongsTo
    {
        return $this->belongsTo(BillingRecord::class);
    }

    public function billingAllocations(): HasMany
    {
        return $this->hasMany(
            PaymentSubmissionBilling::class
        );
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }
}