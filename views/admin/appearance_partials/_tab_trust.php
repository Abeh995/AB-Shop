<?php
/**
 * Appearance Tab 3 — Trust Bar & Value Propositions
 */
?>

<div class="appearance-tab-pane" id="pane-trust">
    <div class="appearance-card">
        <div class="appearance-card-header">
            <div>
                <h3 class="appearance-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                    نوار مزایای خرید و اعتمادسازی (Trust Bar)
                </h3>
                <p class="appearance-card-subtitle">
                    ۴ مزیت رقابتی اصلی فروشگاه شما که زیر بنر اصلی نمایش داده شده و شک مشتری را به اعتماد برای خرید تبدیل می‌کند.
                </p>
            </div>
            <span class="appr-pill <?= $trustBarEnabled ? 'appr-pill-active' : 'appr-pill-muted' ?>">
                <?= $trustBarEnabled ? 'نوار مزایا فعال است' : 'نوار مزایا غیرفعال است' ?>
            </span>
        </div>

        <form method="post" action="appearance.php">
            <?= csrfField() ?>
            <input type="hidden" name="section" value="trust_bar">
            <input type="hidden" name="tab" value="trust">

            <div class="appearance-card-body">
                <div style="margin-bottom:20px;">
                    <label class="section-item-label">
                        <input type="checkbox" name="trust_bar_enabled" value="1" <?= $trustBarEnabled ? 'checked' : '' ?>>
                        <span>نمایش نوار مزایای خرید در صفحه اصلی</span>
                    </label>
                </div>

                <div class="trust-cards-grid">
                    <!-- Item 1: Fast Shipping -->
                    <div class="trust-item-card">
                        <div class="trust-item-lead">
                            <div class="trust-item-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11"/><path d="M14 9h4l4 4v4c0 .6-.4 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><circle cx="17" cy="18" r="2"/></svg>
                            </div>
                            <span class="trust-item-num">مزیت ۱ (ارسال)</span>
                        </div>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label style="font-size:0.78rem; font-weight:700;">تیتر</label>
                            <input class="form-control" type="text" name="trust_item_1_title" value="<?= e($trustItem1Title) ?>" placeholder="ارسال سریع و مطمئن">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:0.78rem; font-weight:700;">توضیح کوتاه</label>
                            <input class="form-control" type="text" name="trust_item_1_desc" value="<?= e($trustItem1Desc) ?>" placeholder="ارسال پستی به سراسر کشور">
                        </div>
                    </div>

                    <!-- Item 2: Quality Guarantee -->
                    <div class="trust-item-card">
                        <div class="trust-item-lead">
                            <div class="trust-item-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            </div>
                            <span class="trust-item-num">مزیت ۲ (ضمانت بازگشت)</span>
                        </div>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label style="font-size:0.78rem; font-weight:700;">تیتر</label>
                            <input class="form-control" type="text" name="trust_item_2_title" value="<?= e($trustItem2Title) ?>" placeholder="ضمانت ۷ روزه کیفیت">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:0.78rem; font-weight:700;">توضیح کوتاه</label>
                            <input class="form-control" type="text" name="trust_item_2_desc" value="<?= e($trustItem2Desc) ?>" placeholder="تعویض بی‌قید و شرط در صورت عدم رضایت">
                        </div>
                    </div>

                    <!-- Item 3: Material / Natural Fibers -->
                    <div class="trust-item-card">
                        <div class="trust-item-lead">
                            <div class="trust-item-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            </div>
                            <span class="trust-item-num">مزیت ۳ (کیفیت الیاف)</span>
                        </div>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label style="font-size:0.78rem; font-weight:700;">تیتر</label>
                            <input class="form-control" type="text" name="trust_item_3_title" value="<?= e($trustItem3Title) ?>" placeholder="الیاف طبیعی نخ‌پنبه">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:0.78rem; font-weight:700;">توضیح کوتاه</label>
                            <input class="form-control" type="text" name="trust_item_3_desc" value="<?= e($trustItem3Desc) ?>" placeholder="ضد حساسیت، لطیف و بسیار با دوام">
                        </div>
                    </div>

                    <!-- Item 4: Packaging / Gift -->
                    <div class="trust-item-card">
                        <div class="trust-item-lead">
                            <div class="trust-item-icon">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"/><rect width="20" height="5" x="2" y="7"/><line x1="12" x2="12" y1="22" y2="7"/><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"/><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"/></svg>
                            </div>
                            <span class="trust-item-num">مزیت ۴ (بسته‌بندی)</span>
                        </div>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label style="font-size:0.78rem; font-weight:700;">تیتر</label>
                            <input class="form-control" type="text" name="trust_item_4_title" value="<?= e($trustItem4Title) ?>" placeholder="بسته‌بندی بهداشتی و شیک">
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label style="font-size:0.78rem; font-weight:700;">توضیح کوتاه</label>
                            <input class="form-control" type="text" name="trust_item_4_desc" value="<?= e($trustItem4Desc) ?>" placeholder="مناسب برای کادو با بسته‌بندی استاندارد">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-actions-bar">
                <button type="submit" class="btn-appr-save">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    ذخیره نوار مزایای خرید
                </button>
            </div>
        </form>
    </div>
</div>
