<?php
// app/Models/SecurityToken.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SecurityToken extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id', 'name', 'token', 'prefix', 'abilities',
        'expires_at', 'is_active', 'created_by', 'ip_address',
    ];

    protected $casts = [
        'abilities' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime',
        'is_active' => 'boolean',
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

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    // Generate token baru dengan prefix
    public static function generateToken(string $prefix = 'sk'): array
    {
        $random = Str::random(32);
        $fullToken = "{$prefix}_" . bin2hex(random_bytes(24));
        
        return [
            'plain' => $fullToken,
            'hashed' => hash('sha256', $fullToken),
            'prefix' => substr($fullToken, 0, 16),
        ];
    }

    // Validasi token plain terhadap hashed di DB
    public static function validateToken(string $plainToken): ?self
    {
        $hashed = hash('sha256', $plainToken);
        $prefix = substr($plainToken, 0, 16);
        
        return self::where('prefix', $prefix)
            ->where('token', $hashed)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->first();
    }

    // Update last_used_at
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }

    // Revoke token
    public function revoke(): void
    {
        $this->update(['is_active' => false]);
    }
}