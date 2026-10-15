import { chromium } from 'playwright';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const authStateFile = path.resolve(__dirname, '.auth/state_absocks_test_Abeh.json');
const targetUrl = 'https://absocks.test/admin/ui-kit.php';

async function run() {
  console.log(`[TEST] Launching browser to exercise UI Kit components on ${targetUrl}...`);
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    storageState: authStateFile,
    ignoreHTTPSErrors: true,
    viewport: { width: 1280, height: 800 }
  });

  const page = await context.newPage();
  const consoleErrors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') {
      consoleErrors.push(msg.text());
    }
  });
  page.on('pageerror', err => {
    consoleErrors.push(err.message);
  });

  // Step 1: Navigate to ui-kit.php
  const response = await page.goto(targetUrl, { waitUntil: 'domcontentloaded' });
  const status = response ? response.status() : null;
  console.log(`[NAV] HTTP Status: ${status}`);
  if (status !== 200) {
    throw new Error(`Expected HTTP 200, got ${status}`);
  }

  // Step 2: Exercise Tabs
  console.log('[TABS] Exercising navigation tabs...');
  const tabs = ['buttons', 'tables', 'overlays', 'all'];
  for (const tabKey of tabs) {
    const btn = page.locator(`.ab-tab-btn[data-tab="${tabKey}"]`).first();
    await btn.click();
    await page.waitForTimeout(150);
    const pane = page.locator(`.ab-tab-pane[data-tab-pane="${tabKey}"]`).first();
    const isActive = await pane.evaluate(el => el.classList.contains('active'));
    console.log(`   * Tab [${tabKey}] clicked -> Pane active: ${isActive}`);
    if (!isActive) throw new Error(`Tab pane for ${tabKey} failed to activate`);
  }

  // Step 3: Exercise Modals (sm, md, lg, xl)
  console.log('[MODALS] Exercising all 4 modal sizes...');
  const modals = [
    { trigger: '[data-ab-modal-open="demoModalSm"]', id: 'demoModalSm' },
    { trigger: '[data-ab-modal-open="demoModalMd"]', id: 'demoModalMd' },
    { trigger: '[data-ab-modal-open="demoModalLg"]', id: 'demoModalLg' },
    { trigger: '[data-ab-modal-open="demoModalXl"]', id: 'demoModalXl' },
  ];

  for (const m of modals) {
    const btn = page.locator(m.trigger).first();
    await btn.scrollIntoViewIfNeeded();
    await btn.click();
    await page.waitForTimeout(200);

    const dialog = page.locator(`#${m.id}`);
    const isOpen = await dialog.evaluate(el => el.hasAttribute('open'));
    console.log(`   * Modal [${m.id}] opened: ${isOpen}`);
    if (!isOpen) throw new Error(`Modal ${m.id} failed to open`);

    // Close via close button or cancel
    const closeBtn = dialog.locator('[data-ab-modal-close], .ab-dialog-close, button:has-text("بستن")').first();
    if ((await closeBtn.count()) > 0) {
      await closeBtn.click();
    } else {
      await page.keyboard.press('Escape');
    }
    await page.waitForTimeout(200);

    const isClosed = await dialog.evaluate(el => !el.hasAttribute('open'));
    console.log(`   * Modal [${m.id}] closed: ${isClosed}`);
    if (!isClosed) throw new Error(`Modal ${m.id} failed to close`);
  }

  // Step 4: Exercise Drawers (end, start)
  console.log('[DRAWERS] Exercising side drawers (end, start)...');
  const drawers = [
    { trigger: '[data-ab-modal-open="demoDrawer"]', id: 'demoDrawer' },
    { trigger: '[data-ab-modal-open="demoDrawerStart"]', id: 'demoDrawerStart' },
  ];

  for (const d of drawers) {
    const btn = page.locator(d.trigger).first();
    await btn.scrollIntoViewIfNeeded();
    await btn.click();
    await page.waitForTimeout(200);

    const dialog = page.locator(`#${d.id}`);
    const isOpen = await dialog.evaluate(el => el.hasAttribute('open'));
    console.log(`   * Drawer [${d.id}] opened: ${isOpen}`);
    if (!isOpen) throw new Error(`Drawer ${d.id} failed to open`);

    // Close drawer
    const closeBtn = dialog.locator('[data-ab-modal-close], .ab-dialog-close, button:has-text("انصراف")').first();
    if ((await closeBtn.count()) > 0) {
      await closeBtn.click();
    } else {
      await page.keyboard.press('Escape');
    }
    await page.waitForTimeout(200);

    const isClosed = await dialog.evaluate(el => !el.hasAttribute('open'));
    console.log(`   * Drawer [${d.id}] closed: ${isClosed}`);
    if (!isClosed) throw new Error(`Drawer ${d.id} failed to close`);
  }

  // Step 5: Submit non-destructive form
  console.log('[FORM] Submitting non-destructive form...');
  const form = page.locator('#uiKitDemoForm');
  await form.scrollIntoViewIfNeeded();
  
  // Fill in demo fields
  await page.locator('input[name="demo_title"]').fill('جوراب کالج تست خودکار');
  await page.locator('input[name="demo_price"]').fill('68000');
  
  // Submit form
  await Promise.all([
    page.waitForResponse(resp => resp.url().includes('ui-kit.php')),
    page.locator('button[type="submit"]:has-text("ثبت آزمایشی فرم")').click()
  ]);
  await page.waitForLoadState('domcontentloaded');

  const afterUrl = page.url();
  console.log(`   * Form submitted. Post-submission URL: ${afterUrl}`);
  
  // Check for success flash or toast or banner
  const pageContent = await page.content();
  const hasFlash = pageContent.includes('اطلاعات فرم نمونه با موفقیت اعتبارسنجی و ثبت گردید');
  console.log(`   * Flash toast / message displayed: ${hasFlash}`);

  console.log(`[CONSOLE] Total console errors encountered: ${consoleErrors.length}`);
  if (consoleErrors.length > 0) {
    console.warn('   Errors:', consoleErrors);
    throw new Error('Console errors were logged during test execution');
  }

  await browser.close();
  console.log('[SUCCESS] All verification steps passed with 0 errors!');
}

run().catch(err => {
  console.error('[FAIL]', err);
  process.exit(1);
});
