# 🔐 Guide Complet d'Authentification - Laravel Banking System

## Vue d'ensemble

Ce système bancaire Laravel implémente une architecture d'authentification robuste utilisant **Laravel Passport** pour OAuth2/JWT, combinée avec un système de rôles et permissions personnalisé. L'authentification est basée sur des tokens JWT Bearer avec contrôle d'accès granulaire.

## 🏗️ Architecture Générale

### Technologies Utilisées
- **Laravel Passport** - Gestion OAuth2 et JWT
- **Middleware personnalisés** - Contrôle d'accès en couches
- **Policies Laravel** - Autorisation métier
- **Gates** - Vérifications booléennes rapides
- **PostgreSQL** - Stockage persistant

### Principes de Sécurité
- **Defense in Depth** - Multiples couches de protection
- **Least Privilege** - Permissions minimales nécessaires
- **Separation of Concerns** - Responsabilités clairement séparées
- **Audit Trail** - Logging complet des actions

## 🔑 Composants d'Authentification

### 1. Modèle User

Le modèle `User` étend `Authenticatable` et inclut des méthodes personnalisées pour rôles et permissions.

```php
<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $keyType = 'string'; // UUID primary key
    protected $fillable = ['nom', 'prenom', 'login', 'password', 'permissions', 'role', ...];
    protected $casts = ['permissions' => 'array'];

    /**
     * Vérifie si l'utilisateur possède une permission spécifique
     */
    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Vérifie si l'utilisateur a un rôle spécifique
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Relations avec Client et Admin
     */
    public function client() { return $this->hasOne(Client::class); }
    public function admin() { return $this->hasOne(Admin::class); }
}
```

### 2. Modèle Token (Personnalisé)

```php
<?php
namespace App\Models;

use Laravel\Passport\Token as PassportToken;

class Token extends PassportToken
{
    /**
     * Personnalisation du modèle Token si nécessaire
     * Par défaut, utilise la configuration Passport standard
     */
}
```

## 🛡️ Middleware Stack

### AuthMiddleware (`auth:api`)

**Emplacement**: `app/Http/Middleware/AuthMiddleware.php`

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next, ...$scopes): mixed
    {
        // 1. Vérification de l'authentification
        if (!$request->user()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // 2. Validation des scopes optionnels
        if ($scopes) {
            $this->validateScopes($request->user(), $scopes);
        }

        return $next($request);
    }

    private function validateScopes($user, $scopes)
    {
        if (!$user->hasAnyScope($scopes)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    }
}
```

**Utilisation**: Appliqué automatiquement via `auth:api` dans les routes.

### RoleMiddleware (`role:*`)

**Emplacement**: `app/Http/Middleware/RoleMiddleware.php`

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): mixed
    {
        if (!$request->user() || !$request->user()->hasRole($role)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
```

**Utilisation**:
```php
Route::middleware('role:admin')->group(function () {
    // Routes admin uniquement
});
```

### PermissionMiddleware (`permission:*`)

**Emplacement**: `app/Http/Middleware/PermissionMiddleware.php`

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $permission): mixed
    {
        if (!$request->user() || !$request->user()->hasPermission($permission)) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
```

### LoggingMiddleware

**Emplacement**: `app/Http/Middleware/LoggingMiddleware.php`

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoggingMiddleware
{
    public function handle(Request $request, Closure $next): mixed
    {
        $start = microtime(true);

        $response = $next($request);

        $duration = round((microtime(true) - $start) * 1000, 2);

        Log::info('API Request', [
            'user' => optional($request->user())->id,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'status' => $response->getStatusCode(),
            'duration_ms' => $duration,
        ]);

        return $response;
    }
}
```

## 📋 Policies Laravel

### ComptePolicy

**Emplacement**: `app/Policies/ComptePolicy.php`

```php
<?php
namespace App\Policies;

use App\Models\User;
use App\Models\Compte;
use Illuminate\Auth\Access\HandlesAuthorization;

class ComptePolicy
{
    use HandlesAuthorization;

    /**
     * Peut lister tous les comptes ?
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('compte:read');
    }

    /**
     * Peut voir un compte spécifique ?
     */
    public function view(User $user, Compte $compte): bool
    {
        // Admin peut voir tous les comptes
        if ($user->hasRole('admin')) {
            return $user->hasPermission('compte:read');
        }

        // Client ne peut voir que ses propres comptes
        if ($user->hasRole('client') && $user->client) {
            return $compte->client_id === $user->client->id &&
                   $user->hasPermission('compte:read');
        }

        return false;
    }

    /**
     * Peut créer un compte ?
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('compte:write');
    }

    /**
     * Peut modifier un compte ?
     */
    public function update(User $user, Compte $compte): bool
    {
        if ($user->hasRole('admin')) {
            return $user->hasPermission('compte:write');
        }

        if ($user->hasRole('client') && $user->client) {
            return $compte->client_id === $user->client->id &&
                   $user->hasPermission('compte:write');
        }

        return false;
    }

    /**
     * Peut supprimer un compte ?
     */
    public function delete(User $user, Compte $compte): bool
    {
        return $user->hasRole('admin') && $user->hasPermission('compte:write');
    }

    /**
     * Peut voir les transactions d'un compte ?
     */
    public function viewTransactions(User $user, Compte $compte): bool
    {
        return $this->view($user, $compte) && $user->hasPermission('transaction:read');
    }
}
```

### AdminPolicy

**Emplacement**: `app/Policies/AdminPolicy.php`

```php
<?php
namespace App\Policies;

use App\Models\User;

class AdminPolicy
{
    /**
     * Peut accéder au dashboard admin ?
     */
    public function view(User $user): bool
    {
        return $user->hasRole('admin') && $user->hasPermission('admin:read');
    }

    /**
     * Peut gérer les utilisateurs ?
     */
    public function manageUsers(User $user): bool
    {
        return $user->hasRole('admin') && $user->hasPermission('admin:write');
    }
}
```

## 🚪 Gates (AuthServiceProvider)

**Emplacement**: `app/Providers/AuthServiceProvider.php`

```php
<?php
namespace App\Providers;

use App\Models\Compte;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Compte::class => \App\Policies\ComptePolicy::class,
        Admin::class => \App\Policies\AdminPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Configuration Passport
        \Laravel\Passport\Passport::useTokenModel(\App\Models\Token::class);

        // Gates pour vérifications rapides
        Gate::define('is-admin', fn(User $user) => $user->hasRole('admin'));
        Gate::define('is-client', fn(User $user) => $user->hasRole('client'));
        Gate::define('has-permission', fn(User $user, string $perm) => $user->hasPermission($perm));
        Gate::define('can-access-bank-operations', fn(User $u) => $u->hasRole('admin') || $u->hasRole('client'));
    }
}
```

## 🛣️ Configuration des Routes

**Emplacement**: `routes/api.php`

```php
<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Routes publiques (pas d'authentification requise)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::middleware('auth:api')->post('logout', [AuthController::class, 'logout']);
});

// Routes OAuth2 Passport (optionnelles)
Route::prefix('oauth')->group(function () {
    Route::post('/token', [AccessTokenController::class, 'issueToken'])
        ->middleware(['throttle:60,1'])
        ->name('passport.token');
    // ... autres routes OAuth2
});

// Routes protégées (authentification + logging requis)
Route::middleware(['auth:api', 'logging'])->prefix('v1')->group(function () {

    // Routes ADMIN uniquement (rôle admin requis)
    Route::middleware('role:admin')->group(function () {
        Route::get('admin/dashboard', [AdminController::class, 'dashboard']);
        Route::apiResource('users', UserController::class);
    });

    // Routes COMPTES (policy-based authorization)
    Route::apiResource('comptes', CompteController::class)
        ->middleware('can:viewAny,App\Models\Compte');

    // Routes TRANSACTIONS (vérification propriétaire)
    Route::get('comptes/{compte}/transactions', [CompteController::class, 'transactions'])
        ->middleware('can:viewTransactions,compte');
});
```

## ⚙️ Contrôleur d'Authentification

**Emplacement**: `app/Http/Controllers/AuthController.php`

```php
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * Connexion utilisateur
     */
    public function login(Request $request)
    {
        // Validation des données d'entrée
        $request->validate([
            'login' => 'required',
            'password' => 'required'
        ]);

        // Tentative d'authentification
        if (!Auth::attempt($request->only('login', 'password'))) {
            return $this->errorResponse('Identifiants invalides', 401);
        }

        $user = Auth::user();

        // Récupération des scopes (permissions)
        $scopes = $this->getScopesForUser($user);

        // Création du token Passport
        $token = $user->createToken('API Access');

        return $this->successResponse([
            'user' => $user,
            'token' => $token->accessToken,
            'token_type' => 'Bearer',
            'expires_in' => config('passport.tokensExpireIn'),
        ], 'Connexion réussie');
    }

    /**
     * Rafraîchissement du token (simplifié)
     */
    public function refresh(Request $request)
    {
        // Dans un vrai système, utiliser refresh tokens
        return $this->login($request);
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->successResponse(null, 'Déconnexion réussie');
    }

    /**
     * Conversion permissions → scopes
     */
    private function getScopesForUser($user): array
    {
        return $user->permissions ?? [];
    }
}
```

## 🔐 Permissions et Rôles

### Rôles Disponibles
- **`admin`** - Administrateur système
- **`client`** - Client bancaire

### Permissions Disponibles

#### Pour Admin:
```php
[
    'admin:read',      // Lire données admin
    'admin:write',     // Modifier données admin
    'compte:read',     // Lire comptes
    'compte:write',    // Modifier comptes
    'transaction:read' // Lire transactions
]
```

#### Pour Client:
```php
[
    'compte:read',     // Lire ses comptes
    'compte:write',    // Modifier ses comptes
    'transaction:read' // Lire ses transactions
]
```

## 🔄 Flux d'Authentification

### 1. Connexion (Login)

```
Client Request:
POST /api/auth/login
{
    "login": "admin@banque.com",
    "password": "password"
}

Server Response:
{
    "success": true,
    "message": "Connexion réussie",
    "data": {
        "user": {...},
        "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...",
        "token_type": "Bearer",
        "expires_in": null
    }
}
```

### 2. Accès aux Routes Protégées

```
Client Request:
GET /api/v1/admin/dashboard
Headers:
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

Server Processing:
1. AuthMiddleware → Vérifie token JWT
2. LoggingMiddleware → Log la requête
3. RoleMiddleware → Vérifie rôle 'admin'
4. Controller → Traite la requête
5. LoggingMiddleware → Log la réponse
```

### 3. Déconnexion (Logout)

```
Client Request:
POST /api/auth/logout
Headers:
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...

Server Processing:
1. AuthMiddleware → Vérifie token
2. Controller → Révoque le token
3. Response → Confirmation
```

## 🧪 Tests d'Authentification

### Tests Réussis ✅

#### Login Admin
```bash
curl -X POST http://localhost:8001/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"login":"admin@banque.com","password":"password"}'
```

#### Accès Dashboard Admin
```bash
curl -X GET http://localhost:8001/api/v1/admin/dashboard \
  -H "Authorization: Bearer {TOKEN}"
```

#### Liste Comptes
```bash
curl -X GET http://localhost:8001/api/v1/comptes \
  -H "Authorization: Bearer {TOKEN}"
```

### Tests Échoués ❌

#### Création Compte (Erreur DB)
```bash
curl -X POST http://localhost:8001/api/v1/comptes \
  -H "Authorization: Bearer {TOKEN}" \
  -d '{"type": "cheque", "soldeInitial": 15000, ...}'
# → Erreur base de données (contrainte unicité)
```

## 🔧 Configuration et Installation

### Variables d'environnement (.env)
```env
# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_banking
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Passport
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=1
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=your_secret
```

### Installation Passport
```bash
# Installer les clés de chiffrement
php artisan passport:install

# Générer les clés RSA
php artisan passport:keys
```

### Migration et Seed
```bash
# Migrations
php artisan migrate

# Seeders (utilisateurs de test)
php artisan db:seed --class=UserSeeder
```

## 🚨 Points de Sécurité

### ✅ Bonnes Pratiques Implémentées
- **Hashing des mots de passe** avec bcrypt
- **Tokens JWT** avec expiration
- **UUID** comme clés primaires
- **Validation stricte** des entrées
- **Logging complet** des actions
- **Middleware en couches** pour séparation des responsabilités

### ⚠️ Recommandations de Sécurité
- **Rate Limiting** sur les endpoints sensibles
- **Refresh Tokens** pour meilleure sécurité
- **2FA** pour les comptes admin
- **Audit logging** plus détaillé
- **Encryption** des données sensibles en DB

## 📊 Métriques et Monitoring

### Logging Automatique
Toutes les requêtes API sont loggées avec:
- ID utilisateur
- Méthode HTTP
- URL complète
- Code de statut
- Durée d'exécution (ms)

### Exemple de Log
```
[2025-11-05 14:57:30] local.INFO: API Request
{
    "user": "8b5b5984-9074-4d8d-85ca-81057cdfaa1a",
    "method": "GET",
    "url": "http://localhost:8001/api/v1/comptes",
    "status": 200,
    "duration_ms": 45.67
}
```

## 🔍 Dépannage

### Erreurs Courantes

#### 401 Unauthorized
- Token manquant ou invalide
- Token expiré
- Vérifier le header `Authorization: Bearer {token}`

#### 403 Forbidden
- Permissions insuffisantes
- Rôle incorrect
- Vérifier les policies et gates

#### Erreur Base de Données
- Contraintes d'unicité violées (email, téléphone)
- Clés étrangères manquantes
- Vérifier les données avant insertion

### Debug Commands
```bash
# Vérifier les tokens actifs
php artisan passport:tokens

# Nettoyer les tokens expirés
php artisan passport:purge

# Lister les routes
php artisan route:list --path=api
```

## 📚 Références

- [Laravel Passport Documentation](https://laravel.com/docs/passport)
- [Laravel Policies](https://laravel.com/docs/authorization#policies)
- [Laravel Gates](https://laravel.com/docs/authorization#gates)
- [OAuth2 RFC](https://tools.ietf.org/html/rfc6749)

---

*Ce guide couvre complètement le système d'authentification. Pour toute question ou modification, consulter la documentation Laravel officielle ou les commentaires dans le code.*