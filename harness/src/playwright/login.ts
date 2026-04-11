/**
 * Playwright login script — authenticates and saves storage state.
 *
 * Usage:
 *   npx tsx src/playwright/login.ts [--url URL] [--user EMAIL] [--pass PASSWORD] [--target local|reference]
 */
import { chromium } from "@playwright/test";
import { resolve, dirname } from "path";
import { fileURLToPath } from "url";
import { config } from "dotenv";

const __dirname = dirname(fileURLToPath(import.meta.url));
config({ path: resolve(__dirname, "../../.env") });

const args = process.argv.slice(2);
function getArg(name: string, fallback: string): string {
  const idx = args.indexOf(`--${name}`);
  return idx >= 0 && args[idx + 1] ? args[idx + 1] : fallback;
}

const target = getArg("target", "local");
const isReference = target === "reference";

const url = getArg(
  "url",
  isReference
    ? process.env.REFERENCE_URL || "https://demo.leantime.io"
    : process.env.LOCAL_URL || "https://leantime-oss.test"
);
const user = getArg(
  "user",
  isReference
    ? process.env.REFERENCE_USER || "marcel@leantime.io"
    : process.env.LOCAL_USER || "marcel@leantime.io"
);
const pass = getArg(
  "pass",
  isReference
    ? process.env.REFERENCE_PASS || ""
    : process.env.LOCAL_PASS || "test"
);

const stateFile = resolve(
  __dirname,
  `../../state/auth/${target}-storage.json`
);

async function login() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ ignoreHTTPSErrors: true });
  const page = await context.newPage();

  console.log(`Logging into ${url} as ${user}...`);

  await page.goto(`${url}/auth/login`, { waitUntil: "networkidle" });

  // Wait for form to be fully loaded and interactive
  await page.waitForSelector('input[name="username"]', { state: "visible", timeout: 10000 });
  await page.waitForSelector('input[name="password"]', { state: "visible", timeout: 10000 });
  await page.waitForTimeout(1000); // Let JS initialize

  // Fill login form using click + type for reliability
  const usernameInput = page.locator('input[name="username"]');
  await usernameInput.click();
  await usernameInput.fill(user);

  const passwordInput = page.locator('input[name="password"]');
  await passwordInput.click();
  await passwordInput.fill(pass);

  // Submit — handle both button and input submit elements
  const submitBtn = page.locator('button[type="submit"], input[type="submit"]').first();
  await submitBtn.click();
  await page.waitForLoadState("networkidle").catch(() => {});
  await page.waitForTimeout(2000);

  // Verify we left the login page
  if (page.url().includes("/auth/login")) {
    throw new Error("Login failed — still on login page. Check credentials.");
  }

  console.log(`Logged in. Current URL: ${page.url()}`);

  // Save storage state (cookies + localStorage)
  await context.storageState({ path: stateFile });
  console.log(`Storage state saved to ${stateFile}`);

  await browser.close();
}

login().catch((err) => {
  console.error("Login failed:", err.message);
  process.exit(1);
});
