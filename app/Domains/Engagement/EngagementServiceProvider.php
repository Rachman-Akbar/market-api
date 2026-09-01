<?php

declare(strict_types=1);

namespace App\Domains\Engagement;

use App\Domains\Engagement\Gaming\Application\Services\GameReportService;
use App\Domains\Engagement\Gaming\Application\Services\GameValidationService;
use App\Domains\Engagement\Gaming\Domain\Repositories\GameSessionRepositoryInterface;
use App\Domains\Engagement\Gaming\Infrastructure\Persistence\Repositories\EloquentGameSessionRepository;
use App\Domains\Engagement\Mission\Application\Policies\MissionPolicy;
use App\Domains\Engagement\Mission\Domain\Repositories\MissionRepositoryInterface;
use App\Domains\Engagement\Mission\Infrastructure\Persistence\Models\MissionModel;
use App\Domains\Engagement\Mission\Infrastructure\Persistence\Repositories\EloquentMissionRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class EngagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MissionRepositoryInterface::class, EloquentMissionRepository::class);
        $this->app->bind(GameSessionRepositoryInterface::class, EloquentGameSessionRepository::class);
        $this->app->singleton(GameValidationService::class);
        $this->app->singleton(GameReportService::class);
    }

    public function boot(): void
    {
        Gate::policy(MissionModel::class, MissionPolicy::class);
    }
}
