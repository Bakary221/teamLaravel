<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthMiddleware
{
    public function handle(Request $request, Closure $next, ...$scopes)
    {
        // Vérifie si l'utilisateur est connecté
        if (!$request->user())
            return response()->json(['error' => 'Unauthenticated'], 401);

        // Si des scopes sont passés, valide-les
        if ($scopes)
            $this->validateScopes($request->user(), $scopes);

        return $next($request);
    }

    private function validateScopes($user, $scopes)
    {
        if (!$user->hasAnyScope($scopes))
            return response()->json(['error' => 'Unauthorized'], 403);
    }
}
