#!/usr/bin/env node
/**
 * Landing Studio end-to-end check (dev-only).
 *
 * Exercises the visual editor the way a store owner would: opens the studio,
 * edits copy, waits for autosave, confirms the iframe preview mirrors the change
 * through the postMessage bridge, publishes, and verifies the storefront got the
 * new snapshot. Also captures desktop/mobile screenshots into ./baseline.
 *
 * Usage:
 *   node test-landing-studio.mjs
 *   AB_BASE_URL=http://localhost:8080 AB_ADMIN_USER=admin AB_ADMIN_PASS=secret node test-landing-studio.mjs
 */

import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const BASE_URL = (process.env.AB_BASE_URL || 'http://localhost:8080').replace(/\/+$/, '');
const USER = process.env.AB_ADMIN_USER || 'admin';
const PASS = process.env.AB_ADMIN_PASS || 'test1234';
const OUT_DIR = path.join(__dirname, 'baseline');
const SHOTS = path.join(OUT_DIR, 'landing-studio');

const results = [];
function check(label, condition, detail = '') {
  results.push({ label, ok: Boolean(condition), detail });
  console.log(`${condition ? '  [PASS]' : '  [FAIL]'} ${label}${detail ? ' — ' + detail : ''}`);
}

async function main() {
  fs.mkdirSync(SHOTS, { recursive: true });

  const browser = await chromium.launch();
  const context = await browser.newContext({ viewport: { width: 1600, height: 1000 }, deviceScaleFactor: 1 });
  const page = await context.newPage();

  const consoleErrors = [];
  page.on('console', (msg) => {
    if (msg.type() === 'error') consoleErrors.push(msg.text());
  });
  page.on('pageerror', (error) => consoleErrors.push('pageerror: ' + error.message));

  // ---- login -------------------------------------------------------------
  await page.goto(`${BASE_URL}/admin/login.php`, { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="username"]', USER);
  await page.fill('input[name="password"]', PASS);
  await Promise.all([page.waitForLoadState('networkidle'), page.click('button[type="submit"], input[type="submit"]')]);
  check('admin login', page.url().includes('/admin/index.php') || !page.url().includes('login.php'), page.url());

  // ---- studio boots ------------------------------------------------------
  await page.goto(`${BASE_URL}/admin/landing.php`, { waitUntil: 'domcontentloaded' });
  await page.waitForSelector('.lb-rail-item', { timeout: 15000 });
  const railCount = await page.locator('.lb-rail-item').count();
  check('section rail renders', railCount >= 20, `${railCount} sections`);
  const groupCount = await page.locator('.lb-group').count();
  check('field panel renders', groupCount > 0, `${groupCount} groups`);
  const inputCount = await page.locator('.lb-panel-body input, .lb-panel-body textarea, .lb-panel-body select').count();
  check('editable controls render', inputCount > 3, `${inputCount} controls`);

  const foldGap = await page.evaluate(() => document.documentElement.scrollHeight - window.innerHeight);
  check('studio fills the fold (no dead page scroll)', foldGap <= 2, `${foldGap}px`);

  // ---- live preview ------------------------------------------------------
  const frame = page.frameLocator('#lbPreviewFrame');
  await frame.locator('body').waitFor({ timeout: 20000 });
  await page.waitForFunction(() => {
    const iframe = document.getElementById('lbPreviewFrame');
    return iframe && iframe.contentDocument && iframe.contentDocument.querySelector('#root, main');
  }, { timeout: 20000 });
  check('preview iframe mounts the storefront', true);

  // ---- edit hero headline ------------------------------------------------
  await page.click('.lb-rail-item[data-section-id="hero"]');
  const stamp = Date.now().toString().slice(-5);
  const newHeadline = `تیتر آزمایشی ${stamp}`;
  const headlineInput = page.locator('[data-field-path="hero.headlineMain"] input');
  await headlineInput.fill(newHeadline);
  await headlineInput.dispatchEvent('input');

  await page.waitForTimeout(400);
  const statusTone = await page.locator('#lbStatusPill').getAttribute('data-tone');
  check('status switches to unsaved-changes', ['dirty', 'neutral'].includes(statusTone), statusTone);

  const inPreview = await page
    .frames()
    .find((f) => f !== page.mainFrame() && (f.name() === '' || true));
  void inPreview;
  const previewText = await page.waitForFunction(
    (needle) => {
      const iframe = document.getElementById('lbPreviewFrame');
      const doc = iframe && iframe.contentDocument;
      return Boolean(doc && doc.body && doc.body.innerText.includes(needle));
    },
    newHeadline,
    { timeout: 15000 }
  ).then(() => true).catch(() => false);
  check('preview reflects the edit live (postMessage bridge)', previewText);

  // ---- autosave ----------------------------------------------------------
  await page.waitForTimeout(1800);
  const savedTone = await page.locator('#lbStatusPill').getAttribute('data-tone');
  check('autosave clears the dirty state', savedTone === 'saved' || savedTone === 'neutral', savedTone);

  // ---- panel head tooling ------------------------------------------------
  const fieldsBefore = await page.locator('.lb-panel-body .lb-field:visible').count();
  await page.locator('.lb-panel-chips .lb-chip-btn[aria-pressed]').click();
  await page.waitForTimeout(250);
  const fieldsFiltered = await page.locator('.lb-panel-body .lb-field:visible').count();
  check(
    'changed-only filter narrows the field list',
    fieldsFiltered > 0 && fieldsFiltered < fieldsBefore,
    `${fieldsFiltered}/${fieldsBefore} fields`
  );
  await page.locator('.lb-panel-chips .lb-chip-btn[aria-pressed]').click();
  await page.waitForTimeout(200);

  await page.click('#lbCollapseAllChip');
  await page.waitForTimeout(250);
  const openGroups = await page.locator('.lb-group[data-open="true"]').count();
  check('collapse-all closes every group', openGroups === 0, `${openGroups} open`);
  await page.click('#lbCollapseAllChip');
  await page.waitForTimeout(200);

  // ---- search ------------------------------------------------------------
  await page.fill('#lbSearchInput', 'زیرتیتر');
  await page.waitForTimeout(300);
  const searchHits = await page.locator('.lb-result-item').count();
  check('field search returns hits', searchHits > 0, `${searchHits} hits`);
  await page.fill('#lbSearchInput', '');

  // ---- devices + section order ------------------------------------------
  await page.click('.lb-device-switch [data-device="mobile"]');
  const deviceAttr = await page.locator('#lbPreviewStage').getAttribute('data-device');
  check('device switcher applies mobile stage', deviceAttr === 'mobile', deviceAttr);
  await page.click('.lb-device-switch [data-device="desktop"]');

  await page.click('.lb-rail-item[data-section-id="pageOrder"]');
  const orderRows = await page.locator('.lb-order-row').count();
  check('section order editor lists sections', orderRows >= 11, `${orderRows} rows`);

  await page.screenshot({ path: path.join(SHOTS, 'studio-desktop.png'), fullPage: false });

  // ---- compact toolbar ---------------------------------------------------
  const toolbarBox = await page.locator('.lb-toolbar').boundingBox();
  check('toolbar stays a slim control strip', toolbarBox.height <= 56, `${toolbarBox.height.toFixed(1)}px`);

  // ---- resizable panes ---------------------------------------------------
  const beforeSplit = await page.evaluate(() => ({
    panel: document.querySelector('.lb-panel').getBoundingClientRect().width,
    preview: document.querySelector('.lb-preview').getBoundingClientRect().width,
  }));
  const splitBox = await page.locator('#lbSplitPanel').boundingBox();
  await page.mouse.move(splitBox.x + splitBox.width / 2, splitBox.y + splitBox.height / 2);
  await page.mouse.down();
  await page.mouse.move(splitBox.x + splitBox.width / 2 - 110, splitBox.y + splitBox.height / 2, { steps: 6 });
  await page.mouse.up();
  await page.waitForTimeout(350);
  const afterSplit = await page.evaluate(() => ({
    panel: document.querySelector('.lb-panel').getBoundingClientRect().width,
    preview: document.querySelector('.lb-preview').getBoundingClientRect().width,
  }));
  check(
    'splitters resize the editor and the preview live',
    afterSplit.panel > beforeSplit.panel + 60 && afterSplit.preview < beforeSplit.preview - 60,
    `panel ${beforeSplit.panel.toFixed(0)}→${afterSplit.panel.toFixed(0)}, preview ${beforeSplit.preview.toFixed(0)}→${afterSplit.preview.toFixed(0)}`
  );
  await page.dblclick('#lbSplitPanel');
  await page.waitForTimeout(300);

  // ---- focus mode hides the admin chrome ---------------------------------
  await page.click('#lbFocusBtn');
  await page.waitForTimeout(600);
  const focusState = await page.evaluate(() => ({
    topbar: getComputedStyle(document.querySelector('.dash-topbar')).display,
    subnav: getComputedStyle(document.querySelector('.admin-subnav-container')).display,
    sidebar: getComputedStyle(document.querySelector('.admin-sidebar')).display,
    foldGap: document.documentElement.scrollHeight - window.innerHeight,
  }));
  check(
    'focus mode hides topbar, topic tabs and sidebar',
    focusState.topbar === 'none' && focusState.subnav === 'none' && focusState.sidebar === 'none',
    JSON.stringify(focusState)
  );
  check('focus mode still fills the fold', focusState.foldGap <= 2, `${focusState.foldGap}px`);
  await page.screenshot({ path: path.join(SHOTS, 'studio-focus.png'), fullPage: false });
  await page.click('#lbFocusBtn');
  await page.waitForTimeout(400);

  // ---- global-search popover stacking ------------------------------------
  await page.fill('#adminGlobalSearch', 'محصول');
  await page.waitForTimeout(1400);
  const popover = await page.evaluate(() => {
    const results = document.querySelector('.admin-search-results');
    const topbar = document.querySelector('.dash-topbar');
    const rect = results.getBoundingClientRect();
    const topEl = document.elementFromPoint(rect.left + rect.width / 2, rect.top + 24);
    return {
      open: getComputedStyle(results).display !== 'none',
      lifted: getComputedStyle(topbar).zIndex,
      onTop: !!(topEl && topEl.closest('.admin-search-results')),
    };
  });
  check(
    'search popover paints above sidebar and studio',
    popover.open && popover.onTop && Number(popover.lifted) > 40,
    JSON.stringify(popover)
  );
  await page.fill('#adminGlobalSearch', '');
  await page.click('.lb-toolbar-id');

  // ---- zen preview -------------------------------------------------------
  await page.click('#lbZenBtn');
  await page.waitForTimeout(600);
  const zenState = await page.locator('#lbStudio').getAttribute('data-zen');
  check('zen mode maximises the preview', zenState === 'true', String(zenState));
  await page.screenshot({ path: path.join(SHOTS, 'studio-zen.png'), fullPage: false });
  await page.click('#lbZenBtn');
  await page.waitForTimeout(400);

  // ---- publish -----------------------------------------------------------
  await page.click('#lbPublishBtn');
  await page.waitForSelector('#lbModalHost:not([hidden]) .lb-modal');
  await page.fill('#lbModalBody input.lb-input', 'انتشار خودکار تست');
  // Assert on the endpoint contract (not toast copy): the autosave toast shares
  // the same words, so text matching used to pass on the wrong event.
  const publishCall = page.waitForResponse(
    (res) => res.url().includes('/ajax/landing_studio.php')
      && /name="action"\r\n\r\npublish\b/.test((res.request().postDataBuffer() || Buffer.alloc(0)).toString('utf8')),
    { timeout: 20000 }
  );
  await page.click('#lbModalFoot .lb-btn--primary');
  const publishJson = await (await publishCall).json();
  check('publish endpoint confirms', publishJson.ok === true, publishJson.message);

  await page.waitForFunction(
    () => (document.getElementById('lbStatusMeta').textContent || '').includes('انتشار'),
    { timeout: 15000 }
  ).then(() => true).catch(() => false);
  const publishedMeta = await page.locator('#lbStatusMeta').innerText();
  check('status bar shows the publish timestamp', /انتشار/.test(publishedMeta), publishedMeta.trim());
  await page.waitForTimeout(500);

  // ---- history -----------------------------------------------------------
  await page.click('#lbHistoryBtn');
  await page.waitForSelector('#lbModalHost:not([hidden]) .lb-history-row');
  const historyRows = await page.locator('.lb-history-row').count();
  check('publish archive lists revisions', historyRows > 0, `${historyRows} revisions`);
  await page.click('#lbModalFoot .lb-btn');

  // ---- storefront got the snapshot --------------------------------------
  const guest = await context.newPage();
  await guest.goto(BASE_URL + '/', { waitUntil: 'domcontentloaded' });
  const guestHtml = await guest.content();
  check('published copy is served to visitors', guestHtml.includes(newHeadline.slice(0, 12)));

  // ---- laptop layout (preview must survive) ------------------------------
  const laptopContext = await browser.newContext({ viewport: { width: 1366, height: 768 } });
  const laptop = await laptopContext.newPage();
  await laptop.goto(`${BASE_URL}/admin/login.php`, { waitUntil: 'domcontentloaded' });
  await laptop.fill('input[name="username"]', USER);
  await laptop.fill('input[name="password"]', PASS);
  await Promise.all([laptop.waitForLoadState('networkidle'), laptop.click('button[type="submit"], input[type="submit"]')]);
  await laptop.goto(`${BASE_URL}/admin/landing.php`, { waitUntil: 'domcontentloaded' });
  await laptop.waitForSelector('.lb-rail-item');
  await laptop.waitForTimeout(700);
  const laptopMetrics = await laptop.evaluate(() => ({
    preview: getComputedStyle(document.querySelector('.lb-preview')).display,
    switchShown: getComputedStyle(document.querySelector('.lb-mobile-switch')).display !== 'none',
    pageOverflowX: document.documentElement.scrollWidth - window.innerWidth,
    foldGap: document.documentElement.scrollHeight - window.innerHeight
  }));
  check('laptop keeps the live preview visible', laptopMetrics.preview !== 'none' && !laptopMetrics.switchShown, JSON.stringify(laptopMetrics));
  check('laptop has no sideways or fold overflow', laptopMetrics.pageOverflowX <= 1 && laptopMetrics.foldGap <= 2, JSON.stringify(laptopMetrics));
  await laptop.screenshot({ path: path.join(SHOTS, 'studio-laptop.png'), fullPage: false });
  await laptopContext.close();

  // ---- mobile layout -----------------------------------------------------
  const mobile = await context.newPage();
  await mobile.setViewportSize({ width: 420, height: 900 });
  await mobile.goto(`${BASE_URL}/admin/landing.php`, { waitUntil: 'domcontentloaded' });
  await mobile.waitForSelector('.lb-rail-item');
  const mobileSwitchVisible = await mobile.locator('.lb-mobile-switch').isVisible();
  check('mobile editor/preview switcher is available', mobileSwitchVisible);
  const overflow = await mobile.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
  check('no horizontal overflow on mobile', overflow <= 1, `${overflow}px`);
  await mobile.click('.lb-mobile-switch [data-mobile-view="preview"]');
  await mobile.waitForTimeout(600);
  await mobile.screenshot({ path: path.join(SHOTS, 'studio-mobile.png'), fullPage: false });

  // ---- console health ----------------------------------------------------
  const realErrors = consoleErrors.filter((text) => !/favicon|404 \(Not Found\).*fonts|Failed to load resource: the server responded with a status of 404 \(Not Found\)/i.test(text));
  check('no JavaScript console errors', realErrors.length === 0, realErrors.slice(0, 3).join(' | '));

  await browser.close();

  const failed = results.filter((r) => !r.ok);
  console.log(`\n${results.length - failed.length}/${results.length} checks passed`);
  console.log(`screenshots: ${SHOTS}`);
  if (failed.length) {
    console.log('\nFAILED:');
    failed.forEach((f) => console.log(`  - ${f.label} ${f.detail}`));
    process.exit(1);
  }
}

main().catch((error) => {
  console.error('FATAL', error);
  process.exit(1);
});
