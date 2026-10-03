-- ============================================================
-- Migration 023 — v1.25.0
-- Appearance & Storefront Enhancement: Hero Promo Banner, Trust Badges,
-- Favicon, and Homepage Sections limits.
-- ============================================================

SET NAMES utf8mb4;

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
    ('hero_banner_enabled', '0'),
    ('hero_banner_image', ''),
    ('hero_banner_badge', 'پیشنهاد ویژه این فصل'),
    ('hero_banner_title', 'کالکشن جدید و خاص جوراب‌های AB'),
    ('hero_banner_subtitle', 'تنوع بی‌نظیر طرح‌ها و رنگ‌ها با الیاف ۱۰۰٪ نخ‌پنبه و بالاترین دوام'),
    ('hero_banner_cta_text', 'مشاهده همه محصولات'),
    ('hero_banner_cta_url', '/categories.php'),
    ('trust_bar_enabled', '1'),
    ('trust_item_1_title', 'ارسال سریع و مطمئن'),
    ('trust_item_1_desc', 'ارسال پستی به سراسر کشور'),
    ('trust_item_2_title', 'ضمانت ۷ روزه کیفیت'),
    ('trust_item_2_desc', 'تعویض بی‌قید و شرط در صورت عدم رضایت'),
    ('trust_item_3_title', 'الیاف طبیعی نخ‌پنبه'),
    ('trust_item_3_desc', 'ضد حساسیت، لطیف و بسیار با دوام'),
    ('trust_item_4_title', 'بسته‌بندی بهداشتی و شیک'),
    ('trust_item_4_desc', 'مناسب برای کادو با بسته‌بندی استاندارد'),
    ('site_favicon', ''),
    ('home_featured_limit', '6'),
    ('home_newest_limit', '6');
