<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataAccessLog extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'token_id',
        'endpoint',
        'method',
        'table_accessed',
        'columns_accessed',
        'rows_returned',
        'ip_address',
        'user_agent',
        'accessed_at',
    ];

    protected $casts = [
        'columns_accessed' => 'array',
        'accessed_at' => 'datetime',
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(ApiAccessToken::class, 'token_id');
    }
}