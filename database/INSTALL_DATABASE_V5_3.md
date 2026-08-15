# Database V5.3

Untuk database existing yang sebelumnya gagal pada migration `2026_08_13_130000_add_installment_inventory_costing`, timpa seluruh folder `database` dengan versi ini.

Jalankan:

```bash
php artisan optimize:clear
php artisan migrate
php artisan db:seed
```

Migration `2026_08_13_130000_add_installment_inventory_costing` sekarang dapat melanjutkan kondisi partial schema. Jika `financial_payment_histories` sudah ada, tabel tersebut dilewati dan migration tetap melanjutkan pembuatan `raw_materials`, `raw_material_stock_movements`, `product_costings`, dan `product_materials`.

Seeder inventory memeriksa keberadaan tabel terlebih dahulu sehingga tidak menjalankan query ke tabel yang belum tersedia.
