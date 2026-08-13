<?php

declare(strict_types=1);

use App\Domains\Admin\Notification\Presentation\Http\Controllers\AdminNotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'active.role:admin'])
    ->prefix('admin/notifications')
    ->group(function (): void {
        Route::get('/', [AdminNotificationController::class, 'index']);
        Route::get('/state', [AdminNotificationController::class, 'state']);
        Route::patch('/read-all', [AdminNotificationController::class, 'markAllRead']);
        Route::patch('/{id}/read', [AdminNotificationController::class, 'markRead'])->whereNumber('id');
    });
