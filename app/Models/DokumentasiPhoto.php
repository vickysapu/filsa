<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumentasiPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'album_id', 'file_path', 'file_name', 'file_type', 'mime_type',
        'file_size', 'caption', 'urutan', 'is_cover', 'metadata', 'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'urutan' => 'integer',
        'is_cover' => 'boolean',
        'metadata' => 'array',
    ];

    public function album()
    {
        return $this->belongsTo(DokumentasiAlbum::class, 'album_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeFormattedAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDimensionsAttribute()
    {
        if (isset($this->metadata['width']) && isset($this->metadata['height'])) {
            return $this->metadata['width'] . ' x ' . $this->metadata['height'];
        }
        return null;
    }
}