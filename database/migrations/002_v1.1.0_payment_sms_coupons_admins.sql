SET NAMES utf8mb4;

ALTER TABLE orders
    ADD COLUMN payment_status ENUM('unpaid','paid','failed') NOT NULL DEFAULT 'unpaid' AFTER status,
    ADD COLUMN payment_authority VARCHAR(64) DEFAULT NULL AFTER payment_status,
    ADD COLUMN payment_ref_id VARCHAR(64) DEFAULT NULL AFTER payment_authority,
    ADD COLUMN coupon_id INT UNSIGNED DEFAULT NULL AFTER coupon_code;

UPDATE orders SET payment_status = 'paid' WHERE status IN ('confirmed','processing','shipped','delivered');

ALTER TABLE admins
    ADD COLUMN role ENUM('super_admin','admin') NOT NULL DEFAULT 'admin' AFTER password_hash,
    ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER role;

UPDATE admins SET role = 'super_admin' WHERE id = (SELECT id FROM (SELECT MIN(id) AS id FROM admins) AS t);

CREATE TABLE IF NOT EXISTS sms_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'logged',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
