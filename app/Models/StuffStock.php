<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StuffStock extends Model
{
    use SoftDeletes;
    protected $fillable = ['stuff_id', 'total_available', 'total_defec'];

    public function stuff()
    {
        return $this->belongsTo(Stuff::class);
    }
}
