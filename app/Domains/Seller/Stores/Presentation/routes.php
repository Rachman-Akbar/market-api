<?php

declare(strict_types=1);

use App\Domains\Seller\Stores\Presentation\Http\Controllers\AdminStoreController;
use App\Domains\Seller\Stores\Presentation\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

Route::prefix('stores')->group(function (): void {
    Route::get('/', [StoreController::class, 'index']);
    Route::get('slug/{slug}', [StoreController::class, 'showBySlug']);
    Route::get('slug/{slug}/products', [StoreController::class, 'productsBySlug']);
    Route::get('{id}', [StoreController::class, 'showById'])->whereNumber('id');
});

Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'active.role:admin'])
    ->prefix('admin/stores')
    ->group(function (): void {
        Route::get('/', [AdminStoreController::class, 'index']);
        Route::put('{id}', [AdminStoreController::class, 'update'])->whereNumber('id');
        Route::patch('{id}/status', [AdminStoreController::class, 'updateStatus'])->whereNumber('id');
    });

Route::get('stores/manage', [StoreController::class, 'manage'])
    ->middleware(['auth:sanctum', 'active.user', 'verified.email', 'active.role:admin']);

Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'active.role:seller'])
    ->group(function (): void {
        Route::get('stores/{id}/manage', [StoreController::class, 'manageShow'])->whereNumber('id');
        Route::put('stores/{id}', [StoreController::class, 'updateStore'])->whereNumber('id');
        Route::post('stores/{id}', [StoreController::class, 'updateStore'])->whereNumber('id');
    });
