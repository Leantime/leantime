# Phase 1: Core Board

## Summary

The core logic model canvas is a new board type that ships free with Leantime. It provides a five-stage causal chain — Inputs, Activities, Outputs, Outcomes, Impact — with a visual hierarchy that emphasizes one active stage while keeping the full chain visible.

---

## 1. Board Type Registration

Register "Logic Model" as a new board type alongside existing canvas boards (Lean Canvas, Value Canvas, SWOT). It should appear in the project board menu and follow the same creation flow.

A project can have multiple logic models. Each is an independent board with its own items and state.

---

## 2. The Five Stages

Stages are **fixed** in core. Titles, subtitles, icons, colors, and order cannot be changed by the user.

| # | Stage | Subtitle | Icon | Primary Color | Background Tint |
|---|-------|----------|------|---------------|-----------------|
| 1 | Inputs | Resources we invest | `arrow-right-to-bracket` | `#4A85B5` | `#EDF3F8` |
| 2 | Activities | What we do | `gears` | `#3E937A` | `#ECF6F2` |
| 3 | Outputs | What we produce | `boxes-stacked` | `#C09035` | `#FBF5EA` |
| 4 | Outcomes | Changes we expect | `chart-line` | `#8E6AAD` | `#F2EDF8` |
| 5 | Impact | Ultimate change | `bullseye` | `#2D7D5E` | `#EAF5F0` |

Icons listed are FontAwesome references from the prototype. Use Leantime's icon system equivalents in production.

---

## 3. Visual Hierarchy — Pricing Page Pattern

This is NOT a kanban board. One stage is always "active" (focused), and the others are receded.

### Active Stage
- `transform: scale(1)`
- Full opacity (1.0)
- Elevated shadow (`0 14px 48px rgba(0,0,0,0.13)`)
- Background: gradient from stage tint color at top fading to white at ~80px
- Header: 3px colored bottom border (stage color)
- Items: fully expanded cards with title, description, footer
- "Current Focus" pill flag above the stage (-11px from top)
- Add item link visible at bottom

### Inactive Stage
- `transform: scale(0.955)`
- Reduced opacity (0.5)
- Subtle shadow (`0 1px 3px rgba(0,0,0,0.04)`)
- Background: white, no gradient
- Header: 2px light border bottom
- Items: compact rows — status dot (8px circle) + title only
- No flag, no add link
- Cursor: pointer (entire stage is clickable)

### Inactive Stage on Hover
- Opacity rises to 0.78
- Scale rises to 0.97
- Shadow increases to medium
- This creates a "peek before you click" interaction

### Transition
- Duration: 350ms
- Easing: `cubic-bezier(0.25, 0.46, 0.45, 0.94)`
- Only one stage is active at a time

---

## 4. Stage Header

Each stage header is a centered column layout containing:

1. **Icon** — 36px rounded square (active) / 28px (inactive). Active: filled with stage color, white icon. Inactive: stage tint background, stage color icon.
2. **Title row** — Stage name (16px bold active / 12px semibold inactive) + count badge
3. **Subtitle** — (11px secondary color active / 10px tertiary inactive)

### Count Badge
- Small circle in stage color showing number of items
- **Hidden by default, visible on hover only** (same pattern as kanban)
- Active: 20px circle, 10px font
- Inactive: 16px circle, 9px font
- Positioned inline after the title text

### Header Border
- Active: 3px bottom border in stage color
- Inactive: 2px bottom border in light gray (`#eef0f2`)

---

## 5. Stage Layout

```
┌──────────────────────────────────────────────────────────────────────┐
│  [Tab: Q1 Youth Literacy ▾]                          [Print] [+ Add] │
├──────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌─Stage─┐  ┌─Stage─┐  ┌─Stage─┐  ┌─Stage─┐  ┌─Stage─┐            │
│  │ACTIVE │  │ inact │  │ inact │  │ inact │  │ inact │            │
│  │       │  │       │  │       │  │       │  │       │            │
│  │ Cards │  │ Dots  │  │ Dots  │  │ Dots  │  │ Dots  │            │
│  │ Full  │  │ Only  │  │ Only  │  │ Only  │  │ Only  │            │
│  │       │  │       │  │       │  │       │  │       │            │
│  │+ Add  │  └───────┘  └───────┘  └───────┘  └───────┘            │
│  └───────┘                                                          │
└──────────────────────────────────────────────────────────────────────┘
```

- Flow container: `display: flex; align-items: flex-start; gap: 10px`
- Each stage: `flex: 1 1 0; min-width: 0`
- Stages naturally size to content height (active stage is taller)

---

## 6. Item Cards

### Expanded Card (Active Stage)

```
┌───────────────────────────────┐
│▌ $250K annual budget          │  ← 3px left border in stage color
│▌ Federal Title I funding...   │  ← Description: 11px, secondary color
│▌                              │
│▌ [In Review ▾]  💬 3  📎 1  (GF)│  ← Footer row
└───────────────────────────────┘
```

- Padding: 8px 10px
- Border-left: 3px solid (stage color)
- Title: 13px, bold
- Description: 11px, secondary color (`#5e6e7d`), single line
- Footer: hypothesis status dropdown + comment count + attachment count + assignee avatar
- Gap between cards: 8px
- Border-radius: 5px
- Hover: subtle background tint (`rgba(0,0,0,0.02)`)

### Compact Row (Inactive Stage)

```
● $250K annual budget
● 1 Director, 3 Specialists...
● 500+ age-appropriate books
```

- Status dot: 8px circle, colored by hypothesis status
- Title: 11px, medium weight, secondary color
- No description, no footer, no left border
- Padding: 5px 8px

### Card Pattern Alignment

The expanded card matches Leantime's existing canvas board cards (Project Value Board, Lean Canvas). It uses the same elements: bold title in accent color, description text, status dropdown pill, comment icon, attachment icon, assignee avatar, and three-dot menu.

---

## 7. Hypothesis Status System

Every item is a hypothesis. The status tracks its validation state. **This is a core feature.**

| Status | Color | Dot Color | Meaning |
|--------|-------|-----------|---------|
| Draft | Blue `#1B75BB` | `#cccccc` (muted) | Not yet examined |
| In Review | Orange `#F0A030` | `#1B75BB` (blue) | Being tested |
| Validated: Valid | Green `#75BB1B` | `#75BB1B` (green) | Evidence confirms |
| On Hold | Red `#BB1B25` | `#BB1B25` (red) | Paused or blocked |
| Validated: Invalid | Red `#BB1B25` | `#BB1B25` (red) | Evidence disproves |

### Status Dropdown
- Displays as a clickable pill on the card (matching existing value canvas pattern)
- Click opens dropdown with all five options, each with their color
- Default for new items: Draft

---

## 8. Logic Model Selector

### Tab Structure
Top bar shows a button with the current model name + dropdown chevron. Follows the same pattern as Leantime's wiki/docs selector (`Docs // Default ▾`).

### Dropdown Content
```
┌─────────────────────────┐
│ Q1 Youth Literacy    ✓  │  ← Active (bold, accent color)
│ Summer Pilot            │
│─────────────────────────│
│ + Create new logic model│
└─────────────────────────┘
```

Just model names. No sub-views, no wiki pages. Click to switch. "+ Create new logic model" at the bottom.

---

## 9. Top Bar Actions

### Core Mode
- **Print** button — triggers `window.print()` (browser native)
- **+ Add** button — opens item creation modal for the active stage

---

## 10. Card Modal

Clicking an item card opens the detail modal (same modal system as existing canvas cards).

### Core Modal Sections

1. **Header** — Editable title, stage label (colored pill, read-only), hypothesis status dropdown, three-dot menu (edit, delete)
2. **Description** — Rich text editor, markdown supported
3. **Comments** — Threaded discussion (existing Leantime comment system)
4. **Attachments** — File uploads
5. **Meta** — Assignee selector, created date, last modified

### Behavior
- Modal opens as slide-over or center modal (match existing pattern)
- Changes save on blur or explicit save button (match existing pattern)
- Status changes in modal update the card immediately

---

## 11. Responsive Behavior

| Breakpoint | Layout |
|------------|--------|
| > 1100px | Five columns side by side |
| 600–1100px | Columns wrap to 2 per row (`flex-wrap: wrap`) |
| < 600px | Single column stack, active stage first |

---

## 12. Keyboard Navigation

- **Tab** — move focus between stages
- **Enter / Space** — activate focused stage
- **Arrow Up/Down** — navigate items within active stage
- **Enter** on item — open card modal
- **Escape** — close modal

---

## 13. ARIA Labels

- Stage: `"Inputs stage, 4 items, Current focus"` / `"Activities stage, 3 items"`
- Item: `"Item: $250K annual budget, Status: Validated Valid"`
- Status dropdown: `"Hypothesis status: In Review, click to change"`
