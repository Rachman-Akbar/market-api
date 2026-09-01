<?php

declare(strict_types=1);

use App\Domains\Identity\Auth\Infrastructure\Middleware\ValidateFirebaseToken;
use App\Domains\Identity\Auth\Presentation\Http\Controllers\AuthController;
use App\Domains\Seller\Stores\Presentation\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function (): void {
    Route::post('/password-register', [AuthController::class, 'passwordRegister'])
        ->middleware('throttle:10,1')
        ->name('password-register');
    Route::post('/password-login', [AuthController::class, 'passwordLogin'])
        ->middleware('throttle:30,1')
        ->name('password-login');
    Route::post('/firebase-login', [AuthController::class, 'firebaseLogin'])
        ->middleware([ValidateFirebaseToken::class, 'throttle:10,1'])
        ->name('firebase-login');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])
        ->middleware('throttle:5,1')
        ->name('forgot-password');
    Route::post('/send-password-reset-code', [AuthController::class, 'sendPasswordResetCode'])
        ->middleware('throttle:5,1')
        ->name('send-password-reset-code');
    Route::post('/verify-email-code', [AuthController::class, 'verifyEmailCode'])
        ->middleware('throttle:10,1')
        ->name('verify-email-code');

    Route::post('/reset-password', [AuthController::class, 'resetPassword'])
        ->middleware('throttle:5,1')
        ->name('reset-password');
    Route::post('/reset-password-with-code', [AuthController::class, 'resetPasswordWithCode'])
        ->middleware('throttle:5,1')
        ->name('reset-password-with-code');

    Route::middleware('auth:sanctum')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logoutCurrentDevice'])->name('logout');
        Route::post('/logout-other-devices', [AuthController::class, 'logoutOtherDevices'])->name('logout-other-devices');
        Route::post('/logout-all-devices', [AuthController::class, 'logoutAllDevices'])->name('logout-all-devices');

        Route::middleware('active.user')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me'])->name('me');
            Route::delete('/account', [AuthController::class, 'deleteCurrentAccount'])->name('account.delete');
            Route::post('/switch-role', [AuthController::class, 'switchRole'])->name('switch-role');
            Route::post('/register-seller', [StoreController::class, 'registerStore'])
                ->middleware('verified.email')
                ->name('register-seller');

            Route::post('/change-password', [AuthController::class, 'changePassword'])
                ->middleware('throttle:3,1')
                ->name('change-password');
            Route::post('/send-verification-code', [AuthController::class, 'sendVerificationCode'])
                ->middleware('throttle:5,1')
                ->name('send-verification-code');
        });
    });
});
