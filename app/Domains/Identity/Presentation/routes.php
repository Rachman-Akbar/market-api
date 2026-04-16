<?php

use App\Domains\Identity\Presentation\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('identity')
    ->name('identity.')
    ->group(function (): void {
        Route::prefix('auth')->group(function (): void {
            Route::post('register', [AuthController::class, 'register']);
            Route::post('login', [AuthController::class, 'login']);

            Route::middleware(['auth:sanctum', 'api.token'])->group(function (): void {
                Route::get('me', [AuthController::class, 'me']);
                Route::post('logout', [AuthController::class, 'logout']);
            });

            if ((bool) config('services.firebase.auth_enabled', false)) {
                Route::get('firebase-login', [AuthController::class, 'firebaseLoginInfo']);

                Route::post('firebase-login', [AuthController::class, 'firebaseLogin'])
                    ->middleware('firebase.token');
            }
        });

        Route::post('switch-role', [AuthController::class, 'switchRole'])
            ->middleware(['auth:sanctum', 'api.token', 'verified.email']);
    });
