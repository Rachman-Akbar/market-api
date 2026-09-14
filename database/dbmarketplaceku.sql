-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 12, 2026 at 01:12 AM
-- Server version: 12.3.3-MariaDB
-- PHP Version: 8.5.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `marketplaceku`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `country` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `city_or_regency` varchar(100) NOT NULL,
  `district` varchar(100) NOT NULL,
  `subdistrict` varchar(100) NOT NULL,
  `postal_code` varchar(20) NOT NULL,
  `full_address` text NOT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `label` varchar(100) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `komerce_destination_id` varchar(50) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_fee_configs`
--

CREATE TABLE `admin_fee_configs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(120) NOT NULL,
  `code` varchar(80) NOT NULL,
  `percentage` decimal(5,2) NOT NULL DEFAULT 0.00,
  `fixed_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `min_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `max_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) NOT NULL,
  `actor_id` char(36) DEFAULT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `module` varchar(80) NOT NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(180) NOT NULL,
  `message` text DEFAULT NULL,
  `reference_type` varchar(100) DEFAULT NULL,
  `reference_id` varchar(100) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `read_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cart_id` bigint(20) UNSIGNED NOT NULL,
  `product_variant_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `catalog_groups`
--

CREATE TABLE `catalog_groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `catalog_group_id` bigint(20) UNSIGNED NOT NULL,
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL,
  `parent_scope_id` bigint(20) UNSIGNED NOT NULL DEFAULT 0,
  `level` tinyint(3) UNSIGNED NOT NULL DEFAULT 1,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_visible_in_menu` tinyint(1) NOT NULL DEFAULT 1,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `full_slug` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `icon_url` varchar(255) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` char(36) DEFAULT NULL,
  `message_type` varchar(30) NOT NULL DEFAULT 'text',
  `message` text NOT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `edited_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_message_reads`
--

CREATE TABLE `chat_message_reads` (
  `message_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) NOT NULL,
  `read_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

CREATE TABLE `conversations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(30) NOT NULL DEFAULT 'direct',
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `subject` varchar(180) DEFAULT NULL,
  `target_role` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `conversation_participants`
--

CREATE TABLE `conversation_participants` (
  `conversation_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) NOT NULL,
  `last_read_at` timestamp NULL DEFAULT NULL,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `left_at` timestamp NULL DEFAULT NULL,
  `is_muted` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_payment_histories`
--

CREATE TABLE `financial_payment_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `financial_transaction_id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `recorded_by` char(36) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'manual',
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `paid_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `financial_transactions`
--

CREATE TABLE `financial_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` char(36) DEFAULT NULL,
  `reference_number` varchar(100) NOT NULL,
  `type` varchar(30) NOT NULL,
  `title` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(30) NOT NULL DEFAULT 'open',
  `due_date` date DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  `settled_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `game_sessions`
--

CREATE TABLE `game_sessions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) NOT NULL,
  `game_type` varchar(40) NOT NULL,
  `session_id` varchar(120) NOT NULL,
  `difficulty` varchar(30) DEFAULT NULL,
  `score` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `correct_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total_questions` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `duration_seconds` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `answers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`answers`)),
  `validation_status` varchar(30) NOT NULL DEFAULT 'accepted',
  `validation_reason` varchar(255) DEFAULT NULL,
  `coins_awarded` int(10) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_framework_tables', 1),
(2, '2026_07_23_000100_create_identity_tables', 1),
(3, '2026_07_23_000200_create_seller_tables', 1),
(4, '2026_07_23_000300_create_catalog_tables', 1),
(5, '2026_07_23_000400_create_marketing_tables', 1),
(6, '2026_07_23_000500_create_commerce_tables', 1),
(7, '2026_07_30_000001_add_catalog_product_query_indexes', 1),
(8, '2026_07_31_000002_add_product_table_header_indexes', 1),
(9, '2026_08_03_100000_add_advanced_marketplace_features', 1),
(10, '2026_08_08_120000_create_admin_notifications_table', 1),
(11, '2026_08_08_130000_rename_ticket_display_to_help', 1),
(12, '2026_08_13_130000_add_installment_inventory_costing', 1),
(13, '2026_08_13_155500_set_existing_users_testing_password', 1),
(14, '2026_08_13_162000_correct_existing_users_testing_password', 1),
(15, '2026_08_15_100000_ensure_inventory_finance_costing_tables', 1),
(16, '2026_08_15_130000_add_raw_material_cost_hpp_reports', 1),
(17, '2026_08_25_100000_create_commission_system_tables', 1),
(18, '2026_08_25_110000_create_schedules_table', 1),
(19, '2026_08_25_120000_add_performance_indexes', 1),
(20, '2026_08_28_100000_create_ppob_tables', 1),
(21, '2026_08_28_110000_create_game_sessions_table', 1),
(22, '2026_08_30_111456_add_payment_to_ppob_transactions_table', 1),
(23, '2026_08_31_000100_add_voucher_terms_columns', 1),
(24, '2026_08_31_000200_add_has_set_password_to_users_table', 1),
(25, '2026_08_31_000300_add_po_stock_to_product_variants', 1),
(26, '2026_09_01_000001_add_payment_columns_to_ppob_transactions', 1),
(27, '2026_09_01_000002_create_receipts_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `missions`
--

CREATE TABLE `missions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `voucher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(160) NOT NULL,
  `code` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type` varchar(80) NOT NULL,
  `target_value` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `conditions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`conditions`)),
  `starts_at` timestamp NOT NULL,
  `ends_at` timestamp NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mission_user_progress`
--

CREATE TABLE `mission_user_progress` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `mission_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) NOT NULL,
  `progress_value` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `status` varchar(30) NOT NULL DEFAULT 'in_progress',
  `completed_at` timestamp NULL DEFAULT NULL,
  `rewarded_at` timestamp NULL DEFAULT NULL,
  `reward_voucher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(100) NOT NULL,
  `order_type` varchar(30) NOT NULL DEFAULT 'normal',
  `preorder_release_at` timestamp NULL DEFAULT NULL,
  `booking_expires_at` timestamp NULL DEFAULT NULL,
  `received_at` timestamp NULL DEFAULT NULL,
  `user_id` char(36) NOT NULL,
  `voucher_id` bigint(20) UNSIGNED DEFAULT NULL,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `shipping_discount_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `admin_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `seller_net` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `payment_status` varchar(50) NOT NULL DEFAULT 'unpaid',
  `payment_method` varchar(100) DEFAULT NULL,
  `midtrans_snap_token` varchar(255) DEFAULT NULL,
  `shipping_address` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sub_order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) NOT NULL,
  `sku` varchar(100) NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `quantity` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(100) NOT NULL,
  `transaction_id` varchar(255) DEFAULT NULL,
  `payment_method` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `payload` longtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppob_finance_entries`
--

CREATE TABLE `ppob_finance_entries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `source_type` varchar(60) NOT NULL,
  `source_id` varchar(40) NOT NULL,
  `ppob_transaction_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_id` varchar(100) NOT NULL,
  `transaction_type` varchar(40) NOT NULL,
  `title` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'posted',
  `occurred_at` timestamp NOT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppob_inquiries`
--

CREATE TABLE `ppob_inquiries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_id` varchar(100) NOT NULL,
  `user_id` char(36) NOT NULL,
  `operator_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_code` varchar(80) DEFAULT NULL,
  `category` varchar(40) NOT NULL,
  `customer_id` varchar(120) NOT NULL,
  `tr_id` varchar(40) DEFAULT NULL,
  `customer_name` varchar(160) DEFAULT NULL,
  `customer_no` varchar(120) DEFAULT NULL,
  `bill_amount` decimal(15,2) DEFAULT NULL,
  `admin_charge` decimal(15,2) DEFAULT NULL,
  `admin_charge_message` varchar(255) DEFAULT NULL,
  `detail` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`detail`)),
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppob_operators`
--

CREATE TABLE `ppob_operators` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(120) NOT NULL,
  `category` varchar(40) NOT NULL,
  `brand` varchar(120) DEFAULT NULL,
  `operator_prefix` varchar(120) DEFAULT NULL,
  `provider_name` varchar(120) NOT NULL DEFAULT 'IAK',
  `icon_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppob_pricing_rules`
--

CREATE TABLE `ppob_pricing_rules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `level` varchar(20) NOT NULL,
  `category` varchar(40) DEFAULT NULL,
  `operator_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `margin_type` varchar(20) NOT NULL DEFAULT 'fixed',
  `margin_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `admin_fee_type` varchar(20) NOT NULL DEFAULT 'fixed',
  `admin_fee_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `commission_type` varchar(20) NOT NULL DEFAULT 'fixed',
  `commission_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `min_selling_price` decimal(15,2) DEFAULT NULL,
  `max_selling_price` decimal(15,2) DEFAULT NULL,
  `priority` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppob_products`
--

CREATE TABLE `ppob_products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `operator_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category` varchar(40) NOT NULL,
  `product_type` varchar(20) NOT NULL DEFAULT 'prepaid',
  `provider_product_code` varchar(80) NOT NULL,
  `name` varchar(160) NOT NULL,
  `brand` varchar(120) DEFAULT NULL,
  `nominal` varchar(80) DEFAULT NULL,
  `provider_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `admin_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `commission` decimal(15,2) NOT NULL DEFAULT 0.00,
  `margin` decimal(15,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `icon_url` varchar(500) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppob_transactions`
--

CREATE TABLE `ppob_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_id` varchar(100) NOT NULL,
  `user_id` char(36) NOT NULL,
  `operator_id` bigint(20) UNSIGNED DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `provider_product_code` varchar(80) DEFAULT NULL,
  `product_name` varchar(200) DEFAULT NULL,
  `category` varchar(40) NOT NULL,
  `product_type` varchar(20) NOT NULL DEFAULT 'prepaid',
  `customer_id` varchar(120) NOT NULL,
  `customer_name` varchar(160) DEFAULT NULL,
  `bill_amount` decimal(15,2) DEFAULT NULL,
  `provider_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `admin_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `commission` decimal(15,2) NOT NULL DEFAULT 0.00,
  `margin` decimal(15,2) NOT NULL DEFAULT 0.00,
  `revenue` decimal(15,2) NOT NULL DEFAULT 0.00,
  `net_profit` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(30) NOT NULL DEFAULT 'pending',
  `snap_token` varchar(500) DEFAULT NULL,
  `midtrans_snap_token` text DEFAULT NULL,
  `midtrans_transaction_id` varchar(100) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `provider_status` varchar(20) DEFAULT NULL,
  `provider_message` varchar(255) DEFAULT NULL,
  `tr_id` varchar(40) DEFAULT NULL,
  `sn` varchar(500) DEFAULT NULL,
  `pin` varchar(255) DEFAULT NULL,
  `provider_raw_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`provider_raw_response`)),
  `callback_signature` varchar(80) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ppob_transaction_logs`
--

CREATE TABLE `ppob_transaction_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ppob_transaction_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_id` varchar(100) NOT NULL,
  `action` varchar(40) NOT NULL,
  `direction` varchar(20) NOT NULL,
  `request_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_payload`)),
  `response_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_payload`)),
  `http_status` int(11) DEFAULT NULL,
  `provider_status` varchar(20) DEFAULT NULL,
  `provider_message` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `primary_category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'published',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_attributes`
--

CREATE TABLE `product_attributes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'select',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_attribute_values`
--

CREATE TABLE `product_attribute_values` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `attribute_id` bigint(20) UNSIGNED NOT NULL,
  `value` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_costings`
--

CREATE TABLE `product_costings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `material_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `labor_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `overhead_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `other_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `hpp` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `margin_percent` decimal(8,4) NOT NULL DEFAULT 0.0000,
  `suggested_price` decimal(18,2) NOT NULL DEFAULT 0.00,
  `selling_price` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_costing_impacts`
--

CREATE TABLE `product_costing_impacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `raw_material_id` bigint(20) UNSIGNED DEFAULT NULL,
  `raw_material_cost_history_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_material_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `new_material_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `old_hpp` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `new_hpp` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `hpp_change_amount` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `hpp_change_percent` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `old_suggested_price` decimal(18,2) NOT NULL DEFAULT 0.00,
  `new_suggested_price` decimal(18,2) NOT NULL DEFAULT 0.00,
  `trigger_type` varchar(50) NOT NULL DEFAULT 'raw_material_cost_change',
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `url` varchar(255) NOT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_materials`
--

CREATE TABLE `product_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `raw_material_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` decimal(18,4) NOT NULL,
  `unit_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `total_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `order_item_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL,
  `review` text DEFAULT NULL,
  `media` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`media`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `sku` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `po_stock` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variant_values`
--

CREATE TABLE `product_variant_values` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED NOT NULL,
  `attribute_id` bigint(20) UNSIGNED NOT NULL,
  `value` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `promotion_payment_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `mobile_image_url` varchar(255) DEFAULT NULL,
  `click_action` enum('none','product','category','url') NOT NULL DEFAULT 'none',
  `target_id` bigint(20) UNSIGNED DEFAULT NULL,
  `target_url` varchar(255) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` char(36) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotion_payments`
--

CREATE TABLE `promotion_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) NOT NULL,
  `payment_number` varchar(100) NOT NULL,
  `package_name` varchar(120) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `proof_url` varchar(255) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` char(36) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `raw_materials`
--

CREATE TABLE `raw_materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit` varchar(50) NOT NULL DEFAULT 'pcs',
  `stock` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `minimum_stock` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `average_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `raw_material_cost_histories`
--

CREATE TABLE `raw_material_cost_histories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `raw_material_id` bigint(20) UNSIGNED NOT NULL,
  `raw_material_stock_movement_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_average_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `new_average_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `change_amount` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `change_percent` decimal(12,4) NOT NULL DEFAULT 0.0000,
  `direction` varchar(20) NOT NULL DEFAULT 'unchanged',
  `reference_type` varchar(50) NOT NULL DEFAULT 'restock',
  `reference_number` varchar(100) DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `raw_material_stock_movements`
--

CREATE TABLE `raw_material_stock_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `raw_material_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(30) NOT NULL,
  `quantity_delta` decimal(18,4) NOT NULL,
  `balance_after` decimal(18,4) NOT NULL,
  `unit_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `total_cost` decimal(18,4) NOT NULL DEFAULT 0.0000,
  `reference_type` varchar(50) NOT NULL DEFAULT 'manual',
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `receipt_number` varchar(64) NOT NULL,
  `user_id` char(36) NOT NULL,
  `source_type` varchar(40) NOT NULL,
  `source_id` varchar(64) NOT NULL,
  `transaction_reference` varchar(100) DEFAULT NULL,
  `receipt_type` varchar(30) NOT NULL DEFAULT 'digital',
  `product_name` varchar(200) DEFAULT NULL,
  `category` varchar(40) DEFAULT NULL,
  `customer_id` varchar(120) DEFAULT NULL,
  `customer_name` varchar(160) DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `admin_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(30) NOT NULL DEFAULT 'pending',
  `transaction_status` varchar(30) NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `email_sent_at` timestamp NULL DEFAULT NULL,
  `email_status` varchar(30) NOT NULL DEFAULT 'none',
  `email_message_id` varchar(160) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) NOT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `priority` varchar(20) NOT NULL DEFAULT 'normal',
  `color` varchar(20) NOT NULL DEFAULT '#10B981',
  `date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_all_day` tinyint(1) NOT NULL DEFAULT 0,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `completed_at` timestamp NULL DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seller_settlements`
--

CREATE TABLE `seller_settlements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `sub_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `settlement_number` varchar(100) NOT NULL,
  `gross_amount` decimal(15,2) NOT NULL,
  `admin_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `shipping_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `net_amount` decimal(15,2) NOT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `settled_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seller_withdrawals`
--

CREATE TABLE `seller_withdrawals` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) NOT NULL,
  `withdrawal_number` varchar(100) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `method` varchar(50) NOT NULL,
  `bank_details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`bank_details`)),
  `status` varchar(30) NOT NULL DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `processed_by` char(36) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` char(36) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shipping_settings`
--

CREATE TABLE `shipping_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `store_latitude` decimal(10,8) NOT NULL,
  `store_longitude` decimal(11,8) NOT NULL,
  `free_shipping_max_distance` decimal(8,2) NOT NULL DEFAULT 0.00,
  `default_flat_rate` decimal(15,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `showcases`
--

CREATE TABLE `showcases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `slug` varchar(160) NOT NULL,
  `description` text DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `showcase_products`
--

CREATE TABLE `showcase_products` (
  `showcase_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `variant_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `movement_key` varchar(120) DEFAULT NULL,
  `type` varchar(30) NOT NULL,
  `quantity_delta` int(11) NOT NULL,
  `balance_after` int(10) UNSIGNED NOT NULL,
  `reference_type` varchar(80) DEFAULT NULL,
  `reference_id` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stores`
--

CREATE TABLE `stores` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` varchar(255) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `email` varchar(120) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `province` varchar(80) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('pending','approved','suspended') NOT NULL DEFAULT 'pending',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `logo` varchar(255) DEFAULT NULL,
  `banner_url` varchar(255) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `store_details`
--

CREATE TABLE `store_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `owner_name` varchar(120) DEFAULT NULL,
  `owner_phone` varchar(30) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `shipping_policy` text DEFAULT NULL,
  `return_policy` text DEFAULT NULL,
  `open_days` varchar(120) DEFAULT NULL,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `whatsapp_url` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(255) DEFAULT NULL,
  `tiktok_url` varchar(255) DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sub_orders`
--

CREATE TABLE `sub_orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED NOT NULL,
  `sub_order_number` varchar(100) NOT NULL,
  `total_items_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `shipping_cost` decimal(15,2) NOT NULL DEFAULT 0.00,
  `admin_fee` decimal(15,2) NOT NULL DEFAULT 0.00,
  `seller_net` decimal(15,2) NOT NULL DEFAULT 0.00,
  `courier` varchar(50) DEFAULT NULL,
  `service` varchar(100) DEFAULT NULL,
  `destination_id` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'pending',
  `tracking_number` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_number` varchar(100) NOT NULL,
  `user_id` char(36) NOT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `category` varchar(80) NOT NULL,
  `subject` varchar(180) NOT NULL,
  `description` text NOT NULL,
  `priority` varchar(30) NOT NULL DEFAULT 'normal',
  `status` varchar(30) NOT NULL DEFAULT 'open',
  `assigned_to` char(36) DEFAULT NULL,
  `last_replied_at` timestamp NULL DEFAULT NULL,
  `resolved_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `support_ticket_messages`
--

CREATE TABLE `support_ticket_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) NOT NULL,
  `message` text NOT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `is_internal` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` char(36) NOT NULL,
  `firebase_uid` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `has_set_password` tinyint(1) NOT NULL DEFAULT 0,
  `name` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `is_email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `banned_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` char(36) NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_vouchers`
--

CREATE TABLE `user_vouchers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` char(36) NOT NULL,
  `voucher_id` bigint(20) UNSIGNED NOT NULL,
  `source_type` varchar(80) DEFAULT NULL,
  `source_id` varchar(100) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'available',
  `claimed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `store_id` bigint(20) UNSIGNED DEFAULT NULL,
  `voucher_scope` enum('platform','store') NOT NULL DEFAULT 'platform',
  `code` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `discount_target` enum('product','shipping') NOT NULL DEFAULT 'product',
  `discount_type` enum('fixed','percentage') NOT NULL,
  `discount_value` decimal(15,2) NOT NULL,
  `min_spend` decimal(15,2) NOT NULL DEFAULT 0.00,
  `min_items` int(10) UNSIGNED DEFAULT NULL,
  `min_distinct_products` int(10) UNSIGNED DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `max_discount` decimal(15,2) DEFAULT NULL,
  `starts_at` timestamp NOT NULL,
  `ends_at` timestamp NOT NULL,
  `usage_limit` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `used_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` char(36) DEFAULT NULL,
  `updated_by` char(36) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ;

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` char(36) NOT NULL,
  `user_id` char(36) NOT NULL,
  `name` varchar(100) NOT NULL DEFAULT 'utama',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

CREATE TABLE `wishlist_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `wishlist_id` char(36) NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `addresses_store_id_unique` (`store_id`),
  ADD KEY `addresses_user_id_is_primary_index` (`user_id`,`is_primary`),
  ADD KEY `addresses_komerce_destination_id_index` (`komerce_destination_id`),
  ADD KEY `addresses_is_primary_index` (`is_primary`);

--
-- Indexes for table `admin_fee_configs`
--
ALTER TABLE `admin_fee_configs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `admin_fee_configs_code_unique` (`code`),
  ADD KEY `admin_fee_configs_created_by_foreign` (`created_by`),
  ADD KEY `admin_fee_configs_updated_by_foreign` (`updated_by`),
  ADD KEY `admin_fee_configs_category_active_index` (`category_id`,`is_active`),
  ADD KEY `admin_fee_configs_is_active_index` (`is_active`),
  ADD KEY `admin_fee_configs_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_notifications_actor_id_foreign` (`actor_id`),
  ADD KEY `admin_notifications_store_id_foreign` (`store_id`),
  ADD KEY `admin_notifications_user_unread_index` (`user_id`,`read_at`,`created_at`),
  ADD KEY `admin_notifications_module_unread_index` (`user_id`,`module`,`read_at`),
  ADD KEY `admin_notifications_reference_index` (`reference_type`,`reference_id`),
  ADD KEY `admin_notifications_module_index` (`module`),
  ADD KEY `admin_notifications_type_index` (`type`),
  ADD KEY `admin_notifications_read_at_index` (`read_at`),
  ADD KEY `admin_notifications_is_active_index` (`is_active`),
  ADD KEY `admin_notifications_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `banners_store_name_unique` (`store_id`,`name`),
  ADD KEY `banners_created_by_foreign` (`created_by`),
  ADD KEY `banners_updated_by_foreign` (`updated_by`),
  ADD KEY `banners_public_index` (`store_id`,`is_active`,`sort_order`,`deleted_at`),
  ADD KEY `banners_is_active_index` (`is_active`),
  ADD KEY `banners_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

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
  ADD UNIQUE KEY `cart_items_cart_id_product_variant_id_unique` (`cart_id`,`product_variant_id`),
  ADD KEY `cart_items_product_variant_id_foreign` (`product_variant_id`);

--
-- Indexes for table `catalog_groups`
--
ALTER TABLE `catalog_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `catalog_groups_name_unique` (`name`),
  ADD UNIQUE KEY `catalog_groups_slug_unique` (`slug`),
  ADD KEY `catalog_groups_created_by_foreign` (`created_by`),
  ADD KEY `catalog_groups_updated_by_foreign` (`updated_by`),
  ADD KEY `catalog_groups_status_index` (`is_active`,`deleted_at`),
  ADD KEY `catalog_groups_is_active_index` (`is_active`),
  ADD KEY `catalog_groups_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_parent_name_unique` (`catalog_group_id`,`parent_scope_id`,`name`),
  ADD UNIQUE KEY `categories_full_slug_unique` (`full_slug`),
  ADD KEY `categories_parent_id_foreign` (`parent_id`),
  ADD KEY `categories_created_by_foreign` (`created_by`),
  ADD KEY `categories_updated_by_foreign` (`updated_by`),
  ADD KEY `categories_catalog_group_id_parent_id_sort_order_index` (`catalog_group_id`,`parent_id`,`sort_order`),
  ADD KEY `categories_menu_status_index` (`is_active`,`is_visible_in_menu`,`deleted_at`),
  ADD KEY `categories_is_active_index` (`is_active`),
  ADD KEY `categories_is_visible_in_menu_index` (`is_visible_in_menu`),
  ADD KEY `categories_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `chat_messages_sender_id_foreign` (`sender_id`),
  ADD KEY `chat_messages_conversation_index` (`conversation_id`,`created_at`),
  ADD KEY `chat_messages_message_type_index` (`message_type`),
  ADD KEY `chat_messages_edited_at_index` (`edited_at`),
  ADD KEY `chat_messages_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `chat_message_reads`
--
ALTER TABLE `chat_message_reads`
  ADD PRIMARY KEY (`message_id`,`user_id`),
  ADD KEY `chat_message_reads_user_index` (`user_id`,`read_at`);

--
-- Indexes for table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `conversations_order_id_foreign` (`order_id`),
  ADD KEY `conversations_created_by_foreign` (`created_by`),
  ADD KEY `conversations_updated_by_foreign` (`updated_by`),
  ADD KEY `conversations_context_index` (`store_id`,`order_id`,`type`),
  ADD KEY `conversations_type_index` (`type`),
  ADD KEY `conversations_target_role_index` (`target_role`),
  ADD KEY `conversations_is_active_index` (`is_active`),
  ADD KEY `conversations_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD PRIMARY KEY (`conversation_id`,`user_id`),
  ADD KEY `conversation_participants_user_index` (`user_id`,`left_at`),
  ADD KEY `conversation_participants_last_read_at_index` (`last_read_at`),
  ADD KEY `conversation_participants_left_at_index` (`left_at`),
  ADD KEY `conversation_participants_is_muted_index` (`is_muted`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `financial_payment_histories`
--
ALTER TABLE `financial_payment_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `financial_payment_histories_store_id_foreign` (`store_id`),
  ADD KEY `fph_txn_paid_index` (`financial_transaction_id`,`paid_at`),
  ADD KEY `financial_payment_histories_recorded_by_index` (`recorded_by`),
  ADD KEY `financial_payment_histories_reference_number_index` (`reference_number`);

--
-- Indexes for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `financial_transactions_reference_number_unique` (`reference_number`),
  ADD KEY `financial_transactions_order_id_foreign` (`order_id`),
  ADD KEY `financial_transactions_user_id_foreign` (`user_id`),
  ADD KEY `financial_transactions_created_by_foreign` (`created_by`),
  ADD KEY `financial_transactions_updated_by_foreign` (`updated_by`),
  ADD KEY `financial_transactions_scope_index` (`store_id`,`type`,`status`,`occurred_at`),
  ADD KEY `financial_transactions_type_index` (`type`),
  ADD KEY `financial_transactions_status_index` (`status`),
  ADD KEY `financial_transactions_due_date_index` (`due_date`),
  ADD KEY `financial_transactions_occurred_at_index` (`occurred_at`),
  ADD KEY `financial_transactions_settled_at_index` (`settled_at`),
  ADD KEY `financial_transactions_is_active_index` (`is_active`),
  ADD KEY `financial_transactions_deleted_at_index` (`deleted_at`),
  ADD KEY `financial_transactions_report_index` (`store_id`,`type`,`status`,`occurred_at`),
  ADD KEY `financial_transactions_aging_index` (`store_id`,`type`,`due_date`,`status`);

--
-- Indexes for table `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `game_sessions_user_type_session_unique` (`user_id`,`game_type`,`session_id`),
  ADD KEY `game_sessions_created_by_foreign` (`created_by`),
  ADD KEY `game_sessions_updated_by_foreign` (`updated_by`),
  ADD KEY `game_sessions_user_type_created_index` (`user_id`,`game_type`,`created_at`),
  ADD KEY `game_sessions_game_type_index` (`game_type`),
  ADD KEY `game_sessions_difficulty_index` (`difficulty`),
  ADD KEY `game_sessions_validation_status_index` (`validation_status`),
  ADD KEY `game_sessions_is_active_index` (`is_active`);

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
-- Indexes for table `missions`
--
ALTER TABLE `missions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `missions_code_unique` (`code`),
  ADD KEY `missions_voucher_id_foreign` (`voucher_id`),
  ADD KEY `missions_created_by_foreign` (`created_by`),
  ADD KEY `missions_updated_by_foreign` (`updated_by`),
  ADD KEY `missions_active_event_index` (`event_type`,`is_active`,`starts_at`,`ends_at`),
  ADD KEY `missions_event_type_index` (`event_type`),
  ADD KEY `missions_starts_at_index` (`starts_at`),
  ADD KEY `missions_ends_at_index` (`ends_at`),
  ADD KEY `missions_is_active_index` (`is_active`),
  ADD KEY `missions_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `mission_user_progress`
--
ALTER TABLE `mission_user_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mission_user_progress_unique` (`mission_id`,`user_id`),
  ADD KEY `mission_user_progress_reward_voucher_id_foreign` (`reward_voucher_id`),
  ADD KEY `mission_user_progress_user_index` (`user_id`,`status`),
  ADD KEY `mission_user_progress_status_index` (`status`),
  ADD KEY `mission_user_progress_completed_at_index` (`completed_at`),
  ADD KEY `mission_user_progress_rewarded_at_index` (`rewarded_at`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_number_unique` (`order_number`),
  ADD KEY `orders_voucher_id_foreign` (`voucher_id`),
  ADD KEY `orders_user_id_created_at_index` (`user_id`,`created_at`),
  ADD KEY `orders_status_index` (`status`),
  ADD KEY `orders_payment_status_index` (`payment_status`),
  ADD KEY `orders_order_type_index` (`order_type`),
  ADD KEY `orders_preorder_release_at_index` (`preorder_release_at`),
  ADD KEY `orders_booking_expires_at_index` (`booking_expires_at`),
  ADD KEY `orders_received_at_index` (`received_at`),
  ADD KEY `orders_user_status_created_index` (`user_id`,`status`,`created_at`),
  ADD KEY `orders_payment_status_created_index` (`payment_status`,`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_sub_order_id_foreign` (`sub_order_id`),
  ADD KEY `order_items_variant_id_foreign` (`variant_id`),
  ADD KEY `order_items_product_created_index` (`product_id`,`created_at`);

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
  ADD UNIQUE KEY `payments_transaction_id_unique` (`transaction_id`),
  ADD KEY `payments_order_number_foreign` (`order_number`),
  ADD KEY `payments_status_index` (`status`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`),
  ADD KEY `permissions_is_active_index` (`is_active`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `ppob_finance_entries`
--
ALTER TABLE `ppob_finance_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ppob_finance_entries_unique` (`source_type`,`source_id`,`transaction_type`),
  ADD KEY `ppob_finance_entries_ppob_transaction_id_foreign` (`ppob_transaction_id`),
  ADD KEY `ppob_finance_entries_created_by_foreign` (`created_by`),
  ADD KEY `ppob_finance_entries_updated_by_foreign` (`updated_by`),
  ADD KEY `ppob_finance_entries_source_index` (`source_type`,`source_id`),
  ADD KEY `ppob_finance_entries_source_type_index` (`source_type`),
  ADD KEY `ppob_finance_entries_reference_id_index` (`reference_id`),
  ADD KEY `ppob_finance_entries_transaction_type_index` (`transaction_type`),
  ADD KEY `ppob_finance_entries_status_index` (`status`),
  ADD KEY `ppob_finance_entries_occurred_at_index` (`occurred_at`),
  ADD KEY `ppob_finance_entries_is_active_index` (`is_active`),
  ADD KEY `ppob_finance_entries_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `ppob_inquiries`
--
ALTER TABLE `ppob_inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ppob_inquiries_operator_id_foreign` (`operator_id`),
  ADD KEY `ppob_inquiries_user_status_index` (`user_id`,`status`),
  ADD KEY `ppob_inquiries_reference_id_index` (`reference_id`),
  ADD KEY `ppob_inquiries_category_index` (`category`),
  ADD KEY `ppob_inquiries_customer_id_index` (`customer_id`),
  ADD KEY `ppob_inquiries_tr_id_index` (`tr_id`),
  ADD KEY `ppob_inquiries_status_index` (`status`),
  ADD KEY `ppob_inquiries_expires_at_index` (`expires_at`),
  ADD KEY `ppob_inquiries_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `ppob_operators`
--
ALTER TABLE `ppob_operators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ppob_operators_slug_unique` (`slug`),
  ADD KEY `ppob_operators_created_by_foreign` (`created_by`),
  ADD KEY `ppob_operators_updated_by_foreign` (`updated_by`),
  ADD KEY `ppob_operators_category_active_index` (`category`,`is_active`),
  ADD KEY `ppob_operators_category_index` (`category`),
  ADD KEY `ppob_operators_is_active_index` (`is_active`),
  ADD KEY `ppob_operators_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `ppob_pricing_rules`
--
ALTER TABLE `ppob_pricing_rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ppob_pricing_rules_operator_id_foreign` (`operator_id`),
  ADD KEY `ppob_pricing_rules_product_id_foreign` (`product_id`),
  ADD KEY `ppob_pricing_rules_created_by_foreign` (`created_by`),
  ADD KEY `ppob_pricing_rules_updated_by_foreign` (`updated_by`),
  ADD KEY `ppob_pricing_rules_lookup_index` (`level`,`is_active`,`priority`),
  ADD KEY `ppob_pricing_rules_level_index` (`level`),
  ADD KEY `ppob_pricing_rules_category_index` (`category`),
  ADD KEY `ppob_pricing_rules_priority_index` (`priority`),
  ADD KEY `ppob_pricing_rules_is_active_index` (`is_active`),
  ADD KEY `ppob_pricing_rules_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `ppob_products`
--
ALTER TABLE `ppob_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ppob_products_provider_product_code_unique` (`provider_product_code`),
  ADD KEY `ppob_products_created_by_foreign` (`created_by`),
  ADD KEY `ppob_products_updated_by_foreign` (`updated_by`),
  ADD KEY `ppob_products_category_status_index` (`category`,`status`,`is_available`),
  ADD KEY `ppob_products_operator_category_index` (`operator_id`,`category`),
  ADD KEY `ppob_products_category_index` (`category`),
  ADD KEY `ppob_products_product_type_index` (`product_type`),
  ADD KEY `ppob_products_margin_index` (`margin`),
  ADD KEY `ppob_products_selling_price_index` (`selling_price`),
  ADD KEY `ppob_products_status_index` (`status`),
  ADD KEY `ppob_products_is_available_index` (`is_available`),
  ADD KEY `ppob_products_is_active_index` (`is_active`),
  ADD KEY `ppob_products_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `ppob_transactions`
--
ALTER TABLE `ppob_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ppob_transactions_reference_id_unique` (`reference_id`),
  ADD KEY `ppob_transactions_operator_id_foreign` (`operator_id`),
  ADD KEY `ppob_transactions_created_by_foreign` (`created_by`),
  ADD KEY `ppob_transactions_updated_by_foreign` (`updated_by`),
  ADD KEY `ppob_transactions_user_status_index` (`user_id`,`status`,`created_at`),
  ADD KEY `ppob_transactions_product_created_index` (`product_id`,`created_at`),
  ADD KEY `ppob_transactions_status_created_index` (`status`,`created_at`),
  ADD KEY `ppob_transactions_category_index` (`category`),
  ADD KEY `ppob_transactions_status_index` (`status`),
  ADD KEY `ppob_transactions_tr_id_index` (`tr_id`),
  ADD KEY `ppob_transactions_paid_at_index` (`paid_at`),
  ADD KEY `ppob_transactions_completed_at_index` (`completed_at`),
  ADD KEY `ppob_transactions_is_active_index` (`is_active`),
  ADD KEY `ppob_transactions_deleted_at_index` (`deleted_at`),
  ADD KEY `ppob_transactions_payment_status_index` (`payment_status`);

--
-- Indexes for table `ppob_transaction_logs`
--
ALTER TABLE `ppob_transaction_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ppob_transaction_logs_tx_action_index` (`ppob_transaction_id`,`action`),
  ADD KEY `ppob_transaction_logs_reference_id_index` (`reference_id`),
  ADD KEY `ppob_transaction_logs_action_index` (`action`),
  ADD KEY `ppob_transaction_logs_direction_index` (`direction`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_store_name_unique` (`store_id`,`name`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD KEY `products_created_by_foreign` (`created_by`),
  ADD KEY `products_updated_by_foreign` (`updated_by`),
  ADD KEY `products_public_feed_index` (`store_id`,`status`,`is_active`,`deleted_at`),
  ADD KEY `products_category_status_index` (`primary_category_id`,`status`,`is_active`),
  ADD KEY `products_brand_index` (`brand`),
  ADD KEY `products_status_index` (`status`),
  ADD KEY `products_is_active_index` (`is_active`),
  ADD KEY `products_deleted_at_index` (`deleted_at`),
  ADD KEY `products_public_feed_idx` (`status`,`is_active`,`created_at`,`id`),
  ADD KEY `products_store_feed_idx` (`store_id`,`status`,`is_active`,`created_at`,`id`),
  ADD KEY `products_category_feed_idx` (`primary_category_id`,`status`,`is_active`,`created_at`,`id`),
  ADD KEY `products_admin_name_sort_idx` (`name`,`id`),
  ADD KEY `products_seller_name_sort_idx` (`store_id`,`name`,`id`),
  ADD KEY `products_seller_active_idx` (`store_id`,`is_active`,`id`),
  ADD KEY `products_seller_status_idx` (`store_id`,`status`,`id`),
  ADD KEY `products_store_active_index` (`store_id`,`is_active`,`deleted_at`);

--
-- Indexes for table `product_attributes`
--
ALTER TABLE `product_attributes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_attributes_name_unique` (`name`),
  ADD UNIQUE KEY `product_attributes_slug_unique` (`slug`);

--
-- Indexes for table `product_attribute_values`
--
ALTER TABLE `product_attribute_values`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_attribute_values_product_id_attribute_id_unique` (`product_id`,`attribute_id`),
  ADD KEY `product_attribute_values_attribute_id_foreign` (`attribute_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`product_id`,`category_id`),
  ADD KEY `product_categories_category_id_product_id_index` (`category_id`,`product_id`),
  ADD KEY `product_categories_is_primary_index` (`is_primary`);

--
-- Indexes for table `product_costings`
--
ALTER TABLE `product_costings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_costings_product_id_unique` (`product_id`),
  ADD KEY `product_costings_store_id_product_id_index` (`store_id`,`product_id`);

--
-- Indexes for table `product_costing_impacts`
--
ALTER TABLE `product_costing_impacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_costing_impacts_product_id_foreign` (`product_id`),
  ADD KEY `product_costing_impacts_raw_material_id_foreign` (`raw_material_id`),
  ADD KEY `product_costing_impacts_raw_material_cost_history_id_foreign` (`raw_material_cost_history_id`),
  ADD KEY `product_costing_impact_lookup` (`store_id`,`product_id`,`occurred_at`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_product_id_is_primary_sort_order_index` (`product_id`,`is_primary`,`sort_order`),
  ADD KEY `product_images_is_primary_index` (`is_primary`);

--
-- Indexes for table `product_materials`
--
ALTER TABLE `product_materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_materials_product_id_raw_material_id_unique` (`product_id`,`raw_material_id`),
  ADD KEY `product_materials_raw_material_id_foreign` (`raw_material_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_reviews_order_item_id_unique` (`order_item_id`),
  ADD KEY `product_reviews_order_id_foreign` (`order_id`),
  ADD KEY `product_reviews_user_id_foreign` (`user_id`),
  ADD KEY `product_reviews_created_by_foreign` (`created_by`),
  ADD KEY `product_reviews_updated_by_foreign` (`updated_by`),
  ADD KEY `product_reviews_product_index` (`product_id`,`is_active`,`created_at`),
  ADD KEY `product_reviews_rating_index` (`rating`),
  ADD KEY `product_reviews_is_active_index` (`is_active`),
  ADD KEY `product_reviews_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_variants_store_sku_unique` (`store_id`,`sku`),
  ADD UNIQUE KEY `product_variants_product_name_unique` (`product_id`,`name`),
  ADD KEY `product_variants_product_id_is_default_index` (`product_id`,`is_default`),
  ADD KEY `product_variants_is_default_index` (`is_default`),
  ADD KEY `product_variants_summary_idx` (`product_id`,`is_default`,`id`),
  ADD KEY `product_variants_sku_search_idx` (`sku`),
  ADD KEY `product_variants_price_filter_idx` (`product_id`,`is_default`,`price`,`id`),
  ADD KEY `product_variants_stock_filter_idx` (`product_id`,`is_default`,`stock`,`id`),
  ADD KEY `product_variants_stock_index` (`product_id`,`stock`);

--
-- Indexes for table `product_variant_values`
--
ALTER TABLE `product_variant_values`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_variant_values_variant_id_attribute_id_unique` (`variant_id`,`attribute_id`),
  ADD KEY `product_variant_values_attribute_id_foreign` (`attribute_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `promotions_name_unique` (`name`),
  ADD KEY `promotions_approved_by_foreign` (`approved_by`),
  ADD KEY `promotions_created_by_foreign` (`created_by`),
  ADD KEY `promotions_updated_by_foreign` (`updated_by`),
  ADD KEY `promotions_public_index` (`approval_status`,`is_active`,`sort_order`,`deleted_at`),
  ADD KEY `promotions_store_index` (`store_id`,`approval_status`,`deleted_at`),
  ADD KEY `promotions_target_id_index` (`target_id`),
  ADD KEY `promotions_is_active_index` (`is_active`),
  ADD KEY `promotions_approval_status_index` (`approval_status`),
  ADD KEY `promotions_submitted_at_index` (`submitted_at`),
  ADD KEY `promotions_approved_at_index` (`approved_at`),
  ADD KEY `promotions_deleted_at_index` (`deleted_at`),
  ADD KEY `promotions_payment_approval_index` (`promotion_payment_id`,`approval_status`);

--
-- Indexes for table `promotion_payments`
--
ALTER TABLE `promotion_payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `promotion_payments_payment_number_unique` (`payment_number`),
  ADD KEY `promotion_payments_user_id_foreign` (`user_id`),
  ADD KEY `promotion_payments_reviewed_by_foreign` (`reviewed_by`),
  ADD KEY `promotion_payments_created_by_foreign` (`created_by`),
  ADD KEY `promotion_payments_updated_by_foreign` (`updated_by`),
  ADD KEY `promotion_payments_store_status_index` (`store_id`,`status`,`deleted_at`),
  ADD KEY `promotion_payments_status_index` (`status`),
  ADD KEY `promotion_payments_paid_at_index` (`paid_at`),
  ADD KEY `promotion_payments_reviewed_at_index` (`reviewed_at`),
  ADD KEY `promotion_payments_is_active_index` (`is_active`),
  ADD KEY `promotion_payments_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `raw_materials`
--
ALTER TABLE `raw_materials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `raw_materials_store_id_code_unique` (`store_id`,`code`),
  ADD KEY `raw_materials_store_id_name_index` (`store_id`,`name`),
  ADD KEY `raw_materials_is_active_index` (`is_active`);

--
-- Indexes for table `raw_material_cost_histories`
--
ALTER TABLE `raw_material_cost_histories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `raw_material_cost_histories_raw_material_id_foreign` (`raw_material_id`),
  ADD KEY `rm_cost_history_movement_fk` (`raw_material_stock_movement_id`),
  ADD KEY `raw_material_cost_history_lookup` (`store_id`,`raw_material_id`,`occurred_at`),
  ADD KEY `raw_material_cost_histories_reference_number_index` (`reference_number`);

--
-- Indexes for table `raw_material_stock_movements`
--
ALTER TABLE `raw_material_stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `raw_material_stock_movements_raw_material_id_foreign` (`raw_material_id`),
  ADD KEY `raw_material_movement_lookup` (`store_id`,`raw_material_id`,`occurred_at`),
  ADD KEY `raw_material_stock_movements_reference_number_index` (`reference_number`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipts_source_unique` (`source_type`,`source_id`),
  ADD UNIQUE KEY `receipts_receipt_number_unique` (`receipt_number`),
  ADD KEY `receipts_user_created_index` (`user_id`,`created_at`),
  ADD KEY `receipts_source_type_index` (`source_type`),
  ADD KEY `receipts_source_id_index` (`source_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`),
  ADD KEY `roles_created_by_foreign` (`created_by`),
  ADD KEY `roles_updated_by_foreign` (`updated_by`),
  ADD KEY `roles_status_index` (`is_active`,`deleted_at`),
  ADD KEY `roles_is_active_index` (`is_active`),
  ADD KEY `roles_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `role_permissions_permission_id_role_id_index` (`permission_id`,`role_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedules_created_by_foreign` (`created_by`),
  ADD KEY `schedules_updated_by_foreign` (`updated_by`),
  ADD KEY `schedules_user_date_index` (`user_id`,`date`,`is_active`),
  ADD KEY `schedules_store_date_type_index` (`store_id`,`date`,`type`),
  ADD KEY `schedules_type_index` (`type`),
  ADD KEY `schedules_priority_index` (`priority`),
  ADD KEY `schedules_date_index` (`date`),
  ADD KEY `schedules_is_active_index` (`is_active`),
  ADD KEY `schedules_deleted_at_index` (`deleted_at`),
  ADD KEY `schedules_user_completed_date_index` (`user_id`,`is_completed`,`date`);

--
-- Indexes for table `seller_settlements`
--
ALTER TABLE `seller_settlements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `seller_settlements_settlement_number_unique` (`settlement_number`),
  ADD KEY `seller_settlements_order_id_foreign` (`order_id`),
  ADD KEY `seller_settlements_sub_order_id_foreign` (`sub_order_id`),
  ADD KEY `seller_settlements_created_by_foreign` (`created_by`),
  ADD KEY `seller_settlements_updated_by_foreign` (`updated_by`),
  ADD KEY `seller_settlements_store_status_index` (`store_id`,`status`,`settled_at`),
  ADD KEY `seller_settlements_status_index` (`status`),
  ADD KEY `seller_settlements_settled_at_index` (`settled_at`),
  ADD KEY `seller_settlements_is_active_index` (`is_active`),
  ADD KEY `seller_settlements_deleted_at_index` (`deleted_at`),
  ADD KEY `seller_settlements_store_created_index` (`store_id`,`created_at`);

--
-- Indexes for table `seller_withdrawals`
--
ALTER TABLE `seller_withdrawals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `seller_withdrawals_withdrawal_number_unique` (`withdrawal_number`),
  ADD KEY `seller_withdrawals_user_id_foreign` (`user_id`),
  ADD KEY `seller_withdrawals_processed_by_foreign` (`processed_by`),
  ADD KEY `seller_withdrawals_created_by_foreign` (`created_by`),
  ADD KEY `seller_withdrawals_updated_by_foreign` (`updated_by`),
  ADD KEY `seller_withdrawals_store_status_index` (`store_id`,`status`,`created_at`),
  ADD KEY `seller_withdrawals_method_index` (`method`),
  ADD KEY `seller_withdrawals_status_index` (`status`),
  ADD KEY `seller_withdrawals_processed_at_index` (`processed_at`),
  ADD KEY `seller_withdrawals_is_active_index` (`is_active`),
  ADD KEY `seller_withdrawals_deleted_at_index` (`deleted_at`),
  ADD KEY `seller_withdrawals_store_created_index` (`store_id`,`created_at`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `shipping_settings`
--
ALTER TABLE `shipping_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `shipping_settings_store_id_unique` (`store_id`);

--
-- Indexes for table `showcases`
--
ALTER TABLE `showcases`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `showcases_store_slug_unique` (`store_id`,`slug`),
  ADD KEY `showcases_created_by_foreign` (`created_by`),
  ADD KEY `showcases_updated_by_foreign` (`updated_by`),
  ADD KEY `showcases_scope_index` (`store_id`,`is_active`,`sort_order`),
  ADD KEY `showcases_is_active_index` (`is_active`),
  ADD KEY `showcases_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `showcase_products`
--
ALTER TABLE `showcase_products`
  ADD PRIMARY KEY (`showcase_id`,`product_id`),
  ADD KEY `showcase_products_product_id_sort_order_index` (`product_id`,`sort_order`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stock_movements_order_item_key_unique` (`order_item_id`,`movement_key`),
  ADD KEY `stock_movements_product_id_foreign` (`product_id`),
  ADD KEY `stock_movements_variant_id_foreign` (`variant_id`),
  ADD KEY `stock_movements_order_id_foreign` (`order_id`),
  ADD KEY `stock_movements_created_by_foreign` (`created_by`),
  ADD KEY `stock_movements_updated_by_foreign` (`updated_by`),
  ADD KEY `stock_movements_scope_index` (`store_id`,`variant_id`,`occurred_at`),
  ADD KEY `stock_movements_type_index` (`type`),
  ADD KEY `stock_movements_occurred_at_index` (`occurred_at`),
  ADD KEY `stock_movements_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `stores_user_id_unique` (`user_id`),
  ADD UNIQUE KEY `stores_slug_unique` (`slug`),
  ADD KEY `stores_created_by_foreign` (`created_by`),
  ADD KEY `stores_updated_by_foreign` (`updated_by`),
  ADD KEY `stores_public_index` (`status`,`is_active`,`deleted_at`),
  ADD KEY `stores_email_index` (`email`),
  ADD KEY `stores_city_index` (`city`),
  ADD KEY `stores_province_index` (`province`),
  ADD KEY `stores_status_index` (`status`),
  ADD KEY `stores_is_active_index` (`is_active`),
  ADD KEY `stores_deleted_at_index` (`deleted_at`),
  ADD KEY `stores_public_catalog_idx` (`status`,`is_active`,`id`);

--
-- Indexes for table `store_details`
--
ALTER TABLE `store_details`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `store_details_store_id_unique` (`store_id`);

--
-- Indexes for table `sub_orders`
--
ALTER TABLE `sub_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sub_orders_sub_order_number_unique` (`sub_order_number`),
  ADD KEY `sub_orders_store_id_status_created_at_index` (`store_id`,`status`,`created_at`),
  ADD KEY `sub_orders_status_index` (`status`),
  ADD KEY `sub_orders_store_status_created_index` (`store_id`,`status`,`created_at`),
  ADD KEY `sub_orders_order_store_index` (`order_id`,`store_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `support_tickets_ticket_number_unique` (`ticket_number`),
  ADD KEY `support_tickets_store_id_foreign` (`store_id`),
  ADD KEY `support_tickets_order_id_foreign` (`order_id`),
  ADD KEY `support_tickets_assigned_to_foreign` (`assigned_to`),
  ADD KEY `support_tickets_created_by_foreign` (`created_by`),
  ADD KEY `support_tickets_updated_by_foreign` (`updated_by`),
  ADD KEY `support_tickets_user_status_index` (`user_id`,`status`,`created_at`),
  ADD KEY `support_tickets_category_index` (`category`),
  ADD KEY `support_tickets_priority_index` (`priority`),
  ADD KEY `support_tickets_status_index` (`status`),
  ADD KEY `support_tickets_last_replied_at_index` (`last_replied_at`),
  ADD KEY `support_tickets_resolved_at_index` (`resolved_at`),
  ADD KEY `support_tickets_is_active_index` (`is_active`),
  ADD KEY `support_tickets_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `support_ticket_messages`
--
ALTER TABLE `support_ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_ticket_messages_user_id_foreign` (`user_id`),
  ADD KEY `support_ticket_messages_ticket_index` (`ticket_id`,`created_at`),
  ADD KEY `support_ticket_messages_is_internal_index` (`is_internal`),
  ADD KEY `support_ticket_messages_read_at_index` (`read_at`),
  ADD KEY `support_ticket_messages_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_firebase_uid_unique` (`firebase_uid`),
  ADD KEY `users_access_status_index` (`is_active`,`banned_at`,`deleted_at`),
  ADD KEY `users_is_email_verified_index` (`is_email_verified`),
  ADD KEY `users_is_active_index` (`is_active`),
  ADD KEY `users_banned_at_index` (`banned_at`),
  ADD KEY `users_created_by_index` (`created_by`),
  ADD KEY `users_updated_by_index` (`updated_by`),
  ADD KEY `users_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `user_roles_role_id_user_id_index` (`role_id`,`user_id`);

--
-- Indexes for table `user_vouchers`
--
ALTER TABLE `user_vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_vouchers_source_unique` (`user_id`,`voucher_id`,`source_type`,`source_id`),
  ADD KEY `user_vouchers_voucher_id_foreign` (`voucher_id`),
  ADD KEY `user_vouchers_user_status_index` (`user_id`,`status`),
  ADD KEY `user_vouchers_source_type_index` (`source_type`),
  ADD KEY `user_vouchers_status_index` (`status`),
  ADD KEY `user_vouchers_used_at_index` (`used_at`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `vouchers_code_unique` (`code`),
  ADD UNIQUE KEY `vouchers_name_unique` (`name`),
  ADD KEY `vouchers_store_id_foreign` (`store_id`),
  ADD KEY `vouchers_created_by_foreign` (`created_by`),
  ADD KEY `vouchers_updated_by_foreign` (`updated_by`),
  ADD KEY `vouchers_scope_index` (`voucher_scope`,`store_id`,`is_active`,`deleted_at`),
  ADD KEY `vouchers_period_index` (`is_active`,`starts_at`,`ends_at`,`deleted_at`),
  ADD KEY `vouchers_voucher_scope_index` (`voucher_scope`),
  ADD KEY `vouchers_discount_target_index` (`discount_target`),
  ADD KEY `vouchers_starts_at_index` (`starts_at`),
  ADD KEY `vouchers_ends_at_index` (`ends_at`),
  ADD KEY `vouchers_is_active_index` (`is_active`),
  ADD KEY `vouchers_deleted_at_index` (`deleted_at`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlists_user_id_name_unique` (`user_id`,`name`);

--
-- Indexes for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlist_items_wishlist_id_product_id_unique` (`wishlist_id`,`product_id`),
  ADD KEY `wishlist_items_product_id_foreign` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_fee_configs`
--
ALTER TABLE `admin_fee_configs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `catalog_groups`
--
ALTER TABLE `catalog_groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_payment_histories`
--
ALTER TABLE `financial_payment_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `game_sessions`
--
ALTER TABLE `game_sessions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `missions`
--
ALTER TABLE `missions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mission_user_progress`
--
ALTER TABLE `mission_user_progress`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppob_finance_entries`
--
ALTER TABLE `ppob_finance_entries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppob_inquiries`
--
ALTER TABLE `ppob_inquiries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppob_operators`
--
ALTER TABLE `ppob_operators`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppob_pricing_rules`
--
ALTER TABLE `ppob_pricing_rules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppob_products`
--
ALTER TABLE `ppob_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppob_transactions`
--
ALTER TABLE `ppob_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ppob_transaction_logs`
--
ALTER TABLE `ppob_transaction_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_attributes`
--
ALTER TABLE `product_attributes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_attribute_values`
--
ALTER TABLE `product_attribute_values`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_costings`
--
ALTER TABLE `product_costings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_costing_impacts`
--
ALTER TABLE `product_costing_impacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_materials`
--
ALTER TABLE `product_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variant_values`
--
ALTER TABLE `product_variant_values`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotion_payments`
--
ALTER TABLE `promotion_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `raw_materials`
--
ALTER TABLE `raw_materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `raw_material_cost_histories`
--
ALTER TABLE `raw_material_cost_histories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `raw_material_stock_movements`
--
ALTER TABLE `raw_material_stock_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seller_settlements`
--
ALTER TABLE `seller_settlements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `seller_withdrawals`
--
ALTER TABLE `seller_withdrawals`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shipping_settings`
--
ALTER TABLE `shipping_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `showcases`
--
ALTER TABLE `showcases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stores`
--
ALTER TABLE `stores`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_details`
--
ALTER TABLE `store_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sub_orders`
--
ALTER TABLE `sub_orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `support_ticket_messages`
--
ALTER TABLE `support_ticket_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_vouchers`
--
ALTER TABLE `user_vouchers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `admin_fee_configs`
--
ALTER TABLE `admin_fee_configs`
  ADD CONSTRAINT `admin_fee_configs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `admin_fee_configs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `admin_fee_configs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD CONSTRAINT `admin_notifications_actor_id_foreign` FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `admin_notifications_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `admin_notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `banners`
--
ALTER TABLE `banners`
  ADD CONSTRAINT `banners_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `banners_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `banners_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `cart_items_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `catalog_groups`
--
ALTER TABLE `catalog_groups`
  ADD CONSTRAINT `catalog_groups_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `catalog_groups_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_catalog_group_id_foreign` FOREIGN KEY (`catalog_group_id`) REFERENCES `catalog_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `categories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD CONSTRAINT `chat_messages_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `chat_message_reads`
--
ALTER TABLE `chat_message_reads`
  ADD CONSTRAINT `chat_message_reads_message_id_foreign` FOREIGN KEY (`message_id`) REFERENCES `chat_messages` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `chat_message_reads_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `conversations_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `conversations_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `conversations_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD CONSTRAINT `conversation_participants_conversation_id_foreign` FOREIGN KEY (`conversation_id`) REFERENCES `conversations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `financial_payment_histories`
--
ALTER TABLE `financial_payment_histories`
  ADD CONSTRAINT `financial_payment_histories_financial_transaction_id_foreign` FOREIGN KEY (`financial_transaction_id`) REFERENCES `financial_transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `financial_payment_histories_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  ADD CONSTRAINT `financial_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_transactions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_transactions_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `financial_transactions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `financial_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `game_sessions`
--
ALTER TABLE `game_sessions`
  ADD CONSTRAINT `game_sessions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `game_sessions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `game_sessions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `missions`
--
ALTER TABLE `missions`
  ADD CONSTRAINT `missions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `missions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `missions_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `mission_user_progress`
--
ALTER TABLE `mission_user_progress`
  ADD CONSTRAINT `mission_user_progress_mission_id_foreign` FOREIGN KEY (`mission_id`) REFERENCES `missions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mission_user_progress_reward_voucher_id_foreign` FOREIGN KEY (`reward_voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mission_user_progress_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_sub_order_id_foreign` FOREIGN KEY (`sub_order_id`) REFERENCES `sub_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_number_foreign` FOREIGN KEY (`order_number`) REFERENCES `orders` (`order_number`) ON DELETE CASCADE;

--
-- Constraints for table `ppob_finance_entries`
--
ALTER TABLE `ppob_finance_entries`
  ADD CONSTRAINT `ppob_finance_entries_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppob_finance_entries_ppob_transaction_id_foreign` FOREIGN KEY (`ppob_transaction_id`) REFERENCES `ppob_transactions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppob_finance_entries_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ppob_inquiries`
--
ALTER TABLE `ppob_inquiries`
  ADD CONSTRAINT `ppob_inquiries_operator_id_foreign` FOREIGN KEY (`operator_id`) REFERENCES `ppob_operators` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppob_inquiries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ppob_operators`
--
ALTER TABLE `ppob_operators`
  ADD CONSTRAINT `ppob_operators_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppob_operators_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ppob_pricing_rules`
--
ALTER TABLE `ppob_pricing_rules`
  ADD CONSTRAINT `ppob_pricing_rules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppob_pricing_rules_operator_id_foreign` FOREIGN KEY (`operator_id`) REFERENCES `ppob_operators` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppob_pricing_rules_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `ppob_products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppob_pricing_rules_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ppob_products`
--
ALTER TABLE `ppob_products`
  ADD CONSTRAINT `ppob_products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppob_products_operator_id_foreign` FOREIGN KEY (`operator_id`) REFERENCES `ppob_operators` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppob_products_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ppob_transactions`
--
ALTER TABLE `ppob_transactions`
  ADD CONSTRAINT `ppob_transactions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppob_transactions_operator_id_foreign` FOREIGN KEY (`operator_id`) REFERENCES `ppob_operators` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppob_transactions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `ppob_products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppob_transactions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `ppob_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ppob_transaction_logs`
--
ALTER TABLE `ppob_transaction_logs`
  ADD CONSTRAINT `ppob_transaction_logs_ppob_transaction_id_foreign` FOREIGN KEY (`ppob_transaction_id`) REFERENCES `ppob_transactions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_primary_category_id_foreign` FOREIGN KEY (`primary_category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `products_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_attribute_values`
--
ALTER TABLE `product_attribute_values`
  ADD CONSTRAINT `product_attribute_values_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_attribute_values_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_categories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_costings`
--
ALTER TABLE `product_costings`
  ADD CONSTRAINT `product_costings_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_costings_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_costing_impacts`
--
ALTER TABLE `product_costing_impacts`
  ADD CONSTRAINT `product_costing_impacts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_costing_impacts_raw_material_cost_history_id_foreign` FOREIGN KEY (`raw_material_cost_history_id`) REFERENCES `raw_material_cost_histories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_costing_impacts_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_costing_impacts_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_materials`
--
ALTER TABLE `product_materials`
  ADD CONSTRAINT `product_materials_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_materials_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_reviews_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reviews_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variants_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variant_values`
--
ALTER TABLE `product_variant_values`
  ADD CONSTRAINT `product_variant_values_attribute_id_foreign` FOREIGN KEY (`attribute_id`) REFERENCES `product_attributes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variant_values_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `promotions`
--
ALTER TABLE `promotions`
  ADD CONSTRAINT `promotions_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `promotions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `promotions_promotion_payment_id_foreign` FOREIGN KEY (`promotion_payment_id`) REFERENCES `promotion_payments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `promotions_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promotions_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `promotion_payments`
--
ALTER TABLE `promotion_payments`
  ADD CONSTRAINT `promotion_payments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `promotion_payments_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `promotion_payments_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promotion_payments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `promotion_payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `raw_materials`
--
ALTER TABLE `raw_materials`
  ADD CONSTRAINT `raw_materials_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `raw_material_cost_histories`
--
ALTER TABLE `raw_material_cost_histories`
  ADD CONSTRAINT `raw_material_cost_histories_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `raw_material_cost_histories_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rm_cost_history_movement_fk` FOREIGN KEY (`raw_material_stock_movement_id`) REFERENCES `raw_material_stock_movements` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `raw_material_stock_movements`
--
ALTER TABLE `raw_material_stock_movements`
  ADD CONSTRAINT `raw_material_stock_movements_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `raw_material_stock_movements_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `receipts`
--
ALTER TABLE `receipts`
  ADD CONSTRAINT `receipts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `roles_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `schedules_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `schedules_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `schedules_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `schedules_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `seller_settlements`
--
ALTER TABLE `seller_settlements`
  ADD CONSTRAINT `seller_settlements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `seller_settlements_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seller_settlements_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seller_settlements_sub_order_id_foreign` FOREIGN KEY (`sub_order_id`) REFERENCES `sub_orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `seller_settlements_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `seller_withdrawals`
--
ALTER TABLE `seller_withdrawals`
  ADD CONSTRAINT `seller_withdrawals_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `seller_withdrawals_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `seller_withdrawals_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `seller_withdrawals_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `seller_withdrawals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shipping_settings`
--
ALTER TABLE `shipping_settings`
  ADD CONSTRAINT `shipping_settings_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `showcases`
--
ALTER TABLE `showcases`
  ADD CONSTRAINT `showcases_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `showcases_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `showcases_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `showcase_products`
--
ALTER TABLE `showcase_products`
  ADD CONSTRAINT `showcase_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `showcase_products_showcase_id_foreign` FOREIGN KEY (`showcase_id`) REFERENCES `showcases` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movements_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movements_order_item_id_foreign` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_movements_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_movements_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_movements_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stores_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stores_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `store_details`
--
ALTER TABLE `store_details`
  ADD CONSTRAINT `store_details_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sub_orders`
--
ALTER TABLE `sub_orders`
  ADD CONSTRAINT `sub_orders_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sub_orders_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`);

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `support_tickets_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `support_tickets_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `support_tickets_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `support_tickets_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_ticket_messages`
--
ALTER TABLE `support_ticket_messages`
  ADD CONSTRAINT `support_ticket_messages_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `support_tickets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_ticket_messages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_vouchers`
--
ALTER TABLE `user_vouchers`
  ADD CONSTRAINT `user_vouchers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_vouchers_voucher_id_foreign` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD CONSTRAINT `vouchers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `vouchers_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vouchers_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD CONSTRAINT `wishlist_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_items_wishlist_id_foreign` FOREIGN KEY (`wishlist_id`) REFERENCES `wishlists` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
