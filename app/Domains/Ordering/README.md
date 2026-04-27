# Ordering Domain

Modul ini menggunakan folder dan namespace:

```txt
app/Domains/Ordering
App\Domains\Ordering
```

## Register Provider

Karena project memakai `bootstrap/app.php`, daftarkan provider di `withProviders()`:

```php
->withProviders([
    App\Domains\Catalog\Infrastructure\Providers\CatalogServiceProvider::class,
    App\Domains\Cart\Infrastructure\Providers\CartServiceProvider::class,
    App\Domains\Ordering\Infrastructure\Providers\OrderingServiceProvider::class,
])
```

Jangan pakai `App\Domains\Order\...` kalau folder modulnya tetap `Ordering`.

## Routes

Provider akan memberi prefix global:

```txt
/api/v1
```

File route modul memakai prefix:

```txt
/orders
```

Jadi endpoint akhirnya:

```txt
GET    /api/v1/orders
POST   /api/v1/orders
GET    /api/v1/orders/{order}
POST   /api/v1/orders/{order}/cancel
PATCH  /api/v1/orders/{order}/status
```

Semua route memakai middleware:

```txt
firebase.auth
```

## Install

Extract folder `Ordering` ke:

```txt
app/Domains/Ordering
```

Lalu jalankan:

```bash
composer dump-autoload
php artisan optimize:clear
php artisan migrate
php artisan route:list --path=api/v1/orders
```

## Required Existing Tables

Modul ini membaca data dari tabel cart dan catalog yang sudah ada:

```txt
users
carts
cart_items
products
```

Minimal kolom yang dibaca:

### carts

```txt
id
user_id
status
order_id nullable
checked_out_at nullable
```

### cart_items

```txt
id
cart_id
product_id
quantity
```

### products

```txt
id
name
sku nullable
price
currency nullable
stock
```

## Tables Created by Ordering Migration

```txt
orders
order_items
order_status_histories
```

## Test Create Order

Header:

```http
Authorization: Bearer FIREBASE_TOKEN
Accept: application/json
Content-Type: application/json
```

Request:

```http
POST /api/v1/orders
```

Body:

```json
{
  "shipping_address": {
    "recipient_name": "Akbar",
    "phone": "08123456789",
    "address_line": "Jl. Melati No. 10",
    "province": "DKI Jakarta",
    "city": "Jakarta Selatan",
    "district": "Kebayoran Baru",
    "postal_code": "12110",
    "notes": "Rumah warna putih"
  },
  "notes": "Tolong packing aman",
  "payment_method": "manual_transfer"
}
```

Expected:

```txt
201 Created
```

Efek database:

```txt
orders bertambah
order_items bertambah
order_status_histories bertambah
products.stock berkurang
carts.status menjadi ordered
```

## Test List Orders

```http
GET /api/v1/orders
GET /api/v1/orders?status=pending
GET /api/v1/orders?payment_status=unpaid
```

## Test Detail Order

```http
GET /api/v1/orders/1
GET /api/v1/orders/ORD-xxxxxx
```

## Test Cancel Order

```http
POST /api/v1/orders/1/cancel
```

Body:

```json
{
  "reason": "User berubah pikiran"
}
```

## Test Update Status

```http
PATCH /api/v1/orders/1/status
```

Body:

```json
{
  "status": "confirmed",
  "note": "Order sudah dikonfirmasi admin"
}
```

Akses update status membutuhkan user admin/permission lewat logic controller:

```txt
is_admin = true
manage-orders
orders.manage
```
