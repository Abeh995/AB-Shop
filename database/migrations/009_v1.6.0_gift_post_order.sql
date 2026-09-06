-- ============================================================
-- Migration 009 — upgrade from v1.5.0 to v1.6.0
-- Run this once against the existing production database (phpMyAdmin > SQL tab).
--
-- Introduces gift box / post-order items: a single catalog entity that can
-- be assigned to an order for free by an admin (a "gift"), offered to the
-- customer as a paid checkout add-on (a "post-order"), or both — which role
-- is available is a pair of flags on the same row, not two separate tables,
-- so a single item can be a gift today and a paid add-on tomorrow without
-- being recreated.
--
-- `order_gift_items` snapshots name/image/cost/price at the moment an item
-- is attached to an order, the same way `order_items` already snapshots
-- products — a later change to a gift item's cost or price must not alter
-- the financial record of an order that already shipped.
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS gift_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_giftable TINYINT(1) NOT NULL DEFAULT 1,       -- can an admin assign this to an order for free
    is_post_orderable TINYINT(1) NOT NULL DEFAULT 0, -- can a customer add this as a paid checkout add-on
    cost_price DECIMAL(12,0) NOT NULL,
    post_order_price DECIMAL(12,0) DEFAULT NULL,     -- required (enforced in the app) when is_post_orderable = 1
    stock INT NOT NULL DEFAULT 0,
    created_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_gift_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    gift_item_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(150) NOT NULL,               -- snapshot
    image VARCHAR(255) DEFAULT NULL,          -- snapshot
    quantity INT NOT NULL,
    unit_cost_price DECIMAL(12,0) NOT NULL,   -- snapshot: what it cost the store, per unit
    unit_selling_price DECIMAL(12,0) NOT NULL DEFAULT 0, -- snapshot: what the customer paid, per unit (0 for a free gift)
    role ENUM('gift','post_order') NOT NULL,
    note VARCHAR(255) DEFAULT NULL,
    assigned_by_admin_id INT UNSIGNED DEFAULT NULL, -- set only for role = 'gift'; NULL for a customer-selected post_order
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (gift_item_id) REFERENCES gift_items(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_by_admin_id) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_gift_item (gift_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE orders
    ADD COLUMN gift_items_total DECIMAL(12,0) NOT NULL DEFAULT 0 AFTER shipping_cost;

-- End of Migration 009
