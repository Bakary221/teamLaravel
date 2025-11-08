<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompteScope implements Scope
{
    /**
     * Appliquer le scope global aux requêtes Compte
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Exclure les comptes fermés par défaut
        $builder->where('statut', '!=', 'fermé');
    }

    /**
     * Étendre le Query Builder avec des méthodes personnalisées
     */
    public function extend(Builder $builder): void
    {
        // Scope pour comptes actifs uniquement
        $builder->macro('actifs', function (Builder $builder) {
            return $builder->where('statut', 'actif');
        });

        // Scope pour comptes bloqués
        $builder->macro('bloques', function (Builder $builder) {
            return $builder->where('statut', 'bloqué');
        });

        // Scope pour comptes d'un type spécifique
        $builder->macro('duType', function (Builder $builder, string $type) {
            return $builder->where('type', $type);
        });

        // Scope pour comptes avec solde positif
        $builder->macro('avecSoldePositif', function (Builder $builder) {
            return $builder->whereHas('transactions', function ($query) {
                $query->selectRaw('compte_id, SUM(CASE WHEN type = "depot" THEN montant ELSE -montant END) as solde')
                      ->having('solde', '>', 0)
                      ->groupBy('compte_id');
            });
        });

        // Scope pour comptes créés récemment
        $builder->macro('recents', function (Builder $builder, int $jours = 30) {
            return $builder->where('created_at', '>=', now()->subDays($jours));
        });

        // Scope pour comptes d'un client spécifique
        $builder->macro('duClient', function (Builder $builder, string $clientId) {
            return $builder->where('client_id', $clientId);
        });

        // Supprimer le scope global pour voir tous les comptes
        $builder->macro('withFermes', function (Builder $builder) {
            return $builder->withoutGlobalScope($this);
        });
    }
}