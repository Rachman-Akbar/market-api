<?php

declare(strict_types=1);

use App\Domains\Seller\Customers\Presentation\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'role:seller,admin', 'permission:orders.view'])
    ->prefix('customers')
    ->group(function (): void {
        Route::get('/', [CustomerController::class, 'index']);
    });
