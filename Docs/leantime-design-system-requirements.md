# Leantime Design System — Requirements Document

**Version:** 1.0
**Date:** February 23, 2026
**Owner:** Gloria Folaron
**Status:** Approved — ready for implementation

---

## 1. Purpose

This document defines the visual design system for Leantime, an open-source project management application. It serves as the canonical reference for all contributors implementing UI changes. Every design decision here is final unless explicitly revisited by the project owner.

**Companion documents:**
- `leantime-design-system-claude-code-prompt.md` — Implementation prompt (sweep-by-sweep execution plan)
- `leantime-component-guide-v2.html` — Live visual reference (28 components rendered with this system)
- `docs/14-DESIGN-TOKENS.md` — Token definitions in codebase (to be updated per this spec)

---

## 2. Brand Palette

Four accent colors. Confirmed. Do not change.

| Token | Hex | Name | Role | WCAG on White |
|-------|-----|------|------|---------------|
| `--accent1` | `#004766` | Deep Teal | Primary. Nav, buttons, links, active states | 9.2:1 ✅ AA |
| `--accent2` | `#00B893` | Vibrant Emerald | Secondary. Success, gradients, progress fills | 2.7:1 ❌ Fills only |
| `--accent3` | `#CADE1B` | Chartreuse | Tertiary. Background fills ONLY | 1.9:1 ❌ Fills only |
| `--accent4` | `#F61067` | Hot Pink | Danger/alerts, destructive actions | 4.6:1 ✅ AA |

**accent3 text variant:** `--accent3-text: #A8B516` — Darkened chartreuse for text contexts. Passes 3:1 for large text only. On dark backgrounds (#1A1A2E), chartreuse hits 8.3:1 — excellent.

**Page background:** `#F5F5F5`

### 2.1 Semantic Color Mapping

Brand colors double as semantic colors. No separate green/red/yellow system.

| Semantic | Maps to | Token | Background Tint |
|----------|---------|-------|-----------------|
| Success | accent2 | `var(--accent2)` | `rgba(0, 184, 147, 0.08)` / `#E6F9F4` |
| Danger / Error | accent4 | `var(--accent4)` | `rgba(246, 16, 103, 0.06)` / `#FDE8F0` |
| Warning | accent3 darkened | `var(--accent3-text)` | `rgba(202, 222, 27, 0.10)` / `#F8FAE6` |
| Info | accent1 | `var(--accent1)` | `rgba(0, 71, 102, 0.06)` / `#E6EEF3` |

### 2.2 Neutral Palette (Scheme Layer — swaps in dark mode)

| Token | Light | Dark | Use |
|-------|-------|------|-----|
| `--color-text-primary` | `#1A1A2E` | `#E8E8ED` | Headings, primary text |
| `--color-text-secondary` | `#4B5563` | `#A0A7B5` | Body text, descriptions |
| `--color-text-muted` | `#9CA3AF` | `#6B7280` | Captions, timestamps, help text |
| `--color-text-disabled` | `#D1D5DB` | `#4B5563` | Disabled states, placeholders |
| `--color-bg-page` | `#F5F5F5` | `#131320` | Page background |
| `--color-bg-card` | `#FFFFFF` | `#1E1E2E` | Card/surface backgrounds |
| `--color-bg-muted` | `#F0F1F3` | `#252538` | Muted backgrounds, table headers |
| `--color-bg-hover` | `#F3F4F6` | `#2A2A3E` | Hover states |
| `--color-border-default` | `#E8ECF0` | `#2E2E42` | Standard borders |
| `--color-border-light` | `#F0F1F3` | `#252538` | Subtle dividers |

---

## 3. Typography

### 3.1 Font Family

**Primary:** Hanken Grotesk (variable, Google Fonts, weights 100–900)
**Load:** `<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@100..900&display=swap">`
**Fallback:** `-apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif`
**Replaces:** Roboto

**User font picker options:**
- Hanken Grotesk (default)
- Atkinson Hyperlegible (accessibility)
- Shantell Sans (playful)

### 3.2 Weight Scale

| Token | Weight | Use |
|-------|--------|-----|
| `--font-weight-body` | 450 | Body text |
| `--font-weight-medium` | 500 | Labels, UI text |
| `--font-weight-semibold` | 600 | Subheadings |
| `--font-weight-heading` | 650 | Headings |
| `--font-weight-bold` | 700 | Bold emphasis |
| `--font-weight-display` | 800 | Hero/display |

### 3.3 Size Scale

| Token | Size | Use |
|-------|------|-----|
| `--text-xs` | 10px | Captions, timestamps |
| `--text-sm` | 11px | Help text, metadata |
| `--text-base` | 13px | Body text |
| `--text-md` | 14px | UI labels, card titles |
| `--text-lg` | 16px | Section headers |
| `--text-xl` | 18px | Page titles |
| `--text-2xl` | 24px | Hero/display |

---

## 4. Spacing, Radius & Shadows

### 4.1 Spacing Scale

| Token | Value | Use |
|-------|-------|-----|
| `--space-1` | 4px | Tight gaps |
| `--space-2` | 8px | Component internal |
| `--space-3` | 12px | Card header padding |
| `--space-4` | 16px | Card body padding, section gaps |
| `--space-5` | 20px | Large section gaps |
| `--space-6` | 24px | Page-level padding |
| `--space-8` | 32px | Hero spacing |

### 4.2 Border Radius

| Token | Value | Use |
|-------|-------|-----|
| `--radius-xs` | 4px | Inline badges, small chips |
| `--radius-sm` | 6px | Buttons, inputs |
| `--radius` | 10px | Cards, modals, containers |
| `--radius-lg` | 14px | Large cards, hero sections |
| `--radius-pill` | 20px | Pills, tags, toggle tracks, timeline bars |
| `--radius-full` | 9999px | Avatars, circular buttons |

### 4.3 Shadows (Scheme layer — adjust opacity in dark mode)

| Token | Value | Use |
|-------|-------|-----|
| `--shadow-sm` | `0 1px 3px rgba(0,0,0,0.04)` | Cards at rest |
| `--shadow-md` | `0 4px 16px rgba(0,0,0,0.07)` | Hover, elevated cards |
| `--shadow-lg` | `0 8px 32px rgba(0,0,0,0.10)` | Dropdowns, popovers |
| `--shadow-xl` | `0 14px 48px rgba(0,0,0,0.13)` | Modals, active focus |

---

## 5. Glass Brutalist Aesthetic

Design direction: clean, modern, translucent surfaces with bold typography and sharp functional layout.

### 5.1 Glass Treatment

| Property | Leantime Theme | Focus Theme | High Contrast Theme |
|----------|---------------|-------------|-------------------|
| `backdrop-filter` | `blur(8px)` | `blur(4px)` or none | None |
| Background (light) | `rgba(255,255,255,0.85)` | `rgba(255,255,255,0.95)` | `#FFFFFF` opaque |
| Background (dark) | `rgba(30,30,46,0.85)` | `rgba(30,30,46,0.95)` | `#1E1E2E` opaque |
| Border | `rgba(255,255,255,0.2)` | Standard token border | Standard token border |
| Inner highlight | `inset 0 1px 0 rgba(255,255,255,0.1)` | None | None |

**CSS tokens:**
- `--glass-bg` — swaps per mode (light/dark)
- `--glass-border` — swaps per mode
- `--glass-inset` — subtle top highlight

### 5.2 Where Glass Applies

- ✅ Card backgrounds, modal overlays, dropdown menus, navigation sidebar, tooltip/popover backgrounds
- ❌ NOT on text areas, inputs, data-dense tables, or anywhere readability would suffer

### 5.3 Neurodivergent Safety

- No parallax
- No animated blur
- No auto-playing motion
- `prefers-reduced-motion` respected globally via CSS media query

---

## 6. Theme Architecture

Three themes, each supporting light + dark mode (6 total combinations).

| Theme | Personality | Glass | Motion | Target User |
|-------|-----------|-------|--------|-------------|
| **Leantime** | Rich, expressive | Full `blur(8px)` + translucency | Cubic-bezier transitions | Default experience |
| **Focus** | Calm, minimal | Reduced or opaque | Minimal, fast transitions | Distraction-sensitive |
| **High Contrast** | Accessibility-first | None (opaque) | `prefers-reduced-motion` default | Low vision, a11y needs |

**Implementation:** Theme configs live in `public/theme/{name}/theme.ini`. Each declares `primaryColor`, `secondaryColor`, `colorModeSupport`, `colorPickerSupport`. Colors flow through CSS custom properties that swap per mode.

**User color picker:** Users can override accent1/accent2 via admin UI. accent3/accent4 tokens need to be added to the theme system.

---

## 7. Stageflow Stage Colors

The Logic Model / Stageflow component (Inputs → Activities → Outputs → Outcomes → Impact) uses per-stage colors:

| Stage | Color | Fill | Text |
|-------|-------|------|------|
| s1 (Inputs) | `#004766` | accent1 | accent1 |
| s2 (Activities) | `#00B893` | accent2 | accent2 |
| s3 (Outputs) | `#CADE1B` / `#A8B516` | accent3 for fills | accent3-text for labels |
| s4 (Outcomes) | `#C84D7C` | accent4 at 60% opacity | accent4 |
| s5 (Impact) | gradient | `linear-gradient(accent1, accent2)` | accent1 |

---

## 8. Component Standards

### 8.1 Buttons

- Primary: accent1 fill + `inset 0 1px 0 rgba(255,255,255,0.15)`
- Ghost: translucent glass background
- Danger: accent4 fill
- Success: accent2 fill
- Warning: accent3-text fill
- Hover: `brightness(1.08)` shift
- Radius: `--radius-sm` (6px)
- Focus: `ring-2 ring-[var(--accent1)] ring-offset-2`
- Minimum touch target: 44×44px

### 8.2 Inputs

- Background: `rgba(255,255,255,0.7)` (glass)
- Border: `1px solid rgba(0,71,102,0.12)` (accent1 at low opacity)
- Focus ring: `0 0 0 3px rgba(0,184,147,0.2)` (accent2 glow)
- Radius: `--radius` (10px)
- Placeholder: `var(--color-text-disabled)`

### 8.3 Cards

- Glass background per theme level
- `--shadow-sm` at rest, `--shadow-md` on hover
- `--radius` (10px)
- Header: bottom border `var(--color-border-default)`

### 8.4 Status Indicators

- ALWAYS pair color with text label (WCAG 1.4.1)
- Dot sizes: 6px (inline), 8px (standalone), 10px (emphasis)
- Use semantic color mapping from Section 2.1

### 8.5 Timeline / Gantt Bars (Emboss Pattern)

Two-layer construction:
1. **Progress fill:** Opaque gradient `accent1 → accent2`
2. **Remaining track:** Translucent pill `rgba(0,0,0,0.04)` light / `rgba(255,255,255,0.08)` dark
3. **Handle:** 8px circle at fill boundary, white with accent2 border
4. **Bar radius:** `--radius-pill` (20px)
5. **Diamond milestone markers:** 10px rotated square, colored by state

**Flagged/overdue:** accent4 gradient
**Ahead of schedule:** accent3 tint
**Dark mode:** Bars appear as frosted glass floating over dark surface

---

## 9. Accessibility Requirements

These are not optional. They ship before cosmetic improvements.

### 9.1 Color

- No status communicated by color alone (WCAG 1.4.1)
- Every colored indicator needs: icon, text label, or pattern companion
- accent2 and accent3 fail WCAG on white — fills/icons only, never standalone text
- accent1 (9.2:1) and accent4 (4.6:1) pass AA for text on white

### 9.2 Keyboard

- Every interactive element has visible focus indicator
- Focus style: `ring-2 ring-[var(--accent1)] ring-offset-2` or `var(--focus-ring)` box-shadow
- All click handlers have keyboard equivalents (Enter + Space for buttons)
- Logical tab order matches visual layout
- No keyboard traps

### 9.3 Screen Reader

- Icon-only buttons: `aria-label="Action name"`
- Decorative icons: `aria-hidden="true"`
- Inputs without visible labels: `aria-label` or `<label for>`
- Status dots/pills: `aria-label="Status: In Progress"`
- HTMX dynamic content targets: `aria-live="polite"` on swap containers
- Loading indicators: `role="status"` + `<span class="tw:sr-only">Loading...</span>`

### 9.4 Motion

- All animations respect `prefers-reduced-motion`
- Global CSS reset or per-component `tw:motion-reduce:transition-none`
- No auto-playing animations

### 9.5 Touch Targets

- Minimum 44×44px clickable area on all interactive elements
- Icon buttons: minimum `tw:p-2.5` (10px padding around icon)

---

## 10. Codebase Conventions

### 10.1 Tech Stack

- PHP / Laravel Blade templates
- HTMX for dynamic content
- Alpine.js for client-side reactivity
- jQuery (legacy — migration planned)
- Tailwind CSS with `tw:` prefix on ALL classes
- Bootstrap 2.x class mapping still in button component

### 10.2 CSS Custom Properties

- `--accent1`, `--accent2` — currently set by theme + user color picker
- `--accent3`, `--accent4` — need to be added to theme system
- Neutral palette tokens — defined in design token docs but not fully implemented in codebase
- Many hardcoded hex values remain throughout templates — these are the implementation target

### 10.3 Key File Locations

| What | Path |
|------|------|
| Theme engine | `app/Core/UI/Theme.php` |
| Default theme | `public/theme/default/` |
| Minimal theme | `public/theme/minimal/` |
| Blade components | `app/Views/Templates/components/` |
| Stageflow | `app/Views/Templates/components/stageflow/` |
| Design tokens doc | `docs/14-DESIGN-TOKENS.md` |
| Token audit prompt | `docs/15-DESIGN-TOKENS-PROMPT.md` |

### 10.4 Implementation Rules

- Use `tw:` prefix for ALL Tailwind classes
- Use CSS custom properties for ALL colors — no hardcoded hex in templates
- One sweep at a time — complete, test, confirm before next
- Test light mode, dark mode, and custom accent after every change
- Preserve visual appearance while normalizing underlying values
- Never touch theme definition files, third-party CSS, or files being actively rewritten
- Flag any color that doesn't map cleanly to a token — don't guess

---

## 11. Implementation Priority

### Phase 1 — Foundation (do first)
1. Update `docs/14-DESIGN-TOKENS.md` with this spec's palette and tokens
2. Add accent3/accent4 to theme system (`Theme.php` + theme.ini files)
3. Sweep hardcoded brand colors → CSS variables
4. Sweep hardcoded neutrals → CSS variables

### Phase 2 — Accessibility (do before cosmetic work)
5. Keyboard focus indicators on all interactive elements
6. Screen reader basics (aria-labels, roles, live regions)
7. Reduced motion support
8. Color-only status indicator fixes
9. Touch target compliance

### Phase 3 — Visual Polish
10. Typography migration (Roboto → Hanken Grotesk)
11. Glass effect implementation
12. Dark mode verification pass
13. Border radius normalization
14. Spacing normalization
15. Shadow normalization
16. Transition normalization
17. Legacy pattern catalog (jQuery, inline styles, .tpl.php files)

---

## 12. Acceptance Criteria

A sweep is complete when:
- All instances in target files are replaced
- Default light theme looks identical before/after
- Dark mode renders correctly (no invisible text, missing borders, white rectangles)
- Custom accent test passes (swap accent1/accent2 in console — nothing hardcoded remains)
- Sweep report produced per `14-DESIGN-TOKENS.md` section 11.3 format
- No regressions in existing functionality

---

*This document is the source of truth. When it conflicts with older documents (component guide v1, logic model v11, original 15-DESIGN-TOKENS-PROMPT.md), this document wins.*
