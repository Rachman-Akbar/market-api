<?php

namespace App\Domains\Order\Voucher\Application\UseCases;

use App\Domains\Order\Voucher\Application\DTOs\VoucherDTO;
use App\Domains\Order\Voucher\Domain\Entities\Voucher;
use App\Domains\Order\Voucher\Domain\Repositories\VoucherRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class ManageVoucherUseCase
{
    public function __construct(
        private VoucherRepositoryInterface $voucherRepository
    ) {}

    public function listVouchers(): Collection
    {
        return $this->voucherRepository->getAll();
    }

    public function showVoucher(int $id): Voucher
    {
        $voucher = $this->voucherRepository->findById($id);
        if (!$voucher) {
            throw new ModelNotFoundException("Voucher tidak ditemukan.");
        }
        return $voucher;
    }

    public function createVoucher(VoucherDTO $dto): Voucher
    {
        // Cek duplikasi kode voucher secara manual di level aplikasi
        if ($this->voucherRepository->findByCode($dto->code)) {
            throw new Exception("Kode voucher '{$dto->code}' sudah digunakan.");
        }

        $voucher = new Voucher([
            'code'           => strtoupper($dto->code),
            'name'           => $dto->name,
            'discount_type'  => $dto->discount_type,
            'discount_value' => $dto->discount_value,
            'min_spend'      => $dto->min_spend,
            'max_discount'   => $dto->max_discount,
            'starts_at'      => $dto->starts_at,
            'ends_at'        => $dto->ends_at,
            'usage_limit'    => $dto->usage_limit,
            'used_count'     => 0, // Awal buat pasti 0
            'store_id'       => $dto->store_id,
            'is_active'      => $dto->is_active,
        ]);

        return $this->voucherRepository->save($voucher);
    }

    public function updateVoucher(int $id, VoucherDTO $dto): Voucher
    {
        $voucher = $this->voucherRepository->findById($id);
        if (!$voucher) {
            throw new ModelNotFoundException("Voucher tidak ditemukan.");
        }

        // Jika mengubah kode, pastikan kode baru tidak bentrok dengan voucher lain
        $existing = $this->voucherRepository->findByCode($dto->code);
        if ($existing && $existing->id !== $id) {
            throw new Exception("Kode voucher '{$dto->code}' sudah digunakan oleh voucher lain.");
        }

        $voucher->fill([
            'code'           => strtoupper($dto->code),
            'name'           => $dto->name,
            'discount_type'  => $dto->discount_type,
            'discount_value' => $dto->discount_value,
            'min_spend'      => $dto->min_spend,
            'max_discount'   => $dto->max_discount,
            'starts_at'      => $dto->starts_at,
            'ends_at'        => $dto->ends_at,
            'usage_limit'    => $dto->usage_limit,
            'store_id'       => $dto->store_id,
            'is_active'      => $dto->is_active,
        ]);

        return $this->voucherRepository->save($voucher);
    }

    public function deleteVoucher(int $id): bool
    {
        $voucher = $this->voucherRepository->findById($id);
        if (!$voucher) {
            throw new ModelNotFoundException("Voucher tidak ditemukan.");
        }
        return $this->voucherRepository->delete($voucher);
    }
}
