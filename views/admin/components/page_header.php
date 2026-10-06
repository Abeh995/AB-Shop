<?php
/**
 * Page Header Presentation Component (views/admin/components/page_header.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var string            $title       Page title (headline)
 * @var string|null       $subtitle    Page subtitle
 * @var string|null       $icon        SVG icon markup
 * @var array|null        $actions     Array of button props or raw HTML
 * @var array|null        $breadcrumbs Array of ['label' => string, 'url' => string|null]
 * @var string|null       $tone        Optional data-tone override
 * @var string|null       $class       Extra CSS class
 * @var string|null       $id          Element ID
 */

$extraClass = !empty($class) ? ' ' . e($class) : '';
$idAttr = !empty($id) ? ' id="' . e($id) . '"' : '';
$toneAttr = !empty($tone) ? ' data-tone="' . e($tone) . '"' : '';
?>
<header class="ab-page-header<?= $extraClass ?>"<?= $idAttr ?><?= $toneAttr ?>>
    <?php if (!empty($breadcrumbs)): ?>
        <nav class="ab-breadcrumbs" aria-label="مسیر ناوبری صفحه">
            <?php foreach ($breadcrumbs as $i => $bc): ?>
                <?php if ($i > 0): ?>
                    <span class="ab-breadcrumbs__sep">/</span>
                <?php endif; ?>
                <?php if (!empty($bc['url'])): ?>
                    <a href="<?= e($bc['url']) ?>" class="ab-breadcrumbs__link"><?= e($bc['label']) ?></a>
                <?php else: ?>
                    <span class="ab-breadcrumbs__current"><?= e($bc['label']) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <div class="ab-page-header__main">
        <div class="ab-page-header__lead">
            <?php if (!empty($icon)): ?>
                <div class="ab-page-header__icon">
                    <?= $icon ?>
                </div>
            <?php endif; ?>
            <div class="ab-page-header__titles">
                <h1 class="ab-page-header__title"><?= e($title) ?></h1>
                <?php if (!empty($subtitle)): ?>
                    <p class="ab-page-header__subtitle"><?= e($subtitle) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($actions)): ?>
            <div class="ab-page-header__actions">
                <?php foreach ($actions as $act): ?>
                    <?php if (is_string($act)): echo $act; else: component('button', $act); endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</header>
