<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUsers = \App\Models\User::where('role', 'Admin')->get();
        foreach ($adminUsers as $user) {
            \App\Models\Admin::factory()->forExistingUser($user->id)->create();
        }
    }
}
