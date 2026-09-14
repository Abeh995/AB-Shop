<?php
$pageTitle = 'حریم خصوصی و امنیت';
$privacyContent = getSiteContent('privacy_content');
renderView('site/privacy', compact('pageTitle', 'privacyContent'));
