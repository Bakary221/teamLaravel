<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numeroCompte' => $this->numero_compte,
            'titulaire' => $this->client->user->nom . ' ' . $this->client->user->prenom,
            'type' => $this->type,
            'solde' => $this->calculerSolde(),
            'devise' => 'FCFA',
            'dateCreation' => $this->created_at->toISOString(),
            'statut' => $this->statut,
            'motifBlocage' => $this->motif_blocage,
            'metadata' => [
                'derniereModification' => $this->updated_at->toISOString(),
                'version' => 1,
            ],
        ];
    }
}