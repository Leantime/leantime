# Phase 2: Core Domain Componentization — Complete Summary

## Claude Code Execution Prompt

```
Read these documents in order before starting Phase 2:
1. Docs/phase-0-complete-summary.md — Component inventory, frontend library decisions, design guidelines
2. Docs/phase-1-complete-summary.md — Modal system architecture, navigation patterns
3. Docs/phase-2-complete-summary.md — THIS FILE: domain template inventory, jQuery dependency map
4. Docs/phase-2-requirements.md — Step-by-step execution instructions with validation checklists
5. Docs/leantime-4.0-dev-unique-changes.md — 4.0-dev component reference designs

Phase 2 Tasks (execute in order):
2.1 — Build shared Blade component library (form inputs, cards, tables, page layouts)
2.2 — Tickets domain: convert .tpl.php → Blade components (38 templates, highest traffic)
2.3 — Projects domain: convert .tpl.php → Blade components (15 templates)
2.4 — Dashboard domain: enhance existing Blade templates with components (3 templates)
2.5 — Calendar domain: convert .tpl.php → Blade components (11 templates)
2.6 — Settings domain: convert .tpl.php → Blade components (2 templates)
2.7 — Users domain: convert .tpl.php → Blade components (8 templates)
2.8 — Timesheets domain: convert .tpl.php → Blade components (7 templates)
2.9 — Menu domain: update existing Blade templates with DaisyUI 5 components (14 templates)

CRITICAL CONSTRAINTS:
- DO NOT change service method signatures, field names, or return types
- DO NOT break the JSON-RPC API surface (mobile app depends on it)
- DO NOT change Controller logic — only template files change
- Every .tpl.php → .blade.php conversion must render identical UI output
- Test each converted template in browser before proceeding to next
- Preserve all HTMX attributes and HxController interactions
- Preserve all existing CSS classes during conversion (add DaisyUI classes alongside)
```

---

## 1. Core Domain Template Inventory

### Templates by Format (Core Domains Only)

| Domain | Total | .blade.php | .tpl.php | .inc.php/.sub.php | .js | Priority |
|--------|-------|-----------|---------|-------------------|-----|----------|
| Tickets | 38 | 8 | 15 | 15 | 3 | 🔴 P1 |
| Help | 44 | 15 | 29 | 0 | 5 | 🟡 P3 (content-only) |
| Projects | 15 | 6 | 7 | 2 | 1 | 🔴 P1 |
| Menu | 14 | 14 | 0 | 0 | 2 | 🟡 P2 (already Blade) |
| Calendar | 11 | 4 | 7 | 0 | 1 | 🔴 P1 |
| Auth | 11 | 7 | 4 | 0 | 1 | 🟢 P4 (low traffic) |
| Plugins | 10 | 10 | 0 | 0 | 0 | ✅ Already Blade |
| Widgets | 9 | 9 | 0 | 0 | 1 | ✅ Already Blade |
| Canvas | 9 | 0 | 0 | 9 | 1 | Phase 3 |
| Users | 8 | 3 | 5 | 0 | 3 | 🟡 P2 |
| Goalcanvas | 8 | 8 | 0 | 0 | 1 | ✅ Already Blade |
| Timesheets | 7 | 1 | 6 | 0 | 1 | 🟡 P2 |
| Wiki | 6 | 0 | 6 | 0 | 1 | Phase 3 |
| Clients | 5 | 0 | 5 | 0 | 1 | Phase 4 |
| Sprints | 2 | 0 | 2 | 0 | 0 | Phase 2 (with Tickets) |
| Setting | 2 | 0 | 2 | 0 | 3 | 🟡 P2 |
| Comments | 4 | 2 | 1 | 1 | 1 | 🟡 P2 (cross-cutting) |
| Dashboard | 3 | 3 | 0 | 0 | 1 | ✅ Already Blade |
| Notifications | 2 | 2 | 0 | 0 | 0 | ✅ Already Blade |
| Files | 3 | 0 | 2 | 1 | 0 | Phase 4 |

### Phase 2 Scope: 108 Templates to Convert/Update

| Category | Template Count | Already Blade | Need Conversion |
|----------|---------------|---------------|-----------------|
| Tickets + Sprints | 40 | 8 | 32 |
| Projects | 15 | 6 | 9 |
| Calendar | 11 | 4 | 7 |
| Setting | 2 | 0 | 2 |
| Users | 8 | 3 | 5 |
| Timesheets | 7 | 1 | 6 |
| Comments | 4 | 2 | 2 |
| Menu (update only) | 14 | 14 | 0 (update) |
| Dashboard (update only) | 3 | 3 | 0 (update) |
| **Total** | **104** | **41** | **63 to convert** |

---

## 2. Tickets Domain — Detailed Template Map

The most complex domain with 38 templates, 3 JS controllers, and the most user traffic.

### Main Views (full pages)

| Template | Format | Complexity | Notes |
|----------|--------|-----------|-------|
| showKanban.tpl.php | .tpl.php | 🔴 HIGH | Kanban board — drag-drop, swimlanes, ticket cards |
| showAll.tpl.php | .tpl.php | 🔴 HIGH | Table list view — DataTables, filters |
| showList.tpl.php | .tpl.php | 🟡 MED | Simple list view |
| showTicket.tpl.php | .tpl.php | 🔴 HIGH | Full-page ticket detail (non-modal version) |
| showAllMilestones.tpl.php | .tpl.php | 🟡 MED | Milestones Gantt view |
| showAllMilestonesOverview.tpl.php | .tpl.php | 🟡 MED | Milestones table view |
| roadmap.tpl.php | .tpl.php | 🟡 MED | Project roadmap view |
| roadmapAll.tpl.php | .tpl.php | 🟡 MED | All-projects roadmap |
| calendar.tpl.php | .tpl.php | 🟡 MED | Ticket calendar view |
| newTicket.tpl.php | .tpl.php | 🟡 MED | Full-page new ticket form |

### Modal Dialogs

| Template | Format | Notes |
|----------|--------|-------|
| showTicketModal.blade.php | ✅ Blade | Ticket detail in modal (already migrated) |
| newTicketModal.tpl.php | .tpl.php | Quick ticket creation modal |
| milestoneDialog.tpl.php | .tpl.php | Milestone create/edit |
| moveTicket.tpl.php | .tpl.php | Move ticket between projects |
| delTicket.tpl.php | .tpl.php | Delete confirmation |
| delMilestone.tpl.php | .tpl.php | Delete milestone confirmation |

### Partials (already mostly Blade)

| Template | Format | Notes |
|----------|--------|-------|
| partials/ticketCard.blade.php | ✅ Blade | Kanban card component |
| partials/subtasks.blade.php | ✅ Blade | Subtask list (HTMX) |
| partials/ticketsubmenu.blade.php | ✅ Blade | Context menu |
| partials/milestoneCard.blade.php | ✅ Blade | Milestone card |
| partials/timerButton.blade.php | ✅ Blade | Timer widget |
| partials/timerLink.blade.php | ✅ Blade | Timer link |
| partials/quickadd-form.inc.php | .inc.php | Inline ticket creation |
| componentTest.blade.php | ✅ Blade | Component test page |

### Submodules (shared across views)

| Template | Format | Used By |
|----------|--------|---------|
| submodules/ticketHeader.sub.php | .sub.php | showAll, showKanban, showList |
| submodules/ticketFilter.sub.php | .sub.php | All ticket views |
| submodules/ticketBoardTabs.sub.php | .sub.php | Kanban, list views |
| submodules/ticketNewBtn.sub.php | .sub.php | All ticket views |
| submodules/ticketNewButton.sub.php | .sub.php | All ticket views |
| submodules/ticketDetails.sub.php | .sub.php | showTicket full page |
| submodules/subTasks.sub.php | .sub.php | showTicket full page |
| submodules/attachments.sub.php | .sub.php | showTicket |
| submodules/comments.sub.php | .sub.php | showTicket |
| submodules/timesheet.sub.php | .sub.php | showTicket |
| submodules/additionalFields.sub.php | .sub.php | showTicket |
| submodules/portfolioHeader.sub.php | .sub.php | Roadmap views |
| submodules/timelineHeader.sub.php | .sub.php | Milestone views |
| submodules/timelineTabs.sub.php | .sub.php | Milestone views |

### Ticket JS Controllers

| File | Purpose | jQuery Usage |
|------|---------|-------------|
| ticketsController.js | Main ticket interactions | Heavy jQuery |
| kanbanController.js | Kanban drag-drop, board logic | Heavy jQuery |
| ticketsRepository.js | AJAX data layer | jQuery.ajax |

---

## 3. jQuery Dependency Map

**1,493 jQuery references** across domain JS controllers. This breaks down by pattern:

### By Usage Pattern (estimated)

| Pattern | Count | Replacement |
|---------|-------|-------------|
| `jQuery("selector")` / `$("selector")` — DOM queries | ~600 | `document.querySelector()` / HTMX |
| `jQuery.ajax()` / `$.get()` / `$.post()` | ~200 | `fetch()` / HTMX `hx-get`/`hx-post` |
| `jQuery(el).on("event")` — event handlers | ~250 | `addEventListener()` / HTMX events |
| `jQuery(el).addClass/removeClass/toggleClass` | ~150 | `el.classList.add/remove/toggle` |
| `jQuery(el).show/hide/toggle` | ~100 | CSS classes / HTMX swap |
| `jQuery(el).val()` / `.text()` / `.html()` | ~100 | Native DOM properties |
| jQuery UI (sortable, draggable, datepicker) | ~50 | Phase 3/4 — needs alternatives |
| jQuery plugins (nyroModal, growl, chosen, etc.) | ~43 | Phase 1 (modal), Phase 3 (rest) |

### jQuery Removal Strategy

Phase 2 does NOT remove jQuery. Instead:
1. New Blade components use vanilla JS + HTMX (no new jQuery)
2. Converted templates preserve existing jQuery in JS controllers
3. jQuery removal happens incrementally in Phase 3-4 as JS controllers are modernized

---

## 4. Bootstrap CSS Dependency Map

**1,099 Bootstrap references** across templates:

| Bootstrap Pattern | Count | DaisyUI/Tailwind Replacement |
|-------------------|-------|------------------------------|
| Grid: `col-md-*`, `col-lg-*`, `col-sm-*`, `col-xs-*` | 789 | Tailwind grid/flex utilities |
| Buttons: `btn-default`, `btn-success`, `btn-danger`, etc. | 97 | DaisyUI `btn`, `btn-primary`, etc. |
| Forms: `form-group`, `form-control` | 175 | DaisyUI `form-control`, `input`, `select` |
| Panels, wells, alerts | ~38 | DaisyUI `card`, `alert` |

### Bootstrap Removal Strategy

Phase 2 uses a **dual-class approach**:
1. Add DaisyUI/Tailwind classes alongside Bootstrap classes
2. Verify visual parity
3. Remove Bootstrap classes in Phase 4 (final cleanup)
4. Remove Bootstrap CSS files in Phase 4

---

## 5. JS Bundle Architecture

### Current Bundles (13 compiled files)

| Bundle | Size Factor | Content | Phase 2 Action |
|--------|------------|---------|----------------|
| compiled-frameworks.js | LARGE | jQuery, jQuery UI core | Keep (Phase 3 removes jQuery UI) |
| compiled-framework-plugins.js | LARGE | jQuery UI, chosen, growl, form, tags | Keep (Phase 3 modernizes) |
| compiled-global-component.js | LARGE | 22 imports: luxon, tippy, slimselect, nyroModal, croppie, etc. | Phase 1 replaces nyroModal |
| compiled-app.js | MEDIUM | Core app + modals + domain JS (glob import) | Update modals import |
| compiled-calendar-component.js | MEDIUM | FullCalendar | Keep |
| compiled-chart-component.js | SMALL | Chart.js | Keep |
| compiled-editor-component.js | LARGE | TipTap editor (36 imports) | Keep |
| compiled-table-component.js | MEDIUM | DataTables | Keep (Phase 3 evaluates replacement) |
| compiled-gantt-component.js | MEDIUM | Frappe Gantt | Keep |
| compiled-htmx.js | SMALL | HTMX core | Keep |
| compiled-htmx-extensions.js | SMALL | HTMX extensions | Keep |
| compiled-footer.js | TINY | Footer scripts | Keep |
| compiled-lottieplayer.js | SMALL | Lottie animations | Keep |

### Domain JS Auto-Import

`compiled-app.js` uses Vite's `import.meta.glob()` to auto-import ALL domain JS:

```javascript
const domainModules = import.meta.glob('../../app/Domain/**/*.js', { eager: true });
```

This means **every domain JS controller is loaded on every page**. Phase 3 should convert this to lazy loading.

---

## 6. Shared Component Library Plan

### Components to Build in Phase 2.1

These components serve as the foundation for ALL domain conversions:

| Component | DaisyUI Base | Purpose | Priority |
|-----------|-------------|---------|----------|
| `<x-form.input>` | input, textarea | Text/number/email inputs with labels + errors | P1 |
| `<x-form.select>` | select | Dropdown with label + errors | P1 |
| `<x-form.checkbox>` | checkbox, toggle | Checkboxes and toggles | P1 |
| `<x-form.radio>` | radio | Radio groups | P1 |
| `<x-form.date>` | — | Date picker wrapper | P1 |
| `<x-form.editor>` | — | TipTap editor wrapper | P2 |
| `<x-form.file>` | file-input | File upload | P2 |
| `<x-form.color>` | — | Color picker | P3 |
| `<x-card>` | card | Content card with header/body/footer | P1 |
| `<x-table>` | table | Data table with sorting | P1 |
| `<x-table.row>` | — | Table row | P1 |
| `<x-alert>` | alert | Status messages | P1 |
| `<x-dropdown>` | dropdown | Dropdown menus | P1 |
| `<x-avatar>` | avatar | User avatar display | P1 |
| `<x-badge>` | badge | Status badges | P1 (exists, update) |
| `<x-progress>` | progress, radial-progress | Progress indicators | P2 |
| `<x-tooltip>` | tooltip | Tippy.js wrapper | P1 |
| `<x-page-header>` | — | Page title + actions bar | P1 (exists, update) |
| `<x-breadcrumbs>` | breadcrumbs | Navigation breadcrumbs | P2 |
| `<x-empty-state>` | — | Empty content placeholder | P2 |
| `<x-confirm-delete>` | — | Delete confirmation dialog | P1 |
| `<x-status-indicator>` | — | Red/yellow/green status dot | P1 |
| `<x-filter-bar>` | — | Filter controls row | P2 |

### Existing Components to Update

| Component | Current State | Action |
|-----------|-------------|--------|
| `actions/modal.blade.php` | Phase 1 updated | ✅ Done |
| `badge.blade.php` | Exists | Update to DaisyUI 5 |
| `button.blade.php` | Exists | Update to DaisyUI 5 |
| `accordion.blade.php` | Exists | Update to DaisyUI 5 |
| `tabs.blade.php` | Exists | Update to DaisyUI 5 |
| `pageheader.blade.php` | Exists | Update to DaisyUI 5 |
| `loader.blade.php` | Exists | Update to DaisyUI 5 |
| `kanban/*.blade.php` | Exists (8 files) | Update to DaisyUI 5 |

---

## 7. CSS Files to Eventually Remove

### Phase 4 Removal Targets

| CSS File | Lines | Replacement |
|----------|-------|-------------|
| bootstrap.css | 6,782 | Tailwind 4 + DaisyUI 5 |
| bootstrap-grid.min.css | — | Tailwind grid |
| bootstrap-responsive.min.css | — | Tailwind responsive |
| bootstrap-timepicker.min.css | — | DaisyUI date input |
| bootstrap-fileupload.min.css | — | DaisyUI file-input |
| nyroModal.css | — | DaisyUI modal (Phase 1) |
| jquery-ui-*.css | — | DaisyUI components |
| jquery.chosen.css | — | SlimSelect (already present) |
| jquery.alerts.css | — | DaisyUI alert |
| style.default.css | 2,969 | Migrate custom styles to Tailwind |
| structure.css | — | Tailwind layout |
| forms.css | — | DaisyUI form components |
| overwrites.css | — | Remove after components cover all cases |
| tinymceSkin/** | — | Remove (TinyMCE migrated ✅) |

---

## 8. Domain-Specific Notes

### Tickets Domain
- `showKanban.tpl.php` uses jQuery UI Sortable for drag-drop — keep jQuery UI in Phase 2, evaluate replacement in Phase 3
- `showAll.tpl.php` uses DataTables jQuery plugin — keep in Phase 2
- `showTicketModal.blade.php` already Blade — update to use new form components
- Submodules (.sub.php) are PHP includes — convert to Blade `@include` or components

### Projects Domain
- `projectHub.blade.php` already Blade — widget-based dashboard
- `showProject.tpl.php` uses chart components and submodules
- `editProject.tpl.php` / `newProject.tpl.php` — form-heavy, good candidates for form components

### Calendar Domain
- `showMyCalendar.tpl.php` integrates FullCalendar JS — keep FC integration
- Multiple modal dialogs (add/edit/delete event) — use Phase 1 modal pattern
- `calendarSettings.blade.php` already Blade

### Settings Domain
- `editCompanySettings.tpl.php` — large form, tab-based, API key management
- `editBoxDialog.tpl.php` — label editing modal

### Users Domain
- `newUser.tpl.php` — invite user form
- Profile components already exist as Blade

### Menu Domain
- Already 100% Blade (14 templates)
- Update to DaisyUI 5 classes (sidebar, drawer, menu components)
