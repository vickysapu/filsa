<?php
// app/Models/Scopes/BranchScope.php

namespace App\Models\Scopes;

use App\Models\Branch;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class BranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $skipModels = [
            Branch::class,
            Permission::class,
            Role::class,
        ];

        if (in_array(get_class($model), $skipModels)) {
            return;
        }

        if (!Auth::check()) {
            return;
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        
        if (!$user instanceof User) {
            return;
        }

        if ($user->isSuperAdmin()) {
            return;
        }

        if ($user->branch_id) {
            $builder->where("{$model->getTable()}.branch_id", $user->branch_id);
        }
    }
}