<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LatihanPelayananDetail extends Model
{
    protected $table = 'latihan_pelayanan_detail';

    protected $fillable = ['latihan_pelayanan_id', 'jemaat_id', 'role'];

    public function latihan()
    {
        return $this->belongsTo(LatihanPelayanan::class);
    }

    public function jemaat()
    {
        return $this->belongsTo(Jemaat::class);
    }
}