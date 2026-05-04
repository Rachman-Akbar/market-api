<?php

declare(strict_types=1);

use App\Domains\Ordering\Presentation\Http\Controllers\CheckoutController;
use App\Domains\Ordering\Presentation\Http\Controllers\OrderController;
use App\Http\Middleware\EnsureApiTokenIsValid;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', EnsureApiTokenIsValid::class])->group(function (): void {
    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->name('checkout.store');

    Route::prefix('orders')
        ->name('orders.')
        ->group(function (): void {
            Route::get('/', [OrderController::class, 'index'])
                ->name('index');

            Route::get('/{order}', [OrderController::class, 'show'])
                ->name('show');

            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])
                ->name('cancel');

            Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])
                ->name('update-status');
        });
});