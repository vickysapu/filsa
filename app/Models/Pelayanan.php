<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pelayanan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor_jadwal',
        'kategori_id',
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
        'waktu' => 'datetime:H:i',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pelayanan) {
            if (empty($pelayanan->nomor_jadwal)) {
                $pelayanan->nomor_jadwal = self::generateNomorJadwal();
            }
        });
    }

    public static function generateNomorJadwal()
    {
        $date = now()->format('Ymd');
        $last = self::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();
        
        $lastNumber = $last ? intval(substr($last->nomor_jadwal, -4)) : 0;
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return 'JDL-' . $date . '-' . $newNumber;
    }

    public function kategori()
    {
        return $this->belongsTo(KategoriPelayanan::class, 'kategori_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(PelayananDetail::class);
    }

    public function jemaats()
    {
        return $this->belongsToMany(Jemaat::class, 'pelayanan_details')
            ->withPivot('status', 'role', 'tanggal_off', 'alasan_off')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal', '>=', now()->startOfDay())
            ->whereIn('status', ['draft', 'active']);
    }

    public function getJemaatCountAttribute()
    {
        return $this->details()->where('status', 'active')->count();
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'draft' => ['label' => 'Draft', 'color' => 'lux-badge-warning'],
            'active' => ['label' => 'Aktif', 'color' => 'lux-badge-success'],
            'completed' => ['label' => 'Selesai', 'color' => 'lux-badge-info'],
            'cancelled' => ['label' => 'Dibatalkan', 'color' => 'lux-badge-danger'],
        ];
        return $labels[$this->status] ?? ['label' => $this->status, 'color' => ''];
    }
}