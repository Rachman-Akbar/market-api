# market-api

Backend Laravel 11 untuk marketplace online. PHP 8.2, MySQL 8 di development, SQLite di test suite.

## Struktur

Domain-driven design dengan 9 modul di bawah `app/Domains/`:

| Domain | Fungsi |
|---|---|
| Identity | Auth, user, role, Sanctum token |
| Catalog | Produk, kategori, atribut, gambar |
| Seller | Toko seller, showcase, stok, inventory bahan baku |
| Order | Checkout, order, sub-order, cart, pembayaran |
| Voucher | Voucher diskon platform & toko |
| Engagement | Misi, notifikasi push |
| Gaming | Tiket online, showroom, voucher game |
| PPOB | Produk digital (PLN, pulsa, paket data) |
| Shared | Spreadsheet import/export, file upload, alamat |

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh
```

Butuh MySQL 8 (development). Buat database lalu set `DB_*` di `.env`.

## Menjalankan

```bash
php artisan serve
```

API prefix: `/api/v1/`. Auth pakai Sanctum (`/api/v1/auth/login`).

## Test

```bash
php artisan test
```

Semua test jalan di SQLite in-memory. Tidak perlu MySQL untuk menjalankan test.

## Code style

```bash
vendor/bin/pint --test    # cek
vendor/bin/pint            # auto-fix
```

Format otomatis mengikuti konfigurasi bawaan Laravel Pint.
