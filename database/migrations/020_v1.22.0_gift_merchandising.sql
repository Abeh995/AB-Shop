-- ============================================================
-- Migration 020 — v1.22.0
-- Merchandising, visual badges, smart cart thresholds and sort order for gift_items
-- ============================================================

SET NAMES utf8mb4;

-- 1. Add tagline to gift_items
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gift_items' AND COLUMN_NAME = 'tagline'
);
SET @sql = IF(@col_exists = 0,
    "ALTER TABLE gift_items ADD COLUMN tagline VARCHAR(190) NULL DEFAULT NULL AFTER name",
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Add badge_text to gift_items
SET @col_exists2 = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gift_items' AND COLUMN_NAME = 'badge_text'
);
SET @sql2 = IF(@col_exists2 = 0,
    "ALTER TABLE gift_items ADD COLUMN badge_text VARCHAR(50) NULL DEFAULT NULL AFTER tagline",
    'SELECT 1'
);
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

-- 3. Add min_cart_total to gift_items
SET @col_exists3 = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gift_items' AND COLUMN_NAME = 'min_cart_total'
);
SET @sql3 = IF(@col_exists3 = 0,
    "ALTER TABLE gift_items ADD COLUMN min_cart_total DECIMAL(12,0) NOT NULL DEFAULT 0 AFTER post_order_price",
    'SELECT 1'
);
PREPARE stmt3 FROM @sql3; EXECUTE stmt3; DEALLOCATE PREPARE stmt3;

-- 4. Add sort_order to gift_items
SET @col_exists4 = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'gift_items' AND COLUMN_NAME = 'sort_order'
);
SET @sql4 = IF(@col_exists4 = 0,
    "ALTER TABLE gift_items ADD COLUMN sort_order INT NOT NULL DEFAULT 0 AFTER stock, ADD INDEX idx_gift_sort (sort_order)",
    'SELECT 1'
);
PREPARE stmt4 FROM @sql4; EXECUTE stmt4; DEALLOCATE PREPARE stmt4;

-- End of Migration 020
