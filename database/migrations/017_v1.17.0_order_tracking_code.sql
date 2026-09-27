-- ============================================================
-- Migration 017 — v1.17.0
-- Add postal parcel tracking_code to orders table
-- ============================================================

SET NAMES utf8mb4;

-- ---------- Add tracking_code column safely ----------
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'tracking_code'
);
SET @sql = IF(@col_exists = 0,
    "ALTER TABLE orders ADD COLUMN tracking_code VARCHAR(100) DEFAULT NULL AFTER payment_method",
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- End of Migration 017
