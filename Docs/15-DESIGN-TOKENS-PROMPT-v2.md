# Design System Implementation — Claude Code Prompt v2

**For Claude Code Execution Against the Leantime Codebase**

| Field | Value |
|---|---|
| Project | Leantime |
| Scope | Codebase-wide token compliance, accessibility, theming |
| Reference | `docs/14-DESIGN-TOKENS.md` v2.0 — **Read this first, it's the source of truth** |
| Risk | LOW per sweep (small, testable changes) |
| Approach | One sweep at a time. Never batch multiple categories. |

---

## Pre-Read (Required)

Before writing any code or running any grep, read these in order:

1. `CLAUDE.md` — project conventions
2. `docs/14-DESIGN-TOKENS.md` — **The canonical token reference. Every decision flows from this.**
3. Skim `app/Views/Templates/components/` — understand Blade component layout
4. Skim one existing Blade component to confirm `tw:` prefix convention and CSS variable usage

---

## Brand Palette (Confirmed — Do Not Change)

| Token | Hex | Role |
|---|---|---|
| `--accent1` | `#004766` | Primary. Nav, buttons, links, active states |
| `--accent2` | `#00B893` | Secondary/success. Fills, icons, progress |
| `--accent2-text` | `#008F72` | Text-safe variant of accent2 (4.5:1 on white) |
| `--accent3` | `#CADE1B` | Tertiary. Backgrounds/fills ONLY |
| `--accent3-text` | `#A8B516` | Large text only (3.1:1) |
| `--accent4` | `#F61067` | Danger/alerts (4.6:1) |

**Old values being replaced:**
- `#1b75bb` → `var(--accent1)`
- `#81B1A8` → `var(--accent2)`
- `#059669` (old success) → `var(--accent2)`
- `#DC2626` (old error) → `var(--accent4)`
- `#D97706` (old warning) → `var(--accent3-text)`

---

## Critical Constraints

### DO

- Work one sweep at a time — complete it, test it, confirm before starting the next
- Use the exact token values from 14-DESIGN-TOKENS.md v2.0
- Use `tw:` prefix for all Tailwind classes
- Use CSS custom properties for all theme-layer colors
- Produce a summary report after each sweep (format in section 13.3 of tokens doc)
- Test in both light and dark mode after color changes
- Preserve existing visual appearance while normalizing the underlying values
- Add accessibility attributes (aria-label, roles, focus styles) as you encounter gaps
- Use `var(--color-bg-card)` for surface whites (toggle handles, avatar borders, etc.)
- Use `var(--color-text-on-accent)` for text on colored backgrounds

### DO NOT

- Change multiple categories in one sweep — colors and typography are separate passes
- Invent new tokens not in the reference document — flag them instead
- Remove existing functionality or change component behavior
- Touch theme definition files (those define the custom properties)
- Touch third-party CSS or JS
- Touch files being actively rewritten by other contributors
- Make cosmetic changes to pages scheduled for full redesign
- Use `accent2` (#00B893) as text on light backgrounds — use `accent2-text` (#008F72)
- Use `accent3` (#CADE1B) as text anywhere — use `accent3-text` (#A8B516) for large text only
- Allow any font size below 10px in the rendered output

---

## Sweep Execution Order

Sweeps are grouped by impact. Complete all sweeps in a group before moving to the next group.

### GROUP A — Accessibility (Blocks Users)

These come first because users are literally locked out without them.

---

### Sweep A1: Keyboard Accessibility

**Goal:** Every interactive element has a visible focus indicator and can be activated with keyboard.

#### Find

```bash
# Elements with click handlers but no keyboard support
grep -rn "@click\|onclick\|hx-get\|hx-post" \
  app/Views/ app/Plugins/ --include="*.blade.php" | grep -v "button\|<a " | head -40

# Check for focus-visible styles
grep -rn "focus-visible\|focus-ring\|:focus" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css" | wc -l

# Interactive elements total
grep -rn "<button\|<a \|<input\|<select\|<textarea" \
  app/Views/ app/Plugins/ --include="*.blade.php" | wc -l
```

#### Fix

For each interactive element:
1. If it's a `<div>` or `<span>` with a click handler, either:
   - Change to `<button>` (preferred) or `<a href>`
   - Or add `tabindex="0"`, `role="button"`, `@keydown.enter="..."`, `@keydown.space.prevent="..."`
2. Add focus style: `tw:focus-visible:ring-2 tw:focus-visible:ring-[var(--accent1)] tw:focus-visible:ring-offset-2`
3. Or use the global `var(--focus-ring)` box-shadow

**Specific patterns from known components:**

| Component | Current | Fix |
|---|---|---|
| Stageflow stages | `onclick` on div | Add `tabindex="0"`, `role="button"`, `@keydown.enter`, `@keydown.space.prevent` |
| Selectable cards | `onclick` on div | Add `role="radio"`, `tabindex="0"`, `aria-checked`, arrow key navigation |
| Demo tabs | Click only | Add `role="tablist"` on container, `role="tab"` on each, `aria-selected`, arrow keys |
| Inline edit triggers | Click only | Add `role="button"`, `tabindex="0"`, `aria-haspopup="listbox"` |
| Props toggle buttons | Click only | Ensure `aria-expanded`, `aria-controls` |
| Card action buttons | May be icon-only | Ensure `aria-label` present |

#### Test

Tab through every major page without touching the mouse. Every interactive element should receive visible focus, activate on Enter (and Space for buttons), and follow visual tab order.

---

### Sweep A2: Screen Reader Support

**Goal:** Every element communicates its purpose to assistive technology.

#### Find

```bash
# Icon-only buttons missing labels
grep -rn "<button[^>]*>\s*<i " app/Views/ --include="*.blade.php" | grep -v "aria-label\|sr-only" | head -20

# Icon-only links missing labels
grep -rn "<a [^>]*>\s*<i " app/Views/ --include="*.blade.php" | grep -v "aria-label\|sr-only" | head -20

# Decorative icons missing aria-hidden (icons next to text)
grep -rn "<i class=\"fa" app/Views/ --include="*.blade.php" | grep -v "aria-hidden\|sr-only\|aria-label" | head -20

# Inputs without labels
grep -rn "<input " app/Views/ --include="*.blade.php" | grep -v "aria-label\|aria-labelledby" | head -20

# Images without alt
grep -rn "<img " app/Views/ --include="*.blade.php" | grep -v "alt=" | head -20

# Progress bars without semantics
grep -rn "progress\|progressbar\|hd-fill\|ind-fill" app/Views/ --include="*.blade.php" | grep -v "role=\|aria-value" | head -20

# HTMX targets without aria-live
grep -rn "hx-target\|hx-swap" app/Views/ --include="*.blade.php" | head -30
```

#### Fix

| Pattern | Fix |
|---|---|
| Icon-only `<button>` | Add `aria-label="Action name"` |
| Icon-only `<a>` | Add `aria-label="Link purpose"` |
| Decorative icon next to text | Add `aria-hidden="true"` to the `<i>` element |
| `<input>` without label | Add `<label for="input-id">` or `aria-label` |
| `<img>` without alt | Add `alt="Description"` or `alt=""` if decorative |
| Status dot/pill | Add `aria-label="Status: In Progress"` |
| Progress bar divs | Add `role="progressbar" aria-valuenow="N" aria-valuemin="0" aria-valuemax="100"` |
| HTMX target containers | Add `aria-live="polite" aria-atomic="false"` |
| Loading indicators | Add `role="status"`, `<span class="tw:sr-only">Loading...</span>` |
| Mode toggles | Add `aria-live="polite"` status region announcing mode change |

#### Ensure sr-only utility exists

```css
.sr-only, .tw\:sr-only {
  position: absolute; width: 1px; height: 1px;
  padding: 0; margin: -1px; overflow: hidden;
  clip: rect(0,0,0,0); white-space: nowrap; border: 0;
}
```

---

### Sweep A3: Color-Only Indicators

**Goal:** No status is communicated by color alone (WCAG 1.4.1).

#### Find

```bash
# Status dots
grep -rn "dot-ok\|dot-wip\|dot-draft\|dot-flag\|st-dot\|sf-m-dot" \
  app/Views/ --include="*.blade.php"

# Colored borders as sole status
grep -rn "border-left.*color\|border-l-" app/Views/ --include="*.blade.php" | head -20

# Proportion bars / micro progress
grep -rn "proportion\|micro-progress\|demo-prop\|demo-micro" \
  app/Views/ --include="*.blade.php" | head -20

# Token compliance dots (component guide specific)
grep -rn "td-ok\|td-warn\|td-err" app/Views/ --include="*.blade.php" | head -20
```

#### Fix

Every colored indicator needs a companion:
- Status dot → add `aria-label="Status: [name]"` or adjacent text
- Pill → verify it has text label (most do)
- Proportion bar segments → add `role="img" aria-label="Done: 40%, Active: 25%..."` on container
- Border-left status → ensure nearby text states the status
- Token compliance dots → add text label or `aria-label`

---

### Sweep A4: Touch Targets

**Goal:** All interactive elements meet minimum touch target size from tokens doc §8.7.

#### Find

```bash
# Small buttons
grep -rn "btn-xs\|btn-sm\|padding:\s*[0-3]px" app/Views/ --include="*.blade.php" | head -20

# Small icon buttons
grep -rn "<button[^>]*class=\"[^\"]*\"[^>]*>\s*<i " app/Views/ --include="*.blade.php" | head -20

# Dropdown pills
grep -rn "dropdown-pill\|ddpill\|dropdownPill" app/Views/ --include="*.blade.php" | head -20

# Toggle switches
grep -rn "toggle\|switch" app/Views/ --include="*.blade.php" --include="*.css" | head -20
```

#### Fix

| Element | Current issue | Fix |
|---|---|---|
| Button XS | ~18px tall | Set min-height 24px, add `::before { content:''; position:absolute; inset:-10px; }` for 44px touch |
| Button SM | ~22px tall | Set min-height 28px, add `::before` extension to 44px |
| Dropdown items | ~21px tall | Set min-height 36px |
| Toggle switches | 20px track height | Wrap in 44×44px clickable container |
| Dropdown pills | ~18px tall | Set min-height 28px, add `::before` for mobile |
| Close buttons (×) | Often 14px visual | Extend hit area to 44×44px with `::before` |

**Touch extension pattern:**
```css
.touch-extend {
  position: relative;
}
.touch-extend::before {
  content: '';
  position: absolute;
  inset: -10px;
}
```

---

### GROUP B — Token Compliance (Blocks Theming)

These must be complete before custom themes or dark mode can ship reliably.

---

### Sweep B1: Hardcoded Brand Colors

**Goal:** Every instance of old brand hex values becomes a CSS variable.

#### Find

```bash
# Old primary blue
grep -rn "#1b75bb\|#1B75BB\|#1b75Bb" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css" --include="*.js"

# Old secondary teal
grep -rn "#81B1A8\|#81b1a8" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css" --include="*.js"

# Old success green (if separate from accent2)
grep -rn "#059669" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"

# Old error red
grep -rn "#DC2626\|#dc2626" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"

# Old warning amber
grep -rn "#D97706\|#d97706" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"

# New palette hex values that should be vars (anything in template, not theme definition)
grep -rn "#004766\|#00B893\|#CADE1B\|#F61067\|#A8B516\|#008F72\|#C84D7C" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"
```

#### Fix

| Found | Replace with |
|---|---|
| `#1b75bb` (any case) in CSS property | `var(--accent1)` |
| `#81B1A8` (any case) in CSS property | `var(--accent2)` |
| `#059669` in CSS property | `var(--accent2)` |
| `#DC2626` in CSS property | `var(--accent4)` |
| `#D97706` in CSS property | `var(--accent3-text)` |
| Tailwind `tw:text-[#1b75bb]` | `tw:text-[var(--accent1)]` |
| JS dynamic styling | `getComputedStyle(document.documentElement).getPropertyValue('--accent1')` |

#### Exclude

- Theme definition files where values ARE the defaults being set
- Comments or documentation
- SVG files with baked-in colors (flag for later asset pass)

#### Test

```javascript
document.documentElement.style.setProperty('--accent1', '#E65100');
document.documentElement.style.setProperty('--accent2', '#FFB74D');
// Scan every changed page — nothing should remain the old blue/teal
```

---

### Sweep B2: Hardcoded Neutral Colors

**Goal:** Background, text, and border colors that are hardcoded hex become CSS custom properties.

#### Find

```bash
# Hardcoded white backgrounds
grep -rn "background:\s*#[Ff][Ff][Ff]\|background-color:\s*#[Ff][Ff][Ff]\|background:\s*white" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"

# Hardcoded dark text
grep -rn "color:\s*#1[Aa]1[Aa]2[Ee]\|color:\s*#333\|color:\s*#222\|color:\s*#000" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"

# Hardcoded gray text
grep -rn "color:\s*#[4-9]" app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css" | head -40

# Hardcoded borders
grep -rn "border.*:\s*.*#[EeFf][0-9A-Fa-f]" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css" | head -40

# Surface whites (should be var(--color-bg-card), NOT white)
grep -rn "background:\s*white\|background:\s*#fff\|border.*solid\s*white\|border.*solid\s*#fff" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css" | head -20
```

#### Fix

| Hardcoded | Token |
|---|---|
| `#FFFFFF`, `white` (backgrounds) | `var(--color-bg-card)` |
| `#F5F5F5`, `#F8F9FB`, `#fafafa` (page bg) | `var(--color-bg-page)` |
| `#F0F1F3`, `#f0f0f0`, `#eee` (muted bg) | `var(--color-bg-muted)` |
| `#F3F4F6` (hover bg) | `var(--color-bg-hover)` |
| `#1A1A2E`, `#333`, `#222`, `#000` (dark text) | `var(--color-text-primary)` |
| `#4B5563`, `#555`, `#666` (body text) | `var(--color-text-secondary)` |
| `#9CA3AF`, `#999`, `#aaa` (muted text) | `var(--color-text-muted)` |
| `#D1D5DB`, `#ccc`, `#ddd` (disabled text) | `var(--color-text-disabled)` |
| `#E8ECF0`, `#e0e0e0`, `#ddd` (borders) | `var(--color-border-default)` |
| `#F0F1F3` (light borders) | `var(--color-border-light)` |
| `white` in toggle handles, avatar borders | `var(--color-bg-card)` |

**Important distinction:** Text on accent-colored backgrounds (button labels on accent1 bg, etc.) should use `var(--color-text-on-accent)` which IS white in both modes. Don't replace those with `var(--color-bg-card)`.

If a color doesn't map cleanly, flag it — don't guess.

#### Test

```javascript
document.documentElement.classList.add('theme-dark');
// Or: document.documentElement.setAttribute('data-mode', 'dark');
// Check: no white rectangles on dark background, no invisible text, borders visible
```

---

### Sweep B3: Dark Mode Verification

**Goal:** After B1 and B2, systematically verify every major page in dark mode.

#### Process

For each major template in `app/Views/`:
1. Load the page
2. Toggle dark mode
3. Check for:
   - Text invisible or near-invisible against background
   - Borders that disappear
   - Cards that blend into page background
   - Focus indicators not visible
   - Shadows invisible (should use `var(--shadow-*)`)
   - Images or icons assuming white background
   - Toggle handles, avatar stack borders still white instead of `var(--color-bg-card)`

#### Fix

- Add missing dark mode token usage
- Replace `var(--shadow-*)` for shadows not yet using variables
- Add `var(--color-bg-card)` background to transparent elements that rely on a white parent

---

### Sweep B4: Glass Treatment

**Goal:** Apply glass brutalist aesthetic to surface elements per theme.

#### Find

```bash
# Card and modal backgrounds
grep -rn "background.*var(--color-bg-card)\|background:\s*var(--bg-card)" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css" | head -30

# Dropdown backgrounds
grep -rn "dropdown\|popover\|flyout" \
  app/Views/Templates/components/ --include="*.blade.php" -l
```

#### Fix

For surface elements (cards, modals, dropdowns, popovers, nav sidebar):
```css
background: var(--glass-bg);
backdrop-filter: var(--glass-blur);
border: var(--glass-border);
/* Optional: */ box-shadow: var(--glass-inner-shadow);
```

**Do NOT apply glass to:** inputs, textareas, data tables, or any element where translucency would impair readability of text content.

**Glass degrades gracefully:** Focus theme gets reduced glass, High Contrast gets none. The CSS variables handle this automatically if the tokens are used.

---

### GROUP C — Visual Consistency (Polish)

---

### Sweep C1: Typography Normalization

**Goal:** Kill every sub-10px font size. Snap all sizes to the type scale.

#### Find

```bash
# Sub-10px font sizes (violations)
grep -rn "font-size:\s*[1-9]px\b" app/Views/ app/Plugins/ \
  --include="*.blade.php" --include="*.css" | grep -v "font-size:\s*1[0-9]px"

# 9px specifically
grep -rn "font-size:\s*9px" app/Views/ app/Plugins/ \
  --include="*.blade.php" --include="*.css"

# 8px and below
grep -rn "font-size:\s*[1-8]px\b" app/Views/ app/Plugins/ \
  --include="*.blade.php" --include="*.css"

# Off-scale sizes
grep -rn "font-size:\s*15px\|font-size:\s*11px\|font-size:\s*17px\|font-size:\s*19px" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"
```

#### Fix

| Found | Snap to |
|---|---|
| 7px, 8px, 9px | 10px (`--text-xs`) |
| 11px | 12px (`--text-sm`) |
| 15px | 14px (`--text-md`) or 16px (`--text-lg`) |
| 17px | 16px (`--text-lg`) or 18px (`--text-xl`) |
| 19px | 18px (`--text-xl`) |

**Font integration:** If Hanken Grotesk is not yet loaded, add to the base layout:
```html
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@100..900&display=swap" rel="stylesheet">
```

---

### Sweep C2: Border Radius Normalization

#### Fix

| Found | Snap to |
|---|---|
| 3px | 4px (`--radius-xs`) |
| 5px | 6px (`--radius-sm`) |
| 7px | 6px or 10px |
| 8px | 10px (`--radius`) |
| 11px, 12px | 10px or 14px (`--radius-lg`) |
| 15px, 16px | 14px or 20px (`--radius-pill`) |

---

### Sweep C3: Spacing Normalization

#### Fix

Snap all off-scale padding/margin/gap values to the 4px grid:
- 3px → 4px
- 5px → 4px
- 7px → 8px
- 9px → 8px
- 11px → 12px
- 13px → 12px
- 15px → 16px

---

### Sweep C4: Shadow Normalization

Replace custom shadow values with tokens. Remove `box-shadow` from `transition` properties.

---

### Sweep C5: Transition Normalization

Replace `transition: all` with specific properties. Ensure durations match scale (100/150/200/300/350ms).

---

### Sweep C6: Reduced Motion

**Goal:** Global `prefers-reduced-motion` reset is present.

#### Find

```bash
grep -rn "prefers-reduced-motion" app/Views/ app/Plugins/ \
  --include="*.css" --include="*.blade.php" | wc -l
```

#### Fix

If not present globally, add to base stylesheet:
```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

---

### GROUP D — Legacy Cleanup (Backlog)

These create a catalog, not immediate fixes. Sort by page traffic/importance.

---

### Sweep D1: Legacy Pattern Catalog

```bash
# jQuery usage
grep -rn "jQuery\|\\\$(" app/Views/ app/Plugins/ --include="*.blade.php" --include="*.js" | wc -l

# .tpl.php files still in use
find app/Views/ -name "*.tpl.php" | wc -l

# Inline styles
grep -rn "style=\"" app/Views/ --include="*.blade.php" | wc -l
```

Output: A ranked backlog of files to migrate, sorted by usage frequency and user-facing impact.

---

## Execution Rules

1. **One sweep per session.** Complete it, test it, produce the report, confirm before starting the next.
2. **Smallest possible changes.** Replace a color value, don't rewrite the component.
3. **Preserve visual output.** The page should look identical after a sweep (in the default light theme). Only dark mode / custom theme / accessibility behavior should improve.
4. **When uncertain, flag.** If a color doesn't map to a token, report it. Don't guess.
5. **Test after every sweep.** Default light, default dark, one custom accent. Keyboard tab-through. Five checks.
6. **Accessibility sweeps are not optional.** Group A comes before Group B for a reason — users are locked out without them.
7. **Font size floor is absolute.** If you find 9px text, it becomes 10px. No exceptions. No "but it's just a label."

---

## Quick Reference: File Types to Scan

| Path | What lives there |
|---|---|
| `app/Views/Templates/` | Core Blade templates |
| `app/Views/Templates/components/` | Shared Blade components (this is the system) |
| `app/Views/Templates/components/stageflow/` | Stageflow/Logic Model component |
| `app/Views/Templates/components/kanban/` | Kanban micro components |
| `app/Views/Templates/components/forms/` | Form components |
| `app/Views/Templates/components/elements/` | UI element components |
| `app/Views/Templates/components/feedback/` | Alerts, notifications |
| `app/Views/Templates/components/actions/` | Modals, confirm dialogs |
| `app/Plugins/PgmPro/Views/` | Plugin views |
| `app/Plugins/StrategyPro/Views/` | Plugin views |
| `public/dist/css/` | Compiled CSS |
| `resources/css/` | Source CSS (pre-build) |

---

## Reporting Template

After each sweep, produce this:

```markdown
## Sweep [ID]: [Name]
**Date:** YYYY-MM-DD
**Files checked:** N
**Issues found:** N (critical: N / minor: N / flagged: N)

### Critical
| File | Line | Before | After | Notes |
|---|---|---|---|---|

### Minor
| File | Line | Before | After | Notes |
|---|---|---|---|---|

### Flagged (needs design decision)
| File | Line | Issue | Question |
|---|---|---|---|

### Test Results
- [ ] Default light mode — visual match
- [ ] Default dark mode — no regressions
- [ ] Custom accent — all elements respond
- [ ] Keyboard navigation — focus visible on all changed elements
- [ ] 200% zoom — no overflow or clipping
```
