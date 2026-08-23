<?php
$pageTitle = 'تماس با ما';
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $sent = true;
}

renderView('site/contact', compact('pageTitle', 'sent'));
