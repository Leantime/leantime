# Visual Reference & Design System

## Overview

This document defines the visual constants, spacing rules, and transition behaviors for the Logic Model Canvas. The definitive source of truth is the v11 HTML prototype (`logic-model-v11.html`).

---

## 1. Color Palette

### Brand Colors
| Name | Value | Usage |
|------|-------|-------|
| Accent Primary | `#00456e` | Header, buttons, links |
| Accent Secondary | `#00aa64` | Positive indicators, narrative border |

### Text Colors
| Name | Value | Usage |
|------|-------|-------|
| Primary | `#1e2b38` | Headings, titles |
| Secondary | `#5e6e7d` | Body text, descriptions |
| Tertiary | `#9aa7b4` | Metadata, timestamps |
| Muted | `#c3ccd4` | Disabled, inactive elements |

### Surface Colors
| Name | Value | Usage |
|------|-------|-------|
| Background | `#f3f4f6` | Page background |
| Surface | `#ffffff` | Cards, panels |
| Border | `#e2e6ea` | Active borders |
| Border Light | `#eef0f2` | Subtle dividers |

### Stage Colors
| Stage | Primary | Background Tint |
|-------|---------|-----------------|
| 1 - Inputs | `#4A85B5` | `#EDF3F8` |
| 2 - Activities | `#3E937A` | `#ECF6F2` |
| 3 - Outputs | `#C09035` | `#FBF5EA` |
| 4 - Outcomes | `#8E6AAD` | `#F2EDF8` |
| 5 - Impact | `#2D7D5E` | `#EAF5F0` |

### Status Colors
| Status | Color |
|--------|-------|
| Draft | `#1B75BB` |
| In Review | `#F0A030` |
| Validated: Valid | `#75BB1B` |
| On Hold | `#BB1B25` |
| Validated: Invalid | `#BB1B25` |

### Health Badge Colors
| State | Background | Icon/Text | Border |
|-------|------------|-----------|--------|
| Strong | `#EAF5E8` | `#5a9e4f` | `#c8e0c2` |
| Warning | `#FEF4E4` | `#c08a2e` | `#edd9ad` |
| Gap | `#FDEAEB` | `#BB1B25` | `#edb5b9` |

---

## 2. Border Radii

| Token | Value | Usage |
|-------|-------|-------|
| r | 12px | Stage cards, panels |
| r-sm | 8px | Inner cards, dropdowns, narrative |
| r-xs | 5px | Item cards, pills |
| pill | 20px | Buttons, badges, count circles |

---

## 3. Shadows

| Token | Value | Usage |
|-------|-------|-------|
| sh-sm | `0 1px 3px rgba(0,0,0,0.04)` | Inactive stages, tabs |
| sh-md | `0 4px 16px rgba(0,0,0,0.07)` | Hover states |
| sh-lg | `0 8px 32px rgba(0,0,0,0.10)` | AI trigger, elevated elements |
| sh-xl | `0 14px 48px rgba(0,0,0,0.13)` | Active stage, dropdowns |

---

## 4. Typography

| Element | Size | Weight | Color |
|---------|------|--------|-------|
| Stage title (active) | 16px | 700 (bold) | Primary |
| Stage title (inactive) | 12px | 600 (semibold) | Primary |
| Stage subtitle (active) | 11px | 400 | Secondary |
| Stage subtitle (inactive) | 10px | 400 | Tertiary |
| Item title | 13px | 600 (semibold) | Primary |
| Item description | 11px | 400 | Secondary |
| Compact item title | 11px | 500 (medium) | Secondary |
| Pill text | 9px | 600 (semibold) | White |
| Metadata | 10px | 400 | Tertiary |
| Count badge | 10px (active), 9px (inactive) | 700 | White |

Font stack: `-apple-system, BlinkMacSystemFont, "Segoe UI", system-ui, sans-serif`

---

## 5. Spacing

| Context | Value |
|---------|-------|
| Gap between stage columns | 10px |
| Gap between item cards | 8px |
| Stage body padding (top, from header) | 14px |
| Stage body padding (sides) | 10px |
| Stage header padding (top, active) | 24px |
| Stage header padding (top, inactive) | 20px |
| Narrative to columns margin | 24px |
| Item card padding | 8px 10px |
| Compact item padding | 5px 8px |
| Progress bar horizontal padding | 16px |
| Max workspace width | 1480px |
| Workspace side padding | 16px |

---

## 6. Transitions

| Element | Duration | Easing | Properties |
|---------|----------|--------|------------|
| Stage focus change | 350ms | `cubic-bezier(0.25, 0.46, 0.45, 0.94)` | transform, opacity, box-shadow, background |
| Stage hover | 150-200ms | ease | opacity, transform, box-shadow |
| Progress bar fill | 500ms | ease | width |
| Count badge appear | 300ms | ease | opacity |
| Health badge hover | 200ms | ease | transform, box-shadow |
| Item hover | 150ms | ease | background |

---

## 7. Active vs. Inactive Stage Properties

| Property | Active | Inactive | Hover (inactive) |
|----------|--------|----------|-------------------|
| Scale | 1.0 | 0.955 | 0.97 |
| Opacity | 1.0 | 0.5 | 0.78 |
| Shadow | sh-xl | sh-sm | sh-md |
| Background | Gradient (tint → white) | White | White |
| Header border | 3px, stage color | 2px, border-light | 2px, border-light |
| Icon size | 36px | 28px | 28px |
| Icon style | Filled (stage color bg, white icon) | Tint (tint bg, stage color icon) | Tint |
| Items | Expanded cards | Compact dot + title | Compact dot + title |
| Focus flag | Visible | Hidden | Hidden |
| Add button | Visible | Hidden | Hidden |
| Z-index | 10 | 1 | 1 |

---

## 8. Responsive Breakpoints

| Breakpoint | Behavior |
|------------|----------|
| > 1100px | Five columns, `flex: 1 1 0`, side by side |
| 600–1100px | `flex-wrap: wrap`, stages become `flex: 1 1 calc(50% - 4px)`, 2 per row |
| < 600px | Single column stack, active stage appears first |

---

## 9. Entry Animation

Stages animate in on page load:
```css
@keyframes enter {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
.stage { animation: enter 350ms ease both; }
```

Each stage can be staggered with `animation-delay` for a cascade effect.

---

## 10. Status Dot Colors (Compact View)

| Status | Dot Color |
|--------|-----------|
| Draft | `#c3ccd4` (muted) |
| In Review | `#1B75BB` (blue) |
| Validated: Valid | `#75BB1B` (green) |
| On Hold | `#BB1B25` (red) |
| Validated: Invalid | `#BB1B25` (red) |

Dots are 8px circles with `border-radius: 50%`, positioned inline before the title text with 5px right margin.
