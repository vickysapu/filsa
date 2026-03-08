<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginActivities extends Model
{
    protected $fillable = [
        'user_id',
        'email_attempt',
        'ip_address',
        'user_agent',
        'success',
        'failure_reason',
        'attempted_at',
    ];

    protected $casts = [
        'success' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    public $timestamps = false;

    const CREATED_AT = 'attempted_at';
    const UPDATED_AT = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDeviceAttribute(): string
    {
        if (str_contains($this->user_agent ?? '', 'Mobile')) {
            return 'Mobile';
        }
        return 'Desktop';
    }
}