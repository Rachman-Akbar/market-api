SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `raw_material_cost_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `raw_material_id` bigint unsigned NOT NULL,
  `raw_material_stock_movement_id` bigint unsigned DEFAULT NULL,
  `old_average_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `new_average_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `change_amount` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `change_percent` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `direction` varchar(20) NOT NULL DEFAULT 'unchanged',
  `reference_type` varchar(50) NOT NULL DEFAULT 'restock',
  `reference_number` varchar(100) DEFAULT NULL,
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `raw_material_cost_histories_store_id_foreign` (`store_id`),
  KEY `raw_material_cost_histories_raw_material_id_foreign` (`raw_material_id`),
  KEY `raw_material_cost_histories_movement_foreign` (`raw_material_stock_movement_id`),
  KEY `raw_material_cost_history_lookup` (`store_id`,`raw_material_id`,`occurred_at`),
  KEY `raw_material_cost_histories_reference_number_index` (`reference_number`),
  CONSTRAINT `raw_material_cost_histories_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `raw_material_cost_histories_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`) ON DELETE CASCADE,
  CONSTRAINT `raw_material_cost_histories_movement_foreign` FOREIGN KEY (`raw_material_stock_movement_id`) REFERENCES `raw_material_stock_movements` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `product_costing_impacts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `store_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `raw_material_id` bigint unsigned DEFAULT NULL,
  `raw_material_cost_history_id` bigint unsigned DEFAULT NULL,
  `old_material_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `new_material_cost` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `old_hpp` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `new_hpp` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `hpp_change_amount` decimal(18,4) NOT NULL DEFAULT '0.0000',
  `hpp_change_percent` decimal(12,4) NOT NULL DEFAULT '0.0000',
  `old_suggested_price` decimal(18,2) NOT NULL DEFAULT '0.00',
  `new_suggested_price` decimal(18,2) NOT NULL DEFAULT '0.00',
  `trigger_type` varchar(50) NOT NULL DEFAULT 'raw_material_cost_change',
  `occurred_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_costing_impacts_store_id_foreign` (`store_id`),
  KEY `product_costing_impacts_product_id_foreign` (`product_id`),
  KEY `product_costing_impacts_raw_material_id_foreign` (`raw_material_id`),
  KEY `product_costing_impacts_history_id_foreign` (`raw_material_cost_history_id`),
  KEY `product_costing_impact_lookup` (`store_id`,`product_id`,`occurred_at`),
  CONSTRAINT `product_costing_impacts_store_id_foreign` FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_costing_impacts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `product_costing_impacts_raw_material_id_foreign` FOREIGN KEY (`raw_material_id`) REFERENCES `raw_materials` (`id`) ON DELETE SET NULL,
  CONSTRAINT `product_costing_impacts_history_id_foreign` FOREIGN KEY (`raw_material_cost_history_id`) REFERENCES `raw_material_cost_histories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
