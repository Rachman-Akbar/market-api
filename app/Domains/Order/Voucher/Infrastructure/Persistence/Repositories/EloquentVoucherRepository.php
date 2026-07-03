<?php

namespace App\Domains\Order\Voucher\Infrastructure\Persistence\Repositories;

use App\Domains\Order\Voucher\Domain\Entities\Voucher;
use App\Domains\Order\Voucher\Domain\Repositories\VoucherRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentVoucherRepository implements VoucherRepositoryInterface
{
    public function getAll(): Collection
    {
        return Voucher::orderBy('created_at', 'desc')->get();
    }

    public function findById(int $id): ?Voucher
    {
        return Voucher::find($id);
    }

    public function findByCode(string $code): ?Voucher
    {
        return Voucher::where('code', strtoupper($code))->first();
    }

    public function save(Voucher $voucher): Voucher
    {
        $voucher->save();
        return $voucher;
    }

    public function delete(Voucher $voucher): bool
    {
        return (bool) $voucher->delete();
    }
}
