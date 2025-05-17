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

### Key Concepts

- **Domains**: Features are organized by domain (Tickets, Projects, Users, etc.)
- **Plugins**: Extend functionality through a plugin architecture
- **Events**: Uses event dispatching for loose coupling between components
- **Templates**: Uses PHP templates (.tpl.php) and Blade templates (.blade.php) for views

## Development Practices

### Plugin Development
Leantime is extendable via plugins. Plugins are located in `app/Plugins` and follow a specific structure:
- `register.php` - Plugin registration file
- `composer.json` - Plugin dependencies
- Standard domain-like structure (Controllers, Services, etc.)

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
