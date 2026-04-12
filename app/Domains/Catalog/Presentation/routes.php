<?php

use App\Domains\Catalog\Presentation\Http\Controllers\CatalogController;
use Illuminate\Support\Facades\Route;

Route::prefix('catalog')
    ->name('catalog.')
    ->group(function (): void {
        Route::get('products', [CatalogController::class, 'index']);
        Route::get('products/{id}', [CatalogController::class, 'show']);

        Route::middleware(['auth:sanctum', 'api.token', 'verified.email', 'role:seller'])->group(function (): void {
            Route::post('products', [CatalogController::class, 'store']);
            Route::put('products/{id}', [CatalogController::class, 'update']);
        });
    });
