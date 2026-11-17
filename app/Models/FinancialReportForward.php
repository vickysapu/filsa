<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialReportForward extends Model
{
    protected $fillable = ['financial_report_id', 'from_branch_id', 'to_branch_id', 'forwarded_by', 'forwarded_at', 'notes', 'action'];

    protected $casts = [
        'forwarded_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(FinancialReport::class, 'financial_report_id');
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'forwarded_by');
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'submit' => 'Dikirim',
            'forward' => 'Diteruskan',
            'approve' => 'Disetujui',
            'reject' => 'Ditolak',
            default => ucfirst($this->action),
        };
    }

    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'submit' => 'info',
            'forward' => 'warning',
            'approve' => 'success',
            'reject' => 'danger',
            default => 'secondary',
        };
    }
}
