<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Http\Resources\CompteResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompteController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lister tous les comptes non archivés.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Récupérer les comptes non supprimés avec pagination
            $comptes = Compte::with(['client.user', 'transactions'])
                ->paginate($request->get('limit', 10));

            // Calculer le solde pour chaque compte
            foreach ($comptes as $compte) {
                $compte->solde = $compte->calculerSolde();
            }

            return $this->paginatedResponse(
                CompteResource::collection($comptes),
                $comptes,
                'Comptes récupérés avec succès'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la récupération des comptes', 500);
        }
    }
}