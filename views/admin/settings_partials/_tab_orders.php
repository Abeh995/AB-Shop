<?php
/**
 * Settings Partial: Orders & Inventory Workflow Policies
 */
?>
<div class="settings-tab-pane active" id="pane-orders" role="tabpanel">
    <div class="settings-card">
        <div class="settings-card-header">
            <div>
                <h3 class="settings-card-title">🛒 فرآیند سفارش، محدودیت‌های خرید و موجودی انبار</h3>
                <p class="settings-card-subtitle">تعیین حداقل سبد خرید، مهلت بارگذاری فیش بانکی، وضعیت پذیرش سفارش و هشدار انبار</p>
            </div>
        </div>

        <form method="post" action="settings.php">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="orders_policy">
            <input type="hidden" name="tab" value="orders">

            <div class="settings-card-body">

                <!-- 1. Store Operation Status (Vacation Mode) -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>🚦 وضعیت سفارش‌گیری فروشگاه (حالت انبارگردانی / تعطیلات)</span>
                    </div>

                    <label class="settings-toggle-wrap">
                        <div class="toggle-info">
                            <span class="toggle-label">پذیرش فعال سفارش‌ها</span>
                            <p class="toggle-desc">در صورت خاموش کردن این گزینه، امکان پرداخت و ثبت سفارش در سایت بسته شده و پیام هشدار در سبد خرید نمایش می‌یابد.</p>
                        </div>
                        <div class="toggle-switch">
                            <input type="checkbox" name="store_order_status" value="active" <?= ($storeOrderStatus ?? 'active') === 'active' ? 'checked' : '' ?> onchange="document.getElementById('pausedMessageGroup').style.display = this.checked ? 'none' : 'block';">
                            <span class="toggle-slider"></span>
                        </div>
                    </label>

                    <div class="form-group" id="pausedMessageGroup" style="<?= ($storeOrderStatus ?? 'active') === 'active' ? 'display:none;' : '' ?>; margin-top:14px;">
                        <label style="font-size:.82rem; font-weight:700;">پیام اطلاع‌رسانی به مشتری در زمان تعلیق سفارش‌گیری:</label>
                        <textarea class="form-control" name="store_paused_message" rows="2" placeholder="مثلاً: به دلیل انبارگردانی، ثبت سفارش تا شنبه غیرفعال است."><?= e($storePausedMessage ?? '') ?></textarea>
                        <p class="form-helper">این پیام با رنگ برجسته در بالای سبد خرید و مراحل تسویه به مشتریان نشان داده می‌شود.</p>
                    </div>
                </div>

                <!-- 2. Basket & Payment Limits -->
                <div class="settings-grid-2">
                    <div class="settings-fieldset">
                        <div class="fieldset-title">
                            <span>💰 حداقل ارزش سبد خرید (تومان)</span>
                        </div>
                        <div class="settings-input-group">
                            <input class="form-control mono-num" type="number" name="min_order_amount" min="0" step="10000" value="<?= (int)($minOrderAmount ?? 0) ?>" placeholder="0">
                            <span class="settings-input-addon addon-suffix">تومان</span>
                        </div>
                        <p class="form-helper">برای سفارش‌های کمتر از این مبلغ، دکمه ورود به مرحله تسویه غیرفعال می‌شود (۰ = بدون محدودیت).</p>
                    </div>

                    <div class="settings-fieldset">
                        <div class="fieldset-title">
                            <span>⏳ مهلت بارگذاری فیش کارت‌به‌کارت</span>
                        </div>
                        <div class="settings-input-group">
                            <input class="form-control mono-num" type="number" name="c2c_timeout_hours" min="1" max="168" value="<?= (int)($c2cTimeoutHours ?? 24) ?>">
                            <span class="settings-input-addon addon-suffix">ساعت</span>
                        </div>
                        <p class="form-helper">مدت زمانی که به خریدار برای واریز و ثبت فیش مهلت داده می‌شود (پیش‌فرض: ۲۴ ساعت).</p>
                    </div>
                </div>

                <!-- 3. Stock Threshold -->
                <div class="settings-fieldset">
                    <div class="fieldset-title">
                        <span>📦 حد آستانه هشدار کسری انبار</span>
                    </div>
                    <div style="max-width:320px;">
                        <div class="settings-input-group">
                            <input class="form-control mono-num" type="number" name="low_stock_threshold" min="0" max="50" value="<?= (int)($lowStockThreshold ?? 3) ?>">
                            <span class="settings-input-addon addon-suffix">عدد</span>
                        </div>
                    </div>
                    <p class="form-helper">موجودی هر محصول یا واریانت که به این عدد یا کمتر برسد، با برچسب هشدار «کسری انبار» در پیشخوان و لیست محصولات نمایش داده می‌شود.</p>
                </div>

                <div class="settings-form-actions">
                    <button type="submit" class="btn-set-save">
                        <span>ذخیره قوانین سفارش و انبار</span>
                    </button>
                </div>

            </div>
        </form>
    </div>
</div>
