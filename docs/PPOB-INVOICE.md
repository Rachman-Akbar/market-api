# Fitur Invoice PPOB

File ini mendokumentasikan fitur **Invoice PPOB** yang ditambahkan 2026-09-01:
alur transaksi digital (pulsa/data/token/tagihan) kini menghasilkan *invoice digital*
yang bisa dilihat user, dibuka lewat halaman web, dan dikirim ke email (idempotent).

## Ringkasan Alur

```text
Transaksi PPOB sukses (prepaid via Midtrans / postpaid via IAK)
        │
        ▼
InvoiceService::generateForTransaction(tx)   → baris @ tabel invoices (idempotent)
        │
        ▼
InvoiceService::sendForTransaction(tx)       → email invoice (idempotent, 1x saja)
        │
        ▼
User melihat invoice via GET /api/v1/ppob/invoices/{ref}
```

Invoice di-generate dan email di-*queue* dari empat jalur sukses yang sama
(lihat [Wiring](#wiring-jalur-sukses)).

## Skema Tabel `invoices`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint (PK) | |
| `invoice_number` | string(64) UNIQUE | Nomor ramah manusia, `INV-YYYYMMDD-XXXXXX` |
| `user_id` | FK → `users(id)` | Pemilik invoice (UUID) |
| `source_type` | string(40) | `ppob_transaction` (mendukung `order` di masa depan) |
| `source_id` | string(64) | ID transaksi sumber |
| `transaction_reference` | string(100) | `reference_id` transaksi PPOB |
| `invoice_type` | string(30) | `digital` |
| `product_name`, `category`, `customer_id`, `customer_name` | string/nullable | Detail produk & pelanggan |
| `subtotal`, `admin_fee`, `discount`, `total` | decimal(15,2) | Rupiah, dari transaksi asli (tidak hardcode) |
| `payment_method` | string(50) | `midtrans` / `via_iak` dll. |
| `payment_status` | string(30) | `paid` / `pending` / dst. |
| `transaction_status` | string(30) | Status transaksi PPOB |
| `paid_at` | timestamp/nullable | |
| `email_sent_at` | timestamp/nullable | Kapan email berhasil di-queue (idempotency) |
| `email_status` | string(30) | `none` → `queued` → `sent` / `failed` |
| `email_message_id` | string(160) | ID pesan untuk tracing |
| `created_at`, `updated_at` | timestamp | |
| `deleted_at` | timestamp | Soft delete |

**Constraint idempotency:** `UNIQUE(source_type, source_id)` — satu invoice per
transaksi, aman dari webhook ganda / retry.

## Endpoint

Semua endpoint butuh `auth:sanctum` + `active.user`.

### `GET /api/v1/ppob/invoices`

Daftar invoice milik user (hanya `invoice_type = digital`), terbaru dulu.

```json
{
  "success": true,
  "data": [ { "invoice_number": "INV-20260901-AB12CD", ... } ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 3 }
}
```

Query param: `per_page` (default 15).

### `GET /api/v1/ppob/invoices/{referenceOrId}`

Menampilkan satu invoice, diterima sebagai:

1. `invoice_number` (contoh `INV-20260901-AB12CD`)
2. `transaction_reference` (contoh `REF-XXXX`)
3. ID transaksi (`id`)

Prilaku:

- Pencarian pertama mengembalikan invoice milik user lain → `404`.
- Jika belum ada invoice, dicari transaksi sukses milik user yang cocok
  (`status ∈ {success, processing}`), lalu invoice di-*generate on-demand*.
- Jika tetap tidak ditemukan → `404 {"success":false}`.

**Keamanan:** seluruh lookup (invoice maupun transaksi on-demand) **scoped ke
`user_id`** yang sedang login. Lihat [Perbaikan keamanan](#perbaikan-keamanan).

Struktur `data` (dari `invoiceArray()`):

```json
{
  "id": 1,
  "invoice_number": "INV-20260901-AB12CD",
  "transaction_reference": "REF-123",
  "invoice_type": "digital",
  "product_name": "XL Axiata 10GB",
  "category": "pulsa",
  "customer_id": "08123456789",
  "customer_name": null,
  "subtotal": 12000.0,
  "admin_fee": 0.0,
  "discount": 0.0,
  "total": 12000.0,
  "payment_method": "midtrans",
  "payment_status": "paid",
  "transaction_status": "success",
  "paid_at": "2026-09-01 14:40:57",
  "created_at": "2026-09-01 14:40:57"
}
```

## InvoiceService

File: `app/Domains/PPOB/Application/Services/InvoiceService.php`
(terdaftar sebagai **singleton** di `PPOBServiceProvider`).

### `generateForTransaction(PpoTransactionModel $tx): InvoiceModel`

- Idempotent: jika invoice untuk `(source_type='ppob_transaction', source_id=tx.id)`
  sudah ada, dikembalikan apa adanya (tidak membuat duplikat).
- Mengisi nilai dari transaksi **asli** (tidak hardcode): `total` dari
  `$tx->total_amount`, `subtotal` dari `revenue`/`total_amount`, `admin_fee`,
  `payment_method`, `payment_status`, `status`, `paid_at`.

### `sendForTransaction(PpoTransactionModel $tx): ?InvoiceModel`

- Cari invoice untuk transaksi tsb; jika belum ada, return `null` (tidak mengirim).
- Delegasikan ke `send()`.

### `send(InvoiceModel $invoice): ?InvoiceModel`

- **Idempotent** — terkunci dari *double-send* oleh level:
  1. Guard `email_sent_at` (sudah dikirim → skip).
  2. **Atomic claim:** `UPDATE invoices SET email_status='queued'
     WHERE id=? AND email_status='none'` — hanya satu proses yang menang
     (aman dari webhook/retry bersamaan).
- Queue via `Mail::to($user->email)->queue(new InvoiceCreatedMail(...))`.
- Sukses → `email_status='sent'`, `email_sent_at=now()`, `email_message_id=UUID`.
- Gagal → `email_status='failed'` + `Log::warning` (bisa dicoba lagi nanti).
- Syarat: user ada & memiliki `email`. Tanpa email → tidak mengirim, return invoice.

## Email Invoice

**Mailable:** `app/Domains/PPOB/Infrastructure/Mail/InvoiceCreatedMail.php`
- `implements ShouldQueue`, `onQueue('emails')`.
- Subject: `Invoice {INV-...} Transaksi Berhasil`.

**View:** `resources/views/emails/invoice-created.blade.php`
- Menampilkan: nomor invoice, produk, customer, metode pembayaran, total, dan tombol
  "Lihat Invoice" → `{APP_FRONTEND_URL}/ppob/invoice/{transaction_reference}`.

**Penting:** Mailable memakai `Content(view: ..., with: [...])` — parameter named
`view:` yang benar untuk Laravel 13.x terpasang. (Order mails lama memakai
`htmlView:` dan akan error; lihat changelog.)

## Wiring (Jalur Sukses)

Invoice + email di-generate pada empat lokasi saat transaksi **sukses**:

1. `app/Domains/PPOB/Application/UseCases/FinalizePpobTopUpUseCase.php`
2. `app/Domains/PPOB/Application/Services/PpoCallbackHandler.php`
3. `app/Domains/PPOB/Presentation/Http/Controllers/PpoTransactionController.php::checkStatus`
4. `app/Domains/PPOB/Presentation/Http/Controllers/PpoBillController.php` (postpaid `payBill`)

Setiap lokasi memanggil `generateForTransaction(tx->fresh())` lalu
`sendForTransaction(tx->fresh())`.

## Perbaikan Keamanan

Pada `InvoiceController::show()`, lookup on-demand transaksi sebelumnya:

```php
PpoTransactionModel::where('user_id', $user->id)
    ->where('reference_id', $referenceOrId)
    ->orWhere('id', $referenceOrId) // ⚠️ lepas dari scope user
```

Ditulis ulang agar cabang `id` tetap berada dalam scope user:

```php
PpoTransactionModel::where('user_id', $user->id)
    ->where(function ($q) use ($referenceOrId): void {
        $q->where('reference_id', $referenceOrId)
            ->orWhere('id', $referenceOrId);
    })
```

## Verifikasi

- ✍️ `php -l` clean di semua file PHP yang diubah.
- 🚀 `php artisan route:list` → routes `api/v1/ppob/invoices` dan `api/v1/ppob/invoices/{referenceOrId}` terdaftar.
- ✉️ Render Mailable terverifikasi (subject, nomor invoice, URL lihat invoice benar).
- 🔁 Idempotency `send()` teruji: panggilan pertama → `sent`; panggilan kedua → timestamp sama.
- 🧹 Data uji dibersihkan (0 baris invoice tersisa, 0 jobs tersisa).
- ⚠️ `php artisan test` tidak bisa dijalankan lokal karena ekstensi `pdo_sqlite`
  tidak terpasang (pre-existing; bukan akibat perubahan ini).