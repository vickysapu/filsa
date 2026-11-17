<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LatihanPelayanan extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'latihan_pelayanan';

    protected $fillable = [
        'branch_id',
        'pelayanan_id',
        'nama_latihan',
        'tanggal',
        'waktu',
        'lokasi',
        'keterangan',
        'status',
        'created_by'
    ];

    // Penting: Cast kolom tanggal dan waktu
    protected $casts = [
        'tanggal' => 'datetime:H:i',        // atau 'datetime:Y-m-d'
        'waktu'   => 'string', 
        'status'  => 'string',
    ];

    public function pelayanan()
    {
        return $this->belongsTo(Pelayanan::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

}