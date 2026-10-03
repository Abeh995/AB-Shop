-- ============================================================
-- Migration 025 — v1.28.0
-- Email Studio Enhancement & Coupon Promotions Architecture
-- Adds signature support to domain mailboxes and max_discount_amount
-- cap to coupons table.
-- ============================================================

SET NAMES utf8mb4;

-- 1. Guarded column addition for signature in email_accounts
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'email_accounts' 
      AND COLUMN_NAME = 'signature'
);

SET @sql = IF(@col_exists = 0, 
    'ALTER TABLE email_accounts ADD COLUMN signature TEXT DEFAULT NULL AFTER password_encrypted', 
    'SELECT 1'
);
PREPARE stmt FROM @sql; 
EXECUTE stmt; 
DEALLOCATE PREPARE stmt;

-- 2. Guarded column addition for max_discount_amount in coupons
SET @col_coupon_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'coupons' 
      AND COLUMN_NAME = 'max_discount_amount'
);

SET @sql_coupon = IF(@col_coupon_exists = 0, 
    'ALTER TABLE coupons ADD COLUMN max_discount_amount DECIMAL(12,0) DEFAULT NULL AFTER min_order_amount', 
    'SELECT 1'
);
PREPARE stmt_c FROM @sql_coupon; 
EXECUTE stmt_c; 
DEALLOCATE PREPARE stmt_c;

-- 3. Ensure coupons table has index on code and is_active if not present
SET @idx_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'coupons' 
      AND INDEX_NAME = 'idx_coupon_lookup'
);

SET @sql_idx = IF(@idx_exists = 0,
    'CREATE INDEX idx_coupon_lookup ON coupons (code, is_active)',
    'SELECT 1'
);
PREPARE stmt_idx FROM @sql_idx;
EXECUTE stmt_idx;
DEALLOCATE PREPARE stmt_idx;

-- End of Migration 025
