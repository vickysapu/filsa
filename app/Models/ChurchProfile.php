<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChurchProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['branch_id', 'church_name', 'tagline', 'address', 'phone', 'whatsapp', 'email', 'website', 'history', 'vision', 'mission', 'logo_path', 'documents', 'facebook', 'instagram', 'youtube', 'tiktok', 'map_embed_code', 'map_latitude', 'map_longitude', 'is_published', 'published_at'];

    protected $casts = ['values' => 'array', 'gallery_images' => 'array', 'documents' => 'array', 'meta_keywords' => 'array', 'is_published' => 'boolean', 'published_at' => 'datetime',];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path ? Storage::url($this->logo_path) : asset('images/default-church-logo.png');
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
