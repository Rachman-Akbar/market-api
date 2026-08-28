<?php

declare(strict_types=1);

namespace App\Domains\Admin;

use App\Domains\Admin\Notification\Domain\Repositories\AdminNotificationRepositoryInterface;
use App\Domains\Admin\Notification\Infrastructure\Persistence\Repositories\EloquentAdminNotificationRepository;
use App\Domains\Admin\StoreContext\Application\Services\AdminStoreContextService;
use App\Domains\Identity\User\Domain\Entities\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

final class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminNotificationRepositoryInterface::class, EloquentAdminNotificationRepository::class);
        $this->app->singleton(AdminStoreContextService::class);
    }

    public function boot(): void
    {
        Broadcast::channel('admin.{userId}.notifications', function (User $user, string $userId): bool {
            if ((string) $user->id !== $userId) {
                return false;
            }

            $activeRole = collect($user->currentAccessToken()?->abilities ?? [])
                ->first(fn (mixed $ability): bool => is_string($ability) && str_starts_with($ability, 'active-role:'));

            return $activeRole === 'active-role:admin' && ($user->hasRole('admin') || $user->hasRole('super_admin'));
        });
    }
}
