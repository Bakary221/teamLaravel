<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponseTrait;

class AuthController extends Controller
{
    use ApiResponseTrait;
    public function login(Request $request)
    {
        $request->validate(['login' => 'required', 'password' => 'required']);

        if (!Auth::attempt($request->only('login', 'password')))
            return $this->errorResponse('Identifiants invalides', 401);

        $user = Auth::user();
        $scopes = $this->getScopesForUser($user);

        $token = $user->createToken('API Access');

        return $this->successResponse([
            'user' => $user,
            'token' => $token->accessToken,
            'token_type' => 'Bearer',
            'expires_in' => config('passport.tokensExpireIn'),
        ], 'Connexion réussie');
    }

    public function refresh(Request $request)
    {
        // This would typically use refresh tokens, but for simplicity we'll re-authenticate
        return $this->login($request);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return $this->successResponse(null, 'Déconnexion réussie');
    }

    private function getScopesForUser($user)
    {
        // Convert permissions to scopes
        return $user->permissions ?? [];
    }
}
