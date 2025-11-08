<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use App\Models\Compte;

class ComptePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermission('compte:read');
    }

    public function view(User $user, Compte $compte)
    {
        if ($user->hasRole('admin'))
            return $user->hasPermission('compte:read');
        if ($user->hasRole('client') && $user->client)
            return $compte->client_id === $user->client->id && $user->hasPermission('compte:read');
        return false;
    }

    public function create(User $user)
    {
        return $user->hasPermission('compte:write');
    }

    public function update(User $user, Compte $compte)
    {
        if ($user->hasRole('admin'))
            return $user->hasPermission('compte:write');
        if ($user->hasRole('client') && $user->client)
            return $compte->client_id === $user->client->id && $user->hasPermission('compte:write');
        return false;
    }

    public function delete(User $user, Compte $compte)
    {
        return $user->hasRole('admin') && $user->hasPermission('compte:write');
    }

    public function viewTransactions(User $user, Compte $compte)
    {
        // Admin peut voir toutes les transactions
        if ($user->hasRole('admin')) {
            return $user->hasPermission('transaction:read');
        }

        // Client ne peut voir que ses propres transactions
        if ($user->hasRole('client') && $user->client) {
            return $compte->client_id === $user->client->id && $user->hasPermission('transaction:read');
        }

        return false;
    }
}
