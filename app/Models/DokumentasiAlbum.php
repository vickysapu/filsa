<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class DokumentasiAlbum extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
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

        static::creating(function ($album) {
            if (empty($album->slug)) {
                $album->slug = self::generateUniqueSlug($album->judul_album);
            }
        });

        static::deleting(function ($album) {
            // Delete all photos when album is deleted
            $album->photos()->delete();
        });
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

    public function kategori()
    {
        return $this->belongsTo(DokumentasiKategori::class, 'kategori_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function photos()
    {
        return $this->hasMany(DokumentasiPhoto::class, 'album_id')->orderBy('urutan');
    }

    public function creator()
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
        
        // Update cover if none set
        if (!$this->cover_image && $count > 0) {
            $firstPhoto = $this->photos()->first();
            if ($firstPhoto) {
                $this->update(['cover_image' => $firstPhoto->file_path]);
            }
        }
    }
}