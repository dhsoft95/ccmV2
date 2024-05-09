<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class candidates extends Model
{

    use HasFactory, Notifiable ,HasApiTokens;
    protected $fillable = [

        'full_name',
        'phone',
        'email',
        'party_affiliation',
        'position_id',
        'region_id',
        'village_id',
        'ward_id',
        'district_id',
        'password',
        'other_candidate_details'
    ];

    use HasFactory;

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function position(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(positions::class);
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

    public function district()
    {
        return $this->belongsTo(districts::class);
    }

    public function messages(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(messages::class);
    }
    public function supporters(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(supporters::class);
    }

}
