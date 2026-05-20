# Leantime — How the Whole Site Works

A developer-facing reference for understanding the full Leantime request lifecycle, architecture, and key subsystems.

---

## Table of Contents

1. [High-Level Overview](#1-high-level-overview)
2. [Directory Structure](#2-directory-structure)
3. [Boot & Request Lifecycle](#3-boot--request-lifecycle)
4. [Routing](#4-routing)
5. [Domain Module Structure](#5-domain-module-structure)
6. [Controllers](#6-controllers)
7. [Services](#7-services)
8. [Repositories & Database Layer](#8-repositories--database-layer)
9. [Template System](#9-template-system)
10. [HTMX Pattern](#10-htmx-pattern)
11. [Authentication & Authorization](#11-authentication--authorization)
12. [Event & Filter System](#12-event--filter-system)
13. [Frontend Architecture](#13-frontend-architecture)
14. [Plugin System](#14-plugin-system)
15. [Configuration](#15-configuration)
16. [CLI Commands](#16-cli-commands)

---

## 1. High-Level Overview

Leantime is a **Laravel 11** based project management system. It departs from standard Laravel conventions in several ways:

- **Custom routing fallback** (Frontcontroller) alongside Laravel routes
- **Domain-Driven Architecture** — 56 self-contained domain modules under `app/Domain/`
- **Dual template system** — legacy `.tpl.php` files and modern Blade `.blade.php`
- **Custom event system** — string-based events/filters (similar to WordPress hooks)
- **HTMX** for async partial page updates (replacing jQuery AJAX)
- **Custom config loader** — all Laravel config in one file, not in `config/*.php`

```
Browser Request
      │
      ▼
public/index.php
      │
      ▼
bootstrap/app.php  →  Creates Laravel Application (custom)
      │
      ▼
Bootloader::boot()
      │
      ▼
HttpKernel::handle()
      │
      ├──► Middleware Stack (15 core middleware)
      │
      ├──► Laravel Router  (if a routes.php match found)
      │         │
      │         └──► Controller::get() / post()
      │
      └──► Frontcontroller (legacy fallback)
                │
                └──► Domain\{Module}\Controllers\{Action}::get() / post()
```

---

## 2. Directory Structure

```
leantime/
├── app/
│   ├── Core/                   # Framework extensions & shared infrastructure
│   │   ├── Application/        # Service providers
│   │   ├── Auth/               # Guards, session, Sanctum
│   │   ├── Bootstrap/          # Custom config loader (LoadConfig)
│   │   ├── Configuration/      # Environment, DefaultConfig, laravelConfig.php
│   │   ├── Console/            # Console kernel
│   │   ├── Controller/         # Base controllers (Controller, HtmxController, Frontcontroller)
│   │   ├── Db/                 # Database wrapper (Db, Repository, DatabaseHelper)
│   │   ├── Domains/            # Base interfaces (DomainService, DomainRepository)
│   │   ├── Events/             # EventDispatcher + DispatchesEvents trait
│   │   ├── Http/               # HttpKernel, IncomingRequest, ApiRequest, HtmxRequest
│   │   ├── Middleware/         # 16 core middleware classes
│   │   ├── Plugins/            # Plugin infrastructure
│   │   ├── Routing/            # RouteLoader
│   │   ├── Support/            # Helpers (DateTimeHelper, Format, Cast, CarbonMacros)
│   │   └── UI/                 # Template engine (Template, Theme)
│   │
│   ├── Domain/                 # 56 business domain modules
│   │   ├── Tickets/
│   │   ├── Projects/
│   │   ├── Dashboard/
│   │   ├── Users/
│   │   └── ... (53 more)
│   │
│   ├── Views/                  # Shared views (used by all domains)
│   │   ├── Templates/
│   │   │   ├── layouts/        # app, entry, blank, error, registration
│   │   │   ├── components/     # <x-global::componentName>
│   │   │   └── sections/       # header, footer, pageBottom
│   │   └── Composers/          # App, Header, Footer, Entry, PageBottom
│   │
│   ├── Plugins/                # Git submodule (private commercial plugins)
│   └── Language/               # INI-based i18n files
│
├── bootstrap/
│   └── app.php                 # Creates Laravel Application, binds kernels
│
├── config/
│   ├── .env                    # Environment variables (DB, mail, auth, etc.)
│   └── .env.sample             # Template for .env
│
├── public/
│   ├── index.php               # Web entry point
│   ├── assets/                 # Source CSS (LESS), JS, images, fonts
│   └── dist/                   # Compiled webpack output (version-stamped)
│
├── storage/                    # Logs, cache, sessions
├── tests/                      # Codeception acceptance + unit tests
├── webpack.mix.js              # Frontend build config (Laravel Mix 6 / Webpack 5)
└── bin/leantime                # CLI entry point (php bin/leantime <command>)
```

---

## 3. Boot & Request Lifecycle

### Step-by-step

**1. `public/index.php`**
- Loads `vendor/autoload.php` and helper files
- Requires `bootstrap/app.php` to get the Application container
- Calls `Bootloader::getInstance()->boot($app)`

**2. `bootstrap/app.php`**
- Creates `Leantime\Core\Application` (extends Laravel's)
- Binds `HttpKernel`, `ConsoleKernel`, `ExceptionHandler`
- Registers `IncomingRequest` as the request class

**3. `Bootloader::boot($app)`**
- Detects request type (web/API/CLI)
- Instantiates the appropriate kernel
- Calls `HttpKernel::handle($request)`

**4. `HttpKernel` bootstrappers** (run in order):
```
LoadEnvironmentVariables
LoadConfig              ← custom: reads laravelConfig.php + .env into Environment
HandleExceptions
RegisterFacades
RegisterProviders
BootProviders
```

**5. Middleware stack** (runs for every HTTP request):
```
1.  TrustProxies            ← proxy header trust
2.  StartSession            ← PHP session init
3.  Installed               ← redirect to /install if not set up
4.  Updated                 ← redirect to update page if DB version behind
5.  LoadPlugins             ← loads user-enabled plugin register.php files
6.  InitialHeaders          ← security headers (CSP, X-Frame-Options)
7.  AuthCheck               ← authentication (web, API, 2FA, public routes)
8.  AuthenticateSession     ← validate session integrity
9.  RequestRateLimiter      ← login 20/min, API 100/min, general 10000/min
10. HandleCors              ← CORS
11. ValidatePostSize        ← max POST size
12. TrimStrings             ← whitespace trim (except passwords)
13. ConvertEmptyStringsToNull
14. SetCacheHeaders         ← etag support
15. Localization            ← language, timezone, date formats, CarbonImmutable macros
16. CurrentProject          ← sets active project context (non-HTMX/API only)
```

**6. Two-pipeline architecture:**
After core middleware, a second pipeline runs plugin-registered middleware (via `Registration::registerMiddleware()`).

**7. Routing** (see [Section 4](#4-routing))

**8. Response filters:**
- `beforeSendResponse` filter runs before the response is sent
- Allows plugins/domains to modify the final response

---

## 4. Routing

Leantime has two routing mechanisms that coexist.

### 4a. Laravel Routes (Modern, Preferred)

- Each domain can have a `routes.php` file
- Loaded at boot by `RouteLoader::loadRoutes()`
- Standard Laravel routing: `Route::get()`, `Route::post()`, etc.
- System plugin routes loaded before user plugins

```php
// Example: app/Domain/Files/routes.php
Route::get('/download/{id}', [Download::class, 'get']);
```

### 4b. Frontcontroller (Legacy Fallback)

When no Laravel route matches, `Frontcontroller` resolves the request by convention:

| URL Pattern | Maps To |
|---|---|
| `/module/action` | `Domain\{Module}\Controllers\{Action}::get()` |
| `/module/action/id` | same, with `id` param |
| `/module/action/id/method` | same, calls `method()` |
| `/hx/module/action` | `Domain\{Module}\Hxcontrollers\{Action}` |

**Resolution order:** Domain Controllers → Domain Hxcontrollers → Plugin Controllers → Plugin Hxcontrollers

### 4c. URL Examples

```
GET  /tickets/showTicket/42        →  Tickets\Controllers\ShowTicket::get(['id' => 42])
POST /projects/newProject          →  Projects\Controllers\NewProject::post($params)
GET  /hx/tickets/ticketCard        →  Tickets\Hxcontrollers\TicketCard::get($params)
POST /api/jsonrpc                  →  Api\Controllers\Jsonrpc::post()
```

---

## 5. Domain Module Structure

Each of the 56 domains follows a consistent structure:

```
app/Domain/{DomainName}/
├── Controllers/          # Full-page HTTP request handlers
├── Hxcontrollers/        # HTMX partial request handlers
├── Services/             # Business logic (and JSON-RPC API surface)
├── Repositories/         # Data access (raw SQL + PDO)
├── Models/               # Plain PHP data structures (no ORM)
├── Templates/            # Views
│   ├── *.tpl.php         # Legacy PHP templates
│   ├── *.blade.php       # Modern Blade templates
│   └── partials/         # HTMX fragment Blade files
├── Js/                   # Domain-specific JavaScript
├── Htmx/                 # HTMX event enums
├── Listeners/            # Event listener classes
├── Composers/            # View composers (optional)
├── Middleware/           # Domain middleware (optional)
└── register.php          # Event/filter listener registration (optional)
```

### Data flow within a domain:

```
HTTP Request
    │
    ▼
Controller::get($params)
    │  (calls)
    ▼
Service::someMethod()
    │  (calls)
    ▼
Repository::query()
    │  (SQL via PDO)
    ▼
Database (zp_* tables)
    │  (returns array/object)
    ▼
Service (may enrich, validate, fire events)
    │  (returns data)
    ▼
Controller
    │  ($tpl->assign('key', $data))
    ▼
Template::display($view)
    │
    ▼
HTML Response
```

---

## 6. Controllers

### Standard Controller (full-page)

Extends `Leantime\Core\Controller\Controller`.

**Modern pattern** (preferred — ~83 controllers):
```php
class ShowTicket extends Controller
{
    private Tickets $ticketService;

    public function init(Tickets $ticketService): void
    {
        $this->ticketService = $ticketService;
    }

    public function get(array $params): Response
    {
        $ticket = $this->ticketService->getTicket($params['id']);
        $this->tpl->assign('ticket', $ticket);
        return $this->tpl->display('tickets.showTicket');
    }

    public function post(array $params): Response
    {
        // handle form submission
    }
}
```

**Legacy pattern** (~55 controllers):
```php
class ShowTicket extends Controller
{
    public function run(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { /* ... */ }
        $this->tpl->display('tickets.showTicket');
    }
}
```

**Key rule:** Controllers call Services only — never Repositories directly.

### HtmxController (HTMX partials)

See [Section 10](#10-htmx-pattern).

---

## 7. Services

Services are the **business logic layer** and the **JSON-RPC API surface**.

```php
namespace Leantime\Domain\Tickets\Services;

use Leantime\Core\Domains\DomainService;
use Leantime\Core\Events\DispatchesEvents;

class Tickets implements DomainService
{
    use DispatchesEvents;

    public function __construct(
        private TicketsRepository $ticketRepository,
        private ProjectsService $projectsService,
    ) {}

    /**
     * @api  ← marks method as part of the JSON-RPC API
     */
    public function getTicket(int $id): array|false
    {
        $ticket = $this->ticketRepository->getTicket($id);
        return self::dispatch_filter('getTicket', $ticket);
    }

    public function updateTicket(array $data): bool
    {
        // validate, update, dispatch events
        $result = $this->ticketRepository->updateTicket($data);
        self::dispatch_event('ticket_updated', ['ticket' => $data]);
        return $result;
    }
}
```

**Rules:**
- Use constructor-based DI with promoted properties
- Validate input; throw exceptions on failure
- Dispatch events/filters at important state changes
- `@api` annotation documents JSON-RPC exposed methods (not enforced at runtime)
- Avoid calling other domain services unless necessary (watch for circular dependencies)

**JSON-RPC access pattern:**
```
POST /api/jsonrpc
{ "method": "leantime.rpc.tickets.getTicket", "params": { "id": 42 } }
```

---

## 8. Repositories & Database Layer

### Repository Base (`app/Core/Db/Repository.php`)

All repositories extend this base and use its fluent `dbcall()` interface:

```php
class Tickets extends Repository
{
    public function getTicket(int $id): object|false
    {
        return $this->dbcall(__FUNCTION__)
            ->prepare('SELECT * FROM zp_tickets WHERE id = :id')
            ->bindValue(':id', $id, PDO::PARAM_INT)
            ->execute()
            ->fetch(PDO::FETCH_OBJ);
    }

    public function getAllTickets(int $projectId): array
    {
        return $this->dbcall(__FUNCTION__)
            ->prepare('SELECT * FROM zp_tickets WHERE projectId = :pid ORDER BY dateCreated DESC')
            ->bindValue(':pid', $projectId, PDO::PARAM_INT)
            ->execute()
            ->fetchAll(PDO::FETCH_OBJ);
    }
}
```

### Table naming convention

All tables use the `zp_` prefix:

| Table | Domain |
|---|---|
| `zp_tickets` | Tickets |
| `zp_projects` | Projects |
| `zp_user` | Users |
| `zp_timesheets` | Timesheets |
| `zp_sprints` | Sprints |
| `zp_calendar` | Calendar |

### Models (`app/Domain/{Domain}/Models/`)

Plain PHP classes with public properties — **not Eloquent**:

```php
class Tickets
{
    public int $id;
    public string $headline;
    public ?string $description = null;
    public int $projectId;
    public string $status = 'new';
    public ?string $dateToFinish = null;
    // ...
}
```

### Database Helpers (`app/Core/Db/DatabaseHelper.php`)

- `arrayToPdoBindingString($array)` — generates `:p0,:p1,:p2` for IN() clauses
- `sanitizeToColumnString($col)` — safe column name for dynamic SQL
- `sanitizeComparitorString($op)` — validates operators (`=`, `<>`, `LIKE`, etc.)

### DateTime conventions

- All DB dates are **UTC**, format `YYYY-MM-DD HH:MM:SS`
- User-input dates are in the **user's timezone**
- Always use `CarbonImmutable` or `dtHelper()` — never raw `date()` or `strtotime()`

---

## 9. Template System

### Two template types

| Type | Files | Status | Used For |
|---|---|---|---|
| Legacy | `*.tpl.php` | ~198 files (70%) | Main views, complex pages |
| Modern | `*.blade.php` | ~91 domain + ~33 shared (30%) | Partials, HTMX fragments, new features |

**Fully modernized (Blade-only):** Dashboard, Gamecenter, Goalcanvas, Menu, Notifications, Plugins, Widgets

**Fully legacy (tpl.php only):** Clients, Files, Ideas, Wiki, Sprints, Setting, Canvas variants

### Layouts (`app/Views/Templates/layouts/`)

| Layout | Used For |
|---|---|
| `app.blade.php` | Main authenticated app (sidebar, header, content) |
| `entry.blade.php` | Login, auth pages (no sidebar) |
| `blank.blade.php` | Minimal layout |
| `error.blade.php` | Error pages |
| `registration.blade.php` | Registration flow |

### Shared Components (`app/Views/Templates/components/`)

Called as `<x-global::componentName>` in any Blade template:

```blade
<x-global::pageheader title="My Tasks" />
<x-global::button label="Save" type="submit" />
<x-global::badge text="In Progress" color="blue" />
<x-global::loadingText />
<x-global::accordion title="Details">...</x-global::accordion>
```

### Template assignment (from controllers)

```php
// Legacy pattern (tpl.php)
$this->tpl->assign('tickets', $tickets);
$this->tpl->display('tickets.showAll');

// HTMX partial
$this->tpl->displayFragment('tickets::partials.ticketCard', 'card-body');

// Empty response (after save, no HTML needed)
$this->tpl->emptyResponse();
```

### View Composers

Global data injected into all views via composers in `app/Views/Composers/`:

- **AppComposer** — user session, theme, app settings
- **HeaderComposer** — page title, meta
- **FooterComposer** — scripts, analytics
- **PageBottomComposer** — JS component requirements

---

## 10. HTMX Pattern

HTMX replaces jQuery AJAX for async partial page updates. URLs under `/hx/` route to `Hxcontrollers/`.

### HtmxController structure

```php
namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets;

class TicketCard extends HtmxController
{
    // Points to Blade partial template
    protected static string $view = 'tickets::partials.ticketCard';

    private Tickets $ticketService;

    // DI via init(), NOT __construct()
    public function init(Tickets $ticketService): void
    {
        $this->ticketService = $ticketService;
    }

    public function get(array $params): void
    {
        $ticket = $this->ticketService->getTicket($params['id']);
        $this->tpl->assign('ticket', $ticket);
        $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
        // Template rendered automatically from $view
    }

    public function save(): void
    {
        // process $_POST...
        $this->tpl->setNotification('Saved!', 'success');
        $this->setHTMXEvent('HTMX.ShowNotification');
        // HX-Trigger header sent to browser
    }
}
```

### HTMX events (cross-component communication)

Domains define events as enums for type safety:

```php
// app/Domain/Tickets/Htmx/HtmxTicketEvents.php
enum HtmxTicketEvents: string {
    case UPDATE        = 'ticket_update';
    case SUBTASK_UPDATE = 'subtasks_update';
}
```

**Triggering from PHP:**
```php
$this->setHTMXEvent(HtmxTicketEvents::UPDATE->value);
// Sends: HX-Trigger: {"ticket_update": null}
```

**Listening in Blade:**
```blade
<div
  hx-get="/hx/tickets/ticketCard/get"
  hx-trigger="ticket_update from:body"
  hx-target="#card-{{ $ticket['id'] }}"
  hx-swap="outerHTML"
>
```

### Common HTMX attribute patterns

| Pattern | Example |
|---|---|
| Lazy load on scroll | `hx-trigger="revealed"` |
| Trigger from another element | `hx-trigger="ticket_update from:body"` |
| Loading indicator | `hx-indicator=".htmx-indicator"` |
| Hover preload | `preload="mouseover"` |
| Show notification | Trigger `HTMX.ShowNotification` event |

### Batch template assignment shorthand

```php
$tplVars = ['data' => $data, 'labels' => $labels, 'user' => $user];
array_map([$this->tpl, 'assign'], array_keys($tplVars), array_values($tplVars));
```

---

## 11. Authentication & Authorization

### Auth guards

| Guard | Type | Used For |
|---|---|---|
| `leantime` | Session (web) | Standard browser UI |
| `sanctum` | Bearer token | Personal access tokens (AdvancedAuth plugin) |
| `jsonRpc` | API key | JSON-RPC API calls |

### Roles (hard-coded)

```
owner > admin > manager > teamlead > developer > editor > commenter (client)
```

- **commenter/client**: Can only access client portal, files, notifications
- **developer**: Cannot create top-level tasks; uses wiki/notes for personal todos
- **teamlead+**: Can create and manage tasks
- **admin/owner**: Access to all projects and system settings

### Session structure

After login, `session('userdata')` contains:

```php
[
    'id'             => 42,
    'name'           => 'Jane',
    'mail'           => 'jane@example.com',
    'role'           => 'teamlead',
    'clientId'       => null,
    'profileId'      => 7,
    'twoFAEnabled'   => false,
    'twoFAVerified'  => false,
    'settings'       => [ /* user preferences */ ],
    'isExternalAuth' => false,
]
```

### AuthCheck middleware flow

```
Request arrives
    │
    ├─ Is this a public route? (install, login, cron, i18n)
    │       └─ Yes → pass through
    │
    ├─ Is this an API request? (x-api-key header)
    │       └─ Validate API key format lt_{user}_{key}
    │
    ├─ Is session authenticated?
    │       └─ No → redirect to /auth/login
    │
    ├─ Is 2FA required but not verified?
    │       └─ Yes → redirect to /twoFA/verify
    │
    └─ Is role "commenter" on non-portal route?
            └─ Yes → redirect to /clientportal
```

### Project-level access

Projects have three visibility levels:
- **Everyone** — all authenticated users
- **Client** — users within the assigned client
- **Assigned** — only users directly assigned to the project

Admins and Owners bypass all project-level restrictions.

### API Authentication

Two key types:
1. **Leantime API Keys** — `lt_{user}_{key}` format, stored as hashed passwords in the `zp_user` table with role `api`
2. **Sanctum Bearer Tokens** — personal access tokens (requires AdvancedAuth plugin)

---

## 12. Event & Filter System

The custom event system lives in `app/Core/Events/`. It mirrors Laravel's Dispatcher interface but adds a WordPress-style filter pipeline.

### Events (fire-and-forget)

```php
// Dispatch
self::dispatch_event('ticket_updated', ['ticket' => $ticketData]);

// Auto-generated full event name:
// leantime.domain.tickets.services.tickets.updateTicket.ticket_updated
```

### Filters (pass data through a pipeline)

```php
// In a service or middleware — each listener can modify the value
$result = self::dispatch_filter('getTicket', $ticketData, ['id' => $id]);

// Returns the final (possibly modified) value after all listeners ran
```

### Listening to events

Listeners are registered in domain `register.php` files:

```php
// Class-based listener (calls handle() method)
EventDispatcher::add_event_listener(
    'leantime.domain.tickets.services.tickets.updateTicket.ticket_updated',
    SendTicketNotification::class
);

// Closure listener with wildcard
EventDispatcher::add_event_listener(
    'leantime.domain.auth.*.userSignUpSuccess',
    function ($params) {
        // runs on any auth method's sign-up success
    }
);

// Filter listener with priority (lower = runs first)
EventDispatcher::add_filter_listener(
    'leantime.domain.menu.repositories.menu.getMenuStructure.menuStructures.project',
    function ($menu) {
        $menu['customItem'] = ['label' => 'Custom', 'url' => '/custom'];
        return $menu;
    },
    50
);
```

### Pattern matching

- `*` — any string segment
- `?` — any single character
- `{RGX:pattern:RGX}` — inline regex

### Blade directives

```blade
@dispatchEvent('eventName')
@dispatchFilter('filterName', $data)
```

### Event name auto-generation

The calling class namespace becomes the event prefix:
```
Class:   Leantime\Domain\Tickets\Services\Tickets::updateTicket()
Hook:    ticket_updated
Result:  leantime.domain.tickets.services.tickets.updateTicket.ticket_updated
```

**Warning:** Renaming or moving a class changes all its event names.

### `DispatchesEvents` trait

Mixed into nearly every service and controller. Provides:
- `self::dispatch_event($hook, $payload, $context = '')`
- `self::dispatch_filter($hook, $payload, $params = [], $context = '')`

---

## 13. Frontend Architecture

### Build system

**Laravel Mix 6 (Webpack 5)** — configured in `webpack.mix.js`. Output: `public/dist/` with version-stamped filenames.

### JavaScript bundles (ALL loaded on every page)

| Bundle | Contents |
|---|---|
| `compiled-htmx` | HTMX core |
| `compiled-htmx-extensions` | head-support, preload, SSE extensions |
| `compiled-frameworks` | jQuery 3.7.1 + Bootstrap 2.x |
| `compiled-framework-plugins` | jQuery UI, Chosen.js, growl, tags input, nestedSortable |
| `compiled-global-component` | Luxon, Moment, Tippy, Uppy, Croppie, Packery, Shepherd, Isotope, GridStack, jsTree, Mermaid, Marked |
| `compiled-editor-component` | TinyMCE 5.10.9 + 20 custom plugins (3.6 MB) |
| `compiled-calendar-component` | FullCalendar + iCal.js |
| `compiled-table-component` | DataTables + plugins |
| `compiled-gantt-component` | Snap.svg + custom Frappe Gantt |
| `compiled-chart-component` | Chart.js + Luxon adapter |
| `compiled-app` | Core app + ALL domain JS via glob `./app/Domain/**/*.js` |

### JavaScript module pattern

```javascript
// IIFE module under the global leantime namespace
leantime.ticketsController = (function () {
    function init() { /* ... */ }
    function doSomething() { /* ... */ }

    return {
        init: init,
        doSomething: doSomething,
    };
})();
```

Domain JS files follow: `{domain}Controller.js`, `{domain}Service.js`, `{domain}Repository.js`

### When to use JS vs HTMX

| Use | When |
|---|---|
| **HTMX** | Loading/updating data from server, form submissions |
| **JavaScript** | Interactivity (drag-and-drop, editors, date pickers, animations) |
| **JSON-RPC fetch** | When HTMX is not suitable and you need a fetch call |

**Fetch pattern (when needed):**
```javascript
fetch(url, {
    credentials: "include",
    headers: { 'X-Requested-With': 'XMLHttpRequest' }
});
```

### CSS architecture

**Three layers:**

1. **Third-party** — Bootstrap 2.x, jQuery UI, Font Awesome 6.5.2
2. **Custom components** — `public/assets/css/components/` (structure, nav, kanban, forms, mobile, tables)
3. **Tailwind 3.4** — available with `tw-` prefix to avoid Bootstrap conflicts

**LESS source** → compiled to `public/dist/css/` via webpack.

### CSS design tokens (always use these)

```css
/* Colors */
--accent1, --accent2, --primary-color
--primary-font-color, --primary-background
--secondary-background, --layered-background

/* Typography */
--primary-font-family, --base-font-size
--font-size-xs → --font-size-xxxl

/* Layout */
--box-radius, --box-radius-small, --box-radius-large
--element-radius, --input-radius

/* Effects */
--min-shadow, --regular-shadow, --large-shadow, --input-shadow
--glass-blur, --glass-background, --glass-border

/* Z-index */
--zlayer-1 → --zlayer-9
```

### Theme system

- Themes in `public/theme/{name}/` with `theme.ini`
- Built-in themes: **default** ("More") and **minimal** ("Less")
- Each has `css/light.css` and `css/dark.css`
- Fonts: Roboto (default), Atkinson Hyperlegible (accessibility), Shantell Sans

---

## 14. Plugin System

### Plugin types

| Type | Loaded When | Format | Toggle |
|---|---|---|---|
| System | At boot (before middleware) | Folder | env config only |
| Custom/Folder | After `LoadPlugins` middleware | Folder | UI |
| Marketplace | After `LoadPlugins` middleware | PHAR + license | UI |

### Plugin directory structure

```
app/Plugins/{PluginName}/
├── composer.json          # Plugin identification (name, version)
├── routes.php             # Laravel routes (optional)
├── register.php           # Event/filter listener registration
├── Controllers/
├── Hxcontrollers/
├── Services/
├── Repositories/
├── Models/
├── Templates/
└── Listeners/
```

### Plugin registration API

```php
// Inside a plugin's register.php or service provider
$registration = new Registration('MyPlugin');
$registration->registerMiddleware([MyMiddleware::class]);
$registration->registerLanguageFiles(['en-US', 'de-DE']);
$registration->addMenuItem([
    'label' => 'My Feature',
    'url'   => '/myplugin/show',
    'icon'  => 'fa-star',
], 'project', ['main', 'submenu-key']);
$registration->addCss(['app.css']);
$registration->addHeaderJs(['vendor.js']);
$registration->addFooterJs(['app.js']);
```

### Plugin loading order

```
1. discoverListeners() ← scans all register.php files
2. System plugins:   loadSystemPluginRoutes()  (during HttpKernel bootstrap)
3. LoadPlugins middleware fires ← user plugins loaded here
4. User plugins:     loadUserPluginRoutes()    (after LoadPlugins)
```

### Plugin lifecycle hooks

```php
class MyPluginService
{
    public function install(): void   { /* create tables, seed data */ }
    public function uninstall(): void { /* drop tables, cleanup */ }
    public function enable(): void    { /* activate features */ }
    public function disable(): void   { /* deactivate features, data preserved */ }
}
```

### Marketplace license model

- Licenses are perpetual, restricted by user count
- Daily cron validates all marketplace plugin licenses against marketplace server
- If active users exceed license limit, plugin is disabled (data preserved)

---

## 15. Configuration

### Environment file (`config/.env`)

Key variables:

```dotenv
# Application
APP_URL=http://localhost:8090
APP_ENV=production
APP_DEBUG=false
TIMEZONE=UTC
LANGUAGE=en-US

# Database
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=leantime
DB_USERNAME=leantime
DB_PASSWORD=secret

# Mail
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_FROM_ADDRESS=noreply@example.com

# Cache / Sessions
LEAN_CACHE_DRIVER=file    # or redis
LEAN_SESSION_DRIVER=file  # or redis

# Plugins (system-level)
LEAN_PLUGINS=MyPlugin,AnotherPlugin

# External Auth
LEAN_OIDC_ENABLE=false
LEAN_LDAP_ENABLED=false
```

### Custom config loader (`app/Core/Bootstrap/LoadConfig.php`)

- Creates an `Environment` instance as the config repository
- Priority: **Env vars > .env file > PHP config > DefaultConfig defaults**
- Maps `#[LaravelConfig('dotted.key')]` attributes on `DefaultConfig` to Laravel config
- All Laravel config (DB, cache, session, auth, mail) lives in `laravelConfig.php`, **not** in `config/*.php`

> **Important:** Standard `artisan publish` will NOT work correctly because Laravel expects `config/*.php` files.

### Adding new config variables

1. Add `LEAN_MY_SETTING=default` to `config/.env.sample`
2. Add property to `app/Core/Configuration/DefaultConfig.php` with `#[LaravelConfig]` attribute if needed
3. Read via `app()->make(Environment::class)->get('LEAN_MY_SETTING')`

---

## 16. CLI Commands

Leantime extends the standard Laravel artisan console. The CLI entry point is `bin/leantime`.

```bash
php bin/leantime <command>
```

### Available commands (`app/Command/`)

| Command | Description |
|---|---|
| `system:update` | Run database migrations / update the installation |
| `plugin:enable {name}` | Enable a plugin |
| `plugin:disable {name}` | Disable a plugin |
| `plugin:install {name}` | Install a plugin from the marketplace |
| `plugin:list` | List all installed plugins |
| `user:add` | Create a new user |
| `setting:save {key} {value}` | Save a system setting |

### Cron

Leantime uses the Laravel Scheduler. Cron jobs are registered via `register.php` files using the scheduler:

```php
// In a domain/plugin register.php
Schedule::call(function () {
    // run periodic job
})->daily();
```

The system cron endpoint is `/cron/run` (bypasses auth in `AuthCheck` middleware).

---

## Quick Reference: Adding a New Feature

### 1. New full-page controller action

```
app/Domain/{Domain}/Controllers/MyAction.php
app/Domain/{Domain}/Templates/myAction.blade.php
```

### 2. New HTMX partial

```
app/Domain/{Domain}/Hxcontrollers/MyPartial.php
app/Domain/{Domain}/Templates/partials/myPartial.blade.php
```

URL: `GET /hx/{domain}/myPartial`

### 3. New service method (also API-exposed)

Add to `app/Domain/{Domain}/Services/{Domain}.php` with `@api` annotation.

### 4. New event listener

Add listener registration to `app/Domain/{Domain}/register.php`.

### 5. New shared Blade component

Add to `app/Views/Templates/components/myComponent.blade.php`.

Use as: `<x-global::myComponent />`

---

*Document reflects Leantime v3.6.2 codebase. Architecture is in active migration (HTMX, Blade templates, class-based events).*
