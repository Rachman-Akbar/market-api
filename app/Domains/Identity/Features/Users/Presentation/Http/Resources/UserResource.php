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
            'id'                => $this->resource->id,
            'email'             => $this->resource->email,
            'name'              => $this->resource->name,
            'avatar'            => $this->resource->avatar,
            'is_email_verified' => $this->resource->is_email_verified,
            'roles'             => $this->resource->roles->map(fn(Role $role) => [
                'id'   => $role->id,
                'name' => $role->name,
            ])->toArray(),
        ];
    }
}