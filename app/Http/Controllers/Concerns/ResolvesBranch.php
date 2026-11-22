<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait ResolvesBranch
{
    protected function isSuperAdmin()
    {
        $user = Auth::user();

        return $user && $user->isSuperAdmin();
    }

    protected function getBranchId()
    {
        return Auth::user()?->currentBranchId();
    }

    protected function resolveBranchId(Request $request)
    {
        return $this->isSuperAdmin() ? $request->input('branch_id') : $this->getBranchId();
    }

    protected function scopeToBranch($query, Request $request)
    {
        if ($this->isSuperAdmin()) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            return $query;
        }

        $branchId = $this->getBranchId();

        return $branchId ? $query->where('branch_id', $branchId) : $query->whereRaw('1=0');
    }

    protected function ownsBranch($model)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $branchId = $this->getBranchId();

        return $branchId && (string) $model->branch_id === (string) $branchId;
    }
}