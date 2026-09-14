<?php
$pageTitle = 'قوانین و مقررات';
$termsContent = getSiteContent('terms_content');
renderView('site/terms', compact('pageTitle', 'termsContent'));
