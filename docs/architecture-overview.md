# Architecture Overview

Leantime is built on Laravel 11 with significant custom components. It follows a domain-driven architecture with a plugin system for extensibility.

## Directory Structure

- `app/` - Main application code
  - `Core/` - Framework core components
    - `Application/` - Application service providers
    - `Auth/` - Authentication services (guards, Sanctum tokens)
    - `Bootstrap/` - Custom bootstrap (LoadConfig)
    - `Configuration/` - App configuration (Environment, DefaultConfig, AppSettings, laravelConfig)
    - `Console/` - Console kernel
    - `Controller/` - Base controllers (Controller, HtmxController, Frontcontroller, Composer)
    - `Db/` - Database abstraction (Db, Repository, DbColumn, DatabaseHelper)
    - `Domains/` - Base domain interfaces (DomainService, DomainRepository, DomainModel)
    - `Events/` - Event system (EventDispatcher, DispatchesEvents trait)
    - `Exceptions/` - Exception handling
    - `Files/` - File management
    - `Http/` - HTTP handling (HttpKernel, IncomingRequest, ApiRequest, HtmxRequest)
    - `Middleware/` - Request middleware (16 middlewares)
    - `Plugins/` - Plugin infrastructure
    - `Routing/` - Route loading
    - `Support/` - Helper utilities (CarbonMacros, DateTimeHelper, Format, Cast)
    - `UI/` - Template handling (Template, Theme, ViewsServiceProvider)
  - `Domain/` - Application domains (56 modules), organized by feature
    - Each domain typically contains:
      - `Controllers/` - HTTP endpoints
      - `Hxcontrollers/` - HTMX-specific controllers
      - `Repositories/` - Data access
      - `Services/` - Business logic
      - `Models/` - Data structures
      - `Templates/` - View templates (`.tpl.php`, `.blade.php`, `partials/`)
      - `Js/` - Domain-specific JavaScript
      - `Composers/` - View composers
      - `Listeners/` - Event listeners
      - `Htmx/` - HTMX event enums
      - `Middleware/` - Domain middleware
      - `register.php` - Event/filter listener registration
  - `Views/` - Shared view files
    - `Templates/layouts/` - Layout skeletons (app, entry, blank, error, registration)
    - `Templates/components/` - Shared Blade components (`<x-globals::componentName>`)
    - `Templates/sections/` - Header, footer, nav sections
    - `Composers/` - Shared view composers (App, Header, Footer, Entry, PageBottom)
  - `Plugins/` - Extension plugins (git submodule to private repo)
  - `Language/` - Internationalization files (INI-based)
- `bootstrap/` - Application bootstrap files
- `config/` - Configuration files (.env, .env.sample)
- `public/` - Web root directory
  - `assets/` - Static assets (CSS, JS, images, fonts)
  - `dist/` - Built/compiled assets (output of `npx mix`)
  - `theme/` - Theme files (default, minimal)
- `storage/` - Storage for logs, cache, and sessions
- `tests/` - Test files (Codeception v5.1)
  - `Acceptance/` - Acceptance tests (Cest format, WebDriver + Selenium)
  - `Unit/` - Unit tests (extending Laravel TestCase)

## Three-Layer Architecture

**Core** (`app/Core/`): Framework code (Laravel) and extended classes. Manages all shared functionality and base features.

**Domain** (`app/Domain/`): 56 domain modules. Each module has several layers:
1. **Controllers** handle HTTP requests and delegate to services
2. **Services** contain business logic and orchestrate operations
3. **Repositories** access and manipulate data storage
4. **Models** represent data structures
5. **Templates** represent view files (Blade and legacy PHP)
6. **Listeners** contain event listeners
7. **Jobs** are queueing jobs

**Plugins** (`app/Plugins/`): Installable domain modules. Each plugin follows the same structure as domain modules but also contains a `composer.json` file for plugin identification. Can be managed as folders or pre-packaged phar files.

## Domain Module Reference

**Core Feature Domains**: Tickets, Projects, Users, Sprints, Timesheets, Calendar, Comments, Files, Wiki, Ideas, Reports, Notifications, Dashboard, Widgets, Menu, Tags, Reactions, Entityrelations, Audit, Read

**Canvas Domains** (14 variants extending `Canvas` base): Canvas (base), Cpcanvas, Dbmcanvas, Eacanvas, Emcanvas, Goalcanvas, Insightscanvas, Lbmcanvas, Leancanvas, Minempathycanvas, Obmcanvas, Retroscanvas, Riskscanvas, Sbcanvas, Smcanvas, Sqcanvas, Swotcanvas, Valuecanvas

**System Domains**: Api, Auth, Cron, CsvImport, Connector, Environment, Errors, Install, Ldap, Modulemanager, Oidc, Plugins, Queue, Setting, Strategy, TwoFA

**Backend-only Domains** (no UI): Audit, Entityrelations, Ldap, Reactions, Read, Tags, Queue

## Canvas Inheritance Pattern

The `Canvas` base domain provides generic controllers, services, and repositories. Each variant extends the base with minimal code -- typically just overriding a `CANVAS_NAME` constant:
```php
class ShowCanvas extends \Leantime\Domain\Canvas\Controllers\ShowCanvas
{
    protected const CANVAS_NAME = 'cp';
}
```
Goalcanvas is the exception, having been fully modernized to Blade with its own service.
