# Stakeholder Report — Implementation Prompt

**Reference doc:** `STAKEHOLDER-REPORT-REQUIREMENTS.md` (the design guide — read it first; this prompt sequences the build against it).
**Mockup:** `board-report-swipe.html` (pages p1–p4) — the visual target. Match its structure, chrome, and the `minmax(0,1fr)` layout discipline.

---

## Role & goal

You're implementing the **stakeholder report** — the `Report` view of the Logic Model page (`Logic Model // Report ▾`) in Leantime. It's the Logic Model canvas **read out** for a board: the same chain the team authors on the Board, with progress, completed work, and health laid over it. Strategy and program scope only.

The defining property: **it is a live projection, never a snapshot.** Every render reads the same `zp_canvas` / goal / `zp_logicmodel_health` tables the Board writes, resolved at report time. There is no report record to freeze. If you find yourself copying canvas state into a saved report, stop — that's the one wrong turn.

---

## Step 0 — The data contract is already verified

§8 of the requirements doc reflects the actual code on `origin/feature/report-screens` (verified). Do not re-derive the shape; **read from §8 and bind to those exact keys.**

The five things you MUST know before writing render code (all in §8, restated for emphasis):

1. **`StrategyReport::getStrategyReport()` nests `buildReport()`'s output at `.report`.** Templates read `$strategyReport['report']['stats']`, `$strategyReport['report']['milestones']`, etc. — NOT top-level.
2. **`logicModel` can be `null`** (no LM canvas authored). Every render path must handle it — no field access without a null check.
3. **Canvas type is `'logicmodel'`, not `'logicmodelcanvas'`.** Any query that filters `zp_canvas.type` uses `'logicmodel'`.
4. **Assumption/hypothesis field is `assumptions`** (the canvas_items column, labeled *Evidence* in the LM UI). No `hp-text` column exists.
5. **Program-level report has no wrapper service.** Call `ReportEngine::buildReport([$programId, ...descendants], $period)` directly; do not invent a `getProgramReport()`.

Also: `zp_logicmodel_health` is plugin-owned (StrategyPro). If you find yourself reading it from core `Reports\*`, back up — go through the plugin's `LogicModel` service instead.

Only re-run verification if a §8 field access fails at runtime (drift since the doc was written).

---

## Build sequence

### 1. Shell & view toggle
Add `Report` as a view on the existing Logic Model page — a toggle beside `Board` (`Logic Model // Board ▾` ↔ `Logic Model // Report ▾`), same page, same chrome, **no new nav entry.** Board authors; Report reads out. Both read the same canvas.

### 2. The 4-page deck
Four pages — Overview, Logic Model, Resources & Coverage, Programs — in a horizontal tab bar **on the background** (the To-Dos `Kanban·Table·List` treatment), plus swipe / arrow-key / arrow-button navigation. Deck sizes to the active page. Persistent report header.

**Layout discipline (hard):** every grid track is `minmax(0,1fr)`, every flex/grid child gets `min-width:0`. Must fit a 12–13" laptop with **zero horizontal scroll.** The mockup already solves this — match it.

### 3. Persistent header + status verdict
Title + provenance line left. Top-right: the **status verdict** — a stated verdict with a provenance line, *not* a tappable pill. Computed by default (`from metrics · 4/5 goals on track`), owner can override (`set by GF · overrides metrics`). Render whichever applies; never show the verdict without its source.

### 4. Page 1 — Overview
KPI band (4 big-number tiles from `stats`), the **"peak this period" hero** (recommend-and-override — nominate from a structural closure OR a completed milestone carrying `outcomeImpact`; expose the override), needs-attention block (from `needsAttention`), Theory-of-Change narrative (stage-colored sentence from `logicModel.narrative`), theory-health strip (from `zp_logicmodel_health`).

### 5. Page 2 — Logic Model read-out
The 5-stage board (`lm_inputs → lm_impact`, colors per design §5). Cards = **task-card standard**: title left; status pill + owner avatar top-right; assumption line (`hp-text`); linked projects; dashed divider → "This period" fold-out with count, delta, attributed close.
- **Outputs and Outcomes** carry the fold-out read-out (default folded).
- **Resources / Activities / Impact** stay lean (context header + status line).
- **Connection-health badges** between stages from `zp_logicmodel_health.health_status` (ok/warning/risk).
- **Delta is v2-gated** — render only when `zp_goal_history` has snapshots; the card must be complete without it.

### 6. Page 3 — Resources & Coverage
Resource summary (people / budget / dependencies) read from **`ResourcesGateway`** (`getForProjects` / `getForProgram`) — **do not recompute; read the gateway's aggregates.** Coverage matrix: per focus area, a resource-backed verdict (covered/thin/gap) requiring *both* goals-tracked and resources-allocated. Empty cell = honestly ambiguous → "load from Logic Model Inputs," never an asserted gap.

### 7. Page 4 — Programs & Narrative
Program rows with RAG (`zp_comment` `module='project'` `.status`), status narrative (`statusUpdates`), "also this period" secondary closures.

### 8. Print parity
`@media print` expands all four pages into one flowing snapshot; hides tab bar, arrows, view toggle, fold toggles (read-outs shown expanded); `page-break-inside: avoid` on cards and stage columns.

---

## Constraints (do / never)

- **DO** resolve everything live from `zp_canvas` / goals / `zp_logicmodel_health` at render time. **NEVER** snapshot or cache canvas state into a report record.
- **DO** treat `zp_projects.parent`'s `NULL` (strategy) vs `0` (top-level program/project) as distinct when walking the tree.
- **DO** read resource figures from `ResourcesGateway`. **NEVER** recompute capacity/budget/dependency aggregates in the report — they're computed once, upstream, and the tab and report must show identical numbers.
- **DO** render the status verdict's provenance. **NEVER** show "On track" without saying whether it's from metrics or an owner.
- **DO** gate deltas on `zp_goal_history` existing. **NEVER** make the delta load-bearing — the card is complete without it.
- **DO** build strategy and program reports only. **NEVER** add a project-level report — project uses `/reports/project`.
- **DO** keep the three health lenses distinct (execution / outcome / theory). **NEVER** collapse "behind schedule" and "fragile theory" into one signal.

---

## Acceptance criteria

- Report renders at strategy and program scope, live from canvas/goal/health tables; editing the Board changes the report with no snapshot step.
- All four pages fit a 1280px-wide viewport with no horizontal scroll; deck sizes to the active page.
- Status verdict shows computed-vs-override provenance.
- Logic Model cards match the task-card standard; Outputs/Outcomes fold out; connection badges reflect `zp_logicmodel_health`.
- Resources section reads `ResourcesGateway` output verbatim (numbers match the PgmPro tab exactly).
- Coverage verdicts are resource-backed; empty cells offer the load affordance rather than asserting gaps.
- Print expands all pages into one snapshot with screen affordances hidden.
- No delta renders until `zp_goal_history` has data; cards are complete without it.

---

## Out of scope (this pass)

- Project-level report (exists as `/reports/project`).
- Writing/computing resource aggregates (the report is a `ResourcesGateway` consumer only).
- The `zp_goal_history` read path for deltas (v2 — snapshots are write-only for now).
- The Resources & Coverage page (p3) itself — scope depends on §10.d: either **shell ships with p3 as a placeholder** (build p1/p2/p4 now, land p3 in a follow-up that adds `ResourcesGateway` wiring + resource-backed coverage verdict), or **block on p3 landing complete**. Confirm with product before starting p3 code.
