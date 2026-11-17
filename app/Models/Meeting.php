<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Meeting extends Model
{
    use SoftDeletes;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['branch_id', 'nama_meeting', 'deskripsi', 'meeting_id', 'meeting_url', 'platform', 'tanggal_mulai', 'tanggal_selesai', 'is_active', 'show_in_website', 'created_by', 'updated_by'];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'is_active' => 'boolean',
        'show_in_website' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = Str::uuid();
            }
            // Generate meeting ID unik jika kosong
            if (empty($model->meeting_id)) {
                $model->meeting_id = 'filadelfia-' . Str::random(12);
            }
            // Generate URL Jitsi jika platform jitsi
            if ($model->platform === 'jitsi' && empty($model->meeting_url)) {
                $model->meeting_url = 'https://meet.jit.si/' . $model->meeting_id;
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('show_in_website', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_mulai', '>=', now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('tanggal_mulai', '<=', now())->where(function ($q) {
            $q->whereNull('tanggal_selesai')->orWhere('tanggal_selesai', '>=', now());
        });
    }
}
