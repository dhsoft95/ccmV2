<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class districts extends Model
{

    protected $fillable = ['region_id', 'name', 'other_district_details'];
    use HasFactory;

    public function region()
    {
        return $this->belongsTo(regions::class);
    }
    public function candidates()
    {
        return $this->hasMany(candidates::class);
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
        return $this->hasMany(ward::class);
    }


}
