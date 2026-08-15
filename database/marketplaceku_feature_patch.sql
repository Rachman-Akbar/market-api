CREATE TABLE IF NOT EXISTS financial_payment_histories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  financial_transaction_id BIGINT UNSIGNED NOT NULL,
  store_id BIGINT UNSIGNED NULL,
  recorded_by CHAR(36) NULL,
  amount DECIMAL(15,2) NOT NULL,
  balance_before DECIMAL(15,2) NOT NULL,
  balance_after DECIMAL(15,2) NOT NULL,
  payment_method VARCHAR(50) NOT NULL DEFAULT 'manual',
  reference_number VARCHAR(100) NULL,
  notes TEXT NULL,
  paid_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX financial_payment_history_lookup (financial_transaction_id, paid_at)
);

CREATE TABLE IF NOT EXISTS raw_materials (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  store_id BIGINT UNSIGNED NOT NULL,
  code VARCHAR(100) NOT NULL,
  name VARCHAR(255) NOT NULL,
  unit VARCHAR(50) NOT NULL DEFAULT 'pcs',
  stock DECIMAL(18,4) NOT NULL DEFAULT 0,
  minimum_stock DECIMAL(18,4) NOT NULL DEFAULT 0,
  average_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  deleted_at TIMESTAMP NULL,
  UNIQUE KEY raw_material_store_code_unique (store_id, code),
  INDEX raw_material_store_name_index (store_id, name)
);

CREATE TABLE IF NOT EXISTS raw_material_stock_movements (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  store_id BIGINT UNSIGNED NOT NULL,
  raw_material_id BIGINT UNSIGNED NOT NULL,
  type VARCHAR(30) NOT NULL,
  quantity_delta DECIMAL(18,4) NOT NULL,
  balance_after DECIMAL(18,4) NOT NULL,
  unit_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  total_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  reference_type VARCHAR(50) NOT NULL DEFAULT 'manual',
  reference_number VARCHAR(100) NULL,
  notes TEXT NULL,
  occurred_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX raw_material_movement_lookup (store_id, raw_material_id, occurred_at)
);

CREATE TABLE IF NOT EXISTS product_costings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL UNIQUE,
  store_id BIGINT UNSIGNED NOT NULL,
  material_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  labor_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  overhead_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  other_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  hpp DECIMAL(18,4) NOT NULL DEFAULT 0,
  margin_percent DECIMAL(8,4) NOT NULL DEFAULT 0,
  suggested_price DECIMAL(18,2) NOT NULL DEFAULT 0,
  selling_price DECIMAL(18,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX product_costing_store_product_index (store_id, product_id)
);

CREATE TABLE IF NOT EXISTS product_materials (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  product_id BIGINT UNSIGNED NOT NULL,
  raw_material_id BIGINT UNSIGNED NOT NULL,
  quantity DECIMAL(18,4) NOT NULL,
  unit_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  total_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  UNIQUE KEY product_material_unique (product_id, raw_material_id)
);

CREATE TABLE IF NOT EXISTS raw_material_cost_histories (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  store_id BIGINT UNSIGNED NOT NULL,
  raw_material_id BIGINT UNSIGNED NOT NULL,
  raw_material_stock_movement_id BIGINT UNSIGNED NULL,
  old_average_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  new_average_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  change_amount DECIMAL(18,4) NOT NULL DEFAULT 0,
  change_percent DECIMAL(12,4) NOT NULL DEFAULT 0,
  direction VARCHAR(20) NOT NULL DEFAULT 'unchanged',
  reference_type VARCHAR(50) NOT NULL DEFAULT 'restock',
  reference_number VARCHAR(100) NULL,
  occurred_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX raw_material_cost_history_lookup (store_id, raw_material_id, occurred_at)
);

CREATE TABLE IF NOT EXISTS product_costing_impacts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  store_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  raw_material_id BIGINT UNSIGNED NULL,
  raw_material_cost_history_id BIGINT UNSIGNED NULL,
  old_material_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  new_material_cost DECIMAL(18,4) NOT NULL DEFAULT 0,
  old_hpp DECIMAL(18,4) NOT NULL DEFAULT 0,
  new_hpp DECIMAL(18,4) NOT NULL DEFAULT 0,
  hpp_change_amount DECIMAL(18,4) NOT NULL DEFAULT 0,
  hpp_change_percent DECIMAL(12,4) NOT NULL DEFAULT 0,
  old_suggested_price DECIMAL(18,2) NOT NULL DEFAULT 0,
  new_suggested_price DECIMAL(18,2) NOT NULL DEFAULT 0,
  trigger_type VARCHAR(50) NOT NULL DEFAULT 'raw_material_cost_change',
  occurred_at TIMESTAMP NOT NULL,
  created_at TIMESTAMP NULL,
  updated_at TIMESTAMP NULL,
  INDEX product_costing_impact_lookup (store_id, product_id, occurred_at)
);
