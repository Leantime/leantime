# Leantime Development Guidelines

This document provides essential information for developers working on the Leantime project.

## Build and Configuration Instructions

### Prerequisites
- PHP 8.x
- Composer
- Node.js and npm
- Docker and Docker Compose (for development and testing environments)

### Installation and Setup

1. **Install Dependencies**:
   ```bash
   # Install all dependencies (including dev dependencies)
   make install-deps-dev
   
   # Install production dependencies only
   make install-deps
   ```

2. **Build the Application**:
   ```bash
   # Development build
   make build-dev
   
   # Production build
   make build
   ```

3. **Run Development Environment**:
   ```bash
   make run-dev
   ```
   This will start a Docker container with the application.

4. **Clear Cache**:
   ```bash
   make clear-cache
   ```

## Testing Information

### Testing Framework

Leantime uses Codeception for testing, with the following test suites:
- Unit tests: For testing individual components
- Acceptance tests: For testing user interactions
- API tests: For testing API endpoints

### Running Tests

Tests are configured to run in Docker containers:

```bash
# Run unit tests
make unit-test

# Run acceptance tests
make acceptance-test

# Run API tests
make api-test
```

### Adding New Tests

1. **Unit Tests**:
   - Create test files in the `tests/Unit` directory
   - Mirror the application structure (e.g., `tests/Unit/app/Core` for testing `app/Core` components)
   - Extend the `\Unit\TestCase` class
   - Use method names prefixed with `test_`

   Example:
   ```php
   <?php
   
   namespace Test\Unit;
   
   class ExampleTest extends \Unit\TestCase
   {
       public function test_example(): void
       {
           // A simple test
           $this->assertTrue(true);
       }
   
       public function test_string_operations(): void
       {
           // A slightly more complex test
           $string = 'Hello, Leantime!';
           $this->assertEquals('Hello, Leantime!', $string);
           $this->assertStringContainsString('Leantime', $string);
           $this->assertStringStartsWith('Hello', $string);
           $this->assertStringEndsWith('!', $string);
       }
   }
   ```

2. **Acceptance Tests**:
   - Create test files in the `tests/Acceptance` directory
   - Configure browser testing in `Acceptance.suite.yml`

3. **API Tests**:
   - Create test files for API endpoints

### Important Notes for Testing

- Tests require Docker to be properly configured and running
- The test environment is set up with specific configurations in the TestCase classes
- Use the `make` commands to ensure the proper environment is used for testing

## Code Style and Development Practices

### Code Style

Leantime follows PSR-12 coding standards with some specific exclusions:

1. **PHP_CodeSniffer**:
   - Run code style checks: `make codesniffer`
   - Fix code style issues: `make codesniffer-fix`
   - Configuration is in `phpcs.xml`

2. **Laravel Pint**:
   - Test code style: `make test-code-style`
   - Fix code style: `make fix-code-style`
   - Configuration is in `.pint/pint.json`

3. **PHPStan**:
   - Run static analysis: `make phpstan`
   - Configuration is in `phpstan.neon`

### Key Code Style Rules

- PSR-12 with specific exclusions (see `phpcs.xml`)
- No unneeded braces in namespaces
- No unused imports
- Specific rules for comments and documentation

### Development Workflow

1. Install dependencies with `make install-deps-dev`
2. Make your changes
3. Run code style checks and fixes:
   ```bash
   make codesniffer
   make test-code-style
   make phpstan
   ```
4. Run tests to ensure your changes don't break existing functionality
5. Submit your changes

## Additional Resources

- The project uses a makefile for common tasks - check `make` commands for available operations
- Configuration files are in the `config` directory
- Templates use a blade-like syntax (`.blade.php` files)

- Project is based on Laravel but uses domain driven design architecture
- Not using eloquent but hard coded sql queries
- various core illuminate classes were overriten and adjusted
- Frontcontroller handles routing and routes based on url/file structure 
- Project recently implemented htmx and plans on expanding usage. 
