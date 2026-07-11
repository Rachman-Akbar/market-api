<?php

use App\Domains\Order\Voucher\Presentation\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

Route::prefix('vouchers')->name('vouchers.')->group(function (): void {
    Route::get('/', [VoucherController::class, 'index'])->name('index');
    Route::get('/{id}', [VoucherController::class, 'show'])->name('show');

    Route::middleware(['auth:sanctum', 'verified.email', 'role:seller,admin'])->group(function (): void {
        Route::post('/', [VoucherController::class, 'store'])->name('store');
        Route::put('/{id}', [VoucherController::class, 'update'])->name('update');
        Route::post('/{id}', [VoucherController::class, 'update'])->name('update.multipart');
        Route::delete('/{id}', [VoucherController::class, 'destroy'])->name('destroy');
    });
});
