# 16 · Resource Allocation Tab — Requirements

**Surface:** `/pgmPro/resourceAllocation` (PgmPro plugin, program-scope)
**Status:** V1 structure landed (`feature/resources-revival`, PR #72). This doc is the durable spec the build conforms to.
**Companion mockups:** `resource-allocation-v1.html`, `resource-allocation-v2.html`, `resource-states.html`

---

## 1. Purpose & scope

The Resource Allocation tab is the **authoring surface** for a program's resources — the people allocated to it, the money budgeted against it, and the external dependencies it relies on. It is the source end of a contract whose read-out end is the stakeholder report's Resources section. Author here; it shows compactly in the board packet.

- **Scope object:** a single program — a `zp_projects` row where `type = 'program'`. The tab aggregates resources across that program's child projects.
- **Two modes, one layout:** `?version=v1` (planning) and `?version=v2` (plan-vs-actual). V2 is the same layout plus an actual-hours overlay drawn from timesheets. V2 **layers onto** V1; it does not fork it.
- **Print parity is first-class.** Board packets sometimes include a resources snapshot, so print is a real output, not an afterthought (see §7).

---

## 2. Structure (fixed)

Three authoring sections, **always in this order, always present**: **People → Budget → Dependencies.** The structure is fixed by contract — it is not user-configurable. (A future per-section *visibility* toggle may hide a section at the view layer without changing this structure or the return shape; that is out of scope here and must never remove a block from the summary object.)

For screen presentation the mockup paginates these into four tabs mirroring the stakeholder report — **Overview · People · Budget · Dependencies** — where Overview carries the program context, the summary strip, and the project color legend/rollup. Tabs are a **screen affordance only**; they must not leak into the print path (§7) or the data model.

---

## 3. The data contract

Every screen consumes the return of `ResourceStructureService::getProgramResourceSummary(int $programProjectId, array $childProjectIds)`. **Design and build against this shape; ignore any prior template layout.**

### 3.1 Top-level return

```
[
  'canvasId'     => int,
  'people'       => people[],        // hydrated, shape below
  'budget'       => budget[],        // hydrated, shape below
  'dependencies' => canvas_item[],   // RAW canvas_item rows (not hydrated)
  'projects'     => int[],           // child project ids
]
```

### 3.2 `people[]`

```
[
  'id'                 => int,             // canvas_items.id
  'name'               => string,
  'role'               => string,
  'userId'             => int,             // 0 = not linked to a real user
  'capacity'           => int,             // hours / week
  'allocations'        => array<int,float>,// { projectId => hoursPerWeek }
  'status'             => string,          // 'active' | 'stub' | 'archived'
  'sourceCanvasItemId' => ?int,            // provenance → originating LM Input canvas item
]
```

### 3.3 `budget[]`

```
[
  'id'        => int,
  'label'     => string,
  'projectId' => int,      // 0 = program-level line
  'budgeted'  => float,
  'spent'     => float,
  'color'     => string,   // '#RRGGBB', default '#9CA3AF'
  'status'    => string,   // 'active' | 'stub' | 'archived'
]
```

### 3.4 `dependencies[]` (raw canvas_item)

```
[
  'id'          => int,
  'description' => string,  // partner-name fallback
  'data'        => string,  // JSON: { partnerName, type, confirmed }
  'status'      => string,
  // ...other canvas_item columns
]
```
Reshaped in the controller to `{ id, partnerName, type, confirmed:bool }`.

### 3.5 Controller-derived view data

The controller computes **all aggregates** so Blade stays presentation-only. These are the template's vocabulary:

- `$projectRows[]` — `{ id, name, color, hours, actual, delta, actualPct, people, pctOfCapacity, shareOfAllocated }`
- `$teamStats` — `{ capacity, allocated, available, capacityUsedPct, availablePct, peopleCount, fullPeople, actualTotal, actualDelta, peopleOverActual }`
- `$budgetStats` — `{ total, spent, spentPct, atRiskCount, linesCount, stubCount }`
- `$depStats` — `{ total, confirmed, tentative }`
- `$budgetLines` — real lines (label non-empty **and** non-zero)
- `$stubBudgetLines` — stubs (empty label **or** all zeros), split out for the "complete me" affordance, **excluded from `$budgetStats` totals**
- `$hoursByProject`, plus V2: `$actuals`, `$actualsByUser`, `$actualsByProject`
- `$stubStatus` — `{ people:int, budget:int, dependencies:int, isEmpty:bool }` — per-box counts for the setup badge

---

## 4. The four data blocks — rendering

### Block 1 — Program context + summary strip (Overview)

Program name (`zp_projects.name`), optional dates, child-project count. Then a **5-read summary strip** mapped to `$teamStats` and `$budgetStats`: Team capacity (`capacityUsedPct`, segmented by project color), Allocated (`allocated`), Available (`available` / `availablePct`), People (`peopleCount` / `fullPeople` "at capacity"), Budget (`spent` of `total`, `atRiskCount`).

**At capacity** = a person `≥99%` allocated (this is what `fullPeople` counts). Do not gate on literal 100%.

### Block 2 — Child projects = the color legend (Overview)

Each child project carries a stable `color` (assigned by `Programs::getColoredProgramProjects()` cycling `PROJECT_COLORS`). **`$projectRows[].color` is the single source of truth for the project palette.** The same color means the same project across every people-bar segment, budget aggregate strip segment, and summary strip. This is the visual through-line.

Each project row shows: color, name, `people` assigned, `hours` planned/wk, `pctOfCapacity` mini-bar.

### Block 3 — People (capacity view)

One row per person: identity, weekly `capacity`, and a horizontal allocation bar whose colored segments (from `allocations`, colored by project) sum toward capacity, with any remainder as a light "available" tail. Right-side read: allocated/capacity, available hours, % used.

States (from `$teamStats` + per-person fields):
- **available** — has an available tail.
- **at capacity** — `≥99%` allocated, no tail (amber treatment).
- **over-allocated** — allocation exceeds capacity (design-supported even if absent from current seed).

Column totals ("landing on each project") sum `allocations` per project, colored by project.

### Block 4 — Budget (money view)

One row per line: `label`, assigned project (`projectId`, `0` = program-level), a `budgeted`→`spent` bar filled 0→100%, and % spent. **`≥90%` spent = at-risk (red); `≥100%` = over (red + weight).** Program strip = `spent` of `total`, `atRiskCount`, segments colored by project (a mini-legend).

**`budget.color` is independent of `$projectRows[].color`** (see gotcha §6.4). Render budget fills from `budget.color`; default `#9CA3AF` gray for unassigned lines.

### Block 5 — Dependencies

One row per external partner: `partnerName`, `type`, and a confirmed/tentative toggle. **Confirmed vs. tentative is the primary signal**; tentative reads as a risk (amber), confirmed as locked-in (green).

---

## 5. States (empty, stub, orphaned)

The old design hid empty sections. The new design surfaces them as **invite-to-author** affordances, and the summary strip must stay **honest** when nothing is authored. Mockup: `resource-states.html`.

### 5.1 Empty section — invite to author

When a section has no rows: icon, plain-language line, and two actions with **"Fill from Logic Model Inputs" leading** (the auto-creation seam), then the section's own "Add …". All three sections offer the fill action — `ResourceRegistrar::registerMappings()` declares `lm_inputs → {people, budget, dependency}` as `generates`, so all three are first-class seed targets. If nothing classifies to a box, the seeder returns "0 new … added" gracefully.

### 5.2 Honest summary strip — nothing authored

When `$teamStats` and `$budgetStats.total` are zero: muted em-dashes and honest labels ("no budget set", "no capacity set", "none added") — never zeros dressed as data.

### 5.3 Stub person — `status === 'stub'`

A person seeded from an LM Input: has a `name` but no linked user. Dashed "complete me" row treatment, a **"Seeded"** chip that links to the source via `sourceCanvasItemId`, and a primary CTA of **"Link user"** — because the one mandatory field to flip stub → active is `userId` (`capacity` defaults to 40 in `allocatePerson()`; `allocations` is optional). Excluded from `$teamStats` totals until completed.

### 5.4 Stub budget line — `status === 'stub'`

The `$stubBudgetLines` "Untitled / complete me" row: dashed, `--warn-tx` text, "Complete line" CTA. A legitimate authored-but-unfilled state, **never rendered as a real $0 line**, excluded from `$budgetStats`.

### 5.5 V2 — actuals can't be tracked

**Keyed on `userId === 0`, independent of `status`** (see gotcha §6.1 / §6.5). A person with no linked user has no timesheets, so actuals are always 0. In plan-vs-actual this must **not** render as a discrepancy ("fully under" in alarm-amber) — that misreads "cannot log" as "failed to log." Show a neutral, dashed "No linked user — actuals can't be tracked yet" in the actual bar, and "link a user to track" in place of the delta.

---

## 6. Build constraints (gotchas — normative)

These six are the traps a fresh implementer must not fall into. They are constraints, not suggestions.

1. **`userId === 0` is the actuals axis, not `status`.** Any person with `userId === 0` cannot have timesheet actuals, regardless of `status`. A `status === 'active'` person whose linked user was deleted is *also* `userId === 0`. Key the V2 "can't-track-actuals" treatment on `userId === 0` alone. Key the stub-*row* treatment on `status === 'stub'`. They usually co-occur; the orphaned-user case splits them, and both must be handled.
2. **Budget stubs are their own path.** Split `status === 'stub'` (or empty-label / all-zero) budget lines into `$stubBudgetLines`, render the "complete me" affordance, and exclude them from `$budgetStats` totals. Never let a stub read as a real $0 line.
3. **Cast `allocations` keys.** JSON-decode stringifies the keys; always `(int) $pid` before using a project id from `allocations`.
4. **`budget.color` is independent of the project palette.** `$projectRows[].color` is the project through-line; `budget.color` is its own field (default `#9CA3AF`). Render budget fills from `budget.color`, not by re-deriving from the assigned project.
5. **V2 fields are always present, never null.** In V1 mode the V2-only fields (`actual`, `delta`, `actualPct`, `actualTotal`, `actualDelta`, `peopleOverActual`, `actuals*`) are `0`/empty, not null. Templates may reference them unguarded — one template, no `@if version` forks.
6. **`getActualHours` period is hardcoded `this_week`.** No period UI yet; the service accepts `this_week | last_week | this_month`, but the controller pins `this_week`. Do not build period-switch UI against a control that doesn't exist.

---

## 7. Print parity

`@media print`: collapse the tab deck and **expand all four pages into one flowing snapshot**; hide the mode toggle, tab bar, arrow buttons, and add buttons; `page-break-inside: avoid` on rows. Tabs and swipe are screen affordances — the print path renders the fixed three-section structure in full.

---

## 8. Interaction surface (HTMX, in-place, no reload)

- Add person / budget line / dependency — inline "add stub" → click-to-edit.
- Edit allocation — click a segment on a person's bar to change that person × project's hours.
- Edit budget — click the amount to edit.
- Toggle confirmed — one-click on a dependency.
- Delete — hover-reveal, confirm dialog, hard delete.
- V1/V2 — segmented control, `?version=`-backed.

---

## 9. ResourcesGateway seam

> **This section is self-contained by design.** It describes the tab→report contract in pure terms, with no back-references, so it can be lifted verbatim into the stakeholder-report requirements (or a shared reference doc) when that work begins.

The Resource Allocation tab is the authoring end of a two-ended contract. The read-out end is the stakeholder report's Resources section. The two communicate only through `ResourcesGateway` (`getForProjects` / `getForProgram`), which exposes the summarized form of the four resource blocks: **program context, projects, people totals, budget totals, dependencies summary.**

Invariants the gateway guarantees to both ends:

- The gateway returns the same four-block shape for every program, always — even when a block is empty (it returns an empty-but-typed block, never a missing key).
- All aggregates are computed once, upstream of both consumers, so the authoring surface and the report render identical numbers (capacity utilization, per-project hour rollups, budget spent %, at-risk detection at `≥90%`, dependency confirmed/tentative counts). Neither consumer recomputes; both read.
- The project-identity color is carried in the payload (per-project `color`), so the through-line is consistent wherever a project appears, on either surface.
- Distinct states are represented in data, not inferred: authored-but-unfilled (stub), empty, and — for people — untrackable (`userId === 0`). A consumer renders these honestly rather than guessing from zeros.
- The tab authors what the report reads. Any capacity-utilization or budget-at-risk pattern the tab establishes has a smaller companion at report scale; the report never introduces resource facts the tab could not author.

Consumers must treat this shape as frozen. Changes to the shape are schema events shipped as a pair (gateway + both consumers), never a one-sided edit.

---

## 10. Non-negotiables (summary)

- Aggregates in the controller; Blade presentation-only.
- `.ra-scope` class-prefix isolates all styles.
- Fixed three-section structure; tabs are screen-only.
- Project palette from `$projectRows[].color`; budget color independent.
- Stub / empty / `userId === 0` states rendered honestly, keyed per §6.
- V2 layers onto V1 — one structure, `?version=`-switched.
- Print expands all sections; screen affordances hidden.
