/**
 * Standalone Responsive Navigation Shell & Drawer Test Harness
 * Tests mobile hamburger, off-canvas drawer slide, backdrop, keyboard escape,
 * and zero horizontal overflow across viewports without requiring database auth.
 */

import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '../..');

// Read CSS and JS bundles directly
const tokensCss = fs.readFileSync(path.join(projectRoot, 'assets/css/admin-tokens.css'), 'utf-8');
const baseCss = fs.readFileSync(path.join(projectRoot, 'assets/css/admin-base.css'), 'utf-8');
const shellCss = fs.readFileSync(path.join(projectRoot, 'assets/css/admin-shell.css'), 'utf-8');
const compCss = fs.readFileSync(path.join(projectRoot, 'assets/css/admin-components.css'), 'utf-8');
const utilCss = fs.readFileSync(path.join(projectRoot, 'assets/css/admin-utilities.css'), 'utf-8');
const abKitJs = fs.readFileSync(path.join(projectRoot, 'assets/js/ab-kit.js'), 'utf-8');
const adminJs = fs.readFileSync(path.join(projectRoot, 'assets/js/admin.js'), 'utf-8');

// Construct mock HTML representing the full admin shell
function getShellHtml(pageTitle = 'پیشخوان مدیریت', hasBnb = true) {
  return `<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>${pageTitle}</title>
<style>
${tokensCss}
${baseCss}
${shellCss}
${compCss}
${utilCss}
</style>
<script>
${abKitJs}
</script>
</head>
<body class="admin-body">
<div class="admin-wrap">
    <aside class="admin-sidebar" id="adminSidebar" aria-label="ناوبری اصلی پنل مدیریت">
        <div class="admin-sidebar-header">
            <a href="index.php" class="admin-logo" id="adminSidebarLogo" title="پیشخوان مدیریت">
                <span class="logo-mark">AB</span>
                <div class="logo-text">
                    <span class="logo-title">فروشگاه جوراب</span>
                    <span class="logo-subtitle">پنل مدیریت</span>
                </div>
            </a>
            <button type="button" class="sidebar-toggle-btn" id="sidebarToggleBtn" title="تغییر وضعیت سایدبار" aria-label="جمع کردن یا باز کردن سایدبار">
                <svg class="toggle-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m15 18-6-6 6-6"/></svg>
            </button>
            <button type="button" class="admin-sidebar-close" id="adminSidebarCloseBtn" title="بستن منو" aria-label="بستن منوی ناوبری">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <nav class="admin-sidebar-nav" id="adminSidebarNav">
            <div class="nav-item-wrap">
                <a href="index.php" class="nav-item-single active">
                    <span class="nav-icon-box"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg></span>
                    <span class="nav-item-label">داشبورد</span>
                </a>
            </div>
            <div class="nav-group active-group open" data-group="products">
                <button type="button" class="nav-group-toggle active" aria-expanded="true">
                    <div class="nav-group-lead">
                        <span class="nav-icon-box"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></span>
                        <span class="nav-item-label">محصولات</span>
                    </div>
                    <div class="nav-group-meta">
                        <svg class="nav-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
                    </div>
                </button>
                <div class="nav-sub-list">
                    <a href="products.php" class="nav-sub-item active"><span class="sub-indicator"></span><span class="sub-label">همه محصولات</span></a>
                    <a href="categories.php" class="nav-sub-item"><span class="sub-indicator"></span><span class="sub-label">دسته‌بندی‌ها</span></a>
                    <a href="coupons.php" class="nav-sub-item"><span class="sub-indicator"></span><span class="sub-label">کدهای تخفیف</span></a>
                </div>
            </div>
            <div class="nav-item-wrap">
                <a href="settings.php" class="nav-item-single">
                    <span class="nav-icon-box"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/></svg></span>
                    <span class="nav-item-label">تنظیمات</span>
                </a>
            </div>
        </nav>

        <div class="admin-sidebar-footer">
            <a href="/" target="_blank" class="sidebar-footer-link">
                <span class="nav-icon-box"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg></span>
                <span class="footer-link-text">مشاهده فروشگاه</span>
            </a>
            <a href="logout.php" class="sidebar-footer-link logout-link">
                <span class="nav-icon-box"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/></svg></span>
                <span class="footer-link-text">خروج</span>
            </a>
        </div>
    </aside>
    <div class="admin-sidebar-backdrop" id="adminSidebarBackdrop" aria-hidden="true"></div>

    <main class="admin-main">
        <header class="dash-topbar" id="adminGlobalTopbar">
            <div class="dash-topbar-right">
                <button type="button" class="admin-mobile-menu-btn" id="adminMobileMenuBtn" aria-label="منوی ناوبری پنل مدیریت" aria-expanded="false" aria-controls="adminSidebar" data-ab-sidebar-toggle>
                    <svg class="admin-menu-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="admin-search-box">
                    <div class="admin-search-input-wrap">
                        <svg class="admin-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="search" id="adminGlobalSearch" class="admin-search-input" placeholder="جستجو..." autocomplete="off">
                        <kbd class="admin-search-kbd">Ctrl K</kbd>
                    </div>
                </div>
                <a href="/" target="_blank" class="btn-dash-store-compact">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                    <span class="store-btn-text">فروشگاه</span>
                </a>
            </div>
            <div class="dash-topbar-left">
                <div class="dash-live-datetime">
                    <div class="dash-datetime-desktop">
                        <span class="live-clock-time">۱۲:۰۰</span>
                        <span class="live-weekday">شنبه</span>
                    </div>
                    <div class="dash-datetime-mobile">
                        <span class="mobile-weekday">شنبه</span>
                        <span class="mobile-shamsi">۱۴۰۳/۰۷/۱۸</span>
                    </div>
                </div>
            </div>
        </header>

        <div class="admin-subnav-container">
            <nav class="admin-subnav-strip">
                <a href="products.php" class="subnav-tab-item active"><span class="subnav-tab-text">همه محصولات</span></a>
                <a href="categories.php" class="subnav-tab-item"><span class="subnav-tab-text">دسته‌بندی‌ها</span></a>
                <a href="coupons.php" class="subnav-tab-item"><span class="subnav-tab-text">تخفیف‌ها</span></a>
            </nav>
        </div>

        <div class="ab-page" data-tone="brand">
            <div class="ab-card">
                <div class="ab-card__body">
                    <p>محتوای آزمایشی پنل مدیریت</p>
                </div>
            </div>
        </div>
    </main>
</div>

${hasBnb ? `
<nav class="admin-bottom-nav" aria-label="ناوبری اصلی پنل ادمین">
    <a href="index.php" class="admin-bottom-nav-item active"><span class="nav-label">داشبورد</span></a>
    <a href="orders.php" class="admin-bottom-nav-item"><span class="nav-label">سفارش‌ها</span></a>
    <a href="products.php" class="admin-bottom-nav-item"><span class="nav-label">محصولات</span></a>
    <a href="finance_dashboard.php" class="admin-bottom-nav-item"><span class="nav-label">مالی</span></a>
    <a href="settings.php" class="admin-bottom-nav-item"><span class="nav-label">تنظیمات</span></a>
</nav>
` : ''}

<script>
${adminJs}
</script>
</body>
</html>`;
}

async function runTests() {
  console.log('=== Running Responsive Admin Navigation & Drawer Tests ===\n');

  const browser = await chromium.launch({ headless: true });
  let totalTests = 0;
  let passedTests = 0;
  let failedTests = 0;

  function assert(condition, message) {
    totalTests++;
    if (condition) {
      passedTests++;
      console.log(`  [PASS] ${message}`);
    } else {
      failedTests++;
      console.error(`  [FAIL] ${message}`);
    }
  }

  const viewports = [
    { name: 'android-s (360x740)', width: 360, height: 740, isMobile: true },
    { name: 'iphone (390x844)', width: 390, height: 844, isMobile: true },
    { name: 'tablet (768x1024)', width: 768, height: 1024, isMobile: true },
    { name: 'md-edge (1024x768)', width: 1024, height: 768, isMobile: false },
    { name: 'desktop (1440x900)', width: 1440, height: 900, isMobile: false },
  ];

  for (const vp of viewports) {
    console.log(`\n---> Testing Viewport: ${vp.name}`);
    const context = await browser.newContext({
      viewport: { width: vp.width, height: vp.height },
      isMobile: vp.isMobile,
      hasTouch: vp.isMobile,
    });
    const page = await context.newPage();
    await page.setContent(getShellHtml('تست ناوبری ادمین', true));
    await page.waitForTimeout(100);

    // 1. Horizontal Scroll Overflow Check
    const overflowCheck = await page.evaluate(() => {
      const doc = document.documentElement;
      return {
        scrollWidth: doc.scrollWidth,
        clientWidth: doc.clientWidth,
        isZeroOverflow: doc.scrollWidth <= doc.clientWidth + 1,
      };
    });
    assert(overflowCheck.isZeroOverflow, `Zero horizontal overflow (scrollWidth=${overflowCheck.scrollWidth}, clientWidth=${overflowCheck.clientWidth})`);

    if (vp.isMobile) {
      // 2. Mobile Menu Button Visibility (< 1024px)
      const menuBtnVisible = await page.evaluate(() => {
        const btn = document.getElementById('adminMobileMenuBtn');
        if (!btn) return false;
        const style = window.getComputedStyle(btn);
        return style.display !== 'none' && btn.offsetWidth > 0;
      });
      assert(menuBtnVisible, 'Mobile hamburger button (#adminMobileMenuBtn) is visible in topbar');

      // 3. Desktop Sidebar Toggle Button Hidden (< 1024px)
      const toggleBtnHidden = await page.evaluate(() => {
        const btn = document.getElementById('sidebarToggleBtn');
        if (!btn) return true;
        const style = window.getComputedStyle(btn);
        return style.display === 'none';
      });
      assert(toggleBtnHidden, 'Desktop sidebar toggle (#sidebarToggleBtn) is hidden on mobile');

      // 4. Initial Off-Canvas Sidebar State (closed, not visible on screen)
      const sidebarInitialOffCanvas = await page.evaluate(() => {
        const sidebar = document.getElementById('adminSidebar');
        const style = window.getComputedStyle(sidebar);
        return !sidebar.classList.contains('is-open') && style.visibility === 'hidden';
      });
      assert(sidebarInitialOffCanvas, 'Sidebar is off-canvas with visibility:hidden when initial closed');

      // 5. Open Drawer via Hamburger Click
      await page.click('#adminMobileMenuBtn');
      await page.waitForTimeout(100);

      const drawerIsOpen = await page.evaluate(() => {
        const sidebar = document.getElementById('adminSidebar');
        const body = document.body;
        const btn = document.getElementById('adminMobileMenuBtn');
        const style = window.getComputedStyle(sidebar);
        return (
          sidebar.classList.contains('is-open') &&
          body.classList.contains('admin-mobile-nav-open') &&
          btn.getAttribute('aria-expanded') === 'true' &&
          style.visibility === 'visible'
        );
      });
      assert(drawerIsOpen, 'Hamburger click opens drawer (adds .is-open, .admin-mobile-nav-open, aria-expanded=true, visibility:visible)');

      // 6. Close Drawer via Close Button Click
      await page.click('#adminSidebarCloseBtn');
      await page.waitForFunction(() => !document.getElementById('adminSidebar').classList.contains('is-open'));
      await page.waitForTimeout(400);

      const drawerIsClosed = await page.evaluate(() => {
        const sidebar = document.getElementById('adminSidebar');
        const body = document.body;
        const btn = document.getElementById('adminMobileMenuBtn');
        const style = window.getComputedStyle(sidebar);
        return (
          !sidebar.classList.contains('is-open') &&
          !body.classList.contains('admin-mobile-nav-open') &&
          btn.getAttribute('aria-expanded') === 'false' &&
          style.visibility === 'hidden'
        );
      });
      assert(drawerIsClosed, 'Close button click closes drawer (removes .is-open, resets aria-expanded=false, visibility:hidden)');

      // 7. Open Drawer and Close via Escape key
      await page.click('#adminMobileMenuBtn');
      await page.waitForTimeout(50);
      await page.keyboard.press('Escape');
      await page.waitForTimeout(100);

      const drawerClosedOnEscape = await page.evaluate(() => {
        const sidebar = document.getElementById('adminSidebar');
        return !sidebar.classList.contains('is-open');
      });
      assert(drawerClosedOnEscape, 'Escape key closes open mobile drawer');

      // 8. Open Drawer and Close via Backdrop Click
      await page.click('#adminMobileMenuBtn');
      await page.waitForTimeout(50);
      await page.click('#adminSidebarBackdrop', { position: { x: 10, y: 10 }, force: true });
      await page.waitForTimeout(100);

      const drawerClosedOnBackdrop = await page.evaluate(() => {
        const sidebar = document.getElementById('adminSidebar');
        return !sidebar.classList.contains('is-open');
      });
      assert(drawerClosedOnBackdrop, 'Backdrop click closes open mobile drawer');

      // 9. Bottom Nav Visibility
      const bnbVisible = await page.evaluate(() => {
        const bnb = document.querySelector('.admin-bottom-nav');
        if (!bnb) return false;
        const style = window.getComputedStyle(bnb);
        return style.display === 'flex';
      });
      assert(bnbVisible, 'Bottom Navigation Bar (BNB) is visible on mobile hub page');

    } else {
      // Desktop checks (>= 1024px)
      const menuBtnHidden = await page.evaluate(() => {
        const btn = document.getElementById('adminMobileMenuBtn');
        if (!btn) return true;
        const style = window.getComputedStyle(btn);
        return style.display === 'none';
      });
      assert(menuBtnHidden, 'Mobile hamburger button (#adminMobileMenuBtn) is hidden on desktop');

      const sidebarDesktopVisible = await page.evaluate(() => {
        const sidebar = document.getElementById('adminSidebar');
        const style = window.getComputedStyle(sidebar);
        return style.display !== 'none' && style.visibility !== 'hidden' && style.position === 'sticky';
      });
      assert(sidebarDesktopVisible, 'Desktop sidebar is sticky and visible');

      const bnbHidden = await page.evaluate(() => {
        const bnb = document.querySelector('.admin-bottom-nav');
        if (!bnb) return true;
        const style = window.getComputedStyle(bnb);
        return style.display === 'none';
      });
      assert(bnbHidden, 'Bottom Navigation Bar (BNB) is hidden on desktop');
    }

    await context.close();
  }

  // Test Subpage without BNB (e.g. editor workstation page)
  console.log('\n---> Testing Subpage without BNB (Collision Safety on Editors)');
  const subContext = await browser.newContext({
    viewport: { width: 390, height: 844 },
    isMobile: true,
    hasTouch: true,
  });
  const subPage = await subContext.newPage();
  await subPage.setContent(getShellHtml('ویرایش محصول', false));
  await subPage.waitForTimeout(100);

  const bnbOmitted = await subPage.evaluate(() => {
    return document.querySelector('.admin-bottom-nav') === null;
  });
  assert(bnbOmitted, 'BNB is omitted on editor pages (preventing layer collisions)');

  const subMenuBtnVisible = await subPage.evaluate(() => {
    const btn = document.getElementById('adminMobileMenuBtn');
    return btn && window.getComputedStyle(btn).display !== 'none';
  });
  assert(subMenuBtnVisible, 'Mobile hamburger button remains functional on editor subpage');

  await subPage.click('#adminMobileMenuBtn');
  await subPage.waitForTimeout(50);
  const subDrawerOpen = await subPage.evaluate(() => {
    return document.getElementById('adminSidebar').classList.contains('is-open');
  });
  assert(subDrawerOpen, 'User can open navigation drawer from editor subpage without being trapped');

  await subContext.close();
  await browser.close();

  console.log(`\n========================================================================`);
  console.log(`Results: ${passedTests}/${totalTests} Passed | ${failedTests} Failed`);
  console.log(`========================================================================\n`);

  if (failedTests > 0) {
    process.exit(1);
  }
}

runTests().catch((err) => {
  console.error('[FATAL]', err);
  process.exit(1);
});
