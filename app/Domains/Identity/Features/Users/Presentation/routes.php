<?php

declare(strict_types=1);

use App\Domains\Identity\Features\Users\Presentation\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('users')
    ->name('users.')
    ->group(function (): void {

        // --- Protected Routes (Harus Login) ---
        Route::middleware(['auth:sanctum', 'ensure.verified'])->group(function (): void {

            // HANYA ADMIN yang boleh melihat list semua user, membuat user via admin, atau menghapus user
            Route::middleware(['ensure.active.role:admin'])->group(function (): void {
                Route::get('/', [UserController::class, 'index'])->name('index');
                Route::post('/', [UserController::class, 'store'])->name('store');
                Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
            });

            // BISA DIAKSES USER BIASA (Atau Admin)
            // Catatan: Di dalam Controller show, update, dan showByEmail,
            // pastikan ada logic untuk ngecek: id yang dicari == id user yang sedang login (kecuali dia admin)
            Route::get('/{id}', [UserController::class, 'show'])->name('show');
            Route::put('/{id}', [UserController::class, 'update'])->name('update');
            Route::get('/email/{email}', [UserController::class, 'showByEmail'])->name('show-by-email');
        });
    });
