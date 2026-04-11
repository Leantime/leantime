# Leantime Design Token Reference & Audit Guide

**Canonical Visual Standards for Codebase Equalization**

| Field | Value |
|---|---|
| Product | Leantime |
| Document | Design Token Reference |
| Version | 2.0 |
| Date | February 23, 2026 |
| Author | Gloria Folaron |
| Status | Living Document |
| Purpose | Single source of truth for visual properties, theming architecture, and accessibility standards. Claude Code uses this to audit and normalize the codebase. |

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

---

## 1. Theming Architecture

### 1.1 Theme Layers

Leantime's visual system has three layers. Understanding which layer a token belongs to determines whether it's themeable.

| Layer | What it controls | Customizable by | Storage |
|---|---|---|---|
| **Brand** | Primary/secondary accent colors, logo | Company admin | `zp_settings` (companysettings.primarycolor, etc.) + `.env` defaults |
| **Scheme** | Light/dark mode, neutral palette swaps | Individual user | User preference + `prefers-color-scheme` media query |
| **Fixed** | Semantic colors, data viz, spacing, typography scale | Nobody (system-defined) | Hardcoded in token definitions |

**Rule:** Brand layer tokens MUST use CSS custom properties (`var(--accent1)`). Scheme layer tokens MUST use CSS custom properties that swap between light and dark. Fixed layer tokens CAN be hardcoded or use Tailwind utilities because they never change.

### 1.2 CSS Custom Property Architecture

```css
:root {
  /* ── Brand layer (set by company, overridden at runtime) ── */
  --accent1: #1b75bb;
  --accent2: #81B1A8;

  /* ── Scheme layer (swap between light and dark) ── */
  --color-text-primary: #1A1A2E;
  --color-text-secondary: #4B5563;
  --color-text-muted: #9CA3AF;
  --color-text-disabled: #D1D5DB;
  --color-text-on-accent: #FFFFFF;

  --color-bg-page: #F8F9FB;
  --color-bg-card: #FFFFFF;
  --color-bg-muted: #F0F1F3;
  --color-bg-hover: #F3F4F6;

  --color-border-default: #E8ECF0;
  --color-border-light: #F0F1F3;

  /* ── Semantic (fixed hue, lightness shifts in dark mode) ── */
  --color-success: #059669;
  --color-success-bg: #ECFDF5;
  --color-warning: #D97706;
  --color-warning-bg: #FEF3C7;
  --color-error: #DC2626;
  --color-error-bg: #FEE2E2;
  --color-info: var(--accent1);
  --color-info-bg: #EFF6FF;

  /* ── Shadows (scheme-aware) ── */
  --shadow-sm: 0 1px 3px rgba(0,0,0,0.04);
  --shadow-md: 0 4px 16px rgba(0,0,0,0.07);
  --shadow-lg: 0 8px 32px rgba(0,0,0,0.10);
  --shadow-xl: 0 14px 48px rgba(0,0,0,0.13);

  /* ── Focus ring (accessibility) ── */
  --focus-ring: 0 0 0 2px var(--color-bg-card), 0 0 0 4px var(--accent1);
}

/* ── Dark mode overrides ── */
@media (prefers-color-scheme: dark) {
  :root.theme-dark, :root {
    --color-text-primary: #E5E7EB;
    --color-text-secondary: #9CA3AF;
    --color-text-muted: #6B7280;
    --color-text-disabled: #4B5563;
    --color-text-on-accent: #FFFFFF;

    --color-bg-page: #111827;
    --color-bg-card: #1F2937;
    --color-bg-muted: #374151;
    --color-bg-hover: #374151;

    --color-border-default: #374151;
    --color-border-light: #1F2937;

    --color-success-bg: rgba(5, 150, 105, 0.15);
    --color-warning-bg: rgba(217, 119, 6, 0.15);
    --color-error-bg: rgba(220, 38, 38, 0.15);
    --color-info-bg: rgba(27, 117, 187, 0.15);

    --shadow-sm: 0 1px 3px rgba(0,0,0,0.2);
    --shadow-md: 0 4px 16px rgba(0,0,0,0.3);
    --shadow-lg: 0 8px 32px rgba(0,0,0,0.4);
    --shadow-xl: 0 14px 48px rgba(0,0,0,0.5);
  }
}
```

### 1.3 Theming Rules

| Rule | Rationale |
|---|---|
| Never hardcode `#1b75bb` or `#81B1A8` | Customers change these. Use `var(--accent1)` / `var(--accent2)`. |
| Never hardcode `#FFFFFF` for backgrounds | Use `var(--color-bg-card)`. Dark mode card is `#1F2937`. |
| Never hardcode `#1A1A2E` for text | Use `var(--color-text-primary)`. Dark mode text is `#E5E7EB`. |
| Semantic colors keep their hue | Green is always success. Only lightness shifts in dark mode. |
| Data viz colors are fixed | `#3E937A`, `#C09035`, `#8E6AAD` never change. Chosen for both-mode contrast. |
| Test every component in both modes | Toggle dark mode, verify nothing becomes invisible or unreadable. |
| Accent contrast safety | When a customer sets accent1 to yellow or light gray, text-on-accent must still work. |

### 1.4 Accent Color Contrast Safety

When accent colors are user-set, white text on the accent may fail contrast. Components that put text on accent backgrounds should:

1. Default to white text (`--color-text-on-accent`)
2. If the system detects a light accent color (perceived lightness > 60%), flip to dark text
3. The Theme service already has `setSchemeColors()` — extend it to compute a contrast-safe text color

---

## 2. Color System

### 2.1 Brand Colors (4-Accent System)

| Token | CSS Variable | Default | Themeable | Usage |
|---|---|---|---|---|
| Primary | `--accent1` | `hsla(199, 100%, 20%, 1)` | Yes (company admin) | Navigation, primary buttons, links, active states |
| Secondary | `--accent2` | `hsla(168, 100%, 33%, 1)` | Yes (company admin) | Accents, secondary actions, gradients |
| Tertiary | `--accent3` | `#CADE1B` | No (brand-fixed) | Highlight, energy, lime accent |
| Quaternary | `--accent4` | `#F61067` | No (brand-fixed) | Celebration, urgency, hot pink accent |

Each accent has associated tokens:

| Base | Text variant | On-accent text | Soft variant | Tint |
|---|---|---|---|---|
| `--accent1` | — | `--accent1-color` (#fff) | `--accent1-hover` | `--accent1-light` |
| `--accent2` | — | `--accent2-color` (#fff) | `--accent2-hover` | `--accent2-light` |
| `--accent3` | `--accent3-text` (#A8B516) | `--accent3-color` (#000) | — | `--accent3-light` |
| `--accent4` | `--accent4-soft` (#C84D7C) | `--accent4-color` (#fff) | — | `--accent4-light` |

### 2.2 Neutral Palette (Themeable — Scheme Layer)

All neutrals MUST use CSS custom properties. They swap between light and dark.

| Token | CSS Variable | Light | Dark | Usage |
|---|---|---|---|---|
| Text primary | `--color-text-primary` | `#1A1A2E` | `#E5E7EB` | Headings, strong labels |
| Text secondary | `--color-text-secondary` | `#4B5563` | `#9CA3AF` | Body text, descriptions |
| Text muted | `--color-text-muted` | `#9CA3AF` | `#6B7280` | Captions, labels, helpers |
| Text disabled | `--color-text-disabled` | `#D1D5DB` | `#4B5563` | Disabled state text |
| Text on accent | `--color-text-on-accent` | `#FFFFFF` | `#FFFFFF` | Text on accent backgrounds |
| Bg page | `--color-bg-page` | `#F8F9FB` | `#111827` | Page body background |
| Bg card | `--color-bg-card` | `#FFFFFF` | `#1F2937` | Cards, panels, modals |
| Bg muted | `--color-bg-muted` | `#F0F1F3` | `#374151` | Inactive states, tracks |
| Bg hover | `--color-bg-hover` | `#F3F4F6` | `#374151` | Hover states on rows |
| Border default | `--color-border-default` | `#E8ECF0` | `#374151` | Card borders, dividers |
| Border light | `--color-border-light` | `#F0F1F3` | `#1F2937` | Subtle dividers |

**Contrast requirements (both modes):**

| Pair | Minimum ratio | WCAG level |
|---|---|---|
| Text primary on Bg card | 7:1 | AAA |
| Text secondary on Bg card | 4.5:1 | AA |
| Text muted on Bg card | 3:1 | AA large text only |
| Text on accent on accent1 | 4.5:1 | AA |

### 2.3 Semantic Colors (Fixed Hue — Scheme Lightness Shifts)

| Token | CSS Variable | Light | Dark bg | Usage |
|---|---|---|---|---|
| Success | `--color-success` | `#059669` | `rgba(5,150,105,0.15)` | Completed, on track |
| Warning | `--color-warning` | `#D97706` | `rgba(217,119,6,0.15)` | At risk, attention needed |
| Error | `--color-error` | `#DC2626` | `rgba(220,38,38,0.15)` | Blocked, overdue, critical |
| Info | `--color-info` | `var(--accent1)` | `rgba(27,117,187,0.15)` | Informational highlights |

**Accessibility rule:** Semantic colors must NEVER be the only indicator. Always pair with an icon (checkmark, warning triangle, X) AND text label (or `aria-label` if icon-only). Shape or position differences help too — don't just swap color on identical elements.

### 2.4 Status Colors

| Status | Color | Icon | aria-label pattern |
|---|---|---|---|
| Active / In Progress | `#1B75BB` | `fa-spinner` or `fa-circle-dot` | "Status: In Progress" |
| Validated / Complete | `#059669` | `fa-check` | "Status: Complete" |
| Draft / Not Started | `#9CA3AF` | `fa-circle` (outline) | "Status: Draft" |
| Flagged / Blocked | `#DC2626` | `fa-exclamation` or `fa-flag` | "Status: Blocked" |

### 2.5 Data Visualization Colors (Fixed — Both Modes)

| Slot | Value | On white | On dark (#1F2937) |
|---|---|---|---|
| Viz 1 | `#3E937A` | 3.4:1 (use labels) | 4.1:1 |
| Viz 2 | `#C09035` | 2.8:1 (use labels) | 3.8:1 |
| Viz 3 | `#8E6AAD` | 3.1:1 (use labels) | 3.5:1 |
| Viz 4 | `#4A85B5` | 3.2:1 (use labels) | 4.0:1 |
| Viz 5 | `#2D7D5E` | 3.8:1 | 4.4:1 |

**Rule:** Never themeable. Always include text labels or patterns — don't rely on color alone.

---

## 3. Typography

### 3.1 Font Stack

```css
--primary-font-family: 'Hanken Grotesk', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
```

**Default: Hanken Grotesk** — Modern geometric sans-serif with excellent legibility at all sizes. Variable font (woff2, weights 100-900).

User-selectable alternatives:

| Key | Display Name | Notes |
|---|---|---|
| `hankengrotesk` | Hanken Grotesk | Default. Variable weight. Modern geometric. |
| `roboto` | Roboto | Legacy default. Good device readability. |
| `atkinson` | Atkinson Hyperlegible | Designed for low-vision readers. |
| `shantell` | Shantell Sans | Increased letter spacing, easier for crowded text. |

The Header composer resolves font keys to display names via `Theme::$fonts` lookup, ensuring @font-face declarations match the CSS `font-family` value.

### 3.2 Font Weight Scale

| Token | Value | Usage |
|---|---|---|
| `--font-weight-body` | 400 | Default body text |
| `--font-weight-medium` | 500 | Emphasized body, table cells |
| `--font-weight-semibold` | 600 | Labels, card titles, sub-headers |
| `--font-weight-heading` | 650 | Section headings (Hanken Grotesk optimal) |
| `--font-weight-bold` | 700 | Page titles, strong emphasis |
| `--font-weight-display` | 800 | Hero numbers, display text |

**Rule:** Always use `var(--font-weight-*)` tokens in component CSS. Never hardcode `font-weight: bold` or numeric values. Exception: `@font-face` declarations must use literal values per CSS spec.

### 3.2 Type Scale

| Token | Size | Weight | Line Height | Color token | Usage |
|---|---|---|---|---|---|
| Display | 22px | 700 | 1.2 | text-primary | Page titles, hero numbers |
| Heading L | 18px | 700 | 1.3 | text-primary | Section headers, modal titles |
| Heading M | 16px | 700 | 1.3 | text-primary | Card titles, panel headers |
| Heading S | 14px | 600-700 | 1.35 | text-primary | Sub-section headers |
| Body | 14px | 400 | 1.5 | text-secondary | Default body text |
| Body strong | 14px | 600 | 1.5 | text-primary | Row names, emphasized body |
| Small | 13px | 400-500 | 1.4 | text-secondary | Table cells, secondary info |
| Caption | 12px | 500-600 | 1.4 | text-primary or muted | Names, footers |
| Micro | 11px | 500 | 1.3 | text-muted | Labels, tags, metadata |
| Nano | 10px | 500-600 | 1.3 | text-muted | Badges, counters |
| Tiny | 9px | 600-700 | 1.2 | varies | Flags, uppercase labels |

### 3.3 Typography Accessibility

| Rule | Standard | Details |
|---|---|---|
| Minimum body text | 14px | Never below 14px for primary readable content |
| Minimum interactive label | 12px | Anything the user reads to take action |
| Minimum decorative text | 9px | Only for supplementary, non-essential labels |
| Body line height | >= 1.5 | WCAG 1.4.12 — improves readability for dyslexia |
| Paragraph spacing | >= 1.5x font size | Between paragraphs or distinct blocks |
| Uppercase letter spacing | 0.4-0.6px | Uppercase is harder to read — spacing helps |
| Max line length | ~70-80 characters | Prevents eye-tracking fatigue |
| Text alignment | Left-align | Never justify — uneven spacing is problematic for dyslexia |
| User font scaling | Must not break layout | Components should accommodate 200% browser zoom |

### 3.4 Typography Rules

- **Headings:** Never below 14px
- **Body:** Default 14px / 400 / 1.5
- **Bold vs. Semibold:** 700 for headings and number emphasis. 600 for names and labels. Never 800+
- **Uppercase:** Only tiny labels (9px), flags, badges. Always with letter-spacing
- **Color:** Always use tokens (`var(--color-text-primary)` etc.), never hardcoded hex
- **Consistency:** If "Small" is 13px, use 13px for all Small-level text — not 13px in one spot, 12px in another

---

## 4. Spacing Scale

### 4.1 Base Scale (4px unit)

| Token | Value | Tailwind | Usage |
|---|---|---|---|
| 0.5 | 2px | `tw:gap-0.5` | Hairline gaps, inline icon spacing |
| 1 | 4px | `tw:gap-1` | Tight gaps between related items |
| 1.5 | 6px | `tw:gap-1.5` | Pill gaps, compact lists |
| 2 | 8px | `tw:gap-2` | Default sibling gap |
| 2.5 | 10px | `tw:gap-2.5` | Compact padding |
| 3 | 12px | `tw:p-3` | Card inner padding (small) |
| 3.5 | 14px | `tw:p-3.5` | Card header padding |
| 4 | 16px | `tw:p-4` | Standard section gap |
| 5 | 20px | `tw:p-5` | Panel padding |
| 6 | 24px | `tw:p-6` | Page horizontal padding |
| 8 | 32px | `tw:p-8` | Page vertical padding |

### 4.2 Layout Spacing

| Context | Value |
|---|---|
| Page max-width | 960px content (some pages full-width) |
| Page padding vertical | 32px |
| Page padding horizontal | 24px |
| Section gap | 24px |
| Card gap | 8-12px |
| Card inner padding | 12-16px |
| List item padding | 8-12px vertical |

**Rule:** No arbitrary values (15px, 13px, 7px, 11px). Snap to the 4px scale.

---

## 5. Border Radius

| Token | Value | Tailwind | Usage |
|---|---|---|---|
| xs | 4px | `tw:rounded` | Swatches, tiny inline elements |
| sm | 6px | `tw:rounded-md` | Buttons, inputs, small cards |
| md | 8px | `tw:rounded-lg` | Containers, medium panels |
| lg | 12px | `tw:rounded-xl` | Cards, modals, major panels |
| full | 9999px | `tw:rounded-full` | Avatars, circular buttons |
| pill | 20px | `tw:rounded-[20px]` | Pill buttons, tags |

**Rule:** Don't use 3px, 5px, 7px, 10px, 16px. Five options cover everything.

---

## 6. Shadows

| Token | CSS Variable | Light | Dark | Usage |
|---|---|---|---|---|
| SM | `--shadow-sm` | `0 1px 3px rgba(0,0,0,0.04)` | `0 1px 3px rgba(0,0,0,0.2)` | Resting cards |
| MD | `--shadow-md` | `0 4px 16px rgba(0,0,0,0.07)` | `0 4px 16px rgba(0,0,0,0.3)` | Hover cards |
| LG | `--shadow-lg` | `0 8px 32px rgba(0,0,0,0.10)` | `0 8px 32px rgba(0,0,0,0.4)` | Floating panels |
| XL | `--shadow-xl` | `0 14px 48px rgba(0,0,0,0.13)` | `0 14px 48px rgba(0,0,0,0.5)` | Popovers |

Shadows MUST use CSS custom properties — dark mode needs stronger shadows. Don't invent custom values.

---

## 7. Transitions and Motion

### 7.1 Duration Scale

| Property | Duration | Easing | Usage |
|---|---|---|---|
| Color / background | 150ms | ease | Hover states, toggles |
| Transform / opacity | 200ms | ease | Scale, fade |
| Layout (height) | 300ms | ease | Collapsible sections |
| Complex entrance | 350ms | `cubic-bezier(0.25, 0.46, 0.45, 0.94)` | Panel entrances |

**Rule:** Never exceed 350ms. No `transition` on `box-shadow`. No `transition: all` in production — specify exact properties.

### 7.2 Reduced Motion (Required)

Every animation MUST respect `prefers-reduced-motion`:

```css
@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
```

In Tailwind: `tw:motion-reduce:transition-none` and `tw:motion-reduce:animate-none`.

- Animations for meaning (progress bar filling, status transition) should complete instantly — end state still visible
- Animations for delight (entrance wobbles, hover bounces) should be fully suppressed

---

## 8. Accessibility Standards

### 8.1 Leantime's Accessibility Philosophy

Leantime is built for neurodiversity — ADHD, autism, dyslexia. Accessibility means:

- **Reducing cognitive load** — consistent patterns so users learn once
- **Supporting focus** — clean hierarchy, minimal distractions, clear active states
- **Respecting processing speeds** — no time-limited interactions, no auto-dismissing messages
- **Accommodating reading patterns** — left-aligned, generous line height, clear font hierarchy
- **Forgiveness** — undo for destructive actions, confirmation for irreversible ones

### 8.2 WCAG Compliance

| Standard | Level | Status |
|---|---|---|
| WCAG 2.1 Level AA | Required | All new components |
| WCAG 2.1 Level AAA | Aspirational | Text contrast, enhanced focus |

### 8.3 Keyboard Navigation

Every interactive element must be keyboard-accessible:

| Element | Required behavior |
|---|---|
| Buttons | Focusable. `Enter` and `Space` activate. |
| Links | Focusable. `Enter` activates. |
| Dropdowns | Arrow keys navigate, `Enter` selects, `Escape` closes |
| Modals | Focus trapped inside, `Escape` closes, focus returns to trigger on close |
| Tabs / toggles | Arrow keys move between options, `Enter`/`Space` activates |
| Collapsible sections | `Enter`/`Space` toggles, `aria-expanded` communicated |
| Canvas cards | Focusable, `Enter` opens detail, arrow keys for list navigation |

**Focus indicator:**
```css
:focus-visible {
  outline: none;
  box-shadow: var(--focus-ring);
  /* Double ring: inner matches card bg, outer uses accent1 */
  /* Ensures visibility on any background in any theme */
}
```

Use `focus-visible` not `focus` — prevents rings on mouse clicks.

**Tab order:** Must follow visual order. Never `tabindex` > 0. Use `tabindex="0"` for custom interactive elements.

### 8.4 Screen Reader Support

| Pattern | Implementation |
|---|---|
| Status indicators | `aria-label="Status: In Progress"` on dots/pills |
| Icon-only buttons | `aria-label="Close"` or `<span class="tw:sr-only">Close</span>` |
| Progress bars | `role="progressbar" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100" aria-label="Enrollment progress"` |
| Expandable sections | `aria-expanded="true/false"` on trigger, `aria-controls="panel-id"` |
| Live updates (HTMX) | `aria-live="polite"` on dynamically updating containers |
| Data tables | `<th scope="col">` headers, `<caption>` for context |
| Form fields | `<label>` with `for` attribute, or `aria-label` / `aria-describedby` |
| Error messages | `aria-invalid="true"` on field, `role="alert"` on error text |
| Modals | `role="dialog" aria-modal="true" aria-labelledby="title-id"` |
| Loading states | `aria-busy="true"` during loads, `role="status"` on spinner |

### 8.5 HTMX-Specific Accessibility

HTMX swaps content dynamically. Screen readers need notification:

```html
<!-- Announce swapped content -->
<div id="content-area" aria-live="polite" aria-atomic="false">
  <!-- HTMX swaps here -->
</div>

<!-- Loading indicator -->
<div class="htmx-indicator" role="status" aria-label="Loading">
  <span class="tw:sr-only">Loading...</span>
</div>
```

**Rule:** Every HTMX target with meaningful content gets `aria-live="polite"`. Use `aria-busy="true"` during swap transitions.

### 8.6 Color Contrast Requirements

| Context | Minimum ratio | WCAG criterion |
|---|---|---|
| Body text (14px) on background | 4.5:1 | 1.4.3 AA |
| Large text (18px+ or 14px bold) | 3:1 | 1.4.3 AA |
| UI components and borders | 3:1 against adjacent | 1.4.11 AA |
| Focus indicators | 3:1 against background | 1.4.11 AA |
| Text on accent backgrounds | 4.5:1 | 1.4.3 AA |

Check in BOTH light and dark mode. Use Chrome DevTools contrast checker.

### 8.7 Touch Targets

| Context | Minimum size |
|---|---|
| Primary actions (buttons) | 44x44px |
| Secondary actions (icon buttons) | 32x32px |
| List items / rows | 44px height |
| Close / dismiss buttons | 44x44px hit area (expand beyond visual icon) |

### 8.8 Cognitive Accessibility (Neurodiversity-Specific)

| Principle | Implementation |
|---|---|
| **Consistent patterns** | Same action = same UI pattern everywhere |
| **No time pressure** | No auto-dismissing toasts. User dismisses manually or persists. |
| **Clear hierarchy** | One primary action per view. Secondary actions visually subordinate. |
| **Chunked information** | Collapsible sections. Don't show everything at once. |
| **Predictable navigation** | Tab order follows visual order. Back button always works. |
| **Forgiveness** | Undo for destructive actions. Confirmation for irreversible ones. |
| **Reduced noise** | No decorative animations. Muted palette. Accent for focus, not decoration. |
| **Working memory support** | Show context inline. Breadcrumbs. "You are here" indicators. Don't make users remember from a previous screen. |
| **Reading support** | Left-aligned text. No justified. High line-height. Clear font hierarchy. |

---

## 9. Component Patterns

### 9.1 Buttons

| Variant | Background | Text | Border | Radius | Padding |
|---|---|---|---|---|---|
| Primary | `var(--accent1)` | `var(--color-text-on-accent)` | none | pill (20px) | 5px 12-16px |
| Secondary | `var(--color-bg-card)` | `var(--color-text-secondary)` | 1px `var(--color-border-default)` | pill (20px) | 5px 12-16px |
| Destructive | `var(--color-error)` | white | none | pill (20px) | 5px 12-16px |
| Ghost | transparent | `var(--accent1)` | none | pill (20px) | 5px 12px |

Font: 11-12px / weight 500-600. Min touch target: 44x44px.

**States:** Hover darkens primary, hover adds bg-hover to secondary. Disabled: `opacity: 0.5; cursor: not-allowed;` + `aria-disabled="true"`. Loading: spinner replaces label, width stable, `aria-busy="true"`.

### 9.2 Cards

| Property | Value |
|---|---|
| Background | `var(--color-bg-card)` |
| Border | `1px var(--color-border-default)` |
| Border radius | 12px (lg) |
| Shadow resting | `var(--shadow-sm)` |
| Shadow hover | `var(--shadow-md)` |
| Padding | 12-16px |

**If interactive:** `tabindex="0"`, `role="button"` or `role="link"`, keyboard activation, focus ring.

### 9.3 Inputs

| Property | Value |
|---|---|
| Background | `var(--color-bg-card)` |
| Border | `1px var(--color-border-default)` |
| Border radius | 6px (sm) |
| Font size | 14px |
| Padding | 8px 12px |
| Focus | `var(--focus-ring)` |
| Placeholder | `var(--color-text-muted)` |
| Error border | `var(--color-error)` |
| Error text | `var(--color-error)`, linked via `aria-describedby` |

**Required:** Explicit `<label>` for every input.

### 9.4 Pills / Badges

| Property | Value |
|---|---|
| Radius | 20px (pill) |
| Font | 9-10px / 600-700 |
| Padding | 1-2px 7-8px |
| Color | White on semantic background |
| A11y | `aria-label` with full status text |

### 9.5 Avatars

| Property | Value |
|---|---|
| Sizes | 18px (compact), 28px (default), 36px (large) |
| Radius | full |
| Font | 32% of diameter / 700 |
| Border | 2px `var(--color-bg-card)` when stacked |
| A11y | `aria-label="[Person name]"` or `title` |

### 9.6 Dropdowns / Popovers

| Property | Value |
|---|---|
| Background | `var(--color-bg-card)` |
| Border | `1px var(--color-border-default)` |
| Radius | 8px (md) |
| Shadow | `var(--shadow-xl)` |
| Item padding | 7px 12px |
| Item font | 12px |
| Item hover | `var(--color-bg-hover)` |
| Keyboard | Arrow keys, Enter selects, Escape closes |
| ARIA | `role="menu"`, `role="menuitem"` |

### 9.7 Modals

| Property | Value |
|---|---|
| Background | `var(--color-bg-card)` |
| Radius | 12px (lg) |
| Shadow | `var(--shadow-lg)` |
| Backdrop | `rgba(0,0,0,0.3)` light / `rgba(0,0,0,0.6)` dark |
| Padding | 20-24px |
| Title | 18px / 700 |
| ARIA | `role="dialog" aria-modal="true" aria-labelledby="title-id"` |
| Focus trap | Required |
| Escape | Required — closes modal |
| Return focus | Required — focus returns to trigger element |

### 9.8 Icons

| Context | Size | Color |
|---|---|---|
| Navigation | 16px | `var(--color-text-muted)` inactive, white active |
| In buttons | 11-12px | Match button text |
| In labels | 8-10px | `var(--color-text-muted)` or semantic |
| Standalone | 14-16px | `var(--color-text-muted)` |

Font Awesome 6. `fa` prefix. Solid default.

**Icons alone MUST have** `aria-label` or `<span class="tw:sr-only">`.

---

## 10. Z-Index Scale

| Layer | Value | Usage |
|---|---|---|
| Base | 1 | Normal flow |
| Floating | 5 | Health badges, overlays |
| Active/focused | 10 | Active stage, focused card |
| Dropdowns | 30-40 | Menus, popovers |
| Sticky | 50 | Fixed headers |
| Modal | 100 | Modal overlays |

**Rule:** No `z-index: 999` or `z-index: 9999`.

---

## 11. Audit Methodology

### 11.1 Sweep Priority Order

Sweeps are ordered by impact — theming and accessibility first, cosmetic polish last.

| # | Category | What to check | Why this order |
|---|---|---|---|
| 1 | **Hardcoded brand colors** | `#1b75bb`, `#81B1A8` not using `var(--accent1)` / `var(--accent2)` | Customer theming breaks |
| 2 | **Hardcoded neutral colors** | `#FFFFFF`, `#1A1A2E`, `#F8F9FB` not using CSS vars | Dark mode breaks |
| 3 | **Dark mode breakage** | Components invisible or unreadable in dark mode | Entire mode unusable |
| 4 | **Keyboard accessibility** | Interactive elements without focus indicators or keyboard activation | Some users locked out |
| 5 | **Screen reader gaps** | Missing `aria-label`, missing `<label>`, icon-only buttons without text | Screen reader users blocked |
| 6 | **HTMX live regions** | Dynamic content areas missing `aria-live` | Updates invisible to assistive tech |
| 7 | **Reduced motion** | Animations not respecting `prefers-reduced-motion` | Motion-sensitive users affected |
| 8 | **Color-only indicators** | Status shown only by color without icon or text | Colorblind users can't distinguish |
| 9 | **Touch targets** | Buttons/links smaller than 44x44px | Mobile and motor-impaired users affected |
| 10 | **Typography mismatches** | Off-scale font sizes, missing line-height, justified text | Readability/dyslexia issues |
| 11 | **Border radius** | Off-scale values (3px, 5px, 7px, 10px) | Visual inconsistency |
| 12 | **Spacing** | Arbitrary padding/margin (15px, 13px, 7px) | Visual inconsistency |
| 13 | **Shadows** | Custom values, missing dark mode shadow vars | Visual inconsistency |
| 14 | **Transitions** | `transition: all`, box-shadow transitions, missing reduced-motion | Performance and a11y |
| 15 | **Legacy patterns** | jQuery, `.tpl.php`, inline styles | Tech debt |

### 11.2 Search Patterns

```bash
# ═══ THEMING VIOLATIONS ═══

# Hardcoded brand colors (should be var(--accent1) / var(--accent2))
grep -rn "#1b75bb\|#1B75BB\|#81B1A8\|#81b1a8" app/Views/ app/Plugins/

# Hardcoded white backgrounds (should be var(--color-bg-card))
grep -rn "background:\s*#[Ff][Ff][Ff]\|background-color:\s*#[Ff][Ff][Ff]\|bg-white" app/Views/ --include="*.blade.php"

# Hardcoded text colors (should be var(--color-text-*))
grep -rn "color:\s*#1A1A2E\|color:\s*#4B5563\|color:\s*#9CA3AF" app/Views/ --include="*.blade.php"

# Hardcoded border colors
grep -rn "border.*#[EeFf][0-9A-Fa-f]" app/Views/ --include="*.blade.php" | head -30

# ═══ ACCESSIBILITY VIOLATIONS ═══

# Icon-only buttons without aria-label
grep -rn "<button[^>]*>[^<]*<i " app/Views/ --include="*.blade.php" | grep -v "aria-label\|sr-only" | head -20

# Inputs without labels
grep -rn "<input " app/Views/ --include="*.blade.php" | grep -v "aria-label\|id=" | head -20

# Images without alt text
grep -rn "<img " app/Views/ --include="*.blade.php" | grep -v "alt=" | head -20

# Missing aria-expanded on toggleable elements
grep -rn "x-show\|x-collapse\|collapse" app/Views/ --include="*.blade.php" | grep -v "aria-expanded" | head -20

# HTMX targets without aria-live
grep -rn "hx-target\|hx-swap" app/Views/ --include="*.blade.php" | head -20
# Then check if those targets have aria-live

# Missing focus-visible styles
grep -rn ":focus[^-]" app/Views/ --include="*.css" --include="*.blade.php" | grep -v "focus-visible" | head -20

# Animations without reduced-motion respect
grep -rn "@keyframes\|animation:" app/Views/ --include="*.css" --include="*.blade.php" | head -20
# Then check for prefers-reduced-motion nearby

# Color-only status (dots without icons or labels)
grep -rn "dot-ok\|dot-wip\|dot-flag\|dot-draft" app/Views/ --include="*.blade.php" | grep -v "aria-label\|sr-only" | head -20

# ═══ VISUAL CONSISTENCY ═══

# Off-scale border-radius
grep -rn "border-radius:\s*[357]px\|border-radius:\s*10px\|border-radius:\s*16px" app/Views/

# Off-scale font sizes
grep -rn "font-size:\s*15px\|font-size:\s*17px\|font-size:\s*19px" app/Views/

# Arbitrary z-index
grep -rn "z-index:\s*[0-9][0-9][0-9]" app/Views/

# Box-shadow transitions (performance)
grep -rn "transition.*box-shadow\|transition.*shadow" app/Views/

# transition: all (over-broad)
grep -rn "transition:\s*all\|transition-property:\s*all" app/Views/

# ═══ LEGACY PATTERNS ═══

# jQuery usage
grep -rn "\$(\|jQuery\|\.click(\|\.on(" app/Views/ app/Plugins/ --include="*.blade.php" --include="*.js" | head -20

# Old template files
find app/Views/ app/Plugins/ -name "*.tpl.php" | head -20

# Inline styles that should be classes
grep -rn "style=\".*font-size\|style=\".*padding\|style=\".*margin\|style=\".*color:" app/Views/ --include="*.blade.php" | head -30
```

### 11.3 Reporting Format

For each sweep, produce:

```
## Sweep: [Category Name]
### Files checked: N
### Issues found: N (critical / minor / flagged)

#### Critical (breaks theming, locks out users, or fails WCAG AA)
- file.blade.php:42 — `#1b75bb` hardcoded, should be `var(--accent1)`
- file.blade.php:67 — <button> with icon only, no aria-label
- file.blade.php:90 — dropdown has no keyboard navigation

#### Minor (cosmetic inconsistency or best-practice gap)
- file.blade.php:120 — padding: 15px, should be 16px (tw:p-4)
- file.blade.php:135 — border-radius: 10px, should be 12px (lg)

#### Flagged (needs design decision)
- file.blade.php:200 — uses #555555, not in palette. Closest: #4B5563. Confirm?
- file.blade.php:215 — animation has no reduced-motion alternative. Suppress or instant?
```

### 11.4 Per-File Checklist

When touching ANY Blade template for any reason, check:

- [ ] All colors use CSS custom properties or tokens (no hardcoded brand/neutral colors)
- [ ] All interactive elements have visible focus indicators (`:focus-visible`)
- [ ] All interactive elements are keyboard accessible
- [ ] All icon-only elements have `aria-label` or `sr-only` text
- [ ] All form inputs have associated `<label>` elements
- [ ] All status indicators pair color with icon/text
- [ ] All HTMX target containers have `aria-live="polite"` if they hold user-facing content
- [ ] All animations respect `prefers-reduced-motion`
- [ ] Font sizes, spacing, radii match the token scale
- [ ] Component looks correct in both light and dark mode

---

## 12. Migration Rules

### 12.1 Color Migration Priority

When migrating colors, follow this priority:

| Current state | Migration target | Priority |
|---|---|---|
| Hardcoded `#1b75bb` / `#81B1A8` | `var(--accent1)` / `var(--accent2)` | **Critical** — theming broken |
| Hardcoded `#FFFFFF` for bg | `var(--color-bg-card)` | **Critical** — dark mode broken |
| Hardcoded `#1A1A2E` for text | `var(--color-text-primary)` | **Critical** — dark mode broken |
| Hardcoded neutral hex | Nearest `var(--color-*)` token | **High** — dark mode inconsistent |
| Hardcoded semantic color | `var(--color-success)` etc. | **Medium** — functional but fragile |
| Off-palette color entirely | Flag for decision | **Low** — needs design input |

### 12.2 CSS to Tailwind Migration

Opportunistic — when touching a file for other reasons, migrate where straightforward:

| CSS | Tailwind (with prefix) |
|---|---|
| `font-size: 14px; font-weight: 600;` | `tw:text-sm tw:font-semibold` |
| `padding: 12px 16px;` | `tw:px-4 tw:py-3` |
| `border-radius: 12px;` | `tw:rounded-xl` |
| `display: flex; gap: 8px;` | `tw:flex tw:gap-2` |

### 12.3 Legacy to Modern Patterns

| Legacy | Modern | Notes |
|---|---|---|
| jQuery event handlers | Alpine.js `@click` or HTMX `hx-get` | |
| `.tpl.php` templates | `.blade.php` with `@props`, `@section` | |
| Inline `<style>` blocks | Tailwind utilities | |
| Custom CSS animations | Tailwind transitions + `motion-reduce:` variants | Ensures reduced-motion respect |
| `z-index: 9999` | Token from z-index scale | |
| `:focus` styles | `:focus-visible` with `var(--focus-ring)` | Prevents unwanted mouse focus rings |
| Auto-dismissing toasts | Persistent until user dismisses | Cognitive accessibility |

### 12.4 What NOT to Touch

- Theme CSS files that define custom properties (those are the source)
- Third-party library CSS
- Print stylesheets
- Legacy pages actively being rewritten (avoid merge conflicts)

---

## 13. Testing Requirements

### 13.1 Visual Testing

Every component should be verified in:

| Condition | What to check |
|---|---|
| Light mode, default accent | Baseline appearance |
| Dark mode, default accent | Colors swap, contrast maintained |
| Light mode, custom accent (e.g., orange `#E65100`) | Brand color applied, text readable |
| Dark mode, custom accent | Brand color applied, text readable |
| Light mode, light accent (e.g., `#FFD54F`) | Text-on-accent contrast — may need dark text |
| 200% browser zoom | Layout doesn't break, text doesn't overflow |
| 320px viewport width | Mobile layout functional |
| Reduced motion preference | Animations suppressed or instant |
| High contrast mode (Windows) | Elements remain distinguishable |

### 13.2 Accessibility Testing

| Tool | What it catches |
|---|---|
| Chrome DevTools Accessibility panel | Missing labels, roles, ARIA issues |
| axe DevTools extension | Automated WCAG violations |
| Tab key walkthrough | Missing focus indicators, broken tab order |
| Screen reader (VoiceOver / NVDA) | Missing announcements, confusing flow |
| Keyboard-only navigation | Unreachable interactive elements |
| Chrome `prefers-reduced-motion` emulation | Animations not respecting preference |

### 13.3 Theming Testing

```bash
# Quick theme test: temporarily set accent colors in browser console
document.documentElement.style.setProperty('--accent1', '#E65100');
document.documentElement.style.setProperty('--accent2', '#FFB74D');

# Toggle dark mode
document.documentElement.classList.toggle('theme-dark');

# Test light accent (contrast concern)
document.documentElement.style.setProperty('--accent1', '#FFD54F');
```

---

## 14. Changelog

| Date | Change | Author |
|---|---|---|
| 2026-02-23 | v1.0 — initial token definitions | GF |
| 2026-02-23 | v1.1 — integrated theming architecture, accessibility standards, cognitive accessibility, dark mode patterns, audit methodology with a11y priorities, testing requirements | GF |
| 2026-02-23 | v2.0 — 4-accent brand color system (accent3/accent4), Hanken Grotesk default font, font weight token scale, semantic alias layer, hardcoded hex color cleanup | GF |
