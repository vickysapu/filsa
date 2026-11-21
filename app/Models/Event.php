<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['branch_id', 'nama_event', 'slug', 'deskripsi', 'deskripsi_lengkap', 'kategori_id', 'tanggal_mulai', 'tanggal_selesai', 'lokasi', 'alamat_lengkap', 'gambar_featured', 'gambar_galeri', 'pembicara', 'kontak_person', 'email_kontak', 'whatsapp_kontak', 'harga_tiket', 'kuota_peserta', 'status', 'is_featured', 'show_in_website', 'created_by', 'updated_by'];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'gambar_galeri' => 'array',
        'is_featured' => 'boolean',
        'show_in_website' => 'boolean',
        'harga_tiket' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->nama_event) . '-' . substr((string) Str::uuid(), 0, 8);
            }
        });
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

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('show_in_website', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_mulai', '>=', now());
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => ['label' => 'Draft', 'class' => 'secondary'],
            'published' => ['label' => 'Published', 'class' => 'success'],
            'ongoing' => ['label' => 'Berlangsung', 'class' => 'primary'],
            'completed' => ['label' => 'Selesai', 'class' => 'info'],
            'cancelled' => ['label' => 'Dibatalkan', 'class' => 'danger'],
        ];
        return $labels[$this->status] ?? ['label' => 'Unknown', 'class' => 'secondary'];
    }
}
