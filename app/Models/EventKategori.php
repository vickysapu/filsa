<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EventKategori extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama_kategori', 'slug', 'deskripsi', 'warna', 'icon',
        'is_active', 'urutan', 'show_in_website',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_website' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kategori) {
            if (empty($kategori->slug)) {
                $kategori->slug = Str::slug($kategori->nama_kategori);
            }
        });
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'kategori_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('nama_kategori');
    }

    public function scopeForWebsite($query)
    {
        return $query->where('show_in_website', true);
    }
}