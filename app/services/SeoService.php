<?php
/**
 * SEO & GEO (Generative Engine Optimization) Infrastructure Service.
 *
 * Provides centralized Schema.org JSON-LD generation, 301 URL redirect
 * management, entity fact grounding for AI search engines, and metadata resolvers.
 *
 * Rules:
 *  - Strict layer boundary (Rule 7): all DB queries, JSON transformations and schema structures live here.
 *  - Snapshotting & safe normalization.
 */

// ============================================================
// 1. 301/302 URL Redirect Engine
// ============================================================

/**
 * Check if the current request path matches a registered redirect rule.
 * Returns the target URL and HTTP status code if found, null otherwise.
 */
function seoResolveRedirect(string $requestUri): ?array
{
    // Normalize path: strip query string and trailing slashes for lookup
    $path = strtok($requestUri, '?');
    $path = '/' . trim($path, '/');
    if ($path === '//') {
        $path = '/';
    }

    try {
        $stmt = db()->prepare("SELECT id, target_url, status_code FROM url_redirects WHERE source_url = ? LIMIT 1");
        $stmt->execute([$path]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            // Update hit metrics asynchronously / best effort
            try {
                $up = db()->prepare("UPDATE url_redirects SET hits = hits + 1, last_hit_at = NOW() WHERE id = ?");
                $up->execute([$row['id']]);
            } catch (Throwable $e) {
                // Silently ignore metric update failure
            }

            return [
                'target_url'  => $row['target_url'],
                'status_code' => (int) $row['status_code'],
            ];
        }
    } catch (Throwable $e) {
        // Fallback gracefully if database or table is unavailable
    }

    return null;
}

/**
 * Register a permanent (301) or temporary (302) redirect rule.
 * Protects against infinite loops (source == target).
 */
function seoRegisterRedirect(string $sourceUrl, string $targetUrl, int $statusCode = 301): array
{
    $source = '/' . trim(strtok($sourceUrl, '?'), '/');
    $target = '/' . trim(strtok($targetUrl, '?'), '/');

    if ($source === $target) {
        return ['ok' => false, 'error' => 'آدرس مبدا و مقصد نمی‌توانند یکسان باشند.'];
    }

    if (!in_array($statusCode, [301, 302], true)) {
        $statusCode = 301;
    }

    try {
        $stmt = db()->prepare("INSERT INTO url_redirects (source_url, target_url, status_code) 
                               VALUES (?, ?, ?) 
                               ON DUPLICATE KEY UPDATE target_url = VALUES(target_url), status_code = VALUES(status_code)");
        $stmt->execute([$source, $target, $statusCode]);
        return ['ok' => true, 'error' => ''];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => 'خطا در ثبت ریدایرکت: ' . $e->getMessage()];
    }
}

/**
 * Get all configured redirects for admin workstation.
 */
function seoGetRedirects(int $limit = 100, int $offset = 0): array
{
    try {
        $stmt = db()->prepare("SELECT * FROM url_redirects ORDER BY id DESC LIMIT ? OFFSET ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Delete a redirect rule by ID.
 */
function seoDeleteRedirect(int $id): array
{
    try {
        $stmt = db()->prepare("DELETE FROM url_redirects WHERE id = ?");
        $stmt->execute([$id]);
        return ['ok' => true, 'error' => ''];
    } catch (Throwable $e) {
        return ['ok' => false, 'error' => $e->getMessage()];
    }
}

// ============================================================
// 2. Schema.org JSON-LD Generators (Google & AI Grounding)
// ============================================================

/**
 * Build BreadcrumbList Schema (Google Rich Results requirement).
 * @param array $crumbs Array of ['name' => string, 'url' => string]
 */
function seoBuildBreadcrumbsJsonLd(array $crumbs): array
{
    $items = [];
    $position = 1;
    $baseUrl = rtrim(SITE_URL, '/');

    foreach ($crumbs as $crumb) {
        $url = $crumb['url'] ?? '';
        if ($url !== '' && !preg_match('~^https?://~i', $url)) {
            $url = $baseUrl . '/' . ltrim($url, '/');
        }

        $items[] = [
            '@type'    => 'ListItem',
            'position' => $position++,
            'name'     => (string) ($crumb['name'] ?? ''),
            'item'     => $url,
        ];
    }

    return [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
}

/**
 * Build comprehensive Product Schema with Merchant Listing specifications.
 * Enforces price in IRR (Rial = Toman * 10), stock availability, brand and return policy.
 */
function seoBuildProductJsonLd(array $product, array $variants = [], ?float $finalPrice = null, int $totalStock = 0): array
{
    $baseUrl = rtrim(SITE_URL, '/');
    $productUrl = $baseUrl . '/product/' . rawurlencode($product['slug']);
    
    // Main image
    $imageUrl = '';
    if (!empty($product['image'])) {
        $imageUrl = $baseUrl . UPLOAD_URL . $product['image'];
    } else {
        $imageUrl = $baseUrl . '/assets/img/placeholder-sock.svg';
    }

    $price = $finalPrice !== null ? (float) $finalPrice : (float) effectivePrice($product);
    $priceInRials = (string) ((int) $price * 10);
    $isAvailable = $totalStock > 0;

    $returnDays = (int) getSetting('return_policy_days', '7');
    $shippingDays = (int) getSetting('shipping_lead_time_days', '2');

    $brandName = !empty($product['brand_name']) ? $product['brand_name'] : SITE_NAME;

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $product['name'],
        'url'         => $productUrl,
        'image'       => [$imageUrl],
        'description' => !empty($product['seo_description']) 
            ? $product['seo_description'] 
            : seoCleanText($product['description'] ?? '', 160),
        'sku'         => $product['sku'] ?: ('SKU-' . $product['id']),
        'mpn'         => !empty($product['mpn']) ? $product['mpn'] : ($product['sku'] ?: ('MPN-' . $product['id'])),
        'brand'       => [
            '@type' => 'Brand',
            'name'  => $brandName,
        ],
        'offers'      => [
            '@type'                   => 'Offer',
            'url'                     => $productUrl,
            'priceCurrency'           => 'IRR',
            'price'                   => $priceInRials,
            'priceValidUntil'         => date('Y-12-31', strtotime('+1 year')),
            'itemCondition'           => 'https://schema.org/NewCondition',
            'availability'            => $isAvailable ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'hasMerchantReturnPolicy' => [
                '@type'                   => 'MerchantReturnPolicy',
                'applicableCountry'       => 'IR',
                'returnPolicyCategory'    => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                'merchantReturnDays'      => $returnDays,
                'returnMethod'            => 'https://schema.org/ReturnByMail',
                'returnFees'              => 'https://schema.org/FreeReturn',
            ],
            'shippingDetails'         => [
                '@type'               => 'OfferShippingDetails',
                'shippingRate'        => [
                    '@type'    => 'MonetaryAmount',
                    'value'    => '0',
                    'currency' => 'IRR',
                ],
                'deliveryTime'        => [
                    '@type'        => 'ShippingDeliveryTime',
                    'handlingTime' => [
                        '@type'    => 'QuantitativeValue',
                        'minValue' => 1,
                        'maxValue' => max(1, $shippingDays),
                        'unitCode' => 'd',
                    ],
                ],
            ],
        ],
    ];

    // Google Merchant Center & Schema Barcode (GTIN)
    if (!empty($product['barcode'])) {
        $cleanBarcode = preg_replace('/\D/', '', (string) $product['barcode']);
        if (strlen($cleanBarcode) === 13) {
            $schema['gtin13'] = $cleanBarcode;
        } elseif ($cleanBarcode !== '') {
            $schema['gtin'] = $cleanBarcode;
        }
    }

    // Demographic Target & Pattern Specifications
    if (!empty($product['gender'])) {
        $genderMap = [
            'men'    => 'male',
            'women'  => 'female',
            'unisex' => 'unisex',
            'kids'   => 'kids',
        ];
        $schema['audience'] = [
            '@type'           => 'PeopleAudience',
            'suggestedGender' => $genderMap[$product['gender']] ?? 'unisex',
        ];
    }
    if (!empty($product['pattern'])) {
        $schema['pattern'] = $product['pattern'];
    }

    // Structured Entity grounding for AI and search engines (material specs + physical sock entities)
    $props = [];
    if (!empty($product['cuff_height'])) {
        $props[] = [
            '@type' => 'PropertyValue',
            'name'  => 'ارتفاع ساق',
            'value' => (string) $product['cuff_height'],
        ];
    }
    if (!empty($product['gender'])) {
        $props[] = [
            '@type' => 'PropertyValue',
            'name'  => 'گروه کاربری / جنسیت',
            'value' => (string) $product['gender'],
        ];
    }

    $specs = seoParseJsonList($product['material_specs'] ?? null);
    if (!empty($specs)) {
        foreach ($specs as $key => $val) {
            if ($val !== '' && $val !== null) {
                $props[] = [
                    '@type' => 'PropertyValue',
                    'name'  => (string) $key,
                    'value' => (string) $val,
                ];
            }
        }
    }

    if (!empty($props)) {
        $schema['additionalProperty'] = $props;
    }

    return $schema;
}

/**
 * Build CollectionPage Schema for category and listing pages.
 */
function seoBuildCategoryJsonLd(array $category, array $products = [], array $faqList = []): array
{
    $baseUrl = rtrim(SITE_URL, '/');
    $categoryUrl = $baseUrl . '/category/' . rawurlencode($category['slug']);

    $items = [];
    $pos = 1;
    foreach ($products as $p) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $pos++,
            'url'      => $baseUrl . '/product/' . rawurlencode($p['slug']),
            'name'     => $p['name'],
        ];
    }

    $schema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'CollectionPage',
        'name'        => $category['seo_title'] ?: $category['name'],
        'description' => $category['seo_description'] ?: seoCleanText($category['description'] ?? '', 160),
        'url'         => $categoryUrl,
        'mainEntity'  => [
            '@type'           => 'ItemList',
            'numberOfItems'   => count($items),
            'itemListElement' => $items,
        ],
    ];

    return $schema;
}

/**
 * Build FAQPage Schema.
 * @param array $faqItems Array of ['q' => string, 'a' => string]
 */
function seoBuildFaqJsonLd(array $faqItems): ?array
{
    if (empty($faqItems)) {
        return null;
    }

    $mainEntity = [];
    foreach ($faqItems as $item) {
        $q = trim((string) ($item['q'] ?? $item['question'] ?? ''));
        $a = trim((string) ($item['a'] ?? $item['answer'] ?? ''));
        if ($q !== '' && $a !== '') {
            $mainEntity[] = [
                '@type'          => 'Question',
                'name'           => $q,
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text'  => $a,
                ],
            ];
        }
    }

    if (empty($mainEntity)) {
        return null;
    }

    return [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $mainEntity,
    ];
}

/**
 * Build factual, product-grounded FAQPage Schema for product detail page.
 * Covers washing instructions, elastic durability, fabric shrinkage/pilling, and shoe size fit.
 */
function seoBuildProductFaqJsonLd(array $product, array $materialSpecs = []): ?array
{
    $name = trim((string)($product['name'] ?? 'این محصول'));
    $cuffHeight = $product['cuff_height'] ?? 'crew';

    $cuffMap = [
        'crew'       => 'ساق‌دار',
        'ankle'      => 'مچی',
        'no-show'    => 'کالج (بدون ساق)',
        'knee-high'  => 'بالای زانو',
        'quarter'    => 'نیم‌ساق',
        'tights'     => 'جوراب شلواری',
    ];
    $cuffFa = $cuffMap[$cuffHeight] ?? 'ساق';

    $washCare = $materialSpecs['دستور شستشو'] ?? 'شستشو با آب سرد یا ولرم (حداکثر ۳۰ درجه سانتی‌گراد) با شوینده ملایم، بدون استفاده از مایع سفیدکننده و خشک‌کن حرارتی.';
    $fiberDesc = $materialSpecs['درصد پنبه'] ?? 'نخ‌پنبه طبیعی سوپر شانه‌شده با بالاترین لطافت و تنفس‌پذیری';

    $faqItems = [
        [
            'q' => 'دستور شستشو و دمای مناسب شستشوی ' . $name . ' چیست؟',
            'a' => 'جهت حفظ حداکثر لطافت و عمر مفید کش جوراب، ' . $washCare . ' توصیه می‌شود جوراب‌ها را پشت و رو کرده و در مجاورت هوای آزاد خشک نمایید.',
        ],
        [
            'q' => 'کشسانی و دوام کش قسمت ' . $cuffFa . ' جوراب چگونه است؟',
            'a' => 'در بافت کش و لبه‌های جوراب از کش اسپندکس/لایکرا استاندارد با تراکم دوبل استفاده شده است که مانع از سر خوردن جوراب روی پا شده و در عین حال هیچ‌گونه فشار نامتعارفی به گردش خون ساق پا وارد نمی‌کند.',
        ],
        [
            'q' => 'آیا الیاف این جوراب پس از شستشو دچار آبرفت یا پرزدهی می‌شود؟',
            'a' => 'خیر؛ به دلیل استفاده از ' . $fiberDesc . ' و عملیات تکمیلی تثبیت حرارتی، آبرفت در محدوده استاندارد بافندگی (زیر ۳٪) کنترل شده و در برابر سایش و پرزدهی (Pilling) مقاومت بسیار بالایی دارد.',
        ],
        [
            'q' => 'این جوراب مناسب چه سایز پای کفش است؟',
            'a' => 'این مدل دارای سایزبندی استاندارد فری‌سایز کشسان بوده و برای سایز پای ۳۶ تا ۴۲ کاملاً جذب و منطبق بر قالب ارگونومیک پا است.',
        ],
    ];

    return seoBuildFaqJsonLd($faqItems);
}

/**
 * Build Store & WebSite Schema with Sitelinks SearchBox.
 */
function seoBuildOrganizationJsonLd(): array
{
    $baseUrl = rtrim(SITE_URL, '/');
    $storeName = SITE_NAME;
    $logoUrl = siteLogoUrl() ? ($baseUrl . siteLogoUrl()) : ($baseUrl . '/assets/img/logo.png');

    return [
        '@context' => 'https://schema.org',
        '@graph'   => [
            [
                '@type'            => 'ClothingStore',
                '@id'              => $baseUrl . '/#store',
                'name'             => $storeName,
                'url'              => $baseUrl,
                'logo'             => $logoUrl,
                'image'            => $logoUrl,
                'telephone'        => getSetting('store_phone', '۰۲۱-۹۱۰۰۸۴۲۰'),
                'email'            => getSetting('store_email', 'info@absocks.ir'),
                'priceRange'       => '$$',
                'address'          => [
                    '@type'           => 'PostalAddress',
                    'addressLocality' => 'Tehran',
                    'addressCountry'  => 'IR',
                ],
            ],
            [
                '@type'           => 'WebSite',
                '@id'             => $baseUrl . '/#website',
                'url'             => $baseUrl,
                'name'            => $storeName,
                'publisher'       => ['@id' => $baseUrl . '/#store'],
                'potentialAction' => [
                    '@type'       => 'SearchAction',
                    'target'      => $baseUrl . '/search?q={search_term_string}',
                    'query-input' => 'required name=search_term_string',
                ],
            ],
        ],
    ];
}

// ============================================================
// 3. String & Structured Helpers
// ============================================================

/**
 * Automatically generate semantic, keyword-optimized alt and title attributes
 * for product gallery images combining Persian product title, English slug, and sequential index.
 */
function seoBuildImageAttributes(array $product, int $index = 1, bool $isMain = false): array
{
    $name = trim((string)($product['name'] ?? 'جوراب'));
    $slug = trim((string)($product['slug'] ?? ''));
    $brand = !empty($product['brand_name']) ? $product['brand_name'] : SITE_NAME;

    if ($isMain) {
        return [
            'alt'   => $name . ($slug !== '' ? ' — ' . $slug : ''),
            'title' => $name . ' | تولید شده از نخ‌پنبه سوپر اعلا — ' . $brand,
        ];
    }

    $idxFa = toPersianDigits((string)$index);
    return [
        'alt'   => $name . ' — نمای ' . $idxFa . ($slug !== '' ? ' (' . $slug . '-' . $index . ')' : ''),
        'title' => $name . ' — تصویر گالری شماره ' . $idxFa,
    ];
}

/**
 * Sock domain dictionary for translating Persian product & category terms to English tokens.
 * Single Source of Truth matching client-side dictionary in ab-kit.js.
 */
function seoGetDomainDictionary(): array
{
    static $dict = null;
    if ($dict !== null) {
        return $dict;
    }
    $dict = [
        // Heights & Sock cuts
        'بالای زانو'   => 'over-knee-socks',
        'بالازانو'     => 'over-knee-socks',
        'جوراب شلواری' => 'tights',
        'نیم ساق'      => 'quarter-socks',
        'نیم‌ساق'      => 'quarter-socks',
        'ساق بلند'     => 'crew-socks',
        'ساق‌بلند'     => 'crew-socks',
        'ساق دار'      => 'crew-socks',
        'ساق‌دار'      => 'crew-socks',
        'ساقدار'       => 'crew-socks',
        'بدون درز'     => 'seamless',
        'پنجه گیری'    => 'seamless',
        'پنجه‌گیری'    => 'seamless',
        'راه راه'      => 'striped',
        'راه‌راه'      => 'striped',
        'طرح دار'      => 'patterned',
        'طرح‌دار'      => 'patterned',
        'طرحدار'       => 'patterned',
        'خال خالی'     => 'polka-dot',
        'خال‌خالی'     => 'polka-dot',
        'تا به تا'     => 'mismatched',
        'لنگه به لنگه' => 'mismatched',
        'بچه گانه'     => 'kids',
        'بچگانه'       => 'kids',
        'نخ پنبه'      => 'cotton',
        'نخ‌پنبه'      => 'cotton',
        'پشم مرینوس'   => 'merino-wool',
        'کالج'         => 'no-show-socks',
        'پاپوش'        => 'no-show-socks',
        'مچی'          => 'ankle-socks',
        'شلواری'       => 'tights',
        'ورزشی'        => 'athletic-socks',
        'اسپرت'        => 'athletic',
        'واریس'        => 'compression-socks',
        'فشرده'        => 'compression',
        'حوله ای'      => 'cushioned',
        'حوله‌ای'      => 'cushioned',
        'توری'         => 'fishnet',
        'گیپور'        => 'lace',

        // Fibers / Materials
        'پنبه'         => 'cotton',
        'نخی'          => 'cotton',
        'پنبه‌ای'      => 'cotton',
        'پشم'          => 'wool',
        'پشمی'         => 'wool',
        'مرینوس'       => 'merino',
        'بامبو'        => 'bamboo',
        'اسپندکس'      => 'spandex',
        'لایکرا'       => 'lycra',
        'ابریشم'       => 'silk',
        'ابریشمی'      => 'silk',
        'لمه'          => 'glitter',
        'شاین'         => 'shimmer',

        // Gender / Audience
        'مردانه'       => 'men',
        'زنانه'        => 'women',
        'دخترانه'      => 'girls',
        'پسرانه'       => 'boys',
        'کودک'         => 'kids',
        'نوزادی'       => 'baby',
        'مشترک'        => 'unisex',

        // Patterns & Themes
        'ساده'         => 'solid',
        'چهارخانه'     => 'plaid',
        'شطرنجی'       => 'checkerboard',
        'فانتزی'       => 'novelty',
        'عروسکی'       => 'cartoon',
        'گوزن'         => 'deer',
        'خرسی'         => 'bear',
        'هندسی'        => 'geometric',
        'گلدار'        => 'floral',
        'گل‌دار'       => 'floral',
        'کریسمس'       => 'christmas',
        'یلدا'         => 'yalda',
        'قلب'          => 'heart',
        'ستاره'        => 'star',

        // Colors
        'مشکی'         => 'black',
        'سیاه'         => 'black',
        'سفید'         => 'white',
        'طوسی'         => 'grey',
        'خاکستری'      => 'grey',
        'سرمه ای'      => 'navy',
        'سرمه‌ای'      => 'navy',
        'آبی'          => 'blue',
        'قرمز'         => 'red',
        'سرخ'          => 'red',
        'سبز'          => 'green',
        'زرد'          => 'yellow',
        'قهوه ای'      => 'brown',
        'قهوه‌ای'      => 'brown',
        'کرم'          => 'beige',
        'بژ'           => 'beige',
        'صورتی'        => 'pink',
        'بنفش'         => 'purple',
        'نارنجی'       => 'orange',
        'زرشکی'        => 'maroon',
        'یشمی'         => 'olive',
    ];
    return $dict;
}

/**
 * Stopwords to strip during slug derivation.
 */
function seoGetStopWords(): array
{
    return [
        'جوراب', 'مدل', 'کد', 'نوع', 'یک', 'جفت', 'خرید', 'فروش',
        'با', 'و', 'های', 'ها', 'از', 'در', 'برای', 'ویژه', 'اعلا', 'اصل'
    ];
}

/**
 * Generates an intelligent English slug from Persian title using domain dictionary.
 */
function seoGenerateEnglishSlug(string $persianText): string
{
    $text = trim($persianText);
    if ($text === '') {
        return '';
    }

    // Convert Persian & Arabic digits to English digits
    $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $textWithEnDigits = str_replace($persianDigits, $englishDigits, $text);

    // Normalize characters (Arabic yeh/kaf to Persian)
    $textNormalized = str_replace(['ي', 'ك'], ['ی', 'ک'], $textWithEnDigits);
    $paddedText = ' ' . preg_replace('/\s+/u', ' ', $textNormalized) . ' ';

    // Strip stop words
    foreach (seoGetStopWords() as $sw) {
        $paddedText = preg_replace('/(\s+)' . preg_quote($sw, '/') . '(\s+)/u', ' ', $paddedText);
    }

    $tokens = [];
    $dict = seoGetDomainDictionary();
    foreach ($dict as $fa => $en) {
        $pattern = '/(^|\s)' . preg_quote($fa, '/') . '($|\s)/u';
        if (preg_match($pattern, $paddedText)) {
            $tokens[] = $en;
            $paddedText = preg_replace($pattern, ' ', $paddedText);
        }
    }

    // Extract any existing English words or numbers from original title
    if (preg_match_all('/[a-zA-Z0-9]+/u', $textWithEnDigits, $matches)) {
        foreach ($matches[0] as $token) {
            $lower = strtolower($token);
            if (!in_array($lower, $tokens, true)) {
                $tokens[] = $lower;
            }
        }
    }

    $rawSlug = implode('-', $tokens);
    $slug = sanitizeEnglishSlug($rawSlug);

    if ($slug === '' || ctype_digit($slug)) {
        if ($slug !== '' && ctype_digit($slug)) {
            return 'socks-' . $slug;
        }
        return '';
    }

    return $slug;
}

/**
 * Strict English kebab-case slug sanitizer.
 * Forces lowercase ASCII [a-z0-9-], eliminates duplicate hyphens, and trims edges.
 * Prevents pure numeric slugs by prefixing with 'socks-'.
 */
function sanitizeEnglishSlug(string $slug, string $fallback = ''): string
{
    $slug = trim($slug);

    // Convert Persian & Arabic digits to English digits
    $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹', '٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $slug = str_replace($persianDigits, $englishDigits, $slug);

    $slug = strtolower($slug);
    // Strip percent-encoded sequences
    $slug = preg_replace('/%[0-9a-f]{2}/i', '-', $slug);
    // Replace whitespace, slashes, and underscores with hyphen
    $slug = preg_replace('/[\s_\/\\\\]+/u', '-', $slug);
    // Keep strictly lowercase ASCII letters, digits, and hyphens
    $slug = preg_replace('/[^a-z0-9\-]/', '-', $slug);
    $slug = preg_replace('/-+/', '-', $slug);
    $slug = trim($slug, '-');

    if ($slug === '') {
        $fallback = trim($fallback);
        if ($fallback !== '') {
            $generated = seoGenerateEnglishSlug($fallback);
            if ($generated !== '') {
                return $generated;
            }
        }
        return 'socks-' . time();
    }

    // Prevent pure numeric slug (e.g. "20" or "42")
    if (ctype_digit($slug)) {
        $slug = 'socks-' . $slug;
    }

    return $slug;
}

/**
 * Clean HTML and truncate text safely for meta tags.
 */
function seoCleanText(?string $text, int $maxLen = 160): string
{
    if ($text === null || $text === '') {
        return '';
    }
    $clean = strip_tags($text);
    $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $clean = preg_replace('/\s+/u', ' ', $clean);
    $clean = trim($clean);

    if (mb_strlen($clean) > $maxLen) {
        return mb_substr($clean, 0, $maxLen - 3) . '...';
    }
    return $clean;
}

/**
 * Safely parse JSON or return empty array.
 */
function seoParseJsonList($input): array
{
    if (is_array($input)) {
        return $input;
    }
    if (empty($input) || !is_string($input)) {
        return [];
    }
    $decoded = json_decode($input, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Convert schema array or multiple schemas to escaped JSON-LD script tag.
 */
function seoRenderJsonLd(array $schemas): string
{
    if (empty($schemas)) {
        return '';
    }

    // Filter out null or empty elements
    $cleanSchemas = array_filter($schemas);
    if (empty($cleanSchemas)) {
        return '';
    }

    $out = '';
    foreach ($cleanSchemas as $schema) {
        $encoded = json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $out .= "<script type=\"application/ld+json\">\n" . $encoded . "\n</script>\n";
    }

    return $out;
}

/**
 * Standard Data Analytics & Event Metadata payload (Extensibility Hook for AI/Humans).
 */
function seoGetAnalyticsMeta(string $pageType, array $context = []): array
{
    return [
        'page_type'    => $pageType,
        'app_version'  => APP_VERSION,
        'currency'     => 'IRT',
        'locale'       => 'fa-IR',
        'timestamp'    => time(),
        'entity_data'  => $context,
    ];
}

/**
 * Render structured Markdown for /llms.txt and /llms-full.txt (GEO standard).
 */
function seoRenderLlmsText(bool $isFull = false): string
{
    $baseUrl = rtrim(SITE_URL, '/');
    $storeName = SITE_NAME;

    $catStmt = db()->query("SELECT name, slug, description, seo_description, seo_guide FROM categories WHERE is_active = 1 ORDER BY sort_order ASC");
    $categories = $catStmt ? $catStmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $prodLimit = $isFull ? 100 : 25;
    $prodStmt = db()->query("SELECT p.id, p.name, p.slug, p.description, p.seo_description, p.price, p.discount_price, p.stock, p.sku, p.material_specs, c.name AS cat_name
                             FROM products p
                             JOIN categories c ON c.id = p.category_id
                             WHERE p.is_active = 1
                             ORDER BY p.is_featured DESC, p.created_at DESC
                             LIMIT $prodLimit");
    $products = $prodStmt ? $prodStmt->fetchAll(PDO::FETCH_ASSOC) : [];

    $phone = getSetting('store_phone', '۰۲۱-۹۱۰۰۸۴۲۰');
    $email = getSetting('store_email', 'info@absocks.ir');
    $returnDays = getSetting('return_policy_days', '7');
    $shippingDays = getSetting('shipping_lead_time_days', '2');

    $out = "# {$storeName} — AI Knowledge & Catalog Directory\n\n";
    $out .= "> فروشگاه تخصصی آنلاین جوراب‌های باکیفیت و خاص؛ تولید شده از نخ‌پنبه سوپر شانه‌شده با بالاترین استانداردهای دوام و لطافت در ایران.\n\n";

    $out .= "## مشخصات برند و هویت تجاری (Brand Identity)\n";
    $out .= "- **نام تجاری**: {$storeName}\n";
    $out .= "- **آدرس وب‌سایت رسمی**: {$baseUrl}\n";
    $out .= "- **محصولات محوری**: جوراب‌های ساق‌بلند، ساق‌کوتاه، مچی، کالج، ورزشی، زنانه، مردانه و فانتزی\n";
    $out .= "- **متریال شاخص**: ۱۰۰٪ نخ‌پنبه طبیعی شانه‌شده (Super Combed Cotton)، بافت کش‌بافتی ضدحساسیت و بدون فشار به گردش خون ساق پا\n";
    $out .= "- **پشتیبانی مشتریان**: تلفن {$phone} | ایمیل {$email}\n";
    $out .= "- **ارسال**: پست پیشتاز سراسر کشور (مدت آماده‌سازی: ۱ الی {$shippingDays} روز کاری)\n";
    $out .= "- **ضمانت و مرجوعی**: {$returnDays} روز ضمانت اصالت و امکان بازگشت کالا در صورت باز نشدن پلمب بهداشتی\n\n";

    $out .= "## دسته‌بندی‌های اصلی (Catalog Categories)\n";
    foreach ($categories as $cat) {
        $catUrl = "{$baseUrl}/category/" . rawurlencode($cat['slug']);
        $desc = $cat['seo_description'] ?: $cat['description'] ?: 'مجموعه متنوع جوراب‌های تخصصی';
        $out .= "- [{$cat['name']}]({$catUrl}): {$desc}\n";
    }
    $out .= "\n";

    $out .= "## محصولات شاخص و مشخصات الیاف (Featured Products & Specs)\n";
    foreach ($products as $p) {
        $pUrl = "{$baseUrl}/product/" . rawurlencode($p['slug']);
        $price = number_format((float)($p['discount_price'] ?: $p['price']));
        $desc = $p['seo_description'] ?: seoCleanText($p['description'] ?? '', 120);
        $specs = seoParseJsonList($p['material_specs'] ?? null);

        $out .= "### [{$p['name']}]({$pUrl})\n";
        $out .= "- **دسته‌بندی**: {$p['cat_name']}\n";
        $out .= "- **قیمت**: {$price} تومان\n";
        $out .= "- **کد کالا**: {$p['sku']}\n";
        $out .= "- **وضعیت موجودی**: " . ($p['stock'] > 0 ? 'موجود و آماده ارسال' : 'ناموجود') . "\n";
        if (!empty($specs)) {
            $specParts = [];
            foreach ($specs as $sk => $sv) {
                $specParts[] = "{$sk}: {$sv}";
            }
            $out .= "- **مشخصات بافت**: " . implode(' | ', $specParts) . "\n";
        }
        $out .= "- **توضیحات**: {$desc}\n\n";
    }

    $out .= "## راهنمای سایز و نگهداری جوراب (Size & Care Guide)\n";
    $out .= "- **سایزبندی فری‌سایز (Free Size)**: مناسب سایز پای ۳۶ تا ۴۲ با کشسانی استاندارد الیاف لایکرا بدون تغییر فرم\n";
    $out .= "- **سایزبندی بزرگ‌پا (Plus Size)**: مناسب سایز ۴۲ تا ۴۶ با تقویت مضاعف پاشنه و پنجه\n";
    $out .= "- **دستورالعمل شستشو**: شستشو با آب ولرم (حداکثر ۳۰ درجه سانتی‌گراد)، استفاده از شوینده‌های ملایم، خودداری از خشک‌کن حرارتی و اتوکشی مستقیم\n\n";

    $out .= "## دسترسی خزنده‌ها و سایت‌مپ (Crawlers & Sitemaps)\n";
    $out .= "- Sitemap XML: {$baseUrl}/sitemap.xml\n";
    $out .= "- Full LLMs Catalog: {$baseUrl}/llms-full.txt\n";
    $out .= "- Contact & Support: {$baseUrl}/contact\n";

    return $out;
}
