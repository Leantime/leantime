import { readFileSync, writeFileSync, existsSync, readdirSync } from "fs";
import { resolve } from "path";
import { CONFIG } from "./config.js";

export interface Issue {
  id: string;
  pageId: string;
  severity: "critical" | "major" | "minor" | "cosmetic";
  category:
    | "htmx-full-page"
    | "js-error"
    | "css-regression"
    | "broken-interaction"
    | "layout-glitch"
    | "missing-content"
    | "other";
  description: string;
  expected: string;
  actual: string;
  screenshot?: string;
  consoleErrors?: string[];
  suggestedFiles?: string[];
}

export interface FixSpec {
  id: string;
  issueIds: string[];
  rootCause: string;
  files: string[];
  description: string;
  status: "planned" | "in-progress" | "applied" | "verified" | "failed";
}

export interface PageResult {
  pageId: string;
  url: string;
  evaluatedAt: string;
  screenshot: string;
  consoleErrors: string[];
  networkErrors: string[];
  htmxIssues: string[];
  domMetrics: Record<string, number>;
  passed: boolean;
}

export interface HarnessState {
  startedAt: string;
  lastUpdated: string;
  currentPhase: "evaluate" | "plan" | "fix" | "verify" | "complete";
  evaluated: string[];
  issueCount: number;
  fixesPlanned: string[];
  fixesApplied: string[];
  fixesFailed: string[];
  totalCostUsd: number;
}

const STATE_FILE = resolve(CONFIG.stateDir, "progress.json");

export function loadState(): HarnessState {
  if (existsSync(STATE_FILE)) {
    return JSON.parse(readFileSync(STATE_FILE, "utf-8"));
  }
  return {
    startedAt: new Date().toISOString(),
    lastUpdated: new Date().toISOString(),
    currentPhase: "evaluate",
    evaluated: [],
    issueCount: 0,
    fixesPlanned: [],
    fixesApplied: [],
    fixesFailed: [],
    totalCostUsd: 0,
  };
}

export function saveState(state: HarnessState): void {
  state.lastUpdated = new Date().toISOString();
  writeFileSync(STATE_FILE, JSON.stringify(state, null, 2));
}

export function savePageResult(result: PageResult): void {
  const file = resolve(CONFIG.screenshotsDir, `${result.pageId}.json`);
  writeFileSync(file, JSON.stringify(result, null, 2));
}

export function loadPageResult(pageId: string): PageResult | null {
  const file = resolve(CONFIG.screenshotsDir, `${pageId}.json`);
  if (!existsSync(file)) return null;
  return JSON.parse(readFileSync(file, "utf-8"));
}

export function saveIssues(pageId: string, issues: Issue[]): void {
  const file = resolve(CONFIG.issuesDir, `${pageId}.json`);
  writeFileSync(file, JSON.stringify(issues, null, 2));
}

export function loadAllIssues(): Issue[] {
  const issues: Issue[] = [];
  if (!existsSync(CONFIG.issuesDir)) return issues;
  for (const f of readdirSync(CONFIG.issuesDir).filter((f) =>
    f.endsWith(".json")
  )) {
    const data = JSON.parse(
      readFileSync(resolve(CONFIG.issuesDir, f), "utf-8")
    );
    issues.push(...(Array.isArray(data) ? data : [data]));
  }
  return issues;
}

export function saveFixSpec(spec: FixSpec): void {
  const file = resolve(CONFIG.fixesDir, `${spec.id}.json`);
  writeFileSync(file, JSON.stringify(spec, null, 2));
}

export function loadFixSpecs(): FixSpec[] {
  const specs: FixSpec[] = [];
  if (!existsSync(CONFIG.fixesDir)) return specs;
  for (const f of readdirSync(CONFIG.fixesDir).filter((f) =>
    f.endsWith(".json")
  )) {
    specs.push(
      JSON.parse(readFileSync(resolve(CONFIG.fixesDir, f), "utf-8"))
    );
  }
  return specs;
}
