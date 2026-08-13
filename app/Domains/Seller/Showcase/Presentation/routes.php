<?php

declare(strict_types=1);

use App\Domains\Seller\Showcase\Presentation\Http\Controllers\ShowcaseController;
use Illuminate\Support\Facades\Route;

Route::get('stores/{storeId}/showcases', [ShowcaseController::class, 'publicIndex'])->whereNumber('storeId');

Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'role:seller,admin', 'permission:showcases.manage'])
    ->prefix('showcases')
    ->group(function (): void {
        Route::get('/', [ShowcaseController::class, 'index']);
        Route::post('/', [ShowcaseController::class, 'store']);
        Route::get('{id}', [ShowcaseController::class, 'show'])->whereNumber('id');
        Route::put('{id}', [ShowcaseController::class, 'update'])->whereNumber('id');
        Route::delete('{id}', [ShowcaseController::class, 'destroy'])->whereNumber('id');
    });
