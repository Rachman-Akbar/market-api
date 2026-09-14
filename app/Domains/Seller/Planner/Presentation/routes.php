<?php

use App\Domains\Seller\Planner\Presentation\Http\Controllers\ScheduleController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'role:seller,admin', 'throttle:120,1'])
    ->prefix('planner')
    ->group(function (): void {
        Route::get('/grid', [ScheduleController::class, 'grid']);
        Route::get('/board', [ScheduleController::class, 'board']);
        Route::get('/export', [ScheduleController::class, 'export']);
        Route::patch('/{id}/complete', [ScheduleController::class, 'complete'])->whereNumber('id');
        Route::patch('/{id}/move', [ScheduleController::class, 'move'])->whereNumber('id');
        Route::get('/', [ScheduleController::class, 'index']);
        Route::post('/', [ScheduleController::class, 'store']);
        Route::get('/{id}', [ScheduleController::class, 'show'])->whereNumber('id');
        Route::put('/{id}', [ScheduleController::class, 'update'])->whereNumber('id');
        Route::delete('/{id}', [ScheduleController::class, 'destroy'])->whereNumber('id');
    });
