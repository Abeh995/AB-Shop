<?php
$pageTitle = 'تماس با ما';
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    // Phase 1: just show a success message. Wiring this up to an email/SMS
    // notification can be added in a later phase.
    $sent = true;
}

$storePhone = getSetting('store_phone', '');

renderView('site/contact', compact('pageTitle', 'sent', 'storePhone'));
