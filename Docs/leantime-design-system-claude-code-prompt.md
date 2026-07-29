# Leantime Design System — Claude Code Implementation Prompt

**For Claude Code Execution**

| Field | Value |
|---|---|
| Project | Leantime |
| Scope | Codebase-wide visual equalization, theming, accessibility |
| Source of Truth | `leantime-design-system-requirements.md` — **all design decisions are there** |
| Token Reference | `docs/14-DESIGN-TOKENS.md` — update this first, then reference it |
| Risk | LOW per sweep (small, testable changes) |
| Approach | One sweep at a time. Never batch multiple categories. |

---

## Pre-Read (Required)

Before writing any code, read these files in order:

1. `CLAUDE.md` — project conventions
2. `leantime-design-system-requirements.md` — **every design decision, confirmed and final**
3. `docs/14-DESIGN-TOKENS.md` — current token definitions (will be updated in Sweep 0)
4. Skim `app/Views/Templates/components/` to understand Blade component structure
5. Confirm `tw:` prefix convention in one existing component

---

## Brand Palette (memorize these)

```
accent1:      #004766  (deep teal)     — primary, nav, buttons, links, info
accent2:      #00B893  (emerald)       — secondary, success, gradients, progress
accent3:      #CADE1B  (chartreuse)    — fills/backgrounds ONLY, never text on white
accent3-text: #A8B516  (dark chartreuse) — warning text, large text only
accent4:      #F61067  (hot pink)      — danger, error, destructive actions
accent4-soft: #C84D7C  (softened pink) — stageflow s4, muted danger contexts
```

**Semantic mapping:** Success=accent2, Danger=accent4, Warning=accent3-text, Info=accent1

**Old colors being replaced:**
- `#1b75bb` (old accent1) → `var(--accent1)` `#004766`
- `#81B1A8` (old accent2) → `var(--accent2)` `#00B893`
- `#1B75BB` variants → `var(--accent1)`

---

## Critical Constraints

### DO

- Work one sweep at a time — complete it, test it, confirm before starting the next
- Use exact token values from the requirements document
- Use `tw:` prefix for all Tailwind classes
- Use CSS custom properties for ALL theme-layer colors
- Produce a summary report after each sweep
- Test in both light and dark mode after color changes
- Preserve existing visual appearance while normalizing underlying values
- Add accessibility attributes (aria-label, roles, focus styles) as you encounter gaps

### DO NOT

- Change multiple categories in one sweep
- Invent new tokens not in the requirements doc — flag them instead
- Remove existing functionality or change component behavior
- Touch theme definition files (those define the custom properties) — except in Sweep 0
- Touch third-party CSS
- Touch files being actively rewritten by other contributors
- Make cosmetic changes to pages scheduled for full redesign
- Use `#1b75bb` or `#81B1A8` anywhere — these are the OLD palette

---

## Sweep 0: Update Token Reference Document

**Goal:** Update `docs/14-DESIGN-TOKENS.md` to match the requirements doc. This makes it the canonical in-codebase reference.

### Changes needed:
1. Replace old palette (`#1b75bb`, `#81B1A8`) with new 4-accent system
2. Add `--accent3`, `--accent3-text`, `--accent4`, `--accent4-soft` token definitions
3. Update semantic color mapping table
4. Add glass treatment tokens (`--glass-bg`, `--glass-border`, `--glass-inset`)
5. Update typography tokens (Hanken Grotesk, weight scale 450–800)
6. Add stageflow stage color definitions (s1–s5)
7. Verify spacing, radius, shadow tokens match requirements doc

### Also update:
- Add accent3/accent4 to theme system in `app/Core/UI/Theme.php`
- Add new tokens to `public/theme/default/theme.ini` and `public/theme/minimal/theme.ini`

### Test:
- Themes still load correctly
- Color picker still works for accent1/accent2
- No console errors

---

## Sweep 1: Hardcoded Brand Colors

**Goal:** Every instance of old brand hex values becomes CSS custom properties.

### Find

```bash
grep -rn "#1b75bb\|#1B75BB\|#81B1A8\|#81b1a8\|#004666\|#00a887" \
  app/Views/ app/Plugins/ \
  --include="*.blade.php" --include="*.css" --include="*.js"
```

Also search for the new palette values that might be hardcoded instead of tokenized:
```bash
grep -rn "#004766\|#00B893\|#CADE1B\|#F61067\|#A8B516" \
  app/Views/ app/Plugins/ \
  --include="*.blade.php" --include="*.css" --include="*.js"
```

### Fix

| Found | Replace with |
|-------|-------------|
| `#1b75bb` / `#1B75BB` / `#004666` / `#004766` in CSS | `var(--accent1)` |
| `#81B1A8` / `#81b1a8` / `#00a887` / `#00B893` in CSS | `var(--accent2)` |
| `#CADE1B` in CSS backgrounds | `var(--accent3)` |
| `#A8B516` in CSS text | `var(--accent3-text)` |
| `#F61067` in CSS | `var(--accent4)` |
| `tw:text-[#1b75bb]` | `tw:text-[var(--accent1)]` |
| `style="color: #1b75bb"` | `style="color: var(--accent1)"` |
| JS dynamic styling | `getComputedStyle(document.documentElement).getPropertyValue('--accent1')` |

### Exclude

- Theme definition files where these ARE the default values being set
- Comments or documentation
- SVG files with baked-in colors (flag for later)

### Test

```javascript
// In browser console — swap accents and verify nothing stays old color
document.documentElement.style.setProperty('--accent1', '#E65100');
document.documentElement.style.setProperty('--accent2', '#FFB74D');
// Scan every changed page — nothing should remain the old blue/teal
```

---

## Sweep 2: Hardcoded Neutral Colors

**Goal:** Background, text, and border hex values become CSS custom properties for dark mode.

### Find

```bash
# Hardcoded white backgrounds
grep -rn "background:\s*#[Ff][Ff][Ff]\|background-color:\s*#[Ff][Ff][Ff]\|background:\s*white" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"

# Hardcoded dark text
grep -rn "color:\s*#1[Aa]1[Aa]2[Ee]\|color:\s*#333\|color:\s*#222\|color:\s*#000" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"

# Hardcoded borders
grep -rn "border.*:\s*.*#[EeFf][0-9A-Fa-f]" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css" | head -40
```

### Fix

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
document.documentElement.classList.add('theme-dark');
// Check: no white rectangles, no invisible text, borders visible
```

---

## Sweep 3: Dark Mode Verification

**Goal:** Systematically check every major page/component in dark mode after Sweeps 1-2.

### Process

For each major template in `app/Views/`:
1. Load the page
2. Toggle dark mode
3. Check for: invisible text, disappeared borders, cards blending into background, invisible focus indicators, invisible shadows, images assuming white background

### Fix

- Add missing dark mode token usage
- Add `var(--shadow-*)` for shadows not yet using CSS variables
- Add `var(--color-bg-card)` background to elements that were transparent

---

## Sweep 4: Keyboard Accessibility

**Goal:** Every interactive element has visible focus indicator and keyboard activation.

### Find

```bash
# Elements with click handlers but no keyboard support
grep -rn "@click\|onclick\|hx-get\|hx-post" \
  app/Views/ app/Plugins/ --include="*.blade.php" | grep -v "button\|<a " | head -20
```

### Fix

For each interactive element:
1. Non-semantic click targets (`<div>`, `<span>`) → change to `<button>` or add `tabindex="0"`, `role="button"`, keyboard event handlers
2. Add focus style: `tw:focus-visible:ring-2 tw:focus-visible:ring-[var(--accent1)] tw:focus-visible:ring-offset-2`

### Test

Tab through every page without mouse. Every interactive element: receives visible focus, activates on Enter/Space, logical tab order.

---

## Sweep 5: Screen Reader Basics

**Goal:** Every element communicates purpose to assistive technology.

### Find

```bash
# Icon-only buttons without labels
grep -rn "<button[^>]*>\s*<i " app/Views/ --include="*.blade.php" | grep -v "aria-label\|sr-only" | head -20

# Icon-only links
grep -rn "<a [^>]*>\s*<i " app/Views/ --include="*.blade.php" | grep -v "aria-label\|sr-only" | head -20

# Inputs without labels
grep -rn "<input " app/Views/ --include="*.blade.php" | grep -v "aria-label\|aria-labelledby" | head -20

# Images without alt
grep -rn "<img " app/Views/ --include="*.blade.php" | grep -v "alt=" | head -20
```

### Fix

| Pattern | Fix |
|---|---|
| Icon-only `<button>` | `aria-label="Action name"` |
| Icon-only `<a>` | `aria-label="Link purpose"` |
| Decorative icon next to text | `aria-hidden="true"` on icon |
| `<input>` without label | `<label for>` or `aria-label` |
| `<img>` without alt | `alt="Description"` or `alt=""` if decorative |
| Status dot/pill | `aria-label="Status: In Progress"` |

---

## Sweep 6: HTMX Live Regions

**Goal:** Dynamic content updates announced to screen readers.

### Find

```bash
grep -rn "hx-target\|hx-swap" app/Views/ --include="*.blade.php" | head -30
```

### Fix

For each HTMX target with user-facing content:
```html
<div id="target-id" aria-live="polite" aria-atomic="false">
```

For loading indicators:
```html
<div class="htmx-indicator" role="status">
  <span class="tw:sr-only">Loading...</span>
</div>
```

---

## Sweep 7: Reduced Motion

**Goal:** All animations respect `prefers-reduced-motion`.

### Find

```bash
grep -rn "@keyframes\|animation:\|transition:" app/Views/ app/Plugins/ \
  --include="*.css" --include="*.blade.php" | head -30
```

### Fix

Global reset (if not already present):
```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

Or per-component: `tw:motion-reduce:transition-none`

---

## Sweep 8: Color-Only Status Indicators

**Goal:** No status communicated by color alone.

### Find

```bash
grep -rn "dot-ok\|dot-wip\|dot-draft\|dot-flag\|bg-green\|bg-red\|bg-yellow" \
  app/Views/ --include="*.blade.php" | head -20
```

### Fix

Every colored status indicator needs a companion: icon inside, `aria-label`, or adjacent text. Verify pills already have text labels.

---

## Sweep 9: Touch Targets

**Goal:** All interactive elements meet 44×44px minimum.

### Find

```bash
grep -rn "padding:\s*[0-3]px\|tw:p-0\|tw:p-0.5\|tw:p-1" \
  app/Views/ --include="*.blade.php" | head -20
```

### Fix

- Minimum 44×44px clickable area
- Icon buttons: `tw:p-2.5` minimum
- Extend hit area with padding or `::before` pseudo-element if visual must stay small

---

## Sweep 10: Typography Migration

**Goal:** Replace Roboto with Hanken Grotesk. Normalize off-scale font sizes.

### Find

```bash
grep -rn "Roboto\|roboto" app/Views/ app/Plugins/ \
  --include="*.blade.php" --include="*.css" --include="*.js"

grep -rn "font-size:\s*15px\|font-size:\s*17px\|font-size:\s*19px" \
  app/Views/ --include="*.blade.php" --include="*.css"
```

### Fix

- Replace font-family declarations with Hanken Grotesk stack
- Snap off-scale sizes: 15px → 14px or 16px, 17px → 16px or 18px
- Normalize font-weights to scale: 450 (body), 500 (medium), 600 (semibold), 650 (heading), 700 (bold)

---

## Sweeps 11-15: Visual Normalization

### Sweep 11: Border Radius
3px → 4px, 5px → 6px, 7px → 8px, 10px → 10px (keep), 16px → 14px or 20px

### Sweep 12: Spacing
15px → 16px, 13px → 12px, 7px → 8px, 11px → 12px, 9px → 8px or 10px

### Sweep 13: Shadows
Replace custom `box-shadow` with `var(--shadow-sm/md/lg/xl)`. Remove `box-shadow` from `transition` properties.

### Sweep 14: Transitions
Replace `transition: all` with specific properties. Ensure durations match scale (150/200/300/350ms).

### Sweep 15: Legacy Pattern Catalog
Catalog jQuery usage, `.tpl.php` files, inline styles. Don't fix all at once — create prioritized backlog.

---

## Execution Rules

1. **One sweep per session.** Complete, test, report, then move to the next.
2. **Smallest possible changes.** Replace a value, don't rewrite the component.
3. **Preserve visual output.** Page looks identical after a token sweep in default theme. Only dark mode / custom theme behavior improves.
4. **When uncertain, flag.** Report unmapped colors. Don't guess.
5. **Test after every sweep.** Light mode default, dark mode, one custom accent. Three checks.
6. **Accessibility sweeps (4-9) are not optional.** They come before cosmetic sweeps. Users are locked out without them.
7. **Use the NEW palette.** `#004766` not `#1b75bb`. `#00B893` not `#81B1A8`. If you see old values in the codebase, those are what you're replacing.

---

## File Scan Targets

| Path | What | Sweep targets |
|---|---|---|
| `app/Views/Templates/` | Core Blade templates | All sweeps |
| `app/Views/Templates/components/` | Shared components | All sweeps |
| `app/Plugins/PgmPro/Views/` | Plugin views | All sweeps |
| `app/Plugins/StrategyPro/Views/` | Plugin views | All sweeps |
| `public/dist/css/` | Compiled CSS | Sweeps 1-3, 11-14 |
| `app/Domain/*/Templates/` | Domain templates | All sweeps |
| `resources/css/` | Source CSS | Sweeps 1-3 |
| `app/Core/UI/Theme.php` | Theme engine | Sweep 0 only |
| `public/theme/*/` | Theme configs | Sweep 0 only |

---

## Sweep Report Template

After each sweep, produce:

```markdown
## Sweep [N]: [Name]
**Files changed:** [count]
**Instances replaced:** [count]

### Changes
- [file]: [what changed] × [count]

### Flagged (needs design decision)
- [file:line]: [description of unmapped value]

### Test Results
- [ ] Light mode: looks identical to before
- [ ] Dark mode: no invisible text, missing borders, white rectangles
- [ ] Custom accent: no hardcoded colors remain
- [ ] No console errors
- [ ] No broken layouts
```
