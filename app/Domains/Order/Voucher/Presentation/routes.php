<?php

use App\Domains\Order\Voucher\Presentation\Http\Controllers\VoucherController;
use Illuminate\Support\Facades\Route;

// Semua rute di bawah ini wajib login (Sanctum)
Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('vouchers')->name('vouchers.')->group(function (): void {

        // 🛒 Akses Publik (Customer & Seller bisa melihat)
        Route::get('/', [VoucherController::class, 'index'])->name('index');          // List Semua Voucher
        Route::get('/{id}', [VoucherController::class, 'show'])->name('show');       // Detail 1 Voucher

        // 🛍️ Akses Terbatas (Hanya untuk akun dengan role Seller)
        // 🛍️ Akses Terbatas (Hanya untuk akun dengan role Seller & Admin)
        Route::middleware(['role:seller,admin'])->group(function () {
            Route::post('/', [VoucherController::class, 'store'])->name('store');
            Route::put('/{id}', [VoucherController::class, 'update'])->name('update');
            Route::delete('/{id}', [VoucherController::class, 'destroy'])->name('destroy');
        });

    });

});
