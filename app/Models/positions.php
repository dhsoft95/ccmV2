<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class positions extends Model
{
    protected $fillable = ['name', 'description', 'other_position_details'];

    use HasFactory;

    public function candidates()
    {
        return $this->hasMany(Candidate::class);
    }
}
