<?php
/**
 * Landing Studio — visual content editor for the React storefront.
 *
 * Pure presentation: the field editors, rails and preview bridge are rendered by
 * assets/js/landing-studio.js from the schema JSON emitted below
 * (single source of truth: app/services/LandingSchemaService.php).
 */

require APP_ROOT . '/views/admin/layout/header.php';
?>

<div class="lb-studio-shell">
<div class="lb-studio" id="lbStudio" data-state="idle">

    <!-- ── Studio toolbar ─────────────────────────────────────────────── -->
    <header class="lb-toolbar">
        <div class="lb-toolbar-id">
            <span class="lb-toolbar-mark" aria-hidden="true">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.9 5.8-5.8 1.9 5.8 1.9L12 18.4l1.9-5.8 5.8-1.9-5.8-1.9L12 3Z"/><path d="M5 19.5 5.5 21l.5-1.5L7.5 19 6 18.5 5.5 17 5 18.5 3.5 19 5 19.5Z"/></svg>
            </span>
            <div class="lb-toolbar-titles">
                <h1>استودیو صفحه اصلی</h1>
                <p>هر متن، تصویر، آیکون و سکشن صفحه اصلی را زنده ویرایش کنید.</p>
            </div>
        </div>

        <div class="lb-toolbar-status" id="lbStatusArea" aria-live="polite">
            <span class="lb-status-pill" id="lbStatusPill" data-tone="neutral">
                <span class="lb-status-dot" aria-hidden="true"></span>
                <span id="lbStatusText">در حال بارگذاری…</span>
            </span>
            <span class="lb-status-meta" id="lbStatusMeta"></span>
        </div>

        <div class="lb-toolbar-actions">
            <div class="lb-btn-group" role="group" aria-label="تاریخچه تغییرات">
                <button type="button" class="lb-icon-btn" id="lbUndoBtn" title="واگرد (Ctrl+Z)" aria-label="واگرد" disabled>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M21 17a9 9 0 0 0-9-9 9 9 0 0 0-6 2.3L3 13"/></svg>
                </button>
                <button type="button" class="lb-icon-btn" id="lbRedoBtn" title="ازنو (Ctrl+Shift+Z)" aria-label="ازنو" disabled>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 7v6h-6"/><path d="M3 17a9 9 0 0 1 9-9 9 9 0 0 1 6 2.3L21 13"/></svg>
                </button>
            </div>

            <button type="button" class="lb-icon-btn" id="lbSelectModeBtn" title="انتخاب بخش با کلیک روی پیش‌نمایش (V)" aria-label="حالت انتخاب از پیش‌نمایش" aria-pressed="false">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 3 7.07 16.97 2.51-7.39 7.39-2.51L3 3Z"/></svg>
            </button>

            <button type="button" class="lb-icon-btn" id="lbPanelToggle" title="جمع کردن ستون ویرایش" aria-label="نمایش یا پنهان کردن ستون ویرایش" aria-pressed="false">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16"/></svg>
            </button>

            <button type="button" class="lb-icon-btn" id="lbFocusBtn" title="تمام‌صفحه (F)" aria-label="تمام‌صفحه" aria-pressed="false">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M16 3h3a2 2 0 0 1 2 2v3"/><path d="M8 21H5a2 2 0 0 1-2-2v-3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
            </button>

            <button type="button" class="lb-icon-btn" id="lbHistoryBtn" title="نسخه‌های منتشرشده" aria-label="تاریخچه انتشار">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l3 2"/></svg>
            </button>

            <div class="lb-menu-wrap">
                <button type="button" class="lb-icon-btn" id="lbMoreBtn" title="ابزارهای بیشتر" aria-label="ابزارهای بیشتر" aria-haspopup="true" aria-expanded="false">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="5" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="12" cy="19" r="1"/></svg>
                </button>
                <div class="lb-menu" id="lbMoreMenu" hidden>
                    <button type="button" data-action="select-mode"><span>حالت انتخاب از پیش‌نمایش</span><small>با کلیک روی هر بخش، فیلدهایش باز می‌شود</small></button>
                    <button type="button" data-action="focus-mode"><span>تمام‌صفحه</span><small>پنهان کردن منوها برای فضای بیشتر</small></button>
                    <button type="button" data-action="history"><span>تاریخچه انتشار</span><small>بازگردانی نسخه‌های قبلی</small></button>
                    <hr>
                    <button type="button" data-action="reset-section"><span>بازنشانی این بخش</span><small>مقادیر پیش‌فرض کارخانه</small></button>
                    <button type="button" data-action="reset-all"><span>بازنشانی همه‌ی فیلدها</span><small>پیش‌نویس به حالت اولیه</small></button>
                    <hr>
                    <button type="button" data-action="import"><span>ورود تنظیمات از JSON</span><small>چسباندن یا انتخاب فایل</small></button>
                    <button type="button" data-action="export"><span>خروجی JSON</span><small>دانلود پیش‌نویس فعلی</small></button>
                    <hr>
                    <button type="button" data-action="discard" class="is-danger"><span>دور ریختن پیش‌نویس</span><small>بازگشت به نسخه منتشرشده</small></button>
                    <button type="button" data-action="shortcuts"><span>کلیدهای میانبر</span><small>Ctrl+S، V، F، Esc</small></button>
                </div>
            </div>

            <a class="lb-btn lb-btn-ghost" href="/" target="_blank" rel="noopener">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span>مشاهده سایت</span>
            </a>

            <button type="button" class="lb-btn lb-btn--primary" id="lbPublishBtn">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4Z"/></svg>
                <span>انتشار تغییرات</span>
                <span class="lb-btn-count" id="lbPublishCount" hidden>0</span>
            </button>
        </div>
    </header>

    <!-- ── Mobile switcher ────────────────────────────────────────────── -->
    <div class="lb-mobile-switch" role="tablist" aria-label="حالت نمایش استودیو">
        <button type="button" role="tab" class="is-active" data-mobile-view="editor">ویرایش محتوا</button>
        <button type="button" role="tab" data-mobile-view="preview">پیش‌نمایش زنده</button>
    </div>

    <!-- ── Workspace ──────────────────────────────────────────────────── -->
    <div class="lb-workspace" id="lbWorkspace">

        <!-- Section rail -->
        <aside class="lb-rail" aria-label="سکشن‌های صفحه">
            <div class="lb-rail-search">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input type="search" id="lbSearchInput" placeholder="جستجوی متن یا فیلد…" autocomplete="off" aria-label="جستجو در فیلدها">
                <kbd>Ctrl K</kbd>
            </div>
            <button type="button" class="lb-rail-toggle" id="lbRailToggle" aria-pressed="false" title="جمع کردن ستون سکشن‌ها">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                <span>سکشن‌ها</span>
            </button>
            <div class="lb-rail-results" id="lbSearchResults" hidden></div>
            <div class="lb-rail-list" id="lbSectionList"></div>
            <div class="lb-rail-foot">
                <a href="appearance.php">ظاهر و صفحه اصلی (کلاسیک)</a>
                <span class="lb-rail-version" id="lbVersionTag">—</span>
            </div>
        </aside>

        <!-- Resize handle: rail ⟷ editor -->
        <button type="button" class="lb-splitter lb-splitter--rail" id="lbSplitRail"
                role="separator" aria-orientation="vertical" tabindex="0"
                aria-label="تغییر عرض ستون سکشن‌ها" title="بکشید تا عرض ستون سکشن‌ها تغییر کند · دوبار کلیک = پیش‌فرض"></button>

        <!-- Field editor -->
        <section class="lb-panel" id="lbPanel" aria-label="ویرایشگر فیلدها">
            <div class="lb-panel-head" id="lbPanelHead"></div>
            <div class="lb-panel-body" id="lbPanelBody"></div>
        </section>

        <!-- Resize handle: editor ⟷ live preview -->
        <button type="button" class="lb-splitter lb-splitter--panel" id="lbSplitPanel"
                role="separator" aria-orientation="vertical" tabindex="0"
                aria-label="تغییر عرض ویرایشگر و پیش‌نمایش" title="بکشید تا عرض ویرایشگر و پیش‌نمایش تغییر کند · دوبار کلیک = پیش‌فرض"></button>

        <!-- Live preview -->
        <section class="lb-preview" id="lbPreview" aria-label="پیش‌نمایش زنده">
            <div class="lb-preview-bar">
                <div class="lb-device-switch" role="group" aria-label="اندازه دستگاه">
                    <button type="button" class="is-active" data-device="desktop" title="دسکتاپ">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="13" rx="2"/><path d="M8 21h8"/><path d="M12 17v4"/></svg>
                    </button>
                    <button type="button" data-device="tablet" title="تبلت">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                    </button>
                    <button type="button" data-device="mobile" title="موبایل">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="2" width="12" height="20" rx="2.5"/><path d="M12 18h.01"/></svg>
                    </button>
                </div>
                <span class="lb-preview-url" id="lbPreviewUrl" dir="ltr">/?lb_preview=1</span>
                <div class="lb-preview-actions">
                    <button type="button" class="lb-icon-btn lb-icon-btn--sm" id="lbZenBtn" title="بزرگ کردن پیش‌نمایش" aria-label="بزرگ کردن پیش‌نمایش" aria-pressed="false">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8V5a2 2 0 0 1 2-2h3"/><path d="M16 3h3a2 2 0 0 1 2 2v3"/><path d="M21 16v3a2 2 0 0 1-2 2h-3"/><path d="M8 21H5a2 2 0 0 1-2-2v-3"/></svg>
                    </button>
                    <button type="button" class="lb-icon-btn lb-icon-btn--sm" id="lbReloadPreviewBtn" title="بارگذاری مجدد پیش‌نمایش" aria-label="بارگذاری مجدد">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-3-6.7L21 8"/><path d="M21 3v5h-5"/></svg>
                    </button>
                    <a class="lb-icon-btn lb-icon-btn--sm" href="/?lb_preview=1" target="_blank" rel="noopener" title="باز کردن پیش‌نمایش در تب جدید" aria-label="باز کردن پیش‌نمایش">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    </a>
                </div>
            </div>
            <div class="lb-preview-stage" id="lbPreviewStage">
                <div class="lb-preview-frame" id="lbPreviewFrameWrap">
                    <iframe id="lbPreviewFrame" src="about:blank" title="پیش‌نمایش زنده صفحه اصلی" loading="lazy"></iframe>
                </div>
                <div class="lb-preview-loading" id="lbPreviewLoading">
                    <span class="lb-spinner" aria-hidden="true"></span>
                    <span>در حال آماده‌سازی پیش‌نمایش…</span>
                </div>
            </div>
        </section>
    </div>
</div>
</div>

<!-- Toast stack -->
<div class="lb-toast-stack" id="lbToasts" aria-live="polite" aria-atomic="false"></div>

<!-- Generic modal host (history / publish / import / shortcuts) -->
<div class="lb-modal-host" id="lbModalHost" hidden>
    <div class="lb-modal-backdrop" data-lb-close></div>
    <div class="lb-modal" role="dialog" aria-modal="true" aria-labelledby="lbModalTitle">
        <header class="lb-modal-head">
            <h2 id="lbModalTitle"></h2>
            <button type="button" class="lb-icon-btn lb-icon-btn--sm" data-lb-close aria-label="بستن">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </header>
        <div class="lb-modal-body" id="lbModalBody"></div>
        <footer class="lb-modal-foot" id="lbModalFoot"></footer>
    </div>
</div>

<input type="file" id="lbFileInput" accept="image/png,image/jpeg,image/webp" hidden>
<input type="file" id="lbJsonInput" accept="application/json,.json" hidden>

<script type="application/json" id="lbStudioBoot">
<?= json_encode($studioBoot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
</script>

<?php require APP_ROOT . '/views/admin/layout/footer.php'; ?>
