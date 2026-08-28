<?php

declare(strict_types=1);

namespace App\Domains\PPOB;

use App\Domains\PPOB\Application\Services\IakProviderService;
use App\Domains\PPOB\Application\Services\PpoCallbackHandler;
use App\Domains\PPOB\Application\Services\PpoCatalogService;
use App\Domains\PPOB\Application\Services\PpoFinanceService;
use App\Domains\PPOB\Application\Services\PricingEngine;
use App\Domains\PPOB\Application\UseCases\PlacePpoOrderUseCase;
use App\Domains\PPOB\Domain\Repositories\PpoFinanceRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoInquiryRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoOperatorRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoPricingRuleRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoProductRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoTransactionLogRepositoryInterface;
use App\Domains\PPOB\Domain\Repositories\PpoTransactionRepositoryInterface;
use App\Domains\PPOB\Infrastructure\Persistence\Repositories\EloquentPpoFinanceRepository;
use App\Domains\PPOB\Infrastructure\Persistence\Repositories\EloquentPpoInquiryRepository;
use App\Domains\PPOB\Infrastructure\Persistence\Repositories\EloquentPpoOperatorRepository;
use App\Domains\PPOB\Infrastructure\Persistence\Repositories\EloquentPpoPricingRuleRepository;
use App\Domains\PPOB\Infrastructure\Persistence\Repositories\EloquentPpoProductRepository;
use App\Domains\PPOB\Infrastructure\Persistence\Repositories\EloquentPpoTransactionLogRepository;
use App\Domains\PPOB\Infrastructure\Persistence\Repositories\EloquentPpoTransactionRepository;
use App\Domains\PPOB\Infrastructure\Providers\IakProviderClient;
use Illuminate\Support\ServiceProvider;

class PPOBServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PpoProductRepositoryInterface::class, EloquentPpoProductRepository::class);
        $this->app->bind(PpoOperatorRepositoryInterface::class, EloquentPpoOperatorRepository::class);
        $this->app->bind(PpoPricingRuleRepositoryInterface::class, EloquentPpoPricingRuleRepository::class);
        $this->app->bind(PpoTransactionRepositoryInterface::class, EloquentPpoTransactionRepository::class);
        $this->app->bind(PpoTransactionLogRepositoryInterface::class, EloquentPpoTransactionLogRepository::class);
        $this->app->bind(PpoInquiryRepositoryInterface::class, EloquentPpoInquiryRepository::class);
        $this->app->bind(PpoFinanceRepositoryInterface::class, EloquentPpoFinanceRepository::class);

        $this->app->bind(IakProviderClient::class, function () {
            return new IakProviderClient(
                baseUrl: (string) config('ppob.provider.base_url'),
                username: (string) config('ppob.provider.username'),
                apiKey: (string) config('ppob.provider.api_key'),
            );
        });

        $this->app->singleton(PricingEngine::class);
        $this->app->singleton(PpoCatalogService::class);
        $this->app->singleton(PpoFinanceService::class);
        $this->app->singleton(IakProviderService::class);
        $this->app->singleton(PpoCallbackHandler::class);
        $this->app->singleton(PlacePpoOrderUseCase::class);
    }

    public function boot(): void
    {
        //
    }
}
