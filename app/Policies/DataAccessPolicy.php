<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DataAccessRequest;
use App\Models\DataAccessGrant;

class DataAccessPolicy
{
    public function manageDataAccess(User $user): bool
    {
        return $user->hasRole('administrator') && $user->is_active;
    }

    public function approveRequest(User $user, DataAccessRequest $request): bool
    {
        return $this->manageDataAccess($user);
    }

    public function viewGrantDetails(User $user, DataAccessGrant $grant): bool
    {
        return $this->manageDataAccess($user);
    }

    public function revokeAccess(User $user, DataAccessGrant $grant): bool
    {
        if ($grant->isExpired()) {
            return false;
        }
        return $this->manageDataAccess($user);
    }

    public function viewLogs(User $user): bool
    {
        return $this->manageDataAccess($user);
    }
}
