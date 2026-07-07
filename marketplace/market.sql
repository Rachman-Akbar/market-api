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
  `user_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_buyer_address` (`user_id`,`label`,`full_address`(255)),
  UNIQUE KEY `unique_seller_address` (`store_id`,`label`,`full_address`(255)),
  KEY `addresses_user_id_index` (`user_id`),
  KEY `addresses_store_id_index` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.addresses: ~3 rows (approximately)
INSERT IGNORE INTO `addresses` (`id`, `user_id`, `store_id`, `label`, `recipient_name`, `phone_number`, `full_address`, `city`, `postal_code`, `notes`, `latitude`, `longitude`, `is_primary`, `created_at`, `updated_at`) VALUES
	(14, '32394b22-956f-4161-a45c-da7ded058428', NULL, 'Rumah Utama', 'John Doe', '081234567890', 'Jl. Merdeka No. 123, RT 01/RW 02, Kecamatan Kebayoran Baru', 'Jakarta Selatan', '12110', NULL, -6.22972800, 106.80572100, 0, '2026-07-02 20:38:14', '2026-07-02 23:16:52'),
	(15, NULL, '35', 'Rumah Utama', 'John Doe', '081234567890', 'Jl. Merdeka No. 123, RT 01/RW 02, Kecamatan Kebayoran Baru', 'Jakarta Selatan', '12110', NULL, -6.22972800, 106.80572100, 1, '2026-07-02 20:39:27', '2026-07-02 20:39:27'),
	(17, '32394b22-956f-4161-a45c-da7ded058428', NULL, 'Rumah Utama', 'John Doe', '081234567890', 'Mahakam Ulu, East Kalimantan, Kalimantan, 75767, Indonesia', 'East Kalimantan', '75767', NULL, 0.67564352, 114.61486816, 1, '2026-07-02 23:16:52', '2026-07-02 23:16:52');

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

-- Dumping data for table kishamarket.banners: ~2 rows (approximately)
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

-- Dumping data for table kishamarket.cache: ~3 rows (approximately)
INSERT IGNORE INTO `cache` (`key`, `value`, `expiration`) VALUES
	('marketapi-cache-catalog_group_3_categories', 'O:29:"Illuminate\\Support\\Collection":2:{s:8:"\0*\0items";a:3:{i:0;O:53:"App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category":14:{s:57:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0id";i:69;s:69:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0catalogGroupId";i:3;s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0parentId";N;s:59:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0name";s:4:"ensk";s:59:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0slug";s:4:"ensk";s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0fullSlug";s:4:"ensk";s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0imageUrl";N;s:62:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0iconUrl";N;s:60:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0level";i:1;s:64:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0sortOrder";i:1;s:68:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0productsCount";i:0;s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0isActive";b:1;s:70:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0isVisibleInMenu";b:1;s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0children";a:0:{}}i:1;O:53:"App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category":14:{s:57:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0id";i:72;s:69:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0catalogGroupId";i:3;s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0parentId";N;s:59:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0name";s:4:"ensk";s:59:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0slug";s:9:"enaHZahsk";s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0fullSlug";s:9:"enaHZahsk";s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0imageUrl";s:75:"https://play.google.com/store/apps/details?id=co.uk.imbranding.imakeprofile";s:62:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0iconUrl";s:75:"https://play.google.com/store/apps/details?id=co.uk.imbranding.imakeprofile";s:60:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0level";i:1;s:64:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0sortOrder";i:1;s:68:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0productsCount";i:0;s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0isActive";b:1;s:70:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0isVisibleInMenu";b:1;s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0children";a:0:{}}i:2;O:53:"App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category":14:{s:57:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0id";i:73;s:69:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0catalogGroupId";i:3;s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0parentId";N;s:59:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0name";s:4:"ensk";s:59:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0slug";s:23:"enaHZsxabshasbhasbhahsk";s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0fullSlug";s:23:"enaHZsxabshasbhasbhahsk";s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0imageUrl";s:75:"https://play.google.com/store/apps/details?id=co.uk.imbranding.imakeprofile";s:62:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0iconUrl";s:75:"https://play.google.com/store/apps/details?id=co.uk.imbranding.imakeprofile";s:60:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0level";i:1;s:64:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0sortOrder";i:1;s:68:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0productsCount";i:0;s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0isActive";b:1;s:70:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0isVisibleInMenu";b:1;s:63:"\0App\\Domains\\Catalog\\Category\\Domain\\Entities\\Category\0children";a:0:{}}}s:28:"\0*\0escapeWhenCastingToString";b:0;}', 1783399589),
	('marketapi-cache-catalog_group_8_categories', 'O:39:"Illuminate\\Database\\Eloquent\\Collection":2:{s:8:"\0*\0items";a:0:{}s:28:"\0*\0escapeWhenCastingToString";b:0;}', 1783399590),
	('marketapi-cache-catalog_groups_active_v5', 'a:2:{i:0;a:5:{s:2:"id";i:3;s:4:"name";s:13:"Gadget & Elek";s:4:"slug";s:25:"gadget-elektronik-updated";s:9:"is_active";b:1;s:10:"categories";a:3:{i:0;a:14:{s:2:"id";i:69;s:16:"catalog_group_id";i:3;s:9:"parent_id";N;s:4:"name";s:4:"ensk";s:4:"slug";s:4:"ensk";s:9:"full_slug";s:4:"ensk";s:9:"image_url";N;s:8:"icon_url";N;s:5:"level";i:1;s:10:"sort_order";i:1;s:14:"products_count";i:0;s:9:"is_active";b:1;s:18:"is_visible_in_menu";b:1;s:8:"children";a:0:{}}i:1;a:14:{s:2:"id";i:72;s:16:"catalog_group_id";i:3;s:9:"parent_id";N;s:4:"name";s:4:"ensk";s:4:"slug";s:9:"enaHZahsk";s:9:"full_slug";s:9:"enaHZahsk";s:9:"image_url";s:75:"https://play.google.com/store/apps/details?id=co.uk.imbranding.imakeprofile";s:8:"icon_url";s:75:"https://play.google.com/store/apps/details?id=co.uk.imbranding.imakeprofile";s:5:"level";i:1;s:10:"sort_order";i:1;s:14:"products_count";i:0;s:9:"is_active";b:1;s:18:"is_visible_in_menu";b:1;s:8:"children";a:0:{}}i:2;a:14:{s:2:"id";i:73;s:16:"catalog_group_id";i:3;s:9:"parent_id";N;s:4:"name";s:4:"ensk";s:4:"slug";s:23:"enaHZsxabshasbhasbhahsk";s:9:"full_slug";s:23:"enaHZsxabshasbhasbhahsk";s:9:"image_url";s:75:"https://play.google.com/store/apps/details?id=co.uk.imbranding.imakeprofile";s:8:"icon_url";s:75:"https://play.google.com/store/apps/details?id=co.uk.imbranding.imakeprofile";s:5:"level";i:1;s:10:"sort_order";i:1;s:14:"products_count";i:0;s:9:"is_active";b:1;s:18:"is_visible_in_menu";b:1;s:8:"children";a:0:{}}}}i:1;a:5:{s:2:"id";i:8;s:4:"name";s:10:"hjkjhkjh &";s:4:"slug";s:5:"hjhjk";s:9:"is_active";b:1;s:10:"categories";a:0:{}}}', 1783399099);

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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.cart_items: ~2 rows (approximately)
INSERT IGNORE INTO `cart_items` (`id`, `cart_id`, `product_variant_id`, `quantity`, `created_at`, `updated_at`) VALUES
	(15, 2, 106, 3, '2026-06-28 19:11:04', '2026-06-28 19:11:04'),
	(26, 3, 12, 2, '2026-07-01 01:20:55', '2026-07-01 01:20:55');

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
	(3, 'Gadget & Elek', 'gadget-elektronik-updated', 1, '2026-06-05 07:25:43', '2026-06-05 07:31:51'),
	(8, 'hjkjhkjh &', 'hjhjk', 1, '2026-06-15 23:00:24', '2026-06-15 23:00:24');

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
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.categories: ~3 rows (approximately)
INSERT IGNORE INTO `categories` (`id`, `catalog_group_id`, `parent_id`, `level`, `sort_order`, `is_active`, `is_visible_in_menu`, `name`, `slug`, `full_slug`, `image_url`, `icon_url`, `created_at`, `updated_at`) VALUES
	(69, 3, NULL, 1, 1, 1, 1, 'ensk', 'ensk', 'ensk', NULL, NULL, '2026-06-15 23:44:39', '2026-06-16 00:26:41'),
	(72, 3, NULL, 1, 1, 1, 1, 'ensk', 'enaHZahsk', 'enaHZahsk', 'https://play.google.com/store/apps/details?id=co.uk.imbranding.imakeprofile', 'https://play.google.com/store/apps/details?id=co.uk.imbranding.imakeprofile', '2026-06-24 19:33:42', '2026-06-24 19:33:42'),
	(73, 3, NULL, 1, 1, 1, 1, 'ensk', 'enaHZsxabshasbhasbhahsk', 'enaHZsxabshasbhasbhahsk', 'https://play.google.com/store/apps/details?id=co.uk.imbranding.imakeprofile', 'https://play.google.com/store/apps/details?id=co.uk.imbranding.imakeprofile', '2026-06-29 01:41:48', '2026-06-29 01:41:48');

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
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `voucher_id` bigint unsigned DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','processing','shipped','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `shipping_address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_user_id_index` (`user_id`),
  KEY `orders_voucher_id_foreign` (`voucher_id`),
  CONSTRAINT `orders_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.orders: ~1 rows (approximately)
INSERT IGNORE INTO `orders` (`id`, `order_number`, `user_id`, `voucher_id`, `total_amount`, `discount_amount`, `status`, `shipping_address`, `created_at`, `updated_at`) VALUES
	(20, 'ORD-20260701-9D5FC664', '32394b22-956f-4161-a45c-da7ded058428', 1, 300000.00, 15990.00, 'pending', 'Jl. Raya Boulevard No. 45, Cluster Lavender', '2026-07-01 01:24:14', '2026-07-01 01:24:14');

-- Dumping structure for table kishamarket.order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `store_id` bigint unsigned NOT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_store_id_index` (`store_id`),
  KEY `order_items_product_id_index` (`product_id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.order_items: ~1 rows (approximately)
INSERT IGNORE INTO `order_items` (`id`, `order_id`, `product_id`, `store_id`, `product_name`, `sku`, `price`, `quantity`, `created_at`, `updated_at`) VALUES
	(23, 20, 11, 20, 'Produk Varian ID 11', 'SKU-VAR-11', 100000.00, 3, '2026-07-01 01:24:14', '2026-07-01 01:24:14');

-- Dumping structure for table kishamarket.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.password_reset_tokens: ~0 rows (approximately)

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
) ENGINE=InnoDB AUTO_INCREMENT=172 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
	(162, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'iPhone 15 Pro', 'dd558e77efc682f943c3502313981bcdc80e3ddfbe665aeeb2573eadb507a8b4', '["access-api","active-role:buyer"]', '2026-07-01 01:24:14', NULL, '2026-06-30 21:05:56', '2026-07-01 01:24:14'),
	(164, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'Laptop-Asus', '59cf3717b4e47384488ceebbf4247857b2e7517c23a957ad169575d76c4afddf', '["access-api","active-role:seller"]', '2026-07-02 20:39:38', NULL, '2026-07-02 20:38:57', '2026-07-02 20:39:38'),
	(165, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'iPhone 15 Pro', '63ebfb37b3c4964b4325abb1e7e163ecc76b02dd9351a09c719f1be5e8aea06c', '["access-api","active-role:buyer"]', '2026-07-02 23:16:52', NULL, '2026-07-02 23:15:56', '2026-07-02 23:16:52'),
	(168, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'Laptop-Asus', 'f0fdec1280a1445f9a48ecf1c16d0df61b3c4cf17600935f14a157f6ebac5566', '["access-api","active-role:admin"]', '2026-07-03 02:26:21', NULL, '2026-07-03 02:25:50', '2026-07-03 02:26:21'),
	(171, 'App\\Domains\\Identity\\Domain\\Entities\\User', '32394b22-956f-4161-a45c-da7ded058428', 'Laptop-Asus', 'a3cb38c7a22e3f5d1326bd4d6ea07c2ea162d0922d9303cd0ed3a44de64ea6cf', '["access-api","active-role:seller"]', '2026-07-03 02:31:06', NULL, '2026-07-03 02:30:50', '2026-07-03 02:31:06');

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

-- Dumping data for table kishamarket.products: ~6 rows (approximately)
INSERT IGNORE INTO `products` (`id`, `store_id`, `primary_category_id`, `name`, `slug`, `description`, `brand`, `thumbnail`, `status`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 35, 69, 'Kemeja Flanel dwcjdw', 'kemeja-flanel-premiujjjm-kjaxkjakjunisex', 'Kemeja flanel berkualitas tinggi dengan bahan katun 100% yang nyaman digunakan sehari-hari.', 'OxCloth', NULL, 'published', 1, '2026-06-29 06:03:40', '2026-06-29 06:10:25'),
	(3, 35, 69, 'Kemeja Flanel Premiumssjjs', 'kemeja-flanel-premium-kjaxkjakjunisex', 'Kemeja flanel berkualitas tinggi dengan bahan katun 100% yang nyaman digunakan sehari-hari.', 'OxCloth', NULL, 'published', 1, '2026-06-29 06:08:18', '2026-06-29 06:08:18'),
	(5, 35, 69, 'Kemeja Flanel dwcjxb zdw', 'kemeja-flanel-premiuxxxjjjm-kjaxkjakjunisex', 'Kemeja flanel berkualitas tinggi dengan bahan katun 100% yang nyaman digunakan sehari-hari.', 'OxCloth', NULL, 'published', 1, '2026-06-29 06:22:53', '2026-06-29 06:22:53'),
	(6, 35, 69, 'Kemeja Flanel dwcsqkjswkjjsjdw', 'kemeja-flanel-premdkjjbkiujjjm-kjaxkjakjunisex', 'Kemejshshka flanel berkualitas tinggi dengan bahan katun 100% yang nyaman digunakan sehari-hari.', 'OxCloth', NULL, 'published', 1, '2026-06-29 06:24:24', '2026-06-29 06:24:48'),
	(8, 35, 69, 'Kemeja Flanel dwcsqkjswkjjsbjkjkjdw', 'kemeja-flanel-dwcsqkjswkjjsbjkjkjdw', 'Kemejshshka flanel berkualitas tinggi dengan bahan katun 100% yang nyaman digunakan sehari-hari.', 'OxCloth', NULL, 'published', 1, '2026-06-29 06:25:34', '2026-06-29 06:25:34'),
	(9, 35, 69, 'Sepatu Running Aero', 'sepatu-running-aero', NULL, 'AeroStride', NULL, 'draft', 1, '2026-06-29 06:27:01', '2026-06-29 06:27:01');

-- Dumping structure for table kishamarket.product_attributes
CREATE TABLE IF NOT EXISTS `product_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'select',
  PRIMARY KEY (`id`),
  UNIQUE KEY `attributes_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.product_attributes: ~3 rows (approximately)
INSERT IGNORE INTO `product_attributes` (`id`, `name`, `slug`, `type`) VALUES
	(1, 'Warna', 'warna', 'select'),
	(3, 'Wadhbrna', 'wadhbrna', 'select'),
	(4, 'Kemeja Flanel Premium - Ukuran XL', 'kemeja-flanel-premium-ukuran-xl', 'select');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.product_attribute_values: ~0 rows (approximately)

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

-- Dumping data for table kishamarket.product_categories: ~6 rows (approximately)
INSERT IGNORE INTO `product_categories` (`product_id`, `category_id`, `is_primary`) VALUES
	(1, 69, 1),
	(3, 69, 1),
	(5, 69, 1),
	(6, 69, 1),
	(8, 69, 1),
	(9, 69, 1);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.product_images: ~0 rows (approximately)

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

-- Dumping data for table kishamarket.product_variants: ~12 rows (approximately)
INSERT IGNORE INTO `product_variants` (`id`, `product_id`, `store_id`, `sku`, `name`, `price`, `stock`, `is_default`, `created_at`, `updated_at`) VALUES
	(1, 1, 35, 'KEMEJA-FLANEL-PREMIUM-OXCLOTH-CAT69-260629-0001', 'Kemeja Flanel Premium', 0.00, 0, 1, '2026-06-29 06:03:40', '2026-06-29 06:03:40'),
	(2, 3, 35, 'KEMEJA-FLANEL-PREMIUMSSJJS-OXCLOTH-CAT69-260629-0001', 'Kemeja Flanel Premiumssjjs', 0.00, 0, 1, '2026-06-29 06:08:18', '2026-06-29 06:08:18'),
	(3, 5, 35, 'KEMEJA-FLANEL-DWCJXB-ZDW-OXCLOTH-CAT69-260629-0001', 'Kemeja Flanel dwcjxb zdw', 0.00, 0, 1, '2026-06-29 06:22:53', '2026-06-29 06:22:53'),
	(4, 6, 35, 'KEMEJA-FLANEL-DWCSQKJSWKJJSJDW-OXCLOTH-C-260629-0001', 'Kemeja Flanel dwcsqkjswkjjsjdw', 700000.00, 90, 1, '2026-06-29 06:24:24', '2026-06-29 06:24:24'),
	(5, 8, 35, 'KEMEJA-FLANEL-DWCSQKJSWKJJSBJKJKJDW-OXCL-260629-0001', 'Kemeja Flanel dwcsqkjswkjjsbjkjkjdw', 700000.00, 90, 1, '2026-06-29 06:25:34', '2026-06-29 06:25:34'),
	(6, 9, 35, 'AERO-RUN-RED-41', 'Sepatu Running Aero - Merah - Ukuran 41', 450000.00, 25, 1, '2026-06-29 06:27:01', '2026-06-29 06:27:01'),
	(7, 9, 35, 'AERO-RUN-RED-42', 'Sepatu Running Aero - Merah - Ukuran 42', 450000.00, 15, 0, '2026-06-29 06:27:01', '2026-06-29 06:27:01'),
	(8, 9, 35, 'AERO-RUN-BLK-41', 'Sepatu Running Aero - Hitam - Ukuran 41', 475000.00, 10, 0, '2026-06-29 06:27:01', '2026-06-29 06:27:01'),
	(9, 1, 35, 'FLNL-PRM-XL', 'Kemeja Flanel Premium - Ukuran XL', 195000.00, 20, 0, '2026-06-29 06:34:04', '2026-06-29 06:34:04'),
	(10, 1, 35, 'KEMEJA-FLANEL-DWCJDW-XL-MERAH-260629-0001', 'Kemeja Flanel dwcjdw - XL - Merah', 195000.00, 20, 0, '2026-06-29 06:51:48', '2026-06-29 06:51:48'),
	(11, 1, 35, 'KEMEJA-FLANEL-DWCJDW-XLL-MERAH-260629-0001', 'Kemeja Flanel dwcjdw - XLL - Merah', 195000.00, 290, 0, '2026-06-29 06:52:13', '2026-06-29 06:52:13'),
	(12, 1, 35, 'KEMEJA-FLANEL-DWCJDW-XLL-MERHHHAH-260629-0001', 'Kemeja Flanel dwcjdw - XLL - Merhhhah', 195000.00, 290, 0, '2026-06-29 06:52:39', '2026-06-29 06:52:39');

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

-- Dumping data for table kishamarket.product_variant_values: ~7 rows (approximately)
INSERT IGNORE INTO `product_variant_values` (`id`, `variant_id`, `attribute_id`, `value`) VALUES
	(1, 9, 3, 'XL'),
	(2, 10, 3, 'XL'),
	(3, 10, 4, 'Merah'),
	(4, 11, 3, 'XLL'),
	(5, 11, 4, 'Merah'),
	(8, 12, 3, 'XLL'),
	(9, 12, 4, 'hitam');

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

-- Dumping data for table kishamarket.roles: ~3 rows (approximately)
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

-- Dumping data for table kishamarket.sessions: ~24 rows (approximately)
INSERT IGNORE INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('1f6tTR9OuTMcQZv76vr2JU1ocZrxf4JFOIp8Wodg', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJoY0hMR2ExT3ZzT0hGYlFUOGRjMW5CQTc1aHFQSzNWc2FNTlh2UEkwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776670743),
	('1jR37R7PdJSvCsdSpDd2I8YFbCfKFnPqLbTWbBtf', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJPaXROVlU4cWU4T0NRdDJtUFhIcVJ5YzVCVGZjNFdhUFhCamxDeGxBIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC90ZXN0LW1hcCIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1783043049),
	('26XeoyiM8jkSAWcKI7aZWbsqcaIkSg9WRtaMT3Mz', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiIwVGdpdm1NZjdOeU1lc3dWT3g4QjlQUzlaY1J1RFk0QzQ3NnNrdDRIIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC90ZXN0LWZpcmViYXNlLWxvZ2luIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1782354896),
	('2keNjE4FWnRDyKxWV5Nhw0qgZVHD9WvSbSnBgN2T', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJEQnlSengwa3o2TzF4cjc1V1ZHOEJlM2ZBR0JIVWhGdzQ0bnFNRHB2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL21hcmtldC1hcGkudGVzdCIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779616974),
	('3KggZokKfEESIs0oRvRGJx5YvsreeM67aCoPQBQ9', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'eyJfdG9rZW4iOiJlcGx5eDN2ZTRRU2t3RzF3WGNOS0I3aVpab2M0MWNTV25kYnlLQVRpIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC90ZXN0LWZpcmViYXNlLWxvZ2luIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1782351234),
	('4730JiHxljFc7uNiZ5zkXgqSdtAfpXRyBm1KmNto', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJpWnU2Y3ZPeDB6aTYxcllMWmZXTk5hemFjUTVrajcyS2lVd1F5ejAzIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC90ZXN0LWZpcmViYXNlLWxvZ2luIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1782355012),
	('7P5kKjWQ9eCWguVCAtYzmDzC5hKSMTdbqG2Gv10z', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'eyJfdG9rZW4iOiJybWtmdUpaMHFiQnhKZ21LUmdnY2JJNU5OaTJNcnljSGVHYUVKV1lEIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1782351229),
	('7ph2vpardSC9DDHmAbFy3HRdJApcxzg59oQBADJg', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'eyJfdG9rZW4iOiJtSmRTeVdOUVpaOGNuNXk2d3IzWHg1MXYyd3hKM0pGclZsOGExNnVwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1783043019),
	('8TMJ3oJz9hof632empJFEOgKaPJlZIlwf0iqVUuE', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI4Zmk0TmV5TkkwU0NEMDhzZE1QVUtEbXhZQUJ3YWUzTVNhVHFsenQyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC90ZXN0LW1hcCIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1783044420),
	('Bbb8sn4ccPNnF0UpSjnDHi835aBV5OGtC5JDbqZe', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJKSDVpOERSV3VCdDJuSUVDaG9Ec1VzTGM4V05EbHY1ckd5MTlvSG9pIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL21hcmtldC1hcGkudGVzdCIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779542384),
	('ev7s5JCNVWA5f8AxwpPF5ZXobCByfzw6e9EujaV1', NULL, '127.0.0.1', 'PostmanRuntime/7.37.3', 'eyJfdG9rZW4iOiJhNnlVTWxUUGl1TDdyMTlzYTFCcVNVZnBVQ2JqRGEycW5qUllXeWNvIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776664754),
	('HeAnjVmCnXKXlA0LbCaJhvCZl9pJ1S5IJyWdpEgU', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ4TURydk90T282UGJCWlNBTTR3d0JjeDBsWENadm5Ia291YUlZUWlKIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC90ZXN0LWZpcmViYXNlLWxvZ2luIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1782351250),
	('HP33qdWDpAtr2cqwRW6ItY5kIAN4hMcFD7Kn5IM3', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJmaEkya3dtWlltS2lYeVlyd0JBZFp3YWY3Z1B0TXV5UlNMTEZGcXhnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1777269692),
	('HXMVHcjZGU2EM47dYUiwMmHWZ7prmW1CWhVydwdL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI3OHk4QkJFSmNqTEFjeG5XdTM5bldENHdUdHVEYW9zaHVBSEl2YnVxIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC90ZXN0LW1hcCIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1783059380),
	('KTvSNNdtAJc8r9gJb2HRCVeeo7Nz84jJsuDFcPMx', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI3ekUwQUpvTFlXQ094R1BxcEwybVBKTEVXRHFPZGlTSG9sUHZkWU5PIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9zYW5jdHVtXC9jc3JmLWNvb2tpZSIsInJvdXRlIjoic2FuY3R1bS5jc3JmLWNvb2tpZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1779631378),
	('Mq9X09SFRjZ8XOwJsI9cJnfKhLPbeLWYK09N9XD1', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ3YU5IVHBCblpNM1l0ckJiZER4VmJlTHhRdW1DMkFGeHRBNDJUMVBEIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC90ZXN0LW1hcCIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1783043669),
	('MzWUUhb023v7FhmvXzq0FBPwLwtq94QtLJcy4MYm', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJLTE81ZWpyOEJYVHlSR1lpeDJ5YnpzMkpqN1RXTTNKdzVENURXNHEyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC90ZXN0LW1hcCIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1783044296),
	('OOeClB6Boiq4wHlSWaWi0l0ZvrwJVz26MIG0Oca7', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJyQzZ5R2oySkRFQ25Sd0hzMWlvd043WTFmVTdGR2VZaHpYSkhVaEk1IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC90ZXN0LW1hcCIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1783049828),
	('R9gPnyuxuzuQOXy9OWlSxKdiv8TgWjbKkRcchcDW', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJHbTJSV2FNMnNCWDhkaXU4N1dvOGNaUlp3WGFvQlI4V0VPTTlmSFl6IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL21hcmtldC1hcGkudGVzdCIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779510889),
	('roV0NSRVI84goonXBmbWjScGWZtgCHolVHN1m7jr', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJPTFIwUldaVWNOa3FDWHB6VnR3OHowQjhBdURMUjY2Mm52SzNIa0U1IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC90ZXN0LWZpcmViYXNlLWxvZ2luIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1782351667),
	('Scl8HU7PJqj8HlaJBQWAgNksUxuy9O0MyJrVaynN', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI2cFhLVFFtQ2wzOUVQbFdrZkFoaUptd2JtdXN5R0dOT0pwUmt1OXRSIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776670741),
	('TGFQSfz9zNriU4KhYcfKiscu176v6XoZMsZ9rOGX', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'eyJfdG9rZW4iOiJxeFFXOTU2OWJldmFYdldUUXFGQWtHUndSWWs4S3N4SEJucDlkYWYyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1779257821),
	('WjLyNWOF1Grkl5A8OnFex9fIE5HfQFHFvEpQb4YA', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ1cU1rQkxSUExoWGRlUk05YkJIR1NIbllHMWVMbTRoblFFa0pyNXBZIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC90ZXN0LWZpcmViYXNlLWxvZ2luIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1782351725),
	('XutD4k1ElmM6xit3Z5C2atiINiJQTncbo5WLd6VG', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'eyJfdG9rZW4iOiI4eFVrRm9nVFJEZ21QMGQxbmhnRzJDT2UwN0l4Wlg0TjVWVmtpU25aIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1782351232);

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

-- Dumping data for table kishamarket.stores: ~1 rows (approximately)
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

-- Dumping data for table kishamarket.store_details: ~1 rows (approximately)
INSERT IGNORE INTO `store_details` (`id`, `store_id`, `owner_name`, `owner_phone`, `description`, `shipping_policy`, `return_policy`, `open_days`, `open_time`, `close_time`, `whatsapp_url`, `instagram_url`, `tiktok_url`, `website_url`, `created_at`, `updated_at`) VALUES
	(6, 35, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-28 20:16:29', '2026-06-28 20:16:29');

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

-- Dumping data for table kishamarket.wishlists: ~1 rows (approximately)
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

-- Dumping data for table kishamarket.wishlist_items: ~1 rows (approximately)
INSERT IGNORE INTO `wishlist_items` (`id`, `wishlist_id`, `product_id`, `added_at`) VALUES
	(9, '99f1ba3d-5472-4338-87c7-cb36455b99b8', 10, '2026-06-25 01:26:01');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
