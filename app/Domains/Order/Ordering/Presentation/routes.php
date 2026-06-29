<?php

use App\Domains\Order\Ordering\Presentation\Http\Controllers\OrderingController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])
        ->prefix('orderings')
        ->name('orderings.')
        ->group(function (): void {
    // 1. Checkout / Buat Order Baru
    Route::post('/', [OrderingController::class, 'store']);

    // 2. Rute Filter & Spesifik (Ditempatkan di atas)
    // Menggunakan 'customer' agar cocok dengan fungsi getByCustomer
    Route::get('customers/{userId}', [OrderingController::class, 'getByCustomer']);
    Route::get('stores/{storeId}', [OrderingController::class, 'getByStore']);

    // 3. Rute Dinamis Detail / Action (Ditempatkan paling bawah)
    Route::get('{id}', [OrderingController::class, 'show']);
    Route::post('{id}/cancel', [OrderingController::class, 'cancel']);
    Route::patch('{id}/status', [OrderingController::class, 'updateStatus']);
});
