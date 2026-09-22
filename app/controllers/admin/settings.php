<?php
/**
 * Store settings — operational settings, branding, social links, and editable
 * public business/legal content.
 *
 * Each settings section is submitted independently so saving one section cannot
 * accidentally overwrite values belonging to another section.
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
        case 'payment':
            setSetting('payment_zarinpal_enabled', isset($_POST['payment_zarinpal_enabled']) ? '1' : '0');
            $cardNumber = preg_replace('/\D+/', '', trim($_POST['card_to_card_number'] ?? ''));
            $cardHolder = trim($_POST['card_to_card_holder'] ?? '');
            if ($cardNumber !== '' && strlen($cardNumber) !== 16) {
                setFlash('error', 'شماره کارت باید ۱۶ رقم باشد.');
                redirect('settings.php');
            }
            if ($cardHolder !== '' && mb_strlen($cardHolder) < 2) {
                setFlash('error', 'نام صاحب کارت معتبر نیست.');
                redirect('settings.php');
            }
            setSetting('card_to_card_number', $cardNumber);
            setSetting('card_to_card_holder', $cardHolder);
            setSetting('card_to_card_note', trim($_POST['card_to_card_note'] ?? ''));
            break;

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
            }
            break;

        case 'announcement':
            setSetting('announcement_bar_enabled', isset($_POST['announcement_bar_enabled']) ? '1' : '0');
            setSetting('announcement_bar_text', trim($_POST['announcement_bar_text'] ?? ''));
            setSetting('announcement_bar_link', trim($_POST['announcement_bar_link'] ?? ''));
            break;

        case 'footer':
            setSetting('footer_about_teaser_text', trim($_POST['footer_about_teaser_text'] ?? ''));
            setSetting('footer_shipping_badge_text', trim($_POST['footer_shipping_badge_text'] ?? ''));
            setSetting('footer_tagline', trim($_POST['footer_tagline'] ?? ''));
            break;

        case 'business':
            foreach (['store_email', 'store_phone', 'store_mobile', 'store_address', 'store_postal_code', 'store_support_hours', 'store_start_date', 'contact_intro'] as $key) {
                setSetting($key, trim($_POST[$key] ?? ''));
            }
            break;

        case 'legal_content':
            foreach (['about_content', 'terms_content', 'privacy_content'] as $key) {
                setSetting($key, trim($_POST[$key] ?? ''));
            }
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

$paymentZarinpalEnabled = getSetting('payment_zarinpal_enabled', '1') === '1';
$cardToCardNumber = getSetting('card_to_card_number', '');
$cardToCardHolder = getSetting('card_to_card_holder', '');
$cardToCardNote = getSetting('card_to_card_note', '');

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
$footerTagline = getSiteContent('footer_tagline');

$storeEmail = getSiteContent('store_email');
$storePhone = getSiteContent('store_phone');
$storeMobile = getSiteContent('store_mobile');
$storeAddress = getSiteContent('store_address');
$storePostalCode = getSiteContent('store_postal_code');
$storeSupportHours = getSiteContent('store_support_hours');
$storeStartDate = getSiteContent('store_start_date');
$contactIntro = getSiteContent('contact_intro');

$aboutContent = getSiteContent('about_content');
$termsContent = getSiteContent('terms_content');
$privacyContent = getSiteContent('privacy_content');

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

    // Integrity check
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

    // Defense-in-depth: strip EXIF if JPEG
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

renderView('admin/settings', compact(
    'pageTitle',
    'paymentZarinpalEnabled', 'cardToCardNumber', 'cardToCardHolder', 'cardToCardNote',
    'priceGuaranteeEnabled', 'priceGuaranteeDays',
    'showProductTags', 'seoIndexingEnabled',
    'siteLogo',
    'announcementBarEnabled', 'announcementBarText', 'announcementBarLink',
    'footerAboutTeaserText', 'footerShippingBadgeText', 'footerTagline',
    'storeEmail', 'storePhone', 'storeMobile', 'storeAddress', 'storePostalCode',
    'storeSupportHours', 'storeStartDate', 'contactIntro',
    'aboutContent', 'termsContent', 'privacyContent',
    'enamadEnabled', 'enamadEmbedCode',
    'socialNetworks', 'socialSettings'
));
