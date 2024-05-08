<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'region_id',
        'district_id',
        'village_id',
        'other_ward_details'
    ];

    public function region()
    {
        return $this->belongsTo(regions::class);
    }

    public function district()
    {
        return $this->belongsTo(districts::class);
    }

    public function village()
    {
        return $this->belongsTo(village::class);
    }

}
