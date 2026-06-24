<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Order\Addresses\Presentation\Http\Controllers\AddressController;


Route::middleware(['auth:sanctum'])
    ->prefix('addresses')
    ->name('addresses.')
    ->group(function (): void {

    Route::get('/', [AddressController::class, 'index']);
    Route::post('/', [AddressController::class, 'store']);
    Route::put('/{id}', [AddressController::class, 'update']);
    Route::delete('/{id}', [AddressController::class, 'destroy']);
});
