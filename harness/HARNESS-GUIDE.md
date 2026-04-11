# Agent SDK Harness for Long-Running Site Remediation

A reusable pattern for building autonomous AI harnesses that systematically evaluate and fix web applications. Based on [Anthropic's harness design for long-running apps](https://www.anthropic.com/engineering/harness-design-long-running-apps).

---

## Core Idea

Instead of one agent doing everything, split the work into three specialized agents that communicate through files:

```
┌──────────────┐     ┌──────────────┐     ┌──────────────┐
│   Evaluator  │────▶│   Planner    │────▶│    Fixer     │
│              │     │              │     │              │
│ Finds issues │     │ Groups by    │     │ Implements   │
│ via browser  │     │ root cause   │     │ code changes │
└──────┬───────┘     └──────────────┘     └──────┬───────┘
       │                                         │
       └────────────── Re-verify ◀───────────────┘
```

The orchestrator loops: **Evaluate → Plan → Fix → Verify** until the site matches the reference.

## Why Three Agents, Not One

The key insight from the Anthropic article: **agents can't self-evaluate well**. A single agent that writes code and judges its own output will be lenient. Separating evaluation from generation — and tuning the evaluator to be skeptical — produces dramatically better results.

Each agent also gets a different tool set, limiting blast radius:

| Agent | Tools | Can Modify Code? |
|-------|-------|-----------------|
| Evaluator | Browser (Playwright MCP), Read, Bash, Write (state files only) | No |
| Planner | Read, Glob, Grep, Write (state files only) | No |
| Fixer | Read, Edit, Write, Bash, Glob, Grep | Yes |

---

## Directory Structure

```
harness/
├── package.json              # Dependencies
├── tsconfig.json
├── .env                      # API keys, URLs, credentials, model config
├── .gitignore                # Ignore state/, node_modules/, .env
│
├── src/
│   ├── index.ts              # Orchestrator — the main loop
│   ├── config.ts             # Loads .env, exports typed config object
│   ├── state.ts              # Read/write progress, issues, fix specs
│   ├── manifest.ts           # Page/route inventory to evaluate
│   │
│   ├── agents/
│   │   ├── evaluator.ts      # Spawns evaluator agent via SDK query()
│   │   ├── planner.ts        # Spawns planner agent
│   │   └── fixer.ts          # Spawns fixer agent
│   │
│   └── playwright/
│       ├── login.ts          # Authenticates, saves session cookies
│       ├── evaluate-page.ts  # Loads page, screenshots, captures errors,
│       │                     #   builds dynamic interaction map
│       ├── compare-pages.ts  # Side-by-side local vs reference screenshots
│       └── interaction-map.js # Browser-injected JS that discovers all
│                              #   clickable/draggable/submittable elements
│
├── prompts/                  # Agent system prompts (tune these separately)
│   ├── evaluator.md
│   ├── planner.md
│   └── fixer.md
│
└── state/                    # Runtime state (gitignored)
    ├── progress.json         # Overall harness progress
    ├── auth/                 # Saved browser sessions (cookies)
    ├── screenshots/          # Page screenshots + evaluation JSONs
    ├── issues/               # Issue files written by evaluator
    └── fixes/                # Fix specifications written by planner
```

---

## Key Components Explained

### 1. Orchestrator (`src/index.ts`)

The main loop. Supports:
- **Phase selection**: `--phase evaluate|plan|fix` or `all`
- **Scope control**: `--priority 1|2|3`, `--page single-page-id`
- **Resume**: `--resume` picks up from last saved state

```
for each page in manifest (filtered by priority):
    if not yet evaluated:
        run evaluator agent
        save state

load all issues
run planner agent (groups issues into fix specs)
save state

for each fix spec:
    run fixer agent
    save state

re-evaluate all pages (verify fixes)
```

State is saved after every step. If the process crashes, `--resume` continues where it left off.

### 2. Page Manifest (`src/manifest.ts`)

A typed list of every page/route to evaluate:

```typescript
interface PageEntry {
  id: string;        // Unique key, e.g. "tickets-kanban"
  path: string;      // URL path, e.g. "/tickets/showKanban"
  name: string;      // Human label
  priority: 1 | 2 | 3;
  needsContext?: boolean;  // Needs a specific entity to exist
}
```

Build this by scanning your app's route definitions. Priority lets you test the most important pages first.

### 3. Playwright Scripts (`src/playwright/`)

Plain TypeScript (no AI) — fast, deterministic browser automation that agents invoke via Bash.

**`login.ts`** — Authenticates and saves cookies to `state/auth/`. Supports multiple targets (local dev, production reference). Detects failed login.

**`evaluate-page.ts`** — For a single page:
1. Navigates with saved auth
2. Waits for async content to settle (HTMX, lazy loading)
3. Captures console errors, network errors, HTMX fragment issues
4. Collects DOM metrics (element counts, forms, tables, etc.)
5. Builds a **dynamic interaction map** (see below)
6. Takes a full-page screenshot
7. Outputs structured JSON

**`compare-pages.ts`** — Takes screenshots of the same page on both local and reference. Detects expired auth sessions and re-authenticates. Collects structural metrics for comparison.

**`interaction-map.js`** — A plain JS file injected into the browser via `page.evaluate()`. Discovers every interactive element on the page dynamically:

| Type | What it finds |
|------|--------------|
| `modal-trigger` | `[data-toggle="modal"]`, `.nyroModal`, etc. |
| `dropdown` | `.dropdown-toggle`, `[data-toggle="dropdown"]` |
| `htmx-action` | `[hx-get]`, `[hx-post]` with click triggers |
| `form` | `<form>` elements |
| `sortable-table` | DataTables, `.table-sortable` |
| `drag-drop` | `[draggable]`, `.sortable`, `.kanban-lane`, `.grid-stack` |
| `tab` | `[role="tab"]`, `.nav-tabs a` |
| `accordion` | `[data-toggle="collapse"]` |
| `link-with-handler` | `[onclick]` elements |
| `button` | Standalone buttons |

Each entry includes a CSS selector, label, type, and bounding box. The evaluator AI agent reads this map and decides which interactions to test — no hardcoded selectors needed.

Third-party component internals (rich text editor buttons, calendar controls, etc.) are filtered out to keep the map focused on your app's own UI.

### 4. Agent Modules (`src/agents/`)

Each wraps the Agent SDK `query()` call with the right configuration:

```typescript
import { query } from "@anthropic-ai/claude-agent-sdk";

for await (const message of query({
  prompt: "...",          // Task-specific instructions
  options: {
    systemPrompt: {       // Use Claude Code defaults + your custom prompt
      type: "preset",
      preset: "claude_code",
      append: agentPrompt,
    },
    cwd: projectRoot,
    model: "claude-opus-4-6",  // or sonnet for cheaper runs
    maxTurns: 30,
    maxBudgetUsd: 2.00,        // Cost cap per agent invocation
    tools: ["Read", "Bash", "Glob", "Write"],
    allowedTools: ["Read", "Bash", "Glob", "Write"],
    mcpServers: {               // Give evaluator browser access
      playwright: {
        command: "npx",
        args: ["@anthropic-ai/mcp-playwright@latest"],
      },
    },
    permissionMode: "acceptEdits",
  },
})) {
  // Handle messages: assistant turns, results, errors
  if (message.type === "assistant") {
    console.log(`Turn ${++turns}: working...`);
  }
  if (message.type === "result" && message.subtype === "success") {
    cost = message.total_cost_usd;
  }
}
```

Key SDK options:
- `tools` / `allowedTools` — Restrict what each agent can do
- `mcpServers` — Give agents access to MCP tools (Playwright, databases, etc.)
- `maxTurns` — Prevent runaway agents
- `maxBudgetUsd` — Cost cap per invocation
- `permissionMode: "acceptEdits"` — Auto-approve file edits (the fixer needs this)
- `model` — Use opus for quality, sonnet for speed/cost

### 5. Agent Prompts (`prompts/`)

Separate markdown files. This is where you tune agent behavior. Key principles:

**Evaluator** — Tune for skepticism. Out of the box, Claude approves mediocre work. You need explicit criteria:
- "Every visual difference from the reference is a bug"
- "A page that works but looks different is BROKEN"
- "Test at most 5-8 interactions per page" (prevents exhaustive clicking)
- List specific things to compare: layout, spacing, active states, typography, modals

**Planner** — Tune for root-cause grouping. Many individual issues share a single fix. The planner should:
- Read source code to verify assumptions
- Group by root cause, not by symptom
- Prioritize: critical first

**Fixer** — Tune for minimal changes. Include your project's coding conventions:
- "Make minimal, targeted changes"
- "Follow the existing patterns in AGENTS.md"
- Specific fix patterns for common issues (e.g., "change `display()` to `displayPartial()`")

### 6. State Management (`src/state.ts`)

File-based state enables resume-on-interrupt and agent-to-agent communication:

```typescript
interface HarnessState {
  startedAt: string;
  lastUpdated: string;
  currentPhase: "evaluate" | "plan" | "fix" | "verify" | "complete";
  evaluated: string[];       // Page IDs done
  issueCount: number;
  fixesPlanned: string[];    // Fix spec IDs
  fixesApplied: string[];
  fixesFailed: string[];
  totalCostUsd: number;
}
```

Issues and fix specs are individual JSON files in `state/issues/` and `state/fixes/`. This lets agents write them independently without file conflicts.

---

## Adapting to a New Project

### Step 1: Fork the harness directory

Copy the `harness/` directory into your project. Update `package.json` name.

### Step 2: Configure `.env`

```env
ANTHROPIC_API_KEY=sk-ant-...

LOCAL_URL=https://your-local-dev.test
LOCAL_USER=admin@example.com
LOCAL_PASS=password

REFERENCE_URL=https://production.example.com
REFERENCE_USER=admin@example.com
REFERENCE_PASS=password

MODEL=claude-sonnet-4-6          # sonnet for speed, opus for quality
MAX_TURNS_EVALUATOR=30
MAX_TURNS_PLANNER=20
MAX_TURNS_FIXER=50
MAX_BUDGET_PER_FIX=2.00
```

### Step 3: Update the page manifest

Replace `manifest.ts` with your app's routes. Scan your router config, controller files, or sitemap to build the list.

### Step 4: Update `login.ts`

Adapt the login flow to your app's auth form: field names, submit button selector, post-login URL check.

### Step 5: Update the interaction map filter

In `interaction-map.js`, update the third-party component filter to exclude your app's specific libraries (e.g., CKEditor, Monaco, FullCalendar, etc.).

### Step 6: Customize agent prompts

This is the most important step. In `prompts/`:

- **evaluator.md**: Describe what "correct" looks like for your app. What are the known patterns of breakage? What should the evaluator specifically look for?
- **planner.md**: Include your app's architecture context. How are templates rendered? What are common root causes? Where do files live?
- **fixer.md**: Include your coding conventions, the project's AGENTS.md rules, common fix patterns.

### Step 7: Run it

```bash
cd harness
npm install
npx tsx src/index.ts --priority 1        # Start with most important pages
npx tsx src/index.ts --resume            # Continue after interruption
npx tsx src/index.ts --page login-page   # Test a single page
```

---

## Cost Management

Typical costs per run (varies with page count and issue complexity):

| Phase | Model | Cost per page |
|-------|-------|--------------|
| Evaluate | opus | $0.50–2.00 |
| Evaluate | sonnet | $0.10–0.50 |
| Plan | opus | $1.00–3.00 total |
| Fix | opus | $0.50–2.00 per fix |

Tips:
- Use `--priority 1` to evaluate only critical pages first
- Use `--phase evaluate` to find issues without fixing (cheaper)
- Set `MAX_BUDGET_PER_FIX` to cap individual fix costs
- Use sonnet for evaluation, opus for fixing (configure per-agent in the agent modules)

---

## Lessons Learned

1. **The evaluator prompt needs the most tuning.** Expect several iterations before it catches the right things at the right severity. Read the issue files it produces and adjust.

2. **Visual comparison is hard to automate.** The Playwright scripts catch structural issues (HTMX errors, console errors, duplicate DOM), but visual regressions require the AI to actually look at screenshots. Make the evaluator prompt very explicit about what to compare.

3. **Filter the interaction map aggressively.** Without filtering, a page with a rich text editor produces 20+ button entries that waste evaluator turns. Filter third-party component internals.

4. **Reference auth expires.** The compare script must detect this and re-authenticate. Check for login page redirects, not just HTTP 200.

5. **File-based state is key.** It enables resume, parallel agents, and debugging. You can inspect `state/issues/` at any point to see what was found.

6. **Context windows are the bottleneck.** Long pages with many interactions can exhaust the evaluator's turn limit. Cap interactions per page (5-8) and filter low-value elements.

7. **Auto-commit per fix works well.** Each fix gets its own commit, making it easy to `git revert` individual changes that made things worse.
