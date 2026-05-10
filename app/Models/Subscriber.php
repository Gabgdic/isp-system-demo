<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscriber extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'full_name',
        'username',
        'email',
        'phone_number',
        'address',
        'plan_name',
        'monthly_fee',
        'status',
        'profile_photo',
    ];
}