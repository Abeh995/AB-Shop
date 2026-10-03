<?php
/**
 * Legacy email compose route: redirects to unified Email Studio compose modal.
 */
$acc = (int)($_GET['account'] ?? 0);
redirect('/admin/emails.php?account=' . $acc . '&compose=1');
