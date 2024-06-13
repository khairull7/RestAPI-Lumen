<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lending extends Model
{
    use SoftDeletes;

    // protected $table = 'restoration';

    protected $fillable= ["stuff_id", "date_time", "name", "user_id", "notes", "total_stuff"];

    public function user()
    {
        return $this->belongsTo(User::class);
        // return $this->belongsTo(User::class, 'stuff_id','id');
        // jika kolom fk tidak sesuai maka perlu di definisikan lagi
    }

    public function stuff() 
    {
        return $this->belongsTo(Stuff::class);
    }

    public function restoration()
    {
        return $this->hasOne(Restoration::class);
    }
}
