# Leantime Plugin Ecosystem: Forward-Port Reference

## Companion Document to `leantime-4.0-dev-unique-changes.md`

**Scope:** 926 commits | 793 files changed (excl. vendor/node_modules/dist) | Plugins submodule + core plugin infrastructure
**Comparison:** Plugins submodule commit `3156738` (4.0-dev) → `5ed0b93c` (v3.6.2)
**Key Finding:** The plugins submodule did NOT diverge — 4.0-dev's commit is an **ancestor** of v3.6.2's. All 926 commits moved forward only on mainline.
**Generated:** February 12, 2026

---

## How the Plugins Submodule Relates to the Main Repo

The main Leantime repo includes `app/Plugins` as a git submodule pointing to `github.com/Leantime/plugins`. Each branch of the main repo pins a specific commit of this submodule:

- **4.0-dev** pins commit `3156738` ("resolve merge conflicts") — an early snapshot
- **v3.6.2** pins commit `5ed0b93c` ("fix recurring task issue") — 926 commits ahead

Since 4.0-dev branched, mainline continued heavy plugin development while the 4.0-dev submodule pointer was never advanced. This means:

1. **4.0-dev is missing 7 entirely new plugins** built since it branched
2. **4.0-dev is missing 926 commits** of bug fixes, features, and refactoring to existing plugins
3. **The core plugin infrastructure** (in the main repo, not the submodule) diverged on BOTH branches — this is where the real conflicts live

---

## Plugins at Time of 4.0-dev Branch (29 plugins)

These plugins existed when 4.0-dev branched and have since been modified on mainline:

| Plugin | Files Changed | Severity | Key Changes |
|--------|:---:|:---:|---|
| Llamadorian | 68 | 🔴 | Heavy refactoring, route updates, prioritization removed, Bedrock provider updates |
| CustomFields | 40 | 🔴 | 7 files deleted, rewrite of composers/contracts, language registration added |
| StrategyPro | 31 | 🟡 | Template updates, stakeholder editing fix, dashboard changes |
| Billing | 26 | 🟡 | 20 new files (Blade templates, Jobs, credits system), cron/subscription rework |
| Notes | 23 | 🟡 | Pin/star functionality, sort/filter, color management, widget restyle |
| PgmPro | 20 | 🟡 | Template and service updates |
| Whiteboardscanvas | 19 | 🟡 | Canvas/whiteboard template changes |
| ProjectWizard | 15 | 🟢 | Template updates, 2 new files |
| Accounts | 13 | 🟡 | Major register.php rewrite, controller updates, inline class removed |
| Workflows | 10 | 🟢 | Model/service/listener updates |
| ThemeBundle | 8 | 🟢 | 9 new files (assets, templates, listeners) |
| Crisp | 8 | 🟢 | 5 new files, eventlistener/hxcontroller additions |
| Pomodoro | 6 | 🟢 | Minor updates |
| ClientConfigLoader | 6 | 🟢 | Cache-based config loading, service provider update |
| SponsorLeantime | 5 | 🟢 | Minor updates |
| Implementationintentions | 5 | 🟢 | Minor updates |
| Freshsales | 5 | 🟢 | Minor updates |
| Sentry | 4 | 🟢 | Minor updates |
| GoogleAnalytics | 3 | 🟢 | Minor updates |
| ApiRateLimiter | 3 | 🟢 | Minor updates |
| Zapier | 2 | 🟢 | Controller update |
| Subscriptions | — | 🟢 | Minimal changes |
| WorkspaceManager | — | 🟢 | register.php rewrite |
| Wootric | — | 🟢 | Minimal changes |

---

## 7 Entirely New Plugins (Not in 4.0-dev)

These plugins were created after 4.0-dev branched and must be added to the forward-ported branch.

### Copilot (141 new files) — 🔴 CRITICAL

The AI copilot system, by far the largest new plugin. This is a full agent framework:

**Architecture:**
- `Agents/` — Multi-agent system with action agents (TaskBreakdown, TaskPrioritization, WritingAssistant), admin agents (Analysis, Orchestration, Synthesis), special agents (ACTCoach, EnvironmentOptimizer, ProjectManager, SystemGuide, WorkNavigator)
- `AiProviders/Bedrock/` — AWS Bedrock integration (chat, streaming, structured output, message mapping)
- `Chat/` — History management, structured outputs (30+ component classes for cognitive assessment, memory, motivation, patterns), token tracking
- `Memory/` — MemoryServiceProvider for persistent AI context
- `Prompts/` — Prompt management with sync command
- `Tools/` — Tool integrations
- `Observability/` — Monitoring/telemetry
- Templates, controllers, hxcontrollers, listeners, middleware, models, repositories, services

**Dependencies:** Own `composer.json`, `package.json`, `webpack.mix.js`, compiled dist assets

**Forward-port concern:** This plugin hooks deeply into the core event system, template rendering, and task domain. Its templates will need componentization if 4.0-dev's DaisyUI/Blade component system is applied to plugins.

### McpServer (35 new files) — 🔴 CRITICAL

Model Context Protocol server for AI tool integration:

- `Tools/` — CalendarTools, CommentTools, GoalTools, MilestoneTools, ProjectTools, TicketTools, TimerTools, TimesheetTools, WebSearchTools
- `Services/` — McpServer, McpToolDiscovery, McpToolExecutor, WebSearchService
- `Support/` — Custom attributes (`@UnifiedTool`, `@Parameter`), entity formatters, LLM string sanitizer
- `Middleware/` — McpAuthMiddleware
- `Command/` — McpDiscoverCommand, McpListCommand, McpStartCommand
- Own `routes.php`, service provider, composer dependencies

**Forward-port concern:** Uses `AITool` attribute from `app/Core/Support/Attributes/` (added in v3.6.2). The MCP routes are exempted from install/update checks. Relies on the v3.6.2 event system.

### AdvancedAuth (28 new files) — 🟡 MEDIUM

OAuth/SSO authentication with personal API tokens:

- OAuth providers (Keycloak, etc.) with callback/redirect controllers
- Personal access token management (CRUD controllers + templates)
- Domain-based authentication routing
- Company settings integration via listeners
- Full i18n (35+ language files)
- Uses `RegistrationService` for language file registration

**Forward-port concern:** Hooks into `leantime.core.middleware.authcheck.*` events (the renamed middleware). Templates are Blade but not componentized.

### Reactions (20 new files) — 🟢 LOW

Emoji reaction system for tickets/comments:

- Event-driven architecture with listeners
- Own webpack build pipeline (`webpack.mix.js`, compiled assets)
- HTMX-based UI with hxcontrollers
- Full i18n support

### CalDAV (18 new files) — 🟢 LOW

Calendar sync via CalDAV protocol:

- CalDAV server implementation with models/repositories/services
- Database migrations
- Settings UI with connect/edit/delete templates (Blade)
- Menu integration via event listeners

### RecurringTasks (16 new files) — 🟢 LOW

Recurring task automation:

- Hxcontrollers, listeners, middleware
- Models/repositories/services
- Own webpack build (`webpack.mix.js`, compiled dist)
- Full i18n support

### EnergyTracker (1 file) — 🟢 LOW

Minimal plugin, services only.

---

## 3 Plugins Removed (Still in 4.0-dev)

These plugins were deleted from mainline after 4.0-dev branched:

| Plugin | Action Required |
|--------|----------------|
| **Mixpanel** | Delete from 4.0-dev. LICENSE was moved to AdvancedAuth. |
| **VWO** | Delete from 4.0-dev. `composer.json` was repurposed for AdvancedAuth. |
| **.idea** | Delete from 4.0-dev. IDE config files cleaned up. |

---

## ⚠️ CRITICAL: Event Hook Name Changes

The most impactful change across all plugins is the **middleware rename** from `auth` to `authcheck` in event hook strings. This affects every plugin that registers public routes, login handlers, or API authentication.

### Before (4.0-dev era)
```php
// These hook names exist in 4.0-dev's plugin register.php files
EventDispatcher::add_filter_listener("leantime.core.middleware.auth.*.publicActions", ...);
EventDispatcher::add_event_listener("leantime.core.middleware.auth.handle.logged_in", ...);
EventDispatcher::add_event_listener("leantime.core.middleware.apiAuth.handle.before_api_request", ...);
```

### After (v3.6.2)
```php
// These are the current hook names
EventDispatcher::add_filter_listener('leantime.core.middleware.authcheck.*.publicActions', ...);
EventDispatcher::add_event_listener('leantime.core.middleware.authcheck.handle.logged_in', ...);
EventDispatcher::add_event_listener('leantime.core.middleware.authcheck.handle.before_api_request', ...);
```

### Affected Plugins
- **Accounts** — `publicActions` filter
- **Billing** — `logged_in` event, `before_api_request` event
- **ClientConfigLoader** — `loginRoute` filter
- **AdvancedAuth** (new) — uses `authcheck` already
- Any plugin registering public routes or auth hooks

**If 4.0-dev's plugin code still uses the old `middleware.auth.*` hook names, those listeners will silently fail** — no errors, just non-functional authentication hooks.

---

## ⚠️ CRITICAL: DispatchesEvents Trait Rewrite on 4.0-dev

The main repo's 4.0-dev branch rewrote `app/Core/Events/DispatchesEvents.php` significantly:

### v3.6.2 (current mainline)
```php
// Calls go through EventDispatcher static class
public static function dispatch_event(...): void {
    EventDispatcher::dispatch_event($hook, $available_params, static::get_event_context($function));
}
```

### 4.0-dev
```php
// Calls go through Laravel Event facade
public static function dispatch_event(...): void {
    Event::dispatch_event($hook, $available_params, static::getEventContext($function));
}
```

Key differences in 4.0-dev:
- Routes through `Illuminate\Support\Facades\Event` instead of `EventDispatcher` directly
- Method renamed: `get_event_context()` → `getEventContext()`
- Method renamed: `set_class_context()` → `setClassContext()`
- New `dispatch()` method added (calls `Event::dispatchEvent()`)
- `dispatchEvent()` signature changed — now accepts `$event` and dispatches via `Event::dispatch()`

**Impact:** If plugins use the `DispatchesEvents` trait directly (rather than calling `EventDispatcher` statically), the behavior will differ between branches. Most plugins call `EventDispatcher` directly in `register.php`, so they're safe. But any plugin with services/controllers using the trait needs verification.

---

## ⚠️ Core Plugin Infrastructure Changes on 4.0-dev

The main repo's 4.0-dev branch modified 14 files in the plugin domain. These changes exist ONLY on 4.0-dev and conflict with v3.6.2:

### Plugins.php (Core)
4.0-dev **removed** the `getEnabledPluginPaths()` method (68 lines) from `app/Core/Plugins/Plugins.php`. This method handles PHAR plugin loading and folder-based plugin path resolution. v3.6.2 added this method as part of its PHAR/marketplace plugin support.

**Conflict resolution:** Favor v3.6.2 — PHAR support and `getEnabledPluginPaths()` are needed for the marketplace.

### Registration Service
4.0-dev **removed** several methods from `app/Domain/Plugins/Services/Registration.php`:
- `getPluginBasePath()` — PHAR-aware path resolution
- `addHeaderJs()` — JS injection via mix manifest
- `registerManifestFolder()` — dist folder registration
- `$distFolderRegistered` property

**Conflict resolution:** These removals suggest 4.0-dev has a different asset loading strategy (likely through the webpack/ES module changes). Need to understand what replaced this functionality before deciding.

### Template Renames
4.0-dev renamed plugin templates:
- `Templates/partials/pluginlist.blade.php` → `Templates/includes/pluginlist.blade.php`
- `Templates/partials/plugintabs.blade.php` → `Templates/includes/plugintabs.blade.php`
- Deleted `Templates/partials/latestPlugins.blade.php`

### Other Modified Files
- `Controllers/CssLoader.php`, `Controllers/Myapps.php`
- `Hxcontrollers/Details.php`, `Hxcontrollers/Marketplaceplugins.php`
- `Models/InstalledPlugin.php`, `Models/MarketplacePlugin.php`
- `Repositories/Plugins.php`, `Services/Plugins.php`
- `Templates/marketplace.blade.php`, `Templates/myapps.blade.php`, `Templates/plugindetails.blade.php`
- `Templates/partials/marketplace/plugincontrols.blade.php`, `Templates/partials/plugin.blade.php`
- `register.php`

---

## Plugin composer.json Pattern Change

All plugins had their `"leantime/leantime": "3.0"` requirement **removed** from `composer.json`. This was a bulk cleanup — the version constraint was dropped because plugins are always loaded in-tree.

4.0-dev plugins still have `"require": { "leantime/leantime": "3.0" }` — these should be removed during the forward-port.

---

## New Plugin Registration Patterns (v3.6.2)

Several plugins adopted new registration patterns not present in 4.0-dev:

### Language Registration via RegistrationService
```php
// New pattern (v3.6.2) — used by AdvancedAuth, CustomFields
$registrationService = app()->makeWith(RegistrationService::class, ['pluginId' => 'PluginName']);
$registrationService->registerLanguageFiles();
```

### Listener Class References (replacing inline closures)
```php
// Old pattern (4.0-dev era)
EventDispatcher::add_event_listener("hook.name", new SomeClass());

// New pattern (v3.6.2)
EventDispatcher::add_event_listener('hook.name', SomeClass::class);
```

### Named Scheduled Tasks
```php
// Old pattern
$scheduler->call(function () { ... })->everyFourHours();

// New pattern (v3.6.2)
$scheduler->call(function () { ... })->name('telemetry')->everyFourHours();
```

---

## Template Evolution: .tpl.php vs .blade.php

Since 4.0-dev branched, 39 new Blade templates (`.blade.php`) and only 2 new `.tpl.php` files were added to plugins. The trend is clearly toward Blade-only templates.

4.0-dev's component system (DaisyUI + Blade components) will need to be applied to these new plugin templates. The largest template sets are:

| Plugin | New Blade Templates | Notes |
|--------|:---:|---|
| Copilot | 12 | Chat panel, action feedback, dashboard, prioritization |
| Billing | 10 | Subscription management, credit addons, confirmations |
| AdvancedAuth | 6 | OAuth settings, personal tokens |
| CalDAV | 4 | Connection management |
| RecurringTasks | 4 | Task automation UI |
| Notes | 3 | Pin/star features |
| Reactions | 2 | Emoji reaction UI |
| Crisp | 2 | Chat integration |

---

## Forward-Port Strategy for Plugins

### Step 1: Advance the Submodule Pointer

Since 4.0-dev's plugins commit is a direct ancestor of v3.6.2's, the simplest path is:

```bash
cd app/Plugins
git checkout 5ed0b93c   # Point to v3.6.2's commit (or main HEAD)
cd ../..
git add app/Plugins
git commit -m "Advance plugins submodule to v3.6.2 state"
```

This gives you all 926 commits of plugin work with zero conflicts in the submodule itself.

### Step 2: Reconcile Core Plugin Infrastructure

This is where conflicts live — in the **main repo**, not the submodule:

| File | Strategy |
|------|----------|
| `app/Core/Plugins/Plugins.php` | **Favor v3.6.2** — keep `getEnabledPluginPaths()` for PHAR/marketplace |
| `app/Core/Events/DispatchesEvents.php` | **Manual merge** — 4.0-dev's Laravel Event facade approach is directionally correct, but ensure `EventDispatcher` static calls still work for plugin `register.php` files |
| `app/Core/Events/EventDispatcher.php` | **Manual merge** — ensure both branches' enhancements are preserved |
| `app/Domain/Plugins/Services/Registration.php` | **Evaluate** — if 4.0-dev removed `addHeaderJs()`/`registerManifestFolder()`, what replaced them? If nothing, favor v3.6.2 |
| `app/Domain/Plugins/Templates/*` | **Favor 4.0-dev** for `partials/` → `includes/` rename if it's part of the component system; otherwise favor v3.6.2 |
| `app/Domain/Plugins/register.php` | **Manual merge** |

### Step 3: Componentize New Plugin Templates

After the submodule is advanced, the 39+ new Blade templates need to be converted to use 4.0-dev's DaisyUI component system. Priority order:

1. **Copilot** — 12 templates, most user-facing, will define the AI experience
2. **Billing** — 10 templates, revenue-critical
3. **AdvancedAuth** — 6 templates, security-critical
4. **CalDAV / RecurringTasks** — settings/management UIs

### Step 4: Verify Event Hook Compatibility

Run this check after merging to find any plugins still using old hook names:

```bash
cd app/Plugins
grep -r "middleware\.auth\." --include="*.php" | grep -v "authcheck" | grep -v "authenticat"
grep -r "middleware\.apiAuth\." --include="*.php"
grep -r "new.*Class()" --include="register.php"  # old instantiation pattern
```

### Step 5: Validate Plugin Loading

After forward-port:
- [ ] All 35+ plugins load without errors
- [ ] Copilot chat panel renders and AI responses work
- [ ] MCP server starts (`php artisan mcp:start`) and tools are discoverable
- [ ] AdvancedAuth OAuth flow completes (Keycloak callback)
- [ ] Billing subscription management works
- [ ] CalDAV sync connects
- [ ] RecurringTasks scheduling fires
- [ ] Reactions render on tickets/comments
- [ ] CustomFields display and save correctly
- [ ] Plugin marketplace loads and can install/update plugins
- [ ] PHAR-format plugins load correctly
- [ ] Plugin routes resolve via RouteLoader
- [ ] Plugin webpack builds complete (`npm run dev` from plugin dirs with webpack.mix.js)

---

## Llamadorian → Copilot Relationship

The Llamadorian plugin (68 files changed) and Copilot plugin (141 new files) are related but separate:

- **Llamadorian** — the original AI integration, still present, heavily modified since 4.0-dev. Routes updated, prioritization removed, Bedrock provider updates, bot removed.
- **Copilot** — the new full agent framework built alongside/on top of Llamadorian. Much larger, with structured outputs, multi-agent orchestration, memory, and observability.

Both plugins exist in v3.6.2. The forward-port needs both — Llamadorian for backward compatibility/base AI features, Copilot for the full agent experience.

---

## Scale Summary

| Metric | Value |
|--------|-------|
| Total commits on mainline not in 4.0-dev plugins | **926** |
| Files changed (excl. vendor/node_modules/dist/Language) | **793** |
| New plugins added | **7** (Copilot, McpServer, AdvancedAuth, Reactions, CalDAV, RecurringTasks, EnergyTracker) |
| Plugins removed | **3** (Mixpanel, VWO, .idea) |
| New Blade templates | **39** |
| Plugins at 4.0-dev branch point | **29** |
| Plugins at v3.6.2 | **33** |
| Core plugin infrastructure files modified on 4.0-dev | **14** |
| Event hook renames affecting plugins | **4+** (auth→authcheck pattern) |

---

*This document was compiled from local git analysis of the plugins submodule at `/Users/gloriafolaron/herd/leantime/app/Plugins` (commits `3156738` vs `5ed0b93c`), the main Leantime repo's `origin/4.0-dev` vs `v3.6.2` diff for core plugin infrastructure, and file-level inspection of new plugin architectures. It complements `leantime-4.0-dev-unique-changes.md` which covers the main repo's changes.*
