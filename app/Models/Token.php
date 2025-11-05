<?php

namespace App\Models;

use Laravel\Passport\Token as PassportToken;

class Token extends PassportToken
{
    public function toArray()
    {
        $array = parent::toArray();
        $user = $this->user;

        $array['claims'] = [
            'role' => $user->role,
            'permissions' => $user->permissions,
            'user_id' => $user->id,
            'client_type' => $user->role === 'admin' ? 'admin' : 'client',
            'client_id' => $user->role === 'client' ? $user->client->id : null,
            'admin_id' => $user->role === 'admin' ? $user->admin->id : null,
        ];
        return $array;
    }
}