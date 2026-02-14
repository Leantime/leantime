# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## About Leantime

Leantime is an open source project management system designed for non-project managers. It combines strategy, planning, and execution in an easy-to-use interface. The application is built with PHP (Laravel), MySQL, and a JS frontend. Current version: 3.6.2.

## Current State & Active Migrations

These are ongoing architectural efforts. None need to be fixed proactively -- they provide context for understanding why the codebase has mixed patterns.

### 1. HTMX Migration (In Progress)
**Goal**: Replace jQuery AJAX and full-page reloads with HTMX partial updates.
**Status**: 8 of 56 domains have dedicated `Hxcontrollers/` with 19 total HxControllers. ~57 Blade templates and ~14 tpl.php files use HTMX attributes.
**Domains with HxControllers**: Tickets, Projects, Timesheets, Widgets, Menu, Notifications, Plugins, Help.
**Pattern**: Main page controllers load minimal data + skeleton; content loads via HTMX partials. New async work should use HTMX, not jQuery AJAX.

### 2. Template Migration (In Progress)
**Goal**: Move from legacy `.tpl.php` to Laravel Blade `.blade.php`.
**Status**: ~198 `.tpl.php` files (legacy) vs ~91 `.blade.php` files in domains + ~33 in shared Views. About 30% migrated.
- **Fully modernized (Blade-only)**: Dashboard, Gamecenter, Goalcanvas, Menu, Notifications, Plugins, Widgets
- **Partially modernized (mix)**: Auth, Calendar, Comments, Help, Projects, Tickets, Timesheets, Users
- **Fully legacy (TPL-only)**: All other canvas variants, Clients, Files, Ideas, Wiki, Sprints, Setting, etc.
**Pattern**: Main page views tend to stay `.tpl.php` while new partials and HTMX fragments use `.blade.php`. When touching templates, prefer Blade for new work.

### 3. Service Layer / JSON-RPC
**Current state**: Services are the business logic layer AND the JSON-RPC API surface. Any public method on a service class can be called via `leantime.rpc.{domain}.{service}.{method}`. The `@api` annotation marks intended API methods but is NOT enforced at runtime.

### 4. Plugin System (Private Submodule)
**Current state**: `app/Plugins/` is a git submodule pointing to a private repository for commercial plugins. In the OSS repo this directory is essentially empty. Three plugin types: system (env config, loads at boot), custom (folder), marketplace (phar + license key).

### 5. Event System (String-Based, Moving to Class-Based)
**Current state**: 100% string-based event names dynamically generated from class namespace (e.g., `leantime.domain.tickets.services.tickets.updateTicket.ticket_updated`). Only one class-based event exists: `Files/Events/FileUploaded.php` (boilerplate). The `DispatchesEvents` trait is mixed into nearly every core class. Future work should prefer class-based events where practical.

### 6. JavaScript Architecture (Outdated, Needs Componentization)
**Current state**: All JS uses a global `leantime` namespace with IIFE module pattern. ~7-8MB of JS loaded on every page (no code splitting). jQuery 3.7.1 + Bootstrap 2.x (ancient) still in use. TinyMCE 5.10.9 is 3.6MB alone. Both Moment.js and Luxon included (redundant). A file-based per-domain loading system was planned but remains commented out in `webpack.mix.js`. Eventually needs componentized architecture with code splitting.

## Development Environment Setup

### Requirements
- PHP 8.2+
- MySQL 8.0+ or MariaDB 10.6+
- Required PHP extensions: BC Math, Ctype, cURL, DOM, Exif, Fileinfo, Filter, GD, Hash, LDAP, Multibyte String, MySQL, OPcache, OpenSSL, PCNTL, PCRE, PDO, Phar, Session, Tokenizer, Zip, SimpleXML

### Local Development with Docker (Recommended)
```bash
# First build the development environment
make clean build

# Start the development server
make run-dev
```

This starts a development server on port 8090 with:
- Leantime app: http://localhost:8090
- MailDev (for email testing): http://localhost:8081
- phpMyAdmin: http://localhost:8082 (auth: leantime/leantime)
- S3Ninja (for S3 testing): http://localhost:8083

### Manual Local Development
```bash
# Install dependencies
make install-deps-dev

# Build for development
make build-dev

# Point your web server to the public/ directory
# Create MySQL database
# Copy config/.env.sample to config/.env and configure your database
# Navigate to <localdomain>/install
```

## Common Commands

### Build Commands
```bash
make install-deps-dev    # Install development dependencies
make install-deps        # Install production dependencies
make build-dev           # Build for development (with source maps)
make build               # Build for production
make clear-cache         # Clear cache
make package             # Package for release
npx mix                  # Build js/css using webpack (run in root or within a plugin)
```

### Testing Commands
```bash
make phpstan             # Run static analysis (level 0)
make test-code-style     # Run code style checks (Laravel Pint)
make fix-code-style      # Fix code style issues (Laravel Pint)
make unit-test           # Run unit tests (Docker)
make acceptance-test     # Run acceptance tests (Docker)

# Run specific acceptance test groups (inside Docker):
docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml exec leantime-dev php vendor/bin/codecept run -g api --steps
docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml exec leantime-dev php vendor/bin/codecept run -g timesheet --steps
# Available groups: api, timesheet, login, ticket, user
```

### CLI Commands
Leantime extended the standard Laravel artisan command and includes several command-line tools located in the `app/Command` directory that can be executed via:
```bash
php bin/leantime [command]
```

Common commands:
- `system:update` - Update the Leantime installation
- `plugin:enable [pluginname]` - Enable a specific plugin
- `plugin:disable [pluginname]` - Disable a specific plugin
- `plugin:install [pluginname]` - Install a plugin from the marketplace
- `plugin:list` - List all installed plugins
- `user:add` - Add a new user
- `setting:save [key] [value]` - Save a system setting

## Code Architecture

### Directory Structure

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
    - `Templates/components/` - Shared Blade components (`<x-global::componentName>`)
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

### General Architecture Overview
The application is built on Laravel 11 with significant custom components. It uses a plugin system for extensibility.
Leantime follows a domain-driven architecture:

**Core**

Framework code (Laravel) and any extended classes are in the `app/Core` folder.
Core manages all shared functionality and base features.

**Domain**

56 domain modules in `app/Domain/`. Each module has several layers representing one domain:

1. **Controllers** handle HTTP requests and delegate to services
2. **Services** contain business logic and orchestrate operations
3. **Repositories** access and manipulate data storage
4. **Models** represent data structures
5. **Templates** represent view files (Blade and legacy PHP)
6. **Listeners** contain event listeners
7. **Jobs** are queueing jobs

**Plugins**
Plugins are installable domain modules living in `app/Plugins/`.
Each plugin follows the same structure as domain modules but also contains a `composer.json` file for plugin identification.
Plugins can be managed as folders or pre-packaged phar files.

### Domain Module Reference

**Core Feature Domains**: Tickets, Projects, Users, Sprints, Timesheets, Calendar, Comments, Files, Wiki, Ideas, Reports, Notifications, Dashboard, Widgets, Menu, Tags, Reactions, Entityrelations, Audit, Read

**Canvas Domains** (14 variants extending `Canvas` base): Canvas (base), Cpcanvas, Dbmcanvas, Eacanvas, Emcanvas, Goalcanvas, Insightscanvas, Lbmcanvas, Leancanvas, Minempathycanvas, Obmcanvas, Retroscanvas, Riskscanvas, Sbcanvas, Smcanvas, Sqcanvas, Swotcanvas, Valuecanvas

**System Domains**: Api, Auth, Cron, CsvImport, Connector, Environment, Errors, Install, Ldap, Modulemanager, Oidc, Plugins, Queue, Setting, Strategy, TwoFA

**Backend-only Domains** (no UI): Audit, Entityrelations, Ldap, Reactions, Read, Tags, Queue

**Canvas Inheritance Pattern**: The `Canvas` base domain provides generic controllers, services, and repositories. Each variant extends the base with minimal code -- typically just overriding a `CANVAS_NAME` constant:
```php
class ShowCanvas extends \Leantime\Domain\Canvas\Controllers\ShowCanvas
{
    protected const CANVAS_NAME = 'cp';
}
```
Goalcanvas is the exception, having been fully modernized to Blade with its own service.

### Architecture Details

#### Application Boot Sequence

1. `public/index.php` loads helpers, autoloader, creates Application via `bootstrap/app.php`
2. `bootstrap/app.php` creates `Leantime\Core\Application` (extends Laravel's), binds HttpKernel, ConsoleKernel, ExceptionHandler, IncomingRequest
3. `Bootloader::getInstance()->boot($app)` captures request and routes to HttpKernel or ConsoleKernel
4. **HttpKernel bootstrappers** (in order): LoadEnvironmentVariables, **LoadConfig** (custom -- loads `laravelConfig.php` + Environment), HandleExceptions, RegisterFacades, RegisterProviders, BootProviders
5. **Middleware pipeline** processes the request (see Middleware section)
6. **Routing**: Tries Laravel routes first, falls back to Frontcontroller if no match

#### Config

System Administrators can configure Leantime using .env files or Environment variables. These need to be stored in the `config/` folder.

**Custom config loader** (`app/Core/Bootstrap/LoadConfig.php`):
- Creates `Environment` instance as config repository (NOT Laravel's standard Repository)
- Loads from `app/Core/Configuration/laravelConfig.php` (NOT from `config/` PHP files)
- Priority order: Environment Variables > .env file > PHP config file > DefaultConfig defaults
- Maps `#[LaravelConfig('dotted.key')]` attributes on `DefaultConfig` properties to Laravel config

**Important**: The list of ServiceProviders is stored in `laravelConfig.php`. ALL Laravel config (database, cache, session, auth, etc.) lives in this single file, not in separate `config/*.php` files. Standard `artisan publish` will NOT work correctly.

User-editable variables should be added to `config/.env.sample` and exposed via `LEAN_*` prefix.

#### Data Layer Architecture (To Be Refactored)

1. **Repository Classes**: Located in domain-specific `/Repositories` folders, these classes extend `Leantime\Core\Db\Repository` and provide the data access layer. They mix raw SQL queries with Laravel Query Builder depending on when code was written. The `dbcall()` method provides a wrapper that dispatches events around SQL execution.

2. **Models**: Located in domain-specific `/Models` folders, these are simple data structures with public properties. No ORM annotations, no validation, no encapsulation. Properties typically use `mixed` type hints. Some use `#[DbColumn('name')]` attributes for column mapping.

3. **Database Abstraction**: `Core/Db/Db.php` wraps Laravel's `DatabaseManager` (not raw PDO anymore). `Core/Db/DatabaseHelper.php` provides cross-database compatibility helpers for MySQL, PostgreSQL, and MS SQL Server.

4. **Table Naming Convention**: Database tables use a `zp_` prefix (e.g., `zp_projects`, `zp_users`).

The following areas will need refactoring for Doctrine integration:

- **Repository Pattern**: Current repositories mix domain logic with data access. They need to be refactored to use Doctrine's EntityManager.
- **Entity Definition**: Current models need to be converted to proper Doctrine entities with annotations/attributes for mapping.
- **SQL Statements**: Raw SQL queries need to be replaced with Doctrine's DQL or QueryBuilder.
- **Column Attributes**: The current `DbColumn` attribute will need to be replaced with Doctrine's mapping annotations.
- **Transactions**: Current manual transaction handling would be replaced with Doctrine's transaction management.
- **Relationship Management**: Current manual relationship handling would be replaced with Doctrine's relationship mappings.

#### HTTP Layer Architecture

**Dual Routing System**:
1. **Laravel Routes** (new, preferred): Standard `routes.php` files in domains and plugins, loaded by `RouteLoader`
2. **Frontcontroller** (legacy, deprecated but still handles most requests): Convention-based URL-to-class mapping

**Frontcontroller URL Convention** (`Core/Controller/Frontcontroller.php`):
```
/module/action           -> Domain\{Module}\Controllers\{Action}::get()|post()
/module/action/id        -> Domain\{Module}\Controllers\{Action}::get()|post() with id param
/module/action/id/method -> Domain\{Module}\Controllers\{Action}::method()
/hx/module/action        -> Domain\{Module}\Hxcontrollers\{Action}
```
Resolution order: Domain Controllers > Domain Hxcontrollers > Plugin Controllers > Plugin Hxcontrollers

**Two Controller Method Patterns** (both coexist):
1. **`run()` method (legacy, ~55 controllers)**: Single method handles GET and POST with inline `$_POST`/`$_GET` checks
2. **`get($params)` / `post($params)` (modern, ~83 controllers)**: Separate methods per HTTP verb. Returns `Response`. **Prefer this pattern for new code.**

**Request Types** (auto-detected via `RequestTypeDetector`):
- `IncomingRequest` - Standard web requests
- `ApiRequest` - API requests (adds `getAuthorizationHeader()`, `getAPIKey()`, `getBearerToken()`)
- `HtmxRequest` - HTMX requests (adds `isBoosted()`, `getTarget()`, `getTriggerName()`, etc.)

**Middleware Stack** (exact order in `HttpKernel.php`):
1. `TrustProxies` - Proxy trust validation
2. `StartSession` - Session init with locking and exponential backoff
3. `Installed` - Redirects to `/install` if not installed
4. `Updated` - Redirects to update if DB version behind
5. `LoadPlugins` - Fires events that trigger user plugin `register.php` loading
6. `InitialHeaders` - Security headers (CSP, X-Frame-Options) -- filterable by plugins
7. `AuthCheck` - Authentication (web guards + API guards, 2FA check, public route bypass)
8. `AuthenticateSession` - Password hash validation, Leantime user session data
9. `RequestRateLimiter` - Rate limits: login 20/min, API 100/min, general 10000/min
10. `HandleCors` - CORS handling
11. `ValidatePostSize` - POST size validation
12. `TrimStrings` - Whitespace trimming (except passwords)
13. `ConvertEmptyStringsToNull` - Empty string to null
14. `SetCacheHeaders` - Cache control with etag support
15. `Localization` - Language, timezone, date/time formats, CarbonImmutable macros
16. `CurrentProject` (domain middleware) - Sets active project context for non-HTMX/API requests

**Two-Pipeline Architecture**: After the core middleware stack, a second pipeline runs for plugin-registered middleware:
```php
// Core middleware -> Plugin middleware -> Router dispatch
```
Plugins register into this second pipeline via `Registration::registerMiddleware()`.

#### Event System

Leantime has a custom event system in `Core/Events/` that implements Laravel's `Dispatcher` interface but provides two parallel mechanisms (similar to WordPress hooks):

**Events** (fire-and-forget):
```php
self::dispatch_event('ticket_created', $payload);
```

**Filters** (modify data through a pipeline):
```php
$result = self::dispatch_filter('beforeReturnAllPlugins', $installedPlugins, ['enabledOnly' => $enabledOnly]);
```

**Event Name Convention**: Names are auto-generated from class namespace + method:
```
leantime.domain.tickets.services.tickets.updateTicket.ticket_updated
```
Moving a class changes all its event names -- this is why class-based events are the desired direction.

**Listener Registration** (in `register.php` files):
```php
// Class-based listener (calls handle() method)
EventDispatcher::add_event_listener(
    'leantime.domain.projects.services.projects.notifyProjectUsers.notifyProjectUsers',
    NotifyProjectUsers::class
);

// Closure listener with wildcard
EventDispatcher::addEventListener('leantime.domain.auth.*.userSignUpSuccess', function ($params) {
    $helperService = app()->make(\Leantime\Domain\Help\Services\Helper::class);
    $helperService->createDefaultProject(session('userdata.id'), session('userdata.role'));
});

// Filter listener with priority
EventDispatcher::add_filter_listener(
    'leantime.domain.menu.repositories.menu.getMenuStructure.menuStructures.project',
    function ($menu) { $menu['newItem'] = [...]; return $menu; },
    50  // lower = earlier execution
);
```

**Pattern Matching**: Supports `*` (any string), `?` (any char), `{RGX:pattern:RGX}` (inline regex).

**Blade Directives**: `@dispatchEvent('eventName')`, `@dispatchFilter('filterName', $data)`

**Event Discovery** (`discoverListeners()`): Called at boot, scans all `app/Domain/*/register.php` files + system plugin `register.php` files. User-enabled plugin register files load later via `LoadPlugins` middleware event.

**register.php Pattern Guide**: Domains that have `register.php`: Auth, CsvImport, Help, Install, Notifications, Plugins, Queue, Reports. These files:
- Register event/filter listeners via `EventDispatcher`
- Schedule cron jobs via Laravel Scheduler
- Hook into application lifecycle events
- All currently use string-based event names

#### Service Layer Architecture

The service layer implements business logic and follows these principles:

1. **Domain Services**: Located in domain-specific `/Services` folders, these classes implement the `Leantime\Core\Domains\DomainService` interface.

2. **Responsibility**: Service classes encapsulate business rules and coordinate between repositories, often combining data from multiple repositories.

3. **Implementation Pattern**:
   - Services delegate data access to repositories
   - Services handle domain-specific validation rules
   - Services trigger events when important state changes occur
   - Services implement permission checks and authorization logic
   - Use constructor-based DI with PHP 8 promoted properties
   - Use `DispatchesEvents` trait for event integration
   - Use `dispatch_filter()` for plugin hook points

4. **Filter System**: Services use a filter system to allow plugins to modify data before and after processing.

5. **API Exposure**: Most public methods in service classes are marked with `@api` annotation to indicate they are part of the stable API. Any public service method can be called via JSON-RPC at `leantime.rpc.{Domain}.{Service}.{method}` -- the `@api` annotation is documentation only, not enforced at runtime.

When refactoring for Doctrine:
- Services will need to work with Doctrine entities instead of array structures
- Transaction handling would be moved from repositories to services
- Hydration logic can be simplified using Doctrine's entity manager

#### JSONRPC API Architecture

Leantime provides its users with a JSON-RPC 2.0 API. The API is a thin wrapper accessible through the API domain (`app/Domain/Api/Controllers/Jsonrpc.php`) and provides structured access to the service layers of all domains.

**Method routing convention**:
```
leantime.rpc.{domain}.{methodname}                  # 4 segments (service = domain name)
leantime.rpc.{domain}.{servicename}.{methodname}     # 5 segments
```

**How it works**: The controller uses PHP Reflection to introspect service method parameters, matches request params by name, validates required params, and attempts type casting. Services are resolved via `app()->make()`.

**Authentication**: Two types:
1. **Leantime API Keys** (`x-api-key` header): Format `lt_{user}_{key}`, acts as service account
2. **Laravel Sanctum** (Bearer tokens): Personal access tokens (requires AdvancedAuth plugin)

**Deprecated API controllers**: The `app/Domain/Api/Controllers/` directory contains legacy REST-like controllers (Tickets.php, Projects.php, etc.) that return JSON. These are deprecated -- all new JS API calls should go through the JSON-RPC endpoint.

#### Template System

Leantime uses a dual template system actively migrating from PHP to Blade:

**Template types**:
- `.tpl.php` (~198 files) - Legacy PHP templates using `$tpl->get('variable')` pattern
- `.blade.php` (~91 in domains, ~33 in Views) - Modern Laravel Blade
- `.sub.php` (~19 files) - Reusable legacy template fragments via `$tpl->displaySubmodule()`
- `.inc.php` (~10 files) - Canvas base includes

**Shared View Folder** (`app/Views/`):
- `Templates/layouts/` - Layout skeletons: `app.blade.php` (main), `entry.blade.php` (login), `blank.blade.php`, `error.blade.php`, `registration.blade.php`
- `Templates/components/` - Shared Blade components: accordion, badge, button, dropdownPill, emojiinput, inlineLinks, inlineSelect, loader, loadingText, pageheader, selectable, tabs, undrawSvg, plus kanban sub-components
- `Templates/sections/` - header, footer, pageBottom, appAnnouncement
- `Composers/` - App, Header, Footer, Entry, PageBottom

**Component syntax**: `<x-global::componentName>` for shared, `<x-widgets::moveableWidget>` for domain-specific.

**Template rendering methods** (on `Template` class):
- `display($template, $layout, $code)` - Full page render with layout
- `displayPartial($template)` - Render without layout
- `displayFragment($viewPath, $fragment)` - HTMX fragment rendering
- `displaySubmodule($alias)` - Render legacy submodule
- `emptyResponse()` - Empty HTTP response

**HTMX for asynchronous calls**

Leantime is using HTMX for elements that should update asynchronously. The process is ongoing.
The goal is that the main page controllers are loading minimal amounts of data to show the page and some shared components (think filters or similar) and all content is being loaded via htmx.
All htmx controllers are inside the HxControllers folder. Templates for htmx calls should be in `templates/partials` as they only represent a small part of the page content.
If a partial or htmx call represents an entity that may be used in various other places (ticket cards, project cards, user cards etc) a component should be created.

**HTMX Pattern Guide**:

URL convention: `/hx/{module}/{controller}/{action}`

Creating an HxController:
```php
namespace Leantime\Domain\{Module}\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;

class MyController extends HtmxController
{
    // Required: points to a Blade partial
    protected static string $view = '{module}::partials.myPartial';

    // DI via init(), NOT __construct()
    public function init(MyService $service): void
    {
        $this->service = $service;
    }

    // Action methods are named semantically, not by HTTP verb
    public function get($params): void
    {
        $this->tpl->assign('data', $this->service->getData($params['id']));
    }

    public function save(): void
    {
        // Process $_POST
        $this->tpl->setNotification('Saved!', 'success');
        $this->setHTMXEvent('HTMX.ShowNotification');
    }
}
```

HTMX event coordination between components:
```php
// PHP: Define events as an enum for type-safety
enum HtmxTicketEvents: string {
    case UPDATE = 'ticket_update';
    case SUBTASK_UPDATE = 'subtasks_update';
}

// PHP: Trigger event in HxController
$this->setHTMXEvent(HtmxTicketEvents::UPDATE->value);
```
```html
<!-- Blade: Listen for events from other components -->
<div hx-get="/hx/tickets/ticketCard/get" hx-trigger="ticket_update from:body" hx-target="#card-123">
```

Common HTMX patterns used:
- Lazy loading: `hx-trigger="revealed"` (widgets load when scrolled into view)
- Cross-component updates: `hx-trigger="ticket_update from:body"`
- Loading indicators: `hx-indicator=".htmx-indicator"` with `<x-global::loadingText>`
- Preloading: `preload="mouseover"` (hover-preload for dropdowns)
- Notifications: `HTMX.ShowNotification` event triggers jQuery growl via global listener in `app.js`

**Batch template variable assignment** (common pattern in HxControllers):
```php
array_map([$this->tpl, 'assign'], array_keys($tplVars), array_values($tplVars));
```

#### Role Management, Authorization and Authentication
- Leantime uses a combination of Laravel's standard Authentication and Sanctum and Custom auth providers.
- Three auth guards: `leantime` (web, session-based), `sanctum` (token), `jsonRpc` (API)
- Each user can have a role which is currently hard coded
- Each user can be assigned to 1 client
- Users can be assigned to projects
- Roles give users access to data or parts of the system
- Additionally each user has specific project access.
  - Projects can be either "Accessible to everyone", "accessible only by users within a client" or accessible by users directly assigned to the project only.
  - Admins and Owners can access all projects

**API Authentication**

System admins and users can create API Keys. There are 2 types of keys:
1. Leantime API Keys which act as service accounts and are handled like a regular user. Format: `lt_{user}_{key}`. The username is the api key name and password is the api-secret
2. Personal Access Tokens can be created by users (if the AdvancedAuth plugin is installed). Tokens can be used to authenticate the user owning them. We use Laravel Sanctum for this.

**Additional Auth providers**
Leantime supports LDAP and OIDC authentication natively but can also integrate additional providers via Laravel Socialite (Authentik, Auth0, Gitea, GitHub, GitLab, Google, Keycloak, Microsoft, Okta, PropelAuth, EduID, SAML2).

## Frontend Architecture

### Build System
Laravel Mix 6.x (Webpack 5.x) -- configured in `webpack.mix.js`. Output goes to `public/dist/` with version-stamped filenames.

**JS bundles** (ALL loaded on every page via `header.blade.php`):
- `compiled-htmx` + `compiled-htmx-extensions` - HTMX core + head-support, preload, SSE extensions
- `compiled-frameworks` - jQuery 3.7.1 + Bootstrap 2.x
- `compiled-framework-plugins` - jQuery UI, Chosen.js, growl, tags input, nestedSortable
- `compiled-global-component` - Luxon, Moment, Tippy.js, Uppy, Croppie, Packery, Shepherd.js, Isotope, GridStack, jsTree, Mermaid, Marked
- `compiled-editor-component` - TinyMCE 5.10.9 + ~20 custom plugins (3.6MB)
- `compiled-calendar-component` - FullCalendar + iCal.js
- `compiled-table-component` - DataTables + plugins
- `compiled-gantt-component` - Snap.svg + custom Frappe Gantt
- `compiled-chart-component` - Chart.js + Luxon adapter
- `compiled-app` - Core app + ALL domain JS files via glob `./app/Domain/**/*.js`

### JavaScript Architecture
**Global namespace**: All JS uses `leantime` namespace with IIFE module pattern:
```javascript
leantime.ticketsController = (function () {
    function doSomething() { ... }
    return { doSomething: doSomething };
})();
```

**Domain JS files** (46 total in `app/Domain/*/Js/`): Pattern mirrors backend -- `{domain}Repository.js` for AJAX, `{domain}Service.js` for logic, `{domain}Controller.js` for UI/DOM.

**Guidance**: Use HTMX for data loading/updates. Use JS only for interactivity (editors, drag-and-drop, etc.). Use JSON-RPC endpoint when fetch is needed. When using fetch:
```javascript
fetch(url, { credentials: "include", headers: { 'X-Requested-With': 'XMLHttpRequest' } })
```

### CSS Architecture
**Three-layer system**:
1. **Third-party**: Bootstrap 2.x, jQuery UI, Font Awesome 6.5.2, library-specific CSS
2. **Custom components**: `public/assets/css/components/` -- structure.css, style.default.css, nav.css, kanban.css, forms.css, mobile.css, tables.css, etc.
3. **Tailwind 3.4.x**: Available with `tw-` prefix to avoid Bootstrap conflicts. Only `@tailwind components` and `@tailwind utilities` active (base disabled). Moving towards Tailwind for new CSS.

**CSS Variables (Design Tokens)**: The theme system is built on 100+ CSS custom properties. Always use these instead of hardcoded values:
- Colors: `--accent1`, `--accent2`, `--primary-color`, `--primary-font-color`, `--primary-background`, `--secondary-background`, `--layered-background`
- Typography: `--primary-font-family`, `--base-font-size`, `--font-size-xs` through `--font-size-xxxl`
- Layout: `--box-radius`, `--box-radius-small`, `--box-radius-large`, `--element-radius`, `--input-radius`
- Shadows: `--min-shadow`, `--regular-shadow`, `--large-shadow`, `--input-shadow`
- Z-index: `--zlayer-1` through `--zlayer-9`
- Glass: `--glass-blur`, `--glass-background`, `--glass-border`

### Theme System
Themes in `public/theme/{name}/` with `theme.ini`, `css/light.css`, `css/dark.css`.
Two built-in themes: **default** ("More") and **minimal** ("Less"), both with light/dark mode.
Fonts: Roboto (default), Atkinson Hyperlegible (accessibility), Shantell Sans.

## Coding Guidelines

### Task Approach Hierarchy

When handling user requests, follow this priority order:

1. **Simple Queries**: For straightforward questions about existing code, use Read/Grep tools directly
2. **Code Modifications**: For changes to existing functionality, analyze the current implementation first
3. **New Features**: For new functionality, research similar existing patterns before implementing
4. **Debugging**: For bug fixes, reproduce the issue first, then implement the fix
5. **Complex Tasks**: For multi-step operations, use TodoWrite to plan before executing

### Context Management

#### Working with Large Codebases
- Use search tools (Grep, Glob) strategically to find relevant code before reading files
- When multiple files might be relevant, batch tool calls to read them efficiently
- Focus on understanding the specific area of code related to the user's request

#### Clarifying Requirements
- Ask clarifying questions when the user's request is ambiguous
- When multiple implementation approaches are possible, present options to the user
- If unsure about existing patterns or conventions, research the codebase first

#### Efficient Tool Usage
- Use Task tool for complex searches that might require multiple rounds
- Batch independent tool calls in single responses
- Read related files together when working on connected functionality

### Testing Strategy

**Framework**: Codeception v5.1 (wraps PHPUnit). All test targets are Docker-first via `make` commands.

#### When to Run Tests
- **Before making changes**: Run relevant tests to establish baseline
- **During development**: Run unit tests for the specific domain being modified
- **After implementation**: Run full test suite for the affected areas
- **Before committing**: Always run code style checks and static analysis

#### Test Selection Guidelines
- For API changes: Run API-specific acceptance tests (`-g api`)
- For domain-specific changes: Run tests for that domain (e.g., `-g timesheet`)
- For core changes: Run full test suite
- For frontend changes: Test both functionality and styling

#### Test Failure Handling
- Never ignore test failures
- Fix failing tests before proceeding with new functionality
- If tests are legitimately outdated, update them as part of the task

### Security Guidelines

#### Data Protection
- Never log sensitive user data (passwords, API keys, personal information)
- Use proper input validation and sanitization for all user inputs
- Follow the existing authentication and authorization patterns
- Be mindful of SQL injection prevention when working with database queries

#### Plugin Development Security
- When working with plugins, ensure they follow the same security standards
- Validate plugin inputs and outputs
- Don't expose internal system information through plugin APIs
- Follow the principle of least privilege for plugin permissions

#### Code Security Practices
- Use parameterized queries through the existing Repository pattern
- Validate file uploads and handle them securely
- Ensure proper session management
- Follow OWASP guidelines for web application security

### Performance Guidelines

#### Database Operations
- Use the existing Repository pattern instead of direct queries
- Be mindful of N+1 query problems when working with related data
- Consider database indexes when adding new query patterns
- Use pagination for large result sets

#### File Operations
- Use batch tool calls when reading multiple related files
- Avoid reading large files unnecessarily - use targeted searches first
- Consider memory usage when processing large datasets

#### Frontend Performance
- Minimize JavaScript bundle size when adding new features
- Use HTMX for efficient partial page updates
- Optimize images and assets appropriately
- Follow existing patterns for lazy loading and caching
- Use htmx for information updates and reloads, use javascript for interactivity

#### Caching
- Leantime uses the Laravel cache either file-based or Redis.
- Cache should be used wherever expensive operations are happening.
- When Redis is available, check if admin has chosen Redis and auto-load config via respective ServiceProvider.

## Development Practices

### Plugin System

Leantime has a comprehensive plugin system that allows extending core functionality:

1. **Plugin Architecture**:
   - Plugins reside in the `app/Plugins` directory (git submodule to private repo for commercial plugins)
   - Each plugin is a self-contained package with its own domain structure
   - Plugins can have their own vendors through Composer
   - Two plugin formats: folder-based and phar-based (for marketplace plugins)

2. **Plugin Registration** (`register.php`):
   - Registers event listeners and filters via `EventDispatcher`
   - The `Registration` service (`Domain\Plugins\Services\Registration`) provides a fluent API:
     ```php
     $registration = new Registration('MyPlugin');
     $registration->registerMiddleware([MyMiddleware::class]);
     $registration->registerLanguageFiles(['en-US', 'de-DE']);
     $registration->addMenuItem([...], 'project', ['main', 'submenu-key']);
     $registration->addCss(['app.css']);
     $registration->addHeaderJs(['vendor.js']);
     $registration->addFooterJs(['app.js']);
     ```

3. **Plugin Loading Order**:
   - **System plugins** (from `LEAN_PLUGINS` env): Loaded at boot during `discoverListeners()`, before middleware. Cannot be disabled via UI.
   - **User plugins**: Loaded when `LoadPlugins` middleware fires (after session, install check, update check)
   - Plugin `routes.php` files are loaded via `RouteLoader`

4. **Plugin Types**:
   - System: Core enabled plugins defined in config. Always loaded, cannot be disabled via UI, load earlier in the stack
   - Marketplace: From marketplace.leantime.io. Delivered as phar packages, require license key validation
   - Custom Folders: Regular plugins or plugins in development

5. **Plugin Lifecycle**: `discoverNewPlugins()` -> `installPlugin()` -> `enablePlugin()` -> `disablePlugin()` -> `removePlugin()`. Each plugin service class can implement `install()`, `uninstall()`, `enable()`, `disable()` hooks.

6. **Plugin license keys and validation**:
   - Plugins can be purchased from the marketplace (marketplace.leantime.io).
   - Each plugin needs to be installed with a license key which is stored in the database
   - License Keys are perpetual however they are restricted by the number of users.
   - Leantime checks number of active users in the system regularly and against the server.
   - If a system has more users than allowed for a plugin the plugin is disabled. Data remains in the database
   - Daily cron validates all marketplace plugin licenses

7. **Refactoring Considerations**:
   - Move away from string-based event hooks to class-based events
   - Implement a more robust dependency management system
   - Standardize plugin activation/deactivation hooks
   - Add versioning and compatibility checking
   - Plugin updates should be handled automatically without having to enter license keys

### Event System
Features should use the event system to maintain loose coupling between components. Event System is custom extension of Laravel events and also includes options for filters. See the Event System section under Architecture Details for full documentation.

### Routing
Leantime has dual routing: Laravel routes (preferred for new code) and legacy Frontcontroller (convention-based URL-to-class mapping). See HTTP Layer Architecture for details.

### Testing
Code should be tested using:
- PHPStan for static analysis (currently level 0)
- Laravel Pint for code style (primary tool; PHPCS also configured but Pint is preferred)
- Codeception v5.1 for both unit and acceptance testing
- Test groups: `api`, `timesheet`, `login`, `ticket`, `user`

## Specific Code Style Guidelines

### Code Style
We use Laravel Pint for code style (config at `.pint/pint.json`).

### Backwards Compatibility
Unless specifically called out DO NOT keep any old code or build any sort of backwards compatibility.

### Configs
All laravel configs need to be stored in the laravelConfig file inside the core configuration folder. Any variables that should be editable by the user should be
added to the sample.env file and exposed via `LEAN_*`. We do not load any custom php configs from the root config folder and as such things like artisan publish will not publish configs correctly. Instead the content needs to be added to laravelConfig.
When redis is available for a certain service (queue, cache, sessions etc) we should check if the admin has chosen to use redis and then automatically load the redis config via the respective serviceProvider.

### Error Logging
When logging errors ALWAYS use the Log Facade (ensure it's included in the use statements).
Example: `Log::error($exception)`

DO NOT use the helper functions error_log()

### Strict types
- Use strict types where ever possible (for returns and for parameters)
- When creating arrays evaluate whether a model/object should be used and create one if deemed appropriate

### Comments
- Add valid phpDoc comments to all methods and classes.
- For each method that is changed verify that PhpDoc comment exists and is aligned
- Methods in services that should be available to our jsonRPC should include the @api doc comment

### DateTime Handling
Always use `CarbonImmutable` or the `dtHelper()` function class for all things datetime and have various macros to help with common date formats.
As a general rule all dates from the database are assumed to be in UTC and in the format YYYY-MM-DD HH:MM:SS
Dates coming from the frontend/user are assumed in the user's timezone and their respective date format.
We have a DateTimeHelper class to parse common datetime formats we find, the dateTimeHelper should be used in most cases.

### Layer enforcement
- Controllers should only call services NOT repositories. If a repo call is detected it should be refactored.
- Services can call repositories
- Be careful when calling domain services in other domain services as circular references can happen
- Services should validate input and throw exceptions when validation fails
