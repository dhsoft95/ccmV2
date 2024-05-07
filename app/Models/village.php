<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class village extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'region_id',
        'district_id',
        'other_village_details'
    ];

    public function region()
    {
        return $this->belongsTo(regions::class);
    }

    public function district()
    {
        return $this->belongsTo(districts::class);
    }
}
