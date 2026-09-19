# CHANGELOG 2026-09-16 — Komisi/Settlement, Filter Tanggal, Perbaikan Bug & Pembersihan Dependensi

Ringkasan seluruh perubahan pada proyek marketplace (API, frontend, game) sesi ini.
Detail teknis fitur: [`KOMISI-SETTLEMENT.md`](./KOMISI-SETTLEMENT.md) dan
[`FILTER-TANGGAL.md`](./FILTER-TANGGAL.md).

## 1. Komisi / Settlement Seller (wiring otomatis saat order selesai)

- **`CalculateCommissionUseCase`** di-refactor ke parameter primitif
  (`orderId, storeId, subOrderId, subOrderAmount, shippingCost, orderNumber,
  orderType, categoryId, buyerUserId`) dan dibuat **idempotent** — jika
  settlement untuk `(order_id, sub_order_id)` sudah ada, dikembalikan tanpa
  duplikat (via `SellerSettlementService::findByOrderAndSubOrder`).
- Settlement dibuat dengan nomor `STL-YYYYMMDD-XXXXXX`, status `pending`,
  `metadata` berisi `order_number` & `order_type`, `created_by` = buyer.
- **Baru** `OrderCommissionService::recordForSubOrder(subOrderId, buyerUserId)`:
  `DB::transaction` + `lockForUpdate` pada sub-order (status wajib `completed`,
  `total_items_price > 0`), menulis `admin_fee`/`seller_net` ke `sub_orders`,
  lalu menyinkronkan `orders.admin_fee`/`seller_net` = `COALESCE(SUM(...))` semua
  sub-order. Terdaftar sebagai singleton di `CommissionServiceProvider`.
- **`AutoOrderIncomeService`** kini menginjeksi `OrderCommissionService`: komisi
  dicatat **lebih dulu**, sub-order di-`refresh`, lalu pemasukan memakai
  `seller_net` bila > 0, selain itu memakai `total_items_price`. Tetap idempotent
  terhadap transaksi `income` yang sudah ada.
- **Dua jalur pemesanan terhubung**:
  - Seller: `OrderingController` jalur seller → auto income.
  - Admin: `UpdateOrderStatusUseCase` menyelesaikan sub-order → auto income
    (komisi + pemasukan) di dalam `DB::transaction`.
- **Perbaikan bug**: notifikasi status (firebase chat + email) dipindah keluar
  dari `DB::transaction` agar exception notifier tidak menggagalkan commit
  status. Nama file: `UpdateOrderStatusUseCase.php`.

## 2. Filter / Parameter Tanggal

- **Order history** (buyer & admin): `index()` menerima `date_from` / `date_to`
  yang dipetakan ke `whereDate('created_at', ...)` di `EloquentOrderRepository::paginateForUser`.
  Berlaku untuk user, customer, dan store view.
- **Analytics / dashboard penjualan**: `SellerFinanceDashboardService::getDashboard`
  dan `getOrderTrend` menerima `dateFrom` / `dateTo` opsional yang menimpa
  `period` (`daily|weekly|monthly|yearly`) → periode menjadi `custom`;
  `getDailyCashflow` menerima rentang `start` + `end`.
  Validasi di controller: `period` enum + `date_from`/`date_to`
  (`date`, `after_or_equal:date_from`).
- **Data master (katalog) tidak memakai filter tanggal** (sengaja).

## 3. Perbaikan Bug (analisis & audit)

1. **Notifier di dalam transaksi** — `UpdateOrderStatusUseCase` (lihat §1).
2. **`EloquentSellerSettlementRepository::getByStore`** membandingkan
   `created_at` dengan string tanggal via `where` (perbandingan jam 00:00 bermasalah)
   → diganti `whereDate` agar batas `from_date`/`to_date` mencakup penuh hari.
3. **Batas tanggal "sampai" menghilangkan baris akhir hari** — `getCashflow`
   (dashboard) dan `HutangPiutangService::exportReport` membandingkan
   `occurred_at <= 'YYYY-MM-DD'` sehingga baris setelah tengah malam tanggal
   `to_date` terlewat → di-fix dengan `Carbon::parse($to)->endOfDay()`.

Filter tanggal yang sudah ada dan tetap berfungsi: financial transactions
(`date_from`/`date_to` di `occurred_at`), stock movements, settlement
(`from_date`/`to_date`), hutang-piutang export (`from_date`/`to_date`).

## 4. Pembersihan Dependensi (tidak memakai package deprecated/berbahaya)

- **`market-api` (Composer)**: `composer audit` sebelumnya 36 advisories (12 paket
  rawan) → kini **0 advisories**. Update: `laravel/framework` 13.4 → **13.32**,
  `laravel/sanctum`, `guzzlehttp/*`, `symfony/routing`, `symfony/http-foundation`,
  `symfony/cache`, dan `mtdowling/jmespath.php` → **2.9.1** (mengatasi
  **CVE-2026-54133 critical** — code injection lewat dependensi `kreait/firebase`).
- **`market-frontend` (npm)**: `npm audit` 4 → **0 vulnerabilities**.
  - `postcss` (transitif via vite) dinaikkan ke **8.5.28** (mengatasi 2 high).
  - `react-router-dom` **6.30.4 → 7.18.4** (major; menangani 2 moderate advisory;
    API yang dipakai — `BrowserRouter/Routes/Route/Link/Navigate/Outlet/useNavigate/
    useLocation/useParams/useSearchParams` — tidak berubah dan build + 133 vitest
    tetap hijau).
- **`market-game` (Android)**: menghapus dependensi **deprecated** `androidx.security:security-crypto`
  (1.1.0-alpha06, diarsipkan oleh Google). `SecurePrefs` ditulis ulang memakai
  **Android Keystore AES-256-GCM** (`javax.crypto` + `KeyGenParameterSpec`) dengan
  API `SharedPreferences` yang sama + fallback plain-prefs.

## 5. Pengujian

- **Backend**: suite penuh **82 passed / 319 assertions**.
  - Baru: `OrderCommissionTest` (3), `DateFilterTest` (3), `DateBoundaryFixTest` (2).
  - Regresi yang dijaga: `OrderCompletionIncomeTest`, `PoStockSplitTest` (cancel).
- **Frontend**: **133 vitest pass** + `vite build` sukses + eslint 0 error.
- **Game**: `:app:compileDebugKotlin` & `:app:testDebugUnitTest` BUILD SUCCESSFUL
  (JDK: Temurin 17 via `JAVA_HOME=/tmp/opencode/jdk17` — kompatibel dengan AGP 8.13 +
  jvmTarget 11; JDK 25 gagal).
- **Regression API**: server di-restart; sort katalog (10 kunci × 2 halaman) 200,
  endpoint katalog 200.

## 6. Dokumentasi

- `RINCIAN FITUR.MD` diisi ulang (sebelumnya kosong) sesuai kondisi aktual 4 materi.
- Tambahan dokumen fitur: `KOMISI-SETTLEMENT.md`, `FILTER-TANGGAL.md`.
- `diagnosis.md` ditambah addendum kondisi 16 Sep 2026.

