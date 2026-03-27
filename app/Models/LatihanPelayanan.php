<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LatihanPelayanan extends Model
{
    use HasFactory;

    protected $table = 'latihan_pelayanan';

    protected $fillable = [
        'kategori_id', 'nama_latihan', 'tanggal', 'waktu', 
        'lokasi', 'keterangan', 'status', 'created_by'
    ];

    protected $dates = ['tanggal'];

    public function kategori()
    {
        return $this->belongsTo(KategoriPelayanan::class);
    }

    public function details()
    {
        return $this->hasMany(LatihanPelayananDetail::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}