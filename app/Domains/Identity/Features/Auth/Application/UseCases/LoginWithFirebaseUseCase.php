<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Auth\Application\UseCases;

use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use InvalidArgumentException;

final class LoginWithFirebaseUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly BuildAuthPayloadUseCase $payload,
        private readonly IssueApiTokenUseCase $issueToken,
    ) {}

    public function execute(array $firebaseUser, ?string $deviceName = null): array
    {
        $firebaseUid = $firebaseUser['uid'] ?? $firebaseUser['sub'] ?? null;
        $email = $firebaseUser['email'] ?? null;

        // Validasi input awal di level aplikasi
        if (!is_string($firebaseUid) || trim($firebaseUid) === '') {
            throw new InvalidArgumentException('Firebase UID tidak valid atau kosong.');
        }

        if (!is_string($email) || trim($email) === '') {
            throw new InvalidArgumentException('Firebase email tidak valid atau kosong.');
        }

        $deviceName = $deviceName ?? 'marketplace-web';

        // 1. Sinkronisasi Mutlak diserahkan ke Repository (MySQL sebagai Source of Truth)
        $user = $this->userRepository->syncFromFirebase($firebaseUser);

        // 2. Terbitkan token akses menggunakan Use Case utilitas Anda
        $token = $this->issueToken->execute($user, $deviceName, 'buyer');

        // 3. Gabungkan struktur payload akhir untuk dikembalikan ke Controller
        return [
            ...$this->payload->execute($user),
            'token_type'   => 'Bearer',
            'api_token'    => $token,
            'access_token' => $token,
        ];
    }
}
