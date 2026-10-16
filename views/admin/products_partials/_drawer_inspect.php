<?php
/**
 * Side Dossier Drawer for Quick Inspect & Stock Management (views/admin/products_partials/_drawer_inspect.php)
 * Native <dialog> side drawer: 0 SQL, 0 $_POST (Rule 7).
 */
?>
<dialog class="ab-drawer" id="prodDrawer" data-side="end" data-ab-modal aria-label="کشوی جزئیات و انبار کالا">
    <!-- Drawer Header -->
    <header class="ab-modal__header">
        <div class="ab-cluster ab-cluster--sm">
            <img src="/assets/img/placeholder-sock.svg" id="drawerThumb" class="prod-drawer-thumb" alt="" width="44" height="44">
            <div class="ab-stack ab-stack--xs">
                <h3 id="drawerName" class="ab-modal__title">نام کالا</h3>
                <div>
                    <code id="drawerSku" class="ab-code ab-code--accent ab-num" dir="ltr">کد SKU</code>
                </div>
            </div>
        </div>
        <button type="button" class="ab-icon-btn ab-icon-btn--sm ab-modal__close" data-ab-modal-close aria-label="بستن کشو">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </header>

    <!-- Drawer Body -->
    <div class="ab-modal__body">
        <div class="ab-stack ab-stack--md">
            <!-- Financial Snapshot -->
            <div class="ab-grid ab-grid--sm prod-drawer-metrics">
                <div class="ab-metric-box">
                    <span class="ab-metric-box__title">قیمت فروش</span>
                    <span class="ab-metric-box__value ab-num" id="drawerPrice">—</span>
                </div>
                <div class="ab-metric-box">
                    <span class="ab-metric-box__title">بهای تمام‌شده</span>
                    <span class="ab-metric-box__value ab-num" id="drawerCost">—</span>
                </div>
                <div class="ab-metric-box">
                    <span class="ab-metric-box__title">مارجین ناخالص</span>
                    <span class="ab-metric-box__value ab-num" id="drawerMargin">—</span>
                </div>
            </div>

            <!-- Quick Stock Editor -->
            <div class="ab-stack ab-stack--xs">
                <div class="ab-cluster ab-cluster--between">
                    <h4 class="ab-text-sm ab-font-bold">مدیریت سریع موجودی انبار</h4>
                    <span class="ab-text-xs ab-text-muted">ویرایش و ذخیره آنی</span>
                </div>
                <div id="drawerStockEditor"></div>
            </div>

            <!-- Gallery Strip -->
            <div id="drawerGallerySection" class="ab-stack ab-stack--xs" hidden>
                <h4 class="ab-text-sm ab-font-bold">گالری تصاویر کالا</h4>
                <div class="prod-gallery-strip" id="drawerGallery"></div>
            </div>
        </div>
    </div>

    <!-- Drawer Footer -->
    <footer class="ab-modal__footer ab-cluster ab-cluster--between">
        <button type="button" class="ab-btn ab-btn--primary" id="drawerSaveStock">ذخیره تغییرات موجودی</button>
        <a href="#" class="ab-btn ab-btn--outline" id="drawerFullEdit">
            <span>ویرایش کامل کالا</span>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
        </a>
    </footer>
</dialog>
