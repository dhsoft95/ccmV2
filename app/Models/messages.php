<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class messages extends Model
{
    protected $fillable = ['candidate_id', 'area_id', 'subject', 'body', 'sent_at'];
    use HasFactory;

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }

    public function area()
    {
        return $this->belongsTo(areas::class);
    }

//    public function recipients()
//    {
//        return $this->hasMany(Recipient::class);
//    }
}
