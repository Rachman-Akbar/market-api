# Seeder & Data Demo — market-api

Ringkasan seeder yang tersedia (diarsipkan dari instruksi `docs-lama/database/*`).

## Cara mengisi data

Database baru (schema + seed):

```bash
php artisan migrate:fresh --seed
```

Database existing (hanya migration + seed):

```bash
php artisan optimize:clear
php artisan migrate
php artisan db:seed
```

Seeder satu kelas:

```bash
php artisan db:seed --class=Database\Seeders\BudiSellerPanelSeeder
php artisan db:seed --class=Database\Seeders\ComprehensiveRealtimeSeeder
php artisan db:seed --class=Database\Seeders\AkbarFahlevySellerSeeder
```

## Seeder utama

| Seeder | Isi |
|---|---|
| `MarketAkbarSeeder` | Seller "Ahmad Market Akbar" |
| `BudiSellerPanelSeeder` | Seller Budi + data panel lengkap (100+ record) |
| `AkbarFahlevySellerSeeder` | Seller akbarfahlevy39, toko "Akbar Fahlevy Store", 16 produk, 15 order, finance, stok, jadwal |
| `ComprehensiveRealtimeSeeder` | Volume realtime minimal 100 record per tabel aplikasi |
| `RealtimeMarketplaceSeeder` | Menyegarkan timeline data testing relatif `now()` |
| `InventoryCostingFinanceSeeder` | Master bahan baku, HPP, finance |
| `AdvancedMarketplaceSeeder` | Data advanced marketplace |
| `RolePermissionSeeder` | Role & permission dasar |
| `AdminSeeder`, `CatalogSeeder`, `ProductSeeder`, `MarketingSeeder`, `MarketplaceFakerSeeder` | Data master & demo |

Hampir semua seeder bersifat **idempotent & additive** (tidak menghapus data luar).

## Akun demo

Password testing umum: `12345678`

- `budi@gmail.com` — super_admin, admin, seller, buyer; toko `budi marketplace lab`
- `rina.admin@gmail.com`, `dimas.admin@gmail.com` — admin
- `sari@gmail.com`, `raka@gmail.com`, `andi@gmail.com` — buyer
- `akbarfahlevy39@gmail.com` / `123` — seller+buyer, "Akbar Fahlevy Store"

Akun faker: `faker.seller.001@marketku.test`, `faker.buyer.001@marketku.test` (+ nomor sampai `MARKETPLACE_FAKER_COUNT`).

> Catatan: instruksi rinci versi lama (V5.2 / V5.3 / V5.6, repo repair SQL) tersimpan di `docs-lama/database/`.
