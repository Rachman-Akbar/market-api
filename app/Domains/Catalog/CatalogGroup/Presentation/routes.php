<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Catalog\CatalogGroup\Presentation\Http\Controllers\CatalogGroupController;

Route::prefix('catalog-groups')->name('catalog-groups.')->group(function () {
    // --- Public Routes ---
    Route::get('/', [CatalogGroupController::class, 'index'])->name('index');
    Route::get('slug/{slug}', [CatalogGroupController::class, 'showBySlug'])->name('show-by-slug');
    Route::get('{id}/categories', [CatalogGroupController::class, 'categories'])->whereNumber('id')->name('categories');
    Route::get('{id}', [CatalogGroupController::class, 'show'])->whereNumber('id')->name('show');

    // --- Protected Routes (Admin Only) ---
    Route::middleware(['auth:sanctum', 'verified.email', 'active.role:admin'])->group(function () {
        Route::post('/', [CatalogGroupController::class, 'store'])->name('store');
        Route::put('{id}', [CatalogGroupController::class, 'update'])->whereNumber('id')->name('update');
        Route::delete('{id}', [CatalogGroupController::class, 'destroy'])->whereNumber('id')->name('destroy');
    });
});