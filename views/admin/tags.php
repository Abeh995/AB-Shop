<?php
/**
 * Admin Product Tags Management View
 * Pure presentation, zero direct SQL queries, zero form mutations (Rule 7).
 */

require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="cpn-workspace">

    <!-- 1. Alert Messages -->
    <?php if (isset($_GET['saved'])): ?>
        <div class="alert alert-success">اطلاعات برچسب با موفقیت ذخیره شد.</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">برچسب با موفقیت حذف گردید.</div>
    <?php endif; ?>
    <?php if (isset($_GET['cleaned'])): ?>
        <div class="alert alert-success"><?= toPersianDigits((string)(int)$_GET['cleaned']) ?> برچسب بلااستفاده با موفقیت پاکسازی شدند.</div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
    <?php endif; ?>

    <!-- 2. Bento KPI Cards -->
    <section class="cpn-kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
        <div class="cpn-kpi-card">
            <div class="cpn-kpi-info">
                <h4>کل برچسب‌ها و مشخصات</h4>
                <div class="cpn-kpi-val" style="color:var(--cpn-primary);">
                    <?= toPersianDigits((string)$stats['total_tags']) ?>
                </div>
                <div class="cpn-kpi-sub">تگ‌های ثبت‌شده در پایگاه داده</div>
            </div>
            <div class="cpn-kpi-icon" style="background:var(--cpn-primary-light); color:var(--cpn-primary);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/><circle cx="7" cy="7" r="1.5" fill="currentColor"/></svg>
            </div>
        </div>

        <div class="cpn-kpi-card">
            <div class="cpn-kpi-info">
                <h4>برچسب‌های متصل به کالا</h4>
                <div class="cpn-kpi-val" style="color:var(--cpn-emerald);">
                    <?= toPersianDigits((string)$stats['used_tags']) ?>
                </div>
                <div class="cpn-kpi-sub">دارای حداقل یک محصول در کاتالوگ</div>
            </div>
            <div class="cpn-kpi-icon" style="background:var(--cpn-emerald-light); color:var(--cpn-emerald);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m5 12 5 5L20 7"/></svg>
            </div>
        </div>

        <div class="cpn-kpi-card">
            <div class="cpn-kpi-info">
                <h4>برچسب‌های بدون استفاده</h4>
                <div class="cpn-kpi-val" style="color:var(--cpn-amber);">
                    <?= toPersianDigits((string)$stats['unused_tags']) ?>
                </div>
                <div class="cpn-kpi-sub">قابل پاکسازی خودکار</div>
            </div>
            <div class="cpn-kpi-icon" style="background:var(--cpn-amber-light); color:var(--cpn-amber);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/></svg>
            </div>
        </div>
    </section>

    <!-- 3. Toolbar -->
    <section class="cpn-toolbar-card">
        <form method="get" action="/admin/tags.php" class="cpn-search-form">
            <input type="text" name="q" value="<?= e($search) ?>" placeholder="جستجوی برچسب یا اسلاگ..." class="cpn-search-input">
            <svg class="cpn-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
            </svg>
            <?php if ($search !== ''): ?>
                <a href="/admin/tags.php" style="position:absolute; left:28px; color:#94a3b8; text-decoration:none; font-size:12px;">✕</a>
            <?php endif; ?>
        </form>

        <div class="cpn-toolbar-actions">
            <?php if ($stats['unused_tags'] > 0): ?>
                <form method="post" action="/admin/tags.php" onsubmit="return confirm('آیا از پاکسازی تمام برچسب‌های بدون استفاده اطمینان دارید؟');" style="margin:0;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="cleanup">
                    <button type="submit" class="btn btn-outline" style="color:var(--cpn-amber); border-color:#fcd34d; font-size:.84rem; padding:8px 14px;">
                        پاکسازی تگ‌های بلااستفاده (<?= toPersianDigits((string)$stats['unused_tags']) ?>)
                    </button>
                </form>
            <?php endif; ?>

            <button type="button" class="btn btn-primary" onclick="openTagModal()" style="display:inline-flex; align-items:center; gap:6px; font-weight:700; font-size:.84rem; padding:8px 16px; border-radius:10px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                افزودن برچسب جدید
            </button>
        </div>
    </section>

    <!-- 4. Tags Table -->
    <section class="cpn-table-card">
        <div style="overflow-x:auto;">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>عنوان برچسب</th>
                        <th>نامک سئو (Slug)</th>
                        <th>محصولات متصل</th>
                        <th>تاریخ ایجاد</th>
                        <th style="text-align:left;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tags)): ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:48px; color:var(--cpn-text-muted);">
                                هیچ برچسبی یافت نشد.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tags as $t): ?>
                            <tr>
                                <td>
                                    <div style="font-weight:700; color:#0f172a; font-size:.9rem;">
                                        <?= e($t['name']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span style="font-family:monospace; font-size:.82rem; color:#475569; background:#f1f5f9; padding:2px 8px; border-radius:6px;" dir="ltr">
                                        <?= e($t['slug']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ((int)$t['products_count'] > 0): ?>
                                        <a href="/admin/products.php?q=<?= urlencode($t['name']) ?>" 
                                           style="display:inline-flex; align-items:center; gap:4px; font-weight:700; color:var(--cpn-primary); text-decoration:none; background:var(--cpn-primary-light); padding:2px 8px; border-radius:6px; font-size:.8rem;">
                                            <?= toPersianDigits((string)$t['products_count']) ?> کالا
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#94a3b8; font-size:.8rem;">بدون محصول</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span style="font-size:.82rem; color:#64748b;">
                                        <?= appDateTime($t['created_at'], 'shamsi') ?>
                                    </span>
                                </td>
                                <td style="text-align:left;">
                                    <div style="display:inline-flex; align-items:center; gap:6px;">
                                        <a href="/admin/tags.php?edit=<?= (int)$t['id'] ?>" class="cpn-action-btn" title="ویرایش">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                        </a>

                                        <form method="post" action="/admin/tags.php" onsubmit="return confirm('آیا از حذف این برچسب اطمینان دارید؟');" style="margin:0; display:inline;">
                                            <?= csrfField() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                            <button type="submit" class="cpn-action-btn btn-delete" title="حذف">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 5. Tag Create/Edit Modal -->
    <div class="cpn-modal-overlay <?= $editTag ? 'active' : '' ?>" id="tagModal">
        <div class="cpn-modal-card" style="max-width:480px;">
            <div class="cpn-modal-header">
                <h3 style="margin:0; font-size:1.05rem; font-weight:800; color:#0f172a;">
                    <?= $editTag ? 'ویرایش برچسب' : 'تعریف برچسب جدید' ?>
                </h3>
                <button type="button" class="btn-copy-code" onclick="closeTagModal()" style="font-size:1.2rem; cursor:pointer;">✕</button>
            </div>

            <form method="post" action="/admin/tags.php" class="cpn-modal-body">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="id" value="<?= (int)($editTag['id'] ?? 0) ?>">

                <div class="cpn-form-group">
                    <label>عنوان برچسب (فارسی) *</label>
                    <input class="cpn-form-control" type="text" name="name" required 
                           value="<?= e($editTag['name'] ?? '') ?>" placeholder="مثلاً: جوراب نخی یا پاییزی">
                </div>

                <div class="cpn-form-group" style="margin-top:10px;">
                    <label>نامک در آدرس سئو (Slug)</label>
                    <input class="cpn-form-control" type="text" name="slug" dir="ltr" 
                           value="<?= e($editTag['slug'] ?? '') ?>" placeholder="اختیاری - خودکار تولید می‌شود">
                    <small style="color:#64748b; font-size:.74rem;">می‌تواند انگلیسی یا فارسی باشد (مثال: cotton-socks).</small>
                </div>

                <div style="display:flex; align-items:center; justify-content:space-between; margin-top:16px; padding-top:14px; border-top:1px solid #e2e8f0;">
                    <button type="button" class="btn btn-outline" onclick="closeTagModal()">انصراف</button>
                    <button type="submit" class="btn btn-primary" style="padding:9px 24px; font-weight:700;">
                        <?= $editTag ? 'ذخیره تغییرات' : 'ایجاد برچسب' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
function openTagModal() {
    document.getElementById('tagModal').classList.add('active');
}
function closeTagModal() {
    document.getElementById('tagModal').classList.remove('active');
    if (window.location.search.includes('edit=')) {
        window.location.href = '/admin/tags.php';
    }
}
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
