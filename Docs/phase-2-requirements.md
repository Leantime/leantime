# Phase 2: Core Domain Componentization — Requirements Document

## For Claude Code Execution

**Project:** Leantime UI Modernization Forward-Port
**Phase:** 2 of 4 — Core Domain Template Conversion + Shared Component Library
**Depends on:** Phase 0 (build pipeline) + Phase 1 (modal system + navigation)
**Estimated Duration:** 3-4 weeks
**Risk Level:** MEDIUM (template-level changes, no business logic)
**Generated:** February 13, 2026

---

## Goal

Convert the highest-traffic core domain templates from `.tpl.php` / `.inc.php` / `.sub.php` to Blade components using DaisyUI 5 + Tailwind 4 + HTMX. Build a shared component library that standardizes forms, cards, tables, and page layouts. Convert **63 templates** across 9 core domains.

---

## 2.1 Build Shared Component Library

**Priority:** FIRST | **Risk:** LOW | **Effort:** 2-3 days

### What to Build

Create reusable Blade components in `app/Views/Templates/components/`. Every subsequent domain conversion depends on these.

### Form Components (`components/form/`)

**`input.blade.php`** — Text, number, email, password, hidden inputs:
```php
@props(['name', 'label' => null, 'type' => 'text', 'value' => '', 'required' => false, 'error' => null])

<div class="form-control w-full">
    @if($label)
        <label class="label" for="{{ $name }}">
            <span class="label-text">{{ $label }} @if($required)<span class="text-error">*</span>@endif</span>
        </label>
    @endif
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}"
           value="{{ old($name, $value) }}"
           {{ $required ? 'required' : '' }}
           {{ $attributes->merge(['class' => 'input input-bordered w-full' . ($error ? ' input-error' : '')]) }} />
    @if($error)
        <label class="label"><span class="label-text-alt text-error">{{ $error }}</span></label>
    @endif
</div>
```

**`select.blade.php`** — Dropdowns with label and error support.

**`checkbox.blade.php`** — Checkboxes and toggles.

**`textarea.blade.php`** — Multi-line text with optional TipTap editor integration.

**`date.blade.php`** — Date picker wrapper (preserves existing datepicker JS).

**`file.blade.php`** — File upload with DaisyUI styling.

**`color.blade.php`** — Color picker wrapper.

### Layout Components (`components/`)

**`card.blade.php`** — Content card with optional header, body, and footer:
```php
@props(['title' => null, 'compact' => false])

<div {{ $attributes->merge(['class' => 'card bg-base-100 shadow-sm' . ($compact ? ' card-compact' : '')]) }}>
    @if($title)
        <div class="card-body">
            <h2 class="card-title">{{ $title }}</h2>
            {{ $slot }}
            @isset($actions)
                <div class="card-actions justify-end">{{ $actions }}</div>
            @endisset
        </div>
    @else
        <div class="card-body">
            {{ $slot }}
        </div>
    @endif
</div>
```

**`table.blade.php`** — Data table wrapper preserving DataTables integration.

**`alert.blade.php`** — Status messages (success, error, warning, info).

**`dropdown.blade.php`** — Dropdown menus using DaisyUI dropdown.

**`confirm-delete.blade.php`** — Delete confirmation dialog pattern.

**`empty-state.blade.php`** — Empty content placeholder with illustration.

**`status-indicator.blade.php`** — Red/yellow/green dot.

**`filter-bar.blade.php`** — Filter controls row (sprint filter, status filter, etc.).

### Update Existing Components

Update these to use DaisyUI 5 classes:
- `badge.blade.php`
- `button.blade.php`
- `accordion.blade.php`
- `tabs.blade.php` + `tabs/heading.blade.php` + `tabs/content.blade.php`
- `pageheader.blade.php`
- `loader.blade.php`

### Validation Checklist
- [ ] All form components render correctly with labels, values, errors
- [ ] Card component renders with title, body, actions
- [ ] Table component works with existing DataTables initialization
- [ ] Alert component shows all 4 variants
- [ ] Dropdown component opens/closes properly
- [ ] Updated existing components maintain visual compatibility
- [ ] Components work inside modals (Phase 1 `<dialog>`)
- [ ] Components work with HTMX attributes (hx-get, hx-post, hx-target)

---

## 2.2 Tickets Domain Conversion

**Priority:** HIGHEST | **Risk:** MEDIUM | **Effort:** 5-7 days

This is the largest and most complex domain. Convert in sub-phases:

### Phase 2.2a: Submodules First (shared across views)

Convert `.sub.php` includes to Blade `@include` or components. These are used by multiple parent templates, so converting them first means parent templates can reference Blade includes.

| Submodule | Lines | Complexity | Key Dependencies |
|-----------|-------|-----------|-----------------|
| ticketHeader.sub.php | — | MED | Sprint filter, view tabs |
| ticketFilter.sub.php | — | MED | Filter dropdowns (chosen.js) |
| ticketBoardTabs.sub.php | — | LOW | Tab navigation |
| ticketNewBtn.sub.php | — | LOW | New ticket button |
| ticketNewButton.sub.php | — | LOW | New ticket button (variant) |
| ticketDetails.sub.php | — | HIGH | Full ticket detail panel |
| subTasks.sub.php | — | MED | Subtask list + add form |
| attachments.sub.php | — | MED | File upload (Uppy) |
| comments.sub.php | — | MED | Comment thread |
| timesheet.sub.php | — | MED | Time logging |
| additionalFields.sub.php | — | LOW | Custom fields |
| portfolioHeader.sub.php | — | LOW | Portfolio view header |
| timelineHeader.sub.php | — | MED | Timeline controls |
| timelineTabs.sub.php | — | LOW | Timeline tab navigation |

**Conversion pattern:**
```php
// Before (.sub.php included via PHP include):
<?php include __DIR__ . '/submodules/ticketHeader.sub.php'; ?>

// After (Blade include):
@include('tickets::partials.ticketHeader')
```

### Phase 2.2b: Modal Dialogs

| Template | Action |
|----------|--------|
| newTicketModal.tpl.php → newTicketModal.blade.php | Convert form to use `<x-form.*>` components |
| milestoneDialog.tpl.php → milestoneDialog.blade.php | Convert form, date pickers |
| moveTicket.tpl.php → moveTicket.blade.php | Convert project selector |
| delTicket.tpl.php → delTicket.blade.php | Use `<x-confirm-delete>` component |
| delMilestone.tpl.php → delMilestone.blade.php | Use `<x-confirm-delete>` component |

### Phase 2.2c: Main Views

| Template | Complexity | Key Concerns |
|----------|-----------|-------------|
| showKanban.tpl.php | 🔴 HIGH | jQuery UI Sortable drag-drop — preserve JS, convert HTML structure |
| showAll.tpl.php | 🔴 HIGH | DataTables integration — preserve table structure, use `<x-table>` |
| showTicket.tpl.php | 🔴 HIGH | Full page ticket — uses many submodules |
| showAllMilestones.tpl.php | 🟡 MED | Gantt chart integration |
| showAllMilestonesOverview.tpl.php | 🟡 MED | DataTables milestone list |
| showList.tpl.php | 🟡 MED | Simple ticket list |
| roadmap.tpl.php | 🟡 MED | Gantt chart |
| roadmapAll.tpl.php | 🟡 MED | All-project Gantt |
| calendar.tpl.php | 🟡 MED | FullCalendar integration |
| newTicket.tpl.php | 🟡 MED | Full-page ticket form |

### Kanban Special Handling

`showKanban.tpl.php` is the most complex template. Key concerns:
1. jQuery UI Sortable for drag-drop — **preserve all sortable initialization**
2. Swimlane grouping (by milestone, sprint, priority) — preserve `data-*` attributes
3. Ticket cards already use `partials/ticketCard.blade.php` — just update the container
4. The kanbanController.js handles all drag-drop AJAX — **do not change this JS**
5. Convert HTML structure to use DaisyUI card/grid utilities alongside existing classes

### Sprint Templates (with Tickets)

| Template | Action |
|----------|--------|
| Sprints/sprintdialog.tpl.php → sprintdialog.blade.php | Convert form, use date components |
| Sprints/delSprint (if exists) | Convert |

### Validation Checklist
- [ ] Kanban board renders with correct layout and drag-drop works
- [ ] Table list view renders with sorting, pagination (DataTables)
- [ ] Full-page ticket detail shows all tabs (details, subtasks, comments, time, files)
- [ ] Ticket modal shows correctly (already Blade, verify with new components)
- [ ] New ticket form creates tickets
- [ ] Milestone dialog creates/edits milestones
- [ ] Delete confirmations work
- [ ] Move ticket between projects works
- [ ] Sprint dialog creates/edits sprints
- [ ] Roadmap/timeline views render Gantt charts
- [ ] All HTMX interactions still work (subtask add, comment submit, etc.)
- [ ] Ticket filters work (sprint, status, milestone, user)
- [ ] Quick-add form works

---

## 2.3 Projects Domain Conversion

**Priority:** HIGH | **Risk:** LOW-MEDIUM | **Effort:** 2-3 days

| Template | Format | Action |
|----------|--------|--------|
| projectHub.blade.php | ✅ Blade | Update to use DaisyUI components |
| partials/projectCard.blade.php | ✅ Blade | Update card styling |
| partials/projectCardProgressBar.blade.php | ✅ Blade | Update progress bar |
| partials/projectHubProjects.blade.php | ✅ Blade | Update grid layout |
| partials/checklist.blade.php | ✅ Blade | Update to DaisyUI |
| createnew.blade.php | ✅ Blade | Update form components |
| showAll.tpl.php → showAll.blade.php | Convert | DataTables project list |
| showProject.tpl.php → showProject.blade.php | Convert | Project detail + charts |
| editProject.tpl.php → editProject.blade.php | Convert | Edit form → use `<x-form.*>` |
| newProject.tpl.php → newProject.blade.php | Convert | New form → use `<x-form.*>` |
| editAccount.tpl.php → editAccount.blade.php | Convert | Account settings form |
| delProject.tpl.php → delProject.blade.php | Convert | Use `<x-confirm-delete>` |
| duplicateProject.tpl.php → duplicateProject.blade.php | Convert | Duplicate form |
| submodules/projectDetails.sub.php | Convert | Include to Blade partial |
| submodules/tickets.sub.php | Convert | Include to Blade partial |

### Validation Checklist
- [ ] Project hub renders with project cards and progress bars
- [ ] Project list (DataTables) renders with sorting
- [ ] Project detail page shows charts and details
- [ ] Create/edit/delete project workflows work
- [ ] Duplicate project works
- [ ] Project settings save correctly

---

## 2.4 Dashboard Domain Update

**Priority:** HIGH | **Risk:** LOW | **Effort:** 0.5-1 day

Already 100% Blade. Update to use shared components:

| Template | Action |
|----------|--------|
| show.blade.php | Update cards, buttons, links to DaisyUI |
| home.blade.php | Update layout to DaisyUI |
| partials/createEntityButton.blade.php | Update dropdown to DaisyUI |

### Validation Checklist
- [ ] Dashboard renders with widgets
- [ ] Create entity button works (ticket, milestone)
- [ ] Widget grid layout preserved

---

## 2.5 Calendar Domain Conversion

**Priority:** MEDIUM | **Risk:** LOW-MEDIUM | **Effort:** 1-2 days

| Template | Format | Action |
|----------|--------|--------|
| showMyCalendar.tpl.php → showMyCalendar.blade.php | Convert | FullCalendar integration — preserve FC init |
| calendarSettings.blade.php | ✅ Blade | Update to DaisyUI |
| connectCalendar.blade.php | ✅ Blade | Update form |
| editExternalCalendar.blade.php | ✅ Blade | Update form |
| importGCal.blade.php | ✅ Blade | Update form |
| addEvent.tpl.php → addEvent.blade.php | Convert | Modal form → use `<x-form.*>` |
| editEvent.tpl.php → editEvent.blade.php | Convert | Modal form |
| delEvent.tpl.php → delEvent.blade.php | Convert | Use `<x-confirm-delete>` |
| delExternalCal.tpl.php → delExternalCal.blade.php | Convert | Use `<x-confirm-delete>` |
| export.tpl.php → export.blade.php | Convert | Export options form |

### Validation Checklist
- [ ] Calendar renders with FullCalendar
- [ ] Add/edit/delete events work
- [ ] Calendar settings save
- [ ] External calendar connection works
- [ ] Export function works

---

## 2.6 Settings Domain Conversion

**Priority:** MEDIUM | **Risk:** LOW | **Effort:** 1 day

| Template | Format | Action |
|----------|--------|--------|
| editCompanySettings.tpl.php → editCompanySettings.blade.php | Convert | Large tabbed form |
| editBoxDialog.tpl.php → editBoxDialog.blade.php | Convert | Label editing modal |

### Validation Checklist
- [ ] Company settings page renders all tabs
- [ ] All settings save correctly
- [ ] API key section works (create, view, delete)
- [ ] Label editing dialog works

---

## 2.7 Users Domain Conversion

**Priority:** MEDIUM | **Risk:** LOW | **Effort:** 1-2 days

| Template | Format | Action |
|----------|--------|--------|
| profile-box.blade.php | ✅ Blade | Update to DaisyUI |
| profile-image.blade.php | ✅ Blade | Update avatar component |
| showAll.tpl.php → showAll.blade.php | Convert | User list (DataTables) |
| newUser.tpl.php → newUser.blade.php | Convert | Invite form → `<x-form.*>` |
| editOwn.tpl.php → editOwn.blade.php | Convert | Profile edit form |
| editUser.tpl.php → editUser.blade.php | Convert | Admin user edit |
| delUser.tpl.php → delUser.blade.php | Convert | Use `<x-confirm-delete>` |

### Validation Checklist
- [ ] User list renders with DataTables
- [ ] Invite user form works
- [ ] Profile edit saves
- [ ] Admin user edit works
- [ ] Delete user confirmation works

---

## 2.8 Timesheets Domain Conversion

**Priority:** MEDIUM | **Risk:** LOW | **Effort:** 1-2 days

| Template | Format | Action |
|----------|--------|--------|
| partials/stopwatch.blade.php | ✅ Blade | Update to DaisyUI |
| showMy.tpl.php → showMy.blade.php | Convert | Personal timesheet view |
| showMyList.tpl.php → showMyList.blade.php | Convert | List view |
| showAll.tpl.php → showAll.blade.php | Convert | All timesheets (admin) |
| addTime.tpl.php → addTime.blade.php | Convert | Time entry form |
| editTime.tpl.php → editTime.blade.php | Convert | Edit time entry |
| delTime.tpl.php → delTime.blade.php | Convert | Delete confirmation |

### Validation Checklist
- [ ] Timesheet views render with correct data
- [ ] Add/edit/delete time entries work
- [ ] Stopwatch widget works
- [ ] Admin all-timesheets view works

---

## 2.9 Menu Domain Update

**Priority:** MEDIUM | **Risk:** LOW | **Effort:** 1 day

Already 100% Blade (14 templates). Update to DaisyUI 5:

- Sidebar menu → DaisyUI `menu` component
- Project selector → DaisyUI `dropdown`
- Top navigation → DaisyUI `navbar`
- Mobile menu → DaisyUI `drawer`

### Validation Checklist
- [ ] Desktop sidebar renders correctly
- [ ] Project selector dropdown works
- [ ] Top navigation shows correctly
- [ ] Mobile responsive menu works
- [ ] Menu state (open/closed) persists
- [ ] Active menu item highlights correctly

---

## Execution Order

```
2.1  Shared component library               [2-3 days]   — Foundation for all conversions
2.2a Tickets submodules                      [2-3 days]   — Shared includes first
2.2b Tickets modal dialogs                   [1 day]      — Forms → components
2.2c Tickets main views                      [3-4 days]   — Largest templates
2.3  Projects domain                         [2-3 days]   — Second highest traffic
2.4  Dashboard update                        [0.5-1 day]  — Already Blade
2.5  Calendar domain                         [1-2 days]   — FullCalendar integration
2.6  Settings domain                         [1 day]      — Large form
2.7  Users domain                            [1-2 days]   — User management
2.8  Timesheets domain                       [1-2 days]   — Time tracking
2.9  Menu domain update                      [1 day]      — Navigation styling
```
**Total: ~3-4 weeks**

---

## Conversion Rules

### .tpl.php → .blade.php Conversion Patterns

```php
// PHP echo
<?= $variable ?>              → {{ $variable }}
<?php echo $variable; ?>       → {{ $variable }}

// PHP escaped
<?php $tpl->e($var); ?>        → {{ $tpl->escape($var) }}
<?= $tpl->e($var) ?>           → {{ $tpl->escape($var) }}

// PHP unescaped
<?= $tpl->__('key') ?>         → {!! __('key') !!}

// PHP control structures
<?php if($condition): ?>        → @if($condition)
<?php endif; ?>                 → @endif
<?php foreach($items as $item): ?> → @foreach($items as $item)
<?php endforeach; ?>            → @endforeach

// Include
<?php include __DIR__ . '/submodules/file.sub.php'; ?> → @include('domain::partials.file')

// Template variables
$tpl->get('varName')           → $varName (passed via controller)
$tpl->assign('key', $value)    → Pass via view data
```

### Dual-Class Approach

During conversion, keep Bootstrap classes alongside DaisyUI to prevent visual regressions:

```html
<!-- Before -->
<div class="col-md-6">
    <input class="form-control" />
</div>

<!-- During Phase 2 (both classes) -->
<div class="col-md-6 md:w-1/2">
    <input class="form-control input input-bordered" />
</div>

<!-- Phase 4 cleanup (Bootstrap removed) -->
<div class="md:w-1/2">
    <input class="input input-bordered" />
</div>
```

---

## Risk Register

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| DataTables breaks after conversion | MEDIUM | HIGH | Preserve exact table HTML structure, test with DataTables init |
| Kanban drag-drop breaks | MEDIUM | HIGH | Keep all jQuery UI Sortable attributes and init code unchanged |
| FullCalendar breaks | LOW | HIGH | Keep FC init unchanged, only change surrounding HTML |
| Form submissions fail | LOW | HIGH | Verify every form action URL and field names match |
| HTMX interactions break | LOW | HIGH | Preserve all hx-* attributes exactly |
| Visual regressions | MEDIUM | MEDIUM | Dual-class approach, visual comparison testing |
| Chosen.js dropdowns break | LOW | MEDIUM | Keep chosen-js imports, test filter dropdowns |
| Submodule include paths break | MEDIUM | MEDIUM | Test every include path, use Blade @include properly |

---

## What This Document Does NOT Cover

- Phase 3: Canvas domains, Wiki, JS optimization, jQuery removal
- Phase 4: Remaining domains, Bootstrap removal, final cleanup
- jQuery controller modernization (preserving all existing JS in Phase 2)
- Backend/service layer changes (none needed)
