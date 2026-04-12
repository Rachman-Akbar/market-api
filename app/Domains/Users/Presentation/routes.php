<?php

use App\Domains\Users\Presentation\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')
    ->name('users.')
    ->group(function (): void {
        Route::middleware(['auth:sanctum', 'api.token', 'verified.email'])->group(function (): void {
            Route::post('seller-profile', [UserController::class, 'updateSellerProfile'])->middleware('role:seller');

            Route::get('addresses', [UserController::class, 'listAddresses']);
            Route::post('addresses', [UserController::class, 'createAddress']);
        });
    });
