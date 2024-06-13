<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Stuff extends Model
{
    use SoftDeletes; // optional digunakan hanya untuk table yang menggunakan fitur softdeletes
    protected $fillable = ["name", "category"];

    // mendefinisikan relasi
    // table yang berperan sebagai Primary Key : hasOne / hasMany / ...
    // table yang berperan sebagai Foreign Key : belongsTo
    // nama function disarankan menggunakan aturan berikut :
    // 1. one to one : nama model yang terhubung versi tunggal
    // 2. one to many :nama model yang terhubung versi jamak ( untuk foreign key nya)
    public function StuffStock()
    {
        return $this->hasOne(StuffStock::class);
    }

    public function inboundStuffs()
    {
        return $this->hasMany(InboundStuff::class);
    }

    public function lendings()
    {
        return $this->hasMany(Lending::class);
    }
}
