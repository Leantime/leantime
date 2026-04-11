# AGENTS.md

Agent instructions for the Leantime codebase. Detailed documentation lives in `docs/`.

## About Leantime

Leantime is an open source project management system designed for non-project managers. Built with PHP (Laravel 11), MySQL, and a JS frontend. Current version: 3.6.2.

## Active Migrations

These are ongoing architectural efforts. They provide context for why the codebase has mixed patterns -- none need to be fixed proactively.

| Migration | Status | Key Detail |
|-----------|--------|------------|
| **HTMX** | In progress | 8/56 domains have `Hxcontrollers/`. New async work should use HTMX, not jQuery AJAX. |
| **Blade Templates** | ~30% done | ~198 `.tpl.php` (legacy) vs ~91 `.blade.php`. Prefer Blade for new work. |
| **Service Layer / JSON-RPC** | Active | Public service methods callable via `leantime.rpc.{domain}.{service}.{method}`. `@api` annotation is docs-only. |
| **Plugin System** | Submodule | `app/Plugins/` is a private submodule. Three types: system, custom, marketplace. |
| **Event System** | String-based | Moving to class-based events. Current: auto-generated names from namespace. Future: prefer class-based. |
| **JS Architecture** | Outdated | Global `leantime` namespace, IIFE pattern, ~7-8MB loaded per page. Needs componentization. |

## Quick Start

```bash
make clean build    # Build the dev environment
make run-dev        # Start dev server at http://localhost:8090
```

See [docs/development-setup.md](docs/development-setup.md) for full setup, build, test, and CLI commands.

## Critical Rules

These are non-obvious rules that cause bugs or style violations if missed:

**Config**: ALL Laravel config lives in `app/Core/Configuration/laravelConfig.php`, NOT in `config/*.php` files. `artisan publish` will NOT work correctly. User-editable variables use `LEAN_*` prefix in `config/.env.sample`.

**Database**: Tables use `zp_` prefix (e.g., `zp_projects`). Use the Repository pattern, never direct queries.

**Layer Enforcement**: Controllers call services only, NEVER repositories directly. Services call repositories. Be careful with cross-domain service calls (circular references).

**Code Style** (Laravel Pint, config at `.pint/pint.json`):
- Use strict types everywhere (returns and parameters)
- Add phpDoc comments to all methods and classes
- Methods exposed via JSON-RPC must include the `@api` doc comment
- Use `Log::error()` for error logging, NEVER `error_log()`
- Use `CarbonImmutable` or `dtHelper()` for all datetime handling
- DB dates are UTC `YYYY-MM-DD HH:MM:SS`; frontend dates are user-timezone

**Backwards Compatibility**: Unless specifically called out, DO NOT keep old code or build backwards compatibility.

**Frontend**:
- Use HTMX for data loading/updates; JS only for interactivity (editors, drag-and-drop)
- Tailwind CSS uses `tw-` prefix to avoid Bootstrap conflicts
- Component namespace is `x-globals::` (plural form)
- Use CSS custom properties (design tokens) instead of hardcoded values

**Routing**: Dual system -- Laravel routes (preferred for new code) and legacy Frontcontroller. See [docs/backend-architecture.md](docs/backend-architecture.md).

## Documentation

| Document | Description |
|----------|-------------|
| [docs/development-setup.md](docs/development-setup.md) | Environment setup, build commands, testing commands, CLI tools |
| [docs/architecture-overview.md](docs/architecture-overview.md) | Directory structure, domain module reference, three-layer architecture |
| [docs/backend-architecture.md](docs/backend-architecture.md) | Boot sequence, config system, data layer, HTTP layer, service layer, auth |
| [docs/frontend-architecture.md](docs/frontend-architecture.md) | Template system, HTMX patterns, build system, JS/CSS architecture, themes |
| [docs/event-system.md](docs/event-system.md) | Events, filters, listener registration, Blade directives, discovery |
| [docs/api.md](docs/api.md) | JSON-RPC 2.0 API, method routing, authentication |
| [docs/plugin-system.md](docs/plugin-system.md) | Plugin types, registration, loading order, lifecycle, licensing |
| [docs/coding-guidelines.md](docs/coding-guidelines.md) | Testing strategy, security, performance, code style rules |
| [docs/design-tokens.md](docs/design-tokens.md) | CSS custom properties, theming architecture, accessibility standards |
| [docs/component-coordination.md](docs/component-coordination.md) | Component standardization rules, canonical namespace, progress tracker |
