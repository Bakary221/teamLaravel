<?php

namespace App\Providers;
use App\Models\Compte;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Compte::class => \App\Policies\ComptePolicy::class,
        Admin::class => \App\Policies\AdminPolicy::class,
    ];


    /**
     * Register any authentication / authorization services.
     */
    public function register()
    {
        // Enregistrer les repositories
        $this->app->bind(\App\Repositories\Interfaces\CompteRepositoryInterface::class, \App\Repositories\Eloquent\CompteRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\UserRepositoryInterface::class, \App\Repositories\Eloquent\UserRepository::class);
        $this->app->bind(\App\Repositories\Interfaces\ClientRepositoryInterface::class, \App\Repositories\Eloquent\ClientRepository::class);

        // Enregistrer les services
        $this->app->singleton(\App\Services\CompteService::class, function ($app) {
            return new \App\Services\CompteService(
                $app->make(\App\Repositories\Interfaces\CompteRepositoryInterface::class),
                $app->make(\App\Repositories\Interfaces\UserRepositoryInterface::class),
                $app->make(\App\Repositories\Interfaces\ClientRepositoryInterface::class)
            );
        });
    }

    public function boot()
    {
        $this->registerPolicies();

        \Laravel\Passport\Passport::useTokenModel(\App\Models\Token::class);

        Gate::define('is-admin', fn(User $user) => $user->hasRole('admin'));
        Gate::define('is-client', fn(User $user) => $user->hasRole('client'));
        Gate::define('has-permission', fn(User $user, string $perm) => $user->hasPermission($perm));
        Gate::define('can-access-bank-operations', fn(User $u) => $u->hasRole('admin') || $u->hasRole('client'));
    }
}
