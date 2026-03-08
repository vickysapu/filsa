<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PelayananDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'pelayanan_id',
        'jemaat_id',
        'role',
        'status',
        'tanggal_off',
        'alasan_off',
        'updated_by',
    ];

    protected $casts = [
        'tanggal_off' => 'date',
    ];

    public function pelayanan()
    {
        return $this->belongsTo(Pelayanan::class);
    }

    public function jemaat()
    {
        return $this->belongsTo(Jemaat::class);
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOff($query)
    {
        return $query->where('status', 'off');
    }
}