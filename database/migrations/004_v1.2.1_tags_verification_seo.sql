-- ============================================================
-- Migration 004 — upgrade from v1.2.0 to v1.2.1
-- Run this script once through phpMyAdmin (SQL tab) against the current database.
-- No data is deleted; this migration only adds new tables/columns and initializes values.
--
-- Changes in this version:
--   1) Product tags                         -> new tags and product_tags tables
--   2) Email and mobile/email verification  -> new customer columns + verification_codes table
--   3) Google indexing control (SEO)       -> new settings entry
-- ============================================================

SET NAMES utf8mb4;

-- ---------- 1) Product tags ----------
CREATE TABLE IF NOT EXISTS tags (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL UNIQUE,
    slug VARCHAR(80) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS product_tags (
    product_id INT UNSIGNED NOT NULL,
    tag_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (product_id, tag_id),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- 2) Customer email and mobile/email verification ----------
ALTER TABLE customers
    ADD COLUMN email VARCHAR(150) DEFAULT NULL AFTER phone,
    ADD COLUMN phone_verified_at TIMESTAMP NULL DEFAULT NULL AFTER password_hash,
    ADD COLUMN email_verified_at TIMESTAMP NULL DEFAULT NULL AFTER email;

-- Customers registered before v1.2.0 are not required to verify their mobile number because verification
-- did not exist at signup; they are automatically marked as verified.
UPDATE customers SET phone_verified_at = created_at WHERE phone_verified_at IS NULL;

CREATE TABLE IF NOT EXISTS verification_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    type ENUM('phone','email') NOT NULL,
    code_hash VARCHAR(64) NOT NULL,
    target VARCHAR(150) NOT NULL,
    attempts INT NOT NULL DEFAULT 0,
    expires_at DATETIME NOT NULL,
    consumed_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    INDEX idx_customer_type (customer_id, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- 3) New settings ----------
INSERT INTO settings (setting_key, setting_value) VALUES
    ('show_product_tags', '1'),
    ('seo_indexing_enabled', '0')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- End of Migration 004
