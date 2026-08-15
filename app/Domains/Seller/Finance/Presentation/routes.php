<?php

declare(strict_types=1);

use App\Domains\Seller\Finance\Presentation\Http\Controllers\FinancialTransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'role:seller,admin', 'permission:finance.manage'])
    ->prefix('finance')
    ->group(function (): void {
        Route::get('/', [FinancialTransactionController::class, 'index']);
        Route::post('/', [FinancialTransactionController::class, 'store']);
        Route::get('{id}', [FinancialTransactionController::class, 'show'])->whereNumber('id');
        Route::put('{id}', [FinancialTransactionController::class, 'update'])->whereNumber('id');
        Route::patch('{id}/payments', [FinancialTransactionController::class, 'recordPayment'])->whereNumber('id');
        Route::get('{id}/payments', [FinancialTransactionController::class, 'paymentHistory'])->whereNumber('id');
        Route::delete('{id}', [FinancialTransactionController::class, 'destroy'])->whereNumber('id');
    });
