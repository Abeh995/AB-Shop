import { chromium } from 'playwright';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const authStateFile = path.resolve(__dirname, '.auth/state_absocks_test_admin.json');

async function testProducts() {
  console.log('[TEST] Starting Interactive Verification for products.php...');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    storageState: authStateFile,
    ignoreHTTPSErrors: true,
    viewport: { width: 1280, height: 800 },
    permissions: ['clipboard-read', 'clipboard-write']
  });

  const page = await context.newPage();
  const consoleErrors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') {
      consoleErrors.push(`[CONSOLE ERROR] ${msg.text()}`);
    }
  });
  page.on('pageerror', err => {
    consoleErrors.push(`[PAGE ERROR] ${err.message}`);
  });

  // 1. Load Page & Verify HTTP 200
  console.log('\n--- 1. Testing products.php Load ---');
  const res = await page.goto('https://absocks.test/admin/products.php', { waitUntil: 'domcontentloaded' });
  console.log(`* HTTP Status: ${res.status()}`);
  if (res.status() !== 200) throw new Error(`products.php returned status ${res.status()}`);

  // 2. Verify Page Header & KPIs
  console.log('* Checking Page Header & Bento KPIs...');
  const headerTitle = await page.locator('.ab-page-header__title').textContent();
  console.log(`  - Header Title: ${headerTitle.trim()}`);
  const kpiCount = await page.locator('.ab-kpi-grid .ab-kpi-card').count();
  console.log(`  - KPI Cards rendered: ${kpiCount}`);
  if (kpiCount < 4) throw new Error(`Expected at least 4 KPI cards, found ${kpiCount}`);

  // 3. Test Drawer Open & Close
  console.log('\n--- 2. Testing Side Dossier Drawer ---');
  const inspectBtn = page.locator('.btn-inspect').first();
  await inspectBtn.click();
  await page.waitForTimeout(400);

  const drawer = page.locator('#prodDrawer');
  const isDrawerOpen = await drawer.evaluate(el => el.hasAttribute('open'));
  console.log(`  - Drawer open: ${isDrawerOpen}`);
  if (!isDrawerOpen) throw new Error('Drawer failed to open');

  const drawerName = await page.locator('#drawerName').textContent();
  console.log(`  - Inspected Product: ${drawerName.trim()}`);

  // Test Stepper inside drawer
  const stepperPlus = drawer.locator('.btn-step-plus').first();
  if (await stepperPlus.count() > 0) {
    const input = drawer.locator('.prod-stepper-input').first();
    const valBefore = parseInt(await input.inputValue(), 10);
    await stepperPlus.click();
    const valAfter = parseInt(await input.inputValue(), 10);
    console.log(`  - Stepper test: ${valBefore} -> ${valAfter}`);
    if (valAfter !== valBefore + 1) throw new Error('Stepper increment failed');
  }

  // Close Drawer
  const closeBtn = drawer.locator('[data-ab-modal-close]').first();
  await closeBtn.click();
  await page.waitForTimeout(400);
  const isDrawerClosed = await drawer.evaluate(el => !el.hasAttribute('open'));
  console.log(`  - Drawer closed: ${isDrawerClosed}`);
  if (!isDrawerClosed) throw new Error('Drawer failed to close');

  // 4. Test Checkbox Selection & Floating Bulk Bar
  console.log('\n--- 3. Testing Checkbox Selection & Bulk Bar ---');
  const rowCheck = page.locator('input[name="selected_ids[]"]').first();
  await rowCheck.check();
  await page.waitForTimeout(200);

  const bulkBar = page.locator('#prodBulkBar');
  const isBulkVisible = await bulkBar.evaluate(el => el.classList.contains('is-visible'));
  console.log(`  - Bulk bar visible on selection: ${isBulkVisible}`);
  if (!isBulkVisible) throw new Error('Bulk bar did not appear upon row selection');

  const bulkText = await page.locator('#prodBulkCount').textContent();
  console.log(`  - Bulk count text: ${bulkText.trim()}`);

  // Test Cancel Selection
  const cancelBtn = page.locator('#bulkCancel');
  await cancelBtn.click();
  await page.waitForTimeout(200);
  const isBulkHidden = await bulkBar.evaluate(el => !el.classList.contains('is-visible'));
  console.log(`  - Bulk bar hidden after cancel: ${isBulkHidden}`);
  if (!isBulkHidden) throw new Error('Bulk bar did not hide after cancel');

  // 5. Test Non-Destructive Filter Form Submit
  console.log('\n--- 4. Testing Filter Form Submit ---');
  const sortSelect = page.locator('select[name="sort"]');
  await sortSelect.selectOption('price_desc');
  const filterBtn = page.locator('button[type="submit"]:has-text("اعمال فیلتر")');
  await Promise.all([
    page.waitForNavigation({ waitUntil: 'domcontentloaded' }),
    filterBtn.click()
  ]);
  console.log(`  - Form submitted successfully. URL: ${page.url()}`);
  if (!page.url().includes('sort=price_desc')) throw new Error('Filter parameter not reflected in URL');

  // 6. Test SKU Copy Toast
  console.log('\n--- 5. Testing SKU Copy ---');
  const skuChip = page.locator('.prod-sku-chip').first();
  if (await skuChip.count() > 0) {
    await skuChip.click();
    await page.waitForTimeout(300);
    const toastCount = await page.locator('.ab-toast, [role="status"]').count();
    console.log(`  - SKU copy triggered toast: ${toastCount > 0}`);
  }

  // 7. Verify Console Errors
  console.log('\n--- 6. Console Error Check ---');
  console.log(`* Console Errors count: ${consoleErrors.length}`);
  if (consoleErrors.length > 0) {
    console.error('Errors found:', consoleErrors);
    throw new Error(`Console errors detected: ${consoleErrors.join(', ')}`);
  }

  await browser.close();
  console.log('\n[PASS] All products.php interaction tests passed cleanly!\n');
}

testProducts().catch(err => {
  console.error('[FAIL]', err);
  process.exit(1);
});
