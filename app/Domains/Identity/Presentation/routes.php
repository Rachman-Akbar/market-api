<?php

use App\Domains\Identity\Presentation\Http\Controllers\AuthController;
use App\Http\Middleware\EnsureApiTokenIsValid;
use App\Http\Middleware\ValidateFirebaseToken;
use Illuminate\Support\Facades\Route;

Route::prefix('identity/auth')->group(function () {
    Route::middleware([ValidateFirebaseToken::class])->group(function () {
        Route::post('/firebase-login', [AuthController::class, 'firebaseLogin']);
        Route::post('/firebase-register', [AuthController::class, 'firebaseRegister']);
    });

    Route::middleware(['auth:sanctum', EnsureApiTokenIsValid::class])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/switch-role', [AuthController::class, 'switchRole']);
    });
});