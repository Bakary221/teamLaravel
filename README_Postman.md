# Guide d'utilisation de l'API avec Postman

Ce guide explique comment utiliser l'API Laravel avec Postman pour tester les endpoints disponibles.

## Prérequis

- **Postman** installé sur votre machine.
- L'application Laravel doit être en cours d'exécution (par exemple, via `php artisan serve` ou Docker).
- Une base de données configurée avec des données de test (utilisez les seeders si nécessaire).

## Configuration de base

- **URL de base** : `http://127.0.0.1:8001/api` (ajustez selon votre configuration).
- **Version de l'API** : v1 (préfixe `/v1`).

## Authentification

L'API utilise Laravel Passport pour l'authentification OAuth2. Pour accéder aux endpoints protégés, vous devez obtenir un token d'accès.

### Étape 1 : Créer un client OAuth (si nécessaire)

Si vous n'avez pas encore de client OAuth, créez-en un via Artisan :

```bash
php artisan passport:client --personal
```

Notez l'ID du client et le secret.

### Étape 2 : Obtenir un token d'accès

1. Ouvrez Postman et créez une nouvelle requête.
2. Méthode : `POST`
3. URL : `http://127.0.0.1:8001/api/oauth/token`
4. Headers :
   - `Content-Type: application/json`
   - `Accept: application/json`
5. Body (raw JSON) :
   ```json
   {
     "grant_type": "password",
     "client_id": "votre_client_id",
     "client_secret": "votre_client_secret",
     "username": "email_utilisateur",
     "password": "mot_de_passe_utilisateur",
     "scope": "*"
   }
   ```
6. Envoyez la requête. Vous recevrez un JSON avec `access_token`.

### Étape 3 : Utiliser le token dans les requêtes

Pour les endpoints authentifiés, ajoutez le header :
- `Authorization: Bearer votre_access_token`

## Endpoints disponibles

### 1. Lister les comptes (GET /api/v1/comptes)

- **Méthode** : GET
- **URL** : `http://localhost:8000/api/v1/comptes`
- **Authentification** : Requise (Bearer Token)
- **Paramètres de requête** :
  - `limit` (optionnel) : Nombre d'éléments par page (défaut : 10)
- **Exemple de réponse** :
  ```json
  {
    "success": true,
    "message": "Comptes récupérés avec succès",
    "data": [
      {
        "id": "uuid-du-compte",
        "numeroCompte": "C123456789",
        "titulaire": "Dupont Jean",
        "type": "cheque",
        "solde": 1500.00,
        "devise": "FCFA",
        "dateCreation": "2025-11-04T08:00:00.000000Z",
        "statut": "actif",
        "motifBlocage": null,
        "metadata": {
          "derniereModification": "2025-11-04T08:00:00.000000Z",
          "version": 1
        }
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 10,
      "total": 50,
      "last_page": 5
    }
  }
  ```

### 2. Créer un compte (POST /api/v1/comptes)

- **Méthode** : POST
- **URL** : `http://127.0.0.1:8001/api/v1/comptes`
- **Authentification** : Requise (Bearer Token)
- **Headers** :
  - `Content-Type: application/json`
- **Corps de la requête** :
  ```json
  {
    "type": "cheque",
    "soldeInitial": 15000,
    "devise": "FCFA",
    "solde": 15000,
    "client": {
      "id": "uuid-client-existant", // Optionnel, si non fourni, un nouveau client sera créé
      "titulaire": "John Doe",
      "nci": "AB123456789",
      "email": "john@example.com",
      "telephone": "771234567",
      "adresse": "Dakar, Senegal",
      "profession": "Developpeur"
    }
  }
  ```
- **Exemple de réponse** :
  ```json
  {
    "success": true,
    "message": "Compte créé avec succès",
    "data": {
      "id": "uuid-du-compte",
      "numeroCompte": "C123456789",
      "titulaire": "John Doe",
      "type": "cheque",
      "solde": 15000,
      "devise": "FCFA",
      "dateCreation": "2025-11-04T08:00:00.000000Z",
      "statut": "actif",
      "motifBlocage": null,
      "metadata": {
        "derniereModification": "2025-11-04T08:00:00.000000Z",
        "version": 1
      }
    }
  }
  ```

### Étapes dans Postman

1. Créez une nouvelle requête.
2. Sélectionnez la méthode GET.
3. Entrez l'URL : `http://127.0.0.1:8001/api/v1/comptes`
4. Dans l'onglet "Authorization", choisissez "Bearer Token" et collez votre `access_token`.
5. Ajoutez des paramètres de requête si nécessaire (ex. `?limit=5`).
6. Cliquez sur "Send".

## Gestion des erreurs

- **401 Unauthorized** : Token manquant ou invalide. Vérifiez l'authentification.
- **500 Internal Server Error** : Erreur serveur. Vérifiez les logs Laravel.
- **404 Not Found** : Endpoint incorrect.

## Conseils supplémentaires

- Utilisez des variables d'environnement dans Postman pour stocker l'URL de base et le token.
- Testez d'abord l'endpoint `/api/user` pour vérifier l'authentification.
- Pour les requêtes POST/PUT, assurez-vous d'inclure le header `Content-Type: application/json`.

Si vous rencontrez des problèmes, vérifiez la configuration CORS dans `config/cors.php` et assurez-vous que le serveur est accessible.