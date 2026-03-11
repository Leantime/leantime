# Component Standardization Coordination

This document is the **central coordination file** for the Leantime component standardization effort.

**ALL worker agents MUST:**
1. Read this entire document before making any changes
2. Follow the rules exactly as written
3. Update the Learnings section with anything new discovered
4. Update the Progress Tracker when done
5. Add Phase 2 candidate patterns to the Domain Component Candidates section

Last updated: Post-Phase-3 polish — chip badge colours fixed, chip hover + caret added, SetCacheHeaders HTMX bypass applied. Build clean.

---

## Canonical Component Namespace

**ALWAYS use `x-globals::` (plural).** Both `x-global::` and `x-globals::` resolve to the same directory (`app/Views/Templates/components/`). We standardize on the **plural form** because it is the majority usage (~933 vs ~387). Any remaining `x-global::` references must be converted.

---

## Component Directory Reference

All components live under `app/Views/Templates/components/` and are referenced as `<x-globals::path.to.component>`.

### Available Components:

| Component | Usage                                                       | Purpose |
|---|-------------------------------------------------------------|---|
| `<x-globals::forms.button>` | Buttons, links as buttons                                   | All clickable action elements |
| `<x-globals::forms.text-input>` | Text, number, email, password inputs                        | All text-type inputs |
| `<x-globals::forms.select>` | Dropdowns/selects and chip dropdowns for things like status | Standard HTML selects |
| `<x-globals::forms.checkbox>` | Checkboxes, toggle switches                                 | |
| `<x-globals::forms.radio>` | Radio buttons                                               | |
| `<x-globals::forms.textarea>` | Multi-line text (NOT TinyMCE)                               | |
| `<x-globals::forms.date>` | Date pickers                                                | Triggers flatpickr |
| `<x-globals::forms.dateInline>` | Compact inline date pickers                                 | |
| `<x-globals::forms.file>` | File upload inputs                                          | |
| `<x-globals::forms.form-field>` | Form field wrapper with label/validation                    | |
| `<x-globals::forms.button-group>` | Button groups                                               | |
| `<x-globals::actions.dropdown-menu>` | Dropdown menus (icon/button trigger)                        | |
| `<x-globals::actions.dropdown-item>` | Items inside dropdown menus                                 | |
| `<x-globals::actions.modal>` | Modal dialogs                                               | |
| `<x-globals::actions.confirm-delete>` | Confirm delete forms                                        | |
| `<x-globals::actions.user-select>` | User assignment avatar dropdown                             | Replaces `userDropdown` Bootstrap dropdowns |
| `<x-globals::elements.icon>` | Icons (Material Symbols + FA)                               | |
| `<x-globals::elements.card>` | Card containers                                             | **DO NOT use for maincontentinner** — emits DaisyUI classes, breaks existing CSS cascade. Use `<div class="maincontentinner">` instead. Card is only for NEW UI that has no existing CSS dependency. |
| `<x-globals::elements.badge>` | Badges/tags/labels                                          | |
| `<x-globals::elements.avatar>` | User avatars                                                | |
| `<x-globals::elements.accordion>` | Collapsible accordion                                       | |
| `<x-globals::elements.table>` | Data tables                                                 | |
| `<x-globals::elements.empty-state>` | Empty state views                                           | |
| `<x-globals::elements.big-number-box>` | Large stat/metric card with number + label               | Welcome widget, goal dashboards |
| `<x-globals::elements.section-title>` | Section/widget title heading (`<h4>`)                    | Replaces `<h4 class="widgettitle ...">` everywhere |
| `<x-globals::feedback.progress>` | Progress bars                                               | |
| `<x-globals::feedback.alert>` | Alert/notification banners                                  | |
| `<x-globals::feedback.loading>` | Loading spinner (HTMX indicator)                            | |
| `<x-globals::feedback.skeleton>` | Loading skeleton placeholders                               | |
| `<x-globals::feedback.indicator>` | Status dot indicators                                       | |
| `<x-globals::navigation.tabs>` | Navigation tab bar (URL-based)                              | View switcher tabs |
| `<x-globals::navigation.tab>` | Individual navigation tab                                   | |
| `<x-globals::navigations.tabs>` | Content tabs (panel switching)                              | In-page section tabs |
| `<x-globals::navigations.tabs.heading>` | Content tab heading                                         | |
| `<x-globals::navigations.tabs.content>` | Content tab panel                                           | |
| `<x-globals::layout.page-header>` | Page header with icon/title                                 | |
| `<x-globals::collapsible>` | Collapsible section                                         | |
| `<x-globals::undrawSvg>` | Undraw SVG illustrations                                    | |
| `<x-globals::proportion-bar>` | Segmented proportion bar                                    | |
| `<x-globals::inlineSelect>` | Inline dropdown select                                      | |
| `<x-globals::metric-cell>` | Metric display cell                                         | |
| `<x-globals::selectable>` | Selectable card (radio/checkbox)                            | |
| `<x-globals::avatar-stack>` | Stack of multiple avatars                                   | |
| `<x-globals::tickets.ticket-card>` | Ticket card                                                 | |
| `<x-globals::tickets.milestone-card>` | Milestone card                                              | |
| `<x-globals::tickets.subtasks>` | Subtask list                                                | |
| `<x-globals::tickets.ticket-submenu>` | Ticket action dropdown                                      | |
| `<x-globals::tickets.timer-button>` | Timer start/stop button                                     | |
| `<x-globals::tickets.timer-link>` | Timer as menu list item                                     | |
| `<x-globals::tickets.quickadd-form>` | Quick add form for kanban                                   | |
| `<x-globals::projects.project-card>` | Project card                                                | |
| `<x-globals::projects.project-card-progress-bar>` | Project progress bar                                        | |
| `<x-globals::projects.project-hub-projects>` | Project hub view                                            | |
| `<x-globals::projects.checklist>` | Project checklist                                           | |
| `<x-globals::goals.goal-card>` | Goal card                                                   | |
| `<x-globals::kanban.swimlane-row-header>` | Kanban swimlane header                                      | |
| `<x-globals::kanban.micro-progress-bar>` | Kanban micro progress bar                                   | |
| `<x-globals::kanban.thermometer-icon>` | Priority thermometer icon                                   | |
| `<x-globals::kanban.tshirt-icon>` | Effort t-shirt icon                                         | |
| `<x-globals::kanban.type-icon>` | Ticket type icon                                            | |
| `<x-globals::kanban.milestone-icon>` | Milestone icon                                              | |
| `<x-globals::kanban.sprint-icon>` | Sprint icon                                                 | |
| `<x-globals::kanban.time-indicator>` | Time alert indicator                                        | |

---

## Component-to-Element Mapping

### Rule: When you see raw HTML, replace with the component listed below.

#### Buttons
```html
<!-- FROM raw HTML: -->
<button class="btn btn-primary">Text</button>
<a class="btn btn-primary" href="...">Text</a>
<input type="submit" class="btn btn-primary" value="Text">

<!-- TO component: -->
<x-globals::forms.button contentRole="primary">Text</x-globals::forms.button>
<x-globals::forms.button contentRole="primary" element="a" href="...">Text</x-globals::forms.button>
<x-globals::forms.button contentRole="primary" :submit="true">Text</x-globals::forms.button>
```

**contentRole mapping:**
- `btn-primary` → `contentRole="primary"`
- `btn-default` or `btn` → `contentRole="secondary"` (or `contentRole="default"` — both map to `btn-default`)
- `btn-secondary` → `contentRole="secondary"`
- `btn-link` → `contentRole="link"`
- `btn-danger` → `state="danger"` (contentRole is ignored when state is danger/success/warning/info — state wins)
- `btn-success` → `state="success"`
- `btn-warning` → `state="warning"`
- `btn-info` → `state="info"`
- `btn-sm` → `scale="s"`
- `btn-lg` → `scale="l"`
- `btn-xs` → `scale="xs"`

**Special cases:**
- Icon-only buttons (transparent, borderless, 32×32px): use `variant="icon-only"` with `leadingVisual="icon_name"`. Renders with `btn-icon-only` CSS class — `color: var(--secondary-font-color)`, hover darkens background.
- Buttons with icons + label: use `leadingVisual="icon_name"` (icon before) or `trailingVisual="icon_name"` (icon after)
- Disabled buttons: use `:disabled="true"` prop
- Circle buttons (round with border): use `variant="circle"`
- Outline buttons: use `:outline="true"`

#### Text Inputs
```html
<!-- FROM raw HTML: -->
<input type="text" name="x" value="..." class="...">

<!-- TO component (with label and wrapper): -->
<x-globals::forms.text-input name="x" labelText="Label" value="{{ $value }}" />

<!-- TO component (bare, no wrapper - use inside existing form-group): -->
<x-globals::forms.text-input name="x" value="{{ $value }}" :bare="true" />
```

**DO NOT convert:**
- `<input type="hidden">` — keep as raw HTML
- Date inputs with `.dates` class — use `<x-globals::forms.date>` instead
- Search inputs that are part of complex JS widgets

#### Selects
```html
<!-- FROM raw HTML: -->
<select name="x" class="...">
    <option value="1">Option 1</option>
</select>

<!-- TO component: -->
<x-globals::forms.select name="x" labelText="Label" :options="$options" :selected="$selected" />

<!-- OR with inline options: -->
<x-globals::forms.select name="x">
    <option value="1">Option 1</option>
</x-globals::forms.select>
```

**Chip variant (pill-shaped select inside forms):**

Chips are purely a visual variant of `forms.select` — a `<select>` styled as a compact pill/badge. They are **form elements**, not action menus. Use them when a field needs to look like an inline tag rather than a full-width dropdown.

```html
<!-- Chip select — renders as a pill, submits like a normal select -->
<x-globals::forms.select variant="chip" name="status">
    <option value="new">New</option>
    <option value="inprogress" selected>In Progress</option>
    <option value="done">Done</option>
</x-globals::forms.select>

<!-- With HTMX inline save (no full form submit needed) -->
<x-globals::forms.select
    variant="chip"
    name="status"
    hx-post="{{ $patchUrl }}"
    hx-trigger="change"
    hx-swap="none"
    hx-vals="{{ $hxVals }}"
>
    ...
</x-globals::forms.select>
```

**Rules:**
- Chips live inside forms (or fire HTMX on change). Never use them as navigation or action triggers.
- Use `<x-globals::actions.dropdown-menu>` for actions/navigation. Use `forms.select variant="chip"` for data fields.
- Per-field chip wrappers (e.g. `<x-tickets::chips.status-select>`) are convenience components that pre-configure options, colors, and HTMX for a specific field — they all delegate to `forms.select variant="chip"` internally.
- Do **not** use `<x-globals::actions.chip>` — that component is **deprecated and must be migrated**. It still exists in the codebase pending migration (tracked in Progress Tracker). Do not use it for any new work.

#### Textareas
```html
<!-- FROM raw HTML: -->
<textarea name="x" rows="4">...</textarea>

<!-- TO component: -->
<x-globals::forms.textarea name="x" labelText="Label" rows="4" />
```

**DO NOT convert textareas with these classes (TinyMCE editors):**
- `tinymceSimple`
- `complexEditor`
- `tinymce`
- `richEditor`
- Any textarea with a TinyMCE JS initialization nearby

#### Tables
```html
<!-- FROM raw HTML: -->
<div class="table-responsive"><table class="table table-hover">...</table></div>

<!-- TO component: -->
<x-globals::elements.table :hover="true">
    <x-slot:head>
        <tr><th>Col 1</th><th>Col 2</th></tr>
    </x-slot:head>
    <tr><td>Data</td><td>Data</td></tr>
</x-globals::elements.table>
```

For DataTables (class `.dataTable` or `datatable` init): add `:datatable="true"`.

**DO NOT convert tables in Wiki `templates.blade.php` — these are document content templates.**

#### Dropdowns (Action Menus)
```html
<!-- FROM raw HTML Bootstrap dropdown: -->
<div class="dropdown">
    <a class="dropdown-toggle" data-toggle="dropdown">Label</a>
    <ul class="dropdown-menu">
        <li><a href="...">Item 1</a></li>
        <li><a href="...">Item 2</a></li>
    </ul>
</div>

<!-- TO component: -->
<x-globals::actions.dropdown-menu label="Label">
    <x-globals::actions.dropdown-item href="...">Item 1</x-globals::actions.dropdown-item>
    <x-globals::actions.dropdown-item href="...">Item 2</x-globals::actions.dropdown-item>
</x-globals::actions.dropdown-menu>
```

**Dropdown-menu props:**
- `variant="icon"` — icon-only trigger (default). Trigger gets `btn-icon-only` class automatically — no need to pass `trigger-class`. Shows `more_vert` icon when no `leadingVisual` or `label` is set.
- `variant="button"` — button trigger (uses `contentRole` to pick btn class)
- `variant="link"` — link trigger (passes `triggerClass` directly, no btn wrapping)
- `label` — text/HTML label on trigger
- `leadingVisual` — icon name (Material Symbol) or FA class before label
- `position="left"` — menu opens left-aligned (right edge aligns with trigger). Adds `.dropdown-position-left` class.
- `position="right"` — menu opens right-aligned (left edge aligns with trigger). Adds `.dropdown-position-right` class.
- Omit `position` — menu defaults to left-opening. Inside `.stickyHeader`, CSS overrides to right-align automatically.
- `scale="s"` / `scale="l"` — adjusts trigger font-size/padding via `.dropdown-scale-s` / `.dropdown-scale-l`
- `contentRole` — only used when `variant="button"` to pick the btn color class
- `trigger-class` — extra classes on the trigger `<a>`. For `variant="icon"`, defaults to `btn-icon-only` — only override when you need a different trigger style.

**dropdown-item props:**
- `href` — link URL
- `leadingVisual` — icon name
- `state="danger"` — red destructive action
- `state="active"` — active/checked state
- `:header="true"` — renders as menu section header
- `:divider="true"` — renders as divider

**DO NOT convert:**
- Complex user-assignment dropdowns with avatar loading — flag as Phase 2 candidate
- Selects using `<x-globals::forms.select variant="chip">` — these are form controls, not action dropdowns
- The `inlineSelect` component-based dropdowns — they already use a component

#### Modals
```html
<!-- FROM raw modal div: -->
<div class="modal" id="myModal">...</div>

<!-- TO component (full dialog): -->
<x-globals::actions.modal title="Title" id="myModal">
    <!-- body content -->
    <x-slot:actions>
        <x-globals::forms.button contentRole="primary" :submit="true">Save</x-globals::forms.button>
    </x-slot:actions>
</x-globals::actions.modal>

<!-- TO component (modal content mode, for HTMX-loaded content): -->
<x-globals::actions.modal mode="content" title="Title">
    ...
</x-globals::actions.modal>
```

#### Alerts/Notifications
```html
<!-- FROM raw: -->
<div class="alert alert-danger">Message</div>

<!-- TO component: -->
<x-globals::feedback.alert state="danger">Message</x-globals::feedback.alert>
```

state options: `success`, `warning`, `error` (or `danger`), `info`

#### Badges
```html
<!-- FROM raw: -->
<span class="badge badge-success">Text</span>
<span class="label label-primary">Text</span>

<!-- TO component: -->
<x-globals::elements.badge state="success">Text</x-globals::elements.badge>
```

#### Progress Bars
```html
<!-- FROM raw Bootstrap: -->
<div class="progress"><div class="progress-bar" style="width:75%"></div></div>

<!-- TO component: -->
<x-globals::feedback.progress :value="75" :max="100" />
```

#### Page Headers
```html
<!-- FROM raw: -->
<div class="pageheader">
    <div class="pageicon"><span class="fa fa-tasks"></span></div>
    <div class="pagetitle">
        <h1>Page Title</h1>
    </div>
</div>

<!-- TO component: -->
<x-globals::layout.page-header icon="fa-tasks" headline="Page Title" />

<!-- WITH subtitle and actions: -->
<x-globals::layout.page-header icon="assignment" headline="Tickets" subtitle="{{ $projectName }}">
    <x-slot:actions>
        <x-globals::forms.button contentRole="primary">New Ticket</x-globals::forms.button>
    </x-slot:actions>
</x-globals::layout.page-header>
```

Note: Use `fa-iconname` for Font Awesome, plain `icon_name` for Material Symbols.

#### Icons
```html
<!-- FROM raw Material Symbols: -->
<span class="material-symbols-outlined">home</span>
<span class="material-symbols-outlined" style="font-size:18px">settings</span>

<!-- TO component: -->
<x-globals::elements.icon name="home" />
<x-globals::elements.icon name="settings" size="sm" />
```

**DO NOT convert icons that are already using `<x-globals::elements.icon>`.**
The icon component already handles FA icons via `fa-` prefix detection.

---

## Tab Components

### Pattern 1: Navigation Tabs (Page-Level View Switching)
Use when clicking a tab navigates to a **different URL/controller**.

```html
<!-- CSS class: .lt-nav-tabs -->
<x-globals::navigation.tabs :sticky="true">
    <x-globals::navigation.tab
        label="Kanban"
        href="{{ BASE_URL }}/tickets/showKanban"
        icon="view_column"
        :active="str_contains($currentRoute, 'Kanban')" />
    <x-globals::navigation.tab
        label="Table"
        href="{{ BASE_URL }}/tickets/showAll"
        icon="table_rows"
        :active="str_contains($currentRoute, 'showAll')" />
</x-globals::navigation.tabs>
```

### Pattern 2: Content Tabs (In-Page Panel Switching)
Use when clicking a tab **shows/hides panels** on the same page.

```html
<!-- CSS class: .lt-tabs .tabbedwidget -->
<!-- After Phase 0c, use the updated navigations component: -->
<x-globals::navigations.tabs persist="url">
    <x-slot:headings>
        <x-globals::navigations.tabs.heading name="details">Details</x-globals::navigations.tabs.heading>
        <x-globals::navigations.tabs.heading name="files">Files</x-globals::navigations.tabs.heading>
    </x-slot:headings>
    <x-slot:contents>
        <x-globals::navigations.tabs.content name="details">
            <!-- content here -->
        </x-globals::navigations.tabs.content>
        <x-globals::navigations.tabs.content name="files">
            <!-- content here -->
        </x-globals::navigations.tabs.content>
    </x-slot:contents>
</x-globals::navigations.tabs>
```

**persist options:** `"url"`, `"hash"`, `"localStorage"`, or omit for no persistence.

---

## Inline Style Handling Rules

**Priority order (highest to lowest):**
1. **Component prop** — use the component's built-in prop (e.g., `scale`, `variant`, `state`)
2. **Tailwind utility** — use `tw:` prefix class
3. **CSS variable class** — if a design token exists, prefer a utility class
4. **Keep inline** — ONLY for PHP-dynamic values

### Common Replacements:

| Inline Style | Replace With |
|---|---|
| `style="width:100%"` | `class="tw:w-full"` |
| `style="width:50%"` | `class="tw:w-1/2"` |
| `style="width:33%"` or `33.33%` | `class="tw:w-1/3"` |
| `style="width:25%"` | `class="tw:w-1/4"` |
| `style="width:20%"` | `class="tw:w-1/5"` |
| `style="display:none"` | `class="tw:hidden"` |
| `style="display:flex"` | `class="tw:flex"` |
| `style="display:inline-flex"` | `class="tw:inline-flex"` |
| `style="display:block"` | `class="tw:block"` |
| `style="display:inline-block"` | `class="tw:inline-block"` |
| `style="text-align:center"` | `class="tw:text-center"` |
| `style="text-align:right"` | `class="tw:text-right"` |
| `style="float:right"` | `class="tw:float-right"` (prefer flex instead) |
| `style="float:left"` | `class="tw:float-left"` |
| `style="overflow-x:auto"` | `class="tw:overflow-x-auto"` |
| `style="overflow:hidden"` | `class="tw:overflow-hidden"` |
| `style="vertical-align:middle"` | `class="tw:align-middle"` |
| `style="padding:0"` or `padding:0px` | `class="tw:p-0"` |
| `style="padding:5px"` | `class="tw:p-1"` |
| `style="padding:10px"` | `class="tw:p-2"` (approx) |
| `style="padding:15px"` | `class="tw:p-4"` (approx) |
| `style="margin:0"` | `class="tw:m-0"` |
| `style="margin-bottom:5px"` | `class="tw:mb-1"` |
| `style="margin-bottom:10px"` | `class="tw:mb-2"` |
| `style="margin-bottom:15px"` | `class="tw:mb-4"` |
| `style="margin-bottom:20px"` | `class="tw:mb-5"` |
| `style="margin-bottom:30px"` | `class="tw:mb-8"` |
| `style="margin-bottom:40px"` | `class="tw:mb-10"` |
| `style="margin-top:10px"` | `class="tw:mt-2"` |
| `style="margin-top:20px"` | `class="tw:mt-5"` |
| `style="margin-right:5px"` | `class="tw:mr-1"` |
| `style="margin-right:10px"` | `class="tw:mr-2"` |
| `style="margin-left:5px"` | `class="tw:ml-1"` |
| `style="font-size:var(--font-size-l)"` | Add as CSS class or use component scale prop |

### Styles That MUST Stay Inline (Dynamic PHP values):
- `style="background-color: {{ $color }}"` — dynamic color from data
- `style="width: {{ $percent }}%"` — dynamic percentage
- `style="border-left: 3px solid {{ $priorityColor }}"` — dynamic border color
- `style="color: {{ $textColor }}"` — dynamic text color
- Any `style` with `{{ }}` PHP expressions

### Styles That Stay for Functional Reasons:
- `style="visibility:hidden"` on tab containers — FOUC prevention (removed by JS)
- `style="display:none"` on elements that are shown/hidden by JavaScript behavior — evaluate case by case, may need to stay

---

## Domain-Specific Class Name Fixes

### Classes to REMOVE or REPLACE:

| Class Found | Action | Replacement |
|---|---|---|
| `ticketDropdown` | Remove — use component | Use `<x-globals::actions.dropdown-menu>` for actions/navigation |
| `userDropdown` | Remove — use component | Same as above |
| `statusDropdown` | Remove — use chip select | Use `<x-globals::forms.select variant="chip" name="status">` inside a form |
| `relatesDropdown` | Remove — use chip select | Use `<x-globals::forms.select variant="chip" name="dependingTicketId">` inside a form |
| `milestoneDropdown` | Remove — use chip select | Use `<x-globals::forms.select variant="chip" name="milestoneid">` inside a form |
| `ticketTable` | Remove | Use `<x-globals::elements.table>` without domain class |
| `timesheetTable` | Remove | Use `<x-globals::elements.table>` without domain class |
| `ticketRows` | Remove | Generic row styling |
| `timesheetRow` | Remove | Generic row styling |
| `userBox` | Replace | Use `<x-globals::elements.card>` |
| `projectBox` | Replace | Use `<x-globals::projects.project-card>` or `<x-globals::elements.card>` |
| `stdform` | Remove | Legacy class, no replacement needed |
| `maincontentinner` | Replace | Use `<x-globals::elements.card>` |

### Classes That STAY (functional JS hooks):
- `ticketTabs`, `projectTabs`, `clientTabs`, `accountTabs`, `companyTabs` — WAIT for content tabs component update (Phase 0c), then remove
- `formModal`, `milestoneModal`, `ticketModal`, `sprintModal` — Keep (trigger jQuery modal behavior, not yet converted)
- `secretInput` — Keep (triggers inline-editing JS)
- `sortableTicketList` — Keep (jQuery sortable target)
- `ticketBox` — Keep for now (used by ticket-card component internally), evaluate per-instance
- `widgettitle`, `title-light`, `title-primary` — ✅ Converted via `<x-globals::elements.section-title>` component. All `app/Domain/` blade files migrated.

### Classes That Are CORRECT and STAY:
- `maincontentinner` inside known components — only replace when it's a bare wrapper div
- All Bootstrap grid classes (`col-md-*`, `row`, etc.)
- All `lt-*` prefixed classes (Leantime-specific utilities)

---

## Files to SKIP ENTIRELY

1. `app/Domain/Dev/Templates/componentPreview.blade.php` — Intentional raw HTML for component preview
2. `app/Domain/Wiki/Templates/templates.blade.php` — Document content templates (tables are document formatting)
3. `app/Domain/Help/Templates/*.blade.php` that are single-line stubs (content = just `@extends` or `@include`)
4. All `.tpl.php` files — Legacy PHP templates, separate effort
5. `<input type="hidden">` elements — Don't convert to component

---

## Key Patterns Reference

### The `widgettitle` Pattern (✅ DONE — Component created and all `app/Domain/` files converted)
```html
<!-- BEFORE (legacy): -->
<h4 class="widgettitle title-light">
    <x-globals::elements.icon name="icon_name" />
    Section Title
</h4>

<!-- AFTER (component): -->
<x-globals::elements.section-title icon="icon_name">Section Title</x-globals::elements.section-title>

<!-- Variants: -->
<x-globals::elements.section-title>Text</x-globals::elements.section-title>                          {{-- light (default) --}}
<x-globals::elements.section-title variant="plain">Text</x-globals::elements.section-title>           {{-- no modifier --}}
<x-globals::elements.section-title variant="primary">Text</x-globals::elements.section-title>         {{-- primary --}}
<x-globals::elements.section-title variant="primary" :borderColor="$color">Text</x-globals::elements.section-title>  {{-- kanban border --}}
<x-globals::elements.section-title icon="forum">Text</x-globals::elements.section-title>              {{-- with icon prop --}}
<x-globals::elements.section-title class="tw:mt-5">Text</x-globals::elements.section-title>           {{-- extra classes via $attributes --}}
<x-globals::elements.section-title tag="h5">Text</x-globals::elements.section-title>                  {{-- h5 tag --}}
```

### The `maincontentinner` Pattern
```html
<!-- FROM: -->
<div class="maincontentinner">
    <h4 class="widgettitle ...">Title</h4>
    content...
</div>

<!-- TO: -->
<x-globals::elements.card title="Title">
    content...
</x-globals::elements.card>
```

### User Assignment Dropdown (COMPLEX — flag as Phase 2)
These appear 9+ times identically. They load user avatars dynamically and have complex JS. Flag for Phase 2 component extraction but DO NOT attempt to convert them now.
```html
<!-- Pattern to flag, not convert: -->
<div class="dropdown ticketDropdown userDropdown noBg">
    <a class="dropdown-toggle" data-toggle="dropdown">
        <img src="..." />
    </a>
    <ul class="dropdown-menu">
        @foreach($users as $user)
            <li>...</li>
        @endforeach
    </ul>
</div>
```

### Filter Bar Pattern (Phase 2 candidate)
```html
<!-- Common in Tickets/Timesheets — flag but don't convert yet -->
<div class="tw:flex tw:items-center tw:justify-between tw:flex-wrap tw:gap-2 tw:mb-5">
    <!-- filter controls -->
</div>
```

---

## Decisions Log

| # | Question | Decision | Rationale |
|---|---|---|---|
| 1 | Namespace: x-global:: or x-globals::? | `x-globals::` (plural) | Majority usage (~933 vs ~387) |
| 2 | Tab standardization? | Both patterns are valid + distinct | Navigation tabs = URL links, Content tabs = panel switching |
| 3 | Wiki content templates? | Skip `templates.blade.php` | Document formatting, not UI |
| 4 | Raw `<form>` tags? | Convert where form component exists | Consistency |
| 5 | Dev componentPreview? | Skip | Intentional raw HTML |
| 6 | Content tabs component? | Update `navigations/tabs` to use `data-tabs` | Don't create new, fix existing |
| 7 | Inline style strategy? | Component props > Tailwind > CSS vars > inline | Dynamic PHP values stay inline |
| 8 | User assignment dropdowns? | Flag for Phase 2, don't convert now | Too complex, needs dedicated component |
| 9 | `ticketBox` class? | Keep on ticket-card component, replace on generic divs | Component uses it internally |
| 10 | `formModal` / `ticketModal` etc.? | Keep — jQuery modal hooks | Not converted until modal system refactored |
| 11 | Chips: form control or action component? | Form control only — `forms.select variant="chip"` | Chips that change data are semantically form inputs. `actions.chip` is deprecated. |

---

## Learnings

*Worker agents: add your discoveries here. Format: `[Domain] - observation`*

### Namespace
- Both `x-global::` and `x-globals::` are registered in `ViewsServiceProvider.php` lines 413-414, both pointing to `app/Views/Templates/`. Functionally identical. Standardizing to `x-globals::`.

### Tab System
- `components/navigation/` (singular) = modern URL-based nav tabs using `.lt-nav-tabs` CSS
- `components/navigations/` (plural) = legacy jQuery-based content tabs (being updated to data-tabs pattern)
- Raw `<div class="lt-tabs tabbedwidget" data-tabs>` is currently the majority content tab implementation
- `tabsController.js` handles the data-tabs pattern natively

### Forms
- `x-globals::forms.form` component exists (70 uses) — raw `<form>` (45+ uses) should be converted
- Many forms use `class="stdform"` — this class should be removed

### Chips
- Chips are a **form element variant**, not an action/navigation pattern.
- The correct component is `<x-globals::forms.select variant="chip">` — a standard `<select>` styled as a pill.
- `<x-globals::actions.chip>` is **deprecated and pending migration** — it still exists in the codebase but must not be used for any new work. All existing usages must be migrated to `forms.select variant="chip"`.
- Per-field wrappers like `<x-tickets::chips.status-select>` exist as convenience components; they configure options/colors/HTMX for a specific field and delegate to `forms.select variant="chip"` internally.
- SlimSelect is auto-initialized on `select.select-chip` elements by `componentInitializer.js` + `dropdownBridge.js`. Badge HTML for options is provided via `data-chip-html` attributes on `<option>` tags and fed into SlimSelect via `setData()`.
- Chips that save inline (without a full form submit) use `hx-post` + `hx-trigger="change"` + `hx-vals` on the `<select>` element. Compute `hx-vals` as `json_encode(['id' => (string)$id])` in a `@php` block — never inline JSON with `{{ }}` inside single-quoted HTML attributes.

### Buttons
- Icon-only buttons **must** use `variant="icon-only"` on `<x-globals::forms.button>`. This emits `btn-icon-only` (32×32px, transparent, `--secondary-font-color`). Do not fake this with inline styles (`width:44px; height:44px` etc.) — that was a bug pattern found in `welcome.blade.php` and `myToDos.blade.php`.
- `btn-icon-only` hover uses `--hover-bg` token. Text decoration is suppressed in `forms.css` — do not add `text-decoration:none` overrides elsewhere.
- `btn btn-link` hover text-decoration is already handled in `forms.css`. Do not add button rules to `text-styles.css`.
- If icon buttons need a custom color due to their container (e.g. white icons on a gradient header), scope a CSS rule in the relevant stylesheet — do not use inline `style="color:..."`.

### CSS Tokens
- `--hover-bg` is the canonical ghost/subtle hover background token. Defined in all 4 theme files (`default/light`, `default/dark`, `minimal/light`, `minimal/dark`). Use this for any subtle hover state — icon buttons, accordion toggles, list rows, day selectors, etc.
- `--dropdown-link-hover-bg` points to `--hover-bg`. Do not override it separately.
- Do **not** use hardcoded `rgba(0,0,0,0.08)` or similar — use `var(--hover-bg)` instead.

### Dropdowns
- All dropdown item sizing (height, padding) is defined once in the base `.dropdown-menu > li > a` rule in `dropdowns.css`. Do not add scoped per-domain overrides. If the base value is wrong, fix the base.
- Dropdown menus support `align="end"` (opens leftward, right-anchored) and `align="start"` (opens rightward) on both `actions.dropdown-menu` and `actions.chip`. This maps to `.dropdown-position-left` / `.dropdown-position-right` CSS classes.
- The `widgetContent:has(.dropdown.open)` overflow hack has been removed. Dropdowns inside widgets must use `align="end"` so the menu opens leftward within the widget bounds.
- The `::after` triangle pointer on dropdowns has been removed entirely.
- `text-decoration:none` on dropdown links is handled by `.dropdown a:hover` in `text-styles.css`.

### CSS File Ownership — Where Styles Belong
- Button styles (including text-decoration, hover) → `forms.css`
- Dropdown styles (sizing, positioning, alignment) → `dropdowns.css`
- Link/text decoration rules → `text-styles.css` (links only, not buttons)
- Widget layout (stickyHeader, widgetContent) → `style.default.css`
- Hover background token → theme files (`public/theme/*/css/*.css`)

### Workflow Rules
- **Always check `app/Views/Templates/components/` before creating a new component.** List the directory and read likely candidates before writing anything new. Creating a duplicate is worse than using an imperfect existing one.
- **Scoped CSS overrides for things that should be uniform are a bug.** If you find yourself writing `.someContext .dropdown-menu > li > a { padding: ... }`, stop — fix the base rule instead.

### Page Header Component and @dispatchEvent Deduplication
- The `page-header` component (`layout/page-header.blade.php`) already includes `@dispatchEvent('beforePageHeaderOpen')`, `@dispatchEvent('afterPageHeaderOpen')`, `@dispatchEvent('beforePageHeaderClose')`, and `@dispatchEvent('afterPageHeaderClose')` internally.
- When converting a raw `<div class="pageheader">` that has these dispatch events explicitly surrounding it, **remove** the surrounding dispatch events — they will double-fire if both the wrapping and the component both emit them.
- The `subtitle` prop on `page-header` maps to a `<small>` tag inside `.pagetitle`, shown above the `<h1>`.

### Tab Conversion — navigations.tabs with Content
- When converting `<div class="lt-tabs tabbedwidget projectTabs" data-tabs>` (or `clientTabs`, etc.) to `x-globals::navigations.tabs`, the tab heading slot is `x-slot:headings` and the content slot is `x-slot:contents`.
- Tab ID matching: the `name` attribute on `.heading` and `.content` must match (e.g. `name="projectdetails"`).
- Content that was previously in a bare `<div id="tabname">` moves into `<x-globals::navigations.tabs.content name="tabname">`.
- `@dispatchEvent('projectTabsList')` that was inside the `<ul role="tablist">` can remain inside `<x-slot:headings>` between heading entries.
- `@dispatchEvent('projectTabsContent')` that was after the last tab div goes inside `<x-slot:contents>` after the last content block.

### Elements.table Component and colgroup
- The `elements.table` component does not have a dedicated `colgroup` slot; it renders `<thead>{{ $head }}</thead>`. Put `<colgroup>` inside the `<x-slot:head>` before the `<tr>`.
- The component already wraps the table in `<div style="overflow-x: auto;">` — do not add another wrapper.

### Inline Styles with CSS Custom Properties
- Static CSS variable references like `style="color:var(--main-titles-color)"` and `style="font-size:38px"` in template hero/intro sections have no direct Tailwind equivalent. Keep them inline when the value is a design token with no Tailwind mapping, or when the size is an unusual value not in the Tailwind scale.
- `style="margin-top:0px"` → `class="tw:mt-0"` (simple substitution always works).

### userBox → elements.card
- `<div class="userBox">` user cards in project team assignment forms → `<x-globals::elements.card>`. The card component wraps in a styled box. For the "add user" variant `userBox--add`, pass `class="userBox--add"` as an extra attribute.

### stdform class
- `class="stdform"` on `<form>` elements is a legacy class with no visual effect. Simply remove it — no replacement needed.

### Big Number Box
- `<x-globals::elements.big-number-box>` was created for the large stat card pattern (`bigNumberBox` CSS class).
- Props: `number` (the value), `label` (description), `state` (`on-track`/`at-risk`/`miss` → maps to `priority-border-*`).
- Supports `$slot` for rich content (e.g. progress bars inside the box).
- Used in: `Widgets/partials/welcome.blade.php`, `Goalcanvas/Templates/dashboard.blade.php`, `PgmPro/Templates/goalDashboard.blade.php`.

### display:none — When to Keep Inline vs. Tailwind tw:hidden
- `style="display:none"` must stay **inline** when JavaScript uses `element.style.display = ''` or `element.style.display = 'block'` to show/hide the element (e.g. `jQuery(...).show()/.hide()`, or JS that sets `el.style.display = (view === 'x') ? '' : 'none'`). jQuery `.show()` sets `style.display = 'block'` which only overrides the inline `style` attribute — it does NOT remove `class="tw:hidden"`. So if you use `tw:hidden` instead of inline, jQuery's `.show()` won't work.
- Safe to use `tw:hidden` when the element is conditionally rendered server-side (never toggled by JS at runtime).
- Calendar day-selector: keep `style="display:none"` — JS sets `daySelector.style.display = ...` directly.
- quickAddForm, subtask-form, task-add-form divs: keep `style="display:none;"` — jQuery `.show()/.hide()` toggle them.

### Tickets Domain — Chip Convenience Components
- Per-field chip convenience components live at `app/Domain/Tickets/Templates/components/chips/` (`status-select.blade.php`, `milestone-select.blade.php`, `effort-select.blade.php`, `priority-select.blade.php`, `sprint-select.blade.php`). These are the canonical reference implementations for each chip type — they pre-configure options, colors, `data-chip-html`, and HTMX. Use them directly in templates where a single-ticket field chip is needed, rather than writing a raw `forms.select variant="chip"` from scratch.
- `hx-vals` for ticket patch chips must be `json_encode(['id' => (string)$id])` — never a raw integer, always cast to string.
- `ticketRows` class (legacy) → replace with `ticket-row` (generic, no special meaning). Do not keep `ticketRows` — it is domain-specific and not needed.
- `ticketTable` class → remove entirely when wrapping in `elements.table`.

### Canvas Domain — statusDropdown/relatesDropdown/userDropdown Classes Must Stay
- `canvasController.js` uses `.statusDropdown .dropdown-menu a`, `.relatesDropdown .dropdown-menu a`, and `.userDropdown .dropdown-menu a` as event delegation selectors (via `e.target.closest(...)`).
- These classes are functional JS hooks — removing them breaks canvas item status/relates/user updates.
- Do NOT convert these Bootstrap dropdowns to `actions.dropdown-menu` component without a full JS refactor. Flag as Phase 2.
- The `ticketDropdown` class on these canvas dropdowns is also referenced alongside `statusDropdown`/`relatesDropdown` — keep all three classes.
- The `initStatusDropdown()`, `initRelatesDropdown()`, `initUserDropdown()` canvas controller methods wire these up. Do not remove the init calls.

### Canvas Split-File Pattern (showCanvasTop / showCanvasBottom)
- Canvas main page uses two partial includes: `showCanvasTop.blade.php` opens `<div class="maincontent">` and `<x-globals::elements.card :flush="true">`, while `showCanvasBottom.blade.php` closes `</x-globals::elements.card>` and `</div>`.
- When converting, the card opening tag goes in Top and the closing tag goes in Bottom — they're complementary halves.
- `showCanvasBottom.blade.php` also contains the `makeInputReadonly` JS call — update its selector to `.maincontent` when maincontentinner is replaced.

### Date Inputs in Canvas Dialogs
- Raw `<input type="text" class="startDate" />` and `<input type="text" class="endDate" />` that are initialized via `leantime.dateController.initDateRangePicker(".startDate", ".endDate")` should be converted to `<x-globals::forms.date name="startDate" class="startDate" />`.
- The `forms.date` component still renders an `<input>` that flatpickr can target — the JS init call remains unchanged.

### PHP String Icon HTML in Blade @php Blocks
- Labels computed in `@php` blocks as PHP strings (e.g., `$statusFilterLabel = ... ? '...' : '<span class="material-symbols-outlined" style="font-size:inherit;vertical-align:middle;">' . $icon . '</span> ' . $title`) are passed as `:label` to components.
- Remove `style="font-size:inherit;vertical-align:middle;"` from these PHP strings — the material-symbols-outlined class already provides appropriate sizing in context. The icon is decorative within a button label.

### actions.chip → forms.select variant="chip" Conversion Guide
- The old `actions.chip` renders as a Bootstrap dropdown (JS-driven, writes to a hidden `<input id="dropdownPill-{id}-{role}">`).
- The new `forms.select variant="chip"` renders as a `<select class="select-chip">` auto-initialized by SlimSelect.
- Each `<option>` needs `data-chip-html="..."` with badge HTML for SlimSelect's rich rendering.
- For HTMX inline save: add `hx-post`, `hx-trigger="change"`, `hx-swap="none"`, `hx-vals` on the `<select>`.
- `hx-vals` must be a PHP string from `json_encode(...)` — never inline JSON inside single-quoted HTML attribute.
- When the chip previously used `initStatusDropdown()` / `initEffortDropdown()` / `initMilestoneDropdown()` JS, those init calls can be removed after converting to chip select — SlimSelect initializes automatically.
- For colorized status chips: compute `chip-badge` class from hex vs. CSS class; use `state-default` for hex-colored items with `style="background:..."`.
- Status labels in Widgets use `$statusLabels[$ticket['projectId']]` (per-project), while Dashboard show.blade.php uses a flat `$statusLabels` array (project-scoped view).

### Wiki Domain — Complex Inline Property Dropdowns
- The wiki `show.blade.php` has domain-specific status/parent/milestone dropdowns in the properties panel. These use Bootstrap `data-toggle="dropdown"` with custom JS `data-value` click handlers and `saveField()` fetch calls. They do NOT conform to the standard `actions.dropdown-menu` pattern (which fires navigation or actions). These are data-saving controls — candidates for `forms.select variant="chip"` or a custom wiki properties component (Phase 2). Leave as-is for now.
- The `page-header` component `$slot` is used (when no `headline` prop is set) to pass complex h1 + inline dropdown content. This works but the slot goes inside `.pagetitle`. Use `<x-slot:actions>` for any icon/button actions that should sit at the right edge of the header bar.

### Tiptap Editors (Rich Text — Do Not Convert)
- Textareas with class `tiptapComplex`, `tiptapSimple`, `tiptapInline`, or `wiki-editor-textarea` are Tiptap rich-text editors. Treat them the same as TinyMCE textareas — NEVER convert to `forms.textarea` component.
- These are initialized by `leantime.tiptapController.initComplexEditor()` / `initSimpleEditor()` / `initComplex()` in JS.

### Comment Reaction Buttons — Domain-Specific, Leave as-is
- `reactions.blade.php` buttons (`class="reaction-btn"`, `class="add-reaction-btn"`) have domain-specific CSS classes and special HTMX behavior. Do NOT convert to `forms.button` — the custom classes are required for reaction styling/behavior.

### Auth/Login-style Layouts
- Auth templates (`login.blade.php`, `requestPwLink.blade.php`, `resetPw.blade.php`, `verify.blade.php`) use `regcontent` div instead of `maincontent`/`maincontentinner`. Do NOT convert `regcontent` to `elements.card` — it's a distinct auth layout class.
- Auth page headers have NO icon — pass `headline` only; no `icon` or `subtitle` needed.
- `login.blade.php` had all 4 dispatchEvents around the pageheader — all removed when converting to `page-header` component (component emits them internally).
- For `requestPwLink.blade.php`, only `beforePageHeaderOpen` and `afterPageHeaderClose` were present (no `after/before` inner events) — still removed because component emits all four internally.

### onboardingProgress Partial
- The progress bar inside `onboardingProgress.blade.php` had `style="width: {{ $percentComplete }}%"` which is a dynamic value and must stay if kept raw. Converting to `<x-globals::feedback.progress :value="$percentComplete" :max="100">` is correct — the component handles the dynamic width internally.
- The step position `style="left: X%"` on step divs must stay inline — these are precise dynamic CSS values positioning the step dots.
- The icon `style="color:var(--main-action-color); padding-left:3px;"` in step circles should stay inline — CSS variable with specific small padding that has no Tailwind equivalent.

### Timesheets Domain — JS-Driven Classes Must Stay
- `timesheetTable` class: kept as an extra class on `elements.table` because (a) `jQuery(".timesheetTable input").change(...)` in `showMy.blade.php` targets it, (b) scoped `<style>` blocks in both `showMy` and `showMyList` use `.timesheetTable` selectors, (c) `tables.css` and `overwrites.css` have `.timesheetTable` CSS rules. The coordination doc lists it as "Remove" but removal breaks runtime JS. Decision: **keep as extra class on the component** to preserve behavior.
- `timesheetRow` class: same reasoning — `jQuery(".timesheetRow").each(...)` and scoped `<style>` blocks reference it. Keep on `<tr>` elements.
- When `maincontentinner` rules like `.maincontentinner .timesheetTable td input` exist in `tables.css`, update them to use just `.timesheetTable` (drop the parent selector) when converting to `elements.card`. Updated `public/assets/css/components/tables.css` accordingly.
- The filter bar in `showAll.blade.php` uses custom `tw:p-5` padding within a `:flush="true"` card so the filter bar and table have consistent edge-to-edge spacing.

### forms.button — v1 Alias `type=` Still Works But Standardize
- `type="primary"` is a valid v1 alias for `contentRole="primary"` (see button component line 26). It works at runtime, but we standardize on `contentRole=` (v3 API) in all new/updated templates.
- `submit` (bare boolean) and `:submit="true"` (explicit prop) both work. Prefer `:submit="true"` for clarity.
- `state="danger"` is correct for danger/delete buttons (not `contentRole="danger"` — state wins over contentRole for danger/success/warning/info).

### simpleColorPicker Inputs (Do Not Convert)
- `<input type="text" class="simpleColorPicker">` elements are JS-initialized color picker widgets. A custom initializer targets them by class name and replaces them with a color swatch UI. Do NOT convert to `forms.text-input` — keep as raw `<input type="text" class="simpleColorPicker">`. Found in `Calendar/Templates/editExternalCalendar.blade.php` and `Calendar/Templates/importGCal.blade.php`.

### FullCalendar Navigation Button Classes (JS Hook — Keep)
- FullCalendar buttons like `fc-prev-button`, `fc-next-button`, `fc-today-button` are referenced via jQuery selectors. When converting to `forms.button`, pass the FC class via `class="fc-today-button"` as an extra attribute — the component merges it onto the rendered element. This preserves JS selector behavior while using the standard component. Found in `Calendar/Templates/showMyCalendar.blade.php`.

### Scoped `<style>` Selector Updates After maincontentinner Conversion
- When a `<style>` block in a template contains rules scoped to `.maincontentinner` (e.g. `.maincontent .maincontentinner { height: calc(100vh - 165px); }`), those rules break after converting to `elements.card`. Solution: add a custom `class="myCustomCard"` prop to the card component instance and update the `<style>` rule to target `.myCustomCard` instead. Found in `Calendar/Templates/showMyCalendar.blade.php` → custom class `calendarMainCard`.

### makeInputReadonly Selector Updates
- When `maincontentinner` divs are replaced with `elements.card`, any `makeInputReadonly(".maincontentinner")` call in the same template must be updated to a broader selector like `.maincontent` to preserve read-only enforcement for non-editor roles.
- `elements.card` renders with class `tw:card tw:bg-base-100 tw:shadow-sm` — it does NOT add `maincontentinner` class.

### elements.card vs. maincontentinner Structural Difference
- `maincontentinner` is a CSS class: adds border-radius, glass background, padding 20px, margin-bottom 10px, shadow, border, flex.
- `elements.card` wraps content in a `<div class="tw:card-body">` unless `:flush="true"` is passed. This adds internal padding on top of the card's own styling — consider if double-padding is a problem.
- For existing layouts where children depend on the parent container's padding, use `:flush="true"` on the card to avoid the extra `tw:card-body` wrapper.

### Ideas Domain — actions.chip → forms.select variant="chip" via hx-patch
- The Ideas `showBoards.blade.php` status chip previously used `actions.chip` + `initStatusDropdown()` JS (which listened on `.statusDropdown .dropdown-menu a` and sent a PATCH fetch to `/api/ideas`).
- After converting to `forms.select variant="chip"`, the `initStatusDropdown()` call is removed. The chip select uses `hx-patch="{{ BASE_URL }}/api/ideas"` + `hx-trigger="change"` + `hx-vals` (JSON-encoded id) — the `name="box"` on the select posts the new status value.
- The `/api/ideas` patch endpoint (`Domain/Api/Controllers/Ideas.php::patch()`) accepts `id` + any field params and calls `patchCanvasItem($id, $params)` — this maps perfectly to the chip's form data.
- When a domain has no HxController but has a REST API controller with a PATCH endpoint, use `hx-patch` to that endpoint as the chip save mechanism.

### Ideas Domain — Page Header with Complex Slot Content
- `advancedBoards.blade.php` and `showBoards.blade.php` use the page-header component's `$slot` (default, no `headline` prop) to pass complex h5 + dropdown-menu + h1 content. This is the same pattern as Wiki's `show.blade.php`.
- The default slot content goes inside `.pagetitle` div. Any action buttons at the right edge go in `<x-slot:actions>`.
- Do NOT wrap the slot content in a named `x-slot:headline` — that slot does not exist on the component. Use the bare default slot when `headline` prop is omitted.

### Plugins Domain — Plugin Card Inline Styles with CSS Tokens
- Plugin card containers (`plugin.blade.php`, `myapps.blade.php`) use CSS custom property values like `var(--kanban-card-bg)`, `var(--box-radius)`, `var(--regular-shadow)` in `background`, `border-radius`, and `box-shadow`. These can be converted to Tailwind arbitrary-value syntax: `tw:bg-[var(--kanban-card-bg)]`, `tw:rounded-[var(--box-radius)]`, `tw:shadow-[var(--regular-shadow)]`. Apply same pattern for `border-radius` variants (`--box-radius-small`, `--box-radius-large`).
- CSS token text colors can be expressed as `tw:text-[color:var(--secondary-font-color)]` (note the `color:` qualifier needed for Tailwind to recognize it as a color value, not an arbitrary property).

### Plugins Domain — plugintabs.blade.php Pattern
- The `<div class="maincontentinner tabs"><ul>...</ul></div>` navigation tab pattern (with `class="active"` on `<li>`) converts to `<x-globals::navigation.tabs>` + `<x-globals::navigation.tab>` components. This produces the correct `lt-nav-tabs` CSS class wrapper.
- Use `navigation.tabs` (singular) for URL-based nav tabs — these are page-level tab links, NOT in-page content switching.

### Menu Domain — display:block on `<ul>` elements
- A `<ul style="display:block;">` inside the menu structure converts to `<ul class="tw:block">`. This is the simplest static style conversion.

### Menu Domain — Notification Badge
- The `<span class="notificationCounter badge badge-danger badge-xs">` converts to `<x-globals::elements.badge state="danger" scale="xs" class="notificationCounter">`. The `notificationCounter` class is preserved as a merge class for jQuery targeting (`.notificationCounter.fadeOut()`).

### Menu Domain — projectSelector.blade.php Inline Border
- The `style="border: none; border-bottom: 1px solid var(--main-border-color);"` converts to `tw:border-0 tw:border-b tw:border-b-[var(--main-border-color)]` using arbitrary CSS variable values.

### Canvas Variant showCanvas Files — Layout-Only Width Styles
- Canvas variant `showCanvas.blade.php` files only contain canvas grid layout columns (`<div class="column" style="width:X%">`). These control the proportional widths of canvas sections.
- Standard values (25%, 33%, 33.33%, 50%, 20%, 100%) could be converted to Tailwind, but non-standard values (13.33%, 16%, 28%, 40%, 84%) have no clean Tailwind equivalent. Since canvas files mix standard and non-standard widths in the same file, it's cleaner to leave ALL canvas column widths as inline styles for consistency.
- Inline padding-top/padding-bottom overrides within canvas rows (like `padding-top: 0px`) CAN be converted since `tw:pt-0`/`tw:pb-0` are standard. These appear in nested canvas row sections and can be split: add the Tailwind class and remove the inline padding portion while keeping the width portion inline.

### Install Domain — Auth Layout Templates
- Install templates (`new.blade.php`, `update.blade.php`) use `regcontent` layout (same as Auth). Do NOT convert `regcontent` to `elements.card` — it's a distinct auth layout wrapper.
- The `pageheader` in install templates has NO `pageicon` div (no icon) — use `page-header` with only `headline` prop, no `icon`.
- Install templates use `$tpl->language->__()` (calling `language` property) instead of `$tpl->__()` — this is a minor variation; leave as-is.

### Setting Domain — jQuery Tabs → data-tabs
- `companyTabs` previously used jQuery UI `.tabs()` initialized in a `<script>` block. Converted to `data-tabs` attribute on the wrapper div + standard `href="#tabname"` link format. The jQuery script block was removed entirely — `tabsController.js` handles data-tabs natively.
- `style="height:auto;"` on empty logo preview div → remove entirely (height:auto is the CSS default, no Tailwind needed).

### Chart.js Canvas Elements — Keep Inline Size
- Chart.js requires fixed pixel dimensions for `<canvas>` elements via their parent container: `style="width:100%; height:350px;"`. The height must stay inline — Tailwind's `tw:h-[350px]` would work but the chart renders better with an explicit inline height. Keep `style="width:100%; height:350px;"` as-is on chart containers.
- `style="width:100%; height:250px;"` on the project progress pie chart container — same rule applies.

### Valuecanvas canvasDialog — Custom vs. Shared canvasDialog
- `Valuecanvas/canvasDialog.blade.php` is a full standalone dialog (228 lines) unlike other canvas variants which use `@include('canvas::canvasDialog', ...)`. This is because Valuecanvas has distinct field configuration. Apply the same conversion rules as a regular canvas dialog (buttons, inline styles, etc.).
- The `tiptapSimple` textareas in this dialog must NOT be converted — the JS initializer targets them by class name.
- `display:none` on `#newMilestone` and `#existingMilestone` divs must stay inline — toggled by `leantime.valueCanvasController.toggleMilestoneSelectors()`.

### actions.user-select Component — JS Coupling Requirements
- The `userDropdown` Bootstrap dropdown pattern has tight JS coupling via specific DOM `id` and `data-*` attributes. The component preserves ALL of them exactly:
  - Trigger link: `id="userDropdownMenuLink{entityId}"` — targeted by `initUserDropdown()` event delegation
  - Avatar span: `id="userImage{entityId}"` — JS swaps `<img>` after user selection
  - Name span: `id="user{entityId}"` — JS updates text label after user selection
  - Item links: `id="userStatusChange{entityId}{userId}"` — JS selects chosen item
  - `data-value="{entityId}_{userId}_{profileId}"` — parsed by JS to know which entity + user + profile
  - `data-label="{full name}"` — JS uses this to update the name span after selection
- Base classes `ticketDropdown userDropdown noBg` are ALWAYS emitted regardless of domain. These are the event delegation selectors in `ticketsController.initUserDropdown()`, `ideasController.initUserDropdown()`, and `canvasController.initUserDropdown()`.
- Note: `Ideas/showBoards.blade.php` original had only `userDropdown noBg` (missing `ticketDropdown`). The component always adds `ticketDropdown` — this is intentional and harmless; the Ideas controller only looks for `.userDropdown`.
- Extra positioning classes (`lastDropdown`, `dropRight`, `right`) are passed via the `dropdownClasses` prop and appended to the base classes.
- The `showUnassign` prop adds a "not assigned" `data-value="{entityId}_0_0"` item — used by showAll (tickets) which allows unassigning. Canvas, Ideas, Kanban do not show this item.
- The `showNameLabel` prop controls whether a text label appears beside the avatar. Table views show the name; Kanban/card views show avatar only.
- The `showArrowIcon` prop controls the `arrow_drop_down` icon after the label — used in milestone table views.
- `goals/goal-card.blade.php` uses the `$users` prop (not `$tpl->get('users')`) because it is a shared component that receives users as a prop from its caller.
- `Canvas/element.blade.php` originally included `&v={{ $user['modified'] }}` in item avatar URLs (cache-busting). This variant is not in the component (all other usages omit it). If canvas avatar caching becomes an issue, the component can add a `cacheKey` per-user mechanism.

### elements.section-title Component
- The `tag` prop (default `'h4'`) allows overriding the heading element. Use `tag="h5"` for the one-off `<h5 class="widgettitle ...">` case found in `Setting/editCompanySettings.blade.php`. No other heading levels were found in scope.
- Conditional icon patterns in canvas files (`@if(!empty($canvasTypes[...]['icon']))<x-globals::elements.icon>@endif`) should be left in the slot content rather than using the `icon` prop — the `icon` prop is best for static, unconditional icons. Dynamic/conditional icons go in the slot.
- Pass dynamic border colors with `:borderColor="$expression"` (bound prop syntax), e.g. `:borderColor="$statusRow['class']"` in `showKanban.blade.php`. The component generates `title-border-{color}` automatically.
- `variant="plain"` is the correct choice for `<h4 class="widget widgettitle">` (the `widget` + `widgettitle` combination found on del* pages and Ideas). There is no `widget` prop — extra classes like `widget` go via `$attributes` merge, but in practice `widget` on an `<h4>` was a legacy error and was dropped on conversion.
- Extra Tailwind classes (`tw:pb-0`, `tw:mt-5`, `tw:mb-0`, `tw:mt-10`, `tw:block`) and semantic classes (`center`, `canvas-title-only`, `canvas-element-title-empty`) pass through cleanly via `$attributes` merge on the underlying heading element.
- Unescaped HTML in slot content (`{!! __('string') !!}`) works fine — the slot renders raw HTML unchanged.

### Structural Element CSS Audit (CRITICAL — Read Before Converting)

Before converting ANY structural HTML wrapper to a component, verify the component emits the same CSS class(es) that the existing stylesheets scope rules under. Converting to a component that emits different classes silently breaks all scoped CSS.

**Audit results — each component verified against `public/assets/css/`:**

| Component | HTML emitted | CSS classes verified | Safe to use? |
|---|---|---|---|
| `elements.card` | `tw:card tw:bg-base-100 tw:shadow-sm` (DaisyUI) | ❌ No matching CSS — breaks cascade for all `.maincontentinner` scoped rules | **DO NOT use as maincontentinner replacement** |
| `elements.table` | `table` + Bootstrap classes | ✅ `table`, `table-striped`, `table-bordered`, `dataTable` all defined | ✅ Safe |
| `elements.badge` | `badge badge-{color} badge-{size}` | ✅ All badge classes defined in `structure.css` lines 311–336 | ✅ Safe |
| `elements.accordion` | `accordionWrapper`, `accordionTitle`, `simpleAccordionContainer` | ✅ All defined in `dropdowns.css` lines 373–459 | ✅ Safe |
| `navigation.tabs` | `lt-nav-tabs` | ✅ Defined in `nav.css` lines 1307–1353 | ✅ Safe |
| `navigations.tabs` | `lt-tabs tabbedwidget` | ✅ Defined in `nav.css` + `style.default.css` | ✅ Safe |
| `layout.page-header` | `pageheader`, `pageicon`, `pagetitle` | ✅ Defined in `structure.css` line 474 + `style.default.css` | ✅ Safe |
| `actions.dropdown-menu` | `dropdown`, `dropdown-toggle`, `dropdown-menu` | ✅ Bootstrap dropdown classes in `structure.css` | ✅ Safe |
| `forms.button` | `btn btn-primary` / `btn btn-default` etc. | ✅ Bootstrap btn classes in `structure.css` | ✅ Safe |
| `forms.select` | `form-control` (standard) / `select-chip` (chip variant) | ✅ `form-control` in `structure.css`; `select-chip` in `forms.css` | ✅ Safe |
| `forms.text-input` | `form-control` | ✅ Defined in `structure.css` line 253 | ✅ Safe |
| `feedback.progress` | `emboss-progress` (class only used as hook — all styling is inline) | ⚠️ Class not in CSS files, but no existing scoped rules depend on it — standalone component | ✅ Safe (new pattern) |
| `elements.section-title` | `widgettitle` + modifier classes | ✅ `widgettitle` defined throughout CSS | ✅ Safe |

**Rule: Always grep `public/assets/css/` for the old class before converting.**
If scoped rules exist (e.g. `.maincontentinner .something { ... }`), the old class MUST be preserved either by keeping raw HTML or ensuring the component emits it.

### maincontentinner — DO NOT replace with elements.card

`<div class="maincontentinner">` is the primary content panel wrapper. It has CSS rules in:
- `structure.css:493` — background (glass), border-radius, padding, shadow, border
- `dropdowns.css` — scoped dropdown/button overrides
- `nav.css` — tab styles within content area
- `text-styles.css` — subtitle styles
- `tables.css` — table overrides
- `overwrites.css` — dropdown noBg overrides
- `print.css` — print layout

Always use `<div class="maincontentinner">` directly. Never replace with `elements.card`.

The `elements.card` component uses DaisyUI Tailwind classes (`tw:card`, `tw:bg-base-100`, `tw:shadow-sm`) — a completely different design system. It is only appropriate for **new UI elements** with no existing CSS cascade dependencies.

### accordion — Unconvertible Patterns

The `elements.accordion` component requires the title and content as named slots in a single tag invocation. Several accordion patterns in the codebase **cannot be converted** without logic refactoring:

1. **Split loop pattern** (`Tickets/showAll`, `showAllMilestones`, `showList`): The `<h5 class="accordionTitle">` and `<div class="simpleAccordionContainer">` are split by `@if`/`@php` blocks wrapping a loop. The component cannot span a conditional boundary.

2. **Non-standard IDs** (`Strategy/showBoards`): Uses `id="accordion_other"` on content div but the toggle calls `accordionToggle('other')` — the component expects `accordion_content-{id}` pattern.

3. **PHP string concatenation** (`Api/apiKey`, `Api/newAPIKey`, `Users/newUser`, `Users/editUser`): Accordion HTML built inside PHP echo strings — not convertible to Blade component.

4. **Custom inline title styles** (`Tickets/submodules/additionalFields`): `style="padding-bottom:15px; font-size:var(--font-size-l)"` on accordion titles — the component doesn't support per-title style overrides.

**Leave these as raw HTML.** Do not attempt to convert them.

### Menu — mainMenuList class fix

`menu.blade.php` had `style="display:block;"` on the main menu `<ul>`. This was changed to `class="tw:block"` (broke) then to `class="mainMenuList"` with a `:not(.mainMenuList)` CSS fix. The CSS rule `.leftmenu .nav-tabs.nav-stacked > li.dropdown ul { display: none; }` hides ALL `<ul>` children of dropdown `<li>` items. The `mainMenuList` class opts the main list out of that rule. This is the correct fix.

### Blade Compile+Lint Protocol
The correct way to verify blade files is:
1. Compile with `$blade->compileString(file_get_contents($file))` (or `$blade->compile($file)` for file-based)
2. Write the compiled PHP to a temp file
3. Run `php -l` on that temp file
Simply running `$blade->compileString()` without PHP-linting the output will miss errors — Blade compilation succeeds but the resulting PHP is broken.

**Known exemptions** (split-file component pattern — card tag straddles two `@include`d files):
- `app/Domain/Canvas/Templates/showCanvasTop.blade.php` — opens `<x-globals::elements.card>`
- `app/Domain/Canvas/Templates/showCanvasBottom.blade.php` — closes `</x-globals::elements.card>`
These will always fail per-file PHP lint. They work correctly at runtime when included together.

### Component Attribute Quote Rules (Critical)
Inside a `<x-component ...>` opening tag, the Blade compiler parses attribute values. Violations cause the entire component tag to be left as raw HTML (silently), and the closing tag produces an orphaned `endif`:

1. **Double-quoted attribute with double-quoted PHP string inside** → compiler breaks:
   - ❌ `headline="{{ __("some.key") }}"` 
   - ✅ `:headline="__('some.key')"` (use bound prop with single-quoted inner string)

2. **Single-quoted attribute with single-quoted PHP array key inside** → compiler breaks:
   - ❌ `hx-vals='{"id": "{{ $arr['key'] }}"}'`
   - ✅ `hx-vals='{"id": "{{ $arr["key"] }}"}'` (use double quotes for array key)

3. **Raw `<?= ?>` or `<?php ?>` tags inside component attribute value** → compiler breaks:
   - ❌ `value="<?= $tpl->escape($x); ?>"`
   - ✅ `:value="$tpl->escape($x)"` (use bound prop)

4. **Escaped single quotes `\'` in `{{ }}` expression** → invalid PHP output:
   - ❌ `{{ str_replace("\'", \'"\', ...) }}`
   - ✅ `{{ str_replace("'", '"', ...) }}` (no escaping needed inside `{{ }}`)

### Anonymous Component Namespace Resolution — `x-tickets::chips.*` NOT `x-tickets::components.chips.*`
- `ViewsServiceProvider.php` registers anonymous component namespaces via `anonymousComponentNamespace("tickets::components", "tickets")`. This means the prefix `tickets` already maps to the view path `tickets::components/`. Therefore:
  - ✅ Correct: `<x-tickets::chips.status-select>` → resolves to `app/Domain/Tickets/Templates/components/chips/status-select.blade.php`
  - ❌ Wrong: `<x-tickets::components.chips.status-select>` → doubles the `components` path segment and fails at runtime
- The same rule applies to ALL domain component namespaces (widgets, projects, goals, kanban, etc.) — the `components/` segment is implicit from the namespace registration.
- All 13 occurrences in showAll, showAllMilestones, showAllMilestonesOverview, showKanban, showList were fixed during the Phase 2 syntax review.

### Dashboard Domain Review (Domain-by-Domain Pass)

**Domain**: Dashboard + Widgets
**Status**: Reviewed and fixed — 3 bugs found and resolved.

#### Bug 1 — Dashboard top spacing (widgets sit under the menu)
- `.maincontent` has `margin-top: -95px` in CSS, designed to overlap the `.pageheader` gradient.
- `home.blade.php` has no pageheader, so it used `tw:mt-0` to cancel the negative margin. But this left zero breathing room between the fixed 48px top nav and the grid.
- **Fix**: Added `tw:pt-4` to `home.blade.php`'s `.maincontent` div, giving the grid top padding without affecting the negative margin compensation.

#### Bug 2 & 3 — MyToDos widget disappears on status/date update (and any save action)
- **Root cause**: Action-only methods in `MyToDos.php` (`updateStatus`, `updateDueDate`, `updateMilestone`, `toggleTaskCollapse`, `saveSorting`) returned `void` or a plain string. `Frontcontroller::executeAction()` checks `$response instanceof Response` — on void/string, it falls through to `$controllerClass->getResponse($response)` → `displayFragment($view, null)` which renders the **entire `myToDos` partial** with empty template variables (since `get()` was never called). The rendered empty/broken HTML gets swapped into wherever the HTMX call targeted, replacing the widget content with an empty-state or broken view.
- **Fix**: All action-only methods now explicitly `return $this->tpl->emptyResponse()`. Added `$this->setHTMXEvent('HTMX.ShowNotification')` so success/error toasts still fire.
- **Rule for all HxControllers**: Any method that performs a side-effect only (patch, save, toggle) MUST return `$this->tpl->emptyResponse()`. Never return `void` from an HxController action — always return a `Response`.

#### Bug 3b — Stale JS init calls in myToDos.blade.php
- `initMilestoneDropdown()` and `initStatusDropdown()` targeted old Bootstrap dropdown classes (`.milestoneDropdown`, `.statusDropdown`). These classes no longer exist — chips use SlimSelect now.
- `.maincontentinner` reference in `makeInputReadonly` fallback was wrong — widgets live inside `.widgetContent`, not `.maincontentinner`.
- **Fix**: Removed both stale init calls. Changed readonly selector to `.widgetContent`.

#### Key architecture insight — HxController response contract
```
// CORRECT — action-only (patch/save/toggle):
public function updateStatus(): Response {
    // ...patch...
    return $this->tpl->emptyResponse();   // ← always return Response
}

// CORRECT — full re-render:
public function get(): void {
    // assigns tpl vars; Frontcontroller calls getResponse() → displayFragment()
}

// WRONG — do NOT do this:
public function updateStatus() {
    // ...patch...
    // falls through to getResponse() → renders partial with empty vars → widget disappears
}
```

### ticket-card.blade.php and subtasks.blade.php — Final actions.chip Removal
- `app/Views/Templates/components/tickets/ticket-card.blade.php` used `actions.chip` for status (always) and milestone (when `$cardType == "full"`). Converted to `<x-tickets::chips.status-select :ticket="(object)$row" :statuses="$statusLabels" />` and `<x-tickets::chips.milestone-select :ticket="(object)$row" :milestones="$milestones" />`. The `(object)$row` cast is needed because the chip components expect an object with `->id`, `->status`, `->milestoneid` etc., while ticket-card receives `$row` as an array.
- `app/Views/Templates/components/tickets/subtasks.blade.php` used `actions.chip` for effort and status per subtask. Converted to `<x-tickets::chips.effort-select :ticket="(object)$subticket" :efforts="$efforts" />` and `<x-tickets::chips.status-select :ticket="(object)$subticket" :statuses="$statusLabels" />`. Removed `initEffortDropdown()` and `initStatusDropdown()` JS calls from the `<script>` block — SlimSelect auto-initializes on `.select-chip` elements.
- After this change, `x-globals::actions.chip` has ZERO remaining usages in blade templates. The component file still exists but is fully deprecated.

### Ticket Chip Components — Array vs. Object Mismatch
- The chip components in `app/Domain/Tickets/Templates/components/chips/` use **object** property access (`$ticket->id`, `$ticket->status`, etc.).
- The list/kanban views loop over `$row` which is an **array** (`$row['id']`, `$row['status']`).
- The correct approach is to cast when calling: `:ticket="(object)$row"`. PHP's `(object)` cast converts an associative array to a stdClass with matching properties.
- `showAllMilestonesOverview.blade.php` is the exception — its `$row` loop variable is already an object (it iterates milestone model objects), so pass `:ticket="$row"` directly without the cast.
- Chip component prop names to use when calling: `status-select` → `:statuses` (not `:statusLabels`); `priority-select` → `:priorities`; `effort-select` → `:efforts`; `milestone-select` → `:milestones` (array of objects with `->id`, `->headline`, `->tags`); `sprint-select` → `:sprints` (array of objects with `->id`, `->name`, `->startDate`, `->endDate`); `type-select` → `:ticketTypes`.
- The JS init calls (`initStatusDropdown()`, `initMilestoneDropdown()`, etc.) target `.statusDropdown .dropdown-menu a` — the OLD Bootstrap dropdown pattern. They become no-ops when the old chips are replaced with `forms.select variant="chip"`. Leave them in the JS block for now; they are harmless and will be cleaned up as part of a separate JS refactor.

### Tom Select Migration (Phase 3 — COMPLETE)
- Tom Select v2.5.2 installed via npm (`npm install tom-select`).
- `dropdownBridge.js` has 4 registrations: chip selects (`select.select-chip`), standard selects (`select.tomselect`), filter-bar selects (`.filterBar select`), tag inputs (`input.tag-input`). Each sets `el._tomSelect` on the element.
- Chip selects use `dropdownParent: 'body'` (escapes `overflow:auto` containers), `controlInput: null` (no search input), and `render.option`/`render.item` reading `data-chip-html` from `<option>` elements.
- `jQuery.fn.chosen` shim delegates to TomSelect. `liszt:updated`/`chosen:updated` event shim calls `tomSelect.sync()`.
- Standard selects use sentinel `data-ts-init`; chip selects use `data-chip-init`.
- **TomSelect DOM structure** (important for tests/CSS): `.ts-wrapper` wraps the original element; `.ts-control` is the clickable trigger; `.ts-dropdown` is the dropdown panel; `.ts-option` are items in the dropdown; `.ts-input` is the text input inside `.ts-control` for tag inputs.
- `entry-frameworks.js`: removed `jquery.tagsinput.min.js` import and associated pre-declared globals (`autocomplete_options`, `attrname`, `i`, `str`).
- **Acceptance test selector mapping**: `.chosen-single` → `.ts-control`; `.chosen-drop` → `.ts-dropdown`; `.chosen-results .active-result` → `.ts-dropdown .ts-option`; `.tagsinput` (jQuery TagsInput container) → `.ts-wrapper .ts-control`.
- **Wiki tags onChange**: `Wiki/show.blade.php` uses `setInterval` to wait for `el._tomSelect` to be set by componentRegistry, then wires `.on('change', ...)` to call `saveField('tags', value, ...)`. No `jQuery.fn.tagsInput` call needed.
- **Deleted files**: `slimselect.js`, `slimselect.min.js`, `jquery.tagsinput.min.js`, `slimselect.min.css`, `slimselect.leantime.css`, `jquery.tagsinput.css`, `chosen-sprite-light.png`, `chosen-sprite-light@2x.png`.

### Chip Badge — label-* Colour Classes Must Be Scoped to .chip-badge
- `todoItem.blade.php` and ticket chip components generate `chip-badge label-info`, `chip-badge label-warning`, `chip-badge label-success`, `chip-badge label-default`, `chip-badge label-important`. These Bootstrap `label-*` classes only have colour rules inside `.ticketDropdown a.label-*` context (`dropdowns.css`) — there is no standalone `.label-info { background: ... }` rule. Without scoped chip rules, "New" status appears white-on-white.
- Fix: Added `.chip-badge.label-info`, `.chip-badge.label-warning`, `.chip-badge.label-success`, `.chip-badge.label-default`, `.chip-badge.label-important`, `.chip-badge.label-danger`, `.chip-badge.label-primary` rules to `forms.css` (in the chip-badge colour section). These mirror the semantic token values from `dropdowns.css`.
- **Mapping used**: `label-info` → `--dark-blue`; `label-success` → `--green`; `label-warning` → `--yellow`; `label-important` → `--red`; `label-danger` → `--dark-red`; `label-default` → `--grey`; `label-primary` → `--dark-blue`.

### Chip Select — Caret and Hover
- A chip with no affordance looks like a static badge. Two CSS changes in `tom-select.css` make it feel interactive:
  - **Caret**: `.ts-wrapper.select-chip .ts-control::after` renders a small CSS border-triangle (▾). The previous rule had `display: none !important` — replaced with actual caret CSS. The `.open` variant flips it up (▴).
  - **Hover**: `.ts-wrapper.select-chip .ts-control:hover` applies `opacity: 0.85; filter: brightness(0.93)` — a subtle darkening that works across any badge colour without hardcoding values.

### SetCacheHeaders Middleware — HTMX Bypass
- `app/Core/Middleware/SetCacheHeaders.php` line 86 called `$response->isNotModified($request)` unconditionally. When the browser has a cached ETag for a widget partial, it returns `304 Not Modified`, and the browser serves stale HTML (e.g. MyToDos showing the old status after a save).
- Fix: Added `if (! $request->headers->has('HX-Request')) { $response->isNotModified($request); }`. HTMX sets `HX-Request: true` on all partial fetches. Bypassing ETag negotiation for these requests ensures HTMX always receives fresh content.
- This is the root-cause fix for the MyToDos stale reload bug. The `$this->tpl->setHTMXEvent(HtmxTicketEvents::UPDATE->value)` added to `updateStatus()` is still correct behaviour (it triggers a re-fetch after save), but it would have been neutralised by the 304 response without this fix.

### Files Domain — cancelLink Button display:none Must Stay Inline
- The "Cancel" button in file upload forms uses `id="cancelLink"` and `jQuery("#cancelLink").show()/.hide()` to toggle visibility. The `style="display:none;"` must remain inline — using `tw:hidden` would break the jQuery show/hide behavior.
- Image thumbnails in file lists use `style="max-height:50px; max-width:70px;"` — these can safely be converted to `tw:max-h-[50px] tw:max-w-[70px]` since they are static CSS constraints, not dynamic values.
- JS-built HTML strings inside `uppy.on('upload-success', ...)` callbacks keep raw dropdown HTML with `style="float:right;"` — these are JS strings, not Blade templates, so they cannot use components. Leave them as-is.

---

## Domain Component Candidates (Phase 2)

*Worker agents: add patterns here with file locations. I (orchestrator) will decide which to build.*

| Pattern Name | Suggested Component Tag | Example Locations | Occurrences | Notes |
|---|---|---|---|---|
| User assignment dropdown | `<x-globals::actions.user-select>` | Tickets/showAll, Ideas/showBoards, Ideas/advancedBoards, Canvas/element | 11+ | ✅ Done — Component created; converted in showAll, showAllMilestones, showAllMilestonesOverview, showKanban, Ideas/showBoards, Ideas/advancedBoards, Canvas/element, goals/goal-card |
| Canvas status/relates dropdown | `<x-canvas::chips.status-select>` (new) | Canvas/element.blade.php | 2 | `.statusDropdown` and `.relatesDropdown` Bootstrap dropdowns wired via `canvasController.initStatusDropdown/initRelatesDropdown()` with `data-value` / `data-label` attributes. Cannot use standard `actions.dropdown-menu` — requires full JS refactor or dedicated canvas chip component. |
| Section/widget title | `<x-globals::elements.section-title>` | Almost everywhere | 20+ | ✅ Done — Component created; all 64 `app/Domain/` blade files converted (~140 total occurrences) |
| Filter bar toolbar | `<x-globals::layout.filter-bar>` | Tickets, Timesheets | 5+ | Flex row with filter controls |
| Inline editable field | TBD | Tickets showAll, showKanban | 15+ | `secretInput` class + jQuery handler |
| User team card (project) | `<x-globals::elements.user-card>` | Projects/showProject team tab | 10+ | Checkbox + avatar + name + role select pattern |
| Gantt chart container | TBD | Projects/submodules/tickets | 1 | `jQuery("#ganttChart").ganttView(...)` — may need dedicated component with data passing |
| File upload / media list | TBD | Clients/showClient, Files domain | 3+ | `fileupload` Bootstrap plugin + `mediamgr_category`/`mediamgr_content` pattern |
| Quick-add task form | `<x-widgets::quick-add-form>` | Widgets/partials/myToDos.blade.php | 3 | Repeated pattern: text-input + projectId select + hidden fields + save/cancel buttons. Appears for emptyGroup + per-group + subtask forms. |
| Widget slot actions | `<x-widgets::slot-actions>` | Widgets/partials/calendar.blade.php, myToDos.blade.php | 3+ | `<div class="widget-slot-actions">` pattern that gets promoted to stickyHeader by JS |
| Wiki article properties | `<x-wiki::article-property>` | Wiki/Templates/show.blade.php | 5 | Status/parent/milestone/author/last-saved property rows in right panel — all share same `wiki-property-row` wrapper, label, value structure |
| Wiki status chip | `<x-wiki::chips.status-select>` or `forms.select variant="chip"` | Wiki/Templates/show.blade.php | 1 | Status dropdown in wiki properties panel — should be converted to a chip select with HTMX save |
| Comment thread | `<x-comments::thread>` | Comments/Templates/submodules/generalComment.blade.php | 1 | Full comment thread rendering: avatar + name + date + text + reply/delete links + reactions. Complex nested structure. |
| Comment reaction bar | `<x-comments::reactions>` | Comments/Templates/partials/reactions.blade.php | 1 | Already a partial, could become a proper component |
| Onboarding step progress | `<x-globals::layout.onboarding-progress>` | Auth/Templates/partials/onboardingProgress.blade.php | 1 | Step wizard with percentage progress bar + named milestone dots. Currently a partial — could become a reusable wizard progress component |
| Profile image/box | `<x-globals::elements.user-profile>` | Users/Templates/components/profile-box.blade.php, profile-image.blade.php | 3+ | Profile picture + name pattern used in comments/mentions — already a domain component, could be promoted to globals |

---

## Progress Tracker

| Domain | Status | Files Done | Total Files | Key Issues Found |
|---|---|---|---|---|
| **PHASE 0** | | | | |
| Namespace Fixup (x-global:: → x-globals::) | ✅ Done | 188 | 188 | sed replaced all closing + opening tags |
| Content Tabs Component Update | ✅ Done | 1 | 1 | navigations/tabs.blade.php updated to data-tabs pattern |
| `actions.chip` Migration → `forms.select variant="chip"` | ✅ Done | 4 | 4 | Deprecated component fully migrated. Last 2 usages were in shared `Views/Templates/components/tickets/ticket-card.blade.php` (status + milestone) and `subtasks.blade.php` (effort + status) — converted to `x-tickets::chips.status-select`, `x-tickets::chips.milestone-select`, `x-tickets::chips.effort-select`. Old JS `initEffortDropdown()`/`initStatusDropdown()` calls removed from subtasks.blade.php (SlimSelect auto-initializes). |
| **PHASE 1** | | | | |
| Tickets | ✅ Done | 29 | 29 | actions.chip → forms.select chip (×16); pageheader → page-header (×4); maincontentinner kept as div (reverted from card); tables → elements.table (×4); inline styles → Tailwind; ticketTable/ticketRows/stdform removed; raw accordions left unconverted (split-loop pattern, see Learnings) |
| Widgets | ✅ Done | 9 | 9 | projectBox removed; actions.chip → forms.select chip; display:none kept inline for JS; CSS vars kept inline; ticketBox on generic divs removed |
| Dashboard | ✅ Done | 3 | 3 | maincontentinner kept as div (reverted from card); actions.chip × 3 → forms.select chip with HTMX patch endpoint; makeInputReadonly selector updated |
| Projects | ✅ Done | 9 | 9 | projectTabs → navigations.tabs; stdform removed; inline styles → Tailwind; maincontentinner kept as div (reverted) |
| Clients | ✅ Done | 5 | 5 | clientTabs → navigations.tabs; stdform removed; tables → elements.table; pageheader @dispatchEvent deduplication; maincontentinner kept as div (reverted) |
| Timesheets | ✅ Done | 7 | 7 | pageheader → page-header (×3); maincontentinner kept as div (reverted from card); tables → elements.table (×2) keeping timesheetTable class for JS/CSS; stopwatch dropdown → dropdown-menu component; stdform/stdformbutton removed; inline styles → Tailwind; badges → elements.badge; timesheetTable/timesheetRow kept (active JS+CSS selectors) |
| Wiki | ✅ Done | 6 | 6 | skip templates.blade.php; pageheader → page-header with slot; display:none kept inline (JS-controlled); tiptapComplex/tiptapSimple textareas left unconverted; wiki status/parent/milestone dropdowns left (complex JS) |
| Comments | ✅ Done | 5 | 5 | tiptapSimple textareas left; reaction buttons left (domain-specific CSS classes); btn btn-success/btn-primary → component; inline flex styles → Tailwind |
| Users | ✅ Done | 8 | 8 | pageheader → page-header (×4); maincontentinner kept as div (reverted from card); stdform removed; raw inputs → text-input; table → elements.table; inline styles → Tailwind; style="width:220px" → tw:w-56 |
| Auth | ✅ Done | 10 | 10 | pageheader → page-header + @dispatchEvent deduplication (login/resetPw); raw inputs → text-input; input[type=submit] → button; OIDC link → button element="a"; inline styles → Tailwind; progress bar → feedback.progress |
| TwoFA | ✅ Done | 2 | 2 | pageheader → page-header (×2); maincontentinner kept as div (reverted from card); stdform removed (×2) |
| Calendar | ✅ Done | 11 | 11 | pageheader → page-header (×2); maincontentinner kept as div (reverted from card); table → elements.table (×1); stdformbutton removed; inline styles → Tailwind; simpleColorPicker inputs kept raw (JS widget); FC nav buttons → forms.button with custom class for JS selector; CSS var inline styles kept for plugin-injected action buttons |
| Connector | ✅ Done | 8 | 8 | pageheader → page-header (×8); maincontentinner kept as div (reverted from card); table → elements.table (×2); inline styles → Tailwind; buttons → forms.button with correct contentRole/state/element |
| Goalcanvas | ✅ Done | 8 | 8 | pageheader → page-header with slot (×2); maincontentinner kept as div (reverted from card); date inputs → forms.date; inline styles → Tailwind; button type= → contentRole=/state=; makeInputReadonly → .maincontent |
| Canvas | ✅ Done | 10 | 10 | pageheader → page-header with slot (×1); maincontentinner kept as div (reverted from card, split across Top/Bottom files); boardDialog inline styles → Tailwind; tiptapSimple textareas left unconverted; statusDropdown/relatesDropdown/userDropdown kept (active JS class selectors); button type= → contentRole=/state=; makeInputReadonly → .maincontent |
| Strategy | ✅ Done | 1 | 1 | pageheader → page-header (×1); maincontentinner kept as div (reverted from card); inline styles → Tailwind; raw accordion left unconverted (non-standard IDs) |
| Ideas | ✅ Done | 6 | 6 | pageheader → page-header (×2, complex slot content); maincontentinner kept as div (reverted from card); actions.chip → forms.select chip with hx-patch to /api/ideas (×1); ticketDropdown removed; makeInputReadonly → .maincontent; inline styles → Tailwind |
| Sprints | ✅ Done | 2 | 2 | button type= → contentRole=/state= standardized (×4); sprintModal kept (jQuery modal hook) |
| Files | ✅ Done | 3 | 3 | pageheader → page-header (×1); maincontentinner kept as div (reverted from card); display:none kept inline for jQuery .show()/.hide() on cancelLink; inline styles on images → Tailwind tw:max-h/tw:max-w |
| Plugins | ✅ Done | 10 | 10 | maincontentinner kept as div (reverted from card); plugintabs navigation.tabs conversion; plugin card inline styles → Tailwind arbitrary-value; buttons type= → contentRole=; links → forms.button element="a"; text-input for license key field |
| Menu | ✅ Done | 14 | 14 | display:block → mainMenuList class + CSS :not() fix (see Learnings); notification badge → elements.badge; projectSelector border inline → tw: arbitrary CSS var |
| Notifications | ✅ Done | 2 | 2 | latestNews border-bottom inline → tw: arbitrary CSS var |
| Cpcanvas | ✅ Done | 1 | 5 | 4 stubs (@include passthrough); showCanvas.blade.php has layout content with canvas grid only — no components to convert |
| Dbmcanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php had padding-top/padding-bottom inline styles → tw:pt-0/tw:pb-0 |
| Eacanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php layout only — no components needed |
| Emcanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php uses elements.icon — already correct |
| Insightscanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php layout only |
| Lbmcanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php layout only |
| Leancanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php layout only |
| Minempathycanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php layout only |
| Obmcanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php had padding-top:0px inline styles → tw:pt-0 |
| Retroscanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php layout only |
| Riskscanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php layout only |
| Sbcanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php had padding-top:10px → tw:pt-2; uses elements.icon already |
| Smcanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php layout only |
| Sqcanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php layout only |
| Swotcanvas | ✅ Done | 1 | 5 | 4 stubs; showCanvas.blade.php had stray border-radius inline style removed |
| Valuecanvas | ✅ Done | 2 | 5 | 3 stubs; showCanvas.blade.php layout only; canvasDialog.blade.php had full dialog content — width:900px → min-w-[900px]; style="width:100/50%" → tw:w-full/tw:w-1/2; button type= → contentRole/state; tiptapSimple textareas not converted (per rules); display:none kept inline (JS-controlled) |
| Errors | ✅ Done | 4 | 4 | All 4 error files: button type= → element="a" + contentRole=; no other changes needed (already use components) |
| Api | ✅ Done | 3 | 3 | apiKey.blade.php: min-width inline → tw:min-w-[700px]; stdform removed; raw text-input → forms.text-input; stdformbutton → bare p; button submit type= → :submit contentRole=. delKey.blade.php: pageheader → page-header; maincontentinner → card; button type= → state/contentRole. newAPIKey.blade.php: same patterns; style="width:100%" → tw:w-full |
| Setting | ✅ Done | 2 | 2 | editBoxDialog.blade.php: button type= → contentRole=. editCompanySettings.blade.php: pageheader → page-header; maincontentinner → card; companyTabs jQuery → data-tabs; style="width:220px" → tw:w-56; style="margin-left:5px" → tw:ml-1; class="hidden" → tw:hidden; class="stdformbutton" removed; button type= → contentRole=; dropdown-menu li/a → dropdown-item; reset logo link → button element="a" |
| Reports | ✅ Done | 1 | 1 | pageheader → page-header; maincontentinner → card; progress bar → feedback.progress; style="text-align:right" → tw:text-right; button type/size → contentRole/scale; chart canvas height/width kept inline (Chart.js requires fixed dimensions) |
| Gamecenter | ✅ Done | 1 | 1 | No changes needed — domain-specific game CSS + canvas element only |
| CsvImport | ✅ Done | 1 | 1 | style="width:100%" → tw:w-full; style="margin-top:5px" → tw:mt-1; style="margin:auto; display:inline-block" → tw:mx-auto tw:inline-block |
| Install | ✅ Done | 2 | 2 | pageheader → page-header (auth layout, no icon); regcontent kept as-is (auth layout per rules); button submit type= → :submit contentRole= |
| Help | ✅ Done | 1 | 159 | support.blade.php: button link/type= → element="a" href contentRole=; button size="lg" → scale="l"; CSS variable inline styles kept (var(--main-titles-color) etc.); style="width:1190px" kept inline (non-standard value) |
| **PHASE 2** | | | | |
| Wire up ticket chip components to list/kanban views | ✅ Done | 5 | 5 | showAll, showAllMilestones, showAllMilestonesOverview, showKanban, showList — all inline chip @php blocks replaced with `<x-tickets::chips.*>` components |
| Syntax Review — Full compile+PHP lint | ✅ Done | 315 | 317 | 315/317 files clean (2 exempt: showCanvasTop/Bottom split-file card). Fixed 9 files: headMenu (single-quote in component attr), canvasHelper (double-quote in component attr), stopwatch (array key quote), userInvite (raw PHP tag in component attr), showCanvasBottom/Top (split-file card — exempt), Files/showAll + browse (escaped quotes in `{{ }}`), showList (missing `</x-globals::elements.card>`), 5 ticket list files (`x-tickets::components.chips.*` → `x-tickets::chips.*`). |
| Domain Component Extraction | Not Started | - | - | Pending Phase 1 completion |
| **DOMAIN REVIEW PASS** | | | | |
| Dashboard + Widgets | ✅ Done | 3 bugs fixed | - | (1) home.blade.php top padding tw:pt-4 added; (2) updateStatus/updateDueDate/updateMilestone/toggleTaskCollapse/saveSorting all return emptyResponse() — prevents widget disappear on save; (3) removed stale initMilestoneDropdown/initStatusDropdown JS calls, fixed makeInputReadonly selector to .widgetContent |

---

## JavaScript Architecture Plan

Last updated: Phase 3 prep — componentRegistry enhancement, file restructure, HTMX event pipeline fixes.

### Four Server Patterns

| Pattern | Example | HTML Source | JS Needs |
|---------|---------|-------------|----------|
| **1. Primary Page** | `tickets/showKanban` | Full page with scaffolding + layout | Page controller init, domain-specific JS |
| **2. HTMX Components** | `hx-get="/hx/widgets/myToDos/get"` | Fragment swapped into existing page | Component init/reinit, lifecycle management |
| **3. Modals/Partials** | `#/tickets/showTicket/53` | Self-contained content loaded into modal overlay | Component init inside modal, cleanup on close |
| **4. JSON-RPC API** | `leantime.rpc.tickets.tickets.getTicket` | JSON data, no HTML | JS consumes data, updates DOM or component state |

### Component Registry (`componentRegistry.js`, renamed from `componentInitializer.js`)

Central component lifecycle manager. Components register a selector + init/destroy functions. The registry handles:

- **Auto-init**: On `DOMContentLoaded`, `htmx:afterSettle`, and manual `init(container)` calls
- **Auto-destroy**: On `htmx:beforeSwap` (only when `detail.shouldSwap === true`)
- **Instance tracking**: Each initialized element gets `el.__ltComponent = { type, state }` — query what's alive
- **State persistence hooks**: Registry accepts `stateKey` option; passes previous state to init function on re-init. Built into API but not actively used yet — ready for ticket card morphing.
- **Cross-domain safe**: Selector-based, not domain-based. A `select.select-chip` in a ticket card works whether rendered inside tickets, goals, or a modal.

### HTMX Event Pipeline

**Client side (`entry-htmx.js`):**
- Custom innerHTML swap handler intercepts `htmx:beforeSwap` for innerHTML swaps (HTMX 2.0.8 bug workaround)
- After manual swap: dispatches `htmx:afterSettle` + `htmx:afterSwap` to restore lifecycle events
- `componentRegistry` `htmx:beforeSwap` handler only destroys when `detail.shouldSwap === true` (prevents destroying freshly-init'd components during our custom swap)

**Server side (PHP):**
- `$this->tpl->setHTMXEvent()` is the ONLY way to set `HX-Trigger` events. Controller-level `$this->setHTMXEvent()` is **deprecated** (silently loses events when used with `emptyResponse()` or when Template headers also set `HX-Trigger`).
- `getResponse()` merges controller + template headers (bugfix: uses `array_merge_recursive` instead of sequential overwrite)
- `setNotification()` auto-adds `HTMX.ShowNotification` to template headers
- Event names should use enum constants (e.g., `HtmxTicketEvents::UPDATE->value`)

### HTMX `hx-select` Inheritance — Widget Scope Pitfall
- Adding `hx-select="#yourToDoContainer"` to the MyToDos container (or child controls) causes all descendant HTMX elements to inherit that selector.
- Timer component self-refresh responses (`/hx/timesheets/timer/get-status/...`) return only a timer node (`<li>`/`<div>`), so inherited `hx-select="#yourToDoContainer"` filters the response to nothing and outerHTML swaps remove the timer element.
- Correct fix is to prevent global `hx-select` inheritance at layout level (`hx-disinherit="hx-select"` on `main.primaryContent`) and avoid local broad `hx-select` on widget containers unless the response actually contains that selector.
- This is why timer buttons/links appeared to "disappear" despite valid server HTML and `shouldSwap: true`.

### Controller Architecture (No Change Needed)

| Layer | Purpose | Example |
|-------|---------|---------|
| **Controllers/** | Full pages + modals | `ShowTicket` loads everything for the ticket modal |
| **Hxcontrollers/** | Components + mutations | `TicketCard` renders one card; `Ticket` patches a field |
| **Composers/** | Auto-inject data into layout views | `Header` provides theme data to every page |

HxControllers already ARE component controllers. No separate `ComponentController` type needed.

### HxController Response Contract

```php
// Pattern A: Render a view (most common — return void, getResponse() handles it)
public function get(): void {
    $this->tpl->assign('data', $this->service->getData());
}

// Pattern B: Action only (no HTML — return emptyResponse())
public function save(): Response {
    $this->service->save($data);
    $this->tpl->setNotification('Saved', 'success');
    $this->tpl->setHTMXEvent(HtmxTicketEvents::UPDATE->value);
    return $this->tpl->emptyResponse();
}

// NEVER: return void from an action method — causes full partial render with empty vars
```

### Inline Script Migration Strategy

**Goal**: Eliminate all inline `<script>` tags in templates. Use `componentRegistry` registrations instead.

**Pattern**: Templates output semantic HTML with `data-*` attributes. Registry handles init.

```html
<!-- Before (inline script): -->
<input class="datepicker" id="duedate">
<script>jQuery(document).ready(function(){ jQuery('.datepicker').datepicker({...}) })</script>

<!-- After (data-driven): -->
<input class="datepicker" data-format="Y-m-d" data-enable-time="false">
<!-- componentRegistry auto-inits flatpickr based on selector -->
```

For PHP data injection (calendar events, charts):
```html
<div data-component="fullcalendar" data-config='@json($calendarConfig)'></div>
```

### File Structure

```
public/assets/
├── css/
│   ├── entries/          ← Vite CSS entry points (moved from resources/css/)
│   ├── components/       ← Existing component CSS
│   └── libs/             ← Third-party CSS
├── js/
│   ├── entries/          ← Vite JS entry points (moved from resources/js/)
│   ├── app/
│   │   ├── app.js        ← Core app namespace
│   │   └── core/
│   │       ├── componentRegistry.js  ← RENAMED from componentInitializer.js
│   │       ├── dropdownBridge.js     ← Chip select registration
│   │       ├── datePickers.js        ← FUTURE: flatpickr registration
│   │       ├── tooltips.js           ← FUTURE: tippy registration
│   │       ├── editors.js            ← FUTURE: tiptap registration
│   │       └── tiptap/               ← Existing tiptap code
│   └── libs/             ← Third-party JS
```

### Phase Tracker

| Phase | Status | Description |
|-------|--------|-------------|
| Phase 1: Foundation | ✅ Done | componentRegistry with instance tracking + state hooks; PHP getResponse() merge fix; setHTMXEvent deprecated; shouldSwap guard on destroy |
| Phase 2: File Restructure | ✅ Done | Deleted 5 stale files + 36MB build/; moved 17 entry files from resources/ → public/assets/entries/; updated vite.config.js + Blade @vite() calls; resources/ directory removed |
| Phase 3: Component Registrations | ✅ Done | Tom Select v2.5.2 replaces SlimSelect + Chosen.js + jQuery TagsInput. 4 registrations in dropdownBridge.js (chip selects, standard selects, filter-bar selects, tag inputs). jQuery.fn.chosen shim + liszt:updated shim included. All vendor CSS/JS files deleted. Build clean. |
| Phase 4: Inline Script Migration | Pending | Migrate HTMX partials first, then shared components, then pages |

### Bugs Found and Fixed

| Bug | Status | Description |
|-----|--------|-------------|
| `getResponse()` header overwrite | Fixed | Template headers overwrote controller headers. Now uses `array_merge_recursive`. |
| `$this->setHTMXEvent()` events lost | Fixed | 8 custom events silently dropped in production. Migrated to `$this->tpl->setHTMXEvent()`. |
| `componentInitializer` `htmx:beforeSwap` destroying fresh chips | Fixed | Destroy handler now checks `detail.shouldSwap === true` before destroying. |
| HTMX innerHTML swap kills `htmx:afterSettle` | Fixed | Custom handler now dispatches `htmx:afterSettle` + `htmx:afterSwap` after manual swap. |
| Dashboard `.maincontent` top margin | Fixed | Added `.maincontent.no-pageheader { margin-top: 0 }` CSS class. |
| SlimSelect `addToBody` for overflow clipping | Fixed | Chip select uses `addToBody: true` to escape `overflow:auto` containers. |
| Chip badge white-on-white (label-info etc.) | Fixed | `.chip-badge.label-*` colour rules added to `forms.css`; `label-info` was only scoped to `.ticketDropdown` context. |
| `SetCacheHeaders` returns 304 for HTMX widget GETs | Fixed | Added `HX-Request` header check — ETag/304 negotiation skipped for all HTMX requests. |
| Chip select has no interactivity affordance | Fixed | Added caret (`::after` border-triangle) and hover `brightness(0.93)` to `.ts-wrapper.select-chip .ts-control` in `tom-select.css`. |
