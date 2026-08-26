<?php

declare(strict_types=1);

use App\Domains\Engagement\Mission\Presentation\Http\Controllers\MissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'permission:missions.participate,missions.manage'])
    ->prefix('engagement/missions')
    ->group(function (): void {
        Route::get('/', [MissionController::class, 'index']);
        Route::get('me', [MissionController::class, 'userMissions']);
        Route::post('/', [MissionController::class, 'store']);
        Route::post('report', [MissionController::class, 'reportEvent']);
        Route::put('{id}', [MissionController::class, 'update'])->whereNumber('id');
        Route::delete('{id}', [MissionController::class, 'destroy'])->whereNumber('id');
    });
