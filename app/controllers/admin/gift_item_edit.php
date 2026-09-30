<?php
/**
 * Gift Item Edit & Create Controller
 * Seamlessly routes to the unified dual-pane Master-Detail workstation.
 */

$id = (int) ($_GET['id'] ?? 0);
if ($id > 0) {
    redirect('gift_items.php?edit=' . $id);
} else {
    redirect('gift_items.php');
}
