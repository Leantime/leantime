# Frontend Architecture

## Template System

Leantime uses a dual template system actively migrating from PHP to Blade:

**Template types**:
- `.tpl.php` (~198 files) - Legacy PHP templates using `$tpl->get('variable')` pattern
- `.blade.php` (~91 in domains, ~33 in Views) - Modern Laravel Blade
- `.sub.php` (~19 files) - Reusable legacy template fragments via `$tpl->displaySubmodule()`
- `.inc.php` (~10 files) - Canvas base includes

**Shared View Folder** (`app/Views/`):
- `Templates/layouts/` - Layout skeletons: `app.blade.php` (main), `entry.blade.php` (login), `blank.blade.php`, `error.blade.php`, `registration.blade.php`
- `Templates/components/` - Shared Blade components: accordion, badge, button, dropdownPill, emojiinput, inlineLinks, inlineSelect, loader, loadingText, pageheader, selectable, tabs, undrawSvg, plus kanban sub-components
- `Templates/sections/` - header, footer, pageBottom, appAnnouncement
- `Composers/` - App, Header, Footer, Entry, PageBottom

**Component syntax**: `<x-globals::componentName>` for shared, `<x-widgets::moveableWidget>` for domain-specific.

**Template rendering methods** (on `Template` class):
- `display($template, $layout, $code)` - Full page render with layout
- `displayPartial($template)` - Render without layout
- `displayFragment($viewPath, $fragment)` - HTMX fragment rendering
- `displaySubmodule($alias)` - Render legacy submodule
- `emptyResponse()` - Empty HTTP response

## HTMX Pattern Guide

Leantime is using HTMX for elements that should update asynchronously. The goal is that main page controllers load minimal data to show the page skeleton and all content loads via HTMX. All HTMX controllers are inside the `Hxcontrollers/` folder. Templates for HTMX calls should be in `templates/partials`. If a partial represents an entity used in various places (ticket cards, project cards, user cards etc) a component should be created.

**URL convention**: `/hx/{module}/{controller}/{action}`

**Creating an HxController**:
```php
namespace Leantime\Domain\{Module}\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;

class MyController extends HtmxController
{
    // Required: points to a Blade partial
    protected static string $view = '{module}::partials.myPartial';

    // DI via init(), NOT __construct()
    public function init(MyService $service): void
    {
        $this->service = $service;
    }

    // Action methods are named semantically, not by HTTP verb
    public function get($params): void
    {
        $this->tpl->assign('data', $this->service->getData($params['id']));
    }

    public function save(): void
    {
        // Process $_POST
        $this->tpl->setNotification('Saved!', 'success');
        $this->setHTMXEvent('HTMX.ShowNotification');
    }
}
```

**HTMX event coordination between components**:
```php
// PHP: Define events as an enum for type-safety
enum HtmxTicketEvents: string {
    case UPDATE = 'ticket_update';
    case SUBTASK_UPDATE = 'subtasks_update';
}

// PHP: Trigger event in HxController
$this->setHTMXEvent(HtmxTicketEvents::UPDATE->value);
```
```html
<!-- Blade: Listen for events from other components -->
<div hx-get="/hx/tickets/ticketCard/get" hx-trigger="ticket_update from:body" hx-target="#card-123">
```

**Common HTMX patterns**:
- Lazy loading: `hx-trigger="revealed"` (widgets load when scrolled into view)
- Cross-component updates: `hx-trigger="ticket_update from:body"`
- Loading indicators: `hx-indicator=".htmx-indicator"` with `<x-global::loadingText>`
- Preloading: `preload="mouseover"` (hover-preload for dropdowns)
- Notifications: `HTMX.ShowNotification` event triggers jQuery growl via global listener in `app.js`

**Batch template variable assignment** (common pattern in HxControllers):
```php
array_map([$this->tpl, 'assign'], array_keys($tplVars), array_values($tplVars));
```

## Build System

Laravel Mix 6.x (Webpack 5.x) -- configured in `webpack.mix.js`. Output goes to `public/dist/` with version-stamped filenames.

**JS bundles** (ALL loaded on every page via `header.blade.php`):
- `compiled-htmx` + `compiled-htmx-extensions` - HTMX core + head-support, preload, SSE extensions
- `compiled-frameworks` - jQuery 3.7.1 + Bootstrap 2.x
- `compiled-framework-plugins` - jQuery UI, Chosen.js, growl, tags input, nestedSortable
- `compiled-global-component` - Luxon, Moment, Tippy.js, Uppy, Croppie, Packery, Shepherd.js, Isotope, GridStack, jsTree, Mermaid, Marked
- `compiled-editor-component` - TinyMCE 5.10.9 + ~20 custom plugins (3.6MB)
- `compiled-calendar-component` - FullCalendar + iCal.js
- `compiled-table-component` - DataTables + plugins
- `compiled-gantt-component` - Snap.svg + custom Frappe Gantt
- `compiled-chart-component` - Chart.js + Luxon adapter
- `compiled-app` - Core app + ALL domain JS files via glob `./app/Domain/**/*.js`

## JavaScript Architecture

**Global namespace**: All JS uses `leantime` namespace with IIFE module pattern:
```javascript
leantime.ticketsController = (function () {
    function doSomething() { ... }
    return { doSomething: doSomething };
})();
```

**Domain JS files** (46 total in `app/Domain/*/Js/`): Pattern mirrors backend -- `{domain}Repository.js` for AJAX, `{domain}Service.js` for logic, `{domain}Controller.js` for UI/DOM.

**Guidance**: Use HTMX for data loading/updates. Use JS only for interactivity (editors, drag-and-drop, etc.). Use JSON-RPC endpoint when fetch is needed. When using fetch:
```javascript
fetch(url, { credentials: "include", headers: { 'X-Requested-With': 'XMLHttpRequest' } })
```

## CSS Architecture

**Three-layer system**:
1. **Third-party**: Bootstrap 2.x, jQuery UI, Font Awesome 6.5.2, library-specific CSS
2. **Custom components**: `public/assets/css/components/` -- structure.css, style.default.css, nav.css, kanban.css, forms.css, mobile.css, tables.css, etc.
3. **Tailwind 3.4.x**: Available with `tw-` prefix to avoid Bootstrap conflicts. Only `@tailwind components` and `@tailwind utilities` active (base disabled). Moving towards Tailwind for new CSS.

**CSS Variables (Design Tokens)**: The theme system is built on 100+ CSS custom properties. Always use these instead of hardcoded values. See [design-tokens.md](design-tokens.md) for the full reference.

Key token categories:
- Colors: `--accent1`, `--accent2`, `--primary-color`, `--primary-font-color`, `--primary-background`, `--secondary-background`, `--layered-background`
- Typography: `--primary-font-family`, `--base-font-size`, `--font-size-xs` through `--font-size-xxxl`
- Layout: `--box-radius`, `--box-radius-small`, `--box-radius-large`, `--element-radius`, `--input-radius`
- Shadows: `--min-shadow`, `--regular-shadow`, `--large-shadow`, `--input-shadow`
- Z-index: `--zlayer-1` through `--zlayer-9`
- Glass: `--glass-blur`, `--glass-background`, `--glass-border`

## Theme System

Themes in `public/theme/{name}/` with `theme.ini`, `css/light.css`, `css/dark.css`.
Two built-in themes: **default** ("More") and **minimal** ("Less"), both with light/dark mode.
Fonts: Roboto (default), Atkinson Hyperlegible (accessibility), Shantell Sans.
