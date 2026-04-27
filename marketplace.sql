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
DROP DATABASE IF EXISTS `kishamarket`;
CREATE DATABASE IF NOT EXISTS `kishamarket` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `kishamarket`;

-- Dumping structure for table kishamarket.addresses
DROP TABLE IF EXISTS `addresses`;
CREATE TABLE IF NOT EXISTS `addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `addresses_user_id_index` (`user_id`),
  CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.addresses: ~3 rows (approximately)
INSERT IGNORE INTO `addresses` (`id`, `user_id`, `label`, `address`, `lat`, `lng`, `created_at`, `updated_at`) VALUES
	(1, '019da96b-08e3-7005-9c1b-8a17feb799ac', 'Alamat Utama', 'Jl. Marketplace No. 1, Jakarta', -6.2000000, 106.8166000, '2026-04-19 22:44:24', '2026-04-19 22:44:24'),
	(2, '019da96b-0adb-72a5-ab39-a74b473b66ff', 'Alamat Utama', 'Jl. Marketplace No. 2, Jakarta', -6.1990000, 106.8176000, '2026-04-19 22:44:24', '2026-04-19 22:44:24'),
	(3, '019da96b-0ce0-701d-97e0-2bf4d98ce6bb', 'Alamat Utama', 'Jl. Marketplace No. 3, Jakarta', -6.1980000, 106.8186000, '2026-04-19 22:44:24', '2026-04-19 22:44:24');

-- Dumping structure for table kishamarket.banners
DROP TABLE IF EXISTS `banners`;
CREATE TABLE IF NOT EXISTS `banners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_type` enum('product','category','store','catalog_group','custom') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'custom',
  `link_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `store_id` bigint unsigned DEFAULT NULL,
  `catalog_group_id` bigint unsigned DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_banners_active_sort` (`is_active`,`sort_order`),
  KEY `idx_banners_product_id` (`product_id`),
  KEY `idx_banners_category_id` (`category_id`),
  KEY `idx_banners_store_id` (`store_id`),
  KEY `idx_banners_catalog_group_id` (`catalog_group_id`),
  CONSTRAINT `fk_banners_catalog_group` FOREIGN KEY (`catalog_group_id`) REFERENCES `catalog_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_banners_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_banners_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_banners_store` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.banners: ~5 rows (approximately)
INSERT IGNORE INTO `banners` (`id`, `title`, `subtitle`, `image_url`, `mobile_image_url`, `link_type`, `link_url`, `product_id`, `category_id`, `store_id`, `catalog_group_id`, `sort_order`, `starts_at`, `ends_at`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Belanja Elektronik Pilihan', 'Koleksi gadget dan device untuk produktivitas harian.', 'https://picsum.photos/seed/banner-electronics/1600/700', 'https://picsum.photos/seed/banner-electronics-mobile/900/1200', 'catalog_group', '/catalog-groups/electronics', NULL, NULL, NULL, 2, 1, '2026-04-23 03:34:25', NULL, 1, '2026-04-23 03:34:25', '2026-04-23 03:34:25'),
	(2, 'Fashion & Beauty Picks', 'Pilihan produk yang lebih visual untuk tampilan homepage buyer.', 'https://picsum.photos/seed/banner-fashion/1600/700', 'https://picsum.photos/seed/banner-fashion-mobile/900/1200', 'catalog_group', '/catalog-groups/fashion-beauty', NULL, NULL, NULL, 3, 2, '2026-04-23 03:34:25', NULL, 1, '2026-04-23 03:34:25', '2026-04-23 03:34:25'),
	(3, 'Promo Toko Terbaik', 'Temukan toko dengan produk dan visual yang lebih rapi.', 'https://picsum.photos/seed/banner-store/1600/700', 'https://picsum.photos/seed/banner-store-mobile/900/1200', 'store', '/stores/seller-1-store', NULL, NULL, 1, NULL, 3, '2026-04-23 03:34:25', NULL, 1, '2026-04-23 03:34:25', '2026-04-23 03:34:25'),
	(4, 'Produk Featured Minggu Ini', 'Produk unggulan yang cocok untuk section recommended.', 'https://picsum.photos/seed/banner-product/1600/700', 'https://picsum.photos/seed/banner-product-mobile/900/1200', 'product', '/products/marketplace-product-1', 1, NULL, NULL, NULL, 4, '2026-04-23 03:34:25', NULL, 1, '2026-04-23 03:34:25', '2026-04-23 03:34:25'),
	(5, 'Kategori Home Living', 'Perlengkapan rumah dan dekorasi yang enak dipandang.', 'https://picsum.photos/seed/banner-home/1600/700', 'https://picsum.photos/seed/banner-home-mobile/900/1200', 'category', '/categories/home-living', NULL, 4, NULL, NULL, 5, '2026-04-23 03:34:25', NULL, 1, '2026-04-23 03:34:25', '2026-04-23 03:34:25');

-- Dumping structure for table kishamarket.cache
DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.cache: ~0 rows (approximately)

-- Dumping structure for table kishamarket.cache_locks
DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.cache_locks: ~0 rows (approximately)

-- Dumping structure for table kishamarket.carts
DROP TABLE IF EXISTS `carts`;
CREATE TABLE IF NOT EXISTS `carts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active_user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_user_id_status_index` (`user_id`,`status`),
  KEY `carts_active_user_id_foreign` (`active_user_id`),
  CONSTRAINT `carts_active_user_id_foreign` FOREIGN KEY (`active_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.carts: ~0 rows (approximately)
INSERT IGNORE INTO `carts` (`id`, `user_id`, `active_user_id`, `status`, `created_at`, `updated_at`) VALUES
	(1, '019dc277-7239-73f0-8235-f1ca32fae41c', '019dc277-7239-73f0-8235-f1ca32fae41c', 'active', '2026-04-26 21:04:13', '2026-04-26 21:04:13');

-- Dumping structure for table kishamarket.cart_items
DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL,
  `price_snapshot` bigint unsigned NOT NULL,
  `product_name_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_image_snapshot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_cart_id_product_id_unique` (`cart_id`,`product_id`),
  KEY `cart_items_product_id_index` (`product_id`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.cart_items: ~0 rows (approximately)

-- Dumping structure for table kishamarket.catalog_groups
DROP TABLE IF EXISTS `catalog_groups`;
CREATE TABLE IF NOT EXISTS `catalog_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `catalog_groups_slug_unique` (`slug`),
  KEY `idx_catalog_groups_is_active_sort_order` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.catalog_groups: ~5 rows (approximately)
INSERT IGNORE INTO `catalog_groups` (`id`, `name`, `slug`, `description`, `image_url`, `cover_image_url`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Food & Beverage', 'food-beverage', 'Produk makanan, snack, minuman, dan kebutuhan konsumsi harian.', 'https://picsum.photos/seed/group-food/600/600', 'https://picsum.photos/seed/group-food-cover/1600/700', 1, 1, '2026-04-23 03:34:24', '2026-04-23 03:35:15'),
	(2, 'Electronics', 'electronics', 'Gadget, aksesoris, perangkat kerja, dan kebutuhan elektronik.', 'https://picsum.photos/seed/group-electronics/600/600', 'https://picsum.photos/seed/group-electronics-cover/1600/700', 2, 1, '2026-04-23 03:34:24', '2026-04-23 03:35:15'),
	(3, 'Fashion & Beauty', 'fashion-beauty', 'Produk fashion, sepatu, style, dan perawatan diri.', 'https://picsum.photos/seed/group-fashion/600/600', 'https://picsum.photos/seed/group-fashion-cover/1600/700', 3, 1, '2026-04-23 03:34:24', '2026-04-23 03:35:15'),
	(4, 'Home Living', 'home-living', 'Rumah tangga, dekorasi, dan kebutuhan living space.', 'https://picsum.photos/seed/group-home/600/600', 'https://picsum.photos/seed/group-home-cover/1600/700', 4, 1, '2026-04-23 03:34:24', '2026-04-23 03:35:15'),
	(5, 'Sports & Hobby', 'sports-hobby', 'Olahraga, buku, hobi, dan aktivitas santai.', 'https://picsum.photos/seed/group-hobby/600/600', 'https://picsum.photos/seed/group-hobby-cover/1600/700', 5, 1, '2026-04-23 03:34:24', '2026-04-23 03:35:15');

-- Dumping structure for table kishamarket.categories
DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `catalog_group_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`),
  KEY `idx_categories_catalog_group_id` (`catalog_group_id`),
  KEY `idx_categories_is_active_sort_order` (`is_active`,`sort_order`),
  CONSTRAINT `fk_categories_catalog_group` FOREIGN KEY (`catalog_group_id`) REFERENCES `catalog_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.categories: ~8 rows (approximately)
INSERT IGNORE INTO `categories` (`id`, `catalog_group_id`, `name`, `slug`, `description`, `image_url`, `cover_image_url`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Food', 'food', 'Makanan cepat saji, snack, dan kebutuhan camilan harian.', 'https://picsum.photos/seed/category-food/600/600', 'https://picsum.photos/seed/category-food-cover/1200/600', 1, 1, '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
	(2, 2, 'Electronics', 'electronics', 'Laptop, gadget, dan aksesoris elektronik untuk produktivitas.', 'https://picsum.photos/seed/category-electronics/600/600', 'https://picsum.photos/seed/category-electronics-cover/1200/600', 2, 1, '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
	(3, 3, 'Fashion', 'fashion', 'Pakaian dan style harian dengan tampilan modern.', 'https://picsum.photos/seed/category-fashion/600/600', 'https://picsum.photos/seed/category-fashion-cover/1200/600', 3, 1, '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
	(4, 4, 'Home & Living', 'home-living', 'Perlengkapan rumah, kitchenware, dan dekorasi ruang.', 'https://picsum.photos/seed/category-home/600/600', 'https://picsum.photos/seed/category-home-cover/1200/600', 4, 1, '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
	(5, 3, 'Beauty', 'beauty', 'Skincare, personal care, dan produk kecantikan.', 'https://picsum.photos/seed/category-beauty/600/600', 'https://picsum.photos/seed/category-beauty-cover/1200/600', 5, 1, '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
	(6, 5, 'Sports', 'sports', 'Peralatan olahraga dan active lifestyle.', 'https://picsum.photos/seed/category-sports/600/600', 'https://picsum.photos/seed/category-sports-cover/1200/600', 6, 1, '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
	(7, 5, 'Books', 'books', 'Buku bacaan, edukasi, dan produk hobi.', 'https://picsum.photos/seed/category-books/600/600', 'https://picsum.photos/seed/category-books-cover/1200/600', 7, 1, '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
	(8, 4, 'Garden', 'garden', 'Kebutuhan kebun, tanaman, dan alat pendukung rumah.', 'https://picsum.photos/seed/category-garden/600/600', 'https://picsum.photos/seed/category-garden-cover/1200/600', 8, 1, '2026-04-19 22:44:14', '2026-04-19 22:44:14');

-- Dumping structure for table kishamarket.failed_jobs
DROP TABLE IF EXISTS `failed_jobs`;
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
DROP TABLE IF EXISTS `jobs`;
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
DROP TABLE IF EXISTS `job_batches`;
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
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.migrations: ~10 rows (approximately)
INSERT IGNORE INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2026_04_12_081750_create_personal_access_tokens_table', 1),
	(5, '2026_04_12_120000_create_roles_and_user_roles_tables', 1),
	(6, '2026_04_12_130000_create_marketplace_core_tables', 1),
	(7, '2026_04_15_000100_align_buyer_marketplace_schema', 1),
	(8, '2026_04_16_000200_drop_unused_product_categories_table', 1),
	(9, '2026_04_20_060711_create_product_categories_table', 2),
	(10, '2026_04_20_000001_create_catalog_groups_table', 3);

-- Dumping structure for table kishamarket.password_reset_tokens
DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table kishamarket.personal_access_tokens
DROP TABLE IF EXISTS `personal_access_tokens`;
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.personal_access_tokens: ~4 rows (approximately)
INSERT IGNORE INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
	(3, 'App\\Models\\User', '019dc277-7239-73f0-8235-f1ca32fae41c', 'api-token', 'b49357400654c86e983e2a4d7fcd64a3f891b0b4fdaef62ff46331b7977b2e2d', '["role:buyer"]', NULL, NULL, '2026-04-24 19:59:19', '2026-04-24 19:59:19'),
	(5, 'App\\Models\\User', '019dc277-7239-73f0-8235-f1ca32fae41c', 'api-token', '5bd0d89176549fefbd0448c8b60b7b298b151e64aa232ac8aad5a993146fce60', '["role:buyer"]', NULL, NULL, '2026-04-24 21:25:24', '2026-04-24 21:25:24'),
	(7, 'App\\Models\\User', '019dc277-7239-73f0-8235-f1ca32fae41c', 'api-token', '41f6e2345cd946aa3ca370d30baec906e2d6f1f59c725748b4616338abfca136', '["role:buyer"]', NULL, NULL, '2026-04-24 21:27:17', '2026-04-24 21:27:17'),
	(10, 'App\\Models\\User', '019dc277-7239-73f0-8235-f1ca32fae41c', 'api-token', '5685593385f3d465ecfaa0b4254a6a9ef99d29bbc537060c2404b7c925dfbadd', '["role:buyer"]', NULL, NULL, '2026-04-26 20:47:09', '2026-04-26 20:47:09'),
	(11, 'App\\Models\\User', '019dc277-7239-73f0-8235-f1ca32fae41c', 'api-token', '56de3862ec66bc40af31dfe9e5c48a93331442f2932a7d7c3eaf84d0eff16f20', '["role:buyer"]', NULL, NULL, '2026-04-26 20:47:13', '2026-04-26 20:47:13');

-- Dumping structure for table kishamarket.products
DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned DEFAULT NULL,
  `seller_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `short_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weight_gram` int unsigned DEFAULT NULL,
  `price` decimal(14,2) NOT NULL,
  `stock` int unsigned NOT NULL DEFAULT '0',
  `thumbnail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  UNIQUE KEY `uk_products_sku` (`sku`),
  KEY `products_seller_id_status_index` (`seller_id`,`status`),
  KEY `products_store_id_foreign` (`store_id`),
  KEY `products_category_id_foreign` (`category_id`),
  KEY `idx_products_is_active_featured` (`is_active`,`is_featured`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.products: ~26 rows (approximately)
INSERT IGNORE INTO `products` (`id`, `store_id`, `category_id`, `seller_id`, `name`, `slug`, `sku`, `description`, `short_description`, `brand`, `weight_gram`, `price`, `stock`, `thumbnail`, `status`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 4, 2, '019da96b-0509-7381-9312-f18813450644', 'Marketplace Product 1', 'marketplace-product-1', 'SKU-00001', 'Description for Marketplace Product 1 in Electronics.', 'Description for Marketplace Product 1 in Electronics.', 'TechLine', 1200, 1968.74, 146, 'https://picsum.photos/seed/1_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(2, 3, 7, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 2', 'marketplace-product-2', 'SKU-00002', 'Description for Marketplace Product 2 in Books.', 'Description for Marketplace Product 2 in Books.', 'BookNest', 450, 549.76, 70, 'https://picsum.photos/seed/2_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(3, 3, 7, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 3', 'marketplace-product-3', 'SKU-00003', 'Description for Marketplace Product 3 in Books.', 'Description for Marketplace Product 3 in Books.', 'BookNest', 450, 370.05, 74, 'https://picsum.photos/seed/3_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(4, 1, 2, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 4', 'marketplace-product-4', 'SKU-00004', 'Description for Marketplace Product 4 in Electronics.', 'Description for Marketplace Product 4 in Electronics.', 'TechLine', 1200, 1514.23, 124, 'https://picsum.photos/seed/4_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(5, 4, 6, '019da96b-0509-7381-9312-f18813450644', 'Marketplace Product 5', 'marketplace-product-5', 'SKU-00005', 'Description for Marketplace Product 5 in Sports.', 'Description for Marketplace Product 5 in Sports.', 'ActiveFit', 900, 884.90, 109, 'https://picsum.photos/seed/5_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(6, 5, 2, '019da96b-06b7-70e0-adfb-035d02015b79', 'Marketplace Product 6', 'marketplace-product-6', 'SKU-00006', 'Description for Marketplace Product 6 in Electronics.', 'Description for Marketplace Product 6 in Electronics.', 'TechLine', 1200, 1813.85, 106, 'https://picsum.photos/seed/6_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(7, 1, 3, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 7', 'marketplace-product-7', 'SKU-00007', 'Description for Marketplace Product 7 in Fashion.', 'Description for Marketplace Product 7 in Fashion.', 'ModeWear', 500, 1616.87, 52, 'https://picsum.photos/seed/7_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(8, 1, 1, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 8', 'marketplace-product-8', 'SKU-00008', 'Description for Marketplace Product 8 in Food.', 'Description for Marketplace Product 8 in Food.', 'DailyChoice', 700, 40.68, 113, 'https://picsum.photos/seed/8_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(9, 3, 1, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 9', 'marketplace-product-9', 'SKU-00009', 'Description for Marketplace Product 9 in Food.', 'Description for Marketplace Product 9 in Food.', 'DailyChoice', 700, 1877.04, 140, 'https://picsum.photos/seed/9_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(10, 5, 8, '019da96b-06b7-70e0-adfb-035d02015b79', 'Marketplace Product 10', 'marketplace-product-10', 'SKU-00010', 'Description for Marketplace Product 10 in Garden.', 'Description for Marketplace Product 10 in Garden.', 'GreenSpace', 1500, 185.88, 46, 'https://picsum.photos/seed/10_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(11, 2, 4, '019da96b-0108-7193-b0f7-63ddb08a9eea', 'Marketplace Product 11', 'marketplace-product-11', 'SKU-00011', 'Description for Marketplace Product 11 in Home & Living.', 'Description for Marketplace Product 11 in Home & Living.', 'HomeEase', 1800, 1988.94, 70, 'https://picsum.photos/seed/11_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(12, 4, 5, '019da96b-0509-7381-9312-f18813450644', 'Marketplace Product 12', 'marketplace-product-12', 'SKU-00012', 'Description for Marketplace Product 12 in Beauty.', 'Description for Marketplace Product 12 in Beauty.', 'GlowCare', 300, 182.01, 20, 'https://picsum.photos/seed/12_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(13, 3, 3, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 13', 'marketplace-product-13', 'SKU-00013', 'Description for Marketplace Product 13 in Fashion.', 'Description for Marketplace Product 13 in Fashion.', 'ModeWear', 500, 1016.13, 90, 'https://picsum.photos/seed/13_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(14, 1, 7, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 14', 'marketplace-product-14', 'SKU-00014', 'Description for Marketplace Product 14 in Books.', 'Description for Marketplace Product 14 in Books.', 'BookNest', 450, 1373.39, 138, 'https://picsum.photos/seed/14_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(15, 1, 6, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 15', 'marketplace-product-15', 'SKU-00015', 'Description for Marketplace Product 15 in Sports.', 'Description for Marketplace Product 15 in Sports.', 'ActiveFit', 900, 802.22, 26, 'https://picsum.photos/seed/15_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(16, 3, 7, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 16', 'marketplace-product-16', 'SKU-00016', 'Description for Marketplace Product 16 in Books.', 'Description for Marketplace Product 16 in Books.', 'BookNest', 450, 77.29, 116, 'https://picsum.photos/seed/16_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(17, 4, 6, '019da96b-0509-7381-9312-f18813450644', 'Marketplace Product 17', 'marketplace-product-17', 'SKU-00017', 'Description for Marketplace Product 17 in Sports.', 'Description for Marketplace Product 17 in Sports.', 'ActiveFit', 900, 1285.41, 34, 'https://picsum.photos/seed/17_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(18, 3, 1, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 18', 'marketplace-product-18', 'SKU-00018', 'Description for Marketplace Product 18 in Food.', 'Description for Marketplace Product 18 in Food.', 'DailyChoice', 700, 1283.56, 29, 'https://picsum.photos/seed/18_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(19, 2, 3, '019da96b-0108-7193-b0f7-63ddb08a9eea', 'Marketplace Product 19', 'marketplace-product-19', 'SKU-00019', 'Description for Marketplace Product 19 in Fashion.', 'Description for Marketplace Product 19 in Fashion.', 'ModeWear', 500, 314.34, 46, 'https://picsum.photos/seed/19_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(20, 4, 2, '019da96b-0509-7381-9312-f18813450644', 'Marketplace Product 20', 'marketplace-product-20', 'SKU-00020', 'Description for Marketplace Product 20 in Electronics.', 'Description for Marketplace Product 20 in Electronics.', 'TechLine', 1200, 481.26, 48, 'https://picsum.photos/seed/20_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(21, 1, 1, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 21', 'marketplace-product-21', 'SKU-00021', 'Description for Marketplace Product 21 in Food.', 'Description for Marketplace Product 21 in Food.', 'DailyChoice', 700, 1459.24, 69, 'https://picsum.photos/seed/21_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(22, 2, 2, '019da96b-0108-7193-b0f7-63ddb08a9eea', 'Marketplace Product 22', 'marketplace-product-22', 'SKU-00022', 'Description for Marketplace Product 22 in Electronics.', 'Description for Marketplace Product 22 in Electronics.', 'TechLine', 1200, 1579.23, 140, 'https://picsum.photos/seed/22_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(23, 3, 7, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 23', 'marketplace-product-23', 'SKU-00023', 'Description for Marketplace Product 23 in Books.', 'Description for Marketplace Product 23 in Books.', 'BookNest', 450, 1809.98, 124, 'https://picsum.photos/seed/23_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(24, 1, 5, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 24', 'marketplace-product-24', 'SKU-00024', 'Description for Marketplace Product 24 in Beauty.', 'Description for Marketplace Product 24 in Beauty.', 'GlowCare', 300, 768.42, 54, 'https://picsum.photos/seed/24_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(25, 1, 2, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 25', 'marketplace-product-25', 'SKU-00025', 'Description for Marketplace Product 25 in Electronics.', 'Description for Marketplace Product 25 in Electronics.', 'TechLine', 1200, 682.93, 6, 'https://picsum.photos/seed/25_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(26, 2, 3, '019da96b-0108-7193-b0f7-63ddb08a9eea', 'Marketplace Product 26', 'marketplace-product-26', 'SKU-00026', 'Description for Marketplace Product 26 in Fashion.', 'Description for Marketplace Product 26 in Fashion.', 'ModeWear', 500, 1109.68, 106, 'https://picsum.photos/seed/26_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22');

-- Dumping structure for table kishamarket.product_categories
DROP TABLE IF EXISTS `product_categories`;
CREATE TABLE IF NOT EXISTS `product_categories` (
  `product_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  UNIQUE KEY `product_categories_product_id_category_id_unique` (`product_id`,`category_id`),
  KEY `product_categories_category_id_foreign` (`category_id`),
  CONSTRAINT `product_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_categories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.product_categories: ~26 rows (approximately)
INSERT IGNORE INTO `product_categories` (`product_id`, `category_id`) VALUES
	(8, 1),
	(9, 1),
	(18, 1),
	(21, 1),
	(1, 2),
	(4, 2),
	(6, 2),
	(20, 2),
	(22, 2),
	(25, 2),
	(7, 3),
	(13, 3),
	(19, 3),
	(26, 3),
	(11, 4),
	(12, 5),
	(24, 5),
	(5, 6),
	(15, 6),
	(17, 6),
	(2, 7),
	(3, 7),
	(14, 7),
	(16, 7),
	(23, 7),
	(10, 8);

-- Dumping structure for table kishamarket.product_images
DROP TABLE IF EXISTS `product_images`;
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_is_primary_index` (`product_id`,`is_primary`),
  KEY `idx_product_images_sort_order` (`product_id`,`sort_order`),
  CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.product_images: ~78 rows (approximately)
INSERT IGNORE INTO `product_images` (`id`, `product_id`, `image_url`, `url`, `alt_text`, `is_primary`, `sort_order`, `created_at`, `updated_at`) VALUES
	(1, 1, 'https://picsum.photos/seed/1_1/900/900', 'https://picsum.photos/seed/1_1/900/900', 'Marketplace Product 1 image', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(2, 1, 'https://picsum.photos/seed/1_2/900/900', 'https://picsum.photos/seed/1_2/900/900', 'Marketplace Product 1 image', 0, 2, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(3, 1, 'https://picsum.photos/seed/1_3/900/900', 'https://picsum.photos/seed/1_3/900/900', 'Marketplace Product 1 image', 0, 3, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(4, 2, 'https://picsum.photos/seed/2_1/900/900', 'https://picsum.photos/seed/2_1/900/900', 'Marketplace Product 2 image', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(5, 2, 'https://picsum.photos/seed/2_2/900/900', 'https://picsum.photos/seed/2_2/900/900', 'Marketplace Product 2 image', 0, 2, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(6, 2, 'https://picsum.photos/seed/2_3/900/900', 'https://picsum.photos/seed/2_3/900/900', 'Marketplace Product 2 image', 0, 3, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(7, 3, 'https://picsum.photos/seed/3_1/900/900', 'https://picsum.photos/seed/3_1/900/900', 'Marketplace Product 3 image', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(8, 3, 'https://picsum.photos/seed/3_2/900/900', 'https://picsum.photos/seed/3_2/900/900', 'Marketplace Product 3 image', 0, 2, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(9, 3, 'https://picsum.photos/seed/3_3/900/900', 'https://picsum.photos/seed/3_3/900/900', 'Marketplace Product 3 image', 0, 3, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(10, 4, 'https://picsum.photos/seed/4_1/900/900', 'https://picsum.photos/seed/4_1/900/900', 'Marketplace Product 4 image', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(11, 4, 'https://picsum.photos/seed/4_2/900/900', 'https://picsum.photos/seed/4_2/900/900', 'Marketplace Product 4 image', 0, 2, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(12, 4, 'https://picsum.photos/seed/4_3/900/900', 'https://picsum.photos/seed/4_3/900/900', 'Marketplace Product 4 image', 0, 3, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(13, 5, 'https://picsum.photos/seed/5_1/900/900', 'https://picsum.photos/seed/5_1/900/900', 'Marketplace Product 5 image', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(14, 5, 'https://picsum.photos/seed/5_2/900/900', 'https://picsum.photos/seed/5_2/900/900', 'Marketplace Product 5 image', 0, 2, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(15, 5, 'https://picsum.photos/seed/5_3/900/900', 'https://picsum.photos/seed/5_3/900/900', 'Marketplace Product 5 image', 0, 3, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(16, 6, 'https://picsum.photos/seed/6_1/900/900', 'https://picsum.photos/seed/6_1/900/900', 'Marketplace Product 6 image', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(17, 6, 'https://picsum.photos/seed/6_2/900/900', 'https://picsum.photos/seed/6_2/900/900', 'Marketplace Product 6 image', 0, 2, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(18, 6, 'https://picsum.photos/seed/6_3/900/900', 'https://picsum.photos/seed/6_3/900/900', 'Marketplace Product 6 image', 0, 3, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(19, 7, 'https://picsum.photos/seed/7_1/900/900', 'https://picsum.photos/seed/7_1/900/900', 'Marketplace Product 7 image', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(20, 7, 'https://picsum.photos/seed/7_2/900/900', 'https://picsum.photos/seed/7_2/900/900', 'Marketplace Product 7 image', 0, 2, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(21, 7, 'https://picsum.photos/seed/7_3/900/900', 'https://picsum.photos/seed/7_3/900/900', 'Marketplace Product 7 image', 0, 3, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(22, 8, 'https://picsum.photos/seed/8_1/900/900', 'https://picsum.photos/seed/8_1/900/900', 'Marketplace Product 8 image', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(23, 8, 'https://picsum.photos/seed/8_2/900/900', 'https://picsum.photos/seed/8_2/900/900', 'Marketplace Product 8 image', 0, 2, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(24, 8, 'https://picsum.photos/seed/8_3/900/900', 'https://picsum.photos/seed/8_3/900/900', 'Marketplace Product 8 image', 0, 3, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(25, 9, 'https://picsum.photos/seed/9_1/900/900', 'https://picsum.photos/seed/9_1/900/900', 'Marketplace Product 9 image', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(26, 9, 'https://picsum.photos/seed/9_2/900/900', 'https://picsum.photos/seed/9_2/900/900', 'Marketplace Product 9 image', 0, 2, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(27, 9, 'https://picsum.photos/seed/9_3/900/900', 'https://picsum.photos/seed/9_3/900/900', 'Marketplace Product 9 image', 0, 3, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(28, 10, 'https://picsum.photos/seed/10_1/900/900', 'https://picsum.photos/seed/10_1/900/900', 'Marketplace Product 10 image', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(29, 10, 'https://picsum.photos/seed/10_2/900/900', 'https://picsum.photos/seed/10_2/900/900', 'Marketplace Product 10 image', 0, 2, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(30, 10, 'https://picsum.photos/seed/10_3/900/900', 'https://picsum.photos/seed/10_3/900/900', 'Marketplace Product 10 image', 0, 3, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(31, 11, 'https://picsum.photos/seed/11_1/900/900', 'https://picsum.photos/seed/11_1/900/900', 'Marketplace Product 11 image', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(32, 11, 'https://picsum.photos/seed/11_2/900/900', 'https://picsum.photos/seed/11_2/900/900', 'Marketplace Product 11 image', 0, 2, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(33, 11, 'https://picsum.photos/seed/11_3/900/900', 'https://picsum.photos/seed/11_3/900/900', 'Marketplace Product 11 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(34, 12, 'https://picsum.photos/seed/12_1/900/900', 'https://picsum.photos/seed/12_1/900/900', 'Marketplace Product 12 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(35, 12, 'https://picsum.photos/seed/12_2/900/900', 'https://picsum.photos/seed/12_2/900/900', 'Marketplace Product 12 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(36, 12, 'https://picsum.photos/seed/12_3/900/900', 'https://picsum.photos/seed/12_3/900/900', 'Marketplace Product 12 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(37, 13, 'https://picsum.photos/seed/13_1/900/900', 'https://picsum.photos/seed/13_1/900/900', 'Marketplace Product 13 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(38, 13, 'https://picsum.photos/seed/13_2/900/900', 'https://picsum.photos/seed/13_2/900/900', 'Marketplace Product 13 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(39, 13, 'https://picsum.photos/seed/13_3/900/900', 'https://picsum.photos/seed/13_3/900/900', 'Marketplace Product 13 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(40, 14, 'https://picsum.photos/seed/14_1/900/900', 'https://picsum.photos/seed/14_1/900/900', 'Marketplace Product 14 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(41, 14, 'https://picsum.photos/seed/14_2/900/900', 'https://picsum.photos/seed/14_2/900/900', 'Marketplace Product 14 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(42, 14, 'https://picsum.photos/seed/14_3/900/900', 'https://picsum.photos/seed/14_3/900/900', 'Marketplace Product 14 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(43, 15, 'https://picsum.photos/seed/15_1/900/900', 'https://picsum.photos/seed/15_1/900/900', 'Marketplace Product 15 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(44, 15, 'https://picsum.photos/seed/15_2/900/900', 'https://picsum.photos/seed/15_2/900/900', 'Marketplace Product 15 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(45, 15, 'https://picsum.photos/seed/15_3/900/900', 'https://picsum.photos/seed/15_3/900/900', 'Marketplace Product 15 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(46, 16, 'https://picsum.photos/seed/16_1/900/900', 'https://picsum.photos/seed/16_1/900/900', 'Marketplace Product 16 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(47, 16, 'https://picsum.photos/seed/16_2/900/900', 'https://picsum.photos/seed/16_2/900/900', 'Marketplace Product 16 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(48, 16, 'https://picsum.photos/seed/16_3/900/900', 'https://picsum.photos/seed/16_3/900/900', 'Marketplace Product 16 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(49, 17, 'https://picsum.photos/seed/17_1/900/900', 'https://picsum.photos/seed/17_1/900/900', 'Marketplace Product 17 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(50, 17, 'https://picsum.photos/seed/17_2/900/900', 'https://picsum.photos/seed/17_2/900/900', 'Marketplace Product 17 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(51, 17, 'https://picsum.photos/seed/17_3/900/900', 'https://picsum.photos/seed/17_3/900/900', 'Marketplace Product 17 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(52, 18, 'https://picsum.photos/seed/18_1/900/900', 'https://picsum.photos/seed/18_1/900/900', 'Marketplace Product 18 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(53, 18, 'https://picsum.photos/seed/18_2/900/900', 'https://picsum.photos/seed/18_2/900/900', 'Marketplace Product 18 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(54, 18, 'https://picsum.photos/seed/18_3/900/900', 'https://picsum.photos/seed/18_3/900/900', 'Marketplace Product 18 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(55, 19, 'https://picsum.photos/seed/19_1/900/900', 'https://picsum.photos/seed/19_1/900/900', 'Marketplace Product 19 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(56, 19, 'https://picsum.photos/seed/19_2/900/900', 'https://picsum.photos/seed/19_2/900/900', 'Marketplace Product 19 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(57, 19, 'https://picsum.photos/seed/19_3/900/900', 'https://picsum.photos/seed/19_3/900/900', 'Marketplace Product 19 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(58, 20, 'https://picsum.photos/seed/20_1/900/900', 'https://picsum.photos/seed/20_1/900/900', 'Marketplace Product 20 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(59, 20, 'https://picsum.photos/seed/20_2/900/900', 'https://picsum.photos/seed/20_2/900/900', 'Marketplace Product 20 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(60, 20, 'https://picsum.photos/seed/20_3/900/900', 'https://picsum.photos/seed/20_3/900/900', 'Marketplace Product 20 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(61, 21, 'https://picsum.photos/seed/21_1/900/900', 'https://picsum.photos/seed/21_1/900/900', 'Marketplace Product 21 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(62, 21, 'https://picsum.photos/seed/21_2/900/900', 'https://picsum.photos/seed/21_2/900/900', 'Marketplace Product 21 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(63, 21, 'https://picsum.photos/seed/21_3/900/900', 'https://picsum.photos/seed/21_3/900/900', 'Marketplace Product 21 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(64, 22, 'https://picsum.photos/seed/22_1/900/900', 'https://picsum.photos/seed/22_1/900/900', 'Marketplace Product 22 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(65, 22, 'https://picsum.photos/seed/22_2/900/900', 'https://picsum.photos/seed/22_2/900/900', 'Marketplace Product 22 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(66, 22, 'https://picsum.photos/seed/22_3/900/900', 'https://picsum.photos/seed/22_3/900/900', 'Marketplace Product 22 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(67, 23, 'https://picsum.photos/seed/23_1/900/900', 'https://picsum.photos/seed/23_1/900/900', 'Marketplace Product 23 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(68, 23, 'https://picsum.photos/seed/23_2/900/900', 'https://picsum.photos/seed/23_2/900/900', 'Marketplace Product 23 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(69, 23, 'https://picsum.photos/seed/23_3/900/900', 'https://picsum.photos/seed/23_3/900/900', 'Marketplace Product 23 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(70, 24, 'https://picsum.photos/seed/24_1/900/900', 'https://picsum.photos/seed/24_1/900/900', 'Marketplace Product 24 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(71, 24, 'https://picsum.photos/seed/24_2/900/900', 'https://picsum.photos/seed/24_2/900/900', 'Marketplace Product 24 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(72, 24, 'https://picsum.photos/seed/24_3/900/900', 'https://picsum.photos/seed/24_3/900/900', 'Marketplace Product 24 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(73, 25, 'https://picsum.photos/seed/25_1/900/900', 'https://picsum.photos/seed/25_1/900/900', 'Marketplace Product 25 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(74, 25, 'https://picsum.photos/seed/25_2/900/900', 'https://picsum.photos/seed/25_2/900/900', 'Marketplace Product 25 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(75, 25, 'https://picsum.photos/seed/25_3/900/900', 'https://picsum.photos/seed/25_3/900/900', 'Marketplace Product 25 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(76, 26, 'https://picsum.photos/seed/26_1/900/900', 'https://picsum.photos/seed/26_1/900/900', 'Marketplace Product 26 image', 1, 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(77, 26, 'https://picsum.photos/seed/26_2/900/900', 'https://picsum.photos/seed/26_2/900/900', 'Marketplace Product 26 image', 0, 2, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
	(78, 26, 'https://picsum.photos/seed/26_3/900/900', 'https://picsum.photos/seed/26_3/900/900', 'Marketplace Product 26 image', 0, 3, '2026-04-19 22:44:23', '2026-04-19 22:44:23');

-- Dumping structure for table kishamarket.reviews
DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `rating` tinyint unsigned NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  KEY `reviews_product_id_rating_index` (`product_id`,`rating`),
  CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.reviews: ~68 rows (approximately)

-- Dumping structure for table kishamarket.roles
DROP TABLE IF EXISTS `roles`;
CREATE TABLE IF NOT EXISTS `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.roles: ~5 rows (approximately)
INSERT IGNORE INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
	(1, 'buyer', '2026-04-19 21:42:35', '2026-04-19 21:42:35'),
	(2, 'seller', '2026-04-19 21:42:35', '2026-04-19 21:42:35'),
	(3, 'admin', '2026-04-19 21:42:35', '2026-04-19 21:42:35'),
	(4, 'courier', '2026-04-19 21:42:35', '2026-04-19 21:42:35'),
	(5, 'sales', '2026-04-19 21:42:35', '2026-04-19 21:42:35');

-- Dumping structure for table kishamarket.sessions
DROP TABLE IF EXISTS `sessions`;
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

-- Dumping data for table kishamarket.sessions: ~3 rows (approximately)
INSERT IGNORE INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('1f6tTR9OuTMcQZv76vr2JU1ocZrxf4JFOIp8Wodg', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJoY0hMR2ExT3ZzT0hGYlFUOGRjMW5CQTc1aHFQSzNWc2FNTlh2UEkwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776670743),
	('ev7s5JCNVWA5f8AxwpPF5ZXobCByfzw6e9EujaV1', NULL, '127.0.0.1', 'PostmanRuntime/7.37.3', 'eyJfdG9rZW4iOiJhNnlVTWxUUGl1TDdyMTlzYTFCcVNVZnBVQ2JqRGEycW5qUllXeWNvIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776664754),
	('HP33qdWDpAtr2cqwRW6ItY5kIAN4hMcFD7Kn5IM3', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJmaEkya3dtWlltS2lYeVlyd0JBZFp3YWY3Z1B0TXV5UlNMTEZGcXhnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1777269692),
	('Scl8HU7PJqj8HlaJBQWAgNksUxuy9O0MyJrVaynN', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI2cFhLVFFtQ2wzOUVQbFdrZkFoaUptd2JtdXN5R0dOT0pwUmt1OXRSIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776670741);

-- Dumping structure for table kishamarket.stores
DROP TABLE IF EXISTS `stores`;
CREATE TABLE IF NOT EXISTS `stores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `short_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stores_user_id_unique` (`user_id`),
  UNIQUE KEY `stores_slug_unique` (`slug`),
  KEY `idx_stores_is_active` (`is_active`),
  CONSTRAINT `stores_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.stores: ~5 rows (approximately)
INSERT IGNORE INTO `stores` (`id`, `user_id`, `name`, `slug`, `description`, `short_description`, `phone`, `email`, `city`, `province`, `address`, `is_active`, `logo`, `banner_url`, `created_at`, `updated_at`) VALUES
	(1, '019da96a-ff4a-7167-a188-05685e19921b', 'Seller 1 Store', 'seller-1-store', 'Curated marketplace store #1.', 'Trusted marketplace store #1 dengan kurasi produk yang rapi dan visual yang lebih menarik.', '08123000001', 'seller1store@ukomp.test', 'Jakarta', 'DKI Jakarta', 'Jl. Store No. 1, Jakarta', 1, 'https://picsum.photos/seed/store1/300/300', 'https://picsum.photos/seed/store-banner-1/1600/600', '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
	(2, '019da96b-0108-7193-b0f7-63ddb08a9eea', 'Seller 2 Store', 'seller-2-store', 'Curated marketplace store #2.', 'Trusted marketplace store #2 dengan kurasi produk yang rapi dan visual yang lebih menarik.', '08123000002', 'seller2store@ukomp.test', 'Bandung', 'Jawa Barat', 'Jl. Store No. 2, Bandung', 1, 'https://picsum.photos/seed/store2/300/300', 'https://picsum.photos/seed/store-banner-2/1600/600', '2026-04-19 22:44:15', '2026-04-19 22:44:15'),
	(3, '019da96b-0352-70b2-89ef-54a033feb736', 'Seller 3 Store', 'seller-3-store', 'Curated marketplace store #3.', 'Trusted marketplace store #3 dengan kurasi produk yang rapi dan visual yang lebih menarik.', '08123000003', 'seller3store@ukomp.test', 'Surabaya', 'Jawa Timur', 'Jl. Store No. 3, Surabaya', 1, 'https://picsum.photos/seed/store3/300/300', 'https://picsum.photos/seed/store-banner-3/1600/600', '2026-04-19 22:44:15', '2026-04-19 22:44:15'),
	(4, '019da96b-0509-7381-9312-f18813450644', 'Seller 4 Store', 'seller-4-store', 'Curated marketplace store #4.', 'Trusted marketplace store #4 dengan kurasi produk yang rapi dan visual yang lebih menarik.', '08123000004', 'seller4store@ukomp.test', 'Yogyakarta', 'DI Yogyakarta', 'Jl. Store No. 4, Yogyakarta', 1, 'https://picsum.photos/seed/store4/300/300', 'https://picsum.photos/seed/store-banner-4/1600/600', '2026-04-19 22:44:16', '2026-04-19 22:44:16'),
	(5, '019da96b-06b7-70e0-adfb-035d02015b79', 'Seller 5 Store', 'seller-5-store', 'Curated marketplace store #5.', 'Trusted marketplace store #5 dengan kurasi produk yang rapi dan visual yang lebih menarik.', '08123000005', 'seller5store@ukomp.test', 'Semarang', 'Jawa Tengah', 'Jl. Store No. 5, Semarang', 1, 'https://picsum.photos/seed/store5/300/300', 'https://picsum.photos/seed/store-banner-5/1600/600', '2026-04-19 22:44:16', '2026-04-19 22:44:16');

-- Dumping structure for table kishamarket.store_details
DROP TABLE IF EXISTS `store_details`;
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.store_details: ~5 rows (approximately)
INSERT IGNORE INTO `store_details` (`id`, `store_id`, `owner_name`, `owner_phone`, `description`, `shipping_policy`, `return_policy`, `open_days`, `open_time`, `close_time`, `whatsapp_url`, `instagram_url`, `tiktok_url`, `website_url`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Seller 1', '081110000001', 'Store dengan fokus produk pilihan untuk kebutuhan populer.', 'Pesanan sebelum jam 15.00 diproses di hari yang sama.', 'Retur 3 hari untuk produk rusak saat diterima.', 'Mon-Sat', '09:00', '20:00', 'https://wa.me/6281110000001', 'https://instagram.com/seller1store', 'https://tiktok.com/@seller1store', 'https://seller1store.test', '2026-04-23 03:34:25', '2026-04-23 03:35:15'),
	(2, 2, 'Seller 2', '081110000002', 'Store dengan kurasi item rumah tangga dan lifestyle.', 'Pengiriman H+1 untuk area Jawa.', 'Retur 5 hari untuk salah kirim atau cacat produksi.', 'Mon-Sun', '09:00', '21:00', 'https://wa.me/6281110000002', 'https://instagram.com/seller2store', 'https://tiktok.com/@seller2store', 'https://seller2store.test', '2026-04-23 03:34:25', '2026-04-23 03:35:15'),
	(3, 3, 'Seller 3', '081110000003', 'Store dengan katalog visual yang cocok untuk homepage buyer.', 'Order diproses maksimal 24 jam.', 'Retur 3 hari jika produk tidak sesuai deskripsi.', 'Mon-Sat', '08:30', '19:30', 'https://wa.me/6281110000003', 'https://instagram.com/seller3store', 'https://tiktok.com/@seller3store', 'https://seller3store.test', '2026-04-23 03:34:25', '2026-04-23 03:35:15'),
	(4, 4, 'Seller 4', '081110000004', 'Store yang menonjolkan produk elektronik dan active items.', 'Packing aman dengan bubble wrap dan box tambahan.', 'Retur 7 hari untuk unit cacat produksi.', 'Mon-Sun', '10:00', '21:00', 'https://wa.me/6281110000004', 'https://instagram.com/seller4store', 'https://tiktok.com/@seller4store', 'https://seller4store.test', '2026-04-23 03:34:25', '2026-04-23 03:35:15'),
	(5, 5, 'Seller 5', '081110000005', 'Store dengan produk kategori beragam dan stok stabil.', 'Pengiriman cepat untuk kota besar.', 'Retur berlaku jika item rusak atau salah kirim.', 'Mon-Sat', '09:30', '20:30', 'https://wa.me/6281110000005', 'https://instagram.com/seller5store', 'https://tiktok.com/@seller5store', 'https://seller5store.test', '2026-04-23 03:34:25', '2026-04-23 03:35:15');

-- Dumping structure for table kishamarket.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `firebase_uid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('buyer','seller','admin') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'buyer',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_firebase_uid_unique` (`firebase_uid`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.users: ~18 rows (approximately)
INSERT IGNORE INTO `users` (`id`, `firebase_uid`, `email`, `password`, `role`, `name`, `avatar`, `is_email_verified`, `created_at`, `updated_at`) VALUES
	('019da96a-fced-70bc-91a4-b8f5f316c612', '92c52013906eff7d11ae14a92923', 'admin@ukomp.test', '$2y$12$oI60EMQTkazFQLsSsxBBgOn2M7EhypkFfyZYqCC0DFvk7EULqeV/G', 'admin', 'Marketplace Admin', NULL, 1, '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
	('019da96a-ff4a-7167-a188-05685e19921b', '0f96f24fb29f1d6a33c894ea84af', 'seller1@ukomp.test', '$2y$12$sXBRk2wfuYuVc5bUB334FuNh4XEhm9lJWUmCdYg4B/NLNTg29pH5i', 'seller', 'Seller 1', NULL, 1, '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
	('019da96b-0108-7193-b0f7-63ddb08a9eea', '0f5f464474f14ec0a13bc14f1279', 'seller2@ukomp.test', '$2y$12$OYuidfKIfHx2uxbTdsR/6eHzoGbWS0wnrqSSB985ApNoAh83mBb6O', 'seller', 'Seller 2', NULL, 1, '2026-04-19 22:44:15', '2026-04-19 22:44:15'),
	('019da96b-0352-70b2-89ef-54a033feb736', '0f9627ee5da5a5a9123901ac7e63', 'seller3@ukomp.test', '$2y$12$ANchKvuem2jKtFl6PmRAhO62k57CiG/PjwDxGnvATbgoPr.NXbxY2', 'seller', 'Seller 3', NULL, 1, '2026-04-19 22:44:15', '2026-04-19 22:44:15'),
	('019da96b-0509-7381-9312-f18813450644', 'd1c6eddeb160f2da80ca5beac00d', 'seller4@ukomp.test', '$2y$12$rOtC1nHBA08RMuQ7utAt2OpMynV9irRP.Et2HfJK5.9qKKeSa6kyC', 'seller', 'Seller 4', NULL, 1, '2026-04-19 22:44:16', '2026-04-19 22:44:16'),
	('019da96b-06b7-70e0-adfb-035d02015b79', 'bafec3baafde8da925ce7a15ad5d', 'seller5@ukomp.test', '$2y$12$aI9qp/W8wlgxmkjgKG5XBOSPoYiH20tZwpYpwKVxNHNjYo6FLHItu', 'seller', 'Seller 5', NULL, 1, '2026-04-19 22:44:16', '2026-04-19 22:44:16'),
	('019da96b-08e3-7005-9c1b-8a17feb799ac', '04c8fa7eb5da9a90b9246efc3fed', 'buyer1@ukomp.test', '$2y$12$3zXfHYah9dCd5bk0q1cB8upfjqkV9e/FrVwLk3auC5mQgmAZ6fn5a', 'buyer', 'Buyer 1', NULL, 1, '2026-04-19 22:44:17', '2026-04-19 22:44:17'),
	('019da96b-0adb-72a5-ab39-a74b473b66ff', 'c8caa23a767e1ae9b5972a0955a1', 'buyer2@ukomp.test', '$2y$12$JBKd3gi4UIiyH/Pad1ca1OmOPhlBuxI7yH64vbMCbTuvj/rTUeC7y', 'buyer', 'Buyer 2', NULL, 1, '2026-04-19 22:44:17', '2026-04-19 22:44:17'),
	('019da96b-0ce0-701d-97e0-2bf4d98ce6bb', '4fccccee9afe940f9db4afcb2b0b', 'buyer3@ukomp.test', '$2y$12$3bbibucEy0ts5gPJbORtKeuvNqcpOZ/QUR9XS0xTZFMzbQVeRS7Iy', 'buyer', 'Buyer 3', NULL, 1, '2026-04-19 22:44:18', '2026-04-19 22:44:18'),
	('019da96b-0e91-70b3-9e0d-ff2efbe12033', '4e0dbdebe00f5c1fe06853bbd359', 'buyer4@ukomp.test', '$2y$12$ojlo64LqOtjUfDzD7XsWhODr/b38Lvk/3GG0gy1sDvwrODWEhu.DS', 'buyer', 'Buyer 4', NULL, 1, '2026-04-19 22:44:18', '2026-04-19 22:44:18'),
	('019da96b-102c-703f-8a73-e5614ea6f75f', 'fec42cec870356275ac00db7031c', 'buyer5@ukomp.test', '$2y$12$RlASRi/imkzEowfCu4lehenZkvCMYyDic8L2Acf4.SetzVxPK/HqC', 'buyer', 'Buyer 5', NULL, 1, '2026-04-19 22:44:19', '2026-04-19 22:44:19'),
	('019da96b-1210-724e-8353-db2529099f65', '1804cf37141411562a974dd7b920', 'buyer6@ukomp.test', '$2y$12$GOTY4aokXrDSaQxpI2kmWu3p8Ls0Sg7B9oCI3ISPmYpaTtnoLN9l2', 'buyer', 'Buyer 6', NULL, 1, '2026-04-19 22:44:19', '2026-04-19 22:44:19'),
	('019da96b-13b5-70b3-937c-851bbcedd357', 'be55628c1a1b1f99bad1352a7dff', 'buyer7@ukomp.test', '$2y$12$MDqvZ2mH0eSyRQ.yDDETc.lPq/OryczpBtM7SgFcz4LdiFnwri/3G', 'buyer', 'Buyer 7', NULL, 1, '2026-04-19 22:44:20', '2026-04-19 22:44:20'),
	('019da96b-1568-70dc-a298-97eb551b3cab', 'd2cb33aff1eb55449a3c3dc6400f', 'buyer8@ukomp.test', '$2y$12$M24TEjce8FaqgeRFdyLsje5CazAZ3YRU/ShJgg7DakAJ6U0h/teKO', 'buyer', 'Buyer 8', NULL, 1, '2026-04-19 22:44:20', '2026-04-19 22:44:20'),
	('019da96b-1778-7212-b34b-585fa4f60d50', 'd889724358e3ab6d0386575d1399', 'buyer9@ukomp.test', '$2y$12$90H80x7aCtgVQkN1hM16VOCn20GcbZIJ63Kdlr4r5Xu08vOAMjwwi', 'buyer', 'Buyer 9', NULL, 1, '2026-04-19 22:44:21', '2026-04-19 22:44:21'),
	('019da96b-190e-72ca-b042-a86ba2c4938f', 'a77916c4807d576aebb66a0a1ef8', 'buyer10@ukomp.test', '$2y$12$rGvqlBA7pHsHav1i4f7BseuiM8nwGbsrHWQI/r0cm9TsDtcSVLmq2', 'buyer', 'Buyer 10', NULL, 1, '2026-04-19 22:44:21', '2026-04-19 22:44:21'),
	('019da96b-1aaa-7085-96a9-b2c9e7a9e0a8', 'a7874660890980d5d9d4e713a03d', 'buyer11@ukomp.test', '$2y$12$CRmjLD2/o6lPdJeLv.dO/u07ipoKuTzXlvmkoXr0y0nJ0chFKovs6', 'buyer', 'Buyer 11', NULL, 1, '2026-04-19 22:44:21', '2026-04-19 22:44:21'),
	('019da96b-1ca6-72cf-be0c-04db858da4ab', '61aaf0d98e46ee0a56500eb66a24', 'buyer12@ukomp.test', '$2y$12$7Tn5lT222.UKxIcz/zlzROFjoU5bgFrT1w.VRz5gh3/N8KVKhB8XW', 'buyer', 'Buyer 12', NULL, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	('019dc277-7239-73f0-8235-f1ca32fae41c', 'qg3DenKlgHMJkV4OzJvzjZegkfJ3', 'akbarfahlevy39@gmail.com', NULL, 'buyer', 'Mochammad Rachman Akbar Fahlevy', 'https://lh3.googleusercontent.com/a/ACg8ocLwIcW9YpN2423z6G110ndWsC_stH2vC89ST117X1UV-w1l_Q=s96-c', 1, '2026-04-24 19:28:21', '2026-04-24 19:28:21'),
	('019dc2e0-8948-7058-9f63-74def2d71bd8', 'Q51x14TWY0Tzw8TX23ONfz95Kw43', 'jhs@gmail.com', '$2y$12$sxHKxTk9OqpjxIH2ABFtfufaJduayI/1CGmr5lQ6Wfck179ifq6jW', 'buyer', 'hgk', NULL, 0, '2026-04-24 21:23:08', '2026-04-24 21:23:08');

-- Dumping structure for table kishamarket.user_roles
DROP TABLE IF EXISTS `user_roles`;
CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_roles_user_id_role_id_unique` (`user_id`,`role_id`),
  KEY `user_roles_role_id_foreign` (`role_id`),
  CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.user_roles: ~0 rows (approximately)
INSERT IGNORE INTO `user_roles` (`id`, `user_id`, `role_id`, `created_at`, `updated_at`) VALUES
	(1, '019dc277-7239-73f0-8235-f1ca32fae41c', 1, '2026-04-24 19:28:21', '2026-04-24 19:28:21'),
	(2, '019dc2e0-8948-7058-9f63-74def2d71bd8', 1, '2026-04-24 21:23:08', '2026-04-24 21:23:08');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
