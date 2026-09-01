<?php

declare(strict_types=1);

use App\Domains\PPOB\Presentation\Http\Controllers\PpoAdminDashboardController;
use App\Domains\PPOB\Presentation\Http\Controllers\PpoAdminOperatorController;
use App\Domains\PPOB\Presentation\Http\Controllers\PpoAdminPricingRuleController;
use App\Domains\PPOB\Presentation\Http\Controllers\PpoAdminProductController;
use App\Domains\PPOB\Presentation\Http\Controllers\PpoBillController;
use App\Domains\PPOB\Presentation\Http\Controllers\PpoCallbackController;
use App\Domains\PPOB\Presentation\Http\Controllers\PpoCatalogController;
use App\Domains\PPOB\Presentation\Http\Controllers\PpoTransactionController;
use App\Domains\PPOB\Presentation\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

// IAK provider callback — public (no auth) so the provider can deliver status.
Route::post('ppob/callback', [PpoCallbackController::class, 'handle'])
    ->name('ppob.callback');

Route::middleware(['auth:sanctum', 'active.user'])->prefix('ppob')->group(function (): void {
    // Catalog (available to any authenticated user)
    Route::get('categories', [PpoCatalogController::class, 'categories']);
    Route::get('operators', [PpoCatalogController::class, 'operators']);
    Route::get('products', [PpoCatalogController::class, 'products']);

    // Buyer transactions
    Route::prefix('transactions')->group(function (): void {
        Route::post('/', [PpoTransactionController::class, 'store']);
        Route::get('/', [PpoTransactionController::class, 'index']);
        Route::get('/{id}', [PpoTransactionController::class, 'show'])->whereNumber('id');
        Route::post('/{id}/check-status', [PpoTransactionController::class, 'checkStatus'])->whereNumber('id');
    });

    // Invoices (scoped to the current user)
    Route::prefix('invoices')->group(function (): void {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::get('/{referenceOrId}', [InvoiceController::class, 'show']);
    });

    // Postpaid bills (verified email required for payment actions)
    Route::middleware('verified.email')->prefix('bills')->group(function (): void {
        Route::post('inquiry', [PpoBillController::class, 'inquiry']);
        Route::post('pay', [PpoBillController::class, 'pay']);
    });
});

// Admin PPOB management + dashboard
Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'active.role:admin', 'throttle:120,1'])
    ->prefix('ppob/admin')
    ->group(function (): void {
        Route::get('dashboard', [PpoAdminDashboardController::class, 'dashboard']);
        Route::get('finance-summary', [PpoAdminDashboardController::class, 'financeSummary']);

        Route::prefix('products')->group(function (): void {
            Route::get('/', [PpoAdminProductController::class, 'index']);
            Route::post('/', [PpoAdminProductController::class, 'store']);
            Route::put('/{id}', [PpoAdminProductController::class, 'update'])->whereNumber('id');
            Route::delete('/{id}', [PpoAdminProductController::class, 'destroy'])->whereNumber('id');
        });

        Route::prefix('operators')->group(function (): void {
            Route::get('/', [PpoAdminOperatorController::class, 'index']);
            Route::post('/', [PpoAdminOperatorController::class, 'store']);
            Route::put('/{id}', [PpoAdminOperatorController::class, 'update'])->whereNumber('id');
            Route::delete('/{id}', [PpoAdminOperatorController::class, 'destroy'])->whereNumber('id');
        });

        Route::prefix('pricing-rules')->group(function (): void {
            Route::get('/', [PpoAdminPricingRuleController::class, 'index']);
            Route::post('/', [PpoAdminPricingRuleController::class, 'store']);
            Route::put('/{id}', [PpoAdminPricingRuleController::class, 'update'])->whereNumber('id');
            Route::delete('/{id}', [PpoAdminPricingRuleController::class, 'destroy'])->whereNumber('id');
        });

        Route::get('balance', [PpoAdminDashboardController::class, 'balance']);
    });
