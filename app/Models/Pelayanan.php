<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Pelayanan extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id',
        'nomor_jadwal',
        'nama_pelayanan',
        'tanggal',
        'waktu',
        'lokasi',
        'keterangan',
        'status',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu'   => 'datetime:H:i',
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
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    /**
     * Generate nomor jadwal yang aman terhadap soft delete
     */

    public static function generateNomorJadwalForDate($tanggal)
    {
        $prefix = date('Ymd', strtotime($tanggal));

        $last = self::withTrashed()
            ->whereDate('tanggal', $tanggal)
            ->orderBy('created_at', 'desc')
            ->first();

        $lastNumber = $last 
            ? intval(substr($last->nomor_jadwal, -3)) 
            : 0;

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

        $random = substr(Str::uuid()->toString(), 0, 8); // ambil 8 karakter saja

        return "JDW-{$prefix}-{$newNumber}-{$random}";
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(PelayananDetail::class);
    }
}