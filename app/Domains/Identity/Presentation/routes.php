<?php

use App\Domains\Identity\Presentation\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('identity/auth')
    ->name('identity.auth.')
    ->group(function () {
        Route::middleware(['firebase.token'])->group(function () {
            Route::post('/firebase-login', [AuthController::class, 'firebaseLogin'])
                ->name('firebase-login');

            Route::post('/firebase-register', [AuthController::class, 'firebaseRegister'])
                ->name('firebase-register');
        });

        Route::middleware(['auth:sanctum', 'api.token'])->group(function () {
            Route::get('/me', [AuthController::class, 'me'])
                ->name('me');

            Route::post('/logout', [AuthController::class, 'logout'])
                ->name('logout');

            Route::post('/switch-role', [AuthController::class, 'switchRole'])
                ->name('switch-role');
        });
    });
