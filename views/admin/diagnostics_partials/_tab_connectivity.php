<?php
/**
 * Diagnostics Partial: Connectivity Testing Suite Pane
 * View Purity: 0 SQL, 0 $_POST, pure presentation (Rule 7).
 */
?>
<div class="diag-tab-pane <?= ($activeTab ?? 'health') === 'tests' ? 'active' : '' ?>" id="pane-tests" role="tabpanel">

    <?php if ($testResult): ?>
        <div class="diag-card" style="border-right:4px solid <?= $testResult['ok'] ? 'var(--diag-success)' : 'var(--diag-danger)' ?>; margin-bottom:20px;">
            <div class="diag-card-body" style="padding:16px 20px;">
                <div style="display:flex; align-items:center; gap:10px; font-weight:700; color:<?= $testResult['ok'] ? '#065F46' : '#991B1B' ?>;">
                    <span style="font-size:1.2rem;"><?= $testResult['ok'] ? '✅' : '❌' ?></span>
                    <span><?= e($testResult['summary'] ?? '') ?></span>
                </div>
                <?php if (!empty($testResult['debug'])): ?>
                    <details style="margin-top:12px;">
                        <summary style="cursor:pointer; color:var(--diag-primary); font-size:.82rem; font-weight:600;">مشاهده داده‌های خام فنی (Debug Payload)</summary>
                        <div class="diag-code-box" style="margin-top:8px;">
                            <?php if (is_string($testResult['debug'])): ?>
                                <?= e($testResult['debug']) ?>
                            <?php else: ?>
                                <?= e(json_encode($testResult['debug'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?>
                            <?php endif; ?>
                        </div>
                    </details>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- 1. Interactive Tests List -->
    <div class="diag-card">
        <div class="diag-card-header">
            <div>
                <h3 class="diag-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><line x1="12" y1="20" x2="12.01" y2="20"/></svg>
                    <span>آزمون‌های زنده پایش اتصال سرویس‌ها</span>
                </h3>
                <p class="diag-card-desc">تست برقراری ارتباط بدون ارسال پیامک، ایمیل یا هزینه برای فروشگاه</p>
            </div>
        </div>
        <div class="diag-card-body">

            <!-- Test 1: Faraz SMS Balance -->
            <div class="diag-test-item">
                <div class="diag-test-info">
                    <h4 class="diag-test-title">۱) بررسی اتصال و استعلام موجودی فراز اس‌ام‌اس</h4>
                    <p class="diag-test-desc">اتصال به وب‌سرویس پترن فراز و دریافت اعتبار عددی و ریالی خط بدون کسر شارژ.</p>
                </div>
                <form method="post" action="diagnostics.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="check_balance">
                    <button type="submit" class="btn btn-sm btn-primary">تست اتصال و موجودی</button>
                </form>
            </div>

            <!-- Test 2: Faraz Pattern Validation -->
            <div class="diag-test-item">
                <div class="diag-test-info">
                    <h4 class="diag-test-title">۲) اعتبارسنجی الگوی پیامک ورود (OTP Pattern)</h4>
                    <p class="diag-test-desc">بررسی نام متغیر پترن ثبت‌شده در پنل فراز و تطابق آن با تنظیمات سیستم.</p>
                </div>
                <form method="post" action="diagnostics.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="check_pattern">
                    <button type="submit" class="btn btn-sm btn-primary">بررسی پترن OTP</button>
                </form>
            </div>

            <!-- Test 3: SMTP Email Handshake -->
            <div class="diag-test-item">
                <div class="diag-test-info">
                    <h4 class="diag-test-title">۳) تست مکالمه سرور ایمیل سازمانی (SMTP Handshake)</h4>
                    <p class="diag-test-desc">بررسی احراز هویت پورت و رمز عبور ایمیل بدون ارسال ایمیل تبلیغاتی یا آزمایشی.</p>
                </div>
                <form method="post" action="diagnostics.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="check_smtp">
                    <button type="submit" class="btn btn-sm btn-primary">تست اتصال SMTP</button>
                </form>
            </div>

            <!-- Test 4: Database Ping -->
            <div class="diag-test-item">
                <div class="diag-test-info">
                    <h4 class="diag-test-title">۴) تست تاخیر رفت و برگشت پایگاه داده (DB Ping Latency)</h4>
                    <p class="diag-test-desc">محاسبه دقیق تاخیر زمانی اجرای کوئری در اتصال سوکت سرور داخلی هاست.</p>
                </div>
                <form method="post" action="diagnostics.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="check_db">
                    <button type="submit" class="btn btn-sm btn-outline">پینگ پایگاه داده</button>
                </form>
            </div>

            <!-- Test 5: Zarinpal Gateway Ping -->
            <div class="diag-test-item">
                <div class="diag-test-info">
                    <h4 class="diag-test-title">۵) بررسی دسترس‌پذیری سرور درگاه زرین‌پال (Zarinpal Ping)</h4>
                    <p class="diag-test-desc">ارسال بسته آزمایش شبکه به نقطه اتصال زرین‌پال جهت اطمینان از باز بودن پورت‌های هاست.</p>
                </div>
                <form method="post" action="diagnostics.php">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="check_zarinpal">
                    <button type="submit" class="btn btn-sm btn-outline">تست شبکه زرین‌پال</button>
                </form>
            </div>

        </div>
    </div>

    <!-- 2. Current Config Values Table -->
    <div class="diag-card">
        <div class="diag-card-header">
            <div>
                <h3 class="diag-card-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M7 7h10"/><path d="M7 12h10"/><path d="M7 17h10"/></svg>
                    <span>مقادیر بارگذاری‌شده از فایل پیکربندی (کلیدهای حساس ماسک شده‌اند)</span>
                </h3>
            </div>
        </div>
        <div class="diag-card-body" style="padding:0;">
            <table class="diag-log-table">
                <tbody>
                    <?php foreach ($configSnapshot as $key => $val): ?>
                        <tr>
                            <td style="font-weight:700; width:280px;"><?= e($key) ?></td>
                            <td class="mono-num" style="color:var(--diag-primary);"><?= e($val) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>
