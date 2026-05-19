<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Tambahkan relasi ini ke model FinancialReport yang sudah ada.
 * Juga pastikan calculateTotals() ada.
 */
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

    // ── Relasi ──────────────────────────────────────────────
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function submittedToBranch()
    {
        return $this->belongsTo(Branch::class, 'submitted_to');
    }

    public function show($id)
    {
        $transaction = FinancialTransaction::findOrFail($id);
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

    /**
     * Riwayat pengiriman / forwarding laporan (BARU)
     */
    public function forwards()
    {
        return $this->hasMany(FinancialReportForward::class)->orderBy('forwarded_at');
    }

    // ── Business Logic ──────────────────────────────────────
    public function calculateTotals(): void
    {
        $income = $this->details()->where('type', 'income')->sum('amount');
        $expense = $this->details()->where('type', 'expense')->sum('amount');
        $this->update([
            'total_income' => $income,
            'total_expense' => $expense,
            'closing_balance' => $this->opening_balance + $income - $expense,
        ]);
    }

    // ── Helpers ─────────────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'submitted' => 'Terkirim',
            'reviewed' => 'Sedang Direview',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'warning',
            'submitted' => 'info',
            'reviewed' => 'primary',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary',
        };
    }
}
