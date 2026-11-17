<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\MorphMany; 
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;

/**
 * @property-read DatabaseNotificationCollection $notifications
 * @method static DatabaseNotificationCollection notifications()
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'branch_id', 'name', 'email', 'password', 'is_active', 'last_login_at', 'avatar'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
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

        public function notifications(): MorphMany
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')
                    ->orderBy('created_at', 'desc');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function jemaat(): BelongsTo
    {
        return $this->belongsTo(Jemaat::class, 'id', 'id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')->withTimestamps();
    }

    public function isHeadquarter(): bool
    {
        return $this->branch && $this->branch->is_headquarter;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('administrator') || $this->isHeadquarter();
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->roles()
            ->whereHas('permissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->exists();
    }

    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function assignRole(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
    }

    public function getRoleNamesAttribute(): string
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->pluck('display_name')->join(', ');
        }
        return $this->roles()->get()->pluck('display_name')->join(', ');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
