<?php
/**
 * Landing Studio field schema — Single Source of Truth for the storefront
 * landing-page content model.
 *
 * Responsibilities:
 *  1. Describe every editable node of the landing config (path, Persian label,
 *     editor control type, constraints) so the admin Studio UI can be generated
 *     without hand-writing hundreds of form fields.
 *  2. Provide the shipped defaults (app/data/landing-defaults.json — generated
 *     from the React source, normalised to the icon keys the components accept).
 *  3. Sanitize/coerce an arbitrary payload back into a safe, schema-shaped
 *     config array (used by every write path: draft save, publish, import).
 *
 * The React bundle consumes the very same shape through
 * window.__AB_INITIAL_DATA__.landingConfig, so adding a field here is enough to
 * make it editable in the Studio; it becomes visible on the storefront only
 * after the bundle reads that path.
 */

/**
 * Build one leaf field descriptor.
 */
function landingField(string $path, string $label, string $type = 'text', array $extra = []): array
{
    return array_merge(['path' => $path, 'label' => $label, 'type' => $type], $extra);
}

/**
 * Build a repeater (array of objects) field descriptor.
 */
function landingRepeater(string $path, string $label, array $fields, array $extra = []): array
{
    return array_merge([
        'path'       => $path,
        'label'      => $label,
        'type'       => 'repeater',
        'itemLabel'  => $extra['itemLabel'] ?? 'آیتم',
        'fields'     => $fields,
        'max'        => $extra['max'] ?? 30,
        'min'        => $extra['min'] ?? 0,
    ], $extra);
}

/**
 * Build a primitive list (array of strings / colours) field descriptor.
 */
function landingList(string $path, string $label, string $itemType = 'text', array $extra = []): array
{
    return array_merge([
        'path'     => $path,
        'label'    => $label,
        'type'     => 'list',
        'itemType' => $itemType,
        'max'      => $extra['max'] ?? 30,
    ], $extra);
}

/**
 * Icon vocabularies accepted by the React components, per usage site.
 * Every key must exist in assets/js/landing-studio.js icon registry too.
 */
function landingIconSets(): array
{
    return [
        'hero'     => ['feather', 'palette', 'truck', 'leaf', 'foot', 'shield', 'heart', 'sparkles', 'zap', 'award', 'package'],
        'trust'    => ['feather', 'refresh', 'truck', 'gift', 'shield', 'sparkles', 'heart', 'zap', 'award'],
        'tech'     => ['leaf', 'foot', 'palette'],
        'badge'    => ['shield', 'badge', 'card', 'bank'],
        'timeline' => ['bag', 'scissors', 'send', 'truck', 'check'],
        'quiz'     => ['Sun', 'Dumbbell', 'Moon', 'Zap', 'Sparkles', 'Palette', 'Shuffle', 'Footprints'],
    ];
}

/**
 * Shipped landing defaults (JSON snapshot of the React defaultLandingConfig,
 * plus the server-rendered SEO block).
 */
function landingDefaultConfig(): array
{
    static $defaults = null;
    if ($defaults !== null) {
        return $defaults;
    }

    $file = APP_ROOT . '/app/data/landing-defaults.json';
    $raw = is_readable($file) ? file_get_contents($file) : '';
    $decoded = $raw !== '' ? json_decode($raw, true) : null;
    $defaults = is_array($decoded) ? $decoded : [];

    return $defaults;
}

/**
 * The complete studio section map: rails, groups and fields.
 *
 * Section keys:
 *  - id       unique slug of the section inside the studio
 *  - path     config path prefix of the section ('' for the page order section)
 *  - preview  value of data-lb-section used by the preview scroll bridge
 *  - toggle   'enabled' (section has its own flag) | 'order' (sectionOrder) | null
 */
function landingStudioSections(): array
{
    static $sections = null;
    if ($sections !== null) {
        return $sections;
    }

    $iconSetOptions = static function (string $set): array {
        return array_map(static fn ($k) => ['value' => $k, 'label' => $k], landingIconSets()[$set] ?? []);
    };

    $sections = [
        [
            'id' => 'topStrip', 'path' => 'topStrip', 'preview' => 'topStrip', 'toggle' => 'enabled',
            'title' => 'نوار اعلان بالای سایت', 'icon' => 'megaphone',
            'desc' => 'نوار باریک بالای صفحه؛ پیام اصلی فروش، کد تخفیف و لینک پیگیری سفارش.',
            'groups' => [
                ['title' => 'نمایش و متن‌ها', 'fields' => [
                    landingField('topStrip.enabled', 'نمایش نوار اعلان', 'toggle'),
                    landingField('topStrip.announcement', 'متن اعلان اصلی', 'textarea', ['rows' => 2, 'maxLength' => 160]),
                    landingField('topStrip.couponBadge', 'کد تخفیف نمایشی', 'text', ['maxLength' => 32, 'hint' => 'کد روی نوار، به‌صورت نشان کوچک نمایش داده می‌شود.']),
                    landingField('topStrip.giftPackagingText', 'متن هدیه و بسته‌بندی', 'text', ['maxLength' => 80]),
                    landingField('topStrip.trackOrderText', 'متن دکمه پیگیری سفارش', 'text', ['maxLength' => 40]),
                    landingField('topStrip.freeShippingThreshold', 'سقف ارسال رایگان', 'number', ['min' => 0, 'step' => 50000, 'unit' => 'تومان']),
                ]],
                ['title' => 'متن‌های کپی کد تخفیف', 'fields' => [
                    landingField('topStrip.copyToast', 'پیام پس از کپی کد', 'text', ['maxLength' => 120, 'hint' => '{code} با کد تخفیف جایگزین می‌شود.']),
                    landingField('topStrip.codeLabel', 'متن نمایش کد', 'text', ['maxLength' => 120, 'hint' => '{code} با کد تخفیف جایگزین می‌شود.']),
                    landingField('topStrip.copyHint', 'راهنمای کلیک روی کد', 'text', ['maxLength' => 80]),
                ]],
            ],
        ],
        [
            'id' => 'seo', 'path' => 'seo', 'preview' => 'hero', 'toggle' => null,
            'title' => 'عنوان صفحه و سئو', 'icon' => 'search',
            'desc' => 'عنوان تب مرورگر، توضیح متا و تصویر اشتراک‌گذاری در شبکه‌های اجتماعی.',
            'groups' => [
                ['title' => 'متادیتا', 'fields' => [
                    landingField('seo.title', 'عنوان صفحه (Title)', 'text', ['maxLength' => 120, 'suggestLength' => 60]),
                    landingField('seo.description', 'توضیح متا (Description)', 'textarea', ['rows' => 3, 'maxLength' => 320, 'suggestLength' => 155]),
                    landingField('seo.ogImage', 'تصویر اشتراک‌گذاری (OG)', 'image', ['hint' => 'ابعاد پیشنهادی ۱۲۰۰×۶۳۰ پیکسل.']),
                ]],
            ],
        ],
        [
            'id' => 'header', 'path' => 'header', 'preview' => 'header', 'toggle' => null,
            'title' => 'هدر، لوگو و منو', 'icon' => 'layout',
            'desc' => 'لوگوتایپ، زیرعنوان لاتین و لینک‌های منوی اصلی دسکتاپ.',
            'groups' => [
                ['title' => 'هویت هدر', 'fields' => [
                    landingField('header.logoTitle', 'عنوان لوگو', 'text', ['maxLength' => 40]),
                    landingField('header.logoSubtitle', 'زیرعنوان لوگو (لاتین)', 'text', ['maxLength' => 40, 'dir' => 'ltr']),
                    landingField('header.searchEnabled', 'نمایش جستجو در هدر', 'toggle'),
                    landingField('header.trackEnabled', 'نمایش پیگیری سفارش', 'toggle'),
                    landingField('header.cartEnabled', 'نمایش سبد خرید', 'toggle'),
                ]],
                ['title' => 'لینک‌های منو', 'fields' => [
                    landingRepeater('header.navLinks', 'لینک‌های منوی اصلی', [
                        landingField('id', 'مقصد (لنگر)', 'select', ['options' => [
                            ['value' => 'catalog', 'label' => 'کاتالوگ محصولات'],
                            ['value' => 'pack', 'label' => 'باکس‌ساز'],
                            ['value' => 'story', 'label' => 'داستان کارگاه'],
                            ['value' => 'gallery', 'label' => 'گالری حرکت'],
                            ['value' => 'size', 'label' => 'راهنمای سایز'],
                            ['value' => 'tech', 'label' => 'جزئیات فنی'],
                            ['value' => 'faq', 'label' => 'سوالات متداول'],
                        ]]),
                        landingField('label', 'عنوان لینک', 'text', ['maxLength' => 30]),
                        landingField('desc', 'توضیح زیر لینک', 'text', ['maxLength' => 120]),
                        landingField('enabled', 'نمایش این لینک', 'toggle'),
                    ], ['itemLabel' => 'لینک منو', 'max' => 8]),
                ]],
                ['title' => 'متن‌های منوی موبایل', 'fields' => [
                    landingField('header.menuLabel', 'برچسب دکمه منو', 'text', ['maxLength' => 30]),
                    landingField('header.menuCloseLabel', 'برچسب بستن منو', 'text', ['maxLength' => 30]),
                    landingField('header.homeLabel', 'عنوان «صفحه اصلی» در منو', 'text', ['maxLength' => 30]),
                    landingField('header.deckHomeLabel', 'برچسب خانه در نوار پایین', 'text', ['maxLength' => 20]),
                    landingField('header.deckMenuLabel', 'برچسب منو در نوار پایین', 'text', ['maxLength' => 20]),
                    landingField('header.searchLabel', 'برچسب دکمه/فیلد جستجو', 'text', ['maxLength' => 30]),
                    landingField('header.faqLabel', 'عنوان «سوالات متداول» در منو', 'text', ['maxLength' => 30]),
                    landingField('header.deckShopLabel', 'برچسب فروشگاه در نوار پایین', 'text', ['maxLength' => 20]),
                    landingField('header.deckWheelLabel', 'برچسب گردونه شانس در نوار پایین', 'text', ['maxLength' => 30]),
                ]],
            ],
        ],
        [
            'id' => 'hero', 'path' => 'hero', 'preview' => 'hero', 'toggle' => 'order',
            'title' => 'بخش اصلی (Hero)', 'icon' => 'sparkles',
            'desc' => 'اولین صفحه‌ای که مشتری می‌بیند: تیتر، زیرتیتر، دکمه‌ها و کارت محصول قهرمان.',
            'groups' => [
                ['title' => 'تیتر و متن اصلی', 'fields' => [
                    landingField('hero.badgeText', 'متن نشان بالای تیتر', 'text', ['maxLength' => 80]),
                    landingField('hero.badgeTag', 'برچسب لاتین نشان', 'text', ['maxLength' => 24, 'dir' => 'ltr']),
                    landingField('hero.headlineMain', 'بخش اول تیتر', 'text', ['maxLength' => 60]),
                    landingField('hero.headlineHighlight', 'بخش رنگی تیتر', 'text', ['maxLength' => 60]),
                    landingField('hero.subtitle', 'زیرتیتر توضیحی', 'textarea', ['rows' => 4, 'maxLength' => 400]),
                    landingField('hero.marqueeText', 'متن نوار متحرک', 'text', ['maxLength' => 80]),
                ]],
                ['title' => 'دکمه‌های فراخوان', 'fields' => [
                    landingField('hero.ctaPrimaryLabel', 'متن دکمه اصلی', 'text', ['maxLength' => 40, 'linkWith' => 'hero.ctaPrimaryLink']),
                    landingField('hero.ctaPrimaryLink', 'لینک دکمه اصلی', 'link'),
                    landingField('hero.ctaSecondaryLabel', 'متن دکمه دوم', 'text', ['maxLength' => 60, 'linkWith' => 'hero.ctaSecondaryLink']),
                    landingField('hero.ctaSecondaryLink', 'لینک دکمه دوم', 'link'),
                    landingField('hero.ctaSecondaryEnabled', 'نمایش دکمه دوم', 'toggle'),
                    landingField('hero.trustChips', 'چیپ‌های اعتماد', 'list', ['itemType' => 'text', 'max' => 6]),
                ]],
                ['title' => 'استایل تیتر', 'fields' => [
                    landingField('hero.underlinePreset', 'طرح خط زیر تیتر', 'select', ['options' => [
                        ['value' => 'wavy', 'label' => 'موجی'],
                        ['value' => 'brush', 'label' => 'قلم‌مویی'],
                        ['value' => 'loop', 'label' => 'حلقه‌ای'],
                        ['value' => 'arc', 'label' => 'کمانی'],
                        ['value' => 'straight', 'label' => 'خط صاف'],
                        ['value' => 'custom', 'label' => 'مسیر دلخواه'],
                    ]]),
                    landingField('hero.customUnderlinePath', 'مسیر SVG دلخواه', 'text', ['dir' => 'ltr', 'showIf' => ['hero.underlinePreset' => 'custom']]),
                    landingField('hero.underlineColor', 'رنگ خط زیر تیتر', 'color'),
                ]],
                ['title' => 'کارت محصول قهرمان', 'fields' => [
                    landingField('hero.productBadge', 'عنوان محصول', 'text', ['maxLength' => 60]),
                    landingField('hero.productTag', 'برچسب لاتین محصول', 'text', ['maxLength' => 24, 'dir' => 'ltr']),
                    landingField('hero.productCollection', 'نام مجموعه', 'text', ['maxLength' => 60]),
                    landingField('hero.productImage', 'تصویر محصول', 'image'),
                    landingField('hero.productPrice', 'قیمت نمایشی', 'number', ['min' => 0, 'step' => 1000, 'unit' => 'تومان']),
                    landingField('hero.productButtonText', 'متن دکمه افزودن به سبد', 'text', ['maxLength' => 30]),
                    landingField('hero.showFeaturedCard', 'نمایش کارت محصول در هیرو', 'toggle'),
                ]],
                ['title' => 'تمبر و آمار کوچک', 'fields' => [
                    landingField('hero.stampEnabled', 'نمایش تمبر چرخان', 'toggle'),
                    landingField('hero.stampText', 'متن تمبر چرخان', 'text', ['maxLength' => 80]),
                    landingField('hero.stampIcon', 'آیکون تمبر', 'icon', ['options' => $iconSetOptions('hero')]),
                    landingRepeater('hero.miniTrustStats', 'آمارهای کوچک اعتماد', [
                        landingField('icon', 'آیکون', 'icon', ['options' => $iconSetOptions('hero')]),
                        landingField('title', 'عنوان', 'text', ['maxLength' => 30]),
                        landingField('subtitle', 'زیرعنوان', 'text', ['maxLength' => 40]),
                    ], ['itemLabel' => 'آمار', 'max' => 4]),
                ]],
                ['title' => 'جلوه‌های حرکتی', 'fields' => [
                    landingField('hero.parallaxEnabled', 'پارالاکس با حرکت موس', 'toggle'),
                    landingField('hero.parallaxIntensity', 'شدت پارالاکس', 'range', ['min' => 0, 'max' => 2, 'step' => 0.1]),
                    landingField('hero.dotGridOpacity', 'شفافیت شبکه نقطه‌ای', 'range', ['min' => 0, 'max' => 1, 'step' => 0.05]),
                ]],
            ],
        ],
        [
            'id' => 'trustBar', 'path' => 'trustBar', 'preview' => 'trustBar', 'toggle' => 'enabled',
            'title' => 'نوار اعتماد و مزایا', 'icon' => 'shield',
            'desc' => 'چهار تا شش مزیت کلیدی فروشگاه همراه با آیکون و نوار متحرک.',
            'groups' => [
                ['title' => 'نمایش و نوار متحرک', 'fields' => [
                    landingField('trustBar.enabled', 'نمایش این بخش', 'toggle'),
                    landingField('trustBar.marqueeText', 'متن نوار متحرک', 'text', ['maxLength' => 80]),
                    landingField('trustBar.marqueeSubText', 'زیرمتن لاتین نوار', 'text', ['maxLength' => 40, 'dir' => 'ltr']),
                    landingField('trustBar.marqueeSpeed', 'سرعت نوار', 'range', ['min' => 5, 'max' => 60, 'step' => 1]),
                ]],
                ['title' => 'آیتم‌های مزیت', 'fields' => [
                    landingRepeater('trustBar.items', 'مزیت‌ها', [
                        landingField('icon', 'آیکون', 'icon', ['options' => $iconSetOptions('trust')]),
                        landingField('title', 'عنوان', 'text', ['maxLength' => 30]),
                        landingField('desc', 'توضیح کوتاه', 'text', ['maxLength' => 50]),
                        landingField('active', 'نمایش داده شود', 'toggle'),
                    ], ['itemLabel' => 'مزیت', 'max' => 8, 'min' => 1]),
                ]],
            ],
        ],
        [
            'id' => 'catalog', 'path' => 'catalog', 'preview' => 'catalog', 'toggle' => 'order',
            'title' => 'کاتالوگ محصولات', 'icon' => 'grid',
            'desc' => 'عنوان، توضیح، جستجو و چیدمان گالری محصولات (محصولات از پنل محصولات می‌آید).',
            'groups' => [
                ['title' => 'عنوان و توضیح', 'fields' => [
                    landingField('catalog.eyebrow', 'برچسب بالای عنوان', 'text', ['maxLength' => 40]),
                    landingField('catalog.title', 'عنوان بخش', 'text', ['maxLength' => 90]),
                    landingField('catalog.desc', 'توضیح بخش', 'textarea', ['rows' => 3, 'maxLength' => 260]),
                ]],
                ['title' => 'جستجو و فیلتر', 'fields' => [
                    landingField('catalog.showSearch', 'نمایش جستجو', 'toggle'),
                    landingField('catalog.showSort', 'نمایش مرتب‌سازی', 'toggle'),
                    landingField('catalog.searchPlaceholder', 'متن راهنمای جستجو', 'text', ['maxLength' => 80]),
                    landingField('catalog.emptySearchText', 'متن «نتیجه‌ای یافت نشد»', 'text', ['maxLength' => 80]),
                    landingField('catalog.resetSearchText', 'متن دکمه بازنشانی فیلتر', 'text', ['maxLength' => 40]),
                    landingList('catalog.categoryTabsOrder', 'ترتیب تب‌های دسته‌بندی', 'text', [
                        'max'          => 12,
                        'suggest'      => 'categories',
                        'hint'         => 'شناسه (slug) دسته‌ها به‌ترتیب نمایش؛ «all» همیشه تب «همه» است.',
                    ]),
                ]],
                ['title' => 'چیدمان و نمایش', 'fields' => [
                    landingField('catalog.defaultLayout', 'چیدمان پیش‌فرض', 'select', ['options' => [
                        ['value' => 'comfort', 'label' => 'راحت (دو ستونی)'],
                        ['value' => 'dense', 'label' => 'فشرده (شبکه‌ای)'],
                        ['value' => 'list', 'label' => 'لیستی'],
                    ]]),
                    landingField('catalog.discountBadgeThreshold', 'حداقل درصد برای نشان تخفیف', 'number', ['min' => 0, 'max' => 100, 'unit' => '٪']),
                    landingField('catalog.defaultColorIndex', 'ایندکس رنگ پیش‌فرض کارت‌ها', 'number', ['min' => 0, 'max' => 12]),
                ]],
                ['title' => 'برچسب‌های مرتب‌سازی و چیدمان', 'fields' => [
                    landingField('catalog.sortPopular', 'برچسب مرتب‌سازی «محبوب»', 'text', ['maxLength' => 30]),
                    landingField('catalog.sortNewest', 'برچسب مرتب‌سازی «جدید»', 'text', ['maxLength' => 30]),
                    landingField('catalog.sortCheap', 'برچسب مرتب‌سازی «ارزان‌ترین»', 'text', ['maxLength' => 30]),
                    landingField('catalog.sortExpensive', 'برچسب مرتب‌سازی «گران‌ترین»', 'text', ['maxLength' => 30]),
                    landingField('catalog.viewComfortLabel', 'عنوان چیدمان بزرگ', 'text', ['maxLength' => 30]),
                    landingField('catalog.viewDenseLabel', 'عنوان چیدمان فشرده', 'text', ['maxLength' => 30]),
                    landingField('catalog.viewListLabel', 'عنوان چیدمان لیستی', 'text', ['maxLength' => 30]),
                    landingField('catalog.clearSearchText', 'برچسب پاک کردن جستجو', 'text', ['maxLength' => 40]),
                    landingField('catalog.productNameTemplate', 'الگوی نام محصول در سبد', 'text', ['maxLength' => 60, 'hint' => 'جای‌گذار: {title}.']),
                ]],
            ],
        ],
        [
            'id' => 'packBuilder', 'path' => 'packBuilder', 'preview' => 'packBuilder', 'toggle' => 'enabled',
            'title' => 'باکس‌ساز هوشمند', 'icon' => 'gift',
            'desc' => 'پک‌های تخفیفی سه‌مرحله‌ای؛ سطوح تخفیف، مزایا و گزینه‌های بسته‌بندی.',
            'groups' => [
                ['title' => 'عنوان و راهنما', 'fields' => [
                    landingField('packBuilder.enabled', 'نمایش این بخش', 'toggle'),
                    landingField('packBuilder.eyebrow', 'برچسب بالای عنوان', 'text', ['maxLength' => 40]),
                    landingField('packBuilder.title', 'عنوان بخش', 'text', ['maxLength' => 90]),
                    landingField('packBuilder.desc', 'توضیح بخش', 'textarea', ['rows' => 3, 'maxLength' => 260]),
                    landingField('packBuilder.step1Heading', 'عنوان مرحله انتخاب محصول', 'text', ['maxLength' => 90]),
                    landingField('packBuilder.autoFillLabel', 'متن دکمه چیدمان خودکار', 'text', ['maxLength' => 40]),
                ]],
                ['title' => 'پک‌ها و سطوح تخفیف', 'fields' => [
                    landingRepeater('packBuilder.tiers', 'پک‌ها', [
                        landingField('title', 'نام پک', 'text', ['maxLength' => 40]),
                        landingField('count', 'تعداد جفت در پک', 'number', ['min' => 1, 'max' => 12]),
                        landingField('discount', 'درصد تخفیف', 'number', ['min' => 0, 'max' => 70, 'unit' => '٪']),
                        landingField('tagline', 'شعار کوتاه', 'text', ['maxLength' => 60]),
                        landingField('perks', 'مزایا', 'list', ['itemType' => 'text', 'max' => 6]),
                        landingField('featured', 'پک پیشنهادی (برجسته)', 'toggle'),
                    ], ['itemLabel' => 'پک', 'max' => 5, 'min' => 1]),
                    landingList('packBuilder.boxOptions', 'گزینه‌های بسته‌بندی', 'text', ['max' => 6]),
                    landingList('packBuilder.sizeOptions', 'گزینه‌های سایز', 'text', ['max' => 6]),
                ]],
                ['title' => 'متن‌های باکس‌ساز', 'fields' => [
                    landingField('packBuilder.capacityText', 'پیام ظرفیت باکس', 'text', ['maxLength' => 120, 'hint' => 'جای‌گذارها: {tier} و {count}.']),
                    landingField('packBuilder.trayText', 'پیام چیدن محصولات در سینی', 'text', ['maxLength' => 120, 'hint' => 'جای‌گذار: {count}.']),
                    landingField('packBuilder.summaryText', 'عنوان سبد خرید پک', 'text', ['maxLength' => 60, 'hint' => 'جای‌گذارها: {tier} و {count}.']),
                    landingField('packBuilder.notePrefix', 'پیشوند متن کارت هدیه', 'text', ['maxLength' => 40, 'hint' => 'جای‌گذار: {note}.']),
                    landingField('packBuilder.notePlaceholder', 'راهنمای فیلد کارت هدیه', 'text', ['maxLength' => 80]),
                    landingField('packBuilder.addButtonText', 'متن دکمه افزودن پک', 'text', ['maxLength' => 50]),
                    landingField('packBuilder.remainingText', 'متن انتخاب‌های باقی‌مانده', 'text', ['maxLength' => 80, 'hint' => 'جای‌گذار: {count}.']),
                    landingField('packBuilder.removeLabel', 'برچسب حذف جفت از باکس', 'text', ['maxLength' => 20]),
                ]],
            ],
        ],
        [
            'id' => 'motionGallery', 'path' => 'motionGallery', 'preview' => 'motionGallery', 'toggle' => 'order',
            'title' => 'گالری حرکت', 'icon' => 'camera',
            'desc' => 'گالری تصاویر با اسکرول افقی و دکمه‌های استایل‌ساز و گردونه شانس.',
            'groups' => [
                ['title' => 'عنوان و متن‌ها', 'fields' => [
                    landingField('motionGallery.eyebrow', 'برچسب بالای عنوان', 'text', ['maxLength' => 40]),
                    landingField('motionGallery.title', 'عنوان بخش', 'text', ['maxLength' => 60]),
                    landingField('motionGallery.desc', 'توضیح بخش', 'textarea', ['rows' => 3, 'maxLength' => 260]),
                    landingField('motionGallery.marqueeText', 'متن نوار متحرک', 'text', ['maxLength' => 60]),
                    landingField('motionGallery.marqueeSubText', 'زیرمتن لاتین نوار', 'text', ['maxLength' => 40, 'dir' => 'ltr']),
                    landingField('motionGallery.ctaStyleQuizText', 'متن دکمه استایل‌ساز', 'text', ['maxLength' => 60]),
                    landingField('motionGallery.ctaLuckyWheelText', 'متن دکمه گردونه شانس', 'text', ['maxLength' => 60]),
                ]],
                ['title' => 'تصاویر گالری', 'fields' => [
                    landingRepeater('motionGallery.images', 'تصاویر', [
                        landingField('img', 'تصویر', 'image'),
                        landingField('caption', 'کپشن', 'text', ['maxLength' => 60]),
                    ], ['itemLabel' => 'تصویر', 'max' => 10]),
                ]],
            ],
        ],
        [
            'id' => 'story', 'path' => 'editorial.story', 'preview' => 'story', 'toggle' => 'order',
            'title' => 'داستان کارگاه', 'icon' => 'book',
            'desc' => 'روایت برند، تصویر کارگاه، پاراگراف‌ها و آمار اعتماد.',
            'groups' => [
                ['title' => 'عنوان و روایت', 'fields' => [
                    landingField('editorial.story.eyebrow', 'برچسب بالای عنوان', 'text', ['maxLength' => 40]),
                    landingField('editorial.story.title', 'عنوان داستان', 'textarea', ['rows' => 2, 'maxLength' => 120, 'hint' => 'برای شکستن خط از Enter استفاده کنید.']),
                    landingField('editorial.story.paragraphs', 'پاراگراف‌ها', 'list', ['itemType' => 'textarea', 'max' => 6]),
                    landingField('editorial.story.image', 'تصویر داستان', 'image'),
                    landingField('editorial.story.marquee', 'متن نوار متحرک', 'text', ['maxLength' => 60]),
                    landingField('editorial.story.marqueeSub', 'زیرمتن لاتین نوار', 'text', ['maxLength' => 40, 'dir' => 'ltr']),
                ]],
                ['title' => 'برچسب‌های روی تصویر', 'fields' => [
                    landingField('editorial.story.locationTitle', 'عنوان مکان کارگاه', 'text', ['maxLength' => 60]),
                    landingField('editorial.story.locationSubtitle', 'زیرعنوان مکان', 'text', ['maxLength' => 60]),
                    landingField('editorial.story.craftTitle', 'عنوان برچسب دوخت', 'text', ['maxLength' => 40]),
                    landingField('editorial.story.ctaLabel', 'متن دکمه بخش', 'text', ['maxLength' => 40]),
                    landingField('editorial.story.ctaLink', 'لینک دکمه بخش', 'link'),
                ]],
                ['title' => 'آمار کارگاه', 'fields' => [
                    landingRepeater('editorial.story.stats', 'آمارها', [
                        landingField('value', 'عدد', 'text', ['maxLength' => 12]),
                        landingField('label', 'برچسب', 'text', ['maxLength' => 40]),
                    ], ['itemLabel' => 'آمار', 'max' => 5]),
                ]],
                ['title' => 'متن جایگزین تصویر کارگاه', 'fields' => [
                    landingField('editorial.story.imageAlt', 'متن جایگزین تصویر کارگاه', 'text', ['maxLength' => 100]),
                ]],
            ],
        ],
        [
            'id' => 'promiseBox', 'path' => 'editorial.promiseBox', 'preview' => 'promiseBox', 'toggle' => 'order',
            'title' => 'باکس ضمانت', 'icon' => 'refresh',
            'desc' => 'قول فروشگاه، شرایط مرجوعی و تضمین دوام بافت.',
            'groups' => [
                ['title' => 'متن ضمانت', 'fields' => [
                    landingField('editorial.promiseBox.enabled', 'نمایش این بخش', 'toggle'),
                    landingField('editorial.promiseBox.eyebrow', 'برچسب بالای عنوان', 'text', ['maxLength' => 60]),
                    landingField('editorial.promiseBox.title', 'عنوان ضمانت', 'text', ['maxLength' => 70]),
                    landingField('editorial.promiseBox.body', 'متن توضیح', 'textarea', ['rows' => 4, 'maxLength' => 400]),
                    landingField('editorial.promiseBox.bullets', 'شرایط کلیدی', 'list', ['itemType' => 'text', 'max' => 6]),
                ]],
                ['title' => 'تمبر و تصویر', 'fields' => [
                    landingField('editorial.promiseBox.stampText', 'متن تمبر', 'text', ['maxLength' => 60]),
                    landingField('editorial.promiseBox.stampIcon', 'آیکون تمبر', 'icon', ['options' => $iconSetOptions('hero')]),
                    landingField('editorial.promiseBox.image', 'تصویر باکس', 'image'),
                    landingField('editorial.promiseBox.ctaLabel', 'متن دکمه', 'text', ['maxLength' => 40]),
                    landingField('editorial.promiseBox.ctaLink', 'لینک دکمه', 'link'),
                    landingField('editorial.promiseBox.bottomMarquee', 'متن نوار پایین', 'text', ['maxLength' => 90]),
                ]],
                ['title' => 'متن جایگزین تصویر باکس هدیه', 'fields' => [
                    landingField('editorial.promiseBox.giftImageAlt', 'متن جایگزین تصویر باکس هدیه', 'text', ['maxLength' => 100]),
                ]],
            ],
        ],
        [
            'id' => 'socialStyle', 'path' => 'socialStyle', 'preview' => 'socialStyle', 'toggle' => 'enabled',
            'title' => 'دیوار استایل کاربران', 'icon' => 'instagram',
            'desc' => 'گالری UGC اینستاگرام و جعبه تشویق مشتری به اشتراک‌گذاری.',
            'groups' => [
                ['title' => 'عنوان و جعبه فراخوان', 'fields' => [
                    landingField('socialStyle.enabled', 'نمایش این بخش', 'toggle'),
                    landingField('socialStyle.eyebrow', 'برچسب بالای عنوان', 'text', ['maxLength' => 40]),
                    landingField('socialStyle.title', 'عنوان بخش', 'text', ['maxLength' => 60]),
                    landingField('socialStyle.desc', 'توضیح بخش', 'textarea', ['rows' => 3, 'maxLength' => 260]),
                    landingField('socialStyle.boxTitle', 'عنوان جعبه فراخوان', 'text', ['maxLength' => 70]),
                    landingField('socialStyle.boxDesc', 'متن جعبه فراخوان', 'textarea', ['rows' => 3, 'maxLength' => 260]),
                    landingField('socialStyle.tagButtonLabel', 'متن دکمه کپی تگ', 'text', ['maxLength' => 40]),
                    landingField('socialStyle.storyCountNote', 'یادداشت تعداد استوری', 'text', ['maxLength' => 60]),
                    landingField('socialStyle.joinTileText', 'متن کاشی «تو هم بیا»', 'text', ['maxLength' => 40]),
                ]],
                ['title' => 'پست‌های شبکه‌های اجتماعی', 'fields' => [
                    landingRepeater('socialStyle.posts', 'پست‌ها', [
                        landingField('img', 'تصویر', 'image'),
                        landingField('handle', 'نام کاربری', 'text', ['maxLength' => 40, 'dir' => 'ltr']),
                    ], ['itemLabel' => 'پست', 'max' => 10]),
                ]],
                ['title' => 'متن‌های تعامل اینستاگرام', 'fields' => [
                    landingField('socialStyle.tagCopiedToast', 'پیام پس از کپی تگ', 'text', ['maxLength' => 120, 'hint' => 'جای‌گذار: {handle}.']),
                    landingField('socialStyle.tagPrompt', 'متن راهنمای تگ', 'text', ['maxLength' => 80, 'hint' => 'جای‌گذار: {handle}.']),
                    landingField('socialStyle.tagCopyLabel', 'برچسب دکمه کپی تگ', 'text', ['maxLength' => 60, 'hint' => 'جای‌گذار: {handle}.']),
                    landingField('socialStyle.postAlt', 'متن جایگزین تصویر پست', 'text', ['maxLength' => 60, 'hint' => 'جای‌گذار: {handle}.']),
                ]],
            ],
        ],
        [
            'id' => 'sizeGuide', 'path' => 'sizeGuide', 'preview' => 'sizeGuide', 'toggle' => 'enabled',
            'title' => 'راهنمای سایز', 'icon' => 'ruler',
            'desc' => 'ابزار پیدا کردن سایز با اسلایدر و جدول تبدیل سایز.',
            'groups' => [
                ['title' => 'عنوان و راهنما', 'fields' => [
                    landingField('sizeGuide.enabled', 'نمایش این بخش', 'toggle'),
                    landingField('sizeGuide.eyebrow', 'برچسب بالای عنوان', 'text', ['maxLength' => 60]),
                    landingField('sizeGuide.title', 'عنوان بخش', 'text', ['maxLength' => 80]),
                    landingField('sizeGuide.desc', 'توضیح بخش', 'textarea', ['rows' => 3, 'maxLength' => 260]),
                    landingField('sizeGuide.smallTip', 'توضیح سایز کوچک', 'textarea', ['rows' => 2, 'maxLength' => 200]),
                    landingField('sizeGuide.largeTip', 'توضیح سایز بزرگ', 'textarea', ['rows' => 2, 'maxLength' => 200]),
                ]],
                ['title' => 'جدول سایز', 'fields' => [
                    landingRepeater('sizeGuide.tableRows', 'ردیف‌های جدول', [
                        landingField('eu', 'سایز اروپایی (EU)', 'text', ['maxLength' => 20]),
                        landingField('cm', 'طول کف پا (cm)', 'text', ['maxLength' => 20]),
                        landingField('ab', 'سایز ای‌بی ساکس', 'text', ['maxLength' => 20]),
                    ], ['itemLabel' => 'ردیف', 'max' => 8]),
                ]],
                ['title' => 'مراحل اندازه‌گیری', 'fields' => [
                    landingField('sizeGuide.steps', 'مراحل راهنمای سایز', 'list', ['itemType' => 'text', 'max' => 6]),
                    landingField('sizeGuide.shoeSizeInputLabel', 'برچسب فیلد شماره کفش', 'text', ['maxLength' => 30]),
                ]],
            ],
        ],
        [
            'id' => 'techTrust', 'path' => 'editorial.techTrust', 'preview' => 'techTrust', 'toggle' => 'order',
            'title' => 'جزئیات فنی و مقایسه', 'icon' => 'cpu',
            'desc' => 'کارت‌های فنی، جدول مقایسه با بازار و نشان‌های اعتماد.',
            'groups' => [
                ['title' => 'عنوان و سرستون‌ها', 'fields' => [
                    landingField('editorial.techTrust.enabled', 'نمایش این بخش', 'toggle'),
                    landingField('editorial.techTrust.eyebrow', 'برچسب بالای عنوان', 'text', ['maxLength' => 40]),
                    landingField('editorial.techTrust.title', 'عنوان بخش', 'text', ['maxLength' => 90]),
                    landingField('editorial.techTrust.desc', 'توضیح بخش', 'textarea', ['rows' => 3, 'maxLength' => 260]),
                    landingField('editorial.techTrust.tableHeaders.label', 'سرستون «جزئیات»', 'text', ['maxLength' => 30]),
                    landingField('editorial.techTrust.tableHeaders.ab', 'سرستون ستون ما', 'text', ['maxLength' => 30]),
                    landingField('editorial.techTrust.tableHeaders.other', 'سرستون ستون رقیب', 'text', ['maxLength' => 30]),
                ]],
                ['title' => 'کارت‌های فنی', 'fields' => [
                    landingRepeater('editorial.techTrust.techCards', 'کارت‌ها', [
                        landingField('icon', 'آیکون', 'icon', ['options' => $iconSetOptions('tech')]),
                        landingField('title', 'عنوان', 'text', ['maxLength' => 60]),
                        landingField('desc', 'توضیح', 'textarea', ['rows' => 2, 'maxLength' => 200]),
                    ], ['itemLabel' => 'کارت', 'max' => 6]),
                ]],
                ['title' => 'جدول مقایسه', 'fields' => [
                    landingRepeater('editorial.techTrust.comparisonRows', 'ردیف‌های مقایسه', [
                        landingField('label', 'ویژگی', 'text', ['maxLength' => 40]),
                        landingField('ab', 'مقدار ای‌بی ساکس', 'text', ['maxLength' => 60]),
                        landingField('other', 'مقدار رقبا', 'text', ['maxLength' => 60]),
                    ], ['itemLabel' => 'ردیف', 'max' => 10]),
                ]],
                ['title' => 'نشان‌های اعتماد', 'fields' => [
                    landingRepeater('editorial.techTrust.trustBadges', 'نشان‌ها', [
                        landingField('icon', 'آیکون', 'icon', ['options' => $iconSetOptions('badge')]),
                        landingField('title', 'عنوان', 'text', ['maxLength' => 30]),
                        landingField('sub', 'زیرعنوان', 'text', ['maxLength' => 60]),
                        landingField('msg', 'پیام هنگام کلیک', 'textarea', ['rows' => 2, 'maxLength' => 200]),
                    ], ['itemLabel' => 'نشان', 'max' => 6]),
                ]],
                ['title' => 'متن‌های جدول مقایسه', 'fields' => [
                    landingField('editorial.techTrust.otherColumnLabel', 'عنوان ستون رقیب', 'text', ['maxLength' => 40]),
                ]],
            ],
        ],
        [
            'id' => 'faq', 'path' => 'editorial.faq', 'preview' => 'faq', 'toggle' => 'order',
            'title' => 'سوالات متداول', 'icon' => 'help',
            'desc' => 'پرسش و پاسخ‌های خرید و جعبه پشتیبانی.',
            'groups' => [
                ['title' => 'عنوان بخش', 'fields' => [
                    landingField('editorial.faq.eyebrow', 'برچسب بالای عنوان', 'text', ['maxLength' => 40]),
                    landingField('editorial.faq.title', 'عنوان بخش', 'text', ['maxLength' => 90]),
                ]],
                ['title' => 'پرسش و پاسخ‌ها', 'fields' => [
                    landingRepeater('editorial.faq.items', 'سوالات', [
                        landingField('q', 'سوال', 'text', ['maxLength' => 120]),
                        landingField('a', 'پاسخ', 'textarea', ['rows' => 3, 'maxLength' => 600]),
                    ], ['itemLabel' => 'سوال', 'max' => 15]),
                ]],
                ['title' => 'جعبه پشتیبانی', 'fields' => [
                    landingField('editorial.faq.supportBox.enabled', 'نمایش جعبه پشتیبانی', 'toggle'),
                    landingField('editorial.faq.supportBox.title', 'عنوان جعبه', 'text', ['maxLength' => 60]),
                    landingField('editorial.faq.supportBox.hours', 'ساعات پاسخ‌گویی', 'text', ['maxLength' => 80]),
                    landingField('editorial.faq.supportBox.responseTime', 'زمان پاسخ‌گویی', 'text', ['maxLength' => 60]),
                    landingField('editorial.faq.supportBox.chatBtnText', 'متن دکمه چت', 'text', ['maxLength' => 30]),
                    landingField('editorial.faq.supportBox.phone', 'شماره تماس', 'text', ['maxLength' => 30, 'dir' => 'ltr']),
                ]],
                ['title' => 'متن‌های پشتیبانی', 'fields' => [
                    landingField('editorial.faq.supportBox.chatUnavailableText', 'پیام چت غیرفعال', 'text', ['maxLength' => 120]),
                ]],
            ],
        ],
        [
            'id' => 'footer', 'path' => 'editorial.footer', 'preview' => 'footer', 'toggle' => null,
            'title' => 'پاورقی و برند', 'icon' => 'layout',
            'desc' => 'کنترل کامل طراحی پاورقی: چیدمان، رنگ‌ها، ستون‌های لینک، نشان‌های اعتماد، خبرنامه و نوار پایین.',
            'groups' => [
                ['title' => 'چیدمان', 'fields' => [
                    landingField('editorial.footer.layoutPreset', 'نوع چیدمان', 'select', ['options' => [
                        ['value' => 'three', 'label' => 'سه‌ستونه (برند | لینک‌ها | خبرنامه)'],
                        ['value' => 'two', 'label' => 'دوستونه'],
                        ['value' => 'stacked', 'label' => 'زیر هم (تک‌ستونه)'],
                    ]]),
                    landingField('editorial.footer.align', 'تراز محتوا', 'select', ['options' => [
                        ['value' => 'start', 'label' => 'راست‌چین'],
                        ['value' => 'center', 'label' => 'وسط‌چین'],
                    ]]),
                    landingField('editorial.footer.contentWidth', 'عرض محتوا', 'range', ['min' => 720, 'max' => 1600, 'step' => 20, 'unit' => 'پیکسل']),
                    landingField('editorial.footer.columnGap', 'فاصله‌ی ستون‌ها', 'range', ['min' => 8, 'max' => 96, 'step' => 2, 'unit' => 'پیکسل']),
                    landingField('editorial.footer.paddingTop', 'فاصله‌ی بالای پاورقی', 'range', ['min' => 0, 'max' => 140, 'step' => 4, 'unit' => 'پیکسل']),
                    landingField('editorial.footer.paddingBottom', 'فاصله‌ی پایین پاورقی', 'range', ['min' => 0, 'max' => 160, 'step' => 4, 'unit' => 'پیکسل']),
                    landingField('editorial.footer.showTopDivider', 'خط جداکننده‌ی بالای نوار پایین', 'toggle'),
                    landingField('editorial.footer.bottomAlign', 'چیدمان نوار پایین', 'select', ['options' => [
                        ['value' => 'split', 'label' => 'دو سر (کپی‌رایت/شعار/دکمه)'],
                        ['value' => 'center', 'label' => 'وسط‌چین'],
                    ]]),
                ]],
                ['title' => 'رنگ و ظاهر', 'fields' => [
                    landingField('editorial.footer.backgroundStyle', 'سبک پس‌زمینه', 'select', ['options' => [
                        ['value' => 'solid', 'label' => 'تک‌رنگ'],
                        ['value' => 'gradient', 'label' => 'گرادیان'],
                        ['value' => 'mesh', 'label' => 'مش نورانی'],
                    ]]),
                    landingField('editorial.footer.backgroundFrom', 'رنگ پس‌زمینه (یا مبدأ گرادیان)', 'color'),
                    landingField('editorial.footer.backgroundTo', 'رنگ دوم گرادیان', 'color', ['showIf' => ['editorial.footer.backgroundStyle' => 'gradient']]),
                    landingField('editorial.footer.textColor', 'رنگ متن', 'color'),
                    landingField('editorial.footer.mutedOpacity', 'شفافیت متن‌های کم‌رنگ', 'range', ['min' => 20, 'max' => 95, 'step' => 2, 'unit' => '٪']),
                    landingField('editorial.footer.accentColor', 'رنگ تأکید (آیکون و نشان)', 'color'),
                    landingField('editorial.footer.buttonColor', 'رنگ دکمه‌ی خبرنامه', 'color'),
                    landingField('editorial.footer.borderColor', 'رنگ خطوط و حاشیه‌ها', 'color'),
                    landingField('editorial.footer.dividerOpacity', 'شفافیت خطوط', 'range', ['min' => 0, 'max' => 100, 'step' => 2, 'unit' => '٪']),
                    landingField('editorial.footer.cardRadius', 'گردی کارت‌ها', 'range', ['min' => 0, 'max' => 32, 'step' => 1, 'unit' => 'پیکسل']),
                    landingField('editorial.footer.decorStyle', 'جلوه‌ی تزئینی', 'select', ['options' => [
                        ['value' => 'none', 'label' => 'بدون جلوه'],
                        ['value' => 'dots', 'label' => 'شبکه‌ی نقطه‌ای'],
                        ['value' => 'glow', 'label' => 'هاله‌ی نورانی'],
                    ]]),
                ]],
                ['title' => 'نمایش بلوک‌ها', 'fields' => [
                    landingField('editorial.footer.showBrand', 'بلوک برند', 'toggle'),
                    landingField('editorial.footer.showSocial', 'آیکون‌های شبکه‌های اجتماعی', 'toggle'),
                    landingField('editorial.footer.showLinks', 'ستون‌های لینک', 'toggle'),
                    landingField('editorial.footer.showNewsletter', 'خبرنامه', 'toggle'),
                    landingField('editorial.footer.showBadges', 'نشان‌های اعتماد', 'toggle'),
                    landingField('editorial.footer.showSlogan', 'شعار لاتین', 'toggle'),
                    landingField('editorial.footer.showCopyright', 'متن کپی‌رایت', 'toggle'),
                    landingField('editorial.footer.showBackToTop', 'دکمه‌ی بازگشت به بالا', 'toggle'),
                    landingField('editorial.footer.backToTopText', 'متن دکمه‌ی بازگشت به بالا', 'text', ['maxLength' => 30]),
                    landingField('editorial.footer.logoSize', 'اندازه‌ی لوگو', 'range', ['min' => 24, 'max' => 64, 'step' => 2, 'unit' => 'پیکسل']),
                    landingField('editorial.footer.socialShape', 'شکل دکمه‌های اجتماعی', 'select', ['options' => [
                        ['value' => 'circle', 'label' => 'دایره'],
                        ['value' => 'square', 'label' => 'مربع گرد'],
                    ]]),
                    landingField('editorial.footer.socialSize', 'اندازه‌ی دکمه‌های اجتماعی', 'range', ['min' => 28, 'max' => 56, 'step' => 2, 'unit' => 'پیکسل']),
                ]],
                ['title' => 'برند', 'fields' => [
                    landingField('editorial.footer.brandTitle', 'عنوان برند', 'text', ['maxLength' => 40]),
                    landingField('editorial.footer.brandSubtitle', 'زیرعنوان لاتین برند', 'text', ['maxLength' => 40, 'dir' => 'ltr']),
                    landingField('editorial.footer.tagline', 'توضیح برند', 'textarea', ['rows' => 4, 'maxLength' => 400]),
                    landingField('editorial.footer.sloganEnglish', 'شعار لاتین', 'text', ['maxLength' => 60, 'dir' => 'ltr']),
                ]],
                ['title' => 'ستون‌های لینک', 'fields' => [
                    landingField('editorial.footer.col1Enabled', 'ستون اول فعال', 'toggle'),
                    landingField('editorial.footer.col1Title', 'عنوان ستون اول', 'text', ['maxLength' => 40]),
                    landingList('editorial.footer.col1Links', 'لینک‌های ستون اول', 'text', ['max' => 8, 'hint' => 'هر خط: برچسب|#مقصد — بدون مقصد یعنی متن ساده.']),
                    landingField('editorial.footer.col2Enabled', 'ستون دوم فعال', 'toggle'),
                    landingField('editorial.footer.col2Title', 'عنوان ستون دوم', 'text', ['maxLength' => 40]),
                    landingList('editorial.footer.col2Links', 'لینک‌های ستون دوم', 'text', ['max' => 8, 'hint' => 'هر خط: برچسب|#مقصد — بدون مقصد یعنی متن ساده.']),
                    landingField('editorial.footer.col3Enabled', 'ستون سوم فعال', 'toggle'),
                    landingField('editorial.footer.col3Title', 'عنوان ستون سوم', 'text', ['maxLength' => 40]),
                    landingList('editorial.footer.col3Links', 'لینک‌های ستون سوم', 'text', ['max' => 8, 'hint' => 'هر خط: برچسب|#مقصد — بدون مقصد یعنی متن ساده.']),
                ]],
                ['title' => 'نشان‌های اعتماد', 'fields' => [
                    landingField('editorial.footer.badgeLayout', 'چیدمان نشان‌ها', 'select', ['options' => [
                        ['value' => 'grid-2', 'label' => 'دو ستون'],
                        ['value' => 'grid-1', 'label' => 'یک ستون'],
                        ['value' => 'row', 'label' => 'یک ردیف'],
                    ]]),
                    landingField('editorial.footer.badgeStyle', 'سبک نشان‌ها', 'select', ['options' => [
                        ['value' => 'soft', 'label' => 'نرم (پس‌زمینه‌ی محو)'],
                        ['value' => 'solid', 'label' => 'پررنگ (رنگ تأکید)'],
                    ]]),
                    landingField('editorial.footer.badge1Enabled', 'نشان ۱ فعال', 'toggle'),
                    landingField('editorial.footer.badge1Icon', 'آیکون نشان ۱', 'icon', ['options' => $iconSetOptions('badge')]),
                    landingField('editorial.footer.trustTitle', 'عنوان نشان ۱ (اینماد)', 'text', ['maxLength' => 30]),
                    landingField('editorial.footer.trustSub', 'زیرعنوان نشان ۱', 'text', ['maxLength' => 30]),
                    landingField('editorial.footer.trustText', 'پیام کلیک نشان ۱', 'text', ['maxLength' => 160]),
                    landingField('editorial.footer.badge2Enabled', 'نشان ۲ فعال', 'toggle'),
                    landingField('editorial.footer.badge2Icon', 'آیکون نشان ۲', 'icon', ['options' => $iconSetOptions('badge')]),
                    landingField('editorial.footer.legalTitle', 'عنوان نشان ۲ (ساماندهی)', 'text', ['maxLength' => 30]),
                    landingField('editorial.footer.legalSub', 'زیرعنوان نشان ۲', 'text', ['maxLength' => 30]),
                    landingField('editorial.footer.legalText', 'پیام کلیک نشان ۲', 'text', ['maxLength' => 160]),
                    landingField('editorial.footer.badge3Enabled', 'نشان ۳ فعال', 'toggle'),
                    landingField('editorial.footer.badge3Icon', 'آیکون نشان ۳', 'icon', ['options' => $iconSetOptions('badge')]),
                    landingField('editorial.footer.gatewayLabel', 'عنوان نشان ۳ (درگاه)', 'text', ['maxLength' => 30]),
                    landingField('editorial.footer.secureTitle', 'زیرعنوان نشان ۳', 'text', ['maxLength' => 30]),
                    landingField('editorial.footer.secureDesc', 'پیام کلیک نشان ۳', 'text', ['maxLength' => 160]),
                    landingField('editorial.footer.badge4Enabled', 'نشان ۴ فعال', 'toggle'),
                    landingField('editorial.footer.badge4Icon', 'آیکون نشان ۴', 'icon', ['options' => $iconSetOptions('badge')]),
                    landingField('editorial.footer.unionLabel', 'عنوان نشان ۴ (اتحادیه)', 'text', ['maxLength' => 40]),
                    landingField('editorial.footer.unionSub', 'زیرعنوان نشان ۴', 'text', ['maxLength' => 30]),
                    landingField('editorial.footer.unionText', 'پیام کلیک نشان ۴', 'text', ['maxLength' => 160]),
                ]],
                ['title' => 'خبرنامه', 'fields' => [
                    landingField('editorial.footer.newsletterStyle', 'سبک فیلد خبرنامه', 'select', ['options' => [
                        ['value' => 'pill', 'label' => 'قرصی (گرد)'],
                        ['value' => 'card', 'label' => 'کارتی (گوشه‌های ملایم)'],
                    ]]),
                    landingField('editorial.footer.newsletterTitle', 'عنوان خبرنامه', 'text', ['maxLength' => 60]),
                    landingField('editorial.footer.newsletterPlaceholder', 'متن راهنمای فیلد', 'text', ['maxLength' => 60]),
                    landingField('editorial.footer.newsletterBtnText', 'متن دکمه عضویت', 'text', ['maxLength' => 30]),
                    landingField('editorial.footer.newsletterNote', 'پیام پس از عضویت', 'text', ['maxLength' => 160]),
                ]],
                ['title' => 'شبکه‌های اجتماعی و نوار پایین', 'fields' => [
                    landingField('editorial.footer.socialLinks.instagram', 'آدرس اینستاگرام', 'text', ['maxLength' => 40, 'dir' => 'ltr']),
                    landingField('editorial.footer.socialLinks.telegram', 'آدرس تلگرام', 'text', ['maxLength' => 40, 'dir' => 'ltr']),
                    landingField('editorial.footer.instagramLabel', 'برچسب اینستاگرام', 'text', ['maxLength' => 30]),
                    landingField('editorial.footer.telegramLabel', 'برچسب تلگرام', 'text', ['maxLength' => 30]),
                    landingField('editorial.footer.copyright', 'متن کپی‌رایت', 'textarea', ['rows' => 2, 'maxLength' => 200]),
                    landingField('editorial.footer.copyrightText', 'کپی‌رایت جایگزین', 'text', ['maxLength' => 160, 'hint' => 'جای‌گذار: {handle}.']),
                ]],
            ],
        ],
        [
            'id' => 'luckyWheel', 'path' => 'luckyWheel', 'preview' => 'luckyWheel', 'toggle' => 'enabled',
            'title' => 'گردونه شانس', 'icon' => 'dice',
            'desc' => 'پنجره گردونه شانس: جایزه‌ها، وزن شانس و کدهای تخفیف.',
            'groups' => [
                ['title' => 'تنظیمات پنجره', 'fields' => [
                    landingField('luckyWheel.enabled', 'فعال بودن گردونه', 'toggle'),
                    landingField('luckyWheel.title', 'عنوان پنجره', 'text', ['maxLength' => 60]),
                    landingField('luckyWheel.subtitle', 'زیرعنوان پنجره', 'text', ['maxLength' => 90]),
                    landingField('luckyWheel.spinButtonLabel', 'متن دکمه چرخاندن', 'text', ['maxLength' => 40]),
                    landingField('luckyWheel.cooldownHours', 'فاصله مجاز بین دو چرخش', 'number', ['min' => 0, 'max' => 720, 'unit' => 'ساعت']),
                    landingField('luckyWheel.winTitle', 'عنوان پیام برنده شدن', 'text', ['maxLength' => 40]),
                    landingField('luckyWheel.winCopyBtn', 'متن دکمه کپی کد', 'text', ['maxLength' => 40]),
                ]],
                ['title' => 'برش‌های گردونه', 'fields' => [
                    landingRepeater('luckyWheel.slices', 'برش‌ها و جایزه‌ها', [
                        landingField('label', 'متن روی برش', 'text', ['maxLength' => 50]),
                        landingField('code', 'کد تخفیف', 'text', ['maxLength' => 24, 'dir' => 'ltr', 'hint' => 'خالی بگذارید تا برش «پوچ» شود.']),
                        landingField('color', 'رنگ برش', 'color'),
                        landingField('weight', 'وزن شانس', 'number', ['min' => 1, 'max' => 20, 'hint' => 'عدد بزرگ‌تر = احتمال بیشتر.']),
                    ], ['itemLabel' => 'برش', 'max' => 12, 'min' => 2]),
                ]],
                ['title' => 'متن‌های گردونه', 'fields' => [
                    landingField('luckyWheel.copiedToast', 'پیام پس از کپی کد جایزه', 'text', ['maxLength' => 120]),
                    landingField('luckyWheel.codeLabel', 'متن نمایش کد جایزه', 'text', ['maxLength' => 80, 'hint' => 'جای‌گذار: {code}.']),
                    landingField('luckyWheel.spinningText', 'متن هنگام چرخش', 'text', ['maxLength' => 40]),
                    landingField('luckyWheel.spinAgainText', 'متن چرخاندن دوباره', 'text', ['maxLength' => 40]),
                ]],
            ],
        ],
        [
            'id' => 'styleMaker', 'path' => 'styleMaker', 'preview' => 'styleMaker', 'toggle' => 'enabled',
            'title' => 'استایل‌ساز هوشمند', 'icon' => 'wand',
            'desc' => 'کوییز سه‌مرحله‌ای پیشنهاد جوراب بر اساس سلیقه مشتری.',
            'groups' => [
                ['title' => 'تنظیمات پنجره', 'fields' => [
                    landingField('styleMaker.enabled', 'فعال بودن استایل‌ساز', 'toggle'),
                    landingField('styleMaker.title', 'عنوان پنجره', 'text', ['maxLength' => 60]),
                    landingField('styleMaker.subtitle', 'زیرعنوان پنجره', 'text', ['maxLength' => 90]),
                    landingField('styleMaker.resultTitle', 'عنوان نتیجه', 'text', ['maxLength' => 90]),
                    landingField('styleMaker.resultAddToCart', 'متن دکمه افزودن به سبد', 'text', ['maxLength' => 30]),
                ]],
                ['title' => 'مراحل کوییز', 'fields' => [
                    landingRepeater('styleMaker.steps', 'مراحل', [
                        landingField('title', 'سوال مرحله', 'text', ['maxLength' => 60]),
                        landingRepeater('options', 'گزینه‌های پاسخ', [
                            landingField('label', 'متن گزینه', 'text', ['maxLength' => 40]),
                            landingField('tag', 'برچسب کوتاه', 'text', ['maxLength' => 20]),
                            landingField('icon', 'آیکون', 'icon', ['options' => $iconSetOptions('quiz')]),
                        ], ['itemLabel' => 'گزینه', 'max' => 6]),
                    ], ['itemLabel' => 'مرحله', 'max' => 6]),
                ]],
                ['title' => 'متن‌های استایل‌ساز', 'fields' => [
                    landingField('styleMaker.reasonTemplate', 'متن دلیل پیشنهاد', 'text', ['maxLength' => 160, 'hint' => 'جای‌گذارها: {tags} و {title}.']),
                    landingField('styleMaker.workshopNote', 'متن پیش‌فرض زیر نتیجه', 'text', ['maxLength' => 120]),
                ]],
            ],
        ],
        [
            'id' => 'trackModal', 'path' => 'trackModal', 'preview' => 'trackModal', 'toggle' => null,
            'title' => 'پیگیری سفارش', 'icon' => 'truck',
            'desc' => 'پنجره رهگیری سفارش و مراحل زمانی ارسال.',
            'groups' => [
                ['title' => 'متن‌های پنجره', 'fields' => [
                    landingField('trackModal.title', 'عنوان پنجره', 'text', ['maxLength' => 60]),
                    landingField('trackModal.subtitle', 'زیرعنوان پنجره', 'text', ['maxLength' => 90]),
                    landingField('trackModal.inputPlaceholder', 'متن راهنمای فیلد کد', 'text', ['maxLength' => 60]),
                    landingField('trackModal.searchButtonText', 'متن دکمه استعلام', 'text', ['maxLength' => 30]),
                    landingField('trackModal.supportPhone', 'شماره پشتیبانی', 'text', ['maxLength' => 30, 'dir' => 'ltr']),
                ]],
                ['title' => 'مراحل رهگیری', 'fields' => [
                    landingRepeater('trackModal.timelineSteps', 'مراحل', [
                        landingField('label', 'عنوان مرحله', 'text', ['maxLength' => 40]),
                        landingField('desc', 'توضیح مرحله', 'text', ['maxLength' => 90]),
                        landingField('icon', 'آیکون', 'icon', ['options' => $iconSetOptions('timeline')]),
                        landingField('state', 'وضعیت', 'select', ['options' => [
                            ['value' => 'done', 'label' => 'انجام شده'],
                            ['value' => 'active', 'label' => 'در حال انجام'],
                            ['value' => 'next', 'label' => 'بعدی'],
                        ]]),
                    ], ['itemLabel' => 'مرحله', 'max' => 8]),
                ]],
                ['title' => 'متن راهنمای پیگیری', 'fields' => [
                    landingField('trackModal.inputHint', 'پیام وقتی کد وارد نشده', 'text', ['maxLength' => 80]),
                ]],
            ],
        ],
        [
            'id' => 'cartDrawer', 'path' => 'cartDrawer', 'preview' => 'cartDrawer', 'toggle' => null,
            'title' => 'سبد خرید', 'icon' => 'cart',
            'desc' => 'کشوی سبد خرید: عنوان، نوار ارسال رایگان و متن‌های دکمه.',
            'groups' => [
                ['title' => 'متن‌ها', 'fields' => [
                    landingField('cartDrawer.title', 'عنوان کشو', 'text', ['maxLength' => 40]),
                    landingField('cartDrawer.announcementText', 'متن نوار ارسال رایگان', 'text', ['maxLength' => 90]),
                    landingField('cartDrawer.freeShippingThreshold', 'سقف ارسال رایگان', 'number', ['min' => 0, 'step' => 50000, 'unit' => 'تومان']),
                    landingField('cartDrawer.discountCode', 'کد تخفیف سبد', 'text', ['maxLength' => 24, 'dir' => 'ltr']),
                    landingField('cartDrawer.emptyTitle', 'متن سبد خالی', 'text', ['maxLength' => 60]),
                    landingField('cartDrawer.emptyButtonText', 'متن دکمه سبد خالی', 'text', ['maxLength' => 40]),
                    landingField('cartDrawer.checkoutButtonText', 'متن دکمه پرداخت', 'text', ['maxLength' => 40]),
                ]],
                ['title' => 'متن‌های سبد خرید', 'fields' => [
                    landingField('cartDrawer.closeLabel', 'برچسب بستن سبد', 'text', ['maxLength' => 30]),
                    landingField('cartDrawer.freeShippingRemaining', 'پیام مانده تا ارسال رایگان', 'text', ['maxLength' => 120, 'hint' => 'جای‌گذار: {amount}.']),
                    landingField('cartDrawer.freeShippingReached', 'پیام رسیدن به ارسال رایگان', 'text', ['maxLength' => 120]),
                    landingField('cartDrawer.colorPrefix', 'پیشوند رنگ', 'text', ['maxLength' => 20]),
                    landingField('cartDrawer.sizePrefix', 'پیشوند سایز', 'text', ['maxLength' => 20]),
                    landingField('cartDrawer.increaseLabel', 'برچسب افزایش تعداد', 'text', ['maxLength' => 20]),
                    landingField('cartDrawer.decreaseLabel', 'برچسب کاهش تعداد', 'text', ['maxLength' => 20]),
                    landingField('cartDrawer.removeLabel', 'برچسب حذف آیتم', 'text', ['maxLength' => 20]),
                ]],
            ],
        ],
        [
            'id' => 'visualEffects', 'path' => 'visualEffects', 'preview' => 'motionGallery', 'toggle' => null,
            'title' => 'جلوه‌های بصری', 'icon' => 'zap',
            'desc' => 'رنگ‌های گالری حرکت، شبکه نقطه‌ای، درخشش محیطی و نوع انیمیشن.',
            'groups' => [
                ['title' => 'گالری حرکت', 'fields' => [
                    landingList('visualEffects.motionGalleryColors', 'پالت رنگ گالری', 'color', ['max' => 8]),
                    landingField('visualEffects.motionGallerySpeed', 'سرعت حرکت', 'range', ['min' => 5, 'max' => 90, 'step' => 1]),
                    landingField('visualEffects.motionGalleryAngle', 'زاویه چیدمان', 'range', ['min' => -8, 'max' => 8, 'step' => 1, 'unit' => 'درجه']),
                ]],
                ['title' => 'جلوه‌های محیطی', 'fields' => [
                    landingField('visualEffects.dotGridDensity', 'تراکم شبکه نقطه‌ای', 'select', ['options' => [
                        ['value' => 'low', 'label' => 'کم'],
                        ['value' => 'medium', 'label' => 'متوسط'],
                        ['value' => 'high', 'label' => 'زیاد'],
                        ['value' => 'disabled', 'label' => 'خاموش'],
                    ]]),
                    landingField('visualEffects.ambientGlowEnabled', 'درخشش محیطی', 'toggle'),
                    landingField('visualEffects.animationStyle', 'نوع انیمیشن', 'select', ['options' => [
                        ['value' => 'spring', 'label' => 'فنری (spring)'],
                        ['value' => 'smooth', 'label' => 'نرم (smooth)'],
                    ]]),
                ]],
            ],
        ],
        [
            'id' => 'pageOrder', 'path' => '', 'preview' => 'hero', 'toggle' => null,
            'title' => 'ترتیب سکشن‌های صفحه', 'icon' => 'list',
            'desc' => 'چیدمان عمودی صفحه اصلی؛ با کشیدن هر ردیف ترتیب را عوض کنید و با کلید کنار آن سکشن را خاموش کنید.',
            'groups' => [
                ['title' => 'ترتیب و نمایش', 'fields' => [
                    landingField('sectionOrder', 'ترتیب سکشن‌ها', 'order', ['hint' => 'سکشن‌های خاموش در صفحه اصلی رندر نمی‌شوند.']),
                ]],
            ],
        ],
    ];

    return $sections;
}

/**
 * Flat map of every editable leaf path => field descriptor (repeaters included).
 * Used by the sanitizer so nothing outside the schema can ever be persisted.
 */
function landingFieldIndex(): array
{
    static $index = null;
    if ($index !== null) {
        return $index;
    }

    $index = [];
    $walk = static function (array $fields, string $prefix) use (&$walk, &$index): void {
        foreach ($fields as $field) {
            $path = $prefix === '' ? (string) $field['path'] : $prefix . '.' . $field['path'];
            $index[$path] = array_merge($field, ['path' => $path]);
            if (($field['type'] ?? '') === 'repeater' && !empty($field['fields'])) {
                $walk($field['fields'], $path);
            }
        }
    };

    foreach (landingStudioSections() as $section) {
        foreach ($section['groups'] as $group) {
            $walk($group['fields'], '');
        }
    }

    return $index;
}

/**
 * Section ids that the storefront can order/disable through sectionOrder.
 */
function landingOrderableSectionIds(): array
{
    $ids = [];
    foreach (landingDefaultConfig()['sectionOrder'] ?? [] as $row) {
        if (!empty($row['id'])) {
            $ids[] = (string) $row['id'];
        }
    }
    return $ids;
}

/**
 * True when an array uses string keys (an object in JSON terms).
 */
function landingIsAssoc(array $value): bool
{
    return $value !== [] && array_keys($value) !== range(0, count($value) - 1);
}

/**
 * Strip markup, control characters and clamp to a sane length.
 */
function landingSanitizeText(string $value, int $maxLength, bool $multiline = false): string
{
    $value = strip_tags($value);
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    // Strip C0/C1 control characters only: ZWNJ (U+200C), RLM and other Unicode
    // format characters are load-bearing in Persian typography and must survive.
    $value = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value);
    $value = $multiline ? trim($value) : trim(preg_replace('/\s+/u', ' ', $value) ?? '');

    return mb_substr($value, 0, max(1, $maxLength));
}

/**
 * Accept only site-relative paths or absolute http(s)/tel/mailto targets.
 */
function landingSanitizeUrl($value, bool $allowEmpty = true): string
{
    $url = trim(strip_tags((string) $value));
    $url = str_replace(["\n", "\r", "\t", ' ', '"', "'"], '', $url);
    if ($url === '') {
        return '';
    }
    if (mb_strlen($url) > 500) {
        $url = mb_substr($url, 0, 500);
    }
    $allowed = preg_match('~^(#|/|https?://|tel:|mailto:)~i', $url) === 1;
    if (!$allowed) {
        return $allowEmpty ? '' : '';
    }

    return $url;
}

/**
 * Coerce a single scalar leaf against its schema descriptor.
 *
 * @param mixed       $default Shipped default value (defines the base type)
 * @param mixed       $input   Raw value coming from the request
 * @param string|null $forced  Force a type (used for primitive list items)
 */
function landingCoerceLeaf(string $path, $default, $input, ?string $forcedType = null)
{
    $field = landingFieldIndex()[$path] ?? [];
    $type = $forcedType ?? ($field['type'] ?? '');

    if ($type === '') {
        if (is_bool($default)) {
            $type = 'toggle';
        } elseif (is_int($default) || is_float($default)) {
            $type = 'number';
        } elseif (is_string($default) && mb_strlen($default) > 120) {
            $type = 'textarea';
        } else {
            $type = 'text';
        }
    }

    if ($input === null) {
        return $default;
    }

    switch ($type) {
        case 'toggle':
            if (is_bool($input)) {
                return $input;
            }
            return in_array(mb_strtolower(trim((string) $input)), ['1', 'true', 'on', 'yes'], true);

        case 'number':
        case 'range':
            if (!is_numeric($input)) {
                return $default;
            }
            $number = is_int($default) ? (int) round((float) $input) : round((float) $input, 3);
            if (isset($field['min']) && $number < $field['min']) {
                $number = is_int($default) ? (int) $field['min'] : (float) $field['min'];
            }
            if (isset($field['max']) && $number > $field['max']) {
                $number = is_int($default) ? (int) $field['max'] : (float) $field['max'];
            }
            return $number;

        case 'select':
        case 'icon':
            $allowed = array_map(static fn ($o) => (string) $o['value'], $field['options'] ?? []);
            $candidate = trim((string) $input);
            return in_array($candidate, $allowed, true) ? $candidate : $default;

        case 'color':
            $candidate = trim((string) $input);
            return preg_match('/^#([0-9a-f]{3}|[0-9a-f]{4}|[0-9a-f]{6}|[0-9a-f]{8})$/i', $candidate) === 1
                ? $candidate
                : $default;

        case 'image':
            $url = landingSanitizeUrl($input);
            return $url !== '' ? $url : (string) $default;

        case 'link':
            return landingSanitizeUrl($input);

        default:
            $maxLength = (int) ($field['maxLength'] ?? ($type === 'textarea' ? 3000 : 400));
            return landingSanitizeText((string) $input, $maxLength, $type === 'textarea');
    }
}

/**
 * Sanitize a list (primitive values) or a repeater (array of objects).
 */
function landingSanitizeCollection(string $path, array $default, $input): array
{
    if (!is_array($input)) {
        return $default;
    }

    $field = landingFieldIndex()[$path] ?? [];
    $max = (int) ($field['max'] ?? 40);
    $items = array_slice(array_values($input), 0, max(1, $max));

    $firstDefault = $default[0] ?? null;
    $objectItems = is_array($firstDefault) && landingIsAssoc($firstDefault);

    if (!$objectItems) {
        $itemType = $field['itemType'] ?? 'text';
        $clean = [];
        foreach ($items as $item) {
            if (is_array($item)) {
                continue;
            }
            $clean[] = landingCoerceLeaf($path . '[]', $firstDefault, $item, $itemType);
        }
        return $clean;
    }

    $lastDefault = $default[count($default) - 1];
    $clean = [];
    foreach ($items as $index => $item) {
        if (!is_array($item)) {
            continue;
        }
        // Per-index template keeps heterogeneous repeaters faithful (e.g. only one
        // pack tier is flagged as "featured", some prize slices have no coupon).
        $template = is_array($default[$index] ?? null) ? $default[$index] : $lastDefault;
        $clean[] = landingSanitizeNode($template, $item, $path);
    }

    return $clean;
}

/**
 * Recursively coerce a payload against the shape of the shipped defaults.
 *
 * @param array $default Default node (defines keys, types and item shape)
 * @param mixed $input   Raw payload node
 */
function landingSanitizeNode(array $default, $input, string $prefix = ''): array
{
    $out = [];
    foreach ($default as $key => $value) {
        $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
        $given = is_array($input) && array_key_exists($key, $input) ? $input[$key] : null;

        if (is_array($value) && landingIsAssoc($value)) {
            $out[$key] = landingSanitizeNode($value, is_array($given) ? $given : [], $path);
        } elseif (is_array($value)) {
            $out[$key] = landingSanitizeCollection($path, $value, $given);
        } else {
            $out[$key] = landingCoerceLeaf($path, $value, $given);
        }
    }

    return $out;
}

/**
 * Public entry point: turn any payload into a safe, complete landing config.
 */
function landingSanitizeConfig(array $input): array
{
    $config = landingSanitizeNode(landingDefaultConfig(), $input);

    // sectionOrder: only known storefront sections survive, relocated labels are
    // re-applied and sections missing from the payload are appended (enabled).
    $known = [];
    foreach (landingDefaultConfig()['sectionOrder'] ?? [] as $row) {
        $known[(string) $row['id']] = (string) $row['label'];
    }

    $order = [];
    foreach ($config['sectionOrder'] ?? [] as $row) {
        $id = (string) ($row['id'] ?? '');
        if (!isset($known[$id]) || isset($order[$id])) {
            continue;
        }
        $order[$id] = [
            'id'      => $id,
            'label'   => $known[$id],
            'enabled' => !empty($row['enabled']),
        ];
    }
    foreach ($known as $id => $label) {
        if (!isset($order[$id])) {
            $order[$id] = ['id' => $id, 'label' => $label, 'enabled' => true];
        }
    }
    $config['sectionOrder'] = array_values($order);

    return $config;
}
