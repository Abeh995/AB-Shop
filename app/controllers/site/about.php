<?php
$pageTitle = 'درباره ما';
$aboutContent = getSiteContent('about_content');
$storeEmail = getSiteContent('store_email');
$storePhone = getSiteContent('store_phone');
$storeMobile = getSiteContent('store_mobile');
$storeAddress = getSiteContent('store_address');
$storePostalCode = getSiteContent('store_postal_code');
$storeSupportHours = getSiteContent('store_support_hours');
$storeStartDate = getSiteContent('store_start_date');

renderView('site/about', compact(
    'pageTitle', 'aboutContent', 'storeEmail', 'storePhone', 'storeMobile',
    'storeAddress', 'storePostalCode', 'storeSupportHours', 'storeStartDate'
));
