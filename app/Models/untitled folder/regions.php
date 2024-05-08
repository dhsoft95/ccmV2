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
    use HasFactory;
    public function candidates()
    {
        return $this->hasMany(candidates::class);
    }

    public function districts()
    {
        return $this->hasMany(districts::class);
    }

    public function location()
    {
        return $this->belongsTo(locations::class);
    }

    public function villages()
    {
        return $this->hasMany(Village::class);
    }

    public function wards()
    {
        return $this->hasManyThrough(Ward::class, districts::class);
    }
}
