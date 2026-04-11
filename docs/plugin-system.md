# Plugin System

Leantime has a comprehensive plugin system that allows extending core functionality.

## Plugin Architecture

- Plugins reside in the `app/Plugins` directory (git submodule to private repo for commercial plugins)
- Each plugin is a self-contained package with its own domain structure
- Plugins can have their own vendors through Composer
- Two plugin formats: folder-based and phar-based (for marketplace plugins)

## Plugin Registration (`register.php`)

Registers event listeners and filters via `EventDispatcher`. The `Registration` service (`Domain\Plugins\Services\Registration`) provides a fluent API:
```php
$registration = new Registration('MyPlugin');
$registration->registerMiddleware([MyMiddleware::class]);
$registration->registerLanguageFiles(['en-US', 'de-DE']);
$registration->addMenuItem([...], 'project', ['main', 'submenu-key']);
$registration->addCss(['app.css']);
$registration->addHeaderJs(['vendor.js']);
$registration->addFooterJs(['app.js']);
```

## Plugin Loading Order

- **System plugins** (from `LEAN_PLUGINS` env): Loaded at boot during `discoverListeners()`, before middleware. Cannot be disabled via UI.
- **User plugins**: Loaded when `LoadPlugins` middleware fires (after session, install check, update check)
- Plugin `routes.php` files are loaded via `RouteLoader`

## Plugin Types

- **System**: Core enabled plugins defined in config. Always loaded, cannot be disabled via UI, load earlier in the stack
- **Marketplace**: From marketplace.leantime.io. Delivered as phar packages, require license key validation
- **Custom Folders**: Regular plugins or plugins in development

## Plugin Lifecycle

`discoverNewPlugins()` -> `installPlugin()` -> `enablePlugin()` -> `disablePlugin()` -> `removePlugin()`. Each plugin service class can implement `install()`, `uninstall()`, `enable()`, `disable()` hooks.

## Plugin License Keys and Validation

- Plugins can be purchased from the marketplace (marketplace.leantime.io).
- Each plugin needs to be installed with a license key which is stored in the database
- License Keys are perpetual however they are restricted by the number of users.
- Leantime checks number of active users in the system regularly and against the server.
- If a system has more users than allowed for a plugin the plugin is disabled. Data remains in the database
- Daily cron validates all marketplace plugin licenses

## Refactoring Considerations

- Move away from string-based event hooks to class-based events
- Implement a more robust dependency management system
- Standardize plugin activation/deactivation hooks
- Add versioning and compatibility checking
- Plugin updates should be handled automatically without having to enter license keys
