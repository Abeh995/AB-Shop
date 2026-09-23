-- Migration 016 — v1.15.0: SMS patterns management & Home landing section controls
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS sms_patterns (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pattern_code VARCHAR(64) NOT NULL,
    title VARCHAR(128) NOT NULL,
    event_key VARCHAR(64) NULL,
    pattern_text TEXT NULL,
    description TEXT NULL,
    variables_count TINYINT UNSIGNED NOT NULL DEFAULT 1,
    variables_config JSON NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event_key (event_key),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO sms_patterns (pattern_code, title, event_key, pattern_text, description, variables_count, variables_config, is_active) VALUES
('s3w2n7f1k6', 'کد تایید ورود و ثبت‌نام (OTP)', 'otp', 'کد احراز:\n%code%\nAB Socks-Shop\n@absocks.ir #%code2%', 'ارسال کد یکبار مصرف ورود و ثبت‌نام با دو متغیر code و code2', 2, '[{"name":"code","type":"numeric","max_len":6,"label":"کد تایید"},{"name":"code2","type":"numeric","max_len":6,"label":"کد تکرار"}]', 1);

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('home_intro_enabled', '0'),
    ('home_intro_title', 'جوراب‌هایی که هر روزتان را راحت‌تر می‌کنند'),
    ('home_intro_subtitle', 'کیفیت پارچه، دوخت مقاوم و طرح‌های به‌روز؛ مستقیم درِ خانه شما.'),
    ('home_categories_enabled', '0'),
    ('home_categories_title', 'دسته‌بندی‌ها'),
    ('home_featured_enabled', '1'),
    ('home_featured_title', 'پیشنهاد ویژه'),
    ('home_newest_enabled', '1'),
    ('home_newest_title', 'آخرین محصولات'),
    ('home_category_strip_enabled', '1'),
    ('home_category_strip_title', 'دسته‌بندی‌ها را از همین‌جا هم می‌بینید')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
