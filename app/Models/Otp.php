<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    protected $table = 'otps';

    protected $fillable = [
        'phone',
        'otp',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];
}
