<?php
/**
 * AB-Socks SMS Patterns — High-Density Pattern Matrix Table
 * @var array $patterns
 * @var array $availableEvents
 */
?>
<div class="sms-table-card">
    <div class="table-responsive" style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:65px; text-align:center;">شناسه</th>
                    <th style="width:140px;">کد پترن (Faraz)</th>
                    <th>عنوان الگو و توضیحات</th>
                    <th>رویداد متناظر سیستمی</th>
                    <th>متغیرها و نگاشت داده</th>
                    <th style="width:90px; text-align:center;">وضعیت</th>
                    <th style="width:150px; text-align:center;">عملیات</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($patterns as $p): 
                $cfg = json_decode($p['variables_config'] ?? '[]', true) ?: [];
                $isUnset = empty($p['pattern_code']) || $p['pattern_code'] === 'unset';
                $category = getSmsEventCategory($p['event_key'] ?? '');
                
                // Build search text
                $varNames = array_map(function($v) { return $v['name'] ?? ''; }, $cfg);
                $searchStr = $p['pattern_code'] . ' ' . $p['title'] . ' ' . ($p['event_key'] ?? '') . ' ' . implode(' ', $varNames);
            ?>
            <tr class="sms-pattern-row" 
                data-category="<?= e($category) ?>" 
                data-unset="<?= $isUnset ? '1' : '0' ?>"
                data-search="<?= e($searchStr) ?>">
                <!-- 1. ID -->
                <td style="text-align:center;">
                    <code style="font-size:0.85rem; color:var(--sms-muted);">#<?= (int)$p['id'] ?></code>
                </td>

                <!-- 2. Pattern Code -->
                <td>
                    <?php if ($isUnset): ?>
                        <span class="pattern-code-unset">
                            <span>⚠️</span> تنظیم‌نشده
                        </span>
                    <?php else: ?>
                        <span class="pattern-code-badge" title="کد ثبت شده در فراز اس‌ام‌اس">
                            <?= e($p['pattern_code']) ?>
                        </span>
                    <?php endif; ?>
                </td>

                <!-- 3. Title & Description -->
                <td>
                    <div style="font-weight:700; font-size:0.92rem; color:var(--color-text, #0f172a);">
                        <?= e($p['title']) ?>
                    </div>
                    <?php if (!empty($p['description'])): ?>
                        <div style="font-size:0.77rem; color:var(--sms-muted); margin-top:2px;">
                            <?= e($p['description']) ?>
                        </div>
                    <?php endif; ?>
                </td>

                <!-- 4. Event Badge -->
                <td>
                    <?php if (!empty($p['event_key']) && isset($availableEvents[$p['event_key']])): ?>
                        <span class="event-badge <?= e($category) ?>">
                            <?= e($availableEvents[$p['event_key']]) ?>
                        </span>
                    <?php elseif (!empty($p['event_key'])): ?>
                        <span class="event-badge other">
                            <?= e($p['event_key']) ?>
                        </span>
                    <?php else: ?>
                        <span style="color:var(--sms-muted); font-size:0.8rem;">— بدون انتساب —</span>
                    <?php endif; ?>
                </td>

                <!-- 5. Dynamic Variables & Data-Binding -->
                <td>
                    <div style="display:flex; align-items:center; gap:6px;">
                        <span style="font-size:0.75rem; font-weight:700; color:var(--sms-muted);">
                            <?= toPersianDigits((string)$p['variables_count']) ?> متغیر
                        </span>
                    </div>
                    <?php if (!empty($cfg)): ?>
                        <div class="var-chips-wrap">
                            <?php foreach ($cfg as $v): 
                                $isBound = !empty($v['source_token']);
                            ?>
                                <span class="var-chip <?= $isBound ? 'bound' : '' ?>" 
                                      title="<?= $isBound ? 'متصل به داده: ' . e($v['source_token']) : 'بدون نگاشت خودکار' ?>">
                                    %<?= e($v['name']) ?>%<?= $isBound ? ' 🔗' : '' ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </td>

                <!-- 6. Quick Active Switch -->
                <td style="text-align:center;">
                    <div style="display:flex; flex-direction:column; align-items:center; gap:4px;">
                        <label class="switch-label" title="تغییر وضعیت فعال/غیرفعال">
                            <input type="checkbox" class="sms-active-toggle" data-id="<?= (int)$p['id'] ?>" <?= !empty($p['is_active']) ? 'checked' : '' ?>>
                            <span class="switch-slider"></span>
                        </label>
                        <span id="status-badge-<?= (int)$p['id'] ?>" class="status-pill <?= !empty($p['is_active']) ? 'status-delivered' : 'status-cancelled' ?>" style="font-size:0.68rem; padding:1px 6px;">
                            <?= !empty($p['is_active']) ? 'فعال' : 'غیرفعال' ?>
                        </span>
                    </div>
                </td>

                <!-- 7. Actions -->
                <td style="text-align:center;">
                    <div class="admin-actions" style="justify-content:center;">
                        <a href="sms_pattern_edit.php?id=<?= (int)$p['id'] ?>" class="btn btn-sm btn-outline" title="ویرایش و تست ارسال">
                            ویرایش و تست
                        </a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('آیا از حذف الگوی «<?= e($p['title']) ?>» اطمینان دارید؟');">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button type="submit" class="btn btn-sm btn-danger" style="padding:4px 8px;" title="حذف الگو">🗑️</button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>

            <!-- No search results row -->
            <tr id="smsNoResultsRow" style="display:none;">
                <td colspan="7" style="text-align:center; padding:30px 10px; color:var(--sms-muted);">
                    هیچ الگویی با فیلتر یا جستجوی انتخابی شما مطابقت ندارد.
                </td>
            </tr>

            <?php if (empty($patterns)): ?>
            <tr>
                <td colspan="7" style="text-align:center; padding:32px 0; color:var(--sms-muted);">
                    هنوز هیچ الگوی پیامکی در سیستم ثبت نشده است. روی «الگوی جدید» بزنید.
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
