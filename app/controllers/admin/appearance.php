<?php
/**
 * Appearance controller — manages customer-facing visual presentation:
 * Home/Landing sections visibility & titles, logo, themes, announcement bar, and footer teasers.
 */

$pageTitle = 'ظاهر و صفحه اصلی';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $section = $_POST['section'] ?? '';

    switch ($section) {
        case 'home_sections':
            setSetting('home_intro_enabled', isset($_POST['home_intro_enabled']) ? '1' : '0');
            setSetting('home_intro_title', trim($_POST['home_intro_title'] ?? ''));
            setSetting('home_intro_subtitle', trim($_POST['home_intro_subtitle'] ?? ''));

            setSetting('home_categories_enabled', isset($_POST['home_categories_enabled']) ? '1' : '0');
            setSetting('home_categories_title', trim($_POST['home_categories_title'] ?? 'دسته‌بندی‌ها'));

            setSetting('home_featured_enabled', isset($_POST['home_featured_enabled']) ? '1' : '0');
            setSetting('home_featured_title', trim($_POST['home_featured_title'] ?? 'پیشنهاد ویژه'));

            setSetting('home_newest_enabled', isset($_POST['home_newest_enabled']) ? '1' : '0');
            setSetting('home_newest_title', trim($_POST['home_newest_title'] ?? 'آخرین محصولات'));

            setSetting('home_category_strip_enabled', isset($_POST['home_category_strip_enabled']) ? '1' : '0');
            setSetting('home_category_strip_title', trim($_POST['home_category_strip_title'] ?? 'دسته‌بندی‌ها را از همین‌جا هم می‌بینید'));

            setFlash('success', 'تنظیمات بخش‌های صفحه اصلی با موفقیت ذخیره شد.');
            break;

        case 'branding':
            if (!empty($_FILES['site_logo']['name']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
                $result = handleBrandingImageUpload($_FILES['site_logo']);
                if ($result['ok']) {
                    $oldLogo = getSetting('site_logo');
                    if ($oldLogo && file_exists(BRANDING_UPLOAD_DIR . $oldLogo)) {
                        @unlink(BRANDING_UPLOAD_DIR . $oldLogo);
                    }
                    setSetting('site_logo', $result['filename']);
                    setFlash('success', 'لوگو با موفقیت بارگذاری شد.');
                } else {
                    setFlash('error', $result['error']);
                    redirect('appearance.php');
                }
            } elseif (isset($_POST['remove_logo'])) {
                $oldLogo = getSetting('site_logo');
                if ($oldLogo && file_exists(BRANDING_UPLOAD_DIR . $oldLogo)) {
                    @unlink(BRANDING_UPLOAD_DIR . $oldLogo);
                }
                setSetting('site_logo', '');
                setFlash('success', 'لوگو حذف شد؛ نام فروشگاه جای آن نمایش داده می‌شود.');
            }
            break;

        case 'announcement':
            setSetting('announcement_bar_enabled', isset($_POST['announcement_bar_enabled']) ? '1' : '0');
            setSetting('announcement_bar_text', trim($_POST['announcement_bar_text'] ?? ''));
            setSetting('announcement_bar_link', trim($_POST['announcement_bar_link'] ?? ''));
            setFlash('success', 'تنظیمات نوار اعلان ذخیره شد.');
            break;

        case 'footer':
            setSetting('footer_about_teaser_text', trim($_POST['footer_about_teaser_text'] ?? ''));
            setSetting('footer_shipping_badge_text', trim($_POST['footer_shipping_badge_text'] ?? ''));
            setSetting('footer_tagline', trim($_POST['footer_tagline'] ?? ''));
            setFlash('success', 'تنظیمات فوتر ذخیره شد.');
            break;
    }

    redirect('appearance.php');
}

$homeIntroEnabled = getSetting('home_intro_enabled', '0') === '1';
$homeIntroTitle = getSetting('home_intro_title', 'جوراب‌هایی که هر روزتان را راحت‌تر می‌کنند');
$homeIntroSubtitle = getSetting('home_intro_subtitle', 'کیفیت پارچه، دوخت مقاوم و طرح‌های به‌روز؛ مستقیم درِ خانه شما.');

$homeCategoriesEnabled = getSetting('home_categories_enabled', '0') === '1';
$homeCategoriesTitle = getSetting('home_categories_title', 'دسته‌بندی‌ها');

$homeFeaturedEnabled = getSetting('home_featured_enabled', '1') === '1';
$homeFeaturedTitle = getSetting('home_featured_title', 'پیشنهاد ویژه');

$homeNewestEnabled = getSetting('home_newest_enabled', '1') === '1';
$homeNewestTitle = getSetting('home_newest_title', 'آخرین محصولات');

$homeCategoryStripEnabled = getSetting('home_category_strip_enabled', '1') === '1';
$homeCategoryStripTitle = getSetting('home_category_strip_title', 'دسته‌بندی‌ها را از همین‌جا هم می‌بینید');

$siteLogo = getSetting('site_logo', '');
$announcementBarEnabled = getSetting('announcement_bar_enabled', '0') === '1';
$announcementBarText = getSetting('announcement_bar_text', '');
$announcementBarLink = getSetting('announcement_bar_link', '');

$footerAboutTeaserText = getSetting('footer_about_teaser_text', '');
$footerShippingBadgeText = getSetting('footer_shipping_badge_text', '');
$footerTagline = getSiteContent('footer_tagline');

// Fetch current active theme for quick glance
$activeTheme = db()->query("SELECT * FROM themes WHERE is_active = 1 LIMIT 1")->fetch();

function handleBrandingImageUpload(array $file): array
{
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['ok' => false, 'error' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.'];
    }

    $allowedMimes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/svg+xml' => 'svg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowedMimes[$mime])) {
        return ['ok' => false, 'error' => 'فقط تصاویر JPG، PNG، WEBP یا SVG مجاز هستند.'];
    }

    if ($mime === 'image/svg+xml') {
        $content = file_get_contents($file['tmp_name']);
        if (stripos($content, '<svg') === false || stripos($content, '<script') !== false) {
            return ['ok' => false, 'error' => 'فایل SVG نامعتبر یا دارای اسکریپت غیرمجاز است.'];
        }
    } else {
        $imgSize = @getimagesize($file['tmp_name']);
        if ($imgSize === false) {
            return ['ok' => false, 'error' => 'فایل انتخابی یک تصویر معتبر نیست.'];
        }
    }

    if (!is_dir(BRANDING_UPLOAD_DIR)) {
        mkdir(BRANDING_UPLOAD_DIR, 0755, true);
    }

    $filename = generateStandardFilename('logo', 0, 'site', $allowedMimes[$mime]);
    $destination = BRANDING_UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['ok' => false, 'error' => 'خطا در ذخیره فایل روی سرور.'];
    }
    @chmod($destination, 0644);

    if ($mime === 'image/jpeg' && function_exists('exif_read_data') && function_exists('imagecreatefromjpeg') && function_exists('imagejpeg')) {
        $exif = @exif_read_data($destination);
        if ($exif && (!empty($exif['GPS']) || !empty($exif['Make']) || !empty($exif['Model']))) {
            $gd = @imagecreatefromjpeg($destination);
            if ($gd) {
                imagejpeg($gd, $destination, 90);
                imagedestroy($gd);
            }
        }
    }

    return ['ok' => true, 'filename' => $filename];
}

renderView('admin/appearance', compact(
    'pageTitle',
    'homeIntroEnabled', 'homeIntroTitle', 'homeIntroSubtitle',
    'homeCategoriesEnabled', 'homeCategoriesTitle',
    'homeFeaturedEnabled', 'homeFeaturedTitle',
    'homeNewestEnabled', 'homeNewestTitle',
    'homeCategoryStripEnabled', 'homeCategoryStripTitle',
    'siteLogo',
    'announcementBarEnabled', 'announcementBarText', 'announcementBarLink',
    'footerAboutTeaserText', 'footerShippingBadgeText', 'footerTagline',
    'activeTheme'
));
