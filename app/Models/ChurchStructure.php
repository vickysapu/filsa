<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ChurchStructure extends Model
{
    protected $fillable = [
        'branch_id',
        'parent_id',
        'jemaat_id',
        'position_name',
        'position_level',
        'sort_order',
        'description',
        'is_active',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

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
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function jemaat(): BelongsTo
    {
        return $this->belongsTo(Jemaat::class, 'jemaat_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChurchStructure::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChurchStructure::class, 'parent_id')->orderBy('sort_order');
    }

    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHierarchy($query, $parentId = null)
    {
        return $query->where('parent_id', $parentId)->orderBy('sort_order');
    }
}