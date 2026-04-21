-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 21, 2026 at 12:14 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kishamarket`
--

-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `roles` (
	`id` int NOT NULL,
	`name` varchar(255) NOT NULL,
	`created_at` timestamp NULL DEFAULT NULL,
INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'buyer', '2026-04-21 00:00:00', '2026-04-21 00:00:00'),
(2, 'seller', '2026-04-21 00:00:00', '2026-04-21 00:00:00'),
(3, 'admin', '2026-04-21 00:00:00', '2026-04-21 00:00:00'),
(4, 'courier', '2026-04-21 00:00:00', '2026-04-21 00:00:00'),
(5, 'sales', '2026-04-21 00:00:00', '2026-04-21 00:00:00');

-- Isi data untuk tabel users
INSERT INTO users (id, firebase_uid, email, name, avatar, is_email_verified, created_at, updated_at) VALUES
(UUID(), 'firebase_uid_1', 'user1@example.com', 'User Satu', NULL, 1, NOW(), NOW()),
(UUID(), 'firebase_uid_5', 'user5@example.com', 'User Lima', NULL, 1, NOW(), NOW());
-- Isi data untuk tabel user_roles
INSERT INTO user_roles (id, user_id, role_id, created_at, updated_at) VALUES
(1, (SELECT id FROM users LIMIT 1 OFFSET 0), 1, NOW(), NOW()),
(2, (SELECT id FROM users LIMIT 1 OFFSET 1), 2, NOW(), NOW()),
(3, (SELECT id FROM users LIMIT 1 OFFSET 2), 3, NOW(), NOW()),
(4, (SELECT id FROM users LIMIT 1 OFFSET 3), 4, NOW(), NOW()),
(5, (SELECT id FROM users LIMIT 1 OFFSET 4), 5, NOW(), NOW());

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

-- Isi data untuk tabel product_images
INSERT INTO product_images (id, product_id, url, is_primary, created_at, updated_at) VALUES
(1, 1, 'https://example.com/img/iphone15.jpg', 1, NOW(), NOW()),
(2, 2, 'https://example.com/img/macbookpro.jpg', 1, NOW(), NOW()),
(3, 3, 'https://example.com/img/kaospolos.jpg', 1, NOW(), NOW()),
(4, 4, 'https://example.com/img/blender.jpg', 1, NOW(), NOW()),
(5, 5, 'https://example.com/img/sepatu-lari.jpg', 1, NOW(), NOW());

-- Isi data untuk tabel stocks
INSERT INTO stocks (product_id, quantity, reserved_quantity, created_at, updated_at) VALUES
(1, 100, 5, NOW(), NOW()),
(2, 50, 2, NOW(), NOW()),
(3, 200, 10, NOW(), NOW()),
(4, 80, 3, NOW(), NOW()),
(5, 120, 7, NOW(), NOW());

-- Isi data untuk tabel carts
INSERT INTO carts (id, user_id, created_at, updated_at) VALUES
(1, (SELECT id FROM users LIMIT 1 OFFSET 0), NOW(), NOW()),
(2, (SELECT id FROM users LIMIT 1 OFFSET 1), NOW(), NOW()),
(3, (SELECT id FROM users LIMIT 1 OFFSET 2), NOW(), NOW()),
(4, (SELECT id FROM users LIMIT 1 OFFSET 3), NOW(), NOW()),
(5, (SELECT id FROM users LIMIT 1 OFFSET 4), NOW(), NOW());

-- Isi data untuk tabel cart_items
INSERT INTO cart_items (id, cart_id, product_id, qty, created_at, updated_at) VALUES
(1, 1, 1, 2, NOW(), NOW()),
(2, 2, 2, 1, NOW(), NOW()),
(3, 3, 3, 3, NOW(), NOW()),
(4, 4, 4, 1, NOW(), NOW()),
(5, 5, 5, 2, NOW(), NOW());

-- Isi data untuk tabel orders
INSERT INTO orders (id, buyer_id, seller_id, status, total_price, created_at, updated_at) VALUES
(1, (SELECT id FROM users LIMIT 1 OFFSET 0), (SELECT id FROM users LIMIT 1 OFFSET 1), 'pending_payment', 20000000, NOW(), NOW()),
(5, (SELECT id FROM users LIMIT 1 OFFSET 4), (SELECT id FROM users LIMIT 1 OFFSET 0), 'pending_payment', 400000, NOW(), NOW());

-- Isi data untuk tabel order_items
INSERT INTO order_items (id, order_id, product_id, price, qty, created_at, updated_at) VALUES
(1, 1, 1, 20000000, 1, NOW(), NOW()),
(2, 2, 2, 30000000, 1, NOW(), NOW()),
(3, 3, 3, 50000, 2, NOW(), NOW()),
-- Isi data untuk tabel payments
INSERT INTO payments (id, order_id, status, payment_method, created_at, updated_at) VALUES
(1, 1, 'pending', 'transfer', NOW(), NOW()),
(2, 2, 'pending', 'transfer', NOW(), NOW()),
(3, 3, 'pending', 'transfer', NOW(), NOW()),
(4, 4, 'pending', 'transfer', NOW(), NOW()),
(5, 5, 'pending', 'transfer', NOW(), NOW());
(1, (SELECT id FROM users LIMIT 1 OFFSET 0), 'Jl. Mawar No.1', NOW(), NOW()),
(2, (SELECT id FROM users LIMIT 1 OFFSET 1), 'Jl. Melati No.2', NOW(), NOW()),
(3, (SELECT id FROM users LIMIT 1 OFFSET 2), 'Jl. Anggrek No.3', NOW(), NOW()),
(4, (SELECT id FROM users LIMIT 1 OFFSET 3), 'Jl. Kenanga No.4', NOW(), NOW()),
(5, (SELECT id FROM users LIMIT 1 OFFSET 4), 'Jl. Dahlia No.5', NOW(), NOW());

-- Isi data untuk tabel reviews
(3, 3, (SELECT id FROM users LIMIT 1 OFFSET 2), 3, 'Cukup baik', NOW(), NOW()),
(4, 4, (SELECT id FROM users LIMIT 1 OFFSET 3), 5, 'Rekomendasi!', NOW(), NOW()),
(5, 5, (SELECT id FROM users LIMIT 1 OFFSET 4), 4, 'Oke banget', NOW(), NOW());

-- Isi data untuk tabel stocks_movements
INSERT INTO stock_movements (id, product_id, type, quantity, reference_type, reference_id, created_at, updated_at) VALUES
(1, 1, 'in', 100, 'order', 1, NOW(), NOW()),
(5, 5, 'in', 120, 'order', 5, NOW(), NOW());

-- Isi data untuk tabel stores
INSERT INTO stores (id, user_id, name, created_at, updated_at) VALUES
(1, (SELECT id FROM users LIMIT 1 OFFSET 0), 'Toko Satu', NOW(), NOW()),
(2, (SELECT id FROM users LIMIT 1 OFFSET 1), 'Toko Dua', NOW(), NOW()),
(3, (SELECT id FROM users LIMIT 1 OFFSET 2), 'Toko Tiga', NOW(), NOW()),
(1, (SELECT id FROM users LIMIT 1 OFFSET 0), 'Jl. Mawar No.1', NOW(), NOW()),
