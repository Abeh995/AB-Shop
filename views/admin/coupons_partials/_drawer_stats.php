<?php
/**
 * Slide-over Analytics Drawer for Coupon Performance & Order Attribution (views/admin/coupons_partials/_drawer_stats.php)
 * Pure presentation: 0 SQL, 0 $_POST (Rule 7).
 */
?>
<dialog class="ab-drawer" id="couponDrawer" data-side="end" data-ab-modal>
    <!-- Drawer Header -->
    <header class="ab-modal__header">
        <div class="ab-stack ab-stack--xs">
            <h3 class="ab-modal__title">عملکرد مالی و آمار سفارشات</h3>
            <div class="ab-cluster ab-cluster--xs">
                <code class="ab-code ab-code--accent ab-num" id="drawerCouponCode" dir="ltr">COUPON_CODE</code>
            </div>
        </div>
        <button type="button" class="ab-icon-btn ab-icon-btn--sm ab-modal__close" data-ab-modal-close aria-label="بستن پنل کشویی"></button>
    </header>

    <!-- Drawer Body -->
    <div class="ab-modal__body">
        <!-- Loading Indicator -->
        <div id="drawerLoading" class="ab-stack ab-stack--sm ab-text-center">
            <div class="ab-text-muted ab-text-sm">در حال دریافت آمار مالی و سفارشات...</div>
        </div>

        <!-- Content Area -->
        <div id="drawerContent" class="ab-stack ab-stack--md" hidden>
            <!-- 4 Metric Boxes -->
            <div class="ab-grid ab-grid--sm" style="--grid-min: 11rem;">
                <div class="ab-metric-box">
                    <span class="ab-metric-box__title">سفارشات موفق با این کد</span>
                    <span class="ab-metric-box__value ab-num" id="statOrdersCount">۰</span>
                </div>
                <div class="ab-metric-box">
                    <span class="ab-metric-box__title">درآمد ناخالص حاصله</span>
                    <span class="ab-metric-box__value ab-num" id="statGrossRevenue">۰ تومان</span>
                </div>
                <div class="ab-metric-box">
                    <span class="ab-metric-box__title">مجموع تخفیف کسر شده</span>
                    <span class="ab-metric-box__value ab-num" id="statTotalDiscount">۰ تومان</span>
                </div>
                <div class="ab-metric-box">
                    <span class="ab-metric-box__title">میانگین ارزش سفارش (AOV)</span>
                    <span class="ab-metric-box__value ab-num" id="statAvgOrder">۰ تومان</span>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="ab-stack ab-stack--xs">
                <h4 class="ab-text-sm ab-font-bold">آخرین سفارش‌های ثبت‌شده با این کد</h4>
                <div class="ab-table-wrap" data-mode="scroll">
                    <table class="ab-table" id="drawerOrdersTable">
                        <thead>
                            <tr>
                                <th>سفارش</th>
                                <th>مشتری</th>
                                <th>مبلغ کل</th>
                                <th>تخفیف</th>
                                <th>وضعیت</th>
                            </tr>
                        </thead>
                        <tbody id="drawerOrdersTbody">
                            <!-- Populated dynamically via JS -->
                        </tbody>
                    </table>
                </div>
                <div id="drawerEmptyOrders" class="ab-text-center ab-text-muted ab-text-sm" hidden>
                    تاکنون سفارشی با این کد تخفیف ثبت نشده است.
                </div>
            </div>
        </div>
    </div>
</dialog>
