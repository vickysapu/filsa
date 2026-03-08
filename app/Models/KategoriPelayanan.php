<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KategoriPelayanan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['nama_kategori', 'deskripsi', 'warna', 'is_active', 'urutan'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pelayanans()
    {
        return $this->hasMany(Pelayanan::class, 'kategori_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('nama_kategori');
    }
}
