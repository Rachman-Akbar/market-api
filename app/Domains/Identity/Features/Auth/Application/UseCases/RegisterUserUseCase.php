<?php

declare(strict_types=1);

namespace App\Domains\Identity\Features\Auth\Application\UseCases;

use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface; // Inject INTERFACE, bukan Eloquent
use App\Domains\Identity\Features\Users\Application\DTOs\CreateUserDTO;
use Illuminate\Support\Facades\DB;

final readonly class RegisterUserUseCase
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private BuildAuthPayloadUseCase $payload,
        private IssueApiTokenUseCase $issueToken,
    ) {}

    public function execute(string $name, string $email, string $password, ?string $deviceName): array
    {
        // 1. Bungkus dalam DB Transaction agar aman
       $user = DB::transaction(function () use ($name, $email, $password) {

            // 2. Gunakan DTO untuk passing data dengan menyertakan roleIds
            $dto = new CreateUserDTO(
                name: $name,
                email: $email,
                password: $password,
                roleIds: [], // Tambahkan ini agar tidak memicu error argument #4
                isEmailVerified: false
            );

            // 3. Buat user via Repository Interface
            $user = $this->userRepository->create($dto);

            // 4. Assign role 'buyer' sebagai role default pendaftaran
            $this->userRepository->assignRoleByName($user, 'buyer');

            return $user;
        });

        // 5. Terbitkan Token Sanctum
        $token = $this->issueToken->execute($user, $deviceName, 'buyer');

        // 6. Kembalikan Response Payload
        return [
            ...$this->payload->execute($user),
            'token_type'   => 'Bearer',
            'access_token' => $token,
            'api_token'    => $token,
        ];
    }
}
