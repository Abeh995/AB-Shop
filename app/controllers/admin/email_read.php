<?php
/**
 * Legacy email read route: redirects to unified Email Studio inbox reader.
 */
$acc = (int)($_GET['account'] ?? 0);
$uid = (int)($_GET['uid'] ?? 0);
redirect('/admin/emails.php?account=' . $acc . '&view=' . $uid);
