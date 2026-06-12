<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Users\Application\UseCases;

use App\Domains\Identity\Domain\Entities\User;
use App\Domains\Identity\Domain\DTOs\CreateUserDTO;
use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Identity\Domain\Exceptions\EmailAlreadyExistsException;

class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function execute(CreateUserDTO $dto): User
    {
        $existingUser = $this->userRepository->findByEmail($dto->email);

        if ($existingUser) {
            throw new EmailAlreadyExistsException("User with email {$dto->email} already exists.");
        }

        return $this->userRepository->create($dto);
    }
}
