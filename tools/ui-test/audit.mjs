#!/usr/bin/env node
/**
 * AB-Socks Admin Panel Playwright UI & Responsive Audit Harness
 * Dev-only tool. Never deployed to production.
 *
 * Runs responsive layout, RTL overflow, tap target, and console error checks
 * across 10 viewports + 4 font scales.
 */

import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// ---------------------------------------------------------------------------
// 1. Configuration & CLI Argument Parsing
// ---------------------------------------------------------------------------

const args = process.argv.slice(2);
function getArgValue(flag) {
  const idx = args.indexOf(flag);
  if (idx !== -1 && args[idx + 1] && !args[idx + 1].startsWith('--')) {
    return args[idx + 1];
  }
  return null;
}
const hasFlag = (flag) => args.includes(flag);

const BASE_URL = (process.env.AB_BASE_URL || getArgValue('--base-url') || 'http://localhost:8080').replace(/\/+$/, '');
const ADMIN_USER = process.env.AB_ADMIN_USER || getArgValue('--user') || 'admin';
const ADMIN_PASS = process.env.AB_ADMIN_PASS || getArgValue('--pass') || 'admin';

const filterPagesArg = getArgValue('--pages') || process.env.AB_PAGES;
const filterPages = filterPagesArg ? filterPagesArg.split(',').map((s) => s.trim().toLowerCase()) : null;

const filterVpArg = getArgValue('--viewports') || process.env.AB_VIEWPORTS;
const filterViewports = filterVpArg ? filterVpArg.split(',').map((s) => s.trim().toLowerCase()) : null;

const HEADED = hasFlag('--headed') || hasFlag('--no-headless');
const UPDATE_SNAPSHOTS = hasFlag('--update-snapshots');

// ---------------------------------------------------------------------------
// 2. Load Page Inventory & Parameters
// ---------------------------------------------------------------------------

const pagesConfigPath = path.join(__dirname, 'pages.json');
const localConfigPath = path.join(__dirname, 'pages.local.json');

if (!fs.existsSync(pagesConfigPath)) {
  console.error(`[ERROR] pages.json missing at ${pagesConfigPath}`);
  process.exit(1);
}

const pagesConfig = JSON.parse(fs.readFileSync(pagesConfigPath, 'utf-8'));
let localConfig = {};
if (fs.existsSync(localConfigPath)) {
  try {
    localConfig = JSON.parse(fs.readFileSync(localConfigPath, 'utf-8'));
  } catch (err) {
    console.warn(`[WARN] Failed to parse pages.local.json: ${err.message}`);
  }
}

// Param defaults merged: pages.json defaults -> pages.local.json -> env vars (AB_PRODUCT_ID, etc.)
const paramDefaults = { ...(pagesConfig.defaults || {}), ...(localConfig.defaults || {}) };
for (const [key, val] of Object.entries(paramDefaults)) {
  const envKey = `AB_${key.toUpperCase()}`;
  if (process.env[envKey]) {
    paramDefaults[key] = process.env[envKey];
  }
}

function resolvePageUrl(pageDef) {
  let url = `${BASE_URL}${pageDef.path}`;
  if (pageDef.params) {
    const searchParams = new URLSearchParams();
    for (const [pKey, pVal] of Object.entries(pageDef.params)) {
      let resolvedVal = pVal;
      const match = typeof pVal === 'string' && pVal.match(/^\{([a-zA-Z0-9_]+)\}$/);
      if (match) {
        const paramKey = match[1];
        resolvedVal = paramDefaults[paramKey] ?? '';
      }
      if (resolvedVal !== '' && resolvedVal !== null && resolvedVal !== undefined) {
        searchParams.set(pKey, resolvedVal);
      }
    }
    const qs = searchParams.toString();
    if (qs) {
      url += (url.includes('?') ? '&' : '?') + qs;
    }
  }
  return url;
}

// ---------------------------------------------------------------------------
// 3. Viewport Specifications
// ---------------------------------------------------------------------------

const ALL_VIEWPORTS = [
  { id: 'android-s', width: 360, height: 740, isMobile: true, hasTouch: true, label: '360x740 (Android Small)' },
  { id: 'iphone', width: 390, height: 844, isMobile: true, hasTouch: true, label: '390x844 (iPhone 12/13/14)' },
  { id: 'tablet', width: 768, height: 1024, isMobile: false, hasTouch: true, label: '768x1024 (Tablet Portrait)' },
  { id: 'md-edge', width: 1024, height: 768, isMobile: false, hasTouch: false, label: '1024x768 (md Breakpoint Edge)' },
  { id: 'laptop', width: 1280, height: 800, isMobile: false, hasTouch: false, label: '1280x800 (Laptop / 150% Zoom)' },
  { id: 'desktop', width: 1440, height: 900, isMobile: false, hasTouch: false, label: '1440x900 (Desktop Standard)' },
  { id: 'fhd', width: 1920, height: 1080, isMobile: false, hasTouch: false, label: '1920x1080 (Full HD)' },
  { id: 'zoom200', width: 960, height: 540, isMobile: false, hasTouch: false, label: '960x540 (FHD at 200% Zoom)' },
  { id: 'zoom300', width: 640, height: 360, isMobile: false, hasTouch: false, label: '640x360 (FHD at 300% Zoom)' },
  { id: 'zoom50', width: 3840, height: 2160, isMobile: false, hasTouch: false, label: '3840x2160 (FHD at 50% Zoom / 4K)' },
  // Font scale scaling variations
  { id: 'laptop_font125', width: 1280, height: 800, isMobile: false, hasTouch: false, fontScale: '125%', label: '1280x800 + Font Scale 125%' },
  { id: 'laptop_font200', width: 1280, height: 800, isMobile: false, hasTouch: false, fontScale: '200%', label: '1280x800 + Font Scale 200%' },
  { id: 'iphone_font125', width: 390, height: 844, isMobile: true, hasTouch: true, fontScale: '125%', label: '390x844 + Font Scale 125%' },
  { id: 'iphone_font200', width: 390, height: 844, isMobile: true, hasTouch: true, fontScale: '200%', label: '390x844 + Font Scale 200%' },
];

const selectedViewports = ALL_VIEWPORTS.filter((vp) => {
  if (!filterViewports) return true;
  return filterViewports.includes(vp.id.toLowerCase()) || filterViewports.includes(vp.id.split('_')[0].toLowerCase());
});

const selectedPages = (pagesConfig.pages || []).filter((p) => {
  if (!filterPages) return true;
  return filterPages.includes(p.id.toLowerCase());
});

// ---------------------------------------------------------------------------
// 4. Output Directories
// ---------------------------------------------------------------------------

const timestamp = new Date().toISOString().replace(/[:.]/g, '-');
const outputDir = path.join(__dirname, 'output', timestamp);
const authDir = path.join(__dirname, '.auth');
const authStateFile = path.join(authDir, 'state.json');

fs.mkdirSync(outputDir, { recursive: true });
fs.mkdirSync(authDir, { recursive: true });

// ---------------------------------------------------------------------------
// 5. Authentication Helper
// ---------------------------------------------------------------------------

async function ensureAuthenticated(browser) {
  if (fs.existsSync(authStateFile)) {
    try {
      const stats = fs.statSync(authStateFile);
      // Valid if created within the last 6 hours
      if (Date.now() - stats.mtimeMs < 6 * 3600 * 1000) {
        return authStateFile;
      }
    } catch (_) {}
  }

  console.log(`[AUTH] Authenticating admin user (${ADMIN_USER}) at ${BASE_URL}/admin/login.php...`);
  const context = await browser.newContext();
  const page = await context.newPage();

  try {
    await page.goto(`${BASE_URL}/admin/login.php`, { waitUntil: 'domcontentloaded', timeout: 15000 });

    const userInput = page.locator('input[name="username"], input[name="user"]');
    const passInput = page.locator('input[name="password"], input[name="pass"]');

    if ((await userInput.count()) > 0) {
      await userInput.first().fill(ADMIN_USER);
      await passInput.first().fill(ADMIN_PASS);

      await Promise.all([
        page.waitForNavigation({ timeout: 10000 }).catch(() => null),
        page.locator('button[type="submit"], input[type="submit"]').first().click(),
      ]);
    }

    // Verify authentication by checking redirect away from login.php
    if (page.url().includes('login.php')) {
      console.warn(`[WARN] Login page did not redirect. Credentials may be invalid or session established.`);
    }

    await context.storageState({ path: authStateFile });
    console.log(`[AUTH] Authentication saved to ${authStateFile}`);
  } catch (err) {
    console.warn(`[WARN] Automated login encountered an issue: ${err.message}. Proceeding with unauthenticated storage.`);
  } finally {
    await context.close();
  }

  return authStateFile;
}

// ---------------------------------------------------------------------------
// 6. In-Page Audit Checks (Evaluated inside browser DOM)
// ---------------------------------------------------------------------------

async function evaluatePageHealth(page, vp) {
  return await page.evaluate((isMobileOrTouch) => {
    const docEl = document.documentElement;
    const body = document.body;
    const innerWidth = window.innerWidth;

    // Check 1: Horizontal scroll overflow
    const docScrollWidth = docEl.scrollWidth;
    const docClientWidth = docEl.clientWidth;
    const hasHorizontalOverflow = docScrollWidth > docClientWidth + 1;

    const overflowElements = [];
    if (hasHorizontalOverflow) {
      const allElements = document.querySelectorAll('body *');
      for (const el of allElements) {
        if (el.offsetWidth === 0 && el.offsetHeight === 0) continue;
        const style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') continue;

        // Skip if inside an ancestor with intentional horizontal scroll
        let parent = el.parentElement;
        let isInsideScrollContainer = false;
        while (parent && parent !== body) {
          const pStyle = window.getComputedStyle(parent);
          if (pStyle.overflowX === 'auto' || pStyle.overflowX === 'scroll' || pStyle.overflowX === 'hidden') {
            isInsideScrollContainer = true;
            break;
          }
          parent = parent.parentElement;
        }
        if (isInsideScrollContainer) continue;

        const rect = el.getBoundingClientRect();
        if (rect.left < -1 || rect.right > innerWidth + 1) {
          let selector = el.tagName.toLowerCase();
          if (el.id) {
            selector += `#${el.id}`;
          } else if (el.className && typeof el.className === 'string') {
            const classes = el.className.trim().split(/\s+/).slice(0, 3).join('.');
            if (classes) selector += `.${classes}`;
          }
          overflowElements.push({
            selector,
            left: Math.round(rect.left),
            right: Math.round(rect.right),
            width: Math.round(rect.width),
          });
          if (overflowElements.length >= 8) break;
        }
      }
    }

    // Check 3: Tap target sizes on mobile/touch viewports (minimum 44x44px)
    const tapViolations = [];
    if (isMobileOrTouch) {
      const targets = document.querySelectorAll('a, button, input:not([type="hidden"]), select, textarea, [role="button"]');
      for (const el of targets) {
        if (el.offsetWidth === 0 && el.offsetHeight === 0) continue;
        const style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden' || style.opacity === '0') continue;

        const rect = el.getBoundingClientRect();
        if (rect.width < 1 || rect.height < 1) continue;

        if (rect.width < 44 || rect.height < 44) {
          // If parent is clickable and sufficiently large, allow it
          const parent = el.parentElement;
          if (parent) {
            const pRect = parent.getBoundingClientRect();
            if (pRect.width >= 44 && pRect.height >= 44 && (parent.tagName === 'A' || parent.tagName === 'BUTTON' || parent.getAttribute('role') === 'button')) {
              continue;
            }
          }

          let sel = el.tagName.toLowerCase();
          if (el.id) sel += `#${el.id}`;
          else if (el.className && typeof el.className === 'string') {
            sel += `.${el.className.trim().split(/\s+/).slice(0, 2).join('.')}`;
          }
          const text = (el.textContent || el.value || el.getAttribute('aria-label') || '').trim().slice(0, 24);
          tapViolations.push({
            selector: sel,
            text,
            width: Math.round(rect.width),
            height: Math.round(rect.height),
          });
          if (tapViolations.length >= 10) break;
        }
      }
    }

    // Check 4: Text legibility (visible text font size < 12px)
    const illegibleNodes = [];
    const textWalker = document.createTreeWalker(body, NodeFilter.SHOW_TEXT);
    let currentNode;
    while ((currentNode = textWalker.nextNode())) {
      const text = currentNode.nodeValue.trim();
      if (!text || text.length < 2) continue;
      const parent = currentNode.parentElement;
      if (!parent) continue;

      const pStyle = window.getComputedStyle(parent);
      if (pStyle.display === 'none' || pStyle.visibility === 'hidden' || pStyle.opacity === '0') continue;
      if (parent.closest('.ab-sr-only, [aria-hidden="true"], svg, script, style')) continue;

      const fontSize = parseFloat(pStyle.fontSize);
      if (fontSize < 11.5) {
        illegibleNodes.push({
          tag: parent.tagName.toLowerCase(),
          size: `${fontSize}px`,
          snippet: text.slice(0, 30),
        });
        if (illegibleNodes.length >= 6) break;
      }
    }

    // Check 5: Sticky bars (bottom nav / savebar) overlap with bottom content
    let stickyBarOverlap = false;
    const bottomBars = document.querySelectorAll('.admin-bottom-nav, .ab-savebar, [data-sticky-bottom]');
    let barTop = Infinity;
    for (const b of bottomBars) {
      const bStyle = window.getComputedStyle(b);
      if (bStyle.display !== 'none' && (bStyle.position === 'fixed' || bStyle.position === 'sticky')) {
        const bRect = b.getBoundingClientRect();
        if (bRect.top < barTop && bRect.height > 10) {
          barTop = bRect.top;
        }
      }
    }
    if (barTop !== Infinity) {
      window.scrollTo(0, body.scrollHeight);
      const interactiveEls = document.querySelectorAll('button:not(.admin-bottom-nav *), a:not(.admin-bottom-nav *), input, select');
      if (interactiveEls.length > 0) {
        const lastEl = interactiveEls[interactiveEls.length - 1];
        const lastRect = lastEl.getBoundingClientRect();
        if (lastRect.bottom > barTop + 5) {
          stickyBarOverlap = true;
        }
      }
    }

    return {
      hasHorizontalOverflow,
      scrollWidth: docScrollWidth,
      clientWidth: docClientWidth,
      overflowElements,
      tapViolations,
      illegibleNodes,
      stickyBarOverlap,
    };
  }, vp.isMobile || vp.hasTouch);
}

// ---------------------------------------------------------------------------
// 7. Main Audit Runner
// ---------------------------------------------------------------------------

async function runAudit() {
  console.log(`========================================================================`);
  console.log(`AB-Socks Admin Panel Playwright UI & Responsiveness Audit`);
  console.log(`Target Base URL: ${BASE_URL}`);
  console.log(`Pages to audit: ${selectedPages.length} | Viewports: ${selectedViewports.length}`);
  console.log(`Artifacts destination: ${outputDir}`);
  console.log(`========================================================================\n`);

  const browser = await chromium.launch({ headless: !HEADED });
  const authState = await ensureAuthenticated(browser);

  const results = [];
  let totalCriticalFailures = 0;

  for (const pageDef of selectedPages) {
    const targetUrl = resolvePageUrl(pageDef);
    console.log(`\n---> Auditing Page: [${pageDef.id}] "${pageDef.name}" (${pageDef.path})`);

    for (const vp of selectedViewports) {
      process.stdout.write(`   * Viewport ${vp.id.padEnd(16)} (${vp.width}x${vp.height}${vp.fontScale ? ' ' + vp.fontScale : ''})... `);

      const contextOptions = {
        viewport: { width: vp.width, height: vp.height },
        isMobile: vp.isMobile ?? false,
        hasTouch: vp.hasTouch ?? false,
      };

      if (pageDef.auth !== false && fs.existsSync(authState)) {
        contextOptions.storageState = authState;
      }

      const context = await browser.newContext(contextOptions);
      const page = await context.newPage();

      const consoleErrors = [];
      const networkErrors = [];

      page.on('console', (msg) => {
        if (msg.type() === 'error') {
          consoleErrors.push(msg.text().slice(0, 120));
        }
      });
      page.on('pageerror', (err) => {
        consoleErrors.push(`[UNCAUGHT] ${err.message}`.slice(0, 120));
      });
      page.on('response', (res) => {
        if (res.status() >= 400 && res.url().startsWith(BASE_URL)) {
          networkErrors.push(`${res.status()} ${res.url().replace(BASE_URL, '')}`);
        }
      });

      let navigationError = null;
      let health = null;

      try {
        await page.goto(targetUrl, { waitUntil: 'domcontentloaded', timeout: 15000 });

        // Apply font scale if specified
        if (vp.fontScale) {
          await page.addStyleTag({ content: `html { font-size: ${vp.fontScale} !important; }` });
        }

        // Wait a brief tick for layout hydration / ab-kit initialization
        await page.waitForTimeout(300);

        health = await evaluatePageHealth(page, vp);

        // Take full-page screenshot
        const screenshotName = `${pageDef.id}__${vp.id}.png`;
        const screenshotPath = path.join(outputDir, screenshotName);
        await page.screenshot({ path: screenshotPath, fullPage: true });
      } catch (err) {
        navigationError = err.message;
      } finally {
        await context.close();
      }

      const hasFail = navigationError || health?.hasHorizontalOverflow || consoleErrors.length > 0 || networkErrors.length > 0;
      if (hasFail) {
        totalCriticalFailures++;
        console.log(`[FAIL]`);
        if (navigationError) console.log(`      Error: ${navigationError}`);
        if (health?.hasHorizontalOverflow) {
          console.log(`      Overflow: scrollWidth ${health.scrollWidth} > clientWidth ${health.clientWidth}`);
          if (health.overflowElements.length > 0) {
            console.log(`      Offenders: ${health.overflowElements.map((o) => `${o.selector} (${o.left}->${o.right})`).join(', ')}`);
          }
        }
        if (consoleErrors.length > 0) console.log(`      Console Errors (${consoleErrors.length}): ${consoleErrors.slice(0, 2).join(' | ')}`);
        if (networkErrors.length > 0) console.log(`      HTTP Failures: ${networkErrors.join(', ')}`);
      } else {
        const warnings = [];
        if (health?.tapViolations?.length > 0) warnings.push(`${health.tapViolations.length} tap targets < 44px`);
        if (health?.illegibleNodes?.length > 0) warnings.push(`${health.illegibleNodes.length} texts < 12px`);
        if (health?.stickyBarOverlap) warnings.push(`Sticky bar overlaps bottom elements`);

        if (warnings.length > 0) {
          console.log(`[PASS with warnings: ${warnings.join('; ')}]`);
        } else {
          console.log(`[OK]`);
        }
      }

      results.push({
        pageId: pageDef.id,
        pageName: pageDef.name,
        path: pageDef.path,
        url: targetUrl,
        archetype: pageDef.archetype || 'custom',
        mobileCritical: pageDef.mobileCritical || false,
        viewport: vp,
        navigationError,
        health,
        consoleErrors,
        networkErrors,
        passed: !hasFail,
      });
    }
  }

  await browser.close();

  // -------------------------------------------------------------------------
  // 8. Generate Summary Reports (Markdown & JSON)
  // -------------------------------------------------------------------------

  writeReports(results, totalCriticalFailures);

  console.log(`\n========================================================================`);
  console.log(`Audit Completed!`);
  console.log(`Total checks: ${results.length} | Critical Failures: ${totalCriticalFailures}`);
  console.log(`Report generated: ${path.join(outputDir, 'report.md')}`);
  console.log(`========================================================================`);

  if (totalCriticalFailures > 0) {
    process.exit(1);
  }
}

function writeReports(results, failureCount) {
  const jsonReportPath = path.join(outputDir, 'report.json');
  fs.writeFileSync(jsonReportPath, JSON.stringify({ timestamp, results, summary: { total: results.length, failures: failureCount } }, null, 2), 'utf-8');

  let md = `# AB-Socks Admin UI Playwright Audit Report\n\n`;
  md += `- **Date/Time:** ${new Date().toISOString()}\n`;
  md += `- **Base URL:** \`${BASE_URL}\`\n`;
  md += `- **Total Tests:** ${results.length}\n`;
  md += `- **Critical Failures:** ${failureCount}\n\n`;

  md += `## Matrix Summary\n\n`;
  md += `| Page | Archetype | Viewport | Status | Overflow | Console Errors | Tap Target Issues | Notes |\n`;
  md += `|---|---|---|---|---|---|---|---|\n`;

  for (const r of results) {
    const statusPill = r.passed ? '✅ PASS' : '❌ FAIL';
    const overflowStr = r.health?.hasHorizontalOverflow ? `⚠️ Yes (${r.health.scrollWidth}px)` : 'No';
    const errCount = (r.consoleErrors?.length || 0) + (r.networkErrors?.length || 0);
    const tapCount = r.health?.tapViolations?.length || 0;
    const notes = r.navigationError ? `Nav error: ${r.navigationError}` : r.health?.stickyBarOverlap ? 'Sticky bar overlap' : '-';

    md += `| **${r.pageName}** (\`${r.pageId}\`) | ${r.archetype} | \`${r.viewport.id}\` | ${statusPill} | ${overflowStr} | ${errCount} | ${tapCount} | ${notes} |\n`;
  }

  const failures = results.filter((r) => !r.passed);
  if (failures.length > 0) {
    md += `\n## Failure Details\n\n`;
    for (const f of failures) {
      md += `### ${f.pageName} (\`${f.pageId}\`) @ \`${f.viewport.id}\`\n\n`;
      if (f.navigationError) md += `- **Navigation Error:** ${f.navigationError}\n`;
      if (f.health?.hasHorizontalOverflow) {
        md += `- **Overflow:** Document width ${f.health.scrollWidth} exceeds viewport ${f.health.clientWidth}.\n`;
        if (f.health.overflowElements.length > 0) {
          md += `  - Offending elements:\n`;
          for (const o of f.health.overflowElements) {
            md += `    - \`${o.selector}\` (left: ${o.left}px, right: ${o.right}px, width: ${o.width}px)\n`;
          }
        }
      }
      if (f.consoleErrors.length > 0) {
        md += `- **Console Errors:**\n`;
        for (const ce of f.consoleErrors) md += `  - \`${ce}\`\n`;
      }
      if (f.networkErrors.length > 0) {
        md += `- **Network Errors:**\n`;
        for (const ne of f.networkErrors) md += `  - \`${ne}\`\n`;
      }
      md += `\n`;
    }
  }

  const mdReportPath = path.join(outputDir, 'report.md');
  fs.writeFileSync(mdReportPath, md, 'utf-8');
}

runAudit().catch((err) => {
  console.error('[FATAL]', err);
  process.exit(1);
});
