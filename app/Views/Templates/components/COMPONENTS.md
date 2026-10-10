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
| text-input | `forms.text-input` | forms | ✅ | refactor/table-component | merged #3558: no-op; 146 call-sites / 56 files; variants `headline`/`large`/`small` (dropped `form`/`legacy` as CSS-redundant); HTML-native `type` prop; **defer JS-coupled** (datepickers/tags/inline-edit/color/sorter/hourCell) + legacy `<?php echo ?>`-in-attr |
| textarea | `forms.textarea` | forms | ✅ | selectsComponentUpdates | merged #3562: thin no-op (attrs + inner-content slot); 10 plain migrated / 6 files; **defer Tiptap editors** (`.tiptapSimple`/`.tiptapComplex`/`.wiki-editor-textarea`) |
| select | `forms.select` + `forms.select.option` | forms | 🟡 | selectsComponentUpdates | P1 (#3873) no-op shell, 115 core Blade selects / 43 files. P2: `enhanced` prop → SlimSelect v2 via one registry (`core/selects.js`); **Chosen removed**; 45 core select tags enhanced; canvas-dialog icons server-rendered (goal dialog icons restored). `optgroup` stays raw markup (no sub-component needed). Plugins + `.tpl.php` in P5/P6. See "Select & dropdown phase" |
| form-field | `forms.field-row` | forms | ⬜ | refactor/table-component | label-row + caption + validation wrapper |
| card (content-box) | `elements.card` | elements | ⬜ | ui-components | **replaces `.maincontentinner`** (167 sites) |
| chip | `forms.chip` (+ `.option`) | forms | 🟡 | selectsComponentUpdates | P3: one delegated handler (`core/chips.js`) saving over JSON-RPC (adapters ticket/canvas/goal/idea); domain wrappers `tickets::chip-{status,milestone,effort,priority,user,sprint}`, `blueprints::chip-label`, `ideas::chip-status`; all 44 core chips migrated; canvas/idea author shown read-only (`elements.author-avatar`). Plugin `.tpl.php` chips in P5/P6 |
| dropdown-menu | `actions.dropdown` | actions | 🟡 | feature/dropdown-component | P4: variants `menu`/`header-menu`/`filter`/`button`/`panel`/`subject` (= today's DOM shapes); items stay raw `<li>` slot; `trigger` slot carries trigger attrs (tippy/hx/href); `keep-open` for panels; Bootstrap 2 data-api stays the engine (no core JS). 69 core dropdowns / 47 files migrated; `subjectSwitcher` composes it; dashboard widget shell server-rendered. Plugins in P6 |
| modal | `actions.modal` | actions | ⬜ | modal line | unify 3 legacy modal systems; HxComponent-aligned |
| tabs | `navigation.tabs` | navigation | ✅ | ui-components | ARIA button-tablist (roving tabindex, Arrow/Home/End, storage prop, lt:tabs:changed event); vanilla JS, htmx.onLoad-aware; variants attached/floating; tab+panel sub-components (no raw contract HTML in consumers); jQuery-UI wrapper retired (deliberate markup change, called out) |
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
- ~~**`class="button"` (not `btn`)**~~ — DONE (#3563): a CSS audit found `.button` has **no rule at
  all**; `input[type='submit']` is styled by the `.btn-primary` element-selector group (forms.css:313), so
  these 44 submits already render as primary buttons. Migrated all 44 to
  `<x-global::forms.button tag="input" inputType="submit" contentRole="primary">` (no-op). Also cleaned up a
  few pre-existing duplicate `class="button" class="button"` attrs. **Follow-up:** ~16 are `del*` confirmation
  submits that look primary today — candidates for `state="danger"` in a later semantic pass (a visual change,
  not a no-op).
- ~~**Unstyled `<input type="submit">`** (no class)~~ — DONE (#3564, round 2): NOT a design change after all —
  `input[type='submit']` is in the `.btn-primary` element-selector group (forms.css:313), so bare submits
  **already looked primary**. Migrated to `contentRole="primary"` (~30 of them). **Intended visual no-op**, not
  strictly byte-identical: the component adds the shared `.btn` base (`input.btn { vertical-align: top; … }`)
  which a bare submit lacked — imperceptible, but worth stating precisely.
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

## Select & dropdown phase — plan

Covers every `<select>`, every colored value-picker "chip", and every Bootstrap menu/nav dropdown in
`app/Domain/**` **and** `app/Plugins/**`. Inventory last refreshed **2026-10-09**.

### Decisions (made)
- Enhanced selects standardize on **SlimSelect v2 (npm `slim-select`)**; **Chosen is removed** entirely.
- The legacy plugin `.tpl.php` pages are **converted to Blade** as part of this phase.
- Chips persist uniformly over **JSON-RPC** (no more `$.ajax PATCH /api/{x}canvas`, no double writes).
- **Native by default**: `forms.select` renders a plain `<select>`; enhancement is opt-in (`enhanced`).
- **Canvas dialog status/relates keep their icons.** #3776 made the Goalcanvas dialog plain native and
  dropped the icons to stop mismatched side-by-side styling; P2 restores them as server-rendered icon
  options on an `enhanced` select (consistent look *and* icons). Same treatment for Canvas, Blueprints,
  Logicmodel and StrategyPro dialogs.

### Inventory (2026-10-09)
| Thing | Count | Notes |
|---|---|---|
| `<select>` | 171 (123 core / 48 plugins; 147 Blade, 20 `.tpl.php`, 3 echoed from `register.php`) | 19 `.chosen(` inits (blanket sweeps `.ticketTabs select`, `#projectdetails select`, global `.project-select` in `menuController.js:35` that also hits ticketFilter's SlimSelect); 15 live `new SlimSelect` (vendored **v1** — ticketFilter's v2 `settings.placeholderText` is silently ignored today); 13 inline `onchange`; 8 option lists built by PHP `echo`/`sprintf` |
| Chips (status · priority · milestone · user/avatar · sprint · effort · relates) | ~54 in 21 files (4 `.tpl.php`) | 25 build their `<li>`s by PHP string concat; **5 incompatible `data-value` grammars**; 5 copies of `initUserDropdown`, 4 of `initStatusDropdown` (tickets/canvas/blueprints/goalcanvas/ideas controllers); canvas binders `body.on` **stack** on re-init; `initStatusDropdown` does `removeClass()` wholesale; HTMX-rendered `partials/ticketCard` is never re-bound; to-do widget status is **written twice** (`hx-post` + RPC) |
| Bootstrap menus/nav | 180 `dropdown-toggle` in 74 files (8 `.tpl.php`) | 6 DOM shapes (see variants); 8 files still hand-roll `header-title-dropdown`; 3 menus built as JS strings (Files uppy ×2, `Widgetcontroller.js` duplicating `moveableWidget.blade.php`); no Escape / close-on-swap anywhere |
| Existing components | — | `dropdownPill` = the chip, **0 call-sites**, duplicate-`class` bug → delete. `inlineSelect` = 3 plugin sites (Implementationintentions), inline non-`htmx.onLoad` script → replace. `subjectSwitcher` (10) = keep. `periodpicker` = the HTMX-correct model. `selectable` = radio tile, out of scope |
| Dead code | — | `Tickets/submodules/additionalFields.blade.php` (0 refs), 8× `new SlimSelect({select:'#searchCanvas'})` (no such element), `#themeSelect` chosen init, `goalCanvasController` chip binders (never called), `Billing/subscriptions_old.tpl.php` (0 refs) |

### Which component do I use? (the line, drawn once)
The header sprint/board switcher, the group-by button and the ⋮ menu all share one mechanism (Bootstrap
`dropdown-toggle` + `dropdown-menu`). They differ only in what the trigger looks like and what picking an
item **means**. Decide by meaning; the variant follows from the trigger shape and is cosmetic.

| Picking an item… | Component | Examples |
|---|---|---|
| is submitted with a form (it is a `<select>`) | `forms.select` | ticket type, client, role, timezone, ticket-filter multi-selects |
| **persists a field on an entity now**, toggle shows the current value in color | `forms.chip` | status, priority, milestone, assignee, sprint, effort, relates |
| **navigates to another subject** (page title changes) | `subjectSwitcher` (composes `actions.dropdown variant="subject"`) | sprint switcher, board switcher on canvases/wiki/ideas, "All Notes ▾" |
| **changes how the current subject is viewed** (one item active) | `actions.dropdown variant="filter"` | group-by, Day/Week/Month, status filter, list/kanban toggle, search type filter |
| **runs a command** on the thing | `variant="menu"` (row/card ⋮) / `variant="header-menu"` (page-header ⋮) | edit / delete / export / print |
| **creates something** | `variant="button"` | "New ▾" |
| opens a **panel** of inputs, not a list | `variant="panel"` | invite link, share URL, recurring-task form, kanban view menu, news/notifications, tags popover |

Tells: `data-value` on items + a `label-*`/color on the toggle → chip. `nav-header` + edit/delete links →
menu. `btn-group.viewDropDown` → filter. `header-title-dropdown` → subject. Gray areas: the ticketHeader
sprint switcher navigates *and* writes `#sprintSelect` → stays `subjectSwitcher` (the hidden-input write is
the item's own `onclick`). `projectHub` uses `header-title-dropdown` but is a client **filter** → `filter`.

### HTMX contract (`forms.select`, `forms.chip`)
1. **`hx-*` lands on the `<select>` itself** — `$attributes` merge onto the control, no wrapper in between,
   so `hx-trigger="change"`, `hx-include`, `hx-vals` behave exactly as raw markup.
2. **Enhanced selects still fire native `change`** on the underlying `<select>` (SlimSelect v2 does this;
   verify on a test page in P2, and re-dispatch from `afterChange` if it ever doesn't).
3. **Survives swaps**: the select registry inits on `htmx.onLoad` (first paint, every swap, nyroModal
   content via `modals.js:47`) and destroys instances inside the target on `htmx:beforeSwap`. No inline
   `<script>` init anywhere.
4. **Chips are delegated** (document-level click), so HTMX-rendered chips work without re-init. After a
   save the chip emits `lt:chip:changed` (bubbles); consumers can `hx-trigger="lt:chip:changed from:body"`.
   `form` mode fires `htmx.trigger(hiddenInput, 'change')`. `adapter="none"` + `hx-post` on options = pure
   HTMX persistence — **never both** (that is today's to-do double write).
5. `hx-*` attribute sets are part of every before/after diff.

### Components
**`forms.select`** (+ `forms.select.option`, `forms.select.optgroup`) — no-op `<select {attrs}>{slot}</select>`,
or renders `:options` when there is no slot. Extra classes pass through (`span11`, `tw-w-full`,
`user-select`; `form-control` too until audited for selects).

| Prop | Default | Renders |
|---|---|---|
| `name`, `multiple`, `disabled`, `required` | — | declared so Blade emits exactly one |
| `value` | null | selected option(s) in `:options` mode |
| `placeholder` | '' | `data-placeholder` (enhanced) |
| `enhanced` | false | `data-lt-select` |
| `search` | auto | `data-search` (auto = >10 options; replaces Chosen `disable_search_threshold:10`) |
| `allowDeselect` / `closeOnSelect` | false / true | `data-*` |
| `options` | [] | `[v => label]` or `[v => [label, icon, color, colorClass, image, selected, disabled]]` |
| `labelText`, `caption`, `validationText/State`, `leadingVisual`, `variant`, `scale` | '' | declared, not rendered (design phase; needs `forms.field-row`) |

`.option`: `value`, `selected`, `disabled`, `icon`, `color`, `colorClass`, `image` → `data-icon/-color/-class/-image`,
turned into SlimSelect v2 `html` by the registry. Replaces the "empty `<select>` filled by JS
`data:[{innerHTML}]`" canvas-dialog pattern with server-rendered options. `.optgroup`: `label`.

**`forms.chip`** (+ `forms.chip.option`) — byte-identical to today's chip markup (classes/ids
`{type}Dropdown`, `{type}DropdownMenuLink{id}`, `.label-*`, `priority-bg-*`, `nav-header border` kept, so
kanban drag/drop, `colorTicketBoxes()` and `.nyroModalCont .ticketDropdown` CSS keep working), plus
`data-lt-chip="{adapter}" data-entity-id data-field [data-canvas-type]` on the wrapper and
`data-current-class` on the toggle.

| Prop | Values |
|---|---|
| `type` | status · user · milestone · effort · priority · relates · sprint |
| `adapter` | ticket · canvas · goal · idea · form · none |
| `entityId`, `field` | id; column (`status`, `editorId`, `milestoneid`, `storypoints`, `priority`, `sprint`, `relates`, `author`, `box`) |
| `value`, `label`, `colorClass`, `color`, `image` | current selection + visual |
| `header`, `align` (end → `pull-right`), `canvasType`, `name` (form mode → hidden input) | |

`.option`: `value` (**raw key only** — retires all 5 grammars), `label`, `colorClass`, `color`, `image`. Admin
free-text status classes are validated `^[A-Za-z0-9_-]+$` at render (fallback `label-default`).
Thin domain wrappers hold the option loops copy-pasted ~54×:
`tickets::chip-{status,priority,effort,milestone,user,sprint}` (effort/priority labels rendered server-side →
JS label maps go away), `blueprints::chip-{status,relates,user}` (shared by Canvas/Blueprints/Logicmodel/
Goalcanvas/StrategyPro/Whiteboards via `adapter` + `canvasType`), `ideas::chip-status` (field `box`).

**`actions.dropdown`** — variant = today's DOM shape (as built in P4; `.item`/`.header`/`.divider` sub-components were dropped — raw `<li>` items are already uniform):

| `variant` | wrapper → trigger → menu |
|---|---|
| `menu` (default) | `div.inlineDropDownContainer` → `a.dropdown-toggle.ticketDropDown > i.fa-ellipsis-v` → `ul.dropdown-menu` |
| `header-menu` | `span.dropdown.dropdownWrapper.headerEditDropdown` → `a.dropdown-toggle.btn.btn-transparent` → `ul.dropdown-menu.editCanvasDropdown` |
| `filter` | `div.btn-group.viewDropDown` → `button.btn.dropdown-toggle` → `ul.dropdown-menu` |
| `button` | `div.btn-group` → `.btn.btn-primary.dropdown-toggle` + caret |
| `panel` | bare `div` → `a.dropdown-toggle` → `div.dropdown-menu` — popovers with content (inputs, tabs, htmx) and menus whose trigger brings its own look (head menu, round buttons); `keep-open` stops inside clicks closing it |
| `subject` | `span.dropdown.dropdownWrapper` → `a.dropdown-toggle.header-title-dropdown` (used by `subjectSwitcher`) |

Props (built): `variant`, `label` (raw trigger HTML) or `trigger` slot (its attributes — `data-tippy-content`,
`hx-*`, `preload`, `href`, extra `class` — go on the trigger), `icon` (⋮ variants), `href`, `as` (wrapper tag, e.g.
`li` in the head menu), `menuAs` (`div` menu for a filter holding a form), `triggerClass`, `menuClass`, `menuId`,
`menuStyle`, `keepOpen`, `ariaLabel`. Wrapper attributes pass through. Escape/outside-click/arrow handling is
Bootstrap 2.3's own, so there is no `core/dropdown` JS.

### JS modules (IIFE on `leantime.*`, added to `compiled-app` after `app.js`)
- **`public/assets/js/app/core/select/index.js`** — Tiptap-style registry: `leantime.select.{init, get,
  refresh, destroy, destroyWithin}`; `select[data-lt-select]:not([data-lt-select-ready])`; WeakMap; builds
  v2 `html` from `data-icon/-color/-class/-image`; class swap on `.ss-main` for `colorClass` options
  (replaces `projectsController.initSelectFields`); `refresh()` for Timesheets project→ticket filtering
  (native `hidden` options) and project-settings status-row cloning. `htmx.onLoad` + `htmx:beforeSwap` +
  modal-close teardown in `modals.js`. Lib: `slim-select@^2` replaces `public/assets/js/libs/slimselect.min.js`
  in `compiled-global-component`; CSS `~slim-select/dist/slimselect.css` in `main.less`.
- **`public/assets/js/app/core/chip/index.js`** — one delegated click handler on
  `[data-lt-chip] .dropdown-menu a[data-value]`; adapters (extensible via `leantime.chip.registerAdapter`):
  `ticket` → `Tickets.Tickets.patchTicket {id, values}`, `canvas` → `Blueprints.Blueprints.patchCanvasItem
  {id, params, canvasType}`, `goal` → `Goalcanvas.Goalcanvas.patchGoalItem`, `idea` → `Ideas.Ideas.patchIdeaItem`,
  `form` → hidden input + `change`. Updates text/class (via `data-current-class`)/color/avatar, growls, emits
  `lt:chip:changed`; `ticketsController` listens once for kanban side effects (`moveCardToSwimlane`,
  `priority-border-*`).
- ~~`core/dropdown/index.js`~~ — not needed: Bootstrap 2.3's data-api already closes on Escape and outside click,
  and a swapped-out menu takes its `.open` wrapper with it.

### Phases (one PR each)
| # | Repo | Scope |
|---|---|---|
| **P1** native select (no-op) | core | `forms.select` shell only (options stay as the slot — `.option`/`.optgroup` arrive with P2's icon options, when something needs them); migrate **every** core Blade select incl. `hx-*`, inline `onchange`, and the Chosen/Slim-enhanced ones (identical markup → existing inits still bind; P2 then only adds `enhanced`). Option lists built by `@php` echo / `sprintf` stay untouched inside the slot. Plugins + `.tpl.php` + `register.php` wait for P5/P6. Delete `additionalFields.blade.php`. |
| **P2** enhanced select, drop Chosen (**visual change**, isolated) | core | slim-select v2 + `core/select`; migrate 15 Slim + 19 Chosen sites; **canvas dialogs (incl. Goalcanvas) get server-rendered icon options back**; remove dead `#searchCanvas`/`#themeSelect` inits, `menuController` global `.project-select`, ticketFilter duplicate `multiple`; rewrite `.ss-*` CSS for v2 (`forms.css:144-154,1205-1266`, `slimselect.leantime.css`); delete Chosen from `package.json`, `webpack.mix.js`, `main.less:21,31`, `css/libs/jquery.chosen.css`, forms.css `.chosen-*`, `accessibility.js` chosen block; update `TimesheetCest` selectors. |
| **P3** chips | core | add `@api` to `Blueprints::patchCanvasItem` + `Goalcanvas::patchGoalItem` (enforced by `Jsonrpc::isApiMethod`); `forms.chip` + wrappers + `core/chip`; migrate the 17 Blade chip files (un-concatenate PHP-echo option lists); drop the to-do `hx-post` double write; delete all 5 controllers' `init*Dropdown` + ~74 template calls + REST-PATCH paths; fix `ticketsController.js:~1377` selector; delete `dropdownPill`. |
| **P4** menus | core | `actions.dropdown` + `core/dropdown`; migrate core menus (hand-rolled subject switchers → `subjectSwitcher`, ⋮, filters incl. Search + kanbanViewMenu, split buttons, panels, headMenu); server-render the `Widgetcontroller.js` shell; drop vestigial `data-toggle` in `onboardingProgress`. Defer Files uppy JS-string menus, icon-picker, projectSelector inner. |
| **P5** `.tpl.php` → Blade port (markup-identical) | plugins | StrategyPro, Whiteboardscanvas, PgmPro, Llamadorian pages; `Billing/subscriptions` (check routing); delete `subscriptions_old`; `register.php` selects → rendered partials; fix Whiteboards `canvasName` casing. Core: pointer bump. |
| **P6** plugin migration | plugins | Apply all four components across `app/Plugins/**` Blade (incl. ported pages, 3 `inlineSelect` sites, PgmPro `resourceAllocation`, colorChosen). Core: pointer bump. |
| **P7** cleanup | core | delete `inlineSelect`, leftover dead JS, REST `patch` in `Api/Controllers/Canvas.php`/`Goalcanvas.php` if no external callers; final tracker update. |

Order: P1 → P2 → P3; P4 can run in parallel with P2/P3; P5 after P1–P4; P6 after P5; P7 last. Plugin
PRs branch from the plugins repo's default branch.

Port recipe (P5): create `X.blade.php` and delete `X.tpl.php` in the same commit; `@extends($layout)` /
`@section('content')`; drop `defined('RESTRICTED')` and `$tpl->get()`; `$tpl->e()` → `{{ }}`, `$tpl->__()` →
`__()`, `echo "<li…"` → `@foreach`; keep inline scripts for P6 to clean. Both attribute gotchas above apply.

### Defer rubric (stays raw)
Selects inside a Bootstrap dropdown panel (`.dropdown-menu`) stay **native** (no `enhanced`): the enhanced
list mounts on `<body>`, so a pick is an outside click and Bootstrap closes the panel (RecurringTasks'
recurrence form is the live example).
Selects with inline handlers beyond `submit()`/`location.href`; option lists built by PHP concat or
`sprintf` over a `dispatchTplFilter` format (Timesheets `showMy` is a plugin extension point — migrate the
shell only); radio-item menus (raw slot); `<?php echo` in attributes (modernize first); JS-string menus.

### Risks
| Risk | Mitigation |
|---|---|
| Kanban drag/drop + `colorTicketBoxes()` read chip classes / `a[data-value^=…]` | classes/ids byte-identical; one selector updated in P3; Playwright kanban drag |
| Chosen→Slim and Slim v1→v2 restyle (~34 sites) | isolated in P2 with screenshot review |
| Timesheets project→ticket filter used Chosen DOM | native `hidden` options + `refresh()`; `-g timesheet` |
| projectsController colorChosen clone/destroy | `refresh()` after clone; add-status-row check |
| `canvasType` allowlist per board | wrappers pass it explicitly; one status change per board type |
| Plugin submodule branch state | plugin PRs from default branch; core bumps pointer after merge |

### Verification (per PR)
`php bin/leantime view:cache` + the quote/`<?php`-in-attr scan; Pint, PHPStan, `npm run build`; Playwright
before/after `outerHTML` of `select, .ticketDropdown, .dropdown, .btn-group, .inlineDropDownContainer`
(incl. `hx-*`) on showAll, showKanban, showList, ticket modal, dashboard, roadmap, timesheets/showMy,
project settings, users/editOwn, goal/lean canvases, ideas, wiki, search, plus StrategyPro/Whiteboards/PgmPro
pages; no-op PRs must match apart from added `data-lt-*`, P2 gets screenshots. Behavior: one RPC call per chip
change (showAll, kanban, to-do widget, ticket modal, a canvas board); kanban drag; Escape and swap-close;
one HTMX select end-to-end. Codeception `-g timesheet`, `-g api`, `-g ticket`, `-g user`.

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
- _textarea_: thin no-op `forms.textarea` (#3562). Body is `<textarea {{ $attributes }}>{{ $slot }}</textarea>`
  — attributes pass through, the field value is the slot (inner content) preserved EXACTLY (textareas are
  whitespace-sensitive). **10 plain textareas migrated across 6 files** (Help projectDefinitionStep ×3,
  Ideas/Wiki newMilestone, Timesheets add/edit + Tickets timesheet description, Widgets myToDos
  description-input ×2). **19 Tiptap editor textareas left RAW** — JS upgrades exactly `textarea.tiptapSimple`
  / `textarea.tiptapComplex` (core/tiptap/index.js) plus the Wiki `.wiki-editor-textarea`; never route those
  through the component. No `variant` arm (plain textareas carry no distinct style class; the only textarea
  classes are editor-coupled).
- _button + text-input completion (round 2)_: swept blade for buttons/inputs missed by #3531/#3558.
  **53 migrated across 38 files**: 29 bare `<input type=submit>` (no class — already looked primary via
  forms.css:313, so `contentRole="primary"` is an intended **visual** no-op; the `.btn` base adds minor props
  like `vertical-align`, imperceptible), 4 token-UI text inputs/buttons, Errors back ×4,
  support sponsor, Auth token UI (create/copy/close/delete), Files cancel ×2, widgetManager reset
  (btn-outline→secondary), Reports chart toggles ×6, showProject delete (btn-danger-outline→state=danger
  variant=outline), 1 comment reply. `btn-sm`/`btn-lg`/`btn-secondary` (own CSS, ≠ Leantime's
  small/large/outline) passed through `class=` pending a design-phase scale/role mapping.
  **Left deferred (correct):** 3 comment `btn-success` role+state combos (component emits one color);
  `partials/subtasks` quickadd (nested `__("…")` + HTMX file); dynamic-class links (calendarSettings,
  Dashboard favoriteProject); `ticketFilter` raw `<a>` (whitespace-sensitive, intentional); custom non-`btn`
  widget buttons (Wiki collapse/panel, calendar day-button, todoItem reset); modal `data-dismiss`/`.close`,
  Files `.delete` icons, file-upload `picSubmit`, dropdown-toggles, `<?php echo` invite variants.
  Verified: compile + Pint clean, 0 button-tag problems, diff is tag swaps (multiline tags collapse to 1 line).
  ALSO: TimesheetCest selectors that clicked `.button` repointed to `input[type=submit]`/name (the `.button`
  class is removed by the migration) — see #3563.
- _select P1 (no-op shell)_: `forms.select` built (thin: `<select {{ $attributes }}>{{ $slot }}</select>`, IDL props
  declared-not-rendered like text-input/textarea). **115 selects across 43 core Blade files** migrated by a brace/quote/
  directive-aware converter (opening + closing tag swap only; diff +226/−226, every changed line a tag). Blocked by the
  component-tag rules: 1 (`__("…")` inside an attr in Goalcanvas `canvasDialog`) → fixed to single quotes, then migrated.
  Deleted dead `Tickets/submodules/additionalFields.blade.php` (0 refs). Verified: `view:cache` clean; 43 compiled views
  reference the component; before/after DOM diff of every `<select>` (sorted attrs, boolean attrs normalized, option
  value/label/selected/disabled/class, optgroups) on 36 pages = **234/235 identical** (the 1 diff is `editOwn` `time_format`,
  whose option labels are a live clock preview); live: SlimSelect binds the ticket-filter selects, Chosen binds all 11
  ticket-modal selects, the HTMX `projectListFilter` selects are processed and a `change` round-trips
  (`/hx/menu/projectSelector/update-menu` 200 → `#mainProjectSelector`). Rendering note: bare boolean attrs come out as
  `multiple="multiple"` / `required="required"` (component attribute bag) — DOM-identical. Duplicate `multiple` on ticketFilter
  `#statusSelect` collapses to one.
- _select P2 (enhanced + Chosen removed)_: `slim-select@^2.13.1` (npm) replaces the vendored v1 and Chosen
  (`chosen-js` uninstalled; Chosen JS/CSS/sprites + vendored v1 files deleted; ~90 `.chosen-*`/v1 `.ss-*` CSS
  selector lines removed — SortableJS's `.sortable-chosen` deliberately kept). New registry `core/selects.js`
  (`leantime.selectController.{init,get,setValue,destroy,destroyWithin}`): inits `select[data-lt-select]` on
  `htmx.onLoad` + modal show, destroys on `htmx:beforeCleanupElement` + modal release (v2 mounts its list on
  `<body>`; verified 5→16→5 panels across a ticket-modal open/close). No inline `new SlimSelect`/`.chosen()` left
  in core (19 Chosen + 14 live Slim inits + 6 dead `#searchCanvas` + `#themeSelect` removed). `forms.select.option`
  (`icon`/`color`/`colorClass` → `data-html`, which v2 reads natively): Canvas/Blueprints/Logicmodel/Goalcanvas
  dialog options now server-rendered instead of JS `data:[{innerHTML}]` arrays; **goal dialog status/relates icons
  restored** and its Type select enhanced too (consistent look, the #3776 goal). Project status colors: enhanced
  + `colorClass` options; row template is now a `<template>` (else the registry enhances it before it's cloned);
  `projectsController.initSelectFields()` kept as a delegate (PgmPro/StrategyPro pages call it). Timesheets
  project↔to-do sync moved into `timesheetsController.initProjectTicketSync` (shared by showMy + editTime).
  Theme: `slimselect.leantime.css` maps `--ss-*` to Leantime tokens on the elements (dark mode resolves);
  label-color rules in dropdowns.css extended to `.ss-values/.ss-list span.label-*`. a11y: v2's own
  combobox/listbox ARIA replaces accessibility.js's Chosen/Slim patches. TimesheetCest repointed to v2 DOM.
  **Gotchas found:** (1) v2 copies the select's classes AND inline style onto its control + panel — Bootstrap
  `span*` grid classes floated/indented it (CSS reset added); an option's class lands on its list row (so color
  goes in `data-html` only). (2) `setSelected` rewrites `<option>`s from v2's own copy (its MutationObserver is
  async) → set the value BEFORE hiding/showing options. (3) setting a value fires `change` → guard handlers that
  set each other (Timesheets). (4) open state is `.ss-open-below`/`.ss-open-above`, not `.ss-open`. (5) jQuery
  `.trigger('change')` doesn't reach native listeners — use `selectController.setValue`. (6) `$attributes->merge()`
  rewrites `style` (`width: 220px` → `width: 220px;`) — emit extra attributes directly instead.
  Verified: view:cache; every `<select>` on 36 pages vs the P1 baseline = 151 identical + 55 differing only by the
  new `data-lt-select*` attrs + 7 expected (canvas options moved server-side, `<template>`, clock labels); live:
  filter, ticket modal (mouse + keyboard picks, modal stays open), canvas dialogs, project colors (+ add row),
  Timesheets sync both pages, editOwn, newProject, moveTicket; light + dark. Companion plugins change: CustomFields
  select enhanced; StrategyPro goal-dialog KPI select moved from `register.php` echo strings into
  `partials/goalKpiSelect` (enhanced).
- _select P2 review fixes (native/enhanced parity)_: (1) the extension-less `~slim-select/styles` import landed
  AFTER our theme in main.css, so equal-specificity theme rules (padding, z-index…) silently lost → import the CSS
  by path. (2) the open list's z-index was `--zlayer-9` (40) < nyroModal (100) → list hidden in modals; now 10000.
  (3) option icons sat high (v2's flex row + Font Awesome line-height) → `line-height: inherit`. (4) Native selects
  now share the enhanced closed state: `appearance:none` + the same chevron SVG (`--select-caret`, dark themes
  override the stroke color), same padding/height. (5) bootstrap.min.css caps every select at 175px, which beat
  templates' own widths → `select[style*="width"] { max-width: 100% }` (explicit widths win; everything else keeps
  the cap — dropping the cap globally grew long-label selects to content width, up to 352px). Rule: mixing
  enhanced and native in one form is fine; they must look the same closed.
- _chip P3_: `forms.chip` + `forms.chip.option` (global) and `core/chips.js` — ONE delegated document click handler for
  `[data-lt-chip] .dropdown-menu a[data-value]` (no per-page init; HTMX/modal-rendered chips just work). Adapters:
  `ticket` → `Tickets.Tickets.patchTicket`, `canvas` → `Blueprints.Blueprints.patchCanvasItem` (with `canvasType`),
  `goal` → `Goalcanvas.Goalcanvas.patchGoalItem`, `idea` → `Ideas.Ideas.patchIdeaItem`; `leantime.chipController.
  registerAdapter()` for plugins. `@api` + `#[RequiresPermission(..., entityScoped: true)]` added to patchCanvasItem /
  patchGoalItem (both already authorize fail-closed against the item's real project). After a save the chip fires
  `lt:chip:changed`; ticketsController listens for the kanban side effects (move card when grouped by that field,
  priority border, timer refresh on status). Markup keeps the old ids/classes (`{type}DropdownMenuLink{id}`,
  `.label-*`, `priority-bg-*`), but `data-value` is now the RAW key (5 old grammars gone); option colors in
  `data-class`/`data-color`, avatars in `data-image`. Domain wrappers hold the option loops that were copy-pasted
  (25 of them built by PHP string concat). 44 chips / 16 core files migrated; deleted dead `canvas::element`; the
  to-do widget's double write (hx-post + RPC) and its now-unused `MyToDos::updateStatus/updateMilestone` removed.
  **Decision (Marcel):** canvas/goal/idea *author* chips can't save (author is not patchable, on purpose) → shown
  read-only via `elements.author-avatar` (avatar + name tooltip). Legacy binders kept ONLY for plugin pages
  (tickets: effort/milestone/status — Llamadorian; canvas: user/status/relates — StrategyPro/Whiteboards; ideas:
  status/user — Whiteboards), each guarded to skip `[data-lt-chip]`; removed in P6. Unknown status now shows
  `label.status_unknown` ("No Status Set") instead of hard-coded "new"/"unknown". Gotchas: the kanban recolors chips
  directly, so chips.js removes every color class the chip's options declare (not just the last saved one); an
  avatar-only chip (kanban) keeps its name in an `.sr-only` `[data-chip-label]` so a pick can't overwrite the avatar.
- _dropdown P4_: `actions.dropdown` (global). 69 core dropdowns in 47 Blade files migrated by script
  (wrapper/trigger/menu classes diffed against the variant's base; remaining classes/attrs passed through), the
  Blade-control-flow ones (guarding `@if`, `@php`, `@if/@else` triggers) by a second pass, the rest by hand (head
  menu, Dashboard round buttons, widget filters, tag popovers, invite link, kanban view menu). Verified by
  before/after DOM of every `.dropdown-toggle` parent on 21 pages: 214/305 byte-identical after normalising, the rest
  only intended deltas — `type="button"` on button triggers, `href="javascript:void(0);"` on hx triggers,
  `aria-hidden` on ⋮ icons, `onclick="event.stopPropagation()"` on keep-open panels (replaces the head menu's jQuery
  binder; the user-invite panel's `noClickProp` never had a handler, now it stays open as intended), `viewDropDown` on
  the to-do/calendar widget and roadmap timeframe filters (same right-alignment they already had). Empty wrappers
  around a guarded menu (non-editors on canvas/idea cards, canvas filters with no boards) are no longer rendered,
  and the `&nbsp;` spacer inside idea/blueprint card ⋮ menus is gone (aligns them with every other card).
  `subjectSwitcher` composes `variant="subject"`; projectHub's client filter uses the same shape (`as="div"`).
  Dashboard: the widget shell is one partial (`widgets::partials.widgetShell`) rendered by the dashboard and by
  `Hxcontrollers\WidgetShell` when the widget manager turns a widget on — the JS string copy in
  `Widgetcontroller.js` is gone (it also printed the untranslated, unescaped widget name), and the ⋮ handlers are
  delegated, so a newly added widget's Hide/Resize work without a reload. Vestigial `data-toggle` removed from
  `onboardingProgress` (×4) and `importProgress`; `dropdownPill` deleted (0 call-sites). **Deferred:** `loginInfo`
  (plugin hook `afterUserinfoMenuOpen` sits inside the wrapper), `stopwatch` (htmx-swapped `li` with conditional
  content), project checklist step popovers, `projectSelector`, Wiki icon picker, Files uppy JS-string menus, plugin
  pages (P6).
