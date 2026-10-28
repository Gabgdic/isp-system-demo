<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'system_name',
        'system_logo',
        'gcash_name',
        'gcash_number',
        'soa_location',
        'authorized_signatory',
        'signatory_title',
        'authorized_signature',
        'soa_body_statement',
    ];
}