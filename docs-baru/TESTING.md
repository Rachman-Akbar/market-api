# Pengujian — market-api

Status terverifikasi pada **September 2026**.

## Menjalankan seluruh test

```bash
php artisan test
```

**Hasil: 53 passed / 175 assertions (hijau).** Semua berjalan di SQLite in-memory; tidak butuh MySQL.

## Cakupan test

| Kelompok | File | Fokus |
|---|---|---|
| Auth | `AuthTest`, `EmailVerificationTest` | register, login, me, forgot/reset password, verifikasi email, google user |
| Catalog | `CategoryCrudTest`, `ProductCrudTest` | CRUD kategori & produk, autorisasi role |
| Gaming | `GameReportIntegrationTest` | report skor arithmetic & sudoku, server-side recompute, dedupe, cap |
| Order | `OrderFlowTest`, `PoStockSplitTest`, `VoucherCrudTest` | alur order, split stok regular/preorder, CRUD & claim voucher |
| PPOB | `PlacePpoOrderIntegrationTest` | alur order PPOB lengkap (pricing + finance) |
| Seller | `StoreCrudTest` | register toko, grant role seller, update toko |
| Spreadsheet | `SpreadsheetTransferTest` | template, export, import preview, blokir buyer |

## Grup khusus

Test integration PPOB & Gaming menjalankan service nyata namun tetap diarahkan ke SQLite pada suite utama. Tidak perlu `--group` khusus; seluruh suite hijau tanpa konfigurasi tambahan.

## Frontend (lintas-repo)

Test frontend: `npm run test` → **125 passed (12 file)**. Build: `npm run build` sukses.
