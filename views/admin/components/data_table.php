<?php
/**
 * Data Table Presentation Component (views/admin/components/data_table.php)
 * Pure presentation component: 0 SQL, 0 $_POST (Rule 7).
 *
 * Implements Section 3.7 specifications:
 * - Stack and scroll modes via data-mode attribute
 * - Responsive column prioritization (data-priority 1, 2, 3)
 * - Automatic data-label generation on cells for mobile card transform
 * - Cell formatters (text, number, price, date, badge, link, actions, html)
 * - Row attributes callback (data-id, data-search for AB.tableFilter)
 * - Empty state integration
 *
 * @var string            $id          Table element ID
 * @var string|null       $mode        'stack' (default) or 'scroll'
 * @var array             $columns     Array of column definitions:
 *                                     [['key' => string, 'label' => string, 'priority' => 1|2|3, 'type' => string, 'align' => string]]
 * @var array             $rows        Array of associative row arrays
 * @var callable|null     $row_attrs   Optional fn($row): array of attributes
 * @var array|null        $cell        Optional array of key => fn($row, $val): string
 * @var array|null        $empty       Empty state config ['title' => string, 'message' => string, 'icon' => string]
 * @var bool|null         $sticky_head Sticky table header (default true)
 * @var bool|null         $selectable  Show row selection checkboxes (default false)
 * @var string|null       $class       Extra table class
 */

$tblId = $id ?? 'dataTable_' . bin2hex(random_bytes(3));
$tblMode = $mode ?? 'stack';
$cols = $columns ?? [];
$dataRows = $rows ?? [];
$isSticky = ($sticky_head ?? true);
$isSelectable = !empty($selectable);

if (empty($dataRows)) {
    component('empty_state', array_merge([
        'title'   => 'هیچ موردی یافت نشد',
        'message' => 'اطلاعاتی برای نمایش در این جدول وجود ندارد.',
    ], $empty ?? []));
    return;
}

$tblClasses = ['ab-table'];
if (!empty($class)) $tblClasses[] = e($class);
?>
<div class="ab-table-wrap" data-mode="<?= e($tblMode) ?>">
    <table class="<?= implode(' ', $tblClasses) ?>" id="<?= e($tblId) ?>">
        <thead>
            <tr>
                <?php if ($isSelectable): ?>
                    <th class="ab-table__col-select" data-priority="1">
                        <label class="ab-check">
                            <input type="checkbox" data-ab-select-all="#<?= e($tblId) ?>" aria-label="انتخاب همه">
                        </label>
                    </th>
                <?php endif; ?>

                <?php foreach ($cols as $col): ?>
                    <?php
                    $prio = (int)($col['priority'] ?? 1);
                    $align = $col['align'] ?? 'start';
                    $alignClass = ($align !== 'start') ? ' ab-text-' . e($align) : '';
                    ?>
                    <th data-priority="<?= $prio ?>" data-col="<?= e($col['key']) ?>" class="<?= $alignClass ?>">
                        <?= e($col['label']) ?>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataRows as $r): ?>
                <?php
                $rowCustomAttrs = '';
                if (!empty($row_attrs) && is_callable($row_attrs)) {
                    $attrList = $row_attrs($r);
                    if (is_array($attrList)) {
                        foreach ($attrList as $aKey => $aVal) {
                            $rowCustomAttrs .= ' ' . e((string)$aKey) . '="' . e((string)$aVal) . '"';
                        }
                    }
                }
                ?>
                <tr<?= $rowCustomAttrs ?>>
                    <?php if ($isSelectable): ?>
                        <td class="ab-table__col-select" data-priority="1" data-label="انتخاب">
                            <label class="ab-check">
                                <input type="checkbox" name="selected_ids[]" value="<?= e((string)($r['id'] ?? '')) ?>" aria-label="انتخاب این ردیف">
                            </label>
                        </td>
                    <?php endif; ?>

                    <?php foreach ($cols as $col): ?>
                        <?php
                        $key = $col['key'];
                        $label = $col['label'];
                        $prio = (int)($col['priority'] ?? 1);
                        $colType = $col['type'] ?? 'text';
                        $align = $col['align'] ?? 'start';
                        $val = $r[$key] ?? null;

                        $alignClass = ($align !== 'start') ? ' ab-text-' . e($align) : '';

                        // Render cell content
                        $renderedCell = '';
                        if (!empty($cell[$key]) && is_callable($cell[$key])) {
                            $renderedCell = $cell[$key]($r, $val);
                        } else {
                            switch ($colType) {
                                case 'price':
                                    $amount = (float)($val ?? 0);
                                    $renderedCell = '<span class="ab-num font-mono">' . toPersianDigits(number_format($amount)) . '</span> <small class="text-muted">تومان</small>';
                                    break;

                                case 'number':
                                    $renderedCell = '<span class="ab-num font-mono">' . toPersianDigits((string)($val ?? '0')) . '</span>';
                                    break;

                                case 'badge':
                                    if (is_array($val)) {
                                        $renderedCell = render_component('badge', $val);
                                    } elseif ($val !== null && $val !== '') {
                                        $renderedCell = render_component('badge', ['label' => (string)$val, 'state' => 'info']);
                                    }
                                    break;

                                case 'date':
                                    $renderedCell = '<span class="ab-num">' . e((string)($val ?? '—')) . '</span>';
                                    break;

                                case 'link':
                                    if (is_array($val)) {
                                        $renderedCell = '<a href="' . e($val['url'] ?? '#') . '" class="ab-link">' . e($val['label'] ?? '') . '</a>';
                                    } else {
                                        $renderedCell = '<a href="#" class="ab-link">' . e((string)$val) . '</a>';
                                    }
                                    break;

                                case 'actions':
                                    $renderedCell = (string)$val;
                                    break;

                                case 'html':
                                    $renderedCell = (string)$val;
                                    break;

                                case 'text':
                                default:
                                    $renderedCell = e((string)($val ?? '—'));
                                    break;
                            }
                        }
                        ?>
                        <td data-priority="<?= $prio ?>" data-label="<?= e($label) ?>" class="<?= $alignClass ?>">
                            <?= $renderedCell ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
