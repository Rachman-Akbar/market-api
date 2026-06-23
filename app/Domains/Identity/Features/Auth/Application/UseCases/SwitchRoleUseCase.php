<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Auth\Application\UseCases;

use App\Domains\Identity\Domain\Entities\User;
use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class SwitchRoleUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository, // Inject Repo di sini
        private IssueApiTokenUseCase $issueToken
    ) {}

    public function execute(User $user, string $targetRole, ?string $deviceName): array
    {
        // 1. Validasi apakah user memiliki role tersebut di database
        if (! $user->roles()->where('name', $targetRole)->exists()) {
            throw new AccessDeniedHttpException("Anda tidak memiliki hak akses sebagai {$targetRole}.");
        }

        // 2. Hapus token lama lewat repository secara aman (Menghindari Undefined Method)
        $this->userRepository->deleteCurrentToken($user);

        // 3. Terbitkan token baru dengan ability sesuai target role
        $newToken = $this->issueToken->execute($user, $deviceName, $targetRole);

        return [
            'message'      => "Berhasil beralih ke akses {$targetRole}.",
            'active_role'  => $targetRole,
            'token_type'   => 'Bearer',
            'access_token' => $newToken,
            'api_token'    => $newToken,
        ];
    }
}