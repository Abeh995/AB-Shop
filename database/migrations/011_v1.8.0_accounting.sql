-- ============================================================
-- Migration 011 — upgrade from v1.7.0 to v1.8.0
-- Run this once against the existing production database (phpMyAdmin > SQL tab).
--
-- Introduces store accounting: order-level profitability and a general
-- expense ledger.
--
-- order_items.unit_cost_price snapshots what the product/variant actually
-- cost the store at the moment it was sold, the same reasoning as every
-- other snapshot column already on this table (product_name, variant_label,
-- unit_price) — a later change to a product's cost_price must not alter
-- what an already-placed order's profit was. It's nullable and NULL for
-- every order placed before this migration; there is no way to know
-- retroactively what a product's cost was at some past order's time, so
-- those orders are simply excluded from profitability figures rather than
-- guessed at.
--
-- `expenses` is a general ledger for costs that aren't tied to a specific
-- product sale — hosting, packaging, advertising, and so on — with an
-- optional reference to whatever entity it relates to, so a purchase like
-- "20 units of product X" can still be traced back to that product without
-- forcing every expense to have one.
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE order_items
    ADD COLUMN unit_cost_price DECIMAL(12,0) DEFAULT NULL AFTER unit_price;

-- Shipping has a revenue side (what the customer is charged, shipping_methods.cost,
-- introduced in 1.7.0) and a cost side (what the store actually pays a courier/post
-- service) — these can differ, and 1.7.0 only tracked the first. actual_cost is
-- backfilled to match cost so existing methods don't suddenly appear to have a
-- bogus 100% margin; an admin can adjust it separately once accounting is in use.
ALTER TABLE shipping_methods
    ADD COLUMN actual_cost DECIMAL(12,0) DEFAULT NULL AFTER cost;
UPDATE shipping_methods SET actual_cost = cost WHERE actual_cost IS NULL;

ALTER TABLE orders
    ADD COLUMN shipping_actual_cost DECIMAL(12,0) DEFAULT NULL AFTER shipping_method_name;

CREATE TABLE IF NOT EXISTS expenses (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    amount DECIMAL(12,0) NOT NULL,
    expense_date DATE NOT NULL,
    category VARCHAR(60) NOT NULL,
    description TEXT DEFAULT NULL,
    status ENUM('active','archived') NOT NULL DEFAULT 'active',
    reference_type VARCHAR(40) DEFAULT NULL,  -- e.g. 'product', 'gift_item' — free-form, not a foreign key
    reference_id INT UNSIGNED DEFAULT NULL,   -- id within reference_type's own table; not enforced as an FK
                                               -- since reference_type can point at more than one table
    created_by INT UNSIGNED DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL,
    INDEX idx_status_date (status, expense_date),
    INDEX idx_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- End of Migration 011
