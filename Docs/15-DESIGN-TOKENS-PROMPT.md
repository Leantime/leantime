# Design Token Audit & Equalization — Claude Code Prompt

**For Claude Code Execution**

| Field | Value |
|---|---|
| Project | Leantime |
| Scope | Codebase-wide visual equalization, theming, accessibility |
| Reference | `Docs/14-DESIGN-TOKENS.md` — **Read this first, it's the source of truth** |
| Risk | LOW per sweep (small, testable changes) |
| Approach | One sweep at a time. Never batch multiple categories. |

---

## Pre-Read (Required)

1. `CLAUDE.md` — project conventions
2. `Docs/14-DESIGN-TOKENS.md` — **The canonical token reference. Every decision flows from this.**
3. Skim `app/Views/Templates/` directory structure to understand component layout
4. Skim one existing Blade component to confirm `tw:` prefix convention and CSS variable usage

---

## Critical Constraints

### DO

- Work one sweep at a time — complete it, test it, confirm before starting the next
- Use the exact token values from 14-DESIGN-TOKENS.md
- Use `tw:` prefix for all Tailwind classes
- Use CSS custom properties for all theme-layer colors
- Produce a summary report after each sweep (format in 14-DESIGN-TOKENS.md section 11.3)
- Test in both light and dark mode after color changes
- Preserve existing visual appearance while normalizing the underlying values
- Add accessibility attributes (aria-label, roles, focus styles) as you encounter gaps

### DO NOT

- Change multiple categories in one sweep — colors and typography are separate passes
- Invent new tokens not in the reference document — flag them instead
- Remove existing functionality or change component behavior
- Touch theme definition files (those define the custom properties)
- Touch third-party CSS
- Touch files being actively rewritten by other contributors
- Make cosmetic changes to pages that are scheduled for full redesign

---

## Sweep 1: Hardcoded Brand Colors

**Goal:** Every instance of `#1b75bb` and `#81B1A8` (and case variants) becomes `var(--accent1)` or `var(--accent2)`.

### Find

```bash
grep -rn "#1b75bb\|#1B75BB\|#81B1A8\|#81b1a8\|#1B75Bb\|#81b1a8" \
  app/Views/ app/Plugins/ \
  --include="*.blade.php" --include="*.css" --include="*.js"
```

### Fix

For each instance:
- If in a `color:` or `background:` CSS property → replace with `var(--accent1)` or `var(--accent2)`
- If in a Tailwind class like `tw:text-[#1b75bb]` → replace with `tw:text-[var(--accent1)]` or equivalent
- If in a `style=""` attribute → same replacement
- If in a JavaScript string for dynamic styling → use `getComputedStyle(document.documentElement).getPropertyValue('--accent1')`

### Exclude

- Theme definition files where these ARE the default values being set
- Comments or documentation referencing the color codes
- SVG files where the color is baked into an asset (flag these for later)

### Test

```javascript
// In browser console — change accent colors and verify nothing stays blue
document.documentElement.style.setProperty('--accent1', '#E65100');
document.documentElement.style.setProperty('--accent2', '#FFB74D');
// Scan every changed page — nothing should remain the old blue/teal
```

### Report

Produce the sweep report per section 11.3 format.

---

## Sweep 2: Hardcoded Neutral Colors (Dark Mode Prep)

**Goal:** Background, text, and border colors that are hardcoded hex values become CSS custom properties so they swap in dark mode.

### Find

```bash
# Hardcoded white backgrounds
grep -rn "background:\s*#[Ff][Ff][Ff]\|background-color:\s*#[Ff][Ff][Ff]\|background:\s*white" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"

# Hardcoded dark text
grep -rn "color:\s*#1[Aa]1[Aa]2[Ee]\|color:\s*#333\|color:\s*#222\|color:\s*#000" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"

# Hardcoded gray text
grep -rn "color:\s*#[4-9][Bb][0-9]\|color:\s*#[Aa-Ff][0-9]" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css" | head -40

# Hardcoded border colors
grep -rn "border.*:\s*.*#[EeFf][0-9A-Fa-f]" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css" | head -40
```

### Fix

Map each hardcoded value to the nearest token:

| Hardcoded | Token |
|---|---|
| `#FFFFFF`, `white` (backgrounds) | `var(--color-bg-card)` |
| `#F8F9FB`, `#f5f5f5`, `#fafafa` (page bg) | `var(--color-bg-page)` |
| `#F0F1F3`, `#f0f0f0`, `#eee` (muted bg) | `var(--color-bg-muted)` |
| `#F3F4F6` (hover bg) | `var(--color-bg-hover)` |
| `#1A1A2E`, `#333`, `#222`, `#000` (dark text) | `var(--color-text-primary)` |
| `#4B5563`, `#555`, `#666` (body text) | `var(--color-text-secondary)` |
| `#9CA3AF`, `#999`, `#aaa` (muted text) | `var(--color-text-muted)` |
| `#D1D5DB`, `#ccc`, `#ddd` (disabled text) | `var(--color-text-disabled)` |
| `#E8ECF0`, `#e0e0e0`, `#ddd` (borders) | `var(--color-border-default)` |
| `#F0F1F3` (light borders) | `var(--color-border-light)` |

If a color doesn't map cleanly, flag it — don't guess.

### Test

```javascript
// Toggle dark mode
document.documentElement.classList.add('theme-dark');
// Check: no white rectangles on dark background, no invisible text, borders visible
```

### Report

Produce the sweep report. Note any colors that didn't map to a token — these need design decisions.

---

## Sweep 3: Dark Mode Verification

**Goal:** After sweeps 1-2, systematically check every major page/component in dark mode.

### Process

For each major template in `app/Views/`:
1. Load the page
2. Toggle dark mode
3. Check for:
   - Text that's invisible or near-invisible against background
   - Borders that disappear
   - Cards that blend into the page background
   - Focus indicators that aren't visible
   - Shadows that are invisible (should use `var(--shadow-*)`)
   - Images or icons that assume a white background

### Fix

- Add missing dark mode token usage
- Add `var(--shadow-*)` for shadows not yet using CSS variables
- Add `var(--color-bg-card)` background to elements that were transparent (worked on white, invisible on dark)

---

## Sweep 4: Keyboard Accessibility

**Goal:** Every interactive element has a visible focus indicator and can be activated with keyboard.

### Find

```bash
# Interactive elements: buttons, links, inputs, selects, textareas
grep -rn "<button\|<a \|<input\|<select\|<textarea" \
  app/Views/ app/Plugins/ --include="*.blade.php" | wc -l

# Elements with click handlers but no keyboard support
grep -rn "@click\|onclick\|hx-get\|hx-post" \
  app/Views/ app/Plugins/ --include="*.blade.php" | grep -v "button\|<a " | head -20

# Check for focus-visible styles
grep -rn "focus-visible\|focus-ring\|:focus" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css" | wc -l
```

### Fix

For each interactive element:
1. If it's a `<div>` or `<span>` with a click handler, either:
   - Change to `<button>` (preferred) or `<a href>`
   - Or add `tabindex="0"`, `role="button"`, `@keydown.enter="..."`, `@keydown.space.prevent="..."`
2. Add focus style: `tw:focus-visible:ring-2 tw:focus-visible:ring-[var(--accent1)] tw:focus-visible:ring-offset-2`
3. Or use the global `var(--focus-ring)` box-shadow

### Test

Tab through every page without touching the mouse. Every interactive element should:
- Receive visible focus
- Activate on Enter (and Space for buttons)
- Have a logical tab order matching visual layout

---

## Sweep 5: Screen Reader Basics

**Goal:** Every element communicates its purpose to assistive technology.

### Find

```bash
# Icon-only buttons (no text content)
grep -rn "<button[^>]*>\s*<i " app/Views/ --include="*.blade.php" | grep -v "aria-label\|sr-only" | head -20

# Icon-only links
grep -rn "<a [^>]*>\s*<i " app/Views/ --include="*.blade.php" | grep -v "aria-label\|sr-only" | head -20

# Inputs without labels
grep -rn "<input " app/Views/ --include="*.blade.php" | grep -v "aria-label\|aria-labelledby" | head -20
# Cross-reference: check if these have a <label for="..."> nearby

# Images without alt
grep -rn "<img " app/Views/ --include="*.blade.php" | grep -v "alt=" | head -20

# Status dots/pills without text
grep -rn "pill-ok\|pill-wip\|pill-draft\|pill-flag\|dot-ok\|dot-wip\|dot-draft\|dot-flag" \
  app/Views/ --include="*.blade.php" | grep -v "aria-label\|sr-only\|title=" | head -20
```

### Fix

| Pattern | Fix |
|---|---|
| Icon-only `<button>` | Add `aria-label="Action name"` |
| Icon-only `<a>` | Add `aria-label="Link purpose"` |
| Decorative icon next to text | Add `aria-hidden="true"` to the icon |
| `<input>` without label | Add `<label for="input-id">` or `aria-label` |
| `<img>` without alt | Add `alt="Description"` or `alt=""` if decorative |
| Status dot/pill | Add `aria-label="Status: In Progress"` |

---

## Sweep 6: HTMX Live Regions

**Goal:** Dynamic content updates are announced to screen readers.

### Find

```bash
# All HTMX targets
grep -rn "hx-target\|hx-swap" app/Views/ --include="*.blade.php" | head -30

# Check which target containers have aria-live
# For each hx-target="#some-id", check if #some-id has aria-live
```

### Fix

For each HTMX target that holds user-facing content:
```html
<div id="target-id" aria-live="polite" aria-atomic="false">
  <!-- HTMX swaps here -->
</div>
```

For loading indicators:
```html
<div class="htmx-indicator" role="status">
  <span class="tw:sr-only">Loading...</span>
  <!-- visual spinner -->
</div>
```

---

## Sweep 7: Reduced Motion

**Goal:** All animations respect `prefers-reduced-motion`.

### Find

```bash
# CSS animations
grep -rn "@keyframes\|animation:" app/Views/ app/Plugins/ \
  --include="*.css" --include="*.blade.php"

# CSS transitions
grep -rn "transition:" app/Views/ app/Plugins/ \
  --include="*.css" --include="*.blade.php" | head -30

# Check for existing reduced-motion support
grep -rn "prefers-reduced-motion\|motion-reduce" app/Views/ app/Plugins/ \
  --include="*.css" --include="*.blade.php" | wc -l
```

### Fix

Option A — global reset (if not already present):
```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

Option B — per-component Tailwind:
```html
<div class="tw:transition-all tw:duration-200 tw:motion-reduce:transition-none">
```

---

## Sweep 8: Color-Only Status Indicators

**Goal:** No status is communicated by color alone.

### Find

```bash
# Status dots
grep -rn "dot-ok\|dot-wip\|dot-draft\|dot-flag" app/Views/ --include="*.blade.php"

# Status backgrounds
grep -rn "bg-green\|bg-red\|bg-yellow\|bg-blue" app/Views/ --include="*.blade.php" | head -20

# Colored borders as sole status indicator
grep -rn "border-left.*color\|border-l-" app/Views/ --include="*.blade.php" | head -20
```

### Fix

Every colored status indicator needs a companion:
- Dot → add icon inside or `aria-label`
- Pill → already has text label (verify it does)
- Border → ensure nearby text states the status
- Chart segment → add text labels or patterns

---

## Sweep 9: Touch Targets

**Goal:** All interactive elements meet minimum touch target size.

### Find

Look for small interactive elements:
```bash
# Small buttons (likely too small)
grep -rn "padding:\s*[0-3]px\|tw:p-0\|tw:p-0.5\|tw:p-1" app/Views/ --include="*.blade.php" | head -20

# Icon buttons without padding
grep -rn "<button[^>]*class=\"[^\"]*\"[^>]*>\s*<i " app/Views/ --include="*.blade.php" | head -20
```

### Fix

- Ensure minimum 44x44px clickable area (even if visual is smaller, extend with padding)
- For icon buttons: `tw:p-2.5` minimum (10px padding around ~24px icon = 44px)
- For close buttons: extend hit area with `::before` pseudo-element if needed

---

## Sweeps 10-15: Visual Consistency

These sweeps normalize typography, radius, spacing, shadows, transitions, and legacy patterns. Follow the search patterns in 14-DESIGN-TOKENS.md section 11.2 and fix each deviation to the nearest token.

### Sweep 10: Typography

Fix off-scale font sizes (15px → 14px or 16px), missing line-heights, justified text → left-aligned, inconsistent weights for same semantic level.

### Sweep 11: Border Radius

Fix off-scale values: 3px → 4px, 5px → 4px or 6px, 7px → 8px, 10px → 8px or 12px, 16px → 12px or 20px.

### Sweep 12: Spacing

Fix arbitrary values: 15px → 16px, 13px → 12px, 7px → 8px, 11px → 12px, 9px → 8px or 10px.

### Sweep 13: Shadows

Replace custom shadow values with `var(--shadow-sm/md/lg/xl)`. Remove `box-shadow` from `transition` properties.

### Sweep 14: Transitions

Replace `transition: all` with specific properties. Ensure durations match scale (150/200/300/350ms). Remove `box-shadow` transitions.

### Sweep 15: Legacy Patterns

Catalog jQuery usage, `.tpl.php` files, and inline styles. Don't fix all at once — create a backlog sorted by page traffic/importance.

---

## Execution Rules

1. **One sweep per session.** Complete, test, report, then move to the next.
2. **Smallest possible changes.** Replace a color value, don't rewrite the component.
3. **Preserve visual output.** The page should look identical after a token sweep (in the default theme). Only dark mode / custom theme behavior should improve.
4. **When uncertain, flag.** If a color doesn't map cleanly to a token, report it. Don't guess.
5. **Test after every sweep.** Light mode default, dark mode, one custom accent. Three checks.
6. **Accessibility sweeps are not optional.** Sweeps 4-9 are just as important as the color sweeps. They come before cosmetic sweeps for a reason — users are literally locked out without them.

---

## Quick Reference: File Types to Scan

| Path | What lives there | Primary sweep targets |
|---|---|---|
| `app/Views/Templates/` | Core Blade templates | All sweeps |
| `app/Views/Templates/components/` | Shared Blade components | All sweeps |
| `app/Plugins/PgmPro/Views/` | Plugin views | All sweeps |
| `app/Plugins/StrategyPro/Views/` | Plugin views | All sweeps |
| `public/dist/css/` | Compiled CSS | Sweeps 1-3, 11-14 |
| `app/Domain/*/Templates/` | Domain-specific templates | All sweeps |
| `resources/css/` | Source CSS (pre-build) | Sweeps 1-3, color vars |
