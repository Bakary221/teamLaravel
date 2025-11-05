<?php

namespace App\Policies;

use App\Models\User;

class AdminPolicy
{
    public function view(User $user)
    {
        return $user->hasRole('admin') && $user->hasPermission('admin:read');
    }

    public function manageUsers(User $user)
    {
        return $user->hasRole('admin') && $user->hasPermission('admin:write');
    }
}
