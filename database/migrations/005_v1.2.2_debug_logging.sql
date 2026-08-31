-- ============================================================
-- Migration 005 — upgrade from v1.2.1 to v1.2.2
-- Run this script once, through phpMyAdmin (SQL tab), against the current database.
--
-- Changes in this version:
--   1) Full, viewable logging for SMS/email troubleshooting -> debug_info
--      column on sms_log + new email_log table
--   2) Multi-image product gallery -> no schema change (the product_images
--      table has existed since 1.0.0, but the admin panel couldn't manage
--      it; this version only adds the UI/logic)
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE sms_log ADD COLUMN debug_info TEXT NULL AFTER status;

CREATE TABLE IF NOT EXISTS email_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'logged',
    debug_info TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- End of Migration 005
