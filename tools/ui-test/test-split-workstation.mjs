/**
 * Standalone Test Harness for Split View Workstation (.ab-split)
 * Tests master list, item styling, active indicator, detail pane switching,
 * and responsive container query collapsing with back button navigation.
 */

import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, '../..');

const tokensCss = fs.readFileSync(path.join(projectRoot, 'assets/css/admin-tokens.css'), 'utf-8');
const baseCss = fs.readFileSync(path.join(projectRoot, 'assets/css/admin-base.css'), 'utf-8');
const shellCss = fs.readFileSync(path.join(projectRoot, 'assets/css/admin-shell.css'), 'utf-8');
const compCss = fs.readFileSync(path.join(projectRoot, 'assets/css/admin-components.css'), 'utf-8');
const utilCss = fs.readFileSync(path.join(projectRoot, 'assets/css/admin-utilities.css'), 'utf-8');
const abKitJs = fs.readFileSync(path.join(projectRoot, 'assets/js/ab-kit.js'), 'utf-8');

function generateTestHtml() {
  return `<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Split View Workstation Test</title>
<style>
${tokensCss}
${baseCss}
${shellCss}
${compCss}
${utilCss}

.test-wrapper {
  padding: 1.5rem;
  max-width: 100%;
}
</style>
<script>
${abKitJs}
</script>
</head>
<body class="admin-body">
  <div class="test-wrapper">
    <div class="ab-split" id="testSplit" data-split-view="master">
      <div class="ab-split__master">
        <div class="ab-toolbar">
          <span class="ab-text-sm ab-text-muted">فهرست سفارش‌ها (Master)</span>
          <span class="ab-badge ab-badge-muted"><span class="ab-badge-text">۲ مورد</span></span>
        </div>
        <div class="ab-split__list">
          <button type="button" class="ab-split__item is-active" id="item1" data-ab-split-select="pane-1" aria-selected="true">
            <div class="ab-split__item-header">
              <span class="ab-split__item-title">سفارش شماره ۱۰۸۸</span>
              <span class="ab-badge ab-badge-success"><span class="ab-badge-text">ارسال شده</span></span>
            </div>
            <div class="ab-split__item-meta">
              <span>علی رضایی</span>
              <strong>۲۴۰,۰۰۰ تومان</strong>
            </div>
          </button>
          <button type="button" class="ab-split__item" id="item2" data-ab-split-select="pane-2" aria-selected="false">
            <div class="ab-split__item-header">
              <span class="ab-split__item-title">سفارش شماره ۱۰۸۹</span>
              <span class="ab-badge ab-badge-warning"><span class="ab-badge-text">در انتظار پرداخت</span></span>
            </div>
            <div class="ab-split__item-meta">
              <span>فاطمه موسوی</span>
              <strong>۱۸۵,۰۰۰ تومان</strong>
            </div>
          </button>
        </div>
      </div>
      <div class="ab-split__detail">
        <div class="ab-toolbar">
          <button type="button" class="ab-btn ab-btn--sm ab-btn--secondary ab-split__back" id="backBtn" data-ab-split-back>
            بازگشت به فهرست
          </button>
          <span class="ab-text-sm ab-text-muted">جزئیات سفارش انتخابی (Detail)</span>
        </div>

        <div class="ab-split__pane" id="pane1" data-split-pane="pane-1">
          <div class="ab-split__pane-header">
            <div class="ab-stack ab-stack--xs">
              <h4 class="ab-split__pane-title">جزئیات سفارش ۱۰۸۸</h4>
              <span class="ab-text-xs ab-text-muted">ثبت شده در ۱۵ مهر ۱۴۰۳</span>
            </div>
            <div class="ab-split__pane-actions">
              <button type="button" class="ab-btn ab-btn--sm ab-btn--outline">چاپ فاکتور</button>
            </div>
          </div>
          <div class="ab-split__pane-section">
            <h5 class="ab-split__pane-section-title">اطلاعات خریدار</h5>
            <dl class="ab-dl">
              <dt class="ab-dl__label">نام خریدار:</dt>
              <dd class="ab-dl__value">علی رضایی</dd>
            </dl>
          </div>
        </div>

        <div class="ab-split__pane" id="pane2" data-split-pane="pane-2" hidden>
          <div class="ab-split__pane-header">
            <div class="ab-stack ab-stack--xs">
              <h4 class="ab-split__pane-title">جزئیات سفارش ۱۰۸۹</h4>
              <span class="ab-text-xs ab-text-muted">ثبت شده در ۱۴ مهر ۱۴۰۳</span>
            </div>
          </div>
          <div class="ab-split__pane-section">
            <h5 class="ab-split__pane-section-title">اطلاعات خریدار</h5>
            <dl class="ab-dl">
              <dt class="ab-dl__label">نام خریدار:</dt>
              <dd class="ab-dl__value">فاطمه موسوی</dd>
            </dl>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>`;
}

async function run() {
  console.log('🚀 Starting Split View Workstation Playwright Test Suite...\n');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext();
  const page = await context.newPage();

  const html = generateTestHtml();
  await page.setContent(html, { waitUntil: 'domcontentloaded' });

  let passed = 0;
  let failed = 0;

  function assert(condition, message) {
    if (condition) {
      console.log(`  ✅ [PASS] ${message}`);
      passed++;
    } else {
      console.error(`  ❌ [FAIL] ${message}`);
      failed++;
    }
  }

  // --- Suite 1: Desktop Dual-Pane Layout & Styling ---
  console.log('--- Test Suite 1: Desktop Dual-Pane Viewport (1200px) ---');
  await page.setViewportSize({ width: 1200, height: 800 });
  await page.waitForTimeout(50);

  // Both master and detail must be visible simultaneously on desktop
  const masterVisible = await page.$eval('.ab-split__master', el => window.getComputedStyle(el).display !== 'none');
  const detailVisible = await page.$eval('.ab-split__detail', el => window.getComputedStyle(el).display !== 'none');
  assert(masterVisible && detailVisible, 'Both master and detail columns are displayed side-by-side on desktop');

  // Back button must NOT be displayed on desktop
  const backBtnVisible = await page.$eval('#backBtn', el => window.getComputedStyle(el).display === 'none');
  assert(backBtnVisible, 'Back button is hidden on desktop wide viewport');

  // Verify item 1 has active styling
  const item1Styles = await page.$eval('#item1', el => {
    const s = window.getComputedStyle(el);
    return {
      borderInlineStartWidth: s.borderInlineStartWidth,
      borderInlineStartStyle: s.borderInlineStartStyle,
    };
  });
  assert(item1Styles.borderInlineStartWidth === '3px' && item1Styles.borderInlineStartStyle === 'solid', 
    `Active item has 3px solid indicator bar (got ${item1Styles.borderInlineStartWidth} ${item1Styles.borderInlineStartStyle})`
  );

  // Verify pane 1 is visible and pane 2 is hidden initially
  const pane1Hidden = await page.$eval('#pane1', el => el.hidden);
  const pane2Hidden = await page.$eval('#pane2', el => el.hidden);
  assert(!pane1Hidden && pane2Hidden, 'Pane 1 is visible and Pane 2 is hidden initially');

  // --- Suite 2: Workstation Item Interaction & Switching ---
  console.log('\n--- Test Suite 2: Item Selection & Detail Switching ---');
  await page.click('#item2');
  await page.waitForTimeout(50);

  const item1IsActiveAfterClick = await page.$eval('#item1', el => el.classList.contains('is-active'));
  const item2IsActiveAfterClick = await page.$eval('#item2', el => el.classList.contains('is-active'));
  assert(!item1IsActiveAfterClick && item2IsActiveAfterClick, 'Clicking Item 2 transfers .is-active class to Item 2');

  const pane1HiddenAfterClick = await page.$eval('#pane1', el => el.hidden);
  const pane2HiddenAfterClick = await page.$eval('#pane2', el => el.hidden);
  assert(pane1HiddenAfterClick && !pane2HiddenAfterClick, 'Pane 2 is now unhidden and Pane 1 is hidden');

  // --- Suite 3: Responsive Collapsing (< 48rem, e.g. 600px) ---
  console.log('\n--- Test Suite 3: Narrow Container Responsive Collapse (500px, < 48rem) ---');
  await page.setViewportSize({ width: 500, height: 750 });
  await page.waitForTimeout(50);

  // Since #testSplit has data-split-view="detail" after clicking item 2:
  // In detail mode, detail must be visible, master must be hidden, back button must be visible
  const narrowMasterHiddenInDetail = await page.$eval('.ab-split__master', el => window.getComputedStyle(el).display === 'none');
  const narrowDetailVisibleInDetail = await page.$eval('.ab-split__detail', el => window.getComputedStyle(el).display !== 'none');
  const narrowBackBtnVisible = await page.$eval('#backBtn', el => window.getComputedStyle(el).display !== 'none');

  assert(narrowMasterHiddenInDetail, 'Master column is hidden on mobile when viewing detail');
  assert(narrowDetailVisibleInDetail, 'Detail column occupies 100% width on mobile');
  assert(narrowBackBtnVisible, 'Back button is visible on mobile when viewing detail');

  // Click back button to return to master view
  console.log('\n--- Test Suite 4: Mobile Back Button Navigation ---');
  await page.click('#backBtn');
  await page.waitForTimeout(50);

  const splitViewAfterBack = await page.$eval('#testSplit', el => el.getAttribute('data-split-view'));
  assert(splitViewAfterBack === 'master', `Split container state transitioned to 'master' (got '${splitViewAfterBack}')`);

  const narrowMasterVisibleInMaster = await page.$eval('.ab-split__master', el => window.getComputedStyle(el).display !== 'none');
  const narrowDetailHiddenInMaster = await page.$eval('.ab-split__detail', el => window.getComputedStyle(el).display === 'none');

  assert(narrowMasterVisibleInMaster, 'Master list is visible after clicking back button');
  assert(narrowDetailHiddenInMaster, 'Detail view is hidden after returning to master view');

  console.log(`\n========================================`);
  console.log(`Summary: ${passed} passed, ${failed} failed`);
  console.log(`========================================\n`);

  await browser.close();
  if (failed > 0) {
    process.exit(1);
  }
}

run().catch(err => {
  console.error(err);
  process.exit(1);
});
