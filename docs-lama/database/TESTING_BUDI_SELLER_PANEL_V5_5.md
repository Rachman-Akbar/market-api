# Budi Seller Panel Testing V5.5

Seeder khusus `BudiSellerPanelSeeder` mengisi data testing yang seluruhnya terhubung ke toko milik `budi@gmail.com`.

Credential testing:

- Email: `budi@gmail.com`
- Password: `12345678`
- Role aktif Seller: `seller`
- Store slug: `budi-marketplace-lab`

Data utama yang dibuat:

- 100 buyer testing yang masing-masing pernah bertransaksi di toko Budi.
- 100 produk Budi dengan variant, gambar, kategori, status draft/published/archived, aktif/nonaktif, stok normal/menipis/habis.
- 140 order toko Budi. 100 order completed untuk review dan 40 order tambahan dengan status pending, processing, shipped, cancelled.
- 100 product review/rating yang valid terhadap order item.
- 100 banner toko.
- 100 voucher toko.
- 100 promotion dan 100 promotion payment bila tabel tersedia.
- 100 bahan baku, histori restock, HPP, dan komposisi material produk.
- Histori stock produk dari restock dan transaksi order.
- 100 transaksi finance dengan income, expense, receivable, payable dan histori cicilan untuk hutang/piutang.
- 100 etalase dengan 5 produk per etalase.
- 100 Help ticket dengan percakapan ticket.
- 100 conversation buyer-seller dengan pesan chat.
- 100 notifikasi aktivitas Budi bila tabel notifikasi tersedia.

Seeder bersifat idempotent untuk data dengan prefix `BUDI-TEST-*`, `BUDITEST*`, `BUDI-RM-*`, dan `budi-test-*`. Menjalankan ulang seeder akan membentuk ulang data testing Budi tanpa menghapus transaksi manual yang tidak memakai prefix tersebut.

Jalankan seluruh seed:

```powershell
php artisan optimize:clear
php artisan migrate
php artisan db:seed
```

Atau hanya data Seller Panel Budi:

```powershell
php artisan db:seed --class=Database\\Seeders\\BudiSellerPanelSeeder
```
