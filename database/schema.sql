-- ============================================================
-- Sock Shop - Database Schema
-- Engine: InnoDB, Charset: utf8mb4 (full Persian text support)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- Admins table
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin','admin') NOT NULL DEFAULT 'admin',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    full_name VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Customer accounts (based on mobile number)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS customers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(150) DEFAULT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone_verified_at TIMESTAMP NULL DEFAULT NULL,
    email_verified_at TIMESTAMP NULL DEFAULT NULL,
    full_name VARCHAR(150) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Verification codes (SMS/email) for customer phone and email verification
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- Product tags (many-to-many)
-- ------------------------------------------------------------
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

-- ------------------------------------------------------------
-- Product categories (parent_id supports subcategories)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL,
    INDEX idx_active_sort (is_active, sort_order),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Products
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(12,0) NOT NULL,              -- تومان، بدون اعشار
    discount_price DECIMAL(12,0) DEFAULT NULL,  -- اگر تخفیف دارد
    cost_price DECIMAL(12,0) DEFAULT NULL,      -- قیمت تمام‌شده؛ مستقل از قیمت فروش
    sku VARCHAR(60) DEFAULT NULL UNIQUE,
    stock INT NOT NULL DEFAULT 0,               -- موجودی کلی (وقتی واریانت ندارد)
    image VARCHAR(255) DEFAULT NULL,            -- تصویر اصلی
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_active_featured (is_active, is_featured),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Product image gallery (additional images beyond the main image)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Product variants (sock size/color) - optional at the product level
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS product_variants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    size VARCHAR(40) DEFAULT NULL,     -- مثلا 39-42
    color VARCHAR(40) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    price_override DECIMAL(12,0) DEFAULT NULL,
    cost_price DECIMAL(12,0) DEFAULT NULL,      -- مستقل از cost_price سطح محصول؛ در صورت NULL، از محصول ارث می‌برد
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Bulk price-change requests. A bulk operation targets an arbitrary
-- product selection (not tied to a category) and is not itself a price
-- record — each affected product still gets its own price_history row.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bulk_price_operations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL,
    field_changed ENUM('cost_price','sale_price') NOT NULL,
    method ENUM('fixed_amount','percentage','direct_value') NOT NULL,
    requested_change VARCHAR(40) NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    product_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE RESTRICT,
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Immutable audit trail of every cost/sale price change, at the product or
-- variant level, whether from a single edit or a bulk operation.
-- variant_id is SET NULL (not CASCADE) on variant deletion, and
-- variant_label is stored as a text snapshot, so a history row stays
-- meaningful even after the variant itself is gone — the same pattern
-- order_items already uses for product_name/variant_label.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS price_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED DEFAULT NULL,
    variant_label VARCHAR(80) DEFAULT NULL,
    field_changed ENUM('cost_price','sale_price') NOT NULL,
    previous_value DECIMAL(12,0) DEFAULT NULL,
    new_value DECIMAL(12,0) NOT NULL,
    change_amount DECIMAL(12,0) NOT NULL,
    change_percentage DECIMAL(8,4) DEFAULT NULL,
    method ENUM('fixed_amount','percentage','direct_value') NOT NULL,
    reason VARCHAR(255) DEFAULT NULL,
    bulk_operation_id INT UNSIGNED DEFAULT NULL,
    admin_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (variant_id) REFERENCES product_variants(id) ON DELETE SET NULL,
    FOREIGN KEY (bulk_operation_id) REFERENCES bulk_price_operations(id) ON DELETE SET NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE RESTRICT,
    INDEX idx_product (product_id),
    INDEX idx_bulk (bulk_operation_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Discount coupons
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS coupons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(60) NOT NULL UNIQUE,
    type ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
    value DECIMAL(12,0) NOT NULL,
    min_order_amount DECIMAL(12,0) DEFAULT 0,
    max_uses INT DEFAULT NULL,
    used_count INT NOT NULL DEFAULT 0,
    expires_at DATE DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Orders
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED DEFAULT NULL,
    order_code VARCHAR(20) NOT NULL UNIQUE,
    customer_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    province VARCHAR(80) NOT NULL,
    city VARCHAR(80) NOT NULL,
    address TEXT NOT NULL,
    postal_code VARCHAR(20) DEFAULT NULL,
    notes TEXT,
    subtotal DECIMAL(12,0) NOT NULL,
    discount_total DECIMAL(12,0) NOT NULL DEFAULT 0,
    shipping_cost DECIMAL(12,0) NOT NULL DEFAULT 0,
    total DECIMAL(12,0) NOT NULL,
    coupon_code VARCHAR(60) DEFAULT NULL,
    coupon_id INT UNSIGNED DEFAULT NULL,
    status ENUM('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
    payment_status ENUM('unpaid','paid','failed') NOT NULL DEFAULT 'unpaid',
    payment_authority VARCHAR(64) DEFAULT NULL,
    payment_ref_id VARCHAR(64) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_customer (customer_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Order line items
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED DEFAULT NULL,
    variant_id INT UNSIGNED DEFAULT NULL,
    product_name VARCHAR(180) NOT NULL,   -- snapshot در لحظه خرید
    variant_label VARCHAR(80) DEFAULT NULL,
    unit_price DECIMAL(12,0) NOT NULL,
    quantity INT NOT NULL,
    line_total DECIMAL(12,0) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ------------------------------------------------------------
-- Persistent cart (for logged-in customers only)
-- variant_id uses 0 instead of NULL for "no variant" so the UNIQUE KEY works correctly
-- (since MySQL doesn't treat multiple NULLs in a UNIQUE KEY as duplicates)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED NOT NULL,
    variant_id INT UNSIGNED NOT NULL DEFAULT 0,
    quantity INT NOT NULL DEFAULT 1,
    locked_unit_price DECIMAL(12,0) NOT NULL,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_customer_product_variant (customer_id, product_id, variant_id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- General store settings (key/value) — includes the cart price guarantee
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (setting_key, setting_value) VALUES
    ('price_guarantee_enabled', '1'),
    ('price_guarantee_days', '7'),
    ('show_product_tags', '1'),
    ('seo_indexing_enabled', '0'),
    ('site_logo', ''),
    ('announcement_bar_enabled', '0'),
    ('announcement_bar_text', ''),
    ('announcement_bar_link', ''),
    ('footer_about_teaser_text', ''),
    ('footer_shipping_badge_text', ''),
    ('store_phone', ''),
    ('social_instagram_enabled', '0'),
    ('social_instagram_url', ''),
    ('social_telegram_enabled', '0'),
    ('social_telegram_url', ''),
    ('social_bale_enabled', '0'),
    ('social_bale_url', ''),
    ('social_torob_enabled', '0'),
    ('social_torob_url', ''),
    ('enamad_enabled', '0'),
    ('enamad_embed_code', '');

-- ------------------------------------------------------------
-- Log of sent SMS messages (whether actually sent, or just logged when no SMS service is configured)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS sms_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'logged',
    debug_info TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Log of sent emails (for troubleshooting email verification)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS email_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'logged',
    debug_info TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Admin-managed storefront themes. `theme_tokens` is a generic
-- (token_group, token_key, token_value) store so token types beyond color
-- (typography, spacing, radius, shadows, ...) can be added later without a
-- schema change. Exactly one theme has is_active = 1 at a time.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS themes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS theme_tokens (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    theme_id INT UNSIGNED NOT NULL,
    token_group VARCHAR(40) NOT NULL DEFAULT 'color',
    token_key VARCHAR(60) NOT NULL,
    token_value VARCHAR(255) NOT NULL,
    FOREIGN KEY (theme_id) REFERENCES themes(id) ON DELETE CASCADE,
    UNIQUE KEY uniq_theme_token (theme_id, token_group, token_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- Initial seed data
-- Note: the admins table is intentionally left empty. After uploading the
-- project to your host, visit /install.php once to securely create the
-- first admin account with your own password (no CLI needed). That page
-- locks itself after use.
-- ------------------------------------------------------------

INSERT INTO categories (name, slug, description, sort_order, is_active) VALUES
('جوراب مردانه', 'mardane', 'انواع جوراب مردانه، ساقدار و ساقکوتاه', 1, 1),
('جوراب زنانه', 'zanane', 'جوراب‌های زنانه شیک و راحت', 2, 1),
('جوراب بچگانه', 'bachegane', 'جوراب‌های رنگارنگ کودک', 3, 1),
('جوراب ورزشی', 'varzeshi', 'مناسب فعالیت و ورزش', 4, 1);

INSERT INTO themes (id, name, is_active) VALUES
(1, 'پیش‌فرض', 1), (2, 'هم‌رنگ لوگو', 0), (3, 'صورتی ملایم', 0), (4, 'کنتراست بالا', 0);

INSERT INTO theme_tokens (theme_id, token_group, token_key, token_value) VALUES
(1, 'color', 'bg', '#F9E9DA'), (1, 'color', 'surface', '#F5E5D6'), (1, 'color', 'text', '#7D5141'),
(1, 'color', 'muted', '#9C7C6C'), (1, 'color', 'border', '#E7D2BF'), (1, 'color', 'primary', '#582B1C'),
(1, 'color', 'primary-dark', '#3E1D12'), (1, 'color', 'primary-light', '#EAD6C7'), (1, 'color', 'accent', '#B89180'),
(2, 'color', 'bg', '#F3E4D6'), (2, 'color', 'surface', '#FFFFFF'), (2, 'color', 'text', '#6B4130'),
(2, 'color', 'muted', '#9A7A68'), (2, 'color', 'border', '#E6D3C2'), (2, 'color', 'primary', '#4A2A1D'),
(2, 'color', 'primary-dark', '#331C12'), (2, 'color', 'primary-light', '#EFDDD0'), (2, 'color', 'accent', '#A97C68'),
(3, 'color', 'bg', '#FBF1EC'), (3, 'color', 'surface', '#FFFFFF'), (3, 'color', 'text', '#6B4A47'),
(3, 'color', 'muted', '#9C807C'), (3, 'color', 'border', '#EDDAD3'), (3, 'color', 'primary', '#4A302E'),
(3, 'color', 'primary-dark', '#331F1D'), (3, 'color', 'primary-light', '#F1DFDA'), (3, 'color', 'accent', '#C9A29A'),
(4, 'color', 'bg', '#F7EAE0'), (4, 'color', 'surface', '#FFFFFF'), (4, 'color', 'text', '#5C3A2E'),
(4, 'color', 'muted', '#8A6B5C'), (4, 'color', 'border', '#E2CFC0'), (4, 'color', 'primary', '#331C12'),
(4, 'color', 'primary-dark', '#1F110B'), (4, 'color', 'primary-light', '#E8D5C7'), (4, 'color', 'accent', '#8B5E4A');
