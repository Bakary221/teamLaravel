<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
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
            'compte_id' => \App\Models\Compte::factory(),
            'type' => fake()->randomElement(['Depot', 'Retrait', 'Transfert']),
            'montant' => fake()->randomFloat(2, 100, 10000),
            'destinataire_id' => fake()->optional()->randomElement(\App\Models\Compte::pluck('id')->toArray()),
        ];
    }
}
