<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class ApiAccessToken extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

        protected $fillable = [
        'id',  
        'grant_id',
        'token_hash',
        'token_prefix',
        'encryption_key',
        'allowed_ips',
        'last_used_at',
        'revoked_at',
    ];

    protected $casts = [
        'allowed_ips' => 'array',
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    protected $hidden = [
        'token_hash',
        'encryption_key',
    ];

    public function grant(): BelongsTo
    {
        return $this->belongsTo(DataAccessGrant::class, 'grant_id');
    }

    public function isRevoked(): bool
    {
        return !is_null($this->revoked_at);
    }

    public function isActive(): bool
    {
        return !$this->isRevoked() && ($this->grant ? $this->grant->isActive() : false);
    }

    public function revoke(): void
    {
        $this->update(['revoked_at' => now()]);
    }

    public static function validateToken(string $providedToken, string $storedHash): bool
    {
        $computedHash = hash('sha512', $providedToken . config('app.key'));
        return hash_equals($storedHash, $computedHash);
    }
}