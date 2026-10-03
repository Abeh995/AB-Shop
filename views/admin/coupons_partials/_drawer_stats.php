<?php
/**
 * Slide-over Analytics Drawer for Coupon Performance & Order Attribution.
 */
?>
<div class="cpn-drawer-overlay" id="couponDrawerOverlay" onclick="closeCouponDrawer(event)">
    <div class="cpn-drawer" onclick="event.stopPropagation()">
        <!-- Drawer Header -->
        <div class="cpn-drawer-header">
            <div>
                <h3 style="margin:0 0 4px; font-size:1.05rem; font-weight:800; color:#0f172a;">
                    عملکرد مالی و آمار سفارشات
                </h3>
                <span id="drawerCouponCode" style="font-family:monospace; font-weight:700; color:var(--cpn-primary); font-size:.9rem;">
                    COUPON_CODE
                </span>
            </div>
            <button type="button" class="btn-copy-code" onclick="closeCouponDrawer()" style="font-size:1.2rem; cursor:pointer;">✕</button>
        </div>

        <!-- Drawer Body -->
        <div class="cpn-drawer-body">
            <!-- Loading Indicator -->
            <div id="drawerLoading" style="text-align:center; padding:40px 0; color:#64748b;">
                <div style="font-weight:600; font-size:.9rem;">در حال دریافت آمار مالی و سفارشات...</div>
            </div>

            <!-- Content Area (Hidden while loading) -->
            <div id="drawerContent" style="display:none; flex-direction:column; gap:20px;">
                <!-- 1. Four ROI Metric Boxes -->
                <div class="cpn-roi-grid">
                    <div class="cpn-roi-box">
                        <h5>سفارشات موفق با این کد</h5>
                        <div class="cpn-roi-num" id="statOrdersCount" style="color:var(--cpn-primary);">0</div>
                    </div>
                    <div class="cpn-roi-box">
                        <h5>درآمد ناخالص حاصله</h5>
                        <div class="cpn-roi-num" id="statGrossRevenue" style="color:var(--cpn-emerald);">0 تومان</div>
                    </div>
                    <div class="cpn-roi-box">
                        <h5>مجموع تخفیف کسر شده</h5>
                        <div class="cpn-roi-num" id="statTotalDiscount" style="color:var(--cpn-rose);">0 تومان</div>
                    </div>
                    <div class="cpn-roi-box">
                        <h5>میانگین ارزش سفارش (AOV)</h5>
                        <div class="cpn-roi-num" id="statAvgOrder" style="color:var(--cpn-sky);">0 تومان</div>
                    </div>
                </div>

                <!-- 2. Recent Orders Table -->
                <div>
                    <h4 style="margin:0 0 10px; font-size:.9rem; font-weight:700; color:#1e293b;">
                        آخرین سفارش‌های ثبت‌شده با این کد
                    </h4>
                    <div style="border:1px solid #e2e8f0; border-radius:10px; overflow:hidden;">
                        <table class="admin-table" style="font-size:.78rem;">
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
                    <div id="drawerEmptyOrders" style="display:none; text-align:center; padding:24px; color:#94a3b8; font-size:.84rem;">
                        تاکنون سفارشی با این کد تخفیف ثبت نشده است.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
