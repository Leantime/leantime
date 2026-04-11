import { query } from "@anthropic-ai/claude-agent-sdk";
import { readFileSync } from "fs";
import { resolve } from "path";
import { CONFIG } from "../config.js";
import type { FixSpec } from "../state.js";

const systemPrompt = readFileSync(
  resolve(CONFIG.promptsDir, "fixer.md"),
  "utf-8"
);

export interface FixResult {
  fixId: string;
  success: boolean;
  filesChanged: string[];
  costUsd: number;
  summary: string;
}

export async function runFixer(spec: FixSpec): Promise<FixResult> {
  const prompt = `Implement this fix:

## Fix Specification
- **ID**: ${spec.id}
- **Root Cause**: ${spec.rootCause}
- **Files to modify**: ${spec.files.join(", ")}
- **Description**: ${spec.description}
- **Related issues**: ${spec.issueIds.join(", ")}

## Instructions
1. Read the fix specification from harness/state/fixes/${spec.id}.json for full details
2. Read each file that needs modification
3. Implement the fix with minimal, targeted changes
4. After fixing, run \`cd /Users/marcelfolaron/Source/Leantime/leantime-oss && npm run build\` if you changed JS/CSS files
5. Create a git commit for this fix with a descriptive message

Output a summary of what you changed.`;

  let costUsd = 0;
  let summary = "";
  let turnCount = 0;
  console.log(`  [fixer] Fixing ${spec.id}: ${spec.rootCause.slice(0, 60)}`);

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
      maxTurns: CONFIG.maxTurnsFixer,
      maxBudgetUsd: CONFIG.maxBudgetPerFix,
      tools: ["Read", "Edit", "Write", "Bash", "Glob", "Grep"],
      allowedTools: ["Read", "Edit", "Write", "Bash", "Glob", "Grep"],
      permissionMode: "acceptEdits",
    },
  })) {
    if (message.type === "assistant") {
      turnCount++;
      console.log(`  [fixer] Turn ${turnCount}/${CONFIG.maxTurnsFixer}`);
    }
    if (message.type === "result" && message.subtype === "success") {
      costUsd = message.total_cost_usd;
      summary = message.result;
      console.log(`  [fixer] Done. ${turnCount} turns, $${costUsd.toFixed(2)}`);
    }
  }

  return {
    fixId: spec.id,
    success: true, // We'll verify with re-evaluation
    filesChanged: spec.files,
    costUsd,
    summary,
  };
}
