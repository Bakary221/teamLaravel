# 🏦 Laravel Banking System - Team Laravel

Un système bancaire complet développé avec Laravel, offrant une API REST sécurisée pour la gestion des comptes bancaires, des clients et des transactions. Le système implémente une authentification robuste avec Laravel Passport et un contrôle d'accès granulaire basé sur les rôles et permissions.

## 📋 Table des Matières

- [Fonctionnalités](#-fonctionnalités)
- [Architecture](#-architecture)
- [Technologies](#-technologies)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Utilisation](#-utilisation)
- [API Documentation](#-api-documentation)
- [Authentification](#-authentification)
- [Base de Données](#-base-de-données)
- [Tests](#-tests)
- [Déploiement](#-déploiement)
- [Contribuer](#-contribuer)
- [Licence](#-licence)

## ✨ Fonctionnalités

### 👥 Gestion des Utilisateurs
- **Rôles**: Admin, Client
- **Permissions granulaire**: Lecture/écriture par module
- **Profils utilisateurs** avec informations personnelles

### 💳 Gestion des Comptes
- **Types de comptes**: Chèque, Épargne
- **Création automatique** de numéro de compte unique
- **Calcul automatique** du solde via transactions
- **Statuts**: Actif, Fermé, Bloqué

### 🔄 Transactions
- **Dépôts initiaux** lors de création de compte
- **Historique complet** des transactions
- **Calcul de solde** en temps réel

### 🔐 Sécurité
- **Authentification JWT** avec Laravel Passport
- **Middleware personnalisés** pour contrôle d'accès
- **Policies Laravel** pour autorisation métier
- **Logging complet** des actions API

### 📊 Dashboard Admin
- **Statistiques générales**: Nombre d'utilisateurs, comptes actifs
- **Gestion des utilisateurs** (CRUD complet)
- **Supervision** des opérations bancaires

## 🏗️ Architecture

```
Laravel Banking System
├── 📁 app/
│   ├── Http/Controllers/          # Contrôleurs API
│   ├── Models/                    # Modèles Eloquent
│   ├── Policies/                  # Politiques d'autorisation
│   ├── Middleware/                # Middlewares personnalisés
│   └── Providers/AuthServiceProvider.php
├── 📁 database/
│   ├── migrations/                # Migrations base de données
│   └── seeders/                   # Données de test
├── 📁 routes/
│   └── api.php                    # Routes API
└── 📁 docs/
    ├── AUTH.md                    # Guide authentification détaillé
    └── README_Postman.md          # Guide utilisation Postman
```

### Patterns Architecturaux
- **Repository Pattern** (implicite via Eloquent)
- **Policy-Based Authorization**
- **Middleware Stack** pour sécurité en couches
- **API Resource Classes** pour transformation des données

## 🛠️ Technologies

### Backend
- **Laravel 10.x** - Framework PHP
- **Laravel Passport** - Authentification OAuth2/JWT
- **PostgreSQL** - Base de données principale
- **PHP 8.1+** - Version minimale requise

### Sécurité & Authentification
- **JWT Tokens** avec expiration
- **BCrypt** pour hashage des mots de passe
- **UUID** comme clés primaires
- **CORS** configuré pour API

### Outils de Développement
- **Composer** - Gestion des dépendances PHP
- **NPM** - Gestion des assets frontend
- **Docker** - Containerisation (optionnel)
- **Laravel Debugbar** - Debugging en développement

### Testing
- **PHPUnit** - Tests unitaires
- **Laravel Dusk** - Tests fonctionnels (optionnel)

## 🚀 Installation

### Prérequis
- PHP 8.1 ou supérieur
- Composer
- PostgreSQL 12+
- Node.js & NPM (pour assets frontend)
- Git

### Étapes d'Installation

1. **Cloner le repository**
   ```bash
   git clone https://github.com/votre-username/teamLaravel.git
   cd teamLaravel
   ```

2. **Installer les dépendances PHP**
   ```bash
   composer install
   ```

3. **Installer les dépendances JavaScript**
   ```bash
   npm install
   ```

4. **Configuration de l'environnement**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configuration de la base de données**
   Éditer `.env`:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=laravel_banking
   DB_USERNAME=votre_username
   DB_PASSWORD=votre_password
   ```

6. **Migration et seed de la base de données**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

7. **Installation de Laravel Passport**
   ```bash
   php artisan passport:install
   php artisan passport:keys
   ```

8. **Build des assets (optionnel)**
   ```bash
   npm run build
   ```

9. **Démarrer le serveur**
   ```bash
   php artisan serve --host=127.0.0.1 --port=8001
   ```

## ⚙️ Configuration

### Variables d'Environnement (.env)

```env
# Application
APP_NAME="Laravel Banking System"
APP_ENV=local
APP_KEY=base64:your_app_key
APP_DEBUG=true
APP_URL=http://localhost:8001

# Database
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_banking
DB_USERNAME=postgres
DB_PASSWORD=password

# Passport
PASSPORT_PERSONAL_ACCESS_CLIENT_ID=1
PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=your_secret_here

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

### Configuration CORS

Le fichier `config/cors.php` est configuré pour permettre les requêtes depuis les origines nécessaires.

### Configuration Passport

Passport est configuré dans `config/passport.php` avec les paramètres par défaut Laravel.

## 📖 Utilisation

### Démarrage Rapide

1. **Serveur de développement**
   ```bash
   php artisan serve --host=127.0.0.1 --port=8001
   ```

2. **Base de données de test**
   ```bash
   php artisan migrate:fresh --seed
   ```

3. **Utilisateur de test**
   - **Admin**: `admin@banque.com` / `password`
   - **Client**: Généré par les seeders

### Utilisation avec Docker (Optionnel)

```bash
# Démarrer les services
docker-compose up -d

# Installation dans le container
docker-compose exec app composer install
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
```

## 📚 API Documentation

### Endpoints Principaux

#### Authentification
- `POST /api/auth/login` - Connexion utilisateur
- `POST /api/auth/refresh` - Rafraîchissement token
- `POST /api/auth/logout` - Déconnexion

#### Comptes (Protégés)
- `GET /api/v1/comptes` - Lister les comptes
- `POST /api/v1/comptes` - Créer un compte
- `PUT /api/v1/comptes/{id}` - Modifier un compte
- `DELETE /api/v1/comptes/{id}` - Supprimer un compte
- `GET /api/v1/comptes/{id}/transactions` - Transactions d'un compte

#### Administration (Admin uniquement)
- `GET /api/v1/admin/dashboard` - Dashboard statistiques
- `GET /api/v1/users` - Gestion utilisateurs
- `POST /api/v1/users` - Créer utilisateur
- `PUT /api/v1/users/{id}` - Modifier utilisateur
- `DELETE /api/v1/users/{id}` - Supprimer utilisateur

### Format des Réponses API

Toutes les réponses suivent le format standardisé:

```json
{
  "success": true|false,
  "message": "Description de l'action",
  "data": { ... } | null,
  "errors": { ... } | null,
  "pagination": { ... } | null
}
```

### Codes de Statut HTTP
- `200` - Succès
- `201` - Création réussie
- `400` - Requête invalide
- `401` - Non authentifié
- `403` - Interdit (permissions insuffisantes)
- `404` - Ressource non trouvée
- `422` - Erreur de validation
- `500` - Erreur serveur

## 🔐 Authentification

### Vue d'ensemble
Le système utilise **Laravel Passport** pour l'authentification OAuth2 avec tokens JWT. Voir [`AUTH.md`](AUTH.md) pour une documentation complète.

### Rôles et Permissions

#### Rôles Disponibles
- **`admin`**: Accès complet au système
- **`client`**: Accès limité à ses propres données

#### Permissions
- `admin:read/write` - Gestion administrative
- `compte:read/write` - Gestion des comptes
- `transaction:read` - Lecture des transactions

### Middleware Stack
1. **AuthMiddleware** - Vérification token JWT
2. **LoggingMiddleware** - Journalisation des requêtes
3. **RoleMiddleware** - Vérification des rôles
4. **PermissionMiddleware** - Vérification des permissions
5. **Policy Gates** - Autorisation métier

## 🗄️ Base de Données

### Schéma Principal

#### Table `users`
- `id` (UUID) - Clé primaire
- `nom`, `prenom` - Informations personnelles
- `login`, `email` - Identifiants
- `password` - Mot de passe hashé
- `role` - Rôle utilisateur (admin/client)
- `permissions` - Permissions JSON
- `status` - Statut (Actif/Inactif)

#### Table `clients`
- `id` (UUID) - Clé primaire
- `user_id` (UUID) - Référence utilisateur
- `profession` - Profession du client

#### Table `comptes`
- `id` (UUID) - Clé primaire
- `client_id` (UUID) - Référence client
- `numero_compte` - Numéro unique généré
- `type` - Type de compte (cheque/epargne)
- `statut` - Statut du compte
- `motif_blocage` - Raison de blocage (optionnel)

#### Table `transactions`
- `id` (UUID) - Clé primaire
- `compte_id` (UUID) - Compte source
- `destinataire_id` (UUID) - Compte destination
- `type` - Type de transaction (depot/retrait/transfert)
- `montant` - Montant de la transaction
- `date_transaction` - Date et heure

### Relations
- **User** → **Client** (1:1)
- **Client** → **Comptes** (1:N)
- **Compte** → **Transactions** (1:N)

### Migrations
Toutes les migrations sont versionnées dans `database/migrations/` avec des noms descriptifs.

## 🧪 Tests

### Tests Disponibles
- **Tests unitaires** avec PHPUnit
- **Tests fonctionnels** des contrôleurs
- **Tests d'intégration** des middlewares et policies

### Exécution des Tests
```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter=AuthControllerTest

# Tests avec couverture
php artisan test --coverage
```

### Tests d'API avec Postman
Voir [`README_Postman.md`](README_Postman.md) pour un guide complet d'utilisation de Postman.

## 🚢 Déploiement

### Préparation pour Production

1. **Optimisation**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   composer install --optimize-autoloader --no-dev
   ```

2. **Variables d'environnement**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   ```

3. **Serveur Web**
   - Configuration Nginx/Apache
   - PHP-FPM recommandé
   - SSL/TLS obligatoire

4. **Base de Données**
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=ProductionSeeder
   ```

### Déploiement Docker

```bash
# Build et déploiement
docker-compose -f docker-compose.prod.yml up -d --build

# Migration en production
docker-compose exec app php artisan migrate --force
```

### Monitoring
- **Logs Laravel** dans `storage/logs/`
- **Health checks** sur `/api/health`
- **Métriques** via Laravel Telescope (optionnel)

## 🤝 Contribuer

### Processus de Contribution

1. **Fork** le repository
2. **Créer** une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. **Commiter** vos changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. **Push** vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. **Créer** une Pull Request

### Standards de Code
- **PSR-12** pour le PHP
- **Tests** obligatoires pour nouvelles fonctionnalités
- **Documentation** à jour
- **Commits** descriptifs

### Tests avant Commit
```bash
composer test
php artisan test
npm run lint  # si applicable
```

## 📄 Licence

Ce projet est sous licence **MIT**. Voir le fichier `LICENSE` pour plus de détails.

## 📞 Support

### Ressources
- **Documentation Laravel**: https://laravel.com/docs
- **Laravel Passport**: https://laravel.com/docs/passport
- **Issues GitHub**: Pour signaler des bugs

### Contact
- **Email**: votre-email@exemple.com
- **Issues**: https://github.com/votre-username/teamLaravel/issues

---

## 🔗 Liens Utiles

- [Laravel Framework](https://laravel.com/)
- [Laravel Passport](https://laravel.com/docs/passport)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [Docker Documentation](https://docs.docker.com/)

---

**Développé avec ❤️ par l'équipe Team Laravel**
