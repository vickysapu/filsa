<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DataAccessRequest extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = ['id', 'requester_name', 'requester_email', 'project_name', 'project_url', 'purpose', 'requested_tables', 'branch_id', 'branch_name', 'status', 'approved_by', 'email_verified_at', 'reviewed_at', 'rejection_reason', 'ip_address', 'user_agent', 'request_hash'];

    protected $casts = [
        'requested_tables' => 'array',
        'email_verified_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function grants(): HasMany
    {
        return $this->hasMany(DataAccessGrant::class, 'request_id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(RequestVerification::class, 'request_id');
    }

    public function isVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getTableNamesAttribute(): array
    {
        return $this->requested_tables ?? [];
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
