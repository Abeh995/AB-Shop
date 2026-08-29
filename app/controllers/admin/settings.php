<?php
/**
 * Store settings, including cart price guarantees, tag display, and Google indexing controls.
 */

$pageTitle = 'تنظیمات فروشگاه';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $enabled = isset($_POST['price_guarantee_enabled']) ? '1' : '0';
    $days = max(1, (int) ($_POST['price_guarantee_days'] ?? 7));
    setSetting('price_guarantee_enabled', $enabled);
    setSetting('price_guarantee_days', (string) $days);

    setSetting('show_product_tags', isset($_POST['show_product_tags']) ? '1' : '0');
    setSetting('seo_indexing_enabled', isset($_POST['seo_indexing_enabled']) ? '1' : '0');

    setFlash('success', 'تنظیمات ذخیره شد.');
    redirect('settings.php');
}

$priceGuaranteeEnabled = getSetting('price_guarantee_enabled', '1') === '1';
$priceGuaranteeDays = (int) getSetting('price_guarantee_days', '7');
$showProductTags = getSetting('show_product_tags', '1') === '1';
$seoIndexingEnabled = getSetting('seo_indexing_enabled', '0') === '1';

renderView('admin/settings', compact('pageTitle', 'priceGuaranteeEnabled', 'priceGuaranteeDays', 'showProductTags', 'seoIndexingEnabled'));
