<?php

namespace App\Services;

use App\Models\Compte;
use App\Models\Client;
use App\Models\User;
use App\Repositories\Interfaces\CompteRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\ClientRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CompteService
{
    public function __construct(
        private CompteRepositoryInterface $compteRepository,
        private UserRepositoryInterface $userRepository,
        private ClientRepositoryInterface $clientRepository
    ) {}

    /**
     * Créer un nouveau compte bancaire
     */
    public function createCompte(array $data): Compte
    {
        DB::beginTransaction();

        try {
            // Créer ou récupérer le client
            $client = $this->getOrCreateClient($data['client']);

            // Créer le compte (l'observer gère le numéro et la transaction initiale)
            $compte = $this->compteRepository->create([
                'id' => Str::uuid(),
                'client_id' => $client->id,
                'type' => $data['type'],
                'statut' => 'actif',
            ]);

            DB::commit();
            return $compte;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Mettre à jour les informations du client associé à un compte
     */
    public function updateClientInfo(Compte $compte, array $data): void
    {
        $user = $compte->client->user;

        // Mettre à jour les champs du User (client)
        if (isset($data['titulaire'])) {
            $parts = explode(' ', $data['titulaire'], 2);
            $user->nom = $parts[0] ?? $data['titulaire'];
            $user->prenom = $parts[1] ?? '';
        }
        if (isset($data['telephone'])) {
            $user->telephone = $data['telephone'];
        }
        if (isset($data['email'])) {
            $user->email = $data['email'];
            $user->login = $data['email']; // Mettre à jour login si email change
        }
        if (isset($data['nci'])) {
            $user->cni = $data['nci'];
        }

        $this->userRepository->update($user, $user->toArray());
    }

    /**
     * Créer ou récupérer un client
     */
    private function getOrCreateClient(array $clientData): Client
    {
        // Si un ID de client est fourni, le récupérer
        if (!empty($clientData['id'])) {
            $client = $this->clientRepository->find($clientData['id']);
            if (!$client) {
                throw new \Exception('Client spécifié non trouvé dans le système');
            }
            return $client;
        }

        // Créer un nouvel utilisateur
        $user = $this->userRepository->create([
            'id' => Str::uuid(),
            'nom' => explode(' ', $clientData['titulaire'])[0] ?? $clientData['titulaire'],
            'prenom' => explode(' ', $clientData['titulaire'])[1] ?? '',
            'login' => $clientData['email'],
            'email' => $clientData['email'], // Ajouter le champ email
            'telephone' => $clientData['telephone'],
            'permissions' => ['compte:read', 'compte:write', 'transaction:read'],
            'status' => 'Actif',
            'cni' => $clientData['nci'],
            'code' => Str::random(6),
            'sexe' => 'Homme',
            'role' => 'client',
            'is_verified' => 1,
            'date_naissance' => now()->subYears(25)->format('Y-m-d'),
            'password' => Hash::make(Str::random(12)),
        ]);

        // Créer le client
        $client = $this->clientRepository->create([
            'id' => Str::uuid(),
            'user_id' => $user->id,
            'profession' => $clientData['profession'] ?? 'Non spécifiée',
        ]);

        return $client;
    }
}