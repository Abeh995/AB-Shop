<?php
/**
 * Notifications Log Controller — Forwarder to Unified Diagnostic Studio
 *
 * Keeps backwards compatibility with existing bookmarks and links while
 * routing all log viewing into the unified Diagnostic Studio (Rule 7).
 */

requireSuperAdmin();

$type = ($_GET['tab'] ?? '') === 'email' ? 'email' : 'sms';
redirect('diagnostics.php?tab=notifications&type=' . $type . '#notifications');
