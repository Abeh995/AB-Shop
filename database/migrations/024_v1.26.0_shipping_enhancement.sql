-- ============================================================
-- Migration 024 — v1.26.0
-- Shipping Methods Enhancement: Estimated delivery time tracking
-- for customer transparency and checkout presentation.
-- ============================================================

SET NAMES utf8mb4;

-- 1. Guarded column addition for estimated_delivery
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'shipping_methods' 
      AND COLUMN_NAME = 'estimated_delivery'
);

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE shipping_methods ADD COLUMN estimated_delivery VARCHAR(120) DEFAULT NULL AFTER description', 
    'SELECT 1'
);
PREPARE stmt FROM @sql; 
EXECUTE stmt; 
DEALLOCATE PREPARE stmt;

-- 2. Seed default estimated delivery for starter methods if null or empty
UPDATE shipping_methods 
SET estimated_delivery = 'تحویل همان‌روز یا ۲۴ ساعته' 
WHERE match_type = 'province_contains' 
  AND match_value = 'تهران' 
  AND (estimated_delivery IS NULL OR estimated_delivery = '');

UPDATE shipping_methods 
SET estimated_delivery = '۲ الی ۴ روز کاری (پست پیشتاز)' 
WHERE match_type = 'default' 
  AND (estimated_delivery IS NULL OR estimated_delivery = '');

-- End of Migration 024
