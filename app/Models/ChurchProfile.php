<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ChurchProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['branch_id', 'church_name', 'tagline', 'address', 'city', 'province', 'postal_code', 'country', 'phone', 'whatsapp', 'email', 'website', 'history', 'vision', 'mission', 'values', 'logo_path', 'gallery_images', 'documents', 'facebook', 'instagram', 'youtube', 'tiktok', 'meta_title', 'meta_description', 'meta_keywords', 'map_embed_code', 'map_latitude', 'map_longitude', 'service_time_sunday', 'service_time_wednesday', 'service_time_friday', 'service_note', 'is_published', 'published_at'];

    protected $casts = [
        'values' => 'array',
        'gallery_images' => 'array',
        'documents' => 'array',
        'meta_keywords' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? Storage::url($this->logo_path) : asset('images/default-church-logo.png');
    }

    public function getGalleryUrlsAttribute()
    {
        return collect($this->gallery_images ?? [])
            ->map(fn($img) => Storage::url($img))
            ->toArray();
    }

    public function getValuesListAttribute()
    {
        return collect($this->values ?? [])
            ->pluck('label')
            ->toArray();
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function getFullAddressAttribute()
    {
        $parts = array_filter([$this->address, $this->city, $this->province, $this->postal_code, $this->country]);
        return implode(', ', $parts) ?: '-';
    }

    public function getSocialLinksAttribute()
    {
        return array_filter([
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'youtube' => $this->youtube,
            'tiktok' => $this->tiktok,
        ]);
    }
}
