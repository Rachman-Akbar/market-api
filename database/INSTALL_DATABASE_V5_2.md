# Database V5.2

Paket ini memperbaiki database existing yang belum mempunyai tabel inventory/costing seperti `raw_materials`.

## Database existing

Timpa folder `database/`, kemudian jalankan:

```bash
php artisan optimize:clear
php artisan migrate
php artisan db:seed
```

Migration repair `2026_08_15_100000_ensure_inventory_finance_costing_tables.php` bersifat idempotent dan tidak menghapus data lama.

## Database baru

```bash
php artisan migrate:fresh --seed
```

## Jika migration Artisan tidak dapat dijalankan

Import file berikut setelah tabel dasar marketplace tersedia:

`database/sql/repair_inventory_finance_costing_v5_2.sql`

Lalu jalankan:

```bash
php artisan db:seed
```

## Tabel yang dipastikan tersedia

- `financial_payment_histories`
- `raw_materials`
- `raw_material_stock_movements`
- `product_costings`
- `product_materials`

Password akun testing tetap `12345678`.
