# Phase 4: Final Cleanup — Bootstrap Removal, Remaining Domains & jQuery Elimination

## Claude Code Execution Prompt

```
Read these documents in order before starting Phase 4:
1. Docs/phase-0-complete-summary.md — Component inventory, frontend library decisions
2. Docs/phase-2-complete-summary.md — Shared component library, Bootstrap dependency map
3. Docs/phase-3-combined.md — Canvas/Wiki done, JS optimization done, jQuery reduction started
4. Docs/phase-4-combined.md — THIS FILE: remaining domain inventory, Bootstrap removal plan

Phase 4 Tasks (execute in order):
4.1 — Remaining domain template conversions (Auth, Clients, Files, Errors, Comments, Help, Connector)
4.2 — Plugin template modernization (StrategyPro, PgmPro, Whiteboardscanvas, Llamadorian, Billing, Accounts)
4.3 — Bootstrap class removal (strip dual-class patterns from ALL templates)
4.4 — Bootstrap CSS file removal (delete bootstrap.css, bootstrap-grid, etc.)
4.5 — jQuery UI replacement (Sortable → native drag API or SortableJS)
4.6 — jQuery elimination (modernize remaining controllers, remove jQuery dependency)
4.7 — Dead CSS cleanup (remove unused component CSS, consolidate custom styles)
4.8 — Final build optimization (tree-shaking, asset hash verification, bundle audit)

CRITICAL CONSTRAINTS:
- DO NOT break the JSON-RPC API surface
- jQuery UI Sortable → SortableJS migration needs careful testing of kanban drag-drop
- Bootstrap removal must be verified page-by-page (789 grid classes, 97 button classes, 175 form classes)
- Plugin templates: update core plugins, provide migration guide for third-party
- Test EVERY page after Bootstrap CSS removal — visual regression is the #1 risk
```

---

## 1. Remaining Domain Template Inventory

### Domains NOT Yet Converted (After Phase 3)

| Domain | Total | .tpl.php | .blade.php | Priority | Notes |
|--------|-------|---------|-----------|----------|-------|
| Help | 44 | 29 | 15 | LOW | Static content pages, tours, onboarding |
| Auth | 11 | 4 | 7 | LOW | Login, register, password reset |
| Connector | 9 | 8 | 0 | LOW | API connectors, rarely viewed |
| Clients | 5 | 5 | 0 | LOW | Client management |
| Errors | 4 | 4 | 0 | LOW | 404, 500, maintenance pages |
| Files | 3 | 2 | 0 | LOW | File browser, upload |
| Comments | 4 | 1 | 2 | MED | Cross-cutting concern, used in tickets/canvas |
| Api | 3 | 3 | 0 | LOW | API key management |
| TwoFA | 2 | 2 | 0 | LOW | Two-factor setup |
| Install | 2 | 2 | 0 | LOW | Installation wizard |
| CsvImport | 1 | 1 | 0 | LOW | CSV import dialog |
| Reports | 1 | 1 | 0 | LOW | Report view |
| Strategy | 1 | 1 | 0 | LOW | Strategy overview |
| **Total** | **90** | **63** | **24** | — | — |

### Plugin Templates to Modernize

| Plugin | Total | .tpl.php | .blade.php | Priority |
|--------|-------|---------|-----------|----------|
| ProjectWizard | 15 | 0 | 15 | ✅ Already Blade |
| StrategyPro | 13 | 12 | 1 | MED — canvas-based plugin |
| Billing | 13 | 2 | 11 | LOW — mostly Blade |
| Copilot | 11 | 0 | 11 | ✅ Already Blade |
| Notes | 10 | 0 | 10 | ✅ Already Blade |
| Llamadorian | 9 | 9 | 0 | MED — AI features |
| Whiteboardscanvas | 7 | 6 | 1 | MED — canvas plugin |
| PgmPro | 7 | 6 | 1 | MED — portfolio management |
| AdvancedAuth | 6 | 0 | 6 | ✅ Already Blade |
| Accounts | 6 | 4 | 2 | LOW |
| CustomFields | 4 | 0 | 4 | ✅ Already Blade |
| CalDAV | 4 | 0 | 4 | ✅ Already Blade |
| Others (8 plugins) | ~12 | 0 | ~12 | ✅ Already Blade |

**Total plugin .tpl.php to convert: ~39 files** (StrategyPro, Llamadorian, Whiteboardscanvas, PgmPro, Accounts, Billing)

---

## 2. Bootstrap Removal Plan

### Phase 4.3: Strip Bootstrap Classes from Templates

After Phases 2-3 added DaisyUI/Tailwind classes alongside Bootstrap (dual-class approach), Phase 4 removes the Bootstrap classes.

**Scope: 1,099 Bootstrap references across all templates**

| Pattern | Count | Replacement Already Added |
|---------|-------|--------------------------|
| `col-md-*`, `col-lg-*`, `col-sm-*`, `col-xs-*` | 789 | Tailwind `md:w-*`, `lg:w-*`, grid/flex |
| `btn-default`, `btn-success`, `btn-danger`, etc. | 97 | DaisyUI `btn-primary`, `btn-error`, etc. |
| `form-group`, `form-control` | 175 | DaisyUI `form-control`, `input` |
| `panel-*`, `well`, `alert-*` | ~38 | DaisyUI `card`, `alert` |

### Removal Process

```bash
# Step 1: Find all remaining Bootstrap grid classes
grep -rn "col-md-\|col-lg-\|col-sm-\|col-xs-\|row" --include="*.blade.php" app/ resources/

# Step 2: For each file, verify DaisyUI/Tailwind equivalent exists
# Step 3: Remove Bootstrap class, keep Tailwind class
# Step 4: Visual test the page
```

### Phase 4.4: Remove Bootstrap CSS Files

| File to Remove | Lines |
|----------------|-------|
| public/assets/css/libs/bootstrap.css | 6,782 |
| public/assets/css/libs/bootstrap.min.css | (minified) |
| public/assets/css/libs/bootstrap-grid.min.css | — |
| public/assets/css/libs/bootstrap-responsive.min.css | — |
| public/assets/css/libs/bootstrap-timepicker.min.css | — |
| public/assets/css/libs/bootstrap-fileupload.min.css | — |
| public/assets/js/libs/bootstrap.min.js | — |
| public/assets/js/libs/bootstrap-fileupload.min.js | — |
| public/assets/js/libs/bootstrap-timepicker.min.js | — |

Also remove from Vite config / CSS imports.

---

## 3. jQuery Elimination Plan

### Phase 4.5: jQuery UI Replacement

jQuery UI is used for:
- **Sortable** — Kanban board drag-drop, canvas item reordering, widget grid
- **Datepicker** — Date inputs (partially replaced by native + flatpickr)
- **Dialog** — Replaced by `<dialog>` in Phase 1
- **Draggable/Droppable** — Kanban cards

**Replacement: SortableJS** (no jQuery dependency, modern API, touch support)

```javascript
// Before (jQuery UI Sortable):
jQuery('.kanbanColumn').sortable({
    connectWith: '.kanbanColumn',
    update: function(event, ui) { ... }
});

// After (SortableJS):
import Sortable from 'sortablejs';
document.querySelectorAll('.kanbanColumn').forEach(el => {
    Sortable.create(el, {
        group: 'kanban',
        onEnd: function(evt) { ... }
    });
});
```

### Phase 4.6: jQuery Core Removal

**After jQuery UI is replaced**, systematically remove jQuery from controllers:

| Controller | jQuery Calls | Complexity | Strategy |
|-----------|-------------|-----------|----------|
| kanbanController.js | ~100 | 🔴 HIGH | Rewrite with vanilla JS + HTMX |
| ticketsController.js | ~120 | 🔴 HIGH | Rewrite core interactions |
| menuController.js | ~40 | 🟡 MED | querySelector + classList |
| usersController.js | ~30 | 🟡 MED | Modernize |
| settingController.js | ~20 | 🟢 LOW | Modernize |
| calendarController.js | ~30 | 🟡 MED | FullCalendar handles most |
| commentsController.js | ~20 | 🟢 LOW | HTMX replaces most |
| dashboardController.js | ~30 | 🟡 MED | Modernize |

**Key library dependencies to address:**
- chosen.js (dropdown) → SlimSelect (already in codebase)
- jquery.growl.js → DaisyUI toast/alert
- jquery.form.js → native FormData + fetch
- jquery.tagsinput.js → modern tag input
- jquery-is-in-viewport → IntersectionObserver

### Removal Order

1. Remove jQuery UI → SortableJS
2. Remove jQuery plugins (chosen → SlimSelect, growl → toast, etc.)
3. Remove jQuery from compiled-framework-plugins.js
4. Modernize domain controllers (one at a time, test after each)
5. Remove jQuery from compiled-frameworks.js
6. Remove jQuery from package.json

---

## 4. Dead CSS Cleanup

### CSS Files to Remove After Full Migration

| File | Lines | Why Remove |
|------|-------|-----------|
| components/nyroModal.css | — | Modal replaced (Phase 1) |
| components/forms.css | — | DaisyUI form components |
| components/overwrites.css | — | No more overrides needed |
| components/reset.css | — | Tailwind preflight handles reset |
| components/progressbars.css | — | DaisyUI progress |
| components/toast-notifications.css | — | DaisyUI toast |
| libs/jquery.alerts.css | — | DaisyUI alert |
| libs/jquery.chosen.css | — | SlimSelect |
| libs/jquery.simple-color-picker.css | — | DaisyUI/Tailwind |
| libs/jquery.tagsinput.css | — | Modern tag input |
| libs/jquery-ui-*.css | — | jQuery UI removed |
| libs/switchery/*.css | — | DaisyUI toggle |
| libs/loading-btn.css | — | DaisyUI loading state |
| libs/loading.css | — | DaisyUI loading |
| libs/isotope.css | — | CSS Grid replaces |
| libs/shepherd-theme-arrows.css | — | Evaluate if tours still use Shepherd |
| libs/tinymceSkin/** | — | TinyMCE removed ✅ |

### Custom CSS to Consolidate

| File | Lines | Action |
|------|-------|--------|
| components/style.default.css | 2,969 | Audit: extract needed styles to Tailwind utilities |
| components/structure.css | — | Migrate layout rules to Tailwind |
| components/nav.css | — | DaisyUI navbar/menu handles this |
| components/kanban.css | — | Migrate to Tailwind utilities |
| components/tables.css | — | DaisyUI table + DataTables CSS only |
| components/text-styles.css | — | Tailwind typography |
| components/mobile.css | 2,511 | Tailwind responsive utilities |
| components/print.css | — | Keep (print styles) |
| components/accessibility.css | — | Keep (a11y overrides) |

### Target: Single CSS Entry Point

After cleanup, the CSS architecture should be:
```
resources/css/
├── app.css          ← Main entry: Tailwind directives + custom utilities
├── editor.css       ← TipTap editor styles
└── print.css        ← Print styles
```

All component styling handled by DaisyUI classes inline. No separate component CSS files.

---

## 5. Final Build Optimization

### Phase 4.8 Checklist

- [ ] Vite build produces hashed asset filenames
- [ ] CSS tree-shaking removes unused Tailwind classes
- [ ] JS tree-shaking removes dead code
- [ ] No jQuery in final bundle
- [ ] No Bootstrap in final bundle
- [ ] Bundle analyzer shows clean dependency tree
- [ ] Gzipped main CSS < 50KB (down from ~200KB+)
- [ ] Gzipped main JS < 150KB (down from ~800KB+)
- [ ] Domain JS chunks < 30KB each
- [ ] All pages load in < 3 seconds on 3G simulation

### Bundle Audit Commands

```bash
# Build with analysis
npx vite build --mode production
npx vite-bundle-analyzer

# Check for jQuery remnants
grep -r "jQuery\|jquery" dist/assets/*.js

# Check for Bootstrap remnants
grep -r "bootstrap\|col-md-\|btn-default" dist/assets/*.css

# Verify hashed filenames
ls -la public/build/assets/
```

---

## 6. Execution Order

```
4.1  Remaining domain conversions               [3-4 days]
4.2  Plugin template modernization              [2-3 days]
4.3  Bootstrap class removal from templates     [2-3 days]
4.4  Bootstrap CSS/JS file removal              [0.5 day]
4.5  jQuery UI → SortableJS                     [2-3 days]
4.6  jQuery elimination from controllers        [3-5 days]
4.7  Dead CSS cleanup + consolidation           [1-2 days]
4.8  Final build optimization + audit           [1 day]
```
**Total: ~3 weeks**

---

## 7. Risk Register

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Bootstrap removal causes visual regressions | HIGH | HIGH | Page-by-page visual testing, dual-class was preparation |
| Kanban drag-drop breaks with SortableJS | MEDIUM | HIGH | Parallel implementation, keep jQuery UI as fallback |
| jQuery removal breaks obscure interaction | MEDIUM | MEDIUM | Remove one controller at a time, test thoroughly |
| Plugin templates break | LOW | MEDIUM | Core plugins updated, migration guide for third-party |
| CSS too aggressive removal | MEDIUM | MEDIUM | Keep backup of removed files, test each page |
| Help/onboarding tours break | LOW | LOW | Test Shepherd.js integration separately |
| Bundle size doesn't improve enough | LOW | LOW | Analyze before/after, identify remaining bloat |

---

## 8. Phase 4 Deliverables (= Project Complete)

### Template Conversion
- [ ] 100% of core domain templates are Blade
- [ ] 100% of core plugin templates are Blade
- [ ] Zero .tpl.php, .inc.php, .sub.php files in active use

### CSS
- [ ] Bootstrap CSS completely removed
- [ ] All styling via Tailwind 4 + DaisyUI 5 + minimal custom CSS
- [ ] Single CSS entry point (app.css)
- [ ] Gzipped CSS < 50KB

### JavaScript
- [ ] jQuery completely removed
- [ ] jQuery UI completely removed
- [ ] All interactions via vanilla JS + HTMX
- [ ] Domain JS lazy-loaded
- [ ] Gzipped main JS < 150KB

### Architecture
- [ ] Consistent Blade component library
- [ ] DaisyUI 5 theming throughout
- [ ] `<dialog>` modals (no nyroModal)
- [ ] hx-boost SPA-like navigation
- [ ] HTMX-driven partial updates
- [ ] WCAG 2.1 AA compliance
- [ ] Mobile-responsive all pages
- [ ] Mobile app API completely intact

### Performance
- [ ] < 3 second initial page load (3G)
- [ ] < 500ms navigation transitions (hx-boost)
- [ ] < 200ms modal opens
- [ ] No unused CSS/JS in production build

---

## 9. Post-Phase 4: Maintenance Guidelines

After Phase 4, all new development should follow:

1. **Templates:** Blade only. Use shared components from `app/Views/Templates/components/`
2. **Styling:** DaisyUI 5 classes + Tailwind 4 utilities only. No custom CSS unless truly needed
3. **Interactivity:** HTMX attributes for server communication. Vanilla JS for client-side logic
4. **Modals:** `<dialog>` via modal component. No hash-based triggers for new features
5. **Forms:** `<x-form.*>` components with validation
6. **Navigation:** All new pages work with hx-boost
7. **Plugins:** Follow component patterns. Reference component library docs
8. **Testing:** Visual regression test after any component change
