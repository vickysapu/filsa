<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Pengeluaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor_transaksi',
        'tanggal',
        'kategori',
        'keperluan',
        'penerima',
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

        static::creating(function ($pengeluaran) {
            if (empty($pengeluaran->nomor_transaksi)) {
                $pengeluaran->nomor_transaksi = self::generateNomorTransaksi();
            }
        });
    }

    /**
     * Generate nomor transaksi unik
     * Format: PGL-YYYYMMDD-XXXX
     * Only checks active (non-deleted) records
     * Must be called within a database transaction for proper locking
     */
    public static function generateNomorTransaksi()
    {
        $date = now()->format('Ymd');
        $prefix = 'PGL-' . $date . '-';
        
        // Get the highest number for today using a subquery with MAX
        // Include soft-deleted records too so we never reuse a number still in DB
        // Lock the rows to prevent race conditions (requires active transaction)
        $maxNumber = self::withTrashed()
            ->whereDate('created_at', now())
            ->where('nomor_transaksi', 'LIKE', $prefix . '%')
            ->selectRaw('MAX(CAST(SUBSTRING(nomor_transaksi, -4) AS UNSIGNED)) as max_num')
            ->lockForUpdate()
            ->first();
        
        $lastNumber = $maxNumber && $maxNumber->max_num ? intval($maxNumber->max_num) : 0;
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return $prefix . $newNumber;
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
     * Scope untuk pengeluaran verified
     */
    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /**
     * Scope untuk pengeluaran pending
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
            'operasional' => 'Operasional',
            'listrik_air' => 'Listrik & Air',
            'gaji_karyawan' => 'Gaji Karyawan',
            'kegiatan' => 'Kegiatan',
            'pemeliharaan' => 'Pemeliharaan',
            'amal_sosial' => 'Amal & Sosial',
            'konsumsi' => 'Konsumsi',
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
     * Total pengeluaran verified
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
     * Total pengeluaran per kategori
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