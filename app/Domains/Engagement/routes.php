<?php

declare(strict_types=1);

use App\Domains\Engagement\Mission\Presentation\Http\Controllers\MissionController;
use App\Domains\Engagement\Gaming\Presentation\Http\Controllers\GameController;
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

// Secure game session reporting (Arithmetic Kilat & Sudoku)
Route::middleware(['auth:sanctum', 'active.user', 'verified.email', 'throttle:60,1'])
    ->prefix('engagement/games')
    ->group(function (): void {
        Route::post('report', [GameController::class, 'report']);
        Route::get('{gameType}/history', [GameController::class, 'history'])->whereIn('gameType', ['arithmetic_kilat', 'sudoku']);
        Route::get('{gameType}/stats', [GameController::class, 'stats'])->whereIn('gameType', ['arithmetic_kilat', 'sudoku']);
        Route::get('{gameType}/leaderboard', [GameController::class, 'leaderboard'])->whereIn('gameType', ['arithmetic_kilat', 'sudoku']);
    });
