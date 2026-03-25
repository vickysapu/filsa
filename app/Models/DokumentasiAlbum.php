<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class DokumentasiAlbum extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id',
        'slug', 'judul_album', 'deskripsi', 'kategori_id', 'event_id',
        'cover_image', 'fotografer', 'tanggal_album', 'lokasi',
        'jumlah_foto', 'status', 'is_featured', 'allow_download',
        'show_in_website', 'meta_tags', 'created_by',
    ];

    protected $casts = [
        'tanggal_album' => 'date',
        'jumlah_foto' => 'integer',
        'is_featured' => 'boolean',
        'allow_download' => 'boolean',
        'show_in_website' => 'boolean',
        'meta_tags' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = self::generateUniqueSlug($model->judul_album);
            }
        });

        static::deleting(function ($album) {
            $album->photos()->delete();
        });
    }

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public static function generateUniqueSlug($name)
    {
        $slug = Str::slug($name);
        $count = 1;
        $originalSlug = $slug;
        
        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }
        
        return $slug;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(DokumentasiKategori::class, 'kategori_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DokumentasiPhoto::class, 'album_id')->orderBy('urutan');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('show_in_website', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForWebsite($query)
    {
        return $query->where('show_in_website', true);
    }

    public function updateFotoCount()
    {
        $count = $this->photos()->count();
        $this->update(['jumlah_foto' => $count]);
        
        if (!$this->cover_image && $count > 0) {
            $firstPhoto = $this->photos()->first();
            if ($firstPhoto) {
                $this->update(['cover_image' => $firstPhoto->file_path]);
            }
        }
    }
}