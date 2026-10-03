-- ============================================================
-- Migration 027 — v1.31.0
-- Coupon Architecture Enhancement: Campaign Metadata, Scoping & Free Shipping
-- Adds title, max_uses_per_customer, category_id, and free_shipping ENUM to coupons table.
-- ============================================================

SET NAMES utf8mb4;

-- 1. Guarded column addition for title in coupons
SET @col_title_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'coupons' 
      AND COLUMN_NAME = 'title'
);

SET @sql_title = IF(@col_title_exists = 0, 
    'ALTER TABLE coupons ADD COLUMN title VARCHAR(150) DEFAULT NULL AFTER code', 
    'SELECT 1'
);
PREPARE stmt_t FROM @sql_title; 
EXECUTE stmt_t; 
DEALLOCATE PREPARE stmt_t;

-- 2. Guarded column addition for max_uses_per_customer in coupons
SET @col_per_user_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'coupons' 
      AND COLUMN_NAME = 'max_uses_per_customer'
);

SET @sql_per_user = IF(@col_per_user_exists = 0, 
    'ALTER TABLE coupons ADD COLUMN max_uses_per_customer INT NOT NULL DEFAULT 1 AFTER max_uses', 
    'SELECT 1'
);
PREPARE stmt_u FROM @sql_per_user; 
EXECUTE stmt_u; 
DEALLOCATE PREPARE stmt_u;

-- 3. Guarded column addition for category_id in coupons
SET @col_cat_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'coupons' 
      AND COLUMN_NAME = 'category_id'
);

SET @sql_cat = IF(@col_cat_exists = 0, 
    'ALTER TABLE coupons ADD COLUMN category_id INT UNSIGNED DEFAULT NULL AFTER max_uses_per_customer', 
    'SELECT 1'
);
PREPARE stmt_cat FROM @sql_cat; 
EXECUTE stmt_cat; 
DEALLOCATE PREPARE stmt_cat;

-- 4. Guarded ENUM alteration to include free_shipping
-- Note: MySQL ALTER TABLE MODIFY COLUMN preserves existing data.
ALTER TABLE coupons MODIFY COLUMN type ENUM('percent','fixed','free_shipping') NOT NULL DEFAULT 'percent';

-- 5. Guarded index on category_id in coupons
SET @idx_cat_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'coupons' 
      AND INDEX_NAME = 'idx_coupon_category'
);

SET @sql_idx_cat = IF(@idx_cat_exists = 0,
    'CREATE INDEX idx_coupon_category ON coupons (category_id)',
    'SELECT 1'
);
PREPARE stmt_idx_cat FROM @sql_idx_cat;
EXECUTE stmt_idx_cat;
DEALLOCATE PREPARE stmt_idx_cat;

-- End of Migration 027
