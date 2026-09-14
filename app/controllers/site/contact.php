<?php
$pageTitle = 'تماس با ما';
$contactIntro = getSiteContent('contact_intro');
$storeEmail = getSiteContent('store_email');
$storePhone = getSiteContent('store_phone');
$storeMobile = getSiteContent('store_mobile');
$storeAddress = getSiteContent('store_address');
$storePostalCode = getSiteContent('store_postal_code');
$storeSupportHours = getSiteContent('store_support_hours');
$contactFormAvailable = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    setFlash('error', 'ارسال فرم تماس هنوز فعال نشده است. لطفاً از راه‌های ارتباطی درج‌شده در همین صفحه استفاده کنید.');
    redirect('/contact');
}

renderView('site/contact', compact(
    'pageTitle', 'contactIntro', 'storeEmail', 'storePhone', 'storeMobile',
    'storeAddress', 'storePostalCode', 'storeSupportHours', 'contactFormAvailable'
));
