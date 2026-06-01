# 📦 MARKETPLACE APPLICATION — PRD & Technical Documentation

**Document Version:** 2.0.0
**Target Audience:** AI Agent / Developer
**Purpose:** Spesifikasi teknis dan fungsional yang sangat spesifik, terstruktur, dan konsisten untuk pengembangan sistem backend dan frontend Marketplace.
**Last Updated:** 2025

---

## 📋 TABLE OF CONTENTS

1. [Project Overview](#1-project-overview)
2. [System Architecture & Tech Stack](#2-system-architecture--tech-stack)
3. [Authentication System (CRITICAL)](#3-authentication-system-critical)
4. [User Roles & Permissions Matrix](#4-user-roles--permissions-matrix)
5. [Feature Specifications](#5-feature-specifications)
   - 5.1 [AUTH Module](#51-auth-module)
   - 5.2 [Buyer Module — Order Flow](#52-buyer-module--order-flow)
   - 5.3 [Seller Module — Store & Catalog Management](#53-seller-module--store--catalog-management)
   - 5.4 [Admin Module — Global Marketplace Management](#54-admin-module--global-marketplace-management)
6. [Database Schema](#6-database-schema)
7. [API Endpoint Contracts](#7-api-endpoint-contracts)
8. [Business Rules & Constraints](#8-business-rules--constraints)
9. [Error Handling Standards](#9-error-handling-standards)
10. [Glossary](#10-glossary)

---

## 1. PROJECT OVERVIEW

**Marketplace** adalah platform e-commerce multiguna berbasis multi-tenant untuk memenuhi kebutuhan harian berupa **barang maupun jasa**.

### Core Characteristics

| Property | Value |
|---|---|
| Architecture | Multi-tenant (many sellers, one platform) |
| Auth Provider | Firebase Auth (Google Sign-In) + Laravel Sanctum |
| Payment Methods | COD, Manual Transfer, Midtrans (VA / QRIS) |
| Multi-Device Auth | ✅ Supported — same account, concurrent sessions, role per device |
| Primary Language | Indonesian (Bahasa Indonesia) |

### Key Design Principle: Multi-Device Role Behavior

> ⚠️ **CRITICAL FOR AI AGENT:** Satu akun pengguna dapat login secara **bersamaan di beberapa perangkat** dengan **role yang berbeda per sesi/device**. Ini bukan multi-role pada akun, melainkan **konteks device yang menentukan interface yang disajikan**.

| Device Type | Default Interface Role Presented |
|---|---|
| Mobile (HP/Smartphone) | **Buyer Interface** — browse, cart, order |
| Desktop (PC/Laptop/Browser lebar) | **Seller Interface** — manage store, products, orders |

> 💡 Implementasi: Device type dideteksi via `User-Agent` header atau parameter `device_type` pada saat token generation. Token Sanctum menyimpan metadata `device_type` (`mobile` / `desktop`). Backend dan Frontend menggunakan `device_type` ini untuk menentukan dashboard/interface yang ditampilkan — **bukan untuk membatasi hak akses secara mutlak**.

---

## 2. SYSTEM ARCHITECTURE & TECH STACK

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT LAYER                         │
│  Mobile App (Flutter/React Native)  │  Web App (React)  │
│  → Buyer Interface (default)        │  → Seller/Admin   │
└──────────────────┬──────────────────┴────────┬──────────┘
                   │ HTTPS + Bearer Token       │
┌──────────────────▼────────────────────────────▼──────────┐
│                  BACKEND (Laravel)                        │
│  ┌──────────────┐  ┌─────────────┐  ┌─────────────────┐  │
│  │ Sanctum Auth │  │  REST API   │  │ Midtrans Webhook│  │
│  └──────────────┘  └─────────────┘  └─────────────────┘  │
└───────────────────────┬──────────────────────────────────┘
                        │
           ┌────────────┴──────────────┐
           │                           │
    ┌──────▼──────┐           ┌────────▼───────┐
    │   MySQL DB  │           │  Firebase Admin │
    │  (Primary)  │           │  SDK (Verify)   │
    └─────────────┘           └────────────────┘
```

### Tech Stack Wajib

| Layer | Technology | Notes |
|---|---|---|
| Database | **MySQL** | Semua data relasional: users, products, orders, dll |
| Auth Provider | **Firebase Auth** | Hanya untuk Google Sign-In di client side |
| Backend Framework | **PHP Laravel** | Core API, business logic |
| Token Management | **Laravel Sanctum** | Bearer Token, multi-device sessions |
| Payment Gateway | **Midtrans** | Snap / Core API untuk VA bank & QRIS |
| File Storage | Configurable (S3/Local) | Product images, banner images, bukti transfer |

---

## 3. AUTHENTICATION SYSTEM (CRITICAL)

### 3.1 Auth Flow: Firebase → MySQL → Sanctum

```
Client (Mobile/Web)
  │
  ├─[1] Google Sign-In via Firebase SDK
  │      ↓
  ├─[2] Firebase issues ID Token
  │      ↓
  ├─[3] POST /api/auth/google
  │      Headers: { "X-Device-Type": "mobile" | "desktop" }
  │      Body: { "id_token": "<firebase_id_token>" }
  │      ↓
Backend
  ├─[4] Verify ID Token → Firebase Admin SDK
  │      ↓
  ├─[5] Check user in MySQL by (email / firebase_uid)
  │      ├─ NOT FOUND → Create new user (role: buyer, is_banned: false)
  │      └─ FOUND → Load existing user
  │      ↓
  ├─[6] Check is_banned === true → Return 403 FORBIDDEN
  │      ↓
  ├─[7] Generate Sanctum Token
  │      Token abilities: based on user.role
  │      Token metadata: { device_type: "mobile"|"desktop" }
  │      ↓
  └─[8] Return { token, user, device_type }

Client (All subsequent requests)
  └─ Header: Authorization: Bearer <sanctum_token>
```

### 3.2 Multi-Device Session Rules

```
RULE 1: Satu user BOLEH memiliki BANYAK token aktif secara bersamaan.
        (Multi-device concurrent login = DIIZINKAN)

RULE 2: Setiap token menyimpan metadata `device_type` (mobile/desktop).

RULE 3: Token TIDAK di-revoke ketika login dari device lain.
        (Tidak ada "kick previous session")

RULE 4: Semua token di-revoke SERENTAK jika user di-banned oleh Admin.

RULE 5: User dapat logout (revoke) 1 token spesifik atau semua token sekaligus.
```

### 3.3 Token Structure (Sanctum Personal Access Token)

```sql
-- personal_access_tokens table (Laravel default + custom columns)
id              BIGINT UNSIGNED PK
tokenable_type  VARCHAR       -- "App\Models\User"
tokenable_id    BIGINT        -- user.id
name            VARCHAR       -- e.g., "mobile_session_2025-01-01"
token           VARCHAR(64)   -- hashed
abilities       TEXT          -- JSON, e.g., ["buyer", "seller"]
device_type     ENUM('mobile','desktop')  -- CUSTOM COLUMN
last_used_at    TIMESTAMP NULL
expires_at      TIMESTAMP NULL
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

### 3.4 Auth API Endpoints

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/auth/google` | None | Login/Register via Firebase ID Token |
| POST | `/api/auth/logout` | Bearer | Revoke current token |
| POST | `/api/auth/logout-all` | Bearer | Revoke ALL tokens milik user ini |
| GET | `/api/auth/me` | Bearer | Get current user info + device_type |
| POST | `/api/auth/upgrade-to-seller` | Bearer (Buyer) | Upgrade role ke seller |

### 3.5 Middleware Stack

```
# Route Groups
Route::middleware(['auth:sanctum'])->group(...)           // Require valid token
Route::middleware(['auth:sanctum', 'role:buyer'])->group(...)    // Buyer only
Route::middleware(['auth:sanctum', 'role:seller'])->group(...)   // Seller only
Route::middleware(['auth:sanctum', 'role:admin'])->group(...)    // Admin only
Route::middleware(['auth:sanctum', 'not.banned'])->group(...)    // Banned check

# Custom Middleware
CheckBanned::class   → Cek users.is_banned, jika true revoke token + return 403
CheckRole::class     → Cek users.role matches required role
```

---

## 4. USER ROLES & PERMISSIONS MATRIX

### Role Definitions

| Role | Description | How Created |
|---|---|---|
| `guest` | Unauthenticated visitor | Default (no account) |
| `buyer` | Pembeli terdaftar | Auto-assigned on first Google login |
| `seller` | Penjual (punya store) | Upgrade dari buyer, atau dibuat admin |
| `admin` | Pengelola platform | Hanya via DB seed atau dibuat admin lain |

### Permissions Matrix

| Feature / Module | Guest | Buyer | Seller | Admin |
|---|:---:|:---:|:---:|:---:|
| Lihat katalog global (produk, banner, kategori) | ✅ | ✅ | ✅ | ✅ |
| Google Sign-In / Registrasi | ✅ | ❌ | ❌ | ❌ |
| Upgrade role → Seller | ❌ | ✅ | ❌ | ❌ |
| Lihat detail produk + varian | ✅ | ✅ | ✅ | ✅ |
| Tambah ke Cart | ❌ | ✅ | ❌ | ❌ |
| Checkout (COD / Transfer / Midtrans) | ❌ | ✅ | ❌ | ❌ |
| Lihat order history sendiri | ❌ | ✅ | ❌ | ❌ |
| Manage toko sendiri (CRUD) | ❌ | ❌ | ✅ | ❌ |
| CRUD produk di toko sendiri | ❌ | ❌ | ✅ | ❌ |
| Lihat & proses order masuk (toko) | ❌ | ❌ | ✅ | ❌ |
| Konfirmasi bukti transfer manual | ❌ | ❌ | ✅ | ❌ |
| Admin: CRUD CatalogGroup global | ❌ | ❌ | ❌ | ✅ |
| Admin: CRUD Kategori global (L1-L3) | ❌ | ❌ | ❌ | ✅ |
| Admin: CRUD Banner global | ❌ | ❌ | ❌ | ✅ |
| Admin: Manage semua user | ❌ | ❌ | ❌ | ✅ |
| Admin: Ban/Unban user | ❌ | ❌ | ❌ | ✅ |
| Admin: Buat akun admin baru | ❌ | ❌ | ❌ | ✅ |

---

## 5. FEATURE SPECIFICATIONS

---

### 5.1 AUTH MODULE

#### 5.1.1 Google Sign-In Flow (Detail)

**Request:**
```http
POST /api/auth/google
Content-Type: application/json
X-Device-Type: mobile   ← WAJIB: "mobile" atau "desktop"

{
  "id_token": "eyJhbGciOiJSUzI1NiIsImtpZCI6..."
}
```

**Response (Success 200):**
```json
{
  "success": true,
  "data": {
    "token": "1|abc123sanctumtoken...",
    "token_type": "Bearer",
    "device_type": "mobile",
    "user": {
      "id": 42,
      "name": "Budi Santoso",
      "email": "budi@gmail.com",
      "role": "buyer",
      "firebase_uid": "abc123firebaseuid",
      "is_banned": false,
      "store": null,
      "created_at": "2025-01-01T00:00:00Z"
    }
  }
}
```

**Response (Banned User 403):**
```json
{
  "success": false,
  "message": "Akun Anda telah diblokir. Hubungi admin untuk informasi lebih lanjut.",
  "error_code": "USER_BANNED"
}
```

#### 5.1.2 Upgrade ke Seller

**Business Rules:**
- Hanya user dengan `role = buyer` yang bisa upgrade.
- Saat upgrade: `users.role` diubah ke `seller` DAN entitas `stores` baru dibuat secara atomik (dalam satu DB transaction).
- Store baru memiliki `status = active` by default.
- Token yang sudah ada tetap valid, `abilities` diperbarui.

**Request:**
```http
POST /api/auth/upgrade-to-seller
Authorization: Bearer <token>
Content-Type: application/json

{
  "store_name": "Toko Budi Jaya",
  "store_description": "Jual kebutuhan rumah tangga",
  "store_address": "Jl. Melati No. 5, Surabaya"
}
```

**Response (Success 200):**
```json
{
  "success": true,
  "message": "Selamat! Anda kini menjadi seller.",
  "data": {
    "user": { "id": 42, "role": "seller" },
    "store": {
      "id": 7,
      "name": "Toko Budi Jaya",
      "slug": "toko-budi-jaya",
      "status": "active",
      "seller_id": 42
    }
  }
}
```

---

### 5.2 BUYER MODULE — ORDER FLOW

#### 5.2.1 Cart (Keranjang Belanja)

**Rules:**
- Cart disimpan di **MySQL** (bukan localStorage), agar konsisten lintas device.
- Satu buyer memiliki **satu cart aktif** (1 cart record, banyak cart_items).
- Cart item terikat ke **product_variant_id** (bukan product_id langsung), karena harga & stok ada di varian.
- Saat checkout, cart di-clear secara otomatis.

**Endpoints:**

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/buyer/cart` | Lihat isi cart |
| POST | `/api/buyer/cart/items` | Tambah item ke cart |
| PUT | `/api/buyer/cart/items/{id}` | Update quantity item |
| DELETE | `/api/buyer/cart/items/{id}` | Hapus item dari cart |
| DELETE | `/api/buyer/cart` | Kosongkan seluruh cart |

**POST /api/buyer/cart/items — Request Body:**
```json
{
  "product_variant_id": 15,
  "quantity": 2
}
```

**Validation Rules:**
- `product_variant_id` harus exist di tabel `product_variants`.
- `quantity` >= 1.
- `quantity` tidak boleh melebihi `product_variants.stock`.
- Jika item dengan `product_variant_id` sama sudah ada di cart → **update quantity** (tidak duplikat).

#### 5.2.2 Checkout & Payment

**Checkout Request:**
```http
POST /api/buyer/checkout
Authorization: Bearer <token>
Content-Type: application/json

{
  "payment_method": "midtrans",   ← "cod" | "manual_transfer" | "midtrans"
  "shipping_address": {
    "recipient_name": "Budi Santoso",
    "phone": "08123456789",
    "address": "Jl. Melati No. 5",
    "city": "Surabaya",
    "province": "Jawa Timur",
    "postal_code": "60111"
  },
  "notes": "Tolong dikemas rapi"
}
```

**Checkout Process (Backend Logic):**
```
1. Validasi cart tidak kosong.
2. Validasi stok semua item masih tersedia (race condition check).
3. Hitung total harga (sum product_variant.price * quantity).
4. Buat record `orders` dengan status awal sesuai payment_method:
   - COD            → status: "processing"
   - manual_transfer → status: "pending_payment"
   - midtrans       → status: "pending_payment"
5. Buat record `order_items` dari cart_items.
6. Kurangi stok (product_variants.stock -= quantity) secara atomik.
7. Clear cart_items setelah berhasil.
8. Jika midtrans → Panggil Midtrans API, dapatkan Snap Token / VA.
9. Return response sesuai payment method.
```

**Response (COD — 201):**
```json
{
  "success": true,
  "data": {
    "order_id": 88,
    "order_number": "ORD-20250101-0088",
    "status": "processing",
    "payment_method": "cod",
    "total_amount": 150000,
    "message": "Pesanan berhasil dibuat. Pembayaran dilakukan saat barang tiba."
  }
}
```

**Response (Midtrans — 201):**
```json
{
  "success": true,
  "data": {
    "order_id": 89,
    "order_number": "ORD-20250101-0089",
    "status": "pending_payment",
    "payment_method": "midtrans",
    "total_amount": 250000,
    "midtrans": {
      "snap_token": "abc123snaptoken",
      "redirect_url": "https://app.sandbox.midtrans.com/snap/v2/vtweb/abc123"
    }
  }
}
```

#### 5.2.3 Order Status Lifecycle

```
                    ┌─────────────────────────────────────────────┐
   [COD]            │  processing → shipped → delivered → completed│
                    └─────────────────────────────────────────────┘

                    ┌──────────────────────────────────────────────────────────┐
   [Manual          │  pending_payment → paid (seller confirm) → processing   │
    Transfer]       │                                         → shipped        │
                    │                                         → delivered      │
                    │                                         → completed      │
                    └──────────────────────────────────────────────────────────┘

                    ┌──────────────────────────────────────────────────────────┐
   [Midtrans]       │  pending_payment → paid (webhook auto) → processing     │
                    │                                         → shipped        │
                    │                                         → delivered      │
                    │                                         → completed      │
                    └──────────────────────────────────────────────────────────┘

   [Any Method]     cancelled   ← bisa dari pending_payment atau processing
```

**Order Status Enum (HARUS KONSISTEN di seluruh codebase):**

| Status | Deskripsi | Siapa yang trigger |
|---|---|---|
| `pending_payment` | Menunggu pembayaran | System saat checkout |
| `processing` | Pembayaran confirmed, sedang diproses seller | System/Seller |
| `shipped` | Barang dikirim | Seller |
| `delivered` | Barang diterima | Seller / Buyer konfirmasi |
| `completed` | Transaksi selesai | System / Buyer |
| `cancelled` | Dibatalkan | Buyer / System (timeout) |

#### 5.2.4 Midtrans Webhook Handler

```http
POST /api/payments/midtrans-notification
(No Auth Header — verified via Midtrans Signature Key)

Body (from Midtrans):
{
  "order_id": "ORD-20250101-0089",
  "transaction_status": "settlement",
  "fraud_status": "accept",
  "signature_key": "abc123..."
}
```

**Backend Webhook Logic:**
```
1. Verifikasi signature_key:
   SHA512(order_id + status_code + gross_amount + SERVER_KEY)
   → Jika tidak match → Return 403, log warning.

2. Jika transaction_status == "settlement" AND fraud_status == "accept":
   → UPDATE orders SET status = "processing", paid_at = NOW()
      WHERE order_number = order_id AND status = "pending_payment"

3. Jika transaction_status == "expire":
   → UPDATE orders SET status = "cancelled" WHERE ...

4. Return HTTP 200 OK (wajib, agar Midtrans tidak retry)
```

#### 5.2.5 Manual Transfer Upload

```http
POST /api/buyer/orders/{order_id}/upload-proof
Authorization: Bearer <token>
Content-Type: multipart/form-data

{
  "proof_image": <file: jpg/png, max 2MB>
}
```

---

### 5.3 SELLER MODULE — STORE & CATALOG MANAGEMENT

#### 5.3.1 Store Management

**Rules:**
- Satu seller = tepat satu store (1:1 relationship).
- Seller **HANYA** bisa mengelola store miliknya sendiri. Endpoint harus auto-resolve store dari `auth()->user()->store`.
- Seller **TIDAK BISA** mengakses atau mengubah data store/produk milik seller lain.

**Store Endpoints:**

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/seller/store` | Lihat data store sendiri |
| PUT | `/api/seller/store` | Update info store |
| GET | `/api/seller/store/dashboard` | Stats: total produk, total order, revenue |

#### 5.3.2 Product & Variant Management

**Product Data Model:**
```
products
  ├── id
  ├── store_id          ← FK ke stores.id
  ├── category_id       ← FK ke categories.id (direkomendasikan level 3)
  ├── name
  ├── slug              ← auto-generated dari name
  ├── description
  ├── thumbnail_url
  ├── is_active         BOOLEAN default true
  ├── created_at
  └── updated_at

product_variants
  ├── id
  ├── product_id        ← FK ke products.id
  ├── sku               ← auto atau manual
  ├── price             DECIMAL(15,2)
  ├── stock             INT UNSIGNED
  ├── weight_gram       INT (untuk kalkulasi ongkir)
  └── options           JSON  ← [{"type":"Warna","value":"Merah"},{"type":"Ukuran","value":"XL"}]

```

> ⚠️ **AI AGENT NOTE:** Varian produk menggunakan kolom `options` bertipe JSON untuk fleksibilitas. Tidak ada tabel terpisah untuk variant_types dan variant_values agar query lebih simpel. Format `options` adalah array of objects `{type, value}`.

**Product Endpoints (Seller):**

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/seller/products` | List semua produk toko sendiri |
| POST | `/api/seller/products` | Buat produk baru beserta varian |
| GET | `/api/seller/products/{id}` | Detail produk |
| PUT | `/api/seller/products/{id}` | Update produk |
| DELETE | `/api/seller/products/{id}` | Hapus produk (soft delete) |

**POST /api/seller/products — Request Body:**
```json
{
  "category_id": 15,
  "name": "Kaos Polos Premium",
  "description": "Kaos berbahan katun combed 30s, nyaman dipakai sehari-hari.",
  "thumbnail_url": "https://storage.example.com/products/kaos-thumb.jpg",
  "variants": [
    {
      "sku": "KAO-MRH-S",
      "price": 75000,
      "stock": 50,
      "weight_gram": 200,
      "options": [
        { "type": "Warna", "value": "Merah" },
        { "type": "Ukuran", "value": "S" }
      ]
    },
    {
      "sku": "KAO-MRH-M",
      "price": 75000,
      "stock": 30,
      "weight_gram": 220,
      "options": [
        { "type": "Warna", "value": "Merah" },
        { "type": "Ukuran", "value": "M" }
      ]
    }
  ]
}
```

**Validation Rules (Create Product):**
- `category_id` harus exist di tabel `categories`.
- `variants` array wajib ada, minimal 1 item.
- Setiap varian: `price` >= 0, `stock` >= 0, `weight_gram` >= 1.
- `store_id` di-inject otomatis dari `auth()->user()->store->id` (tidak dari request body).

#### 5.3.3 Order Management (Seller)

**Seller Order Endpoints:**

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/seller/orders` | List order masuk (filter by status) |
| GET | `/api/seller/orders/{id}` | Detail order |
| PUT | `/api/seller/orders/{id}/status` | Update status order |
| PUT | `/api/seller/orders/{id}/confirm-payment` | Konfirmasi bukti transfer manual |

**PUT /api/seller/orders/{id}/status — Request:**
```json
{
  "status": "shipped",
  "tracking_number": "JNE123456789",
  "courier": "JNE"
}
```

**Status Transition Rules (Seller dapat mengubah ke):**
```
processing → shipped
shipped    → delivered
```
> Seller TIDAK BISA mengubah status ke `completed`, `cancelled`, atau `pending_payment`.

---

### 5.4 ADMIN MODULE — GLOBAL MARKETPLACE MANAGEMENT

#### 5.4.1 Global Catalog Domain Management

Admin mengelola **domain katalog global** yang menjadi fondasi seluruh marketplace.

**Komponen yang dikelola Admin:**

```
1. CatalogGroups (Pengelompokan Kebutuhan)
   └── Contoh: "Harian", "Belanja", "Rumahan", "Bangunan"
   └── Digunakan untuk filter di Homepage

2. Categories (Hierarki 3 Level)
   ├── Level 1 (Root):   Elektronik | Makanan & Minuman
   ├── Level 2 (Sub):    Perangkat Dapur | Cemilan Kering
   └── Level 3 (Leaf):   Blender & Juicer | Keripik Pedas
       └── Produk seller terikat ke level 3

3. Banners (Promosi Visual)
   └── Fields: image_url, title, target_url, is_active, sort_order
```

**Admin Catalog Endpoints:**

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/admin/catalog-groups` | List semua catalog groups |
| POST | `/api/admin/catalog-groups` | Buat catalog group |
| PUT | `/api/admin/catalog-groups/{id}` | Update catalog group |
| DELETE | `/api/admin/catalog-groups/{id}` | Hapus catalog group |
| GET | `/api/admin/categories` | List semua kategori (tree) |
| POST | `/api/admin/categories` | Buat kategori |
| PUT | `/api/admin/categories/{id}` | Update kategori |
| DELETE | `/api/admin/categories/{id}` | Hapus kategori (jika tidak ada produk) |
| GET | `/api/admin/banners` | List semua banner |
| POST | `/api/admin/banners` | Buat banner |
| PUT | `/api/admin/banners/{id}` | Update banner |
| DELETE | `/api/admin/banners/{id}` | Hapus banner |

**POST /api/admin/categories — Request:**
```json
{
  "parent_id": null,        ← null = Level 1 (Root)
  "name": "Elektronik",
  "slug": "elektronik",
  "icon_url": "https://storage.example.com/icons/elektronik.svg",
  "catalog_group_id": 1     ← FK ke catalog_groups (opsional)
}
```

**Category Level Auto-Detection:**
```
parent_id = null          → level = 1
parent.level = 1          → level = 2
parent.level = 2          → level = 3
parent.level = 3          → REJECT (max 3 levels)
```

#### 5.4.2 User Management & Moderation

**Admin User Endpoints:**

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/admin/users` | List semua user (paginated, filterable) |
| GET | `/api/admin/users/{id}` | Detail user |
| POST | `/api/admin/users/{id}/ban` | Ban user |
| POST | `/api/admin/users/{id}/unban` | Unban user |
| POST | `/api/admin/users` | Buat akun admin baru |

**POST /api/admin/users/{id}/ban — Logic:**
```
1. UPDATE users SET is_banned = true WHERE id = {id}
2. DELETE FROM personal_access_tokens WHERE tokenable_id = {id}
   (Revoke SEMUA token user tersebut)
3. Return 200 OK dengan pesan konfirmasi
```

**POST /api/admin/users — Create Admin:**
```json
{
  "name": "Admin Baru",
  "email": "admin2@marketplace.com",
  "role": "admin"
}
```
> ⚠️ Admin baru tidak memiliki password karena login menggunakan Google Firebase. Email harus terdaftar di Firebase project.

---

## 6. DATABASE SCHEMA

### 6.1 Core Tables

```sql
-- USERS
CREATE TABLE users (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  firebase_uid    VARCHAR(128) UNIQUE NOT NULL,
  name            VARCHAR(255) NOT NULL,
  email           VARCHAR(255) UNIQUE NOT NULL,
  avatar_url      TEXT NULL,
  role            ENUM('buyer','seller','admin') DEFAULT 'buyer',
  is_banned       BOOLEAN DEFAULT FALSE,
  banned_at       TIMESTAMP NULL,
  banned_reason   TEXT NULL,
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- STORES (1:1 dengan users dimana role=seller)
CREATE TABLE stores (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seller_id       BIGINT UNSIGNED UNIQUE NOT NULL,  -- FK users.id
  name            VARCHAR(255) NOT NULL,
  slug            VARCHAR(255) UNIQUE NOT NULL,
  description     TEXT NULL,
  address         TEXT NULL,
  logo_url        TEXT NULL,
  status          ENUM('active','inactive') DEFAULT 'active',
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (seller_id) REFERENCES users(id)
);

-- CATALOG GROUPS
CREATE TABLE catalog_groups (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL,
  slug        VARCHAR(100) UNIQUE NOT NULL,
  icon_url    TEXT NULL,
  sort_order  INT DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- CATEGORIES (Self-Referencing, 3 Level)
CREATE TABLE categories (
  id                BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  parent_id         BIGINT UNSIGNED NULL,              -- FK categories.id
  catalog_group_id  BIGINT UNSIGNED NULL,              -- FK catalog_groups.id
  name              VARCHAR(255) NOT NULL,
  slug              VARCHAR(255) UNIQUE NOT NULL,
  icon_url          TEXT NULL,
  level             TINYINT UNSIGNED NOT NULL,          -- 1, 2, atau 3
  sort_order        INT DEFAULT 0,
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (parent_id) REFERENCES categories(id),
  FOREIGN KEY (catalog_group_id) REFERENCES catalog_groups(id)
);

-- BANNERS
CREATE TABLE banners (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(255) NOT NULL,
  image_url   TEXT NOT NULL,
  target_url  TEXT NOT NULL,      -- internal path atau external URL
  is_active   BOOLEAN DEFAULT TRUE,
  sort_order  INT DEFAULT 0,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- PRODUCTS
CREATE TABLE products (
  id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  store_id        BIGINT UNSIGNED NOT NULL,          -- FK stores.id
  category_id     BIGINT UNSIGNED NOT NULL,          -- FK categories.id (level 3 recommended)
  name            VARCHAR(255) NOT NULL,
  slug            VARCHAR(255) UNIQUE NOT NULL,
  description     TEXT NULL,
  thumbnail_url   TEXT NULL,
  is_active       BOOLEAN DEFAULT TRUE,
  deleted_at      TIMESTAMP NULL,                    -- Soft delete
  created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (store_id) REFERENCES stores(id),
  FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- PRODUCT VARIANTS
CREATE TABLE product_variants (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id  BIGINT UNSIGNED NOT NULL,              -- FK products.id
  sku         VARCHAR(100) UNIQUE NOT NULL,
  price       DECIMAL(15,2) NOT NULL,
  stock       INT UNSIGNED NOT NULL DEFAULT 0,
  weight_gram INT UNSIGNED NOT NULL DEFAULT 0,
  options     JSON NOT NULL,                         -- [{"type":"Warna","value":"Merah"}]
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- CARTS
CREATE TABLE carts (
  id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  buyer_id    BIGINT UNSIGNED UNIQUE NOT NULL,       -- FK users.id (1 cart per buyer)
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (buyer_id) REFERENCES users(id)
);

-- CART ITEMS
CREATE TABLE cart_items (
  id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cart_id             BIGINT UNSIGNED NOT NULL,      -- FK carts.id
  product_variant_id  BIGINT UNSIGNED NOT NULL,      -- FK product_variants.id
  quantity            INT UNSIGNED NOT NULL DEFAULT 1,
  created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY unique_cart_variant (cart_id, product_variant_id),
  FOREIGN KEY (cart_id) REFERENCES carts(id),
  FOREIGN KEY (product_variant_id) REFERENCES product_variants(id)
);

-- ORDERS
CREATE TABLE orders (
  id                   BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_number         VARCHAR(50) UNIQUE NOT NULL,  -- e.g. ORD-20250101-0001
  buyer_id             BIGINT UNSIGNED NOT NULL,      -- FK users.id
  store_id             BIGINT UNSIGNED NOT NULL,      -- FK stores.id
  status               ENUM('pending_payment','processing','shipped','delivered','completed','cancelled') DEFAULT 'pending_payment',
  payment_method       ENUM('cod','manual_transfer','midtrans') NOT NULL,
  payment_proof_url    TEXT NULL,                     -- untuk manual_transfer
  midtrans_snap_token  TEXT NULL,
  midtrans_va_number   VARCHAR(100) NULL,
  total_amount         DECIMAL(15,2) NOT NULL,
  shipping_address     JSON NOT NULL,
  notes                TEXT NULL,
  tracking_number      VARCHAR(100) NULL,
  courier              VARCHAR(50) NULL,
  paid_at              TIMESTAMP NULL,
  shipped_at           TIMESTAMP NULL,
  delivered_at         TIMESTAMP NULL,
  completed_at         TIMESTAMP NULL,
  cancelled_at         TIMESTAMP NULL,
  created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (buyer_id) REFERENCES users(id),
  FOREIGN KEY (store_id) REFERENCES stores(id)
);

-- ORDER ITEMS
CREATE TABLE order_items (
  id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id            BIGINT UNSIGNED NOT NULL,      -- FK orders.id
  product_variant_id  BIGINT UNSIGNED NOT NULL,      -- FK product_variants.id
  product_name        VARCHAR(255) NOT NULL,          -- snapshot nama produk saat order
  variant_options     JSON NOT NULL,                  -- snapshot opsi varian saat order
  price               DECIMAL(15,2) NOT NULL,         -- snapshot harga saat order
  quantity            INT UNSIGNED NOT NULL,
  subtotal            DECIMAL(15,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id),
  FOREIGN KEY (product_variant_id) REFERENCES product_variants(id)
);

-- SANCTUM TOKENS (Modified)
-- Tambahkan kolom device_type ke tabel default Laravel
ALTER TABLE personal_access_tokens
  ADD COLUMN device_type ENUM('mobile','desktop') NULL AFTER abilities;
```

---

## 7. API ENDPOINT CONTRACTS

### 7.1 Public Endpoints (No Auth Required)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/catalog/groups` | List semua catalog groups |
| GET | `/api/catalog/categories` | List kategori tree |
| GET | `/api/catalog/categories/{slug}` | Detail kategori |
| GET | `/api/catalog/banners` | List banner aktif |
| GET | `/api/catalog/products` | List produk (paginated, filterable) |
| GET | `/api/catalog/products/{slug}` | Detail produk + semua varian |

**Query Params for GET /api/catalog/products:**
```
?catalog_group_id=1
?category_id=15
?store_id=7
?search=kaos
?min_price=50000
?max_price=200000
?sort=price_asc|price_desc|newest|popular
?page=1
?per_page=20
```

### 7.2 Buyer Endpoints (Auth: Bearer, Role: buyer)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/buyer/cart` | Lihat cart |
| POST | `/api/buyer/cart/items` | Tambah item |
| PUT | `/api/buyer/cart/items/{id}` | Update quantity |
| DELETE | `/api/buyer/cart/items/{id}` | Hapus item |
| POST | `/api/buyer/checkout` | Checkout |
| GET | `/api/buyer/orders` | Order history |
| GET | `/api/buyer/orders/{id}` | Detail order |
| POST | `/api/buyer/orders/{id}/upload-proof` | Upload bukti transfer |
| POST | `/api/buyer/orders/{id}/confirm-received` | Konfirmasi terima barang |

### 7.3 Seller Endpoints (Auth: Bearer, Role: seller)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/seller/store` | Info store |
| PUT | `/api/seller/store` | Update store |
| GET | `/api/seller/store/dashboard` | Dashboard stats |
| GET | `/api/seller/products` | List produk |
| POST | `/api/seller/products` | Buat produk |
| GET | `/api/seller/products/{id}` | Detail produk |
| PUT | `/api/seller/products/{id}` | Update produk |
| DELETE | `/api/seller/products/{id}` | Hapus produk |
| GET | `/api/seller/orders` | List order masuk |
| GET | `/api/seller/orders/{id}` | Detail order |
| PUT | `/api/seller/orders/{id}/status` | Update status order |
| PUT | `/api/seller/orders/{id}/confirm-payment` | Konfirmasi transfer manual |

### 7.4 Admin Endpoints (Auth: Bearer, Role: admin)

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/admin/catalog-groups` | List catalog groups |
| POST | `/api/admin/catalog-groups` | Create |
| PUT | `/api/admin/catalog-groups/{id}` | Update |
| DELETE | `/api/admin/catalog-groups/{id}` | Delete |
| GET | `/api/admin/categories` | List kategori |
| POST | `/api/admin/categories` | Create |
| PUT | `/api/admin/categories/{id}` | Update |
| DELETE | `/api/admin/categories/{id}` | Delete |
| GET | `/api/admin/banners` | List banner |
| POST | `/api/admin/banners` | Create |
| PUT | `/api/admin/banners/{id}` | Update |
| DELETE | `/api/admin/banners/{id}` | Delete |
| GET | `/api/admin/users` | List users |
| GET | `/api/admin/users/{id}` | Detail user |
| POST | `/api/admin/users` | Create admin |
| POST | `/api/admin/users/{id}/ban` | Ban user |
| POST | `/api/admin/users/{id}/unban` | Unban user |

### 7.5 Webhook Endpoints

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| POST | `/api/payments/midtrans-notification` | Signature Key | Midtrans payment callback |

---

## 8. BUSINESS RULES & CONSTRAINTS

### 8.1 Critical Business Rules

```
BR-001: User baru selalu mendapat role "buyer" secara otomatis.
BR-002: Satu user = maksimal 1 store (jika role seller).
BR-003: Seller hanya bisa CRUD produk di toko miliknya sendiri.
BR-004: Cart disimpan di MySQL, bukan client-side storage.
BR-005: Cart item merujuk ke product_variant_id, bukan product_id.
BR-006: Stok dikurangi saat checkout berhasil (bukan saat add to cart).
BR-007: Order items menyimpan snapshot harga & nama produk saat order dibuat.
BR-008: Banned user: SEMUA token di-revoke, SEMUA session berakhir.
BR-009: Admin hanya bisa dibuat oleh admin lain (tidak ada registrasi publik admin).
BR-010: Kategori maksimal 3 level hierarki.
BR-011: Produk terikat ke category level 3 (leaf category).
BR-012: Multi-device login: satu akun bisa login di HP (buyer UI) dan Desktop (seller UI) bersamaan.
BR-013: Midtrans webhook harus diverifikasi signature key sebelum mengubah status order.
BR-014: Token device_type ditentukan dari header X-Device-Type saat POST /api/auth/google.
```

### 8.2 Data Integrity Rules

```
DI-001: Order number format: "ORD-YYYYMMDD-{4digit_zero_padded}"
DI-002: Product slug = auto-generated dari name (lowercase, hyphenated, unique).
DI-003: Store slug = auto-generated dari store name (lowercase, hyphenated, unique).
DI-004: Soft delete pada products (gunakan deleted_at, bukan hard delete).
DI-005: Order items tidak bisa diedit setelah order dibuat.
DI-006: Stok tidak bisa negatif (UNSIGNED INT constraint).
```

---

## 9. ERROR HANDLING STANDARDS

### 9.1 Standard Error Response Format

```json
{
  "success": false,
  "message": "Pesan error yang human-readable dalam Bahasa Indonesia",
  "error_code": "MACHINE_READABLE_CODE",
  "errors": {
    "field_name": ["Validation error message"]
  }
}
```

### 9.2 Error Code Reference

| Error Code | HTTP Status | Kondisi |
|---|---|---|
| `UNAUTHENTICATED` | 401 | Token tidak ada atau tidak valid |
| `UNAUTHORIZED` | 403 | Role tidak memiliki akses ke resource |
| `USER_BANNED` | 403 | User terkena banned |
| `NOT_FOUND` | 404 | Resource tidak ditemukan |
| `VALIDATION_ERROR` | 422 | Input tidak valid |
| `INSUFFICIENT_STOCK` | 422 | Stok tidak cukup saat checkout |
| `CART_EMPTY` | 422 | Cart kosong saat checkout |
| `STORE_NOT_FOUND` | 404 | User seller tidak memiliki store |
| `INVALID_STATUS_TRANSITION` | 422 | Perubahan status order tidak valid |
| `MIDTRANS_SIGNATURE_INVALID` | 403 | Webhook signature tidak cocok |
| `CATEGORY_MAX_LEVEL` | 422 | Mencoba buat kategori level 4 |

---

## 10. GLOSSARY

| Term | Definition |
|---|---|
| **Bearer Token** | Sanctum Personal Access Token yang dikirim via header `Authorization: Bearer <token>` |
| **CatalogGroup** | Pengelompokan tematik kategori (Harian, Belanja, dll) untuk filter homepage |
| **device_type** | Metadata token: `mobile` = tampilkan Buyer UI, `desktop` = tampilkan Seller UI |
| **Firebase UID** | Identifier unik user dari Firebase Auth, disimpan di kolom `firebase_uid` di MySQL |
| **ID Token** | JWT yang diissue Firebase setelah Google Sign-In, dikirim ke backend untuk verifikasi |
| **Leaf Category** | Kategori level 3 — tempat produk seller terikat |
| **Midtrans Snap Token** | Token dari Midtrans untuk membuka payment UI (Snap.js) |
| **Order Snapshot** | Data harga dan nama produk yang di-copy ke `order_items` saat checkout agar tidak berubah jika produk diedit kemudian |
| **Soft Delete** | Menandai record sebagai dihapus via `deleted_at` tanpa menghapus dari database |
| **VA (Virtual Account)** | Nomor rekening virtual untuk pembayaran transfer bank via Midtrans |
| **Webhook** | HTTP POST dari Midtrans ke `/api/payments/midtrans-notification` saat status pembayaran berubah |

---

> 📌 **Note for AI Agent:** Dokumen ini adalah single source of truth untuk seluruh logika bisnis, skema database, dan kontrak API. Selalu rujuk ke dokumen ini sebelum mengimplementasikan fitur baru. Jika ada ambiguitas, prioritaskan Business Rules (Section 8) di atas segalanya.
