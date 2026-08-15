# Database Testing Ziip Marketplace

Password testing seluruh akun seed dan seluruh akun yang sudah ada ketika migration dijalankan adalah `12345678`.

Migration `2026_08_13_155500_set_existing_users_testing_password.php` hanya mengganti nilai password satu kali ketika migrate dijalankan. Migration ini tidak menambah default, trigger, constraint, atau aturan database yang memaksa password tersebut. Setelah migration selesai, password user tetap dapat diganti melalui aplikasi menjadi password lain. Password testing `12345678` terdiri dari 8 karakter dan sesuai dengan aturan registrasi normal aplikasi yang mensyaratkan minimal 8 karakter.

Seeder utama menjalankan `RealtimeMarketplaceSeeder` paling akhir. Seeder ini menyegarkan timeline data testing berdasarkan `now()` saat seeder dijalankan, termasuk pesanan, pembayaran, keuangan, stok, chat, dan notifikasi admin.

Akun utama:

- `budi@gmail.com` / `12345678` — role `super_admin`, `admin`, `seller`, dan `buyer`; toko testing aktif `budi marketplace lab`
- `rina.admin@gmail.com` / `12345678`
- `dimas.admin@gmail.com` / `12345678`
- `sari@gmail.com` / `12345678`
- `raka@gmail.com` / `12345678`
- `andi@gmail.com` / `12345678`

Akun Faker mengikuti pola:

- `faker.seller.001@marketku.test` sampai jumlah `MARKETPLACE_FAKER_COUNT`
- `faker.buyer.001@marketku.test` sampai jumlah `MARKETPLACE_FAKER_COUNT`

Semua akun Faker menggunakan password `12345678`.

Untuk database baru gunakan:

```bash
php artisan migrate:fresh --seed
```

Untuk database yang sudah mempunyai user dan hanya ingin menjalankan migration baru:

```bash
php artisan migrate
```

Setelah migration dijalankan, seluruh user yang sudah ada dapat login menggunakan `12345678` sampai password masing-masing diubah lagi.

## Koreksi password testing V4.1

Untuk database yang sudah pernah menjalankan migration V4 sebelumnya, migration `2026_08_13_162000_correct_existing_users_testing_password.php` akan mengganti password seluruh user yang sudah ada menjadi `12345678` satu kali saat `php artisan migrate` dijalankan. Migration ini tidak menambahkan default, constraint, trigger, atau kewajiban password pada tabel.
