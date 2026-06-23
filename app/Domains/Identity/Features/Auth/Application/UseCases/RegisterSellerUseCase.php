<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Auth\Application\UseCases;

use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface;
use App\Domains\Identity\Features\Auth\Application\DTOs\RegisterSellerDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class RegisterSellerUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private IssueApiTokenUseCase $issueToken,
        private BuildAuthPayloadUseCase $payload
    ) {}

    public function execute(string $userId, array $data, ?string $deviceName): array
    {
        $user = $this->userRepository->findById($userId);
        if (!$user) {
            throw new \RuntimeException("User tidak ditemukan.");
        }

        return DB::transaction(function () use ($user, $data, $deviceName) {
            // 1. Buat DTO untuk Toko
            $dto = new RegisterSellerDTO(
                storeName: trim($data['store_name']),
                slug: Str::slug($data['store_name']) . '-' . Str::random(5),
                phone: $data['phone'] ?? null,
                city: $data['city'] ?? null,
                province: $data['province'] ?? null,
                address: $data['address'] ?? null
            );

            // 2. Buat Toko di Database (Otomatis Aktif)
            $this->userRepository->registerStore($user->id, $dto);

            // 3. Assign Role 'seller' ke user
            $this->userRepository->assignRoleByName($user, 'seller');

            // 4. Terbitkan token baru dengan active-role: seller
            $token = $this->issueToken->execute($user, $deviceName ?? 'marketplace-web', 'seller');

            return [
                ...$this->payload->execute($user->refresh()),
                'token_type'   => 'Bearer',
                'access_token' => $token,
                'api_token'    => $token,
            ];
        });
    }
}