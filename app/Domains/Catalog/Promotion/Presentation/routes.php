<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Catalog\Promotion\Presentation\Http\Controllers\PromotionController;

Route::prefix('promotions')->name('promotions.')->group(function () {
    // --- Public Route ---
    // Siapa saja (termasuk pembeli/publik) bisa melihat daftar promosi yang aktif
    Route::get('/', [PromotionController::class, 'index'])->name('index');

    // --- Protected Routes (Hanya Admin dan Seller yang bisa CRUD) ---
    Route::middleware(['auth:sanctum', 'verified.email', 'active.role:admin,seller'])->group(function () {
        Route::post('/', [PromotionController::class, 'store'])->name('store');
        Route::put('/{id}', [PromotionController::class, 'update'])->name('update');
        Route::delete('/{id}', [PromotionController::class, 'destroy'])->name('destroy');
    });
});