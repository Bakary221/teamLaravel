<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;
use Laravel\Passport\Http\Controllers\PersonalAccessTokenController;
use Laravel\Passport\Http\Controllers\TransientTokenController;
use App\Http\Controllers\CompteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function () {
    Route::get('comptes', [CompteController::class, 'index']);
    Route::post('comptes', [CompteController::class, 'store']);
});









































Route::prefix('oauth')->group(function () {
    Route::post('/token', [AccessTokenController::class, 'issueToken'])
        ->middleware(['throttle:60,1'])
        ->name('passport.token');

    Route::get('/authorize', [AuthorizationController::class, 'authorize'])
        ->name('passport.authorizations.authorize');

    Route::post('/authorize', [ApproveAuthorizationController::class, 'approve'])
        ->name('passport.authorizations.approve');

    Route::delete('/authorize', [DenyAuthorizationController::class, 'deny'])
        ->name('passport.authorizations.deny');

    Route::post('/personal-access-tokens', [PersonalAccessTokenController::class, 'store'])
        ->name('passport.personal.tokens');

    Route::get('/token/refresh', [TransientTokenController::class, 'refresh'])
        ->middleware('auth:api')
        ->name('passport.token.refresh');
});
