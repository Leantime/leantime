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
Leantime includes several command-line tools located in the `app/Command` directory that can be executed via:
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

### Architecture Overview

Leantime follows a layered architecture:

1. **Controllers** handle HTTP requests and delegate to services
2. **Services** contain business logic and orchestrate operations
3. **Repositories** access and manipulate data storage
4. **Models** represent data structures

The application is built on a custom PHP framework with Laravel components. It uses a plugin system for extensibility.

### Data Layer Architecture (To Be Refactored)

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

### HTTP Layer Architecture

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

### Event System

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

### Service Layer Architecture

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

### API Architecture

The API layer uses:

1. **JSON-RPC**: The primary API protocol exposed via `Jsonrpc.php` controller
2. **REST Endpoints**: Domain-specific REST controllers in each domain's `Controllers` folder
3. **Response Format**: Standard JSON responses with consistent structure
4. **Authentication**: Token-based authentication with API keys
5. **Controllers**: API controllers follow a consistent pattern with `get`, `post`, `patch`, and `delete` methods

### Template System

Leantime uses a dual template system:

1. **PHP Templates**: Traditional `.tpl.php` files for most views
2. **Blade Templates**: Laravel's Blade engine (`.blade.php`) for newer features
3. **Template Composers**: Classes that prepare view data before rendering
4. **Theme Support**: Supports customizable themes through the `theme/` directory

### Key Concepts

- **Domains**: Features are organized by domain (Tickets, Projects, Users, etc.)
- **Plugins**: Extend functionality through a plugin architecture
- **Events**: Uses event dispatching for loose coupling between components
- **Front Controller**: Custom routing system that maps URLs to the appropriate domain controller
- **HTMX Integration**: Modern UI updates use HTMX for partial page updates without full page reloads

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
   - String-based event hooks (a refactoring target)
   - Extension via Service Provider pattern
   - UI extensions through templates and composers
   - API extensions through controllers

5. **Plugin Types**:
   - Custom: Local folder-based plugins
   - System: Core enabled plugins defined in config
   - Marketplace: Plugins from the Leantime marketplace

6. **Refactoring Considerations**:
   - Move away from string-based event hooks to class-based events
   - Implement a more robust dependency management system
   - Standardize plugin activation/deactivation hooks
   - Add versioning and compatibility checking

### Frontend Development
The frontend uses:
- Mix of both old and newer javascript
- Modern ES6+ JavaScript
- Webpack for bundling (via Laravel Mix)
- Less CSS preprocessor
- Use HTMX for asynchronous requests (keep in mind that htmx controllers are organized under hxcontrollers in each domain)

### Database Access
Database operations should use the Repository pattern through the appropriate Repository classes.

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
