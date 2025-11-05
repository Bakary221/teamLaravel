## Laravel pour débutants — Documentation complète et progressive

Bienvenue ! Ce document vise à expliquer, pas à pas, les concepts clés de Laravel pour un·e débutant·e. Il est rédigé en français et contient des explications, des exemples et une petite trousse d'outils pour que vous puissiez commencer à coder avec confiance.

---

## 1. Préambule — Qu'est-ce que Laravel ?

Laravel est un framework PHP moderne pour construire des applications web (sites et APIs). Il fournit une structure (conventions) et des outils (artisan, Eloquent, migration, middleware...) qui accélèrent le développement et maintiennent le code organisé.

Objectifs pour un·e débutant·e :
- Comprendre l'architecture MVC.
- Savoir créer routes, controllers, views et models.
- Manipuler la base de données avec migrations et Eloquent.
- Gérer validation, authentification et tests de base.

---

## 2. Prérequis

- PHP (v8.x recommandée). 
- Composer (gestionnaire de dépendances PHP).
- Un serveur local (Valet, Homestead, Docker, ou le serveur PHP intégré).
- Une base de données (MySQL, PostgreSQL, SQLite pour tests rapides).

Si vous travaillez avec Docker, adaptez les commandes données ici à votre configuration.

---

## 3. Structure d'un projet Laravel (essentiel)

- `routes/` : définit toutes les routes HTTP (web, api, console, channels).
- `app/Http/Controllers/` : logique des controllers (actions liées aux routes).
- `resources/views/` : templates Blade (HTML + directives Laravel).
- `app/Models/` : modèles Eloquent (représentation des tables DB).
- `database/migrations/` : scripts pour créer/modifier les tables.
- `database/factories/` : factories pour générer des données de test.
- `database/seeders/` : peupler la base avec des données initiales.
- `app/Providers/` : providers pour enregistrer services et bindings.
- `.env` : configuration d'environnement (DB, mail, keys...).

Connaître ces dossiers permet de rapidement localiser où ajouter du code.

---

## 4. Cycle de développement simple

1. Créer une route dans `routes/web.php` ou `routes/api.php`.
2. Relier la route à un controller ou une closure.
3. Écrire la logique dans un controller (ou model si logique DB).
4. Créer une vue Blade pour l'affichage (si web).
5. Créer/mettre à jour une migration si la structure DB change.
6. Lancer `php artisan migrate`.

---

## 5. Routage (routes)

Fichier principal : `routes/web.php` (pour pages web) et `routes/api.php` (pour API REST).

Exemple de route :

```php
use App\Http\Controllers\CompteController;

Route::get('/comptes', [CompteController::class, 'index']);
Route::post('/comptes', [CompteController::class, 'store']);
```

Conseils :
- Utilisez `Route::resource('comptes', CompteController::class)` pour les routes RESTful courantes.
- Groupes et middleware : `Route::middleware('auth')->group(function () { ... });`

---

## 6. Controllers

- Rôle : recevoir la requête, valider, appeler la logique métier (souvent dans des modèles ou services), et retourner une réponse.
- Créer : `php artisan make:controller CompteController`.

Exemple minimal :

```php
class CompteController extends Controller
{
    public function index()
    {
        $comptes = Compte::all();
        return response()->json($comptes);
    }
}
```

Bonnes pratiques :
- Gardez les controllers légers : déléguez la logique lourde aux modèles, services, ou actions.
- Utilisez les Form Requests pour la validation.

---

## 7. Requêtes et Validation (Form Requests)

Form Requests : classes dédiées à la validation.

Créer : `php artisan make:request StoreCompteRequest`.

Exemple (simplifié) :

```php
public function rules()
{
    return [
        'nom' => 'required|string|max:255',
        'solde' => 'required|numeric|min:0',
    ];
}
```

Avantages : clarté, réutilisation et tests facilités.

---

## 8. Middleware

- Rôle : filtrer les requêtes HTTP globalement ou par groupe de routes (auth, throttle, CORS...).
- Créer : `php artisan make:middleware CheckSomething`.

Exemple : protéger des routes avec `auth` ou `throttle:60,1`.

---

## 9. Models & Eloquent (ORM)

Eloquent simplifie l'accès à la base.

- Un Model représente une table.
- Méthodes utiles : `all`, `find`, `where`, `create`, `update`, `delete`.

Exemple :

```php
$compte = Compte::create(['nom' => 'A', 'solde' => 100]);
$comptes = Compte::where('solde', '>', 0)->get();
```

Relations (les plus courantes) :
- one-to-many : `hasMany` / `belongsTo`.
- one-to-one : `hasOne` / `belongsTo`.
- many-to-many : `belongsToMany`.
- polymorphic : `morphMany`, `morphTo`.

Exemple relation :

```php
class Client extends Model
{
    public function comptes()
    {
        return $this->hasMany(Compte::class);
    }
}
```

Accessors & Mutators : transformer les données à l'accès et l'enregistrement (`getFooAttribute`, `setFooAttribute`).

Casts : protéger le typage (`protected $casts = ['is_active' => 'boolean', 'meta' => 'array'];`).

Soft Deletes : `use SoftDeletes;` puis `protected $dates = ['deleted_at'];` — permet la suppression logique.

---

## 10. Migrations, Seeders et Factories

- Migrations : versionnent la structure DB. Créer : `php artisan make:migration create_comptes_table`.
- Seeders : scripts pour insérer des données initiales (`php artisan db:seed`).
- Factories : générer des données de test (utilisées avec seeders ou tests).

Commandes utiles :

```bash
php artisan migrate       # applique les migrations
php artisan migrate:rollback
php artisan migrate:fresh --seed
php artisan db:seed
```

---

## 11. API Resources (serialisation)

Utilisez `php artisan make:resource CompteResource` pour transformer la sortie JSON proprement.

Exemple :

```php
return new CompteResource($compte);
```

Collections : `CompteResource::collection($comptes)`.

---

## 12. Authentification et Autorisation

Authentification : Laravel propose plusieurs options (Jetstream, Breeze, Sanctum, Passport). Pour API simple, `Sanctum` est léger et recommandé.

Autorisation : Policies et Gates.

Créer une policy : `php artisan make:policy ComptePolicy --model=Compte`.

Dans controller : `authorize('update', $compte);`

---

## 13. Événements, Listeners et Jobs

- Events : déclenchent des actions (p.ex. `TransactionCreated`).
- Listeners : réagissent aux events.
- Jobs : tâches asynchrones (envoyées aux queues).

Créer un job : `php artisan make:job SendNotificationJob`.

---

## 14. Queues et scheduling

- Queue : déchargez le travail lourd (emails, traitement) pour améliorer la réactivité.
- Scheduler : planifier des commandes (`app/Console/Kernel.php`).

Exemple Cron : `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`.

---

## 15. Tests (PHPUnit) — basiques

- Tests d'unités et tests fonctionnels (feature tests).
- Créer un test : `php artisan make:test CompteTest --unit` ou `--feature`.

Exemple rapide :

```php
public function test_can_create_compte()
{
    $response = $this->postJson('/api/comptes', [/* données valides */]);
    $response->assertStatus(201);
}
```

Conseil : utilisez factories et base de données en mémoire (sqlite) pour tests rapides.

---

## 16. Debugging & outils utiles

- `dd()` / `dump()` pour inspecter des variables rapidement.
- Laravel Debugbar (package) et Telescope (package officiel) pour debugging avancé.
- Logs : `storage/logs/laravel.log`.

---

## 17. Fichiers, Storage et Uploads

- Config : `config/filesystems.php` (local, s3...).
- Stocker fichiers : `Storage::put('path', $content)` et `Storage::url('path')`.

---

## 18. Configuration, env et bonnes pratiques

- Ne mettez jamais de secrets dans le code — utilisez `.env`.
- Versionnez `config/*.php` mais pas `.env`.

Bonnes pratiques :
- Utilisez des variables d'environnement pour DB, mail, keys.
- Mettez en place des checks pour s'assurer que `APP_KEY` est défini en production.

---

## 19. Commandes artisan & cheatsheet utile

- `php artisan serve` — lance un serveur local (développement).
- `php artisan make:controller NameController` — créer un controller.
- `php artisan make:model Name -m` — crée modèle + migration.
- `php artisan migrate`, `php artisan db:seed`.
- `composer install` / `composer update`.
- `npm install` puis `npm run dev` (assets, Vite).

---

## 20. Déploiement — points fondamentaux

- Générez `APP_KEY` (`php artisan key:generate`).
- Exécutez migrations en production avec précaution (`php artisan migrate --force`).
- Cachez configuration et routes pour perf : `php artisan config:cache`, `php artisan route:cache`.
- Assurez-vous des permissions sur `storage` et `bootstrap/cache`.

---

## 21. Petits exercices pratiques (pour apprendre)

1. Créez une ressource `Compte` avec migrations, factory, seeder, controller et routes API. Testez la création et la lecture.
2. Ajoutez validation via Form Request.
3. Implémentez authentification API simple avec Sanctum et protégez les routes.
4. Créez une job pour envoyer une notification (simulez l'envoi), mettez-la dans la queue et testez qu'elle est dispatchée.

---

## 22. Glossaire rapide

- MVC : Model — View — Controller.
- ORM : Object-Relational Mapping (Eloquent).
- Migration : script versionné modifiant la DB.
- Seeder / Factory : génération de données pour tests.
- Middleware : couche intermédiaire sur la requête.

---

## 23. Ressources pour continuer

- Documentation officielle (en anglais) : https://laravel.com/docs
- Laracasts (tutoriels) : https://laracasts.com

---

## 24. Résumé & prochaines étapes

Vous avez maintenant une vue d'ensemble des éléments essentiels de Laravel. Pour progresser :
- Suivez les exercices ci-dessus.
- Lisez la documentation officielle sur les sujets que vous utilisez.
- Pratiquez en construisant de petites API ou applications CRUD.

Bon apprentissage !

---

Fichier créé : `resources/docs/Laravel_pour_debutants.md` — ouvert pour mise à jour si vous voulez plus d'exemples ou adaptation à votre projet (par ex. integration Sanctum, description des models présents dans votre code, ou traductions spécifiques).
