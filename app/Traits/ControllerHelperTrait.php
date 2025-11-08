<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait ControllerHelperTrait
{
    /**
     * Valider un UUID
     */
    protected function validateUuid(string $id, string $fieldName = 'ID'): bool
    {
        if (!Str::isUuid($id)) {
            $this->errorResponse("{$fieldName} invalide", 400);
            return false;
        }
        return true;
    }

    /**
     * Récupérer un modèle ou retourner une erreur
     */
    protected function findOrFail($model, string $id, string $modelName = 'Ressource')
    {
        $instance = $model::find($id);
        if (!$instance) {
            abort(404, "{$modelName} non trouvé");
        }
        return $instance;
    }

    /**
     * Vérifier l'autorisation et lever une exception si refusée
     */
    protected function authorizeAction(string $ability, $model = null): void
    {
        $this->authorize($ability, $model);
    }

    /**
     * Wrapper pour les opérations avec gestion d'erreur
     */
    protected function tryAction(callable $action, string $errorMessage = 'Erreur interne')
    {
        try {
            return $action();
        } catch (\Exception $e) {
            return $this->errorResponse($errorMessage, 500);
        }
    }
}