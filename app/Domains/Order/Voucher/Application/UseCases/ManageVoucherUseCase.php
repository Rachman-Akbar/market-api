<?php

declare(strict_types=1);

namespace App\Domains\Order\Voucher\Application\UseCases;

use App\Domains\Order\Voucher\Application\DTOs\VoucherDTO;
use App\Domains\Order\Voucher\Domain\Entities\Voucher;
use App\Domains\Order\Voucher\Domain\Repositories\VoucherRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class ManageVoucherUseCase
{
    public function __construct(
        private VoucherRepositoryInterface $voucherRepository
    ) {}

    public function listVouchers(array $filters = []): Collection
    {
        return $this->voucherRepository->getAll($filters);
    }

    public function showVoucher(int $id): Voucher
    {
        $voucher = $this->voucherRepository->findById($id);

        if (!$voucher) {
            throw new ModelNotFoundException('Voucher tidak ditemukan.');
        }

        return $voucher;
    }

    public function createVoucher(VoucherDTO $dto): Voucher
    {
        if ($this->voucherRepository->findByCode($dto->code)) {
            throw new Exception("Kode voucher '{$dto->code}' sudah digunakan.");
        }

        $voucher = new Voucher([
            'code' => strtoupper($dto->code),
            'name' => $dto->name,
            'image' => $dto->image,
            'discount_type' => $dto->discount_type,
            'discount_value' => $dto->discount_value,
            'min_spend' => $dto->min_spend,
            'max_discount' => $dto->max_discount,
            'starts_at' => $dto->starts_at,
            'ends_at' => $dto->ends_at,
            'usage_limit' => $dto->usage_limit,
            'used_count' => 0,
            'store_id' => $dto->store_id,
            'is_active' => $dto->is_active,
        ]);

        return $this->voucherRepository->save($voucher);
    }

    public function updateVoucher(int $id, VoucherDTO $dto): Voucher
    {
        $voucher = $this->showVoucher($id);
        $existing = $this->voucherRepository->findByCode($dto->code);

        if ($existing && $existing->id !== $id) {
            throw new Exception("Kode voucher '{$dto->code}' sudah digunakan oleh voucher lain.");
        }

        $voucher->fill([
            'code' => strtoupper($dto->code),
            'name' => $dto->name,
            'image' => $dto->image ?? $voucher->image,
            'discount_type' => $dto->discount_type,
            'discount_value' => $dto->discount_value,
            'min_spend' => $dto->min_spend,
            'max_discount' => $dto->max_discount,
            'starts_at' => $dto->starts_at,
            'ends_at' => $dto->ends_at,
            'usage_limit' => $dto->usage_limit,
            'store_id' => $dto->store_id,
            'is_active' => $dto->is_active,
        ]);

        return $this->voucherRepository->save($voucher);
    }

    public function deleteVoucher(int $id): bool
    {
        return $this->voucherRepository->delete($this->showVoucher($id));
    }
}
