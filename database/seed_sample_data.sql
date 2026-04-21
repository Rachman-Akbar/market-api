-- Isi data untuk tabel catalog_groups
INSERT INTO catalog_groups (id, name, slug, description, created_at, updated_at) VALUES
(1, 'Elektronik', 'elektronik', 'Produk elektronik dan gadget', NOW(), NOW()),
(2, 'Fashion', 'fashion', 'Pakaian dan aksesoris', NOW(), NOW()),
(3, 'Rumah Tangga', 'rumah-tangga', 'Peralatan rumah tangga', NOW(), NOW()),
(4, 'Olahraga', 'olahraga', 'Peralatan olahraga', NOW(), NOW()),
(5, 'Kesehatan', 'kesehatan', 'Produk kesehatan', NOW(), NOW());

-- Isi data untuk tabel categories
INSERT INTO categories (id, catalog_group_id, name, slug, description, created_at, updated_at) VALUES
(1, 1, 'Handphone', 'handphone', 'Berbagai jenis handphone', NOW(), NOW()),
(2, 1, 'Laptop', 'laptop', 'Laptop dan notebook', NOW(), NOW()),
(3, 2, 'Baju Pria', 'baju-pria', 'Pakaian pria', NOW(), NOW()),
(4, 2, 'Baju Wanita', 'baju-wanita', 'Pakaian wanita', NOW(), NOW()),
(5, 3, 'Peralatan Dapur', 'peralatan-dapur', 'Alat-alat dapur', NOW(), NOW());

-- Isi data untuk tabel users
INSERT INTO users (id, firebase_uid, email, name, avatar, is_email_verified, created_at, updated_at) VALUES
(UUID(), 'firebase_uid_1', 'user1@example.com', 'User Satu', NULL, 1, NOW(), NOW()),
(UUID(), 'firebase_uid_2', 'user2@example.com', 'User Dua', NULL, 1, NOW(), NOW()),
(UUID(), 'firebase_uid_3', 'user3@example.com', 'User Tiga', NULL, 0, NOW(), NOW()),
(UUID(), 'firebase_uid_4', 'user4@example.com', 'User Empat', NULL, 0, NOW(), NOW()),
(UUID(), 'firebase_uid_5', 'user5@example.com', 'User Lima', NULL, 1, NOW(), NOW());

-- Isi data untuk tabel products
INSERT INTO products (id, seller_id, name, slug, description, price, status, created_at, updated_at) VALUES
(1, (SELECT id FROM users LIMIT 1 OFFSET 0), 'iPhone 15', 'iphone-15', 'Smartphone terbaru dari Apple', 20000000, 'active', NOW(), NOW()),
(2, (SELECT id FROM users LIMIT 1 OFFSET 1), 'Macbook Pro', 'macbook-pro', 'Laptop powerful dari Apple', 30000000, 'active', NOW(), NOW()),
(3, (SELECT id FROM users LIMIT 1 OFFSET 2), 'Kaos Polos', 'kaos-polos', 'Kaos polos berbagai warna', 50000, 'active', NOW(), NOW()),
(4, (SELECT id FROM users LIMIT 1 OFFSET 3), 'Blender', 'blender', 'Blender dapur multifungsi', 350000, 'active', NOW(), NOW()),
(5, (SELECT id FROM users LIMIT 1 OFFSET 4), 'Sepatu Lari', 'sepatu-lari', 'Sepatu lari ringan dan nyaman', 400000, 'active', NOW(), NOW());

-- Isi data untuk tabel product_categories
INSERT INTO product_categories (product_id, category_id) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 5),
(5, 4);
