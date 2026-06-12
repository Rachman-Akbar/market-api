<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Users\Application\UseCases;

use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Identity\Domain\Exceptions\UserNotFoundException;

class DeleteUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function execute(string $id): void
    {
        $deleted = $this->userRepository->delete($id);

        if (!$deleted) {
            throw new UserNotFoundException("User with ID {$id} not found.");
        }
    }
}
