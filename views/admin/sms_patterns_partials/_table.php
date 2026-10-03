<?php
/**
 * AB-Socks SMS Patterns — High-Density Pattern Matrix Table
 * High-aesthetic data table with compact horizontal alignment, vector SVG badges,
 * and ergonomic action groups.
 * View Purity: 0 SQL, 0 $_POST, pure presentation (Rule 7).
 *
 * @var array $patterns
 * @var array $availableEvents
 */
?>
<div class="sms-table-card">
    <div class="table-responsive" style="overflow-x:auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width:60px; text-align:center;">شناسه</th>
                    <th style="width:140px;">کد پترن (Faraz)</th>
                    <th>عنوان الگو و توضیحات</th>
                    <th>رویداد متناظر سیستمی</th>
                    <th>متغیرها و نگاشت داده</th>
                    <th style="width:110px; text-align:center;">وضعیت</th>
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
                    <span class="sms-id-badge">#<?= (int)$p['id'] ?></span>
                </td>

                <!-- 2. Pattern Code -->
                <td>
                    <?php if ($isUnset): ?>
                        <a href="sms_pattern_edit.php?id=<?= (int)$p['id'] ?>" class="pattern-code-unset" title="برای تنظیم کلیک کنید">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                            <span>تنظیم‌نشده ←</span>
                        </a>
                    <?php else: ?>
                        <div class="pattern-code-wrap">
                            <span class="pattern-code-badge" title="کد ثبت شده در فراز اس‌ام‌اس"><?= e($p['pattern_code']) ?></span>
                            <button type="button" class="btn-copy-code" onclick="navigator.clipboard.writeText('<?= e($p['pattern_code']) ?>'); this.textContent='✓'; setTimeout(()=>this.textContent='کپی', 1200);" title="کپی کد">کپی</button>
                        </div>
                    <?php endif; ?>
                </td>

                <!-- 3. Title & Description -->
                <td>
                    <div class="sms-title-cell">
                        <div class="sms-title-text"><?= e($p['title']) ?></div>
                        <?php if (!empty($p['description'])): ?>
                            <div class="sms-desc-text"><?= e($p['description']) ?></div>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- 4. Event Category Badge -->
                <td>
                    <?php if (!empty($p['event_key']) && isset($availableEvents[$p['event_key']])): ?>
                        <span class="event-badge <?= e($category) ?>">
                            <span class="event-dot"></span>
                            <span><?= e($availableEvents[$p['event_key']]) ?></span>
                        </span>
                    <?php elseif (!empty($p['event_key'])): ?>
                        <span class="event-badge other">
                            <span class="event-dot"></span>
                            <span><?= e($p['event_key']) ?></span>
                        </span>
                    <?php else: ?>
                        <span class="event-badge-none">— بدون انتساب —</span>
                    <?php endif; ?>
                </td>

                <!-- 5. Dynamic Variables & Data-Binding -->
                <td>
                    <div class="sms-var-summary">
                        <span class="var-count-badge"><?= toPersianDigits((string)$p['variables_count']) ?> متغیر</span>
                        <?php if (!empty($cfg)): ?>
                            <div class="var-chips-inline">
                                <?php foreach ($cfg as $v): 
                                    $isBound = !empty($v['source_token']);
                                ?>
                                    <span class="var-chip <?= $isBound ? 'bound' : '' ?>" 
                                          title="<?= $isBound ? 'متصل به: ' . e($v['source_token']) : 'بدون نگاشت داده' ?>">
                                        %<?= e($v['name']) ?>%
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- 6. Quick Active Switch -->
                <td style="text-align:center;">
                    <div class="sms-status-cell">
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
                    <div class="table-action-group">
                        <a href="sms_pattern_edit.php?id=<?= (int)$p['id'] ?>" class="btn-action-edit" title="ویرایش و تست ارسال">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                            <span>ویرایش و تست</span>
                        </a>
                        <form method="post" style="display:inline;" onsubmit="return confirm('آیا از حذف الگوی «<?= e($p['title']) ?>» اطمینان دارید؟');">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                            <button type="submit" class="btn-action-delete" title="حذف الگو">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                            </button>
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
                <td colspan="7" style="text-align:center; padding:36px 0; color:var(--sms-muted);">
                    هنوز هیچ الگوی پیامکی در سیستم ثبت نشده است. روی «الگوی جدید» بزنید.
                </td>
            </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
