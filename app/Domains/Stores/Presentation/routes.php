<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Stores\Presentation\Http\Controllers\StoreController;
use App\Domains\Stores\Presentation\Http\Controllers\SellerOnboardingController;
use App\Http\Middleware\EnsureApiTokenIsValid;

$storeRoutes = function (): void {
    Route::get('/', [StoreController::class, 'index'])
        ->name('index');

    Route::get('/{slug}', [StoreController::class, 'showBySlug'])
        ->where('slug', '[A-Za-z0-9\-]+')
        ->name('show-by-slug');

    Route::get('/{slug}/products', [StoreController::class, 'productsBySlug'])
        ->where('slug', '[A-Za-z0-9\-]+')
        ->name('products-by-slug');
};

Route::prefix('stores')
    ->name('stores.')
    ->group($storeRoutes);

/**
 * Temporary backward-compatible routes.
 * Hapus ini nanti kalau frontend sudah diganti dari /catalog/stores ke /stores.
 */
Route::prefix('catalog/stores')
    ->name('catalog.stores.')
    ->group($storeRoutes);

Route::middleware(['auth:sanctum', EnsureApiTokenIsValid::class])->group(function () {
    Route::post('/seller/onboarding', [SellerOnboardingController::class, 'store']);
});
