# Glass Effects Implementation — Claude Code Prompt

**For Claude Code Execution**

| Field | Value |
|---|---|
| Project | Leantime |
| Scope | Glass effect implementation across UI surfaces |
| Reference | `leantime-glass-effects-requirements.md` — **Read first, it's the surface decision map** |
| Depends on | Design System v2 foundation (token aliasing) — must be complete |
| Risk | LOW per phase (visual changes, no logic changes) |
| Approach | One phase at a time. Complete, test, confirm before next. |

---

## Pre-Read (Required)

1. `CLAUDE.md` — project conventions
2. `leantime-glass-effects-requirements.md` — **The surface decision map. Every decision flows from this.**
3. `docs/14-DESIGN-TOKENS.md` — current token definitions
4. Skim `app/Views/Templates/layouts/` to understand main layout structure
5. Skim `app/Views/Templates/components/elements/card.blade.php` to see existing glass prop

---

## Critical Constraints

### DO

- Work one phase at a time — complete, test, confirm
- Use the exact glass token names from the requirements doc
- Use `tw:` prefix for all Tailwind classes
- Test in both light and dark mode after every change
- Test in both "default" and "minimal" themes
- Test with browser DevTools "Layers" panel to verify composite layer count
- Preserve existing visual appearance — glass is an enhancement, not a redesign
- Add `-webkit-backdrop-filter` alongside `backdrop-filter` for Safari

### DO NOT

- Add glass to surfaces marked ⬜ (No glass) in the requirements doc
- Add `backdrop-filter` to components that render in lists (kanban cards, table rows, timeline bars)
- Animate blur values — glass appears instant
- Use `[data-theme="dark"]` or `.theme-dark` selectors — dark mode uses separate CSS files
- Use `.theme-focus` or `.theme-high-contrast` selectors — these don't exist
- Remove the `glass` prop from card component — it's the opt-in mechanism
- Exceed 15 simultaneously visible glass surfaces on any page

### ARCHITECTURE AWARENESS

Leantime has **two themes** ("default" and "minimal"), each with **two mode files**:
- `public/theme/default/css/light.css`
- `public/theme/default/css/dark.css`
- `public/theme/minimal/css/light.css`
- `public/theme/minimal/css/dark.css`

Dark mode is NOT controlled by CSS selectors. Leantime loads the appropriate CSS file. All dark-mode token values must go in `dark.css` files, not in `@media` queries or attribute selectors in shared CSS.

---

## Phase A: Dark Mode Normalization + Subtle Glass Tokens + Utility Classes

**Goal:** (1) Correct the existing dark mode glass values that are too translucent. (2) Add the missing `--glass-bg-subtle` and `--glass-blur-subtle` tokens to all 4 theme files. (3) Add `.lt-glass` and `.lt-glass-subtle` utility classes to shared CSS.

### Step 0: Normalize existing dark mode glass values

The current dark.css files have wrong values. Fix them BEFORE adding subtle tokens.

**In `public/theme/default/css/dark.css`** — find and replace:
```css
/* CURRENT (wrong): */
--glass-bg: rgba(0, 0, 0, 0.5);   /* 50% opacity pure black — too translucent */
--glass-blur: blur(24px);          /* too heavy */

/* REPLACE WITH: */
--glass-bg: rgba(30, 30, 46, 0.85);  /* 85% opacity, tinted dark — matches light mode ratio */
--glass-blur: blur(8px);              /* matches light mode */
```

**In `public/theme/minimal/css/dark.css`** — check current `--glass-bg` value. If it's 30% opacity, update to match minimal's opaque philosophy:
```css
/* Minimal dark should be near-opaque, consistent with minimal light */
--glass-bg: var(--color-bg-card);  /* fully opaque */
--glass-blur: blur(4px);           /* keep light blur for minimal */
```

**Verify the hierarchy is correct after changes:**
- default/light: 85% opacity, blur(8px) ✓
- default/dark: 85% opacity, blur(8px) ✓ (was 50%, blur(24px))
- minimal/light: 100% opacity, blur(4px) ✓
- minimal/dark: 100% opacity, blur(4px) ✓ (was 30%)

### Step 1: Find theme CSS files

```bash
# Locate the 4 theme CSS files
find public/theme/ -name "light.css" -o -name "dark.css" | sort

# Verify primary glass tokens already exist
grep -n "glass-blur\|glass-bg\|glass-border\|glass-inset" \
  public/theme/default/css/light.css \
  public/theme/default/css/dark.css \
  public/theme/minimal/css/light.css \
  public/theme/minimal/css/dark.css
```

### Step 2: Add subtle tokens to each file

**In `public/theme/default/css/light.css`** — find the existing glass tokens block and add:
```css
--glass-bg-subtle: rgba(255, 255, 255, 0.92);
--glass-blur-subtle: blur(4px);
```

**In `public/theme/default/css/dark.css`** — add:
```css
--glass-bg-subtle: rgba(30, 30, 46, 0.92);
--glass-blur-subtle: blur(4px);
```

**In `public/theme/minimal/css/light.css`** — add:
```css
--glass-bg-subtle: var(--color-bg-card);
--glass-blur-subtle: none;
```

**In `public/theme/minimal/css/dark.css`** — add:
```css
--glass-bg-subtle: var(--color-bg-card);
--glass-blur-subtle: none;
```

### Step 3: Add utility classes

Find the shared CSS file where global utility classes live:

```bash
# Check where existing glass-related styles are
grep -rn "\.glass\|glass-bg\|backdrop-filter.*var(--glass" \
  resources/css/ app/Views/ \
  --include="*.css" --include="*.blade.php" | head -20

# Find the main stylesheet
ls resources/css/main.css resources/css/app.css 2>/dev/null
```

Add these utility classes to the shared CSS (NOT in a theme file — these reference tokens that the theme files define):

```css
/* Glass utility classes */
.lt-glass {
  background: var(--glass-bg);
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur);
  border: 1px solid var(--glass-border);
  box-shadow: var(--glass-inset), var(--shadow-sm);
}

.lt-glass-subtle {
  background: var(--glass-bg-subtle);
  backdrop-filter: var(--glass-blur-subtle);
  -webkit-backdrop-filter: var(--glass-blur-subtle);
  border: 1px solid var(--glass-border);
}

@supports not (backdrop-filter: blur(1px)) {
  .lt-glass, .lt-glass-subtle {
    background: var(--color-bg-card);
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
  }
}
```

**Note:** Phase A may have committed `--emboss-track` and `--emboss-highlight` tokens alongside glass tokens. These are inert (nothing references them). Leave them — a future emboss spec will consume them. Do not add emboss utility classes to glass.css.

### Test

```bash
# Build CSS if needed
# [check CLAUDE.md for build command]

# Then in browser:
```

```javascript
// Verify subtle tokens exist
getComputedStyle(document.documentElement).getPropertyValue('--glass-bg-subtle');
// Should return rgba value, not empty

// Verify utility classes work
const test = document.createElement('div');
test.className = 'lt-glass';
test.style.cssText = 'width:100px;height:100px;position:fixed;top:50%;left:50%;z-index:9999';
document.body.appendChild(test);
console.log('backdrop-filter:', getComputedStyle(test).backdropFilter);
// Should return blur(Xpx), not 'none'
document.body.removeChild(test);

// Switch to minimal theme and verify glass is degraded
// (reload with minimal theme active)
```

### Report

List: files modified, tokens added per file, any conflicts found.

---

## Phase B: Overlay Surfaces (Modal, Dropdown, Tooltip, Popover)

**Goal:** Add glass treatment to floating/overlay surfaces. Highest impact, lowest risk — they appear one at a time. Also fix the hardcoded blur on `#modal-blur-overlay`.

### Find overlay components

```bash
# Modal
find app/Views/Templates/components/actions/ -name "*.blade.php" | head -10
grep -n "background\|backdrop\|bg-white\|bg-card\|blur" \
  app/Views/Templates/components/actions/modal.blade.php \
  app/Views/Templates/components/actions/confirm-delete.blade.php 2>/dev/null

# Dropdowns
find app/Views/Templates/components/ -name "*dropdown*" -o -name "*drop-down*" | head -10
grep -n "background\|bg-white\|bg-card" \
  app/Views/Templates/components/elements/dropdown.blade.php 2>/dev/null

# Tooltips / popovers
grep -rn "tooltip\|popover\|health-pop\|tip\|preview" \
  app/Views/Templates/components/ \
  --include="*.blade.php" | head -20

# THE HARDCODED BLUR — must fix
grep -n "modal-blur-overlay\|blur(12px)" resources/css/main.css
```

### Fix — Modal

The global modal already uses `var(--glass-blur)`. Verify it's fully wired:

```bash
grep -n "glass\|backdrop-filter\|blur" \
  app/Views/Templates/components/actions/modal.blade.php
```

If the modal panel (the white rectangle, not the backdrop) doesn't use the glass class yet, add it:

```php
{{-- Modal panel should use glass class --}}
<div class="lt-glass tw:rounded-[var(--radius)] ...">
```

The `::backdrop` / scrim stays dark and opaque:
```css
dialog::backdrop {
  background: rgba(0, 0, 0, 0.5);
}
```

### Fix — Hardcoded modal blur overlay

In `resources/css/main.css`, find `#modal-blur-overlay` with `backdrop-filter: blur(12px)`:

```css
/* BEFORE */
#modal-blur-overlay {
  backdrop-filter: blur(12px);
}

/* AFTER */
#modal-blur-overlay {
  backdrop-filter: var(--glass-blur);
  -webkit-backdrop-filter: var(--glass-blur);
}
```

### Fix — Dropdowns

Find the dropdown menu container in each dropdown component. Replace its background with glass:

```php
{{-- BEFORE: opaque dropdown --}}
<div class="tw:bg-[var(--color-bg-card)] tw:border tw:border-[var(--color-border-default)] tw:rounded-[var(--radius-sm)] tw:shadow-lg ...">

{{-- AFTER: glass dropdown --}}
<div class="lt-glass tw:rounded-[var(--radius-sm)] tw:shadow-[var(--shadow-lg)] ...">
```

The `.lt-glass` class includes border and background — remove any duplicate `tw:bg-*` and `tw:border-*` classes that would conflict.

### Fix — Tooltips / Popovers

These are smaller surfaces. Use `lt-glass-subtle`:

```php
{{-- BEFORE --}}
<div class="tw:bg-[var(--color-bg-card)] tw:border ...">

{{-- AFTER --}}
<div class="lt-glass-subtle tw:rounded-[var(--radius-sm)] ...">
```

### Test

1. Open a modal → glass panel visible, backdrop is dark scrim
2. Open a dropdown → glass menu, text readable
3. Hover a tooltip → subtle glass, not overwhelming
4. Dark mode → all overlays adapt automatically
5. minimal theme → all overlays should be opaque or near-opaque (tokens degrade)
6. Verify `#modal-blur-overlay` uses token now, not hardcoded blur(12px)

---

## Phase C: Layout Surfaces (Nav Sidebar, Top Header)

**Goal:** Add glass to the navigation sidebar (full) and sticky top header (subtle). Also fix the hardcoded inline blur on the registration layout.

### Find layout templates

```bash
# Main layout
find app/Views/Templates/layouts/ -name "*.blade.php" | head -20

# Nav sidebar
grep -rn "nav\|sidebar\|side-bar" \
  app/Views/Templates/layouts/ \
  --include="*.blade.php" | head -20

# Top header
grep -rn "header\|top-bar\|topbar\|app-bar" \
  app/Views/Templates/layouts/ \
  --include="*.blade.php" | head -20

# Registration layout with hardcoded blur
grep -rn "backdrop-filter.*blur(3px)\|blur(3px)" \
  app/Views/Templates/ \
  --include="*.blade.php" | head -10
```

### Fix — Nav Sidebar

Find the nav sidebar container element. Add full glass:

```php
{{-- Nav sidebar --}}
<nav class="lt-glass tw:h-full tw:overflow-y-auto ...">
```

Or if using inline styles:
```php
style="background: var(--glass-bg); backdrop-filter: var(--glass-blur); -webkit-backdrop-filter: var(--glass-blur); border-right: 1px solid var(--glass-border);"
```

### Fix — Top Header

The top header is sticky. Use subtle glass so scrolling content blurs through gently:

```php
{{-- Top header --}}
<header class="lt-glass-subtle tw:sticky tw:top-0 tw:z-50 ...">
```

### Fix — Registration layout hardcoded blur

Find the inline `backdrop-filter: blur(3px)` and replace:

```php
{{-- BEFORE --}}
style="backdrop-filter: blur(3px);"

{{-- AFTER --}}
style="backdrop-filter: var(--glass-blur-subtle); -webkit-backdrop-filter: var(--glass-blur-subtle);"
```

### Test

1. Scroll the page → header shows content blurring through subtly
2. Sidebar shows page content blurring through
3. Dark mode → both surfaces adapt
4. No "double blur" where sidebar meets header (separate blur contexts)
5. minimal theme → sidebar and header should be opaque or near-opaque
6. Mobile/responsive → sidebar may collapse to drawer. Glass applies when open.
7. Registration page → verify blur uses token, not hardcoded 3px

---

## Phase D: Container Opt-In (Card glass prop, Widget subtle glass)

**Goal:** Wire the card component's `glass` prop to the `.lt-glass` utility class (fixing the nonexistent `tw:glass`). Add subtle glass to widget component.

### Find card component

```bash
grep -n "glass" app/Views/Templates/components/elements/card.blade.php
```

### Fix — Card

The card already has `glass => false`. It likely maps to a Tailwind class `tw:glass` that doesn't exist. Replace with the plain CSS `.lt-glass` class:

```php
{{-- In card.blade.php --}}
@props([
  'glass' => false,
  {{-- ... other props --}}
])

<div class="
  tw:rounded-[var(--radius)]
  {{ $glass ? 'lt-glass' : 'tw:bg-[var(--color-bg-card)] tw:border tw:border-[var(--color-border-default)]' }}
  tw:shadow-[var(--shadow-sm)]
  ...
">
```

Key: when `glass=true`, the `.lt-glass` class provides background, border, and backdrop-filter. When `glass=false`, standard opaque styling with explicit bg and border.

### Fix — Widget

In `components/content/widget.blade.php`, add subtle glass:

```php
<div class="lt-glass-subtle tw:rounded-[var(--radius)] ...">
```

### DO NOT add glass to stageflow

Per design decision #2 (locked), stageflow active cards do NOT get glass. The accent border + elevation already differentiates. Skip this component entirely.

### Test

1. Dashboard → widget cards show subtle glass (slight blur-through on page bg)
2. Any page with `<x-global::elements.card :glass="true">` → full glass card
3. Cards without glass prop → opaque, no blur
4. Dark mode → all variants
5. minimal theme → widgets should be opaque (subtle tokens degrade to opaque in minimal)
6. Count visible glass surfaces on dashboard → should stay under 15

---

## Phase E: Dark Mode + Theme Verification

**Goal:** Systematic check of every glass surface in dark mode, in both themes.

### Process

For each surface modified in Phases B-E, check in 4 combinations:
1. default theme + light mode
2. default theme + dark mode
3. minimal theme + light mode
4. minimal theme + dark mode

### Checklist per surface

- [ ] Glass surface visible but not "bright rectangle on dark background"
- [ ] Text on glass surface is readable
- [ ] Borders visible but subtle (not bright white lines)
- [ ] Shadows visible (dark mode may need boosted opacity)
- [ ] minimal theme: glass is degraded (opaque or near-opaque)

### Find any remaining hardcoded blurs

```bash
# Any backdrop-filter not using tokens
grep -rn "backdrop-filter:\s*blur" \
  app/Views/ resources/css/ \
  --include="*.blade.php" --include="*.css" \
  | grep -v "var(--glass"
```

Any results are hardcoded values that won't respect theme degradation. Replace with appropriate `var(--glass-blur)` or `var(--glass-blur-subtle)`.

### Report

For each glass surface:

| Surface | default/light | default/dark | minimal/light | minimal/dark | Fix |
|---------|--------------|-------------|---------------|-------------|-----|
| Modal | ✅ | ✅ / ⚠️ | ✅ | ✅ / ⚠️ | (describe) |
| ... | | | | | |

---

## Execution Rules

1. **One phase per session.** A-E in order. Phase A must be complete before any others.
2. **Glass tokens, not hardcoded values.** Every `backdrop-filter: blur(Xpx)` must use `var(--glass-blur)` or `var(--glass-blur-subtle)`. Every translucent background must use `var(--glass-bg)` or `var(--glass-bg-subtle)`.
3. **No dark mode selectors in shared CSS.** Dark values live in `dark.css` files. Do not add `[data-theme="dark"]` or `.theme-dark` rules.
4. **Performance awareness.** After each phase, count glass surfaces on the most complex page. Stay under 15.
5. **Safari compatibility.** Always pair `backdrop-filter` with `-webkit-backdrop-filter`.
6. **No animated blur.** Glass appears/disappears with the component. Blur value never transitions.
7. **When uncertain, skip.** If glass looks wrong or hurts readability, mark it ⬜ and flag it.
8. **This spec is glass only.** `backdrop-filter` frosted surfaces. Progress bar emboss, milestone bar polish, toggle track treatments are separate specs. Don't add them here.
9. **Test all 4 combos.** default/light, default/dark, minimal/light, minimal/dark. Every phase.
