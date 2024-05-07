<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class areas extends Model
{
    protected $fillable = ['name', 'other_area_details'];
    use HasFactory;

    public function messages()
    {
        return $this->hasMany(messages::class);
    }
}
