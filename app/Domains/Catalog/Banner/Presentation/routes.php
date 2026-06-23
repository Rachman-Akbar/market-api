<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Catalog\Banner\Presentation\Http\Controllers\BannerController;

Route::prefix('banners')->name('shop-banners.')->group(function () {
    // --- Public Route ---
    Route::get('/', [BannerController::class, 'index'])->name('index');

    // --- Protected Routes (Seller Only) ---
    Route::middleware(['auth:sanctum', 'verified.email', 'active.role:seller'])->group(function () {
        Route::post('/', [BannerController::class, 'store'])->name('store');
        Route::put('/{id}', [BannerController::class, 'update'])->name('update');
        Route::delete('/{id}', [BannerController::class, 'destroy'])->name('destroy');
    });
});