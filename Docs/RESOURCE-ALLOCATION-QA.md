# Resource Allocation — QA Walkthrough

**Surface:** `/pgmPro/resourceAllocation`
**Scope:** V1 planning + V2 plan-vs-actual, backward auto-fill (people/budget from existing data), all state variations.
**Out of scope:** Forward LM-Inputs classifier (§7 follow-up), Dependencies auto-fill (no deterministic source), stakeholder report Resources section.

---

## Setup

Have ready:
- A **program** (a `zp_projects` row with `type = 'program'`).
- 2+ **child projects** parented to that program.
- At least one child project with **users assigned** (for the people seeder).
- At least one child project with **`dollarBudget > 0`** (for the budget seeder).
- A separate program with **no** child projects — for empty-state verification.

Set the current project to the program before loading the URL.

---

## Flow 1 — Fresh program (no resources authored)

1. Load `/pgmPro/resourceAllocation` on the fresh program.
2. Overview summary strip should read **honest em-dashes** — not zeros:
   - Team capacity: `—`
   - Allocated: `0h` with sub-label `nothing allocated`
   - Available: `—` with `no capacity set`
   - People: `0` with `none added`
   - Budget: `—` with `no budget set`
3. People / Budget / Dependencies tabs each show an **invite-to-author card** — icon + hint + two buttons:
   - People: **"Add everyone from child projects"** (working) + "Add person" (manual)
   - Budget: **"Add lines from child project budgets"** (working) + "Add budget line" (manual)
   - Dependencies: "Fill from Logic Model Inputs — soon" (disabled, tooltip explains) + "Add dependency"
4. Global bar top-right shows **"Auto-fill from existing data"** (working, not disabled).

## Flow 2 — Backward auto-fill (one click)

5. Click **"Auto-fill from existing data"** in the top bar.
6. Page reloads with a **success toast**: `"Added N people and M budget lines. Skipped 0 already present."`
7. Overview strip now shows real numbers (capacity used %, allocated hours, available, people count, budget total).
8. People tab: one row per assigned user across all child projects, deduped, with `capacity = 40h/wk` and empty allocation bar (all available).
9. Budget tab: one line per child project with `dollarBudget > 0`, spend at $0, project name as label, project color on the segment.
10. Click **"Auto-fill from existing data"** again — toast says: `"Everything is already added (N matches skipped)."` No duplicates created.

## Flow 3 — Per-section seeders

11. Reset to fresh state (delete all resources for the program).
12. Go to People tab → click **"Add everyone from child projects"** in the empty state.
13. Page reloads; only people are added, budget remains empty.
14. Go to Budget tab → click **"Add lines from child project budgets"**.
15. Page reloads; only budget lines added.

## Flow 4 — V1 planning interactions

16. On People tab (with people present), click a project-color segment on any person's allocation bar → hours-per-project edit should surface.
17. Add hours; the person's total allocation updates; the summary strip's "Allocated" and "Available" recompute; "% used" mini-bar reflects the new state.
18. Any person at ≥99% shows the **at-capacity amber treatment** and the People summary cell shows "N at capacity."
19. On Budget tab, click a budget line's amount to edit; ≥90% spent renders red (at-risk); ≥100% renders red + bold (over).
20. On Dependencies tab, toggle a dependency between confirmed / tentative — chip color flips green ↔ amber.

## Flow 5 — Stub person (seeded from LM Input, not linked to a user)

21. **Setup** (until forward LM seeder ships, do this manually):
    - Insert a row into `zp_canvas_items` where `box='people'`, `status='stub'`, `data` JSON has `{"userId": 0, "capacity": 40, "allocations": {}}`, `description = 'Jordan Rivera'`, optionally set `milestoneId` to a canvas item id for the "source" link.
22. Reload the People tab. The stub row renders below active people as a **dashed "complete me"** row.
23. **"Seeded"** chip shows next to the name; if `milestoneId`/`sourceCanvasItemId` was set, it's a hyperlink (opens the LM Input source).
24. The bar area reads: `"Link a user to set capacity & hours"` (no fake allocation).
25. Right column shows a **"Link user"** button.
26. Stub is **not counted** in `teamStats` totals — summary strip's "Allocated" excludes them.

## Flow 6 — V2 plan-vs-actual mode

27. Append `?version=v2` to the URL (or use the mode toggle).
28. Summary strip now has **6 cells** (adds "Actual (this wk)" between Allocated and Available).
29. Reading-guide bar shows plan/actual legend + `Xh / Yh logged this week` chip.
30. Overview projects table has header row (Project / Planned / Actual / Δ / bar) and each row shows planned+actual overlaid.
31. People tab renders **two bars per person**: planned (thick) + actual ghost (thin dashed).
32. Delta column reads `+Xh over` (red) / `−Xh under` (amber) / `on plan`.
33. Log some timesheet entries against a child-project ticket for one of the users; reload — the actual bar fills, the delta updates.

## Flow 7 — V2 orphaned-user state (`userId === 0` on an active row)

34. **Setup**: find an active person, edit their canvas_item's `data` JSON to set `userId: 0` (simulating a deleted linked user).
35. In V2 mode, that row's **actual bar becomes a neutral dashed strip** reading: `"No linked user — actuals can't be tracked yet"`.
36. The delta column reads `link a user to track` (italic, muted) — **not** alarm-amber "fully under."
37. Same treatment applies to stub-status persons in V2 (they always have `userId === 0`).

## Flow 8 — Empty states persist correctly

38. Set state where only budget exists (delete people, keep budget) → People tab shows the invite-card, Budget tab shows real rows.
39. Delete all resources → all three tabs show invite cards, summary strip returns to em-dashes.

## Flow 9 — Print parity

40. From the tab, hit browser print (or `Cmd+P`). Should show **all sections in one flowing snapshot** (Overview → People → Budget → Dependencies) — not just the current tab.
41. Global bar (mode toggle, auto-fill button) + tab bar + add-buttons should be **hidden** in print.
42. Rows should not break across pages (page-break-inside: avoid).

## Flow 10 — MCP / RPC surface

43. Via MCP client: call `seedResourcesFromProjects` with `programId` = your program id + `type` = `both`. Response text should show added/skipped counts.
44. Via JSON-RPC: `POST /api/jsonrpc` with method `leantime.rpc.PgmPro.ResourceStructureService.seedPeopleFromChildProjects`, params `{programId: N}`. Returns `{added, skipped, canvasId}`.
45. Both should be idempotent and return the same shape as the UI.

---

## Known-limited (do not flag)

- The **"Fill from Logic Model Inputs"** button on Dependencies (and the future forward-direction path elsewhere) is intentionally disabled with a "coming soon" tooltip. §7 scoped this as follow-up.
- No period switcher in V2 — `getActualHours` is hardcoded to `this_week`. Service accepts other periods but there's no UI yet.
- **Old `12-RESOURCE-ALLOCATION.md` and `13-RESOURCE-ALLOCATION-PROMPT.md`** in Docs are the pre-revival versions; the current spec is `16-RESOURCE-ALLOCATION-REQUIREMENTS.md` (pending renumber to `19-` to clear the `16-CROSS-CUTTING-CONCERNS.md` collision).
