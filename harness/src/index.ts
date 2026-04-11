#!/usr/bin/env npx tsx
/**
 * Leantime Harness Orchestrator
 *
 * Three-agent loop: Evaluate → Plan → Fix → Verify
 * Based on: https://www.anthropic.com/engineering/harness-design-long-running-apps
 *
 * Usage:
 *   npx tsx src/index.ts                    # Full run (all phases)
 *   npx tsx src/index.ts --phase evaluate   # Only evaluate pages
 *   npx tsx src/index.ts --phase plan       # Only plan fixes (requires evaluation)
 *   npx tsx src/index.ts --phase fix        # Only apply fixes (requires plan)
 *   npx tsx src/index.ts --priority 1       # Only priority-1 pages
 *   npx tsx src/index.ts --page dashboard-home  # Single page
 *   npx tsx src/index.ts --resume           # Resume from last state
 */
import { execSync } from "child_process";
import { existsSync, mkdirSync, writeFileSync } from "fs";
import { createInterface } from "readline";
import { CONFIG } from "./config.js";
import { getPagesByPriority, getPage, type PageEntry } from "./manifest.js";
import {
  loadState,
  saveState,
  loadAllIssues,
  loadFixSpecs,
  saveFixSpec,
  loadPageResult,
  type HarnessState,
} from "./state.js";
import { evaluateSinglePage } from "./agents/evaluator.js";
import { runPlanner } from "./agents/planner.js";
import { runFixer } from "./agents/fixer.js";

// ---------------------------------------------------------------------------
// CLI args
// ---------------------------------------------------------------------------
const args = process.argv.slice(2);
function getArg(name: string, fallback: string): string {
  const idx = args.indexOf(`--${name}`);
  return idx >= 0 && args[idx + 1] ? args[idx + 1] : fallback;
}
const hasFlag = (name: string) => args.includes(`--${name}`);

const phaseArg = getArg("phase", "all");
const priorityArg = parseInt(getArg("priority", "3")) as 1 | 2 | 3;
const singlePage = getArg("page", "");
const resumeMode = hasFlag("resume");
const autoMode = hasFlag("auto"); // --auto skips feedback prompts

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------
function log(msg: string) {
  const ts = new Date().toISOString().slice(11, 19);
  console.log(`[${ts}] ${msg}`);
}

/**
 * Pause and ask for user feedback. Returns the feedback text,
 * or empty string if user just pressed Enter.
 * Special inputs:
 *   "skip" — skip the current fix
 *   "stop" — stop the harness
 *   "revert" — revert the last commit
 */
async function askForFeedback(prompt: string): Promise<string> {
  if (autoMode) return "";

  const rl = createInterface({ input: process.stdin, output: process.stdout });
  return new Promise((resolve) => {
    rl.question(`\n💬 ${prompt}\n   [Enter=continue, "skip"=skip next, "stop"=halt, "revert"=undo last commit]\n   > `, (answer) => {
      rl.close();
      resolve(answer.trim());
    });
  });
}

function ensureDirs() {
  for (const dir of [
    CONFIG.stateDir,
    CONFIG.screenshotsDir,
    CONFIG.issuesDir,
    CONFIG.fixesDir,
    CONFIG.authDir,
  ]) {
    if (!existsSync(dir)) mkdirSync(dir, { recursive: true });
  }
}

function ensureAuth(target: "local" | "reference" = "local") {
  const authFile = `${CONFIG.authDir}/${target}-storage.json`;
  if (!existsSync(authFile)) {
    log(`Auth state missing for ${target}. Running login...`);
    execSync(
      `cd "${CONFIG.harnessRoot}" && npx tsx src/playwright/login.ts --target ${target}`,
      { stdio: "inherit", timeout: 30000 }
    );
    if (!existsSync(authFile)) {
      throw new Error(`Login failed for ${target} — auth file not created`);
    }
    log(`Login successful for ${target}.`);
  }
}

// ---------------------------------------------------------------------------
// Phase: Evaluate
// ---------------------------------------------------------------------------
async function runEvaluatePhase(
  state: HarnessState,
  pages: PageEntry[]
): Promise<void> {
  state.currentPhase = "evaluate";
  saveState(state);

  const toEvaluate = pages.filter((p) => !state.evaluated.includes(p.id));

  if (toEvaluate.length === 0) {
    log("All pages already evaluated. Use --phase evaluate without --resume to re-evaluate.");
    return;
  }

  log(
    `Evaluating ${toEvaluate.length} pages (${pages.length - toEvaluate.length} already done)...`
  );

  for (const page of toEvaluate) {
    log(`[EVAL] ${page.name} (${page.path})`);

    try {
      const result = await evaluateSinglePage(page.id, page.path);
      state.evaluated.push(page.id);
      state.totalCostUsd += result.costUsd;
      saveState(state);

      // Check result
      const pageResult = loadPageResult(page.id);
      if (pageResult && !pageResult.passed) {
        log(
          `  -> Issues found: ${pageResult.consoleErrors.length} console errors, ${pageResult.htmxIssues.length} HTMX issues`
        );
      } else {
        log(`  -> Passed initial check`);
      }
    } catch (err: any) {
      log(`  -> ERROR: ${err.message}`);
      // Continue with next page
    }
  }

  // Count total issues
  const allIssues = loadAllIssues();
  state.issueCount = allIssues.length;
  saveState(state);

  log(
    `Evaluation complete. ${state.evaluated.length} pages evaluated, ${state.issueCount} issues found. Cost: $${state.totalCostUsd.toFixed(2)}`
  );

  // Let user review issues before planning
  const feedback = await askForFeedback(
    `Evaluation done. Review the issues in harness/state/issues/ — anything it missed? Any feedback?`
  );
  if (feedback === "stop") {
    log("Stopped by user.");
    return;
  }
  if (feedback) {
    const feedbackFile = `${CONFIG.stateDir}/user-feedback.txt`;
    const entry = `[${new Date().toISOString()}] After evaluation: ${feedback}\n`;
    writeFileSync(feedbackFile, entry, { flag: "a" });
    log(`Feedback saved.`);
  }
}

// ---------------------------------------------------------------------------
// Phase: Plan
// ---------------------------------------------------------------------------
async function runPlanPhase(state: HarnessState): Promise<void> {
  state.currentPhase = "plan";
  saveState(state);

  const issues = loadAllIssues();
  if (issues.length === 0) {
    log("No issues to plan fixes for. Run evaluation first.");
    return;
  }

  log(`Planning fixes for ${issues.length} issues...`);

  const result = await runPlanner(issues);
  state.totalCostUsd += result.costUsd;

  // Reload fix specs from files written by the planner agent
  const specs = loadFixSpecs();
  state.fixesPlanned = specs.map((s) => s.id);
  saveState(state);

  log(
    `Planning complete. ${specs.length} fix specs created. Cost: $${result.costUsd.toFixed(2)}`
  );
}

// ---------------------------------------------------------------------------
// Phase: Fix
// ---------------------------------------------------------------------------
async function runFixPhase(state: HarnessState): Promise<void> {
  state.currentPhase = "fix";
  saveState(state);

  const specs = loadFixSpecs();
  const toFix = specs.filter(
    (s) =>
      s.status === "planned" &&
      !state.fixesApplied.includes(s.id) &&
      !state.fixesFailed.includes(s.id)
  );

  if (toFix.length === 0) {
    log("No fixes to apply. Run planning first.");
    return;
  }

  log(`Applying ${toFix.length} fixes...`);

  for (const spec of toFix) {
    log(`[FIX] ${spec.id}: ${spec.rootCause}`);

    try {
      const result = await runFixer(spec);
      state.totalCostUsd += result.costUsd;

      if (result.success) {
        spec.status = "applied";
        saveFixSpec(spec);
        state.fixesApplied.push(spec.id);
        log(`  -> Applied. ${result.summary.slice(0, 100)}`);
      } else {
        spec.status = "failed";
        saveFixSpec(spec);
        state.fixesFailed.push(spec.id);
        log(`  -> Failed.`);
      }

      saveState(state);

      // --- User feedback checkpoint ---
      const feedback = await askForFeedback(
        `Fix ${spec.id} applied. Check the site and give feedback (or just hit Enter to continue).`
      );

      if (feedback === "stop") {
        log("Stopped by user.");
        return;
      }
      if (feedback === "revert") {
        log("Reverting last commit...");
        execSync("git revert --no-edit HEAD", { cwd: CONFIG.projectRoot, stdio: "inherit" });
        state.fixesApplied.pop();
        spec.status = "failed";
        saveFixSpec(spec);
        state.fixesFailed.push(spec.id);
        saveState(state);
        log("Reverted.");
        continue;
      }
      if (feedback === "skip") {
        log("Skipping next fix.");
        continue;
      }
      if (feedback) {
        // Save user feedback to a file the fixer/planner can read next time
        const feedbackFile = `${CONFIG.stateDir}/user-feedback.txt`;
        const entry = `[${new Date().toISOString()}] After ${spec.id}: ${feedback}\n`;
        writeFileSync(feedbackFile, entry, { flag: "a" });
        log(`Feedback saved. Will be included in future agent context.`);
      }
    } catch (err: any) {
      log(`  -> ERROR: ${err.message}`);
      state.fixesFailed.push(spec.id);
      saveState(state);
    }
  }

  log(
    `Fix phase complete. Applied: ${state.fixesApplied.length}, Failed: ${state.fixesFailed.length}. Cost: $${state.totalCostUsd.toFixed(2)}`
  );
}

// ---------------------------------------------------------------------------
// Phase: Verify
// ---------------------------------------------------------------------------
async function runVerifyPhase(
  state: HarnessState,
  pages: PageEntry[]
): Promise<void> {
  state.currentPhase = "verify";
  saveState(state);

  if (state.fixesApplied.length === 0) {
    log("No fixes to verify.");
    return;
  }

  log(`Re-evaluating pages after ${state.fixesApplied.length} fixes...`);

  // Reset evaluation state for re-evaluation
  const previouslyEvaluated = [...state.evaluated];
  state.evaluated = [];
  saveState(state);

  await runEvaluatePhase(state, pages);

  const issues = loadAllIssues();
  const previousIssueCount = state.issueCount;

  log(
    `Verification complete. Issues: ${previousIssueCount} -> ${issues.length}`
  );

  if (issues.length < previousIssueCount) {
    log(
      `Progress: ${previousIssueCount - issues.length} issues resolved.`
    );
  }

  if (issues.length > 0) {
    log(`${issues.length} issues remain. Consider running another fix cycle.`);
  }

  state.currentPhase = "complete";
  saveState(state);
}

// ---------------------------------------------------------------------------
// Main
// ---------------------------------------------------------------------------
async function main() {
  log("=== Leantime Harness ===");
  log(`Phase: ${phaseArg} | Priority: ${priorityArg} | Resume: ${resumeMode}`);

  ensureDirs();

  // Load or reset state
  let state: HarnessState;
  if (resumeMode) {
    state = loadState();
    log(`Resuming from previous run (${state.evaluated.length} pages evaluated, ${state.fixesApplied.length} fixes applied)`);
  } else if (phaseArg === "all") {
    state = loadState();
    // Reset if starting fresh
    if (state.evaluated.length === 0) {
      state.startedAt = new Date().toISOString();
    }
  } else {
    state = loadState();
  }

  // Ensure auth
  try {
    ensureAuth("local");
  } catch (err: any) {
    log(`FATAL: ${err.message}`);
    process.exit(1);
  }

  // Optionally auth to reference
  try {
    ensureAuth("reference");
  } catch {
    log("Warning: Reference auth failed. Comparison will be skipped.");
  }

  // Determine pages
  let pages: PageEntry[];
  if (singlePage) {
    const page = getPage(singlePage);
    if (!page) {
      log(`Unknown page: ${singlePage}`);
      process.exit(1);
    }
    pages = [page];
  } else {
    pages = getPagesByPriority(priorityArg);
  }

  log(`Pages in scope: ${pages.length}`);

  // Execute phases
  try {
    if (phaseArg === "evaluate" || phaseArg === "all") {
      await runEvaluatePhase(state, pages);
    }

    if (phaseArg === "plan" || phaseArg === "all") {
      await runPlanPhase(state);
    }

    if (phaseArg === "fix" || phaseArg === "all") {
      await runFixPhase(state);
    }

    if (phaseArg === "all") {
      await runVerifyPhase(state, pages);
    }
  } catch (err: any) {
    log(`FATAL ERROR: ${err.message}`);
    saveState(state);
    process.exit(1);
  }

  // Final summary
  log("\n=== Summary ===");
  log(`Pages evaluated: ${state.evaluated.length}`);
  log(`Issues found: ${state.issueCount}`);
  log(`Fixes applied: ${state.fixesApplied.length}`);
  log(`Fixes failed: ${state.fixesFailed.length}`);
  log(`Total cost: $${state.totalCostUsd.toFixed(2)}`);
  log(`State saved to: ${CONFIG.stateDir}/progress.json`);
}

main().catch((err) => {
  console.error("Harness crashed:", err);
  process.exit(1);
});
