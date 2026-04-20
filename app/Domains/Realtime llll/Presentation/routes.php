<?php

use App\Domains\Realtime\Presentation\Http\Controllers\RealtimeController;
use Illuminate\Support\Facades\Route;

Route::prefix('realtime')
    ->name('realtime.')
    ->group(function (): void {
        Route::middleware(['auth:sanctum', 'api.token', 'verified.email'])->group(function (): void {
            Route::get('orders/{orderId}', [RealtimeController::class, 'integrationGuide']);
        });
    });
