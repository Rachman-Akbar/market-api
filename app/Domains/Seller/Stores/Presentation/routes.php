<?php

use App\Domains\Seller\Stores\Presentation\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('stores')->group(function (): void {
    Route::get('/', [StoreController::class, 'index']);
    Route::get('slug/{slug}', [StoreController::class, 'showBySlug']);
    Route::get('slug/{slug}/products', [StoreController::class, 'productsBySlug']);
    Route::get('{id}', [StoreController::class, 'showById'])->whereNumber('id');
});

Route::middleware(['auth:sanctum', 'verified.email', 'active.role:seller'])->group(function (): void {
    Route::put('stores/{id}', [StoreController::class, 'updateStore']);
    Route::post('stores/{id}', [StoreController::class, 'updateStore']);
});
