<?php

use App\Domains\Identity\Presentation\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('identity')
    ->name('identity.')
    ->group(function (): void {
        Route::prefix('auth')->group(function (): void {
            Route::get('firebase-login', [AuthController::class, 'firebaseLoginInfo']);

            Route::post('firebase-login', [AuthController::class, 'firebaseLogin'])
                ->middleware('firebase.token');
        });

        Route::post('switch-role', [AuthController::class, 'switchRole'])
            ->middleware(['auth:sanctum', 'api.token', 'verified.email']);
    });
