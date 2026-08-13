<?php

declare(strict_types=1);

namespace App\Domains\Engagement;

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
    }

    public function boot(): void
    {
        Gate::policy(MissionModel::class, MissionPolicy::class);
    }
}
