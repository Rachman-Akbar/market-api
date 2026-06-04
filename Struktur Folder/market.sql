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
CREATE TABLE IF NOT EXISTS `banners` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_type` enum('product','category','store','catalog_group','custom') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'custom',
  `link_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.cache: ~7 rows (approximately)
INSERT IGNORE INTO `cache` (`key`, `value`, `expiration`) VALUES
	('laravel-cache-catalog_groups_active_v5', 'a:10:{i:0;a:7:{s:2:"id";i:6;s:4:"name";s:7:"Belanja";s:4:"slug";s:7:"belanja";s:11:"description";s:40:"Kategori utama untuk produk marketplace.";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:39:{i:0;a:7:{s:2:"id";i:70;s:9:"parent_id";i:60;s:4:"name";s:19:"aksesoris handphone";s:4:"slug";s:19:"aksesoris-handphone";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:1;a:7:{s:2:"id";i:66;s:9:"parent_id";i:59;s:4:"name";s:16:"aksesoris kamera";s:4:"slug";s:16:"aksesoris-kamera";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:2;a:7:{s:2:"id";i:59;s:9:"parent_id";N;s:4:"name";s:31:"audio kamera elektronik lainnya";s:4:"slug";s:31:"audio-kamera-elektronik-lainnya";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:3;a:7:{s:2:"id";i:74;s:9:"parent_id";i:62;s:4:"name";s:5:"dapur";s:4:"slug";s:5:"dapur";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:4;a:7:{s:2:"id";i:75;s:9:"parent_id";i:62;s:4:"name";s:14:"dekorasi rumah";s:4:"slug";s:14:"dekorasi-rumah";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:5;a:7:{s:2:"id";i:60;s:9:"parent_id";N;s:4:"name";s:16:"handphone tablet";s:4:"slug";s:16:"handphone-tablet";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:6;a:7:{s:2:"id";i:67;s:9:"parent_id";i:59;s:4:"name";s:14:"kamera digital";s:4:"slug";s:14:"kamera-digital";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:7;a:7:{s:2:"id";i:72;s:9:"parent_id";i:61;s:4:"name";s:17:"komponen komputer";s:4:"slug";s:17:"komponen-komputer";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:8;a:7:{s:2:"id";i:61;s:9:"parent_id";N;s:4:"name";s:15:"komputer laptop";s:4:"slug";s:15:"komputer-laptop";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:9;a:7:{s:2:"id";i:73;s:9:"parent_id";i:61;s:4:"name";s:6:"laptop";s:4:"slug";s:6:"laptop";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:10;a:7:{s:2:"id";i:68;s:9:"parent_id";i:59;s:4:"name";s:15:"lighting studio";s:4:"slug";s:15:"lighting-studio";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:11;a:7:{s:2:"id";i:69;s:9:"parent_id";i:59;s:4:"name";s:12:"media player";s:4:"slug";s:12:"media-player";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:12;a:7:{s:2:"id";i:62;s:9:"parent_id";N;s:4:"name";s:12:"rumah tangga";s:4:"slug";s:12:"rumah-tangga";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:13;a:7:{s:2:"id";i:71;s:9:"parent_id";i:60;s:4:"name";s:10:"smartphone";s:4:"slug";s:10:"smartphone";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:14;a:7:{s:2:"id";i:13;s:9:"parent_id";i:67;s:4:"name";s:13:"Action Camera";s:4:"slug";s:13:"action-camera";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}i:15;a:7:{s:2:"id";i:30;s:9:"parent_id";i:74;s:4:"name";s:10:"Alat Masak";s:4:"slug";s:10:"alat-masak";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}i:16;a:7:{s:2:"id";i:24;s:9:"parent_id";i:71;s:4:"name";s:13:"Android Phone";s:4:"slug";s:13:"android-phone";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}i:17;a:7:{s:2:"id";i:9;s:9:"parent_id";i:66;s:4:"name";s:24:"Baterai & Charger Kamera";s:4:"slug";s:22:"baterai-charger-kamera";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}i:18;a:7:{s:2:"id";i:17;s:9:"parent_id";i:69;s:4:"name";s:14:"Blu Ray Player";s:4:"slug";s:14:"blu-ray-player";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}i:19;a:7:{s:2:"id";i:22;s:9:"parent_id";i:70;s:4:"name";s:14:"Case Handphone";s:4:"slug";s:14:"case-handphone";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}i:20;a:7:{s:2:"id";i:32;s:9:"parent_id";i:75;s:4:"name";s:14:"Lampu Dekorasi";s:4:"slug";s:14:"lampu-dekorasi";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}i:21;a:7:{s:2:"id";i:26;s:9:"parent_id";i:73;s:4:"name";s:13:"Laptop Gaming";s:4:"slug";s:13:"laptop-gaming";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}i:22;a:7:{s:2:"id";i:28;s:9:"parent_id";i:72;s:4:"name";s:3:"RAM";s:4:"slug";s:3:"ram";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}i:23;a:7:{s:2:"id";i:20;s:9:"parent_id";i:68;s:4:"name";s:10:"Ring Light";s:4:"slug";s:10:"ring-light";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}i:24;a:7:{s:2:"id";i:10;s:9:"parent_id";i:66;s:4:"name";s:11:"Case Kamera";s:4:"slug";s:11:"case-kamera";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}i:25;a:7:{s:2:"id";i:23;s:9:"parent_id";i:70;s:4:"name";s:17:"Charger Handphone";s:4:"slug";s:17:"charger-handphone";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}i:26;a:7:{s:2:"id";i:18;s:9:"parent_id";i:69;s:4:"name";s:10:"DVD Player";s:4:"slug";s:10:"dvd-player";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}i:27;a:7:{s:2:"id";i:25;s:9:"parent_id";i:71;s:4:"name";s:6:"iPhone";s:4:"slug";s:6:"iphone";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}i:28;a:7:{s:2:"id";i:14;s:9:"parent_id";i:67;s:4:"name";s:10:"Kamera 360";s:4:"slug";s:10:"kamera-360";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}i:29;a:7:{s:2:"id";i:33;s:9:"parent_id";i:75;s:4:"name";s:6:"Karpet";s:4:"slug";s:6:"karpet";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}i:30;a:7:{s:2:"id";i:27;s:9:"parent_id";i:73;s:4:"name";s:12:"Laptop Kerja";s:4:"slug";s:12:"laptop-kerja";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}i:31;a:7:{s:2:"id";i:31;s:9:"parent_id";i:74;s:4:"name";s:15:"Peralatan Makan";s:4:"slug";s:15:"peralatan-makan";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}i:32;a:7:{s:2:"id";i:29;s:9:"parent_id";i:72;s:4:"name";s:3:"SSD";s:4:"slug";s:3:"ssd";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}i:33;a:7:{s:2:"id";i:21;s:9:"parent_id";i:68;s:4:"name";s:6:"Tripod";s:4:"slug";s:6:"tripod";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}i:34;a:7:{s:2:"id";i:11;s:9:"parent_id";i:66;s:4:"name";s:21:"Cleaning Tools Kamera";s:4:"slug";s:21:"cleaning-tools-kamera";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:3;}i:35;a:7:{s:2:"id";i:15;s:9:"parent_id";i:67;s:4:"name";s:11:"Kamera DSLR";s:4:"slug";s:11:"kamera-dslr";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:3;}i:36;a:7:{s:2:"id";i:19;s:9:"parent_id";i:69;s:4:"name";s:16:"MP3 & MP4 Player";s:4:"slug";s:14:"mp3-mp4-player";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:3;}i:37;a:7:{s:2:"id";i:12;s:9:"parent_id";i:66;s:4:"name";s:18:"Memory Card Kamera";s:4:"slug";s:18:"memory-card-kamera";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:4;}i:38;a:7:{s:2:"id";i:16;s:9:"parent_id";i:67;s:4:"name";s:17:"Mirrorless Camera";s:4:"slug";s:17:"mirrorless-camera";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:4;}}}i:1;a:7:{s:2:"id";i:2;s:4:"name";s:11:"Electronics";s:4:"slug";s:11:"electronics";s:11:"description";s:61:"Gadget, aksesoris, perangkat kerja, dan kebutuhan elektronik.";s:9:"image_url";s:52:"https://picsum.photos/seed/group-electronics/600/600";s:15:"cover_image_url";s:59:"https://picsum.photos/seed/group-electronics-cover/1600/700";s:10:"categories";a:1:{i:0;a:7:{s:2:"id";i:2;s:9:"parent_id";N;s:4:"name";s:11:"Electronics";s:4:"slug";s:11:"electronics";s:9:"image_url";s:55:"https://picsum.photos/seed/category-electronics/600/600";s:8:"icon_url";N;s:10:"sort_order";i:2;}}}i:2;a:7:{s:2:"id";i:3;s:4:"name";s:16:"Fashion & Beauty";s:4:"slug";s:14:"fashion-beauty";s:11:"description";s:50:"Produk fashion, sepatu, style, dan perawatan diri.";s:9:"image_url";s:48:"https://picsum.photos/seed/group-fashion/600/600";s:15:"cover_image_url";s:55:"https://picsum.photos/seed/group-fashion-cover/1600/700";s:10:"categories";a:2:{i:0;a:7:{s:2:"id";i:3;s:9:"parent_id";N;s:4:"name";s:7:"Fashion";s:4:"slug";s:7:"fashion";s:9:"image_url";s:51:"https://picsum.photos/seed/category-fashion/600/600";s:8:"icon_url";N;s:10:"sort_order";i:3;}i:1;a:7:{s:2:"id";i:5;s:9:"parent_id";N;s:4:"name";s:6:"Beauty";s:4:"slug";s:6:"beauty";s:9:"image_url";s:50:"https://picsum.photos/seed/category-beauty/600/600";s:8:"icon_url";N;s:10:"sort_order";i:5;}}}i:3;a:7:{s:2:"id";i:7;s:4:"name";s:8:"Featured";s:4:"slug";s:8:"featured";s:11:"description";s:33:"Kategori pilihan dan rekomendasi.";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:0:{}}i:4;a:7:{s:2:"id";i:1;s:4:"name";s:15:"Food & Beverage";s:4:"slug";s:13:"food-beverage";s:11:"description";s:62:"Produk makanan, snack, minuman, dan kebutuhan konsumsi harian.";s:9:"image_url";s:45:"https://picsum.photos/seed/group-food/600/600";s:15:"cover_image_url";s:52:"https://picsum.photos/seed/group-food-cover/1600/700";s:10:"categories";a:1:{i:0;a:7:{s:2:"id";i:1;s:9:"parent_id";N;s:4:"name";s:4:"Food";s:4:"slug";s:4:"food";s:9:"image_url";s:48:"https://picsum.photos/seed/category-food/600/600";s:8:"icon_url";N;s:10:"sort_order";i:1;}}}i:5;a:7:{s:2:"id";i:4;s:4:"name";s:11:"Home Living";s:4:"slug";s:11:"home-living";s:11:"description";s:51:"Rumah tangga, dekorasi, dan kebutuhan living space.";s:9:"image_url";s:45:"https://picsum.photos/seed/group-home/600/600";s:15:"cover_image_url";s:52:"https://picsum.photos/seed/group-home-cover/1600/700";s:10:"categories";a:2:{i:0;a:7:{s:2:"id";i:4;s:9:"parent_id";N;s:4:"name";s:13:"Home & Living";s:4:"slug";s:11:"home-living";s:9:"image_url";s:48:"https://picsum.photos/seed/category-home/600/600";s:8:"icon_url";N;s:10:"sort_order";i:4;}i:1;a:7:{s:2:"id";i:8;s:9:"parent_id";N;s:4:"name";s:6:"Garden";s:4:"slug";s:6:"garden";s:9:"image_url";s:50:"https://picsum.photos/seed/category-garden/600/600";s:8:"icon_url";N;s:10:"sort_order";i:8;}}}i:6;a:7:{s:2:"id";i:8;s:4:"name";s:16:"Kebutuhan Harian";s:4:"slug";s:16:"kebutuhan-harian";s:11:"description";s:24:"Produk kebutuhan harian.";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:0:{}}i:7;a:7:{s:2:"id";i:5;s:4:"name";s:14:"Sports & Hobby";s:4:"slug";s:12:"sports-hobby";s:11:"description";s:43:"Olahraga, buku, hobi, dan aktivitas santai.";s:9:"image_url";s:46:"https://picsum.photos/seed/group-hobby/600/600";s:15:"cover_image_url";s:53:"https://picsum.photos/seed/group-hobby-cover/1600/700";s:10:"categories";a:2:{i:0;a:7:{s:2:"id";i:6;s:9:"parent_id";N;s:4:"name";s:6:"Sports";s:4:"slug";s:6:"sports";s:9:"image_url";s:50:"https://picsum.photos/seed/category-sports/600/600";s:8:"icon_url";N;s:10:"sort_order";i:6;}i:1;a:7:{s:2:"id";i:7;s:9:"parent_id";N;s:4:"name";s:5:"Books";s:4:"slug";s:5:"books";s:9:"image_url";s:49:"https://picsum.photos/seed/category-books/600/600";s:8:"icon_url";N;s:10:"sort_order";i:7;}}}i:8;a:7:{s:2:"id";i:9;s:4:"name";s:16:"Tagihan & Top Up";s:4:"slug";s:14:"tagihan-top-up";s:11:"description";s:38:"Pembayaran tagihan dan top up digital.";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:0:{}}i:9;a:7:{s:2:"id";i:10;s:4:"name";s:22:"Travel & Entertainment";s:4:"slug";s:20:"travel-entertainment";s:11:"description";s:26:"Produk travel dan hiburan.";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:0:{}}}', 1779505251),
	('laravel-cache-category_menu_tree_all_v1', 'a:12:{i:0;a:9:{s:2:"id";i:59;s:9:"parent_id";N;s:16:"catalog_group_id";i:6;s:4:"name";s:31:"audio kamera elektronik lainnya";s:4:"slug";s:31:"audio-kamera-elektronik-lainnya";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;s:8:"children";a:4:{i:0;a:7:{s:2:"id";i:66;s:9:"parent_id";i:59;s:4:"name";s:16:"aksesoris kamera";s:4:"slug";s:16:"aksesoris-kamera";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:1;a:7:{s:2:"id";i:67;s:9:"parent_id";i:59;s:4:"name";s:14:"kamera digital";s:4:"slug";s:14:"kamera-digital";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:2;a:7:{s:2:"id";i:68;s:9:"parent_id";i:59;s:4:"name";s:15:"lighting studio";s:4:"slug";s:15:"lighting-studio";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:3;a:7:{s:2:"id";i:69;s:9:"parent_id";i:59;s:4:"name";s:12:"media player";s:4:"slug";s:12:"media-player";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}}}i:1;a:9:{s:2:"id";i:60;s:9:"parent_id";N;s:16:"catalog_group_id";i:6;s:4:"name";s:16:"handphone tablet";s:4:"slug";s:16:"handphone-tablet";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;s:8:"children";a:2:{i:0;a:7:{s:2:"id";i:70;s:9:"parent_id";i:60;s:4:"name";s:19:"aksesoris handphone";s:4:"slug";s:19:"aksesoris-handphone";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:1;a:7:{s:2:"id";i:71;s:9:"parent_id";i:60;s:4:"name";s:10:"smartphone";s:4:"slug";s:10:"smartphone";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}}}i:2;a:9:{s:2:"id";i:61;s:9:"parent_id";N;s:16:"catalog_group_id";i:6;s:4:"name";s:15:"komputer laptop";s:4:"slug";s:15:"komputer-laptop";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;s:8:"children";a:2:{i:0;a:7:{s:2:"id";i:72;s:9:"parent_id";i:61;s:4:"name";s:17:"komponen komputer";s:4:"slug";s:17:"komponen-komputer";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:1;a:7:{s:2:"id";i:73;s:9:"parent_id";i:61;s:4:"name";s:6:"laptop";s:4:"slug";s:6:"laptop";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}}}i:3;a:9:{s:2:"id";i:62;s:9:"parent_id";N;s:16:"catalog_group_id";i:6;s:4:"name";s:12:"rumah tangga";s:4:"slug";s:12:"rumah-tangga";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;s:8:"children";a:2:{i:0;a:7:{s:2:"id";i:74;s:9:"parent_id";i:62;s:4:"name";s:5:"dapur";s:4:"slug";s:5:"dapur";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}i:1;a:7:{s:2:"id";i:75;s:9:"parent_id";i:62;s:4:"name";s:14:"dekorasi rumah";s:4:"slug";s:14:"dekorasi-rumah";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:0;}}}i:4;a:9:{s:2:"id";i:1;s:9:"parent_id";N;s:16:"catalog_group_id";i:1;s:4:"name";s:4:"Food";s:4:"slug";s:4:"food";s:9:"image_url";s:48:"https://picsum.photos/seed/category-food/600/600";s:8:"icon_url";N;s:10:"sort_order";i:1;s:8:"children";a:0:{}}i:5;a:9:{s:2:"id";i:2;s:9:"parent_id";N;s:16:"catalog_group_id";i:2;s:4:"name";s:11:"Electronics";s:4:"slug";s:11:"electronics";s:9:"image_url";s:55:"https://picsum.photos/seed/category-electronics/600/600";s:8:"icon_url";N;s:10:"sort_order";i:2;s:8:"children";a:0:{}}i:6;a:9:{s:2:"id";i:3;s:9:"parent_id";N;s:16:"catalog_group_id";i:3;s:4:"name";s:7:"Fashion";s:4:"slug";s:7:"fashion";s:9:"image_url";s:51:"https://picsum.photos/seed/category-fashion/600/600";s:8:"icon_url";N;s:10:"sort_order";i:3;s:8:"children";a:0:{}}i:7;a:9:{s:2:"id";i:4;s:9:"parent_id";N;s:16:"catalog_group_id";i:4;s:4:"name";s:13:"Home & Living";s:4:"slug";s:11:"home-living";s:9:"image_url";s:48:"https://picsum.photos/seed/category-home/600/600";s:8:"icon_url";N;s:10:"sort_order";i:4;s:8:"children";a:0:{}}i:8;a:9:{s:2:"id";i:5;s:9:"parent_id";N;s:16:"catalog_group_id";i:3;s:4:"name";s:6:"Beauty";s:4:"slug";s:6:"beauty";s:9:"image_url";s:50:"https://picsum.photos/seed/category-beauty/600/600";s:8:"icon_url";N;s:10:"sort_order";i:5;s:8:"children";a:0:{}}i:9;a:9:{s:2:"id";i:6;s:9:"parent_id";N;s:16:"catalog_group_id";i:5;s:4:"name";s:6:"Sports";s:4:"slug";s:6:"sports";s:9:"image_url";s:50:"https://picsum.photos/seed/category-sports/600/600";s:8:"icon_url";N;s:10:"sort_order";i:6;s:8:"children";a:0:{}}i:10;a:9:{s:2:"id";i:7;s:9:"parent_id";N;s:16:"catalog_group_id";i:5;s:4:"name";s:5:"Books";s:4:"slug";s:5:"books";s:9:"image_url";s:49:"https://picsum.photos/seed/category-books/600/600";s:8:"icon_url";N;s:10:"sort_order";i:7;s:8:"children";a:0:{}}i:11;a:9:{s:2:"id";i:8;s:9:"parent_id";N;s:16:"catalog_group_id";i:4;s:4:"name";s:6:"Garden";s:4:"slug";s:6:"garden";s:9:"image_url";s:50:"https://picsum.photos/seed/category-garden/600/600";s:8:"icon_url";N;s:10:"sort_order";i:8;s:8:"children";a:0:{}}}', 1779505158),
	('marketapi-cache-auth_payload_019e43cc-03f2-7366-9a07-4b3a5ad07058', 'a:4:{s:4:"user";a:4:{s:2:"id";s:36:"019e43cc-03f2-7366-9a07-4b3a5ad07058";s:4:"name";s:9:"qsgqwgsjh";s:5:"email";s:14:"aki9@gmail.com";s:6:"avatar";N;}s:5:"roles";a:0:{}s:11:"active_role";s:5:"buyer";s:5:"store";N;}', 1779636026),
	('marketapi-cache-auth_payload_019e6ed3-6536-70bd-a1dc-e778032c2fa0', 'a:4:{s:4:"user";a:5:{s:2:"id";s:36:"019e6ed3-6536-70bd-a1dc-e778032c2fa0";s:4:"name";s:17:"Pengepakan Barang";s:5:"email";s:18:"ehdbwjhb@gmail.com";s:6:"avatar";N;s:12:"firebase_uid";N;}s:5:"roles";a:1:{i:0;s:5:"buyer";}s:11:"active_role";s:5:"buyer";s:5:"store";N;}', 1779976108),
	('marketapi-cache-catalog_group_slug_electronics', 'a:8:{s:2:"id";i:2;s:4:"name";s:11:"Electronics";s:4:"slug";s:11:"electronics";s:11:"description";s:61:"Gadget, aksesoris, perangkat kerja, dan kebutuhan elektronik.";s:9:"image_url";s:52:"https://picsum.photos/seed/group-electronics/600/600";s:15:"cover_image_url";s:59:"https://picsum.photos/seed/group-electronics-cover/1600/700";s:9:"is_active";b:1;s:10:"categories";a:1:{i:0;a:16:{s:2:"id";i:2;s:16:"catalog_group_id";i:2;s:9:"parent_id";N;s:4:"name";s:11:"Electronics";s:4:"slug";s:11:"electronics";s:9:"full_slug";s:11:"electronics";s:11:"description";s:61:"Laptop, gadget, dan aksesoris elektronik untuk produktivitas.";s:9:"image_url";s:55:"https://picsum.photos/seed/category-electronics/600/600";s:8:"icon_url";N;s:15:"cover_image_url";s:62:"https://picsum.photos/seed/category-electronics-cover/1200/600";s:5:"level";i:1;s:10:"sort_order";i:2;s:14:"products_count";i:0;s:9:"is_active";b:1;s:18:"is_visible_in_menu";b:1;s:8:"children";a:0:{}}}}', 1779593900),
	('marketapi-cache-catalog_groups_active_v5', 'a:10:{i:0;a:7:{s:2:"id";i:6;s:4:"name";s:7:"Belanja";s:4:"slug";s:7:"belanja";s:11:"description";s:40:"Kategori utama untuk produk marketplace.";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:1:{i:0;a:7:{s:2:"id";i:7;s:9:"parent_id";i:1;s:4:"name";s:17:"Laptop & Komputer";s:4:"slug";s:15:"laptop-komputer";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}}}i:1;a:7:{s:2:"id";i:2;s:4:"name";s:11:"Electronics";s:4:"slug";s:11:"electronics";s:11:"description";s:61:"Gadget, aksesoris, perangkat kerja, dan kebutuhan elektronik.";s:9:"image_url";s:52:"https://picsum.photos/seed/group-electronics/600/600";s:15:"cover_image_url";s:59:"https://picsum.photos/seed/group-electronics-cover/1600/700";s:10:"categories";a:2:{i:0;a:7:{s:2:"id";i:2;s:9:"parent_id";N;s:4:"name";s:7:"Fashion";s:4:"slug";s:7:"fashion";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}i:1;a:7:{s:2:"id";i:3;s:9:"parent_id";N;s:4:"name";s:17:"Makanan & Minuman";s:4:"slug";s:15:"makanan-minuman";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:3;}}}i:2;a:7:{s:2:"id";i:3;s:4:"name";s:16:"Fashion & Beauty";s:4:"slug";s:14:"fashion-beauty";s:11:"description";s:50:"Produk fashion, sepatu, style, dan perawatan diri.";s:9:"image_url";s:48:"https://picsum.photos/seed/group-fashion/600/600";s:15:"cover_image_url";s:55:"https://picsum.photos/seed/group-fashion-cover/1600/700";s:10:"categories";a:2:{i:0;a:7:{s:2:"id";i:15;s:9:"parent_id";i:5;s:4:"name";s:4:"Meja";s:4:"slug";s:4:"meja";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}i:1;a:7:{s:2:"id";i:4;s:9:"parent_id";N;s:4:"name";s:4:"Buku";s:4:"slug";s:4:"buku";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:4;}}}i:3;a:7:{s:2:"id";i:7;s:4:"name";s:8:"Featured";s:4:"slug";s:8:"featured";s:11:"description";s:33:"Kategori pilihan dan rekomendasi.";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:2:{i:0;a:7:{s:2:"id";i:6;s:9:"parent_id";i:1;s:4:"name";s:10:"Smartphone";s:4:"slug";s:10:"smartphone";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}i:1;a:7:{s:2:"id";i:11;s:9:"parent_id";i:3;s:4:"name";s:7:"Minuman";s:4:"slug";s:7:"minuman";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}}}i:4;a:7:{s:2:"id";i:1;s:4:"name";s:15:"Food & Beverage";s:4:"slug";s:13:"food-beverage";s:11:"description";s:62:"Produk makanan, snack, minuman, dan kebutuhan konsumsi harian.";s:9:"image_url";s:45:"https://picsum.photos/seed/group-food/600/600";s:15:"cover_image_url";s:52:"https://picsum.photos/seed/group-food-cover/1600/700";s:10:"categories";a:1:{i:0;a:7:{s:2:"id";i:1;s:9:"parent_id";N;s:4:"name";s:10:"Elektronik";s:4:"slug";s:10:"elektronik";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}}}i:5;a:7:{s:2:"id";i:4;s:4:"name";s:11:"Home Living";s:4:"slug";s:11:"home-living";s:11:"description";s:51:"Rumah tangga, dekorasi, dan kebutuhan living space.";s:9:"image_url";s:45:"https://picsum.photos/seed/group-home/600/600";s:15:"cover_image_url";s:52:"https://picsum.photos/seed/group-home-cover/1600/700";s:10:"categories";a:2:{i:0;a:7:{s:2:"id";i:10;s:9:"parent_id";i:3;s:4:"name";s:15:"Snack & Cemilan";s:4:"slug";s:13:"snack-cemilan";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}i:1;a:7:{s:2:"id";i:5;s:9:"parent_id";N;s:4:"name";s:15:"Perabotan Rumah";s:4:"slug";s:15:"perabotan-rumah";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:5;}}}i:6;a:7:{s:2:"id";i:8;s:4:"name";s:16:"Kebutuhan Harian";s:4:"slug";s:16:"kebutuhan-harian";s:11:"description";s:24:"Produk kebutuhan harian.";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:1:{i:0;a:7:{s:2:"id";i:14;s:9:"parent_id";i:5;s:4:"name";s:5:"Kursi";s:4:"slug";s:5:"kursi";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}}}i:7;a:7:{s:2:"id";i:5;s:4:"name";s:14:"Sports & Hobby";s:4:"slug";s:12:"sports-hobby";s:11:"description";s:43:"Olahraga, buku, hobi, dan aktivitas santai.";s:9:"image_url";s:46:"https://picsum.photos/seed/group-hobby/600/600";s:15:"cover_image_url";s:53:"https://picsum.photos/seed/group-hobby-cover/1600/700";s:10:"categories";a:2:{i:0;a:7:{s:2:"id";i:8;s:9:"parent_id";i:2;s:4:"name";s:12:"Pakaian Pria";s:4:"slug";s:12:"pakaian-pria";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}i:1;a:7:{s:2:"id";i:9;s:9:"parent_id";i:2;s:4:"name";s:14:"Pakaian Wanita";s:4:"slug";s:14:"pakaian-wanita";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;}}}i:8;a:7:{s:2:"id";i:9;s:4:"name";s:16:"Tagihan & Top Up";s:4:"slug";s:14:"tagihan-top-up";s:11:"description";s:38:"Pembayaran tagihan dan top up digital.";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:0:{}}i:9;a:7:{s:2:"id";i:10;s:4:"name";s:22:"Travel & Entertainment";s:4:"slug";s:20:"travel-entertainment";s:11:"description";s:26:"Produk travel dan hiburan.";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:1:{i:0;a:7:{s:2:"id";i:16;s:9:"parent_id";i:6;s:4:"name";s:7:"Android";s:4:"slug";s:7:"android";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;}}}}', 1780541019),
	('marketapi-cache-header_menu_v1', 'a:10:{i:0;a:6:{s:2:"id";i:6;s:4:"name";s:7:"Belanja";s:4:"slug";s:7:"belanja";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:0:{}}i:1;a:6:{s:2:"id";i:2;s:4:"name";s:11:"Electronics";s:4:"slug";s:11:"electronics";s:9:"image_url";s:52:"https://picsum.photos/seed/group-electronics/600/600";s:15:"cover_image_url";s:59:"https://picsum.photos/seed/group-electronics-cover/1600/700";s:10:"categories";a:2:{i:0;a:11:{s:2:"id";i:2;s:16:"catalog_group_id";i:2;s:9:"parent_id";N;s:4:"name";s:7:"Fashion";s:4:"slug";s:7:"fashion";s:9:"full_slug";s:7:"fashion";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;s:5:"level";i:1;s:8:"children";a:2:{i:0;a:11:{s:2:"id";i:8;s:16:"catalog_group_id";i:5;s:9:"parent_id";i:2;s:4:"name";s:12:"Pakaian Pria";s:4:"slug";s:12:"pakaian-pria";s:9:"full_slug";s:20:"fashion/pakaian-pria";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:2;s:8:"children";a:1:{i:0;a:11:{s:2:"id";i:20;s:16:"catalog_group_id";N;s:9:"parent_id";i:8;s:4:"name";s:11:"Kemeja Pria";s:4:"slug";s:11:"kemeja-pria";s:9:"full_slug";s:32:"fashion/pakaian-pria/kemeja-pria";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:3;s:8:"children";a:0:{}}}}i:1;a:11:{s:2:"id";i:9;s:16:"catalog_group_id";i:5;s:9:"parent_id";i:2;s:4:"name";s:14:"Pakaian Wanita";s:4:"slug";s:14:"pakaian-wanita";s:9:"full_slug";s:22:"fashion/pakaian-wanita";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;s:5:"level";i:2;s:8:"children";a:1:{i:0;a:11:{s:2:"id";i:21;s:16:"catalog_group_id";N;s:9:"parent_id";i:9;s:4:"name";s:12:"Dress Wanita";s:4:"slug";s:12:"dress-wanita";s:9:"full_slug";s:35:"fashion/pakaian-wanita/dress-wanita";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:3;s:8:"children";a:0:{}}}}}}i:1;a:11:{s:2:"id";i:3;s:16:"catalog_group_id";i:2;s:9:"parent_id";N;s:4:"name";s:17:"Makanan & Minuman";s:4:"slug";s:15:"makanan-minuman";s:9:"full_slug";s:15:"makanan-minuman";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:3;s:5:"level";i:1;s:8:"children";a:2:{i:0;a:11:{s:2:"id";i:10;s:16:"catalog_group_id";i:4;s:9:"parent_id";i:3;s:4:"name";s:15:"Snack & Cemilan";s:4:"slug";s:13:"snack-cemilan";s:9:"full_slug";s:29:"makanan-minuman/snack-cemilan";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:2;s:8:"children";a:1:{i:0;a:11:{s:2:"id";i:22;s:16:"catalog_group_id";N;s:9:"parent_id";i:10;s:4:"name";s:7:"Keripik";s:4:"slug";s:7:"keripik";s:9:"full_slug";s:37:"makanan-minuman/snack-cemilan/keripik";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:3;s:8:"children";a:0:{}}}}i:1;a:11:{s:2:"id";i:11;s:16:"catalog_group_id";i:7;s:9:"parent_id";i:3;s:4:"name";s:7:"Minuman";s:4:"slug";s:7:"minuman";s:9:"full_slug";s:23:"makanan-minuman/minuman";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;s:5:"level";i:2;s:8:"children";a:1:{i:0;a:11:{s:2:"id";i:23;s:16:"catalog_group_id";N;s:9:"parent_id";i:11;s:4:"name";s:4:"Kopi";s:4:"slug";s:4:"kopi";s:9:"full_slug";s:28:"makanan-minuman/minuman/kopi";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:3;s:8:"children";a:0:{}}}}}}}}i:2;a:6:{s:2:"id";i:3;s:4:"name";s:16:"Fashion & Beauty";s:4:"slug";s:14:"fashion-beauty";s:9:"image_url";s:48:"https://picsum.photos/seed/group-fashion/600/600";s:15:"cover_image_url";s:55:"https://picsum.photos/seed/group-fashion-cover/1600/700";s:10:"categories";a:1:{i:0;a:11:{s:2:"id";i:4;s:16:"catalog_group_id";i:3;s:9:"parent_id";N;s:4:"name";s:4:"Buku";s:4:"slug";s:4:"buku";s:9:"full_slug";s:4:"buku";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:4;s:5:"level";i:1;s:8:"children";a:2:{i:0;a:11:{s:2:"id";i:12;s:16:"catalog_group_id";N;s:9:"parent_id";i:4;s:4:"name";s:5:"Fiksi";s:4:"slug";s:5:"fiksi";s:9:"full_slug";s:10:"buku/fiksi";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:2;s:8:"children";a:1:{i:0;a:11:{s:2:"id";i:24;s:16:"catalog_group_id";N;s:9:"parent_id";i:12;s:4:"name";s:5:"Novel";s:4:"slug";s:5:"novel";s:9:"full_slug";s:16:"buku/fiksi/novel";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:3;s:8:"children";a:0:{}}}}i:1;a:11:{s:2:"id";i:13;s:16:"catalog_group_id";N;s:9:"parent_id";i:4;s:4:"name";s:8:"Nonfiksi";s:4:"slug";s:8:"nonfiksi";s:9:"full_slug";s:13:"buku/nonfiksi";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;s:5:"level";i:2;s:8:"children";a:0:{}}}}}}i:3;a:6:{s:2:"id";i:7;s:4:"name";s:8:"Featured";s:4:"slug";s:8:"featured";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:0:{}}i:4;a:6:{s:2:"id";i:1;s:4:"name";s:15:"Food & Beverage";s:4:"slug";s:13:"food-beverage";s:9:"image_url";s:45:"https://picsum.photos/seed/group-food/600/600";s:15:"cover_image_url";s:52:"https://picsum.photos/seed/group-food-cover/1600/700";s:10:"categories";a:1:{i:0;a:11:{s:2:"id";i:1;s:16:"catalog_group_id";i:1;s:9:"parent_id";N;s:4:"name";s:10:"Elektronik";s:4:"slug";s:10:"elektronik";s:9:"full_slug";s:10:"elektronik";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:1;s:8:"children";a:2:{i:0;a:11:{s:2:"id";i:6;s:16:"catalog_group_id";i:7;s:9:"parent_id";i:1;s:4:"name";s:10:"Smartphone";s:4:"slug";s:10:"smartphone";s:9:"full_slug";s:21:"elektronik/smartphone";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:2;s:8:"children";a:2:{i:0;a:11:{s:2:"id";i:16;s:16:"catalog_group_id";i:10;s:9:"parent_id";i:6;s:4:"name";s:7:"Android";s:4:"slug";s:7:"android";s:9:"full_slug";s:29:"elektronik/smartphone/android";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:3;s:8:"children";a:0:{}}i:1;a:11:{s:2:"id";i:17;s:16:"catalog_group_id";N;s:9:"parent_id";i:6;s:4:"name";s:6:"iPhone";s:4:"slug";s:6:"iphone";s:9:"full_slug";s:28:"elektronik/smartphone/iphone";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;s:5:"level";i:3;s:8:"children";a:0:{}}}}i:1;a:11:{s:2:"id";i:7;s:16:"catalog_group_id";i:6;s:9:"parent_id";i:1;s:4:"name";s:17:"Laptop & Komputer";s:4:"slug";s:15:"laptop-komputer";s:9:"full_slug";s:26:"elektronik/laptop-komputer";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;s:5:"level";i:2;s:8:"children";a:2:{i:0;a:11:{s:2:"id";i:18;s:16:"catalog_group_id";N;s:9:"parent_id";i:7;s:4:"name";s:13:"Laptop Gaming";s:4:"slug";s:13:"laptop-gaming";s:9:"full_slug";s:40:"elektronik/laptop-komputer/laptop-gaming";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:3;s:8:"children";a:0:{}}i:1;a:11:{s:2:"id";i:19;s:16:"catalog_group_id";N;s:9:"parent_id";i:7;s:4:"name";s:9:"Ultrabook";s:4:"slug";s:9:"ultrabook";s:9:"full_slug";s:36:"elektronik/laptop-komputer/ultrabook";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;s:5:"level";i:3;s:8:"children";a:0:{}}}}}}}}i:5;a:6:{s:2:"id";i:4;s:4:"name";s:11:"Home Living";s:4:"slug";s:11:"home-living";s:9:"image_url";s:45:"https://picsum.photos/seed/group-home/600/600";s:15:"cover_image_url";s:52:"https://picsum.photos/seed/group-home-cover/1600/700";s:10:"categories";a:1:{i:0;a:11:{s:2:"id";i:5;s:16:"catalog_group_id";i:4;s:9:"parent_id";N;s:4:"name";s:15:"Perabotan Rumah";s:4:"slug";s:15:"perabotan-rumah";s:9:"full_slug";s:15:"perabotan-rumah";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:5;s:5:"level";i:1;s:8:"children";a:2:{i:0;a:11:{s:2:"id";i:14;s:16:"catalog_group_id";i:8;s:9:"parent_id";i:5;s:4:"name";s:5:"Kursi";s:4:"slug";s:5:"kursi";s:9:"full_slug";s:21:"perabotan-rumah/kursi";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:2;s:8:"children";a:1:{i:0;a:11:{s:2:"id";i:25;s:16:"catalog_group_id";N;s:9:"parent_id";i:14;s:4:"name";s:12:"Kursi Kantor";s:4:"slug";s:12:"kursi-kantor";s:9:"full_slug";s:34:"perabotan-rumah/kursi/kursi-kantor";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:1;s:5:"level";i:3;s:8:"children";a:0:{}}}}i:1;a:11:{s:2:"id";i:15;s:16:"catalog_group_id";i:3;s:9:"parent_id";i:5;s:4:"name";s:4:"Meja";s:4:"slug";s:4:"meja";s:9:"full_slug";s:20:"perabotan-rumah/meja";s:9:"image_url";N;s:8:"icon_url";N;s:10:"sort_order";i:2;s:5:"level";i:2;s:8:"children";a:0:{}}}}}}i:6;a:6:{s:2:"id";i:8;s:4:"name";s:16:"Kebutuhan Harian";s:4:"slug";s:16:"kebutuhan-harian";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:0:{}}i:7;a:6:{s:2:"id";i:5;s:4:"name";s:14:"Sports & Hobby";s:4:"slug";s:12:"sports-hobby";s:9:"image_url";s:46:"https://picsum.photos/seed/group-hobby/600/600";s:15:"cover_image_url";s:53:"https://picsum.photos/seed/group-hobby-cover/1600/700";s:10:"categories";a:0:{}}i:8;a:6:{s:2:"id";i:9;s:4:"name";s:16:"Tagihan & Top Up";s:4:"slug";s:14:"tagihan-top-up";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:0:{}}i:9;a:6:{s:2:"id";i:10;s:4:"name";s:22:"Travel & Entertainment";s:4:"slug";s:20:"travel-entertainment";s:9:"image_url";N;s:15:"cover_image_url";N;s:10:"categories";a:0:{}}}', 1780541436);

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
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `active_user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_user_id_status_index` (`user_id`,`status`),
  KEY `carts_active_user_id_foreign` (`active_user_id`),
  KEY `carts_deleted_at_index` (`deleted_at`),
  CONSTRAINT `carts_active_user_id_foreign` FOREIGN KEY (`active_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.carts: ~1 rows (approximately)
INSERT IGNORE INTO `carts` (`id`, `user_id`, `active_user_id`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(30, '019e6ee0-f99c-7010-b38f-e5cc6f24829d', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'active', '2026-05-28 06:58:33', '2026-05-28 06:58:33', NULL);

-- Dumping structure for table kishamarket.cart_items
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cart_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `quantity` int unsigned NOT NULL,
  `price_snapshot` bigint unsigned NOT NULL,
  `product_name_snapshot` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_image_snapshot` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cart_items_cart_id_product_id_unique` (`cart_id`,`product_id`),
  KEY `cart_items_product_id_index` (`product_id`),
  KEY `cart_items_deleted_at_index` (`deleted_at`),
  CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.cart_items: ~2 rows (approximately)
INSERT IGNORE INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `price_snapshot`, `product_name_snapshot`, `product_image_snapshot`, `created_at`, `updated_at`, `deleted_at`) VALUES
	(27, 30, 24, 1, 768, 'Marketplace Product 24', NULL, '2026-05-28 08:11:04', '2026-05-28 08:11:04', NULL),
	(28, 30, 23, 1, 1809, 'Marketplace Product 23', NULL, '2026-05-31 22:14:31', '2026-05-31 22:14:31', NULL);

-- Dumping structure for table kishamarket.catalog_groups
CREATE TABLE IF NOT EXISTS `catalog_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `catalog_groups_slug_unique` (`slug`),
  KEY `idx_catalog_groups_is_active_sort_order` (`is_active`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.catalog_groups: ~10 rows (approximately)
INSERT IGNORE INTO `catalog_groups` (`id`, `name`, `slug`, `description`, `image_url`, `cover_image_url`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 'Food & Beverage', 'food-beverage', 'Produk makanan, snack, minuman, dan kebutuhan konsumsi harian.', 'https://picsum.photos/seed/group-food/600/600', 'https://picsum.photos/seed/group-food-cover/1600/700', 1, 1, '2026-04-23 03:34:24', '2026-04-23 03:35:15'),
	(2, 'Electronics', 'electronics', 'Gadget, aksesoris, perangkat kerja, dan kebutuhan elektronik.', 'https://picsum.photos/seed/group-electronics/600/600', 'https://picsum.photos/seed/group-electronics-cover/1600/700', 2, 1, '2026-04-23 03:34:24', '2026-04-23 03:35:15'),
	(3, 'Fashion & Beauty', 'fashion-beauty', 'Produk fashion, sepatu, style, dan perawatan diri.', 'https://picsum.photos/seed/group-fashion/600/600', 'https://picsum.photos/seed/group-fashion-cover/1600/700', 3, 1, '2026-04-23 03:34:24', '2026-04-23 03:35:15'),
	(4, 'Home Living', 'home-living', 'Rumah tangga, dekorasi, dan kebutuhan living space.', 'https://picsum.photos/seed/group-home/600/600', 'https://picsum.photos/seed/group-home-cover/1600/700', 4, 1, '2026-04-23 03:34:24', '2026-04-23 03:35:15'),
	(5, 'Sports & Hobby', 'sports-hobby', 'Olahraga, buku, hobi, dan aktivitas santai.', 'https://picsum.photos/seed/group-hobby/600/600', 'https://picsum.photos/seed/group-hobby-cover/1600/700', 5, 1, '2026-04-23 03:34:24', '2026-04-23 03:35:15'),
	(6, 'Belanja', 'belanja', 'Kategori utama untuk produk marketplace.', NULL, NULL, 1, 1, '2026-05-06 07:22:58', '2026-05-06 07:23:06'),
	(7, 'Featured', 'featured', 'Kategori pilihan dan rekomendasi.', NULL, NULL, 2, 1, '2026-05-06 07:22:58', '2026-05-06 07:23:06'),
	(8, 'Kebutuhan Harian', 'kebutuhan-harian', 'Produk kebutuhan harian.', NULL, NULL, 3, 1, '2026-05-06 07:22:58', '2026-05-06 07:23:06'),
	(9, 'Tagihan & Top Up', 'tagihan-top-up', 'Pembayaran tagihan dan top up digital.', NULL, NULL, 4, 1, '2026-05-06 07:22:58', '2026-05-06 07:23:06'),
	(10, 'Travel & Entertainment', 'travel-entertainment', 'Produk travel dan hiburan.', NULL, NULL, 5, 1, '2026-05-06 07:22:58', '2026-05-06 07:23:06');

-- Dumping structure for table kishamarket.categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `catalog_group_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `level` tinyint unsigned NOT NULL DEFAULT '1',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_slug` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_visible_in_menu` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_categories_parent_slug` (`parent_id`,`slug`),
  UNIQUE KEY `uk_categories_full_slug` (`full_slug`),
  KEY `idx_categories_menu_tree` (`catalog_group_id`,`parent_id`,`is_active`,`is_visible_in_menu`,`sort_order`),
  KEY `idx_categories_level` (`level`),
  CONSTRAINT `fk_categories_catalog_group` FOREIGN KEY (`catalog_group_id`) REFERENCES `catalog_groups` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.categories: ~25 rows (approximately)
INSERT IGNORE INTO `categories` (`id`, `catalog_group_id`, `parent_id`, `level`, `name`, `slug`, `full_slug`, `description`, `image_url`, `icon_url`, `cover_image_url`, `sort_order`, `is_active`, `is_visible_in_menu`, `created_at`, `updated_at`) VALUES
	(1, 1, NULL, 1, 'Elektronik', 'elektronik', 'elektronik', 'Produk elektronik', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(2, 2, NULL, 1, 'Fashion', 'fashion', 'fashion', 'Pakaian dan aksesoris', NULL, NULL, NULL, 2, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(3, 2, NULL, 1, 'Makanan & Minuman', 'makanan-minuman', 'makanan-minuman', 'Produk makanan dan minuman', NULL, NULL, NULL, 3, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(4, 3, NULL, 1, 'Buku', 'buku', 'buku', 'Buku berbagai genre', NULL, NULL, NULL, 4, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(5, 4, NULL, 1, 'Perabotan Rumah', 'perabotan-rumah', 'perabotan-rumah', 'Furniture dan dekorasi rumah', NULL, NULL, NULL, 5, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(6, 7, 1, 2, 'Smartphone', 'smartphone', 'elektronik/smartphone', 'Smartphone dan gadget', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(7, 6, 1, 2, 'Laptop & Komputer', 'laptop-komputer', 'elektronik/laptop-komputer', 'Laptop, PC, aksesoris', NULL, NULL, NULL, 2, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(8, 5, 2, 2, 'Pakaian Pria', 'pakaian-pria', 'fashion/pakaian-pria', 'Kemeja, celana, jaket pria', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(9, 5, 2, 2, 'Pakaian Wanita', 'pakaian-wanita', 'fashion/pakaian-wanita', 'Dress, blouse, rok', NULL, NULL, NULL, 2, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(10, 4, 3, 2, 'Snack & Cemilan', 'snack-cemilan', 'makanan-minuman/snack-cemilan', 'Keripik, biskuit, permen', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(11, 7, 3, 2, 'Minuman', 'minuman', 'makanan-minuman/minuman', 'Minuman ringan, jus, kopi', NULL, NULL, NULL, 2, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(12, NULL, 4, 2, 'Fiksi', 'fiksi', 'buku/fiksi', 'Novel, cerpen, komik', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(13, NULL, 4, 2, 'Nonfiksi', 'nonfiksi', 'buku/nonfiksi', 'Biografi, motivasi, sains', NULL, NULL, NULL, 2, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(14, 8, 5, 2, 'Kursi', 'kursi', 'perabotan-rumah/kursi', 'Kursi makan, sofa, kursi kerja', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(15, 3, 5, 2, 'Meja', 'meja', 'perabotan-rumah/meja', 'Meja belajar, meja makan', NULL, NULL, NULL, 2, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(16, 10, 6, 3, 'Android', 'android', 'elektronik/smartphone/android', 'Smartphone Android', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(17, NULL, 6, 3, 'iPhone', 'iphone', 'elektronik/smartphone/iphone', 'Apple iPhone', NULL, NULL, NULL, 2, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(18, NULL, 7, 3, 'Laptop Gaming', 'laptop-gaming', 'elektronik/laptop-komputer/laptop-gaming', 'Laptop untuk gaming', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(19, NULL, 7, 3, 'Ultrabook', 'ultrabook', 'elektronik/laptop-komputer/ultrabook', 'Laptop tipis dan ringan', NULL, NULL, NULL, 2, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(20, NULL, 8, 3, 'Kemeja Pria', 'kemeja-pria', 'fashion/pakaian-pria/kemeja-pria', 'Kemeja formal dan kasual', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(21, NULL, 9, 3, 'Dress Wanita', 'dress-wanita', 'fashion/pakaian-wanita/dress-wanita', 'Gaun wanita', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(22, NULL, 10, 3, 'Keripik', 'keripik', 'makanan-minuman/snack-cemilan/keripik', 'Keripik kentang, singkong', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(23, NULL, 11, 3, 'Kopi', 'kopi', 'makanan-minuman/minuman/kopi', 'Biji kopi, kopi bubuk', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(24, NULL, 12, 3, 'Novel', 'novel', 'buku/fiksi/novel', 'Novel romantis, thriller', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58'),
	(25, NULL, 14, 3, 'Kursi Kantor', 'kursi-kantor', 'perabotan-rumah/kursi/kursi-kantor', 'Kursi ergonomis untuk kerja', NULL, NULL, NULL, 1, 1, 1, '2026-05-24 10:00:58', '2026-05-24 10:00:58');

-- Dumping structure for table kishamarket.category_attributes
CREATE TABLE IF NOT EXISTS `category_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `is_required` tinyint(1) DEFAULT '0',
  `is_variant` tinyint(1) DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_category_attribute` (`category_id`,`attribute_id`),
  KEY `fk_category_attribute_attribute` (`attribute_id`),
  CONSTRAINT `fk_category_attribute_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_category_attribute_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table kishamarket.category_attributes: ~0 rows (approximately)

-- Dumping structure for table kishamarket.checkout_sessions
CREATE TABLE IF NOT EXISTS `checkout_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `session_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','pending_payment','waiting_manual_verification','paid','cancelled','expired','failed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `payment_gateway` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_order_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_snap_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_redirect_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `midtrans_payment_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_transaction_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_fraud_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_payload` json DEFAULT NULL,
  `payment_instructions` json DEFAULT NULL,
  `manual_transfer_payload` json DEFAULT NULL,
  `manual_transfer_proof_path` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manual_verified_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manual_verified_at` timestamp NULL DEFAULT NULL,
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shipping_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `cart_snapshot` json NOT NULL,
  `shipping_address` json NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_order_id` bigint unsigned DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `checkout_sessions_session_number_unique` (`session_number`),
  UNIQUE KEY `checkout_sessions_midtrans_order_id_unique` (`midtrans_order_id`),
  KEY `checkout_sessions_user_id_index` (`user_id`),
  KEY `checkout_sessions_status_index` (`status`),
  KEY `checkout_sessions_created_order_id_index` (`created_order_id`),
  KEY `checkout_sessions_manual_verified_by_index` (`manual_verified_by`),
  CONSTRAINT `checkout_sessions_created_order_id_foreign` FOREIGN KEY (`created_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `checkout_sessions_manual_verified_by_foreign` FOREIGN KEY (`manual_verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `checkout_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.checkout_sessions: ~0 rows (approximately)

-- Dumping structure for table kishamarket.checkout_session_items
CREATE TABLE IF NOT EXISTS `checkout_session_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `checkout_session_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int unsigned NOT NULL,
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checkout_session_items_session_id_index` (`checkout_session_id`),
  KEY `checkout_session_items_product_id_index` (`product_id`),
  CONSTRAINT `checkout_session_items_session_id_foreign` FOREIGN KEY (`checkout_session_id`) REFERENCES `checkout_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.checkout_session_items: ~0 rows (approximately)

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

-- Dumping structure for table kishamarket.midtrans_notifications
CREATE TABLE IF NOT EXISTS `midtrans_notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_attempt_id` bigint unsigned DEFAULT NULL,
  `checkout_session_id` bigint unsigned DEFAULT NULL,
  `order_id` bigint unsigned DEFAULT NULL,
  `gateway_order_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway_transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload_hash` char(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` json NOT NULL,
  `received_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `midtrans_notifications_payload_hash_unique` (`payload_hash`),
  KEY `midtrans_notifications_gateway_order_id_index` (`gateway_order_id`),
  KEY `midtrans_notifications_order_id_index` (`order_id`),
  KEY `midtrans_notifications_payment_attempt_id_foreign` (`payment_attempt_id`),
  KEY `midtrans_notifications_checkout_session_id_index` (`checkout_session_id`),
  CONSTRAINT `midtrans_notifications_checkout_session_id_foreign` FOREIGN KEY (`checkout_session_id`) REFERENCES `checkout_sessions` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `midtrans_notifications_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `midtrans_notifications_payment_attempt_id_foreign` FOREIGN KEY (`payment_attempt_id`) REFERENCES `payment_attempts` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.midtrans_notifications: ~0 rows (approximately)

-- Dumping structure for table kishamarket.migrations
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

-- Dumping structure for table kishamarket.orders
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_number` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `midtrans_order_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_cart_id` bigint unsigned DEFAULT NULL,
  `source_cart_item_ids` json DEFAULT NULL,
  `status` enum('pending','confirmed','processing','shipped','delivered','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_status` enum('unpaid','pending','paid','failed','expired','refunded','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid',
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shipping_cost` decimal(15,2) NOT NULL DEFAULT '0.00',
  `discount_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `tax_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `grand_total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shipping_address` json NOT NULL,
  `payment_method` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_gateway` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_snap_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_redirect_url` text COLLATE utf8mb4_unicode_ci,
  `midtrans_payment_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_transaction_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_fraud_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `midtrans_payload` json DEFAULT NULL,
  `payment_instructions` json DEFAULT NULL,
  `payment_failed_reason` text COLLATE utf8mb4_unicode_ci,
  `paid_at` timestamp NULL DEFAULT NULL,
  `payment_expires_at` timestamp NULL DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  UNIQUE KEY `orders_midtrans_order_id_unique` (`midtrans_order_id`),
  KEY `orders_user_id_index` (`user_id`),
  KEY `orders_order_number_index` (`order_number`),
  KEY `orders_status_index` (`status`),
  KEY `orders_payment_status_index` (`payment_status`),
  KEY `orders_created_at_index` (`created_at`),
  KEY `orders_source_cart_id_index` (`source_cart_id`),
  KEY `orders_payment_status_expires_index` (`payment_status`,`payment_expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.orders: ~0 rows (approximately)

-- Dumping structure for table kishamarket.order_items
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` int unsigned NOT NULL,
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `unit_price` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`),
  KEY `order_items_product_id_index` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.order_items: ~0 rows (approximately)

-- Dumping structure for table kishamarket.order_status_histories
CREATE TABLE IF NOT EXISTS `order_status_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `from_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `changed_by` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_histories_order_id_index` (`order_id`),
  KEY `order_status_histories_changed_by_index` (`changed_by`),
  KEY `order_status_histories_created_at_index` (`created_at`),
  CONSTRAINT `order_status_histories_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.order_status_histories: ~0 rows (approximately)

-- Dumping structure for table kishamarket.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table kishamarket.payment_attempts
CREATE TABLE IF NOT EXISTS `payment_attempts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned DEFAULT NULL,
  `checkout_session_id` bigint unsigned DEFAULT NULL,
  `attempt_no` int unsigned NOT NULL,
  `gateway` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'midtrans',
  `gateway_order_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gateway_transaction_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snap_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `redirect_url` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('initiated','pending','paid','failed','expired','cancelled','refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'initiated',
  `payment_type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fraud_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `failure_reason` text COLLATE utf8mb4_unicode_ci,
  `provider_response_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_response_message` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'IDR',
  `gross_amount` bigint unsigned NOT NULL,
  `request_payload` json DEFAULT NULL,
  `response_payload` json DEFAULT NULL,
  `latest_notification_payload` json DEFAULT NULL,
  `payment_instructions` json DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_attempts_gateway_gateway_order_id_unique` (`gateway`,`gateway_order_id`),
  UNIQUE KEY `payment_attempts_order_attempt_no_unique` (`order_id`,`attempt_no`),
  KEY `payment_attempts_order_id_index` (`order_id`),
  KEY `payment_attempts_status_index` (`status`),
  KEY `payment_attempts_gateway_transaction_id_index` (`gateway_transaction_id`),
  KEY `payment_attempts_gateway_status_expires_index` (`gateway`,`status`,`expires_at`),
  KEY `payment_attempts_checkout_session_id_foreign` (`checkout_session_id`),
  CONSTRAINT `payment_attempts_checkout_session_id_foreign` FOREIGN KEY (`checkout_session_id`) REFERENCES `checkout_sessions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `payment_attempts_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.payment_attempts: ~0 rows (approximately)

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
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.personal_access_tokens: ~53 rows (approximately)
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
	(103, 'App\\Models\\User', '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 'marketplace-web', 'f44da723b3a43d3a69fa9ab2ec2a85b5340dfeb992a8d2401b7ebd2397881b77', '["*"]', '2026-06-01 00:02:41', NULL, '2026-06-01 00:02:21', '2026-06-01 00:02:41');

-- Dumping structure for table kishamarket.products
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned DEFAULT NULL,
  `primary_category_id` bigint unsigned DEFAULT NULL,
  `seller_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `short_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  KEY `idx_products_is_active_featured` (`is_active`,`is_featured`),
  KEY `idx_products_status_created_id` (`status`,`created_at`,`id`),
  KEY `idx_products_primary_category_status` (`primary_category_id`,`status`),
  KEY `idx_products_store_status_created_id` (`store_id`,`status`,`created_at`,`id`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`primary_category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `products_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `products_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.products: ~26 rows (approximately)
INSERT IGNORE INTO `products` (`id`, `store_id`, `primary_category_id`, `seller_id`, `name`, `slug`, `sku`, `description`, `short_description`, `brand`, `weight_gram`, `price`, `stock`, `thumbnail`, `status`, `is_featured`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, 4, 10, '019da96b-0509-7381-9312-f18813450644', 'Marketplace Product 1', 'marketplace-product-1', 'SKU-00001', 'Description for Marketplace Product 1 in Electronics.', 'Description for Marketplace Product 1 in Electronics.', 'TechLine', 1200, 190000068.74, 145, 'https://picsum.photos/seed/1_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(2, 3, 10, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 2', 'marketplace-product-2', 'SKU-00002', 'Description for Marketplace Product 2 in Books.', 'Description for Marketplace Product 2 in Books.', 'BookNest', 450, 549.76, 42, 'https://picsum.photos/seed/2_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(3, 3, 10, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 3', 'marketplace-product-3', 'SKU-00003', 'Description for Marketplace Product 3 in Books.', 'Description for Marketplace Product 3 in Books.', 'BookNest', 450, 370.05, 74, 'https://picsum.photos/seed/3_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(4, 1, 2, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 4', 'marketplace-product-4', 'SKU-00004', 'Description for Marketplace Product 4 in Electronics.', 'Description for Marketplace Product 4 in Electronics.', 'TechLine', 1200, 1514.23, 124, 'https://picsum.photos/seed/4_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(5, 4, 6, '019da96b-0509-7381-9312-f18813450644', 'Marketplace Product 5', 'marketplace-product-5', 'SKU-00005', 'Description for Marketplace Product 5 in Sports.', 'Description for Marketplace Product 5 in Sports.', 'ActiveFit', 900, 884.90, 109, 'https://picsum.photos/seed/5_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(6, 5, 2, '019da96b-06b7-70e0-adfb-035d02015b79', 'Marketplace Product 6', 'marketplace-product-6', 'SKU-00006', 'Description for Marketplace Product 6 in Electronics.', 'Description for Marketplace Product 6 in Electronics.', 'TechLine', 1200, 1813.85, 105, 'https://picsum.photos/seed/6_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(7, 1, 3, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 7', 'marketplace-product-7', 'SKU-00007', 'Description for Marketplace Product 7 in Fashion.', 'Description for Marketplace Product 7 in Fashion.', 'ModeWear', 500, 24616.87, 51, 'https://picsum.photos/seed/7_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(8, 1, 1, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 8', 'marketplace-product-8', 'SKU-00008', 'Description for Marketplace Product 8 in Food.', 'Description for Marketplace Product 8 in Food.', 'DailyChoice', 700, 40.68, 113, 'https://picsum.photos/seed/8_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(9, 3, 1, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 9', 'marketplace-product-9', 'SKU-00009', 'Description for Marketplace Product 9 in Food.', 'Description for Marketplace Product 9 in Food.', 'DailyChoice', 700, 1000877.04, 140, 'https://picsum.photos/seed/9_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(10, 5, 8, '019da96b-06b7-70e0-adfb-035d02015b79', 'Marketplace Product 10', 'marketplace-product-10', 'SKU-00010', 'Description for Marketplace Product 10 in Garden.', 'Description for Marketplace Product 10 in Garden.', 'GreenSpace', 1500, 185.88, 46, 'https://picsum.photos/seed/10_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(11, 2, 4, '019da96b-0108-7193-b0f7-63ddb08a9eea', 'Marketplace Product 11', 'marketplace-product-11', 'SKU-00011', 'Description for Marketplace Product 11 in Home & Living.', 'Description for Marketplace Product 11 in Home & Living.', 'HomeEase', 1800, 1000988.94, 68, 'https://picsum.photos/seed/11_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(12, 4, 5, '019da96b-0509-7381-9312-f18813450644', 'Marketplace Product 12', 'marketplace-product-12', 'SKU-00012', 'Description for Marketplace Product 12 in Beauty.', 'Description for Marketplace Product 12 in Beauty.', 'GlowCare', 300, 182.01, 20, 'https://picsum.photos/seed/12_1/900/900', 'published', 1, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(13, 3, 3, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 13', 'marketplace-product-13', 'SKU-00013', 'Description for Marketplace Product 13 in Fashion.', 'Description for Marketplace Product 13 in Fashion.', 'ModeWear', 500, 1016.13, 89, 'https://picsum.photos/seed/13_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
	(14, 1, 7, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 14', 'marketplace-product-14', 'SKU-00014', 'Description for Marketplace Product 14 in Books.', 'Description for Marketplace Product 14 in Books.', 'BookNest', 450, 1373.39, 99, 'https://picsum.photos/seed/14_1/900/900', 'published', 0, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
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

-- Dumping structure for table kishamarket.product_attributes
CREATE TABLE IF NOT EXISTS `product_attributes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `type` enum('text','number','select','multiselect','boolean') DEFAULT 'text',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table kishamarket.product_attributes: ~0 rows (approximately)

-- Dumping structure for table kishamarket.product_attribute_options
CREATE TABLE IF NOT EXISTS `product_attribute_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` bigint unsigned NOT NULL,
  `value` varchar(255) NOT NULL,
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_attribute_options_attribute` (`attribute_id`),
  CONSTRAINT `fk_attribute_options_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table kishamarket.product_attribute_options: ~0 rows (approximately)

-- Dumping structure for table kishamarket.product_attribute_values
CREATE TABLE IF NOT EXISTS `product_attribute_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_product_attribute` (`product_id`,`attribute_id`),
  KEY `fk_product_attribute_attribute` (`attribute_id`),
  CONSTRAINT `fk_product_attribute_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_product_attribute_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table kishamarket.product_attribute_values: ~0 rows (approximately)

-- Dumping structure for table kishamarket.product_categories
CREATE TABLE IF NOT EXISTS `product_categories` (
  `product_id` bigint unsigned NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `uq_product_category` (`product_id`,`category_id`),
  KEY `idx_product_categories_category_product` (`category_id`,`product_id`),
  KEY `idx_product_categories_is_primary` (`product_id`,`is_primary`),
  CONSTRAINT `fk_pc_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pc_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.product_categories: ~37 rows (approximately)
INSERT IGNORE INTO `product_categories` (`product_id`, `category_id`, `is_primary`, `created_at`, `updated_at`) VALUES
	(1, 11, 0, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(1, 21, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(2, 11, 0, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(2, 22, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(3, 12, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(4, 13, 0, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(4, 23, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(5, 15, 0, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(5, 24, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(6, 16, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(7, 17, 0, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(7, 25, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(8, 18, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(9, 19, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(10, 20, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(11, 11, 0, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(11, 21, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(12, 12, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(13, 13, 0, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(13, 23, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(14, 14, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(15, 15, 0, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(15, 24, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(16, 16, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(17, 17, 0, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(17, 25, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(18, 18, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(19, 19, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(20, 20, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(21, 11, 0, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(21, 21, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(22, 11, 0, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(22, 22, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(23, 13, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(24, 15, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(25, 17, 1, '2026-05-24 10:15:48', '2026-05-24 10:15:48'),
	(25, 25, 0, '2026-05-24 10:15:48', '2026-05-24 10:15:48');

-- Dumping structure for table kishamarket.product_images
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `image_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `alt_text` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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

-- Dumping structure for table kishamarket.product_variants
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `price` decimal(14,2) NOT NULL,
  `stock` int unsigned DEFAULT '0',
  `weight_gram` int unsigned DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_product_variants_product` (`product_id`),
  CONSTRAINT `fk_product_variants_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table kishamarket.product_variants: ~0 rows (approximately)

-- Dumping structure for table kishamarket.product_variant_values
CREATE TABLE IF NOT EXISTS `product_variant_values` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `variant_id` bigint unsigned NOT NULL,
  `attribute_id` bigint unsigned NOT NULL,
  `option_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_variant_value_variant` (`variant_id`),
  KEY `fk_variant_value_attribute` (`attribute_id`),
  KEY `fk_variant_value_option` (`option_id`),
  CONSTRAINT `fk_variant_value_attribute` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_variant_value_option` FOREIGN KEY (`option_id`) REFERENCES `product_attribute_options` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_variant_value_variant` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table kishamarket.product_variant_values: ~0 rows (approximately)

-- Dumping structure for table kishamarket.reviews
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

-- Dumping data for table kishamarket.reviews: ~0 rows (approximately)

-- Dumping structure for table kishamarket.roles
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

-- Dumping structure for table kishamarket.seller_profiles
CREATE TABLE IF NOT EXISTS `seller_profiles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `store_id` bigint unsigned DEFAULT NULL,
  `status` enum('pending','active','suspended','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `verified_at` timestamp NULL DEFAULT NULL,
  `suspended_at` timestamp NULL DEFAULT NULL,
  `rejected_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `seller_profiles_user_id_unique` (`user_id`),
  UNIQUE KEY `seller_profiles_store_id_unique` (`store_id`),
  KEY `seller_profiles_status_index` (`status`),
  CONSTRAINT `seller_profiles_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL,
  CONSTRAINT `seller_profiles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.seller_profiles: ~5 rows (approximately)
INSERT IGNORE INTO `seller_profiles` (`id`, `user_id`, `store_id`, `status`, `verified_at`, `suspended_at`, `rejected_reason`, `created_at`, `updated_at`) VALUES
	(1, '019da96a-ff4a-7167-a188-05685e19921b', 1, 'active', '2026-05-18 06:34:17', NULL, NULL, '2026-05-18 06:34:17', '2026-05-18 06:34:17'),
	(2, '019da96b-0108-7193-b0f7-63ddb08a9eea', 2, 'active', '2026-05-18 06:34:17', NULL, NULL, '2026-05-18 06:34:17', '2026-05-18 06:34:17'),
	(3, '019da96b-0352-70b2-89ef-54a033feb736', 3, 'active', '2026-05-18 06:34:17', NULL, NULL, '2026-05-18 06:34:17', '2026-05-18 06:34:17'),
	(4, '019da96b-0509-7381-9312-f18813450644', 4, 'active', '2026-05-18 06:34:17', NULL, NULL, '2026-05-18 06:34:17', '2026-05-18 06:34:17'),
	(5, '019da96b-06b7-70e0-adfb-035d02015b79', 5, 'active', '2026-05-18 06:34:17', NULL, NULL, '2026-05-18 06:34:17', '2026-05-18 06:34:17');

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

-- Dumping data for table kishamarket.sessions: ~9 rows (approximately)
INSERT IGNORE INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('1f6tTR9OuTMcQZv76vr2JU1ocZrxf4JFOIp8Wodg', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJoY0hMR2ExT3ZzT0hGYlFUOGRjMW5CQTc1aHFQSzNWc2FNTlh2UEkwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776670743),
	('2keNjE4FWnRDyKxWV5Nhw0qgZVHD9WvSbSnBgN2T', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJEQnlSengwa3o2TzF4cjc1V1ZHOEJlM2ZBR0JIVWhGdzQ0bnFNRHB2IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL21hcmtldC1hcGkudGVzdCIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779616974),
	('Bbb8sn4ccPNnF0UpSjnDHi835aBV5OGtC5JDbqZe', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJKSDVpOERSV3VCdDJuSUVDaG9Ec1VzTGM4V05EbHY1ckd5MTlvSG9pIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL21hcmtldC1hcGkudGVzdCIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779542384),
	('ev7s5JCNVWA5f8AxwpPF5ZXobCByfzw6e9EujaV1', NULL, '127.0.0.1', 'PostmanRuntime/7.37.3', 'eyJfdG9rZW4iOiJhNnlVTWxUUGl1TDdyMTlzYTFCcVNVZnBVQ2JqRGEycW5qUllXeWNvIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776664754),
	('HP33qdWDpAtr2cqwRW6ItY5kIAN4hMcFD7Kn5IM3', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJmaEkya3dtWlltS2lYeVlyd0JBZFp3YWY3Z1B0TXV5UlNMTEZGcXhnIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1777269692),
	('KTvSNNdtAJc8r9gJb2HRCVeeo7Nz84jJsuDFcPMx', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI3ekUwQUpvTFlXQ094R1BxcEwybVBKTEVXRHFPZGlTSG9sUHZkWU5PIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwXC9zYW5jdHVtXC9jc3JmLWNvb2tpZSIsInJvdXRlIjoic2FuY3R1bS5jc3JmLWNvb2tpZSJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1779631378),
	('R9gPnyuxuzuQOXy9OWlSxKdiv8TgWjbKkRcchcDW', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJHbTJSV2FNMnNCWDhkaXU4N1dvOGNaUlp3WGFvQlI4V0VPTTlmSFl6IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL21hcmtldC1hcGkudGVzdCIsInJvdXRlIjpudWxsfSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==', 1779510889),
	('Scl8HU7PJqj8HlaJBQWAgNksUxuy9O0MyJrVaynN', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI2cFhLVFFtQ2wzOUVQbFdrZkFoaUptd2JtdXN5R0dOT0pwUmt1OXRSIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776670741),
	('TGFQSfz9zNriU4KhYcfKiscu176v6XoZMsZ9rOGX', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'eyJfdG9rZW4iOiJxeFFXOTU2OWJldmFYdldUUXFGQWtHUndSWWs4S3N4SEJucDlkYWYyIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1779257821);

-- Dumping structure for table kishamarket.stores
CREATE TABLE IF NOT EXISTS `stores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `short_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(80) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `banner_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stores_user_id_unique` (`user_id`),
  UNIQUE KEY `stores_slug_unique` (`slug`),
  KEY `idx_stores_active_created_id` (`is_active`,`created_at`,`id`),
  CONSTRAINT `stores_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.stores: ~5 rows (approximately)
INSERT IGNORE INTO `stores` (`id`, `user_id`, `name`, `slug`, `description`, `short_description`, `phone`, `email`, `city`, `province`, `address`, `is_active`, `logo`, `banner_url`, `created_at`, `updated_at`) VALUES
	(1, '019da96a-ff4a-7167-a188-05685e19921b', 'Seller 1 Store', 'seller-1-store', 'Curated marketplace store #1.', 'Trusted marketplace store #1 dengan kurasi produk yang rapi dan visual yang lebih menarik.', '08123000001', 'seller1store@ukomp.test', 'Jakarta', 'DKI Jakarta', 'Jl. Store No. 1, Jakarta', 1, 'https://picsum.photos/seed/store1/300/300', 'https://picsum.photos/seed/store-banner-1/1600/600', '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
	(2, '019da96b-0108-7193-b0f7-63ddb08a9eea', 'Seller 2 Store', 'seller-2-store', 'Curated marketplace store #2.', 'Trusted marketplace store #2 dengan kurasi produk yang rapi dan visual yang lebih menarik.', '08123000002', 'seller2store@ukomp.test', 'Bandung', 'Jawa Barat', 'Jl. Store No. 2, Bandung', 1, 'https://picsum.photos/seed/store2/300/300', 'https://picsum.photos/seed/store-banner-2/1600/600', '2026-04-19 22:44:15', '2026-04-19 22:44:15'),
	(3, '019da96b-0352-70b2-89ef-54a033feb736', 'Seller 3 Store', 'seller-3-store', 'Curated marketplace store #3.', 'Trusted marketplace store #3 dengan kurasi produk yang rapi dan visual yang lebih menarik.', '08123000003', 'seller3store@ukomp.test', 'Surabaya', 'Jawa Timur', 'Jl. Store No. 3, Surabaya', 1, 'https://picsum.photos/seed/store3/300/300', 'https://picsum.photos/seed/store-banner-3/1600/600', '2026-04-19 22:44:15', '2026-04-19 22:44:15'),
	(4, '019da96b-0509-7381-9312-f18813450644', 'Seller 4 Store', 'seller-4-store', 'Curated marketplace store #4.', 'Trusted marketplace store #4 dengan kurasi produk yang rapi dan visual yang lebih menarik.', '08123000004', 'seller4store@ukomp.test', 'Yogyakarta', 'DI Yogyakarta', 'Jl. Store No. 4, Yogyakarta', 1, 'https://picsum.photos/seed/store4/300/300', 'https://picsum.photos/seed/store-banner-4/1600/600', '2026-04-19 22:44:16', '2026-04-19 22:44:16'),
	(5, '019da96b-06b7-70e0-adfb-035d02015b79', 'Seller 5 Store', 'seller-5-store', 'Curated marketplace store #5.', 'Trusted marketplace store #5 dengan kurasi produk yang rapi dan visual yang lebih menarik.', '08123000005', 'seller5store@ukomp.test', 'Semarang', 'Jawa Tengah', 'Jl. Store No. 5, Semarang', 1, 'https://picsum.photos/seed/store5/300/300', 'https://picsum.photos/seed/store-banner-5/1600/600', '2026-04-19 22:44:16', '2026-04-19 22:44:16');

-- Dumping structure for table kishamarket.store_catalog_groups
CREATE TABLE IF NOT EXISTS `store_catalog_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `name` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `thumbnail` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_catalog_groups_store_id_slug_unique` (`store_id`,`slug`),
  KEY `idx_store_catalog_groups_store_active_order` (`store_id`,`is_active`,`sort_order`,`id`),
  CONSTRAINT `store_catalog_groups_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.store_catalog_groups: ~0 rows (approximately)

-- Dumping structure for table kishamarket.store_catalog_group_product
CREATE TABLE IF NOT EXISTS `store_catalog_group_product` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_catalog_group_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_catalog_group_product_unique` (`store_catalog_group_id`,`product_id`),
  KEY `store_catalog_group_product_product_id_foreign` (`product_id`),
  CONSTRAINT `store_catalog_group_product_group_id_foreign` FOREIGN KEY (`store_catalog_group_id`) REFERENCES `store_catalog_groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `store_catalog_group_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.store_catalog_group_product: ~0 rows (approximately)

-- Dumping structure for table kishamarket.store_categories
CREATE TABLE IF NOT EXISTS `store_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` varchar(160) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_categories_store_id_slug_unique` (`store_id`,`slug`),
  KEY `idx_store_categories_store_active_order` (`store_id`,`is_active`,`sort_order`,`id`),
  KEY `store_categories_parent_id_foreign` (`parent_id`),
  CONSTRAINT `store_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `store_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `store_categories_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.store_categories: ~0 rows (approximately)

-- Dumping structure for table kishamarket.store_category_product
CREATE TABLE IF NOT EXISTS `store_category_product` (
  `store_category_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`store_category_id`,`product_id`),
  KEY `store_category_product_product_id_foreign` (`product_id`),
  CONSTRAINT `store_category_product_category_id_foreign` FOREIGN KEY (`store_category_id`) REFERENCES `store_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `store_category_product_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.store_category_product: ~0 rows (approximately)

-- Dumping structure for table kishamarket.store_details
CREATE TABLE IF NOT EXISTS `store_details` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `owner_name` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_phone` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `shipping_policy` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `return_policy` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `open_days` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `open_time` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `close_time` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `instagram_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tiktok_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
CREATE TABLE IF NOT EXISTS `users` (
  `id` char(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `firebase_uid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_firebase_uid_unique` (`firebase_uid`),
  KEY `users_deleted_at_index` (`deleted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.users: ~17 rows (approximately)
INSERT IGNORE INTO `users` (`id`, `firebase_uid`, `email`, `password`, `name`, `avatar`, `is_email_verified`, `created_at`, `updated_at`, `deleted_at`) VALUES
	('019da96a-ff4a-7167-a188-05685e19921b', '', 'seller1@ukomp.test', '$2y$12$sXBRk2wfuYuVc5bUB334FuNh4XEhm9lJWUmCdYg4B/NLNTg29pH5i', 'Seller 1', NULL, 1, '2026-04-19 22:44:14', '2026-04-19 22:44:14', NULL),
	('019da96b-0108-7193-b0f7-63ddb08a9eea', '0f5f464474f14ec0a13bc14f1279', 'seller2@ukomp.test', '$2y$12$OYuidfKIfHx2uxbTdsR/6eHzoGbWS0wnrqSSB985ApNoAh83mBb6O', 'Seller 2', NULL, 1, '2026-04-19 22:44:15', '2026-04-19 22:44:15', NULL),
	('019da96b-0352-70b2-89ef-54a033feb736', '0f9627ee5da5a5a9123901ac7e63', 'seller3@ukomp.test', '$2y$12$ANchKvuem2jKtFl6PmRAhO62k57CiG/PjwDxGnvATbgoPr.NXbxY2', 'Seller 3', NULL, 1, '2026-04-19 22:44:15', '2026-04-19 22:44:15', NULL),
	('019da96b-0509-7381-9312-f18813450644', 'NULL', 'seller4@ukomp.test', '$2y$12$rOtC1nHBA08RMuQ7utAt2OpMynV9irRP.Et2HfJK5.9qKKeSa6kyC', 'Seller 4', NULL, 1, '2026-04-19 22:44:16', '2026-04-19 22:44:16', NULL),
	('019da96b-06b7-70e0-adfb-035d02015b79', 'bafec3baafde8da925ce7a15ad5d', 'seller5@ukomp.test', '$2y$12$aI9qp/W8wlgxmkjgKG5XBOSPoYiH20tZwpYpwKVxNHNjYo6FLHItu', 'Seller 5', NULL, 1, '2026-04-19 22:44:16', '2026-04-19 22:44:16', NULL),
	('019da96b-08e3-7005-9c1b-8a17feb799ac', '04c8fa7eb5da9a90b9246efc3fed', 'buyer1@ukomp.test', '$2y$12$3zXfHYah9dCd5bk0q1cB8upfjqkV9e/FrVwLk3auC5mQgmAZ6fn5a', 'Buyer 1', NULL, 1, '2026-04-19 22:44:17', '2026-04-19 22:44:17', NULL),
	('019da96b-0adb-72a5-ab39-a74b473b66ff', 'c8caa23a767e1ae9b5972a0955a1', 'buyer2@ukomp.test', '$2y$12$JBKd3gi4UIiyH/Pad1ca1OmOPhlBuxI7yH64vbMCbTuvj/rTUeC7y', 'Buyer 2', NULL, 1, '2026-04-19 22:44:17', '2026-04-19 22:44:17', NULL),
	('019da96b-0ce0-701d-97e0-2bf4d98ce6bb', '4fccccee9afe940f9db4afcb2b0b', 'buyer3@ukomp.test', '$2y$12$3bbibucEy0ts5gPJbORtKeuvNqcpOZ/QUR9XS0xTZFMzbQVeRS7Iy', 'Buyer 3', NULL, 1, '2026-04-19 22:44:18', '2026-04-19 22:44:18', NULL),
	('019da96b-0e91-70b3-9e0d-ff2efbe12033', '4e0dbdebe00f5c1fe06853bbd359', 'buyer4@ukomp.test', '$2y$12$ojlo64LqOtjUfDzD7XsWhODr/b38Lvk/3GG0gy1sDvwrODWEhu.DS', 'Buyer 4', NULL, 1, '2026-04-19 22:44:18', '2026-04-19 22:44:18', NULL),
	('019da96b-1210-724e-8353-db2529099f65', '1804cf37141411562a974dd7b920', 'buyer6@ukomp.test', '$2y$12$GOTY4aokXrDSaQxpI2kmWu3p8Ls0Sg7B9oCI3ISPmYpaTtnoLN9l2', 'Buyer 6', NULL, 1, '2026-04-19 22:44:19', '2026-04-19 22:44:19', NULL),
	('019da96b-13b5-70b3-937c-851bbcedd357', 'be55628c1a1b1f99bad1352a7dff', 'buyer7@ukomp.test', '$2y$12$MDqvZ2mH0eSyRQ.yDDETc.lPq/OryczpBtM7SgFcz4LdiFnwri/3G', 'Buyer 7', NULL, 1, '2026-04-19 22:44:20', '2026-04-19 22:44:20', NULL),
	('019da96b-1568-70dc-a298-97eb551b3cab', 'd2cb33aff1eb55449a3c3dc6400f', 'buyer8@ukomp.test', '$2y$12$M24TEjce8FaqgeRFdyLsje5CazAZ3YRU/ShJgg7DakAJ6U0h/teKO', 'Buyer 8', NULL, 1, '2026-04-19 22:44:20', '2026-04-19 22:44:20', NULL),
	('019da96b-1778-7212-b34b-585fa4f60d50', 'd889724358e3ab6d0386575d1399', 'buyer9@ukomp.test', '$2y$12$90H80x7aCtgVQkN1hM16VOCn20GcbZIJ63Kdlr4r5Xu08vOAMjwwi', 'Buyer 9', NULL, 1, '2026-04-19 22:44:21', '2026-04-19 22:44:21', NULL),
	('019da96b-190e-72ca-b042-a86ba2c4938f', 'a77916c4807d576aebb66a0a1ef8', 'buyer10@ukomp.test', '$2y$12$rGvqlBA7pHsHav1i4f7BseuiM8nwGbsrHWQI/r0cm9TsDtcSVLmq2', 'Buyer 10', NULL, 1, '2026-04-19 22:44:21', '2026-04-19 22:44:21', NULL),
	('019da96b-1aaa-7085-96a9-b2c9e7a9e0a8', 'a7874660890980d5d9d4e713a03d', 'buyer11@ukomp.test', '$2y$12$CRmjLD2/o6lPdJeLv.dO/u07ipoKuTzXlvmkoXr0y0nJ0chFKovs6', 'Buyer 11', NULL, 1, '2026-04-19 22:44:21', '2026-04-19 22:44:21', NULL),
	('019da96b-1ca6-72cf-be0c-04db858da4ab', '61aaf0d98e46ee0a56500eb66a24', 'buyer12@ukomp.test', '$2y$12$7Tn5lT222.UKxIcz/zlzROFjoU5bgFrT1w.VRz5gh3/N8KVKhB8XW', 'Buyer 12', NULL, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22', NULL),
	('019e6ee0-f99c-7010-b38f-e5cc6f24829d', NULL, 'aki9@gmail.com', '$2y$12$xlLfYYWClG8k8kTmS5UB9el9tWeRZGC1kAOO9d0TZvzkHwHxkVBRW', 'djhcjsh', NULL, 0, '2026-05-28 06:58:18', '2026-05-28 06:58:18', NULL);

-- Dumping structure for table kishamarket.user_roles
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
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table kishamarket.user_roles: ~22 rows (approximately)
INSERT IGNORE INTO `user_roles` (`id`, `user_id`, `role_id`, `created_at`, `updated_at`) VALUES
	(10, '019da96b-08e3-7005-9c1b-8a17feb799ac', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(11, '019da96b-190e-72ca-b042-a86ba2c4938f', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(12, '019da96b-1aaa-7085-96a9-b2c9e7a9e0a8', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(13, '019da96b-1ca6-72cf-be0c-04db858da4ab', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(14, '019da96b-0adb-72a5-ab39-a74b473b66ff', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(15, '019da96b-0ce0-701d-97e0-2bf4d98ce6bb', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(16, '019da96b-0e91-70b3-9e0d-ff2efbe12033', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(18, '019da96b-1210-724e-8353-db2529099f65', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(19, '019da96b-13b5-70b3-937c-851bbcedd357', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(20, '019da96b-1568-70dc-a298-97eb551b3cab', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(21, '019da96b-1778-7212-b34b-585fa4f60d50', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(22, '019da96a-ff4a-7167-a188-05685e19921b', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(23, '019da96b-0108-7193-b0f7-63ddb08a9eea', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(24, '019da96b-0352-70b2-89ef-54a033feb736', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(25, '019da96b-0509-7381-9312-f18813450644', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(26, '019da96b-06b7-70e0-adfb-035d02015b79', 1, '2026-05-18 06:21:31', '2026-05-18 06:21:31'),
	(41, '019da96a-ff4a-7167-a188-05685e19921b', 2, '2026-05-18 06:34:30', '2026-05-18 06:34:30'),
	(42, '019da96b-0108-7193-b0f7-63ddb08a9eea', 2, '2026-05-18 06:34:30', '2026-05-18 06:34:30'),
	(43, '019da96b-0352-70b2-89ef-54a033feb736', 2, '2026-05-18 06:34:30', '2026-05-18 06:34:30'),
	(44, '019da96b-0509-7381-9312-f18813450644', 2, '2026-05-18 06:34:30', '2026-05-18 06:34:30'),
	(45, '019da96b-06b7-70e0-adfb-035d02015b79', 2, '2026-05-18 06:34:30', '2026-05-18 06:34:30'),
	(59, '019e6ee0-f99c-7010-b38f-e5cc6f24829d', 1, '2026-05-28 06:58:18', '2026-05-28 06:58:18');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
