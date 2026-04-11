# Development Environment Setup

## Requirements

- PHP 8.2+
- MySQL 8.0+ or MariaDB 10.6+
- Required PHP extensions: BC Math, Ctype, cURL, DOM, Exif, Fileinfo, Filter, GD, Hash, LDAP, Multibyte String, MySQL, OPcache, OpenSSL, PCNTL, PCRE, PDO, Phar, Session, Tokenizer, Zip, SimpleXML

## Local Development with Docker (Recommended)

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

## Manual Local Development

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

## Build Commands

```bash
make install-deps-dev    # Install development dependencies
make install-deps        # Install production dependencies
make build-dev           # Build for development (with source maps)
make build               # Build for production
make clear-cache         # Clear cache
make package             # Package for release
npx mix                  # Build js/css using webpack (run in root or within a plugin)
```

## Testing Commands

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

## CLI Commands

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
