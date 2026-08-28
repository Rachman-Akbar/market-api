<?php

declare(strict_types=1);

use App\Domains\Admin\Dashboard\Presentation\Http\Controllers\AdminDashboardController;
use App\Domains\Admin\Notification\Presentation\Http\Controllers\AdminNotificationController;
use App\Domains\Admin\StoreContext\Presentation\Http\Controllers\AdminStoreContextController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'active.role:admin', 'throttle:120,1'])
    ->prefix('admin')
    ->group(function (): void {
        // Dashboard
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'stats']);
        Route::get('/dashboard/order-trend', [AdminDashboardController::class, 'orderTrend']);
        Route::get('/dashboard/top-stores', [AdminDashboardController::class, 'topStores']);

        // Store Context (single-store admin monitoring)
        Route::prefix('stores/context')->group(function (): void {
            Route::get('/', [AdminStoreContextController::class, 'stores']);
            Route::get('/{storeId}', [AdminStoreContextController::class, 'store'])->whereNumber('storeId');
            Route::get('/{storeId}/stats', [AdminStoreContextController::class, 'stats'])->whereNumber('storeId');
            Route::get('/{storeId}/order-trend', [AdminStoreContextController::class, 'orderTrend'])->whereNumber('storeId');
            Route::get('/{storeId}/orders', [AdminStoreContextController::class, 'orders'])->whereNumber('storeId');
            Route::get('/{storeId}/products', [AdminStoreContextController::class, 'products'])->whereNumber('storeId');
            Route::get('/{storeId}/settlements', [AdminStoreContextController::class, 'settlements'])->whereNumber('storeId');
        });

        // Notifications
        Route::get('/notifications', [AdminNotificationController::class, 'index']);
        Route::get('/notifications/state', [AdminNotificationController::class, 'state']);
        Route::patch('/notifications/read-all', [AdminNotificationController::class, 'markAllRead']);
        Route::patch('/notifications/{id}/read', [AdminNotificationController::class, 'markRead'])->whereNumber('id');
    });
