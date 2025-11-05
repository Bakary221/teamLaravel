<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The data type of the primary key.
     */
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nom',
        'prenom',
        'login',
        'password',
        'telephone',
        'status',
        'cni',
        'code',
        'sexe',
        'role',
        'is_verified',
        'date_naissance',
        'role', 
        'permissions',

    ];
    
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }


    public function client()
    {
        return $this->hasOne(Client::class);
    }


    public function hasPermission(string $permission): bool {
        return in_array($permission, $this->permissions ?? []);
    }

    public function hasRole(string $role): bool {
        return $this->role === $role;
    }



    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'permissions'=>'array'
    ];
}
