import { chromium } from 'playwright';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const authStateFile = path.resolve(__dirname, '.auth/state_absocks_test_Abeh.json');
const targetUrl = 'https://absocks.test/admin/users.php';

async function run() {
  console.log(`[TEST] Exercising Users page on ${targetUrl}...`);
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

  // Step 1: Navigate to users.php
  const response = await page.goto(targetUrl, { waitUntil: 'domcontentloaded' });
  const status = response ? response.status() : null;
  console.log(`[NAV] HTTP Status: ${status}`);
  if (status !== 200) {
    throw new Error(`Expected HTTP 200, got ${status}`);
  }

  // Step 2: Exercise Modal 1 - Create Admin
  console.log('[MODAL 1] Testing Create Admin modal...');
  const createBtn = page.locator('[data-ab-modal-open="modalCreateAdmin"]').first();
  await createBtn.click();
  await page.waitForTimeout(200);

  const createDialog = page.locator('#modalCreateAdmin');
  const isCreateOpen = await createDialog.evaluate(el => el.hasAttribute('open'));
  console.log(`   * Create Modal opened: ${isCreateOpen}`);
  if (!isCreateOpen) throw new Error('Create Modal failed to open');

  // Close via cancel button
  const cancelBtn = createDialog.locator('button[data-ab-modal-close], button:has-text("انصراف")').first();
  await cancelBtn.click();
  await page.waitForTimeout(200);
  const isCreateClosed = await createDialog.evaluate(el => !el.hasAttribute('open'));
  console.log(`   * Create Modal closed: ${isCreateClosed}`);
  if (!isCreateClosed) throw new Error('Create Modal failed to close');

  // Step 3: Exercise Modal 2 - Edit Admin (open and populate)
  console.log('[MODAL 2] Testing Edit Admin modal...');
  const editBtn = page.locator('button[data-edit-user]').first();
  await editBtn.click();
  await page.waitForTimeout(200);

  const editDialog = page.locator('#modalEditAdmin');
  const isEditOpen = await editDialog.evaluate(el => el.hasAttribute('open'));
  console.log(`   * Edit Modal opened: ${isEditOpen}`);
  if (!isEditOpen) throw new Error('Edit Modal failed to open');

  const editFullNameInput = editDialog.locator('input[name="full_name"]');
  const currentVal = await editFullNameInput.inputValue();
  console.log(`   * Edit Modal populated with full_name: "${currentVal}"`);
  if (!currentVal) throw new Error('Edit Modal did not populate full_name');

  // Step 4: Submit non-destructive form
  console.log('[FORM SUBMISSION] Submitting Edit Admin form...');
  const updatedVal = currentVal.includes('(Updated)') ? currentVal.replace(' (Updated)', '') : currentVal + ' (Updated)';
  await editFullNameInput.fill(updatedVal);

  await Promise.all([
    page.waitForResponse(resp => resp.url().includes('users.php') && resp.status() === 200),
    editDialog.locator('button[type="submit"]:has-text("ذخیره تغییرات")').click()
  ]);
  await page.waitForLoadState('domcontentloaded');
  console.log(`   * Form submitted successfully! Post-submit URL: ${page.url()}`);

  const pageBody = await page.content();
  const hasSuccessMessage = pageBody.includes('تغییرات با موفقیت ذخیره شد') || pageBody.includes('موفق');
  console.log(`   * Success feedback confirmed: ${hasSuccessMessage}`);

  // Step 5: Exercise Modal 3 - Password Change
  console.log('[MODAL 3] Testing Password Change modal...');
  const pwBtn = page.locator('button[data-pw-id]').first();
  await pwBtn.click();
  await page.waitForTimeout(200);

  const pwDialog = page.locator('#modalPasswordAdmin');
  const isPwOpen = await pwDialog.evaluate(el => el.hasAttribute('open'));
  console.log(`   * Password Modal opened: ${isPwOpen}`);
  if (!isPwOpen) throw new Error('Password Modal failed to open');

  const pwUserDisplay = await pwDialog.locator('#pwAdminUsername').textContent();
  console.log(`   * Password Modal context user: "${pwUserDisplay.trim()}"`);

  // Close password modal
  await pwDialog.locator('button[data-ab-modal-close], button:has-text("انصراف")').first().click();
  await page.waitForTimeout(200);
  const isPwClosed = await pwDialog.evaluate(el => !el.hasAttribute('open'));
  console.log(`   * Password Modal closed: ${isPwClosed}`);
  if (!isPwClosed) throw new Error('Password Modal failed to close');

  // Step 6: Exercise Filter Chips
  console.log('[FILTER CHIPS] Testing quick filter chips...');
  const getVisibleRows = async () => {
    return await page.locator('#adminUsersTable tbody tr:not([style*="display: none"])').count();
  };

  const totalRows = await getVisibleRows();
  console.log(`   * Total rows initially: ${totalRows}`);

  // Click 'مدیران عادی'
  const filterAdmin = page.locator('.ab-chip[data-filter="admin"]');
  if (await filterAdmin.count() > 0) {
    await filterAdmin.click();
    await page.waitForTimeout(150);
    const regularCount = await getVisibleRows();
    console.log(`   * Filter 'مدیران عادی' clicked -> Visible rows: ${regularCount}`);
  }

  // Click 'مدیران ارشد'
  const filterSuper = page.locator('.ab-chip[data-filter="super_admin"]');
  if (await filterSuper.count() > 0) {
    await filterSuper.click();
    await page.waitForTimeout(150);
    const superCount = await getVisibleRows();
    console.log(`   * Filter 'مدیران ارشد' clicked -> Visible rows: ${superCount}`);
  }

  // Reset filter by clicking 'همه'
  const filterAll = page.locator('.ab-chip[data-filter="all"]');
  await filterAll.click();
  await page.waitForTimeout(150);
  const resetCount = await getVisibleRows();
  console.log(`   * Filter 'همه' clicked -> Visible rows: ${resetCount}`);
  if (resetCount !== totalRows) throw new Error('Filter reset failed');

  // Step 7: Exercise Live Search
  console.log('[LIVE SEARCH] Testing table live search...');
  const searchInput = page.locator('.ab-toolbar__search input');
  await searchInput.fill('Bihir');
  await page.waitForTimeout(150);
  const searchCount = await getVisibleRows();
  console.log(`   * Search 'Bihir' -> Visible rows: ${searchCount}`);

  await searchInput.fill('');
  await page.waitForTimeout(150);
  const clearedCount = await getVisibleRows();
  console.log(`   * Search cleared -> Visible rows: ${clearedCount}`);
  if (clearedCount !== totalRows) throw new Error('Search clear failed');

  // Step 8: Console error verification
  console.log(`[CONSOLE] Total console errors encountered: ${consoleErrors.length}`);
  if (consoleErrors.length > 0) {
    console.warn('   Errors:', consoleErrors);
    throw new Error('Console errors were logged during test execution');
  }

  await browser.close();
  console.log('[SUCCESS] All users.php verification checks passed with 100% success!');
}

run().catch(err => {
  console.error('[FAIL]', err);
  process.exit(1);
});
