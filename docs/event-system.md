# Event System

Leantime has a custom event system in `Core/Events/` that implements Laravel's `Dispatcher` interface but provides two parallel mechanisms (similar to WordPress hooks):

## Events (Fire-and-Forget)

```php
self::dispatch_event('ticket_created', $payload);
```

## Filters (Modify Data Through a Pipeline)

```php
$result = self::dispatch_filter('beforeReturnAllPlugins', $installedPlugins, ['enabledOnly' => $enabledOnly]);
```

## Event Name Convention

Names are auto-generated from class namespace + method:
```
leantime.domain.tickets.services.tickets.updateTicket.ticket_updated
```
Moving a class changes all its event names -- this is why class-based events are the desired direction.

## Listener Registration

Registration happens in `register.php` files:

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

## Pattern Matching

Supports `*` (any string), `?` (any char), `{RGX:pattern:RGX}` (inline regex).

## Blade Directives

`@dispatchEvent('eventName')`, `@dispatchFilter('filterName', $data)`

## Event Discovery

`discoverListeners()` is called at boot, scanning all `app/Domain/*/register.php` files + system plugin `register.php` files. User-enabled plugin register files load later via `LoadPlugins` middleware event.

## register.php Pattern Guide

Domains that have `register.php`: Auth, CsvImport, Help, Install, Notifications, Plugins, Queue, Reports. These files:
- Register event/filter listeners via `EventDispatcher`
- Schedule cron jobs via Laravel Scheduler
- Hook into application lifecycle events
- All currently use string-based event names

Features should use the event system to maintain loose coupling between components. Future work should prefer class-based events where practical.
