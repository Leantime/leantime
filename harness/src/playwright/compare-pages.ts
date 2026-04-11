/**
 * Compares a page on local vs reference by taking side-by-side screenshots.
 * Detects expired auth sessions and re-authenticates automatically.
 *
 * Usage:
 *   npx tsx src/playwright/compare-pages.ts --page <pageId> --path <urlPath>
 */
import { chromium, type Page } from "@playwright/test";
import { resolve, dirname } from "path";
import { fileURLToPath } from "url";
import { existsSync, writeFileSync } from "fs";
import { execSync } from "child_process";
import { config } from "dotenv";

const __dirname = dirname(fileURLToPath(import.meta.url));
config({ path: resolve(__dirname, "../../.env") });

const args = process.argv.slice(2);
function getArg(name: string, fallback: string): string {
  const idx = args.indexOf(`--${name}`);
  return idx >= 0 && args[idx + 1] ? args[idx + 1] : fallback;
}

const pageId = getArg("page", "unknown");
const pagePath = getArg("path", "/dashboard/home");

const localUrl = process.env.LOCAL_URL || "https://leantime-oss.test";
const refUrl = process.env.REFERENCE_URL || "https://demo.leantime.io";

const localAuth = resolve(__dirname, "../../state/auth/local-storage.json");
const refAuth = resolve(__dirname, "../../state/auth/reference-storage.json");

async function isLoggedIn(page: Page): Promise<boolean> {
  const url = page.url();
  if (url.includes("/auth/login") || url.includes("/install")) return false;
  // Check for presence of main app chrome (nav, sidebar, etc.)
  const hasNav = await page.evaluate(() => {
    return document.querySelectorAll("nav, .mainnavigation, #navigation, .leftpanel").length > 0;
  });
  return hasNav;
}

function reAuth(target: "local" | "reference") {
  console.error(`Session expired for ${target}. Re-authenticating...`);
  execSync(
    `cd "${resolve(__dirname, "../..")}" && npx tsx src/playwright/login.ts --target ${target}`,
    { stdio: "inherit", timeout: 30000 }
  );
}

interface PageSnapshot {
  title: string;
  elementCount: number;
  mainContentHeight: number;
  mainContentWidth: number;
  hasError: boolean;
  bodyClasses: string;
  /** Key structural elements and their visibility */
  structure: {
    hasSidebar: boolean;
    hasTopNav: boolean;
    hasTabs: boolean;
    tabCount: number;
    formCount: number;
    tableCount: number;
    cardCount: number;
    buttonCount: number;
    inputCount: number;
    modalCount: number;
  };
  /** Text content digest — the first 200 chars of each major content section */
  contentSections: string[];
}

async function takeSnapshot(
  baseUrl: string,
  authFile: string,
  screenshotPath: string,
  target: "local" | "reference"
): Promise<PageSnapshot | null> {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    storageState: authFile,
    ignoreHTTPSErrors: true,
    viewport: { width: 1440, height: 900 },
  });
  const page = await context.newPage();

  await page.goto(`${baseUrl}${pagePath}`, {
    waitUntil: "networkidle",
    timeout: 30000,
  }).catch(() => {});

  await page.waitForTimeout(3000);

  // Check if we're actually logged in
  if (!(await isLoggedIn(page))) {
    await browser.close();
    // Re-authenticate and retry once
    reAuth(target);
    const browser2 = await chromium.launch({ headless: true });
    const context2 = await browser2.newContext({
      storageState: authFile,
      ignoreHTTPSErrors: true,
      viewport: { width: 1440, height: 900 },
    });
    const page2 = await context2.newPage();
    await page2.goto(`${baseUrl}${pagePath}`, {
      waitUntil: "networkidle",
      timeout: 30000,
    }).catch(() => {});
    await page2.waitForTimeout(3000);

    if (!(await isLoggedIn(page2))) {
      console.error(`Failed to authenticate to ${target} after retry.`);
      await browser2.close();
      return null;
    }

    await page2.screenshot({ path: screenshotPath, fullPage: true });
    const snap = await collectSnapshot(page2);
    await browser2.close();
    return snap;
  }

  await page.screenshot({ path: screenshotPath, fullPage: true });
  const snap = await collectSnapshot(page);
  await browser.close();
  return snap;
}

async function collectSnapshot(page: Page): Promise<PageSnapshot> {
  return await page.evaluate(() => {
    const main = document.querySelector(
      ".maincontent, .main-content, #main, main, .content-wrapper"
    );

    // Collect text from major content sections for comparison
    const sections: string[] = [];
    document.querySelectorAll(
      ".widget, .card, .panel, section, .tab-pane, .content-section, form"
    ).forEach((el) => {
      const text = (el.textContent || "").trim().replace(/\s+/g, " ").slice(0, 200);
      if (text.length > 20) sections.push(text);
    });

    return {
      title: document.title,
      elementCount: document.querySelectorAll("*").length,
      mainContentHeight: main ? main.scrollHeight : document.body.scrollHeight,
      mainContentWidth: main ? main.scrollWidth : document.body.scrollWidth,
      hasError:
        document.querySelectorAll(".error, .alert-danger, .alert-error").length > 0,
      bodyClasses: document.body.className,
      structure: {
        hasSidebar: document.querySelectorAll(".leftpanel, .sidebar, aside").length > 0,
        hasTopNav: document.querySelectorAll("nav, .navbar, .mainnavigation").length > 0,
        hasTabs: document.querySelectorAll('[role="tab"], .nav-tabs a, [data-toggle="tab"]').length > 0,
        tabCount: document.querySelectorAll('[role="tab"], .nav-tabs a, [data-toggle="tab"]').length,
        formCount: document.querySelectorAll("form").length,
        tableCount: document.querySelectorAll("table").length,
        cardCount: document.querySelectorAll(".card, .widget, .panel").length,
        buttonCount: document.querySelectorAll("button, input[type='submit'], .btn").length,
        inputCount: document.querySelectorAll("input, select, textarea").length,
        modalCount: document.querySelectorAll(".modal, [role='dialog']").length,
      },
      contentSections: sections.slice(0, 10),
    };
  });
}

async function compare() {
  if (!existsSync(localAuth)) {
    console.error("Local auth not found. Run: npm run login");
    process.exit(1);
  }
  if (!existsSync(refAuth)) {
    // Auto-login to reference
    reAuth("reference");
  }

  const localScreenshot = resolve(
    __dirname,
    `../../state/screenshots/${pageId}-local.png`
  );
  const refScreenshot = resolve(
    __dirname,
    `../../state/screenshots/${pageId}-reference.png`
  );

  console.error(`Comparing ${pageId}: local vs reference...`);

  const [localSnap, refSnap] = await Promise.all([
    takeSnapshot(localUrl, localAuth, localScreenshot, "local"),
    takeSnapshot(refUrl, refAuth, refScreenshot, "reference"),
  ]);

  if (!localSnap || !refSnap) {
    // Gracefully handle — still produce a result with what we have
    const partialComparison = {
      pageId,
      path: pagePath,
      local: localSnap,
      reference: refSnap,
      error: !localSnap ? "Could not load local page" : "Could not load reference page (session expired or page doesn't exist on reference). The evaluator should navigate to the reference site manually via Playwright MCP to compare.",
      screenshots: {
        local: localSnap ? resolve(__dirname, `../../state/screenshots/${pageId}-local.png`) : null,
        reference: refSnap ? resolve(__dirname, `../../state/screenshots/${pageId}-reference.png`) : null,
      },
      note: "Reference comparison incomplete. The evaluator agent should use Playwright MCP to navigate to the reference site directly and compare visually.",
    };
    const resultFile = resolve(__dirname, `../../state/screenshots/${pageId}-comparison.json`);
    writeFileSync(resultFile, JSON.stringify(partialComparison, null, 2));
    console.log(JSON.stringify(partialComparison, null, 2));
    return;
  }

  const comparison = {
    pageId,
    path: pagePath,
    local: localSnap,
    reference: refSnap,
    structureDiffs: {
      sidebarMatch: localSnap.structure.hasSidebar === refSnap.structure.hasSidebar,
      topNavMatch: localSnap.structure.hasTopNav === refSnap.structure.hasTopNav,
      tabCountDiff: localSnap.structure.tabCount - refSnap.structure.tabCount,
      formCountDiff: localSnap.structure.formCount - refSnap.structure.formCount,
      inputCountDiff: localSnap.structure.inputCount - refSnap.structure.inputCount,
      buttonCountDiff: localSnap.structure.buttonCount - refSnap.structure.buttonCount,
      cardCountDiff: localSnap.structure.cardCount - refSnap.structure.cardCount,
      elementCountDiff: localSnap.elementCount - refSnap.elementCount,
      heightDiff: localSnap.mainContentHeight - refSnap.mainContentHeight,
    },
    screenshots: {
      local: localScreenshot,
      reference: refScreenshot,
    },
    note: "IMPORTANT: The evaluator agent MUST visually compare the two screenshots. " +
      "The local version must match the reference design. " +
      "Any visual deviation (layout, spacing, styling, component appearance) is an issue.",
  };

  const resultFile = resolve(
    __dirname,
    `../../state/screenshots/${pageId}-comparison.json`
  );
  writeFileSync(resultFile, JSON.stringify(comparison, null, 2));
  console.log(JSON.stringify(comparison, null, 2));
}

compare().catch((err) => {
  console.error("Comparison failed:", err.message);
  process.exit(1);
});
