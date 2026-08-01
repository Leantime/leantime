# Phase 1: Modal System & Navigation — Requirements Document

## For Claude Code Execution

**Project:** Leantime UI Modernization Forward-Port
**Phase:** 1 of 4 — Modal System Replacement + SPA-like Navigation
**Depends on:** Phase 0 complete (Laravel 12, Vite, Tailwind 4, DaisyUI 5, HTMX 2.0.8)
**Estimated Duration:** 2 weeks
**Risk Level:** MEDIUM-HIGH (touches every domain's user-facing interaction pattern)
**Generated:** February 13, 2026

---

## Goal

Replace the jQuery nyroModal system (hash-based URLs + iframe overlays) with native `<dialog>` elements powered by DaisyUI 5 and HTMX 2.x. Add hx-boost for SPA-like navigation. The modal system is the #1 pain point — 97 references across 42 files, 131 hash-based triggers, and a global `modals.js` that couples to dead TinyMCE code.

---

## Architecture Decision: The Bridge Pattern

**Do NOT do a big-bang replacement.** Instead, build a **modalManager.js** that:

1. Listens for the same `hashchange` events as the current `modals.js`
2. Instead of opening nyroModal, fetches content via fetch() into a `<dialog>` element
3. Sends `is-modal: true` header so the Frontcontroller routes to regular Controllers
4. Handles `HTMX.closemodal` event to close the `<dialog>`
5. Runs `htmx.process()` on dialog content (same as nyroModal's `afterShowCont`)
6. Exposes `leantime.modals.openModal()` and `leantime.modals.closeModal()` API — **same interface** so plugins don't break

This means the migration is **incremental**: old hash triggers work immediately through the bridge, and individual templates can be upgraded to direct HTMX triggers over time.

---

## 1.1 Build the Modal Component + Manager

**Priority:** FIRST | **Risk:** MEDIUM | **Effort:** 4-8 hours

### Update `app/Views/Templates/components/actions/modal.blade.php`

The file already exists. Update it to use `<dialog>` + DaisyUI 5:

```php
@props([
    'id' => null,
    'size' => 'md',
    'closeable' => true,
])

@php
$sizeClass = match($size) {
    'sm' => 'max-w-lg',
    'md' => 'max-w-3xl',
    'lg' => 'max-w-5xl',
    'xl' => 'max-w-7xl',
    'ticket' => 'max-w-7xl min-h-[80vh]',
    default => 'max-w-3xl',
};
$modalId = $id ?? 'modal-' . uniqid();
@endphp

<dialog id="{{ $modalId }}" class="modal" {{ $attributes }}>
    <div class="modal-box {{ $sizeClass }}">
        @if($closeable)
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
            </form>
        @endif
        <div class="modal-content">
            {{ $slot }}
        </div>
        @isset($actions)
            <div class="modal-action">
                {{ $actions }}
            </div>
        @endisset
    </div>
    @if($closeable)
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    @endif
</dialog>
```

### Create `public/assets/js/app/core/modalManager.js`

This replaces `modals.js` with backward compatibility. Key behaviors:

- Same `leantime.modals` namespace and API
- Reads `window.location.hash`, fetches URL with `is-modal: true` header
- Renders response in a `<dialog>` element
- Sizes modal based on URL pattern (showTicket/ideaDialog → large)
- Calls `htmx.process()` on loaded content
- Calls `tippy()` for tooltips
- Listens for `HTMX.closemodal` event from server
- On close: pushes history state, runs `globalModalCallback` or `location.reload()`
- Shows DaisyUI loading spinner while content loads

### Update `resources/js/compiled-global-component.js`

Replace nyroModal import with modalManager:
```diff
- import '../../public/assets/js/libs/jquery.nyroModal/js/jquery.nyroModal.custom.js';
+ import '../../public/assets/js/app/core/modalManager.js';
```

### Validation Checklist
- [ ] Existing `href="#/tickets/showTicket/123"` links open a `<dialog>` modal
- [ ] Modal content loads and renders correctly
- [ ] HTMX attributes inside modal content are active
- [ ] `leantime.modals.closeModal()` works (plugin compatibility)
- [ ] `leantime.modals.setCustomModalCallback()` works
- [ ] Modal closes on Escape key, backdrop click, and close button
- [ ] `HTMX.closemodal` server event closes the modal
- [ ] Hash is cleaned from URL when modal closes
- [ ] Tippy tooltips work inside modal
- [ ] Ticket modal opens at large size
- [ ] Calendar modals open at default size
- [ ] No console errors


## 1.2 Add Global `<dialog>` Container to Layout

**Priority:** HIGH | **Risk:** LOW | **Effort:** 1-2 hours

Add to `app/Views/Templates/layouts/app.blade.php` before closing `</body>`:

```html
<dialog id="global-modal" class="modal">
    <div class="modal-box max-w-5xl">
        <form method="dialog">
            <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2">✕</button>
        </form>
        <div class="modal-content" id="global-modal-content"></div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>
```

### Validation Checklist
- [ ] `<dialog>` element present in page DOM
- [ ] Does not conflict with existing page layout
- [ ] modalManager.js finds and uses this element


## 1.3 Core Domain Modal Migration

**Priority:** HIGH | **Risk:** MEDIUM | **Effort:** 1-2 days

### Strategy

The bridge pattern means all existing `href="#/"` triggers work IMMEDIATELY — no template changes needed for basic functionality. This step focuses on:

1. **Removing the jQuery formModal initialization** (the `afterShowCont` callback)
2. **Testing each domain's modals** work through the bridge
3. **Fixing any templates** that make iframe/nyroModal assumptions

### Priority Testing Order (by trigger count)

| Priority | Domain | Triggers | Key Modals |
|----------|--------|----------|------------|
| 1 | Tickets | 25 | showTicket, newTicket, editMilestone, moveTicket, delTicket |
| 2 | Goalcanvas | 14 | editCanvasItem, bigRock, delCanvasItem |
| 3 | Canvas (shared) | 12 | editCanvasItem, boardDialog (fixes 16 domains) |
| 4 | Ideas | 12 | ideaDialog, boardDialog |
| 5 | Calendar | 8 | addEvent, editEvent, calendarSettings |
| 6 | Wiki | 6 | articleDialog, wikiModal |
| 7 | Dashboard | 5 | showTicket, newTicket, newUser |
| 8 | Sprints | 4 | editSprint, delSprint |
| 9 | Setting | 3 | editBoxLabel, apiKey |
| 10 | Projects | 1 | createnew |

### Template Compatibility Concerns

Some modal templates may assume they're inside nyroModal's container (`.nyroModalCont`). Search for and update:

```bash
grep -rn "nyroModalCont\|nmTop\|nmManual\|nmObj" --include="*.php" --include="*.js" --include="*.blade.php" --include="*.tpl.php" app/ resources/ | grep -v vendor | grep -v node_modules
```

Replace `.nyroModalCont` selectors with `#global-modal-content` or `.modal-content`.

### Validation Checklist
- [ ] All hash-based triggers open modals for each domain above
- [ ] Modal forms submit correctly (POST data reaches controller)
- [ ] Controller redirects work inside modals
- [ ] `$this->tpl->closeModal()` triggers dialog close
- [ ] No `.nyroModalCont` references remain in active code paths


## 1.4 Canvas Domain Modal Consolidation

**Priority:** HIGH | **Risk:** MEDIUM | **Effort:** 4-8 hours

### Why

19 canvas domains share `Canvas/Templates/` files. Each has its own JS controller with duplicated `openModalManually()`. Fix the shared templates + create one handler = fix 19 domains.

### What to Do

1. **Verify `Canvas/Templates/canvasDialog.inc.php`** works inside `<dialog>` (no iframe assumptions)
2. **Verify `Canvas/Templates/element.inc.php`** hash triggers work via bridge
3. **Verify `Canvas/Templates/showCanvasTop.inc.php`** hash triggers work
4. **Create shared `leantime.canvasModal.openModalManually()`** that delegates to hash trigger
5. **Update 16+ canvas JS controllers** to use shared handler

### Special Cases
- **Goalcanvas** — has own `canvasDialog.blade.php` and `bigRockDialog.blade.php` (already Blade)
- **Valuecanvas** — has own `canvasDialog.tpl.php`
- **Ideas** — has own `ideaDialog.tpl.php` and `boardDialog.php`

### Validation Checklist
- [ ] 3+ canvas domains tested (e.g., Lean, SWOT, Retros)
- [ ] Canvas item create/edit/delete modals work
- [ ] Board create/edit dialogs work
- [ ] Comment dialogs work
- [ ] Goal canvas special dialogs work
- [ ] Idea dialogs work


## 1.5 Ticket Domain Deep Migration

**Priority:** HIGH | **Risk:** MEDIUM | **Effort:** 4-8 hours

### showTicketModal.blade.php

Most complex modal. Contains subtask links, comments, timesheets, inline editing. Verify:

- Renders correctly inside `<dialog>` (was inside nyroModal iframe)
- Subtask `href="#/"` links open nested modals (modal-in-modal)
- Comment submission via HTMX works
- Ticket status/priority/effort inline changes work
- `is-modal: true` header ensures Frontcontroller returns full controller response

### Other Ticket Modals

| Template | Format | Action |
|----------|--------|--------|
| newTicketModal.tpl.php | .tpl.php | Verify form submission |
| milestoneDialog.tpl.php | .tpl.php | Verify save + close |
| moveTicket.tpl.php | .tpl.php | Verify project selector |
| delTicket.tpl.php | .tpl.php | Verify delete confirmation |

### Validation Checklist
- [ ] Show ticket modal opens with full content at large size
- [ ] Subtask links open nested modals
- [ ] Comment submission works
- [ ] Ticket field changes save
- [ ] Milestone dialog opens and saves
- [ ] New ticket modal creates ticket
- [ ] Move ticket works
- [ ] Delete confirmation works
- [ ] Modal close after save triggers list refresh


## 1.6 hx-boost Navigation

**Priority:** MEDIUM | **Risk:** MEDIUM | **Effort:** 4-8 hours

### Implementation

1. **Add `hx-boost` to content wrapper** in `app/Views/Templates/layouts/app.blade.php`:

```html
<div class="maincontent" 
     hx-boost="true" 
     hx-target="#maincontentinner" 
     hx-swap="innerHTML show:window:top" 
     hx-push-url="true">
```

2. **Detect boosted requests in layout:**

```php
@if(request()->header('HX-Boosted'))
    @yield('content')
@else
    <!DOCTYPE html>
    <html>
    ... full page shell ...
    @yield('content')
    ... end shell ...
    </html>
@endif
```

3. **Exclude from boost:** hash links (automatic), external links, auth links, downloads. Add `hx-boost="false"` where needed.

4. **Re-initialize page JS** after navigation:

```javascript
document.addEventListener('htmx:afterSettle', function(evt) {
    // Re-initialize domain controllers, tooltips, etc.
});
```

5. **Add loading indicator:**

```html
<div id="page-loading" class="htmx-indicator">
    <progress class="progress w-full"></progress>
</div>
```

### Frontcontroller Support

Already exists — line 202 checks `hx-boosted`. Boosted requests route to regular Controllers (full page content), not HxControllers (fragments).

### What NOT to Boost

- External links, hash links, downloads, auth/logout, links with `hx-boost="false"`

### Validation Checklist
- [ ] Page navigation doesn't full-reload
- [ ] Browser back/forward work
- [ ] Page title updates
- [ ] Left menu active state updates
- [ ] Page-specific JS initializes after navigation
- [ ] Modal links still work (not boosted)
- [ ] External links open normally
- [ ] Loading indicator shows during transitions


## 1.7 Plugin Compatibility Verification

**Priority:** MEDIUM | **Risk:** LOW | **Effort:** 2-4 hours

### Plugin Status

| Plugin | Modal Pattern | Expected Status |
|--------|--------------|-----------------|
| Notes | closeModal(), setCustomModalCallback(), hash triggers | ✅ Bridge handles |
| Billing | Direct jQuery('.nyroModal').nyroModal() | ⚠️ Needs shim or update |
| StrategyPro | openModalManually(), hash triggers | ✅ Canvas consolidation handles |
| Whiteboardscanvas | hash triggers, closeModal() | ✅ Bridge handles |
| AdvancedAuth | hash triggers, closeModal() | ✅ Bridge handles |
| Copilot | showTicket link | ✅ Bridge handles |
| Help | closeModal() (5 calls) | ✅ Bridge handles |

### Billing Plugin Shim

```javascript
// Shim for jQuery.fn.nyroModal — converts to hash trigger
if (jQuery && jQuery.fn) {
    jQuery.fn.nyroModal = function(options) {
        this.each(function() {
            jQuery(this).on('click', function(e) {
                e.preventDefault();
                var href = jQuery(this).attr('href');
                if (href && href.startsWith('#')) {
                    window.location.hash = href.substring(1);
                }
            });
        });
        return this;
    };
    jQuery.nmManual = function(url, options) {
        window.location.hash = url.replace(leantime.appUrl, '');
    };
}
```

### Validation Checklist
- [ ] Notes: notebook/note dialogs work
- [ ] AdvancedAuth: token creation modal works
- [ ] Help: helper modals work
- [ ] No plugin console errors

---

## Execution Order

```
1.1 Modal component + modalManager.js           [4-8 hours]
1.2 Global <dialog> in layout                    [1-2 hours]
1.3 Core domain modal testing + fixes            [1-2 days]
1.4 Canvas domain consolidation                  [4-8 hours]
1.5 Ticket domain deep migration                 [4-8 hours]
1.6 hx-boost navigation                          [4-8 hours]
1.7 Plugin compatibility verification            [2-4 hours]
```
**Total: ~2 weeks**

---

## Phase 1 Deliverables

- [ ] modalManager.js replaces modals.js (same API, `<dialog>` backend)
- [ ] All 131 hash-based modal triggers work through bridge
- [ ] `<dialog>` modals render with DaisyUI 5 styling
- [ ] Modals accessible (Escape, focus trap, aria attributes)
- [ ] Nested modals work
- [ ] Canvas modal code consolidated (16 files → 1 shared handler)
- [ ] hx-boost SPA-like navigation working
- [ ] All plugins' modal interactions verified
- [ ] Mobile API (JSON-RPC) completely unaffected
- [ ] No TinyMCE references in modal code
- [ ] nyroModal JS still in codebase but unused (remove after Phase 2 verification)

---

## Risk Register

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Modal content doesn't render in `<dialog>` | MEDIUM | HIGH | is-modal header ensures full response; test each type |
| Hash-based triggers break | LOW | HIGH | Bridge pattern — same listener, different backend |
| Nested modals break | MEDIUM | MEDIUM | Test showTicket → subtask → delete chain |
| hx-boost breaks page JS | MEDIUM | MEDIUM | htmx:afterSettle re-init; disable boost per-link |
| Plugin modals break | LOW | MEDIUM | Same API; shim for direct nyroModal calls |
| Canvas modals break | LOW | HIGH | Fix shared template once, test 3 canvas types |
| Form submission fails in `<dialog>` | MEDIUM | HIGH | Verify POST/redirect cycle per domain |
| CSS conflicts | LOW | MEDIUM | DaisyUI modal classes namespaced |

---

## What This Document Does NOT Cover

- Phase 2: Core domain component forward-porting
- Phase 3: JS optimization, canvas domain componentization
- Phase 4: Remaining domains, Bootstrap removal
- Converting .tpl.php to .blade.php (happens in Phase 2-4)
- nyroModal library file removal (after Phase 2 verification)
