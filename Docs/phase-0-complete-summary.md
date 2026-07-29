# Leantime UI Modernization: Phase 0 Complete Summary

## Claude Code Execution Prompt

```
You are executing Phase 0 of the Leantime UI Modernization project. This is a foundation-setup phase — no component migration yet, just upgrading the toolchain.

BEFORE DOING ANYTHING: Read these documents in order:
1. Docs/leantime-4.0-dev-unique-changes.md — What 4.0-dev changed
2. Docs/leantime-plugin-changes-forward-port.md — Plugin ecosystem gaps
3. Docs/leantime-mobile-backend-compatibility.md — Mobile API surface (DO NOT BREAK)
4. Docs/phase-0-requirements.md — Detailed requirements with validation checklists

PHASE 0 TASKS (execute in this exact order):

0.1 LARAVEL 12 UPGRADE [2-4 hours]
- Update composer.json: "laravel/framework": "^12.0"
- Run composer update laravel/framework --with-all-dependencies
- Verify Carbon 3.x (already at 3.10.1)
- Run: php artisan test
- Grep-verify no breaking changes hit us:
  grep -rn "?.*\$.*= null" --include="*.php" app/ | grep "__construct"
  grep -rn "Schema::getTables\|Schema::getTableListing\|Schema::getViews\|HasUuids" --include="*.php" app/
- Test JSON-RPC endpoint still works (mobile app depends on it)

0.2 BUILD PIPELINE: LARAVEL MIX → VITE [1-2 days]
- npm install --save-dev vite laravel-vite-plugin
- npm uninstall laravel-mix
- Convert webpack.mix.js → vite.config.js (map all entry points)
- Update package.json scripts: dev → vite, build → vite build
- Replace all mix() calls in Blade with @vite():
  grep -rn "mix(" --include="*.php" --include="*.blade.php" resources/ app/
- Keep webpack.mix.js renamed as .webpack.mix.js.bak for rollback
- Test: npm run dev (HMR), npm run build (production)

0.3 TAILWIND CSS 3.4 → 4.x [4-8 hours]
- npm install tailwindcss@latest
- Convert tailwind.config.js to CSS-native @import/@plugin model
- KEEP tw- prefix during Phase 0: @import "tailwindcss" prefix(tw-);
- Verify existing tw-* classes still work across all pages

0.4 DAISYUI 5 INSTALLATION [2-4 hours]
- npm install daisyui@latest
- Add @plugin "daisyui" to main CSS file
- Set up dual-mode (DaisyUI alongside existing Bootstrap/custom CSS)
- Create dev-only component preview route
- CLASS NAME CHANGES from 4.0-dev era:
  form-control → fieldset/label
  card-compact → card-sm
  bottom-nav → dock
  avatar online → avatar avatar-online
  dropdown → dropdown + popover attribute

0.5 HTMX 1.9.12 → 2.0.8 + IDIOMORPH [4-8 hours]
- npm install htmx.org@latest
- Install idiomorph extension
- Check for data-hx-* attributes (HTMX 2.x default changed):
  grep -rn "data-hx-" --include="*.php" --include="*.blade.php" --include="*.tpl.php" resources/ app/
- Test all 8 HxController domains still work
- Test MyToDos infinite scroll

0.6 COMPONENT INFRASTRUCTURE [2-4 hours]
- Create app/Views/Components/ subdirectories: actions/, content/, elements/, forms/, navigations/
- Establish anonymous component pattern with @props
- Update CLAUDE.md with component conventions, DaisyUI 5 classes, HTMX 2.x patterns

0.7 REFERENCE MODAL COMPONENT [2-4 hours]
- Create app/Views/Components/modal.blade.php using native <dialog> + DaisyUI
- Add HTMX open/close event pattern
- Add to component preview page

CRITICAL CONSTRAINTS:
- DO NOT change any service method signatures in app/Domain/*/Services/
- DO NOT rename any data model fields (mobile app depends on exact field names)
- DO NOT remove the AdvancedAuth plugin or change its API
- DO NOT change the JSON-RPC routing in app/Domain/Api/Controllers/Jsonrpc.php
- DO NOT modify event hook names (auth→authcheck already happened on v3.6.2)
- Mailer uses PHPMailer directly (app/Core/Mailer.php), not Laravel Mail — leave it alone
- All 33+ plugins must continue loading after every change

VALIDATION: After each step, run the checklist in Docs/phase-0-requirements.md.
```

---

## What This Document Is

This is a consolidated reference compiling ALL research, analysis, and decisions made during the Leantime UI Modernization planning sessions. It synthesizes:

1. **Codebase assessment** — 4.0-dev branch analysis, v3.6.2 mainline state, component gap analysis
2. **Technology stack review** — Version gaps, breaking changes, upgrade paths
3. **Mobile backend compatibility audit** — API surface, auth flow, field name dependencies
4. **Plugin ecosystem analysis** — 926-commit gap, 7 new plugins, event hook changes
5. **Laravel 12 upgrade validation** — Breaking change impact, deployment surface review
6. **Component design system** — From the Component_Updates_Tracker spreadsheet: component inventory, properties, frontend libraries, guidelines
7. **Phase 0 requirements** — Detailed execution plan with validation checklists

---

## 1. Project Overview

### Current State (v3.6.2 mainline)

Leantime is an open-source project management app on Laravel 11. The codebase has two parallel efforts:

- **Mainline (v3.6.2):** Production branch. 46% Blade migration complete (205 Blade vs 237 legacy .tpl.php). 15 shared components. 8 of 56 domains have HTMX controllers. 70MB JS bundles, 18.7MB per-page load.
- **4.0-dev:** Feature branch, 693 commits behind master. Contains 65 well-structured Blade components and HTMX patterns. Serves as a design reference library, NOT mergeable code.

### The 4.0-dev Forward-Port Strategy

Rather than merging 4.0-dev (impossible — too many conflicts), we forward-port the *designs and patterns* from 4.0-dev onto the current mainline. This means:

- Use 4.0-dev's 65 components as reference designs
- Rebuild them using modern tooling (DaisyUI 5, HTMX 2.x, Tailwind 4)
- Respect v3.6.2's current infrastructure (plugins, auth, API surface)

### Timeline

4 phases over 16 weeks (4-5 months part-time). Claude Code handles 60-80% mechanical work.

| Phase | Weeks | Focus |
|---|---|---|
| **Phase 0** | 1-2 | Foundation: Laravel 12, Vite, Tailwind 4, DaisyUI 5, HTMX 2.x |
| Phase 1 | 3-4 | Modal system replacement (37 files), HTMX hx-boost navigation |
| Phase 2 | 5-8 | Core domain components (tickets, projects, dashboard, settings) |
| Phase 3 | 9-12 | JS optimization (TinyMCE removal, bundle splitting), canvas domains |
| Phase 4 | 13-16 | Remaining domains, CSS cleanup (Bootstrap removal) |

---

## 2. Architecture Constraints

### 2.1 Mobile App API Surface — DO NOT BREAK

The React Native/Expo mobile app communicates via:

1. **JSON-RPC** at `/api/jsonrpc` — All data operations
2. **Direct HTTP** — Authentication (`/advancedAuth/getToken`, `/advancedAuth/mobileStatus`)

The JSON-RPC controller (`app/Domain/Api/Controllers/Jsonrpc.php`) resolves services via `app()->make($serviceName)->$methodName(...$preparedParams)`. It uses reflection to match named params to method parameters.

**89 RPC methods across 7 domains:**

| Domain | Methods | Critical Methods |
|---|---|---|
| Tickets | 20 | patch, quickAddTicket, getOpenUserTicketsThisWeekAndLater, getAllSubtasks |
| Projects | 18 | getProjectsAssignedToUser, changeCurrentSessionProject, getProjectHierarchyAssignedToUser |
| Calendar | 8 | getCalendar, getExternalCalendarEvents, addEvent, editEvent |
| Comments | 5 | getComments, addComment (⚠️ parameter ordering concerns) |
| Users | 15 | getUser, getUsersWithProjectAccess (⚠️ signature mismatches) |
| Timesheets | 13 | logTime, punchIn/punchOut, isClocked, pollForNewTimesheets |
| Notes | 10 | patchCanvasItem, patchCanvas (may be v3.6.2 additions) |

**Known parameter quirks:**
- `oderId` typo used as param name in multiple mobile app calls (legacy naming, do not "fix")
- Comments `getComments()` expects `($module, $entityId, ...)` but mobile sends `{ moduleId, module }`
- Users `getUsersWithProjectAccess` expects `($currentUser, $projectId)` but mobile sends `{ projectId }` only

**Data model field names consumed by mobile app** (any rename = broken app):
- Tasks: `id`, `headline`, `description`, `projectId`, `status` (numeric), `priority` (numeric 1-5), `storyPoints`, `hourRemaining`, `planHours`, `dateToFinish`, `editFrom`, `editTo`, `editorId`, `editorFirstName`/`editorFirstname`, `milestoneId`/`milestoneid`, `milestoneHeadline`, `milestoneColor`, `tags`, `createdAt`, `updatedAt`
- Projects: `id`, `name`, `clientId`, `clientName`, `color`
- Users: `id`, `firstName`, `lastName`, `email`, `role`, `profileId`

### 2.2 Plugin Ecosystem

**33+ plugins must continue working.** 7 new plugins added after 4.0-dev branched:

| Plugin | Files | Risk | Notes |
|---|---|---|---|
| Copilot | 141 | CRITICAL | Full AI agent framework, AWS Bedrock, hooks into event system |
| McpServer | 35 | CRITICAL | MCP server for AI tools, uses @UnifiedTool/@Parameter attributes |
| AdvancedAuth | 28 | CRITICAL | OAuth/SSO + mobile app token auth — mobile app REQUIRES this |
| Reactions | 20 | LOW | Emoji reactions |
| CalDAV | 18 | LOW | Calendar sync |
| RecurringTasks | 16 | LOW | Recurring task automation |
| EnergyTracker | 1 | LOW | Minimal |

**Event hook renames:** `leantime.core.middleware.auth.*` → `leantime.core.middleware.authcheck.*`. Plugins using old hook names silently fail.

**Plugin submodule:** 4.0-dev pins commit 3156738, v3.6.2 pins 5ed0b93c (926 commits ahead). Clean fast-forward possible — no divergence.

### 2.3 Deployment Surface

| Method | Details | Laravel 12 Impact |
|---|---|---|
| Docker | PHP 8.3-fpm-alpine, nginx, supervisor | None (PHP 8.3 compatible) |
| Docker Compose | MySQL 8.4 + Leantime | None |
| Laravel Herd/Valet | Local dev | None |
| Traditional hosting | Apache/nginx + PHP | None |
| MySQL | Primary DB | None (Schema::create only) |
| PostgreSQL | Supported via env var | None |

**Email:** PHPMailer directly (`app/Core/Mailer.php`), NOT Laravel Mail. Configured via `LEAN_EMAIL_*` env vars. Completely unaffected by Laravel upgrades.

**External integrations:** S3, OIDC, LDAP, Redis, CalDAV, AWS Bedrock, MCP, Google Calendar/iCal — all use standard Laravel APIs, no breaking changes.

---

## 3. Technology Stack: Current vs Target

### 3.1 Version Gap Analysis

| Dependency | Current (v3.6.2) | Target (Phase 0) | Breaking Changes |
|---|---|---|---|
| Laravel | ^11.44 | ^12.0 | Minimal — maintenance release. Nullable resolution, Schema method defaults. None affect Leantime. |
| Carbon | 3.10.1 | 3.x | Already compatible ✅ |
| PHP | 8.3 (Docker) | 8.3 | No change needed |
| Tailwind CSS | ^3.4.1 | 4.x | Ground-up rewrite, CSS-native config. Class names backward compatible. |
| DaisyUI | not installed | 5.5.x | New install. Class names differ from 4.0-dev era (see §3.2). |
| HTMX | ^1.9.12 | ^2.0.8 | data- prefix default changed. Morph swap via Idiomorph added. |
| Build tool | Laravel Mix ^6.0.49 | Vite (latest) | Complete migration. mix() → @vite() in Blade. |
| TinyMCE | ^5.10.9 | ^5.10.9 (remove Phase 3) | EOL but keep for now. TipTap migration already covers highest-usage editors. |
| MySQL | 8.4 | 8.4 | No change |

### 3.2 DaisyUI Class Name Migration (4.0-dev → v5)

4.0-dev components were built with DaisyUI 3.x/4.x conventions. DaisyUI 5 renamed several classes:

| 4.0-dev Class | DaisyUI 5 Class | Notes |
|---|---|---|
| `form-control` | `fieldset` / `label` | Semantic HTML components |
| `card-compact` | `card-sm` | Size modifier renamed |
| `bottom-nav` | `dock` | Component renamed |
| `avatar online` | `avatar avatar-online` | State modifier prefixed |
| `avatar offline` | `avatar avatar-offline` | State modifier prefixed |
| dropdown (CSS-only) | dropdown + `popover` attr | Native HTML popover |

### 3.3 HTMX 2.x New Patterns

```
hx-on:click="..."              — Event attribute (replaces hx-on="click: ...")
hx-swap="morph"                — Morph swap (preserves DOM state, requires Idiomorph)
hx-swap="morph:innerHTML"      — Morph inner content only
hx-sync="closest form:abort"   — Race condition prevention
hx-trigger="revealed"          — Fire when scrolled into viewport
hx-trigger="changed"           — Fire only if value changed
hx-trigger="keyup delay:300ms" — Debounced input
```

**When to use morph swaps:** Form inputs (preserve focus), editors (TipTap), drag-drop areas, any element with interactive state.
**When NOT to use:** Simple content updates, list replacements, static content refreshes.

---

## 4. Component Design System (from Spreadsheet)

### 4.1 Component Inventory

The Component_Updates_Tracker spreadsheet defines the complete design system. Components are organized into categories with priority levels, status, and property definitions.

#### Global Simple Components (36 total)

**P0 — Must Have (8 components):**

| Component | Category | Status | Assigned | Key Properties |
|---|---|---|---|---|
| dropdown-menu | actions | Replacement in progress | Muhtasim | Child: dropdown-item |
| modal | actions | Component dev in progress | Marcel | Needs design ✅ |
| chip | actions | Not started | Marcel | type, variant[input/choice/action/select], color |
| card | elements | Component dev in progress | Marcel | Glasmorphism option, replaces .maincontentinner |
| button | forms | Replacement in progress | Marcel | type, leadingVisual, trailingVisual, state, element[a/input/button] |
| select | forms | Replacement in progress | Tanveer | variation[tags/single/multiple], remoteUrl, search |
| text-input | forms | Replacement in progress | Tanveer | label-text, caption, validation-text/state, leading/trailing-visual |
| tabs | navigation | Replacement in progress | Tanveer | Child: tab. Needs design. |

**P0 — Special Components (2):**

| Component | Category | Status | Notes |
|---|---|---|---|
| text-editor | forms | Not started | TinyMCE replacement (TipTap migration in progress) |
| date-picker | forms | Component dev in progress | Date picker + date ranges. Needs design. |

**P1 — Should Have (18 components):**

| Component | Category | Status |
|---|---|---|
| avatar | elements | Component dev in progress |
| accordion | elements | Component dev in progress |
| table | elements | Not started |
| empty-state | elements | Component dev in progress |
| date-info | elements | Replacement in progress |
| code | elements | Component dev in progress |
| statistic | elements | Component dev in progress |
| badge | elements | Component dev in progress |
| checkbox | forms | Replacement in progress |
| radio | forms | Component dev in progress |
| toggle | forms | Not started |
| button-group | forms | — |
| steps | navigation | Component dev in progress |
| breadcrumbs | navigation | Not started |
| pagination | navigation | Component dev in progress |
| alert | feedback | Not started |
| loading | feedback | Not started |
| progress | feedback | Not started |
| skeleton | feedback | Not started |
| indicator | feedback | Not started |
| color-picker | forms | Not started |
| page-header | layout | Not started |
| select-panel | action | Not started |
| context-menu | navigation | Replacement in progress |
| form-field | forms | Not started |

**P2 — Nice to Have (7 components):**
chat bubble, keyboard, calendar, range, textarea, file-input, emoji-input, menu, navbar

#### Domain-Specific Components

| Component | Domain | Notes |
|---|---|---|
| comment list | comments | — |
| ticket-card | tickets | — |
| milestone-card | tickets | — |
| ticket-state-label | tickets | — |
| projectCard | projects | — |

### 4.2 Shared Component Properties (IDL Attributes)

The design system defines a consistent property interface across all components:

| Property | Options | Default | Purpose |
|---|---|---|---|
| `content-role` | default, primary, secondary, ghost/tertiary, accent, link | primary (actions) | Visual importance |
| `state` | default, info, warning, danger, success | default | Semantic state |
| `variant` | component-specific | "" | Behavior variations |
| `scale` | xs, s, m, l, xl | m | Size |
| `position` | left, right, top, bottom, inner, outer, start, end | bottom | Placement |
| `element` | a, input, button | — | HTML element override |
| `align` | start, end | — | Alignment |
| `label-text` | text | "" | Label content |
| `caption` | text | "" | Sub-label text |
| `validation-text` | text | "" | Validation message |
| `leading-visual` | icon | "" | Icon before content |
| `trailing-visual` | icon | "" | Icon after content |
| `items` | list | [] | Data items |
| `link` | URL | "#" | Navigation target |

### 4.3 Component Development Checklist (from Guidelines)

Every component MUST satisfy:

- [ ] Uses only valid HTML attribute names
- [ ] Can be reused by various modules
- [ ] Can accept any type of data/content
- [ ] Merges attributes (`$attributes->merge()`)
- [ ] Has default states
- [ ] Fails gracefully if data or attributes are wrong
- [ ] Does not require "design input" from component user (attributes focus on content type, not style)
- [ ] No business logic
- [ ] Is responsive
- [ ] Only Tailwind CSS & DaisyUI CSS (no custom CSS without approval)
- [ ] JS assets loaded as part of component
- [ ] CSS assets loaded as part of component
- [ ] Uses only approved JS libraries
- [ ] Does not require developer to "prepare data for component" (handles data as provided by controller)
- [ ] Has appropriate aria-* attributes
- [ ] Follows WCAG guidelines
- [ ] Works in modals and regular pages
- [ ] All HTML elements replaced with component usage

**Decision tree for "Do I need a component?":**
1. Is this a one or two-off instance? → No component needed
2. Can it be added as a variant to an existing component? → Create variant instead
3. Otherwise → Create component

---

## 5. Frontend Libraries Inventory

### 5.1 Keep (Approved)

| Library | Version | Purpose |
|---|---|---|
| HTMX | ^1.9.12 → ^2.0.8 | Core interaction framework |
| Tailwind CSS | ^3.4.1 → 4.x | CSS framework |
| DaisyUI | NEW → 5.5.x | Component CSS library |
| FullCalendar | ^6.1.11 | Calendar visualization |
| Tippy.js | ^6.3.7 | Tooltips |
| Popper.js | ^2.11.8 | Tooltip positioning (Tippy dep) |
| Chart.js | ^3.6.0 | Report charts |
| Lottie Player | ^2.0.4 | Lottie animations (AI robot) |
| Canvas Confetti | ^1.9.3 | Confetti animation |
| Croppie | ^2.6.5 | Profile picture cropping |
| Shepherd.js | ^11.2.0 | Onboarding tours |
| Uppy | ^3.25.3 | File uploads |
| jQuery | 3.7.1 | Legacy dependency |
| Gridstack | ^10.1.2 | Dashboard widget layout |
| JSTree | ^3.3.16 | Wiki tree view |
| Leader Line | ^1.0.7 | Kanban dependency arrows |
| Luxon | ^3.4.4 | Date formatting |
| Masonry/Isotope | ^4.2.2 / ^3.0.6 | Pinterest-style layouts |
| Packery | ^2.1.2 | Idea board drag-drop grid |
| Frappe Gantt | 0.6.1 | Gantt charts (heavily modified) |
| Prism.js | — | Syntax highlighting |
| Choices.js | — | Select dropdowns (forked) |
| Excalidraw | v0.16.1 | Whiteboard plugin |
| Sentry | ^7.116.0 | Error tracking |
| FontAwesome Free | ^6.5.2 | Icons |

### 5.2 Replace Soon

| Library | Replace With | Notes |
|---|---|---|
| TinyMCE 5.10.9 | TipTap | EOL. TipTap migration covers todos, wikis, notes, comments. Remaining: canvas dialogs, file descriptions, ticket fields. |
| NyroModal | Native `<dialog>` + DaisyUI | Phase 1 — 37 files use legacy jQuery modal. Heavily modified fork. |
| Datatables | TBD | Sorting/paging tables |
| Frappe Gantt | TBD | Heavily modified, needs more powerful replacement |
| FontAwesome Iconpicker | Blade UI Icons | Wiki article icon picker |

### 5.3 Replace with Existing Library

| Library | Replace With |
|---|---|
| Chosen.js | Choices.js |
| jQuery Tags | Choices.js |
| SlimSelect | Choices.js |
| Select2 | Choices.js |
| Moment.js | Luxon |

### 5.4 Remove

| Library | Reason |
|---|---|
| Bootstrap Fileupload (3.0) | Legacy |
| jQuery Forms | Not used |
| Simple Color Picker | Use browser default |

### 5.5 To Be Discussed

| Library | Notes |
|---|---|
| jQuery UI | Used for datepickers, tabs, progress bars, accordion, drag-drop. Partially replaceable with DaisyUI components. |
| jQuery Touch Punch | May not be needed anymore |
| ajv (JSON schema) | Unclear if still used |
| imagesloaded | Unclear if still needed |

---

## 6. 4.0-dev Remaining Task List Status

### 6.1 Task Summary by Person

| Person | Role | Status |
|---|---|---|
| Marcel | Lead — infrastructure, special components | Multiple in-progress (plugins, project selector, datepicker, ticket view, canvases, wiki, files) |
| Muhtasim | UI components — forms, dropdowns, filters | Most tasks done. In-progress: goal add/edit, install route |
| Tanveer | UI components — inputs, badges, filters | Most tasks done. PRs merged. |

### 6.2 Not-Started Tasks (still need work)

| Task | Location | Notes |
|---|---|---|
| First onboarding | — | P0, Marcel |
| AI onboarding | — | P0, Marcel |
| Project dashboard milestone cards | project dashboard | Should be a component |
| Milestone cards not loading HTMX | project dashboard | — |
| Project updates → comments component | project dashboard | Marcel |
| Milestone calendar broken | milestone calendar | — |
| Goal modal metric → regular input | goal modal | — |
| Goal modal buttons (one secondary) | goal modal | — |
| Blueprint card headlines → primary color | — | Marcel |
| Canvases not working | canvas | Marcel |
| Wiki not working | — | Marcel |
| Files | — | Marcel |
| Project settings 403 | project | Marcel |

### 6.3 Known Bugs (from spreadsheet)

| Module | Issue |
|---|---|
| Dashboard | Error — page does not load, only skeleton code |
| Timesheets | Dropdown select options UI issue |
| Timesheets | Week/List view out of page bounds |
| Calendar | Button sizing, primary/secondary color |
| Calendar | Add event — date-time input type fix |
| Company → All Clients | Missing @endsection |
| Company → User Mgmt | Missing @endsection |
| Company → Integrations | Missing @endsection |
| Home dashboard | Broken (Marcel) |
| Project menu | Broken (Marcel) |
| Task context menu | Not opening |
| Delete | May not work |
| View selector | Milestone gantt view |

---

## 7. TinyMCE → TipTap Migration Status

TipTap migration already in progress (pending merge). Highest-usage editors done:

| Area | Status |
|---|---|
| To-dos | ✅ Migrated to TipTap |
| Wikis | ✅ Migrated to TipTap |
| Notes | ✅ Migrated to TipTap |
| Comments | ✅ Migrated to TipTap |

**Still on TinyMCE (26 files):**
- Canvas dialog (`canvasDialog.inc.php`) — shared by all 15+ canvas types
- File descriptions
- Ticket additional fields
- Dashboard status updates

**Infrastructure cleanup (after all editors migrated):**
- `editors.js` (~400 lines TinyMCE config) → remove
- `modals.js` (TinyMCE save/destroy on modal transitions) → remove coupling
- `webpack.mix.js` (~30 TinyMCE plugin files) → remove from bundle
- `EditorTypeEnum.php` → simplify
- **3.5MB bundle savings** from removing TinyMCE

**Critical coupling:** `modals.js` has `beforePostSubmit` and `beforeShowCont` callbacks that call `tinymce.get()`. Once TipTap is everywhere AND modals use native `<dialog>`, this coupling dissolves.

---

## 8. Key File Locations

```
# Build system
webpack.mix.js                    — Current build config (→ vite.config.js)
package.json                      — JS dependencies
composer.json                     — PHP dependencies
tailwind.config.js                — Current Tailwind config (→ CSS-native)

# Core infrastructure
app/Core/Mailer.php               — Email (PHPMailer, NOT Laravel Mail)
app/Core/Events/DispatchesEvents.php — Event system trait
app/Core/Events/EventDispatcher.php  — Static event dispatcher

# API surface (mobile app — DO NOT TOUCH)
app/Domain/Api/Controllers/Jsonrpc.php — JSON-RPC router
app/Plugins/AdvancedAuth/             — Mobile app authentication

# Installation
app/Domain/Install/Repositories/Install.php — DB migrations
app/Domain/Install/Services/SchemaBuilder.php — Schema creation

# Docker
.docker/Dockerfile                — Production image (PHP 8.3)
.docker/docker-compose.yml        — MySQL 8.4 + Leantime
.docker/config/                   — nginx, php-fpm, supervisor

# Components
app/Views/Components/             — Shared Blade components (15 → 65 target)

# Plugins (must all keep working)
app/Plugins/AdvancedAuth/         — OAuth/SSO + mobile tokens
app/Plugins/Copilot/              — AI agent (141 files)
app/Plugins/McpServer/            — MCP server (35 files)
app/Plugins/Notes/                — Notes/notebooks (mobile app uses)
```

---

## 9. Useful Grep Commands

```bash
# mix() references (need → @vite)
grep -rn "mix(" --include="*.php" --include="*.blade.php" resources/ app/

# HTMX data- prefix (2.x migration)
grep -rn "data-hx-" --include="*.php" --include="*.blade.php" --include="*.tpl.php" resources/ app/

# Laravel 12 breaking changes
grep -rn "?.*\$.*= null" --include="*.php" app/ | grep "__construct"
grep -rn "Schema::getTables\|Schema::getTableListing\|Schema::getViews\|HasUuids" --include="*.php" app/

# TinyMCE references (Phase 3)
grep -rn "tinymce\|TinyMCE\|tiny_mce" --include="*.php" --include="*.js" --include="*.blade.php" resources/ app/ | grep -v vendor | grep -v node_modules

# Event hook compatibility
grep -rn "middleware.auth\." --include="*.php" app/Plugins/

# Tailwind prefix tracking
grep -rn "tw-" --include="*.php" --include="*.blade.php" resources/ app/ | wc -l

# HxControllers inventory
find app/ -name "*.php" -path "*/Hxcontrollers/*" | sort

# NyroModal usage (Phase 1)
grep -rn "nyroModal\|nyromodal" --include="*.php" --include="*.js" --include="*.blade.php" resources/ app/ | grep -v vendor | grep -v node_modules
```

---

## 10. Design System References

From the spreadsheet's Helpful Resources:

| Resource | URL | Use For |
|---|---|---|
| DaisyUI | https://daisyui.com/docs/install/ | Primary component CSS |
| GitHub Primer | https://primer.style/ | Reference design system |
| Material Design 3 | https://m3.material.io/components/chips/overview | Chip/chip patterns |
| Atlassian Design | https://atlassian.design/components | PM-specific patterns |
| Blade UI Kit | https://blade-ui-kit.com/ | Laravel Blade components |
| BladewindUI | https://bladewindui.com/ | Blade component examples |
| Razor UI | https://razorui.com/libraries/blade-application-ui/dropdowns | Blade dropdown patterns |
| Material Tailwind | https://www.material-tailwind.com/ | Tailwind component patterns |
| Component Decision Trees | https://www.smashingmagazine.com/2024/05/decision-trees-ui-components/ | When to componentize |
| MDN HTML Attributes | https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes | Attribute reference |
| WCAG Guidelines | https://developer.mozilla.org/en-US/docs/Web/Accessibility/Understanding_WCAG | Accessibility |
| ARIA Guide | https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/ARIA_Guides | Accessibility |
