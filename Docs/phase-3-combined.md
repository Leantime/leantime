# Phase 3: Canvas Domains, Wiki & JS Optimization — Complete Summary & Requirements

## Claude Code Execution Prompt

```
Read these documents in order before starting Phase 3:
1. Docs/phase-0-complete-summary.md — Component inventory, frontend library decisions
2. Docs/phase-1-complete-summary.md — Modal system, canvas domain architecture
3. Docs/phase-2-complete-summary.md — Shared component library, jQuery dependency map
4. Docs/phase-3-combined.md — THIS FILE: canvas domain plan, JS optimization strategy
5. Docs/phase-2-requirements.md — Conversion patterns (reuse for canvas/wiki templates)

Phase 3 Tasks (execute in order):
3.1 — Canvas domain template conversion (shared templates → Blade, fixes 19 domains)
3.2 — Goalcanvas DaisyUI 5 update (already Blade)
3.3 — Ideas domain conversion (.tpl.php → Blade)
3.4 — Wiki domain conversion (.tpl.php → Blade)
3.5 — JS bundle splitting (eager → lazy loading for domain controllers)
3.6 — jQuery reduction pass 1 (remove jQuery from new components, modernize key controllers)
3.7 — Remove dead JS libraries (nyroModal, TinyMCE skin CSS, unused jQuery plugins)

CRITICAL CONSTRAINTS:
- Canvas shared templates affect 19 domains — test 3+ canvas types after changes
- DO NOT break jQuery UI Sortable (kanban) — that stays until Phase 4
- DO NOT remove jQuery itself — reduce usage, don't eliminate
- Domain JS glob import (import.meta.glob eager) → convert to dynamic import()
- Keep DataTables, FullCalendar, Frappe Gantt untouched
```

---

## 1. Canvas Domain Template Architecture

### Shared Templates (Canvas/Templates/)

These 9 files serve 16 canvas domains directly + 3 with custom overrides:

| Template | Purpose | Modal Triggers | Complexity |
|----------|---------|---------------|-----------|
| canvasDialog.inc.php | Item create/edit dialog (main modal) | None (is the modal) | 🔴 HIGH |
| canvasComment.inc.php | Comment on canvas item | None (is the modal) | 🟡 MED |
| boardDialog.php | Board create/edit | None (is the modal) | 🟡 MED |
| element.inc.php | Single canvas element display | 3 hash triggers | 🟡 MED |
| showCanvasTop.inc.php | Canvas header with controls | 4 hash triggers | 🟡 MED |
| modals.inc.php | Modal includes container | — | 🟢 LOW |
| showCanvasBottom.inc.php | Canvas footer | — | 🟢 LOW |
| showCanvas.inc.php | Main canvas grid layout | — | 🟡 MED |
| canvasChart.inc.php | Canvas chart view | — | 🟡 MED |

### Canvas Domains Using Shared Templates

These 16 domains have NO custom templates — they rely entirely on Canvas/Templates/:

```
Cpcanvas, Dbmcanvas, Eacanvas, Emcanvas, Insightscanvas, Lbmcanvas,
Leancanvas, Logicmodelcanvas, Minempathycanvas, Obmcanvas, Retroscanvas,
Riskscanvas, Sbcanvas, Smcanvas, Sqcanvas, Swotcanvas
```

Each has: 5 .tpl.php files (showCanvas, editCanvasItem, editCanvasComment, delCanvas, delCanvasItem) + 1 JS controller.

**These .tpl.php files are thin wrappers** that include the shared templates. Conversion pattern:
```php
// Current (e.g., Leancanvas/Templates/showCanvas.tpl.php):
<?php $canvasName = 'lean'; include __DIR__ . '/../../Canvas/Templates/showCanvas.inc.php'; ?>

// After (Leancanvas/Templates/showCanvas.blade.php):
@include('canvas::showCanvas', ['canvasName' => 'lean'])
```

### Domains with Custom Templates

| Domain | Custom Templates | Action |
|--------|-----------------|--------|
| Goalcanvas | canvasDialog.blade.php, bigRockDialog.blade.php, showCanvas.blade.php, dashboard.blade.php (all Blade ✅) | Update to DaisyUI 5 components |
| Valuecanvas | canvasDialog.tpl.php (custom) | Convert to Blade |
| Ideas | ideaDialog.tpl.php, boardDialog.php, showBoards.tpl.php, advancedBoards.tpl.php | Convert to Blade |

### Canvas JS Controller Consolidation

16 canvas JS controllers duplicate the same code. Phase 1 started consolidation with `canvasModal.js`. Phase 3 completes it:

**Create `public/assets/js/app/core/canvasController.js`** — shared canvas logic:
- `initSortable()` — canvas item drag-drop
- `initCanvasFilters()` — filter controls
- `openModalManually()` — already done in Phase 1

**Each domain controller** reduces to configuration-only:
```javascript
leantime.leanCanvasController = leantime.canvasController.init('lean');
```

---

## 2. Wiki Domain

| Template | Format | Action |
|----------|--------|--------|
| show.tpl.php | .tpl.php | Convert — main wiki view with article tree |
| articleDialog.tpl.php | .tpl.php | Convert — article editor modal |
| wikiDialog.tpl.php | .tpl.php | Convert — wiki create/edit modal |
| delWiki.tpl.php | .tpl.php | Convert — use `<x-confirm-delete>` |
| delArticle.tpl.php | .tpl.php | Convert — use `<x-confirm-delete>` |
| wikiModal.tpl.php | .tpl.php | Convert — wiki create modal |

Key concern: `show.tpl.php` uses jstree for the article navigation tree. Preserve jstree integration.

---

## 3. JS Bundle Optimization

### Current Problem

`compiled-app.js` eagerly imports ALL domain JS controllers:
```javascript
const domainModules = import.meta.glob('../../app/Domain/**/*.js', { eager: true });
```

This loads 45+ JS files on every page, regardless of which domain the user is viewing.

### Solution: Dynamic Imports

Convert to lazy loading:
```javascript
// Before (loads everything eagerly):
const domainModules = import.meta.glob('../../app/Domain/**/*.js', { eager: true });

// After (loads on demand):
const domainModules = import.meta.glob('../../app/Domain/**/*.js');
// Each module is now a function: () => import('...')
// Load based on current page/route
```

**Vite will automatically code-split** dynamic imports into separate chunks. Each domain's JS loads only when that domain's page is visited.

### Page-Specific Loading Pattern

```javascript
// In compiled-app.js:
const currentModule = document.body.dataset.module; // Set by layout template
const domainModules = import.meta.glob('../../app/Domain/**/*.js');

// Load only the current domain's JS
for (const [path, loader] of Object.entries(domainModules)) {
    if (path.includes(`/${currentModule}/`)) {
        loader(); // Dynamic import — Vite creates a chunk
    }
}
```

Layout template adds: `<body data-module="{{ $currentModule }}">`

### Libraries to Lazy-Load

| Library | Current Bundle | Load When |
|---------|---------------|-----------|
| FullCalendar | compiled-calendar-component.js | Calendar pages only |
| Chart.js | compiled-chart-component.js | Dashboard, project detail |
| Frappe Gantt | compiled-gantt-component.js | Roadmap/timeline views |
| DataTables | compiled-table-component.js | List views |
| Lottie Player | compiled-lottieplayer.js | Welcome/onboarding |

These are already separate bundles — ensure they're loaded conditionally via `@vite` in templates that need them, not globally.

### Expected Bundle Size Improvement

| State | Main Bundle | Total Page Load |
|-------|------------|----------------|
| Current (eager) | ~800KB+ | Everything on every page |
| After splitting | ~200KB core | +50-100KB per domain chunk |

---

## 4. jQuery Reduction Pass 1

### Strategy

Phase 3 does NOT remove jQuery. It reduces new jQuery usage and modernizes the most-called patterns:

1. **New code: zero jQuery** — all Phase 3 Blade components use vanilla JS + HTMX
2. **Canvas controllers: consolidate** — 16 duplicated controllers → 1 shared + config
3. **Key patterns: modernize** — Replace `jQuery.ajax()` calls in high-traffic controllers with `fetch()`

### High-Priority jQuery Replacement Targets

| File | jQuery Calls | Action |
|------|-------------|--------|
| ticketsController.js | ~120 | Phase 4 (too risky in Phase 3) |
| kanbanController.js | ~100 | Phase 4 (jQuery UI Sortable dependency) |
| 16x canvas controllers | ~400 total | Consolidate to 1 shared controller |
| dashboardController.js | ~30 | Modernize — mostly event handlers |
| menuController.js | ~40 | Modernize — DOM queries + toggle |
| projectsController.js | ~30 | Modernize where safe |

### Dead Library Removal

| Library | Location | Reason for Removal |
|---------|----------|-------------------|
| jquery.nyroModal | public/assets/js/libs/jquery.nyroModal/ | Replaced by modalManager.js (Phase 1) |
| nyroModal.css | public/assets/css/components/nyroModal.css | No longer used |
| TinyMCE skin CSS | public/assets/css/libs/tinymceSkin/ | TinyMCE migrated to TipTap ✅ |
| TinyMCE plugins | public/assets/js/libs/tinymce-plugins/ | TinyMCE migrated ✅ |
| jquery.alerts.css | public/assets/css/libs/jquery.alerts.css | Replaced by DaisyUI alert |

### Validation Checklist
- [ ] No page loads nyroModal JS
- [ ] No page loads TinyMCE CSS/JS
- [ ] Bundle size measurably smaller
- [ ] All canvas domains still function
- [ ] Dashboard, menu, project pages still function

---

## 5. Execution Order

```
3.1  Canvas shared templates → Blade          [2-3 days]   — Fixes 16 domains at once
3.2  Goalcanvas DaisyUI update                [0.5 day]    — Already Blade
3.3  Ideas domain conversion                  [1-2 days]   — Custom templates
3.4  Wiki domain conversion                   [1-2 days]   — jstree integration
3.5  JS bundle splitting                      [1-2 days]   — Vite code splitting
3.6  jQuery reduction + controller consolidation [2-3 days] — Canvas JS, key controllers
3.7  Dead library removal                     [0.5 day]    — Cleanup
```
**Total: ~2 weeks**

---

## 6. Risk Register

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Canvas shared template change breaks domains | MEDIUM | HIGH | Test Lean, SWOT, Retros after changes |
| jstree breaks in wiki | LOW | MEDIUM | Preserve jstree init code exactly |
| Dynamic imports cause flash/delay | LOW | MEDIUM | Preload hints, loading states |
| jQuery removal breaks unexpected dependency | MEDIUM | MEDIUM | Only remove from new code + dead libraries |
| Vite code splitting increases request count | LOW | LOW | HTTP/2 handles parallel requests well |

---

## 7. Phase 3 Deliverables

- [ ] All 19 canvas domains use Blade templates
- [ ] Canvas JS consolidated (16 controllers → 1 shared + config)
- [ ] Wiki domain fully converted to Blade
- [ ] Ideas domain fully converted to Blade
- [ ] Domain JS lazy-loaded (not eager)
- [ ] Dead libraries removed (nyroModal, TinyMCE assets)
- [ ] Bundle size reduced by 30-40%
- [ ] Zero new jQuery introduced
