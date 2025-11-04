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
        $type = fake()->randomElement(['depot', 'retrait', 'transfert']);
        $compteId = \App\Models\Compte::factory()->create()->id;

        return [
            'compte_id' => $compteId,
            'type' => $type,
            'montant' => fake()->randomFloat(2, 100, 10000),
            'destinataire_id' => $type === 'transfert' ? \App\Models\Compte::where('id', '!=', $compteId)->inRandomOrder()->first()->id ?? $compteId : $compteId,
        ];
    }
}
