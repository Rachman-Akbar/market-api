# CHANGELOG 2026-09-01 — Invoice PPOB + Perbaikan Migrasi

Ringkasan seluruh perubahan yang dilakukan pada **market-api** hari ini. Detail teknis
fitur invoice ada di [`PPOB-INVOICE.md`](./PPOB-INVOICE.md).

## 1. Perbaikan Bug Migrasi (blokir pending migrations)

**File:** `database/migrations/2026_09_01_000001_add_payment_columns_to_ppob_transactions.php`

- Masalah: migrasi menduplikasi kolom `payment_method` / `payment_status` yang sudah
  ditambahkan oleh `2026_08_30_111456_add_payment_to_ppob_transactions_table`.
  Migrasi gagal (`duplicate column`) sehingga seluruh migrasi pending terblokir.
- Perbaikan: migrasi dijadikan **idempotent** — setiap kolom dicek dulu via
  `SHOW COLUMNS FROM ppob_transactions` sebelum ditambahkan.
- Status: ✅ `Ran` (batch 4).

## 2. Fitur Invoice PPOB (baru)

### Backend

| Artifak | Lokasi |
|---|---|
| Migrasi tabel `invoices` | `database/migrations/2026_09_01_000002_create_invoices_table.php` (✅ Ran, batch 4) |
| Model | `app/Domains/PPOB/Infrastructure/Persistence/Models/InvoiceModel.php` |
| Service (generate + email idempotent) | `app/Domains/PPOB/Application/Services/InvoiceService.php` |
| Controller | `app/Domains/PPOB/Presentation/Http/Controllers/InvoiceController.php` |
| Routes | `app/Domains/PPOB/Presentation/routes.php` |
| Mailable | `app/Domains/PPOB/Infrastructure/Mail/InvoiceCreatedMail.php` |
| Blade view email | `resources/views/emails/invoice-created.blade.php` |
| Binding DI (singleton) | `app/Domains/PPOB/PPOBServiceProvider.php` |

### Endpoint baru (Semua user-scoped, `auth:sanctum` + `active.user`)

| Method | Path | Fungsi |
|---|---|---|
| GET | `/api/v1/ppob/invoices` | Daftar invoice user (terbaru dulu, paginate) |
| GET | `/api/v1/ppob/invoices/{referenceOrId}` | Detail invoice (by invoice_number / transaction_reference / id), on-demand generate jika belum ada |

### Wiring (invoice di-generate + email di-antrikan saat transaksi sukses)

- `FinalizePpobTopUpUseCase` (finalisasi top-up)
- `PpoCallbackHandler` (callback IAK/webhook Midtrans)
- `PpoTransactionController::checkStatus`
- `PpoBillController` (postpaid payBill)

## 3. Perbaikan Keamanan

**File:** `app/Domains/PPOB/Presentation/Http/Controllers/InvoiceController.php`

- `show()`: lookup on-demand transaksi memakai `orWhere('id', ...)` yang tidak
  di-*group*, sehingga **scoping user tidak berlaku** untuk cabang `id` (transaksi
  milik user lain bisa terbaca). Diperbaiki dengan closure `where(fn) { reference_id OR id }`
  yang tetap di dalam scope `where('user_id', ...)`.

## 4. Catatan Penting untuk Maintainer

- **Kesalahan pada Order mails (pre-existing):** `OrderConfirmedMail`/`OrderShippedMail`
  dkk. memakai parameter named `htmlView:` pada `Mailables\Content`, padahal versi
  Laravel 13.x yang terpasang memakai konstruktor lama `Content($view, $html, ...)`.
  Akibatnya order email akan error `Unknown named parameter $htmlView` saat dikirim.
  Mailable invoice baru ditulis dengan `view:` yang benar dan sudah terverifikasi render.
  **Disarankan** memperbaiki Order mails untuk konsistensi.
- `Mail::fake()` di versi ini memerlukan argumen pada `Mail::fake()->sent(...)`.