<?php

namespace App\Domains\Order\Addresses\Application\UseCases;

use App\Domains\Order\Addresses\Application\DTOs\AddressDTO;
use App\Domains\Order\Addresses\Infrastructure\Persistence\Mappers\AddressMapper;
use App\Domains\Order\Addresses\Domain\Entities\Address;
use App\Domains\Order\Addresses\Domain\Repositories\AddressRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ManageAddressUseCase
{
    public function __construct(
        private AddressRepositoryInterface $addressRepository
    ) {}

    public function listAddresses(?string $userId, ?string $storeId): Collection
    {
        return $this->addressRepository->getByOwner($userId, $storeId);
    }

    public function createAddress(AddressDTO $dto): Address
    {
        return DB::transaction(function () use ($dto) {
            $isFirstAddress = !$this->addressRepository->hasAddresses($dto->user_id, $dto->store_id);

            $address = new Address(AddressMapper::toEntityArray($dto));
            $this->addressRepository->save($address);

            if ($dto->is_primary || $isFirstAddress) {
                $address->markAsPrimary();
            }

            return $address;
        });
    }

    public function updateAddress(int $id, AddressDTO $dto): Address
    {
        return DB::transaction(function () use ($id, $dto) {
            $address = $this->addressRepository->findByIdAndOwner($id, $dto->user_id, $dto->store_id);

            if (!$address) {
                throw new ModelNotFoundException("Alamat tidak ditemukan atau Anda tidak memiliki akses.");
            }

            $address->fill(AddressMapper::toEntityArray($dto));
            $this->addressRepository->save($address);

            if ($dto->is_primary) {
                $address->markAsPrimary();
            }

            return $address;
        });
    }

    public function deleteAddress(int $id, ?string $userId, ?string $storeId): bool
    {
        $address = $this->addressRepository->findByIdAndOwner($id, $userId, $storeId);

        if (!$address) {
            throw new ModelNotFoundException("Alamat tidak ditemukan atau Anda tidak memiliki akses.");
        }

        return $this->addressRepository->delete($address);
    }
}
