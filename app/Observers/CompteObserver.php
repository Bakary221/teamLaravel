<?php

namespace App\Observers;

use App\Models\Compte;
use Illuminate\Support\Facades\Log;

class CompteObserver
{
    /**
     * Générer un numéro de compte unique lors de la création
     */
    public function creating(Compte $compte)
    {
        $compte->numero_compte = $this->generateNumeroCompte();
    }

    /**
     * Créer une transaction de dépôt initial après création
     */
    public function created(Compte $compte)
    {
        // Créer la transaction initiale si un solde initial est fourni
        if (request()->has('soldeInitial') && request('soldeInitial') > 0) {
            $compte->transactions()->create([
                'type' => 'depot',
                'montant' => request('soldeInitial'),
                'destinataire_id' => $compte->id,
            ]);
        }
    }

    /**
     * Calculer automatiquement le solde lors de la récupération
     */
    public function retrieved(Compte $compte)
    {
        try {
            $compte->solde = $compte->calculerSolde();
        } catch (\Exception $e) {
            $compte->solde = 0;
            Log::warning('Erreur calcul solde pour compte ' . $compte->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Gérer la fermeture du compte avant suppression
     */
    public function deleting(Compte $compte)
    {
        // Vérifier le solde avant suppression
        $solde = $compte->calculerSolde();
        if ($solde > 0) {
            throw new \Exception('Impossible de fermer le compte : solde positif détecté. Veuillez d\'abord retirer tous les fonds.');
        }

        $compte->statut = 'fermé';
        $compte->date_fermeture = now();
        $compte->save();
    }

    /**
     * Générer un numéro de compte unique
     */
    private function generateNumeroCompte(): string
    {
        $maxAttempts = 10;
        $attempts = 0;

        do {
            if ($attempts >= $maxAttempts) {
                throw new \Exception('Impossible de générer un numéro de compte unique après ' . $maxAttempts . ' tentatives');
            }
            $numero = 'C' . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
            $attempts++;
        } while (Compte::where('numero_compte', $numero)->exists());

        return $numero;
    }
}