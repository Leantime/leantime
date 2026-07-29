# Leantime 4.0 UI Modernization — Complete Project Summary

## For Claude Code Execution

**Project:** Leantime UI Modernization Forward-Port
**Scope:** Forward-port 4.0-dev component work onto v3.6.2 mainline + modernize infrastructure
**Total Timeline:** 16 weeks (4 phases), ~4-5 months part-time
**Generated:** February 12, 2026

---

## 1. What This Project Is

Leantime has two diverged branches:

- **v3.6.2 (mainline)** — Production. 693 commits ahead of 4.0-dev. Has new plugins (Copilot AI, MCP Server, AdvancedAuth, CalDAV, Reactions, RecurringTasks), mobile app API, and infrastructure improvements.
- **4.0-dev** — Design reference. 65 well-structured Blade components, HTMX patterns, DaisyUI integration, native `<dialog>` modals, ES module JS. NOT mergeable — too far behind.

The project forward-ports the **design and component patterns** from 4.0-dev onto v3.6.2's codebase while simultaneously upgrading the infrastructure stack.

---

## 2. Current State Assessment

### Template Migration Progress
- 46% Blade migration complete: 205 `.blade.php` vs 237 legacy `.tpl.php`
- 15 shared components on mainline vs 65 on 4.0-dev (50 component gap)
- 8 of 56 domains have HTMX controllers

### Critical Pain Points
1. **Modal system** — 37 files use legacy jQuery/nyroModal. 4.0-dev has native `<dialog>` reference.
2. **Full page reloads** — HTMX hx-boost can solve; morph swaps (HTMX 2.x) prevent content staleness.
3. **JS bundle size** — 70MB total, 18.7MB per-page load. TipTap 6.8MB, TinyMCE 3.5MB (redundant/EOL), global components 5.1MB.
4. **CSS complexity** — Bootstrap 2.x + custom CSS + Tailwind (tw- prefix) coexist.

### 4.0-dev Remaining Task List (from Component Tracker)

**Workload summary:**
- Muhtasim: 26 tasks, 24 done
- Tanveer: 13 tasks, 13 done
- Marcel: 14 tasks, 0 done ← these are the unfinished items

**Marcel's unfinished tasks (carried forward to this project):**
- Ensure all plugins work
- First onboarding + AI onboarding
- Project selector (menu tree)
- Project updates (replace with comments component)
- Goal chip for status
- Ticket view improvements
- Datepicker (everywhere)
- Blueprint cards headlines
- Canvases not working
- Wiki not working
- Files
- Project settings 403
- Home dashboard broken
- Project menu broken

**Outstanding bugs:**
- Dashboard: page does not load, only skeleton code
- Timesheets: dropdown select options UI issue; week/list views out of page
- Calendar: button sizing, primary/secondary color; add event date-time input
- Company pages (All Clients, User Mgmt, Integrations): missing `@endsection`
- Milestone calendar broken
- Task context menu not opening
- Delete may not work
- View selector on milestone gantt view

---

## 3. Component Design System

### Component Inventory (from Component Updates Tracker)

**Global Simple Components (35 total):**

| Component | Category | Tag | Priority | Status | Assigned |
|---|---|---|---|---|---|
| dropdown-menu | actions | `globals::actions.dropdown-menu` | P0 | Replacement in progress | Muhtasim |
| modal | actions | `globals::actions.modal` | P0 | Component dev in progress | Marcel |
| chip | actions | `globals::actions.chip` | P0 | Not started | Marcel |
| card | elements | `globals::elements.card` | P0 | Component dev in progress | Marcel |
| button | forms | `globals::forms.button` | P0 | Replacement in progress | Marcel |
| select | forms | `globals::forms.select` | P0 | Replacement in progress | Tanveer |
| text-input | forms | `globals::forms.text-input` | P0 | Replacement in progress | Tanveer |
| tabs | navigation | `globals::navigation.tabs` | P0 | Replacement in progress | Tanveer |
| form-field | forms | `globals::forms.form-field` | P0 | Not started | Tanveer |
| avatar | elements | `globals::elements.avatar` | P1 | Component dev in progress | Muhtasim |
| accordion | elements | `globals::elements.accordion` | P1 | Component dev in progress | Muhtasim |
| table | elements | `globals::elements.table` | P1 | Not started | Joe |
| empty-state | elements | `globals::elements.empty-state` | P1 | Component dev in progress | Marcel |
| date-info | elements | `globals::elements.date-info` | P1 | Replacement in progress | Marcel |
| code | elements | `globals::elements.code` | P1 | Component dev in progress | Tanveer |
| statistic | elements | `globals::elements.statistic` | P1 | Component dev in progress | Tanveer |
| badge | elements | `globals::elements.badge` | P1 | Component dev in progress | Tanveer |
| checkbox | forms | `globals::forms.checkbox` | P1 | Replacement in progress | Tanveer |
| radio | forms | `globals::forms.radio` | P1 | Component dev in progress | Tanveer |
| toggle | forms | `globals::forms.toggle` | P1 | Not started | Marcel |
| button-group | forms | `globals::forms.button-group` | P1 | — | Marcel |
| steps | navigation | `globals::navigation.steps` | P1 | Component dev in progress | Tanveer |
| breadcrumbs | navigation | `globals::navigation.breadcrumbs` | P1 | Not started | — |
| pagination | navigation | `globals::navigation.pagination` | P1 | Component dev in progress | Tanveer |
| alert | feedback | `globals::feedback.alert` | P1 | Not started | — |
| loading | feedback | `globals::feedback.loading` | P1 | Not started | — |
| progress | feedback | `globals::feedback.progress` | P1 | Not started | — |
| skeleton | feedback | `globals::feedback.skeleton` | P1 | Not started | — |
| indicator | feedback | `globals::feedback.indicator` | P1 | Not started | — |
| chat bubble | elements | `globals::elements.chat bubble` | P2 | Not started | — |
| keyboard | elements | `globals::elements.keyboard` | P2 | Not started | — |
| calendar | elements | `globals::elements.calendar` | P2 | Not started | — |
| range | forms | `globals::forms.range` | P2 | Not started | — |
| textarea | forms | `globals::forms.textarea` | P2 | Not started | — |
| file-input | forms | `globals::forms.file-input` | P2 | Not started | — |
| menu | navigation | `globals::navigation.menu` | P2 | Not started | — |
| navbar | navigation | `globals::navigation.navbar` | P2 | Not started | — |

**Global Special Components:**

| Component | Category | Tag | Priority | Status | Assigned |
|---|---|---|---|---|---|
| text-editor | forms | `globals::forms.text-editor` | P0 | Not started | Marcel |
| date-picker | forms | `globals::forms.date-picker` | P0 | Component dev in progress | Marcel |
| color-picker | forms | `globals::forms.color-picker` | P1 | Not started | — |
| page-header | layout | `globals::layout.pageheader` | P1 | Not started | — |
| emoji-input | forms | `globals::forms.emoji-input` | P2 | Not started | — |
| select-panel | action | `globals::action.select-panel` | P1 | Not started | — |
| context-menu | navigation | `globals::navigation.context-menu` | P1 | Replacement in progress | Muhtasim |

**Domain-Specific Components:**

| Component | Domain | Tag |
|---|---|---|
| list | comments | `globals::comments.list` |
| ticket-card | tickets | `globals::tickets.ticket-card` |
| milestone-card | tickets | `globals::tickets.milestone-card` |
| ticket-state-label | tickets | `globals::tickets.ticket-state-label` |
| projectCard | projects | — |


### Component Attribute System

Components use a standardized attribute system with these IDL (Interface Definition Language) attributes:

| Attribute | Options | Default | Purpose |
|---|---|---|---|
| `content-role` | default, primary, secondary, ghost/tertiary, accent, link | primary (actions) | Semantic role of the component |
| `state` | default, info, warning, danger, success | default | Visual state indicator |
| `variant` | component-specific | "" | Different behavior modes (e.g., chip: filter/action/select) |
| `scale` | xs, s, m, l, xl | m | Size scale |
| `position` | left, right, top, bottom, inner, outer, start, end | bottom | Placement relative to parent |
| `element` | a, input, button | — | HTML element override (e.g., cancel button → `<a>`) |
| `align` | start, end | — | Content alignment |

Content attributes shared across form components:
- `label-text`, `label-position` (top/left/right/bottom/inside)
- `caption` (descriptive text underneath)
- `validation-text`, `validation-state`
- `leading-visual`, `trailing-visual` (icons)
- `items` (data list for selects/dropdowns)

### Component Development Checklist (from Guidelines sheet)

Every component must satisfy:
- [ ] Uses only valid attribute names
- [ ] Can be re-used by various modules
- [ ] Can accept any type of data/content
- [ ] Merges attributes (via `$attributes->merge()`)
- [ ] Has default states
- [ ] Fails gracefully with wrong data/attributes
- [ ] No design input required from component user (content-focused attributes, not style)
- [ ] No business logic
- [ ] Is responsive
- [ ] Only Tailwind CSS & DaisyUI CSS (no additional CSS without approval)
- [ ] JS assets loaded as part of component
- [ ] CSS assets loaded as part of component
- [ ] Uses only approved JS components
- [ ] Does not require data preparation by developer (handles controller data directly)
- [ ] Has appropriate `aria-*` attributes
- [ ] Follows WCAG guidelines
- [ ] Works in modals and regular pages
- [ ] All legacy HTML elements replaced

---

## 4. Frontend Library Audit

### Libraries to KEEP (approved for component use)

| Library | Version | Used For |
|---|---|---|
| FullCalendar | ^6.1.11 | Calendar visualization |
| Lottie Player | ^2.0.4 | Animations (AI robot) |
| Tippy.js | ^6.3.7 | Tooltips |
| Popper.js | ^2.11.8 | Tooltip dependency |
| Canvas Confetti | ^1.9.3 | Confetti animation |
| Chart.js | ^3.6.0 | Report charts |
| Chart.js Luxon Adapter | ^1.3.1 | Chart date support |
| HTMX | ^1.9.12 → **^2.0.8** | HTMX support (upgrading) |
| Tailwind | ^3.4.1 → **4.x** | CSS framework (upgrading) |
| Uppy | ^3.25.3 | File uploads |
| Choices.js | forked | Select dropdowns (THE standard) |
| Frappe Gantt | 0.6.1 (modified) | Gantt charts |
| jQuery Growl | 1.3.5 | Notification tooltips |
| Croppie | ^2.6.5 | Profile picture cropping |

### Libraries to REPLACE SOON

| Library | Replace With | Notes |
|---|---|---|
| TinyMCE | TipTap | EOL. Migration in progress. Most high-usage editors already migrated. |
| NyroModal | Native `<dialog>` + DaisyUI | Heavy jQuery modal. 37 files to migrate. |
| FontAwesome Icon Picker | Blade UI Icons | Wiki articles only |
| Datatables | TBD | Tables across system with sorting/paging |
| Frappe Gantt | TBD (more powerful) | Heavily modified, functional but limited |

### Libraries to REMOVE

| Library | Notes |
|---|---|
| Bootstrap 3 fileupload | Legacy |
| jQuery Forms (jquery.form.js) | Not used |
| Simple Color Picker | Use browser default |

### Libraries to CONSOLIDATE (replace with Choices.js)

| Library | Current Use |
|---|---|
| Chosen JS | Select dropdowns |
| jQuery Tags | Tags input |
| SlimSelect | Another dropdown library |
| Select2 | Yet another dropdown library |

### Libraries to EVALUATE

| Library | Version | Question |
|---|---|---|
| jQuery UI | 1.13.2 | Used for datepickers, tabs, progress bars, accordion, drag-drop. Replace with DaisyUI/HTMX? |
| jQuery UI Touch Punch | ^0.2.3 | Still needed? |
| Ajv JSON validator | ^8.13.0 | Still in use? |
| imagesloaded | ^5.0.0 | Still needed? |
| Moment.js | ^2.29.4 | Replace with Luxon (already present) |

### Libraries to KEEP but NOT component-approved

jQuery (3.7.1), Gridstack (^10.1.2), Masonry (^4.2.2), Isotope (^3.0.6), Packery (^2.1.2), Leader Line (^1.0.7), JSTree (^3.3.16), Shepherd.js (^11.2.0), Prism.js, Sentry, Luxon (^3.4.4), iCal.js (^1.5.0), Excalidraw (v0.16.1)

---

## 5. Version Upgrades Required (Phase 0)

| Dependency | Current | Target | Risk | Notes |
|---|---|---|---|---|
| Laravel | ^11.44 | ^12.0 | LOW | Maintenance release. All breaking changes verified as non-impactful. |
| Carbon | 3.10.1 | 3.x | NONE | Already compatible |
| Build pipeline | Laravel Mix ^6.0.49 | Vite | MEDIUM-HIGH | Enables Tailwind 4 + ESM + tree-shaking |
| Tailwind CSS | ^3.4.1 | 4.x | MEDIUM | CSS-native config model. DaisyUI 5 requires this. |
| DaisyUI | not installed | 5.5.x | LOW | Additive. 63 components. Pure CSS. |
| HTMX | ^1.9.12 | ^2.0.8 + Idiomorph | LOW-MEDIUM | Morph swaps preserve DOM state during partial updates |
| TinyMCE | ^5.10.9 | REMOVE (Phase 3) | MEDIUM | EOL. TipTap migration mostly done. |

### DaisyUI 5 Class Name Changes (from 4.0-dev reference)

| Old (4.0-dev era) | New (DaisyUI 5) |
|---|---|
| `form-control` | `fieldset` / `label` |
| `card-compact` | `card-sm` |
| `bottom-nav` | `dock` |
| `avatar online` | `avatar avatar-online` |
| `avatar offline` | `avatar avatar-offline` |

---

## 6. Architecture Constraints — DO NOT BREAK

### Mobile App (React Native/Expo)

Communicates via JSON-RPC at `/api/jsonrpc`. The controller uses `app()->make()` to resolve services and reflection to map params by name.

**89 RPC methods** across 7 domains (Tickets: 20, Projects: 18, Users: 15, Timesheets: 13, Notes: 10, Calendar: 8, Comments: 5).

Auth requires AdvancedAuth plugin (v3.6.2 only, NOT in 4.0-dev): `POST /advancedAuth/getToken` → Bearer token for all API calls.

**Field names consumed by mobile** (renaming breaks the app silently):
- Tasks: `id`, `headline`, `description`, `projectId`, `status`, `priority`, `storyPoints`, `planHours`, `dateToFinish`, `editFrom`, `editTo`, `editorId`, `milestoneId`, `tags`
- Projects: `id`, `name`, `clientId`, `clientName`, `color`
- Users: `id`, `firstName`, `lastName`, `email`, `role`, `profileId`

Full method inventory: `Docs/leantime-mobile-backend-compatibility.md`

### Plugin Ecosystem (33+ plugins)

- Plugins submodule: 4.0-dev is 926 commits behind v3.6.2. Clean fast-forward.
- 7 new plugins not in 4.0-dev: Copilot (141 files), McpServer (35), AdvancedAuth (28), Reactions (20), CalDAV (18), RecurringTasks (16), EnergyTracker (1)
- Event hook rename: `middleware.auth.*` → `middleware.authcheck.*` (silent failure if wrong)
- DispatchesEvents: 4.0-dev uses Laravel Event facade, v3.6.2 uses EventDispatcher static. Must preserve backward compat.

### Deployment Surface

- Docker (PHP 8.3-fpm-alpine, nginx, MySQL 8.4)
- PostgreSQL support (via `LEAN_DB_DEFAULT_CONNECTION = 'pgsql'`)
- SMTP via PHPMailer (not Laravel Mail)
- S3, OIDC/OAuth, LDAP, Redis, CalDAV, AWS Bedrock (Copilot), MCP Server
- Installation via SchemaBuilder (`Schema::create()` — database-agnostic)

