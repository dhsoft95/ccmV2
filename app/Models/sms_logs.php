<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class sms_logs extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'recipient',
        'status',
        'message',
    ];

    // Cast the 'status' attribute to a boolean
    protected $casts = [
        'status' => 'boolean',
    ];


    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}

