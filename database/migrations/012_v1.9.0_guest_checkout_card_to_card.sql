-- ============================================================
-- Migration 012 — v1.9.0
-- Guest checkout now requires a verified customer account before order
-- completion, and card-to-card payment is added alongside Zarinpal.
-- ============================================================

SET NAMES utf8mb4;

-- ---------- Payment settings ----------
INSERT INTO settings (setting_key, setting_value) VALUES
    ('payment_zarinpal_enabled', '1'),
    ('card_to_card_number', ''),
    ('card_to_card_holder', ''),
    ('card_to_card_note', '')
ON DUPLICATE KEY UPDATE setting_key = VALUES(setting_key);

-- ---------- Order payment metadata ----------
SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'payment_method'
);
SET @sql = IF(@col_exists = 0,
    "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(30) NOT NULL DEFAULT 'zarinpal' AFTER total",
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'card_to_card_receipt'
);
SET @sql = IF(@col_exists = 0,
    "ALTER TABLE orders ADD COLUMN card_to_card_receipt VARCHAR(255) DEFAULT NULL AFTER payment_method",
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'card_to_card_submitted_at'
);
SET @sql = IF(@col_exists = 0,
    "ALTER TABLE orders ADD COLUMN card_to_card_submitted_at DATETIME DEFAULT NULL AFTER card_to_card_receipt",
    'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- End of Migration 012
