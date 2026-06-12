<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Users\Presentation\Http\Resources;

use App\Domains\Identity\Domain\Entities\User;
use App\Domains\Identity\Domain\Entities\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read User $resource
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getId(),
            'email' => $this->resource->getEmail(),
            'name' => $this->resource->getName(),
            'avatar' => $this->resource->getAvatar(),
            'is_email_verified' => $this->resource->isEmailVerified(),
            'roles' => $this->resource->getRoles()->map(fn(Role $role) => [
                'id' => $role->getId(),
                'name' => $role->getName(),
            ])->toArray(),
        ];
    }
}
