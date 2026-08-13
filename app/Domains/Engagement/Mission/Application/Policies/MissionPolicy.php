<?php

declare(strict_types=1);

namespace App\Domains\Engagement\Mission\Application\Policies;

use App\Domains\Engagement\Mission\Infrastructure\Persistence\Models\MissionModel;
use App\Domains\Identity\User\Domain\Entities\User;

final class MissionPolicy
{
    public function before(User $user): ?bool
    {
        return $user->hasRole('super_admin') ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('missions.manage') || $user->hasPermission('missions.participate');
    }

    public function view(User $user, MissionModel $mission): bool
    {
        return $this->viewAny($user) && ($mission->is_active || $user->hasPermission('missions.manage'));
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('missions.manage');
    }

    public function update(User $user): bool
    {
        return $user->hasPermission('missions.manage');
    }

    public function delete(User $user): bool
    {
        return $user->hasPermission('missions.manage');
    }
}
