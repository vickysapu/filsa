<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Branch;

class Inventaris extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'branch_id',
        'nama_barang',
        'kategori',
        'kondisi',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
