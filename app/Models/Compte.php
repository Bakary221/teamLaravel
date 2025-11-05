<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compte extends Model
{
    use HasFactory, SoftDeletes;

    protected $keyType = 'string';

    protected $fillable = [
        'client_id',
        'numero_compte',
        'type',
        'statut',
        'motif_blocage',
        'date_fermeture',
    ];

    protected static function booted()
    {
        static::addGlobalScope('nonSupprimes', function (Builder $builder) {
            $builder->where('statut', '!=', 'fermé');
        });
    }

    // Les relations entre compte et les autres modèles (client, transactions)
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Scope pour récupérer un compte par numéro.
     */
    public function scopeNumero(Builder $query, string $numero): Builder
    {
        return $query->where('numero_compte', $numero);
    }

    /**
     * Scope pour récupérer les comptes d'un client basé sur le téléphone.
     */
    public function scopeClient(Builder $query, string $telephone): Builder
    {
        return $query->whereHas('client.user', function ($q) use ($telephone) {
            $q->where('telephone', $telephone);
        });
    }

    /**
     * Calculer le solde du compte : somme des dépôts - somme des retraits.
     */
    public function calculerSolde(): float
    {
        $depots = $this->transactions()->where('type', 'depot')->sum('montant');
        $retraits = $this->transactions()->where('type', 'retrait')->sum('montant');

        return $depots - $retraits;
    }
}
