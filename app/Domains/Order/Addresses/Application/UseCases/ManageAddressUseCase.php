<?php

declare(strict_types=1);

namespace App\Domains\Order\Addresses\Application\UseCases;

use App\Domains\Order\Addresses\Application\DTOs\AddressDTO;
use App\Domains\Order\Addresses\Domain\Entities\Address;
use App\Domains\Order\Addresses\Domain\Repositories\AddressRepositoryInterface;
use App\Domains\Order\Addresses\Infrastructure\Persistence\Mappers\AddressMapper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

final class ManageAddressUseCase
{
    public function __construct(
        private AddressRepositoryInterface $addressRepository,
        private ResolveAddressDestinationUseCase $resolveDestinationUseCase
    ) {}

    public function listAddresses(?string $userId, ?string $storeId): Collection
    {
        return $this->addressRepository->getByOwner($userId, $storeId);
    }

    public function createAddress(AddressDTO $dto): Address
    {
        $this->resolveDestination($dto);

        return DB::transaction(function () use ($dto): Address {
            if ($dto->store_id !== null && $this->addressRepository->hasAddresses(null, $dto->store_id)) {
                throw ValidationException::withMessages([
                    'store_id' => ['Satu toko hanya boleh memiliki satu alamat. Gunakan endpoint update untuk mengubah alamat toko.'],
                ]);
            }

            $isFirstAddress = !$this->addressRepository->hasAddresses($dto->user_id, $dto->store_id);
            $address = new Address(AddressMapper::toEntityArray($dto));

            if ($dto->store_id !== null) {
                $address->is_primary = true;
            }

            $this->addressRepository->save($address);

            if ($dto->is_primary || $isFirstAddress || $dto->store_id !== null) {
                $address->markAsPrimary();
            }

            return $address->fresh();
        });
    }

    public function updateAddress(int $id, AddressDTO $dto): Address
    {
        $this->resolveDestination($dto);

        return DB::transaction(function () use ($id, $dto): Address {
            $address = $this->addressRepository->findByIdAndOwner($id, $dto->user_id, $dto->store_id);

            if (!$address) {
                throw new ModelNotFoundException('Alamat tidak ditemukan atau Anda tidak memiliki akses.');
            }

            $wasPrimary = (bool) $address->is_primary;
            $address->fill(AddressMapper::toEntityArray($dto));

            if ($dto->store_id !== null) {
                $address->is_primary = true;
            }

            $this->addressRepository->save($address);

            if ($dto->is_primary || $dto->store_id !== null) {
                $address->markAsPrimary();
            } elseif ($wasPrimary && $dto->user_id !== null) {
                $replacement = $this->addressRepository
                    ->getByOwner($dto->user_id, null)
                    ->first(fn (Address $item): bool => $item->getKey() !== $address->getKey());

                if ($replacement) {
                    $replacement->markAsPrimary();
                } else {
                    $address->markAsPrimary();
                }
            }

            return $address->fresh();
        });
    }

    public function deleteAddress(int $id, ?string $userId, ?string $storeId): bool
    {
        return DB::transaction(function () use ($id, $userId, $storeId): bool {
            $address = $this->addressRepository->findByIdAndOwner($id, $userId, $storeId);

            if (!$address) {
                throw new ModelNotFoundException('Alamat tidak ditemukan atau Anda tidak memiliki akses.');
            }

            $wasPrimary = (bool) $address->is_primary;
            $deleted = $this->addressRepository->delete($address);

            if ($deleted && $wasPrimary && $userId !== null) {
                $replacement = $this->addressRepository->getByOwner($userId, null)->first();
                $replacement?->markAsPrimary();
            }

            return $deleted;
        });
    }

    private function resolveDestination(AddressDTO $dto): void
    {
        try {
            $resolved = $this->resolveDestinationUseCase->execute($dto->destinationLookupData());
            $dto->komerce_destination_id = $resolved->id;
        } catch (Throwable $exception) {
            throw ValidationException::withMessages([
                'address_destination' => [$exception->getMessage()],
            ]);
        }
    }
}
