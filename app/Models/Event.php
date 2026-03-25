<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id',
        'slug', 'nama_event', 'deskripsi', 'deskripsi_lengkap', 'kategori_id',
        'tanggal_mulai', 'tanggal_selesai', 'lokasi', 'alamat_lengkap',
        'gambar_featured', 'gambar_galeri', 'pembicara', 'kontak_person',
        'email_kontak', 'whatsapp_kontak', 'harga_tiket', 'kuota_peserta',
        'jumlah_terdaftar', 'status', 'is_featured', 'show_in_website',
        'meta_tags', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'harga_tiket' => 'decimal:2',
        'gambar_galeri' => 'array',
        'meta_tags' => 'array',
        'is_featured' => 'boolean',
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
                $model->slug = self::generateUniqueSlug($model->nama_event);
            }
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
        return $this->belongsTo(EventKategori::class, 'kategori_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function dokumentasiAlbums(): HasMany
    {
        return $this->hasMany(DokumentasiAlbum::class);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('show_in_website', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_mulai', '>=', now())
            ->whereIn('status', ['published', 'ongoing']);
    }

    public function scopeOngoing($query)
    {
        return $query->where('tanggal_mulai', '<=', now())
            ->where(function($q) {
                $q->whereNull('tanggal_selesai')
                  ->orWhere('tanggal_selesai', '>=', now());
            })
            ->whereIn('status', ['published', 'ongoing']);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => ['label' => 'Draft', 'color' => 'lux-badge-warning'],
            'published' => ['label' => 'Published', 'color' => 'lux-badge-success'],
            'ongoing' => ['label' => 'Berlangsung', 'color' => 'lux-badge-info'],
            'completed' => ['label' => 'Selesai', 'color' => 'lux-badge-secondary'],
            'cancelled' => ['label' => 'Dibatalkan', 'color' => 'lux-badge-danger'],
        ];
        return $labels[$this->status] ?? ['label' => $this->status, 'color' => ''];
    }

    public function getIsRegistrationOpenAttribute()
    {
        if ($this->kuota_peserta === null) return true;
        return $this->jumlah_terdaftar < $this->kuota_peserta;
    }

    public function getRemainingQuotaAttribute()
    {
        if ($this->kuota_peserta === null) return null;
        return max(0, $this->kuota_peserta - $this->jumlah_terdaftar);
    }
}