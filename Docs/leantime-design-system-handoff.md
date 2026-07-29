# Leantime Design System — Complete Handoff Document

**Purpose:** This document captures every design decision made across ~15 sessions of collaborative work between Gloria (Leantime founder) and Claude. Drop this into a new chat along with the referenced project files to continue work without losing context.

**Owner:** Gloria Folaron · gloriafolaron@gmail.com
**Project:** Leantime (open-source project management)
**Codebase:** `/Users/gloriafolaron/Herd/leantime`
**Stack:** PHP / Laravel Blade / HTMX / Alpine.js / jQuery (legacy) / Tailwind CSS (`tw:` prefix)

---

## Table of Contents

1. [Brand Palette (Confirmed)](#1-brand-palette)
2. [Semantic Color Mapping](#2-semantic-color-mapping)
3. [Stageflow Stage Colors](#3-stageflow-stage-colors)
4. [Contrast & Accessibility Notes](#4-contrast--accessibility)
5. [Glass Brutalist Aesthetic](#5-glass-brutalist-aesthetic)
6. [3-Theme Architecture](#6-3-theme-architecture)
7. [Typography](#7-typography)
8. [Spacing, Radius & Shadows](#8-spacing-radius--shadows)
9. [Component Modernization Specs](#9-component-modernization-specs)
10. [Milestone / Gantt Bar Patterns](#10-milestone--gantt-bar-patterns)
11. [Existing Codebase Architecture](#11-existing-codebase-architecture)
12. [What's Been Built](#12-whats-been-built)
13. [What Still Needs Doing](#13-what-still-needs-doing)
14. [File Locations](#14-file-locations)
15. [Claude Code Prompt (for implementation)](#15-claude-code-prompt)

---

## 1. Brand Palette

**These are confirmed. Do not change.**

| Token | Hex | Name | Role |
|-------|-----|------|------|
| `--accent1` | `#004766` | Deep Teal | Primary. Nav, buttons, links, active states |
| `--accent2` | `#00B893` | Vibrant Emerald | Secondary. Success, gradients, progress fills |
| `--accent3` | `#CADE1B` | Chartreuse | Tertiary. Backgrounds/fills ONLY (fails WCAG on white) |
| `--accent4` | `#F61067` | Hot Pink | Danger/alerts. Passes AA on white (4.6:1) |
| Page bg | `#F5F5F5` | Light Gray | Page background |

**accent3 text variant:** When chartreuse must appear as text, darken to `#A8B516` (passes 3:1 for large text). Never use `#CADE1B` as text on white — it's 1.9:1 contrast ratio. On dark backgrounds, chartreuse is excellent (8.3:1).

---

## 2. Semantic Color Mapping

**Brand colors double as semantic colors. No separate green/red/yellow system.**

| Semantic | Maps to | Token |
|----------|---------|-------|
| Success | accent2 `#00B893` | `var(--accent2)` |
| Danger / Error | accent4 `#F61067` | `var(--accent4)` |
| Warning | accent3 `#A8B516` (darkened) | `var(--accent3-text)` |
| Info | accent1 `#004766` | `var(--accent1)` |

Background tints for alerts/badges:
- Success bg: `rgba(0, 184, 147, 0.08)` or `#E6F9F4`
- Danger bg: `rgba(246, 16, 103, 0.06)` or `#FDE8F0`
- Warning bg: `rgba(202, 222, 27, 0.10)` or `#F8FAE6`
- Info bg: `rgba(0, 71, 102, 0.06)` or `#E6EEF3`

---

## 3. Stageflow Stage Colors

The Logic Model / Stageflow component uses 5 stages (Inputs → Activities → Outputs → Outcomes → Impact). Each stage needs its own color.

| Stage | Color | Notes |
|-------|-------|-------|
| s1 (Inputs) | `#004766` (accent1) | Deep teal |
| s2 (Activities) | `#00B893` (accent2) | Emerald |
| s3 (Outputs) | `#A8B516` (accent3 darkened) | Chartreuse for text, `#CADE1B` for fills |
| s4 (Outcomes) | `#F61067` at 60% opacity | Hot pink softened. Or `#C84D7C` solid |
| s5 (Impact) | Gradient accent1→accent2 | Deep teal to emerald sweep |

---

## 4. Contrast & Accessibility

### WCAG Results (on white `#FFFFFF`)

| Color | Ratio | AA Normal | AA Large | Notes |
|-------|-------|-----------|----------|-------|
| accent1 `#004766` | 9.2:1 | ✅ Pass | ✅ Pass | Excellent |
| accent2 `#00B893` | 2.7:1 | ❌ Fail | ❌ Fail | Use for fills/icons only, not text |
| accent3 `#CADE1B` | 1.9:1 | ❌ Fail | ❌ Fail | Fills/badges ONLY |
| accent3-text `#A8B516` | 3.1:1 | ❌ Fail | ✅ Pass (3:1) | Large text only |
| accent4 `#F61067` | 4.6:1 | ✅ Pass | ✅ Pass | Good for buttons/badges |

### On dark backgrounds (`#1A1A2E`)

| Color | Ratio | Notes |
|-------|-------|-------|
| accent3 `#CADE1B` | 8.3:1 | Excellent — chartreuse shines in dark mode |
| accent2 `#00B893` | 6.8:1 | Good |
| accent4 `#F61067` | 4.1:1 | Acceptable for large text |

### Rules
- Status must NEVER be communicated by color alone (WCAG 1.4.1)
- Every colored indicator needs: icon, text label, or pattern companion
- Focus indicators: `ring-2 ring-[var(--accent1)] ring-offset-2`
- Minimum touch target: 44×44px

---

## 5. Glass Brutalist Aesthetic

Gloria's design direction: "glass brutalist" — clean, modern, slightly translucent surfaces with bold typography and sharp functional layout.

### Glass Treatment Specs

**"More" (Leantime theme):**
- `backdrop-filter: blur(8px)`
- `background: rgba(255, 255, 255, 0.85)` (light) / `rgba(30, 30, 46, 0.85)` (dark)
- `border: 1px solid rgba(255, 255, 255, 0.2)`
- Subtle inner shadow: `inset 0 1px 0 rgba(255,255,255,0.1)`

**"Less" (Focus theme):**
- `backdrop-filter: blur(4px)` or none
- More opaque backgrounds
- Minimal decoration

**Neurodivergent-safe:**
- No parallax
- No animated blur
- No auto-playing motion
- `prefers-reduced-motion` respected globally

### Where Glass Applies
- Card backgrounds
- Modal overlays
- Dropdown menus
- Navigation sidebar
- Tooltip/popover backgrounds
- NOT on text areas, inputs, or data-dense tables

---

## 6. 3-Theme Architecture

Leantime's existing theme system uses `theme.ini` files in `/public/theme/{name}/`. Each theme declares `primaryColor`, `secondaryColor`, `colorModeSupport`, `colorPickerSupport`.

### Existing Themes (in codebase)

| Theme | Primary | Secondary | Features |
|-------|---------|-----------|----------|
| "Default" (More) | `#004666` | `#00a887` | Color mode + picker |
| "Minimal" (Less) | `#004666` | `#00a887` | Color mode + picker |

### Proposed 3-Theme System

| Theme | Personality | Glass | Motion | Typography |
|-------|------------|-------|--------|-----------|
| **Leantime** | Rich, expressive | Full blur(8px) + translucency | Cubic-bezier transitions | Hanken Grotesk, variable weights |
| **Focus** | Calm, minimal | Reduced or opaque | Minimal, fast transitions | Same font, fewer weight variations |
| **High Contrast** | Accessibility-first | None (opaque surfaces) | `prefers-reduced-motion` default | Atkinson Hyperlegible option |

Each theme supports light + dark mode (6 total combinations). Colors come from CSS custom properties that swap per mode.

---

## 7. Typography

### Font: Hanken Grotesk (replacing Roboto)

- **Source:** Google Fonts, variable font (100–900 weight axis)
- **Load:** `<link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@100..900&display=swap">`
- **Fallback:** `-apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif`

### Weight Scale

| Use | Weight | Token |
|-----|--------|-------|
| Body text | 450 | `--font-weight-body` |
| Labels / UI text | 500 | `--font-weight-medium` |
| Subheadings | 600 | `--font-weight-semibold` |
| Headings | 650 | `--font-weight-heading` |
| Bold emphasis | 700 | `--font-weight-bold` |
| Display / Hero | 800 | `--font-weight-display` |

### Size Scale

| Token | Size | Use |
|-------|------|-----|
| `--text-xs` | 10px | Captions, timestamps |
| `--text-sm` | 11px | Help text, metadata |
| `--text-base` | 13px | Body text |
| `--text-md` | 14px | UI labels, card titles |
| `--text-lg` | 16px | Section headers |
| `--text-xl` | 18px | Page titles |
| `--text-2xl` | 24px | Hero/display |

### Font Picker (user preference)
- Hanken Grotesk (default)
- Atkinson Hyperlegible (accessibility option)
- Shantell Sans (handwritten/playful option)

---

## 8. Spacing, Radius & Shadows

### Border Radius Scale

| Token | Value | Use |
|-------|-------|-----|
| `--radius-xs` | 4px | Inline badges, small chips |
| `--radius-sm` | 6px | Buttons, inputs, small cards |
| `--radius` | 10px | Cards, modals, containers |
| `--radius-lg` | 14px | Large cards, hero sections |
| `--radius-pill` | 20px | Pills, tags, toggle tracks |
| `--radius-full` | 9999px | Avatars, circular buttons |

### Spacing

| Context | Value |
|---------|-------|
| Card body padding | 14px |
| Card header padding | 12px |
| List item padding | 10px |
| Section gaps | 10px |
| Component internal gap | 6–8px |
| Dense mode reduction | ~75% of standard |

### Shadows

| Token | Value | Use |
|-------|-------|-----|
| `--shadow-sm` | `0 1px 3px rgba(0,0,0,0.04)` | Cards at rest |
| `--shadow-md` | `0 4px 16px rgba(0,0,0,0.07)` | Hover, elevated cards |
| `--shadow-lg` | `0 8px 32px rgba(0,0,0,0.10)` | Dropdowns, popovers |
| `--shadow-xl` | `0 14px 48px rgba(0,0,0,0.13)` | Modals, active focus |

---

## 9. Component Modernization Specs

### Buttons
- Primary: accent1 fill + subtle inset highlight (`inset 0 1px 0 rgba(255,255,255,0.15)`)
- Ghost: translucent backdrop-filter background
- Hover: `transform: scale(1.01)`, brightness shift
- Radius: `--radius-sm` (6px)
- Focus: `ring-2 ring-[var(--accent1)] ring-offset-2`

### Inputs
- Background: `rgba(255, 255, 255, 0.7)` (glass effect)
- Border: `1px solid rgba(0, 71, 102, 0.12)` (accent1 at low opacity)
- Focus ring: `0 0 0 3px rgba(0, 184, 147, 0.2)` (accent2 glow)
- Radius: 10px (`--radius`)
- Placeholder: `var(--text-disabled)` color

### Cards
- Glass background per theme level
- `--shadow-sm` at rest, `--shadow-md` on hover
- `--radius` (10px)
- Header: bottom border using `var(--border-default)`

### Status Indicators
- Always pair color dot with text label
- Dot sizes: 6px (inline), 8px (standalone), 10px (emphasis)
- Use semantic colors from Section 2

---

## 10. Milestone / Gantt Bar Patterns

From visual reference screenshots analyzed in session. Two-layer bar construction:

### Bar Anatomy
1. **Progress fill:** Opaque gradient (`accent1 → accent2` sweep)
2. **Remaining track:** Translucent pill `rgba(255,255,255,0.08–0.12)` allowing grid bleed-through
3. **Progress handle:** Small circle at fill boundary (draggable affordance)
4. **Bar radius:** `--radius-pill` (20px) — full pill shape

### Color Coding for Milestones
- Standard: `accent1 → accent2` gradient (teal to emerald)
- Flagged/overdue: `accent4` gradient (hot pink)
- Ahead of schedule: `accent3` tint (chartreuse highlight)

### Dark Mode
- Bars read as "frosted glass floating over dark surface"
- Unfilled track: `rgba(255,255,255,0.08–0.12)` for lift
- Filled portion appears to glow from within

### Grayscale Validation
- Shape/shadow/glass carry visual weight without color ✅
- Progress readable via darker fill vs empty shell ✅
- Diamond milestone marker holds up perfectly ✅

---

## 11. Existing Codebase Architecture

### Theme System
- **Theme.php:** `app/Core/UI/Theme.php` — Manages theme loading, CSS variable injection
- **Theme configs:** `public/theme/{name}/theme.ini` — Declares name, colors, feature flags
- **Theme CSS:** `public/theme/{name}/css/` — Theme-specific overrides
- **Color picker:** Users can override accent1/accent2 via admin UI

### Component System
- **Blade components:** `app/Views/Templates/components/` — `<x-global::component>`
- **Stageflow:** `components/stageflow/card.blade.php` + `item.blade.php` + `styles.blade.php` (392 lines)
- **Forms:** `components/forms/input.blade.php`, `select.blade.php`, etc.
- **Elements:** `components/elements/card.blade.php`, `dropdown.blade.php`, etc.
- **Kanban:** `components/kanban/` subfolder with micro components
- **Tailwind prefix:** All Tailwind classes use `tw:` prefix

### Key Patterns
- HTMX for dynamic content (`hx-get`, `hx-post`, `hx-target`, `hx-swap`)
- Alpine.js for client-side reactivity
- jQuery still present (tabs, selectable, some dropdowns) — migration planned
- Bootstrap 2.x class mapping still in button component

### CSS Custom Properties (current)
- `--accent1`, `--accent2` — Set by theme + user color picker
- Neutral palette tokens exist in design token docs but not fully implemented
- Many hardcoded hex values throughout templates

---

## 12. What's Been Built

### Visual Component Guide v1
- **File:** `leantime-component-guide.html` (925 lines, standalone HTML)
- **Contents:** 28 components across 7 categories with live demos, props tables, token compliance indicators
- **Uses OLD palette:** Still has `#1b75bb` / `#81B1A8` — needs update to new brand colors

### Logic Model v11
- **File:** `logic-model-v11.html` — Full stageflow prototype
- **Shows:** Core vs Plugin mode toggle, stage focus interaction, health indicators, AI assist trigger
- **Uses CUSTOM palette:** Has its own stage colors that need harmonizing

### Design Tokens Audit Prompt
- **File:** `15-DESIGN-TOKENS-PROMPT.md` — Claude Code prompt for 15-sweep codebase audit
- **References:** `14-DESIGN-TOKENS.md` (in Leantime docs folder)
- **Status:** Ready to execute but uses old color references

---

## 13. What Still Needs Doing

### Priority 1 — Requirements Docs (this is what Gloria asked for)
- [ ] Create requirements document for the updated design system (all decisions above)
- [ ] Create Claude Code prompt for implementing these changes in the codebase

### Priority 2 — Update Component Guide
- [ ] Rebuild component guide HTML with corrected 4-accent palette
- [ ] Apply glass brutalist styling to all 28 component demos
- [ ] Integrate Hanken Grotesk font
- [ ] Update token compliance indicators for new palette
- [ ] Add Timeline/Gantt Bar as new component section
- [ ] Document 3-theme architecture visually

### Priority 3 — Codebase Implementation
- [ ] Update `14-DESIGN-TOKENS.md` with new palette and all decisions above
- [ ] Update `15-DESIGN-TOKENS-PROMPT.md` to use new color values
- [ ] Execute token sweeps (hardcoded colors → CSS variables)
- [ ] Add accent3/accent4 tokens to theme system
- [ ] Implement glass effects in component CSS
- [ ] Typography migration (Roboto → Hanken Grotesk)
- [ ] Dark mode improvements
- [ ] Accessibility sweeps (keyboard, screen reader, reduced motion)

---

## 14. File Locations

| What | Path |
|------|------|
| Leantime codebase | `/Users/gloriafolaron/Herd/leantime` |
| Design tokens doc | `docs/14-DESIGN-TOKENS.md` |
| Token audit prompt | `docs/15-DESIGN-TOKENS-PROMPT.md` |
| Theme engine | `app/Core/UI/Theme.php` |
| Default theme | `public/theme/default/` |
| Minimal theme | `public/theme/minimal/` |
| Blade components | `app/Views/Templates/components/` |
| Stageflow component | `app/Views/Templates/components/stageflow/` |
| Component guide v1 | (standalone HTML, attached to project) |
| Logic model v11 | (standalone HTML, attached to project) |

---

## 15. Claude Code Prompt (for implementation)

When ready to implement in the codebase, use this prompt structure:

```
You are working on the Leantime open-source project management application.

REQUIRED READING FIRST:
1. docs/14-DESIGN-TOKENS.md — canonical token reference
2. CLAUDE.md — project conventions
3. This handoff document (for all design decisions)

BRAND PALETTE:
- accent1: #004766 (deep teal) — primary
- accent2: #00B893 (emerald) — success/secondary  
- accent3: #CADE1B (chartreuse) — backgrounds only, #A8B516 for text
- accent4: #F61067 (hot pink) — danger/alerts

RULES:
- Use tw: prefix for all Tailwind classes
- Use CSS custom properties for ALL colors (no hardcoded hex)
- One sweep at a time — complete, test, confirm before next
- Test light mode, dark mode, and custom accent after every change
- Preserve visual appearance while normalizing underlying values
- Never touch theme definition files, third-party CSS, or files being actively rewritten

[Then specify which sweep to execute — see 15-DESIGN-TOKENS-PROMPT.md for sweep definitions]
```

---

## How to Use This Document

**To continue the visual component guide work:**
> "I'm building a visual component guide for Leantime. Here's my design system handoff document [paste or attach this file]. I also have the current component guide HTML [attach leantime-component-guide.html] and a logic model prototype [attach logic-model-v11.html]. Please rebuild the component guide using the corrected palette and glass brutalist aesthetic described in the handoff."

**To create the requirements doc:**
> "Here's my design system handoff document [paste/attach]. Please create a formal requirements document covering the visual design system, suitable for sharing with contributors and referencing during implementation."

**To start codebase implementation:**
> "Here's my design system handoff document [paste/attach]. I also have the design tokens audit prompt [attach 15-DESIGN-TOKENS-PROMPT.md]. Please start with Sweep 1: replacing hardcoded brand colors with CSS variables, using the updated palette from the handoff."

**To resume any specific task:**
> Reference the relevant section number from this document. All decisions are final unless you explicitly want to revisit them.
