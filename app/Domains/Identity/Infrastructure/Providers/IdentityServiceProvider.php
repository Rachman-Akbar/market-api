<?php

declare(strict_types=1);

namespace App\Domains\Identity\Infrastructure\Providers;

use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Identity\Infrastructure\Persistence\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind Interface ke Implementation secara global untuk Bounded Context Identity
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
    }

    public function boot(): void
    {
        // Daftarkan routing atau config khusus Identity jika ada di sini
    }
}
