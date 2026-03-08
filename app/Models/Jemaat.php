<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jemaat extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'NIK',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'email',
        'status_pernikahan',
        'pekerjaan',
        'pendidikan',
        'tanggal_bergabung',
        'status_jemaat',
        'foto',
    ];

    protected $casts = [
        'NIK' => 'integer',
        'jenis_kelamin' => 'boolean',
        'tanggal_lahir' => 'date',
        'no_hp' => 'integer',
        'tanggal_bergabung' => 'date',
        'status_jemaat' => 'boolean',
    ];

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
}