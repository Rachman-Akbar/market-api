<?php

declare(strict_types=1);

use App\Domains\Cart\Presentation\Http\Controllers\CartController;
use App\Http\Middleware\EnsureApiTokenIsValid;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', EnsureApiTokenIsValid::class])
    ->prefix('carts')
    ->name('carts.')
    ->group(function (): void {
        Route::get('/', [CartController::class, 'show'])
            ->name('show');

        Route::post('/items', [CartController::class, 'store'])
            ->name('items.store');

        Route::patch('/items/{productId}', [CartController::class, 'update'])
            ->name('items.update');

        Route::delete('/items/{productId}', [CartController::class, 'destroy'])
            ->name('items.destroy');

        Route::delete('/', [CartController::class, 'clear'])
            ->name('clear');
    });