<?php

declare(strict_types=1);

use App\Domains\Identity\Presentation\Http\Controllers\AuthController;
use App\Http\Middleware\ValidateFirebaseToken;
use Illuminate\Support\Facades\Route;

Route::prefix('identity/auth')
    ->name('identity.auth.')
    ->group(function (): void {
        Route::post('/password-register', [AuthController::class, 'passwordRegister'])
            ->name('password-register');

        Route::post('/password-login', [AuthController::class, 'passwordLogin'])
            ->name('password-login');

        Route::post('/firebase-login', [AuthController::class, 'firebaseLogin'])
        ->middleware(ValidateFirebaseToken::class);

        Route::middleware(['auth:sanctum'])->group(function (): void {
            Route::get('/me', [AuthController::class, 'me'])
                ->name('me');

            Route::post('/logout', [AuthController::class, 'logoutCurrentDevice'])
                ->name('logout');

            Route::post('/logout-other-devices', [AuthController::class, 'logoutOtherDevices'])
                ->name('logout-other-devices');

            Route::post('/logout-all-devices', [AuthController::class, 'logoutAllDevices'])
                ->name('logout-all-devices');

            Route::delete('/account', [AuthController::class, 'deleteCurrentAccount'])
                ->name('account.delete');
        });
    });


    