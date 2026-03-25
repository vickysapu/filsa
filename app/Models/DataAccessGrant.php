<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DataAccessGrant extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',  
        'request_id',
        'table_name',
        'allowed_columns',
        'restricted_columns',
        'filters',
        'allow_create',
        'allow_update',
        'allow_delete',
        'rate_limit_per_minute',
        'expires_at',
    ];

    protected $casts = [
        'allowed_columns' => 'array',
        'restricted_columns' => 'array',
        'filters' => 'array',
        'allow_create' => 'boolean',
        'allow_update' => 'boolean',
        'allow_delete' => 'boolean',
        'rate_limit_per_minute' => 'integer',
        'expires_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(DataAccessRequest::class, 'request_id');
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(ApiAccessToken::class, 'grant_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && now()->gt($this->expires_at);
    }

    public function isActive(): bool
    {
        return !$this->isExpired() && $this->request->status === 'approved';
    }
}