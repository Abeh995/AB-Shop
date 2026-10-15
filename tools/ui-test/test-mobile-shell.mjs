import { chromium } from 'playwright';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const authStateFile = path.resolve(__dirname, '.auth/state_absocks_test_Abeh.json');
const testPages = [
  { id: 'orders', path: '/admin/orders.php' },
  { id: 'products', path: '/admin/products.php' },
  { id: 'finance_dashboard', path: '/admin/finance_dashboard.php' },
  { id: 'settings', path: '/admin/settings.php' }
];

async function run() {
  console.log('[TEST] Verifying mobile shell/nav behavior on legacy pages...');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    storageState: authStateFile,
    ignoreHTTPSErrors: true,
    viewport: { width: 390, height: 844 },
    isMobile: true,
    hasTouch: true
  });

  const page = await context.newPage();
  const results = [];

  for (const p of testPages) {
    const url = `https://absocks.test${p.path}`;
    console.log(`\n---> Testing ${p.id} (${url})`);
    const consoleErrors = [];
    page.on('console', msg => {
      if (msg.type() === 'error') consoleErrors.push(msg.text());
    });

    const res = await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 20000 });
    const status = res ? res.status() : 0;
    console.log(`   HTTP Status: ${status}`);

    // Check hamburger button
    const hamburger = page.locator('#adminMobileMenuBtn');
    const hasHamburger = (await hamburger.count()) > 0 && await hamburger.isVisible();
    console.log(`   Hamburger button visible: ${hasHamburger}`);

    // Click hamburger to open sidebar (use force: true to prevent Playwright RTL auto-scroll distortion on overflowing legacy pages)
    await hamburger.click({ force: true });
    await page.waitForTimeout(350);

    const sidebar = page.locator('#adminSidebar');
    const isOpen = await sidebar.evaluate(el => el.classList.contains('is-open'));
    console.log(`   Sidebar opened (.is-open): ${isOpen}`);

    // Take screenshot of open drawer on mobile
    const ssDir = path.join(__dirname, 'output/mobile-shell-check');
    fs.mkdirSync(ssDir, { recursive: true });
    await page.screenshot({ path: path.join(ssDir, `${p.id}_sidebar_open.png`) });

    // Close via close button
    const closeBtn = page.locator('#adminSidebarCloseBtn');
    await closeBtn.click({ force: true });
    await page.waitForTimeout(350);

    const isClosed = await sidebar.evaluate(el => !el.classList.contains('is-open'));
    console.log(`   Sidebar closed (.is-open removed): ${isClosed}`);

    // Check bottom nav
    const bottomNav = page.locator('.admin-bottom-nav');
    const hasBnb = (await bottomNav.count()) > 0 && await bottomNav.isVisible();
    console.log(`   Bottom navigation visible: ${hasBnb}`);

    results.push({
      page: p.id,
      status,
      hasHamburger,
      isOpen,
      isClosed,
      hasBnb,
      consoleErrors: consoleErrors.length
    });
  }

  await browser.close();
  console.log('\n========================================');
  console.log('Mobile Shell Verification Summary:');
  console.table(results);
}

run().catch(err => {
  console.error('[FATAL]', err);
  process.exit(1);
});
