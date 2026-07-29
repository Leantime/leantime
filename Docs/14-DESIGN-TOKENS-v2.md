# Leantime Design Token Reference — v2.0

**Single Source of Truth for Visual Properties, Theming, Accessibility, and Component Sizing**

| Field | Value |
|---|---|
| Product | Leantime |
| Document | 14-DESIGN-TOKENS.md |
| Version | 2.0 |
| Date | February 24, 2026 |
| Author | Gloria Folaron |
| Status | Living Document |
| Replaces | v1.1 (Feb 23, 2026) — old palette (#1b75bb/#81B1A8) |
| Source of truth for | Every visual property in the codebase. Component guide, Claude Code prompts, and contributor docs all derive from this. |

---

## How to Use This Document

This document serves three purposes:

1. **Build reference:** When creating new components, match these tokens exactly.
2. **Audit target:** When normalizing existing components, compare against these tokens and fix deviations.
3. **Accessibility and theming gate:** Every component must pass the accessibility checks and theming rules defined here before shipping.

Every visual property should resolve to a token. Every interactive element should be keyboard-accessible. Every color should work in both light and dark mode. If you encounter something that doesn't meet these standards, it's either wrong (fix it) or a new decision is needed (flag it).

### Prefixes and Conventions

- **Tailwind classes use `tw:` prefix** — e.g., `tw:rounded-lg`, `tw:text-sm`
- **CSS custom properties** — defined in theme files and `:root`, prefixed with `--`
- **No jQuery** — all new interactivity via HTMX + Alpine.js + vanilla JS
- **Blade only** — no `.tpl.php` files in new code
- **Semantic HTML first** — use `<button>`, `<nav>`, `<main>`, `<dialog>`, `<details>` before reaching for ARIA

### What Changed in v2.0

| Area | v1.1 | v2.0 |
|---|---|---|
| Brand colors | 2 accents (#1b75bb, #81B1A8) | 4 accents (#004766, #00B893, #CADE1B, #F61067) |
| Semantic colors | Separate green/red/yellow | Brand colors ARE semantic colors |
| Font | System stack only | Hanken Grotesk primary, system fallback |
| Font weights | 400/600/700 | Variable 450–800 |
| Theme count | Light/dark | 3 themes × light/dark = 6 combos |
| Glass effects | Not specified | Full spec (backdrop-filter, translucency) |
| Component minimums | Touch targets mentioned | Enforced sizes with exact values |
| Radius scale | 4/8/12/20 | 4/6/10/14/20 |
| Stageflow colors | Data viz palette | Derived from brand accents |

---

## 1. Theming Architecture

### 1.1 Theme Layers

| Layer | What it controls | Customizable by | Storage |
|---|---|---|---|
| **Brand** | Accent colors (4), logo | Company admin | `zp_settings` + `.env` defaults |
| **Scheme** | Light/dark mode, neutral palette swaps | Individual user | User preference + `prefers-color-scheme` |
| **Theme** | Glass level, motion, density | Individual user | User preference |
| **Fixed** | Semantic mappings, spacing, type scale | Nobody (system-defined) | Hardcoded in token definitions |

**Rule:** Brand and Scheme layer tokens MUST use CSS custom properties. Fixed layer tokens CAN be hardcoded or use Tailwind utilities because they never change.

### 1.2 Three Themes

| Theme | Glass | Motion | Density | Font |
|---|---|---|---|---|
| **Leantime** | Full (`blur(8px)`, translucent surfaces) | Cubic-bezier transitions (200–350ms) | Standard | Hanken Grotesk variable |
| **Focus** | Reduced (`blur(4px)` or opaque) | Minimal, fast (100–150ms) | Standard | Hanken Grotesk, fewer weight variations |
| **High Contrast** | None (fully opaque) | `prefers-reduced-motion` default | Generous | Atkinson Hyperlegible option |

Each theme supports light + dark mode (6 total combinations).

### 1.3 CSS Custom Property Architecture

```css
:root {
  /* ── Brand layer (set by company admin, overridden at runtime) ── */
  --accent1: #004766;          /* Deep Teal — primary */
  --accent2: #00B893;          /* Vibrant Emerald — success/secondary */
  --accent3: #CADE1B;          /* Chartreuse — fills/badges ONLY */
  --accent3-text: #A8B516;     /* Chartreuse darkened — text on light bg (3.1:1 large) */
  --accent4: #F61067;          /* Hot Pink — danger/alerts (4.6:1) */

  /* Derived tints for backgrounds */
  --accent1-light: rgba(0, 71, 102, 0.06);    /* #E6EEF3 equivalent */
  --accent2-light: rgba(0, 184, 147, 0.08);   /* #E6F9F4 equivalent */
  --accent3-light: rgba(202, 222, 27, 0.10);  /* #F8FAE6 equivalent */
  --accent4-light: rgba(246, 16, 103, 0.06);  /* #FDE8F0 equivalent */

  /* Text-safe variant for accent2 (2.7:1 raw is too low for text) */
  --accent2-text: #008F72;     /* Darkened emerald for text on white (4.5:1) */

  /* ── Scheme layer (swap between light and dark) ── */
  --color-text-primary: #1A1A2E;
  --color-text-secondary: #4B5563;
  --color-text-muted: #9CA3AF;
  --color-text-disabled: #D1D5DB;
  --color-text-on-accent: #FFFFFF;

  --color-bg-page: #F5F5F5;
  --color-bg-card: #FFFFFF;
  --color-bg-muted: #F0F1F3;
  --color-bg-hover: #F3F4F6;

  --color-border-default: #E8ECF0;
  --color-border-light: #F0F1F3;

  /* ── Shadows (scheme — swap in dark mode) ── */
  --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
  --shadow-md: 0 4px 16px rgba(0,0,0,0.07);
  --shadow-lg: 0 8px 32px rgba(0,0,0,0.10);
  --shadow-xl: 0 14px 48px rgba(0,0,0,0.13);

  /* ── Focus ring ── */
  --focus-ring: 0 0 0 2px var(--color-bg-card), 0 0 0 4px var(--accent1);

  /* ── Glass treatment (theme layer) ── */
  --glass-bg: rgba(255, 255, 255, 0.85);
  --glass-blur: blur(8px);
  --glass-border: 1px solid rgba(255, 255, 255, 0.2);
  --glass-inner-shadow: inset 0 1px 0 rgba(255,255,255,0.1);

  /* ── Stageflow stage colors (derived from brand) ── */
  --s1: var(--accent1);                   /* Deep Teal */
  --s2: var(--accent2);                   /* Emerald */
  --s3: var(--accent3-text);              /* Chartreuse (text-safe) */
  --s4: #C84D7C;                          /* accent4 softened to 60% */
  --s5-start: var(--accent1);             /* Gradient start */
  --s5-end: var(--accent2);               /* Gradient end */

  --s1-bg: rgba(0, 71, 102, 0.06);
  --s2-bg: rgba(0, 184, 147, 0.08);
  --s3-bg: rgba(168, 181, 22, 0.08);
  --s4-bg: rgba(246, 16, 103, 0.05);
  --s5-bg: rgba(0, 71, 102, 0.04);
}

/* ── Dark mode ── */
[data-mode="dark"], .theme-dark {
  --color-text-primary: #E5E7EB;
  --color-text-secondary: #9CA3AF;
  --color-text-muted: #6B7280;
  --color-text-disabled: #4B5563;

  --color-bg-page: #111827;
  --color-bg-card: #1F2937;
  --color-bg-muted: #374151;
  --color-bg-hover: #374151;

  --color-border-default: #374151;
  --color-border-light: #1F2937;

  --shadow-sm: 0 1px 3px rgba(0,0,0,0.20);
  --shadow-md: 0 4px 16px rgba(0,0,0,0.30);
  --shadow-lg: 0 8px 32px rgba(0,0,0,0.35);
  --shadow-xl: 0 14px 48px rgba(0,0,0,0.40);

  --glass-bg: rgba(30, 30, 46, 0.85);
  --glass-border: 1px solid rgba(255, 255, 255, 0.08);
  --glass-inner-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
}

/* ── Focus theme (less glass) ── */
.theme-focus {
  --glass-bg: var(--color-bg-card);
  --glass-blur: blur(4px);
}

/* ── High Contrast theme (no glass) ── */
.theme-hc {
  --glass-bg: var(--color-bg-card);
  --glass-blur: none;
  --glass-border: 1px solid var(--color-border-default);
  --glass-inner-shadow: none;
}
```

### 1.4 Accent Color Contrast Safety

| Color | On white (#F5F5F5) | On dark (#1F2937) | Allowed uses |
|---|---|---|---|
| accent1 `#004766` | 9.2:1 ✅ | — | Text, fills, icons — anywhere |
| accent2 `#00B893` | 2.7:1 ❌ | 6.8:1 ✅ | Fills, icons, large decorative. **Never as text on light bg** |
| accent2-text `#008F72` | 4.5:1 ✅ | — | Text variant of accent2 when text is required |
| accent3 `#CADE1B` | 1.9:1 ❌ | 8.3:1 ✅ | Fills/badges ONLY on light. Excellent text on dark |
| accent3-text `#A8B516` | 3.1:1 ⚠️ | — | Large text only (≥18px or ≥14px bold) |
| accent4 `#F61067` | 4.6:1 ✅ | 4.1:1 ⚠️ | Text and fills on light. Large text on dark |

### 1.5 Theming Rules

1. **Never hardcode hex in templates** — always `var(--token)`
2. **Never use `accent2` as text on light backgrounds** — use `var(--accent2-text)` instead
3. **Never use `accent3` as text** — use `var(--accent3-text)` for large text only
4. **Test every change in**: default light, default dark, one custom accent
5. **Glass applies to surfaces** (cards, modals, dropdowns, nav) — NEVER inputs, text areas, or data tables
6. **White on accent backgrounds** — always `var(--color-text-on-accent)`, never hardcoded `#fff`
7. **Surface elements (toggle handles, emboss handles)** — use `var(--color-bg-card)` not `white`

---

## 2. Semantic Color Mapping

**Brand colors ARE semantic colors. No separate green/red/yellow system.**

| Semantic | Token | Light value | Usage |
|---|---|---|---|
| Success | `var(--accent2)` | `#00B893` | Completed, on track, validated |
| Danger / Error | `var(--accent4)` | `#F61067` | Blocked, overdue, critical, delete |
| Warning | `var(--accent3-text)` | `#A8B516` | At risk, attention needed (large text only) |
| Info | `var(--accent1)` | `#004766` | Informational, active, in progress |

**Background tints for alerts/badges:**

| Semantic | Background token | Value |
|---|---|---|
| Success bg | `var(--accent2-light)` | `rgba(0, 184, 147, 0.08)` |
| Danger bg | `var(--accent4-light)` | `rgba(246, 16, 103, 0.06)` |
| Warning bg | `var(--accent3-light)` | `rgba(202, 222, 27, 0.10)` |
| Info bg | `var(--accent1-light)` | `rgba(0, 71, 102, 0.06)` |

**Accessibility rule:** Semantic colors must NEVER be the only indicator. Always pair with icon AND text label (or `aria-label`).

### 2.1 Status Colors

| Status | Color token | Icon | aria-label pattern |
|---|---|---|---|
| Active / In Progress | `var(--accent1)` | `fa-spinner` or `fa-circle-dot` | "Status: In Progress" |
| Validated / Complete | `var(--accent2)` | `fa-check` | "Status: Complete" |
| Draft / Not Started | `var(--color-text-muted)` | `fa-circle` (outline) | "Status: Draft" |
| Flagged / Blocked | `var(--accent4)` | `fa-exclamation` or `fa-flag` | "Status: Blocked" |
| Warning / At Risk | `var(--accent3-text)` | `fa-exclamation-triangle` | "Status: At Risk" |

### 2.2 Data Visualization Colors (Fixed — Both Modes)

| Slot | Value | Notes |
|---|---|---|
| Viz 1 | `var(--accent1)` `#004766` | Primary series |
| Viz 2 | `var(--accent2)` `#00B893` | Secondary series |
| Viz 3 | `var(--accent3-text)` `#A8B516` | Tertiary series |
| Viz 4 | `var(--accent4)` `#F61067` | Quaternary series |
| Viz 5 | `var(--s4)` `#C84D7C` | Fifth series (softened pink) |

**Rule:** Never themeable. Always include text labels or patterns — don't rely on color alone.

---

## 3. Typography

### 3.1 Font Stack

**Primary:** Hanken Grotesk (variable, 100–900)
```css
font-family: 'Hanken Grotesk', -apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif;
```

**Load:**
```html
<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@100..900&display=swap" rel="stylesheet">
```

**User font picker options:**
- Hanken Grotesk (default)
- Atkinson Hyperlegible (accessibility)
- Shantell Sans (playful/handwritten)

### 3.2 Weight Scale

| Token | Weight | Usage |
|---|---|---|
| `--fw-body` | 450 | Body text |
| `--fw-medium` | 500 | Labels, UI text |
| `--fw-semi` | 600 | Subheadings, emphasized labels |
| `--fw-heading` | 650 | Headings |
| `--fw-bold` | 700 | Bold emphasis, numbers |
| `--fw-display` | 800 | Display/hero text |

### 3.3 Type Scale

| Token | Size | Weight | Line Height | Usage |
|---|---|---|---|---|
| `--text-2xl` | 24px | 800 | 1.2 | Hero, display numbers |
| `--text-xl` | 18px | 650 | 1.3 | Page titles, modal titles |
| `--text-lg` | 16px | 650 | 1.3 | Section headers, card titles |
| `--text-md` | 14px | 600 | 1.35 | Sub-sections, UI labels |
| `--text-base` | 13px | 450 | 1.5 | Body text, descriptions |
| `--text-sm` | 12px | 500 | 1.4 | Table cells, secondary info, captions |
| `--text-xs` | 10px | 500–600 | 1.3 | Badges, counters, uppercase labels |

### 3.4 Typography Accessibility

| Rule | Standard | Details |
|---|---|---|
| **Absolute floor** | **10px** | Nothing in the product renders below 10px. Ever. |
| Minimum body text | 13px | Primary readable content |
| Minimum interactive label | 12px | Anything the user reads to take action |
| Minimum decorative/metadata | 10px | Badges, timestamps, counters. Must not carry essential meaning alone. |
| Body line height | ≥ 1.5 | WCAG 1.4.12 |
| Paragraph spacing | ≥ 1.5× font size | Between paragraphs |
| Uppercase letter spacing | 0.4–0.6px | Uppercase is harder to read |
| Max line length | ~70–80 characters | Prevents eye-tracking fatigue |
| Text alignment | Left-align | Never justify |
| User font scaling | Must not break layout | Accommodate 200% browser zoom |

**Removed from v1.1:** The "Tiny 9px" token is eliminated. The old 9px was used for uppercase flags and labels — those now use 10px (`--text-xs`) with letter-spacing.

### 3.5 Typography Rules

- **Headings:** Never below 14px
- **Body:** Default 13px / 450 / 1.5
- **Bold vs. Semibold:** 700 for numbers and emphasis, 650 for headings, 600 for labels. Never 800+ except display.
- **Uppercase:** Only at `--text-xs` (10px), always with letter-spacing 0.4–0.6px
- **Color:** Always use tokens, never hardcoded hex
- **accent2 as text:** Use `var(--accent2-text)` not `var(--accent2)`

---

## 4. Spacing Scale

### 4.1 Base Scale (4px unit)

| Token | Value | Common usage |
|---|---|---|
| `--space-1` | 4px | Icon gaps, tight inline spacing |
| `--space-2` | 8px | Component internal gaps, small padding |
| `--space-3` | 12px | Standard padding, input padding |
| `--space-4` | 16px | Card body padding, section gaps |
| `--space-5` | 20px | Large gaps, modal padding |
| `--space-6` | 24px | Page-level spacing |
| `--space-8` | 32px | Major section breaks |

### 4.2 Snap Rule

No off-scale values. If a measurement doesn't match a token, snap to the nearest:
- 3px → 4px (`--space-1`)
- 5px → 4px or 8px (choose nearest)
- 7px → 8px (`--space-2`)
- 9px → 8px (`--space-2`)
- 10px → 8px or 12px (choose nearest)
- 11px → 12px (`--space-3`)
- 13px → 12px (`--space-3`)
- 15px → 16px (`--space-4`)

### 4.3 Layout Spacing

| Context | Value |
|---|---|
| Card body padding | `--space-4` (16px) |
| Card header padding | `--space-3` (12px) |
| List item padding | `--space-3` (12px) vertical, `--space-4` (16px) horizontal |
| Section gaps | `--space-3` (12px) |
| Component internal gap | `--space-2` (8px) |
| Page margin | `--space-6` (24px) minimum |

---

## 5. Border Radius

| Token | Value | Usage |
|---|---|---|
| `--radius-xs` | 4px | Inline badges, small chips |
| `--radius-sm` | 6px | Buttons, inputs, small cards |
| `--radius` | 10px | Cards, modals, containers |
| `--radius-lg` | 14px | Large cards, hero sections |
| `--radius-pill` | 20px | Pills, tags, toggle tracks |
| `--radius-full` | 9999px | Avatars, circular buttons |

---

## 6. Shadows

| Token | Value | Usage |
|---|---|---|
| `--shadow-sm` | `0 1px 3px rgba(0,0,0,0.04)` | Cards at rest |
| `--shadow-md` | `0 4px 16px rgba(0,0,0,0.07)` | Hover states, elevated cards |
| `--shadow-lg` | `0 8px 32px rgba(0,0,0,0.10)` | Dropdowns, popovers |
| `--shadow-xl` | `0 14px 48px rgba(0,0,0,0.13)` | Modals, active stageflow stage |

Dark mode shadows use higher opacity (see CSS architecture above).

**Rule:** Never include `box-shadow` in `transition` properties — it causes repaint on every frame.

---

## 7. Transitions and Motion

### 7.1 Duration Scale

| Token | Value | Usage |
|---|---|---|
| `--duration-fast` | 100ms | Color changes, opacity |
| `--duration-base` | 150ms | Most interactions |
| `--duration-moderate` | 200ms | Panel reveals, size changes |
| `--duration-slow` | 300ms | Complex animations, layout shifts |
| `--duration-enter` | 350ms | Major reveals (stageflow, modals) |

### 7.2 Easing

| Usage | Value |
|---|---|
| Standard | `cubic-bezier(0.25, 0.46, 0.45, 0.94)` |
| Enter | `cubic-bezier(0.0, 0.0, 0.2, 1.0)` |
| Exit | `cubic-bezier(0.4, 0.0, 1.0, 1.0)` |

### 7.3 Reduced Motion (Required)

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

**Rule:** This MUST be in the global stylesheet. Individual components do not need their own reduced motion handling if this is present.

### 7.4 What NOT to Animate

- No parallax
- No animated blur (static only)
- No auto-playing motion
- No bouncing/pulsing indicators
- No content shifts after page load

---

## 8. Accessibility Standards

### 8.1 Philosophy

Leantime is built for neurodiversity — ADHD, autism, dyslexia. Accessibility means reducing cognitive load, supporting focus, respecting processing speeds, accommodating reading patterns, and providing forgiveness for mistakes.

### 8.2 WCAG Compliance

| Standard | Level | Status |
|---|---|---|
| WCAG 2.1 Level AA | Required | All components |
| WCAG 2.1 Level AAA | Aspirational | Text contrast, enhanced focus |

### 8.3 Keyboard Navigation

Every interactive element must be keyboard-accessible:

| Element | Required behavior |
|---|---|
| Buttons | Focusable. `Enter` and `Space` activate. |
| Links | Focusable. `Enter` activates. |
| Dropdowns | Arrow keys navigate, `Enter` selects, `Escape` closes |
| Modals | Focus trapped inside, `Escape` closes, focus returns to trigger |
| Tabs / toggles | Arrow keys move between options, `Enter`/`Space` activates |
| Collapsible sections | `Enter`/`Space` toggles, `aria-expanded` communicated |
| Stageflow stages | Focusable via `tabindex="0"`, `Enter`/`Space` activates, `role="button"` |
| Selectable cards | `role="radio"` or `role="checkbox"`, arrow keys navigate, `aria-checked` |
| Inline edit triggers | `role="button"`, `Enter` opens editor |

**Focus indicator:**
```css
:focus-visible {
  outline: none;
  box-shadow: var(--focus-ring);
}
```
Use `focus-visible` not `focus`. Apply to ALL interactive elements, not just buttons.

### 8.4 Screen Reader Support

| Pattern | Implementation |
|---|---|
| Status indicators | `aria-label="Status: In Progress"` on dots/pills |
| Icon-only buttons | `aria-label="Close"` or `<span class="tw:sr-only">Close</span>` |
| Decorative icons next to text | `aria-hidden="true"` on the icon |
| Progress bars | `role="progressbar" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100"` |
| Expandable sections | `aria-expanded="true/false"` on trigger, `aria-controls="panel-id"` |
| Live updates (HTMX) | `aria-live="polite"` on dynamically updating containers |
| Data tables | `<th scope="col">` headers, `<caption>` for context |
| Form fields | `<label>` with `for` attribute, or `aria-label` / `aria-describedby` |
| Error messages | `aria-invalid="true"` on field, `role="alert"` on error text, linked via `aria-describedby` |
| Modals | `role="dialog" aria-modal="true" aria-labelledby="title-id"` |
| Loading states | `aria-busy="true"`, `role="status"` on spinner, `<span class="tw:sr-only">Loading...</span>` |
| Mode toggles | `aria-live="polite"` region announcing "Dark mode enabled" / "Light mode enabled" |
| Color-only segments | `role="img" aria-label="Done: 40%"` on each segment |

**Screen reader utility:**
```css
.sr-only, .tw\:sr-only {
  position: absolute; width: 1px; height: 1px;
  padding: 0; margin: -1px; overflow: hidden;
  clip: rect(0,0,0,0); white-space: nowrap; border: 0;
}
```

### 8.5 HTMX-Specific Accessibility

```html
<div id="content-area" aria-live="polite" aria-atomic="false">
  <!-- HTMX swaps here -->
</div>

<div class="htmx-indicator" role="status" aria-label="Loading">
  <span class="tw:sr-only">Loading...</span>
</div>
```

Every HTMX target with meaningful content gets `aria-live="polite"`. Use `aria-busy="true"` during swap transitions.

### 8.6 Color Contrast Requirements

| Context | Minimum ratio | WCAG criterion |
|---|---|---|
| Body text (13px+) on background | 4.5:1 | 1.4.3 AA |
| Large text (18px+ or 14px bold) | 3:1 | 1.4.3 AA |
| UI components and borders | 3:1 against adjacent | 1.4.11 AA |
| Focus indicators | 3:1 against background | 1.4.11 AA |
| Text on accent backgrounds | 4.5:1 | 1.4.3 AA |

Check in BOTH light and dark mode.

### 8.7 Touch Targets (Enforced Minimums)

| Context | Minimum rendered size | If visual is smaller |
|---|---|---|
| Primary actions (buttons) | 44×44px | — must be this size |
| Secondary actions (icon buttons) | 32×32px | Extend with `::before { inset: -6px }` to reach 44px |
| Dropdown menu items | 36px height minimum | — |
| List items / rows | 44px height | — |
| Close/dismiss buttons | 24×24px visual | Extend hit area to 44×44px with `::before` |
| Toggle switches | 36×20px visual | Wrapper must be 44×44px clickable area |
| Inline pills/chips | 28px height minimum | Extend with `::before` to 44px on mobile |

**Touch extension pattern:**
```css
.touch-extend {
  position: relative;
}
.touch-extend::before {
  content: '';
  position: absolute;
  inset: -10px; /* Extend clickable area beyond visual bounds */
}
```

### 8.8 Cognitive Accessibility (Neurodiversity-Specific)

| Principle | Implementation |
|---|---|
| Consistent patterns | Same action = same UI pattern everywhere |
| No time pressure | No auto-dismissing toasts. User dismisses manually. |
| Clear hierarchy | One primary action per view. Secondary actions visually subordinate. |
| Chunked information | Collapsible sections. Don't show everything at once. |
| Predictable navigation | Tab order follows visual order. Back button always works. |
| Forgiveness | Undo for destructive actions. Confirmation for irreversible ones. |
| Reduced noise | No decorative animations. Accent for focus, not decoration. |
| Working memory support | Show context inline. Breadcrumbs. "You are here" indicators. |
| Reading support | Left-aligned text. No justified. High line-height. Clear font hierarchy. |

---

## 9. Component Patterns

### 9.1 Buttons

| Property | Value |
|---|---|
| Primary bg | `var(--accent1)` + `inset 0 1px 0 rgba(255,255,255,0.15)` |
| Primary text | `var(--color-text-on-accent)` |
| Ghost bg | Glass or `var(--color-bg-card)` |
| Ghost border | `1px solid var(--color-border-default)` |
| Danger bg | `var(--accent4)` |
| Radius | `var(--radius-sm)` (6px) |
| Focus | `var(--focus-ring)` |
| Hover | `brightness(1.08)` or `var(--color-bg-hover)` for ghost |

**Sizes:**

| Size | Padding | Font | Min height | Touch target |
|---|---|---|---|---|
| XS | 4px 8px | `--text-xs` (10px) | 24px | Extend to 44px with `::before` |
| SM | 6px 12px | `--text-xs` (10px) | 28px | Extend to 44px with `::before` |
| Default | 8px 16px | `--text-sm` (12px) | 36px | Already meets 36px, extend for mobile |
| LG | 12px 24px | `--text-md` (14px) | 44px | Native 44px ✅ |

**States:** Hover darkens primary, hover adds bg-hover to ghost. Disabled: `opacity: 0.5; cursor: not-allowed;` + `aria-disabled="true"`. Loading: spinner replaces label, width stable, `aria-busy="true"`.

### 9.2 Cards

| Property | Value |
|---|---|
| Background | `var(--glass-bg)` with `backdrop-filter: var(--glass-blur)` |
| Border | `var(--glass-border)` |
| Radius | `var(--radius)` (10px) |
| Shadow resting | `var(--shadow-sm)` |
| Shadow hover | `var(--shadow-md)` |
| Padding body | `var(--space-4)` (16px) |
| Padding header | `var(--space-3)` (12px) |
| Header border | `1px solid var(--color-border-light)` |

If interactive: `tabindex="0"`, `role="button"` or `role="link"`, keyboard activation, focus ring.

### 9.3 Inputs

| Property | Value |
|---|---|
| Background | `rgba(255, 255, 255, 0.7)` (glass tint) |
| Border | `1px solid rgba(0, 71, 102, 0.12)` (accent1 at low opacity) |
| Radius | `var(--radius)` (10px) |
| Font size | `var(--text-base)` (13px) |
| Padding | `var(--space-2) var(--space-3)` (8px 12px) |
| Min height | 36px |
| Focus glow | `0 0 0 3px rgba(0, 184, 147, 0.2)` (accent2 glow) |
| Placeholder | `var(--color-text-disabled)` |
| Error border | `var(--accent4)` |
| Error text | `var(--accent4)`, linked via `aria-describedby` |

**Required:** Explicit `<label for="input-id">` for every input. No exceptions.

### 9.4 Pills / Badges

| Property | Value |
|---|---|
| Radius | `var(--radius-pill)` (20px) |
| Font | `--text-xs` (10px) / weight 600 |
| Padding | 2px 8px |
| Min height | 20px |
| Color | White on semantic background |
| A11y | `aria-label="Status: [value]"` |

### 9.5 Avatars

| Size name | Dimensions | Font size | Usage |
|---|---|---|---|
| XS | 24×24px | 10px | Compact lists, inline mentions |
| SM | 28×28px | 11px | Default in cards, comments |
| MD | 32×32px | 13px | Profile indicators |
| LG | 40×40px | 15px | Headers, profiles |
| XL | 48×48px | 18px | Profile pages, settings |

**Dropped:** 18px and 22px sizes from v1.1. Minimum is 24px.

| Property | Value |
|---|---|
| Radius | `var(--radius-full)` |
| Background | `linear-gradient(135deg, var(--accent1), var(--accent2))` |
| Text | `var(--color-text-on-accent)` |
| Font weight | 700 |
| Stack border | `2px solid var(--color-bg-card)` (NOT white) |
| Stack overlap | -6px margin |
| A11y | `aria-label="[Person name]"` or `title` |

### 9.6 Status Dots

| Context | Size | Accompanied by |
|---|---|---|
| Inline (in text/pills) | 6px | Adjacent text |
| Standalone | 8px | Text label or `aria-label` |
| Emphasis | 10px | Text label or `aria-label` |

**Minimum:** 6px. Nothing smaller.

### 9.7 Dropdowns / Popovers

| Property | Value |
|---|---|
| Background | `var(--glass-bg)` with `backdrop-filter: var(--glass-blur)` |
| Border | `1px solid var(--color-border-default)` |
| Radius | `var(--radius-sm)` (6px) |
| Shadow | `var(--shadow-lg)` |
| Item padding | `var(--space-2) var(--space-3)` (8px 12px) |
| Item min height | 36px |
| Item font | `var(--text-sm)` (12px) |
| Item hover | `var(--color-bg-hover)` |
| Keyboard | Arrow keys navigate, `Enter` selects, `Escape` closes |
| ARIA | `role="menu"`, `role="menuitem"` |

### 9.8 Modals

| Property | Value |
|---|---|
| Background | `var(--glass-bg)` with `backdrop-filter: var(--glass-blur)` |
| Radius | `var(--radius)` (10px) |
| Shadow | `var(--shadow-xl)` |
| Backdrop | `rgba(0,0,0,0.3)` light / `rgba(0,0,0,0.6)` dark |
| Padding | `var(--space-5)` (20px) |
| Title | `--text-xl` (18px) / weight 650 |
| ARIA | `role="dialog" aria-modal="true" aria-labelledby="title-id"` |
| Focus trap | Required |
| Escape | Required — closes modal |
| Return focus | Required — focus returns to trigger element |

### 9.9 Toggle Switches

| Property | Value |
|---|---|
| Track | 36×20px, `var(--radius-pill)` |
| Handle | 16×16px, `var(--radius-full)`, `var(--color-bg-card)` (NOT white) |
| Active | Track: `var(--accent1)` |
| Inactive | Track: `var(--color-text-disabled)` |
| Wrapper clickable area | 44×44px minimum (extend beyond visual) |
| A11y | `role="switch"`, `aria-checked`, keyboard `Space` to toggle |

### 9.10 Inline Edit Triggers (Dropdown Pills)

| Property | Value |
|---|---|
| Padding | `var(--space-1) var(--space-3)` (4px 12px) |
| Min height | 28px |
| Font | `--text-xs` (10px) / weight 500 |
| Border | `1px solid var(--color-border-default)` |
| Radius | `var(--radius-pill)` (20px) |
| Touch target | Extend to 44px with `::before` on mobile |
| A11y | `role="button"`, `aria-haspopup="listbox"`, `aria-expanded` |

### 9.11 Progress Bars

| Property | Value |
|---|---|
| Track height | 6px |
| Track bg | `var(--color-bg-muted)` (translucent for glass effect) |
| Track radius | 3px |
| Fill radius | 3px |
| Default fill | `linear-gradient(90deg, var(--accent1), var(--accent2))` |
| Danger fill | `var(--accent4)` gradient |
| A11y | `role="progressbar" aria-valuenow="N" aria-valuemin="0" aria-valuemax="100"` |

### 9.12 Icons

| Context | Size | Color |
|---|---|---|
| Navigation | 16px | `var(--color-text-muted)` inactive, `var(--color-text-on-accent)` active |
| In buttons | 12px | Match button text |
| In labels | 10px | `var(--color-text-muted)` or semantic |
| Standalone | 14–16px | `var(--color-text-muted)` |

Font Awesome 6. `fa` prefix. Solid default.

**Icons alone MUST have** `aria-label` or `<span class="tw:sr-only">`. Decorative icons next to text get `aria-hidden="true"`.

---

## 10. Stageflow Stage Colors

| Stage | Color token | Background token | Usage |
|---|---|---|---|
| s1 (Inputs) | `var(--s1)` = accent1 | `var(--s1-bg)` | Deep teal |
| s2 (Activities) | `var(--s2)` = accent2 | `var(--s2-bg)` | Emerald |
| s3 (Outputs) | `var(--s3)` = accent3-text | `var(--s3-bg)` | Chartreuse (text-safe) |
| s4 (Outcomes) | `var(--s4)` = #C84D7C | `var(--s4-bg)` | Hot pink softened |
| s5 (Impact) | Gradient `var(--s5-start)` → `var(--s5-end)` | `var(--s5-bg)` | Teal to emerald |

### Stageflow Component Sizing

| Element | Active state | Inactive state |
|---|---|---|
| Stage icon | 36×36px, `var(--radius)` | 28×28px, 7px radius |
| Stage name | `--text-lg` (16px) / weight 700 | `--text-sm` (12px) / weight 600 |
| Stage subtitle | `--text-sm` (12px) | `--text-xs` (10px) |
| Item title | `--text-base` (13px) / weight 600 | `--text-sm` (12px) / weight 500 |
| Item description | `--text-sm` (12px) | Hidden |
| Status dot | 8px | 6px |
| Item border-left | 3px solid stage color | transparent |

---

## 11. Emboss / Timeline Bar Patterns

### Bar Anatomy

1. **Progress fill:** Opaque gradient (`accent1 → accent2` sweep)
2. **Remaining track:** Translucent pill `rgba(255,255,255,0.08–0.12)` — allows background bleed-through
3. **Progress handle:** Small circle at fill boundary (draggable affordance), `var(--color-bg-card)` background
4. **Bar radius:** `var(--radius-pill)` (20px) — full pill shape

### Color Coding

| State | Fill |
|---|---|
| Standard | `accent1 → accent2` gradient |
| Flagged/overdue | `accent4` gradient |
| Ahead of schedule | `accent3` tint highlight |
| Complete | 100% fill, no handle |

### Dark Mode

Bars appear as "frosted glass floating over dark surface." Unfilled track: `rgba(255,255,255,0.08)`. Filled portion glows. Diamond milestone markers hold up in grayscale.

---

## 12. Z-Index Scale

| Layer | Value | Usage |
|---|---|---|
| Base | 0 | Default content |
| Sticky headers | 10 | Table headers, toolbar |
| Active stageflow stage | 10 | Elevated stage |
| Dropdowns | 20 | Menus, popovers |
| Modals | 30 | Dialog overlays |
| Toasts | 40 | Notification popups |
| AI trigger | 50 | Fixed position AI button |
| Global header | 100 | Top navigation bar |

---

## 13. Audit Methodology

### 13.1 Sweep Priority Order

Sweeps are grouped. Complete one group before moving to the next.

**Group A — Accessibility (blocks users)**
1. Keyboard accessibility (tabindex, roles, keydown handlers)
2. Screen reader support (aria-labels, live regions, sr-only)
3. Color-only indicators (add icons/text companions)
4. Touch targets (enforce minimums from §8.7)

**Group B — Token Compliance (blocks theming)**
5. Hardcoded brand colors → CSS variables (accent1–4)
6. Hardcoded neutrals → CSS variables (text, bg, border)
7. Dark mode verification
8. Glass treatment application

**Group C — Visual Consistency (polish)**
9. Typography normalization (kill sub-10px, snap to scale)
10. Border radius normalization
11. Spacing normalization
12. Shadow normalization
13. Transition normalization

**Group D — Legacy Cleanup**
14. jQuery → Alpine.js/HTMX migration catalog
15. `.tpl.php` → Blade migration catalog
16. Inline style elimination

### 13.2 Search Patterns

```bash
# ── Brand colors (old values) ──
grep -rn "#1b75bb\|#1B75BB\|#81B1A8\|#81b1a8" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"

# ── Brand colors (new values, should be vars not hex) ──
grep -rn "#004766\|#00B893\|#CADE1B\|#F61067\|#A8B516" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"

# ── Hardcoded white backgrounds ──
grep -rn "background:\s*#[Ff][Ff][Ff]\|background-color:\s*#[Ff][Ff][Ff]\|background:\s*white" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"

# ── Sub-10px font sizes (violations) ──
grep -rn "font-size:\s*[0-9]px\|font-size:\s*[7-9]px" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css"

# ── Interactive divs without keyboard support ──
grep -rn "@click\|onclick\|hx-get\|hx-post" \
  app/Views/ app/Plugins/ --include="*.blade.php" | grep -v "button\|<a " | head -20

# ── Icon-only buttons missing aria-label ──
grep -rn "<button[^>]*>\s*<i " app/Views/ --include="*.blade.php" | grep -v "aria-label\|sr-only"

# ── Missing focus-visible ──
grep -rn "focus-visible\|focus-ring\|:focus" \
  app/Views/ app/Plugins/ --include="*.blade.php" --include="*.css" | wc -l
```

### 13.3 Reporting Format

After each sweep:

```markdown
## Sweep: [Category Name]
### Files checked: N
### Issues found: N (critical / minor / flagged)

#### Critical (breaks theming, locks out users, or fails WCAG AA)
| File | Line | Issue | Fix |
|---|---|---|---|

#### Minor (cosmetic inconsistency)
| File | Line | Issue | Fix |
|---|---|---|---|

#### Flagged (needs design decision)
| File | Line | Issue | Question |
|---|---|---|---|
```

---

## 14. Migration Rules

### 14.1 Color Migration

| Priority | Old value | New value |
|---|---|---|
| 1 | `#1b75bb` (any case) | `var(--accent1)` |
| 2 | `#81B1A8` (any case) | `var(--accent2)` |
| 3 | Hardcoded `#059669` (success) | `var(--accent2)` |
| 4 | Hardcoded `#DC2626` (error) | `var(--accent4)` |
| 5 | Hardcoded `#D97706` (warning) | `var(--accent3-text)` |
| 6 | Hardcoded white backgrounds | `var(--color-bg-card)` or `var(--glass-bg)` |
| 7 | Hardcoded dark text | `var(--color-text-primary)` / `secondary` / `muted` |
| 8 | Hardcoded borders | `var(--color-border-default)` or `var(--color-border-light)` |

### 14.2 What NOT to Touch

- Theme definition files (where CSS variables ARE being set)
- Third-party CSS or JS
- Files being actively rewritten by other contributors
- SVG assets with baked-in colors (flag for later)
- Comments or documentation referencing color codes

---

## 15. Testing Requirements

### 15.1 After Every Sweep

1. **Default light mode** — page looks identical to before the sweep
2. **Default dark mode** — no invisible text, no white rectangles, borders visible
3. **Custom accent** — change accent1 to orange (`#E65100`), accent2 to gold (`#FFB74D`) via console:
   ```javascript
   document.documentElement.style.setProperty('--accent1', '#E65100');
   document.documentElement.style.setProperty('--accent2', '#FFB74D');
   ```
4. **Keyboard navigation** — tab through page, every interactive element receives visible focus
5. **200% browser zoom** — layout doesn't break, text doesn't overflow

### 15.2 Accessibility Testing

- Screen reader: VoiceOver (Mac) or NVDA (Windows) — read through one complete flow
- Keyboard-only: complete one full task without touching the mouse
- Color blindness: simulate protanopia (red-blind) and deuteranopia (green-blind) — verify status is still readable via icons/text
- Reduced motion: enable `prefers-reduced-motion` — verify no animations play

---

## 16. Changelog

### v2.0 — February 24, 2026
- **Brand palette:** Replaced 2-accent (#1b75bb, #81B1A8) with 4-accent (#004766, #00B893, #CADE1B, #F61067)
- **Added:** accent2-text (#008F72) for safe text usage of emerald
- **Added:** accent3, accent3-text, accent4 tokens with derived tints
- **Added:** Glass treatment specs (backdrop-filter, translucency levels)
- **Added:** 3-theme architecture (Leantime, Focus, High Contrast)
- **Font:** Hanken Grotesk variable font (replaces system-only stack)
- **Weights:** Variable scale 450–800 (replaces 400/600/700)
- **Typography floor:** 10px absolute minimum (eliminated 9px "Tiny" token)
- **Type scale:** Adjusted — 12px replaces 11px for `--text-sm`, 13px for `--text-base`
- **Radius:** Added 6px and 14px stops (4/6/10/14/20 scale)
- **Stageflow colors:** Derived from brand palette instead of separate viz colors
- **Component minimums:** Enforced sizes with exact height/padding values
- **Avatars:** Dropped 18px and 22px sizes, 24px minimum
- **Status dots:** 6px minimum enforced
- **Touch targets:** Full specification with extension patterns
- **Toggle switches:** Spec for wrapper sizing and `role="switch"`
- **Inline edit triggers:** Min 28px height, ARIA patterns
- **Audit sweeps:** Reordered — accessibility before visual consistency
- **Semantic colors:** Brand colors ARE semantic (no separate system)
- **Surface whites:** Distinguished text-on-accent (keep white) from surface elements (use `var(--color-bg-card)`)
- **Emboss patterns:** Timeline/Gantt bar specs added
