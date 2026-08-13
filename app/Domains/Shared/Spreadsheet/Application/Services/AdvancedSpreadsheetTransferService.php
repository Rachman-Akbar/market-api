<?php

declare(strict_types=1);

namespace App\Domains\Shared\Spreadsheet\Application\Services;

use App\Domains\Catalog\Product\Infrastructure\Persistence\Models\ProductVariantModel;
use App\Domains\Identity\User\Domain\Entities\User;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderItemModel;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\OrderModel;
use App\Domains\Order\Ordering\Infrastructure\Persistence\Models\SubOrderModel;
use App\Domains\Seller\Finance\Infrastructure\Persistence\Models\FinancialTransactionModel;
use App\Domains\Seller\Stock\Application\Services\StockMovementService;
use App\Domains\Seller\Stock\Infrastructure\Persistence\Models\StockMovementModel;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;

final class AdvancedSpreadsheetTransferService
{
    private array $createdOrders = [];
    private array $orderSignatures = [];

    public function __construct(private StockMovementService $stockMovementService) {}

    public function supports(string $module): bool
    {
        return in_array($module, ['order', 'income', 'expense', 'receivable', 'payable', 'stock'], true);
    }

    public function reset(): void
    {
        $this->createdOrders = [];
        $this->orderSignatures = [];
    }

    public function scopedQuery(Request $request, string $module): Builder
    {
        $role = $this->activeRole($request);
        $storeId = $role === 'seller' ? $this->sellerStoreId($request) : null;

        if ($module === 'order') {
            return OrderModel::query()
                ->when($storeId !== null, fn (Builder $query) => $query->whereHas('subOrders', fn (Builder $subQuery) => $subQuery->where('store_id', $storeId)))
                ->with(['subOrders' => function ($query) use ($storeId): void {
                    $query->when($storeId !== null, fn ($subQuery) => $subQuery->where('store_id', $storeId))
                        ->with(['store:id,name', 'items']);
                }]);
        }

        if ($module === 'stock') {
            return StockMovementModel::query()
                ->when($storeId !== null, fn (Builder $query) => $query->where('store_id', $storeId))
                ->with(['store:id,name', 'product:id,name', 'variant:id,product_id,store_id,sku,name,price,stock', 'order:id,order_number']);
        }

        return FinancialTransactionModel::query()
            ->where('type', $module)
            ->when($storeId !== null, fn (Builder $query) => $query->where('store_id', $storeId))
            ->with(['store:id,name', 'order:id,order_number', 'counterparty:id,name,email']);
    }

    public function persist(Request $request, string $module, array $row): void
    {
        match ($module) {
            'order' => $this->persistOrder($request, $row),
            'stock' => $this->persistStock($request, $row),
            'income', 'expense', 'receivable', 'payable' => $this->persistFinance($request, $module, $row),
            default => throw new InvalidArgumentException('Modul spreadsheet lanjutan tidak didukung.'),
        };
    }

    public function exportRows(string $module, iterable $models): array
    {
        $rows = [];

        foreach ($models as $model) {
            if ($module === 'order') {
                foreach ($model->subOrders as $subOrder) {
                    foreach ($subOrder->items as $item) {
                        $variant = $item->variant_id ? ProductVariantModel::query()->find($item->variant_id) : null;
                        $buyer = User::query()->find($model->user_id);
                        $rows[] = [
                            'id' => $model->id,
                            'order_number' => $model->order_number,
                            'buyer_email' => $buyer?->email ?: '',
                            'store_name' => $subOrder->store?->name ?: '',
                            'sub_order_number' => $subOrder->sub_order_number,
                            'order_type' => $model->order_type,
                            'status' => $model->status,
                            'payment_status' => $model->payment_status,
                            'payment_method' => $model->payment_method,
                            'shipping_address' => $model->shipping_address,
                            'sku' => $item->sku,
                            'product_name' => $item->product_name,
                            'variant_name' => $variant?->name ?: '',
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'shipping_cost' => $subOrder->shipping_cost,
                            'courier' => $subOrder->courier,
                            'service' => $subOrder->service,
                            'destination_id' => $subOrder->destination_id,
                            'tracking_number' => $subOrder->tracking_number,
                            'preorder_release_at' => $this->formatDate($model->preorder_release_at),
                            'booking_expires_at' => $this->formatDate($model->booking_expires_at),
                            'received_at' => $this->formatDate($model->received_at),
                        ];
                    }
                }
                continue;
            }

            if ($module === 'stock') {
                $rows[] = [
                    'id' => $model->id,
                    'store_name' => $model->store?->name ?: '',
                    'sku' => $model->variant?->sku ?: '',
                    'product_name' => $model->product?->name ?: '',
                    'variant_name' => $model->variant?->name ?: '',
                    'movement_type' => $model->type,
                    'quantity_delta' => $model->quantity_delta,
                    'balance_after' => $model->balance_after,
                    'reference_type' => $model->reference_type,
                    'reference_id' => $model->reference_id,
                    'order_number' => $model->order?->order_number ?: '',
                    'notes' => $model->notes,
                    'occurred_at' => $this->formatDate($model->occurred_at),
                ];
                continue;
            }

            $rows[] = [
                'id' => $model->id,
                'store_name' => $model->store?->name ?: '',
                'order_number' => $model->order?->order_number ?: '',
                'counterparty_email' => $model->counterparty?->email ?: '',
                'reference_number' => $model->reference_number,
                'title' => $model->title,
                'description' => $model->description,
                'amount' => $model->amount,
                'paid_amount' => $model->paid_amount,
                'status' => $model->status,
                'due_date' => $this->formatDate($model->due_date, 'Y-m-d'),
                'occurred_at' => $this->formatDate($model->occurred_at),
                'settled_at' => $this->formatDate($model->settled_at),
                'is_active' => $model->is_active ? 1 : 0,
            ];
        }

        return $rows;
    }

    public function analyzeMissingRelations(Request $request, string $module, array $rows): array
    {
        $missing = [];
        $role = $this->activeRole($request);
        $sellerStoreId = $role === 'seller' ? $this->sellerStoreId($request) : null;

        foreach ($rows as $item) {
            $rowNumber = (int) ($item['row_number'] ?? 0);
            $row = (array) ($item['data'] ?? []);
            $store = $sellerStoreId ? StoreModel::query()->find($sellerStoreId) : $this->findStore($row['store_name'] ?? null);

            if (! $store && in_array($module, ['order', 'stock'], true)) {
                $this->addMissing($missing, 'store', $this->clean($row['store_name'] ?? ''), $rowNumber);
            }

            if ($module === 'order') {
                if (! $this->findActiveUser($row['buyer_email'] ?? null)) {
                    $this->addMissing($missing, 'user', $this->clean($row['buyer_email'] ?? ''), $rowNumber);
                }
                if ($store && ! $this->findVariant((int) $store->id, $row['sku'] ?? null)) {
                    $this->addMissing($missing, 'product_variant', $this->clean($row['sku'] ?? ''), $rowNumber);
                }
            } elseif ($module === 'stock') {
                if ($store && ! $this->findVariant((int) $store->id, $row['sku'] ?? null)) {
                    $this->addMissing($missing, 'product_variant', $this->clean($row['sku'] ?? ''), $rowNumber);
                }
            } else {
                if ($this->clean($row['store_name'] ?? '') !== '' && ! $store) {
                    $this->addMissing($missing, 'store', $this->clean($row['store_name'] ?? ''), $rowNumber);
                }
                $email = $this->clean($row['counterparty_email'] ?? '');
                if ($email !== '' && ! $this->findActiveUser($email)) {
                    $this->addMissing($missing, 'user', $email, $rowNumber);
                }
            }

            $orderNumber = $this->clean($row['order_number'] ?? '');
            if ($orderNumber !== '' && ! OrderModel::query()->where('order_number', $orderNumber)->exists() && $module !== 'order') {
                $this->addMissing($missing, 'order', $orderNumber, $rowNumber);
            }
        }

        $blocking = array_values($missing);

        return [
            'requires_confirmation' => false,
            'can_continue' => $blocking === [],
            'missing_relations' => [],
            'blocking_relations' => $blocking,
        ];
    }

    private function persistFinance(Request $request, string $module, array $row): void
    {
        $mode = $this->importMode($request);
        $role = $this->activeRole($request);
        $store = $role === 'seller' ? StoreModel::query()->findOrFail($this->sellerStoreId($request)) : $this->findStore($row['store_name'] ?? null);
        $storeId = $store?->id;
        $order = $this->findOrder($row['order_number'] ?? null);

        if ($role === 'seller' && $order && ! SubOrderModel::query()->where('order_id', $order->id)->where('store_id', $storeId)->exists()) {
            throw new InvalidArgumentException('Order tidak terhubung dengan toko Seller.');
        }

        $counterparty = $this->findActiveUser($row['counterparty_email'] ?? null);
        if ($this->clean($row['counterparty_email'] ?? '') !== '' && ! $counterparty) {
            throw new InvalidArgumentException('Email pihak transaksi tidak ditemukan atau tidak aktif.');
        }

        $model = $mode === 'update'
            ? FinancialTransactionModel::query()->where('type', $module)->when($role === 'seller', fn (Builder $query) => $query->where('store_id', $storeId))->findOrFail((int) $row['id'])
            : new FinancialTransactionModel();
        $amount = $this->positiveFloat($row['amount'] ?? null, 'Nominal transaksi');
        $settlement = in_array($module, ['payable', 'receivable'], true);
        $paidAmount = max(0, $this->floatValue($row['paid_amount'] ?? 0));

        if ($paidAmount > $amount) {
            throw new InvalidArgumentException('Jumlah dibayar tidak boleh melebihi nominal transaksi.');
        }
        if (! $settlement && $paidAmount !== 0.0) {
            throw new InvalidArgumentException('paid_amount harus 0 untuk pemasukan dan pengeluaran.');
        }

        $requestedStatus = strtolower($this->clean($row['status'] ?? ''));
        $allowedStatuses = $settlement ? ['open', 'partial', 'paid', 'cancelled'] : ['draft', 'posted', 'cancelled'];
        if ($requestedStatus === '' || ! in_array($requestedStatus, $allowedStatuses, true)) {
            throw new InvalidArgumentException('Status transaksi tidak valid untuk modul '.$module.'.');
        }
        $status = $this->financeStatus($module, $amount, $paidAmount, $requestedStatus);
        $reference = $this->clean($row['reference_number'] ?? '') ?: $this->financeReference($module);
        $duplicate = FinancialTransactionModel::query()->where('reference_number', $reference)->when($model->exists, fn (Builder $query) => $query->whereKeyNot($model->id))->exists();
        if ($duplicate) {
            throw new InvalidArgumentException('Reference number transaksi sudah digunakan: '.$reference);
        }

        $title = $this->clean($row['title'] ?? '');
        if ($title === '') {
            throw new InvalidArgumentException('Judul transaksi wajib diisi.');
        }

        $model->fill([
            'store_id' => $storeId,
            'order_id' => $order?->id,
            'user_id' => $counterparty?->id,
            'reference_number' => $reference,
            'type' => $module,
            'title' => Str::limit($title, 160, ''),
            'description' => $this->nullable($row['description'] ?? null),
            'amount' => $amount,
            'paid_amount' => $paidAmount,
            'status' => $status,
            'due_date' => $this->nullableDate($row['due_date'] ?? null, 'Y-m-d'),
            'occurred_at' => $this->requiredDate($row['occurred_at'] ?? null, 'Tanggal transaksi'),
            'settled_at' => $settlement && $status === 'paid' ? ($this->nullableDate($row['settled_at'] ?? null) ?: now()) : null,
            'is_active' => $this->boolValue($row['is_active'] ?? true),
            'created_by' => $model->created_by ?: $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ])->save();
    }

    private function persistStock(Request $request, array $row): void
    {
        $mode = $this->importMode($request);
        $role = $this->activeRole($request);
        $store = $role === 'seller' ? StoreModel::query()->findOrFail($this->sellerStoreId($request)) : $this->findStore($row['store_name'] ?? null);
        if (! $store) {
            throw new InvalidArgumentException('Nama toko wajib diisi dan harus tersedia.');
        }

        $referenceMovement = null;
        if ($mode === 'update') {
            $referenceMovement = StockMovementModel::query()->where('store_id', $store->id)->findOrFail((int) $row['id']);
        }

        $variant = $referenceMovement
            ? ProductVariantModel::query()->lockForUpdate()->find($referenceMovement->variant_id)
            : $this->findVariant((int) $store->id, $row['sku'] ?? null, true);
        if (! $variant) {
            throw new InvalidArgumentException('SKU variant tidak ditemukan pada toko.');
        }
        $requestedSku = $this->clean($row['sku'] ?? '');
        if ($referenceMovement && $requestedSku !== '' && Str::lower($requestedSku) !== Str::lower((string) $variant->sku)) {
            throw new InvalidArgumentException('SKU tidak sesuai dengan ID Stock Movement yang dipilih.');
        }

        $variant = ProductVariantModel::query()->lockForUpdate()->findOrFail($variant->id);
        $currentBalance = (int) $variant->stock;
        if ($mode === 'update' && $this->clean($row['balance_after'] ?? '') !== '') {
            $targetBalance = $this->nonNegativeInt($row['balance_after'], 'Saldo akhir');
            $delta = $targetBalance - $currentBalance;
        } else {
            $delta = $this->nonZeroInt($row['quantity_delta'] ?? null, 'Perubahan stok');
            $targetBalance = $currentBalance + $delta;
        }

        if ($targetBalance < 0) {
            throw new InvalidArgumentException('Stok akhir tidak boleh negatif.');
        }
        if ($delta === 0) {
            throw new InvalidArgumentException('Perubahan stok menghasilkan saldo yang sama.');
        }

        $order = $this->findOrder($row['order_number'] ?? null);
        if ($role === 'seller' && $order && ! SubOrderModel::query()->where('order_id', $order->id)->where('store_id', $store->id)->exists()) {
            throw new InvalidArgumentException('Order stok bukan milik toko Seller.');
        }

        $movementType = $this->stockType($row['movement_type'] ?? null, $delta);
        if (in_array($movementType, ['inbound', 'release'], true) && $delta < 0) {
            throw new InvalidArgumentException('movement_type barang masuk atau release harus memakai quantity_delta positif.');
        }
        if (in_array($movementType, ['outbound', 'reservation'], true) && $delta > 0) {
            throw new InvalidArgumentException('movement_type barang keluar atau reservation harus memakai quantity_delta negatif.');
        }

        $variant->forceFill(['stock' => $targetBalance])->save();
        StockMovementModel::create([
            'store_id' => $store->id,
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'order_id' => $order?->id,
            'movement_key' => 'spreadsheet-'.Str::uuid(),
            'type' => $movementType,
            'quantity_delta' => $delta,
            'balance_after' => $targetBalance,
            'reference_type' => $this->nullable($row['reference_type'] ?? null) ?: 'spreadsheet_import',
            'reference_id' => $this->nullable($row['reference_id'] ?? null),
            'notes' => $this->nullable($row['notes'] ?? null),
            'occurred_at' => $this->requiredDate($row['occurred_at'] ?? null, 'Tanggal movement'),
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);
    }

    private function persistOrder(Request $request, array $row): void
    {
        $mode = $this->importMode($request);
        $role = $this->activeRole($request);
        $store = $role === 'seller' ? StoreModel::query()->findOrFail($this->sellerStoreId($request)) : $this->findStore($row['store_name'] ?? null);
        if (! $store) {
            throw new InvalidArgumentException('Nama toko wajib diisi dan harus tersedia.');
        }

        $orderNumber = $this->clean($row['order_number'] ?? '');
        if ($orderNumber === '') {
            throw new InvalidArgumentException('Nomor pesanan wajib diisi.');
        }

        $buyer = $this->findActiveUser($row['buyer_email'] ?? null);
        if (! $buyer) {
            throw new InvalidArgumentException('Email buyer tidak ditemukan atau tidak aktif.');
        }

        $variant = $this->findVariant((int) $store->id, $row['sku'] ?? null, true);
        if (! $variant) {
            throw new InvalidArgumentException('SKU variant tidak ditemukan pada toko: '.$this->clean($row['sku'] ?? ''));
        }

        $status = strtolower($this->clean($row['status'] ?? 'pending'));
        if (! in_array($status, ['pending', 'processing', 'shipped', 'received', 'completed', 'cancelled'], true)) {
            throw new InvalidArgumentException('Status pesanan tidak valid.');
        }
        $paymentStatus = strtolower($this->clean($row['payment_status'] ?? 'unpaid'));
        if (! in_array($paymentStatus, ['unpaid', 'pending', 'paid', 'failed', 'refunded'], true)) {
            throw new InvalidArgumentException('Status pembayaran tidak valid.');
        }
        $orderType = strtolower($this->clean($row['order_type'] ?? 'normal'));
        if (! in_array($orderType, ['normal', 'preorder', 'booking'], true)) {
            throw new InvalidArgumentException('Tipe pesanan tidak valid.');
        }

        $signature = implode('|', [(string) $buyer->id, $orderType, $status, $paymentStatus, $this->clean($row['shipping_address'] ?? '')]);
        if (isset($this->orderSignatures[$orderNumber]) && $this->orderSignatures[$orderNumber] !== $signature) {
            throw new InvalidArgumentException('Data utama pesanan tidak konsisten untuk nomor '.$orderNumber.'.');
        }
        $this->orderSignatures[$orderNumber] = $signature;

        if ($mode === 'update') {
            $order = OrderModel::query()
                ->when($role === 'seller', fn (Builder $query) => $query->whereHas('subOrders', fn (Builder $subQuery) => $subQuery->where('store_id', $store->id)))
                ->findOrFail((int) $row['id']);
            if ($order->order_number !== $orderNumber) {
                throw new InvalidArgumentException('order_number tidak sesuai dengan ID Order.');
            }
        } else {
            if (isset($this->createdOrders[$orderNumber])) {
                $order = OrderModel::query()->findOrFail($this->createdOrders[$orderNumber]);
            } else {
                if (OrderModel::query()->where('order_number', $orderNumber)->exists()) {
                    throw new InvalidArgumentException('Nomor pesanan sudah tersedia. Gunakan mode Import Update Data.');
                }
                $order = new OrderModel();
            }
        }

        $previousOrderStatus = $order->exists ? (string) $order->status : 'pending';
        $shippingAddress = $this->clean($row['shipping_address'] ?? '');
        if ($shippingAddress === '') {
            throw new InvalidArgumentException('Alamat pengiriman wajib diisi.');
        }
        if ($orderType === 'preorder' && ! $this->nullableDate($row['preorder_release_at'] ?? null)) {
            throw new InvalidArgumentException('Tanggal rilis wajib untuk preorder.');
        }
        if ($orderType === 'booking' && ! $this->nullableDate($row['booking_expires_at'] ?? null)) {
            throw new InvalidArgumentException('Batas booking wajib untuk pesanan booking.');
        }

        $order->fill([
            'order_number' => $orderNumber,
            'order_type' => $orderType,
            'preorder_release_at' => $this->nullableDate($row['preorder_release_at'] ?? null),
            'booking_expires_at' => $this->nullableDate($row['booking_expires_at'] ?? null),
            'received_at' => $this->nullableDate($row['received_at'] ?? null),
            'user_id' => $buyer->id,
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_method' => $this->nullable($row['payment_method'] ?? null),
            'shipping_address' => $shippingAddress,
        ])->save();
        $this->createdOrders[$orderNumber] = (int) $order->id;

        $subOrder = SubOrderModel::query()->where('order_id', $order->id)->where('store_id', $store->id)->first();
        $previousSubStatus = $subOrder?->status ?: 'pending';
        if (! $subOrder) {
            $subOrder = new SubOrderModel(['order_id' => $order->id, 'store_id' => $store->id]);
        }
        $subNumber = $this->clean($row['sub_order_number'] ?? '') ?: $this->subOrderNumber($orderNumber, (int) $store->id);
        if (SubOrderModel::query()->where('sub_order_number', $subNumber)->when($subOrder->exists, fn (Builder $query) => $query->whereKeyNot($subOrder->id))->exists()) {
            throw new InvalidArgumentException('Nomor sub-order sudah digunakan: '.$subNumber);
        }
        $subOrder->fill([
            'sub_order_number' => $subNumber,
            'shipping_cost' => max(0, $this->floatValue($row['shipping_cost'] ?? 0)),
            'courier' => $this->nullable($row['courier'] ?? null),
            'service' => $this->nullable($row['service'] ?? null),
            'destination_id' => $this->clean($row['destination_id'] ?? '') ?: '0',
            'status' => $status,
            'tracking_number' => $this->nullable($row['tracking_number'] ?? null),
        ])->save();

        $quantity = $this->positiveInt($row['quantity'] ?? null, 'Quantity');
        $price = $this->clean($row['price'] ?? '') === '' ? (float) $variant->price : max(0, $this->floatValue($row['price']));
        $item = OrderItemModel::query()->where('sub_order_id', $subOrder->id)->where('sku', $variant->sku)->first();
        $oldQuantity = $item ? (int) $item->quantity : 0;

        if ($item && in_array($previousSubStatus, ['shipped', 'received', 'completed', 'cancelled'], true) && $quantity !== $oldQuantity) {
            throw new InvalidArgumentException('Quantity order yang sudah dikirim, diterima, selesai, atau dibatalkan tidak dapat diubah lewat import.');
        }

        if (! $item) {
            $item = new OrderItemModel(['sub_order_id' => $subOrder->id]);
        }
        $item->fill([
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'product_name' => $this->clean($row['product_name'] ?? '') ?: $variant->product?->name ?: 'Produk',
            'sku' => $variant->sku,
            'price' => $price,
            'quantity' => $quantity,
        ])->save();

        $stockDelta = -1 * ($quantity - $oldQuantity);
        if ($status !== 'cancelled' && $stockDelta !== 0) {
            $lockedVariant = ProductVariantModel::query()->lockForUpdate()->findOrFail($variant->id);
            $nextStock = (int) $lockedVariant->stock + $stockDelta;
            if ($nextStock < 0) {
                throw new InvalidArgumentException('Stok tidak mencukupi untuk quantity pesanan.');
            }
            $lockedVariant->forceFill(['stock' => $nextStock])->save();
            StockMovementModel::create([
                'store_id' => $store->id,
                'product_id' => $variant->product_id,
                'variant_id' => $variant->id,
                'order_id' => $order->id,
                'order_item_id' => $item->id,
                'movement_key' => $oldQuantity === 0 ? 'checkout-reserved' : 'spreadsheet-order-'.Str::uuid(),
                'type' => $stockDelta < 0 ? 'outbound' : 'release',
                'quantity_delta' => $stockDelta,
                'balance_after' => $nextStock,
                'reference_type' => 'order_import',
                'reference_id' => (string) $order->id,
                'notes' => $oldQuantity === 0 ? 'Reservasi stok dari import pesanan' : 'Koreksi quantity dari import pesanan',
                'occurred_at' => now(),
                'created_by' => $request->user()?->id,
                'updated_by' => $request->user()?->id,
            ]);
        }

        $subOrder->forceFill([
            'total_items_price' => OrderItemModel::query()->where('sub_order_id', $subOrder->id)->selectRaw('COALESCE(SUM(price * quantity), 0) as total')->value('total'),
        ])->save();
        $order->forceFill([
            'total_amount' => SubOrderModel::query()->where('order_id', $order->id)->selectRaw('COALESCE(SUM(total_items_price + shipping_cost), 0) as total')->value('total'),
        ])->save();

        $skipInitialCancelledRelease = $status === 'cancelled' && $oldQuantity === 0;
        if (! $skipInitialCancelledRelease && $previousSubStatus !== $status) {
            $this->stockMovementService->syncSubOrderStatus((int) $subOrder->id, $previousSubStatus, $status);
        } elseif (! $skipInitialCancelledRelease && $previousOrderStatus !== $status) {
            $this->stockMovementService->syncOrderStatus((int) $order->id, $previousOrderStatus, $status);
        }
    }

    private function activeRole(Request $request): string
    {
        $role = $request->attributes->get('active_role');
        if (is_string($role) && $role !== '') {
            return strtolower(trim($role));
        }
        $ability = collect($request->user()?->currentAccessToken()?->abilities ?? [])->first(fn (mixed $value): bool => is_string($value) && str_starts_with($value, 'active-role:'));
        return is_string($ability) ? strtolower(trim(substr($ability, 12))) : 'buyer';
    }

    private function sellerStoreId(Request $request): int
    {
        $userId = (string) ($request->user()?->getAuthIdentifier() ?? '');
        $storeId = (int) ($request->attributes->get('seller_store_id') ?? 0);

        if ($storeId <= 0 && $userId !== '') {
            $storeId = (int) DB::table('stores')
                ->where('user_id', $userId)
                ->whereNull('deleted_at')
                ->orderByDesc('is_active')
                ->orderBy('id')
                ->value('id');
        }

        if ($storeId <= 0) {
            throw new InvalidArgumentException('Seller belum memiliki toko aktif.');
        }

        $request->attributes->set('seller_store_id', $storeId);

        return $storeId;
    }

    private function importMode(Request $request): string
    {
        $mode = strtolower($this->clean($request->attributes->get('import_mode', $request->input('import_mode', 'create'))));
        return in_array($mode, ['create', 'update'], true) ? $mode : 'create';
    }

    private function findStore(mixed $name): ?StoreModel
    {
        $value = $this->clean($name);
        if ($value === '') return null;
        return StoreModel::query()->whereRaw('LOWER(TRIM(name)) = ?', [Str::lower($value)])->first();
    }

    private function findActiveUser(mixed $email): ?User
    {
        $value = Str::lower($this->clean($email));
        if ($value === '') return null;
        return User::query()->whereRaw('LOWER(TRIM(email)) = ?', [$value])->where('is_active', true)->first();
    }

    private function findVariant(int $storeId, mixed $sku, bool $withProduct = false): ?ProductVariantModel
    {
        $value = $this->clean($sku);
        if ($value === '') return null;
        return ProductVariantModel::query()->when($withProduct, fn (Builder $query) => $query->with('product:id,name'))->where('store_id', $storeId)->whereRaw('LOWER(TRIM(sku)) = ?', [Str::lower($value)])->first();
    }

    private function findOrder(mixed $number): ?OrderModel
    {
        $value = $this->clean($number);
        return $value === '' ? null : OrderModel::query()->where('order_number', $value)->first();
    }

    private function financeStatus(string $type, float $amount, float $paid, string $requested): string
    {
        if (in_array($type, ['income', 'expense'], true)) {
            return in_array($requested, ['draft', 'posted', 'cancelled'], true) ? $requested : 'posted';
        }
        if ($requested === 'cancelled') return 'cancelled';
        if ($paid <= 0) return 'open';
        return $paid >= $amount ? 'paid' : 'partial';
    }

    private function financeReference(string $type): string
    {
        do {
            $value = strtoupper(substr($type, 0, 3)).'-'.now()->format('YmdHis').'-'.Str::upper(Str::random(5));
        } while (FinancialTransactionModel::query()->where('reference_number', $value)->exists());
        return $value;
    }

    private function subOrderNumber(string $orderNumber, int $storeId): string
    {
        $base = Str::upper(Str::limit(Str::slug($orderNumber, ''), 60, '')).'-'.$storeId;
        $value = $base;
        $counter = 1;
        while (SubOrderModel::query()->where('sub_order_number', $value)->exists()) {
            $value = $base.'-'.$counter++;
        }
        return $value;
    }

    private function stockType(mixed $requested, int $delta): string
    {
        $value = strtolower($this->clean($requested));
        $map = ['in' => 'inbound', 'out' => 'outbound', 'adjustment' => 'adjustment', 'reservation' => 'reservation', 'release' => 'release', 'inbound' => 'inbound', 'outbound' => 'outbound'];
        return $map[$value] ?? ($delta > 0 ? 'inbound' : 'outbound');
    }

    private function addMissing(array &$missing, string $type, string $name, int $rowNumber): void
    {
        if ($name === '') return;
        $key = $type.'|'.Str::lower($name);
        if (! isset($missing[$key])) {
            $missing[$key] = ['type' => $type, 'name' => $name, 'row_numbers' => [], 'can_auto_create' => false, 'context' => ''];
        }
        $missing[$key]['row_numbers'][] = $rowNumber;
        $missing[$key]['row_numbers'] = array_values(array_unique($missing[$key]['row_numbers']));
    }

    private function clean(mixed $value): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', (string) ($value ?? '')));
    }

    private function nullable(mixed $value): ?string
    {
        $value = $this->clean($value);
        return $value === '' ? null : $value;
    }

    private function boolValue(mixed $value): bool
    {
        if (is_bool($value)) return $value;
        return in_array(strtolower($this->clean($value)), ['1', 'true', 'yes', 'ya', 'aktif', 'active'], true);
    }

    private function floatValue(mixed $value): float
    {
        if (! is_numeric($value)) throw new InvalidArgumentException('Nilai nominal harus berupa angka tanpa simbol mata uang.');
        return round((float) $value, 2);
    }

    private function positiveFloat(mixed $value, string $label): float
    {
        $number = $this->floatValue($value);
        if ($number <= 0) throw new InvalidArgumentException($label.' harus lebih besar dari nol.');
        return $number;
    }

    private function positiveInt(mixed $value, string $label): int
    {
        if (! is_numeric($value) || (int) $value <= 0) throw new InvalidArgumentException($label.' harus berupa angka bulat positif.');
        return (int) $value;
    }

    private function nonNegativeInt(mixed $value, string $label): int
    {
        if (! is_numeric($value) || (int) $value < 0) throw new InvalidArgumentException($label.' harus berupa angka bulat nol atau lebih.');
        return (int) $value;
    }

    private function nonZeroInt(mixed $value, string $label): int
    {
        if (! is_numeric($value) || (int) $value === 0) throw new InvalidArgumentException($label.' harus berupa angka bulat selain nol.');
        return (int) $value;
    }

    private function nullableDate(mixed $value, string $format = 'Y-m-d H:i:s'): ?string
    {
        if ($value instanceof DateTimeInterface) return $value->format($format);
        $text = $this->clean($value);
        if ($text === '') return null;
        if (is_numeric($value)) return SpreadsheetDate::excelToDateTimeObject((float) $value)->format($format);
        $time = strtotime($text);
        if ($time === false) throw new InvalidArgumentException('Format tanggal tidak valid: '.$text);
        return date($format, $time);
    }

    private function requiredDate(mixed $value, string $label): string
    {
        return $this->nullableDate($value) ?? throw new InvalidArgumentException($label.' wajib diisi.');
    }

    private function formatDate(mixed $value, string $format = 'Y-m-d H:i:s'): string
    {
        if ($value instanceof DateTimeInterface) return $value->format($format);
        if (is_object($value) && method_exists($value, 'format')) return $value->format($format);
        return $value ? (string) $value : '';
    }
}
