<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Admin;
use App\Models\Client;
use App\Models\Compte;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::create([
            'id' => Str::uuid(),
            'nom' => 'Admin',
            'prenom' => 'System',
            'login' => 'admin@banque.com',
            'password' => Hash::make('password'),
            'telephone' => '771234567',
            'status' => 'Actif',
            'cni' => '1234567890123',
            'code' => 'AB12CD34',
            'sexe' => 'Homme',
            'role' => 'admin',
            'permissions' => ['admin:read', 'admin:write', 'compte:read', 'compte:write', 'transaction:read'],
            'date_naissance' => '1980-01-01',
            'is_verified' => 1,
        ]);
        Admin::create(['id' => Str::uuid(), 'user_id' => $adminUser->id]);

        $clientUser = User::create([
            'id' => Str::uuid(),
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'login' => 'jean.dupont@email.com',
            'password' => Hash::make('password'),
            'telephone' => '772345678',
            'status' => 'Actif',
            'cni' => '2345678901234',
            'code' => 'EF56GH78',
            'sexe' => 'Homme',
            'role' => 'client',
            'permissions' => ['compte:read', 'compte:write', 'transaction:read'],
            'date_naissance' => '1990-05-15',
            'is_verified' => 1,
        ]);
        $client = Client::create(['id' => Str::uuid(), 'user_id' => $clientUser->id, 'profession' => 'Non spécifiée']);
        Compte::create([
            'id' => Str::uuid(),
            'client_id' => $client->id,
            'numero_compte' => 'C000000001',
            'type' => 'cheque',
            'statut' => 'actif'
        ]);

        User::factory(48)->create();
    }
}
