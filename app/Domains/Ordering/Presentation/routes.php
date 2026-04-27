<?php

declare(strict_types=1);

use App\Domains\Ordering\Presentation\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['firebase.auth'])
    ->prefix('orders')
    ->name('orders.')
    ->group(function (): void {
        Route::get('/', [OrderController::class, 'index'])->name('index');

        Route::post('/', [OrderController::class, 'store'])->name('store');

        Route::get('/{order}', [OrderController::class, 'show'])->name('show');

        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');

        Route::patch('/{order}/status', [OrderController::class, 'updateStatus'])->name('update-status');
    });
