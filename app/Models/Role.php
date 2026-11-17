<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
class Role extends Model
{
    use SoftDeletes;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['branch_id', 'name', 'display_name', 'description', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
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
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permission')->withTimestamps();
    }
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role')->withTimestamps();
    }
    public function isGlobal(): bool
    {
        return $this->branch_id === null;
    }
    public function isAccessibleBy(User $user): bool
    {
        return $user->isSuperAdmin() || $this->isGlobal() || $this->branch_id === $user->branch_id;
    }
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }
    public function givePermission(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function scopeAccessibleBy($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->whereNull('branch_id')->orWhere('branch_id', $user->branch_id);
        });
    }
}
