-- ============================================================
-- Migration 031 — v1.41.0
-- Google Merchant Center & Schema.org Product Entity Enrichment
-- Adds barcode (GTIN), mpn, brand_name, gender, pattern, cuff_height to products table.
-- Compatible with MySQL 8.x, DirectAdmin phpMyAdmin, and MariaDB.
-- ============================================================

SET NAMES utf8mb4;

-- 1. barcode (GTIN / EAN-13 / UPC barcode)
SET @col_barcode = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'barcode'
);
SET @sql_barcode = IF(@col_barcode = 0, 
    'ALTER TABLE products ADD COLUMN barcode VARCHAR(32) DEFAULT NULL AFTER sku', 
    'SELECT 1'
);
PREPARE stmt_barcode FROM @sql_barcode; EXECUTE stmt_barcode; DEALLOCATE PREPARE stmt_barcode;

-- 2. mpn (Manufacturer Part Number)
SET @col_mpn = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'mpn'
);
SET @sql_mpn = IF(@col_mpn = 0, 
    'ALTER TABLE products ADD COLUMN mpn VARCHAR(64) DEFAULT NULL AFTER barcode', 
    'SELECT 1'
);
PREPARE stmt_mpn FROM @sql_mpn; EXECUTE stmt_mpn; DEALLOCATE PREPARE stmt_mpn;

-- 3. brand_name (Distinct workshop or sub-brand)
SET @col_brand = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'brand_name'
);
SET @sql_brand = IF(@col_brand = 0, 
    'ALTER TABLE products ADD COLUMN brand_name VARCHAR(120) DEFAULT NULL AFTER mpn', 
    'SELECT 1'
);
PREPARE stmt_brand FROM @sql_brand; EXECUTE stmt_brand; DEALLOCATE PREPARE stmt_brand;

-- 4. gender (Target demographic)
SET @col_gender = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'gender'
);
SET @sql_gender = IF(@col_gender = 0, 
    "ALTER TABLE products ADD COLUMN gender ENUM('unisex', 'men', 'women', 'kids') NOT NULL DEFAULT 'unisex' AFTER brand_name", 
    'SELECT 1'
);
PREPARE stmt_gender FROM @sql_gender; EXECUTE stmt_gender; DEALLOCATE PREPARE stmt_gender;

-- 5. pattern (Visual pattern/style)
SET @col_pattern = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'pattern'
);
SET @sql_pattern = IF(@col_pattern = 0, 
    "ALTER TABLE products ADD COLUMN pattern ENUM('solid', 'striped', 'graphic', 'novelty', 'geometric', 'floral', 'plaid') NOT NULL DEFAULT 'solid' AFTER gender", 
    'SELECT 1'
);
PREPARE stmt_pattern FROM @sql_pattern; EXECUTE stmt_pattern; DEALLOCATE PREPARE stmt_pattern;

-- 6. cuff_height (Sock height categorization)
SET @col_cuff = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'cuff_height'
);
SET @sql_cuff = IF(@col_cuff = 0, 
    "ALTER TABLE products ADD COLUMN cuff_height ENUM('crew', 'ankle', 'no-show', 'knee-high', 'quarter', 'tights') NOT NULL DEFAULT 'crew' AFTER pattern", 
    'SELECT 1'
);
PREPARE stmt_cuff FROM @sql_cuff; EXECUTE stmt_cuff; DEALLOCATE PREPARE stmt_cuff;

-- 7. Composite Index for filtering
SET @idx_entities = (
    SELECT COUNT(*) FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND INDEX_NAME = 'idx_product_entities'
);
SET @sql_idx_entities = IF(@idx_entities = 0, 
    'ALTER TABLE products ADD INDEX idx_product_entities (gender, cuff_height, pattern)', 
    'SELECT 1'
);
PREPARE stmt_idx_entities FROM @sql_idx_entities; EXECUTE stmt_idx_entities; DEALLOCATE PREPARE stmt_idx_entities;
