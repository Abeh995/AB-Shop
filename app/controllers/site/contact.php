<?php
$pageTitle = 'تماس با ما';
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    // Phase 1: display a success message only; email delivery can be added in a later phase.
    $sent = true;
}

renderView('site/contact', compact('pageTitle', 'sent'));
