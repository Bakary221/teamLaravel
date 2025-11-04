<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Admin>
 */
class AdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => \App\Models\User::factory()->create(['role' => 'Admin'])->id,
            'poste' => fake()->jobTitle(),
        ];
    }

    public function forExistingUser($userId)
    {
        return $this->state(function (array $attributes) use ($userId) {
            return [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'user_id' => $userId,
            ];
        });
    }
}
