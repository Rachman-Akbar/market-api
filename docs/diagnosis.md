# Diagnosis Proyek Marketplace — Status Fitur & To-Do List

> Tanggal analisis: 12 September 2026
> Metode: analisis statis kode (routes, controllers, halaman, test) tanpa mengubah file.
> Lingkup: `market-api` (Laravel/DDD), `market-frontend` (React SPA), `market-game` (Android Kotlin/Compose).
> Acuan materi: `market-api/docs/RINCIAN FITUR.MD` (4 materi: BUYER, SELLER, ADMIN, GAME).

---

## 1. Ringkasan eksekutif

Proyek marketplace terdiri dari 3 sub-proyek yang saling terhubung:

| Sub-proyek | Stack | Ukuran | Status umum |
|---|---|---|---|
| `market-api` | Laravel + Sanctum, DDD modular (`app/Domains/**`) | ±245 endpoint API / 12 domain bisnis / 71 tabel | **Sangat lengkap**, nyaris semua fitur diimplementasikan |
| `market-frontend` | React 18 + Vite + Tailwind + React Query | ±300+ file sumber, 3 portal (Buyer/Seller/Admin) | **Lengkap**, mayoritas halaman aktif terhubung ke API |
| `market-game` | Kotlin + Jetpack Compose (Android) | 7 game, mission integration, auth | **Lengkap untuk gameplay**, masih ada fitur "bonus" yang belum |

Rata-rata tingkat penyelesaian terhadap 4 materi:

- **BUYER**: ±95% selesai
- **SELLER**: ±90% selesai (beberapa halaman masih *placeholder* sisi frontend)
- **ADMIN**: ±95% selesai
- **GAME**: ±80% selesai (backend integration sudah penuh; fitur *non-core* game belum)

Tidak ditemukan endpoint yang di-stub, route yang di-comment-out, atau marker "not implemented" di API. Satu-satunya `TODO` di kode API berada di `SpreadsheetTransferController.php:841` (penyamaan logika voucher) dan modul referensi `app/Domains/Template/Cart/**` yang lengkap tapi **tidak terdaftar** di `DomainServiceProvider` (mati/tidak aktif).

---

## 2. Status fitur per materi

### 2.1 MATERI BUYER

| No | Fitur (acuan RINCIAN) | Status | Bukti / Catatan |
|---|---|---|---|
| 1 | List store, promotion, banner, product, katalog group, kategori, voucher | ✅ Selesai | Endpoint publik `catalog/*`, `order/vouchers` + halaman Home/Search/Category/Product/Stores/Promotion di frontend |
| 2 | Add to cart, wishlist | ✅ Selesai | `order/carts*`, `order/wishlist`; UI Cart & Wishlist tab aktif |
| 3 | Create order + produk digital (pulsa/WiFi/dll → PPOB) | ✅ Selesai | `order/orderings*` + domain `PPOB` (pulsa, data, token listrik, tagihan, internet, voucher) dengan receipt & Midtrans webhook |
| 4 | Chat seller | ✅ Selesai | `communication/conversations*`; RealtimeChatPage (Echo + polling fallback) |
| 5 | Use voucher | ✅ Selesai | Voucher diterapkan di checkout; `order/vouchers/mine`, `/claim` |
| 6 | Help admin (tiket bantuan) | ✅ Selesai | `support/tickets*`; BuyerHelpPage |

**Fitur tambahan yang sudah ada** (di luar acuan): auth password + Google/Firebase, verifikasi email, forgot/reset password, ubah password, switch role, hapus akun, profil (biodata/alamat/keamanan manajemen sesi), checkout lengkap (alamat + peta Leaflet + resolusi ongkir + payment: Midtrans/COD/transfer manual/cash toko), histori order & konfirmasi diterima, review produk, notifikasi, riwayat pembayaran gabungan (order + PPOB), halaman missions/games progres.

**Belum / catatan:**
- ⚠️ `/profile/orders`, `/profile/wishlist`, `/orders/:id` hanya *redirect* ke hub tab `/cart` (fitur tetap berfungsi, bukan halaman mandiri).
- ⚠️ `register-seller` tersedia tapi alur *upgrade buyer → seller* sepenuhnya diarahkan via role-switch/onboarding.

### 2.2 MATERI SELLER

| No | Fitur (acuan RINCIAN) | Status | Bukti / Catatan |
|---|---|---|---|
| 1 | CRUD bahan baku | ✅ Selesai | `seller/inventory/materials*`, model `raw_materials`, restock produksi + konsumsi otomatis bahan baku |
| 2 | Transaksi (+ riwayat) | ✅ Selesai | `seller/finance*` + histori, `seller/orders`, finance dashboard/cashflow |
| 3 | HPP (+ riwayat) | ✅ Selesai | `product/{productId}/costing`, `product_costings`, `cost-impact`, `raw-material-cost...`, laporan HPP |
| 4 | Voucher (seller scope) | ✅ Selesai | `order/vouchers/seller/*`; SellerVoucherPage |
| 5 | Promotion (seller scope) | ✅ Selesai | `/seller/promotions` + alur approval admin + payment promotion |
| 6 | Help admin | ✅ Selesai | `support/tickets`; HelpPage seller |
| 3b | Chat | ✅ Selesai | RealtimeChatPage seller |

**Fitur tambahan seller:** dashboard metrik + funnel, manajemen produk (varian, stok PO/pre-order split, gambar, HPP), stock movement/adjustment, etalase/showcase (drag-drop), banner, pelanggan (customer list + export), review, planner/kalender produksi, operasional order (bulk status + spreadsheet), utang-piutang + pencatatan cicilan, dan mesin import/export spreadsheet 16 modul.

**Belum / catatan:**
- ⚠️ 4 halaman sidebar seller masih **placeholder** "Frontend ready, waiting for API": `/seller/categories`, `/seller/catalog-groups`, `/seller/users`, `/seller/store-management`. Padahal API kategori & katalog-group **sudah ada** untuk admin. Berarti butuh: (a) endpoint CRUD kategori/catalog-group/anggota toko yang di-scope ke seller, atau (b) mengarahkan halaman tsb ke endpoint admin yang sudah ada.
- ⚠️ Tidak ada halaman khusus "laporan HPP visual" (HPP tersaji via tab costing/stock, belum ada halaman laporan mandiri).

### 2.3 MATERI ADMIN

| No | Fitur (acuan RINCIAN) | Status | Bukti / Catatan |
|---|---|---|---|
| 1 | CRUD kategori, katalog group, promotion, voucher, user, daily mission | ✅ Selesai | `identity/users*`, `/identity/roles`, `catalog/categories*`, `catalog/catalog-groups*`, `admin/*promotions`, `order/vouchers/admin/*`, `engagement/missions` CRUD + event types + report. MissionsPage admin (Games) aktif |
| 2 | Manajemen/monitoring store, product, dll (banned/approved) | ✅ Selesai | `admin/stores/context/*`, `seller/admin/stores*` (status aktif/suspend), `catalog/admin/products*`, `promotions/{id}/approve|reject`, `promotion-payments` approve/reject, notification admin |
| 3 | Help admin (kelola tiket) | ✅ Selesai | `support/tickets` (updateStatus admin-only, reply internal, delete admin-only) |
| 4 | Chat (announcement & ikut percakapan) | ✅ Selesai | `communication/announcements` (broadcast per role/user) + RealtimeChatPage admin |

**Fitur tambahan admin:** dashboard statistik/order-trend/top-stores, "Monitoring Toko" per store (stats, orders, products, settlements), PPOB admin penuh (produk, operator, pricing rules, balance/finance), RBAC (roles/permissions CRUD), fee/komisi, settlement & withdrawal approval, review management, planner, spreadsheet, dan notifikasi realtime.

**Belum / catatan:**
- ⚠️ 2 menu admin *placeholder*: `/admin/store-information` & `/admin/store-preview` — keduanya hanya tombol navigasi ke halaman nyata (`/admin/stores` & `/stores`), bukan modul yang hilang secara fungsional.
- ⚠️ **Tidak ada dashboard "daily mission" khusus admin selain CRUD** (progres harian user dilihat per-user; tidak ada agregasi "siapa yang menyelesaikan hari ini"). Bisa jadi perbaikan kecil.

### 2.4 MATERI GAME

| No | Fitur (acuan RINCIAN) | Status | Bukti / Catatan |
|---|---|---|---|
| 1 | Get voucher setelah menyelesaikan misi | ✅ Selesai (end-to-end) | API: misi dengan `voucher_id`, event-report engine `engagement/missions/report` → otomatis insert ke `user_vouchers` + status `rewarded`. Aplikasi: semua game melapor hasil → voucher bisa diklaim/dilihat di `VouchersViewModel` & halaman BuyerMissionsPage |
| 2 | Games (Arithmetic, Match Card) | ✅ Selesai (7 game) | Backend: `engagement/games/*` (server-trusted scoring, anti-cheat, history/stats/leaderboard) untuk `arithmetic_kilat` & `sudoku`. Android: 7 game — **SDG Quiz Battle, Sortir Sampah (Trash Sort), SDG Match (match_card), Clean River, Myth or Fact, Arithmetic Kilat, Sudoku** |

**Rincian integrasi game → backend (sudah aktif):**
- Quiz/TrashSort/MatchCard/CleanRiver/MythFact → `POST /engagement/missions/report`
- Arithmetic Kilat & Sudoku → `POST /engagement/games/report` (dengan `session_id`, `duration`, anti-cheat: recompute skor, tolak duplikat, validasi durasi)
- Auth (register/login/me), catalog, cart, orders, `vouchers/mine`, `vouchers/{id}/claim` sudah terintegrasi di Android
- 5 tab UI (Home/Actions/Game/Tree/Profile), onboarding, splash, hero tree + XP/level, achievement gallery, edukasi 17 SDG

**Belum (fitur pendukung game, bukan materi wajib):**
- ⚠️ Suara/SFX **& haptic** (0 resource audio, tidak ada SoundManager/Vibrator) — meski `SFX_IMPLEMENTATION_GUIDE.md` & `REALTIME_AND_SFX_CONCERNS.md` ada.
- ⚠️ Persistensi lokal progres & unlock difficulty (tidak pakai Room/DataStore untuk progres game).
- ⚠️ Tutorial per-game, anti-cheat lokal/premier cap, analytics, opsi aksesibilitas.
- ⚠️ Leaderboard tidak ada UI di Android (endpoint `games/{type}/leaderboard` sudah ada di API).
- ⚠️ Websocket/realtime **tidak ada di Android** (chat realtime hanya di web frontend).
- ⚠️ Multiplayer, battle pass, seasonal event, social sharing, push notification — belum.
- ⚠️ Card "Clean River" di game hub sengaja di-comment-out (screens & route tetap ada tapi tidak bisa dibuka dari hub).
- ⚠️ Belum ada test otomatis Android (contoh test masih placeholder).

---

## 3. Temuan lintas modul (penting)

1. **Kesenjangan halaman placeholder vs API**: semua *placeholder* frontend ("waiting for API") sebenarnya punya API yang sudah jalan — hanya perlu endpoint scope-seller/penyesuaian atau navigasi. Ini sumber backlog terbesar di sisi frontend.
2. **Test coverage backend bagus tapi tidak lengkap**: 11 file feature test ±50 kasus. Belum ada test untuk: Admin dashboard/notification, Seller Finance/Inventory/Planner/Showcase/Customers, Support tickets, Chat, Commission/settlement, Cart/Wishlist/Addresses/Review, Role/permission admin.
3. **Test coverage frontend tipis**: 14 file test, semua menguji util & 2 komponen form. Tidak ada test halaman/integrasi/hook/hooks realtime.
4. **Game tanpa test otomatis**.
5. **Refactor kecil bertanda**: middleware alias `role`/`active.role`/`verified.email` terdaftar ganda (`bootstrap/app.php` + `IdentityServiceProvider::boot()`); `AppServiceProvider` kosong dengan komentar observer pindah; `Template/Cart` mati.
6. **Debug leftover**: `routes/web.php` jmemuat halaman uji `/test-firebase-login`, `/test-map`, `/test-checkout`.
7. **Firebase login** hard-fail saat `FIREBASE_CREDENTIALS` tidak dikonfigurasi.

---

## 4. TO-DO LIST (prioritas)

### PRIORITAS TINGGI

**GAME**
- [ ] Tambahkan SFX + haptic (sesuai `SFX_IMPLEMENTATION_GUIDE.md`).
- [ ] Aktifkan kembali card "Clean River" di game hub.
- [ ] Tambahkan UI leaderboard (API `engagement/games/{type}/leaderboard` sudah siap).
- [ ] Tambah test otomatis dasar (unit: generator aritmatika/sudoku; UI Compose dasar).
- [ ] Persistensi progres lokal & unlock difficulty berbasis XP/level.

**BUYER**
- [ ] Jadikan `/profile/orders` & `/profile/wishlist` halaman mandiri (bukan redirect).
- [ ] Test integration untuk alur cart → voucher → checkout → payment (Midtrans sandbox).

**SELLER**
- [ ] Implementasikan 4 halaman placeholder: categories, catalog-groups, users, store-management (API kategori/catalog-group sudah ada — buat versi scope-seller atau arahkan ke endpoint yang sudah ada).
- [ ] Test untuk alur HPP + restock produksi (konsumsi bahan baku).

**ADMIN**
- [ ] Tambah agregasi "daily mission" (siapa menyelesaikan misi hari ini) di admin.
- [ ] Test untuk dashboard admin, store-context, approval store & promotion.

### PRIORITAS SEDANG

- [ ] Lengkapi test feature backend: Chat, Support, Finance/Inventory Seller, Commission/Settlement, Cart/Wishlist/Review.
- [ ] Tambah test halaman/integrasi frontend (AuthContext, checkout, panel admin).
- [ ] Hapus rapi modul `app/Domains/Template/Cart/**` (tidak terdaftar) atau daftarkan sebagai referensi resmi.
- [ ] Bersihkan duplikasi registrasi middleware alias.
- [ ] Pindahkan komentar observer `AppServiceProvider` → `IdentityServiceProvider::boot()`.
- [ ] Hapus/sembunyikan halaman debug web (`test-firebase-login`, `test-map`, `test-checkout`).

### PRIORITAS RENDAH / NICE-TO-HAVE

**GAME**
- [ ] Tutorial per game, anti-cheat lokal, analitik.
- [ ] Multiplayer, battle pass, seasonal event, social sharing.
- [ ] WebSocket realtime (chat/notifikasi) di Android.

**BUYER/SELLER/ADMIN**
- [ ] Halaman laporan HPP mandiri untuk seller.
- [ ] Push notification (burnt Through Firebase/REverb) untuk event penting (order baru, payment).

---

## 5. Addendum — 16 September 2026

Perubahan signifikan sejak analisis awal (12 Sep) yang memperbarui sebagian temuan di atas:

### Fitur & perbaikan baru
- **Komisi/settlement seller ter-wire otomatis** saat sub-order selesai (jalur seller & admin)
  — idempotent, sinkron `admin_fee`/`seller_net` ke `sub_orders` dan `orders`,
  pemasukan memakai `seller_net`. Detail: [`KOMISI-SETTLEMENT.md`](./KOMISI-SETTLEMENT.md).
  → Menutup celah "Belum ada test untuk Commission/settlement".
- **Filter tanggal** pada riwayat order (buyer/admin) + dashboard penjualan (custom range),
  cashflow, order trend; perbaikan boundary `to_date` di settlement, cashflow, dan export
  hutang-piutang (baris akhir hari kini tercakup). Detail: [`FILTER-TANGGAL.md`](./FILTER-TANGGAL.md).
- Perbaikan bug: notifier status dipindah keluar dari `DB::transaction` di
  `UpdateOrderStatusUseCase`; `EloquentSellerSettlementRepository::getByStore` memakai
  `whereDate`; cashflow/export pakai `startOfDay()`/`endOfDay()`.

### Pengujian
- Backend: **82 passed / 319 assertions** (sebelumnya ±50 kasus / 11 file).
  File baru: `OrderCommissionTest`, `DateFilterTest`, `DateBoundaryFixTest`.
- Frontend: **133 vitest pass**, `vite build` OK, eslint 0 error.
- Game: `compileDebugKotlin` + `testDebugUnitTest` BUILD SUCCESSFUL (JDK 17 Temurin;
  JDK 25 gagal — kebutuhan akan JDK 17 untuk AGP 8.13).
- Endpoint: ±303 route API (`php artisan route:list`) — sebelumnya estimasi ±245.

### Ketergantungan / keamanan
- `composer audit`: 36 advisories → **0** (laravel 13.32, symfony fix, `jmespath 2.9.1`
  menutup **CVE-2026-54133 critical**).
- `npm audit`: 4 → **0** (postcss 8.5.28; `react-router-dom` 6.30.4 → **7.18.4** major).
- Game: `androidx.security:security-crypto` (deprecated) diganti **Android Keystore AES-256-GCM**
  di `SecurePrefs`.

### Status placeholder / TODO yang TIDAK berubah
- 4 halaman sidebar seller placeholder (categories/catalog-groups/users/store-management), laporan HPP visual, Clean River card di game hub, SFX/haptic, leaderboard UI Android, test Android — status tetap seperti §2/§4.

### Catatan dokumentasi
- `RINCIAN FITUR.MD` yang menjadi acuan materi **telah diisi ulang** (sebelumnya kosong 0 byte).

*Dokumen ini sebelumnya dibuat otomatis dari analisis kode tanpa mengubah file proyek; addendum ini ditulis manual sesuai hasil kerja sesi.*