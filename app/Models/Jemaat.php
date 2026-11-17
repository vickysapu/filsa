<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Models\Scopes\BranchScope;

class Jemaat extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['branch_id', 'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'no_hp', 'email', 'pekerjaan', 'pendidikan', 'status_jemaat', 'foto', 'priority'];

    protected $casts = [
        'jenis_kelamin' => 'boolean',
        'tanggal_lahir' => 'date',
        'tanggal_bergabung' => 'date',
        'status_jemaat' => 'boolean',
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

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope());
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Scope untuk jemaat aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status_jemaat', true);
    }

    /**
     * Get jenis kelamin label
     */
    public function getJenisKelaminLabelAttribute()
    {
        return $this->jenis_kelamin ? 'Laki-laki' : 'Perempuan';
    }

    /**
     * Get usia jemaat
     */
    public function getUsiaAttribute()
    {
        return $this->tanggal_lahir ? $this->tanggal_lahir->age : null;
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'id');
    }
}
