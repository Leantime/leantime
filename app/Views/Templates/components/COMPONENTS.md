# Frontend Componentization — Tracker & Playbook

> **Owner:** maintained by Claude as the single source of truth for the componentization
> effort. Supersedes the "Component Updates Tracker" spreadsheet (whose *status* column is
> stale — the taxonomy, naming, prop vocabulary, and priorities are kept).

## Goal

Route **all** of Leantime's HTML through a central component layer so that a future design
overhaul (e.g. daisyUI) becomes a one-file change instead of an N-thousand-call-site change.

## The rules (how we do this safely)

1. **No-op first.** Every component renders **byte-for-byte what the page renders today** —
   same Bootstrap/`lt-`/`forms.css` classes. **Zero visual change.** We insert the abstraction
   layer without touching the output.
2. **The prop API is the durable contract; the rendered classes are the swappable
   implementation.** Call-sites are written against the canonical prop vocabulary (below) now.
   At design time, only each component's internal class-map + the CSS change — restyling the
   whole app from one place. This is the entire point.
3. **One component at a time, tested each step.** Build no-op component → verify identical
   render (compile + Playwright before/after) → migrate call-sites in small batches → test →
   commit → next. No big-bang merges (that's what broke `feature/ui-components`).
4. **Defer the design engine.** No daisyUI, no `tw-`-prefix churn, no JS rewrite during the
   no-op phase. The design update (daisyUI or otherwise) is a later, separate phase that
   becomes trivial *because* the component layer exists.
5. **Old branches are API reference only**, never a merge source (see Branch Landscape).

## Taxonomy & naming

Category-namespaced anonymous Blade components — resolves today with **no** ServiceProvider
change (nested folders already work):

```
<x-global::{category}.{name}>  →  app/Views/Templates/components/{category}/{name}.blade.php
```

Six categories: **`elements` · `forms` · `actions` · `navigation` · `feedback` · `layout`**.
Domain-specific components live under their domain namespace, e.g.
`<x-tickets::ticket-card>` → `app/Domain/Tickets/Templates/components/ticket-card.blade.php`.

## Prop vocabulary (the IDL — the durable contract)

| Prop | Options | Default | Notes |
|---|---|---|---|
| `contentRole` | default · primary · secondary · tertiary(=ghost) · accent · link | primary (actions) | semantic role |
| `state` | default · info · warning · danger · success | default | |
| `variant` | component-specific | `''` | behavior/shape variant |
| `scale` | xs · s · m · l · xl | m | size |
| `position` | left · right · top · bottom · inner · outer · start · end | bottom | |
| `tag` (element) | a · input · button · … | component-specific | polymorphic element |
| `align` | start · end | | |
| `labelText` | text | `''` | |
| `labelPosition` | top · left · right · bottom · inside | | |
| `caption` | text | `''` | helper text under the control |
| `validationText` / `validationState` | text / state | `''` | |
| `leadingVisual` / `trailingVisual` | icon class | `''` | |
| `items` | array | `[]` | for list-driven components |

> Props are **camelCase** in `@props` (`contentRole`); Blade normalizes `content-role="…"`
> attributes to the same variable, so call-sites may use either.

## No-op mapping principle (worked example: button)

The canonical vocabulary maps to **today's** classes so output is unchanged:

| canonical | renders today | (at design time →) |
|---|---|---|
| `contentRole="primary"` | `btn btn-primary` | `dui-btn dui-btn-primary` |
| `contentRole="secondary"` | `btn btn-secondary` | … |
| `contentRole="default"` | `btn btn-default` | … |
| `contentRole="tertiary"`/`ghost` | `btn btn-transparent` | … |
| `contentRole="link"` | `btn btn-link` | … |
| `state="danger"` | `btn btn-danger` | … |
| `scale="s"` / `scale="l"` | `btn btn-small` / `btn btn-large` | … |

Extra/legacy classes pass through via `$attributes->merge` (e.g. `class="addCanvasLink"`).
JS-coupled buttons (`dropdown-toggle`) are migrated in the **dropdown** component phase, not here.

## Component registry

Status: ⬜ todo · 🟡 in progress · ✅ no-op done (on master) · 🎨 design-updated.
"Ref" = branch to crib the prop API from (reference only — do not merge).

### P0 — primitives & core
| Component | Tag | Cat | Status | Ref | Notes |
|---|---|---|---|---|---|
| button | `forms.button` | forms | ✅ | refactor/table-component | merged #3531: no-op migration + 3-tier role model |
| text-input | `forms.text-input` | forms | 🟡 | refactor/table-component | PR #3558: thin no-op; **146 call-sites / 56 files migrated**; **defer JS-coupled** (datepickers/tags/inline-edit/color/sorter/hourCell) + legacy `<?php echo ?>`-in-attr |
| textarea | `forms.textarea` | forms | ⬜ | selectsComponentUpdates | |
| select (native) | `forms.select` | forms | ⬜ | refactor/table-component | native no-op first; JS-enhanced later |
| form-field | `forms.field-row` | forms | ⬜ | refactor/table-component | label-row + caption + validation wrapper |
| card (content-box) | `elements.card` | elements | ⬜ | ui-components | **replaces `.maincontentinner`** (167 sites) |
| chip | `actions.chip` | actions | ⬜ | selectsComponentUpdates | |
| dropdown-menu | `actions.dropdown` | actions | ⬜ | refactor/table-component | JS-coupled (Bootstrap dropdown) |
| modal | `actions.modal` | actions | ⬜ | modal line | unify 3 legacy modal systems; HxComponent-aligned |
| tabs | `navigation.tabs` | navigation | ⬜ | ui-components | jQuery-UI tabs; needs htmx.onLoad re-init |
| text-editor | `forms.text-editor` | forms | ⬜ | (Tiptap core) | wrap Tiptap (already HTMX-aware) |
| date-picker | `forms.date-picker` | forms | ⬜ | selectsComponentUpdates | jQuery-UI datepicker; needs htmx.onLoad re-init |

### P1
| Component | Tag | Cat | Status | Notes |
|---|---|---|---|---|
| checkbox | `forms.checkbox` | forms | ⬜ | |
| radio | `forms.radio` | forms | ⬜ | |
| toggle | `forms.toggle` | forms | ⬜ | |
| button-group | `forms.button-group` | forms | ⬜ | |
| badge | `elements.badge` | elements | ⬜ | flat `badge` exists on master — migrate to category |
| avatar | `elements.avatar` | elements | ⬜ | flat `avatar` exists on master |
| accordion | `elements.accordion` | elements | ⬜ | flat `accordion` exists on master |
| table | `elements.table` | elements | ⬜ | DataTables-coupled; class-backed (`Table.php`) |
| empty-state | `elements.empty-state` | elements | ⬜ | wraps `undrawSvg` |
| date-info | `elements.date-info` | elements | ⬜ | relative-time |
| statistic / code | `elements.statistic` / `elements.code` | elements | ⬜ | |
| steps / breadcrumbs / pagination | `navigation.*` | navigation | ⬜ | |
| alert / progress / skeleton / loading / indicator | `feedback.*` | feedback | ⬜ | `loader`/`loadingText` exist on master |
| page-header | `layout.page-header` | layout | ⬜ | flat `pageheader` exists on master |
| color-picker / select-panel / context-menu | various | ⬜ | |

### Domain-specific
| Component | Tag | Status | Notes |
|---|---|---|---|
| ticket-card | `tickets::ticket-card` | ⬜ | **= the tile from `refactor/card-column-components`** |
| ticket-column | `tickets::ticket-column` | ⬜ | **= `column` from `refactor/card-column-components`** |
| milestone-card | `tickets::milestone-card` | ⬜ | |
| project-card | `projects::project-card` | ⬜ | |
| comments list | `comments::list` | ⬜ | HxController-backed |

## Card naming resolution (decided)

- `elements.card` = the glass **content-box** that replaces `.maincontentinner`.
- The small **tile** I shipped on `refactor/card-column-components` becomes `tickets::ticket-card`.
- My `column` becomes `tickets::ticket-column`.
- `refactor/card-column-components` is **superseded** — its work folds into the above; the
  Logic Model board will consume `tickets::*` + `elements.card`.

## Branch landscape (reference only — DO NOT merge)

| Branch | Age | Use as | Verdict |
|---|---|---|---|
| `feature/ui-components` | fresh (Feb 2026) | richest reference: daisyUI theme, full category layer, 11/12 P0, domain cards, JS modules | reference; broke features as a big-bang — harvest APIs, don't merge |
| `refactor/table-component` | ~2024 | **best forms/table/form-field + prop IDL + `Table.php`** | reference |
| `selectsComponentUpdates` | Jan 2025 | superset forms incl. chip/datepicker/select + 113 call-site examples | reference |
| `feature/leantime-design-tokens` | 2024 | daisyUI theme + Material-3 palette token values | reference (for design phase) |
| modal line (`feature/modal-component`) | 2024 | `<dialog>` + hash-routed global page-modal pattern | reference (rebuild on HxComponent) |
| `refactor/javascript-to-modules-…` | 2024 | full domain-JS ESM conversion (still pending eventually) | reference |
| `feature/card-component`, `feature/table-component`, `left-nav-design-fix`, `file-component`, `button/text-input/checkbox-radio-component`, `commentsComponent` | 2024 | stale/subsumed | reference at most |

## JS-backed component pattern (the standard)

Copy **Tiptap** (`public/assets/js/app/core/tiptap/index.js`) — the only widget already correct:
- markup carries a `data-lt-*` initializer attribute (never an inline `<script>`),
- one central **idempotent registry** per widget type (`WeakMap`, `data-…-initialized` guard),
- wired to **`htmx.onLoad`** (init on first paint + every swap) and, where teardown is needed,
  `htmx:beforeSwap`/`htmx:afterSwap`,
- heavy bundles lazy-loaded via `Template::requireComponents([...])` / `needsComponent()`.

This fixes the SlimSelect / Chosen / jQuery-UI-datepicker / tabs / inlineSelect bug where
inline `jQuery(document).ready` init runs only on first paint and breaks after HTMX swaps.

## ⚠️ Gotcha: no double-quotes inside a component attribute value

Blade parses **component** attributes more strictly than plain HTML. A `"` inside a `{{ }}`
expression within an attribute value terminates the attribute early and breaks the tag —
even though the same markup works as a raw `<a href="...">`. So when migrating:
- `href="{{ $x["key"] }}"` → use `{{ $x['key'] }}` (single-quote the array key), or `:link="$x['key']"`.
- `href="{{ BASE_URL . "/path/$id" }}"` → use `link="{{ BASE_URL }}/path/{{ $id }}"` (Blade interpolation).
- `class="{{ $c ? "a" : "b" }}"` → single-quote the strings, or compute in `@php`.
Run the brace/quote-aware scan (forms.button opening tags with a `"` inside any `{{ }}`) after any
button migration batch — `view:cache` does NOT catch these (they fail at render, not compile).

## ⚠️ Gotcha: no legacy `<?php echo ?>` / `<?= ?>` inside a component attribute value

Raw PHP echo tags work in a plain `<input placeholder="<?php echo … ?>">` (PHP executes at render),
but Laravel's **component-tag compiler** treats a non-bound attribute value as a *literal string*, so
`<?php … ?>` inside a `<x-…>` attribute does NOT reliably execute. **Leave such inputs RAW** (or first
modernize the echo to `{{ … }}` / `{!! $tpl->escape(…) !!}` in a separate step, then migrate). Found in
`Auth/userInvite` (placeholders use `<?php echo $tpl->language->__('…') ?>`) — deferred. Scan migrated
tags for `<?php` / `<?=` before committing.

## Per-component playbook (repeatable)

1. Read what the primitive renders today (classes, JS hooks, every call-site shape).
2. Build the **no-op** component under the right category, full prop IDL, mapping to today's classes.
3. `php bin/leantime view:cache` + `vendor/bin/pint --test` (syntactic gate).
4. Migrate a **small pilot** batch of call-sites; **Playwright before/after** to prove zero visual diff.
5. Migrate the rest in batches, re-verifying; commit per batch.
6. Update this tracker (status, gotchas, call-site count migrated).

## Button migration — deferral backlog (handle in later passes)

The no-op migration deliberately defers buttons it can't migrate without changing the rendered
class set / behavior. Categories found (to revisit, some need a design decision):
- ~~**`class="button"` (not `btn`)**~~ — DONE (PR follow-up): a CSS audit found `.button` has **no rule at
  all**; `input[type='submit']` is styled by the `.btn-primary` element-selector group (forms.css:313), so
  these 44 submits already render as primary buttons. Migrated all 44 to
  `<x-global::forms.button tag="input" inputType="submit" contentRole="primary">` (no-op). Also cleaned up a
  few pre-existing duplicate `class="button" class="button"` attrs. **Follow-up:** ~16 are `del*` confirmation
  submits that look primary today — candidates for `state="danger"` in a later semantic pass (a visual change,
  not a no-op).
- **Unstyled `<input type="submit">`** (no class) — adding `btn` is a *design* change, not a no-op. Defer.
- **Unmapped btn variants** — `btn-sm`/`btn-lg` (vs Leantime `btn-small`/`btn-large`),
  `btn-danger-outline`, `btn-circle`, `btn-inverse`, `btn-file`. Add mappings (after confirming CSS) or keep deferred.
- **role+state combo** (`btn btn-default btn-success`) — component currently emits one color; allow coexistence.
- ~~`<a onclick>` without `href`~~ — DONE: component emits `href` only when `link` is set; migrate these by omitting the `link` prop.
- **dropdown-toggle / data-toggle / fileupload / span.btn** — handled in the dropdown / file-upload / later phases.

## Text-input migration — scope & defer rubric

`forms.text-input` is a **thin no-op**: it emits a plain `<input>` with today's class (default = no
class) and passes all attributes through; the label/validation IDL props are declared but not rendered
(a wrapper would change markup — that's the design phase). Pass the **HTML-native `type=`** (it is a
declared `@prop`, so Blade extracts it from the attribute bag — emits exactly one `type`, never a duplicate).

- ✅ **Migrate (146 done in PR #3558; more in follow-ups):** standard inputs (bare), headline title inputs
  (`main-title-input` → `variant="headline"`), search inputs. Map source class → `variant`; any extra
  non-variant class (tw-utilities, `pull-left`, …) passes through `class=`.
  **`.form-control` AND `.input` → bare** (NOT variants): both are pure Bootstrap cruft — forms.css element
  selectors override `.form-control`, and `.input` has *no backing CSS rule at all*; a bare input renders
  identically (the entry-page width that `.form-control` gave comes from `.regpanelinner input{width:100%}`).

### Variant taxonomy (evidence-backed — 4-agent CSS audit)
Only visually-distinct treatments earn a variant. Verdicts:
| variant | class | real? | what it actually is |
|---|---|---|---|
| `headline` | `.main-title-input` | ✅ | large 24/26px (`--font-size-xxxl`) title font + `box-shadow:none`; keeps border/bg |
| `large` | `.input-large` | ✅ (width-only) | fixed `width:210px` — forms.css never sets width, so it survives |
| `small` | `.input-small` | ✅ (width-only) | fixed `width:90px` |
| `ghost` *(planned)* | `.secretInput` | ✅ | inline-edit "looks like text until touched": transparent, no border/shadow, hover/focus reveal box. Pending its async-save JS migration. |
| ~~`form`~~ | `.form-control` | ❌ removed | overridden by forms.css element selectors |
| ~~`legacy`~~ | `.input` | ❌ removed | no `.input` CSS rule exists anywhere |
- ⛔ **Leave RAW — do-not-touch signals** (JS-coupled; breaking these regresses behavior):
  - **datepickers** (jQuery-UI): `.dates .duedates .quickDueDates .dateFrom .dateTo .editFrom .editTo
    .startDate .endDate .projectDateFrom .projectDateTo .week-picker .hasDatepicker` + ids `#deadline
    #sprintStart #sprintEnd #event_date_* #date #startDate #endDate #timesheetdate #invoiced* #paidDate`
    (many init via inline `<script>` in the template + an a11y pass on `.hasDatepicker`).
  - **time**: `.timepicker`, `type="time"`, `#dueTime #timeFrom #timeTo`.
  - **tags**: `#tags` (+ `#tags_tag`/`#tags_tagsinput`), `.tagsinputField`, `data-role="tagsinput"`, `#wikiTagsInput`.
  - **inline-edit / async-save**: `.secretInput`, `.asyncInputUpdate` (+ `data-label` / `data-id`).
  - **color**: `.simpleColorPicker`.   **honeypot**: `.ohnohoney`.
  - **JS grids / clone-templates**: `.hourCell` (timesheet grid), `.sorter` + `name`/`id` clone markers
    like `XXNEWKEYXX` or pipe-keyed `name="new|GENERAL_BILLABLE|…"`.
  - **dynamic `class`/`id`** built with `{{ }}` / `{!! !!}` (can't statically classify → defer).
  - **legacy `<?php echo ?>` / `<?= ?>` in an attribute value** (see gotcha above).
  - **any inline `onchange` / `onblur` / `onkeyup` / `oninput` / `onfocus` handler**.

## Progress log

- _Phase 0_: tracker created; `feature/componentization` branched off master; card-naming resolved.
- _button_: no-op `forms.button` built + 2 correctness fixes (native button-type, no default color).
- _button pilot_: `Auth/login` migrated; Playwright before/after = byte-identical (proven).
- _button batch 1_: ~65 plain buttons migrated across 46 core form/admin/CRUD templates (9-agent
  fan-out, disjoint files); ~70 deferred per the backlog above. Verified: view:cache compiles,
  audit shows no JS-coupled class swallowed, real before/after on /users/showAll = identical class set.
- _button href tweak_: component emits href only when `link` is set (so `<a onclick>` w/o href migrates).
- _button batch 2_: ~100 plain buttons migrated across 43 JS-heavy templates (Tickets, Dashboard,
  Widgets, Canvas/Blueprints/Goalcanvas/Logicmodel, Ideas, Wiki, Calendar, Sprints); the rest deferred
  (dropdown-toggles, fc-* calendar, file-uploads, class="button", unmapped variants, role+state).
  Verified: compile clean, audit clean, live no-op spot-check on /goalcanvas/showCanvas.
  **Core plain-button migration is now essentially complete** — remaining work = the deferral backlog
  (dropdowns get migrated in the dropdown-component phase; class="button"/unstyled = design decisions).
- _button role sanity pass_: 15 Back/Cancel/"Go Back" buttons that were hard-coded btn-primary in the
  original markup demoted to contentRole="secondary" (alternative/navigate-away actions). Only the role
  VALUE changed. This is intentionally NOT a no-op (appearance changes; secondary is unstyled until the
  design phase).
- _button role promotions_: 5 main-action submits that were `default` promoted to `primary` for
  consistency with siblings — Ideas board create/save (advancedBoards + showBoards, ×4) and the
  Comments/showAll reply (generalComment's reply was already primary). Genuinely-secondary `default`
  buttons (Back, Export, Copy, Reset Logo, Resend Invite, Close, Activate) left as-is.
- _button outline variant_: added `variant="outline"` to forms.button (emits btn-outline /
  btn-{state}-outline). All "Save & Close" buttons set to variant="outline" to match the edit-ticket
  save style (7 sites: 5 canvas/idea dialogs + the ticketDetails/articleDialog inputs componentized).
- _action-links -> secondary_: ~35 standalone Cancel/Back/Close/Delete/Remove links that were bare
  `<a>` text-links (no btn class) converted to `<x-global::forms.button ... contentRole="secondary">`,
  preserving onclick + JS-hook classes (delete/formModal/editTimeModal/...). Strictly skipped: dropdown
  `<li>` menu-items (incl. menu delete/edit), accordion + inline `|`-separated toggles, add/create
  toggles, nav, timers, and already-`btn` links. Still bare (flagged, not converted): inline per-comment
  `deleteComment` links + per-row table delete actions (would need a smaller-scale/inline treatment).
- _text-input_: thin no-op `forms.text-input` built on `feature/text-input-component` (off master, post-#3531).
  Scope + datepicker/tags/inline-edit defer rubric above. **PR #3558.**
- _text-input pilot_: `Projects/newProject` headline (`main-title-input` → `variant="headline"`) migrated;
  Playwright = byte-identical (same class/type/name/id/style/value/placeholder); the two `.dateFrom/.dateTo`
  datepickers on the same page left RAW (component never applied to JS-coupled inputs → can't regress).
  (Note: dev instance currently isn't loading `compiled-app`/jQuery, so runtime datepicker init couldn't be
  exercised — but the datepicker DOM is byte-identical to master since those lines are untouched.)
- _text-input sweep_: **146 call-sites across 56 files** migrated (63-file 2-phase workflow: per-file migrate
  + adversarial diff-verify; all 63 verified ok). Diff is perfectly symmetric (202 ins / 202 del = pure
  in-place swaps). Static audit of all 146: 0 problems (no `type=`/inputType dup, no variant class left in
  `class=`, no JS-coupled signal swallowed, no nested-quote, no dup attrs). Compile + Pint clean. Live render
  no-op confirmed on `/setting/editCompanySettings` (`pull-left` passthrough) + `/clients/newClient` (bare).
  **Deferred to follow-ups:** `Auth/userInvite` (3 inputs w/ legacy `<?php echo ?>` in attrs — see gotcha),
  `Tickets/partials/ticketCard` + `partials/subtasks` (HTMX inline-edit/date), and everywhere the
  do-not-touch signals (datepickers/tags/inline-edit/color/`sorter`/`hourCell`/dynamic-class).
- _text-input API refinement (review feedback)_: two API cleanups after review.
  (1) **`inputType` → `type`**: renamed the prop to the HTML-native `type` (17 call-sites). It's a declared
  `@prop`, so Blade extracts it from the attribute bag → exactly one `type`, no duplication. (`forms.button`
  keeps `inputType` because it's polymorphic — `type` is ambiguous across a/button/input.)
  (2) **dropped `variant="form"`** (the `form`/`bordered`→`.form-control` arm). 3-agent CSS audit proved
  `.form-control` is cosmetically redundant in Leantime: `forms.css` element selectors (`input[type=text]…`,
  loaded after Bootstrap) override its bg/border/radius/shadow/padding/height/color, and the only residual
  effect (desktop `width:100%`) is already supplied by container rules (`.regpanelinner input{width:100%}`)
  for the sole 7 call-sites (login ×2, twoFA/verify ×1, install ×4 — all entry pages). No JS hooks
  `.form-control` on inputs. Collapsed those 7 to bare; live render on `/auth/login` = bare inputs, single
  `type`, no `form-control`. Bare IS the form look now.
- _text-input variant taxonomy (review feedback)_: 4-agent CSS audit to keep ONLY evidence-backed variants.
  Findings: `headline`(.main-title-input) = REAL (large `--font-size-xxxl` font + shadow removed);
  `large`(.input-large)/`small`(.input-small) = REAL but width-only (210px/90px — the one prop forms.css
  doesn't set); `ghost`(.secretInput) = REAL inline-edit treatment (4 distinct low-chrome looks found, the
  canonical one being .secretInput) but its call-sites are the deferred async-save fields, so it's a planned
  variant; `legacy`(.input) = REDUNDANT (no `.input` CSS rule exists anywhere). **Dropped `variant="legacy"`**
  (1 call-site, TwoFA/edit → bare; removed the arm). Component now exposes only `headline`/`large`/`small`.
