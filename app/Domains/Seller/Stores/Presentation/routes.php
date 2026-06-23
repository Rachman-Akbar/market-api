<?php

use App\Domains\Seller\Stores\Presentation\Http\Controllers\StoreController;
use Illuminate\Support\Facades\Route;

// Endpoint Publik
Route::prefix('stores')->group(function () {
    Route::get('/', [StoreController::class, 'index']);
    Route::get('slug/{slug}', [StoreController::class, 'showBySlug']);
    Route::get('slug/{slug}/products', [StoreController::class, 'productsBySlug']);
    Route::get('{id}', [StoreController::class, 'showById'])->where('id', '[0-9]+');
});

// Endpoint Terproteksi
Route::middleware(['auth:sanctum', 'verified.email'])->group(function () {

    // Disinkronkan dengan alias di bootstrap/app.php yaitu 'active.role'
    Route::middleware(['active.role:seller'])->group(function () {
        Route::put('stores/{id}', [StoreController::class, 'updateStore']);
    });

    // Jika ingin membuat endpoint khusus admin besok:
    Route::middleware(['active.role:admin'])->prefix('admin')->group(function () {
        // Route::put('stores/{id}/ban', [StoreController::class, 'banStore']);
    });
});