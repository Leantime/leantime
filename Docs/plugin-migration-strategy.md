# Leantime Plugin Migration Strategy — Per-Plugin Approach

## Companion Document to Phase 4.2

**Scope:** 32 active plugin directories (31 with code, 1 dead) in `app/Plugins`
**Branch:** Current working branch (v3.6.2 mainline, post-Phase 0–4 planning)
**Generated:** February 13, 2026

---

## 1. Current Plugin Inventory (Live Audit)

### Full Plugin List (32 directories)

| Plugin | Templates (.tpl.php / .blade.php) | Own Build Pipeline | composer.json | DispatchesEvents Trait | Event Hooks |
|--------|:-:|:-:|:-:|:-:|:-:|
| **Copilot** | 0 / 11 | ✅ webpack.mix.js + package.json | ✅ | — | 9 EventDispatcher calls |
| **McpServer** | 0 / 1 | — | ✅ (+ vendor/) | ✅ (2 services) | ServiceProvider + EventDispatcher |
| **AdvancedAuth** | 0 / 6 | — | ✅ | — | 7 EventDispatcher calls |
| **Billing** | 2 / 11 | — | ✅ | — | 10 EventDispatcher calls |
| **Llamadorian** | 9 / 0 | — | ✅ | ✅ (5 files) | 12 EventDispatcher calls |
| **StrategyPro** | 12 / 1 | — | ✅ | — | EventDispatcher calls |
| **PgmPro** | 6 / 1 | — | ✅ | — | EventDispatcher calls |
| **Whiteboardscanvas** | 6 / 1 | — | ✅ | — | EventDispatcher calls |
| **Accounts** | 4 / 2 | — | ✅ | ✅ (2 files) | EventDispatcher + authcheck |
| **CustomFields** | 0 / 4 | — | ✅ | — | EventDispatcher calls |
| **Notes** | 0 / 10 | — | ✅ | — | EventDispatcher calls |
| **Reactions** | 0 / 2 | ✅ webpack.mix.js (no package.json) | ✅ | ✅ (1 file) | EventDispatcher calls |
| **RecurringTasks** | 0 / 2 | ✅ webpack.mix.js + package.json | ✅ | — | EventDispatcher calls |
| **CalDAV** | 0 / 4 | — | ✅ | — | EventDispatcher calls |
| **ProjectWizard** | 0 / 15 | — | ✅ | — | EventDispatcher calls |
| **Crisp** | 0 / 1 | — | ✅ | — | EventDispatcher calls |
| **ThemeBundle** | 0 / 2 | — | ✅ | — | EventDispatcher calls |
| **Implementationintentions** | 0 / 3 | — | ✅ | ✅ (1 file) | EventDispatcher calls |
| **Pomodoro** | 0 / 1 | — | ✅ | — | EventDispatcher calls |
| **Workflows** | 0 / 0 | — | ✅ | — | Services only |
| **ClientConfigLoader** | 0 / 0 | — | ✅ | — | authcheck hook |
| **ApiRateLimiter** | 0 / 0 | — | ✅ | — | authcheck hook |
| **Subscriptions** | 0 / 0 | — | ✅ | — | Minimal |
| **WorkspaceManager** | 0 / 0 | — | ✅ | — | EventDispatcher calls |
| **Freshsales** | 0 / 0 | — | ✅ | — | EventDispatcher calls |
| **GoogleAnalytics** | 0 / 0 | — | ✅ | — | EventDispatcher calls |
| **SponsorLeantime** | 0 / 0 | — | ✅ | — | EventDispatcher calls |
| **Wootric** | 0 / 0 | — | ✅ | — | EventDispatcher calls |
| **Sentry** | 0 / 0 | — | ✅ | — | EventDispatcher calls |
| **Zapier** | 0 / 1 | — | ✅ | — | Minimal |
| **EnergyTracker** | 0 / 0 | — | — | — | Services only |
| **GoogleCalendar** | 0 / 0 | — | — (lock only) | — | ⚠️ DEAD — No register.php, no PHP files |

### Key Findings vs. Documentation

1. **GoogleCalendar is dead** — Has vendor/ directory with Google API libs but no register.php, no PHP files, no templates. Not in original docs. Should be removed or flagged.
2. **Event hook migration is COMPLETE** — `grep` for `middleware.auth.` (old pattern) returns zero results. All 5 plugins using authcheck hooks (ClientConfigLoader, ApiRateLimiter, Accounts, AdvancedAuth, Billing) are already on the correct `middleware.authcheck` pattern. This was flagged as a risk in the forward-port doc but has already been resolved on the current branch.
3. **11 files use DispatchesEvents trait** — Across Llamadorian (5), McpServer (2), Accounts (2), Reactions (1), Implementationintentions (1). These need verification against the trait's method signatures after any core Event system reconciliation.
4. **3 plugins have own webpack.mix.js** — Copilot, Reactions, RecurringTasks. Core migrated to Vite in Phase 0. These are now orphaned on Laravel Mix.
5. **39 .tpl.php files remain** across 6 plugins (StrategyPro: 12, Llamadorian: 9, PgmPro: 6, Whiteboardscanvas: 6, Accounts: 4, Billing: 2).

---

## 2. Tier Classification

### Tier 1 — Dedicated Migration Strategy Required

| Plugin | Why | Risk |
|--------|-----|------|
| **Copilot** | 141+ files, own build pipeline, 999-line custom CSS, jQuery dependency, 8 JS files (2,851 lines), deep event system integration, AI-critical | 🔴 CRITICAL |
| **McpServer** | ServiceProvider architecture, own vendor/, custom attributes (`@UnifiedTool`, `@Parameter`), route exemptions, CLI commands, DispatchesEvents in 2 services | 🔴 CRITICAL |
| **AdvancedAuth** | Security-critical (OAuth/SSO + mobile app auth), 6 Blade templates with Bootstrap classes, authcheck hooks, 35+ language files | 🟡 HIGH |

### Tier 2 — Focused Checklist Required

| Plugin | Why | Risk |
|--------|-----|------|
| **Billing** | Revenue-critical, 2 legacy .tpl.php + 11 Blade, heavy Bootstrap usage in old templates, inline HTML in register.php, authcheck hooks | 🟡 HIGH |
| **Llamadorian** | 9 legacy .tpl.php (100% unconverted), 5 files using DispatchesEvents trait, jQuery.nmManual modal calls, StoryTime popup architecture | 🟡 MEDIUM |
| **StrategyPro** | 12 legacy .tpl.php (most of any plugin), canvas-based UI | 🟡 MEDIUM |
| **PgmPro** | 6 legacy .tpl.php, portfolio management UI | 🟢 MEDIUM |
| **Whiteboardscanvas** | 6 legacy .tpl.php, canvas-based UI | 🟢 MEDIUM |
| **Accounts** | 4 legacy .tpl.php, 2 DispatchesEvents files, authcheck hook | 🟢 LOW |

### Tier 3 — Batch Pass (Remaining 22 plugins)

All already on Blade or have no templates. Require only: DaisyUI class audit, Bootstrap class removal, build pipeline check, DispatchesEvents verification (where applicable).

---

## 3. Tier 1: Copilot Migration Strategy

### 3.1 Overview

Copilot is the largest and most complex plugin. It has its own build pipeline (webpack.mix.js), 999 lines of custom CSS, 2,851 lines of JS across 8 files, jQuery UI dependencies (resizable/draggable), and deep integration with the core event system via 9+ event hooks.

### 3.2 Build Pipeline Migration (Mix → Vite)

**Current state:** Copilot uses its own `webpack.mix.js` that compiles 8 JS files and 1 CSS file to `dist/`. It externalizes jQuery via `externals: { jquery: 'jQuery' }`.

**Problem:** Core migrated to Vite in Phase 0. Plugin still uses Mix. This creates two parallel build systems, prevents tree-shaking of Copilot's JS, and means Copilot's assets aren't part of the core build's hash-versioning.

**Strategy:**

1. **Create `vite.config.js` for Copilot** — Multi-entry Vite config producing the same 8+1 output files to `dist/`.
2. **Update asset registration** — Copilot's `register.php` calls `$registrationService->addFooterJs()` and `$registrationService->addCss()` with relative paths. Verify these resolve correctly with Vite's hashed output. If `RegistrationService` doesn't support Vite manifests yet, this needs a core-side adapter.
3. **Remove jQuery externalization** — Instead of `externals: { jquery: 'jQuery' }`, refactor the 15+ jQuery calls in `copilot.js` to vanilla JS (see 3.4).
4. **Remove `webpack.mix.js`, `package.json`, `node_modules/`** from Copilot. Assets compile via Vite.
5. **Verify `dist/mix-manifest.json`** is no longer read anywhere.

**Decision needed:** Should Copilot's Vite config be standalone (plugin-level `vite.config.js`) or integrated into the core Vite config as additional entry points? Standalone is cleaner for plugin architecture but core integration enables shared chunk deduplication.

### 3.3 CSS Migration (Custom → Tailwind/DaisyUI)

**Current state:** `copilot.css` is 999 lines of fully custom CSS. Uses CSS custom properties (`var(--neutral)`, `var(--primary)`) that align with DaisyUI's theme variables, which is good. However, it defines its own layout system, spacing, animations, and component styles entirely outside DaisyUI.

**Strategy:**

1. **Audit for DaisyUI equivalents** — Many patterns map directly:
   - `.copilot-chat-message-bubble { background: var(--neutral); border-radius: 8px; }` → DaisyUI `chat-bubble` component
   - `.copilot-panel-header` → DaisyUI `navbar` or card header pattern
   - Button styles → DaisyUI `btn btn-ghost`, `btn btn-sm`
   - Loading/skeleton states → DaisyUI `loading` and `skeleton`

2. **Keep Copilot-specific layout CSS** — The chat panel positioning, fullscreen toggle, resizable panel, and streaming animation are unique to Copilot and have no DaisyUI equivalent. These should remain as custom CSS but be migrated from a standalone file to Tailwind `@apply` directives or `@layer components` in a Copilot-specific CSS module.

3. **Replace Bootstrap grid in `dashboard.blade.php`** — Currently uses `col-md-12`, `col-md-8`, `col-md-4`. Replace with Tailwind grid utilities.

4. **Target:** Reduce from 999 lines to ~300-400 lines of truly Copilot-specific CSS (panel layout, streaming animation, chat scroll behavior). Everything else maps to DaisyUI classes inline in templates.

### 3.4 JavaScript: jQuery Elimination

**Current state:** `copilot.js` (1,178 lines) uses jQuery for:
- `jQuery(panel).resizable()` and `jQuery(panel).draggable()` — jQuery UI
- `jQuery('#' + messageId).find(...)` — DOM queries
- `jQuery('#' + messageId).data("id", data.id)` — Data attributes
- `jQuery(this).attr("hx-vals", ...)` — HTMX attribute manipulation
- `jQuery('.tool-message').remove()` — DOM removal

**Strategy:**

1. **jQuery UI resizable/draggable → CSS `resize` + vanilla JS drag** — The chat panel's resize and drag behavior can be implemented with CSS `resize: both` for simple resize, or a lightweight drag library if needed. Since Phase 4.5 is bringing in SortableJS for kanban, evaluate if SortableJS or a sibling lib handles drag.

2. **jQuery DOM queries → `document.querySelector`** — All `jQuery('#' + id).find(...)` patterns become `document.querySelector('#' + id + ' .selector')`.

3. **jQuery `.data()` → `dataset`** — `jQuery(el).data("id", val)` → `el.dataset.id = val`.

4. **jQuery `.attr("hx-vals")` → `setAttribute`** — Direct equivalent.

5. **jQuery `.remove()` → `el.remove()`** — Native.

6. **Execution order:** Refactor `copilot.js` first (1,178 lines, most jQuery), then `direct-actions.js` (388 lines), then `task-prioritization.js` (401 lines). The remaining files (`markdown-parser.js`, `marked-extensions.js`, `mermaid-init.js`, `stream-processor.js`, `tiptap-integration.js`) likely don't use jQuery.

### 3.5 Template Componentization

**Current state:** All 11 templates are Blade (good), but they use raw HTML/CSS classes rather than the shared component library.

**Templates to update:**

| Template | Priority | Key Changes |
|----------|----------|-------------|
| `dashboard.blade.php` | HIGH | Replace Bootstrap grid (`col-md-*`) with Tailwind |
| `partials/chatPanel.blade.php` | HIGH | Buttons → `<x-globals::forms.button>`, panel structure |
| `partials/chat.blade.php` | MED | Message bubbles → evaluate DaisyUI chat component |
| `partials/feedbackButtons.blade.php` | MED | Buttons → `<x-globals::forms.button>` |
| `partials/prioritizationToggle.blade.php` | MED | Toggle → `<x-globals::forms.toggle>` |
| `partials/taskDashboardActions.blade.php` | LOW | Action buttons → component |
| `partials/taskPriorityActions.blade.php` | LOW | Badge/chip usage → `<x-globals::actions.chip>` |
| `partials/actionFeedback.blade.php` | LOW | Alert → `<x-globals::feedback.alert>` |
| `partials/chatHistory.blade.php` | LOW | List items |
| `partials/dailyWelcome.blade.php` | LOW | Static content |
| `partials/inlineMessage.blade.php` | LOW | Inline alert/message |

### 3.6 Event System Verification

Copilot registers 9 event hooks. None use the old `middleware.auth` pattern (confirmed). Hooks to verify still fire correctly after Phase 4:

1. `leantime.core.middleware.loadplugins.handle.pluginsEvents` — Config extension
2. `leantime.*.beforeBodyClose` — Chat panel injection
3. `leantime.core.console.consolekernel.schedule.cron` — Prompt sync cron
4. `leantime.domain.tickets.templates.dashboard.afterHeadline` — Dashboard actions
5. `leantime.domain.tickets.templates.showTicketModal.beforeSubtasks` — Task breakdown
6. `leantime.*.submenuSection` — Submenu items
7. `leantime.domain.widgets.templates.partials.myToDos.beforeTodoWidgetGroupByDropdown` — Prioritization toggle
8. `leantime.domain.tickets.services.tickets.getToDoWidgetHierarchicalAssignments.myTodoWidgetTasks` — Task filter
9. `leantime.domain.*.todoWidgetSortableEnabled` — Sortable management

**Risk:** Hooks 4–9 reference specific template hook points. If Phase 4.1 (remaining domain conversions) changes the template structure in tickets/widgets domains, these hooks may silently stop firing. **Verify each hook has a matching `@event` or `EventDispatcher::dispatch_event()` call in the converted template.**

### 3.7 Copilot Execution Order

```
1. Build pipeline: webpack.mix.js → vite.config.js          [0.5 day]
2. jQuery elimination from copilot.js                         [1 day]
3. jQuery elimination from remaining JS files                 [0.5 day]
4. CSS audit: identify DaisyUI replacements                   [0.5 day]
5. Template componentization (dashboard + chatPanel first)    [1 day]
6. CSS reduction: replace custom with DaisyUI/Tailwind        [1 day]
7. Event hook verification (all 9 hooks fire)                 [0.5 day]
8. End-to-end test: chat panel renders, AI responds, streaming works  [0.5 day]
```
**Total: ~5.5 days**

---

## 4. Tier 1: McpServer Migration Strategy

### 4.1 Overview

McpServer is architecturally unique — it's the only plugin using a full Laravel ServiceProvider (`McpServerServiceProvider extends McpServiceProvider`), its own Composer vendor/ directory (php-mcp/laravel), custom PHP attributes, artisan CLI commands, and route exemptions. It has minimal UI (1 Blade template) but deep infrastructure coupling.

### 4.2 ServiceProvider Compatibility

**Current state:** `McpServerServiceProvider` extends `PhpMcp\Laravel\McpServiceProvider` and registers:
- Singletons: `McpRegistrar`, `McpToolExecutor`, `McpToolDiscovery`, `McpPromptService`
- Middleware: `mcp.auth`
- CLI commands: `McpStartCommand`, `McpListCommand`, `McpDiscoverCommand`
- Route exemptions for Installed/Updated middleware via EventDispatcher

**Migration concern:** The ServiceProvider uses `EventDispatcher::add_filter_listener()` directly in `registerMcpRouteExemptions()`. This is the static call pattern (correct for v3.6.2). If the DispatchesEvents trait reconciliation in Phase 4 changes how EventDispatcher works internally, verify these filter listeners still register.

**Strategy:**
1. **No template work needed** — Single Blade template (`timer.blade.php`) has only 2 Bootstrap button classes (`btn btn-default`). Quick replacement with DaisyUI `btn`.
2. **Verify ServiceProvider boots after Phase 4** — Test `php artisan mcp:start`, `php artisan mcp:list`, `php artisan mcp:discover`.
3. **Verify route exemptions work** — MCP routes must bypass Installed/Updated middleware.
4. **Test all 9 tool domains** — CalendarTools, CommentTools, GoalTools, MilestoneTools, ProjectTools, TicketTools, TimerTools, TimesheetTools, WebSearchTools. Each tool uses reflection and `@UnifiedTool`/`@Parameter` attributes from `app/Core/Support/Attributes/`.

### 4.3 DispatchesEvents Trait Verification

Two services use the trait: `McpPromptService` and `McpServer`. Need to verify:
- Method signatures match (`dispatch_event` vs `dispatchEvent`, `get_event_context` vs `getEventContext`)
- Events actually fire (not silently swallowed)

**Test:** After DispatchesEvents reconciliation, run `php artisan mcp:list` and verify all tools are discovered.

### 4.4 Vendor Dependency

McpServer bundles its own `vendor/` with `php-mcp/laravel`. This is independent of the core Composer vendor/ and won't be affected by the Laravel 12 upgrade (Phase 0). However:
- Verify `php-mcp/laravel` is compatible with Laravel 12
- The `autoload.php` is required directly in `register.php` — this pattern bypasses core's autoloader

### 4.5 McpServer Execution Order

```
1. Replace 2 Bootstrap button classes in timer.blade.php        [0.1 day]
2. Verify ServiceProvider boots (artisan commands)               [0.25 day]
3. Verify route exemptions (MCP routes bypass middleware)        [0.25 day]
4. Test all 9 tool domains                                       [0.5 day]
5. DispatchesEvents trait verification (2 services)              [0.25 day]
6. php-mcp/laravel Laravel 12 compatibility check                [0.25 day]
```
**Total: ~1.5 days**

---

## 5. Tier 1: AdvancedAuth Migration Strategy

### 5.1 Overview

AdvancedAuth handles OAuth/SSO authentication and personal API tokens. It's the auth provider for the mobile app (`POST /advancedAuth/getToken`). It has 6 Blade templates all using Bootstrap classes, 8 event listeners, and 35+ language files.

### 5.2 Template Migration

**Current state:** All 6 templates are Blade but use Bootstrap classes heavily:

| Template | Bootstrap Usage | Component Replacements |
|----------|----------------|----------------------|
| `new.blade.php` | `form-group`, `form-control`, `btn btn-default` | `<x-globals::forms.form-field>`, `<x-globals::forms.text-input>`, `<x-globals::forms.button>` |
| `settings.blade.php` | `col-md-12`, `col-md-8`, `form-group` (×4) | Tailwind grid, `<x-globals::forms.form-field>` |
| `token-created.blade.php` | `form-group`, `form-control`, `btn btn-default` | Form components |
| `tokens.blade.php` | `col-md-12` | Tailwind width |
| `partials/personalTokens.blade.php` | Review needed | — |
| `partials/showButtons.blade.php` | Review needed | — |

**Strategy:** Straightforward component replacement. These are simple form-based templates.

### 5.3 Mobile App Auth Verification

**CRITICAL:** AdvancedAuth provides `POST /advancedAuth/getToken` which returns Bearer tokens for the mobile app's JSON-RPC API. The auth flow is:
1. Mobile app → `POST /advancedAuth/getToken` with credentials
2. AdvancedAuth validates, returns Bearer token
3. Mobile app uses token for all subsequent `/api/jsonrpc` calls

**Test matrix:**
- [ ] Token generation endpoint returns valid token
- [ ] Token auth works for JSON-RPC calls
- [ ] OAuth callback redirect works (Keycloak)
- [ ] `publicActions` filter correctly exempts auth routes
- [ ] Domain-based auth routing (CheckDomain listener) works

### 5.4 Event Hook Verification

8 event hooks, all using `EventDispatcher` static calls (correct pattern):
1. `loadplugins.handle.pluginsEvents` — SetConfig listener
2. `users.templates.editOwn.tabs` — Personal token tab
3. `setting.templates.editCompanySettings.tabs` — Settings tab
4. `setting.templates.editCompanySettings.tabsContent` — Settings content
5. `users.templates.editOwn.tabsContent` — Personal token content
6. `*.beforeRegcontentClose` — Provider buttons
7. `auth.controllers.login.post.beforeAuthServiceCall` — Domain check
8. `middleware.authcheck.*.publicActions` — Public route filter

**Risk:** Hooks 2–5 inject into specific template locations. If those templates are restructured during Phase 4.1 (Auth domain conversion), the hooks must be re-verified.

### 5.5 AdvancedAuth Execution Order

```
1. Template componentization (6 templates)                       [0.5 day]
2. Bootstrap class removal                                       [0.25 day]
3. Mobile app auth flow verification                             [0.5 day]
4. OAuth callback test (Keycloak)                                [0.25 day]
5. Event hook verification (8 hooks)                             [0.25 day]
```
**Total: ~1.75 days**

---

## 6. Tier 2: Focused Checklists

### 6.1 Billing

**Template work:** 2 `.tpl.php` files to convert (`subscriptions_old.tpl.php` is heavily Bootstrap), 11 `.blade.php` to audit for Bootstrap classes.

**Special concerns:**
- `register.php` contains inline HTML in event listeners (menu items, announcement banner with `btn-fancy`). These need DaisyUI class updates.
- Loads JS via `<script>` tag in event listener (`billingModals.js`) — should use `$registrationService->addFooterJs()`.
- Uses `authcheck.handle.logged_in` and `authcheck.handle.before_api_request` hooks (verified correct pattern).

**Checklist:**
- [ ] Convert 2 `.tpl.php` → `.blade.php`
- [ ] Audit 11 `.blade.php` for Bootstrap classes
- [ ] Update inline HTML in `register.php` (menu, announcements)
- [ ] Migrate `billingModals.js` script tag to proper registration
- [ ] Verify subscription limit check still fires on login
- [ ] Verify API request billing check still fires
- [ ] Test Stripe checkout flow end-to-end

### 6.2 Llamadorian

**Template work:** 9 `.tpl.php` files, ALL need conversion to Blade. This is the most template-heavy conversion.

**Special concerns:**
- Uses `jQuery.nmManual()` (nyroModal) for StoryTime popup — must migrate to `<dialog>` modal system
- 5 files use `DispatchesEvents` trait — highest trait usage of any plugin
- Uses legacy function-based event listeners (`function addStatusReportLink(...)`) rather than class-based
- Several event listeners are commented out (task prioritization features, now superseded by Copilot)

**Checklist:**
- [ ] Convert 9 `.tpl.php` → `.blade.php` with DaisyUI components
- [ ] Replace `jQuery.nmManual()` StoryTime modal with `<dialog>`
- [ ] Verify 5 DispatchesEvents trait files after core reconciliation
- [ ] Modernize function-based listeners to class-based (or leave as-is if stable)
- [ ] Remove commented-out code (dead task prioritization features)
- [ ] Test StoryTime popup, status updates, AI settings tab, dashboard notifications

### 6.3 StrategyPro

**Template work:** 12 `.tpl.php` files. Largest count of any plugin.

**Special concerns:**
- Canvas-based UI — shares patterns with core Canvas domain (Phase 3 converted shared canvas templates). StrategyPro likely has custom overrides of `canvasDialog.tpl.php` and `canvasComment.tpl.php`.
- Check if StrategyPro's canvas templates can use the shared Blade canvas components created in Phase 3, or if they need custom versions.

**Checklist:**
- [ ] Audit which templates can use shared canvas components from Phase 3
- [ ] Convert remaining custom `.tpl.php` → `.blade.php`
- [ ] Apply DaisyUI/component patterns
- [ ] Test canvas CRUD operations, kanban view, roadmap view

### 6.4 PgmPro

**Template work:** 6 `.tpl.php` + 1 `.blade.php`.

**Checklist:**
- [ ] Convert 6 `.tpl.php` → `.blade.php`
- [ ] Apply shared component library
- [ ] Test portfolio management views

### 6.5 Whiteboardscanvas

**Template work:** 6 `.tpl.php` + 1 `.blade.php`.

**Special concerns:** Canvas/whiteboard templates — similar to StrategyPro, check shared component reuse from Phase 3.

**Checklist:**
- [ ] Audit shared canvas component reuse
- [ ] Convert 6 `.tpl.php` → `.blade.php`
- [ ] Test whiteboard/canvas operations

### 6.6 Accounts

**Template work:** 4 `.tpl.php` + 2 `.blade.php`.

**Special concerns:**
- 2 files use `DispatchesEvents` trait (`InviteTeamStep`, `ScheduleSyncStep`)
- Uses `authcheck` hook for public actions

**Checklist:**
- [ ] Convert 4 `.tpl.php` → `.blade.php`
- [ ] Verify DispatchesEvents in 2 service files
- [ ] Test registration, invite, onboarding flows

---

## 7. Tier 3: Batch Pass

For the remaining 22 plugins (all already Blade or template-less), run these automated checks:

### 7.1 Bootstrap Class Removal (Automated)

```bash
cd app/Plugins
grep -rn "btn-default\|btn-success\|btn-danger\|btn-warning\|btn-info\|btn-primary" \
  --include="*.blade.php" --include="*.php" | grep -v vendor | grep -v node_modules

grep -rn "col-md-\|col-lg-\|col-sm-\|col-xs-\|form-group\|form-control\|panel-" \
  --include="*.blade.php" --include="*.php" | grep -v vendor | grep -v node_modules
```

**Known hits (from audit):**
- McpServer `timer.blade.php`: 2 × `btn btn-default` → DaisyUI `btn`
- Any templates rendered via `echo` in register.php listener callbacks

### 7.2 DaisyUI 5 Class Audit

If any plugins reference DaisyUI 4-era class names (from the 4.0-dev component era):

```bash
grep -rn "form-control\|card-compact\|bottom-nav\|avatar online\|avatar offline" \
  --include="*.blade.php" | grep -v vendor
```

Replace with DaisyUI 5 equivalents: `form-control` → `fieldset`/`label`, `card-compact` → `card-sm`, `bottom-nav` → `dock`, etc.

### 7.3 DispatchesEvents Trait Verification

11 files across 6 plugins use the trait. After core Event system reconciliation:

```bash
# Verify method signatures match
grep -rn "dispatch_event\|get_event_context\|set_class_context\|dispatchEvent\|getEventContext\|setClassContext" \
  --include="*.php" app/Plugins/ | grep -v vendor | grep -v node_modules
```

Ensure whichever method naming convention the reconciled trait uses is reflected in all 11 plugin files.

### 7.4 Build Pipeline Orphans

3 plugins have `webpack.mix.js`:
- **Copilot** — Addressed in Tier 1 strategy (section 3.2)
- **Reactions** — Has `webpack.mix.js` but NO `package.json`. Check if it uses root-level node_modules or is a dead config.
- **RecurringTasks** — Has both `webpack.mix.js` and `package.json`. Needs Vite migration or verification that Mix still works independently.

### 7.5 Dead Plugin Cleanup

- **GoogleCalendar** — No register.php, no PHP files outside vendor/. Remove or document as placeholder.

### 7.6 composer.json Cleanup

Per the forward-port doc, all plugins had `"leantime/leantime": "3.0"` removed. Verify:

```bash
grep -rn '"leantime/leantime"' app/Plugins/*/composer.json
```

If any still have it, remove the constraint.

---

## 8. Cross-Cutting Concerns

### 8.1 RegistrationService + Vite Compatibility

Multiple plugins use `$registrationService->addFooterJs()` and `$registrationService->addCss()` to register assets. After the core Mix → Vite migration (Phase 0), verify:
1. `RegistrationService` can resolve assets from plugin `dist/` directories
2. Hash-versioned filenames (if Vite is used for plugins) are handled
3. The `mix-manifest.json` pattern (used by Copilot's dist/) is still read OR migrated to Vite manifests

### 8.2 Inline HTML in register.php Files

Several plugins echo HTML directly in event listeners (Billing menu items, Llamadorian buttons, etc.). These inline HTML snippets contain Bootstrap classes that will break after Bootstrap CSS removal in Phase 4.4. Audit ALL `register.php` files:

```bash
grep -rn "echo.*class=" app/Plugins/*/register.php | grep -v vendor
```

### 8.3 Plugin Testing Order

After all migrations, test plugins in dependency order:

1. **AdvancedAuth** first — Other plugins depend on auth working
2. **McpServer** — Independent, can test in isolation
3. **Copilot** — Depends on AdvancedAuth for user context
4. **Billing** — Depends on auth
5. **Llamadorian** — Depends on project/ticket data
6. **StrategyPro / PgmPro / Whiteboardscanvas** — Depend on canvas system (Phase 3)
7. **Everything else** — Batch test

---

## 9. Timeline Summary

| Tier | Plugins | Effort | Dependencies |
|------|---------|--------|-------------|
| **Tier 1: Copilot** | 1 | 5.5 days | Phase 4.1 template hooks, Phase 4.5 jQuery UI removal |
| **Tier 1: McpServer** | 1 | 1.5 days | Core DispatchesEvents reconciliation |
| **Tier 1: AdvancedAuth** | 1 | 1.75 days | Phase 4.1 Auth domain conversion |
| **Tier 2: Billing** | 1 | 1.5 days | — |
| **Tier 2: Llamadorian** | 1 | 2 days | Phase 1 modal system (for nyroModal replacement) |
| **Tier 2: StrategyPro** | 1 | 1.5 days | Phase 3 canvas components |
| **Tier 2: PgmPro + Whiteboardscanvas** | 2 | 1.5 days | Phase 3 canvas components |
| **Tier 2: Accounts** | 1 | 0.75 days | — |
| **Tier 3: Batch pass** | 22 | 1.5 days | — |
| **Cross-cutting verification** | All | 1 day | All phases complete |
| **Total** | **32** | **~18.5 days** | — |

This replaces the original Phase 4.2 estimate of 2-3 days, which was significantly underscoped. The original estimate only covered template modernization and didn't account for build pipeline migration, jQuery elimination within plugins, CSS migration, DispatchesEvents verification, or mobile auth testing.

---

*This document was compiled from live analysis of `/Users/gloriafolaron/herd/leantime/app/Plugins` on the current working branch, cross-referenced with `leantime-plugin-changes-forward-port.md`, `phase-4-combined.md`, and `project-summary-and-claude-code-prompt.md`.*
