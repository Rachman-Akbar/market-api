<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Domains\Catalog\Promotion\Presentation\Http\Controllers\PromotionController;

Route::prefix('promotions')
    ->name('promotions.')
    ->group(function () {
        Route::get('/', [PromotionController::class, 'index'])->name('index');
        Route::post('/', [PromotionController::class, 'store'])->name('store');
        Route::put('/{id}', [PromotionController::class, 'update'])->name('update');
        Route::delete('/{id}', [PromotionController::class, 'destroy'])->name('destroy');
    });
