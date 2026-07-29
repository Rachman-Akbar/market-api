<?php

declare(strict_types=1);

use App\Domains\Order\Voucher\Presentation\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::prefix('vouchers')->name('vouchers.')->group(function (): void {
    Route::get('/', [VoucherController::class, 'index'])->name('index');
    Route::get('/{id}', [VoucherController::class, 'show'])->whereNumber('id')->name('show');

    Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'active.role:admin'])->group(function (): void {
        Route::get('/manage/list', [VoucherController::class, 'manage'])->name('admin.manage');
        Route::post('/', [VoucherController::class, 'store'])->name('admin.store');
        Route::put('/{id}', [VoucherController::class, 'update'])->whereNumber('id')->name('admin.update');
        Route::post('/{id}', [VoucherController::class, 'update'])->whereNumber('id')->name('admin.update.multipart');
        Route::delete('/{id}', [VoucherController::class, 'destroy'])->whereNumber('id')->name('admin.destroy');
    });

    Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'active.role:seller', 'seller.store.available'])->group(function (): void {
        Route::get('/seller/manage/list', [VoucherController::class, 'manage'])->name('seller.manage');
        Route::post('/seller', [VoucherController::class, 'store'])->name('seller.store');
        Route::put('/seller/{id}', [VoucherController::class, 'update'])->whereNumber('id')->name('seller.update');
        Route::post('/seller/{id}', [VoucherController::class, 'update'])->whereNumber('id')->name('seller.update.multipart');
        Route::delete('/seller/{id}', [VoucherController::class, 'destroy'])->whereNumber('id')->name('seller.destroy');
    });
});
