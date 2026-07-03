<?php

use App\Domains\Order\Voucher\Presentation\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

// Jika ingin dilindungi oleh auth token (Sanctum)
// Route::middleware('auth:sanctum')->group(function () {

    // Grouping route untuk voucher
    Route::prefix('vouchers')->name('vouchers.')->group(function () {
        Route::get('/', [VoucherController::class, 'index'])->name('index');          // List Semua Voucher
        Route::get('/{id}', [VoucherController::class, 'show'])->name('show');       // Detail 1 Voucher
        Route::post('/', [VoucherController::class, 'store'])->name('store');         // Create Voucher
        Route::put('/{id}', [VoucherController::class, 'update'])->name('update');    // Update Voucher
        Route::delete('/{id}', [VoucherController::class, 'destroy'])->name('destroy');// Delete Voucher
    });

// });
