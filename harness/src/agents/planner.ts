import { query } from "@anthropic-ai/claude-agent-sdk";
import { readFileSync, existsSync } from "fs";
import { resolve } from "path";
import { CONFIG } from "../config.js";
import type { Issue } from "../state.js";

const systemPrompt = readFileSync(
  resolve(CONFIG.promptsDir, "planner.md"),
  "utf-8"
);

export interface PlanningResult {
  fixSpecs: unknown[];
  costUsd: number;
}

export async function runPlanner(issues: Issue[]): Promise<PlanningResult> {
  const issuesSummary = issues
    .map(
      (i) =>
        `- [${i.severity}] ${i.category}: ${i.description} (page: ${i.pageId})` +
        (i.suggestedFiles?.length
          ? `\n  Suggested files: ${i.suggestedFiles.join(", ")}`
          : "")
    )
    .join("\n");

  // Include user feedback if available
  const feedbackFile = resolve(CONFIG.stateDir, "user-feedback.txt");
  const userFeedback = existsSync(feedbackFile)
    ? readFileSync(feedbackFile, "utf-8")
    : "";

  const prompt = `Analyze these ${issues.length} issues and create grouped fix specifications.
${userFeedback ? `\n## User Feedback (IMPORTANT — prioritize this)\n${userFeedback}\n` : ""}

## Issues Found
${issuesSummary}

## Full Issue Data
The complete issue JSON files are in harness/state/issues/. Read them for full details.

## Instructions
1. Read the issue files for complete details
2. Read the relevant source code files to understand the root causes
3. Group related issues that share a root cause
4. Write fix specifications to harness/state/fixes/ as JSON files (fix-001.json, fix-002.json, etc.)
5. Order by severity: critical issues first

Output a summary of the fix plan.`;

  let costUsd = 0;
  let turnCount = 0;
  console.log(`  [planner] Analyzing ${issues.length} issues...`);

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
      maxTurns: CONFIG.maxTurnsPlanner,
      tools: ["Read", "Glob", "Grep", "Write"],
      allowedTools: ["Read", "Glob", "Grep", "Write"],
      permissionMode: "acceptEdits",
    },
  })) {
    if (message.type === "assistant") {
      turnCount++;
      console.log(`  [planner] Turn ${turnCount}/${CONFIG.maxTurnsPlanner}`);
    }
    if (message.type === "result" && message.subtype === "success") {
      costUsd = message.total_cost_usd;
      console.log(`  [planner] Done. ${turnCount} turns, $${costUsd.toFixed(2)}`);
    }
  }

  return {
    fixSpecs: [], // Loaded separately from state files
    costUsd,
  };
}
