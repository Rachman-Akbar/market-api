# RINCIAN FITUR — Marketplace

> Versi: diperbarui 16 September 2026.
> Dokumen ini berisi rincian fitur proyek marketplace sesuai kondisi terkini,
> dirinci per materi: **BUYER**, **SELLER**, **ADMIN**, **GAME**.

---

## A. BUYER

### A.1 Katalog & Produk
- List produk publik, search, filter, pagination (`catalog/products`).
- List store, banner, promotion, voucher, kategori, katalog group.
- Detail produk (varian, harga, stok, review, gambar), produk digital/PPOB.
- Filter tanggal **tidak** diterapkan pada data master/katalog (sengaja).

### A.2 Transaksi (Order)
- Checkout lengkap: pilih alamat (peta Leaflet), resolusi ongkir, apply voucher, pilih pembayaran (Midtrans / COD / transfer manual / cash toko).
- Create order: produk general, booking, pre-order (order-type `normal | booking | preorder`).
- Create order produk digital: pulsa, data, token listrik, tagihan, internet, voucher (domain PPOB).
- Cart, wishlist, voucher klaim & pakai.
- Riwayat order + filter status/`date_from`/`date_to` + konfirmasi pesanan diterima.
- Review produk yang sudah dibeli.
- Bukti pembayaran (receipt) PPOB (lihat `PPOB-RECEIPT.md`).

### A.3 Interaksi
- Chat seller (realtime, Echo + polling fallback).
- Help admin (tiket bantuan): buat tiket, lihat status, balasan.
- Notifikasi.

### A.4 Akun
- Auth: password + Google/Firebase login, verifikasi email, forgot/reset password, ubah password.
- Profil: biodata, alamat, keamanan & manajemen sesi, delete akun, switch role.
- Missions/games progress (lihat materi GAME).

---

## B. SELLER

Seller punya 2 cara pemakaian: **berjualan saja** atau **sekaligus operasional**.

### B.1 Toko & Katalog
- Register account seller + create store.
- CRUD banner, produk (varian, gambar, HPP), showcase (drag-drop), voucher, promotion (main marketplace).
- Kelola stok & stock movement/adjustment, pembelian stok, split PO/pre-order.
- Kategori & katalog group (scope admin; halaman frontend seller menyusul).

### B.2 Operasional
- CRUD bahan baku (raw materials) + restock produksi & konsumsi otomatis saat produksi.
- HPP + riwayat, cost-impact, laporan biaya bahan baku.
- Transaksi & riwayat finance, pencatatan cicilan.
- Planner/kalender produksi (schedule).
- Pelanggan: daftar + export.

### B.3 Finance & Komisi
- Dashboard penjualan: metrik, funnel, cashflow, order trend (lihat `FILTER-TANGGAL.md`).
- Pemasukan otomatis (`income`) saat sub-order selesai + komisi/admin fee + settlement seller (lihat `KOMISI-SETTLEMENT.md`).
- Utang-piutang (payable/receivable) + aging + export.
- Withdrawal & settlement approval (dengan peran admin).

### B.4 Interaksi & Laporan
- Manajemen review produk, order (bulk status + spreadsheet).
- Chat buyer, help admin.
- Import + export laporan & master data via spreadsheet (16 modul).
- Dekorasi toko. Sidebar dibuat seringkas mungkin agar mudah mengelompokkan & mengelola toko.

---

## C. ADMIN

Admin punya 2 mode: **Admin Marketplace** dan **Admin Monitoring**.

### C.1 Admin Marketplace
- CRUD kategori, katalog group, promotion, voucher, user, daily mission.
- Approve/reject store, produk, promotion & payment promotion; banned/suspend store.
- Kelola pulsa & produk PPOB (operator, pricing rules, balance/finance).
- Help admin (kelola tiket: update status, reply internal, delete).
- Chat: announcement broadcast per role/user, ikut percakapan.
- RBAC: roles & permissions CRUD.
- Fee/komisi: config admin fee, settlement & withdrawal approval.
- Review management.

### C.2 Admin Monitoring
- Dashboard statistik + order trend + top stores.
- Menampilkan diagram bagan perbandingan semua toko yang melakukan jual-beli di marketplace.
- "Monitoring Toko" per store: stats, orders, products, settlements.

---

## D. GAME (Android — Kotlin / Jetpack Compose)

### D.1 Games (7 game)
- **SDG Quiz Battle**, **Sortir Sampah (Trash Sort)**, **SDG Match (match_card)**, **Clean River**, **Myth or Fact**, **Arithmetic Kilat**, **Sudoku**.

### D.2 Integrasi Backend (aktif)
- Misi harian (daily mission) dengan `voucher_id`; event-report → insert `user_vouchers` + status `rewarded`:
  - Quiz / TrashSort / MatchCard / CleanRiver / MythFact → `POST /engagement/missions/report`.
  - Arithmetic Kilat & Sudoku → `POST /engagement/games/report` (session_id, duration, anti-cheat server-trusted: recompute skor, tolak duplikat, validasi durasi).
- Auth (register/login/me), katalog, cart, orders, `vouchers/mine`, `vouchers/{id}/claim` sudah terintegrasi.
- 5 tab UI (Home/Actions/Game/Tree/Profile), onboarding, splash, hero tree + XP/level, achievement gallery, edukasi 17 SDG.
- Penyimpanan token aman memakai **Android Keystore AES-256-GCM** (menggantikan `androidx.security:security-crypto` yang deprecated).

### D.3 Belum / Catatan
- SFX/haptic, persistensi progres lokal & unlock difficulty, tutorial per game, UI leaderboard di Android (API sudah ada), multiplayer/battle pass/seasonal event/social sharing/push notification, test otomatis Android (masih placeholder).
- Card "Clean River" di game hub sengaja di-comment-out.