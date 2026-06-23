<?php

declare(strict_types=1);

use App\Domains\Identity\Features\Users\Presentation\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')
    ->name('users.')
    ->group(function (): void {

        // --- Protected Routes (Harus Login & Email Verified) ---
        Route::middleware(['auth:sanctum', 'verified.email'])->group(function (): void {

            // HANYA ADMIN (Menggunakan alias active.role)
            Route::middleware(['active.role:admin'])->group(function (): void {
                Route::get('/', [UserController::class, 'index'])->name('index');
                Route::post('/', [UserController::class, 'store'])->name('store');
                Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
            });

            // BISA DIAKSES USER BIASA ATAU ADMIN
            Route::get('/{id}', [UserController::class, 'show'])->name('show');
            Route::put('/{id}', [UserController::class, 'update'])->name('update');
            Route::get('/email/{email}', [UserController::class, 'showByEmail'])->name('show-by-email');
        });
    });