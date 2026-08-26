<?php

declare(strict_types=1);

namespace App\Domains\Seller\Planner;

use App\Domains\Seller\Planner\Application\Services\ScheduleService;
use App\Domains\Seller\Planner\Domain\Repositories\ScheduleRepositoryInterface;
use App\Domains\Seller\Planner\Infrastructure\Persistence\Repositories\EloquentScheduleRepository;
use Illuminate\Support\ServiceProvider;

class PlannerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ScheduleRepositoryInterface::class, EloquentScheduleRepository::class);
        $this->app->singleton(ScheduleService::class);
    }

    public function boot(): void
    {
        //
    }
}
