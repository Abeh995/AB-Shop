-- ============================================================
-- Migration 002 — upgrade from version 1.0.0 to 1.1.0
-- Run this script once on a database where the main schema.sql has already been applied.
-- Execute it from phpMyAdmin (SQL tab). No data is deleted; only new columns/tables are
-- added.
--
-- Changes in this version:
--   1) ZarinPal integration              -> new columns in orders
--   2) Checkout coupon support            -> coupon_id column in orders
--   3) SMS logging                         -> new sms_log table
--   4) Multiple admin roles                -> role and is_active columns in admins
-- ============================================================

SET NAMES utf8mb4;

-- ---------- Orders: online payment and coupon data ----------
ALTER TABLE orders
    ADD COLUMN payment_status ENUM('unpaid','paid','failed') NOT NULL DEFAULT 'unpaid' AFTER status,
    ADD COLUMN payment_authority VARCHAR(64) DEFAULT NULL AFTER payment_status,
    ADD COLUMN payment_ref_id VARCHAR(64) DEFAULT NULL AFTER payment_authority,
    ADD COLUMN coupon_id INT UNSIGNED DEFAULT NULL AFTER coupon_code;

-- Legacy orders created before this update were all treated as cash-on-delivery orders;
-- mark their payment status as paid so they remain consistent in reports
-- because the previous version had no online/offline payment distinction. Remove this statement if needed.
UPDATE orders SET payment_status = 'paid' WHERE status IN ('confirmed','processing','shipped','delivered');

-- ---------- Admins: roles and active status ----------
ALTER TABLE admins
    ADD COLUMN role ENUM('super_admin','admin') NOT NULL DEFAULT 'admin' AFTER password_hash,
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role;

-- The first admin created through install.php is automatically promoted to super_admin
-- so the account can manage additional admins from the panel.
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
