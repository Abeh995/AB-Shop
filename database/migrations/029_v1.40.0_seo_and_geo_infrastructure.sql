-- ============================================================
-- Migration 029 — v1.40.0
-- SEO, Generative Engine Optimization (GEO/AI) & 301 Redirects Infrastructure
-- Adds:
--  - SEO metadata and AI material specifications to products table
--  - SEO metadata, rich buyer guide, intro and FAQ to categories table
--  - url_redirects table for 301/302 URL redirection and slug change preservation
--  - Search Console and return policy settings
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- 1. Guarded columns for `products` table
-- ------------------------------------------------------------

-- 1.1 seo_title
SET @col_p_title = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'seo_title'
);
SET @sql_p_title = IF(@col_p_title = 0, 
    'ALTER TABLE products ADD COLUMN seo_title VARCHAR(190) DEFAULT NULL AFTER name', 
    'SELECT 1'
);
PREPARE stmt_p_title FROM @sql_p_title; EXECUTE stmt_p_title; DEALLOCATE PREPARE stmt_p_title;

-- 1.2 seo_description
SET @col_p_desc = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'seo_description'
);
SET @sql_p_desc = IF(@col_p_desc = 0, 
    'ALTER TABLE products ADD COLUMN seo_description VARCHAR(255) DEFAULT NULL AFTER description', 
    'SELECT 1'
);
PREPARE stmt_p_desc FROM @sql_p_desc; EXECUTE stmt_p_desc; DEALLOCATE PREPARE stmt_p_desc;

-- 1.3 canonical_url
SET @col_p_canon = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'canonical_url'
);
SET @sql_p_canon = IF(@col_p_canon = 0, 
    'ALTER TABLE products ADD COLUMN canonical_url VARCHAR(255) DEFAULT NULL AFTER seo_description', 
    'SELECT 1'
);
PREPARE stmt_p_canon FROM @sql_p_canon; EXECUTE stmt_p_canon; DEALLOCATE PREPARE stmt_p_canon;

-- 1.4 material_specs (JSON for AI and structured entity grounding: yarn, cotton%, thickness, height, season)
SET @col_p_specs = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'products' AND COLUMN_NAME = 'material_specs'
);
SET @sql_p_specs = IF(@col_p_specs = 0, 
    'ALTER TABLE products ADD COLUMN material_specs JSON DEFAULT NULL AFTER sku', 
    'SELECT 1'
);
PREPARE stmt_p_specs FROM @sql_p_specs; EXECUTE stmt_p_specs; DEALLOCATE PREPARE stmt_p_specs;


-- ------------------------------------------------------------
-- 2. Guarded columns for `categories` table
-- ------------------------------------------------------------

-- 2.1 seo_title
SET @col_c_title = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'seo_title'
);
SET @sql_c_title = IF(@col_c_title = 0, 
    'ALTER TABLE categories ADD COLUMN seo_title VARCHAR(190) DEFAULT NULL AFTER name', 
    'SELECT 1'
);
PREPARE stmt_c_title FROM @sql_c_title; EXECUTE stmt_c_title; DEALLOCATE PREPARE stmt_c_title;

-- 2.2 seo_description
SET @col_c_desc = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'seo_description'
);
SET @sql_c_desc = IF(@col_c_desc = 0, 
    'ALTER TABLE categories ADD COLUMN seo_description VARCHAR(255) DEFAULT NULL AFTER description', 
    'SELECT 1'
);
PREPARE stmt_c_desc FROM @sql_c_desc; EXECUTE stmt_c_desc; DEALLOCATE PREPARE stmt_c_desc;

-- 2.3 seo_intro (short intro displayed above the product catalog grid)
SET @col_c_intro = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'seo_intro'
);
SET @sql_c_intro = IF(@col_c_intro = 0, 
    'ALTER TABLE categories ADD COLUMN seo_intro TEXT DEFAULT NULL AFTER description', 
    'SELECT 1'
);
PREPARE stmt_c_intro FROM @sql_c_intro; EXECUTE stmt_c_intro; DEALLOCATE PREPARE stmt_c_intro;

-- 2.4 seo_guide (comprehensive buyer guide, sock styling & care tips at bottom of category page)
SET @col_c_guide = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'seo_guide'
);
SET @sql_c_guide = IF(@col_c_guide = 0, 
    'ALTER TABLE categories ADD COLUMN seo_guide TEXT DEFAULT NULL AFTER seo_intro', 
    'SELECT 1'
);
PREPARE stmt_c_guide FROM @sql_c_guide; EXECUTE stmt_c_guide; DEALLOCATE PREPARE stmt_c_guide;

-- 2.5 seo_faq (JSON array of FAQ items: [{q: string, a: string}] for FAQPage schema and SERP expansion)
SET @col_c_faq = (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'categories' AND COLUMN_NAME = 'seo_faq'
);
SET @sql_c_faq = IF(@col_c_faq = 0, 
    'ALTER TABLE categories ADD COLUMN seo_faq JSON DEFAULT NULL AFTER seo_guide', 
    'SELECT 1'
);
PREPARE stmt_c_faq FROM @sql_c_faq; EXECUTE stmt_c_faq; DEALLOCATE PREPARE stmt_c_faq;


-- ------------------------------------------------------------
-- 3. URL Redirects table (301 Permanent Redirects)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `url_redirects` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `source_url` VARCHAR(255) NOT NULL,
    `target_url` VARCHAR(255) NOT NULL,
    `status_code` SMALLINT UNSIGNED NOT NULL DEFAULT 301,
    `hits` INT UNSIGNED NOT NULL DEFAULT 0,
    `last_hit_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_source` (`source_url`),
    INDEX `idx_source` (`source_url`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ------------------------------------------------------------
-- 4. Initial Seed Settings for Search Console & Rich Results
-- ------------------------------------------------------------
INSERT IGNORE INTO `settings` (`setting_key`, `setting_value`) VALUES
    ('google_site_verification', ''),
    ('bing_site_verification', ''),
    ('return_policy_days', '7'),
    ('shipping_lead_time_days', '2');

