<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Logger l'opération avant traitement
        Log::info('Operation logged', [
            'timestamp' => now(),
            'host' => $request->getHost(),
            'operation' => $request->method() . ' ' . $request->path(), // Ex. : PATCH /api/v1/comptes/{id}
            'resource' => $request->route('compteId') ?? 'N/A', // ID du compte
            'user_id' => auth()->id() ?? 'N/A', // Utilisateur connecté
        ]);

        return $next($request);
    }
}
