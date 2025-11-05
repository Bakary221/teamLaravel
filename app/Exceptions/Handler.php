<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (ValidationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Données de validation invalides',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $this->renderable(function (\Illuminate\Database\QueryException $e, $request) {
            if ($request->is('api/*')) {
                \Illuminate\Support\Facades\Log::error('Erreur de base de données: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de base de données',
                    'errors' => ['database' => 'Une erreur est survenue lors de l\'accès à la base de données'],
                ], 500);
            }
        });

        $this->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Authentification requise',
                    'errors' => ['auth' => 'Token d\'authentification manquant ou invalide'],
                ], 401);
            }
        });

        $this->renderable(function (\Illuminate\Auth\Access\AuthorizationException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé',
                    'errors' => ['auth' => 'Vous n\'avez pas les permissions nécessaires'],
                ], 403);
            }
        });

        $this->renderable(function (\Illuminate\Database\Eloquent\ModelNotFoundException $e, $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ressource non trouvée',
                    'errors' => ['resource' => 'L\'élément demandé n\'existe pas'],
                ], 404);
            }
        });
    }
}
