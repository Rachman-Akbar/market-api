# Fitur Bukti Pembayaran (Receipt) PPOB

File ini mendokumentasikan fitur **Bukti Pembayaran (receipt) PPOB**:
alur transaksi digital (pulsa/data/token/tagihan) kini menghasilkan *bukti pembayaran*
yang bisa dilihat user, dibuka lewat halaman web, dan dikirim ke email (idempotent).

> **Catatan konsep:** Berbeda dengan *invoice* (dokumen penagihan yang dikirim
> untuk meminta pembayaran), *receipt* / *kuitansi* adalah **bukti bahwa pembayaran
> sudah terjadi**. Karena transaksi PPOB dibayar langsung via Midtrans/IAK, yang
> dihasilkan adalah **bukti pembayaran**, bukan invoice.

## Ringkasan Alur

```text
Transaksi PPOB sukses (prepaid via Midtrans / postpaid via IAK)
        │
        ▼
ReceiptService::generateForTransaction(tx)   → baris @ tabel receipts (idempotent)
        │
        ▼
ReceiptService::sendForTransaction(tx)       → email bukti pembayaran (idempotent, 1x saja)
        │
        ▼
User melihat bukti pembayaran via GET /api/v1/ppob/receipts/{ref}
```

Bukti pembayaran di-generate dan email dikirim dari empat jalur sukses yang sama
(lihat [Wiring](#wiring-jalur-sukses)).

## Skema Tabel `receipts`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint (PK) | |
| `receipt_number` | string(64) UNIQUE | Nomor ramah manusia, `RCT-YYYYMMDD-XXXXXX` |
| `user_id` | FK → `users(id)` | Pemilik bukti pembayaran (UUID) |
| `source_type` | string(40) | `ppob_transaction` atau `order` (marketplace) |
| `source_id` | string(64) | ID transaksi sumber |
| `transaction_reference` | string(100) | `reference_id` transaksi PPOB / `order_number` |
| `receipt_type` | string(30) | `digital` atau `order` |
| `product_name`, `category`, `customer_id`, `customer_name` | string/nullable | Detail produk & pelanggan |
| `subtotal`, `admin_fee`, `discount`, `total` | decimal(15,2) | Rupiah, dari transaksi asli (tidak hardcode) |
| `payment_method` | string(50) | `midtrans` / `via_iak` dll. |
| `payment_status` | string(30) | `paid` / `pending` / dst. |
| `transaction_status` | string(30) | Status transaksi PPOB |
| `paid_at` | timestamp/nullable | |
| `email_sent_at` | timestamp/nullable | Kapan email berhasil dikirim (idempotency) |
| `email_status` | string(30) | `none` → `queued` → `sent` / `failed` |
| `email_message_id` | string(160) | ID pesan untuk tracing |
| `created_at`, `updated_at` | timestamp | |
| `deleted_at` | timestamp | Soft delete |

**Constraint idempotency:** `UNIQUE(source_type, source_id)` — satu bukti pembayaran
per transaksi, aman dari webhook ganda / retry.

## Endpoint

Semua endpoint butuh `auth:sanctum` + `active.user`.

### `GET /api/v1/ppob/receipts`

Daftar bukti pembayaran milik user, terbaru dulu. Dapat difilter dengan
query param `type=digital|order`.

```json
{
  "success": true,
  "data": [ { "receipt_number": "RCT-20260901-AB12CD", ... } ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 15, "total": 3 }
}
```

Query param: `per_page` (default 15), `type` (`digital`|`order`).

### `GET /api/v1/ppob/receipts/history`

Riwayat transaksi terpadu (PPOB + marketplace order) dalam satu daftar datar,
diurutkan berdasarkan tanggal (terbaru dulu), dengan paginasi manual.

```json
{
  "success": true,
  "data": [
    { "type": "digital", "reference_id": "REF-123", "product_name": "XL 10GB",
      "total_amount": 12000.0, "status": "success", "payment_status": "paid", ... }
  ],
  "meta": { "current_page": 1, "last_page": 1, "per_page": 20, "total": 5 }
}
```

Query param: `per_page` (default 20), `page`, `type` (`digital`|`order`).

### `GET /api/v1/ppob/receipts/{referenceOrId}`

Menampilkan satu bukti pembayaran. Di terima sebagai:

1. `receipt_number` (contoh `RCT-20260901-AB12CD`)
2. `transaction_reference` (contoh `REF-XXXX`)
3. ID transaksi (`id`)

Perilaku:

- Pencarian pertama mengembalikan milik user lain → `404`.
- Jika belum ada receipt, dicari transaksi milik user yang cocok:
  - `status ∈ {success, processing}` → receipt di-*generate on-demand*;
  - status lain (pending/failed/cancelled) → detail tetap dikembalikan **langsung
    dari transaksi** (`transactionArray()`) sehingga selalu tampil.
- Marketplace order yang sudah `paid` → receipt di-*generate on-demand*.
- Jika tetap tidak ditemukan → `404 {"success":false}`.

**Keamanan:** seluruh lookup (receipt maupun transaksi on-demand) **scoped ke
`user_id`** yang sedang login. Lihat [Perbaikan keamanan](#perbaikan-keamanan).

Struktur `data` (dari `receiptArray()`):

```json
{
  "id": 1,
  "receipt_number": "RCT-20260901-AB12CD",
  "transaction_reference": "REF-123",
  "receipt_type": "digital",
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

Untuk transaksi digital yang belum punya receipt (mis. status pending/failed),
`data` diisi oleh `transactionArray()` dengan bentuk yang sama, ditambah properti
`raw` berisi detail provider (`sn`, `tr_id`, `provider_status`, `provider_message`).

### `POST /api/v1/ppob/receipts/{referenceOrId}/send-email`

Mengirim email bukti pembayaran ke email akun user yang sedang login (idempotent).

```json
{
  "success": true,
  "message": "Bukti pembayaran telah dikirim ke user@example.com",
  "data": { "email": "user@example.com", "email_status": "sent" }
}
```

## ReceiptService

File: `app/Domains/PPOB/Application/Services/ReceiptService.php`
(terdaftar sebagai **singleton** di `PPOBServiceProvider`).

### `generateForTransaction(PpoTransactionModel $tx): ReceiptModel`

- Idempotent: jika receipt untuk `(source_type='ppob_transaction', source_id=tx.id)`
  sudah ada, dikembalikan apa adanya (tidak membuat duplikat).
- Mengisi nilai dari transaksi **asli** (tidak hardcode): `total` dari
  `$tx->total_amount`, `subtotal` dari `revenue`/`total_amount`, `admin_fee`,
  `payment_method`, `payment_status`, `status`, `paid_at`.

### `generateForOrder(OrderModel $order): ReceiptModel`

- Idempotent, untuk order marketplace. Membangun `product_name` dari order items
  (1 item → nama produk; banyak → `N produk`), `total` dari `total_amount`,
  `discount` dari `discount_amount` + `shipping_discount_amount`.

### `sendForTransaction(PpoTransactionModel $tx): ?ReceiptModel`

- Cari receipt untuk transaksi tsb; jika belum ada, return `null` (tidak mengirim).
- Delegasikan ke `send()`.

### `sendForOrder(OrderModel $order): ?ReceiptModel`

- Sama seperti `sendForTransaction`, untuk order marketplace.

### `send(ReceiptModel $receipt): ?ReceiptModel`

- **Idempotent** — terkunci dari *double-send* oleh level:
  1. Guard `email_sent_at` (sudah dikirim → skip).
  2. **Atomic claim:** `UPDATE receipts SET email_status='queued'
     WHERE id=? AND email_status='none'` — hanya satu proses yang menang
     (aman dari webhook/retry bersamaan).
- Kirim `Mail::to($user->email)->send(...)` **sinkron**:
  - `source_type='ppob_transaction'` → `PaymentReceiptMail` (dari data transaksi,
    berisi produk, nomor pelanggan, **SN & TRID**, nominal, status);
  - `source_type='order'` → `ReceiptMail`.
- Sukses → `email_status='sent'`, `email_sent_at=now()`, `email_message_id=UUID`.
- Gagal → `email_status='failed'` + `Log::warning` (bisa dicoba lagi nanti).
- Syarat: user ada & memiliki `email`. Tanpa email → tidak mengirim, return receipt.

## Email Bukti Pembayaran

**Mailable digital:** `app/Domains/PPOB/Infrastructure/Mail/PaymentReceiptMail.php`
- Dipakai untuk transaksi PPOB (pulsa/data/token/tagihan).
- Subject: `Bukti Pembayaran Berhasil - {nama produk}`.
- Memakai `PpoTransactionModel` langsung → menampilkan SN/TRID dari provider.

**Mailable order:** `app/Domains/PPOB/Infrastructure/Mail/ReceiptMail.php`
- Dipakai untuk marketplace order (`implements ShouldQueue`, `onQueue('emails')`).
- Subject: `Bukti Pembayaran {RCT-...}`.

**View:**
- `resources/views/emails/payment-receipt.blade.php` (digital)
- `resources/views/emails/receipt-created.blade.php` (order)
- Menampilkan: nomor bukti, produk, customer, metode pembayaran, total, dan tombol
  "Lihat Detail Transaksi" → `{APP_FRONTEND_URL}/ppob/receipt/{transaction_reference}`.

**Penting:** Mailable memakai `Content(view: ..., with: [...])` — parameter named
`view:` yang benar untuk Laravel 13.x terpasang. (Order mails lama memakai
`htmlView:` dan akan error; lihat changelog.)

## Wiring (Jalur Sukses)

Receipt + email di-generate pada empat lokasi saat transaksi **sukses**:

1. `app/Domains/PPOB/Application/UseCases/FinalizePpobTopUpUseCase.php`
2. `app/Domains/PPOB/Application/Services/PpoCallbackHandler.php`
3. `app/Domains/PPOB/Presentation/Http/Controllers/PpoTransactionController.php::checkStatus`
4. `app/Domains/PPOB/Presentation/Http/Controllers/PpoBillController.php` (postpaid `pay`)

Setiap lokasi memanggil `generateForTransaction(tx->fresh())` lalu
`sendForTransaction(tx->fresh())`.

## Perbaikan Keamanan

Pada `ReceiptController::show()`, lookup on-demand transaksi sebelumnya:

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
- 🚀 `php artisan route:list --path=ppob/receipts` → routes
  `api/v1/ppob/receipts`, `.../history`, `.../{referenceOrId}`,
  `.../{referenceOrId}/send-email` terdaftar.
- ✉️ Render Mailable terverifikasi (subject, nomor receipt, URL lihat benar).
- 🔁 Idempotency `send()` teruji: panggilan pertama → `sent`; panggilan kedua → timestamp sama.
- 🧹 Data uji dibersihkan (0 baris receipt tersisa, 0 jobs tersisa).
- ⚠️ `php artisan test` tidak bisa dijalankan lokal karena ekstensi `pdo_sqlite`
  tidak terpasang (pre-existing; bukan akibat perubahan ini).
