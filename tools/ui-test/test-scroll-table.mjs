/**
 * Standalone Test Harness for data_table Scroll Mode & Priority Suppression
 * Tests that data-mode="scroll" tables preserve all columns at narrow container widths
 * and high zoom levels while data-mode="stack" respects column prioritization.
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

function generateTestHtml() {
  // Columns 1-2: prio 1, Cols 3-5: prio 2, Cols 6-12: prio 3
  const thHeaders = Array.from({ length: 12 }, (_, i) => {
    const c = i + 1;
    const prio = c <= 2 ? 1 : c <= 5 ? 2 : 3;
    return `<th data-priority="${prio}" data-col="col_${c}">ستون ${c}</th>`;
  }).join('');

  const tdCells = Array.from({ length: 12 }, (_, i) => {
    const c = i + 1;
    const prio = c <= 2 ? 1 : c <= 5 ? 2 : 3;
    return `<td data-priority="${prio}" data-label="ستون ${c}">داده ستون ${c}</td>`;
  }).join('');

  return `<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Table Scroll Mode Test</title>
<style>
${tokensCss}
${baseCss}
${shellCss}
${compCss}
${utilCss}

.test-container {
  padding: 1.5rem;
  max-width: 100%;
}
</style>
</head>
<body class="admin-body">
  <div class="test-container" id="testContainer">
    <h2>۱. جدول با حالت اسکرول افقی (Scroll Mode)</h2>
    <div class="ab-table-wrap" data-mode="scroll" id="scrollTableWrap">
      <table class="ab-table" id="stress12Table">
        <thead>
          <tr>${thHeaders}</tr>
        </thead>
        <tbody>
          <tr>${tdCells}</tr>
        </tbody>
      </table>
    </div>

    <h2 style="margin-top: 2rem;">۲. جدول با حالت عادی پشته‌ای (Stack Mode)</h2>
    <div class="ab-table-wrap" data-mode="stack" id="stackTableWrap">
      <table class="ab-table" id="stackTable">
        <thead>
          <tr>${thHeaders}</tr>
        </thead>
        <tbody>
          <tr>${tdCells}</tr>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>`;
}

async function run() {
  console.log('🚀 Starting Data Table Scroll & Priority Playwright Suite...\n');
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

  // --- Suite 1: Desktop Viewport (1200px) ---
  console.log('--- Test Suite 1: Wide Desktop (1200px, width > 56rem) ---');
  await page.setViewportSize({ width: 1200, height: 800 });
  await page.waitForTimeout(50);

  const desktopScrollVisibleTh = await page.$$eval('#stress12Table th', ths => 
    ths.filter(th => window.getComputedStyle(th).display !== 'none').length
  );
  assert(desktopScrollVisibleTh === 12, `Scroll table shows all 12 TH columns on wide screen (got ${desktopScrollVisibleTh})`);

  const desktopStackVisibleTh = await page.$$eval('#stackTable th', ths => 
    ths.filter(th => window.getComputedStyle(th).display !== 'none').length
  );
  assert(desktopStackVisibleTh === 12, `Stack table shows all 12 TH columns on wide screen (got ${desktopStackVisibleTh})`);

  // --- Suite 2: Medium Container (700px, width < 56rem, width > 40rem) ---
  console.log('\n--- Test Suite 2: Medium Container (700px / ~43.75rem: < 56rem, > 40rem) ---');
  await page.setViewportSize({ width: 700, height: 800 });
  await page.waitForTimeout(50);

  // Stack table should hide priority 3 (columns 6-12), leaving 5 visible columns (1-5)
  const medStackVisibleTh = await page.$$eval('#stackTable th', ths => 
    ths.filter(th => window.getComputedStyle(th).display !== 'none').length
  );
  assert(medStackVisibleTh === 5, `Stack table suppresses priority 3 columns, showing 5 columns (got ${medStackVisibleTh})`);

  // Scroll table MUST PRESERVE all 12 columns!
  const medScrollVisibleTh = await page.$$eval('#stress12Table th', ths => 
    ths.filter(th => window.getComputedStyle(th).display !== 'none').length
  );
  assert(medScrollVisibleTh === 12, `Scroll table PRESERVES all 12 TH columns under 56rem (got ${medScrollVisibleTh})`);

  const medScrollVisibleTd = await page.$$eval('#stress12Table td', tds => 
    tds.filter(td => window.getComputedStyle(td).display !== 'none').length
  );
  assert(medScrollVisibleTd === 12, `Scroll table PRESERVES all 12 TD cells under 56rem (got ${medScrollVisibleTd})`);

  // Verify horizontal scrollbar is active on scroll table
  const scrollWrapMetrics = await page.$eval('#scrollTableWrap', el => ({
    scrollWidth: el.scrollWidth,
    clientWidth: el.clientWidth,
    isScrollable: el.scrollWidth > el.clientWidth,
  }));
  assert(scrollWrapMetrics.isScrollable, `Scroll table wrap has scrollWidth (${scrollWrapMetrics.scrollWidth}px) > clientWidth (${scrollWrapMetrics.clientWidth}px)`);

  // --- Suite 3: Mobile Viewport (390px, width < 40rem) ---
  console.log('\n--- Test Suite 3: Mobile Viewport (390px, width < 40rem) ---');
  await page.setViewportSize({ width: 390, height: 844 });
  await page.waitForTimeout(50);

  // Scroll table MUST STILL PRESERVE all 12 columns on mobile!
  const mobScrollVisibleTh = await page.$$eval('#stress12Table th', ths => 
    ths.filter(th => window.getComputedStyle(th).display !== 'none').length
  );
  assert(mobScrollVisibleTh === 12, `Scroll table PRESERVES all 12 TH columns on mobile (got ${mobScrollVisibleTh})`);

  const mobScrollVisibleTd = await page.$$eval('#stress12Table td', tds => 
    tds.filter(td => window.getComputedStyle(td).display !== 'none').length
  );
  assert(mobScrollVisibleTd === 12, `Scroll table PRESERVES all 12 TD cells on mobile (got ${mobScrollVisibleTd})`);

  // Verify sticky first column styling
  const stickyThStyles = await page.$eval('#stress12Table th:first-child', el => {
    const s = window.getComputedStyle(el);
    return {
      position: s.position,
      insetInlineStart: s.insetInlineStart,
      zIndex: s.zIndex,
    };
  });
  assert(stickyThStyles.position === 'sticky', `First TH has position: sticky (got ${stickyThStyles.position})`);
  assert(stickyThStyles.insetInlineStart === '0px', `First TH has inset-inline-start: 0 (got ${stickyThStyles.insetInlineStart})`);
  assert(parseInt(stickyThStyles.zIndex) >= 3, `First TH has high z-index (got ${stickyThStyles.zIndex})`);

  const stickyTdStyles = await page.$eval('#stress12Table td:first-child', el => {
    const s = window.getComputedStyle(el);
    return {
      position: s.position,
      insetInlineStart: s.insetInlineStart,
      zIndex: s.zIndex,
    };
  });
  assert(stickyTdStyles.position === 'sticky', `First TD has position: sticky (got ${stickyTdStyles.position})`);
  assert(stickyTdStyles.insetInlineStart === '0px', `First TD has inset-inline-start: 0 (got ${stickyTdStyles.insetInlineStart})`);

  // --- Suite 4: Zoom Simulation Tests (110%, 125%, 200%) ---
  console.log('\n--- Test Suite 4: Zoom Simulation (110%, 125%, 200%) ---');
  for (const zoomPct of [110, 125, 200]) {
    // Zoom in browser reduces effective CSS pixels: e.g. 1024px @ 125% zoom has effective viewport 819px
    const zoomScale = zoomPct / 100;
    const effectiveWidth = Math.round(1024 / zoomScale);
    await page.setViewportSize({ width: effectiveWidth, height: 768 });
    await page.waitForTimeout(50);

    const zoomScrollVisibleTh = await page.$$eval('#stress12Table th', ths => 
      ths.filter(th => window.getComputedStyle(th).display !== 'none').length
    );
    assert(zoomScrollVisibleTh === 12, `Scroll table has all 12 columns visible at ${zoomPct}% zoom (effective width: ${effectiveWidth}px)`);
  }

  // --- Suite 5: Horizontal Scroll Action & Sticky Pinning ---
  console.log('\n--- Test Suite 5: Horizontal Scrolling & Sticky Pinning in RTL ---');
  await page.setViewportSize({ width: 600, height: 600 });
  await page.waitForTimeout(50);

  // Initial scroll position
  const initialScroll = await page.$eval('#scrollTableWrap', el => el.scrollLeft);
  
  // In RTL, scrollLeft can be 0 or negative depending on browser standard. Scroll leftwards:
  await page.$eval('#scrollTableWrap', el => {
    el.scrollBy({ left: -250, behavior: 'instant' });
  });
  await page.waitForTimeout(50);

  const firstThBbox = await page.$eval('#stress12Table th:first-child', el => {
    const r = el.getBoundingClientRect();
    return { left: r.left, right: r.right, width: r.width };
  });

  const wrapBbox = await page.$eval('#scrollTableWrap', el => {
    const r = el.getBoundingClientRect();
    return { left: r.left, right: r.right, width: r.width };
  });

  // In RTL, first column is anchored to the right edge of wrap
  const isPinnedToRight = Math.abs(firstThBbox.right - wrapBbox.right) <= 2;
  assert(isPinnedToRight, `In RTL, sticky first column stays pinned to right edge when scrolled (col right: ${firstThBbox.right}, wrap right: ${wrapBbox.right})`);

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
