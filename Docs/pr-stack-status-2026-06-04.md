# PR Stack Status — 2026-06-04

Working notes for Marcel's review queue. Six PRs in flight, all green where CI runs, two coordinated pairs.

## Open PRs

### Core (Leantime/leantime)

| PR | Title | Branch | Scope |
|---|---|---|---|
| [#3481](https://github.com/Leantime/leantime/pull/3481) | feat(stageflow): spotlight non-hovered stages on row hover | `feat/stageflow-spotlight-hover` | Pure CSS. Hover any stage → others fade to 40%, hovered card lifts with shadow. Logic Model and any other stageflow consumer. |
| [#3482](https://github.com/Leantime/leantime/pull/3482) | feat(libs): npm-manage html2canvas + jsPDF | `feat/logicmodel-export-libs` | Pivoted from "commit minified blobs" to "npm + Mix build". Lazy-loaded at runtime by export consumers. **Must merge with plugin #47.** |
| [#3484](https://github.com/Leantime/leantime/pull/3484) | fix(logicmodelcanvas): left-align status filter dropdown menu | `fix/logicmodel-status-filter-positioning` | Logic Model "All status" filter was rendering 134px to the left, behind the sidebar, getting clipped by `.primaryContent` overflow. Inline override on this template only — Blueprints/Canvas templates left alone (their `pull-right` placement makes the right-align rule correct). |
| [#3491](https://github.com/Leantime/leantime/pull/3491) | chore(logicmodelcanvas): drop unused snapshot mount div | `chore/remove-snapshot-mount-div` | Empty `<div id="snapshotListContainer">` in core template was the HTMX target for the snapshot feature being removed from the plugin. **Pairs with plugin #46.** |

### Plugins (Leantime/plugins, private)

| PR | Title | Branch | Scope |
|---|---|---|---|
| [#46](https://github.com/Leantime/plugins/pull/46) | fix(strategypro): wizard UX fixes from end-to-end walkthrough | `fix/wizard-ux-issues` | Large bundle: 16+ commits. See breakdown below. **Pairs with core #3491.** |
| [#47](https://github.com/Leantime/plugins/pull/47) | fix(strategypro): make Logic Model PNG/PDF export render correctly | `fix/logicmodel-export-png-wrap` | Three fixes: item-title wrap during export, color-mix → 8-digit hex for html2canvas, loader path update for the npm pivot. **Must merge with core #3482.** |

## Merge order

Two pairs must land together; the other three are independent.

**Coordinated pairs (atomic):**
- core #3482 ⇄ plugin #47 — both point at the new `/dist/js/` lib paths. Merging either alone breaks exports until the other lands.
- core #3491 ⇄ plugin #46 — the snapshot removal lives in #46; the empty mount div in #3491 has no consumer once snapshots are gone.

**Standalone:**
- core #3481 — spotlight is pure CSS, no dependency on anything else.
- core #3484 — local template override, no cross-PR impact.
- plugin #46 is also internally complete (no further plugin PR depends on it).

## What's in plugin #46 (the big one)

Sixteen commits, all coordinated around the StrategyPro Logic Model feature work. Grouped by concern:

**Bug fixes (visible to user):**
- Milestone entity-link pills routed to `/tickets/showTicket/` → 500 because milestones have their own modal. Now routes to `/tickets/editMilestone/`.
- Milestone classification: WorkGenerator was recording milestones as `entityBType='Ticket'`. Now `'Milestone'`. Legacy rows reclassified at render time.
- Status / relates label lookups in dashboard and showCanvas tpl files were crashing on empty values. Guarded with `isset()`.
- Health badge anchored inside the column header (was straddling the corner at `top:-4px`).
- PNG export item title wrap fix (also in #47).
- E2E pass on the sector-template HxController surfaced four edge cases: cancel was loading, invalid fixtureKey was silent, invalid canvasId was 500, view-vars assignment was duplicated. All fixed.

**Feature behaviour:**
- StrategyPro Logic Model features now gate to `type=strategy` projects only — narrative, health badges, wizard, entity pills disappear on regular project blueprints.
- Generated goals now have a real `title` (Goal: …) and a real `description` (Metric: …). Was previously `title=''` with the canvas bold text shoved into description.

**Removal:**
- Snapshot feature ripped out. Storage worked, consumption side (view, restore, compare) was never built — the feature was write-only. Schema cleaned up via `dropIfExists` in update path. (See [Architectural question](#open-architectural-question) below — the templates / sample-content system replaces some of what snapshots was reaching for.)

**Code review responses (Copilot inline comments on #46):**
- WorkGenerator `(int) $result` was coercing bool `true` to ticket #1. Replaced with explicit `match (true)` accepting array-with-id or numeric only.
- register.php entity-link renderer: nullsafe `?->` on `first()` lookups so orphaned `entity_relationship` rows don't raise PHP 8 warnings.
- Health-badge popover max-height (`Math.max(available, 280)`) — **kept intentionally**, see [Known tradeoffs](#known-tradeoffs).

## Known tradeoffs (intentional, flagged for Marcel)

1. **Snapshot table drop on plugin update.** `zp_logicmodel_snapshots` is dropped via `dropIfExists` in the plugin's `update()` method. This destroys any saved snapshots on existing installs. The feature was effectively unusable (no view, restore, compare, export), so the risk of losing meaningful production data is near zero. Flagged here in case any pilot user did rely on it.

2. **Healthbadge popover max-height kept at `Math.max(available, 280)`.** Copilot suggested clamping to `available`. The current behaviour ("minimum 280px usable, scroll internally if the badge is near the viewport bottom") is documented inline and is the intentional choice — sub-280px popovers near the viewport bottom would make the content too cramped to read. If you disagree, flip to `Math.min(available, …)` and it's a one-line change.

3. **Plugin features gated to `type=strategy` projects only.** The architectural call was: StrategyPro's smart features (narrative, health badges, wizard, entity pills) only make sense in a strategic-planning context. Regular project blueprints get a plain OSS canvas. Helper is `logicModelIsStrategyProject($canvasId)`, used by 8 render listeners. If you want any of those features available on non-strategy projects, remove the gate from the specific listener.

4. **Generated goal titles inherit from canvas item `description` field (the bold line on the card), with `conclusion` flowing into goal `description` ("Metric:"). The dashboard label "Metric:" is hardcoded as a translation string. When the conclusion is more "measurement method" than "metric" (e.g. "Measured by pre/post DIBELS comparison"), the label reads slightly off. Relabeling spans 40+ translation files — flagged as a separate decision later, not blocking this PR.

5. **The "All status" dropdown label** in the Logic Model header is grammatically rough but unchanged in this PR — relabel decision deferred (same translation cost as #4).

## Open architectural question

The sector templates work in StrategyPro is a NEW system parallel to Blueprints' `TemplateRegistry`. Both load file-based definitions via a registry service. Conceptually overlapping but with different data shapes (YAML structure-schema vs. JSON content-fixture) and on different sides of the core/plugin boundary.

Three options laid out for Marcel:
- **A.** Extend Blueprints' `TemplateRegistry` to also handle content fixtures. Cleaner. Touches core. Multi-day refactor.
- **B.** Keep the parallel implementation but rename it ("sector starters") to clarify the boundary.
- **C.** Drop sector starters as a separate concept; rely on the wizard for pre-fill.

**Pending Marcel's call before further sector-template work proceeds.** A separate icon-polish edit (per-sector Font Awesome icons in the selector partial) is sitting uncommitted locally so we don't accumulate work on this surface until the direction is decided.

## Status check (as of writing)

- Core CI: all four open PRs are green (acceptance, phpstan, pint, unit, security audit).
- Plugins repo: no CI configured.
- Copilot reviews on #3481 (2 inline comments) and #46 (4 inline comments) — three addressed in code, one (popover height) intentionally left, two on #3481 (opacity multiplication + hardcoded shadow) addressed.
- No human reviewer feedback yet.
