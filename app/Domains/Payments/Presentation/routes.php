<?php

use App\Domains\Payments\Presentation\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::prefix('payments')
    ->name('payments.')
    ->group(function (): void {
        Route::middleware(['auth:sanctum', 'api.token', 'verified.email'])->group(function (): void {
            Route::post('update-status', [PaymentController::class, 'updateStatus']);
        });
    });
