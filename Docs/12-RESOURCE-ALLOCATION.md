# Resource Allocation View — PgmPro Plugin

**Product Requirements Document**

| Field | Value |
|---|---|
| Product | Leantime |
| Feature | Resource Allocation View (PgmPro) |
| Version | 2.0 |
| Date | February 23, 2026 |
| Author | Gloria Folaron |
| Status | Draft |
| Plugin | PgmPro (Program Management) |
| Core Dependencies | ResourceStructure domain (schema), Canvas domain (storage) |
| Prerequisites | WorkStructure Core, Logic Model Canvas Phase 1, StrategyPro Wizard |
| Applies to | `10-ADDENDUM-WORKSTRUCTURE-ARCHITECTURE.md` (schema pattern), `11-ADDENDUM-RESOURCE-DECISIONS.md` (PgmPro owns resources) |

---

## 1. Overview

### 1.1 Problem Statement

Program managers who route Logic Model work into a program need to answer two fundamental questions:

1. **Planning:** "Do I have what I need to run this program?" — budget, people, hours, dependencies.
2. **Tracking:** "Is reality matching my plan?" — actual hours logged vs. planned, budget spend vs. budgeted.

Today, Leantime has no program-level resource view. Resource information exists as text notes on Logic Model Inputs items or scattered across child project settings. There is no visual allocation, no capacity view, no budget tracking, and no plan-vs-actual comparison.

### 1.2 Solution

Three deliverables:

**ResourceStructure (Core Domain):** A schema definition layer — parallel to WorkStructure — that defines what resource types exist (people allocations, budget items, dependencies) and how they relate. Does not hold instance data. Stores in canvas tables.

**Resource Canvas (Board Type):** A new canvas board type (`type = 'resource'`) registered by PgmPro. Resource items live in `zp_canvas` / `zp_canvas_items` — the same tables every other canvas uses. No new tables for resource data.

**Resource Allocation View (PgmPro):** A custom visualization that reads from the Resource Canvas but renders completely differently from a standard canvas board. Fill-up containers, stacked bars, summary strips. Two modes: V1 (planning) and V2 (plan vs. actual).

### 1.3 Architecture: Why Canvas Tables

Leantime has 14 canvas variants, all extending a Canvas base domain. They all store in `zp_canvas` (boards) and `zp_canvas_items` (items). Each variant differentiates by `type` column and interprets the generic item fields (`box`, `description`, `assumptions`, `data`, `conclusion`, `status`, `tags`) for its own semantics.

Resource allocation follows the same pattern:

| Canvas field | Resource semantics |
|---|---|
| `type` (on canvas) | `'resource'` |
| `box` (stage) | `'people'`, `'budget'`, `'dependency'` |
| `description` | Item name (person name, budget line name, partner name) |
| `assumptions` | Role (for people), category (for budget) |
| `data` | JSON: structured fields (hours, amounts, projectId, userId) |
| `conclusion` | Notes / completion prompt text |
| `status` | `'stub'`, `'active'`, `'archived'` |
| `milestoneId` | Links to related project milestone (optional) |
| `author` | Who created the resource item |
| `tags` | Tagging for filtering |

This means:
- No new database tables for resource data
- Full CRUD service layer inherited from Canvas base
- Entityrelations linking to parent program and source Logic Model already works
- Comments, tags, milestone linking, sorting — all free
- Every canvas infrastructure improvement automatically benefits resources

### 1.4 Architecture: ResourceStructure as Schema Layer

Just as WorkStructure defines the *shape* of work without holding work instances, ResourceStructure defines the *shape* of resources without holding resource instances.

```
WorkStructure pattern:
  Schema defines: element types (milestone, task, goal), relationships, cross-structure mappings
  Instance data lives in: zp_tickets, zp_canvas_items, zp_goal_canvas_items
  Plugins read/write through: schema-aware service layer

ResourceStructure pattern:
  Schema defines: resource types (people, budget, dependency), relationships, field contracts
  Instance data lives in: zp_canvas_items (type='resource')
  Plugins read/write through: schema-aware service layer
```

ResourceStructure registers a structure definition in WorkStructure's registry with element types for people, budget, and dependency. This means the same cross-structure mapping system that translates "Logic Model output → Project milestone" can also translate "Logic Model input → Resource allocation" — the wizard already knows how.

### 1.5 Design Principles

- **No new tables.** Canvas infrastructure handles storage. ResourceStructure handles schema.
- **V1 is beautiful.** The planning view is the foundation — clean, focused, visually delightful. V2 adds complexity on top but never degrades V1.
- **Project-first workflow.** Projects are the primary organizing unit. People and budget are allocated TO projects.
- **Unified hover.** A single `hoveredProject` state drives highlighting across project rows, person containers, and budget items simultaneously.
- **Progressive disclosure.** People and Budget sections collapse with inline summaries via shared components.
- **Canvas seeding.** Logic Model Inputs stage auto-classifies into resource canvas items with `status='stub'`.

### 1.6 Relationship to Existing Documents

| Document | Relationship |
|---|---|
| `10-ADDENDUM-WORKSTRUCTURE-ARCHITECTURE.md` | ResourceStructure mirrors the schema-definition pattern |
| `11-ADDENDUM-RESOURCE-DECISIONS.md` | PgmPro owns resource management, ResourceStructure is core data layer |
| `07-WORKSTRUCTURE.md` | Resource structure registers alongside work structures |
| `09-ADDENDUM-MAPPING-CORRECTION.md` | Inputs stage maps to resource items (not work entities) |

---

## 2. ResourceStructure Core Domain

### 2.1 What It Is

A schema definition layer that registers resource types and their field contracts. Lives in `app/Domain/ResourceStructure/`. Follows the same architecture as WorkStructure (addendum 10).

### 2.2 Structure Registration

ResourceStructure registers a "Resource" structure in the WorkStructure registry:

| Element type_key | Label | Description | Domain reference |
|---|---|---|---|
| `people` | People | Person allocated to program with weekly hours per project | Canvas (type='resource', box='people') |
| `budget` | Budget | Budget line item allocated to a project | Canvas (type='resource', box='budget') |
| `dependency` | Dependency | External partnership or facility required | Canvas (type='resource', box='dependency') |

Relationship definitions within the Resource structure:

| From | To | Relationship | Meaning |
|---|---|---|---|
| people | (project entity) | `assigned_to` | Person works on this project |
| budget | (project entity) | `funds` | Budget line funds this project |
| dependency | (project entity) | `required_by` | Dependency needed for this project |

Cross-structure mapping (Logic Model → Resource):

| Source structure | Source element | Target structure | Target element | mapping_type |
|---|---|---|---|---|
| Logic Model | input | Resource | people | `generates` (when classified as people) |
| Logic Model | input | Resource | budget | `generates` (when classified as budget) |
| Logic Model | input | Resource | dependency | `generates` (when classified as dependency) |

### 2.3 Field Contracts

Each resource type defines its expected fields in the `data` JSON column of `zp_canvas_items`:

**People items** (`box = 'people'`):
```json
{
  "userId": 42,
  "capacity": 40,
  "allocations": {
    "projectId_1": 15,
    "projectId_2": 20
  }
}
```
- `description` = person name
- `assumptions` = role title
- `status` = "stub" (unassigned) | "active" (allocated)

**Budget items** (`box = 'budget'`):
```json
{
  "projectId": 7,
  "budgeted": 85000,
  "spent": 62000,
  "color": "#3E937A"
}
```
- `description` = budget line name
- `assumptions` = category (personnel, materials, facilities, etc.)
- `status` = "stub" (no amount) | "active" (amount set)

**Dependency items** (`box = 'dependency'`):
```json
{
  "partnerName": "Community College",
  "type": "facility",
  "confirmed": false
}
```
- `description` = dependency name
- `conclusion` = notes on status
- `status` = "stub" (unconfirmed) | "active" (confirmed)

### 2.4 Service Layer

ResourceStructure provides a thin service that wraps canvas access with resource-specific semantics:

```php
class ResourceStructureService
{
    // Schema queries
    public function getResourceElementTypes(): array;
    public function getFieldContract(string $typeKey): array;

    // Read methods — resource-aware wrappers around canvas service
    public function getPeopleByProgram(int $programId): array;
    public function getBudgetByProgram(int $programId): array;
    public function getDependenciesByProgram(int $programId): array;

    // Aggregation — joins canvas data with timesheets for actuals
    public function getActualHours(int $programId, string $period = 'this_week'): array;
    public function getBudgetSummary(int $programId): array;

    // Write methods — create/update resource canvas items
    public function allocatePerson(int $canvasId, array $personData): int;
    public function setBudgetItem(int $canvasId, array $budgetData): int;
    public function addDependency(int $canvasId, array $depData): int;

    // Stub seeding — called during program creation
    public function seedFromCanvasInputs(int $programId, int $canvasId, array $canvasInputs): void;
    public function getStubCompletionStatus(int $programId): array;
}
```

### 2.5 Domain Location

```
app/Domain/ResourceStructure/
├── Models/
│   └── ResourceFieldContract.php    # Defines expected JSON fields per type
├── Services/
│   ├── ResourceStructureService.php # Resource-aware canvas wrapper
│   └── ResourceRegistrar.php        # Registers "Resource" in WorkStructure registry
├── Repositories/
│   └── ResourceStructureRepository.php  # Canvas queries filtered by type='resource'
└── register.php
```

### 2.6 What It Does NOT Contain

- No new database tables (uses canvas tables)
- No UI (PgmPro handles visualization)
- No board renderer (the resource view is custom, not a standard canvas board)
- No domain-specific models for individual allocations (those are canvas items)

---

## 3. Shared Blade Components

These components live in `app/Views/Templates/components/` and are available to any board, plugin, or view across Leantime.

### 3.1 Collapsible Section

A reusable expandable/collapsible container with header, inline summary when collapsed, and animated content reveal.

**Component:** `x-collapsible`

**Props:**
| Prop | Type | Default | Description |
|---|---|---|---|
| `title` | string | required | Section header text |
| `icon` | string | null | Emoji or icon class for header |
| `default-open` | boolean | true | Initial expanded state |
| `collapsed-summary` | view | null | Blade partial shown inline when collapsed |

**Behavior:**
| Trigger | Effect |
|---|---|
| Click header | Toggle expanded/collapsed |
| Collapsed | Header shows inline summary, no bottom border |
| Expanded | Content renders below, 1px `#F0F1F3` border between header and content |
| Animation | `max-height` transition, 0.3s ease |
| Chevron | Rotates 180° on toggle, 0.2s ease |

**Styling:**
- Container: white background, 12px radius, 1px `#E8ECF0` border
- Header padding: 14px 20px
- Content padding: 18px 20px
- Title: 14px weight 700 `#1A1A2E`
- Chevron: `#9CA3AF` stroke, 16×16 SVG

**Used in:** Resource view (People, Budget), future: Logic Model stages, program dashboard sections, settings panels.

### 3.2 Proportion Bar

A horizontal stacked bar where segments fill proportionally by value.

**Component:** `x-proportion-bar`

**Props:**
| Prop | Type | Default | Description |
|---|---|---|---|
| `segments` | array | required | `[{value, color, label?, overlay?}]` |
| `total` | number | sum of values | Denominator for proportions |
| `height` | string | '8px' | Bar height |
| `radius` | string | '4px' | Border radius |
| `show-labels` | boolean | false | Show text labels inside segments |
| `track-color` | string | '#F0F1F3' | Background track color |

**Variants:**
| Variant | Height | Labels | Usage |
|---|---|---|---|
| Thin | 6-8px | No | Summary strip, collapsed summaries, budget line items |
| Medium | 28px | Yes (when segment > 12% width) | Budget expanded bar |
| With overlay | Any | Optional | V2 spent/actual layer |

**Overlay support:** Each segment can have an `overlay` object `{value, color}` that renders as a second fill within the segment (for V2 plan-vs-actual).

**Used in:** Resource summary strip, budget bar, capacity bars, collapsed section summaries, any percentage-breakdown visualization.

### 3.3 Avatar Stack

Overlapping circular avatars with initials, colored by status.

**Component:** `x-avatar-stack`

**Props:**
| Prop | Type | Default | Description |
|---|---|---|---|
| `people` | array | required | `[{initials, status}]` |
| `size` | number | 28 | Circle diameter in px |
| `overlap` | number | -6 | Negative margin for overlap |
| `max-show` | number | 8 | Max visible before "+N" |

**Status colors:**
- At capacity (`status: 'full'`): `#059669` background
- Partial (`status: 'partial'`): `#4A85B5` background
- Open/unassigned (`status: 'open'`): dashed `#D1D5DB` border, transparent background

**Used in:** Resource collapsed People summary, project cards, task assignment views.

### 3.4 Metric Cell

A labeled number with optional secondary text and mini visualization.

**Component:** `x-metric-cell`

**Props:**
| Prop | Type | Default | Description |
|---|---|---|---|
| `label` | string | required | Top label text |
| `value` | string | required | Primary number/text |
| `suffix` | string | null | Unit suffix (h, %, K) |
| `secondary` | string | null | Below-value text |
| `color` | string | '#1A1A2E' | Value color |
| `secondary-color` | string | '#9CA3AF' | Secondary text color |

**Styling:**
- Label: 11px weight 500 `#9CA3AF`
- Value: 22px weight 700
- Suffix: 12px weight 400 `#9CA3AF`
- Secondary: 11px
- Padding: 14px 18px

**Used in:** Resource summary strip (Team Capacity, Allocated, Available, People, Budget cells), any dashboard header.

---

## 4. V1 — Planning Mode

V1 answers: **"Do I have what I need to run this program?"**

### 4.1 Summary Strip

A horizontal row of `x-metric-cell` components separated by 1px dividers, inside a rounded container with `#E8ECF0` gap color.

| Cell | Label | Value | Secondary | Condition |
|---|---|---|---|---|
| Team Capacity | "Team Capacity" | `{pct}% used` | `x-proportion-bar` (thin, project-colored) | — |
| Allocated | "Allocated" | `{hours}h` | `of {capacity}h/wk` | — |
| Available | "Available" | `{remaining}h` | `{pct}% open` | Amber if > 40h |
| People | "People" | `{active}/{total}` | `{full} at capacity` | — |
| Budget | "Budget" | `${allocated}K` | `${unallocated}K unallocated` | Amber if unallocated > 0 |

### 4.2 Project Rows

Each project is a horizontal card. See Section 8.2 for exact measurements.

| Element | Position | Spec |
|---|---|---|
| Color swatch | Left | 14×14px, 4px radius, project color |
| Project name | Left of center | 14px, weight 600 |
| People count | Below name | 12px, `#9CA3AF` |
| Hours | Right of center | 24px weight 700 + 13px suffix |
| Capacity bar | Far right | `x-proportion-bar` (thin, 100px wide, single project color) |
| Capacity % | Below bar | 10px `#9CA3AF` |

### 4.3 People Section

Wrapped in `x-collapsible` with `collapsed-summary` showing `x-avatar-stack` + counts + `x-proportion-bar`.

#### Person Containers (Expanded)

Each person renders as a vertical container that fills from the bottom. This is the signature visualization — not a shared component, it's resource-view specific.

**Container spec:**
- Width: 64px, Height: 200px (constant)
- Border radius: 8px
- Background: `#F0F2F5` (has allocations) or dashed `#D1D5DB` border (empty)
- Tick marks at 25%, 50%, 75%: `1px solid rgba(0,0,0,0.04)`

**Fill segments** (bottom to top, one per project):
- Height: `(hours / capacity) × 100%`
- Background: solid project color
- Separator: 1.5px `rgba(255,255,255,0.5)` between segments
- Label: `{hours}h` white, 12px weight 700 (when segment > 14% height)

**Percentage badge:** Floating pill at fill line.
- Background: `#1A1A2E` (partial) or `#059669` (full)
- Font: 10px weight 700, white, padding 2px 8px, radius 10px

**Footer:** `{allocated}/{capacity}h` — 12px weight 700

**Empty state:** Dashed border, "+" (20px) + "Assign" (10px)

### 4.4 Budget Section

Wrapped in `x-collapsible` with `collapsed-summary` showing text metrics + `x-proportion-bar`.

#### Expanded State

**Stacked bar:** `x-proportion-bar` (medium variant, 28px, with labels)

**Line items:** Color swatch (10×10) + name + amount + `x-proportion-bar` (thin, 100px) + percentage

**Unallocated row:** Shows when `TOTAL_BUDGET - sum(budgeted) > 0`

---

## 5. V2 — Plan vs. Actual Mode

V2 answers: **"Is reality matching my plan?"**

V2 preserves every element of V1 and adds a second data layer on top. V2 overlays are additive — they never modify V1 elements.

### 5.1 Reading Guide

Appears only in V2, between auto-fill and summary strip. Background `#FAFBFC`, border `1px solid #F0F1F3`. Visual legend: solid swatch = planned, light overlay swatch = actual logged, numbers format = actual/planned.

### 5.2 Summary Strip Additions

Adds one `x-metric-cell` after "Allocated":

| Cell | Label | Value | Secondary |
|---|---|---|---|
| Actual | "Actual (this wk)" | `{actual}h` | Variance text, color-coded |

Budget cell changes: primary shows spent, secondary shows "of ${budgeted}K budgeted".

### 5.3 Project Row Additions

Replaces single hours display with Planned column, Actual column, and variance badge. Capacity bar gains dark overlay for actual proportion.

**Variance color logic:**
- Green `#059669` / bg `#ECFDF5`: variance ≤ +2h
- Amber `#D97706` / bg `#FEF3C7`: variance < -5h
- Red `#DC2626` / bg `#FEE2E2`: variance > +2h

### 5.4 Person Container Additions

Each segment gains an **actual overlay**:
- Fill: `rgba(255,255,255,0.22)` from bottom of segment
- Height: `(actual / planned) × 100%` of segment
- Border: `2px solid rgba(255,255,255,0.6)` at top when < 100%
- Label changes to `{actual}/{planned}` in 10px (smaller to fit)

Footer changes: primary = `{actual}/{allocated}h`, variance line = "{N}h under/over" or "On track"

### 5.5 Budget Section Additions

Stacked bar: `x-proportion-bar` with overlay support — each segment gets spent overlay `rgba(0,0,0,0.12)`. Labels change to `${spent}K / ${budgeted}K`.

Line items gain: burn rate badge (`{pct}% spent`), spent column, proportion bar overlay.

---

## 6. Canvas Auto-Fill Preview

### 6.1 Purpose

Shows how Logic Model Inputs stage items were auto-classified into resource canvas items during program creation. Toggled via "Show Canvas Auto-Fill" button.

### 6.2 Classification Rules

| Pattern | Classification | Canvas item created |
|---|---|---|
| `/funding\|grant\|budget\|allocation/i` | budget | `box='budget'`, `status='stub'` |
| `/staff\|people\|manager\|coordinator\|specialist\|director/i` | people | `box='people'`, `status='stub'` |
| `/partner\|organization\|vendor\|facility\|agreement/i` | dependency | `box='dependency'`, `status='stub'` |

Count extraction: "3 case managers" → creates 3 separate people canvas items.
Amount extraction: "$250K annual budget" → populates `data.budgeted` field.

### 6.3 Layout

Two-column mapping: left shows original canvas inputs with classification dots, right shows generated resource stubs grouped by category with completion prompts (amber badges: "Amount?", "Who? Hours?", "Status?").

### 6.4 Setting Up → Ongoing

Program starts with `status='stub'` items. Dashboard prompts completion. As stubs transition to `status='active'`, program enters Ongoing mode.

---

## 7. Data Flow

### 7.1 Creation Flow

```
Logic Model Canvas (zp_canvas_items, type='logicmodel')
  │
  │ StrategyPro wizard
  │
  ├─→ Activities → Tasks           (WorkStructure mapping)
  ├─→ Outputs → Milestones          (WorkStructure mapping)
  ├─→ Outcomes → Goals              (WorkStructure mapping)
  ├─→ Impact → Project/Program      (WorkStructure mapping)
  │
  └─→ Inputs → Resource Canvas Items  (ResourceStructure mapping)
       │
       │ Auto-classified: box='people' / 'budget' / 'dependency'
       │ Created with status='stub' in Resource Canvas (zp_canvas_items, type='resource')
       │
       └─→ Entityrelations link canvas input → resource item (seeded_from)
```

### 7.2 Read Flow

```
PgmPro Resource View
  │
  ├─→ ResourceStructureService.getPeopleByProgram()
  │     └─→ Canvas query: type='resource', box='people', projectId=program
  │
  ├─→ ResourceStructureService.getBudgetByProgram()
  │     └─→ Canvas query: type='resource', box='budget', projectId=program
  │
  └─→ ResourceStructureService.getActualHours()  (V2 only)
        └─→ JOIN zp_timesheets ON projectId + userId, aggregate by period
```

### 7.3 Data Sources

| Data | Source | How |
|---|---|---|
| Projects | `zp_projects` via parent | Existing Projects service |
| People allocations | `zp_canvas_items` (type='resource', box='people') | ResourceStructure service |
| Budget items | `zp_canvas_items` (type='resource', box='budget') | ResourceStructure service |
| Dependencies | `zp_canvas_items` (type='resource', box='dependency') | ResourceStructure service |
| Actual hours (V2) | `zp_timesheets` grouped by project + user | ResourceStructure service |
| Canvas stubs | Logic Model items via `zp_entityrelations` | At program creation |
| User details | `zp_user` | Existing Users service |

---

## 8. Visual Design Specification

### 8.1 Color Palette

**Project colors:** `#3E937A` (green), `#C09035` (amber), `#8E6AAD` (purple)

**Status:** `#059669` good, `#D97706` warning, `#DC2626` critical, `#9CA3AF` neutral

**Surfaces:** `#F8F9FB` page, `#ffffff` cards, `#F0F1F3` tracks, `#E8ECF0` borders, `#1A1A2E` primary text

### 8.2 Typography

| Element | Size | Weight | Color |
|---|---|---|---|
| Page title | 22px | 700 | `#1A1A2E` |
| Program name | 12px | 500 | `#9CA3AF` |
| Section headers | 14px | 700 | `#1A1A2E` |
| Summary numbers | 22px | 700 | `#1A1A2E` |
| Summary labels | 11px | 500 | `#9CA3AF` |
| Project row name | 14px | 600 | `#1A1A2E` |
| Project row hours | 24px | 700 | `#1A1A2E` |
| Container segment (V1) | 12px | 700 | `#ffffff` |
| Container segment (V2) | 10px | 700 | `#ffffff` |
| Container person name | 12px | 600 | `#1A1A2E` |
| Budget line name | 13px | 500 | `#4B5563` |
| Budget line amount | 14px | 700 | `#1A1A2E` |

### 8.3 Spacing & Sizing

| Element | Spec |
|---|---|
| Max content width | 960px centered |
| Page padding | 32px × 24px |
| Container | 64px wide × 200px tall, 8px gap, 8px radius |
| Cards | 12px radius, 1px `#E8ECF0` border |
| Capacity bar (row) | 100px × 8px × 4px radius |
| Budget bar | 28px × 8px radius |
| Swatches | 14×14 (rows), 10×10 (budget lines) |
| Avatar stack | 28px circles, -6px overlap |

### 8.4 V2 Visual Encoding

| Element | Encoding |
|---|---|
| Container actual overlay | `rgba(255,255,255,0.22)`, border `2px solid rgba(255,255,255,0.6)` |
| Budget actual overlay | `rgba(0,0,0,0.12)` |
| Budget line bar overlay | `rgba(0,0,0,0.15)` |
| Variance badges | Colored pill: green/amber/red backgrounds |

---

## 9. Interaction Patterns

### 9.1 Unified Hover

Single `hoveredProject` state shared across all sections. Dimming: rows `0.4`, segments `0.12`, budget lines `0.3`. Labels in dimmed segments: `opacity: 0`.

### 9.2 Collapsible Sections

Via `x-collapsible` shared component. People defaults expanded, Budget defaults expanded. Collapsed inline summaries show `x-avatar-stack`, counts, `x-proportion-bar`.

### 9.3 Version Toggle

Segmented control in header. V1 default. Components accept `version` prop. V1 code paths never modified by V2.

---

## 10. Edge Cases

- **New program:** All zeros, empty states, auto-fill toggle visible if from canvas
- **Unassigned people:** Dashed container, "?", not counted in capacity
- **Over-allocated:** Badge turns red, fill clipped at 100% visually
- **V2 with no actuals:** Overlays empty, all variance shows "under"
- **Budget fully allocated:** No unallocated row, $0 in green

---

## 11. Future Considerations (Not in Scope)

- Project creation/editing flow and people assignment UI
- Historical plan vs. actual (multi-week trends)
- Variance threshold configuration and alerts
- Dependency tracking visualization
- Gap analysis automation
- Responsive/mobile layout, print/export, accessibility audit
- ACM personal capacity layer (`11-ADDENDUM-RESOURCE-DECISIONS.md`)

---

## 12. Reference Implementation

**File:** `ResourceV1V2.jsx` (969 lines) — pixel-accurate React prototype with hardcoded sample data. Production implementation reads from Resource Canvas via ResourceStructure service.
