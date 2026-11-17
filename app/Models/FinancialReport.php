<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FinancialReport extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }

    protected $fillable = ['branch_id', 'report_type', 'report_year', 'report_month', 'period_start', 'period_end', 'opening_balance', 'total_income', 'total_expense', 'closing_balance', 'status', 'submitted_to', 'submitted_at', 'submitted_by', 'approved_by', 'approved_at', 'rejection_reason', 'notes', 'created_by', 'updated_by'];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'opening_balance' => 'decimal:2',
        'total_income' => 'decimal:2',
        'total_expense' => 'decimal:2',
        'closing_balance' => 'decimal:2',
    ];


    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function submittedToBranch()
    {
        return $this->belongsTo(Branch::class, 'submitted_to');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(FinancialReportDetail::class);
    }

    public function forwards()
    {
        return $this->hasMany(FinancialReportForward::class)->orderBy('forwarded_at');
    }

    // Relasi ke Church (melalui details)
    public function church()
    {
        return $this->hasOneThrough(Church::class, FinancialReportDetail::class, 'financial_report_id', 'id', 'id', 'church_id');
    }

    // ==================== ACCESSOR ====================

    public function getChurchNameAttribute(): string
    {
        if ($this->church) {
            return $this->church->name ?? ($this->church->pic_name ?? '-');
        }

        // Fallback dari detail pertama
        $firstDetail = $this->details->first();
        if ($firstDetail && $firstDetail->church) {
            return $firstDetail->church->name ?? ($firstDetail->church->pic_name ?? '-');
        }

        return $this->branch?->name ?? '-';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Terkirim',
            'reviewed' => 'Sedang Direview',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($this->status ?? 'Draft'),
        };
    }

    public function calculateTotals()
    {
        $this->total_income = $this->details()->where('type', 'income')->sum('amount');
        $this->total_expense = $this->details()->where('type', 'expense')->sum('amount');
        $this->closing_balance = $this->total_income - $this->total_expense + ($this->opening_balance ?? 0);
        $this->save();
    }
}
