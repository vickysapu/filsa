<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DokumentasiKategori extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id',
        'nama_kategori', 'slug', 'deskripsi', 'warna',
        'is_active', 'show_in_website', 'urutan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show_in_website' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->nama_kategori);
            }
        });
    }

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(brances::class);
    }

    public function albums(): HasMany
    {
        return $this->hasMany(DokumentasiAlbum::class, 'kategori_id');
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