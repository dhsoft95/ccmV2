<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class supporters extends Model
{
    protected $fillable = [
        'first_name', 'last_name', 'dob', 'gander', 'region_id', 'village_id', 'ward_id', 'district_id','candidate_id', 'phone_number', 'promised', 'other_supporter_details'
    ];

    use HasFactory;
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function candidate(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(candidates::class);
    }

    public function region(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(regions::class);
    }
    public function village(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(village::class);
    }
    public function ward(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ward::class);
    }
    public function candidates(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(candidates::class);
    }
    public function district()
    {
        return $this->belongsTo(districts::class);
    }
}
