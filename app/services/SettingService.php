<?php
/**
 * Setting & Configuration Service
 *
 * Encapsulates all operations on `settings` and `site_content` tables,
 * logo branding uploads, default configuration fallbacks, and Bento directory KPIs.
 *
 * Invariants:
 * - Zero raw SQL in controllers (Rule 7).
 * - Settings writes are validated and transactional where appropriate.
 * - Branding image uploads are sanitized (SVG & MIME checks) and old files are cleanly unlinked.
 */

/**
 * Fetch all grouped settings needed for general store configuration and appearance.
 */
function getStoreSettingsData(): array
{
    $socialNetworks = [
        'instagram' => 'اینستاگرام',
        'telegram'  => 'تلگرام',
        'bale'      => 'بله',
        'torob'     => 'ترب',
    ];

    $socialSettings = [];
    foreach (array_keys($socialNetworks) as $key) {
        $socialSettings[$key] = [
            'enabled' => getSetting("social_{$key}_enabled", '0') === '1',
            'url'     => getSetting("social_{$key}_url", ''),
        ];
    }

    return [
        'socialNetworks' => $socialNetworks,
        'socialSettings' => $socialSettings,

        // Payment
        'paymentZarinpalEnabled' => getSetting('payment_zarinpal_enabled', '1') === '1',
        'cardToCardNumber'       => getSetting('card_to_card_number', ''),
        'cardToCardHolder'       => getSetting('card_to_card_holder', ''),
        'cardToCardNote'         => getSetting('card_to_card_note', ''),

        // Storefront features
        'priceGuaranteeEnabled'  => getSetting('price_guarantee_enabled', '1') === '1',
        'priceGuaranteeDays'     => (int) getSetting('price_guarantee_days', '7'),
        'showProductTags'        => getSetting('show_product_tags', '1') === '1',
        'seoIndexingEnabled'     => getSetting('seo_indexing_enabled', '0') === '1',

        // Live Search
        'searchLiveEnabled'       => getSetting('search_live_enabled', '1') === '1',
        'searchSuggestLimit'      => (int) getSetting('search_suggest_limit', '6'),
        'searchMinChars'          => (int) getSetting('search_min_chars', '2'),
        'searchScopeName'         => getSetting('search_scope_name', '1') === '1',
        'searchScopeDescription'  => getSetting('search_scope_description', '1') === '1',
        'searchIncludeCategories' => getSetting('search_include_categories', '1') === '1',

        // Branding & Storefront visuals
        'siteLogo'                => getSetting('site_logo', ''),
        'announcementBarEnabled'  => getSetting('announcement_bar_enabled', '0') === '1',
        'announcementBarText'     => getSetting('announcement_bar_text', ''),
        'announcementBarLink'     => getSetting('announcement_bar_link', ''),

        // Footer copy
        'footerAboutTeaserText'   => getSetting('footer_about_teaser_text', ''),
        'footerShippingBadgeText' => getSetting('footer_shipping_badge_text', ''),
        'footerTagline'           => getSiteContent('footer_tagline'),

        // Business info
        'storeEmail'        => getSiteContent('store_email'),
        'storePhone'        => getSiteContent('store_phone'),
        'storeMobile'       => getSiteContent('store_mobile'),
        'storeAddress'      => getSiteContent('store_address'),
        'storePostalCode'   => getSiteContent('store_postal_code'),
        'storeSupportHours' => getSiteContent('store_support_hours'),
        'storeStartDate'    => getSiteContent('store_start_date'),
        'contactIntro'      => getSiteContent('contact_intro'),

        // Legal & CMS Content
        'aboutContent'      => getSiteContent('about_content'),
        'termsContent'      => getSiteContent('terms_content'),
        'privacyContent'    => getSiteContent('privacy_content'),

        // Trust badges
        'enamadEnabled'     => getSetting('enamad_enabled', '0') === '1',
        'enamadEmbedCode'   => getSetting('enamad_embed_code', ''),

        // Home Landing sections
        'homeIntroEnabled'          => getSetting('home_intro_enabled', '0') === '1',
        'homeIntroTitle'            => getSetting('home_intro_title', 'جوراب‌هایی که هر روزتان را راحت‌تر می‌کنند'),
        'homeIntroSubtitle'         => getSetting('home_intro_subtitle', 'کیفیت پارچه، دوخت مقاوم و طرح‌های به‌روز؛ مستقیم درِ خانه شما.'),
        'homeCategoriesEnabled'     => getSetting('home_categories_enabled', '0') === '1',
        'homeCategoriesTitle'       => getSetting('home_categories_title', 'دسته‌بندی‌ها'),
        'homeFeaturedEnabled'       => getSetting('home_featured_enabled', '1') === '1',
        'homeFeaturedTitle'         => getSetting('home_featured_title', 'پیشنهاد ویژه'),
        'homeNewestEnabled'         => getSetting('home_newest_enabled', '1') === '1',
        'homeNewestTitle'           => getSetting('home_newest_title', 'آخرین محصولات'),
        'homeCategoryStripEnabled'  => getSetting('home_category_strip_enabled', '1') === '1',
        'homeCategoryStripTitle'    => getSetting('home_category_strip_title', 'دسته‌بندی‌ها را از همین‌جا هم می‌بینید'),
    ];
}

/**
 * Handle saving any configuration section.
 *
 * @param string $section Section identifier
 * @param array $post POST parameters
 * @param array $files FILES parameters
 * @return array{ok: bool, error: ?string, message: ?string}
 */
function saveStoreSection(string $section, array $post, array $files = []): array
{
    $socialNetworks = ['instagram', 'telegram', 'bale', 'torob'];

    switch ($section) {
        case 'payment':
            setSetting('payment_zarinpal_enabled', isset($post['payment_zarinpal_enabled']) ? '1' : '0');
            $cardNumber = preg_replace('/\D+/', '', trim($post['card_to_card_number'] ?? ''));
            $cardHolder = trim($post['card_to_card_holder'] ?? '');

            if ($cardNumber !== '' && strlen($cardNumber) !== 16) {
                return ['ok' => false, 'error' => 'شماره کارت باید ۱۶ رقم باشد.', 'message' => null];
            }
            if ($cardHolder !== '' && mb_strlen($cardHolder) < 2) {
                return ['ok' => false, 'error' => 'نام صاحب کارت معتبر نیست.', 'message' => null];
            }

            setSetting('card_to_card_number', $cardNumber);
            setSetting('card_to_card_holder', $cardHolder);
            setSetting('card_to_card_note', trim($post['card_to_card_note'] ?? ''));
            return ['ok' => true, 'error' => null, 'message' => 'تنظیمات پرداخت و حساب بانکی ذخیره شد.'];

        case 'business':
            foreach (['store_email', 'store_phone', 'store_mobile', 'store_address', 'store_postal_code', 'store_support_hours', 'store_start_date', 'contact_intro'] as $key) {
                setSetting($key, trim($post[$key] ?? ''));
            }
            return ['ok' => true, 'error' => null, 'message' => 'اطلاعات کسب‌وکار و تماس به‌روزرسانی شد.'];

        case 'search':
            setSetting('search_live_enabled', isset($post['search_live_enabled']) ? '1' : '0');
            setSetting('search_suggest_limit', (string) max(2, min(20, (int) ($post['search_suggest_limit'] ?? 6))));
            setSetting('search_min_chars', (string) max(1, min(5, (int) ($post['search_min_chars'] ?? 2))));
            setSetting('search_scope_name', isset($post['search_scope_name']) ? '1' : '0');
            setSetting('search_scope_description', isset($post['search_scope_description']) ? '1' : '0');
            setSetting('search_include_categories', isset($post['search_include_categories']) ? '1' : '0');
            return ['ok' => true, 'error' => null, 'message' => 'تنظیمات جستجوی زنده فروشگاه ذخیره شد.'];

        case 'legal_content':
            foreach (['about_content', 'terms_content', 'privacy_content'] as $key) {
                setSetting($key, trim($post[$key] ?? ''));
            }
            return ['ok' => true, 'error' => null, 'message' => 'محتوای صفحات متنی و قوانین ذخیره شد.'];

        case 'social':
            foreach ($socialNetworks as $key) {
                setSetting("social_{$key}_enabled", isset($post["social_{$key}_enabled"]) ? '1' : '0');
                setSetting("social_{$key}_url", trim($post["social_{$key}_url"] ?? ''));
            }
            setSetting('enamad_enabled', isset($post['enamad_enabled']) ? '1' : '0');
            setSetting('enamad_embed_code', trim($post['enamad_embed_code'] ?? ''));
            return ['ok' => true, 'error' => null, 'message' => 'تنظیمات شبکه‌های اجتماعی و نمادها ذخیره شد.'];

        case 'seo':
            setSetting('seo_indexing_enabled', isset($post['seo_indexing_enabled']) ? '1' : '0');
            return ['ok' => true, 'error' => null, 'message' => 'تنظیمات سئو و ایندکسینگ موتورهای جستجو ذخیره شد.'];

        case 'tags':
            setSetting('show_product_tags', isset($post['show_product_tags']) ? '1' : '0');
            return ['ok' => true, 'error' => null, 'message' => 'تنظیمات نمایش برچسب‌های محصول به‌روز شد.'];

        case 'price_guarantee':
            setSetting('price_guarantee_enabled', isset($post['price_guarantee_enabled']) ? '1' : '0');
            setSetting('price_guarantee_days', (string) max(1, (int) ($post['price_guarantee_days'] ?? 7)));
            return ['ok' => true, 'error' => null, 'message' => 'تنظیمات ضمانت قیمت ذخیره شد.'];

        case 'home_sections':
            setSetting('home_intro_enabled', isset($post['home_intro_enabled']) ? '1' : '0');
            setSetting('home_intro_title', trim($post['home_intro_title'] ?? ''));
            setSetting('home_intro_subtitle', trim($post['home_intro_subtitle'] ?? ''));

            setSetting('home_categories_enabled', isset($post['home_categories_enabled']) ? '1' : '0');
            setSetting('home_categories_title', trim($post['home_categories_title'] ?? 'دسته‌بندی‌ها'));

            setSetting('home_featured_enabled', isset($post['home_featured_enabled']) ? '1' : '0');
            setSetting('home_featured_title', trim($post['home_featured_title'] ?? 'پیشنهاد ویژه'));

            setSetting('home_newest_enabled', isset($post['home_newest_enabled']) ? '1' : '0');
            setSetting('home_newest_title', trim($post['home_newest_title'] ?? 'آخرین محصولات'));

            setSetting('home_category_strip_enabled', isset($post['home_category_strip_enabled']) ? '1' : '0');
            setSetting('home_category_strip_title', trim($post['home_category_strip_title'] ?? 'دسته‌بندی‌ها را از همین‌جا هم می‌بینید'));
            return ['ok' => true, 'error' => null, 'message' => 'تنظیمات بخش‌های صفحه اصلی ذخیره شد.'];

        case 'branding':
            if (!empty($files['site_logo']['name']) && $files['site_logo']['error'] === UPLOAD_ERR_OK) {
                $uploadRes = saveBrandingLogo($files['site_logo']);
                if (!$uploadRes['ok']) {
                    return $uploadRes;
                }
                return ['ok' => true, 'error' => null, 'message' => 'لوگوی فروشگاه با موفقیت بارگذاری شد.'];
            } elseif (isset($post['remove_logo'])) {
                removeBrandingLogo();
                return ['ok' => true, 'error' => null, 'message' => 'لوگو حذف شد؛ نام فروشگاه جای آن نمایش داده می‌شود.'];
            }
            return ['ok' => true, 'error' => null, 'message' => 'بدون تغییر در لوگو.'];

        case 'announcement':
            setSetting('announcement_bar_enabled', isset($post['announcement_bar_enabled']) ? '1' : '0');
            setSetting('announcement_bar_text', trim($post['announcement_bar_text'] ?? ''));
            setSetting('announcement_bar_link', trim($post['announcement_bar_link'] ?? ''));
            return ['ok' => true, 'error' => null, 'message' => 'تنظیمات نوار اعلان ذخیره شد.'];

        case 'footer':
            setSetting('footer_about_teaser_text', trim($post['footer_about_teaser_text'] ?? ''));
            setSetting('footer_shipping_badge_text', trim($post['footer_shipping_badge_text'] ?? ''));
            setSetting('footer_tagline', trim($post['footer_tagline'] ?? ''));
            return ['ok' => true, 'error' => null, 'message' => 'تنظیمات فوتر ذخیره شد.'];

        default:
            return ['ok' => false, 'error' => 'بخش تنظیمات نامعتبر است.', 'message' => null];
    }
}

/**
 * Handle logo upload and save into BRANDING_UPLOAD_DIR.
 */
function saveBrandingLogo(array $file): array
{
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['ok' => false, 'error' => 'حجم تصویر نباید بیشتر از ۲ مگابایت باشد.', 'message' => null];
    }

    $allowedMimes = [
        'image/jpeg'    => 'jpg',
        'image/png'     => 'png',
        'image/webp'    => 'webp',
        'image/svg+xml' => 'svg',
    ];

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset($allowedMimes[$mime])) {
        return ['ok' => false, 'error' => 'فقط تصاویر JPG، PNG، WEBP یا SVG مجاز هستند.', 'message' => null];
    }

    if ($mime === 'image/svg+xml') {
        $content = file_get_contents($file['tmp_name']);
        if (stripos($content, '<svg') === false || stripos($content, '<script') !== false) {
            return ['ok' => false, 'error' => 'فایل SVG نامعتبر یا دارای اسکریپت غیرمجاز است.', 'message' => null];
        }
    } else {
        $imgSize = @getimagesize($file['tmp_name']);
        if ($imgSize === false) {
            return ['ok' => false, 'error' => 'فایل انتخابی یک تصویر معتبر نیست.', 'message' => null];
        }
    }

    if (!is_dir(BRANDING_UPLOAD_DIR)) {
        mkdir(BRANDING_UPLOAD_DIR, 0755, true);
    }

    $filename = generateStandardFilename('logo', 0, 'site', $allowedMimes[$mime]);
    $destination = BRANDING_UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return ['ok' => false, 'error' => 'خطا در ذخیره فایل روی سرور.', 'message' => null];
    }
    @chmod($destination, 0644);

    // Strip EXIF metadata from JPEG
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

    // Remove old logo file if it exists
    $oldLogo = getSetting('site_logo');
    if ($oldLogo && file_exists(BRANDING_UPLOAD_DIR . $oldLogo)) {
        @unlink(BRANDING_UPLOAD_DIR . $oldLogo);
    }

    setSetting('site_logo', $filename);
    return ['ok' => true, 'filename' => $filename, 'error' => null, 'message' => 'لوگو با موفقیت ذخیره شد.'];
}

/**
 * Remove active branding logo file and clear database entry.
 */
function removeBrandingLogo(): bool
{
    $oldLogo = getSetting('site_logo');
    if ($oldLogo && file_exists(BRANDING_UPLOAD_DIR . $oldLogo)) {
        @unlink(BRANDING_UPLOAD_DIR . $oldLogo);
    }
    setSetting('site_logo', '');
    return true;
}

/**
 * Fetch Bento Hub KPI statistics for the central Settings Directory.
 */
function getSettingsDirectoryStats(): array
{
    $pdo = db();

    // Shipping count
    $shippingCount = (int) $pdo->query("SELECT COUNT(*) FROM shipping_methods WHERE is_active = 1")->fetchColumn();

    // SMS patterns count
    $smsCount = (int) $pdo->query("SELECT COUNT(*) FROM sms_patterns WHERE is_active = 1")->fetchColumn();

    // Email accounts count
    $emailCount = (int) $pdo->query("SELECT COUNT(*) FROM email_accounts WHERE is_active = 1")->fetchColumn();

    // Admins count
    $adminCount = (int) $pdo->query("SELECT COUNT(*) FROM admins WHERE is_active = 1")->fetchColumn();

    // Active theme
    $activeTheme = $pdo->query("SELECT name FROM themes WHERE is_active = 1 LIMIT 1")->fetch();
    $activeThemeName = $activeTheme['name'] ?? 'پیش‌فرض';

    // Payment status summary
    $zarinpalOn = getSetting('payment_zarinpal_enabled', '1') === '1';
    $c2cNumber = getSetting('card_to_card_number', '');
    $paymentStatus = [];
    if ($zarinpalOn) $paymentStatus[] = 'زرین‌پال فعال';
    if ($c2cNumber !== '') $paymentStatus[] = 'کارت‌به‌کارت فعال';
    if (empty($paymentStatus)) $paymentStatus[] = 'روش پرداخت غیرفعال';

    return [
        'shippingActiveCount' => $shippingCount,
        'smsActiveCount'      => $smsCount,
        'emailActiveCount'    => $emailCount,
        'adminActiveCount'    => $adminCount,
        'activeThemeName'     => $activeThemeName,
        'paymentSummary'      => implode(' · ', $paymentStatus),
        'seoIndexed'          => getSetting('seo_indexing_enabled', '0') === '1',
        'hasLogo'             => !empty(getSetting('site_logo')),
    ];
}
