<?php

namespace App\Domains\Order\Addresses\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Domains\Order\Addresses\Application\UseCases\ManageAddressUseCase;
use App\Domains\Order\Addresses\Infrastructure\Persistence\Mappers\AddressMapper;
use App\Domains\Order\Addresses\Presentation\Http\Requests\StoreAddressRequest;
use App\Domains\Order\Addresses\Presentation\Http\Resources\AddressResource;
use App\Domains\Identity\Domain\Repositories\UserRepositoryInterface; // Import repo auth kamu
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AddressController extends Controller
{
    public function __construct(
        private ManageAddressUseCase $useCase,
        private UserRepositoryInterface $userRepository // Inject repository identity kamu
    ) {}

    /**
     * Otomatis mengidentifikasi context owner berdasarkan token device aktif
     */
    private function resolveOwnerByTokenAbility(): array
    {
        $user = Auth::user(); // Dapatkan entity User dari token Sanctum

        if (!$user) {
            throw new AccessDeniedHttpException("User tidak terautentikasi.");
        }

        // Gunakan method dari repository kamu untuk membaca active-role di dalam token
        $activeRole = $this->userRepository->getActiveRoleFromCurrentToken($user);

        // Jika token memiliki context seller
        if ($activeRole === 'seller') {
            // Validasi apakah toko milik user ini ada dan aktif (lewat repo kamu)
            if (!$this->userRepository->hasSellerAccess($user)) {
                throw new AccessDeniedHttpException("Akses ditolak. Toko Anda belum terdaftar atau tidak aktif.");
            }

            // Pastikan relasi store sudah ter-load (sesuai logic di repo kamu)
            $storeId = (string) $user->store->id;

            return [null, $storeId]; // Context: Toko
        }

        // Default / fallback jika token bertindak sebagai buyer
        return [$user->id, null]; // Context: Buyer
    }

    public function store(StoreAddressRequest $request): JsonResponse
    {
        // Deteksi pemilik otomatis via Sanctum Abilities
        [$userId, $storeId] = $this->resolveOwnerByTokenAbility();

        $dto = AddressMapper::fromRequestArray($request->validated(), $userId, $storeId);
        $address = $this->useCase->createAddress($dto);

        return response()->json([
            'status' => 'success',
            'message' => 'Alamat berhasil disimpan sesuai dengan sesi device aktif.',
            'data' => new AddressResource($address)
        ], 201);
    }
}
