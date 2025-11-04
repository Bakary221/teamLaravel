<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientUsers = \App\Models\User::where('role', 'Client')->get();
        foreach ($clientUsers as $user) {
            \App\Models\Client::factory()->forExistingUser($user->id)->create();
        }
    }
}
