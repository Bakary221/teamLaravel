<?php

namespace App\Http\Controllers;

use App\Models\Compte;
use App\Models\Client;
use App\Models\User;
use App\Http\Resources\CompteResource;
use App\Http\Requests\CreateCompteRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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

    /**
     * Créer un nouveau compte bancaire.
     */
    public function store(CreateCompteRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Validation des données
            $validatedData = $request->validated();
            $data = $validatedData;

            // Vérifier si le client existe
            $client = null;
            if (!empty($data['client']['id'])) {
                $client = Client::find($data['client']['id']);
                if (!$client) {
                    return $this->errorResponse('Client non trouvé', 404);
                }
            } else {
                // Créer un nouvel utilisateur
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

                // Créer le client
                $client = new Client();
                $client->id = Str::uuid();
                $client->user_id = $user->id;
                $client->profession = $data['client']['profession'] ?? 'Non spécifiée';
                $client->save();

            }

            // Générer un numéro de compte unique
            $numeroCompte = $this->generateNumeroCompte();

            // Créer le compte
            $compte = new Compte();
            $compte->id = Str::uuid();
            $compte->client_id = $client->id;
            $compte->numero_compte = $numeroCompte;
            $compte->type = $data['type'];
            $compte->statut = 'actif';
            $compte->motif_blocage = null;
            $compte->save();

            // Créer une transaction de dépôt initial
            $compte->transactions()->create([
                'type' => 'depot',
                'montant' => $data['soldeInitial'],
                'destinataire_id' => $compte->id, // Même compte pour les dépôts
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Compte créé avec succès',
                'data' => new CompteResource($compte),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Erreur lors de la création du compte: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Générer un numéro de compte unique.
     */
    private function generateNumeroCompte(): string
    {
        do {
            $numero = 'C' . str_pad(mt_rand(1, 99999999), 8, '0', STR_PAD_LEFT);
        } while (Compte::where('numero_compte', $numero)->exists());

        return $numero;
    }

    /**
     * Supprimer (soft delete) un compte.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $compte = Compte::withoutGlobalScopes()->findOrFail($id);
            if ($compte->statut === 'fermé') {
                return $this->errorResponse('Ce compte est déjà fermé.', 400);
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
            ], 'Compte supprimé avec succès');
        } catch (\Exception $e) {
            return $this->errorResponse('Erreur lors de la suppression du compte', 500);
        }
    }

}