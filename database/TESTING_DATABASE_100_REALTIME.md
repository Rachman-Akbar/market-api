# Database Testing 100 Realtime

Seeder utama untuk volume data testing adalah `Database\\Seeders\\ComprehensiveRealtimeSeeder`.

Target default adalah minimal 100 record pada tabel aplikasi yang digunakan Seller, Buyer, dan Admin. `MarketplaceFakerSeeder` juga dipaksa minimum 100 agar data master dan relasi dasar tidak turun walaupun environment mengisi nilai yang lebih kecil.

Product rating dan review dibuat sebanyak 100 record aktif dan terhubung langsung ke `orders`, `sub_orders`, `order_items`, `products`, dan buyer. Order yang memiliki review dibuat berstatus `completed` dan `paid`, sehingga data review sesuai alur aplikasi. Distribusi rating menggunakan nilai 1 sampai 5 dengan mayoritas 4 sampai 5. Timestamp menggunakan waktu relatif terhadap `now()` saat seeder dijalankan.

Data realtime tambahan menggunakan prefix `RT-` atau nama `Realtime`. Seeder membersihkan data buatannya sendiri sebelum membuat ulang sehingga aman untuk pengujian berulang dan timestamp akan ikut diperbarui.

Tabel runtime framework tidak diisi data palsu: `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`, `password_reset_tokens`, dan `personal_access_tokens`. Tabel tersebut dikelola Laravel saat aplikasi berjalan dan pengisian 100 record palsu dapat mengganggu session, queue, cache, reset password, dan autentikasi.

Untuk database baru jalankan:

```bash
php artisan migrate:fresh --seed
```

Untuk mengulang data testing tanpa menghapus schema:

```bash
php artisan db:seed
```

Untuk menjalankan hanya volume 100 realtime:

```bash
php artisan db:seed --class=Database\\Seeders\\ComprehensiveRealtimeSeeder
```

Password seluruh akun testing tetap `12345678`.

File `database/sql/verify_realtime_100_counts.sql` dapat dijalankan setelah seeding untuk melihat jumlah record seluruh tabel aplikasi yang ditargetkan.
