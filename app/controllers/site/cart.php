<?php
/**
 * Cart page controller
 */

$pageTitle = 'سبد خرید';
$workspace = getStorefrontCartWorkspaceData();

renderView('site/cart', array_merge(['pageTitle' => $pageTitle], $workspace));
