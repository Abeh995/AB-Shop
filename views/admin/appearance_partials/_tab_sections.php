<?php
/**
 * Appearance Tab 1 — Homepage Sections & Layout
 */
?>

<div class="appearance-tab-pane active" id="pane-sections">
    <div class="appearance-card">
        <div class="appearance-card-header">
            <div>
                <h3 class="appearance-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M3 15h18"/></svg>
                    مدیریت بخش‌های صفحه اصلی (Landing Page)
                </h3>
                <p class="appearance-card-subtitle">هر سکشن را به دلخواه روشن یا خاموش کنید، عناوین را سفارشی‌سازی کنید و سقف نمایش کالاها را مشخص نمایید.</p>
            </div>
        </div>

        <form method="post" action="appearance.php">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="home_sections">
            <input type="hidden" name="tab" value="sections">

            <div class="appearance-card-body">
                <!-- 1. Text Hero Intro -->
                <div class="section-item-card <?= $homeIntroEnabled ? 'is-active' : '' ?>">
                    <div class="section-item-header">
                        <label class="section-item-label">
                            <input type="checkbox" name="home_intro_enabled" value="1" <?= $homeIntroEnabled ? 'checked' : '' ?>>
                            <span>۱. متن و تیتر معرفی بالای صفحه (Hero Intro)</span>
                        </label>
                        <span class="appr-pill <?= $homeIntroEnabled ? 'appr-pill-active' : 'appr-pill-muted' ?>">
                            <?= $homeIntroEnabled ? 'فعال' : 'غیرفعال' ?>
                        </span>
                    </div>
                    <div class="section-item-fields">
                        <div class="form-group">
                            <label style="font-size:0.8rem; font-weight:700;">تیتر اصلی</label>
                            <input class="form-control" type="text" name="home_intro_title" value="<?= e($homeIntroTitle) ?>" placeholder="جوراب‌هایی که هر روزتان را راحت‌تر می‌کنند">
                        </div>
                        <div class="form-group">
                            <label style="font-size:0.8rem; font-weight:700;">توضیحات کوتاه زیر تیتر</label>
                            <input class="form-control" type="text" name="home_intro_subtitle" value="<?= e($homeIntroSubtitle) ?>" placeholder="کیفیت پارچه، دوخت مقاوم و...">
                        </div>
                    </div>
                    <small style="color:var(--appr-text-muted); font-size:0.75rem; display:block; margin-top:6px;">
                        💡 نکته: در صورتی که «بنر پروموشن ویژه» در تب دوم فعال باشد، بنر تصویری جایگزین این متن خواهد شد.
                    </small>
                </div>

                <!-- 2. Categories Grid -->
                <div class="section-item-card <?= $homeCategoriesEnabled ? 'is-active' : '' ?>">
                    <div class="section-item-header">
                        <label class="section-item-label">
                            <input type="checkbox" name="home_categories_enabled" value="1" <?= $homeCategoriesEnabled ? 'checked' : '' ?>>
                            <span>۲. کارت‌های بزرگ دسته‌بندی‌ها (Category Grid)</span>
                        </label>
                        <span class="appr-pill <?= $homeCategoriesEnabled ? 'appr-pill-active' : 'appr-pill-muted' ?>">
                            <?= $homeCategoriesEnabled ? 'فعال' : 'غیرفعال' ?>
                        </span>
                    </div>
                    <div class="section-item-fields">
                        <div class="form-group" style="max-width:360px;">
                            <label style="font-size:0.8rem; font-weight:700;">عنوان نمایشی بخش</label>
                            <input class="form-control" type="text" name="home_categories_title" value="<?= e($homeCategoriesTitle) ?>" placeholder="دسته‌بندی‌ها">
                        </div>
                    </div>
                </div>

                <!-- 3. Featured Carousel -->
                <div class="section-item-card <?= $homeFeaturedEnabled ? 'is-active' : '' ?>">
                    <div class="section-item-header">
                        <label class="section-item-label">
                            <input type="checkbox" name="home_featured_enabled" value="1" <?= $homeFeaturedEnabled ? 'checked' : '' ?>>
                            <span>۳. اسلایدر پیشنهاد ویژه (Featured Products)</span>
                        </label>
                        <span class="appr-pill <?= $homeFeaturedEnabled ? 'appr-pill-active' : 'appr-pill-muted' ?>">
                            <?= $homeFeaturedEnabled ? 'فعال' : 'غیرفعال' ?>
                        </span>
                    </div>
                    <div class="section-item-fields">
                        <div class="form-group">
                            <label style="font-size:0.8rem; font-weight:700;">عنوان نمایشی بخش</label>
                            <input class="form-control" type="text" name="home_featured_title" value="<?= e($homeFeaturedTitle) ?>" placeholder="پیشنهاد ویژه">
                        </div>
                        <div class="form-group" style="max-width:200px;">
                            <label style="font-size:0.8rem; font-weight:700;">تعداد کالا در اسلایدر</label>
                            <select class="form-control" name="home_featured_limit">
                                <?php foreach ([4, 6, 8, 12, 16] as $limit): ?>
                                    <option value="<?= $limit ?>" <?= (int)($homeFeaturedLimit ?? 6) === $limit ? 'selected' : '' ?>><?= toPersianDigits((string)$limit) ?> کالا</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 4. Newest Carousel -->
                <div class="section-item-card <?= $homeNewestEnabled ? 'is-active' : '' ?>">
                    <div class="section-item-header">
                        <label class="section-item-label">
                            <input type="checkbox" name="home_newest_enabled" value="1" <?= $homeNewestEnabled ? 'checked' : '' ?>>
                            <span>۴. اسلایدر جدیدترین محصولات (Newest Products)</span>
                        </label>
                        <span class="appr-pill <?= $homeNewestEnabled ? 'appr-pill-active' : 'appr-pill-muted' ?>">
                            <?= $homeNewestEnabled ? 'فعال' : 'غیرفعال' ?>
                        </span>
                    </div>
                    <div class="section-item-fields">
                        <div class="form-group">
                            <label style="font-size:0.8rem; font-weight:700;">عنوان نمایشی بخش</label>
                            <input class="form-control" type="text" name="home_newest_title" value="<?= e($homeNewestTitle) ?>" placeholder="آخرین محصولات">
                        </div>
                        <div class="form-group" style="max-width:200px;">
                            <label style="font-size:0.8rem; font-weight:700;">تعداد کالا در اسلایدر</label>
                            <select class="form-control" name="home_newest_limit">
                                <?php foreach ([4, 6, 8, 12, 16] as $limit): ?>
                                    <option value="<?= $limit ?>" <?= (int)($homeNewestLimit ?? 6) === $limit ? 'selected' : '' ?>><?= toPersianDigits((string)$limit) ?> کالا</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 5. Category Strip -->
                <div class="section-item-card <?= $homeCategoryStripEnabled ? 'is-active' : '' ?>">
                    <div class="section-item-header">
                        <label class="section-item-label">
                            <input type="checkbox" name="home_category_strip_enabled" value="1" <?= $homeCategoryStripEnabled ? 'checked' : '' ?>>
                            <span>۵. نوار دسته‌بندی‌ها در انتهای صفحه (Category Strip)</span>
                        </label>
                        <span class="appr-pill <?= $homeCategoryStripEnabled ? 'appr-pill-active' : 'appr-pill-muted' ?>">
                            <?= $homeCategoryStripEnabled ? 'فعال' : 'غیرفعال' ?>
                        </span>
                    </div>
                    <div class="section-item-fields">
                        <div class="form-group" style="max-width:400px;">
                            <label style="font-size:0.8rem; font-weight:700;">عنوان نمایشی بخش</label>
                            <input class="form-control" type="text" name="home_category_strip_title" value="<?= e($homeCategoryStripTitle) ?>" placeholder="دسته‌بندی‌ها را از همین‌جا هم می‌بینید">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions-bar">
                <button type="submit" class="btn-appr-save">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    ذخیره چیدمان صفحه اصلی
                </button>
            </div>
        </form>
    </div>
</div>
