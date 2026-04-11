# Backend Architecture

## Application Boot Sequence

1. `public/index.php` loads helpers, autoloader, creates Application via `bootstrap/app.php`
2. `bootstrap/app.php` creates `Leantime\Core\Application` (extends Laravel's), binds HttpKernel, ConsoleKernel, ExceptionHandler, IncomingRequest
3. `Bootloader::getInstance()->boot($app)` captures request and routes to HttpKernel or ConsoleKernel
4. **HttpKernel bootstrappers** (in order): LoadEnvironmentVariables, **LoadConfig** (custom -- loads `laravelConfig.php` + Environment), HandleExceptions, RegisterFacades, RegisterProviders, BootProviders
5. **Middleware pipeline** processes the request (see Middleware section)
6. **Routing**: Tries Laravel routes first, falls back to Frontcontroller if no match

## Config System

System Administrators can configure Leantime using .env files or Environment variables. These need to be stored in the `config/` folder.

**Custom config loader** (`app/Core/Bootstrap/LoadConfig.php`):
- Creates `Environment` instance as config repository (NOT Laravel's standard Repository)
- Loads from `app/Core/Configuration/laravelConfig.php` (NOT from `config/` PHP files)
- Priority order: Environment Variables > .env file > PHP config file > DefaultConfig defaults
- Maps `#[LaravelConfig('dotted.key')]` attributes on `DefaultConfig` properties to Laravel config

**Important**: The list of ServiceProviders is stored in `laravelConfig.php`. ALL Laravel config (database, cache, session, auth, etc.) lives in this single file, not in separate `config/*.php` files. Standard `artisan publish` will NOT work correctly.

User-editable variables should be added to `config/.env.sample` and exposed via `LEAN_*` prefix.

## Data Layer Architecture (To Be Refactored)

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

## HTTP Layer Architecture

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

## Service Layer Architecture

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

## Role Management, Authorization and Authentication

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
