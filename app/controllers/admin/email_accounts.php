<?php
/**
 * Legacy email accounts route: redirects to unified Email Studio accounts tab.
 */
redirect('/admin/emails.php?tab=accounts' . (!empty($_GET['edit']) ? '&edit_account=' . (int)$_GET['edit'] : ''));
