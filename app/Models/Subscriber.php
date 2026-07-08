<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}