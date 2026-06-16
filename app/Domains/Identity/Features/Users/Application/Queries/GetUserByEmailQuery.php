<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Users\Application\Queries;

use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Identity\Domain\Entities\User;
use App\Domains\Identity\Domain\Exceptions\UserNotFoundException;

class GetUserByEmailQuery
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function execute(string $email): User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new UserNotFoundException("User with email {$email} not found.");
        }

        return $user;
    }
}
