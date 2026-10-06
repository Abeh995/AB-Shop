<?php
/**
 * Toolbar & Filtering Presentation Component (views/admin/components/toolbar.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * @var array|null        $search   ['name' => 'q', 'placeholder' => '...', 'value' => '', 'target' => '#myTable']
 * @var array|null        $filters  Array of form_field props or raw HTML strings
 * @var array|null        $actions  Array of button props or raw HTML strings
 * @var array|null        $bulk     Array of bulk actions: ['options' => [...], 'target' => '#myTable']
 * @var string|null       $class    Extra toolbar class
 * @var string|null       $id       Toolbar ID
 */

$extraClass = !empty($class) ? ' ' . e($class) : '';
$idAttr = !empty($id) ? ' id="' . e($id) . '"' : '';
?>
<div class="ab-toolbar<?= $extraClass ?>"<?= $idAttr ?>>
    <div class="ab-toolbar__lead">
        <?php if (!empty($search)): ?>
            <div class="ab-toolbar__search">
                <input type="search"
                       class="ab-input ab-input--search"
                       name="<?= e($search['name'] ?? 'q') ?>"
                       placeholder="<?= e($search['placeholder'] ?? 'جست‌وجو در لیست...') ?>"
                       value="<?= e($search['value'] ?? '') ?>"
                       <?= !empty($search['target']) ? ' data-ab-filter-target="' . e($search['target']) . '"' : '' ?>
                       autocomplete="off"
                       aria-label="<?= e($search['placeholder'] ?? 'جست‌وجو') ?>">
            </div>
        <?php endif; ?>

        <?php if (!empty($filters)): ?>
            <div class="ab-toolbar__filters">
                <?php foreach ($filters as $f): ?>
                    <?php if (is_string($f)): echo $f; elseif (is_array($f)): component('form_field', $f); endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($bulk) && !empty($bulk['options'])): ?>
            <div class="ab-toolbar__bulk">
                <select class="ab-select ab-select--bulk" name="bulk_action" data-bulk-target="<?= e($bulk['target'] ?? '') ?>" aria-label="عملیات گروهی">
                    <option value="">عملیات گروهی...</option>
                    <?php foreach ($bulk['options'] as $bVal => $bLabel): ?>
                        <option value="<?= e((string)$bVal) ?>"><?= e((string)$bLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($actions)): ?>
        <div class="ab-toolbar__actions">
            <?php foreach ($actions as $act): ?>
                <?php if (is_string($act)): echo $act; else: component('button', $act); endif; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
