<?php

declare(strict_types=1);

namespace App\Domains\Seller\Showcase\Application\Policies;

use App\Domains\Identity\User\Domain\Entities\User;
use App\Domains\Seller\Showcase\Infrastructure\Persistence\Models\ShowcaseModel;

final class ShowcasePolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('showcases.manage');
    }

    public function view(User $user, ShowcaseModel $showcase): bool
    {
        return $this->ownsStore($user, (int) $showcase->store_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('showcases.manage');
    }

    public function update(User $user, ShowcaseModel $showcase): bool
    {
        return $this->ownsStore($user, (int) $showcase->store_id);
    }

    public function delete(User $user, ShowcaseModel $showcase): bool
    {
        return $this->ownsStore($user, (int) $showcase->store_id);
    }

    private function ownsStore(User $user, int $storeId): bool
    {
        return $user->hasRole('admin') || (
            $user->hasPermission('showcases.manage')
            && (int) $user->store?->id === $storeId
        );
    }
}
