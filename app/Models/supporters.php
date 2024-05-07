<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class supporters extends Model
{
    protected $fillable = ['user_id','full_name','dob','gander', 'candidate_id', 'phone_number', 'promised', 'other_supporter_details'];

    use HasFactory;
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function candidate(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(candidates::class);
    }
}
