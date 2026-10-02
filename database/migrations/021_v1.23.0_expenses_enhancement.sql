-- ============================================================
-- Migration 021 — v1.23.0
-- Expenses Enhancement: Payment source, payee, expense nature, and receipt attachments
-- ============================================================

SET NAMES utf8mb4;

-- 1. Add payment_source to expenses
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND COLUMN_NAME = 'payment_source'
);
SET @sql = IF(@col_exists = 0,
    "ALTER TABLE expenses ADD COLUMN payment_source VARCHAR(60) NOT NULL DEFAULT 'کارت اصلی فروشگاه' AFTER category",
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Add payee to expenses
SET @col_exists2 = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND COLUMN_NAME = 'payee'
);
SET @sql2 = IF(@col_exists2 = 0,
    "ALTER TABLE expenses ADD COLUMN payee VARCHAR(120) NULL DEFAULT NULL AFTER payment_source",
    'SELECT 1'
);
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

-- 3. Add expense_nature to expenses
SET @col_exists3 = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND COLUMN_NAME = 'expense_nature'
);
SET @sql3 = IF(@col_exists3 = 0,
    "ALTER TABLE expenses ADD COLUMN expense_nature ENUM('fixed', 'variable', 'capital') NOT NULL DEFAULT 'variable' AFTER payee",
    'SELECT 1'
);
PREPARE stmt3 FROM @sql3; EXECUTE stmt3; DEALLOCATE PREPARE stmt3;

-- 4. Add receipt_image to expenses
SET @col_exists4 = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND COLUMN_NAME = 'receipt_image'
);
SET @sql4 = IF(@col_exists4 = 0,
    "ALTER TABLE expenses ADD COLUMN receipt_image VARCHAR(255) NULL DEFAULT NULL AFTER description",
    'SELECT 1'
);
PREPARE stmt4 FROM @sql4; EXECUTE stmt4; DEALLOCATE PREPARE stmt4;

-- 5. Add index on payment_source if not exists
SET @idx_exists = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND INDEX_NAME = 'idx_payment_source'
);
SET @sql5 = IF(@idx_exists = 0,
    "ALTER TABLE expenses ADD INDEX idx_payment_source (payment_source)",
    'SELECT 1'
);
PREPARE stmt5 FROM @sql5; EXECUTE stmt5; DEALLOCATE PREPARE stmt5;

-- 6. Add index on payee if not exists
SET @idx_exists2 = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND INDEX_NAME = 'idx_payee'
);
SET @sql6 = IF(@idx_exists2 = 0,
    "ALTER TABLE expenses ADD INDEX idx_payee (payee)",
    'SELECT 1'
);
PREPARE stmt6 FROM @sql6; EXECUTE stmt6; DEALLOCATE PREPARE stmt6;

-- 7. Add index on expense_nature if not exists
SET @idx_exists3 = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'expenses' AND INDEX_NAME = 'idx_expense_nature'
);
SET @sql7 = IF(@idx_exists3 = 0,
    "ALTER TABLE expenses ADD INDEX idx_expense_nature (expense_nature)",
    'SELECT 1'
);
PREPARE stmt7 FROM @sql7; EXECUTE stmt7; DEALLOCATE PREPARE stmt7;
