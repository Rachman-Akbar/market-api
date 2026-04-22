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

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `label`, `address`, `lat`, `lng`, `created_at`, `updated_at`) VALUES
(1, '019da96b-08e3-7005-9c1b-8a17feb799ac', 'Alamat Utama', 'Jl. Marketplace No. 1, Jakarta', -6.2000000, 106.8166000, '2026-04-19 22:44:24', '2026-04-19 22:44:24'),
(2, '019da96b-0adb-72a5-ab39-a74b473b66ff', 'Alamat Utama', 'Jl. Marketplace No. 2, Jakarta', -6.1990000, 106.8176000, '2026-04-19 22:44:24', '2026-04-19 22:44:24'),
(3, '019da96b-0ce0-701d-97e0-2bf4d98ce6bb', 'Alamat Utama', 'Jl. Marketplace No. 3, Jakarta', -6.1980000, 106.8186000, '2026-04-19 22:44:24', '2026-04-19 22:44:24');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, '019da96b-08e3-7005-9c1b-8a17feb799ac', '2026-04-19 22:44:24', '2026-04-19 22:44:24');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint UNSIGNED NOT NULL,
  `cart_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity` bigint UNSIGNED NOT NULL DEFAULT '1',
  `qty` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `quantity`, `qty`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1, '2026-04-19 22:44:24', '2026-04-19 22:44:24'),
(2, 1, 2, 1, 1, '2026-04-19 22:44:24', '2026-04-19 22:44:24');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Food', 'food', '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
(2, 'Electronics', 'electronics', '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
(3, 'Fashion', 'fashion', '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
(4, 'Home & Living', 'home-living', '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
(5, 'Beauty', 'beauty', '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
(6, 'Sports', 'sports', '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
(7, 'Books', 'books', '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
(8, 'Garden', 'garden', '2026-04-19 22:44:14', '2026-04-19 22:44:14');

-- --------------------------------------------------------

--
-- Table structure for table `catalog_groups`
--

CREATE TABLE `catalog_groups` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
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

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `buyer_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `seller_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending_payment',
  `total_price` decimal(14,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `buyer_id`, `seller_id`, `status`, `total_price`, `created_at`, `updated_at`) VALUES
(1, '019da96b-08e3-7005-9c1b-8a17feb799ac', '019da96b-08e3-7005-9c1b-8a17feb799ac', '019da96b-0509-7381-9312-f18813450644', 'pending', 2518.50, '2026-04-19 22:44:24', '2026-04-19 22:44:24');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity` bigint UNSIGNED NOT NULL DEFAULT '1',
  `price` decimal(14,2) NOT NULL,
  `qty` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `qty`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 1968.74, 1, '2026-04-19 22:44:24', '2026-04-19 22:44:24'),
(2, 1, 2, 1, 549.76, 1, '2026-04-19 22:44:24', '2026-04-19 22:44:24');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `status`, `payment_method`, `created_at`, `updated_at`) VALUES
(1, 1, 'pending', 'manual_transfer', '2026-04-19 22:44:24', '2026-04-19 22:44:24');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `store_id` bigint UNSIGNED DEFAULT NULL,
  `category_id` bigint UNSIGNED DEFAULT NULL,
  `seller_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(14,2) NOT NULL,
  `stock` int UNSIGNED NOT NULL DEFAULT '0',
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `store_id`, `category_id`, `seller_id`, `name`, `slug`, `description`, `price`, `stock`, `thumbnail`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 2, '019da96b-0509-7381-9312-f18813450644', 'Marketplace Product 1', 'marketplace-product-1', 'Description for Marketplace Product 1 in Electronics.', 1968.74, 146, 'https://picsum.photos/seed/product1/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(2, 3, 7, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 2', 'marketplace-product-2', 'Description for Marketplace Product 2 in Books.', 549.76, 70, 'https://picsum.photos/seed/product2/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(3, 3, 7, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 3', 'marketplace-product-3', 'Description for Marketplace Product 3 in Books.', 370.05, 74, 'https://picsum.photos/seed/product3/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(4, 1, 2, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 4', 'marketplace-product-4', 'Description for Marketplace Product 4 in Electronics.', 1514.23, 124, 'https://picsum.photos/seed/product4/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(5, 4, 6, '019da96b-0509-7381-9312-f18813450644', 'Marketplace Product 5', 'marketplace-product-5', 'Description for Marketplace Product 5 in Sports.', 884.90, 109, 'https://picsum.photos/seed/product5/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(6, 5, 2, '019da96b-06b7-70e0-adfb-035d02015b79', 'Marketplace Product 6', 'marketplace-product-6', 'Description for Marketplace Product 6 in Electronics.', 1813.85, 106, 'https://picsum.photos/seed/product6/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(7, 1, 3, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 7', 'marketplace-product-7', 'Description for Marketplace Product 7 in Fashion.', 1616.87, 52, 'https://picsum.photos/seed/product7/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(8, 1, 1, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 8', 'marketplace-product-8', 'Description for Marketplace Product 8 in Food.', 40.68, 113, 'https://picsum.photos/seed/product8/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(9, 3, 1, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 9', 'marketplace-product-9', 'Description for Marketplace Product 9 in Food.', 1877.04, 140, 'https://picsum.photos/seed/product9/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(10, 5, 8, '019da96b-06b7-70e0-adfb-035d02015b79', 'Marketplace Product 10', 'marketplace-product-10', 'Description for Marketplace Product 10 in Garden.', 185.88, 46, 'https://picsum.photos/seed/product10/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(11, 2, 4, '019da96b-0108-7193-b0f7-63ddb08a9eea', 'Marketplace Product 11', 'marketplace-product-11', 'Description for Marketplace Product 11 in Home & Living.', 1988.94, 70, 'https://picsum.photos/seed/product11/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(12, 4, 5, '019da96b-0509-7381-9312-f18813450644', 'Marketplace Product 12', 'marketplace-product-12', 'Description for Marketplace Product 12 in Beauty.', 182.01, 20, 'https://picsum.photos/seed/product12/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(13, 3, 3, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 13', 'marketplace-product-13', 'Description for Marketplace Product 13 in Fashion.', 1016.13, 90, 'https://picsum.photos/seed/product13/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(14, 1, 7, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 14', 'marketplace-product-14', 'Description for Marketplace Product 14 in Books.', 1373.39, 138, 'https://picsum.photos/seed/product14/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(15, 1, 6, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 15', 'marketplace-product-15', 'Description for Marketplace Product 15 in Sports.', 802.22, 26, 'https://picsum.photos/seed/product15/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(16, 3, 7, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 16', 'marketplace-product-16', 'Description for Marketplace Product 16 in Books.', 77.29, 116, 'https://picsum.photos/seed/product16/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(17, 4, 6, '019da96b-0509-7381-9312-f18813450644', 'Marketplace Product 17', 'marketplace-product-17', 'Description for Marketplace Product 17 in Sports.', 1285.41, 34, 'https://picsum.photos/seed/product17/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(18, 3, 1, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 18', 'marketplace-product-18', 'Description for Marketplace Product 18 in Food.', 1283.56, 29, 'https://picsum.photos/seed/product18/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(19, 2, 3, '019da96b-0108-7193-b0f7-63ddb08a9eea', 'Marketplace Product 19', 'marketplace-product-19', 'Description for Marketplace Product 19 in Fashion.', 314.34, 46, 'https://picsum.photos/seed/product19/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(20, 4, 2, '019da96b-0509-7381-9312-f18813450644', 'Marketplace Product 20', 'marketplace-product-20', 'Description for Marketplace Product 20 in Electronics.', 481.26, 48, 'https://picsum.photos/seed/product20/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(21, 1, 1, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 21', 'marketplace-product-21', 'Description for Marketplace Product 21 in Food.', 1459.24, 69, 'https://picsum.photos/seed/product21/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(22, 2, 2, '019da96b-0108-7193-b0f7-63ddb08a9eea', 'Marketplace Product 22', 'marketplace-product-22', 'Description for Marketplace Product 22 in Electronics.', 1579.23, 140, 'https://picsum.photos/seed/product22/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(23, 3, 7, '019da96b-0352-70b2-89ef-54a033feb736', 'Marketplace Product 23', 'marketplace-product-23', 'Description for Marketplace Product 23 in Books.', 1809.98, 124, 'https://picsum.photos/seed/product23/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(24, 1, 5, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 24', 'marketplace-product-24', 'Description for Marketplace Product 24 in Beauty.', 768.42, 54, 'https://picsum.photos/seed/product24/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(25, 1, 2, '019da96a-ff4a-7167-a188-05685e19921b', 'Marketplace Product 25', 'marketplace-product-25', 'Description for Marketplace Product 25 in Electronics.', 682.93, 6, 'https://picsum.photos/seed/product25/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(26, 2, 3, '019da96b-0108-7193-b0f7-63ddb08a9eea', 'Marketplace Product 26', 'marketplace-product-26', 'Description for Marketplace Product 26 in Fashion.', 1109.68, 106, 'https://picsum.photos/seed/product26/800/800', 'published', '2026-04-19 22:44:22', '2026-04-19 22:44:22');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `url`, `is_primary`, `created_at`, `updated_at`) VALUES
(1, 1, 'https://picsum.photos/seed/1_1/900/900', 'https://picsum.photos/seed/1_1/900/900', 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(2, 1, 'https://picsum.photos/seed/1_2/900/900', 'https://picsum.photos/seed/1_2/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(3, 1, 'https://picsum.photos/seed/1_3/900/900', 'https://picsum.photos/seed/1_3/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(4, 2, 'https://picsum.photos/seed/2_1/900/900', 'https://picsum.photos/seed/2_1/900/900', 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(5, 2, 'https://picsum.photos/seed/2_2/900/900', 'https://picsum.photos/seed/2_2/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(6, 2, 'https://picsum.photos/seed/2_3/900/900', 'https://picsum.photos/seed/2_3/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(7, 3, 'https://picsum.photos/seed/3_1/900/900', 'https://picsum.photos/seed/3_1/900/900', 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(8, 3, 'https://picsum.photos/seed/3_2/900/900', 'https://picsum.photos/seed/3_2/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(9, 3, 'https://picsum.photos/seed/3_3/900/900', 'https://picsum.photos/seed/3_3/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(10, 4, 'https://picsum.photos/seed/4_1/900/900', 'https://picsum.photos/seed/4_1/900/900', 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(11, 4, 'https://picsum.photos/seed/4_2/900/900', 'https://picsum.photos/seed/4_2/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(12, 4, 'https://picsum.photos/seed/4_3/900/900', 'https://picsum.photos/seed/4_3/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(13, 5, 'https://picsum.photos/seed/5_1/900/900', 'https://picsum.photos/seed/5_1/900/900', 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(14, 5, 'https://picsum.photos/seed/5_2/900/900', 'https://picsum.photos/seed/5_2/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(15, 5, 'https://picsum.photos/seed/5_3/900/900', 'https://picsum.photos/seed/5_3/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(16, 6, 'https://picsum.photos/seed/6_1/900/900', 'https://picsum.photos/seed/6_1/900/900', 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(17, 6, 'https://picsum.photos/seed/6_2/900/900', 'https://picsum.photos/seed/6_2/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(18, 6, 'https://picsum.photos/seed/6_3/900/900', 'https://picsum.photos/seed/6_3/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(19, 7, 'https://picsum.photos/seed/7_1/900/900', 'https://picsum.photos/seed/7_1/900/900', 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(20, 7, 'https://picsum.photos/seed/7_2/900/900', 'https://picsum.photos/seed/7_2/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(21, 7, 'https://picsum.photos/seed/7_3/900/900', 'https://picsum.photos/seed/7_3/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(22, 8, 'https://picsum.photos/seed/8_1/900/900', 'https://picsum.photos/seed/8_1/900/900', 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(23, 8, 'https://picsum.photos/seed/8_2/900/900', 'https://picsum.photos/seed/8_2/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(24, 8, 'https://picsum.photos/seed/8_3/900/900', 'https://picsum.photos/seed/8_3/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(25, 9, 'https://picsum.photos/seed/9_1/900/900', 'https://picsum.photos/seed/9_1/900/900', 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(26, 9, 'https://picsum.photos/seed/9_2/900/900', 'https://picsum.photos/seed/9_2/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(27, 9, 'https://picsum.photos/seed/9_3/900/900', 'https://picsum.photos/seed/9_3/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(28, 10, 'https://picsum.photos/seed/10_1/900/900', 'https://picsum.photos/seed/10_1/900/900', 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(29, 10, 'https://picsum.photos/seed/10_2/900/900', 'https://picsum.photos/seed/10_2/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(30, 10, 'https://picsum.photos/seed/10_3/900/900', 'https://picsum.photos/seed/10_3/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(31, 11, 'https://picsum.photos/seed/11_1/900/900', 'https://picsum.photos/seed/11_1/900/900', 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(32, 11, 'https://picsum.photos/seed/11_2/900/900', 'https://picsum.photos/seed/11_2/900/900', 0, '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(33, 11, 'https://picsum.photos/seed/11_3/900/900', 'https://picsum.photos/seed/11_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(34, 12, 'https://picsum.photos/seed/12_1/900/900', 'https://picsum.photos/seed/12_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(35, 12, 'https://picsum.photos/seed/12_2/900/900', 'https://picsum.photos/seed/12_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(36, 12, 'https://picsum.photos/seed/12_3/900/900', 'https://picsum.photos/seed/12_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(37, 13, 'https://picsum.photos/seed/13_1/900/900', 'https://picsum.photos/seed/13_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(38, 13, 'https://picsum.photos/seed/13_2/900/900', 'https://picsum.photos/seed/13_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(39, 13, 'https://picsum.photos/seed/13_3/900/900', 'https://picsum.photos/seed/13_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(40, 14, 'https://picsum.photos/seed/14_1/900/900', 'https://picsum.photos/seed/14_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(41, 14, 'https://picsum.photos/seed/14_2/900/900', 'https://picsum.photos/seed/14_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(42, 14, 'https://picsum.photos/seed/14_3/900/900', 'https://picsum.photos/seed/14_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(43, 15, 'https://picsum.photos/seed/15_1/900/900', 'https://picsum.photos/seed/15_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(44, 15, 'https://picsum.photos/seed/15_2/900/900', 'https://picsum.photos/seed/15_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(45, 15, 'https://picsum.photos/seed/15_3/900/900', 'https://picsum.photos/seed/15_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(46, 16, 'https://picsum.photos/seed/16_1/900/900', 'https://picsum.photos/seed/16_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(47, 16, 'https://picsum.photos/seed/16_2/900/900', 'https://picsum.photos/seed/16_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(48, 16, 'https://picsum.photos/seed/16_3/900/900', 'https://picsum.photos/seed/16_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(49, 17, 'https://picsum.photos/seed/17_1/900/900', 'https://picsum.photos/seed/17_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(50, 17, 'https://picsum.photos/seed/17_2/900/900', 'https://picsum.photos/seed/17_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(51, 17, 'https://picsum.photos/seed/17_3/900/900', 'https://picsum.photos/seed/17_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(52, 18, 'https://picsum.photos/seed/18_1/900/900', 'https://picsum.photos/seed/18_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(53, 18, 'https://picsum.photos/seed/18_2/900/900', 'https://picsum.photos/seed/18_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(54, 18, 'https://picsum.photos/seed/18_3/900/900', 'https://picsum.photos/seed/18_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(55, 19, 'https://picsum.photos/seed/19_1/900/900', 'https://picsum.photos/seed/19_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(56, 19, 'https://picsum.photos/seed/19_2/900/900', 'https://picsum.photos/seed/19_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(57, 19, 'https://picsum.photos/seed/19_3/900/900', 'https://picsum.photos/seed/19_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(58, 20, 'https://picsum.photos/seed/20_1/900/900', 'https://picsum.photos/seed/20_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(59, 20, 'https://picsum.photos/seed/20_2/900/900', 'https://picsum.photos/seed/20_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(60, 20, 'https://picsum.photos/seed/20_3/900/900', 'https://picsum.photos/seed/20_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(61, 21, 'https://picsum.photos/seed/21_1/900/900', 'https://picsum.photos/seed/21_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(62, 21, 'https://picsum.photos/seed/21_2/900/900', 'https://picsum.photos/seed/21_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(63, 21, 'https://picsum.photos/seed/21_3/900/900', 'https://picsum.photos/seed/21_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(64, 22, 'https://picsum.photos/seed/22_1/900/900', 'https://picsum.photos/seed/22_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(65, 22, 'https://picsum.photos/seed/22_2/900/900', 'https://picsum.photos/seed/22_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(66, 22, 'https://picsum.photos/seed/22_3/900/900', 'https://picsum.photos/seed/22_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(67, 23, 'https://picsum.photos/seed/23_1/900/900', 'https://picsum.photos/seed/23_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(68, 23, 'https://picsum.photos/seed/23_2/900/900', 'https://picsum.photos/seed/23_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(69, 23, 'https://picsum.photos/seed/23_3/900/900', 'https://picsum.photos/seed/23_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(70, 24, 'https://picsum.photos/seed/24_1/900/900', 'https://picsum.photos/seed/24_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(71, 24, 'https://picsum.photos/seed/24_2/900/900', 'https://picsum.photos/seed/24_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(72, 24, 'https://picsum.photos/seed/24_3/900/900', 'https://picsum.photos/seed/24_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(73, 25, 'https://picsum.photos/seed/25_1/900/900', 'https://picsum.photos/seed/25_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(74, 25, 'https://picsum.photos/seed/25_2/900/900', 'https://picsum.photos/seed/25_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(75, 25, 'https://picsum.photos/seed/25_3/900/900', 'https://picsum.photos/seed/25_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(76, 26, 'https://picsum.photos/seed/26_1/900/900', 'https://picsum.photos/seed/26_1/900/900', 1, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(77, 26, 'https://picsum.photos/seed/26_2/900/900', 'https://picsum.photos/seed/26_2/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(78, 26, 'https://picsum.photos/seed/26_3/900/900', 'https://picsum.photos/seed/26_3/900/900', 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `rating` tinyint UNSIGNED NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(1, '019da96b-190e-72ca-b042-a86ba2c4938f', 1, 3, 'Consequatur nobis maxime eos quis a velit accusantium eos tempora.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(2, '019da96b-1ca6-72cf-be0c-04db858da4ab', 1, 5, 'Tempora rerum iste tenetur ipsum numquam excepturi vero aut qui nisi veritatis ipsa quis dolor veritatis.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(3, '019da96b-1568-70dc-a298-97eb551b3cab', 2, 4, 'Et officiis doloremque ut velit qui dolorem aut omnis itaque suscipit nesciunt.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(4, '019da96b-1ca6-72cf-be0c-04db858da4ab', 3, 5, 'Vero pariatur molestiae est et non nihil dignissimos laborum quod expedita molestias aperiam voluptas laborum cupiditate.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(5, '019da96b-1568-70dc-a298-97eb551b3cab', 4, 3, 'Rerum iste perferendis molestiae velit ut itaque delectus necessitatibus aut necessitatibus tenetur quaerat pariatur suscipit accusantium.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(6, '019da96b-13b5-70b3-937c-851bbcedd357', 4, 5, 'Recusandae exercitationem deleniti vel illum aspernatur eos velit reprehenderit deleniti tempora quia repellat aut provident cumque.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(7, '019da96b-1ca6-72cf-be0c-04db858da4ab', 4, 4, 'Vero eos optio alias ut sunt saepe et quis aut labore adipisci autem quia ipsum ut et.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(8, '019da96b-0adb-72a5-ab39-a74b473b66ff', 4, 4, 'Et autem aliquam quis natus ut totam ea natus.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(9, '019da96b-13b5-70b3-937c-851bbcedd357', 5, 4, 'Et placeat ipsum totam accusamus natus officia cum rem ut quis qui libero.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(10, '019da96b-0adb-72a5-ab39-a74b473b66ff', 5, 3, 'Temporibus sit quaerat dignissimos dolorem ullam est fuga recusandae id laborum non quae.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(11, '019da96b-1210-724e-8353-db2529099f65', 5, 5, 'Eum qui velit aperiam voluptatem laboriosam dolores vel quam alias beatae.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(12, '019da96b-0ce0-701d-97e0-2bf4d98ce6bb', 6, 3, 'In non asperiores nisi necessitatibus cum sed animi ullam sint expedita.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(13, '019da96b-0e91-70b3-9e0d-ff2efbe12033', 7, 4, 'Quod reiciendis est est eos possimus itaque totam distinctio.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(14, '019da96b-1778-7212-b34b-585fa4f60d50', 7, 5, 'Fugit voluptas soluta non animi odio molestiae quia natus.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(15, '019da96b-0ce0-701d-97e0-2bf4d98ce6bb', 7, 5, 'Eos ducimus suscipit facilis dolorem assumenda dolorem quis.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(16, '019da96b-1568-70dc-a298-97eb551b3cab', 7, 3, 'Sed error soluta et omnis consectetur eos sed quia ex eius recusandae expedita.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(17, '019da96b-0ce0-701d-97e0-2bf4d98ce6bb', 8, 4, 'Explicabo ea voluptate aut consequatur et natus non ut sint aperiam cupiditate culpa aut ut.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(18, '019da96b-1aaa-7085-96a9-b2c9e7a9e0a8', 8, 4, 'Tempore et odio sint voluptatem quaerat architecto rerum alias dicta.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(19, '019da96b-1568-70dc-a298-97eb551b3cab', 8, 3, 'Omnis dolore repellat reprehenderit laborum cupiditate architecto quisquam perspiciatis officiis sit et sunt rerum.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(20, '019da96b-102c-703f-8a73-e5614ea6f75f', 9, 5, 'Nulla provident architecto voluptas aliquam explicabo esse provident aperiam reprehenderit ut non ut vel.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(21, '019da96b-08e3-7005-9c1b-8a17feb799ac', 10, 5, 'Sed omnis asperiores id dolor minima pariatur et voluptatem molestiae modi inventore accusantium.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(22, '019da96b-13b5-70b3-937c-851bbcedd357', 10, 5, 'Minus voluptas soluta sint voluptatem voluptas illum odit velit voluptas.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(23, '019da96b-1210-724e-8353-db2529099f65', 10, 5, 'Quia voluptatibus eligendi quo ad est culpa et optio et quia labore ratione earum.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(24, '019da96b-0adb-72a5-ab39-a74b473b66ff', 10, 4, 'Sint quaerat quibusdam aut est porro et voluptas dolores aut impedit molestiae.', '2026-04-19 22:44:22', '2026-04-19 22:44:22'),
(25, '019da96b-1778-7212-b34b-585fa4f60d50', 11, 3, 'Non laborum ut quia sit rerum quae iusto quas nobis quaerat voluptatum.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(26, '019da96b-0adb-72a5-ab39-a74b473b66ff', 12, 4, 'Nobis sunt delectus dolor cumque placeat dolor fugiat ea qui facere adipisci consectetur voluptas et ut.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(27, '019da96b-1210-724e-8353-db2529099f65', 12, 5, 'Ut rerum commodi in esse accusamus illo officia enim.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(28, '019da96b-190e-72ca-b042-a86ba2c4938f', 13, 3, 'Eaque quia reprehenderit quidem et consequuntur velit quidem sint sed.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(29, '019da96b-190e-72ca-b042-a86ba2c4938f', 13, 5, 'Quisquam in illum eum deserunt necessitatibus id deserunt ducimus nostrum voluptates aut incidunt consectetur.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(30, '019da96b-1568-70dc-a298-97eb551b3cab', 13, 5, 'Voluptas non iusto ut sit dolor dolorem alias quidem id veritatis sapiente et quis accusantium.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(31, '019da96b-08e3-7005-9c1b-8a17feb799ac', 13, 3, 'Non exercitationem asperiores distinctio facere repudiandae nulla eligendi.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(32, '019da96b-13b5-70b3-937c-851bbcedd357', 14, 3, 'Vitae et modi quia recusandae sunt in vel error quasi voluptas et quis iusto quae.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(33, '019da96b-1210-724e-8353-db2529099f65', 14, 4, 'Eos voluptates commodi eveniet aut molestias quia amet labore doloribus corrupti exercitationem.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(34, '019da96b-1ca6-72cf-be0c-04db858da4ab', 15, 4, 'Perferendis qui reiciendis porro officia quia et et.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(35, '019da96b-0ce0-701d-97e0-2bf4d98ce6bb', 15, 4, 'Dolores perferendis delectus eum quia expedita eveniet consequuntur.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(36, '019da96b-190e-72ca-b042-a86ba2c4938f', 15, 3, 'Ad iusto enim a ut iusto voluptatem amet tenetur velit.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(37, '019da96b-102c-703f-8a73-e5614ea6f75f', 16, 3, 'Maiores dolores et ipsum veritatis doloribus quo ut fuga quia tempore.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(38, '019da96b-0ce0-701d-97e0-2bf4d98ce6bb', 16, 3, 'Odit ut doloremque labore quisquam minus earum voluptas accusamus.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(39, '019da96b-1ca6-72cf-be0c-04db858da4ab', 16, 3, 'Dicta excepturi temporibus ut rerum ipsa labore nostrum est reiciendis facilis.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(40, '019da96b-1568-70dc-a298-97eb551b3cab', 16, 3, 'Corrupti facilis dolorum quod asperiores sunt et sint vitae illo id officia est.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(41, '019da96b-13b5-70b3-937c-851bbcedd357', 17, 4, 'Quis voluptas pariatur magni iste veniam possimus et vel deleniti fugiat vitae et iusto laboriosam sunt excepturi.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(42, '019da96b-1210-724e-8353-db2529099f65', 17, 3, 'Quia repudiandae eius exercitationem cumque molestias in porro aut praesentium consequatur atque illum quo distinctio.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(43, '019da96b-1aaa-7085-96a9-b2c9e7a9e0a8', 17, 5, 'Voluptatem illo quas dignissimos sed aut ad in itaque illum sint eos odio magni.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(44, '019da96b-102c-703f-8a73-e5614ea6f75f', 18, 5, 'Dolores nihil possimus ullam veniam maiores eum quis molestiae cum tenetur animi voluptatem nisi dignissimos dolorum occaecati.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(45, '019da96b-190e-72ca-b042-a86ba2c4938f', 18, 3, 'Deserunt facere eum culpa quidem eligendi placeat voluptates nihil natus cumque qui earum est.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(46, '019da96b-1210-724e-8353-db2529099f65', 18, 5, 'Natus consequatur perferendis doloribus eaque dolor debitis soluta voluptatem molestiae.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(47, '019da96b-13b5-70b3-937c-851bbcedd357', 19, 5, 'Quae facilis amet et omnis tempora tempora dolorem perspiciatis eos eaque ducimus quaerat magnam cupiditate sed omnis.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(48, '019da96b-0ce0-701d-97e0-2bf4d98ce6bb', 19, 4, 'Eos corrupti nobis natus quibusdam minus eligendi nulla error voluptas dolor voluptate aut iure earum praesentium.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(49, '019da96b-0adb-72a5-ab39-a74b473b66ff', 19, 3, 'Quas fuga a iste omnis omnis libero ipsam quasi in optio consequatur sit.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(50, '019da96b-1568-70dc-a298-97eb551b3cab', 20, 5, 'Voluptates qui et maiores totam debitis sint nihil error sint quis.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(51, '019da96b-1210-724e-8353-db2529099f65', 20, 4, 'Aspernatur aut eos distinctio error nihil distinctio est.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(52, '019da96b-1568-70dc-a298-97eb551b3cab', 21, 3, 'Dolorem ipsum maxime quia molestiae consequatur sunt hic numquam nisi impedit harum.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(53, '019da96b-190e-72ca-b042-a86ba2c4938f', 21, 4, 'Asperiores qui consequatur facilis non voluptas qui labore laborum maxime aut aliquid voluptatem vero eum ducimus.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(54, '019da96b-1778-7212-b34b-585fa4f60d50', 21, 3, 'Id atque rerum eveniet qui modi voluptate et.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(55, '019da96b-1ca6-72cf-be0c-04db858da4ab', 21, 4, 'Soluta quidem autem quis facere aut iusto tempora nostrum autem aliquid quae velit qui quia commodi eaque.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(56, '019da96b-1778-7212-b34b-585fa4f60d50', 22, 5, 'Ut sunt et aut nulla nesciunt mollitia quia nesciunt omnis.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(57, '019da96b-0e91-70b3-9e0d-ff2efbe12033', 22, 5, 'Aliquam quis odio inventore a vero commodi aspernatur reiciendis quo aut qui sed.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(58, '019da96b-102c-703f-8a73-e5614ea6f75f', 23, 4, 'Voluptate rem fugit expedita nesciunt iusto ea consequatur.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(59, '019da96b-1210-724e-8353-db2529099f65', 23, 3, 'Laborum iste commodi aperiam esse omnis voluptatem sit ab sunt sapiente cumque.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(60, '019da96b-0adb-72a5-ab39-a74b473b66ff', 23, 5, 'Voluptatem voluptas est aliquid quae quibusdam quia sapiente mollitia tempora ratione placeat aut debitis.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(61, '019da96b-0ce0-701d-97e0-2bf4d98ce6bb', 24, 3, 'Quis fuga aliquam aliquid nisi tempore accusantium ad suscipit laudantium suscipit.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(62, '019da96b-0adb-72a5-ab39-a74b473b66ff', 24, 3, 'Eius sint minima qui qui asperiores culpa voluptates unde sapiente id impedit sequi vero.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(63, '019da96b-0ce0-701d-97e0-2bf4d98ce6bb', 25, 3, 'Omnis magnam voluptatem quis sapiente qui minima est cumque ducimus.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(64, '019da96b-0adb-72a5-ab39-a74b473b66ff', 25, 4, 'Vel debitis aut similique atque amet aut et omnis delectus.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(65, '019da96b-102c-703f-8a73-e5614ea6f75f', 25, 5, 'Vero adipisci recusandae aut et consequatur veritatis dignissimos.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(66, '019da96b-102c-703f-8a73-e5614ea6f75f', 26, 4, 'Qui eos ullam et ducimus rerum et illum et dicta eum.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(67, '019da96b-1aaa-7085-96a9-b2c9e7a9e0a8', 26, 4, 'Ipsum tenetur architecto aliquam ratione cumque quas eaque est incidunt.', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(68, '019da96b-1778-7212-b34b-585fa4f60d50', 26, 3, 'Laudantium similique deleniti amet error modi provident cum.', '2026-04-19 22:44:23', '2026-04-19 22:44:23');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'buyer', '2026-04-19 21:42:35', '2026-04-19 21:42:35'),
(2, 'seller', '2026-04-19 21:42:35', '2026-04-19 21:42:35'),
(3, 'admin', '2026-04-19 21:42:35', '2026-04-19 21:42:35'),
(4, 'courier', '2026-04-19 21:42:35', '2026-04-19 21:42:35'),
(5, 'sales', '2026-04-19 21:42:35', '2026-04-19 21:42:35');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('1f6tTR9OuTMcQZv76vr2JU1ocZrxf4JFOIp8Wodg', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJoY0hMR2ExT3ZzT0hGYlFUOGRjMW5CQTc1aHFQSzNWc2FNTlh2UEkwIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776670743),
('ev7s5JCNVWA5f8AxwpPF5ZXobCByfzw6e9EujaV1', NULL, '127.0.0.1', 'PostmanRuntime/7.37.3', 'eyJfdG9rZW4iOiJhNnlVTWxUUGl1TDdyMTlzYTFCcVNVZnBVQ2JqRGEycW5qUllXeWNvIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776664754),
('Scl8HU7PJqj8HlaJBQWAgNksUxuy9O0MyJrVaynN', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI2cFhLVFFtQ2wzOUVQbFdrZkFoaUptd2JtdXN5R0dOT0pwUmt1OXRSIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19', 1776670741);

-- --------------------------------------------------------

--
-- Table structure for table `stocks`
--

CREATE TABLE `stocks` (
  `product_id` bigint UNSIGNED NOT NULL,
  `quantity` bigint UNSIGNED NOT NULL DEFAULT '0',
  `reserved_quantity` bigint UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stocks`
--

INSERT INTO `stocks` (`product_id`, `quantity`, `reserved_quantity`, `created_at`, `updated_at`) VALUES
(1, 146, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(2, 70, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(3, 74, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(4, 124, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(5, 109, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(6, 106, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(7, 52, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(8, 113, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(9, 140, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(10, 46, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(11, 70, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(12, 20, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(13, 90, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(14, 138, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(15, 26, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(16, 116, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(17, 34, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(18, 29, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(19, 46, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(20, 48, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(21, 69, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(22, 140, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(23, 124, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(24, 54, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(25, 6, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(26, 106, 0, '2026-04-19 22:44:23', '2026-04-19 22:44:23');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` bigint UNSIGNED NOT NULL,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `product_id`, `type`, `quantity`, `reference_type`, `reference_id`, `created_at`, `updated_at`) VALUES
(1, 1, 'initial_stock', 146, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(2, 2, 'initial_stock', 70, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(3, 3, 'initial_stock', 74, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(4, 4, 'initial_stock', 124, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(5, 5, 'initial_stock', 109, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(6, 6, 'initial_stock', 106, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(7, 7, 'initial_stock', 52, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(8, 8, 'initial_stock', 113, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(9, 9, 'initial_stock', 140, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(10, 10, 'initial_stock', 46, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(11, 11, 'initial_stock', 70, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(12, 12, 'initial_stock', 20, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(13, 13, 'initial_stock', 90, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(14, 14, 'initial_stock', 138, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(15, 15, 'initial_stock', 26, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(16, 16, 'initial_stock', 116, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(17, 17, 'initial_stock', 34, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(18, 18, 'initial_stock', 29, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(19, 19, 'initial_stock', 46, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(20, 20, 'initial_stock', 48, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(21, 21, 'initial_stock', 69, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(22, 22, 'initial_stock', 140, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:23', '2026-04-19 22:44:23'),
(23, 23, 'initial_stock', 124, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:24', '2026-04-19 22:44:24'),
(24, 24, 'initial_stock', 54, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:24', '2026-04-19 22:44:24'),
(25, 25, 'initial_stock', 6, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:24', '2026-04-19 22:44:24'),
(26, 26, 'initial_stock', 106, 'seeder', 'marketplace-operational-seeder', '2026-04-19 22:44:24', '2026-04-19 22:44:24');

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stores`
--

INSERT INTO `stores` (`id`, `user_id`, `name`, `slug`, `description`, `logo`, `created_at`, `updated_at`) VALUES
(1, '019da96a-ff4a-7167-a188-05685e19921b', 'Seller 1 Store', 'seller-1-store', 'Curated marketplace store #1.', 'https://picsum.photos/seed/store1/300/300', '2026-04-19 22:44:14', '2026-04-19 22:44:14'),
(2, '019da96b-0108-7193-b0f7-63ddb08a9eea', 'Seller 2 Store', 'seller-2-store', 'Curated marketplace store #2.', 'https://picsum.photos/seed/store2/300/300', '2026-04-19 22:44:15', '2026-04-19 22:44:15'),
(3, '019da96b-0352-70b2-89ef-54a033feb736', 'Seller 3 Store', 'seller-3-store', 'Curated marketplace store #3.', 'https://picsum.photos/seed/store3/300/300', '2026-04-19 22:44:15', '2026-04-19 22:44:15'),
(4, '019da96b-0509-7381-9312-f18813450644', 'Seller 4 Store', 'seller-4-store', 'Curated marketplace store #4.', 'https://picsum.photos/seed/store4/300/300', '2026-04-19 22:44:16', '2026-04-19 22:44:16'),
(5, '019da96b-06b7-70e0-adfb-035d02015b79', 'Seller 5 Store', 'seller-5-store', 'Curated marketplace store #5.', 'https://picsum.photos/seed/store5/300/300', '2026-04-19 22:44:16', '2026-04-19 22:44:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `firebase_uid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('buyer','seller','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'buyer',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_email_verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firebase_uid`, `email`, `password`, `role`, `name`, `avatar`, `is_email_verified`, `created_at`, `updated_at`) VALUES
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
('019da96b-1ca6-72cf-be0c-04db858da4ab', '61aaf0d98e46ee0a56500eb66a24', 'buyer12@ukomp.test', '$2y$12$7Tn5lT222.UKxIcz/zlzROFjoU5bgFrT1w.VRz5gh3/N8KVKhB8XW', 'buyer', 'Buyer 12', NULL, 1, '2026-04-19 22:44:22', '2026-04-19 22:44:22');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addresses_user_id_index` (`user_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `carts_user_id_unique` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cart_items_cart_id_product_id_unique` (`cart_id`,`product_id`),
  ADD KEY `cart_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`);

--
-- Indexes for table `catalog_groups`
--
ALTER TABLE `catalog_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `catalog_groups_slug_unique` (`slug`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_buyer_id_status_index` (`buyer_id`,`status`),
  ADD KEY `orders_seller_id_status_index` (`seller_id`,`status`),
  ADD KEY `orders_user_id_status_index` (`user_id`,`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payments_order_id_unique` (`order_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD KEY `products_seller_id_status_index` (`seller_id`,`status`),
  ADD KEY `products_store_id_foreign` (`store_id`),
  ADD KEY `products_category_id_foreign` (`category_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD UNIQUE KEY `product_categories_product_id_category_id_unique` (`product_id`,`category_id`),
  ADD KEY `product_categories_category_id_foreign` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_is_primary_index` (`product_id`,`is_primary`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_user_id_foreign` (`user_id`),
  ADD KEY `reviews_product_id_rating_index` (`product_id`,`rating`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stocks`
--
ALTER TABLE `stocks`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stock_movements_product_id_type_index` (`product_id`,`type`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stores_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `stores_slug_unique` (`slug`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_firebase_uid_unique` (`firebase_uid`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_roles_user_id_role_id_unique` (`user_id`,`role_id`),
  ADD KEY `user_roles_role_id_foreign` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `catalog_groups`
--
ALTER TABLE `catalog_groups`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_buyer_id_foreign` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_seller_id_foreign` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_categories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stocks`
--
ALTER TABLE `stocks`
  ADD CONSTRAINT `stocks_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
