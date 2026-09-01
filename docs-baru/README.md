# market-api — Dokumentasi Resmi (Baru)

Dokumentasi terbaru dan **tersinkron dengan kondisi aktual terverifikasi** (September 2026).
Versi lama disimpan terpisah di `docs-lama/`.

## Ringkasan

Backend **Laravel 13** (PHP 8.3) bermodel **Domain-Driven Design (DDD)** untuk marketplace (Ziip / MarketKu) dengan 12 domain modular. Melayani 3 portal: Buyer, Seller, Admin, plus layanan PPOB & Gaming.

## Status Kesehatan Terverifikasi

| Item | Status | Bukti |
|---|---|---|
| Test backend (`php artisan test`) | ✅ Hijau | **53 passed, 175 assertions** |
| Test frontend (`npm run test`) | ✅ Hijau | **125 passed (12 file)** |
| Frontend build (`npm run build`) | ✅ Sukses | vandal chunk ter-compile |
| Git repository | ✅ Ada | Ketiga folder (api/frontend/game) |
| CI backend | ✅ Ada | `.github/workflows/ci.yml` |
| Lint script frontend | ✅ Ada | `npm run lint` / `lint:fix` |
| Bahasa | `stable` | Laravel 13, React 18, Kotlin/Compose |

> Catatan koreksi dokumen lama: dokumen sebelumnya menyebut "7 test fail / belum git / zero tests / NOT READY". Kondisi aktual **semua test hijau**, **sudah git + CI**, dan **test suite lengkap** (backend 53, frontend 125).

## Struktur

Domain-driven design dengan 12 modul di bawah `app/Domains/`:

| Domain | Fungsi |
|---|---|
| Admin | Dashboard, store context (monitoring toko), notifikasi, manajemen toko |
| Catalog | Produk, kategori (hirarki), atribut, gambar, banner, promosi, spreadsheet |
| Identity | Auth, user, role/permission, Sanctum, register-seller |
| Order | Cart, checkout/ordering, payment (Midtrans), voucher, wishlist, alamat, review |
| Seller | Toko, showcase, stok/inventory, finance, customers, planner |
| PPOB | Produk digital (pulsa, data, token listrik, tagihan), pricing, IAK |
| Engagement | Misi (mission), game (arithmetic kilat, sudoku), reward |
| Finance | Komisi, transaksi finansial, settlement, withdrawal |
| Communication | Chat/konservasi, realtime |
| Support | Tiket helpdesk |
| Shared | Utilities lintas domain |
| Template | Legacy chart/template (hampir tidak dipakai) |

## Teknologi

- **Framework**: Laravel 13, PHP 8.3
- **Auth/RBAC**: Laravel Sanctum + Spatie laravel-permission (ability `active-role:{role}`)
- **Database**: SQLite (dev/test `:memory:`) / MySQL (produksi) — 20+ migration
- **Realtime**: Laravel Reverb (WebSocket) + Firebase messaging
- **Eksternal**: Midtrans (payment), IAK/mobilepulsa (PPOB), RajaOngkir/Komerce (ongkir), Firebase (auth Google), PhpSpreadsheet (import/export)

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

Untuk menambah DB baru: `php artisan migrate:fresh --seed`.

## Menjalankan

```bash
php artisan serve
```

Prefix API: `/api/v1/`. Auth: Sanctum (`POST /api/v1/identity/auth/password-login`).

## Test

```bash
php artisan test
```

Seluruh test berjalan di SQLite in-memory (`DB_DATABASE=:memory:`); **tidak butuh MySQL**. Status: 53 passed / 175 assertions.

## Code style

```bash
vendor/bin/pint --test   # cek
vendor/bin/pint          # auto-fix
```

## CI

`.github/workflows/ci.yml` menjalankan `composer install` → `pint --test` → `php artisan test` pada branch `main` / PR.
