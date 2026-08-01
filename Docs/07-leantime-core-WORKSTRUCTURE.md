# WorkStructure Core + Content-to-Work Wizard

**Product Requirements Document**

| Field | Value |
|---|---|
| Product | Leantime |
| Feature | WorkStructure Core + Content-to-Work Wizard |
| Version | 3.0 |
| Date | February 20, 2026 |
| Author | Gloria Folaron |
| Status | Draft |
| Core Domain | `app/Domain/WorkStructure/` |
| Plugin Layer | StrategyPro, Copilot, future plugins |
| Prerequisites | Phase 1: Core Logic Model Board |

---

## 1. Overview

### 1.1 Problem Statement

Leantime users define strategy through boards (Logic Model Canvas, Ideas Board, Goal Canvas) and need to translate that thinking into actionable work. Today this requires manual re-entry. But beyond the creation problem, there is no standard way for any part of Leantime to describe a bundle of work — its milestones, tasks, goals, relationships, and context — in a portable, normalized format. Every feature that needs to create, read, or present work structures reinvents its own approach.

### 1.2 Solution

Two layers:

**Layer 1 — WorkStructure (Core Domain):** A normalized interchange format for describing work and a generator service for creating Leantime entities from it. This lives in core and provides anchor points that any plugin can hook into.

**Layer 2 — Plugin Implementations:** Source adapters (Logic Model, Ideas, Goals), wizard UIs, Copilot flows, and view renderers. Each plugin produces or consumes WorkStructures through the core anchor.

### 1.3 The Anchor Metaphor

WorkStructure is a tow truck anchor in core. Plugins hook into it and build on top. The anchor does not know about Logic Models, Ideas, Copilot, or stakeholder dashboards. It only knows the shape of work and how to create it or describe it.

> **Core provides the hitch point. Plugins bring the vehicle.**
>
> Write direction: any adapter → WorkStructure → WorkGenerator → creates entities.
> Read direction: existing project → WorkStructure → any renderer → presents work.
> Same shape, opposite flow. One interchange format serves all directions.

### 1.4 Design Principles

- **Core is thin.** WorkStructure defines the contract and provides the generator. No UI, no board-specific logic, no wizard steps.
- **Plugins are thick.** All board-specific adapters, wizard flows, Copilot conversations, and view renderers live in plugins.
- **Canvas is source of truth.** Adapters read from boards; they never maintain parallel data models.
- **Two entry points, one outcome.** Manual fill and Copilot guided flow both produce WorkStructures that feed the same generator.
- **Bidirectional by design.** WorkStructure supports both writing new work and reading existing work, enabling creation and presentation through the same format.

### 1.5 Relationship to Existing PRDs

This document extends the Logic Model Canvas PRD (Docs/00 through 06). It covers the core WorkStructure domain and the first plugin implementation (StrategyPro wizard). It does not duplicate the core board spec, plugin features, or Copilot conversation design.

---

## 2. Core Domain: WorkStructure

### 2.1 Domain Location

The WorkStructure domain lives at `app/Domain/WorkStructure/` alongside the existing 56 domain modules. It follows the standard Leantime domain structure: Services, Models, Repositories, Contracts.

```
app/Domain/WorkStructure/
├── Contracts/
│   └── WorkStructureAdapter.php       # Interface for source adapters
├── Models/
│   ├── WorkStructure.php              # The normalized work bundle
│   ├── ProposedMilestone.php
│   ├── ProposedTask.php
│   ├── ProposedGoal.php
│   ├── ProposedResource.php
│   ├── SourceMeta.php
│   ├── RoutingDecision.php
│   └── EntityLink.php
├── Services/
│   ├── WorkGenerator.php              # Write direction: WorkStructure → entities
│   ├── WorkStructureReader.php        # Read direction: entities → WorkStructure
│   └── AdapterRegistry.php            # Discovers registered adapters
├── Repositories/
│   └── EntityLinkRepository.php       # Generic entity link storage
├── Events/
│   ├── WorkStructureCreated.php
│   ├── WorkGenerationStarted.php
│   ├── WorkGenerationCompleted.php
│   ├── WorkGenerationFailed.php
│   ├── EntityLinked.php
│   ├── EntityUnlinked.php
│   └── WorkStructureRead.php
└── register.php
```

### 2.2 The WorkStructure Contract

WorkStructure is a normalized data object that describes a proposed or existing bundle of work. It is the interface between producers (adapters, project readers) and consumers (generators, renderers).

#### WorkStructure Shape

| Property | Type | Purpose |
|---|---|---|
| id | string\|null | Null for proposed work, populated for existing work reads |
| title | string | Project or work bundle name |
| objective | string | Strategic objective / north star description |
| source | SourceMeta | Where this structure came from (board type, board ID, adapter) |
| routing | RoutingDecision | What kind of entity to create (project, program, strategy, in-place) |
| milestones | ProposedMilestone[] | Proposed or existing milestones |
| tasks | ProposedTask[] | Proposed or existing tasks, each referencing a parent milestone |
| goals | ProposedGoal[] | Proposed or existing goals with metric definitions |
| resources | ProposedResource[] | Dependencies, budget items, team requirements |
| links | EntityLink[] | Bidirectional links between source items and work entities |
| metadata | array | Extensible key-value pairs for plugin-specific data |

#### SourceMeta

| Property | Type | Purpose |
|---|---|---|
| boardType | string | Canvas type identifier (e.g., 'logicmodel', 'ideas', 'goalcanvas') |
| boardId | int | The specific board instance ID |
| adapterClass | string | Fully qualified class name of the adapter that produced this |
| generatedAt | datetime\|null | When this structure was produced |
| generatedBy | int\|null | User who triggered production |

#### RoutingDecision

| Property | Type | Purpose |
|---|---|---|
| type | enum | Values: `in_place`, `project`, `program`, `strategy` |
| parentId | int\|null | Parent entity ID (project, program, or strategy to nest under) |
| projectId | int\|null | Existing project ID for `in_place` routing |

#### Sub-object Field Mapping

Each sub-object (ProposedMilestone, ProposedTask, ProposedGoal, ProposedResource) carries the fields needed for entity creation, plus a `sourceItemId` that traces back to the originating board item. Field shapes map to existing Leantime service method parameters (`Tickets::quickAddMilestone`, `Tickets::quickAddTicket`, `Goalcanvas::createGoal`).

### 2.3 WorkStructureAdapter Interface

The core domain defines an interface that any plugin can implement to produce WorkStructures from board data:

```php
interface WorkStructureAdapter
{
    /** Board type identifier (e.g., 'logicmodel', 'ideas') */
    public function getSourceType(): string;

    /** Can this board produce a meaningful WorkStructure? */
    public function canAdapt(int $boardId): bool;

    /** Evaluate what's populated vs. missing */
    public function assessCompleteness(int $boardId): CompletenessReport;

    /** Read the board and produce a normalized WorkStructure */
    public function adapt(int $boardId): WorkStructure;
}
```

Plugins register their adapters through Leantime's event system. The core domain maintains a registry of available adapters via `AdapterRegistry`, allowing any part of the system to discover what boards can produce WorkStructures.

### 2.4 WorkGenerator Service

Takes a WorkStructure and creates Leantime entities. Thin orchestration over existing services:

| Method | What It Does | Services Called |
|---|---|---|
| `generate(WorkStructure)` | Full generation: creates all entities | Orchestrates all methods below in order |
| `createProject(WorkStructure)` | Creates the project entity | `Projects::addProject()` |
| `createMilestones(projectId, Milestone[])` | Creates milestones under the project | `Tickets::quickAddMilestone()` |
| `createTasks(projectId, Task[])` | Creates tasks under their milestones | `Tickets::quickAddTicket()` |
| `createGoals(projectId, Goal[])` | Creates a goal board and goals | `Goalcanvas::createGoalboard()`, `createGoal()` |
| `recordLinks(EntityLink[])` | Records source-to-entity links | Inserts into entity links table |

The `generate()` method respects the routing decision: `in_place` skips project creation and uses the existing project ID; `project`, `program`, and `strategy` create the appropriate entity type in `zp_projects` with the correct `type` and `parent` fields.

### 2.5 WorkStructureReader Service

The reverse operation: reads existing Leantime entities and produces a WorkStructure. This enables the read direction — any renderer can consume the same format.

| Method | What It Does | Sources |
|---|---|---|
| `fromProject(projectId)` | Reads a full project into a WorkStructure | Project, its milestones, tasks, goals, links |
| `fromMilestone(milestoneId)` | Reads a single milestone scope | Milestone, its tasks, related goals |
| `fromProgram(programId)` | Reads a program with child projects | Program entity, child projects via parent FK |

This is the anchor for view-only plugins, stakeholder dashboards, reporting tools, and any feature that needs to present work status without knowing the internal entity model.

### 2.6 Entity Links Table

The core domain owns a generic entity linking table that tracks bidirectional connections between source items and work entities:

| Field | Type | Purpose |
|---|---|---|
| id | int (PK) | Auto-increment |
| source_type | varchar(50) | Source system: 'logicmodel', 'ideas', 'goalcanvas', etc. |
| source_item_id | int | ID in the source system |
| target_entity_type | varchar(50) | Leantime entity: 'project', 'milestone', 'ticket', 'goal' |
| target_entity_id | int | ID of the Leantime entity |
| link_type | varchar(30) | Relationship: 'generated_from', 'maps_to', 'tracks' |
| created_by | int (FK) | User who created the link |
| created_at | datetime | Timestamp |

> **Why Entity Links Live in Core**
>
> Entity links are not Logic Model–specific. An Ideas board item linked to a task, a Goal canvas goal linked to a milestone, a stakeholder view linked to a project — these all need the same table. Putting it in core means every plugin uses the same linking mechanism, and queries like "show me everything linked to this milestone" work across all source types.

---

## 3. Core Events

The WorkStructure domain dispatches class-based events (following the migration direction documented in CLAUDE.md) that any plugin can listen to:

| Event Class | When Fired | Payload |
|---|---|---|
| WorkStructureCreated | An adapter produces a new WorkStructure | WorkStructure, source info, user |
| WorkGenerationStarted | `generate()` is called | WorkStructure, routing decision, user |
| WorkGenerationCompleted | All entities successfully created | WorkStructure with populated entity IDs, user |
| WorkGenerationFailed | Entity creation failed | WorkStructure, error details, partial results |
| EntityLinked | A source-to-entity link is recorded | Link details |
| EntityUnlinked | A link is removed | Link details |
| WorkStructureRead | `fromProject`/`fromMilestone`/`fromProgram` called | WorkStructure, reader context |

These events are the hook points for plugins. StrategyPro listens to `WorkGenerationCompleted` to record activity log entries. Copilot listens to `WorkStructureCreated` to log conversation context alongside the structure. A future analytics plugin could listen to `WorkStructureRead` to track stakeholder dashboard views.

---

## 4. Strategy Plugin: Logic Model Wizard

The first consumer of the core WorkStructure domain. Lives entirely in `app/Plugins/StrategyPro/`.

### 4.1 Entry Points

**Entry Point A: Manual Fill, Then Generate.** User fills out the Logic Model Canvas through the standard board UI. When ready, they click "Create Work from Canvas" in the board toolbar (visible when Strategy Plugin is active). This launches the translation wizard with canvas data pre-loaded.

**Entry Point B: Copilot Guided Flow (Phase 3).** The Copilot walks the user through filling the canvas conversationally, then suggests creating work. Both paths produce the same WorkStructure through the Logic Model adapter.

### 4.2 Logic Model Source Adapter

The StrategyPro plugin registers a `LogicModelAdapter` that implements the core `WorkStructureAdapter` interface:

| Canvas Stage | WorkStructure Property | Mapping |
|---|---|---|
| Impact (Stage 5) | `objective` | Item title → strategic objective / project description |
| Outcomes (Stage 4) | `goals[]` | Each item → goal with metrics (startValue, endValue, metricType) |
| Activities (Stage 2) | `milestones[]` | Each item → milestone with timeframe mapping |
| Outputs (Stage 3) | `tasks[]` | Each item → task, grouped under parent Activity's milestone |
| Inputs (Stage 1) | `resources[]` | Items → resource notes, budget entries, team requirements |

The adapter's `assessCompleteness()` method evaluates which stages have items and returns a report that drives wizard weight (see 4.4).

### 4.3 Translation Wizard (4 Steps)

**Step 1 — Scope Confirmation:** Shows Activities mapped as proposed milestones. User can reorder, group/split activities, set timeframes, or remove items.

**Step 2 — Deliverables & Tasks:** For each confirmed milestone, shows associated Outputs as proposed tasks. User can add, edit, remove tasks, assign owners, flag dependencies.

**Step 3 — Success Criteria:** Maps Outcomes to goals on the Goalcanvas. User confirms goal-to-milestone links, sets metric types and targets, adds qualitative definitions.

**Step 4 — Review & Generate:** Tree view of the complete proposed structure. Includes routing decision. One-click "Generate" calls `WorkGenerator.generate()` with the confirmed WorkStructure.

### 4.4 Adaptive Wizard Weight

| Canvas State | Wizard Behavior | Duration |
|---|---|---|
| Rich (4–5 stages, multiple items) | Single review screen, expandable sections | 30–60 seconds |
| Moderate (2–3 stages) | Full 4-step flow, pre-filled where data exists | 2–4 minutes |
| Sparse (0–1 stages) | Extended flow with prompts for missing data | 5–10 minutes |
| Lightweight escape | Skip wizard, create minimal project shell | Instant |

### 4.5 Routing Decision

Before generation, the wizard asks what kind of work to create:

| Option | Entity Type | Plugin Required | zp_projects Fields |
|---|---|---|---|
| Work in current project | `in_place` | None | Uses `session('currentProject')` |
| Its own project | `project` | None | `type='project'`, optional parent |
| A program | `program` | PgmPro | `type='program'`, parent=strategy |
| A strategic initiative | `strategy` | StrategyPro | `type='strategy'`, auto-creates focus areas |

Options 3 and 4 only appear when their plugins are active. The routing decision is stored in the WorkStructure's `RoutingDecision` and passed to the core `WorkGenerator`.

### 4.6 Living Link

After generation, StrategyPro maintains bidirectional connections through the core entity links table.

**Forward (Canvas → Work):** Canvas items show a linked entity indicator (folder icon + name in card footer). Card modal shows full link details. Click navigates to the linked project, milestone, or goal.

**Backward (Work → Canvas):** Project dashboard shows "Strategy Source" link to the Logic Model. Milestone detail shows originating Activity item. Goal detail shows originating Outcome item.

**Change Detection:** When canvas items with linked entities are modified, StrategyPro fires a notification: "Your Logic Model has changed. Activity X is linked to Milestone Y. Review impact?" Links to a diff view. User can update entities, dismiss, or unlink.

**Regeneration:** For major canvas changes, user can re-run the wizard. It detects existing links and offers three paths: update existing entities, add new for new canvas items, or archive and replace.

---

## 5. Wizard Component Architecture

The wizard UI is built with Blade components and HTMX, targeting the architecture direction documented in CLAUDE.md: components for reusable entities, HTMX for async loading, Blade for all new templates.

### 5.1 Plugin File Structure

```
app/Plugins/StrategyPro/
├── Hxcontrollers/
│   └── Wizard/
│       ├── Start.php                  # Launches wizard, runs adapter
│       ├── ScopeStep.php              # Step 1: milestone confirmation
│       ├── DeliverablesStep.php       # Step 2: task mapping
│       ├── CriteriaStep.php           # Step 3: goal mapping
│       ├── ReviewStep.php             # Step 4: review + generate
│       └── Routing.php                # Routing decision handler
├── Templates/
│   ├── components/
│   │   ├── wizard-shell.blade.php     # Outer wizard frame + progress
│   │   ├── wizard-step.blade.php      # Step wrapper with nav
│   │   ├── milestone-row.blade.php    # Draggable milestone item
│   │   ├── task-row.blade.php         # Task item within milestone
│   │   ├── goal-row.blade.php         # Goal mapping row
│   │   ├── resource-note.blade.php    # Resource/dependency item
│   │   ├── entity-tree.blade.php      # Review tree view
│   │   ├── routing-picker.blade.php   # Routing decision selector
│   │   ├── completeness-badge.blade.php # Canvas readiness indicator
│   │   └── entity-link-badge.blade.php  # Linked entity indicator for cards
│   └── partials/
│       ├── wizard-scope.blade.php     # Step 1 content
│       ├── wizard-deliverables.blade.php # Step 2 content
│       ├── wizard-criteria.blade.php  # Step 3 content
│       └── wizard-review.blade.php    # Step 4 content
├── Services/
│   ├── LogicModelAdapter.php          # Implements WorkStructureAdapter
│   └── WizardStateService.php         # Cache-based wizard state
├── Events/
│   └── Htmx/
│       └── HtmxWizardEvents.php       # HTMX event enum
└── register.php
```

### 5.2 Composing from Shared Components

The wizard composes from existing `app/Views/Templates/components/` rather than reinventing UI primitives:

| Shared Component | Usage in Wizard |
|---|---|
| `<x-global::elements.card>` | Wraps each wizard step content area, uses `header` and `actions` slots for step title and nav buttons |
| `<x-global::selectable>` | Routing decision picker (radio-style selection cards for "What kind of work is this?") |
| `<x-global::progress>` | Wizard step progress bar (value=currentStep, max=totalSteps) |
| `<x-global::elements.button-group>` | Step navigation (Back / Next / Generate) |
| `<x-global::button>` | Primary and secondary actions within steps |
| `<x-global::accordion>` | Collapsed milestone sections in the review step |
| `<x-global::forms.select>` | Milestone timeframe pickers, assignee dropdowns |
| `<x-global::forms.input>` | Inline editing of milestone/task titles |
| `<x-global::forms.checkbox>` | Item selection for what to include in generation |
| `<x-global::elements.empty-state>` | Shown when a stage has no items to map |
| `<x-global::elements.status-indicator>` | Canvas completeness per stage |
| `<x-global::loader>` | Generation in-progress state |
| `<x-global::feedback.alert>` | Validation messages, sparse canvas warnings |
| `<x-global::avatar>` | Assignee display in task rows |
| `<x-global::stageflow.card>` | Reused in review step to show the canvas source alongside proposed work |
| `<x-global::stageflow.item>` | Individual canvas items shown as source context |
| `<x-global::badge>` | Entity type labels (milestone, task, goal) in the review tree |

### 5.3 Plugin-Specific Components

Components that are wizard-specific and live in the StrategyPro plugin:

#### `<x-strategypro::wizard-shell>`

The outer frame. Renders the progress bar, step indicators, and the HTMX target container where step partials swap in.

```html
@props(['currentStep' => 1, 'totalSteps' => 4, 'boardId' => null])

<div class="tw:max-w-4xl tw:mx-auto tw:py-8" id="wizardFrame">
    {{-- Progress --}}
    <x-global::progress :value="$currentStep" :max="$totalSteps" size="sm" />

    {{-- Step indicators --}}
    <div class="tw:flex tw:justify-between tw:mt-4 tw:mb-8">
        @foreach(['Scope', 'Deliverables', 'Success Criteria', 'Review'] as $i => $label)
            <span class="tw:text-sm {{ $currentStep > $i + 1 ? 'tw:text-success' : ($currentStep == $i + 1 ? 'tw:font-bold' : 'tw:text-base-300') }}">
                {{ $label }}
            </span>
        @endforeach
    </div>

    {{-- Step content swaps here via HTMX --}}
    <div id="wizardStep">
        {{ $slot }}
    </div>
</div>
```

#### `<x-strategypro::wizard-step>`

Wraps individual step content with consistent navigation.

```html
@props([
    'step' => 1,
    'totalSteps' => 4,
    'boardId' => null,
    'title' => '',
    'subtitle' => '',
    'prevUrl' => null,
    'nextUrl' => null,
])

<x-global::elements.card :title="$title">
    <x-slot name="header">
        <div>
            <h2 class="tw:card-title">{{ $title }}</h2>
            @if($subtitle)
                <p class="tw:text-sm tw:text-base-content/60">{{ $subtitle }}</p>
            @endif
        </div>
    </x-slot>

    {{ $slot }}

    <x-slot name="actions">
        <x-global::elements.button-group>
            @if($prevUrl)
                <x-global::button
                    hx-get="{{ $prevUrl }}"
                    hx-target="#wizardStep"
                    type="secondary">Back</x-global::button>
            @endif
            @if($nextUrl)
                <x-global::button
                    hx-get="{{ $nextUrl }}"
                    hx-target="#wizardStep"
                    hx-include="#wizardStep"
                    type="primary">{{ $step == $totalSteps ? 'Generate' : 'Next' }}</x-global::button>
            @endif
        </x-global::elements.button-group>
    </x-slot>
</x-global::elements.card>
```

#### `<x-strategypro::milestone-row>`

A draggable milestone item within Step 1, showing the Activity source and editable fields.

#### `<x-strategypro::task-row>`

A task item within Step 2, nested under its parent milestone, with inline edit and assignee picker.

#### `<x-strategypro::goal-row>`

A goal mapping row in Step 3, linking an Outcome to metric fields.

#### `<x-strategypro::routing-picker>`

Composes `<x-global::selectable>` cards for the routing decision. Only shows plugin-gated options when those plugins are active.

```html
<div class="tw:grid tw:grid-cols-2 tw:gap-4">
    <x-global::selectable name="routingType" value="in_place" id="route-inplace"
        :selected="$default === 'in_place' ? 'true' : 'false'">
        <x-slot name="label">Work in current project</x-slot>
        Add milestones and tasks to {{ $currentProjectName }}
    </x-global::selectable>

    <x-global::selectable name="routingType" value="project" id="route-project"
        :selected="$default === 'project' ? 'true' : 'false'">
        <x-slot name="label">Its own project</x-slot>
        Create a new project for this work
    </x-global::selectable>

    @if($hasPgmPro)
    <x-global::selectable name="routingType" value="program" id="route-program">
        <x-slot name="label">A program</x-slot>
        Span multiple projects under a program
    </x-global::selectable>
    @endif

    @if($hasStrategyPro)
    <x-global::selectable name="routingType" value="strategy" id="route-strategy">
        <x-slot name="label">A strategic initiative</x-slot>
        Create a strategy with focus area boards
    </x-global::selectable>
    @endif
</div>
```

#### `<x-strategypro::entity-tree>`

Review step tree view. Composes `<x-global::accordion>`, `<x-global::badge>`, and the milestone/task/goal row components to show the full proposed structure.

#### `<x-strategypro::entity-link-badge>`

Small indicator for canvas item cards showing that a work entity was generated from this item. Used on the Logic Model board after generation.

### 5.4 HTMX Patterns

Following the patterns documented in CLAUDE.md:

**Step progression:** Each step is an HxController that renders its partial. Navigation uses `hx-get` targeting `#wizardStep` to swap content without full page reload.

```
/hx/strategypro/wizard/start/{boardId}       → Runs adapter, launches shell
/hx/strategypro/wizard/scopeStep/{boardId}    → Step 1 partial
/hx/strategypro/wizard/deliverablesStep/{boardId} → Step 2 partial
/hx/strategypro/wizard/criteriaStep/{boardId}  → Step 3 partial
/hx/strategypro/wizard/reviewStep/{boardId}    → Step 4 partial
/hx/strategypro/wizard/routing/{boardId}       → Routing decision handler
```

**Cross-component events:** Define as enum for type safety:

```php
enum HtmxWizardEvents: string
{
    case STEP_CHANGED = 'wizard_step_changed';
    case STRUCTURE_UPDATED = 'wizard_structure_updated';
    case GENERATION_COMPLETE = 'wizard_generation_complete';
}
```

**State management:** Wizard state is cached per user via `WizardStateService`:

- `wizard.{userId}.logicmodel.{boardId}.structure` — the in-progress WorkStructure
- `wizard.{userId}.logicmodel.{boardId}.step` — current step number
- `wizard.{userId}.logicmodel.{boardId}.routing` — routing decision

State persists across step navigations and is cleared on completion or cancellation.

**Lazy loading within steps:** For the review tree, milestones load collapsed and expand via `hx-trigger="click"` to load their tasks and goals. This keeps the review step fast even for large canvas boards.

**Generation feedback:** The Generate button uses `hx-post` with `hx-indicator` pointing to a `<x-global::loader>`. On success, the `GENERATION_COMPLETE` event triggers a redirect to the newly created project.

### 5.5 CSS Approach

Following CLAUDE.md guidance: Tailwind 3.4.x with `tw:` prefix for new work. Use CSS custom properties (design tokens) for colors and spacing rather than hardcoded values. No new Bootstrap dependencies.

Wizard-specific styles are minimal since components compose from shared UI:

- Step transition animations (Tailwind `tw:transition-all`)
- Drag handle styling for milestone reordering (integrates with existing nestedSortable if needed, or lightweight HTML5 drag-and-drop)
- Review tree indentation (Tailwind margin utilities)

---

## 6. Copilot Plugin: Guided Flow

Ships in Phase 3. The Copilot plugin adds guided canvas population on top of the same WorkStructure pipeline.

### 6.1 Pre-Flow Routing Question

Before starting, the Copilot asks a single question to determine direction:

> **"Let's build out your strategy. Where do you want to start?"**
>
> 1. **I know what I want to achieve** (Impact/Outcomes backward)
> 2. **I know what I have to work with** (Inputs forward)
> 3. **I have a specific project in mind** (Activities outward)
> 4. **I'm not sure yet — just ask me questions** (outcome-backward default)

Default direction is outcome-backward. This aligns with how non-profit, education, and government organizations think about program design: start with the change you want to see.

Each selection routes to the same conversation but sequences questions differently. The canvas fills identically regardless of direction.

### 6.2 Conversation Pattern

For each stage the Copilot addresses:

1. Ask an open-ended question appropriate to the stage
2. Parse the response into candidate canvas items
3. Show what it will add to the canvas, ask for confirmation
4. Write confirmed items to the canvas via the standard API
5. Brief reflection/summary before the next stage

| Stage | Example Prompt | Output |
|---|---|---|
| Impact | "What's the ultimate change you want to create?" | Single item: strategic north star |
| Outcomes | "What measurable changes would tell you this is working?" | 2–4 items with indicators |
| Activities | "What programs or activities will drive those outcomes?" | 3–6 work packages |
| Outputs | "For each activity, what will you produce or deliver?" | Grouped deliverables |
| Inputs | "What resources, funding, or people do you need?" | Resource inventory |

### 6.3 Handoff to Wizard

When the canvas is sufficiently populated, the Copilot suggests: "Your Logic Model looks ready. Want me to help create your project structure?" On confirmation, it triggers the StrategyPro wizard with the same Logic Model adapter producing the same WorkStructure. The Copilot adds its conversation context to the WorkStructure's metadata for activity logging.

### 6.4 Enmotiv Integration (Future)

When enmotiv profiles are available, the pre-flow routing question can be pre-answered based on profile type. The routing selection is behavioral data that validates against profiles even before integration ships.

---

## 7. Future Consumers

The core WorkStructure domain supports plugins beyond the initial StrategyPro wizard. Each hooks into the same anchors.

### 7.1 Ideas Board (Write Direction)

A future Ideas adapter produces WorkStructures from approved ideas. Before the full wizard, a triage step determines scope:

- **Single task:** skip wizard, direct create via WorkGenerator
- **Milestone with deliverables:** lightweight 1–2 step wizard
- **Its own project:** full wizard with routing
- **Program or strategic:** routes to program/strategy creation (plugin-gated)

### 7.2 Goal Canvas (Write Direction)

A Goal Canvas adapter maps goals to milestones or projects, preserving KPI tracking and parent/child goal relationships through the WorkStructure format.

### 7.3 Stakeholder View Plugin (Read Direction)

A stakeholder view plugin uses `WorkStructureReader` to read an existing project into a WorkStructure, then renders it through a read-only template for external audiences.

- **No authentication required.** External users see a generated view, not the Leantime app.
- **Strategy language, not PM jargon.** Because WorkStructure carries entity links back to canvas items, progress can display in Logic Model terms: "Activity: Community Outreach — 3 of 4 deliverables completed" instead of "Milestone 72% complete."
- **Multiple audiences.** Grant funders see progress against the Logic Model. Board members see the strategic dashboard. Partner organizations see shared milestones.
- **Same data, different lens.** The WorkStructure is identical; only the renderer changes.

### 7.4 Reporting & Export (Read Direction)

Any reporting or export tool can consume a WorkStructure to generate grant reports, board presentations, or compliance documentation. The format is the same regardless of what's consuming it.

### 7.5 API / Integration (Both Directions)

WorkStructure could serve as the payload format for an API endpoint that accepts work structures from external systems (write) or exposes Leantime project status to external consumers (read). Not planned for initial implementation but the architecture supports it naturally.

---

## 8. Implementation Phasing

### Phase 1: Core Domain (ships with Logic Model Phase 2)

Build the anchor points in core. These are deliberately thin.

- WorkStructure model class with all sub-objects
- `WorkStructureAdapter` interface
- `WorkGenerator` service (orchestrates existing project/milestone/task/goal services)
- `WorkStructureReader` service (reads existing entities into WorkStructure)
- Entity links table and repository (generic, not Logic Model–specific)
- Core events (class-based): `WorkStructureCreated`, `WorkGenerationStarted`, `WorkGenerationCompleted`, `WorkGenerationFailed`, `EntityLinked`, `EntityUnlinked`, `WorkStructureRead`
- `AdapterRegistry` (plugins register adapters via events)

### Phase 2: StrategyPro Wizard (ships with Logic Model Phase 2)

First plugin to hook into the core anchors.

- `LogicModelAdapter` implementing `WorkStructureAdapter`
- Blade component library: `wizard-shell`, `wizard-step`, `milestone-row`, `task-row`, `goal-row`, `routing-picker`, `entity-tree`, `entity-link-badge`, `completeness-badge`
- 4-step wizard with HTMX step controllers
- Composition from shared components (`card`, `selectable`, `progress`, `button-group`, `accordion`, `forms/*`, `empty-state`, `badge`, `avatar`)
- Routing decision UI
- "Create Work from Canvas" toolbar action
- Entity linking from canvas items to generated work
- Living Link: forward and backward navigation
- Activity log data capture

### Phase 3: Living Link + Copilot

- Change detection on canvas items with linked entities
- Notification system for strategy changes
- Diff view and regeneration flow (update / add / replace)
- Copilot guided canvas population flow
- Pre-flow routing question and conversational stage population
- Copilot-to-wizard handoff

### Phase 4: Framework Extension

- Ideas Board source adapter and triage flow
- Goal Canvas source adapter
- Stakeholder View plugin (read direction, external audiences)
- Template system for pre-populated canvas examples
- Enmotiv profile-driven flow direction

---

## 9. Telemetry & Activity Logging

### 9.1 Activity Log

All wizard interactions are captured via core events. StrategyPro listens to `WorkGenerationCompleted` and related events to write to the `logic_model_activity_log` table (data capture starts Phase 2, per existing PRD):

- Wizard started: source board, items selected, routing decision
- Step progression and time per step
- Items confirmed, modified, added, or removed during wizard
- Entities created with IDs
- Wizard cancellation and drop-off point

### 9.2 Product Metrics

- Starting point selection distribution (outcome-backward vs. input-forward vs. middle-out)
- Canvas completeness at wizard trigger
- Step completion time and drop-off rates
- Revision patterns (how often users go back)
- Living link engagement (how often users act on change notifications)
- Read direction usage (stakeholder view access frequency, audience types)

---

## 10. Open Questions

| # | Question | Impact | Status |
|---|---|---|---|
| 1 | Should WorkStructure support nesting (a program WorkStructure containing child project WorkStructures)? | Read direction complexity for programs/strategies | Open |
| 2 | How should the reader handle cases where entities were independently modified after generation? | Living Link reliability, staleness detection | Open |
| 3 | Should the Copilot flow support mid-conversation canvas editing (user switches to manual while Copilot is active)? | Copilot state management complexity | Open |
| 4 | What's the right granularity for Inputs mapping? Budget items vs. resource notes vs. team assignments are different entity types. | Mapping completeness | Open |
| 5 | Should the stakeholder view plugin use WorkStructure snapshots (point-in-time) or live reads? | Performance vs. freshness tradeoff | Open |
| 6 | How does the wizard interact with existing project content? Merge, append, or choice? | UX for in-place routing | Open |
| 7 | Should WorkStructure carry status/progress data, or is that derived at read time from entity state? | WorkStructure scope, caching strategy | Open |
| 8 | Is there a "Copilot Lite" or free-tier AI path for canvas-to-work that doesn't require the full Copilot plugin? | Business model, open-source AI plugin scope | Open |
| 9 | Should any new shared components (e.g., a generic `wizard-step` or `step-progress`) be promoted to `app/Views/Templates/components/` for reuse by future wizards? | Component library growth strategy | Open |
