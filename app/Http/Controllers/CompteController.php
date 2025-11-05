<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Client;
use App\Models\User;
use App\Http\Resources\CompteResource;
use App\Http\Requests\CreateCompteRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CompteController extends Controller
{
    use ApiResponseTrait;

    /**
     * Lister tous les comptes non archivés.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Validation des paramètres de requête
            $validated = $request->validate([
                'limit' => 'nullable|integer|min:1|max:100',
            ]);

            // Récupérer les comptes non supprimés avec pagination
            $comptes = Compte::with(['client.user', 'transactions'])
                ->paginate($validated['limit'] ?? 10);

            // Calculer le solde pour chaque compte
            foreach ($comptes as $compte) {
                try {
                    $compte->solde = $compte->calculerSolde();
                } catch (\Exception $e) {
                    // En cas d'erreur de calcul de solde, définir à 0 et logger
                    $compte->solde = 0;
                    Log::warning('Erreur lors du calcul du solde pour le compte ' . $compte->id . ': ' . $e->getMessage());
                }
            }

            return $this->paginatedResponse(
                CompteResource::collection($comptes),
                $comptes,
                'Comptes récupérés avec succès'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Paramètres de requête invalides', 422, $e->errors());
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des comptes: ' . $e->getMessage());
            return $this->errorResponse('Erreur interne du serveur lors de la récupération des comptes', 500);
        }
    }

    /**
     * Créer un nouveau compte bancaire.
     */
    public function store(CreateCompteRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Validation des données (déjà faite par CreateCompteRequest)
            $data = $request->validated();

            // Vérifier si le client existe
            $client = null;
            if (!empty($data['client']['id'])) {
                $client = Client::find($data['client']['id']);
                if (!$client) {
                    DB::rollBack();
                    return $this->errorResponse('Client spécifié non trouvé dans le système', 404);
                }
            } else {
                // Créer un nouvel utilisateur
                try {
                    $user = new User();
                    $user->id = Str::uuid();
                    $user->nom = explode(' ', $data['client']['titulaire'])[0] ?? $data['client']['titulaire'];
                    $user->prenom = explode(' ', $data['client']['titulaire'])[1] ?? '';
                    $user->login = $data['client']['email'];
                    $user->email = $data['client']['email'];
                    $user->telephone = $data['client']['telephone'];
                    $user->status = 'Actif';
                    $user->cni = $data['client']['nci'];
                    $user->code = Str::random(6);
                    $user->sexe = 'Homme'; // Par défaut
                    $user->role = 'Client';
                    $user->is_verified = 1;
                    $user->date_naissance = now()->subYears(25)->format('Y-m-d'); // Par défaut
                    $user->password = Hash::make(Str::random(12)); // Générer un mot de passe aléatoire
                    $user->save();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erreur lors de la création de l\'utilisateur: ' . $e->getMessage());
                    return $this->errorResponse('Erreur lors de la création du profil utilisateur', 500);
                }

                // Créer le client
                try {
                    $client = new Client();
                    $client->id = Str::uuid();
                    $client->user_id = $user->id;
                    $client->profession = $data['client']['profession'] ?? 'Non spécifiée';
                    $client->save();
                } catch (\Exception $e) {
                    DB::rollBack();
                    Log::error('Erreur lors de la création du client: ' . $e->getMessage());
                    return $this->errorResponse('Erreur lors de la création du profil client', 500);
                }
            }

            // Générer un numéro de compte unique
            try {
                $numeroCompte = $this->generateNumeroCompte();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur lors de la génération du numéro de compte: ' . $e->getMessage());
                return $this->errorResponse('Erreur lors de la génération du numéro de compte', 500);
            }

            // Créer le compte
            try {
                $compte = new Compte();
                $compte->id = Str::uuid();
                $compte->client_id = $client->id;
                $compte->numero_compte = $numeroCompte;
                $compte->type = $data['type'];
                $compte->statut = 'actif';
                $compte->motif_blocage = null;
                $compte->save();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur lors de la création du compte: ' . $e->getMessage());
                return $this->errorResponse('Erreur lors de la création du compte bancaire', 500);
            }

            // Créer une transaction de dépôt initial
            try {
                $compte->transactions()->create([
                    'type' => 'depot',
                    'montant' => $data['soldeInitial'],
                    'destinataire_id' => $compte->id, // Même compte pour les dépôts
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Erreur lors de la création de la transaction initiale: ' . $e->getMessage());
                return $this->errorResponse('Erreur lors de l\'initialisation du solde du compte', 500);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compte bancaire créé avec succès',
                'data' => new CompteResource($compte),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->errorResponse('Données de création invalides', 422, $e->errors());
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur inattendue lors de la création du compte: ' . $e->getMessage());
            return $this->errorResponse('Erreur interne du serveur lors de la création du compte', 500);
        }
    }

    /**
     * Mettre à jour les informations du client associé à un compte.
     */
    public function update(UpdateClientRequest $request, string $compteId): JsonResponse
    {
        try {
            // Validation de l'ID du compte
            if (!\Illuminate\Support\Str::isUuid($compteId)) {
                return $this->errorResponse('ID du compte invalide', 400);
            }

            $compte = Compte::find($compteId);
            if (!$compte) {
                return $this->errorResponse('Compte non trouvé', 404);
            }

            $client = $compte->client;
            if (!$client) {
                return $this->errorResponse('Client associé au compte non trouvé', 404);
            }

            $user = $client->user;
            if (!$user) {
                return $this->errorResponse('Utilisateur associé au client non trouvé', 404);
            }

            // Mettre à jour les champs du User (client)
            if ($request->has('titulaire')) {
                $parts = explode(' ', $request->titulaire, 2);
                $user->nom = $parts[0] ?? $request->titulaire;
                $user->prenom = $parts[1] ?? '';
            }
            if ($request->has('telephone')) {
                $user->telephone = $request->telephone;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
                $user->login = $request->email; // Mettre à jour login si email change
            }
            if ($request->has('nci')) {
                $user->cni = $request->nci;
            }

            try {
                $user->save();
            } catch (\Illuminate\Database\QueryException $e) {
                Log::error('Erreur de base de données lors de la mise à jour de l\'utilisateur ' . $user->id . ': ' . $e->getMessage());
                return $this->errorResponse('Erreur lors de la sauvegarde des informations utilisateur', 500);
            }

            // Recalculer le solde si nécessaire
            try {
                $compte->solde = $compte->calculerSolde();
            } catch (\Exception $e) {
                Log::warning('Erreur lors du recalcul du solde pour le compte ' . $compteId . ': ' . $e->getMessage());
                $compte->solde = 0; // Valeur par défaut en cas d'erreur
            }

            return $this->successResponse(
                new CompteResource($compte),
                'Informations du client mises à jour avec succès'
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Données de mise à jour invalides', 422, $e->errors());
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Erreur de base de données lors de la mise à jour du compte ' . $compteId . ': ' . $e->getMessage());
            return $this->errorResponse('Erreur de base de données lors de la mise à jour', 500);
        } catch (\Exception $e) {
            Log::error('Erreur inattendue lors de la mise à jour du compte ' . $compteId . ': ' . $e->getMessage());
            return $this->errorResponse('Erreur interne du serveur lors de la mise à jour', 500);
        }
    }


    /**
     * Générer un numéro de compte unique.
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

    /**
     * Supprimer (soft delete) un compte.
     */
    public function destroy($id): JsonResponse
    {
        try {
            // Validation de l'ID du compte
            if (!\Illuminate\Support\Str::isUuid($id)) {
                return $this->errorResponse('ID du compte invalide', 400);
            }

            $compte = Compte::withoutGlobalScopes()->find($id);
            if (!$compte) {
                return $this->errorResponse('Compte non trouvé', 404);
            }

            if ($compte->statut === 'fermé') {
                return $this->errorResponse('Ce compte est déjà fermé et ne peut pas être supprimé à nouveau', 400);
            }

            // Vérifier si le compte a un solde positif
            try {
                $solde = $compte->calculerSolde();
                if ($solde > 0) {
                    return $this->errorResponse('Impossible de fermer le compte : solde positif détecté. Veuillez d\'abord retirer tous les fonds.', 400);
                }
            } catch (\Exception $e) {
                Log::warning('Erreur lors de la vérification du solde pour la suppression du compte ' . $id . ': ' . $e->getMessage());
                return $this->errorResponse('Erreur lors de la vérification du solde du compte', 500);
            }

            $compte->statut = 'fermé';
            $compte->date_fermeture = now();
            $compte->save();
            $compte->delete();

            return $this->successResponse([
                'id' => $compte->id,
                'numeroCompte' => $compte->numero_compte,
                'statut' => $compte->statut,
                'dateFermeture' => $compte->date_fermeture ? $compte->date_fermeture->toISOString() : null,
            ], 'Compte fermé et supprimé avec succès');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Erreur de base de données lors de la suppression du compte ' . $id . ': ' . $e->getMessage());
            return $this->errorResponse('Erreur de base de données lors de la suppression du compte', 500);
        } catch (\Exception $e) {
            Log::error('Erreur inattendue lors de la suppression du compte ' . $id . ': ' . $e->getMessage());
            return $this->errorResponse('Erreur interne du serveur lors de la suppression du compte', 500);
        }
    }

}