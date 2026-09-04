# CHANGELOG 2026-09-01 — Bukti Pembayaran (Receipt) PPOB + Perbaikan Migrasi

Ringkasan seluruh perubahan yang dilakukan pada **market-api** hari ini. Detail teknis
fitur bukti pembayaran ada di [`PPOB-RECEIPT.md`](./PPOB-RECEIPT.md).

> **Update refactor:** Fitur yang sebelumnya bernama "Invoice PPOB" telah di-refactor
> total menjadi **"Bukti Pembayaran (Receipt)"** — lihat bagian 5. Dokumentasi lama
> `PPOB-INVOICE.md` diganti `PPOB-RECEIPT.md`.

## 1. Perbaikan Bug Migrasi (blokir pending migrations)

**File:** `database/migrations/2026_09_01_000001_add_payment_columns_to_ppob_transactions.php`

- Masalah: migrasi menduplikasi kolom `payment_method` / `payment_status` yang sudah
  ditambahkan oleh `2026_08_30_111456_add_payment_to_ppob_transactions_table`.
  Migrasi gagal (`duplicate column`) sehingga seluruh migrasi pending terblokir.
- Perbaikan: migrasi dijadikan **idempotent** — setiap kolom dicek dulu via
  `SHOW COLUMNS FROM ppob_transactions` sebelum ditambahkan.
- Status: ✅ `Ran` (batch 4).

## 2. Fitur Bukti Pembayaran (Receipt) PPOB

### Backend

| Artifak | Lokasi |
|---|---|
| Migrasi tabel `receipts` | `database/migrations/2026_09_01_000002_create_receipts_table.php` |
| Model | `app/Domains/PPOB/Infrastructure/Persistence/Models/ReceiptModel.php` |
| Service (generate + email idempotent) | `app/Domains/PPOB/Application/Services/ReceiptService.php` |
| Controller | `app/Domains/PPOB/Presentation/Http/Controllers/ReceiptController.php` |
| Routes | `app/Domains/PPOB/Presentation/routes.php` |
| Mailable digital | `app/Domains/PPOB/Infrastructure/Mail/PaymentReceiptMail.php` |
| Mailable order | `app/Domains/PPOB/Infrastructure/Mail/ReceiptMail.php` |
| Blade view email digital | `resources/views/emails/payment-receipt.blade.php` |
| Blade view email order | `resources/views/emails/receipt-created.blade.php` |
| Binding DI (singleton) | `app/Domains/PPOB/PPOBServiceProvider.php` |

### Endpoint baru (Semua user-scoped, `auth:sanctum` + `active.user`)

| Method | Path | Fungsi |
|---|---|---|
| GET | `/api/v1/ppob/receipts` | Daftar bukti pembayaran user (terbaru dulu, paginate; filter `type`) |
| GET | `/api/v1/ppob/receipts/history` | Riwayat transaksi terpadu (PPOB + order marketplace) |
| GET | `/api/v1/ppob/receipts/{referenceOrId}` | Detail bukti pembayaran (by receipt_number / transaction_reference / id), on-demand generate jika belum ada |
| POST | `/api/v1/ppob/receipts/{referenceOrId}/send-email` | Kirim email bukti pembayaran ke email user (idempotent) |

### Wiring (receipt di-generate + email dikirim saat transaksi sukses)

- `FinalizePpobTopUpUseCase` (finalisasi top-up)
- `PpoCallbackHandler` (callback IAK/webhook Midtrans)
- `PpoTransactionController::checkStatus`
- `PpoBillController` (postpaid pay)

## 3. Perbaikan Keamanan

**File:** `app/Domains/PPOB/Presentation/Http/Controllers/ReceiptController.php`

- `show()`: lookup on-demand transaksi memakai `orWhere('id', ...)` yang tidak
  di-*group*, sehingga **scoping user tidak berlaku** untuk cabang `id` (transaksi
  milik user lain bisa terbaca). Diperbaiki dengan closure `where(fn) { reference_id OR id }`
  yang tetap di dalam scope `where('user_id', ...)`.

## 4. Catatan Penting untuk Maintainer

- **Kesalahan pada Order mails (pre-existing):** `OrderConfirmedMail`/`OrderShippedMail`
  dkk. memakai parameter named `htmlView:` pada `Mailables\Content`, padahal versi
  Laravel 13.x yang terpasang memakai konstruktor lama `Content($view, $html, ...)`.
  Akibatnya order email akan error `Unknown named parameter $htmlView` saat dikirim.
  Mailable receipt baru ditulis dengan `view:` yang benar dan sudah terverifikasi render.
  **Disarankan** memperbaiki Order mails untuk konsistensi.
- `Mail::fake()` di versi ini memerlukan argumen pada `Mail::fake()->sent(...)`.

## 5. Refactor: Invoice → Bukti Pembayaran (Receipt)

Konsep lama "invoice" di-refactor total menjadi **receipt / bukti pembayaran**,
karena pembayaran sudah terjadi langsung (bukan penagihan di muka).

### Pemetaan rename

| Sebelum (invoice) | Sesudah (receipt) |
|---|---|
| Tabel `invoices` | Tabel `receipts` |
| `InvoiceModel` | `ReceiptModel` |
| `InvoiceService` | `ReceiptService` |
| `InvoiceController` | `ReceiptController` |
| `InvoiceCreatedMail` (`invoice-created.blade.php`) | `ReceiptMail` (`receipt-created.blade.php`, order) |
| `PaymentReceiptMail` (`payment-receipt.blade.php`) | tetap (digital) |
| Kolom `invoice_number` | `receipt_number` (format `RCT-YYYYMMDD-XXXXXX`) |
| Kolom `invoice_type` | `receipt_type` |
| Route `/api/v1/ppob/invoices` | `/api/v1/ppob/receipts` |
| Frontend `/ppob/invoice/:ref` | `/ppob/receipt/:ref` |

### Yang ikut diubah

- `FinalizePpobTopUpUseCase`, `PpoCallbackHandler`, `PpoBillController`,
  `PpoTransactionController` → memakai `ReceiptService`.
- `PPOBServiceProvider` → bind singletons `ReceiptService`.
- Frontend: `ppobService.js` (hooks `usePpobReceipt`), `PpobReceiptPage`,
  `engine.js` (`receiptEmailEngine`), `CartPage` / `HistoryPage` / `PpobPage` /
  `PpobCheckoutModal` (label "Bukti Pembayaran").

### Sekilas langkah migrasi DB

1. Drop tabel `invoices` lama (opsional, bila sudah pernah dipakai).
2. Jalankan migration `create_receipts_table` (file `..._000002_create_receipts_table.php`).
3. (Opsional) Salin data `invoices` → `receipts` bila ada riwayat produksi.
