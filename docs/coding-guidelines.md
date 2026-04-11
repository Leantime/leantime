# Coding Guidelines

## Testing Strategy

**Framework**: Codeception v5.1 (wraps PHPUnit). All test targets are Docker-first via `make` commands.

**When to Run Tests**:
- Before making changes: Run relevant tests to establish baseline
- During development: Run unit tests for the specific domain being modified
- After implementation: Run full test suite for the affected areas
- Before committing: Always run code style checks and static analysis

**Test Selection**:
- For API changes: Run API-specific acceptance tests (`-g api`)
- For domain-specific changes: Run tests for that domain (e.g., `-g timesheet`)
- For core changes: Run full test suite
- For frontend changes: Test both functionality and styling

**Test tools**:
- PHPStan for static analysis (currently level 0)
- Laravel Pint for code style (primary tool; PHPCS also configured but Pint is preferred)
- Codeception v5.1 for both unit and acceptance testing
- Test groups: `api`, `timesheet`, `login`, `ticket`, `user`

Never ignore test failures. Fix failing tests before proceeding with new functionality. If tests are legitimately outdated, update them as part of the task.

## Security Guidelines

**Data Protection**:
- Never log sensitive user data (passwords, API keys, personal information)
- Use proper input validation and sanitization for all user inputs
- Follow the existing authentication and authorization patterns
- Be mindful of SQL injection prevention when working with database queries

**Plugin Development Security**:
- Validate plugin inputs and outputs
- Don't expose internal system information through plugin APIs
- Follow the principle of least privilege for plugin permissions

**Code Security Practices**:
- Use parameterized queries through the existing Repository pattern
- Validate file uploads and handle them securely
- Ensure proper session management
- Follow OWASP guidelines for web application security

## Performance Guidelines

**Database Operations**:
- Use the existing Repository pattern instead of direct queries
- Be mindful of N+1 query problems when working with related data
- Consider database indexes when adding new query patterns
- Use pagination for large result sets

**Frontend Performance**:
- Minimize JavaScript bundle size when adding new features
- Use HTMX for efficient partial page updates
- Optimize images and assets appropriately
- Follow existing patterns for lazy loading and caching
- Use HTMX for information updates and reloads, use JavaScript for interactivity

**Caching**:
- Leantime uses the Laravel cache either file-based or Redis
- Cache should be used wherever expensive operations are happening
- When Redis is available, check if admin has chosen Redis and auto-load config via respective ServiceProvider

## Code Style

We use Laravel Pint for code style (config at `.pint/pint.json`).

### Backwards Compatibility

Unless specifically called out DO NOT keep any old code or build any sort of backwards compatibility.

### Configs

All Laravel configs need to be stored in the laravelConfig file inside the core configuration folder. Any variables that should be editable by the user should be added to the sample.env file and exposed via `LEAN_*`. We do not load any custom PHP configs from the root config folder and as such things like artisan publish will not publish configs correctly. Instead the content needs to be added to laravelConfig. When Redis is available for a certain service (queue, cache, sessions etc) we should check if the admin has chosen to use Redis and then automatically load the Redis config via the respective ServiceProvider.

### Error Logging

When logging errors ALWAYS use the Log Facade (ensure it's included in the use statements).
Example: `Log::error($exception)`

DO NOT use the helper functions error_log()

### Strict Types

- Use strict types wherever possible (for returns and for parameters)
- When creating arrays evaluate whether a model/object should be used and create one if deemed appropriate

### Comments

- Add valid phpDoc comments to all methods and classes
- For each method that is changed verify that phpDoc comment exists and is aligned
- Methods in services that should be available to our JSON-RPC should include the @api doc comment

### DateTime Handling

Always use `CarbonImmutable` or the `dtHelper()` function class for all things datetime and have various macros to help with common date formats.
As a general rule all dates from the database are assumed to be in UTC and in the format YYYY-MM-DD HH:MM:SS.
Dates coming from the frontend/user are assumed in the user's timezone and their respective date format.
We have a DateTimeHelper class to parse common datetime formats we find, the dateTimeHelper should be used in most cases.

### Layer Enforcement

- Controllers should only call services NOT repositories. If a repo call is detected it should be refactored.
- Services can call repositories
- Be careful when calling domain services in other domain services as circular references can happen
