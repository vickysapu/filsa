<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Pemasukan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor_transaksi',
        'tanggal',
        'kategori',
        'sumber_dana',
        'jumlah',
        'keterangan',
        'status',
        'bukti_foto',
        'created_by',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jumlah' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    /**
     * Boot method untuk auto-generate nomor transaksi
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pemasukan) {
            if (empty($pemasukan->nomor_transaksi)) {
                $pemasukan->nomor_transaksi = self::generateNomorTransaksi();
            }
        });
    }

    /**
     * Generate nomor transaksi unik
     * Format: PMK-YYYYMMDD-XXXX
     */
    public static function generateNomorTransaksi()
    {
        $date = now()->format('Ymd');
        $lastPemasukan = self::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();
        
        $lastNumber = $lastPemasukan ? intval(substr($lastPemasukan->nomor_transaksi, -4)) : 0;
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return 'PMK-' . $date . '-' . $newNumber;
    }

    /**
     * Relasi ke User yang membuat
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi ke User yang verifikasi
     */
    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Scope untuk pemasukan verified
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope untuk pemasukan pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope untuk filter tanggal
     */
    public function scopeTanggalRange($query, $start, $end)
    {
        return $query->whereBetween('tanggal', [$start, $end]);
    }

    /**
     * Get label kategori
     */
    public function getKategoriLabelAttribute()
    {
        $labels = [
            'persembahan_minggu' => 'Persembahan Minggu',
            'persembahan_khusus' => 'Persembahan Khusus',
            'donasi' => 'Donasi',
            'kegiatan' => 'Kegiatan',
            'usaha_mandiri' => 'Usaha Mandiri',
            'lainnya' => 'Lainnya',
        ];
        return $labels[$this->kategori] ?? $this->kategori;
    }

    /**
     * Get label status
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'verified' => 'Verified',
            'rejected' => 'Ditolak',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Get formatted jumlah
     */
    public function getJumlahFormattedAttribute()
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }

    /**
     * Total pemasukan verified
     */
    public static function getTotalVerified($start = null, $end = null)
    {
        $query = self::verified();
        if ($start && $end) {
            $query->whereBetween('tanggal', [$start, $end]);
        }
        return $query->sum('jumlah');
    }

    /**
     * Total pemasukan per kategori
     */
    public static function getTotalPerKategori($start = null, $end = null)
    {
        $query = self::verified();
        if ($start && $end) {
            $query->whereBetween('tanggal', [$start, $end]);
        }
        return $query->select('kategori', DB::raw('SUM(jumlah) as total'))
            ->groupBy('kategori')
            ->get();
    }
}