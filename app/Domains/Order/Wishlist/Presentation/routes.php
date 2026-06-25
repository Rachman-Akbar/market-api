<?php

declare(strict_types=1);

use App\Domains\Order\Wishlist\Presentation\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

    Route::middleware(['auth:sanctum'])
        ->prefix('wishlist')
        ->name('wishlist.')
        ->group(function (): void {

        Route::get('/', [WishlistController::class, 'index']);           // Ambil Data
        Route::post('/', [WishlistController::class, 'store']);          // Simpan Data
        Route::delete('/{productId}', [WishlistController::class, 'destroy']); // Hapus Data

    });
