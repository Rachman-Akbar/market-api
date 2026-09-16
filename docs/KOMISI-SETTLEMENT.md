# Komisi/Settlement Seller — Alur Otomatis saat Order Selesai

> Fitur: 16 September 2026. Domain: `Finance/Commission` (+ wiring di `Seller/Finance` & `Order/Ordering`).

## Ringkasan

Saat sebuah **sub-order milik seller berstatus `completed`**, sistem otomatis:

1. Menghitung komisi/admin fee (berdasarkan konfigurasi admin fee), membuat
   **settlement seller** (idempotent).
2. Menulis `admin_fee` & `seller_net` pada sub-order dan menyinkronkan total ke
   order induk.
3. Mencatat **pemasukan (income)** seller senilai `seller_net` (bila > 0),
   fallback `total_items_price`, ke transaksi finance.

Alur ini berlaku untuk order yang diselesaikan lewat jalur **seller** maupun
jalur **admin** (`UpdateOrderStatusUseCase`).

## Komponen

| Artifak | Lokasi |
|---|---|
| Use-case hitung komisi | `app/Domains/Finance/Commission/Application/UseCases/CalculateCommissionUseCase.php` |
| Service komisi per sub-order (baru) | `app/Domains/Finance/Commission/Application/Services/OrderCommissionService.php` |
| Service settlement | `app/Domains/Finance/Commission/Application/Services/SellerSettlementService.php` |
| Konfigurasi admin fee | `app/Domains/Finance/Commission/Application/Services/AdminFeeConfigService.php` (`calculateFee`) |
| Repo (getByStore / findByOrderAndSubOrder) | `app/Domains/Finance/Commission/Infrastructure/Persistence/Repositories/EloquentSellerSettlementRepository.php` |
| Pemasukan otomatis | `app/Domains/Seller/Finance/Application/Services/AutoOrderIncomeService.php` |
| Binding singleton | `app/Domains/Finance/Commission/CommissionServiceProvider.php` |

## Detail Alur

### 1. `OrderCommissionService::recordForSubOrder(int $subOrderId, ?string $buyerUserId = null): ?SellerSettlement`

- `DB::transaction`, sub-order di-`lockForUpdate` (dengan relasi `parentOrder`).
- Prasyarat: sub-order ada, `status === 'completed'`, `total_items_price > 0`.
- Memanggil `CalculateCommissionUseCase::execute(...)`:
  - Cek `findByOrderAndSubOrder(orderId, subOrderId)` — jika sudah ada,
    settlement dikembalikan (tidak duplikat).
  - `adminFee = feeConfigService->calculateFee(amount, categoryId)`.
  - `netAmount = max(0, amount - adminFee)`.
  - Insert settlement: `settlement_number = STL-YYYYMMDD-XXXXXX`, `status = pending`,
    `gross_amount`, `admin_fee`, `shipping_fee`, `net_amount`, `created_by = buyerUserId`,
    `metadata` = `{ order_number, order_type }`.
- Sub-order di-`forceFill(['admin_fee', 'seller_net'])` lalu `save()`.
- `syncParentAmounts(orderId)`: `orders.admin_fee/seller_net` di-update ke
  `COALESCE(SUM(...))` dari semua sub-order.

### 2. `AutoOrderIncomeService::recordForSubOrder(int $subOrderId, ?string $buyerUserId): bool`

- Panggil komisi **lebih dulu** (`$this->orderCommission->recordForSubOrder(...)`),
  lalu `$subOrder->refresh()`.
- Idempotent: jika sudah ada transaksi `type=income` untuk `(store_id, order_id)` → `false`.
- `amount = seller_net > 0 ? seller_net : total_items_price`.
- Membuat `financial_transactions`:
  - `reference_number = INC-YYYYMMDDHHIISS-XXXXX`
  - `type = income`, `status = posted`, `occurred_at = sub_order->updated_at ?? now()`
  - `metadata = { source: 'order_completed', order_number, sub_order_id }`
  - Deskripsi memakai line item produk (`produk x jumlah`).

## Endpoint Terkait (tetap ada)

- Seller finance: `GET /seller/finance/dashboard*` (prefix `finance`, middleware
  `auth:sanctum` + `active.user` + `verified.email` + `role:seller,admin` +
  `permission:finance.manage`).
- Settlement & withdrawal admin: `Finance/Commission/Presentation/routes.php`
  (`AdminFeeConfigController`, `SellerSettlementController`, `SellerWithdrawalController`).

## Idempotensi

| Level | Kunci | Akibat diulang |
|---|---|---|
| Settlement | `(order_id, sub_order_id)` | dikembalikan apa adanya, tidak duplikat |
| Income | `(store_id, order_id)` + `type=income` | dilewati |

## Pengujian

- `tests/Feature/Seller/OrderCommissionTest.php` (3 skenario: selesai → settlement +
  sync amount + income; idempotensi via `AutoOrderIncomeService` dipanggil 2×;
  konfigurasi admin fee 10% → `admin_fee` 5000, `seller_net` 45000, income 45000).
- Regresi terkait: `OrderCompletionIncomeTest`, `PoStockSplitTest` (cancel tidak
  membuat double-release stock).