<?php

declare(strict_types=1);

use App\Domains\Identity\Presentation\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('identity/auth')
    ->name('identity.auth.')
    ->group(function (): void {
        Route::post('/password-register', [AuthController::class, 'passwordRegister'])
            ->name('password-register');

        Route::post('/password-login', [AuthController::class, 'passwordLogin'])
            ->name('password-login');

        Route::middleware(['firebase.token'])->group(function (): void {
            Route::post('/firebase-login', [AuthController::class, 'firebaseLogin'])
                ->name('firebase-login');
        });

        Route::middleware(['auth:sanctum'])->group(function (): void {
            Route::get('/me', [AuthController::class, 'me'])
                ->name('me');

            Route::post('/logout', [AuthController::class, 'logout'])
                ->name('logout');

            Route::post('/switch-role', [AuthController::class, 'switchRole'])
                ->name('switch-role');
        });
    });