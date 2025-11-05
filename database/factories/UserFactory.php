<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $email = fake()->unique()->safeEmail();
        $role = fake()->randomElement(['admin', 'client']);
        $permissions = $role === 'admin'
            ? ['compte:read', 'compte:write', 'transaction:read', 'admin:read', 'admin:write']
            : ['compte:read', 'compte:write', 'transaction:read'];
        return [
            'id' => Str::uuid(),
            'nom' => fake()->name(),
            'prenom' => fake()->lastName(),
            'telephone' => fake()->unique()->phoneNumber(),
            'status' => fake()->randomElement(['Actif', 'Inactif']),
            'cni' => fake()->unique()->bothify('??##########'),
            'code' => fake()->bothify('??##??##'),
            'sexe' => fake()->randomElement(['Homme', 'Femme']),
            'login' => $email,
            'password' => static::$password ??= Hash::make('password'),
            'date_naissance' => fake()->date(),
            'role' => $role,
            'permissions' => $permissions,
            'is_verified' => 1,
        ];
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'admin',
                'permissions' => ['compte:read', 'compte:write', 'transaction:read', 'admin:read', 'admin:write'],
            ];
        });
    }

    public function client()
    {
        return $this->state(function (array $attributes) {
            return [
                'role' => 'client',
                'permissions' => ['compte:read', 'compte:write', 'transaction:read'],
            ];
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
