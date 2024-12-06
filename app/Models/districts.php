<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class districts extends Model
{
    use HasFactory;

    protected $fillable = ['region_id', 'name', 'other_district_details'];

    public function region()
    {
        return $this->belongsTo(regions::class, 'region_id');
    }

    public function candidates()
    {
        return $this->belongsTo(candidates::class);
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
        return $this->hasMany(ward::class, 'district_id'); // Correcting the foreign key name
    }
    public function supporters()
    {
        return $this->hasMany(Supporters::class);
    }
}
