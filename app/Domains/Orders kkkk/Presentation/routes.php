<?php

use App\Domains\Orders\Presentation\Http\Controllers\CartController;
use App\Domains\Orders\Presentation\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('orders')
    ->name('orders.')
    ->group(function (): void {
        Route::middleware(['auth:sanctum', 'api.token', 'verified.email'])->group(function (): void {
            Route::post('checkout', [OrderController::class, 'checkout'])->middleware('role:buyer');
            Route::get('my-orders', [OrderController::class, 'myOrders'])->middleware('role:buyer');

            Route::get('seller', [OrderController::class, 'sellerOrders'])->middleware('role:seller');
            Route::post('{orderId}/decision', [OrderController::class, 'sellerDecision'])->middleware('role:seller');
        });
    });

Route::prefix('cart')
    ->name('cart.')
    ->middleware(['auth:sanctum', 'api.token', 'verified.email', 'role:buyer'])
    ->group(function (): void {
        Route::post('add', [CartController::class, 'add']);
    });
