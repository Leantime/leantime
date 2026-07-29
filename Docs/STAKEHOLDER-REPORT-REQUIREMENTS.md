# Stakeholder Report — Requirements & Design Guide

**Surface:** the `Report` view of the Logic Model page — `Logic Model // Report ▾`
**Scope levels:** strategy and program only (project keeps Marcel's existing `/reports/project`)
**Companion mockup:** `board-report-swipe.html` (pages p1–p4)
**Status:** design locked; data contract verified against `origin/feature/report-screens` (§8 updated with the drifts caught). Ready for prompt companion.

---

## 1. Purpose & what this is

The stakeholder report is **the Logic Model canvas, read out** — a board-facing view of the same chain a team authors on the Logic Model Board. It is not a new document type and not a separate report builder. It lives as a **view toggle** on the existing Logic Model page: `Board` (author) ↔ `Report` (read out), same page, same chrome, same data.

The core idea: a board should be able to see not just *what the plan is* (the canvas) but *how it's going* (progress, completed work, health) laid over that plan — without the team maintaining a second artifact. The report is a **live projection**, never a snapshot (see §6.1 — this is the single most important build constraint).

It answers three questions a board actually asks, kept as three distinct health lenses (§4):
1. **Are we doing the work?** (execution)
2. **Is it producing the outcomes?** (outcome)
3. **Is the underlying theory sound?** (theory health) — the lens boards usually lack.

---

## 2. Structure — a 4-page swipeable deck

One report, four pages, navigated by a horizontal tab bar on the background (the To-Dos `Kanban · Table · List` convention) plus swipe / arrow-key / arrow-button conveniences. The deck sizes to the active page (no dead space on short pages). Persistent report header across all four.

1. **Overview** — the board-level read: KPI band, "peak this period" hero, needs-attention, Theory-of-Change narrative, theory-health strip.
2. **Logic Model** — the 5-stage chain read out (Resources → Activities → Outputs → Outcomes → Impact), canvas-context cards with fold-out "this period" read-outs, connection-health badges.
3. **Resources & Coverage** — resource summary reads (people / budget / dependencies) from `ResourcesGateway`, and the coverage matrix (is each focus area resourced *and* tracked?).
4. **Programs** — program rows with RAG, and the status narrative.

Tabs are a **screen affordance**; print expands all four into one flowing snapshot (§7).

**Hard layout constraint:** fits a 12–13" laptop with **no horizontal scroll.** Root cause of prior overflow was CSS grid `1fr` (= `minmax(auto,1fr)`, won't shrink below content). Fix is `minmax(0,1fr)` on every grid track + `min-width:0` on flex/grid children. This is non-negotiable and already solved in the mockup.

---

## 3. The persistent report header

Across all pages:
- **Title + provenance** left: report subject, `Strategy report · Q2 2026 · last closed period · updated {date}`.
- **Status verdict** top-right — a **stated verdict with quiet provenance**, not a tappable pill. "On track" with a colored dot, and beneath it the source line: `from metrics · 4/5 goals on track` when computed, or `set by GF · overrides metrics` when an owner has overridden. This is the **computed-by-default, owner-can-override** model made visible (§5). A board must never wonder whether the verdict is the arithmetic talking or a human.
- **Period + Export** actions.

---

## 4. The three health lenses (keep distinct)

The report deliberately separates three signals that boards often collapse:

- **Execution health** — from `ReportEngine` `needsAttention` / `stats`. Overdue milestones, stalled work. Rendered as the needs-attention block (p1) and RAG on program rows (p4).
- **Outcome health** — goal RAG from the goal rollup. Are the tracked goals on track? Rendered in the KPI band and the Outcomes column read-outs (p2).
- **Theory health** — from `zp_logicmodel_health`. Is each *connection* in the chain sound, and are its assumptions evidenced? This is the lens boards lack. Rendered as the theory-health strip (p1) and connection badges between stages (p2). A fragile Outputs→Outcomes link with an unproven, un-evidenced assumption is a distinct risk from "behind schedule" — surface it as such.

---

## 5. Page-by-page rendering

### Page 1 — Overview

- **KPI band** — the anchor scale. 4 big-number tiles (completed w/ delta, goals on track, milestones overdue, headline outcome metric). Big numbers read in one glance.
- **"Peak this period" hero** — a recommend-and-override slot. The system nominates the period's most significant closure (fires on **structural closure** OR a **completed milestone carrying `outcomeImpact`**); the owner can pin/override. Shows the achievement, its linked goal, and date. Labeled "Recommended · change" so the owner knows it's proposable.
- **Needs-attention** — the execution lens; the at-risk callout.
- **Theory of Change narrative** — the chain rendered as a sentence, stage-colored: *By investing in {inputs}, delivering {activities}, we produce {outputs} — toward {outcomes}, in service of {impact}.*
- **Theory-health strip** — the fragile-connection warning drawn from `zp_logicmodel_health`.

### Page 2 — Logic Model read-out

The 5-stage board (same stages, colors, and order as the canvas):

| Stage | box | color |
|---|---|---|
| Resources | `lm_inputs` | `#4A85B5` blue |
| Activities | `lm_activities` | `#3E937A` teal |
| Outputs | `lm_outputs` | `#C09035` amber |
| Outcomes | `lm_outcomes` | `#8E6AAD` purple |
| Impact | `lm_impact` | `#2D7D5E` green |

**Cards follow the task-card standard** (so it's unmistakably a canvas item, read out):
- Title left; **status pill + owner avatar top-right** (`Validated` / `In Progress` / `Draft`, plus a `High` risk flag where applicable).
- **Assumption / hypothesis line** beneath (the canvas item's `assumptions` column — the field labeled *Evidence* in the Logic Model UI; `hp-text` is not a real column).
- **Linked projects** ("Linked to 2 projects").
- A dashed divider → **"This period"** → the read-out layer: the count phrased as a count ("180 of 300 sent · 112 attended"), a **delta** (`▲ +1.2` — **v2-gated on `zp_goal_history`**; absent in v1, card still complete), and the **attributed close** (who closed it, when, evidence files — from the completed milestone's `outcomeImpact`).

**Targeted density:** Outputs and Outcomes carry the fold-out read-out (default folded behind a "this period" toggle, so the board opens compact). Resources / Activities / Impact stay lean — canvas-context header + a status line only; there's nothing measured there to read out.

**Connection-health badges** sit between stages (ok / warning / risk = strong / needs-work / gap), from `zp_logicmodel_health.health_status`; amber warns a fragile link.

Board-page controls follow Leantime convention: filter top-left in-panel, sort / `⋮` top-right.

### Page 3 — Resources & Coverage

- **Resource summary** — three reads from `ResourcesGateway`: People (FTE + capacity utilization), Budget (spent vs. plan, at-risk), Dependencies (confirmed vs. pending). This is the summarized companion to the PgmPro Resource Allocation tab; full detail lives there. (See §11 for the shared contract.)
    > **Not yet wired** in `origin/feature/report-screens` — `StrategyReport` has zero `ResourcesGateway` references today. This section is new scope for the report build (see §10.d).
- **Coverage matrix** — the current code (`StrategyReport::buildCoverageMatrix`) returns `{stages, columns, cells, unalignedColumns}`: a boolean matrix of LM-item → program/project linkage, with a separate list of programs/projects that have no LM-item home (**off-strategy work**). To reach the resource-backed **covered ● / thin ◐ / gap ○** verdict this page targets, `buildCoverageMatrix` needs a second pass that overlays `ResourcesGateway` per cell:
    - **covered** — LM item linked to a program AND the program has resources authored for it (people + budget)
    - **thin** — linked but resources incomplete (only one of people/budget, or a stub-only allocation)
    - **gap** — linked, expected to be resourced, none authored
    - **empty cell** — no LM linkage; honestly ambiguous → offer "load from Logic Model Inputs" rather than asserting a gap
    - **off-strategy work** — programs in `unalignedColumns` render as a distinct callout beneath the matrix (they're a fourth signal, not a coverage-cell state — see §10.a resolution)

### Page 4 — Programs & Narrative

- **Program rows** — each child program with its RAG status and a count of completed work.
- **Status narrative** — the portfolio-level and per-program status updates (from `statusUpdates`).
- **"Also this period"** — secondary closures not chosen as the hero, with a link to attach them to an outcome.

---

## 6. Build constraints (normative)

1. **Live projection, never a snapshot.** Every page reads the *same* canvas / goal / health tables the Board writes, resolved at report time. Do not copy, freeze, or cache the canvas into a report record. Edit a card on the Board → it changes here. A goal advances → the bar moves here. There is one dataset, two views. This is the defining property of the feature; a snapshotted report is a wrong report.
2. **Strategy and program levels only.** Do not build a third project-level report; project uses the existing `/reports/project`. `getStrategyReport` (strategy) and the program equivalent are the two entry points.
3. **Hierarchy hazard.** `zp_projects.parent` is a self-FK, but **strategies have `parent = NULL` while top-level programs/projects have `parent = 0`.** Resolve the tree with that in mind; don't assume `NULL` and `0` are interchangeable.
4. **Delta is v2-gated.** The `▲ +1.2 vs Q1` deltas need `zp_goal_history` snapshots to exist. In v1, `zp_goal_history` is **write-only** (snapshots accumulate but aren't read back); deltas render only once there's a quarter of history. Design must be complete *without* the delta — it's additive, never load-bearing.
5. **Status is computed-default, owner-override** (§3). Render provenance honestly; never show "On track" without saying whether it's from metrics or a person.
6. **The hero is recommend-and-override.** The system proposes, the owner disposes. Never auto-pin without exposing the override; never leave it blank if a qualifying closure exists.
7. **`zp_logicmodel_health` is a plugin-owned table.** It's defined and populated by StrategyPro. Core `Reports\Services\ReportEngine` must **not** read it directly — that would let core know about a plugin's schema. Health data reaches the report through `StrategyReport::getStrategyReport()` (plugin-side), which calls the plugin-side `LogicModel::getHealthBadges()`. Same boundary discipline as `ResourcesGateway`.

---

## 7. Print parity

`@media print`: expand the 4-page deck into one flowing snapshot; hide the tab bar, arrows, view toggle, and fold toggles (print shows read-outs expanded); `page-break-inside: avoid` on cards and stage columns. Tabs/swipe are screen-only.

---

## 8. Data contract (from `ReportEngine` / `StrategyReport`)

> Verified against `origin/feature/report-screens` (as of the read-through that produced this doc). Return shapes below are the actual code, not the earlier design shorthand.

### `Reports\Services\ReportEngine::buildReport(int[] $projectIds, ReportPeriod $period): array`

```php
[
  'period'         => ReportPeriod,        // echoed for template convenience
  'projectIds'     => int[],               // filtered to authorized ids
  'summaries'      => object[],            // per-project summary rows
  'milestones'     => [
    'completed' => [...], 'inProgress' => [...], 'overdue' => [...],
    'upcoming' => [...],  'allDone' => [...],    'slippage' => [...],
  ],
  'goals'          => ['goals' => object[], 'byProject' => [...], 'counts' => ['ontrack' => int, ...]],
  'statusUpdates'  => [...],
  'effort'         => ['total' => float, ...],
  'deltas'         => ['completedPrior', 'completedDelta', 'hoursPrior', 'hoursDelta', 'priorPeriodLabel'],
  'needsAttention' => [...],               // execution-lens block
  'stats'          => [
    'completed'    => int,   // → KPI tile 1
    'inFlight'     => int,
    'overdue'      => int,   // → KPI tile 3
    'upcoming'     => int,
    'goalsOnTrack' => int,   // → KPI tile 2 (numerator)
    'goalsTotal'   => int,   // → KPI tile 2 (denominator)
    'hoursLogged'  => float,
  ],
]
```

### `StrategyPro\Services\StrategyReport::getStrategyReport(int $strategyId, ReportPeriod $period): array`

**Critical:** `buildReport()`'s output is **nested at `.report`**, NOT spread top-level. Templates read `$strategyReport['report']['stats']`, `$strategyReport['report']['milestones']`, etc.

```php
[
  'report'            => <buildReport() output above>,   // nested, not spread
  'strategyGoals'     => ['goals' => object[], 'byProject' => [...], 'counts' => [...]],
                                                          // each goal->childGoalCount populated when setting==='linkAndReport'
  'programRows'       => object[],                        // one row per program + direct project
  'programSummaries'  => object[],                        // program-only summaries (for coverage/rollups)
  'programUpdates'    => [...],                           // → "Also this period" (p4)
  'logicModel'        => null | [                         // null when no LM canvas authored
    'canvasId'       => int,
    'narrative'      => ['text' => string, 'hasItems' => bool, 'stageTexts' => array],
    'stageProgress'  => ['lm_inputs' => ['percent', 'validated', 'total'], ...],
    'healthBadges'   => [                                 // keyed 1..4 (connectors between stages)
      1 => ['from_stage', 'connector_label', 'health_status', 'assumption_text', 'risk_level', 'evidence_notes', 'has_data'],
      ...
    ],
    'coverageMatrix' => [                                 // see §5.3 for the resource-backed upgrade planned
      'stages'           => ['lm_inputs' => ['title', 'icon', 'items'], ...],
      'columns'          => [projectId => ['id', 'name'], ...],   // programs + direct projects
      'cells'            => [itemId => [columnId => true, ...], ...],  // boolean linkage matrix
      'unalignedColumns' => int[],                        // programs/projects with no LM linkage = OFF-STRATEGY WORK
    ],
  ],
]
```

### Program-level entry point

`ReportEngine::buildReport()` composed directly with `[$programId, ...descendants]`. No `getProgramReport()` wrapper exists today; `PgmPro\Controllers\Report` and `PgmPro\Hxcontrollers\Report` call the engine directly with a descendant-id set. Adding a `PgmPro\Services\ProgramReport` wrapper is optional and only worth it if the program-level report needs plugin-specific enrichment the strategy one does.

### Source tables

- **Logic Model canvas** = `zp_canvas` with **`type = 'logicmodel'`** (not `'logicmodelcanvas'`). Verified: `Logicmodelcanvas::CANVAS_NAME = 'logicmodel'`.
- **Canvas items** = `zp_canvas_items`; stage = `box` ∈ `{lm_inputs, lm_activities, lm_outputs, lm_outcomes, lm_impact}`.
- **Canvas item fields** — main text = `description`; **assumption / hypothesis** = `assumptions` column (labeled "Evidence" in the LM UI); validation status = `status` (`status_draft`, `status_review`, `status_valid`, `status_hold`, `status_invalid`).
- **Theory / connection health** = `zp_logicmodel_health` (**plugin-owned**, StrategyPro) — `from_stage` (1–4), `health_status` (`ok|warning|risk`), `assumption_text`, `risk_level`, `evidence_notes`. See §6.7 — must be read via plugin service, never directly from core `ReportEngine`.
- **Focus areas** = `zp_canvas` `type = 'goalcanvas'`; **goals** = `zp_canvas_items` `box = 'goal'`; cross-project rollup via `$goal->setting === 'linkAndReport'` + `Goalcanvas::getChildGoalsForReporting()` at `ReportEngine.php:231`.
- **Status / RAG** = `zp_comment` `module = 'project'`, `.status`.
- **Attributed close** = `zp_tickets.outcomeImpact` on completed milestones.
- **KPI snapshots** = `zp_goal_history` (v2 delta source; write-only in v1).

---

## 9. States

- **No logic model authored** — the report can't project a chain that doesn't exist. Invite to the Board ("build your logic model to see it read out here") rather than an empty deck.
- **Stage with no items** — show the stage, empty, with its context (don't hide it; the chain shape is information).
- **Goal with no history** — render current value, omit the delta (per §6.4).
- **Coverage cell empty** — ambiguous by honest design; offer "load from Logic Model Inputs," don't assert a gap.
- **Status not overridden** — provenance reads "from metrics," not blank.

---

## 10. Open items

- **a. Coverage rollup scope — RESOLVED.** Strategy focus areas only. Off-strategy work is a *separate signal*, not a coverage-cell state — it surfaces via the `unalignedColumns` list (`buildCoverageMatrix` already returns it) rendered as an "Off-strategy work" callout beneath the matrix. Same principle as §4's three-lens split: distinct signals stay distinct. The code already produces `unalignedColumns`, so this is a rendering decision, not a query change.
- **b. Program-level report entry point — RESOLVED.** No wrapper service. Program reports call `ReportEngine::buildReport([$programId, ...descendants], $period)` directly. `PgmPro/Controllers/Report.php` and `PgmPro/Hxcontrollers/Report.php` on `feature/report-screens` already follow this pattern. Add a `PgmPro\Services\ProgramReport` wrapper later only if plugin-specific enrichment (like the LM-canvas overlay `StrategyReport` does) becomes necessary at program level.
- **c. `ReportEngine` return-key verification — RESOLVED.** Done against `origin/feature/report-screens`; §8 above reflects the actual code, not the earlier design shorthand. Drifts caught: nested `.report` key, missing `programSummaries`/`programUpdates`, `logicModel` nullability, canvas type `'logicmodel'` (not `'logicmodelcanvas'`), field `assumptions` (not `hp-text`).
- **d. Resources & Coverage page (p3) — scope question, still open.** Two live constraints: (1) `ResourcesGateway` has zero references in `feature/report-screens` today — the p3 resource summary is new integration scope. (2) `buildCoverageMatrix` returns a boolean linkage matrix; the resource-backed **covered/thin/gap** verdict this page targets requires overlaying `getForProjects()` per cell. Decision: **does the report shell ship in v1 with p3 as a placeholder** ("Resource coverage view coming soon"), **or block on p3 landing complete** (gateway wiring + coverage matrix upgrade before shell merges)? Blocking is cleaner UX (no "coming soon" tab in a shipping product); shell-first is faster to get eyes on p1/p2/p4. Product call.

---

## 11. ResourcesGateway seam (consumer side)

> This is the **read-out end** of the same contract the Resource Allocation tab authors. The section below is the frozen contract description, identical in intent to the resource-tab doc's seam section — the report *consumes* what the tab *produces*.

The report's Resources section (page 3) reads through `ResourcesGateway` (`getForProjects` / `getForProgram`), which exposes the summarized form of the four resource blocks: **program context, projects, people totals, budget totals, dependencies summary.**

Invariants the report relies on:
- The gateway returns the same four-block shape for every program, always — even when a block is empty (empty-but-typed, never a missing key).
- All aggregates are computed once, upstream of both consumers, so the report and the authoring tab render identical numbers (capacity utilization, per-project rollups, budget spent %, at-risk at `≥90%`, dependency confirmed/tentative). The report **does not recompute**; it reads.
- Project-identity color is carried in the payload, consistent wherever a project appears.
- Distinct states are represented in data, not inferred: authored-but-unfilled (stub), empty, untrackable. The report renders these honestly (this is what lets the coverage matrix distinguish "gap" from "unauthored").
- The report never introduces resource facts the tab could not author.

The report is a pure consumer here. If it needs a resource figure the gateway doesn't expose, that's a gateway change shipped as a pair (gateway + tab + report), never a report-side computation.

---

## 12. Non-negotiables (summary)

- **Live projection, never snapshot** (§6.1) — the defining property.
- Strategy + program only; project uses existing `/reports/project`.
- Fits a laptop, no horizontal scroll (`minmax(0,1fr)` everywhere).
- Three health lenses kept distinct (execution / outcome / theory).
- Status = computed-default + owner-override, provenance shown.
- Cards = task-card standard; Outputs/Outcomes fold-out, others lean.
- Coverage verdict is resource-backed; empty = honestly ambiguous.
- Resources read from `ResourcesGateway`, never recomputed.
- Delta v2-gated; design complete without it.
- Print expands all pages; screen affordances hidden.
- `zp_logicmodel_health` reached via plugin service; core `ReportEngine` never reads a plugin table directly.
- Template access uses the actual return shape: `$strategyReport['report']['stats']` (nested), not `$strategyReport['stats']`.
