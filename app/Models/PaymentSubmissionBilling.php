<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentSubmissionBilling extends Model
{
    protected $fillable = [
        'payment_submission_id',
        'billing_record_id',
        'amount_applied',
    ];

    protected $casts = [
        'amount_applied' => 'decimal:2',
    ];

    public function paymentSubmission(): BelongsTo
    {
        return $this->belongsTo(
            PaymentSubmission::class
        );
    }

    public function billingRecord(): BelongsTo
    {
        return $this->belongsTo(
            BillingRecord::class
        );
    }
}