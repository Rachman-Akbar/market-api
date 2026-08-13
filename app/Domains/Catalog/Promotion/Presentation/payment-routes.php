<?php

declare(strict_types=1);

use App\Domains\Catalog\Promotion\Presentation\Http\Controllers\PromotionPaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'active.user', 'verified.email'])->prefix('promotion-payments')->name('promotion-payments.')->group(function (): void {
    Route::get('/', [PromotionPaymentController::class, 'index'])->middleware(['role:admin,seller', 'permission:promotion_payments.submit,promotion_payments.review'])->name('index');

    Route::middleware(['active.role:seller', 'seller.store.available', 'permission:promotion_payments.submit'])->group(function (): void {
        Route::post('/', [PromotionPaymentController::class, 'store'])->name('store');
    });

    Route::middleware(['active.role:admin', 'permission:promotion_payments.review'])->group(function (): void {
        Route::patch('/{id}/approve', [PromotionPaymentController::class, 'approve'])->whereNumber('id')->name('approve');
        Route::patch('/{id}/reject', [PromotionPaymentController::class, 'reject'])->whereNumber('id')->name('reject');
    });
});
