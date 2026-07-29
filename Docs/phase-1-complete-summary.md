# Phase 1: Modal System & Navigation — Complete Summary

## Claude Code Execution Prompt

```
Read these documents in order before starting Phase 1:
1. Docs/phase-0-complete-summary.md — Component inventory, frontend libraries, design system guidelines
2. Docs/phase-0-requirements.md — Architecture constraints, mobile API surface, event system
3. Docs/phase-1-complete-summary.md — THIS FILE: modal inventory, navigation patterns, canvas domain map
4. Docs/phase-1-requirements.md — Step-by-step execution instructions with validation checklists
5. Docs/leantime-4.0-dev-unique-changes.md — 4.0-dev component reference designs
6. Docs/leantime-mobile-backend-compatibility.md — API surface (DO NOT BREAK)

Phase 1 Tasks (execute in order):
1.1 — Replace modals.js + nyroModal with native <dialog> modal system
1.2 — Migrate hash-based modal triggers (href="#/...") to HTMX-powered <dialog>
1.3 — Migrate formModal-class templates to Blade modal component
1.4 — Add hx-boost to main navigation for SPA-like transitions
1.5 — Convert high-priority core domain modals (Tickets, Calendar, Goals, Projects)
1.6 — Establish canvas domain modal pattern (1 template serves 19 canvas domains)
1.7 — Plugin modal compatibility layer

CRITICAL CONSTRAINTS:
- DO NOT change any service method signatures, field names, or return types
- DO NOT break the JSON-RPC API surface (mobile app depends on it)
- DO NOT remove nyroModal JS until ALL modal triggers are migrated
- DO NOT change URL routing — modal endpoints must still respond to direct HTTP requests
- Plugins that call leantime.modals.closeModal() need a shim until they're updated
- The Frontcontroller already has is-modal header detection — USE this, don't reinvent
- Canvas domains share a SINGLE template system (Canvas/Templates/) — fix once, fix 19 domains
```

---

## 1. Current Modal Architecture

### How Modals Work Today

The current modal system is a **hash-based URL + jQuery nyroModal** pattern:

1. User clicks `<a href="#/tickets/showTicket/123">` 
2. `window.addEventListener("hashchange")` fires in `modals.js`
3. `leantime.modals.openModal()` parses the hash, builds a URL from it
4. `jQuery.nmManual(url, options)` opens nyroModal — an iframe/AJAX modal
5. nyroModal fetches the full controller response and renders it in an overlay
6. The Frontcontroller detects `is-modal` header and adjusts layout (no chrome)
7. On close, `beforeClose` callback either runs `globalModalCallback` or `location.reload()`

### Key Files

| File | Purpose | Lines |
|------|---------|-------|
| `public/assets/js/app/core/modals.js` | Global modal open/close/hashchange listener | 117 |
| `public/assets/js/libs/jquery.nyroModal/js/jquery.nyroModal.custom.js` | jQuery nyroModal library | ~800 |
| `app/Core/Controller/Frontcontroller.php` | `is-modal` header detection, `redirect()` modal handling | key lines: 200-202, 395-430 |
| `app/Core/UI/Template.php` | `closeModal()` → sets HTMX event header `HTMX.closemodal` | line 205 |
| `resources/js/compiled-global-component.js` | Imports nyroModal into build bundle | line 21 |

### modals.js Analysis

```javascript
// Core behavior:
leantime.modals = (function () {
    openModal()      // Reads window.location.hash, calls jQuery.nmManual()
    closeModal()     // Calls jQuery.nmTop().close()
    setCustomModalCallback(fn) // Sets window.globalModalCallback for close behavior
})();

// Lifecycle hooks in nyroModal options:
beforePostSubmit → saves/destroys TinyMCE editors (DEAD CODE — TinyMCE migrated ✅)
beforeShowCont   → saves/destroys TinyMCE editors (DEAD CODE — TinyMCE migrated ✅)  
afterShowCont    → calls htmx.process() on modal content + re-init tippy tooltips
beforeClose      → pushes history state, runs globalModalCallback OR location.reload()
```

**Important:** The `afterShowCont` callback calls `window.htmx.process('.nyroModalCont')` — this means HTMX attributes inside modals are already being activated. The new system just needs to ensure htmx.process() runs on `<dialog>` content too.

### Frontcontroller Modal Detection

```php
// Frontcontroller.php line 200-202 — Routes to HxController OR regular Controller
if ($this->incomingRequest instanceof HtmxRequest &&
    $this->incomingRequest->header('is-modal') == false &&
    $this->incomingRequest->header('hx-boosted') == false) {
    $controllerType = 'Hxcontrollers';  // HTMX partial response
} else {
    $controllerType = 'Controllers';     // Full page OR modal response
}

// Frontcontroller.php line 402 — Redirect handling for modals
if (app('request')->headers->get('is-modal')) {
    Frontcontroller::redirectHtmx($url, $headers);
}
```

**This means:** When `is-modal: true` header is sent, the Frontcontroller uses regular Controllers (full response) instead of HxControllers. The new `<dialog>` modal system should send this header with `hx-headers='{"is-modal": "true"}'` to maintain the same routing.

### Template.php closeModal()

```php
public function closeModal(): void {
    $this->setHTMXEvent('HTMX.closemodal');
}
```

Controllers already call `$this->tpl->closeModal()` to signal modal close. The new system needs to listen for the `HTMX.closemodal` event.

---

## 2. Modal Trigger Inventory

### Hash-Based Triggers (`href="#/..."`)

**101 hash-based modal triggers** across core domains + **30+ in plugins**.

#### By Domain (Core — 101 triggers)

| Domain | Count | Key Modals |
|--------|-------|------------|
| Tickets | 25 | showTicket, newTicket, editMilestone, delTicket, moveTicket, delMilestone |
| Canvas (shared) | 12 | editCanvasItem, delCanvasItem, boardDialog, delCanvas |
| Goalcanvas | 14 | editCanvasItem, delCanvasItem, bigRock, delCanvas, editCanvasComment |
| Ideas | 12 | ideaDialog, boardDialog, delCanvasItem |
| Calendar | 8 | addEvent, editEvent, delEvent, export, connectCalendar, calendarSettings, editExternal, delExternalCal |
| Dashboard | 5 | showTicket, newTicket, editMilestone, newUser |
| Wiki | 6 | articleDialog, wikiModal, delWiki, delArticle |
| Timesheets | 3 | showTicket (links to ticket modal) |
| Widgets | 5 | showTicket, editMilestone, moveTicket, delete, widgetManager |
| Setting | 3 | editBoxLabel, apiKey, newApiKey |
| Projects | 1 | createnew |
| Sprints | 4 | editSprint, delSprint |
| Clients | 1 | newUser |
| Plugins | 1 | details |

#### By Domain (Plugins — 30+ triggers)

| Plugin | Count | Key Modals |
|--------|-------|------------|
| Notes | 8 | notesDialog, notebookDialog, delNote, delNotebook |
| StrategyPro | 6 | editCanvasItem, delCanvasItem, focusArea, delCanvas |
| Whiteboardscanvas | 8 | whiteboardDialog, group, delCanvas, delCanvasItem, editCanvasItem |
| Billing | 2 | changePlanModal, addCreditsModal |
| AdvancedAuth | 1 | create (token) |
| PgmPro | 1 | editBoxLabel |
| Copilot | 1 | showTicket (link) |

### formModal CSS Class Triggers

**40 template files** use `class="formModal"` to trigger nyroModal on links/buttons. These work via jQuery event delegation:

```javascript
jQuery(".formModal, .modal").nyroModal(modalOptions);
```

These are in the `afterShowCont` callback, meaning nested modals also get wired up.

### leantime.modals.closeModal() Callers

Templates/JS that explicitly call the close function:

| File | Context |
|------|---------|
| Notes/delNotebook.blade.php | Cancel button |
| Notes/delNote.blade.php | Cancel button |
| Notes/notesDialog.blade.php | Custom callback |
| Whiteboardscanvas/whiteboardGroup.blade.php | Cancel button |
| AdvancedAuth/token-created.blade.php | Done button |
| Help/helperController.js | Close helper modal (5 calls) |
| Billing/billingModals.js | Close billing modal |
| Timesheets/timesheetsController.js | Close after save |

### openModalManually() Pattern

Used in canvas JS controllers to programmatically open modals. Found in **11 canvas domain JS files** + 2 plugins. Each canvas controller has its own copy — they all do the same thing: `jQuery.nmManual(url, options)`.

---

## 3. Canvas Domain Architecture

### 19 Canvas Domains Share ONE Template System

The `app/Domain/Canvas/` domain provides **shared templates** that all canvas-type domains inherit:

```
Canvas/Templates/
├── boardDialog.php         — Create/edit board dialog
├── canvasComment.inc.php   — Comment dialog
├── canvasDialog.inc.php    — Main item edit dialog (CORE MODAL)
├── element.inc.php         — Single canvas element (has modal triggers)
├── modals.inc.php          — Modal includes
├── showCanvasTop.inc.php   — Top header (has modal triggers)
```

**Every canvas domain** (Lean, SWOT, Business Model, Empathy, etc.) either uses these directly or has minimal overrides. Only Goalcanvas and Valuecanvas have their own dialog templates.

| Canvas Domain | Has Own Dialog? | Uses Shared Templates? |
|---|---|---|
| Canvas (base) | ✅ canvasDialog.inc.php | — |
| Goalcanvas | ✅ canvasDialog.blade.php, bigRockDialog.blade.php | Partially |
| Valuecanvas | ✅ canvasDialog.tpl.php | Partially |
| Ideas | ✅ ideaDialog.tpl.php, boardDialog.php | Own implementation |
| Cpcanvas, Dbmcanvas, Eacanvas, Emcanvas, Insightscanvas, Lbmcanvas, Leancanvas, Logicmodelcanvas, Minempathycanvas, Obmcanvas, Retroscanvas, Riskscanvas, Sbcanvas, Smcanvas, Sqcanvas, Swotcanvas | ❌ | ✅ All use Canvas/Templates/ |

**This means:** Fixing the modal system in `Canvas/Templates/canvasDialog.inc.php` and the shared JS controller pattern fixes modals for **16 canvas domains at once**.

### Canvas JS Controller Pattern

Each canvas domain has its own JS controller that duplicates modal logic:

```javascript
// Pattern repeated in 16+ files:
var openModalManually = function (url) {
    jQuery.nmManual(url, modalOptions);
};
```

These need consolidation into a single canvas modal handler.

---

## 4. HxController Inventory

**45 HxControllers** exist (20 core + 25 plugin). These handle HTMX partial responses and are NOT used for modal content (modals use regular Controllers).

### Core Domain HxControllers

| Domain | Controllers | Purpose |
|--------|-------------|---------|
| Help | HelperModal | Help overlay |
| Menu | ProjectSelector | Project switcher |
| Notifications | News, NewsBadge | Notification panel |
| Plugins | Details, Marketplaceplugins | Plugin browser |
| Projects | Checklist, ProjectCard, ProjectCardProgress, ProjectHubProjects | Project dashboard widgets |
| Tickets | Milestones, Subtasks, TicketCard, TimerButton | Ticket partial updates |
| Timesheets | Stopwatch | Timer widget |
| Widgets | Calendar, MyProjects, MyToDos, Welcome | Dashboard widgets |

### Plugin HxControllers

AdvancedAuth (2), Copilot (3), Crisp (1), CustomFields (2), Implementationintentions (1), Llamadorian (2), Notes (5), Pomodoro (1), ProjectWizard (4), Reactions (2), RecurringTasks (1), ThemeBundle (1).

---

## 5. Existing Blade Components

**31 component files** already exist across the codebase:

### Global Components (`app/Views/Templates/components/`)

| Component | Status |
|-----------|--------|
| accordion.blade.php | ✅ Exists |
| actions/modal.blade.php | ✅ Exists (may need DaisyUI 5 update) |
| badge.blade.php | ✅ Exists |
| button.blade.php | ✅ Exists |
| dropdownPill.blade.php | ✅ Exists |
| emojiinput.blade.php | ✅ Exists |
| inlineLinks.blade.php | ✅ Exists |
| inlineSelect.blade.php | ✅ Exists |
| kanban/* (8 files) | ✅ Kanban sub-components |
| loader.blade.php | ✅ Exists |
| loadingText.blade.php | ✅ Exists |
| pageheader.blade.php | ✅ Exists |
| selectable.blade.php | ✅ Exists |
| tabs.blade.php + tabs/* | ✅ Tab components |
| undrawSvg.blade.php | ✅ Exists |

### Domain-Specific Components

| Domain | Components |
|--------|-----------|
| Comments | input.blade.php, reply.blade.php |
| Users | profile-box.blade.php, profile-image.blade.php |
| Widgets | moveableWidget.blade.php |

**Note:** `actions/modal.blade.php` already exists — Phase 1 should UPDATE this to use `<dialog>` + DaisyUI 5 rather than creating a new file.

---

## 6. Navigation Architecture

### Current Full-Page Navigation

Every page link is a full HTTP request. The main layout (`app/Views/Templates/layouts/app.blade.php`) includes:

```
mainwrapper
├── header (logo, burger menu)
├── leftmenu (@include menu::menu)
├── maincontent
│   ├── pageheader
│   └── maincontentinner (@yield content)
└── footer
```

### hx-boost Opportunity

The Frontcontroller already handles `hx-boosted` requests:

```php
$this->incomingRequest->header('hx-boosted') == false
```

When `hx-boost="true"` is on a navigation link, HTMX sends the request with `HX-Boosted: true` header. The Frontcontroller routes to regular Controllers (not HxControllers) — same as modals. The response replaces just the content area, keeping the navigation shell.

**Implementation:** Add `hx-boost="true"` to the `<body>` or main content wrapper. HTMX will intercept all `<a>` clicks and swap content. The layout template needs to handle boosted requests by returning only the content portion (not the full HTML shell).

### Layout Detection Needed

The app layout must detect HTMX boosted requests and return partial HTML:

```php
@if(request()->header('HX-Boosted'))
    {{-- Just the page content, no shell --}}
    @yield('content')
@else
    {{-- Full HTML page with nav, header, footer --}}
    <!DOCTYPE html>...
@endif
```

---

## 7. Migration Metrics

### Scope Summary

| Category | Count | Phase 1 Target |
|----------|-------|----------------|
| nyroModal references | 97 occurrences in 42 files | Replace ALL |
| Hash-based modal triggers | 131 (101 core + 30 plugin) | Core: all, Plugins: compatibility shim |
| formModal class triggers | 40 files | Replace ALL |
| leantime.modals.closeModal() calls | 13 files | Shim + migrate |
| Canvas domains sharing templates | 19 domains, 1 template set | Fix templates once |
| Core modal dialog templates | ~25 unique | Migrate to Blade components |
| Plugin modal dialog templates | ~10 unique | Compatibility layer |
| HxControllers (no changes needed) | 45 | Verify still work |

### Files to Create/Modify

**New files:**
- Updated `app/Views/Templates/components/actions/modal.blade.php` — `<dialog>` + DaisyUI 5
- `public/assets/js/app/core/modalManager.js` — Replacement for modals.js (hash listener + `<dialog>` bridge)
- Layout partial for hx-boost responses

**Core files to modify:**
- `app/Views/Templates/layouts/app.blade.php` — hx-boost support, `<dialog>` container
- `resources/js/compiled-global-component.js` — Remove nyroModal import, add modalManager
- Every template with `href="#/"` patterns (101 in core)
- Every template with `class="formModal"` (40 files)
- `Canvas/Templates/*.inc.php` — Shared canvas modal templates (fixes 19 domains)

---

## 8. Design System References

For modal and navigation component patterns, reference:
- DaisyUI Modal: https://daisyui.com/components/modal/
- DaisyUI Menu: https://daisyui.com/components/menu/
- DaisyUI Navbar: https://daisyui.com/components/navbar/
- HTMX hx-boost: https://htmx.org/attributes/hx-boost/
- HTMX Modals: https://htmx.org/examples/modal-custom/
- HTML `<dialog>`: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/dialog
- GitHub Primer Dialog: https://primer.style/components/dialog
