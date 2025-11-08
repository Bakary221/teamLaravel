<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Services\CompteService;
use App\Http\Resources\CompteResource;
use App\Http\Requests\CreateCompteRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Traits\ApiResponseTrait;
use App\Traits\ControllerHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class CompteController extends Controller
{
    use ApiResponseTrait, ControllerHelperTrait;

    public function __construct(
        private CompteService $compteService
    ) {}

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('can-access-bank-operations');
        $this->authorizeAction('viewAny', Compte::class);

        return $this->tryAction(function () use ($request) {
            $validated = $request->validate(['limit' => 'nullable|integer|min:1|max:100']);

            $comptes = Compte::with(['client.user', 'transactions'])
                ->paginate($validated['limit'] ?? 10);

            return $this->paginatedResponse(
                CompteResource::collection($comptes),
                $comptes,
                'Comptes récupérés avec succès'
            );
        });
    }

    public function store(CreateCompteRequest $request): JsonResponse
    {
        $this->authorizeAction('create', Compte::class);

        return $this->tryAction(function () use ($request) {
            $compte = $this->compteService->createCompte($request->validated());

            return $this->successResponse(
                new CompteResource($compte),
                'Compte bancaire créé avec succès',
                201
            );
        });
    }

    public function update(UpdateClientRequest $request, string $compteId): JsonResponse
    {
        if (!$this->validateUuid($compteId, 'ID du compte')) {
            return $this->errorResponse('ID du compte invalide', 400);
        }

        $compte = $this->findOrFail(Compte::class, $compteId, 'Compte');
        $this->authorizeAction('update', $compte);

        return $this->tryAction(function () use ($compte, $request) {
            $this->compteService->updateClientInfo($compte, $request->validated());

            return $this->successResponse(
                new CompteResource($compte),
                'Informations du client mises à jour avec succès'
            );
        });
    }


    public function destroy(string $id): JsonResponse
    {
        if (!$this->validateUuid($id, 'ID du compte')) {
            return $this->errorResponse('ID du compte invalide', 400);
        }

        $compte = $this->findOrFail(Compte::withoutGlobalScopes(), $id, 'Compte');
        $this->authorizeAction('delete', $compte);

        if ($compte->statut === 'fermé') {
            return $this->errorResponse('Ce compte est déjà fermé', 400);
        }

        return $this->tryAction(function () use ($compte) {
            $compte->delete();

            return $this->successResponse([
                'id' => $compte->id,
                'numeroCompte' => $compte->numero_compte,
                'statut' => $compte->statut,
                'dateFermeture' => $compte->date_fermeture?->toISOString(),
            ], 'Compte fermé et supprimé avec succès');
        });
    }

    public function transactions(string $compteId): JsonResponse
    {
        if (!$this->validateUuid($compteId, 'ID du compte')) {
            return $this->errorResponse('ID du compte invalide', 400);
        }

        $compte = $this->findOrFail(Compte::class, $compteId, 'Compte');
        $this->authorizeAction('viewTransactions', $compte);

        return $this->tryAction(function () use ($compte) {
            $transactions = $compte->transactions()
                ->orderBy('created_at', 'desc')
                ->paginate(10);

            return $this->paginatedResponse(
                $transactions,
                $transactions,
                'Transactions récupérées avec succès'
            );
        });
    }

}