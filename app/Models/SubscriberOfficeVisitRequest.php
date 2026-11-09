<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriberOfficeVisitRequest extends Model
{
    protected $fillable = [
        'subscriber_id',
        'request_type',
        'visit_date',
        'status',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }
}
