-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for kishamarket
CREATE DATABASE IF NOT EXISTS `kishamarket` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `kishamarket`;

-- Dumping structure for table kishamarket.addresses
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_id` bigint unsigned DEFAULT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `province` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_or_regency` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `district` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subdistrict` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `komerce_destination_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_addresses_user_id` (`user_id`),
  KEY `fk_addresses_store_id` (`store_id`),
  CONSTRAINT `fk_addresses_store_id` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_addresses_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.addresses: ~2 rows (approximately)
INSERT IGNORE INTO `addresses` (`id`, `user_id`, `store_id`, `country`, `province`, `city_or_regency`, `district`, `subdistrict`, `postal_code`, `full_address`, `notes`, `label`, `recipient_name`, `phone_number`, `latitude`, `longitude`, `komerce_destination_id`, `is_primary`, `created_at`, `updated_at`) VALUES
	(2, NULL, 35, 'Indonesia', 'East Java', 'Sidoarjo', 'Prambon', 'Cangkringturi', '61264', 'Cangkringturi, Prambon, Sidoarjo, East Java, 61264, Indonesia', NULL, 'Rumah Utama', 'John Doe', '081234567890', -7.45949647, 112.59939194, '31761264', 1, '2026-07-08 20:38:43', '2026-07-08 20:38:43'),
	(4, '32394b22-956f-4161-a45c-da7ded058428', NULL, 'Indonesia', 'East Java', 'Sidoarjo', 'Wonoayu', 'Simo Angin Angin', '61264', 'Simo Angin Angin, Wonoayu, Sidoarjo, East Java, 61264, Indonesia', NULL, 'Rumah Utama', 'John Doe', '081234567890', -7.43932629, 112.60052919, '31761264', 1, '2026-07-08 20:43:30', '2026-07-08 20:43:30');

-- Dumping structure for table kishamarket.banners
CREATE TABLE IF NOT EXISTS `banners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_shop_banners_store` (`store_id`,`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.banners: ~1 rows (approximately)
INSERT IGNORE INTO `banners` (`id`, `store_id`, `image_url`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 27, 'https://cdn.marketplace.com/stores/store-1/dekorasi-banner-1.jpg', 1, 1, '2026-06-21 20:34:35', '2026-06-21 20:34:35'),
	(3, 27, 'https://cdn.marketplace.com/stores/store-1/dekorasi-banner-1.jpg', 1, 1, '2026-06-22 23:47:31', '2026-06-22 23:47:31');

-- Dumping structure for table kishamarket.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.cache: ~0 rows (approximately)

-- Dumping structure for table kishamarket.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.cache_locks: ~0 rows (approximately)

-- Dumping structure for table kishamarket.carts
CREATE TABLE IF NOT EXISTS `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `carts_user_id_unique` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.carts: ~2 rows (approximately)
INSERT IGNORE INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
	(2, 'fe55a239-8462-4e8f-99e1-3755faa6507a', '2026-06-26 01:17:14', '2026-06-26 01:17:14'),
	(3, '32394b22-956f-4161-a45c-da7ded058428', '2026-06-28 19:15:37', '2026-06-28 19:15:37');

-- Dumping structure for table kishamarket.cart_items
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint unsigned NOT NULL,
  `product_variant_id` bigint unsigned NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_cart_id_variant_id_unique` (`cart_id`,`product_variant_id`),
  KEY `cart_items_variant_id_index` (`product_variant_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.cart_items: ~1 rows (approximately)
INSERT IGNORE INTO `cart_items` (`id`, `cart_id`, `product_variant_id`, `quantity`, `created_at`, `updated_at`) VALUES
	(15, 2, 2, 3, '2026-06-28 19:11:04', '2026-06-28 19:11:04'),
	(39, 3, 3, 3, '2026-07-09 01:03:49', '2026-07-09 01:03:49');

-- Dumping structure for table kishamarket.catalog_groups
CREATE TABLE IF NOT EXISTS `catalog_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `catalog_groups_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.catalog_groups: ~2 rows (approximately)
INSERT IGNORE INTO `catalog_groups` (`id`, `name`, `slug`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Elektronik & Gadget', 'elektronik-gadget', 1, '2026-07-07 04:46:44', '2026-07-07 04:46:44'),
	(2, 'Fashion & Pakaian', 'fashion-pakaian', 1, '2026-07-07 04:46:44', '2026-07-07 04:46:44'),
	(3, 'Home & Living', 'home-living', 1, '2026-07-07 04:46:44', '2026-07-07 04:46:44');

-- Dumping structure for table kishamarket.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `catalog_group_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `level` int NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_visible_in_menu` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_categories_catalog_group` (`catalog_group_id`),
  KEY `fk_categories_parent` (`parent_id`),
  CONSTRAINT `fk_categories_catalog_group` FOREIGN KEY (`catalog_group_id`) REFERENCES `catalog_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.categories: ~10 rows (approximately)
INSERT IGNORE INTO `categories` (`id`, `catalog_group_id`, `parent_id`, `level`, `sort_order`, `is_active`, `is_visible_in_menu`, `name`, `slug`, `full_slug`, `image_url`, `icon_url`, `created_at`, `updated_at`) VALUES
	(1, 1, NULL, 1, 1, 1, 1, 'Komputer & Laptop', 'komputer-laptop', 'komputer-laptop', 'https://picsum.photos/200/300', 'icon-laptop.png', '2026-07-07 04:46:44', '2026-07-07 04:46:44'),
	(2, 2, NULL, 1, 2, 1, 1, 'Pakaian Pria', 'pakaian-pria', 'pakaian-pria', 'https://picsum.photos/200/300', 'icon-man.png', '2026-07-07 04:46:44', '2026-07-07 04:46:44'),
	(3, 1, 4, 3, 1, 1, 1, 'Laptop Gaming', 'laptop-gaming', 'komputer-laptop/laptop-gaming', 'https://picsum.photos/200/300', 'icon-gaming.png', '2026-07-07 04:46:44', '2026-07-07 04:46:44'),
	(4, 2, 2, 2, 1, 1, 1, 'Kaos & Polo', 'kaos-polo', 'pakaian-pria/kaos-polo', 'https://picsum.photos/200/300', 'icon-shirt.png', '2026-07-07 04:46:44', '2026-07-07 04:46:44'),
	(5, 3, NULL, 1, 1, 1, 1, 'Dekorasi Rumah', 'dekorasi-rumah', 'dekorasi-rumah', NULL, NULL, '2026-07-07 04:54:48', '2026-07-07 04:54:48'),
	(6, 3, 5, 2, 1, 1, 1, 'Jam Dinding', 'jam-dinding', 'dekorasi-rumah/jam-dinding', NULL, NULL, '2026-07-07 04:54:48', '2026-07-07 04:54:48'),
	(74, 1, 3, 3, 1, 1, 1, 'Laptop Asus ROG', 'laptop-asus-rog', 'komputer-laptop/laptop-gaming/laptop-asus-rog', NULL, NULL, NULL, NULL),
	(75, 1, 3, 3, 2, 1, 1, 'Laptop MSI', 'laptop-msi', 'komputer-laptop/laptop-gaming/laptop-msi', NULL, NULL, NULL, NULL),
	(76, 2, 4, 3, 1, 1, 1, 'Kaos Polos Cotton Combed', 'kaos-polos-cotton-combed', 'pakaian-pria/kaos-polo/kaos-polos-cotton-combed', NULL, NULL, NULL, NULL),
	(77, 2, 4, 3, 2, 1, 1, 'Polo Shirt Bordir', 'polo-shirt-bordir', 'pakaian-pria/kaos-polo/polo-shirt-bordir', NULL, NULL, NULL, NULL);

-- Dumping structure for table kishamarket.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table kishamarket.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.jobs: ~0 rows (approximately)

-- Dumping structure for table kishamarket.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.job_batches: ~0 rows (approximately)

-- Dumping structure for table kishamarket.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.migrations: ~0 rows (approximately)

-- Dumping structure for table kishamarket.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `voucher_id` bigint unsigned DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `payment_method` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_snap_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `fk_orders_user_id` (`user_id`),
  CONSTRAINT `fk_orders_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.orders: ~4 rows (approximately)
INSERT IGNORE INTO `orders` (`id`, `order_number`, `user_id`, `voucher_id`, `total_amount`, `discount_amount`, `status`, `payment_status`, `payment_method`, `midtrans_snap_token`, `shipping_address`, `created_at`, `updated_at`) VALUES
	(4, 'ORD-20260709-146381CF', '32394b22-956f-4161-a45c-da7ded058428', NULL, 135000.00, 0.00, 'pending', 'unpaid', 'tunai_toko', NULL, 'Ambil Sendiri di Toko Utama', '2026-07-08 23:58:17', '2026-07-08 23:58:17'),
	(5, 'ORD-20260709-F2067DE6', '32394b22-956f-4161-a45c-da7ded058428', NULL, 39000000.00, 0.00, 'pending', 'unpaid', 'tunai_toko', NULL, 'Ambil Sendiri di Toko Utama', '2026-07-09 00:07:09', '2026-07-09 00:07:09'),
	(6, 'ORD-20260709-7401449C', '32394b22-956f-4161-a45c-da7ded058428', NULL, 39000000.00, 0.00, 'pending', 'unpaid', 'tunai_toko', NULL, 'Ambil Sendiri di Toko Utama', '2026-07-09 00:09:03', '2026-07-09 00:09:03'),
	(7, 'ORD-20260709-6907FDCE', '32394b22-956f-4161-a45c-da7ded058428', NULL, 135000.00, 0.00, 'pending', 'unpaid', 'tunai_toko', NULL, 'Ambil Sendiri di Toko Utama', '2026-07-09 00:09:37', '2026-07-09 00:09:37'),
	(8, 'ORD-20260709-4C8160F0', '32394b22-956f-4161-a45c-da7ded058428', NULL, 39000000.00, 0.00, 'pending', 'unpaid', 'tunai_toko', NULL, 'Ambil Sendiri di Toko Utama', '2026-07-09 00:31:33', '2026-07-09 00:31:33'),
	(9, 'ORD-20260709-AFB341F9', '32394b22-956f-4161-a45c-da7ded058428', NULL, 270000.00, 0.00, 'pending', 'unpaid', 'tunai_toko', NULL, 'Ambil Sendiri di Toko Utama', '2026-07-09 00:35:47', '2026-07-09 00:35:47'),
	(10, 'ORD-20260709-A0D47620', '32394b22-956f-4161-a45c-da7ded058428', NULL, 78025000.00, 0.00, 'pending', 'unpaid', 'transfer_manual', NULL, 'Simo Angin Angin, Wonoayu, Sidoarjo, East Java, 61264, Indonesia, Sidoarjo 61264', '2026-07-09 00:36:13', '2026-07-09 00:36:13'),
	(11, 'ORD-20260709-8D6D1F0F', '32394b22-956f-4161-a45c-da7ded058428', NULL, 150000.00, 0.00, 'pending', 'unpaid', 'transfer_manual', NULL, 'Simo Angin Angin, Wonoayu, Sidoarjo, East Java, 61264, Indonesia, Sidoarjo 61264', '2026-07-09 00:36:48', '2026-07-09 00:36:48'),
	(12, 'ORD-20260709-C3D6DE76', '32394b22-956f-4161-a45c-da7ded058428', NULL, 39015000.00, 0.00, 'pending', 'unpaid', 'midtrans', '68de5bf5-ca4b-40dd-9709-09ee0cd720f1', 'Simo Angin Angin, Wonoayu, Sidoarjo, East Java, 61264, Indonesia, Sidoarjo 61264', '2026-07-09 00:37:30', '2026-07-09 00:37:30'),
	(13, 'ORD-20260709-33F01E33', '32394b22-956f-4161-a45c-da7ded058428', NULL, 150000.00, 0.00, 'pending', 'unpaid', 'midtrans', 'b12a8b07-a892-4921-9727-3ad2615c5558', 'Simo Angin Angin, Wonoayu, Sidoarjo, East Java, 61264, Indonesia, Sidoarjo 61264', '2026-07-09 00:43:57', '2026-07-09 00:43:57'),
	(14, 'ORD-20260709-11824DCD', '32394b22-956f-4161-a45c-da7ded058428', NULL, 39015000.00, 0.00, 'pending', 'unpaid', 'midtrans', 'fcb6f7de-7c28-4079-8aeb-3594f3d7734b', 'Simo Angin Angin, Wonoayu, Sidoarjo, East Java, 61264, Indonesia, Sidoarjo 61264', '2026-07-09 01:03:58', '2026-07-09 01:03:58');

-- Dumping structure for table kishamarket.order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sub_order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_order_items_sub_order` (`sub_order_id`),
  KEY `fk_order_items_product` (`product_id`),
  CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  CONSTRAINT `fk_order_items_sub_order` FOREIGN KEY (`sub_order_id`) REFERENCES `sub_orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.order_items: ~4 rows (approximately)
INSERT IGNORE INTO `order_items` (`id`, `sub_order_id`, `product_id`, `product_name`, `sku`, `price`, `quantity`, `created_at`, `updated_at`) VALUES
	(2, 4, 2, 'Hitam - L', 'TSHIRT-BLK-L', 45000.00, 3, '2026-07-08 23:58:17', '2026-07-08 23:58:17'),
	(3, 5, 1, 'RAM 16GB / SSD 512GB', 'ROG-G14-RAM16', 19500000.00, 2, '2026-07-09 00:07:09', '2026-07-09 00:07:09'),
	(4, 6, 1, 'RAM 16GB / SSD 512GB', 'ROG-G14-RAM16', 19500000.00, 2, '2026-07-09 00:09:03', '2026-07-09 00:09:03'),
	(5, 7, 2, 'Hitam - L', 'TSHIRT-BLK-L', 45000.00, 3, '2026-07-09 00:09:37', '2026-07-09 00:09:37'),
	(6, 8, 1, 'RAM 16GB / SSD 512GB', 'ROG-G14-RAM16', 19500000.00, 2, '2026-07-09 00:31:33', '2026-07-09 00:31:33'),
	(7, 9, 2, 'Hitam - L', 'TSHIRT-BLK-L', 45000.00, 6, '2026-07-09 00:35:48', '2026-07-09 00:35:48'),
	(8, 10, 1, 'RAM 16GB / SSD 512GB', 'ROG-G14-RAM16', 19500000.00, 4, '2026-07-09 00:36:13', '2026-07-09 00:36:13'),
	(9, 11, 2, 'Hitam - L', 'TSHIRT-BLK-L', 45000.00, 3, '2026-07-09 00:36:48', '2026-07-09 00:36:48'),
	(10, 12, 1, 'RAM 16GB / SSD 512GB', 'ROG-G14-RAM16', 19500000.00, 2, '2026-07-09 00:37:30', '2026-07-09 00:37:30'),
	(11, 13, 2, 'Hitam - L', 'TSHIRT-BLK-L', 45000.00, 3, '2026-07-09 00:43:57', '2026-07-09 00:43:57'),
	(12, 14, 1, 'RAM 16GB / SSD 512GB', 'ROG-G14-RAM16', 19500000.00, 2, '2026-07-09 01:03:58', '2026-07-09 01:03:58');

-- Dumping structure for table kishamarket.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table kishamarket.payments
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payload` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_payments_order_number` (`order_number`),
  CONSTRAINT `fk_payments_order_number` FOREIGN KEY (`order_number`) REFERENCES `orders` (`order_number`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.payments: ~0 rows (approximately)

-- Dumping structure for table kishamarket.personal_access_tokens
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=178 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.personal_access_tokens: ~96 rows (approximately)
INSERT IGNORE INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
	(46, 'App\\Models\\User', '019e3a7b-ccaa-7334-a726-d66f38315f04', 'marketplace-api-pe7fibsc', '76a3876982ed7ea034a835e13f54c7db8b42ef5260899b5d90ac2bca159fd67e', '["access-api","active-role:buyer"]', '2026-05-18 02:51:05', NULL, '2026-05-18 02:47:32', '2026-05-18 02:51:05'),
	(47, 'App\\Models\\User', '019e3a7f-1f49-73ce-a168-79600626b230', 'marketplace-api-341lmyky', 'd372e3b3d9b2228cd8c3d8c00e937e05ea047cc1396120e220a3604aa44723a2', '["access-api","active-role:buyer"]', NULL, NULL, '2026-05-18 02:51:10', '2026-05-18 02:51:10'),
	(48, 'App\\Models\\User', '019e3a7f-1f49-73ce-a168-79600626b230', 'marketplace-api-ggehveov', 'd24ff0f63d1d9db020aed9cb800ccbd221f8915964fc6d60b75fdf067f8d11b9', '["access-api","active-role:buyer"]', '2026-05-18 02:55:15', NULL, '2026-05-18 02:51:45', '2026-05-18 02:55:15'),
	(49, 'App\\Models\\User', '019e3a7f-1f49-73ce-a168-79600626b230', 'marketplace-api-u26ksa3j', '01c572535d2dc85ddb8cb04348eb0d8b8d2f7be015af57b435d30f6f3013d6ad', '["access-api","active-role:seller"]', NULL, NULL, '2026-05-18 02:55:11', '2026-05-18 02:55:11'),
	(50, 'App\\Models\\User', '019e3a7f-1f49-73ce-a168-79600626b230', 'marketplace-api-am1pqpdd', 'c8b550ecaf7b7ad0f0ec1bf426d56d9ee512e4b91ecb0f143ed81d53a2421b00', '["access-api","active-role:buyer"]', '2026-05-18 03:21:49', NULL, '2026-05-18 03:21:32', '2026-05-18 03:21:49'),
	(51, 'App\\Models\\User', '019e3a7f-1f49-73ce-a168-79600626b230', 'marketplace-api-th99spbq', 'e6334d3fbdbff791eb492e072137d83e2c785ead424fffe6abf2d9479d739e85', '["access-api","active-role:buyer"]', NULL, NULL, '2026-05-18 03:23:15', '2026-05-18 03:23:15'),
	(52, 'App\\Models\\User', '019e3a7f-1f49-73ce-a168-79600626b230', 'marketplace-api-8ccnut8r', '87b38499173f66c0f610c644f2f22d9f4e3cea51491fd0803b5f8bd809628163', '["access-api","active-role:buyer"]', '2026-05-18 03:44:52', NULL, '2026-05-18 03:44:38', '2026-05-18 03:44:52'),
	(53, 'App\\Models\\User', '019e3a7f-1f49-73ce-a168-79600626b230', 'marketplace-api-vvu0flus', 'e211bf99e703af0a0dfaf6861f54bb03b85cbb92b97b4992b08a48384dbb95a6', '["access-api","active-role:buyer"]', '2026-05-18 03:53:40', NULL, '2026-05-18 03:45:31', '2026-05-18 03:53:40'),
	(54, 'App\\Models\\User', '019e3a7b-ccaa-7334-a726-d66f38315f04', 'marketplace-api-7oj7ovfk', '7bc40cab82525df69298a2a58427e416bb245a80b4d0b585d845bd3677a87b64', '["access-api","active-role:buyer"]', '2026-05-18 05:31:05', NULL, '2026-05-18 05:30:54', '2026-05-18 05:31:05'),
	(55, 'App\\Models\\User', '019e3a7b-ccaa-7334-a726-d66f38315f04', 'marketplace-api-4viwgzea', '151a48163955406cdba9aac4d59c39f1ad6f0b57fa5956f4cf2a4f5f57a78f43', '["access-api","active-role:buyer"]', '2026-05-18 05:31:46', NULL, '2026-05-18 05:30:56', '2026-05-18 05:31:46'),
	(56, 'App\\Models\\User', '019e3a7b-ccaa-7334-a726-d66f38315f04', 'marketplace-api-rkdzcxrh', '460385d172683ecdb150d089bcdaec7039c6e6009873434a428e0727a56ad38f', '["access-api","active-role:seller"]', '2026-05-18 06:03:13', NULL, '2026-05-18 05:31:40', '2026-05-18 06:03:13'),
	(57, 'App\\Models\\User', '019e3b77-0898-72a8-ac3f-4ad8d095f984', 'marketplace-api-1b8hlxx2', '6bdbaf03f2892d71838bcf204992b9d37723d02a237ed2d8074c79a28fddf9aa', '["access-api","active-role:buyer"]', '2026-05-18 07:22:06', NULL, '2026-05-18 07:21:57', '2026-05-18 07:22:06'),
	(58, 'App\\Models\\User', '019e3b77-0898-72a8-ac3f-4ad8d095f984', 'marketplace-api-sxncpq9x', '75ca7d7ad1068734f2c2f06412890bcdd9644d1ce60eca12cd628a3d8aef0529', '["access-api","active-role:buyer"]', '2026-05-18 18:05:12', NULL, '2026-05-18 07:22:02', '2026-05-18 18:05:12'),
	(59, 'App\\Models\\User', '019e3b77-0898-72a8-ac3f-4ad8d095f984', 'marketplace-api-3rgruhrm', '91e07a57c8c74716169c12820a48544913a6a43270e3c1587e83028a6e9925f0', '["access-api","active-role:seller"]', NULL, NULL, '2026-05-18 07:22:57', '2026-05-18 07:22:57'),
	(61, 'App\\Models\\User', '019e3dd1-8149-70d7-b1bc-09d9b5dda4d7', 'marketplace-web', 'b4881776dc4e5aaf7e1035e54099b72047435dac0f335d779ad3e17f117027b1', '["*"]', '2026-05-18 22:22:41', NULL, '2026-05-18 21:34:02', '2026-05-18 22:22:41'),
	(62, 'App\\Models\\User', '019e3dd1-8149-70d7-b1bc-09d9b5dda4d7', 'marketplace-api-xek7xzdt', '389684fa3cfd6736d575834aaa236667dfa41324dde4ed9db261d2eab1caed36', '["access-api","active-role:seller"]', NULL, NULL, '2026-05-18 21:34:28', '2026-05-18 21:34:28'),
	(63, 'App\\Models\\User', '019e3dd1-8149-70d7-b1bc-09d9b5dda4d7', 'marketplace-web', '86c08b84bdfc02cf7ae1ddf263b3f247ae38bf2a148eecf77582cad33e0111d7', '["*"]', NULL, NULL, '2026-05-18 22:23:36', '2026-05-18 22:23:36'),
	(65, 'App\\Models\\User', '019e3dd1-8149-70d7-b1bc-09d9b5dda4d7', 'marketplace-web', '14c3f8b438b4a934b7380ebcd7f4b31c3aa6bdb14e9844656fbe15a20bfde79d', '["*"]', '2026-05-19 00:25:48', NULL, '2026-05-19 00:25:41', '2026-05-19 00:25:48'),
	(67, 'App\\Models\\User', '019e3f31-780b-7073-890d-816445f8827e', 'marketplace-api-mez6e8se', '45997c9610716872b50e49d87f22ba96e5a75d7d33f066732c745f734784aa05', '["access-api","active-role:buyer"]', NULL, NULL, '2026-05-19 00:44:27', '2026-05-19 00:44:27'),
	(68, 'App\\Models\\User', '019e3f31-780b-7073-890d-816445f8827e', 'marketplace-api-xbtsjixu', '84a3e678d71c382f72691b12230fa49e48186bb6aa10a2df7dcdc218451eee1d', '["access-api","active-role:buyer"]', NULL, NULL, '2026-05-19 00:44:35', '2026-05-19 00:44:35'),
	(69, 'App\\Models\\User', '019e3f33-ef97-70d2-a5b7-694b20811fc0', 'marketplace-web', 'c1c4f9112ec756b79aa59e51a1131554f67e7179381f381d005ce84524696eaa', '["*"]', '2026-05-19 01:23:35', NULL, '2026-05-19 00:47:08', '2026-05-19 01:23:35'),
	(70, 'App\\Models\\User', '019e3f5f-81aa-7260-8512-945f64f78a44', 'marketplace-web', 'eea8be6ab5b6305fe218e70a176f50de63b04fbc167f6b4bea0f39bcc1c88ef4', '["*"]', '2026-05-19 01:34:47', NULL, '2026-05-19 01:34:44', '2026-05-19 01:34:47'),
	(71, 'App\\Models\\User', '019e433d-3ff6-706d-82e7-e265e07d1df3', 'marketplace-web', '5a3fd0c2b0b163e08662112a81fde33999b7a4f9b56435397348d8dc2c7bae08', '["access-api","active-role:buyer"]', NULL, NULL, '2026-05-19 19:35:49', '2026-05-19 19:35:49'),
	(72, 'App\\Models\\User', '019e433d-3ff6-706d-82e7-e265e07d1df3', 'marketplace-web', 'f605b9ed734dc67fb08afe4f2aa2cc381e197e7fdabf8cb556eaa49037f55d1d', '["access-api","active-role:buyer"]', NULL, NULL, '2026-05-19 19:37:16', '2026-05-19 19:37:16'),
	(73, 'App\\Models\\User', '019e433d-3ff6-706d-82e7-e265e07d1df3', 'marketplace-web', 'c723b6d357eda5d5675354038e5305e929d269ed01180e3abd7eb1d3cfbdc1bc', '["access-api","active-role:buyer"]', NULL, NULL, '2026-05-19 19:37:21', '2026-05-19 19:37:21'),
	(74, 'App\\Models\\User', '019e433d-3ff6-706d-82e7-e265e07d1df3', 'marketplace-web', '50b1b14b770421b2a4ad9941b3634ad0eeca2112dad57bb355ead601a0687708', '["access-api","active-role:buyer"]', NULL, NULL, '2026-05-19 20:01:13', '2026-05-19 20:01:13'),
	(75, 'App\\Models\\User', '019e433d-3ff6-706d-82e7-e265e07d1df3', 'marketplace-web', '75b457d0c297a69c84edddc4dec47ecf3708eb9d596ef349a4e8f9595a7df1e3', '["access-api","active-role:buyer"]', NULL, NULL, '2026-05-19 20:06:18', '2026-05-19 20:06:18'),
	(76, 'App\\Models\\User', '019e433d-3ff6-706d-82e7-e265e07d1df3', 'marketplace-web', 'e050a6716799f118e694ea96d1f83b9a821c5e350bea3cca5e33df6fa5896fc9', '["access-api","active-role:buyer"]', NULL, NULL, '2026-05-19 20:23:36', '2026-05-19 20:23:36'),
	(77, 'App\\Models\\User', '019e3f5f-81aa-7260-8512-945f64f78a44', 'marketplace-web', 'b7786181ce31533ee70c43bf6f3c76d7a6037f323f677e3c5317ad15eb644530', '["*"]', '2026-05-19 21:22:29', NULL, '2026-05-19 20:24:57', '2026-05-19 21:22:29'),
	(78, 'App\\Models\\User', '019e433d-3ff6-706d-82e7-e265e07d1df3', 'marketplace-web', '002f4e9e0610fb665399b6a8b411161a7a88faa15d31d1694ff2cc97c061c45f', '["*"]', '2026-05-19 21:33:46', NULL, '2026-05-19 21:33:35', '2026-05-19 21:33:46'),
	(79, 'App\\Models\\User', '019e433d-3ff6-706d-82e7-e265e07d1df3', 'marketplace-web', '8ed17109ca703a1b0fc243c11ed96e941b0e6e6093e8fd2cee32dbba7667f8f7', '["*"]', '2026-05-19 21:35:35', NULL, '2026-05-19 21:34:11', '2026-05-19 21:35:35'),
	(80, 'App\\Models\\User', '019e3f5f-81aa-7260-8512-945f64f78a44', 'marketplace-web', '7708401c5c7b9a402be3380aad96135d08f7c63dd2e0b7169b69de568c4aae52', '["*"]', '2026-05-19 21:38:51', NULL, '2026-05-19 21:38:07', '2026-05-19 21:38:51'),
	(81, 'App\\Models\\User', '019e433d-3ff6-706d-82e7-e265e07d1df3', 'marketplace-web', '70aeb9ca86a003216c674f0eb1f43c257e1345c34f2f33c43c49cfb03f59eb6b', '["*"]', '2026-05-19 22:04:23', NULL, '2026-05-19 21:39:25', '2026-05-19 22:04:23'),
	(82, 'App\\Models\\User', '019e43cc-03f2-7366-9a07-4b3a5ad07058', 'marketplace-web', '95e534b8eee860bd43fbd6b39f757865189d696664d12de47b6fce8e7e238c08', '["*"]', '2026-05-19 22:12:17', NULL, '2026-05-19 22:11:44', '2026-05-19 22:12:17'),
	(83, 'App\\Models\\User', '019e43cc-03f2-7366-9a07-4b3a5ad07058', 'marketplace-web', 'ab0543fbcb64e153cfa85baaa991d0a4cc528f6ff0223621fb75914529de2278', '["*"]', '2026-05-24 07:00:38', NULL, '2026-05-24 05:04:20', '2026-05-24 07:00:38'),
	(84, 'App\\Models\\User', '019e43cc-03f2-7366-9a07-4b3a5ad07058', 'marketplace-web', '6a9163423751231e6fc73cb45b04c03ee43476a9d25e266578e1491281cecc35', '["*"]', '2026-05-24 07:20:02', NULL, '2026-05-24 07:02:59', '2026-05-24 07:20:02'),
	(85, 'App\\Models\\User', '019e43cc-03f2-7366-9a07-4b3a5ad07058', 'marketplace-web', 'b6dde9ff1f2af35443db6dc97de66b2cf4e07bdc485b4aea99177b920e43c674', '["*"]', '2026-05-24 08:15:41', NULL, '2026-05-24 08:15:25', '2026-05-24 08:15:41'),
	(86, 'App\\Models\\User', '019e43cc-03f2-7366-9a07-4b3a5ad07058', 'marketplace-web', '66f33551f761f957c73e7f9f3f9f701613610a800c2b8c9c51ae3b0182b0c82e', '["*"]', '2026-05-28 05:26:46', NULL, '2026-05-24 08:16:23', '2026-05-28 05:26:46'),
	(87, 'App\\Models\\User', '019e6ed3-6536-70bd-a1dc-e778032c2fa0', 'marketplace-web', '27544f4bad8510802821487133530f472579558065ba464ecf1c76f995cfd4fa', '["*"]', '2026-05-28 06:43:35', NULL, '2026-05-28 06:43:28', '2026-05-28 06:43:35'),
	(88, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', '9ae6ae4df4cad63c0d3dbdf57d3d4c2bad0242569bd98fc10fb0b18d3dd8032d', '["*"]', '2026-05-28 07:19:39', NULL, '2026-05-28 06:58:18', '2026-05-28 07:19:39'),
	(89, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', '129c9618cb09e35bdba838f51075ab37c3a499b8711b4041ac77418b7bc0c0b6', '["*"]', '2026-05-28 07:51:49', NULL, '2026-05-28 07:51:34', '2026-05-28 07:51:49'),
	(90, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', '911106d49b84b9d02fa6ac80e2bb903e0172cc9bd8e0d18874a964ef67c2a4e1', '["*"]', '2026-05-28 07:56:08', NULL, '2026-05-28 07:55:46', '2026-05-28 07:56:08'),
	(93, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', '8280001aedbce578aef6267fe42da4cff27c67abead6a920cfc6ab8783b8d69a', '["*"]', '2026-05-31 23:11:40', NULL, '2026-05-31 23:02:25', '2026-05-31 23:11:40'),
	(94, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', 'e94ddbd3e1162d9a2bf8f262528c51b9934bbec3633db658efc3f2fe5813e116', '["*"]', '2026-05-31 23:14:28', NULL, '2026-05-31 23:14:26', '2026-05-31 23:14:28'),
	(95, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', '75bf02d3e7542297d593730436dc76d65a10d52cd500a43feac8551c51392fad', '["*"]', '2026-05-31 23:14:37', NULL, '2026-05-31 23:14:36', '2026-05-31 23:14:37'),
	(96, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', '054ac017571f45eddb509a831f21977ff6e6c8a2eb387fc81c99997c4e9d93ce', '["*"]', '2026-05-31 23:14:44', NULL, '2026-05-31 23:14:43', '2026-05-31 23:14:44'),
	(97, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', '242cfbbc0353850ab9c3949e0c806025b00e3d4bd3dc65112420d29f0aed589c', '["*"]', '2026-05-31 23:14:46', NULL, '2026-05-31 23:14:45', '2026-05-31 23:14:46'),
	(98, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', '1d71e8f8cb332b93923650403b3090a34ee0249157916bc66f9a0ca80f7d6c7e', '["*"]', NULL, NULL, '2026-05-31 23:28:01', '2026-05-31 23:28:01'),
	(99, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', '7625118b1bc07afe6abf0234c3e3a60ae3676ba38edd2c54dfd77a52126424e3', '["*"]', '2026-05-31 23:36:04', NULL, '2026-05-31 23:31:51', '2026-05-31 23:36:04'),
	(100, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', 'ca73f31f11b4b8cba775ccb6d8865997a276e09cef6b327e2fa01849f1eaca81', '["*"]', NULL, NULL, '2026-05-31 23:36:11', '2026-05-31 23:36:11'),
	(101, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', '74e147d3d6e2297dd3c3ec66515e8154e29f76e65eeeb3a1482823d665125199', '["*"]', '2026-05-31 23:50:06', NULL, '2026-05-31 23:46:51', '2026-05-31 23:50:06'),
	(102, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', 'e9e8d759f0ed88ca9bb6bc8b8ced0b57d127672f1f7e07db72e2d3f6c2f9f174', '["*"]', NULL, NULL, '2026-05-31 23:51:02', '2026-05-31 23:51:02'),
	(103, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', 'f44da723b3a43d3a69fa9ab2ec2a85b5340dfeb992a8d2401b7ebd2397881b77', '["*"]', '2026-06-01 00:02:41', NULL, '2026-06-01 00:02:21', '2026-06-01 00:02:41'),
	(104, 'App\\Domains\\Identity\\Domain\\Entities\\User', '18a454f4-995e-44cc-ab74-662c6b67e9b6', 'thunder-client', '7a6fa9b58eed710752b5f1f41baa558d06f2237be9d187e3d5956081961747fd', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-11 06:27:58', '2026-06-11 06:27:58'),
	(105, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fd9ff9aa-4358-4873-b088-29d7c47d8798', 'marketplace-web', '274be5f6d964630442e717edecc6acd986d4dcbaa835d64bac802c1159ecb13d', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-11 06:31:02', '2026-06-11 06:31:02'),
	(106, 'App\\Domains\\Identity\\Domain\\Entities\\User', '712e352f-038d-4570-beae-83f4c2dad26b', 'marketplace-web', '8fa9b0662a0ec7e515fd468e646bc0571724e5687ec9b399ab3ab4c62fa175b7', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-11 06:32:31', '2026-06-11 06:32:31'),
	(107, 'App\\Domains\\Identity\\Domain\\Entities\\User', '712e352f-038d-4570-beae-83f4c2dad26b', 'marketplace-web', '9479640c60512ffb7e295222c9d6ca9aec93b5b647a2060f3a1d6f11a92cff22', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-11 06:33:00', '2026-06-11 06:33:00'),
	(108, 'App\\Domains\\Identity\\Domain\\Entities\\User', '4ca37b1b-c936-47ae-9167-4b3b38a062ea', 'marketplace-web', '3b5624f30a08e68c0c48922900390725010c1f1e1ae0f1b9b181eae794e3e301', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-11 06:37:54', '2026-06-11 06:37:54'),
	(109, 'App\\Domains\\Identity\\Domain\\Entities\\User', '4ca37b1b-c936-47ae-9167-4b3b38a062ea', 'marketplace-web', '3396de502b5143f6454e88756723526f6870f4ef4c3a4e903ce3741550ef9cbe', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-12 08:05:16', '2026-06-12 08:05:16'),
	(110, 'App\\Domains\\Identity\\Domain\\Entities\\User', '5ba4a4cc-395e-4604-a56e-7ae3193226e6', 'marketplace-web', '34a897adf08ec616c3fa6fbbfddde5793917f5af6c675827557ff78e0558d69f', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-16 00:27:57', '2026-06-16 00:27:57'),
	(111, 'App\\Domains\\Identity\\Domain\\Entities\\User', '5ba4a4cc-395e-4604-a56e-7ae3193226e6', 'marketplace-web', 'c90794abd8fdbe0815cf931cfaee776f6739ea972c382de97012374c16302686', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-21 21:06:36', '2026-06-21 21:06:36'),
	(116, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'marketplace-web', '585d12bafee71e10a297193a6fa71819aed473fa13b68828acf9bbb2e41acf2f', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-22 01:24:07', '2026-06-22 01:24:07'),
	(117, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'marketplace-web', 'f41675a06878277701c48edcbae33c24e3974557c0c2cb012ef2524d1c7ec4ac', '["access-api","active-role:buyer"]', '2026-06-22 18:18:16', NULL, '2026-06-22 01:25:36', '2026-06-22 18:18:16'),
	(118, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'iPhone 15 Pro', 'f5a589bc27c17257a385c9b861e60dd678d158dcf68cc3535d81db723e30fac5', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-22 20:20:42', '2026-06-22 20:20:42'),
	(119, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'marketplace-web', '06da021b10349c4af066f51f9396f666842840cd8ab54659b10483a28312a07a', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-22 20:21:16', '2026-06-22 20:21:16'),
	(120, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'marketplace-web', '631ac5de8f8efd28449fe04aa0c54e51b618bf349034eec214f53c9a8897e169', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-22 20:25:22', '2026-06-22 20:25:22'),
	(121, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'marketplace-web', '04df70e10d64ce43b2e332d2a18bfdb800cf9b52050fbd31255dc7de2f5fbe08', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-22 20:26:24', '2026-06-22 20:26:24'),
	(122, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'marketplace-web', '535fea5a7faa53f5e9710b635dd760e375099c37f2dc585852c0a814584eb187', '["access-api","active-role:buyer"]', '2026-06-22 20:43:16', NULL, '2026-06-22 20:35:15', '2026-06-22 20:43:16'),
	(123, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'marketplace-web', 'a42dea4398acc3c7cf9ddb0da7fc9835b230c673873ddddfd895176d50e908d2', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-22 20:43:36', '2026-06-22 20:43:36'),
	(125, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'Laptop-Asus', '4db58272d74635eefce5076972c2b52856a4ba0a58cbfc0f99c215f8359d12dd', '["access-api","active-role:seller"]', '2026-06-22 23:51:31', NULL, '2026-06-22 21:07:58', '2026-06-22 23:51:31'),
	(126, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'iPhone 15 Pro', '61b3c8005a62bdd7c8bfcc4340660801de3afc94eaa75d9b7ad58f46ff1f6a35', '["access-api","active-role:buyer"]', '2026-06-24 02:31:33', NULL, '2026-06-23 18:28:52', '2026-06-24 02:31:33'),
	(128, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'Laptop-Asus', 'e642120f3278131e418f1de16fde309077749de737dece6a17d80ee986b7629e', '["access-api","active-role:seller"]', '2026-06-23 21:44:56', NULL, '2026-06-23 21:07:22', '2026-06-23 21:44:56'),
	(130, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'Laptop-Asus', '34124e88fcb79367a342632cae1edaa20ba55dbd60840c84275dbee4ef36d031', '["access-api","active-role:seller"]', '2026-06-23 23:43:06', NULL, '2026-06-23 21:46:06', '2026-06-23 23:43:06'),
	(131, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'iPhone 15 Pro', '49824c3ac239fb536ff176ee1e7a04c4148a4ba16de0500c92ee9dd829904999', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-24 00:38:52', '2026-06-24 00:38:52'),
	(132, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'iPhone 15 Pro', 'a106e8ab8be1569311b8cfa59ed698309415d21bfc7b5d30a2b3b2715d6ee3c1', '["access-api","active-role:buyer"]', '2026-06-24 01:28:51', NULL, '2026-06-24 00:44:02', '2026-06-24 01:28:51'),
	(134, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'Laptop-Asus', 'a0b6477229557ec3fee88a5bb9c170eba04071e4865317523735297931ac5a61', '["access-api","active-role:seller"]', '2026-06-24 01:34:25', NULL, '2026-06-24 01:34:01', '2026-06-24 01:34:25'),
	(135, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'iPhone 15 Pro', 'ba107e5436e59a444efa6a22845819192d25ebfdbc398523cd3e0d4465039916', '["access-api","active-role:buyer"]', '2026-06-24 19:24:39', NULL, '2026-06-24 18:18:23', '2026-06-24 19:24:39'),
	(138, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'marketplace-web', '364af15d5edd1096c5d360d00a6cfaad0cb7c9408f8584ac3673713045d1f148', '["access-api","active-role:seller"]', NULL, NULL, '2026-06-24 19:31:00', '2026-06-24 19:31:00'),
	(139, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'iPhone 15 Pro', '7d09dd5f1f8b4f4dcd780f60aefc56562ef92fd8f3c088a08a6beb9bca678837', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-24 19:31:27', '2026-06-24 19:31:27'),
	(141, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'marketplace-web', '4e99d2bf5cf1bb4e4ce65c264c0706cb15e83925a8fe1dea8945bb660cadd507', '["access-api","active-role:admin"]', '2026-06-24 19:33:41', NULL, '2026-06-24 19:33:11', '2026-06-24 19:33:41'),
	(142, 'App\\Domains\\Identity\\Domain\\Entities\\User', '45e1e9f7-a60c-448e-bee5-9d7a7db1e7de', 'Browser Testing (Laravel Blade)', 'b5d11f4a8f8b54a1a539ef8042e328eec424db9649219dba8cfa0c94619b959e', '["access-api","active-role:buyer"]', NULL, NULL, '2026-06-24 19:37:09', '2026-06-24 19:37:09'),
	(143, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'iPhone 15 Pro', '5eea3358c0ea5e45bc0f6bdadde411c9fb882a42e91320bb505192b074d7536e', '["access-api","active-role:buyer"]', '2026-06-26 02:25:55', NULL, '2026-06-26 01:14:51', '2026-06-26 02:25:55'),
	(144, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'iPhone 15 Pro', '042005ac64652ae6d99687fdc28a8bf6387cef0f8a824d0cfbf4600ad50e8ce8', '["access-api","active-role:buyer"]', '2026-06-28 19:12:04', NULL, '2026-06-28 19:08:20', '2026-06-28 19:12:04'),
	(146, 'App\\Domains\\Identity\\Domain\\Entities\\User', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'Laptop-Asus', '0350c20da54271bfd72f7f35fd5a8a3eeeb74a2798ae98c7f64e97e6bae0aed4', '["access-api","active-role:admin"]', '2026-06-28 19:14:25', NULL, '2026-06-28 19:14:10', '2026-06-28 19:14:25'),
	(147, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'iPhone 15 Pro', '15a450ae032871df2a7dea4d034fa6ffc2b2c842f4b58e2dfb237404a4af0745', '["access-api","active-role:buyer"]', '2026-06-28 19:16:34', NULL, '2026-06-28 19:15:24', '2026-06-28 19:16:34'),
	(149, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'Laptop-Asus', '3485e44afdcb174012145a4643501eb473bc97950215d2a32833142ba054667b', '["access-api","active-role:seller"]', '2026-06-28 20:16:29', NULL, '2026-06-28 19:57:19', '2026-06-28 20:16:29'),
	(153, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'Laptop-Asus', 'f1df6a6b5f64fb33dc2fa9e5678b6d02f0ee27e49b14c19bc814307ce1af1ef7', '["access-api","active-role:seller"]', '2026-06-29 01:32:40', NULL, '2026-06-28 20:37:35', '2026-06-29 01:32:40'),
	(155, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'Laptop-Asus', '9600c45ee8574712036a306405470ab0828ac15c1faae87036db4a7122d6bb80', '["access-api","active-role:seller"]', NULL, NULL, '2026-06-29 00:39:07', '2026-06-29 00:39:07'),
	(157, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'Laptop-Asus', '1eff7fe581905bcd1182c05f53d75d717dafde159f788a80abc451784194baa4', '["access-api","active-role:seller"]', '2026-06-29 01:34:34', NULL, '2026-06-29 01:33:15', '2026-06-29 01:34:34'),
	(159, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'Laptop-Asus', 'f4e23d5c45ae9ec001820c6500f8b34f4f7b088bbaef9f4e3e6263b1c5be5d06', '["access-api","active-role:seller"]', '2026-06-29 02:24:51', NULL, '2026-06-29 01:36:59', '2026-06-29 02:24:51'),
	(161, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'Laptop-Asus', '094c54f8388592627a8fba0668e9179939576c2f0bdbd34375ba51ae4cff07b4', '["access-api","active-role:seller"]', '2026-07-06 21:23:20', NULL, '2026-06-29 05:55:17', '2026-07-06 21:23:20'),
	(162, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'iPhone 15 Pro', 'dd558e77efc682f943c3502313981bcdc80e3ddfbe665aeeb2573eadb507a8b4', '["access-api","active-role:buyer"]', '2026-07-08 19:16:31', NULL, '2026-06-30 21:05:56', '2026-07-08 19:16:31'),
	(164, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'Laptop-Asus', '59cf3717b4e47384488ceebbf4247857b2e7517c23a957ad169575d76c4afddf', '["access-api","active-role:seller"]', '2026-07-02 20:39:38', NULL, '2026-07-02 20:38:57', '2026-07-02 20:39:38'),
	(165, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'iPhone 15 Pro', '63ebfb37b3c4964b4325abb1e7e163ecc76b02dd9351a09c719f1be5e8aea06c', '["access-api","active-role:buyer"]', '2026-07-02 23:16:52', NULL, '2026-07-02 23:15:56', '2026-07-02 23:16:52'),
	(168, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'Laptop-Asus', 'f0fdec1280a1445f9a48ecf1c16d0df61b3c4cf17600935f14a157f6ebac5566', '["access-api","active-role:admin"]', '2026-07-03 02:26:21', NULL, '2026-07-03 02:25:50', '2026-07-03 02:26:21'),
	(171, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'Laptop-Asus', 'a3cb38c7a22e3f5d1326bd4d6ea07c2ea162d0922d9303cd0ed3a44de64ea6cf', '["access-api","active-role:seller"]', '2026-07-03 02:31:06', NULL, '2026-07-03 02:30:50', '2026-07-03 02:31:06'),
	(172, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'iPhone 15 Pro', 'f39c25a722b55467ba193ceacfbb059197b048a67932a9dd06fab786250c9fd3', '["access-api","active-role:buyer"]', NULL, NULL, '2026-07-08 00:51:02', '2026-07-08 00:51:02'),
	(173, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'iPhone 15 Pro', '67e51cf2377ea01f61a9b9a9cd8bc9ab79df6af6ff3c22f9fd0c28dcb0b752d0', '["access-api","active-role:buyer"]', NULL, NULL, '2026-07-08 00:53:32', '2026-07-08 00:53:32'),
	(175, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'Laptop-Asus', '7ccf0271f8cea38e500a0cdc7d2ab19e61f42d3407f4aca2e7bb83faa70cfcc3', '["access-api","active-role:seller"]', '2026-07-08 20:38:43', NULL, '2026-07-08 20:26:51', '2026-07-08 20:38:43'),
	(176, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'iPhone 15 Pro', '8927d38ec6b18d05f7d76fe95473da63aba8caebe9200e390f362b9a43c69826', '["access-api","active-role:buyer"]', '2026-07-08 20:43:30', NULL, '2026-07-08 20:42:39', '2026-07-08 20:43:30'),
	(177, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'iPhone 15 Pro', '146974f5e7cd815c4f253290053e2618e774e492e361f99cc4b735cb4503122b', '["access-api","active-role:buyer"]', '2026-07-09 01:03:55', NULL, '2026-07-08 21:34:28', '2026-07-09 01:03:55');

-- Dumping structure for table kishamarket.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `primary_category_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `brand` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thumbnail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published','archived') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'published',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `products_store_name_unique` (`store_id`,`name`),
  KEY `fk_products_category` (`primary_category_id`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`primary_category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_products_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.products: ~2 rows (approximately)
INSERT IGNORE INTO `products` (`id`, `store_id`, `primary_category_id`, `name`, `slug`, `description`, `brand`, `thumbnail`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 35, 3, 'Asus ROG Zephyrus G14', 'asus-rog-zephyrus-g14', 'Laptop gaming tipis dan bertenaga tinggi dengan prosesor generasi terbaru.', 'Asus', 'https://picsum.photos/200/200', 'published', 1, '2026-07-07 04:48:45', '2026-07-07 04:48:45'),
	(2, 35, 74, 'Kaos Polos Cotton Combed 30s', 'kaos-polos-cotton-combed-30s', 'Kaos polos premium bahan katun combed 30s, adem dan nyaman dipakai seharian.', 'BasicWear', 'https://picsum.photos/200/200', 'published', 1, '2026-07-07 04:48:45', '2026-07-08 02:01:48');

-- Dumping structure for table kishamarket.product_attributes
CREATE TABLE IF NOT EXISTS `product_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'select',
  PRIMARY KEY (`id`),
  UNIQUE KEY `attributes_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.product_attributes: ~4 rows (approximately)
INSERT IGNORE INTO `product_attributes` (`id`, `name`, `slug`, `type`) VALUES
	(1, 'Bahan', 'bahan', 'text'),
	(2, 'Ukuran', 'ukuran', 'select'),
	(3, 'Warna', 'warna', 'select'),
	(4, 'Kapasitas RAM', 'kapasitas-ram', 'select');

-- Dumping structure for table kishamarket.product_attribute_values
CREATE TABLE IF NOT EXISTS `product_attribute_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pav_product` (`product_id`),
  KEY `fk_pav_attribute` (`attribute_id`),
  CONSTRAINT `fk_pav_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pav_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.product_attribute_values: ~2 rows (approximately)
INSERT IGNORE INTO `product_attribute_values` (`id`, `product_id`, `attribute_id`, `value`) VALUES
	(1, 1, 1, 'Aluminium Chassis'),
	(2, 2, 1, '100% Katun Combed 30s');

-- Dumping structure for table kishamarket.product_categories
CREATE TABLE IF NOT EXISTS `product_categories` (
  `product_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `fk_pc_category` (`category_id`),
  CONSTRAINT `fk_pc_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pc_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.product_categories: ~4 rows (approximately)
INSERT IGNORE INTO `product_categories` (`product_id`, `category_id`, `is_primary`) VALUES
	(1, 1, 0),
	(1, 76, 1),
	(2, 76, 0),
	(2, 77, 1);

-- Dumping structure for table kishamarket.product_images
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_is_primary_index` (`product_id`,`is_primary`),
  KEY `idx_product_images_sort_order` (`product_id`,`sort_order`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.product_images: ~3 rows (approximately)
INSERT IGNORE INTO `product_images` (`id`, `product_id`, `url`, `alt_text`, `is_primary`, `sort_order`, `created_at`, `updated_at`) VALUES
	(1, 1, 'https://picsum.photos/500/500?random=1', 'Tampak Depan Asus ROG', 1, 0, '2026-07-07 04:48:45', '2026-07-07 04:48:45'),
	(2, 1, 'https://picsum.photos/500/500?random=2', 'Tampak Samping Asus ROG', 0, 1, '2026-07-07 04:48:45', '2026-07-07 04:48:45'),
	(3, 2, 'https://picsum.photos/500/500?random=3', 'Kaos Hitam Depan', 1, 0, '2026-07-07 04:48:45', '2026-07-07 04:48:45');

-- Dumping structure for table kishamarket.product_variants
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `store_id` bigint unsigned NOT NULL,
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `stock` int NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `variants_store_sku_unique` (`store_id`,`sku`),
  UNIQUE KEY `variants_product_name_unique` (`product_id`,`name`),
  UNIQUE KEY `product_variants_store_id_sku_unique` (`store_id`,`sku`),
  KEY `fk_variants_product` (`product_id`),
  CONSTRAINT `fk_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_variants_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.product_variants: ~4 rows (approximately)
INSERT IGNORE INTO `product_variants` (`id`, `product_id`, `store_id`, `sku`, `name`, `price`, `stock`, `is_default`, `created_at`, `updated_at`) VALUES
	(1, 1, 35, 'ROG-G14-RAM16', 'RAM 16GB / SSD 512GB', 19500000.00, 10, 1, '2026-07-07 04:48:45', '2026-07-07 04:48:45'),
	(2, 1, 35, 'ROG-G14-RAM32', 'RAM 32GB / SSD 1TB', 24000000.00, 5, 0, '2026-07-07 04:48:45', '2026-07-07 04:48:45'),
	(3, 2, 35, 'TSHIRT-BLK-L', 'Hitam - L', 45000.00, 50, 1, '2026-07-07 04:48:45', '2026-07-07 04:48:45'),
	(4, 2, 35, 'TSHIRT-RED-XL', 'Merah - XL', 47000.00, 30, 0, '2026-07-07 04:48:45', '2026-07-07 04:48:45');

-- Dumping structure for table kishamarket.product_variant_values
CREATE TABLE IF NOT EXISTS `product_variant_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `variant_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `value` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pvv_variant` (`variant_id`),
  KEY `fk_pvv_attribute` (`attribute_id`),
  CONSTRAINT `fk_pvv_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pvv_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.product_variant_values: ~6 rows (approximately)
INSERT IGNORE INTO `product_variant_values` (`id`, `variant_id`, `attribute_id`, `value`) VALUES
	(1, 1, 4, '16GB'),
	(2, 2, 4, '32GB'),
	(3, 3, 3, 'Hitam'),
	(4, 3, 2, 'L'),
	(5, 4, 3, 'Merah'),
	(6, 4, 2, 'XL');

-- Dumping structure for table kishamarket.promotions
CREATE TABLE IF NOT EXISTS `promotions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `click_action` enum('none','product','category','url') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `target_id` bigint unsigned DEFAULT NULL,
  `target_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_promotions_active_order` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.promotions: ~1 rows (approximately)
INSERT IGNORE INTO `promotions` (`id`, `image_url`, `mobile_image_url`, `click_action`, `target_id`, `target_url`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'https://cdn.marketplace.com/promotions/promo-gajian-juni.jpg', 'https://cdn.marketplace.com/promotions/promo-gajian-juni-mobile.jpg', 'product', 8, NULL, 1, 1, '2026-06-21 20:10:24', '2026-06-21 20:10:24');

-- Dumping structure for table kishamarket.roles
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.roles: ~2 rows (approximately)
INSERT IGNORE INTO `roles` (`id`, `name`, `created_at`) VALUES
	(1, 'Buyer', '2026-06-04 12:34:54'),
	(2, 'Seller', '2026-06-04 12:34:54'),
	(3, 'admin', '2026-06-12 15:46:43');

-- Dumping structure for table kishamarket.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.sessions: ~1 rows (approximately)
INSERT IGNORE INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('Rb3v3BZZXpHWmyEp2jTciDQPPQgxVTz7xwjgFQh5', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJRV0ZPUUJQNkZvQXR4ZkRiaTUzQlFJeHFhRzY0a29wMURFeWxUZWpUIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC90ZXN0LWNoZWNrb3V0Iiwicm91dGUiOiJjaGVja291dC50ZXN0In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1783578467),
	('vrp9oz92eL9CYiqMMVoay4gzdpPbAO4kRe9oSx2j', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJIdUxiVnlud2hjaTMwTEFuWk05aHlpWWhEZ3Z6SzFBdEdNNzhyZGNLIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC90ZXN0LWZpcmViYXNlLWxvZ2luIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1783564671),
	('xLWsbQehQh3ApeWQ4kbg5OvGTE4UdvGSOWlIwS0a', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI5SmkxWEg5VUM3UG5uUXo2QnZZVktabFdGNTlrMTEwT3djUnhROHV2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC90ZXN0LWZpcmViYXNlLWxvZ2luIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1783500968);

-- Dumping structure for table kishamarket.shipping_settings
CREATE TABLE IF NOT EXISTS `shipping_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `store_latitude` decimal(10,8) NOT NULL,
  `store_longitude` decimal(11,8) NOT NULL,
  `free_shipping_max_distance` decimal(5,2) NOT NULL DEFAULT '0.00',
  `default_flat_rate` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_shipping_settings_store` (`store_id`),
  CONSTRAINT `fk_shipping_settings_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.shipping_settings: ~0 rows (approximately)

-- Dumping structure for table kishamarket.stores
CREATE TABLE IF NOT EXISTS `stores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `short_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stores_user_id_unique` (`user_id`),
  UNIQUE KEY `stores_slug_unique` (`slug`),
  CONSTRAINT `stores_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.stores: ~0 rows (approximately)
INSERT IGNORE INTO `stores` (`id`, `user_id`, `name`, `slug`, `description`, `short_description`, `phone`, `email`, `city`, `province`, `address`, `is_active`, `logo`, `created_at`, `updated_at`) VALUES
	(35, '32394b22-956f-4161-a45c-da7ded058428', 'Elekdjkdjkdkjtronik', 'elekdjkdjkdkjtronik', NULL, NULL, '081234567890', NULL, 'Sidoarjo', 'Jawa Timur', 'Jl. Raya Gedangan No. 12', 1, NULL, '2026-06-28 20:16:29', '2026-06-28 20:16:29');

-- Dumping structure for table kishamarket.store_details
CREATE TABLE IF NOT EXISTS `store_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `owner_name` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `shipping_policy` text COLLATE utf8mb4_unicode_ci,
  `return_policy` text COLLATE utf8mb4_unicode_ci,
  `open_days` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `open_time` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `close_time` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_details_store_id_unique` (`store_id`),
  CONSTRAINT `fk_store_details_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.store_details: ~0 rows (approximately)
INSERT IGNORE INTO `store_details` (`id`, `store_id`, `owner_name`, `owner_phone`, `description`, `shipping_policy`, `return_policy`, `open_days`, `open_time`, `close_time`, `whatsapp_url`, `instagram_url`, `tiktok_url`, `website_url`, `created_at`, `updated_at`) VALUES
	(6, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-28 20:16:29', '2026-06-28 20:16:29');

-- Dumping structure for table kishamarket.sub_orders
CREATE TABLE IF NOT EXISTS `sub_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `store_id` bigint unsigned NOT NULL,
  `sub_order_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_items_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `shipping_cost` decimal(12,2) NOT NULL DEFAULT '0.00',
  `courier` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `destination_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `tracking_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_order_number` (`sub_order_number`),
  KEY `fk_sub_orders_parent` (`order_id`),
  KEY `fk_sub_orders_store` (`store_id`),
  CONSTRAINT `fk_sub_orders_parent` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sub_orders_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.sub_orders: ~10 rows (approximately)
INSERT IGNORE INTO `sub_orders` (`id`, `order_id`, `store_id`, `sub_order_number`, `total_items_price`, `shipping_cost`, `courier`, `destination_id`, `status`, `tracking_number`, `created_at`, `updated_at`) VALUES
	(4, 4, 35, 'ORD-20260709-146381CF-S35', 135000.00, 0.00, 'ambil_sendiri', 'STORE-PICKUP', 'pending', NULL, '2026-07-08 23:58:17', '2026-07-08 23:58:17'),
	(5, 5, 35, 'ORD-20260709-F2067DE6-S35', 39000000.00, 0.00, 'ambil_sendiri', 'STORE-PICKUP', 'pending', NULL, '2026-07-09 00:07:09', '2026-07-09 00:07:09'),
	(6, 6, 35, 'ORD-20260709-7401449C-S35', 39000000.00, 0.00, 'ambil_sendiri', 'STORE-PICKUP', 'pending', NULL, '2026-07-09 00:09:03', '2026-07-09 00:09:03'),
	(7, 7, 35, 'ORD-20260709-6907FDCE-S35', 135000.00, 0.00, 'ambil_sendiri', 'STORE-PICKUP', 'pending', NULL, '2026-07-09 00:09:37', '2026-07-09 00:09:37'),
	(8, 8, 35, 'ORD-20260709-4C8160F0-S35', 39000000.00, 0.00, 'ambil_sendiri', 'STORE-PICKUP', 'pending', NULL, '2026-07-09 00:31:33', '2026-07-09 00:31:33'),
	(9, 9, 35, 'ORD-20260709-AFB341F9-S35', 270000.00, 0.00, 'ambil_sendiri', 'STORE-PICKUP', 'pending', NULL, '2026-07-09 00:35:48', '2026-07-09 00:35:48'),
	(10, 10, 35, 'ORD-20260709-A0D47620-S35', 78000000.00, 25000.00, 'express', '31761264', 'pending', NULL, '2026-07-09 00:36:13', '2026-07-09 00:36:13'),
	(11, 11, 35, 'ORD-20260709-8D6D1F0F-S35', 135000.00, 15000.00, 'jne', '31761264', 'pending', NULL, '2026-07-09 00:36:48', '2026-07-09 00:36:48'),
	(12, 12, 35, 'ORD-20260709-C3D6DE76-S35', 39000000.00, 15000.00, 'jne', '31761264', 'pending', NULL, '2026-07-09 00:37:30', '2026-07-09 00:37:30'),
	(13, 13, 35, 'ORD-20260709-33F01E33-S35', 135000.00, 15000.00, 'jne', '31761264', 'pending', NULL, '2026-07-09 00:43:57', '2026-07-09 00:43:57'),
	(14, 14, 35, 'ORD-20260709-11824DCD-S35', 39000000.00, 15000.00, 'jne', '31761264', 'pending', NULL, '2026-07-09 01:03:58', '2026-07-09 01:03:58');

-- Dumping structure for table kishamarket.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firebase_uid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_firebase_uid_unique` (`firebase_uid`),
  KEY `users_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.users: ~4 rows (approximately)
INSERT IGNORE INTO `users` (`id`, `firebase_uid`, `email`, `password`, `name`, `avatar`, `is_email_verified`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('32394b22-956f-4161-a45c-da7ded058428', NULL, 'akbarr@gmail.com', '$2y$12$mePD4yALbTrQJKnGihxV9.X9iAaBL2iv1BQO608uRe4FLfcdj1oPa', 'ss Doe', 'https://example.com/avatars/johndoe.png', 1, '2026-06-28 19:14:28', '2026-06-28 19:14:28', NULL),
	('45e1e9f7-a60c-448e-bee5-9d7a7db1e7de', 'qg3DenKlgHMJkV4OzJvzjZegkfJ3', 'akbarfahlevy39@gmail.com', '$2y$12$PSvFG/Df2VRhr5kw4tO.SOYY2zV/kH0ETOwGhJYJpMdUHyzPHRS1y', 'Mochammad Rachman Akbar Fahlevy', 'https://lh3.googleusercontent.com/a/ACg8ocLwIcW9YpN2423z6G110ndWsC_stH2vC89ST117X1UV-w1l_Q=s96-c', 1, '2026-06-24 19:37:09', '2026-06-24 19:37:09', NULL),
	('4ee11211-b153-4b7b-805d-bf838d65d802', NULL, 'aris.wijaya@example.com', '$2y$12$gNIKsaElxjgoePJJptduAutFKlHwbJto5qlrga4SLIz4BdSQD5h3O', 'Aris Wijaya', NULL, 1, '2026-06-22 00:10:49', '2026-06-22 00:10:49', NULL),
	('fe55a239-8462-4e8f-99e1-3755faa6507a', NULL, 'akbarr@gmail.nmsahgshj', '$2y$12$pvCw/4oNEg.KkfPhm1HU2.4v28FU/ojFL2hyMUbbGPvegF/OYBFu.', 'sakjaskj Doe', 'https://example.com/avatars/johndoe.png', 1, '2026-06-22 00:00:15', '2026-06-22 01:24:53', NULL);

-- Dumping structure for table kishamarket.user_roles
CREATE TABLE IF NOT EXISTS `user_roles` (
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `fk_user_roles_role_id` (`role_id`),
  CONSTRAINT `fk_user_roles_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table kishamarket.user_roles: ~6 rows (approximately)
INSERT IGNORE INTO `user_roles` (`user_id`, `role_id`, `created_at`, `updated_at`) VALUES
	('32394b22-956f-4161-a45c-da7ded058428', 1, '2026-06-28 19:14:28', '2026-06-28 19:14:28'),
	('32394b22-956f-4161-a45c-da7ded058428', 2, '2026-06-28 20:16:29', '2026-06-28 20:16:29'),
	('32394b22-956f-4161-a45c-da7ded058428', 3, '2026-06-22 00:10:49', '2026-06-22 00:10:49'),
	('45e1e9f7-a60c-448e-bee5-9d7a7db1e7de', 1, '2026-06-24 19:37:09', '2026-06-24 19:37:09'),
	('fe55a239-8462-4e8f-99e1-3755faa6507a', 1, '2026-06-22 00:00:15', '2026-06-22 00:00:15'),
	('fe55a239-8462-4e8f-99e1-3755faa6507a', 2, '2026-06-22 20:43:16', '2026-06-22 20:43:16');

-- Dumping structure for table kishamarket.vouchers
CREATE TABLE IF NOT EXISTS `vouchers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_type` enum('fixed','percentage') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
  `discount_value` decimal(10,2) NOT NULL,
  `min_spend` decimal(10,2) NOT NULL DEFAULT '0.00',
  `max_discount` decimal(10,2) DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `usage_limit` int unsigned NOT NULL DEFAULT '0',
  `used_count` int unsigned NOT NULL DEFAULT '0',
  `store_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `vouchers_store_id_index` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.vouchers: ~7 rows (approximately)
INSERT IGNORE INTO `vouchers` (`id`, `code`, `name`, `discount_type`, `discount_value`, `min_spend`, `max_discount`, `starts_at`, `ends_at`, `is_active`, `usage_limit`, `used_count`, `store_id`, `created_at`, `updated_at`) VALUES
	(1, 'DISKON2026', 'Promo Seru Pertengahan Tahun', 'percentage', 10.00, 50000.00, 15990.00, '2026-06-30 00:00:00', '2026-07-07 23:59:59', 1, 150, 1, NULL, '2026-06-30 20:53:14', '2026-06-30 20:54:03'),
	(2, 'DISKON202886', 'Promo Seru Pertengahan Tahun', 'percentage', 10.00, 50000.00, 15990.00, '2026-06-30 00:00:00', '2026-07-07 23:59:59', 1, 150, 0, NULL, '2026-07-03 02:20:16', '2026-07-03 02:20:16'),
	(3, 'SXSX', 'Promo Seru Pertengahan Tahun', 'percentage', 10.00, 50000.00, 15990.00, '2026-06-30 00:00:00', '2026-07-07 23:59:59', 1, 150, 0, '35', '2026-07-03 02:24:30', '2026-07-03 02:24:30'),
	(4, 'SXSJJX', 'Promo Seru Pertengahan Tahun', 'percentage', 10.00, 50000.00, 15990.00, '2026-06-30 00:00:00', '2026-07-07 23:59:59', 1, 150, 0, '35', '2026-07-03 02:26:11', '2026-07-03 02:26:11'),
	(5, 'KJBJK', 'Promo Seru Pertengahan Tahun', 'percentage', 10.00, 50000.00, 15990.00, '2026-06-30 00:00:00', '2026-07-07 23:59:59', 1, 150, 0, '35', '2026-07-03 02:27:33', '2026-07-03 02:27:33'),
	(6, 'KJJHHBJK', 'Promo Seru Pertengahan Tahun', 'percentage', 10.00, 50000.00, 15990.00, '2026-06-30 00:00:00', '2026-07-07 23:59:59', 1, 150, 0, NULL, '2026-07-03 02:30:32', '2026-07-03 02:30:32'),
	(7, 'S', 'Promo Seru Pertengahan Tahun', 'percentage', 10.00, 50000.00, 15990.00, '2026-06-30 00:00:00', '2026-07-07 23:59:59', 1, 150, 0, NULL, '2026-07-03 02:31:07', '2026-07-03 02:31:07');

-- Dumping structure for table kishamarket.wishlists
CREATE TABLE IF NOT EXISTS `wishlists` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Utama',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_wishlists_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.wishlists: ~0 rows (approximately)
INSERT IGNORE INTO `wishlists` (`id`, `user_id`, `name`, `created_at`, `updated_at`) VALUES
	('99f1ba3d-5472-4338-87c7-cb36455b99b8', 'fe55a239-8462-4e8f-99e1-3755faa6507a', 'Utama', '2026-06-25 01:26:01', '2026-06-25 01:26:01');

-- Dumping structure for table kishamarket.wishlist_items
CREATE TABLE IF NOT EXISTS `wishlist_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `wishlist_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `added_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_wishlist_product` (`wishlist_id`,`product_id`),
  KEY `fk_wi_product` (`product_id`),
  CONSTRAINT `fk_wi_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wi_wishlist` FOREIGN KEY (`wishlist_id`) REFERENCES `wishlists` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.wishlist_items: ~0 rows (approximately)
INSERT IGNORE INTO `wishlist_items` (`id`, `wishlist_id`, `product_id`, `added_at`) VALUES
	(9, '99f1ba3d-5472-4338-87c7-cb36455b99b8', 10, '2026-06-25 01:26:01');

-- Dumping structure for trigger kishamarket.before_category_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `before_category_update` BEFORE UPDATE ON `categories` FOR EACH ROW BEGIN
    -- Menggunakan NOT (OLD <=> NEW) sebagai pengganti IS DISTINCT FROM yang aman untuk nilai NULL
    IF NOT (OLD.parent_id <=> NEW.parent_id) THEN
        IF NEW.parent_id IS NULL THEN
            -- Jika parent_id diubah jadi NULL, otomatis turunkan ke level 1
            SET NEW.level = 1;
        ELSE
            -- Jika diubah ke parent baru, set level sesuai level parent + 1
            SET NEW.level = (SELECT level FROM categories WHERE id = NEW.parent_id) + 1;
        END IF;
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger kishamarket.tg_addresses_validate_owner_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tg_addresses_validate_owner_insert` BEFORE INSERT ON `addresses` FOR EACH ROW BEGIN
    IF (NEW.user_id IS NOT NULL AND NEW.store_id IS NOT NULL) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Gagal: Alamat tidak boleh memiliki user_id dan store_id sekaligus.';
    END IF;
    
    IF (NEW.user_id IS NULL AND NEW.store_id IS NULL) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Gagal: Alamat harus dikaitkan dengan kelayakan salah satu owner (user_id atau store_id).';
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger kishamarket.tg_addresses_validate_owner_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';
DELIMITER //
CREATE TRIGGER `tg_addresses_validate_owner_update` BEFORE UPDATE ON `addresses` FOR EACH ROW BEGIN
    IF (NEW.user_id IS NOT NULL AND NEW.store_id IS NOT NULL) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Gagal: Alamat tidak boleh memiliki user_id dan store_id sekaligus.';
    END IF;
    
    IF (NEW.user_id IS NULL AND NEW.store_id IS NULL) THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Gagal: Alamat harus dikaitkan dengan kelayakan salah satu owner (user_id atau store_id).';
    END IF;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
