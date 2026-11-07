-- Migration: 030_v1.40.1_product_spec_presets.sql
-- Description: Dynamic database taxonomy for technical sock specs (material, height, season, usage, toe seam, wash care).
-- Applied: v1.40.1

CREATE TABLE IF NOT EXISTS `product_spec_presets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `spec_key` VARCHAR(64) NOT NULL COMMENT 'Category/Key identifier (e.g. ارتفاع ساق, درصد پنبه, فصل و ضخامت)',
    `spec_value` VARCHAR(191) NOT NULL COMMENT 'Full factual value saved to product material_specs & injected to LLMs',
    `label` VARCHAR(191) NULL COMMENT 'Short display label for quick-fill chips in admin UI',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_spec_key_active_sort` (`spec_key`, `is_active`, `sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Initial Seed Data: Comprehensive sock industry specifications
INSERT IGNORE INTO `product_spec_presets` (`spec_key`, `spec_value`, `label`, `sort_order`, `is_active`) VALUES
-- 1. Cuff Heights (ارتفاع ساق)
('ارتفاع ساق', 'ساق‌دار (Crew)', 'ساق‌دار', 10, 1),
('ارتفاع ساق', 'نیم‌ساق (Quarter)', 'نیم‌ساق', 20, 1),
('ارتفاع ساق', 'مچی (Ankle)', 'مچی', 30, 1),
('ارتفاع ساق', 'کالج (No-show)', 'کالج', 40, 1),
('ارتفاع ساق', 'بالای زانو (Over-Knee)', 'بالای زانو', 50, 1),
('ارتفاع ساق', 'ساق‌بلند ورزشی (Compression)', 'ساق‌بلند ورزشی', 60, 1),
('ارتفاع ساق', 'پاپوش روفرشی (Slipper)', 'پاپوش روفرشی', 70, 1),
('ارتفاع ساق', 'جوراب شلواری (Tights)', 'جوراب شلواری', 80, 1),

-- 2. Fiber Compositions (درصد پنبه / الیاف)
('درصد پنبه', '۱۰۰٪ نخ‌پنبه سوپر شانه شده', '۱۰۰٪ پنبه سوپر', 10, 1),
('درصد پنبه', '۸۵٪ پنبه، ۱۲٪ پلی‌آمید، ۳٪ الاستین', '۸۵٪ پنبه ترکیبی', 20, 1),
('درصد پنبه', '۸۰٪ پشم مرینوس، ۲۰٪ پلی‌آمید', '۸۰٪ پشم مرینوس', 30, 1),
('درصد پنبه', '۷۵٪ الیاف بامبو طبیعی، ۲۰٪ پلی‌آمید، ۵٪ اسپندکس', '۷۵٪ الیاف بامبو', 40, 1),
('درصد پنبه', '۹۵٪ پنبه، ۵٪ کش اسپندکس', '۹۵٪ پنبه، ۵٪ کش', 50, 1),
('درصد پنبه', '۷۰٪ پنبه ارگانیک، ۲۵٪ نخ بامبو، ۵٪ کش', 'پنبه ارگانیک و بامبو', 60, 1),

-- 3. Thickness & Seasonality (ضخامت و فصل مناسب)
('فصل و ضخامت', 'چهارفصل', 'چهارفصل', 10, 1),
('فصل و ضخامت', 'تابستانه و خنک (نازک)', 'تابستانه (خنک)', 20, 1),
('فصل و ضخامت', 'زمستانه و گرم (حوله‌ای/ضخیم)', 'زمستانه (گرم/حوله‌ای)', 30, 1),
('فصل و ضخامت', 'پاییزه و بهاره (متوسط)', 'پاییزه و بهاره', 40, 1),

-- 4. Usage & Styling (کاربرد و استایل)
('کاربرد', 'روزمره و رسمی', 'روزمره و رسمی', 10, 1),
('کاربرد', 'اسپرت و ورزشی', 'اسپرت و ورزشی', 20, 1),
('کاربرد', 'فانتزی و مهمانی', 'فانتزی و مهمانی', 30, 1),
('کاربرد', 'راحتی و خانگی', 'راحتی و خانگی', 40, 1),
('کاربرد', 'رسمی و اداری', 'رسمی و اداری', 50, 1),
('کاربرد', 'کوهنوردی و ترکینگ', 'کوهنوردی و ترکینگ', 60, 1),

-- 5. Toe Seam Construction (نوع دوخت پنجه)
('نوع دوخت پنجه', 'پنجه‌گیری بدون درز (Seamless / روسو)', 'بدون درز (Seamless)', 10, 1),
('نوع دوخت پنجه', 'دوخت تخت و بدون برجستگی', 'دوخت تخت و راحت', 20, 1),
('نوع دوخت پنجه', 'دوخت استاندارد کارگاهی', 'دوخت استاندارد', 30, 1),

-- 6. Wash & Care (دستور شستشو)
('دستور شستشو', 'شستشو با آب سرد ۳۰ درجه، بدون سفیدکننده، اتوکشی نشود', 'آب سرد ۳۰ درجه', 10, 1),
('دستور شستشو', 'شستشوی ملایم ماشینی با مایع لباسشویی، بدون خشک‌کن چرخشی', 'شستشوی ملایم ماشینی', 20, 1),
('دستور شستشو', 'شستشوی دستی با شوینده ملایم، خشک کردن روی سطح افقی', 'شستشوی دستی', 30, 1);

