import { chromium } from 'playwright';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const outputDir = path.resolve(__dirname, 'output/landing');
fs.mkdirSync(outputDir, { recursive: true });

async function main() {
  console.log('[TEST] Starting landing page audit against http://127.0.0.1:8085 ...');
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    locale: 'fa-IR',
  });
  const page = await context.newPage();

  const consoleLogs = [];
  const errors = [];
  page.on('console', msg => {
    if (msg.type() === 'error') {
      errors.push(msg.text());
    } else {
      consoleLogs.push(msg.text());
    }
  });
  page.on('pageerror', err => {
    errors.push(err.message);
  });

  try {
    const res = await page.goto('http://127.0.0.1:8085/', { waitUntil: 'networkidle', timeout: 30000 });
    console.log(`HTTP Status: ${res.status()}`);

    // Wait 3s for the initial loader animation to finish
    await page.waitForTimeout(3000);

    // Verify Title and Headline
    const title = await page.title();
    console.log(`Page Title: ${title}`);

    // Take Desktop Full Page Screenshot
    await page.screenshot({ path: path.join(outputDir, 'desktop_hero.png'), fullPage: false });
    console.log('Saved desktop_hero.png');

    // Test Catalog: find a product card and swatches
    const swatches = page.locator('article button[title]');
    const swatchCount = await swatches.count();
    console.log(`Found ${swatchCount} color swatches on catalog cards.`);
    if (swatchCount > 0) {
      await swatches.first().click();
      console.log('Clicked first color swatch successfully.');
    }

    // Test Pack Builder
    const packButtons = page.locator('#pack button');
    console.log(`Found ${await packButtons.count()} buttons in PackBuilder section.`);

    // Test Lucky Wheel button
    const wheelFab = page.locator('button[aria-label="گردونه شانس"]').last();
    if (await wheelFab.count() > 0 && await wheelFab.isVisible()) {
      await wheelFab.click();
      await page.waitForTimeout(800);
      await page.screenshot({ path: path.join(outputDir, 'lucky_wheel_modal.png') });
      console.log('Saved lucky_wheel_modal.png');
      // close modal
      await page.keyboard.press('Escape');
      await page.waitForTimeout(400);
    }

    // Scroll down to take full page screenshot
    await page.screenshot({ path: path.join(outputDir, 'landing_fullpage.png'), fullPage: true });
    console.log('Saved landing_fullpage.png');

    // Test Mobile Viewport
    console.log('\n[TEST] Running Mobile 390x844 audit...');
    const mobileContext = await browser.newContext({
      viewport: { width: 390, height: 844 },
      isMobile: true,
      hasTouch: true,
      locale: 'fa-IR',
    });
    const mobilePage = await mobileContext.newPage();
    await mobilePage.goto('http://127.0.0.1:8085/', { waitUntil: 'networkidle', timeout: 30000 });
    await mobilePage.waitForTimeout(3000);
    await mobilePage.screenshot({ path: path.join(outputDir, 'mobile_hero.png'), fullPage: false });
    console.log('Saved mobile_hero.png');

    // Click cart icon on mobile to verify CartDrawer
    const cartBtn = mobilePage.locator('button[aria-label="سبد خرید"], button:has(svg.lucide-shopping-bag)').first();
    if (await cartBtn.count() > 0) {
      await cartBtn.click();
      await mobilePage.waitForTimeout(600);
      await mobilePage.screenshot({ path: path.join(outputDir, 'mobile_cart_drawer.png') });
      console.log('Saved mobile_cart_drawer.png');
    }

    console.log('\n=== Summary ===');
    console.log(`Console Errors: ${errors.length}`);
    if (errors.length > 0) {
      console.log('Errors:', errors);
    } else {
      console.log('Zero console errors detected!');
    }
  } catch (err) {
    console.error('Test execution error:', err);
  } finally {
    await browser.close();
  }
}

main();
