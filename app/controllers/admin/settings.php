<?php
/**
 * Store settings — cart price guarantee, product tags visibility, SEO indexing,
 * site branding/logo, header announcement bar, footer content and social links.
 *
 * Each settings section below is submitted as its own <form> with a hidden
 * "section" field, and only the keys belonging to that section are written.
 * This avoids the earlier fragile pattern of mirroring every other section's
 * values as hidden fields (easy to forget one and silently reset a setting).
 */

$pageTitle = 'تنظیمات فروشگاه';

$socialNetworks = [
    'instagram' => 'اینستاگرام',
    'telegram'  => 'تلگرام',
    'bale'      => 'بله',
    'torob'     => 'ترب',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $section = $_POST['section'] ?? '';

    switch ($section) {
        case 'price_guarantee':
            setSetting('price_guarantee_enabled', isset($_POST['price_guarantee_enabled']) ? '1' : '0');
            setSetting('price_guarantee_days', (string) max(1, (int) ($_POST['price_guarantee_days'] ?? 7)));
            break;

        case 'tags':
            setSetting('show_product_tags', isset($_POST['show_product_tags']) ? '1' : '0');
            break;

        case 'seo':
            setSetting('seo_indexing_enabled', isset($_POST['seo_indexing_enabled']) ? '1' : '0');
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
                    redirect('settings.php');
                }
            } elseif (isset($_POST['remove_logo'])) {
                $oldLogo = getSetting('site_logo');
                if ($oldLogo && file_exists(BRANDING_UPLOAD_DIR . $oldLogo)) {
                    @unlink(BRANDING_UPLOAD_DIR . $oldLogo);
                }
                setSetting('site_logo', '');
                setFlash('success', 'لوگو حذف شد؛ نام فروشگاه جای آن نمایش داده می‌شود.');
            } else {
                setFlash('success', 'تنظیمات ذخیره شد.');
            }
            redirect('settings.php');
            break;

        case 'announcement':
            setSetting('announcement_bar_enabled', isset($_POST['announcement_bar_enabled']) ? '1' : '0');
            setSetting('announcement_bar_text', trim($_POST['announcement_bar_text'] ?? ''));
            setSetting('announcement_bar_link', trim($_POST['announcement_bar_link'] ?? ''));
            break;

        case 'footer':
            setSetting('footer_about_teaser_text', trim($_POST['footer_about_teaser_text'] ?? ''));
            setSetting('footer_shipping_badge_text', trim($_POST['footer_shipping_badge_text'] ?? ''));
            setSetting('store_phone', trim($_POST['store_phone'] ?? ''));
            break;

        case 'social':
            foreach (array_keys($socialNetworks) as $key) {
                setSetting("social_{$key}_enabled", isset($_POST["social_{$key}_enabled"]) ? '1' : '0');
                setSetting("social_{$key}_url", trim($_POST["social_{$key}_url"] ?? ''));
            }
            setSetting('enamad_enabled', isset($_POST['enamad_enabled']) ? '1' : '0');
            setSetting('enamad_embed_code', trim($_POST['enamad_embed_code'] ?? ''));
            break;
    }

    setFlash('success', 'تنظیمات ذخیره شد.');
    redirect('settings.php');
}

$priceGuaranteeEnabled = getSetting('price_guarantee_enabled', '1') === '1';
$priceGuaranteeDays = (int) getSetting('price_guarantee_days', '7');
$showProductTags = getSetting('show_product_tags', '1') === '1';
$seoIndexingEnabled = getSetting('seo_indexing_enabled', '0') === '1';

$siteLogo = getSetting('site_logo', '');
$announcementBarEnabled = getSetting('announcement_bar_enabled', '0') === '1';
$announcementBarText = getSetting('announcement_bar_text', '');
$announcementBarLink = getSetting('announcement_bar_link', '');

$footerAboutTeaserText = getSetting('footer_about_teaser_text', '');
$footerShippingBadgeText = getSetting('footer_shipping_badge_text', '');
$storePhone = getSetting('store_phone', '');

$enamadEnabled = getSetting('enamad_enabled', '0') === '1';
$enamadEmbedCode = getSetting('enamad_embed_code', '');

$socialSettings = [];
foreach (array_keys($socialNetworks) as $key) {
    $socialSettings[$key] = [
        'enabled' => getSetting("social_{$key}_enabled", '0') === '1',
        'url'     => getSetting("social_{$key}_url", ''),
    ];
}

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

    if (!is_dir(BRANDING_UPLOAD_DIR)) {
        mkdir(BRANDING_UPLOAD_DIR, 0755, true);
    }

    $filename = 'logo-' . bin2hex(random_bytes(8)) . '.' . $allowedMimes[$mime];

    if (!move_uploaded_file($file['tmp_name'], BRANDING_UPLOAD_DIR . $filename)) {
        return ['ok' => false, 'error' => 'خطا در ذخیره فایل روی سرور.'];
    }

    return ['ok' => true, 'filename' => $filename];
}

renderView('admin/settings', compact(
    'pageTitle',
    'priceGuaranteeEnabled', 'priceGuaranteeDays',
    'showProductTags',
    'seoIndexingEnabled',
    'siteLogo',
    'announcementBarEnabled', 'announcementBarText', 'announcementBarLink',
    'footerAboutTeaserText', 'footerShippingBadgeText', 'storePhone',
    'enamadEnabled', 'enamadEmbedCode',
    'socialNetworks', 'socialSettings'
));
