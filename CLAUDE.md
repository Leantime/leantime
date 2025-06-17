# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## About Leantime

Leantime is an open source project management system designed for non-project managers. It combines strategy, planning, and execution in an easy-to-use interface. The application is built with PHP, MySQL, and a modern JS frontend.

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
# Install development dependencies
make install-deps-dev

# Install production dependencies
make install-deps

# Build for development (with source maps)
make build-dev

# Build for production
make build

# Clear cache
make clear-cache

# Package for release
make package

# Build js/css using webpack (run in root or within a plugin)
npx mix

```

### Testing Commands
```bash
# Run static analysis
make phpstan

# Run code style checks
make test-code-style

# Fix code style issues
make fix-code-style

# Run unit tests
make unit-test

# Run acceptance tests
make acceptance-test

# Run specific acceptance test groups
# For API tests:
docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml exec leantime-dev php vendor/bin/codecept run -g api --steps

# For timesheet tests:
docker compose --file .dev/docker-compose.yaml --file .dev/docker-compose.tests.yaml exec leantime-dev php vendor/bin/codecept run -g timesheet --steps
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
    - `Auth/` - Authentication services
    - `Configuration/` - App configuration
    - `Controller/` - Base controllers
    - `Db/` - Database abstraction
    - `Domains/` - Base domain classes
    - `Events/` - Event system
    - `Files/` - File management
    - `Http/` - HTTP request handling
    - `Middleware/` - Request middleware
    - `Plugins/` - Plugin system
    - `Support/` - Helper utilities
    - `UI/` - Template handling
  - `Domain/` - Application domains, organized by feature
    - Each domain typically contains:
      - `Controllers/` - HTTP endpoints
      - `Hxcontrollers/` - HTMX-specific controllers
      - `Repositories/` - Data access
      - `Services/` - Business logic
      - `Models/` - Data structures
      - `Templates/` - View templates
      - `Js/` - Domain-specific JavaScript
  - `Plugins/` - Extension plugins
  - `Language/` - Internationalization files
- `bootstrap/` - Application bootstrap files
- `config/` - Configuration files
- `public/` - Web root directory
  - `assets/` - Static assets (CSS, JS, images)
  - `theme/` - Theme files
- `storage/` - Storage for logs, cache, and sessions
- `tests/` - Test files
  - `Acceptance/` - Acceptance tests
  - `Unit/` - Unit tests

### General Architecture Overview
The application is built on Laravel with a few custom components. It uses a plugin system for extensibility.
Leantime follows a domain-driven-architecture:

**Core**

Framework code (laravel) and any extended classes are in the `app/Core` folder.
Core manages all shared functionality and base features. 

**Domain**

Each module in Domain has several layers representing on domain: 

1. **Controllers** handle HTTP requests and delegate to services
2. **Services** contain business logic and orchestrate operations
3. **Repositories** access and manipulate data storage
4. **Models** represent data structures
5. **Templates** represents view files (using blade)
6. **Listeners** contain event listeners
7. **Jobs** are queueing jobs

**Plugins**
Plugins are installable domain modules living in app/Plugins
Each plugin follows the same structure as domain modules but also contains a composer.json file for plugin identification.
Plugins can be managed as folders or pre-packaged phar files. 


### Architecture Details

#### Config

System Administrators can configure Leantime using .env files or Environment variables. These need to be stored in the config/ folder.
We have a custom config loader in app/Core/Configuration that loads the env file, maps some old config parameters and merges it with laravel config. Configs can be extended by plugins.
Important: The list of ServiceProviders are also stored in laravelConfig.

#### Data Layer Architecture (To Be Refactored)

The current data layer consists of:

1. **Repository Classes**: Located in domain-specific `/Repositories` folders, these classes extend `Leantime\Core\Db\Repository` and provide the data access layer. They contain raw SQL queries and interact directly with the database.

2. **Models**: Located in domain-specific `/Models` folders, these are simple data structures with public properties. Current models are basic and don't leverage encapsulation or validation.

3. **Database Abstraction**: Currently using direct PDO queries with manual parameter binding through a custom database abstraction layer in `Core/Db/Db.php`.

4. **Table Naming Convention**: Database tables use a `zp_` prefix (e.g., `zp_projects`, `zp_users`).

The following areas will need refactoring for Doctrine integration:

- **Repository Pattern**: Current repositories mix domain logic with data access. They need to be refactored to use Doctrine's EntityManager.
- **Entity Definition**: Current models need to be converted to proper Doctrine entities with annotations/attributes for mapping.
- **SQL Statements**: Raw SQL queries need to be replaced with Doctrine's DQL or QueryBuilder.
- **Column Attributes**: The current `DbColumn` attribute will need to be replaced with Doctrine's mapping annotations.
- **Transactions**: Current manual transaction handling would be replaced with Doctrine's transaction management.
- **Relationship Management**: Current manual relationship handling would be replaced with Doctrine's relationship mappings.

#### HTTP Layer Architecture

Leantime uses a custom HTTP layer with the following components:

1. **Front Controller**: The `Frontcontroller` class (`Core/Controller/Frontcontroller.php`) acts as the main entry point, routing requests to the appropriate domain controller.

2. **Base Controller**: All domain controllers extend the `Core/Controller/Controller.php` base class, which provides common functionality.

3. **Request Types**: Different request types are handled via specialized implementations:
   - Standard web requests
   - API requests via `ApiRequest` class
   - HTMX requests via `HtmxRequest` class

4. **Response Handling**: Controllers return `Symfony\Component\HttpFoundation\Response` objects.

5. **Middleware**: The application uses a middleware stack for request processing:
   - `InitialHeaders`: Sets up basic HTTP headers
   - `TrustProxies`: Handles proxy configurations
   - `StartSession`: Initializes the session
   - `Installed`: Checks if the application is installed
   - `Updated`: Verifies the application is up to date
   - `LoadPlugins`: Loads enabled plugins
   - `Localization`: Sets up language settings
   - `AuthCheck`: Validates user authentication

#### Event System

Leantime has a rich event system located in `Core/Events/` that provides:

1. **Event Dispatching**: The `EventDispatcher` class handles events through a registry of listeners.

2. **Event Types**:
   - **Events**: Fire-and-forget notifications 
   - **Filters**: Allow modification of data passing through the system

3. **Plugin Integration**: The event system is used heavily for plugin integration, allowing plugins to hook into various parts of the application without modifying core code.

4. **Event Discovery**: Events and listeners are automatically discovered through `register.php` files in domains and plugins.

5. **Wildcards and Patterns**: Supports wildcard event names and pattern matching for flexible event subscription.

6. **String-Based Event Names** (Refactoring Target): Events are dispatched using string-based names, often dynamically generated based on class location or namespace. This approach creates brittleness when refactoring, as moving classes breaks event listeners. Future refactoring should consider:
   - Using class-based event objects instead of strings
   - Implementing event constants to centralize event name definitions
   - Creating an event registry or enum for type-safety
   - Implementing IDE tooling to identify event usage throughout the codebase

#### Service Layer Architecture

The service layer implements business logic and follows these principles:

1. **Domain Services**: Located in domain-specific `/Services` folders, these classes implement the `Leantime\Core\Domains\DomainService` interface.

2. **Responsibility**: Service classes encapsulate business rules and coordinate between repositories, often combining data from multiple repositories.

3. **Implementation Pattern**:
   - Services delegate data access to repositories
   - Services handle domain-specific validation rules
   - Services trigger events when important state changes occur
   - Services implement permission checks and authorization logic

4. **Filter System**: Services use a filter system to allow plugins to modify data before and after processing.

5. **API Exposure**: Most public methods in service classes are marked with `@api` annotation to indicate they are part of the stable API.

When refactoring for Doctrine:
- Services will need to work with Doctrine entities instead of array structures
- Transaction handling would be moved from repositories to services
- Hydration logic can be simplified using Doctrine's entity manager

#### JSONRPC API Architecture

Leantime provides it's users with a JsonRPC API. The api is a thin wrapper accessible through the API domain and provides a structured access to the service layers of all domains.

The API layer uses:

1. **JSON-RPC**: The primary API protocol exposed via `Jsonrpc.php` controller
2. **Response Format**: Standard JSON responses with consistent structure
3. **Authentication**: Token-based authentication with API keys
4. **Controllers**: API controllers follow a consistent pattern with `get`, `post`, `patch`, and `delete` methods

**Deprecated API controllers**
The api domain modules contains various api controllers for different modules in most cases returning json. This was used to enable javascript functionality.
The pattern has been deprecated and all javascript api calls should go through the jsonrpc api. 


#### Template System

Leantime uses a dual template system:

1. **PHP Templates**: Traditional `.tpl.php` files for most views
2. **Blade Templates**: Laravel's Blade engine (`.blade.php`) for newer features
3. **Template Composers**: Classes that prepare view data before rendering
4. **Theme Support**: Supports customizable themes through the `theme/` directory

**Shared View Folder**

There is a central `app/Views/` folder for various shared views, components and composers.
- layouts: to bootstrap the general skeleton of various layouts (blank, app, login/entry, registration).
- components: shared blade components
- composer: shared composers
- sections: various sections used in the skeletons for headers, footers, nav etc.

**HTMX for aynchronous calls**

Leantime is using HTMX for elements that should update asynchronously. The process is ongoing. 
The goal is that the main page controllers are loading minimal amounts of data to show the page and some shared components (think filters or similar) and all content is being loaded via htmx.
All htmx controllers are inside the HxControllers folder. Templates for htmx calls should be in templates/partials as they only represent a small part of the page content.
If a partial or htmx call represents an entity that may be used in various other places (ticket cards, project cards, user cards etc) a component should be created. 

#### Role Management, Authorization and Authentication
- Leantime uses a combination of Laravel's standard Authentication and Sanctum and Custom auth providers. 
- Each user can have a role which is currently hard coded
- Each user can be assigned to 1 client
- Users can be assigned to projects
- Roles give users access to data or parts of the system
- Additionally each user has specific project access. 
- - Projects can be either "Accessible to everyone", "accessible only by users within a client" or accessible by users directly assigned to the project only. 
- - Admins and Owners can access all projects

**API Authentication**

System admins and users can create API Keys. There are 2 types of keys
1. Leantime API Keys which act as service accounts and are handled like a regular user. The username is the api key name and password is the api-secret
2. Personal Access Tokens can be created by users (if the AdvancedAuth plugin is installed). Tokens can be used to authenticate the user owning them. We use Laravel Sanctum for this.

**Additional Auth providers**
Leantime supports LDAP and OIDC authentication natively but can also integrate additional providers via Laravel Socialite.

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

#### CachingW
- Leantimne uses the Laravel cache either file based or redis. 
- Cache should be used wherever expensive operations are happening. 

## Development Practices

### Plugin System

Leantime has a comprehensive plugin system that allows extending core functionality:

1. **Plugin Architecture**:
   - Plugins reside in the `app/Plugins` directory
   - Each plugin is a self-contained package with its own domain structure
   - Plugins can have their own vendors through Composer
   - Two plugin formats: folder-based and phar-based (for marketplace plugins)

2. **Plugin Registration**:
   - `register.php` file bootstraps the plugin
   - Plugins register event listeners and filters
   - Plugins can register middleware, language files, and menu items
   - `RegistrationService` facilitates registration of plugin components

3. **Plugin Loading**:
   - Plugins can be system plugins (configured in environment)
   - Plugins can be user-enabled through the admin interface
   - The `PluginManager` and `Plugins` service handle loading plugins

4. **Integration Points**:
   - Create event classes when adding new event hooks
   - String-based event hooks (a refactoring target) are depreacted
   - Extension via Service Provider pattern
   - UI extensions through templates and composers
   - API extensions through controllers

5. **Plugin Types**:
   - System: Core enabled plugins defined in config. These plugins are always loaded, cannot be disabled via UI and load earlier in the stack
   - Marketplace: Plugins from the Leantime marketplace. These plugins are delivered as phar packages and check for license keys
   - Plugin Folders: Regular plugins or plugins in development are in folders.

6. **Plugin license keys and validation**:
- Plugins can be purchased from the marketplace (marketplace.leantime.io). 
- Each plugin needs to be installed with a license key which is stored in the database
- License Keys are prepetual however they are restricted by the number of users. 
- Leantime checks number of active users in the system reegularly and against the server.
- If a system has more users than allowed for a plugin the plugin is disabled. Data remains in the database

7. **Refactoring Considerations**:
   - Move away from string-based event hooks to class-based events
   - Implement a more robust dependency management system
   - Standardize plugin activation/deactivation hooks
   - Add versioning and compatibility checking
   - Plugin updates should be handled automatically without having to enter license keys

### Frontend Development
The frontend uses:
- Mix of both old and newer javascript
- Modern ES6+ JavaScript
- Webpack for bundling (via Laravel Mix)
- Less CSS preprocessor
- Use HTMX for asynchronous requests (keep in mind that htmx controllers are organized under hxcontrollers in each domain)

The general guidance for frontend development is that information and updates should happen using htmx elements and endpoints. 
Javascript is used primarily to interact with the UI (think editors, drag and drop, etc) 

**CSS**
- The primary css files are inside of public/assets and included through the main.less file which gets built by npx mix
- We support theming which is mostly based on updating css variables. Themes are inside of public/themes and based on the selected theme either the dark or light css will be used which sets various css variables
- Many values like box-shadow, borders, paddings, colors, font sizes etc are available as css variables and we should always use those. 
- Leantime is moving towards using tailwind for most css. At this point all tailwind classes are prefixed with tw-*

### Event System
Features should use the event system to maintain loose coupling between components. Event System is custom extension of Laravel events and also includes options for filters.

### Routing
Leantime built a custom frontcontroller that routes requests by url structure to the appropriate domain controller.

### Testing
Code should be tested using:
- PHPStan for static analysis
- PHP_CodeSniffer for code style
- PHPUnit for unit testing
- Codeception for acceptance testing

## Specific Code Style Guidelines

### Code Style
We use laravel pint for code style. 

### Configs
All laravel configs need to be stored in the laravelConfig file inside the core configuration folder. Any variables that should be editable by the user should be
added to the sample.end file and exposed via `LEAN_*`. We do not load any custom php configs from the root config folder and as such things like artisaon publish will not publish configs correctly. Instead the content needs to be added to laravelConfig.
When redis is available for a certain service (queue, cache, sessions etc) we should check if the admin has chosen to use redis and then automatically load the redis config via the respective serviceProvider.

### Error Logging
When logging errors ALWAYS use the Log Facade NOT the helper (ensure it's included in the use statements). 
Example: `Log::error($exception)`

### Strict types
- Use strict types where ever possible (for returns and for parameters)
- When creating arrays evaluate whether a model/object should be used and create one if deemed appropriate

### Comments
- Add valid phpDoc comments to all methods and classes. 
- For each method that is changed verify that PhpDoc comment exists and is aligned
- Methods in services that should be available to our jsonRPC should include the @api doc comment

### DateTime Handling
We are using the CarbonImmutable class for all things datetime and have various macros to help with common date formats.
As a general rule all dates from the database are assumed to be in UTC and in the format YYYY-MM-DD HH:MM:SS 
Dates coming from the frontend/user are assumed in the user's timezone and their respective date format. 
We have a DateTimeHelper class to parse commone datetime formats we find, the dateTimehelper should be used in most cases.

### Layer enforcement
- Controllers should only call services NOT repositories. If a repo call is detected it should be refactored.
- Services can call repositories
- Be careful when calling domain services in other domain services as circular references can happen
- Services should validated input and throw exceptions when validation fails
