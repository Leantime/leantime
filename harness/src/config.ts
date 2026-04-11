import { config } from "dotenv";
import { resolve, dirname } from "path";
import { fileURLToPath } from "url";

const __dirname = dirname(fileURLToPath(import.meta.url));
config({ path: resolve(__dirname, "../.env") });

export const CONFIG = {
  localUrl: process.env.LOCAL_URL || "https://leantime-oss.test",
  localUser: process.env.LOCAL_USER || "marcel@leantime.io",
  localPass: process.env.LOCAL_PASS || "test",

  referenceUrl: process.env.REFERENCE_URL || "https://demo.leantime.io",
  referenceUser: process.env.REFERENCE_USER || "marcel@leantime.io",
  referencePass: process.env.REFERENCE_PASS || "",

  model: process.env.MODEL || "claude-sonnet-4-6",
  maxTurnsEvaluator: parseInt(process.env.MAX_TURNS_EVALUATOR || "30"),
  maxTurnsPlanner: parseInt(process.env.MAX_TURNS_PLANNER || "20"),
  maxTurnsFixer: parseInt(process.env.MAX_TURNS_FIXER || "50"),
  maxBudgetPerFix: parseFloat(process.env.MAX_BUDGET_PER_FIX || "2.00"),

  // Paths
  projectRoot: resolve(__dirname, "../.."),
  harnessRoot: resolve(__dirname, ".."),
  stateDir: resolve(__dirname, "../state"),
  screenshotsDir: resolve(__dirname, "../state/screenshots"),
  issuesDir: resolve(__dirname, "../state/issues"),
  fixesDir: resolve(__dirname, "../state/fixes"),
  authDir: resolve(__dirname, "../state/auth"),
  promptsDir: resolve(__dirname, "../prompts"),
} as const;
