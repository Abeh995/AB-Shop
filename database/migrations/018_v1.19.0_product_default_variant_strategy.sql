-- ============================================================
-- Migration 018 — v1.19.0
-- Configurable default product variant strategy (global + per-product)
-- ============================================================

SET NAMES utf8mb4;

-- 1. Add use_global_variant_strategy to products table
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'use_global_variant_strategy'
);
SET @sql = IF(@col_exists = 0,
    "ALTER TABLE products ADD COLUMN use_global_variant_strategy TINYINT(1) NOT NULL DEFAULT 1 AFTER stock",
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Add is_default to product_variants table
SET @col_exists2 = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'product_variants' AND COLUMN_NAME = 'is_default'
);
SET @sql2 = IF(@col_exists2 = 0,
    "ALTER TABLE product_variants ADD COLUMN is_default TINYINT(1) NOT NULL DEFAULT 0 AFTER cost_price",
    'SELECT 1'
);
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

-- 3. Insert default store setting for global variant selection strategy
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('default_variant_strategy', 'highest_stock')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- End of Migration 018
