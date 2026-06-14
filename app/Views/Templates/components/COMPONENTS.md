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
| button | `forms.button` | forms | 🟡 | refactor/table-component | first pilot; no-op |
| text-input | `forms.text-input` | forms | ⬜ | refactor/table-component | |
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
- **`class="button"` (not `btn`)** — many form submit inputs use `.button`. Need to confirm
  whether `.button` styling == `.btn` in forms.css; if so, add it to the component/migration.
- **Unstyled `<input type="submit">`** (no class) — adding `btn` is a *design* change, not a no-op. Defer.
- **Unmapped btn variants** — `btn-sm`/`btn-lg` (vs Leantime `btn-small`/`btn-large`),
  `btn-danger-outline`, `btn-circle`, `btn-inverse`, `btn-file`. Add mappings (after confirming CSS) or keep deferred.
- **role+state combo** (`btn btn-default btn-success`) — component currently emits one color; allow coexistence.
- ~~`<a onclick>` without `href`~~ — DONE: component emits `href` only when `link` is set; migrate these by omitting the `link` prop.
- **dropdown-toggle / data-toggle / fileupload / span.btn** — handled in the dropdown / file-upload / later phases.

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
