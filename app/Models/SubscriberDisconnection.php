<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriberDisconnection extends Model
{
    protected $fillable = [
        'subscriber_id',
        'disconnected_at',
    ];

    protected $casts = [
        'disconnected_at' => 'datetime',
    ];

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }
}
