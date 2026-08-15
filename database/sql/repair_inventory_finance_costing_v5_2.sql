SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `financial_payment_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `financial_transaction_id` bigint unsigned NOT NULL,
  `store_id` bigint unsigned DEFAULT NULL,
  `recorded_by` char(36) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'manual',
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text,
  `paid_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `financial_payment_histories_financial_transaction_id_foreign` (`financial_transaction_id`),
  KEY `financial_payment_histories_store_id_foreign` (`store_id`),
  KEY `financial_payment_histories_recorded_by_index` (`recorded_by`),
  KEY `financial_payment_histories_reference_number_index` (`reference_number`),
  KEY `financial_payment_histories_transaction_paid_index` (`financial_transaction_id`,`paid_at`),
  CONSTRAINT `financial_payment_histories_financial_transaction_id_foreign` FOREIGN KEY (`financial_transaction_id`) REFERENCES `financial_transactions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `financial_payment_histories_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `raw_materials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `code` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `unit` varchar(50) NOT NULL DEFAULT 'pcs',
  `stock` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `minimum_stock` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `average_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `raw_materials_store_id_code_unique` (`store_id`,`code`),
  KEY `raw_materials_is_active_index` (`is_active`),
  KEY `raw_materials_store_id_name_index` (`store_id`,`name`),
  CONSTRAINT `raw_materials_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `raw_material_stock_movements` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `raw_material_id` bigint unsigned NOT NULL,
  `type` varchar(30) NOT NULL,
  `quantity_delta` decimal(18,4) NOT NULL,
  `balance_after` decimal(18,4) NOT NULL,
  `unit_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `total_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `reference_type` varchar(50) NOT NULL DEFAULT 'manual',
  `reference_number` varchar(100) DEFAULT NULL,
  `notes` text,
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `raw_material_stock_movements_store_id_foreign` (`store_id`),
  KEY `raw_material_stock_movements_raw_material_id_foreign` (`raw_material_id`),
  KEY `raw_material_stock_movements_reference_number_index` (`reference_number`),
  KEY `raw_material_movement_lookup` (`store_id`,`raw_material_id`,`occurred_at`),
  CONSTRAINT `raw_material_stock_movements_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `raw_material_stock_movements_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_costings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `store_id` bigint unsigned NOT NULL,
  `material_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `labor_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `overhead_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `other_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `hpp` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `margin_percent` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `suggested_price` decimal(18,2) NOT NULL DEFAULT '0.00',
  `selling_price` decimal(18,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_costings_product_id_unique` (`product_id`),
  KEY `product_costings_store_id_foreign` (`store_id`),
  KEY `product_costings_store_id_product_id_index` (`store_id`,`product_id`),
  CONSTRAINT `product_costings_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_costings_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_materials` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `raw_material_id` bigint unsigned NOT NULL,
  `quantity` decimal(18,4) NOT NULL,
  `unit_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `total_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_materials_product_id_raw_material_id_unique` (`product_id`,`raw_material_id`),
  KEY `product_materials_raw_material_id_foreign` (`raw_material_id`),
  CONSTRAINT `product_materials_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_materials_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
