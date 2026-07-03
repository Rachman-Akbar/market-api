<?php

namespace App\Domains\Order\Voucher\Domain\Repositories;

use App\Domains\Order\Voucher\Domain\Entities\Voucher;
use Illuminate\Support\Collection;

interface VoucherRepositoryInterface
{
    public function getAll(): Collection;
    public function findById(int $id): ?Voucher;
    public function findByCode(string $code): ?Voucher;
    public function save(Voucher $voucher): Voucher;
    public function delete(Voucher $voucher): bool;
}
