-- ============================================================
-- Migration 002 — upgrade from v1.0.0 to v1.1.0
-- Run this script once, through phpMyAdmin (SQL tab), against a database
-- that has already run the original schema.sql. No data is deleted; this
-- only adds new columns/tables.
--
-- Changes in this version:
--   1) Zarinpal payment gateway integration -> new columns on orders
--   2) Coupon system enabled at checkout    -> coupon_id column on orders
--   3) SMS system                           -> new sms_log table
--   4) Multiple admins with different roles -> role and is_active columns on admins
-- ============================================================

SET NAMES utf8mb4;

-- ---------- Orders: online payment info and coupon code ----------
ALTER TABLE orders
    ADD COLUMN payment_status ENUM('unpaid','paid','failed') NOT NULL DEFAULT 'unpaid' AFTER status,
    ADD COLUMN payment_authority VARCHAR(64) DEFAULT NULL AFTER payment_status,
    ADD COLUMN payment_ref_id VARCHAR(64) DEFAULT NULL AFTER payment_authority,
    ADD COLUMN coupon_id INT UNSIGNED DEFAULT NULL AFTER coupon_code;

-- Existing orders (from before this update) were all recorded as "cash on
-- delivery"; so they aren't lost in reports, their payment status is
-- manually marked paid (since the previous version had no online/offline
-- concept at all). Feel free to remove this line if you'd rather not.
UPDATE orders SET payment_status = 'paid' WHERE status IN ('confirmed','processing','shipped','delivered');

-- ---------- Admins: role and active/inactive status ----------
ALTER TABLE admins
    ADD COLUMN role ENUM('super_admin','admin') NOT NULL DEFAULT 'admin' AFTER password_hash,
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role;

-- The first admin ever created (via install.php) automatically becomes
-- super_admin, so they can add/manage other admins from the panel.
UPDATE admins SET role = 'super_admin' WHERE id = (SELECT id FROM (SELECT MIN(id) AS id FROM admins) AS t);

-- ---------- SMS log table ----------
CREATE TABLE IF NOT EXISTS sms_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'logged',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- End of Migration 002
