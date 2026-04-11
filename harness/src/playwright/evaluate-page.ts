/**
 * Evaluates a single page: navigates, screenshots, captures errors, HTMX issues,
 * and produces an **interaction map** of everything on the page that can be
 * clicked, submitted, or dragged.
 *
 * The interaction map is NOT tested here — it's handed to the evaluator AI agent
 * which uses Playwright MCP to selectively exercise the interactions it deems
 * important (modals, settings dialogs, drag-and-drop, form submissions, etc.).
 *
 * Usage:
 *   npx tsx src/playwright/evaluate-page.ts --page <pageId> --path <urlPath> [--target local|reference]
 */
import { chromium } from "@playwright/test";
import { resolve, dirname } from "path";
import { fileURLToPath } from "url";
import { existsSync, readFileSync, writeFileSync } from "fs";
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
const target = getArg("target", "local");
const isReference = target === "reference";

const baseUrl = isReference
  ? process.env.REFERENCE_URL || "https://demo.leantime.io"
  : process.env.LOCAL_URL || "https://leantime-oss.test";

const stateFile = resolve(
  __dirname,
  `../../state/auth/${target}-storage.json`
);
const screenshotFile = resolve(
  __dirname,
  `../../state/screenshots/${pageId}-${target}.png`
);
const resultFile = resolve(
  __dirname,
  `../../state/screenshots/${pageId}-${target}.json`
);

// ---------------------------------------------------------------------------
// Types
// ---------------------------------------------------------------------------

interface InteractiveElement {
  selector: string;
  type: string;
  label: string;
  bbox: { x: number; y: number; w: number; h: number };
}

interface EvalResult {
  pageId: string;
  target: string;
  url: string;
  evaluatedAt: string;
  screenshot: string;
  consoleErrors: string[];
  consoleWarnings: string[];
  networkErrors: string[];
  htmxIssues: string[];
  domMetrics: Record<string, number>;
  pageTitle: string;
  loadTimeMs: number;
  interactionMap: InteractiveElement[];
  passed: boolean;
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------

async function evaluatePage() {
  if (!existsSync(stateFile)) {
    console.error(
      `Auth state not found at ${stateFile}. Run login.ts first.`
    );
    process.exit(1);
  }

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    storageState: stateFile,
    ignoreHTTPSErrors: true,
    viewport: { width: 1440, height: 900 },
  });

  const page = await context.newPage();
  const consoleErrors: string[] = [];
  const consoleWarnings: string[] = [];
  const networkErrors: string[] = [];
  const htmxIssues: string[] = [];

  // Capture console messages
  page.on("console", (msg) => {
    if (msg.type() === "error") {
      consoleErrors.push(msg.text());
    } else if (msg.type() === "warning") {
      consoleWarnings.push(msg.text());
    }
  });

  page.on("pageerror", (err) => {
    consoleErrors.push(`PageError: ${err.message}`);
  });

  // Capture network failures and HTMX fragment issues
  page.on("response", async (response) => {
    const status = response.status();
    const url = response.url();

    if (status >= 400) {
      networkErrors.push(`${status} ${response.statusText()} - ${url}`);
    }

    if (url.includes("/hx/") || response.request().headers()["hx-request"]) {
      try {
        const body = await response.text().catch(() => "");
        if (body.includes("<!DOCTYPE html>") || body.includes("<html")) {
          htmxIssues.push(
            `HTMX response contains full page HTML: ${url} (${body.length} bytes)`
          );
        }
        if (body.includes("<head>") && body.includes("<body>")) {
          htmxIssues.push(
            `HTMX response contains <head>+<body> tags: ${url}`
          );
        }
      } catch {
        // Response body not available
      }
    }
  });

  const fullUrl = `${baseUrl}${pagePath}`;
  console.error(`Evaluating: ${fullUrl} (${pageId})`);

  const startTime = Date.now();

  try {
    await page.goto(fullUrl, { waitUntil: "networkidle", timeout: 30000 });
  } catch (err: any) {
    console.error(`Navigation warning: ${err.message}`);
  }

  // Wait for HTMX to settle
  await page
    .waitForFunction(
      () => {
        const htmx = (window as any).htmx;
        return !htmx || document.querySelectorAll(".htmx-request").length === 0;
      },
      { timeout: 10000 }
    )
    .catch(() => {
      htmxIssues.push("HTMX did not settle within 10s (pending requests)");
    });

  // Extra wait for lazy-loaded content
  await page.waitForTimeout(2000);

  const loadTimeMs = Date.now() - startTime;

  // -------------------------------------------------------------------------
  // Collect DOM metrics
  // -------------------------------------------------------------------------
  const domMetrics = await page.evaluate(() => {
    return {
      totalElements: document.querySelectorAll("*").length,
      visibleErrors: document.querySelectorAll(
        ".error, .alert-danger, .alert-error"
      ).length,
      emptyContainers: document.querySelectorAll(
        ".widget-content:empty, .card-body:empty, .main-content:empty"
      ).length,
      brokenImages: Array.from(document.querySelectorAll("img")).filter(
        (img) => !img.complete || img.naturalWidth === 0
      ).length,
      htmxElements: document.querySelectorAll("[hx-get],[hx-post],[hx-put],[hx-delete]").length,
      forms: document.querySelectorAll("form").length,
      modals: document.querySelectorAll(".modal, [role='dialog']").length,
      tables: document.querySelectorAll("table").length,
    };
  });

  const pageTitle = await page.title();

  // Check for duplicate page content (full page appended)
  const duplicateCheck = await page.evaluate(() => {
    const navs = document.querySelectorAll(
      "nav, .navbar, .mainnavigation, #navigation"
    );
    return { navbarCount: navs.length };
  });

  if (duplicateCheck.navbarCount > 1) {
    htmxIssues.push(
      `Duplicate navigation (${duplicateCheck.navbarCount} navbars) — likely full page in HTMX fragment`
    );
  }

  // -------------------------------------------------------------------------
  // Build interaction map — discover everything interactive on the page
  // -------------------------------------------------------------------------
  const interactionMapScript = readFileSync(
    resolve(__dirname, "interaction-map.js"), "utf-8"
  );
  const interactionMap: InteractiveElement[] = await page.evaluate(interactionMapScript);

  // Take screenshot
  await page.screenshot({ path: screenshotFile, fullPage: true });

  const result: EvalResult = {
    pageId,
    target,
    url: fullUrl,
    evaluatedAt: new Date().toISOString(),
    screenshot: screenshotFile,
    consoleErrors,
    consoleWarnings,
    networkErrors,
    htmxIssues,
    domMetrics,
    pageTitle,
    loadTimeMs,
    interactionMap,
    passed:
      consoleErrors.length === 0 &&
      htmxIssues.length === 0 &&
      networkErrors.filter((e) => !e.includes("favicon")).length === 0,
  };

  writeFileSync(resultFile, JSON.stringify(result, null, 2));
  console.log(JSON.stringify(result, null, 2));

  await browser.close();
}

evaluatePage().catch((err) => {
  console.error("Evaluation failed:", err.message);
  process.exit(1);
});
