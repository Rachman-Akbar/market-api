# Filter / Parameter Tanggal — Riwayat Transaksi & Dashboard

> Fitur: 16 September 2026. Prinsip: **filter tanggal hanya untuk fitur transaksional**
> (laporan, riwayat, analitik). Data master/katalog sengaja tidak memakai filter tanggal.

## Ringkasan Pola

- Param input: `date_from`, `date_to` (format `Y-m-d`), kadang `from_date` / `to_date`.
- Batas filter memakai `whereDate` (mencakup seluruh hari) dan `Carbon` `startOfDay()`/`endOfDay()`
  agar baris di tengah malam tanggal akhir tidak terlewat.
- Dashboard memakai `period` (`daily|weekly|monthly|yearly`); jika `date_from`/`date_to`
  diberikan, periode dianggap `custom` dan menimpa rentang.

## Cakupan Filter Tanggal per Fitur

| Fitur | Endpoint | Param | Basis kolom |
|---|---|---|---|
| Riwayat order (buyer/admin/user/store/customer) | `GET /order/orderings*` (`index/getByStore/getByCustomer`) | `date_from`, `date_to` | `orders.created_at` |
| Dashboard penjualan | `GET /seller/finance/dashboard` | `date_from`, `date_to` (+ `period`) | rentang sesuai periode / custom |
| Order trend | `GET /seller/finance/dashboard/order-trend` | `date_from`, `date_to` (+ `period`) | rentang custom |
| Cashflow | `GET /seller/finance/dashboard/cashflow` | `from_date`, `to_date` (wajib) | `financial_transactions.occurred_at` |
| Transaksi finansial | `GET /seller/finance/` | `date_from`, `date_to` | `occurred_at` |
| Settlement | `GET finance/...settlements` (getByStore) | `from_date`, `to_date` | `created_at` |
| Hutang-piutang export | `GET /seller/finance/hutang-piutang/{type}/export` | `from_date`, `to_date` | `occurred_at` |
| Stock movements | stock movements seller | `date_from`, `date_to` | `moved_at` / `created_at` |
| Katalog (master) | `catalog/*` | — (tidak dipakai) | — |

## Detail Implementasi (backend)

- `EloquentOrderRepository::paginateForUser`: `whereDate('created_at', '>=', date_from)`
  dan `whereDate('created_at', '<=', date_to)` saat param terisi.
- `OrderingController::index` (buyer + admin): meneruskan filter
  `['user_id','status','payment_status','order_type','date_from','date_to','search']`.
- `SellerFinanceDashboardService`:
  - `getDashboard(storeId, period, ?dateFrom, ?dateTo)` dan
    `getOrderTrend(...)`: bila `dateFrom` terisi → `period = 'custom'`.
  - `resolveRange`: hasil `[startOfDay, endOfDay | now()]`.
  - `getDailyCashflow(storeId, Carbon $start, Carbon $end)`.
- Validasi controller (`SellerFinanceDashboardController`):
  - `period: in:daily,weekly,monthly,yearly`
  - `date_from: date`
  - `date_to: date|after_or_equal:date_from`
  - cashflow: `from_date: required|date`, `to_date: required|date|after_or_equal:from_date`.

## Perbaikan Boundary (bug yang ditemukan & diperbaiki)

1. **`EloquentSellerSettlementRepository::getByStore`** memakai `where('created_at','>=', $from_date)`
   (perbandingan string). Diubah ke `whereDate` agar `to_date` mencakup penuh hari
   (test: settlement `to_date` mencakup baris di jam akhir hari).
2. **`getCashflow` (dashboard)** dan **`HutangPiutangService::exportReport`**
   membandingkan `occurred_at <= 'Y-m-d'` → baris setelah tengah malam tanggal akhir
   terlewat. Diperbaiki dengan `Carbon::parse($toDate)->endOfDay()` dan
   `Carbon::parse($fromDate)->startOfDay()`.

## Pengujian

- `tests/Feature/Seller/DateFilterTest.php` (3): riwayat order menghormati
  `date_from`/`date_to`; dashboard rentang custom; order trend rentang custom.
- `tests/Feature/Seller/DateBoundaryFixTest.php` (2): settlement `to_date` mencakup
  baris akhir hari; cashflow `to_date` mencakup transaksi akhir hari.
- Catatan fixture: `financial_transactions.reference_number` NOT NULL (isi `TRX-...`/`INC-...`);
  `SellerSettlementModel` tidak menampung `created_at` di `$fillable` → pakai `forceCreate`
  agar timestamp kustom tersimpan.