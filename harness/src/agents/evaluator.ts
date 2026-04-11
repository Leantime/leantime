import { query } from "@anthropic-ai/claude-agent-sdk";
import { readFileSync, existsSync } from "fs";
import { resolve } from "path";
import { CONFIG } from "../config.js";
import type { Issue, PageResult } from "../state.js";

const systemPrompt = readFileSync(
  resolve(CONFIG.promptsDir, "evaluator.md"),
  "utf-8"
);

export interface EvaluationResult {
  pageId: string;
  issues: Issue[];
  passed: boolean;
  costUsd: number;
}

export async function runEvaluator(
  pageResults: PageResult[],
  comparisonData?: Record<string, unknown>
): Promise<EvaluationResult[]> {
  const resultsContext = pageResults
    .map(
      (r) =>
        `## Page: ${r.pageId} (${r.url})\n` +
        `Screenshot: ${r.screenshot}\n` +
        `Console Errors: ${JSON.stringify(r.consoleErrors)}\n` +
        `Network Errors: ${JSON.stringify(r.networkErrors)}\n` +
        `HTMX Issues: ${JSON.stringify(r.htmxIssues)}\n` +
        `DOM Metrics: ${JSON.stringify(r.domMetrics)}\n` +
        `Passed initial check: ${r.passed}\n`
    )
    .join("\n---\n");

  const prompt = `Evaluate these page results. Compare local vs reference.

${resultsContext}

${comparisonData ? `\nComparison with reference site:\n${JSON.stringify(comparisonData, null, 2)}` : ""}

Write issues to harness/state/issues/.`;

  const results: EvaluationResult[] = [];
  let costUsd = 0;

  for await (const message of query({
    prompt,
    options: {
      systemPrompt: {
        type: "preset",
        preset: "claude_code",
        append: systemPrompt,
      },
      cwd: CONFIG.projectRoot,
      model: CONFIG.model,
      maxTurns: CONFIG.maxTurnsEvaluator,
      tools: ["Read", "Bash", "Glob", "Write"],
      allowedTools: ["Read", "Bash", "Glob", "Write"],
      permissionMode: "acceptEdits",
    },
  })) {
    if (message.type === "result" && message.subtype === "success") {
      costUsd = message.total_cost_usd;
      for (const pr of pageResults) {
        results.push({ pageId: pr.pageId, issues: [], passed: pr.passed, costUsd });
      }
    }
  }

  return results;
}

/**
 * Evaluate a single page by comparing local vs reference visually.
 * The agent uses Playwright MCP to open both sites and compare them.
 */
export async function evaluateSinglePage(
  pageId: string,
  pagePath: string
): Promise<EvaluationResult> {
  const prompt = `Compare this page on LOCAL vs REFERENCE and report every visual/behavioral difference.

## Page: ${pageId}
## Path: ${pagePath}

## Step 1 — Run the automated scan
\`cd harness && npx tsx src/playwright/evaluate-page.ts --page ${pageId} --path "${pagePath}" --target local\`
Read the result JSON: harness/state/screenshots/${pageId}-local.json
Read the screenshot: harness/state/screenshots/${pageId}-local.png

## Step 2 — Take a reference screenshot
Use Playwright MCP:
1. browser_navigate to ${CONFIG.referenceUrl}${pagePath}
2. browser_take_screenshot (save as harness/state/screenshots/${pageId}-reference.png)

If you get redirected to a login page, log in first:
- Navigate to ${CONFIG.referenceUrl}/auth/login
- Fill username: ${CONFIG.referenceUser}
- Fill password: ${CONFIG.referencePass}
- Submit, then navigate to ${CONFIG.referenceUrl}${pagePath}

## Step 3 — Compare the two screenshots
Read BOTH:
- harness/state/screenshots/${pageId}-local.png (LOCAL)
- harness/state/screenshots/${pageId}-reference.png (REFERENCE)

Note every visual difference. The reference is the ground truth.

## Step 4 — Test key interactions on BOTH sites
For this page, test the most important 3-5 interactions:
- Click tabs/navigation links within the page
- Open any modals or dialogs
- Test dropdowns

For EACH interaction:
1. Do it on LOCAL, take a screenshot
2. Do the SAME thing on REFERENCE, take a screenshot
3. Compare

The biggest bug to catch: clicking a tab or link on LOCAL causes the ENTIRE PAGE to render inside the content area (you'll see double navigation, double headers). This is CRITICAL.

## Step 5 — Write issues
Write ONLY visual/behavioral differences to harness/state/issues/${pageId}.json
Do NOT report: i18n issues, accessibility issues, code quality issues, or things that look the same on both sites.`;

  const mcpConfig: Record<string, any> = {
    playwright: {
      command: "npx",
      args: ["@anthropic-ai/mcp-playwright@latest"],
    },
  };

  let costUsd = 0;
  let turnCount = 0;

  console.log(`  [evaluator] Starting 1:1 comparison for ${pageId}...`);

  for await (const message of query({
    prompt,
    options: {
      systemPrompt: {
        type: "preset",
        preset: "claude_code",
        append: systemPrompt,
      },
      cwd: CONFIG.projectRoot,
      model: CONFIG.model,
      maxTurns: CONFIG.maxTurnsEvaluator,
      tools: ["Read", "Bash", "Glob", "Write"],
      allowedTools: ["Read", "Bash", "Glob", "Write"],
      mcpServers: mcpConfig,
      permissionMode: "acceptEdits",
    },
  })) {
    if (message.type === "assistant") {
      turnCount++;
      const content = (message as any).message?.content;
      const preview = typeof content === "string"
        ? content.slice(0, 120).replace(/\n/g, " ")
        : "";
      console.log(`  [evaluator] Turn ${turnCount}/${CONFIG.maxTurnsEvaluator}: ${preview || "(tool use)"}`);
    }
    if (message.type === "result" && message.subtype === "success") {
      costUsd = message.total_cost_usd;
      console.log(`  [evaluator] Done. ${turnCount} turns, $${costUsd.toFixed(2)}`);
    }
    if (message.type === "result" && message.subtype !== "success") {
      console.log(`  [evaluator] ENDED: ${message.subtype}`);
    }
  }

  return { pageId, issues: [], passed: false, costUsd };
}
