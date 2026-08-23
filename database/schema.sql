SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('super_admin','admin') NOT NULL DEFAULT 'admin',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    full_name VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    slug VARCHAR(140) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS products (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id INT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(200) NOT NULL UNIQUE,
    description TEXT,
    price DECIMAL(12,0) NOT NULL,
    discount_price DECIMAL(12,0) DEFAULT NULL,
    sku VARCHAR(60) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_active_featured (is_active, is_featured),
    INDEX idx_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS product_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS product_variants (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    product_id INT UNSIGNED NOT NULL,
    size VARCHAR(40) DEFAULT NULL,
    color VARCHAR(40) DEFAULT NULL,
    stock INT NOT NULL DEFAULT 0,
    price_override DECIMAL(12,0) DEFAULT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


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


CREATE TABLE IF NOT EXISTS orders (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
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
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS order_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    order_id INT UNSIGNED NOT NULL,
    product_id INT UNSIGNED DEFAULT NULL,
    variant_id INT UNSIGNED DEFAULT NULL,
    product_name VARCHAR(180) NOT NULL,
    variant_label VARCHAR(80) DEFAULT NULL,
    unit_price DECIMAL(12,0) NOT NULL,
    quantity INT NOT NULL,
    line_total DECIMAL(12,0) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;


CREATE TABLE IF NOT EXISTS sms_log (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'logged',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone (phone)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



INSERT INTO categories (name, slug, description, sort_order, is_active) VALUES
('جوراب مردانه', 'mardane', 'انواع جوراب مردانه، ساقدار و ساقکوتاه', 1, 1),
('جوراب زنانه', 'zanane', 'جوراب‌های زنانه شیک و راحت', 2, 1),
('جوراب بچگانه', 'bachegane', 'جوراب‌های رنگارنگ کودک', 3, 1),
('جوراب ورزشی', 'varzeshi', 'مناسب فعالیت و ورزش', 4, 1);
