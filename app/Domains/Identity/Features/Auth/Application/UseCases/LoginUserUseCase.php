<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Auth\Application\UseCases;

use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class LoginUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private BuildAuthPayloadUseCase $payload,
        private IssueApiTokenUseCase $issueToken,
    ) {}

    public function execute(string $email, string $password, ?string $deviceName): array
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !Hash::check($password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (method_exists($user, 'trashed') && $user->trashed()) {
            throw ValidationException::withMessages([
                'email' => ['Akun tidak ditemukan.'],
            ]);
        }

        $token = $this->issueToken->execute($user, $deviceName, 'buyer');

        return [
            ...$this->payload->execute($user),
            'token_type'   => 'Bearer',
            'access_token' => $token,
            'api_token'    => $token,
        ];
    }
}
