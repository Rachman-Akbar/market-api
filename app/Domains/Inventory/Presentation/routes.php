<?php

use App\Domains\Inventory\Presentation\Http\Controllers\InventoryController;
use Illuminate\Support\Facades\Route;

Route::prefix('inventory')
    ->name('inventory.')
    ->group(function (): void {
        Route::middleware(['auth:sanctum', 'api.token', 'verified.email', 'role:seller'])->group(function (): void {
            Route::post('update-stock', [InventoryController::class, 'updateStock']);
        });
    });
