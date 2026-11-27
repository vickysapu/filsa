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

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'branch_id',
        'church_name',
        'address',
        'city',
        'phone',
        'email',
        'history',
        'vision',
        'mission',
        'logo_path',
        'facebook',
        'instagram',
        'youtube',
        'tiktok',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getLogoUrlAttribute()
    {
        return $this->logo_path
            ? Storage::url($this->logo_path)
            : asset('images/default-church-logo.png');
    }

    public function getFullAddressAttribute()
    {
        return implode(', ', array_filter([$this->address, $this->city])) ?: '-';
    }

    public function getSocialLinksAttribute()
    {
        return array_filter([
            'facebook'  => $this->facebook,
            'instagram' => $this->instagram,
            'youtube'   => $this->youtube,
            'tiktok'    => $this->tiktok,
        ]);
    }
}