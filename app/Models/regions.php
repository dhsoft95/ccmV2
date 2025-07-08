<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class regions extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'other_region_details'];

    public function candidates()
    {
        return $this->hasMany(candidate::class);
    }

    public function districts()
    {
        return $this->hasMany(districts::class, 'region_id');
    }

    public function location()
    {
        return $this->belongsTo(locations::class);
    }

    public function villages()
    {
        return $this->hasMany(village::class);
    }

    public function wards()
    {
        return $this->hasManyThrough(ward::class, districts::class);
    }
}
