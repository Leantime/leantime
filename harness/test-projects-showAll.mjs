import { chromium } from "playwright";
import { resolve } from "path";
import { readFileSync } from "fs";

const harnessDir = "/Users/marcelfolaron/Source/Leantime/leantime-oss/harness";
const ssDir = resolve(harnessDir, "state/screenshots");

async function testSite(name, baseUrl, storageFile) {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    storageState: JSON.parse(readFileSync(storageFile, "utf-8")),
    ignoreHTTPSErrors: true,
    viewport: { width: 1440, height: 900 },
  });
  const page = await context.newPage();
  
  await page.goto(`${baseUrl}/projects/showAll`, { waitUntil: "networkidle", timeout: 30000 });
  await page.waitForTimeout(2000);
  await page.screenshot({ path: resolve(ssDir, `projects-showAll-${name}-fresh.png`) });
  console.log(`${name}: Main page screenshot taken`);

  // Test 1: Click the "+ New Project" button
  try {
    const newProjectBtn = page.locator('a:has-text("New Project"), button:has-text("New Project")').first();
    if (await newProjectBtn.isVisible({ timeout: 3000 })) {
      await newProjectBtn.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: resolve(ssDir, `projects-showAll-${name}-newproject.png`) });
      console.log(`${name}: New Project click screenshot taken`);
      await page.keyboard.press('Escape');
      await page.waitForTimeout(500);
    }
  } catch (e) {
    console.log(`${name}: New Project click failed: ${e.message}`);
  }

  // Navigate back
  await page.goto(`${baseUrl}/projects/showAll`, { waitUntil: "networkidle", timeout: 30000 });
  await page.waitForTimeout(2000);

  // Test 2: Check "Show Closed Projects" checkbox
  try {
    const checkbox = page.locator('#showClosed');
    if (await checkbox.isVisible({ timeout: 3000 })) {
      await checkbox.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: resolve(ssDir, `projects-showAll-${name}-showclosed.png`) });
      console.log(`${name}: Show Closed checkbox screenshot taken`);
    }
  } catch (e) {
    console.log(`${name}: Show Closed click failed: ${e.message}`);
  }

  // Test 3: Click column header to test sorting
  try {
    const header = page.locator('th:has-text("Project Name")').first();
    if (await header.isVisible({ timeout: 3000 })) {
      await header.click();
      await page.waitForTimeout(1000);
      await page.screenshot({ path: resolve(ssDir, `projects-showAll-${name}-sortclick.png`) });
      console.log(`${name}: Sort click screenshot taken`);
    }
  } catch (e) {
    console.log(`${name}: Sort click failed: ${e.message}`);
  }

  await browser.close();
}

async function main() {
  const localStorage = resolve(harnessDir, "state/auth/local-storage.json");
  const refStorage = resolve(harnessDir, "state/auth/reference-storage.json");
  
  console.log("Testing LOCAL site...");
  await testSite("local", "https://leantime-oss.test", localStorage);
  console.log("\nTesting REFERENCE site...");
  await testSite("reference", "https://demo.leantime.io", refStorage);
}

main().catch(console.error);
