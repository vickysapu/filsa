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

    protected $casts = [
        'parent_id' => 'array',
        'is_active' => 'boolean',
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

    /**
     * Get all parent structures (handles both single and multiple parents)
     */
    public function getParentStructures()
    {
        if (empty($this->parent_id)) {
            return collect();
        }

        $parentIds = is_array($this->parent_id) ? $this->parent_id : [$this->parent_id];
        return ChurchStructure::whereIn('id', $parentIds)->get();
    }

    /**
     * Add a parent ID to the parent_id array
     */
    public function addParent($parentId)
    {
        if (empty($this->parent_id)) {
            $this->parent_id = [$parentId];
        } else {
            $parents = is_array($this->parent_id) ? $this->parent_id : [$this->parent_id];
            if (!in_array($parentId, $parents)) {
                $parents[] = $parentId;
                $this->parent_id = $parents;
            }
        }
        return $this;
    }

    /**
     * Remove a parent ID from the parent_id array
     */
    public function removeParent($parentId)
    {
        if (empty($this->parent_id)) {
            return $this;
        }

        $parents = is_array($this->parent_id) ? $this->parent_id : [$this->parent_id];
        $this->parent_id = array_values(array_filter($parents, fn($id) => $id !== $parentId)) ?: null;
        return $this;
    }

    /**
     * Check if has a specific parent
     */
    public function hasParent($parentId)
    {
        if (empty($this->parent_id)) {
            return false;
        }

        $parents = is_array($this->parent_id) ? $this->parent_id : [$this->parent_id];
        return in_array($parentId, $parents);
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
    /**
     * Scope to find all children of a given parent ID
     * Since parent_id is now JSON, we need to check if parent ID is in the array
     */
    public function scopeChildrenOf($query, $parentId)
    {
        return $query->whereRaw('JSON_CONTAINS(parent_id, JSON_QUOTE(?))', [$parentId])->orderBy('sort_order');
    }}