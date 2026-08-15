# Database V5.6

Gunakan migration Laravel sebagai sumber schema utama.

Untuk database existing:

```powershell
php artisan optimize:clear
php artisan migrate
php artisan db:seed --class=Database\Seeders\BudiSellerPanelSeeder
```

Untuk database baru:

```powershell
php artisan migrate:fresh --seed
```

Perubahan V5.6:

- Menambahkan `raw_material_cost_histories`.
- Menambahkan `product_costing_impacts`.
- Restock bahan baku menghitung weighted average cost.
- Perubahan average cost menyegarkan HPP produk terkait dan membuat histori dampak.
- HPP mengambil biaya bahan baku dari database, bukan input biaya manual di form HPP.
- Penambahan stok produk melalui Persediaan memakai resep `product_materials` untuk mengurangi bahan baku secara atomik.
- Product dan import Product tidak menjadi jalur perubahan saldo stok.
- Import/export Bahan Baku, Stok Bahan Baku, HPP, Stok Produk, Finance tersedia sebagai modul terpisah.
- Pelanggan dan Review bersifat export-only karena datanya berasal dari transaksi aktual.

Fallback SQL untuk dua tabel laporan tersedia di `database/sql/upgrade_cost_hpp_report_v5_6.sql`.
