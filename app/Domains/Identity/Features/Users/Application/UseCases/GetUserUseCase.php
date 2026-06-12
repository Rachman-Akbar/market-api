<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Users\Application\UseCases;

use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Identity\Domain\Entities\User;
use App\Domains\Identity\Domain\Exceptions\UserNotFoundException;

class GetUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function execute(string $id): User
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            throw new UserNotFoundException("User with ID {$id} not found.");
        }

        return $user;
    }
}
