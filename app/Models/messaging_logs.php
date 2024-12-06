<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class messaging_logs extends Model
{   /**
 * The attributes that are mass assignable.
 *
 * @var array
 */
    protected $fillable = [
        'supporter_id',
        'channel',
        'success',
        'response',
    ];

    /**
     * Get the supporter associated with the messaging log.
     */
    public function supporter()
    {
        return $this->belongsTo(Supporters::class);
    }
}
