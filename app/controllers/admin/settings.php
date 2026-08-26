<?php
/**
 * Store settings — currently includes the cart price guarantee.
 */

$pageTitle = 'تنظیمات فروشگاه';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $enabled = isset($_POST['price_guarantee_enabled']) ? '1' : '0';
    $days = max(1, (int) ($_POST['price_guarantee_days'] ?? 7));

    setSetting('price_guarantee_enabled', $enabled);
    setSetting('price_guarantee_days', (string) $days);

    setFlash('success', 'تنظیمات ذخیره شد.');
    redirect('settings.php');
}

$priceGuaranteeEnabled = getSetting('price_guarantee_enabled', '1') === '1';
$priceGuaranteeDays = (int) getSetting('price_guarantee_days', '7');

renderView('admin/settings', compact('pageTitle', 'priceGuaranteeEnabled', 'priceGuaranteeDays'));
