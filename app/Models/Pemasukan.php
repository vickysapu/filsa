<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Pemasukan extends Model
{
    use HasFactory, SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'branch_id',
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid();
            }
            if (empty($model->nomor_transaksi)) {
                $model->nomor_transaksi = self::generateNomorTransaksi();
            }
        });
    }

    protected static function booted()
    {
        static::addGlobalScope(new \App\Models\Scopes\BranchScope);
    }

    public static function generateNomorTransaksi()
    {
        $date = now()->format('Ymd');
        $lastPemasukan = self::whereDate('created_at', now())
            ->orderBy('created_at', 'desc')
            ->first();
        
        $lastNumber = $lastPemasukan ? intval(substr($lastPemasukan->nomor_transaksi, -4)) : 0;
        $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        
        return 'PMK-' . $date . '-' . $newNumber;
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeTanggalRange($query, $start, $end)
    {
        return $query->whereBetween('tanggal', [$start, $end]);
    }

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

    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pending',
            'verified' => 'Verified',
            'rejected' => 'Ditolak',
        ];
        return $labels[$this->status] ?? $this->status;
    }

    public function getJumlahFormattedAttribute()
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }

    public static function getTotalVerified($start = null, $end = null)
    {
        $query = self::verified();
        if ($start && $end) {
            $query->whereBetween('tanggal', [$start, $end]);
        }
        return $query->sum('jumlah');
    }

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