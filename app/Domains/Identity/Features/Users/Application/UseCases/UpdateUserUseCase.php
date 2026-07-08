<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Users\Application\UseCases;

use App\Domains\Identity\Domain\Entities\User;
use App\Domains\Identity\Features\Users\Application\DTOs\UpdateUserDTO;
use App\Domains\Identity\Domain\Exceptions\UserNotFoundException;
use App\Domains\Identity\Domain\Exceptions\EmailAlreadyExistsException;
use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;

class UpdateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function execute(string $id, UpdateUserDTO $dto): User
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            throw new UserNotFoundException("User with ID {$id} not found.");
        }

        // Jika email diupdate dan berbeda dengan email lama, cek keunikan
        if ($dto->email !== null && $dto->email !== $user->email) {
            $existingUser = $this->userRepository->findByEmail($dto->email);
            if ($existingUser) {
                throw new EmailAlreadyExistsException("Email {$dto->email} is already taken.");
            }
        }

        /* Catatan Keamanan:
           Jika user (terutama dari Google Auth yang passwordnya masih null)
           mengirimkan $dto->password, maka EloquentUserRepository@update 
           akan otomatis meng-hash-nya dan menyimpannya ke database.
        */

        return $this->userRepository->update($id, $dto);
    }
}