<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PelayananDetail extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id',
        'pelayanan_id',
        'jemaat_id',
        'role',
        'status',
        'tanggal_off',
        'alasan_off',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_off' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid();
            }
        });
    }

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function pelayanan(): BelongsTo
    {
        return $this->belongsTo(Pelayanan::class);
    }

    public function jemaat(): BelongsTo
    {
        return $this->belongsTo(Jemaat::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOff($query)
    {
        return $query->where('status', 'off');
    }
}