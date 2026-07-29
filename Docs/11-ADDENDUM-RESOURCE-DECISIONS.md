# 11-ADDENDUM-RESOURCE-DECISIONS.md

## Addendum: Resource Management Boundaries & Plugin Decisions

**Applies to:** `07-WORKSTRUCTURE.md`, `08-WORKSTRUCTURE-PROMPT.md`, `10-ADDENDUM-WORKSTRUCTURE-ARCHITECTURE.md`
**Date:** February 21, 2026
**Trigger:** Discussion of where resource management lives, program-level work creation, and future adaptive capacity
**Status:** APPROVED

---

## Decisions

### 1. ResourceStructure is a Core Data Layer

ResourceStructure follows the same pattern as WorkStructure: a thin core domain that stores the data (allocation tables, models, basic query service). It does not contain management UI or business logic. Any plugin or view can read from it.

```
app/Domain/ResourceStructure/
├── Models/
├── Services/
├── Repositories/
└── register.php
```

Deferred to PgmPro redesign. Not built with the initial WorkStructure delivery.

### 2. Resource Management Lives in PgmPro

There is no separate ResourceManagement plugin. PgmPro owns the resource management experience — allocation UI, budget tracking, people assignment across projects. It writes to ResourceStructure core tables.

Rationale: Resource allocation is inherently a program-level decision. A program manager allocating budget and people across child projects IS program management. Separating it into its own plugin creates an orphan with no natural home.

### 3. Program View: Aggregate Read + Resource Write

The program-level view has two modes:

**Read-only aggregation** of work data from child entities:
- Goals (from Goalcanvas, linked via entityrelations)
- Project status (from child projects)
- Timeline (milestones rolled up from child projects)
- Source link back to originating canvas (Logic Model or other)

**Read-write resource management** (the program manager's workspace):
- Allocate people to child projects
- Set budget envelopes per project
- Track allocation vs. actuals (hours, budget)

Without the resource features, the program view is a curated read-only dashboard. Resource management upgrades it to an actual workspace.

### 4. Routing-Dependent Mapping

When the wizard creates work, the canvas-to-entity mapping changes based on the routing decision:

| Canvas Stage | → Single Project | → Program |
|---|---|---|
| Impact | Project description | Program description |
| Outcomes | Goals | Goals (program-level) |
| Outputs | Milestones | Goals (program-level KPIs) |
| Activities | Tasks | Projects (one per activity) |
| Inputs | Resource notes | Resource allocations (when resource features ship) |

This means the wizard must ask routing BEFORE presenting the mapping. Routing moves from Step 4 to Step 1 in the wizard flow.

**Note to Claude Code:** The wizard step order in `08-WORKSTRUCTURE-PROMPT.md` Section 2.5 changes. Routing selection is now the first interaction. The scope/deliverables/criteria steps that follow adapt their labels and grouping based on whether the user chose project or program routing.

### 5. Logic Model Canvas Appears at Both Levels

**StrategyPro plugin** provides the Logic Model Canvas board type. It can be used at:

- **Strategy level:** Planning and framing (the primary creation context)
- **Program level:** As the source canvas linked to the program. Not embedded or duplicated — the program view links back to it and shows live status indicators on canvas items based on entityrelations to child project data.

---

## Plugin Boundary Summary

### StrategyPro (Strategy Management)

**Owns:**
- Logic Model Canvas board type
- Work creation wizard (canvas → project or program)
- Cross-structure mappings (Logic Model → Project, Logic Model → Program)
- Living Link (bidirectional canvas ↔ work navigation)
- Future: additional canvas types, board templates, stakeholder views

**Does not own:**
- Resource management (PgmPro)
- Program dashboard aggregation (PgmPro)
- Personal capacity management (future ACM)

### PgmPro (Program Management)

**Owns:**
- Program dashboard (aggregate view of child projects, goals, timelines)
- Resource management UI (people allocation, budget envelopes, capacity tracking)
- Writes to ResourceStructure core tables
- Program-level goal surfacing (reads goals linked via entityrelations)

**Ships in phases:**
1. Aggregate read-only dashboard + basic resource data inputs (fast follow to WorkStructure)
2. Full resource allocation and tracking features
3. Allocation vs. actuals reporting

**Does not own:**
- Logic Model Canvas (StrategyPro)
- Work creation wizard (StrategyPro)
- Personal capacity or individual workload (future ACM)

### Future: Adaptive Capacity Management (ACM)

**Concept:** Personal capacity management that goes beyond simple hour allocation. Helps the user manage their day-to-day energy, focus, and workload based on:

- Motivational profile and energy patterns (enmotiv integration)
- Mental/emotional capacity signals
- External biometric sources (Apple Health, Oura)
- Motivational overlap — alignment between assigned work and what energizes the user

**Reads from:**
- ResourceStructure (what's been allocated to this user)
- WorkStructure (what work exists and its state)
- Timesheets (actual hours logged)
- enmotiv (motivational profile, behavioral patterns)
- External APIs (health/biometric data)

**Does not duplicate:**
- Program-level resource allocation (that's PgmPro)
- Project-level task assignment (that's existing Tickets domain)

ACM is the personal companion layer. PgmPro allocates resources top-down. ACM helps the individual manage their capacity bottom-up. They read from the same data but serve different users with different questions.

---

## Data Flow Summary

```
StrategyPro                    PgmPro                      ACM (future)
  │                              │                             │
  │ Creates work from            │ Allocates resources         │ Manages personal
  │ canvas via wizard            │ across projects             │ capacity & energy
  │                              │                             │
  ▼                              ▼                             ▼
┌─────────────────────────────────────────────────────────────────────┐
│                          CORE                                       │
│                                                                     │
│  WorkStructure          ResourceStructure        Entityrelations    │
│  (structure schemas)    (allocation tables)      (instance links)   │
│                                                                     │
│  Tickets    Goalcanvas    Projects    Timesheets    Users           │
│  (tasks,    (goals)       (projects,  (hours)       (people)       │
│  milestones)              programs)                                 │
└─────────────────────────────────────────────────────────────────────┘
```

---

## No Other Changes

The WorkStructure core domain architecture (addendum 10), the mapping correction (addendum 09), and the wizard component/UX spec (08) remain valid. This addendum adds:

- Routing-dependent mapping table
- Wizard step reorder (routing first)
- PgmPro as the resource management home
- ACM as the future personal capacity layer
- Plugin boundary definitions
