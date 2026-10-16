import { chromium } from 'playwright';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const authStateFile = path.resolve(__dirname, '.auth/state_absocks_test_admin.json');

async function testInteractions() {
  console.log('[TEST] Starting Interactive Verification for coupons.php & tags.php...');
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
      consoleErrors.push(`[CONSOLE ERROR] ${msg.text()}`);
    }
  });
  page.on('pageerror', err => {
    consoleErrors.push(`[PAGE ERROR] ${err.message}`);
  });

  // =========================================================================
  // 1. Tags Page Verification
  // =========================================================================
  console.log('\n--- 1. Testing tags.php ---');
  let res = await page.goto('https://absocks.test/admin/tags.php', { waitUntil: 'domcontentloaded' });
  console.log(`* HTTP Status: ${res.status()}`);
  if (res.status() !== 200) throw new Error(`tags.php returned status ${res.status()}`);

  // Test Create Tag Modal
  console.log('* Opening Create Tag Modal...');
  const addTagBtn = page.locator('button[data-ab-modal-open="tagModal"], a[data-ab-modal-open="tagModal"]').first();
  await addTagBtn.click();
  await page.waitForTimeout(300);

  const tagModal = page.locator('#tagModal');
  const isTagOpen = await tagModal.evaluate(el => el.hasAttribute('open'));
  console.log(`  - Tag Modal open: ${isTagOpen}`);
  if (!isTagOpen) throw new Error('Tag Modal did not open');

  // Close Tag Modal
  console.log('* Closing Tag Modal...');
  const closeTagBtn = tagModal.locator('[data-ab-modal-close]').first();
  await closeTagBtn.click();
  await page.waitForTimeout(300);
  const isTagClosed = await tagModal.evaluate(el => !el.hasAttribute('open'));
  console.log(`  - Tag Modal closed: ${isTagClosed}`);
  if (!isTagClosed) throw new Error('Tag Modal failed to close');

  // Test Edit Tag button
  const editTagBtn = page.locator('[data-tag-edit]').first();
  if (await editTagBtn.count() > 0) {
    console.log('* Testing Edit Tag delegation...');
    const tagName = await editTagBtn.getAttribute('data-name');
    await editTagBtn.click();
    await page.waitForTimeout(300);

    const inputVal = await page.locator('#tagNameInput').inputValue();
    console.log(`  - Modal populated with name: "${inputVal}" (matches "${tagName}")`);
    if (inputVal !== tagName) throw new Error(`Tag modal name mismatch: expected "${tagName}", got "${inputVal}"`);

    await tagModal.locator('[data-ab-modal-close]').first().click();
    await page.waitForTimeout(200);
  }

  // Non-destructive search submit
  console.log('* Submitting search form on tags...');
  await page.fill('input[name="q"]', 'test-search');
  await page.press('input[name="q"]', 'Enter');
  await page.waitForLoadState('domcontentloaded');
  console.log(`  - Search URL: ${page.url()}`);

  // Reset back to main tags page
  await page.goto('https://absocks.test/admin/tags.php', { waitUntil: 'domcontentloaded' });

  // =========================================================================
  // 2. Coupons Page Verification
  // =========================================================================
  console.log('\n--- 2. Testing coupons.php ---');
  res = await page.goto('https://absocks.test/admin/coupons.php', { waitUntil: 'domcontentloaded' });
  console.log(`* HTTP Status: ${res.status()}`);
  if (res.status() !== 200) throw new Error(`coupons.php returned status ${res.status()}`);

  // Test Create Coupon Modal
  console.log('* Opening Create Coupon Modal...');
  const addCouponBtn = page.locator('button[data-ab-modal-open="couponModal"], a[data-ab-modal-open="couponModal"]').first();
  await addCouponBtn.click();
  await page.waitForTimeout(300);

  const couponModal = page.locator('#couponModal');
  const isCouponOpen = await couponModal.evaluate(el => el.hasAttribute('open'));
  console.log(`  - Coupon Modal open: ${isCouponOpen}`);
  if (!isCouponOpen) throw new Error('Coupon Modal did not open');

  // Test coupon type toggle: fixed
  console.log('* Toggling coupon type to fixed...');
  await page.selectOption('#couponTypeSelect', 'fixed');
  await page.waitForTimeout(100);
  const fixedLabel = await page.locator('#couponValueLabel').textContent();
  console.log(`  - Value label for fixed: "${fixedLabel.trim()}"`);
  if (!fixedLabel.includes('مبلغ تخفیف')) throw new Error('Fixed type label mismatch');

  // Test coupon type toggle: free_shipping
  console.log('* Toggling coupon type to free_shipping...');
  await page.selectOption('#couponTypeSelect', 'free_shipping');
  await page.waitForTimeout(100);
  const isValueHidden = await page.locator('#couponValueGroup').evaluate(el => el.style.display === 'none');
  console.log(`  - Value group hidden for free_shipping: ${isValueHidden}`);
  if (!isValueHidden) throw new Error('Value group not hidden for free_shipping');

  // Test random code generation button
  console.log('* Testing random code generation via AJAX...');
  await page.locator('[data-action="generate-random-code"]').click();
  await page.waitForTimeout(800);
  const generatedCode = await page.locator('#couponCodeInput').inputValue();
  console.log(`  - Generated random code: "${generatedCode}"`);
  if (!generatedCode || !generatedCode.startsWith('OFF')) {
    throw new Error(`Random code generation failed, got: "${generatedCode}"`);
  }

  // Close Coupon Modal
  console.log('* Closing Coupon Modal...');
  await couponModal.locator('[data-ab-modal-close]').first().click();
  await page.waitForTimeout(300);
  const isCouponClosed = await couponModal.evaluate(el => !el.hasAttribute('open'));
  console.log(`  - Coupon Modal closed: ${isCouponClosed}`);
  if (!isCouponClosed) throw new Error('Coupon Modal failed to close');

  // Test Copy Code Toast
  const copyBtn = page.locator('[data-copy-code]').first();
  if (await copyBtn.count() > 0) {
    console.log('* Testing 1-click code copy button...');
    await copyBtn.click();
    await page.waitForTimeout(300);
    const toast = page.locator('.ab-toast');
    const toastCount = await toast.count();
    console.log(`  - Toast notification triggered: ${toastCount > 0}`);
  }

  // Test Analytics Drawer
  const statsBtn = page.locator('[data-coupon-stats]').first();
  if (await statsBtn.count() > 0) {
    console.log('* Testing Analytics Drawer...');
    const couponCode = await statsBtn.getAttribute('data-code');
    await statsBtn.click();
    await page.waitForTimeout(600);

    const drawer = page.locator('#couponDrawer');
    const isDrawerOpen = await drawer.evaluate(el => el.hasAttribute('open'));
    console.log(`  - Coupon Drawer open: ${isDrawerOpen}`);
    if (!isDrawerOpen) throw new Error('Coupon Drawer did not open');

    const drawerCodeText = await page.locator('#drawerCouponCode').textContent();
    console.log(`  - Drawer coupon code header: "${drawerCodeText.trim()}" (expected "${couponCode}")`);
    if (drawerCodeText.trim() !== couponCode) throw new Error('Drawer code mismatch');

    // Wait for performance fetch to complete
    await page.waitForTimeout(500);
    const isContentVisible = await page.locator('#drawerContent').evaluate(el => !el.hidden && el.style.display !== 'none');
    console.log(`  - Drawer analytics content loaded: ${isContentVisible}`);

    // Close drawer
    await drawer.locator('[data-ab-modal-close]').first().click();
    await page.waitForTimeout(300);
    const isDrawerClosed = await drawer.evaluate(el => !el.hasAttribute('open'));
    console.log(`  - Drawer closed: ${isDrawerClosed}`);
    if (!isDrawerClosed) throw new Error('Coupon Drawer failed to close');
  }

  // Test quick filter chip: active
  console.log('* Testing quick filter status chip...');
  const activeChip = page.locator('a.ab-chip:has-text("فعال")').first();
  await activeChip.click();
  await page.waitForLoadState('domcontentloaded');
  console.log(`  - Filter URL: ${page.url()}`);
  if (!page.url().includes('status=active')) throw new Error('Status filter did not navigate');

  // Check console errors
  console.log(`\n* Total Console / Page Errors: ${consoleErrors.length}`);
  if (consoleErrors.length > 0) {
    console.error(consoleErrors);
    throw new Error(`Encountered ${consoleErrors.length} console errors during interaction`);
  }

  await browser.close();
  console.log('\n[SUCCESS] All interactive tests passed with 0 console errors!');
}

testInteractions().catch(err => {
  console.error('[FATAL]', err);
  process.exit(1);
});
