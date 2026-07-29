<?php

declare(strict_types=1);

use App\Domains\Catalog\Promotion\Presentation\Http\Controllers\PromotionController;
use Illuminate\Support\Facades\Route;

Route::prefix('promotions')->name('promotions.')->group(function (): void {
    Route::get('/', [PromotionController::class, 'index'])->name('index');

    Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'active.role:admin'])->group(function (): void {
        Route::get('/manage', [PromotionController::class, 'manage'])->name('manage');
        Route::post('/', [PromotionController::class, 'store'])->name('store');
        Route::put('/{id}', [PromotionController::class, 'update'])->whereNumber('id')->name('update');
        Route::patch('/{id}/approve', [PromotionController::class, 'approve'])->whereNumber('id')->name('approve');
        Route::patch('/{id}/reject', [PromotionController::class, 'reject'])->whereNumber('id')->name('reject');
        Route::delete('/{id}', [PromotionController::class, 'destroy'])->whereNumber('id')->name('destroy');
    });
});
