<?php

declare(strict_types=1);

namespace App\Domains\Seller\Finance\Application\Services;

use App\Domains\Seller\Finance\Domain\Repositories\FinancialTransactionRepositoryInterface;
use App\Domains\Seller\Finance\Infrastructure\Persistence\Models\FinancialTransactionModel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class FinancialTransactionService
{
    public function __construct(private FinancialTransactionRepositoryInterface $repository) {}

    public function paginate(array $filters, int $perPage, ?int $storeId): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage, $storeId);
    }

    public function find(int $id, ?int $storeId): FinancialTransactionModel
    {
        return $this->repository->find($id, $storeId)
            ?? throw new InvalidArgumentException('Transaksi keuangan tidak ditemukan.');
    }

    public function save(array $data, ?int $id, ?int $sellerStoreId): FinancialTransactionModel
    {
        return DB::transaction(function () use ($data, $id, $sellerStoreId): FinancialTransactionModel {
            $model = $id ? $this->find($id, $sellerStoreId) : new FinancialTransactionModel();
            $storeId = $sellerStoreId ?? ($data['store_id'] ?? null);
            $orderId = isset($data['order_id']) ? (int) $data['order_id'] : null;

            if ($orderId !== null && $storeId !== null && ! DB::table('sub_orders')
                ->where('order_id', $orderId)
                ->where('store_id', (int) $storeId)
                ->exists()) {
                throw new InvalidArgumentException('Order tidak terhubung dengan toko transaksi keuangan.');
            }

            $amount = round((float) $data['amount'], 2);
            $paidAmount = min($amount, max(0, round((float) ($data['paid_amount'] ?? 0), 2)));
            $status = $this->resolveStatus((string) $data['type'], $amount, $paidAmount, (string) ($data['status'] ?? 'open'));

            $model->fill([
                'store_id' => $storeId,
                'order_id' => $orderId,
                'user_id' => $data['user_id'] ?? null,
                'reference_number' => $model->reference_number ?: $this->referenceNumber((string) $data['type']),
                'type' => $data['type'],
                'title' => trim((string) $data['title']),
                'description' => $data['description'] ?? null,
                'amount' => $amount,
                'paid_amount' => $paidAmount,
                'status' => $status,
                'due_date' => $data['due_date'] ?? null,
                'occurred_at' => $data['occurred_at'],
                'settled_at' => $status === 'paid' ? now() : null,
                'is_active' => $data['is_active'] ?? true,
                'metadata' => $data['metadata'] ?? null,
            ]);

            return $this->repository->save($model);
        });
    }

    public function recordPayment(int $id, float $amount, ?int $storeId): FinancialTransactionModel
    {
        return DB::transaction(function () use ($id, $amount, $storeId): FinancialTransactionModel {
            $model = FinancialTransactionModel::query()
                ->when($storeId !== null, fn ($query) => $query->where('store_id', $storeId))
                ->lockForUpdate()
                ->find($id);

            if (! $model) {
                throw new InvalidArgumentException('Transaksi keuangan tidak ditemukan.');
            }

            if (! in_array($model->type, ['payable', 'receivable'], true)) {
                throw new InvalidArgumentException('Pembayaran hanya berlaku untuk hutang atau piutang.');
            }

            $model->paid_amount = min((float) $model->amount, (float) $model->paid_amount + max(0, $amount));
            $model->status = $this->resolveStatus($model->type, (float) $model->amount, (float) $model->paid_amount, $model->status);
            $model->settled_at = $model->status === 'paid' ? now() : null;

            return $this->repository->save($model);
        });
    }

    public function delete(int $id, ?int $storeId): void
    {
        $this->repository->delete($this->find($id, $storeId));
    }

    private function resolveStatus(string $type, float $amount, float $paidAmount, string $requested): string
    {
        if (! in_array($type, ['payable', 'receivable'], true)) {
            return in_array($requested, ['draft', 'posted', 'cancelled'], true) ? $requested : 'posted';
        }

        if ($requested === 'cancelled') {
            return 'cancelled';
        }

        if ($paidAmount <= 0) {
            return 'open';
        }

        return $paidAmount >= $amount ? 'paid' : 'partial';
    }

    private function referenceNumber(string $type): string
    {
        return strtoupper(substr($type, 0, 3)) . '-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(5));
    }
}
