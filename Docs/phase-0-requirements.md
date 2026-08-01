# Phase 0: Foundation Setup — Requirements Document

## For Claude Code Execution

**Project:** Leantime UI Modernization Forward-Port
**Branch:** `4.0-dev` forward-port onto `v3.6.2` mainline
**Phase:** 0 of 4 — Foundation Setup
**Estimated Duration:** ~1 week focused work
**Risk Level:** MEDIUM-HIGH (build pipeline migration is riskiest item)
**Generated:** February 12, 2026

---

## Context

Leantime is an open-source project management application built on Laravel 11. A `4.0-dev` branch (693 commits behind master) contains 65 well-structured Blade components and HTMX patterns that serve as a design reference library. The mainline (`v3.6.2`) has continued evolving with 46% Blade migration complete, new plugins (Copilot, McpServer, AdvancedAuth, Reactions, CalDAV, RecurringTasks, EnergyTracker), and infrastructure improvements.

Phase 0 establishes the modern foundation before any component forward-porting begins. Every subsequent phase depends on this work being solid.

### Codebase Location
- **Backend:** this repository's root
- **Mobile App:** the `leantime-mobile` repository (React Native/Expo), checked out as a sibling
- **Docker:** `.docker/Dockerfile` and `.docker/docker-compose.yml`
- **Dev Docker:** `.dev/docker-compose.yaml` and `.dev/dockerfile`

### Companion Documents (READ THESE FIRST)
- `Docs/leantime-4.0-dev-unique-changes.md` — What 4.0-dev changed and what to forward-port
- `Docs/leantime-plugin-changes-forward-port.md` — Plugin ecosystem gap analysis
- `Docs/leantime-mobile-backend-compatibility.md` — Mobile app API surface and breaking change risks

---

## Architecture Constraints

### Mobile App API Surface — DO NOT BREAK

The mobile app (React Native/Expo) communicates with the backend via two mechanisms:

1. **JSON-RPC** at `/api/jsonrpc` — All data operations (tasks, projects, users, calendar, timesheets, notes, comments)
2. **Direct HTTP** — Authentication only (`/advancedAuth/getToken`, `/advancedAuth/mobileStatus`)

The JSON-RPC controller (`app/Domain/Api/Controllers/Jsonrpc.php`) uses reflection to resolve service methods: `app()->make($serviceName)->$methodName(...$preparedParams)`. It maps named params from the request to method parameters by name (not position), checks types, and casts as needed.

**Critical: The JSON-RPC layer uses `app()->make()` (Laravel container resolution) to instantiate every service class.** Any change to service constructor signatures, method signatures, or return types will break the mobile app silently (returns RPC errors instead of data).

The following RPC domains are actively used by the mobile app:
- **Tickets** — 20 methods (CRUD, subtasks, milestones, status/priority/effort labels, scheduling, polling)
- **Projects** — 18 methods (CRUD, hierarchy, roles, avatars, types, session switching)
- **Calendar** — 8 methods (events CRUD, external calendars, iCal URL)
- **Comments** — 5 methods (CRUD, polling) — has parameter ordering concerns
- **Users** — 15 methods (CRUD, profile pictures, settings, password checks)
- **Timesheets** — 13 methods (time logging, punch clock, polling)
- **Notes** — 10 methods (plugin — canvas CRUD, notebooks)

**Auth depends on AdvancedAuth plugin** (new in v3.6.2, does NOT exist in 4.0-dev). Login flow: `POST /advancedAuth/getToken` returns `{ id, token }`. All subsequent API calls use `Authorization: Bearer {token}` headers.

**Data model field names the mobile app consumes** (any rename breaks the app):
- Tasks: `id`, `headline`, `description`, `projectId`, `status`, `priority`, `storyPoints`, `hourRemaining`, `planHours`, `dateToFinish`, `editFrom`, `editTo`, `editorId`, `milestoneId`, `tags`, `createdAt`, `updatedAt`
- Projects: `id`, `name`, `clientId`, `clientName`, `color`
- Users: `id`, `firstName`, `lastName`, `email`, `role`, `profileId`

### Installation & Deployment Surface

1. **Docker** (`.docker/Dockerfile`) — PHP 8.3-fpm-alpine, nginx, supervisor.
2. **Docker Compose** (`.docker/docker-compose.yml`) — MySQL 8.4 + Leantime container.
3. **Dev Docker** (`.dev/docker-compose.yaml`) — Development environment.
4. **Laravel Herd / Valet** — Local development.
5. **Traditional hosting** — Apache/nginx + PHP + MySQL/PostgreSQL.

Database: MySQL (primary), PostgreSQL (via `LEAN_DB_DEFAULT_CONNECTION = 'pgsql'`). Schema creation via `Schema::create()` (database-agnostic).

### Email / SMTP

**PHPMailer** directly (not Laravel Mail): `app/Core/Mailer.php`. Not affected by Laravel 12 upgrade.

### External Integrations

S3, OIDC/OAuth, LDAP, Redis, CalDAV, AWS Bedrock (Copilot), MCP Server, Google Calendar/iCal.

### Event System

WordPress-style hook system (`DispatchesEvents.php` / `EventDispatcher.php`), NOT Laravel events. 4.0-dev rewrote to use Laravel Event facade — forward-port must preserve backward compatibility. Event hook rename on v3.6.2: `auth.*` → `authcheck.*`.

---

## Phase 0 Requirements

### 0.1 Laravel 12 Upgrade

**Priority:** FIRST | **Risk:** LOW | **Effort:** 2-4 hours

#### What to do
1. Update `composer.json`: `"laravel/framework": "^12.0"`
2. `composer update laravel/framework --with-all-dependencies`
3. Verify Carbon v3 (already 3.10.1 ✅)
4. `php artisan test`

#### Breaking changes to check
```bash
# Nullable container resolution (MOST IMPORTANT)
grep -rn "?.*\$.*= null" --include="*.php" app/ | grep "__construct"

# Schema method changes
grep -rn "Schema::getTables\|Schema::getTableListing\|Schema::getViews" --include="*.php" app/

# HasUuids trait
grep -rn "HasUuids" --include="*.php" app/

# Grammar constructor changes
grep -rn "Grammar::\|setConnection\|getPrefix\|withTablePrefix\|setTablePrefix" --include="*.php" app/
```

All verified: ZERO breaking changes affect Leantime.

#### Validation checklist
- [ ] `composer update` succeeds
- [ ] `php artisan test` passes
- [ ] JSON-RPC endpoint responds
- [ ] Web installer works (fresh install)
- [ ] DB migration runs
- [ ] Login works (session + AdvancedAuth token)
- [ ] SMTP, S3, OIDC, LDAP, Redis work (if configured)
- [ ] Docker build + compose up work
- [ ] PostgreSQL works (if testable)


### 0.2 Build Pipeline Migration: Laravel Mix → Vite

**Priority:** HIGH | **Risk:** MEDIUM-HIGH | **Effort:** 1-2 days

#### What to do
1. `npm install --save-dev vite laravel-vite-plugin && npm uninstall laravel-mix`
2. Convert `webpack.mix.js` → `vite.config.js` (map all entry points)
3. Update `package.json` scripts: `dev` → `vite`, `build` → `vite build`
4. Replace `mix()` → `@vite()` in all Blade layouts:
```bash
grep -rn "mix(" --include="*.php" --include="*.blade.php" resources/ app/
```
5. Handle webpack patterns: `require()` → `import`, `module.exports` → `export default`, `process.env` → `import.meta.env`
6. Keep `webpack.mix.js` renamed as `.webpack.mix.js.bak` for rollback

#### Validation checklist
- [ ] `npm run dev` starts Vite with HMR
- [ ] `npm run build` produces production bundles
- [ ] All pages render correctly
- [ ] TipTap editor works
- [ ] HTMX interactions work
- [ ] Calendar/Gantt/Kanban/Canvas views render
- [ ] Chart.js visualizations render
- [ ] Build output size tracked
- [ ] Docker build works


### 0.3 Tailwind CSS Upgrade: 3.4 → 4.x

**Priority:** HIGH | **Risk:** MEDIUM | **Effort:** 4-8 hours

#### What to do
1. `npm install tailwindcss@latest`
2. Convert `tailwind.config.js` → CSS-native:
```css
@import "tailwindcss" prefix(tw-);
@source "../views/**/*.blade.php";
@source "../../app/Views/**/*.blade.php";
@theme { --color-primary: #1a73e8; }
```
3. Keep `tw-` prefix during Phase 0

#### Validation checklist
- [ ] Compiles without errors
- [ ] Existing `tw-*` classes work
- [ ] No visual regressions
- [ ] Custom theme values preserved


### 0.4 DaisyUI 5 Installation

**Priority:** MEDIUM | **Risk:** LOW | **Effort:** 2-4 hours

#### What to do
1. `npm install daisyui@latest`
2. Add `@plugin "daisyui"` to CSS config
3. Configure themes, set up dual-mode (alongside Bootstrap)
4. Create dev-only component preview page

#### DaisyUI 5 class changes from 4.0-dev era
| Old (4.0-dev) | New (DaisyUI 5) |
|---|---|
| `form-control` | `fieldset` / `label` |
| `card-compact` | `card-sm` |
| `bottom-nav` | `dock` |
| `avatar online` | `avatar avatar-online` |
| dropdown (CSS-only) | dropdown + `popover` attr |

#### Validation checklist
- [ ] DaisyUI classes render correctly
- [ ] Theme switching works
- [ ] No conflicts with Bootstrap
- [ ] Preview page works
- [ ] Modal renders with `<dialog>`


### 0.5 HTMX Upgrade: 1.9.12 → 2.0.8 + Idiomorph

**Priority:** MEDIUM | **Risk:** LOW-MEDIUM | **Effort:** 4-8 hours

#### What to do
1. `npm install htmx.org@latest` + install Idiomorph
2. Check for `data-hx-*` attributes:
```bash
grep -rn "data-hx-" --include="*.php" --include="*.blade.php" --include="*.tpl.php" resources/ app/
```
3. Test all 8 HxController domains + MyToDos infinite scroll
4. Add Idiomorph to base layout (don't add `hx-ext="morph"` yet)

#### HTMX 2.x patterns for CLAUDE.md
```
hx-on:click="..."              — Event attribute
hx-swap="morph"                — Morph swap (preserves DOM state)
hx-sync="closest form:abort"   — Race condition prevention
hx-trigger="revealed"          — Viewport trigger
hx-trigger="keyup delay:300ms" — Debounced input
```

#### Validation checklist
- [ ] Existing HTMX interactions work
- [ ] MyToDos infinite scroll works
- [ ] Form submissions work
- [ ] Idiomorph loads without errors
- [ ] No console errors


### 0.6 Component Infrastructure Setup

**Priority:** MEDIUM | **Risk:** LOW | **Effort:** 2-4 hours

#### What to do
1. Create `app/Views/Components/` subdirs: `actions/`, `content/`, `elements/`, `forms/`, `navigations/`
2. Establish anonymous component pattern with `@props`, `$attributes->merge()`, named slots
3. Class-based ONLY for: computed logic, DB access, service injection, `shouldRender()`
4. Update CLAUDE.md with conventions
5. Verify existing 15 components still work

#### Validation checklist
- [ ] Directory structure created
- [ ] CLAUDE.md updated (creation patterns, DaisyUI migration table, HTMX reference)
- [ ] Existing 15 components render correctly


### 0.7 Reference Modal Component

**Priority:** LOW | **Risk:** LOW | **Effort:** 2-4 hours

#### What to do
1. Create `app/Views/Components/modal.blade.php` — native `<dialog>` + DaisyUI
2. Props: `id`, `title`, `size` (sm/md/lg/xl), `closeable`
3. HTMX trigger pattern: `hx-get` loads content → `showModal()` → close triggers refresh
4. Add to component preview page

#### Validation checklist
- [ ] Opens/closes correctly (button, backdrop, Escape)
- [ ] HTMX loads content into modal
- [ ] Close triggers parent content refresh
- [ ] Mobile viewport works
- [ ] Nested modals work
- [ ] Focus trap works

---

## Phase 0 Execution Order

```
0.1 Laravel 12 Upgrade                    [2-4 hours]
0.2 Build Pipeline Migration (Mix → Vite)  [1-2 days]
0.3 Tailwind CSS Upgrade (3.4 → 4.x)      [4-8 hours]
0.4 DaisyUI 5 Installation                [2-4 hours]
0.5 HTMX Upgrade (1.9 → 2.0.8)            [4-8 hours]
0.6 Component Infrastructure Setup         [2-4 hours]
0.7 Reference Modal Component              [2-4 hours]
```
**Total: ~5-7 working days**

---

## Phase 0 Deliverables

- [ ] Laravel 12 running, all tests passing
- [ ] Vite dev + production builds working
- [ ] Tailwind 4 + DaisyUI 5 installed and rendering
- [ ] HTMX 2.0.8 + Idiomorph loaded
- [ ] All existing pages render (no regressions)
- [ ] JSON-RPC + AdvancedAuth still work (mobile app)
- [ ] Docker build + compose work
- [ ] Fresh install + DB migration work
- [ ] Component directory structure + CLAUDE.md updated
- [ ] Reference modal component working
- [ ] Build output size baseline recorded

---

## Version Reference

| Dependency | Current (v3.6.2) | Target (Phase 0) |
|---|---|---|
| PHP | ^8.2 (Docker: 8.3) | ^8.2 (Docker: 8.3) |
| Laravel | ^11.44 | ^12.0 |
| Tailwind CSS | ^3.4.1 | 4.x |
| DaisyUI | not installed | 5.5.x |
| HTMX | ^1.9.12 | ^2.0.8 + Idiomorph |
| Build tool | Laravel Mix ^6.0.49 | Vite + laravel-vite-plugin |
| MySQL | 8.4 | 8.4 |

---

## Risk Register

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Vite migration breaks rendering | MEDIUM | HIGH | Keep webpack.mix.js as rollback |
| Tailwind 4 changes class behavior | LOW | MEDIUM | v4 maintains v3 compatibility |
| HTMX 2.x breaks interactions | LOW | MEDIUM | Backward compatible; test all domains |
| Laravel 12 breaks service resolution | LOW | HIGH | Grep verified: zero breaking changes |
| DaisyUI conflicts with Bootstrap | LOW | LOW | Dual-mode, unique namespace |
| Docker build fails | MEDIUM | MEDIUM | Test after Vite migration |
| Mobile app breaks | LOW | CRITICAL | JSON-RPC integration tests |
| DB migrations fail | LOW | HIGH | Test fresh install + upgrade path |
| Plugin loading breaks | LOW | MEDIUM | Verify all 33+ plugins load |

---

## What This Document Does NOT Cover

- **Phase 1:** Modal system replacement (37 files), HTMX hx-boost
- **Phase 2:** Core domain component forward-porting
- **Phase 3:** JS optimization (bundle splitting), canvas domains
- **Phase 4:** Remaining domains, CSS cleanup (Bootstrap removal)
- Plugin submodule advancement (926 commits)
- 4.0-dev merge conflicts (14 core files)

---

## Appendix: Useful Grep Commands

```bash
# mix() references (need → @vite)
grep -rn "mix(" --include="*.php" --include="*.blade.php" resources/ app/

# HTMX data- prefix (2.x migration)
grep -rn "data-hx-" --include="*.php" --include="*.blade.php" --include="*.tpl.php" resources/ app/

# Laravel 12 breaking changes
grep -rn "?.*\$.*= null" --include="*.php" app/ | grep "__construct"
grep -rn "Schema::getTables\|Schema::getTableListing\|Schema::getViews\|HasUuids" --include="*.php" app/

# Event hook compatibility
grep -rn "middleware.auth\." --include="*.php" app/Plugins/

# Tailwind prefix tracking
grep -rn "tw-" --include="*.php" --include="*.blade.php" resources/ app/ | wc -l

# HxControllers inventory
find app/ -name "*.php" -path "*/Hxcontrollers/*" | sort
```
