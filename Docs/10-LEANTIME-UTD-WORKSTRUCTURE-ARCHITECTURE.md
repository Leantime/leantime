# 10-ADDENDUM-WORKSTRUCTURE-ARCHITECTURE.md

## Addendum: WorkStructure as Schema Definition Layer

**Applies to:** `07-WORKSTRUCTURE.md` (Section 2), `08-WORKSTRUCTURE-PROMPT.md` (Deliverable 1)
**Date:** February 21, 2026
**Trigger:** Architecture review — WorkStructure was specced as a data container holding domain-specific models. It should be a schema definition layer that describes structures, element types, and relationships without holding instance data.
**Status:** APPROVED — supersedes all core domain specs in referenced documents

---

## What Changed

The original spec defined WorkStructure as a normalized data object containing `ProposedMilestone`, `ProposedTask`, `ProposedGoal`, etc. This violated DDD by pulling domain-specific models into the WorkStructure domain and conflated the structure definition with instance data.

**WorkStructure is a schema, not a container.** It defines what types of elements exist, how they relate to each other, and how one structure maps to another. It does not hold individual milestones, tasks, or goals. Those stay in their own domain tables (`zp_tickets`, `zp_canvas_items`, `zp_goal_canvas_items`). WorkStructure describes the *shape* — like a UML diagram describes tables without containing rows.

---

## Core Concepts

### WorkStructure

A named, reusable definition of how work is organized. Examples:

- **"Project"** — the current Leantime hierarchy: milestones contain tasks, goals measure milestones
- **"Logic Model"** — five stages (input → activity → output → outcome → impact) with causal relationships
- **"Simple Project"** — tasks only, no milestones
- **"Program"** — projects grouped under a program with shared resources

Each is a different organizational opinion. The system ships with a few. Plugins can register more.

### Element Definition

A type of thing that can exist within a structure. WorkStructure doesn't know what a "milestone" is — it knows that the "Project" structure has an element definition called "milestone" with certain metadata, a reference to a state machine (future), and a class/domain reference for entity realization.

### Relationship Definition

How element types relate to each other within a structure. Directional. "task belongs_to milestone" and "milestone measures goal" are relationship definitions within the "Project" structure.

### Cross-Structure Mapping

How element types in one structure correspond to element types in another. This is what makes the wizard possible: "Logic Model output ↔ Project milestone" means when you generate work from a Logic Model, outputs become milestones. The mapping is a relationship between structures, not between instances.

---

## How It Works — Logic Model Wizard Example

```
1. User fills out Logic Model Canvas (data in zp_canvas_items)

2. User clicks "Create Work from Canvas"

3. Wizard reads the Logic Model WorkStructure definition:
   - It has element types: input, activity, output, outcome, impact
   - It has relationships: input→activity, activity→output, etc.

4. Wizard reads the cross-structure mapping:
   - Logic Model "output" ↔ Project "milestone"
   - Logic Model "activity" ↔ Project "task"  
   - Logic Model "outcome" ↔ Project "goal"
   - Logic Model "impact" ↔ Project (the structure itself)

5. Wizard presents canvas items mapped to their target types:
   - "200 participants enrolled" (output) → will become a milestone
   - "Skills assessment" (activity) → will become a task
   - User confirms/edits

6. Generator creates entities in their native domain tables:
   - Milestone → zp_tickets (via Tickets service)
   - Task → zp_tickets (via Tickets service)
   - Goal → zp_goal_canvas_items (via Goalcanvas service)

7. Entity relationships recorded in zp_entityrelations:
   - canvas_item_42 → ticket_108 (generated_from)
   - canvas_item_37 → ticket_115 (generated_from)
```

WorkStructure never held the data. It defined the shape that told the wizard how to map and the generator what to create.

---

## Data Model

### zp_work_structures

The structure definitions themselves.

| Column | Type | Purpose |
|---|---|---|
| id | INT (PK) | Auto-increment |
| title | VARCHAR(255) | Human name: "Project", "Logic Model", "Program" |
| description | TEXT | What this structure represents |
| type | VARCHAR(50) | Classifier: 'system', 'plugin', 'custom' |
| created_by | INT NULL | NULL for system-defined structures |
| meta | JSON NULL | Extensible metadata |
| created_at | DATETIME | Timestamp |
| modified_at | DATETIME | Timestamp |

**Ships with:**
- "Project" (system) — codifies current milestone/task/goal hierarchy
- "Logic Model" (plugin, registered by StrategyPro) — five-stage causal chain

### zp_work_structure_elements

Element type definitions within a structure. Not instances — definitions.

| Column | Type | Purpose |
|---|---|---|
| id | INT (PK) | Auto-increment |
| structure_id | INT (FK) | Which structure this element type belongs to |
| type_key | VARCHAR(50) | Identifier: 'milestone', 'task', 'activity', 'output', etc. |
| label | VARCHAR(100) | Human display name |
| description | TEXT NULL | What this element type represents in this structure |
| domain_reference | VARCHAR(255) NULL | Class or domain that realizes this type (e.g., 'Leantime\\Domain\\Tickets') |
| sort_order | INT | Display/hierarchy order within the structure |
| meta | JSON NULL | Extensible: icon, color, default fields, state machine ref (future) |
| created_at | DATETIME | Timestamp |

**Example rows for "Project" structure:**

| structure_id | type_key | label | domain_reference | sort_order |
|---|---|---|---|---|
| 1 | milestone | Milestone | Leantime\Domain\Tickets | 1 |
| 1 | task | Task | Leantime\Domain\Tickets | 2 |
| 1 | goal | Goal | Leantime\Domain\Goalcanvas | 3 |

**Example rows for "Logic Model" structure:**

| structure_id | type_key | label | domain_reference | sort_order |
|---|---|---|---|---|
| 2 | input | Input | Leantime\Domain\Logicmodelcanvas | 1 |
| 2 | activity | Activity | Leantime\Domain\Logicmodelcanvas | 2 |
| 2 | output | Output | Leantime\Domain\Logicmodelcanvas | 3 |
| 2 | outcome | Outcome | Leantime\Domain\Logicmodelcanvas | 4 |
| 2 | impact | Impact | Leantime\Domain\Logicmodelcanvas | 5 |

### zp_work_structure_relationships

How element types relate within a structure.

| Column | Type | Purpose |
|---|---|---|
| id | INT (PK) | Auto-increment |
| structure_id | INT (FK) | Which structure this relationship belongs to |
| from_element_id | INT (FK) | Source element definition |
| to_element_id | INT (FK) | Target element definition |
| relationship_type | VARCHAR(50) | 'belongs_to', 'produces', 'measures', 'funds', 'leads_to', etc. |
| description | TEXT NULL | Human description of this relationship |
| meta | JSON NULL | Extensible |

**Example rows for "Project":**

| from (type_key) | to (type_key) | relationship_type |
|---|---|---|
| task | milestone | belongs_to |
| milestone | goal | measures |

**Example rows for "Logic Model":**

| from (type_key) | to (type_key) | relationship_type |
|---|---|---|
| input | activity | enables |
| activity | output | produces |
| output | outcome | leads_to |
| outcome | impact | contributes_to |

### zp_work_structure_mappings

Cross-structure element type mappings. This is what powers the wizard's translation.

| Column | Type | Purpose |
|---|---|---|
| id | INT (PK) | Auto-increment |
| source_structure_id | INT (FK) | Source structure (e.g., Logic Model) |
| source_element_id | INT (FK) | Source element definition (e.g., "output") |
| target_structure_id | INT (FK) | Target structure (e.g., Project) |
| target_element_id | INT (FK) | Target element definition (e.g., "milestone") |
| mapping_type | VARCHAR(50) | 'equivalent', 'generates', 'informs' |
| meta | JSON NULL | Extensible |

**Example rows:**

| source structure | source element | target structure | target element | mapping_type |
|---|---|---|---|---|
| Logic Model | impact | Project | (structure itself) | generates |
| Logic Model | output | Project | milestone | generates |
| Logic Model | activity | Project | task | generates |
| Logic Model | outcome | Project | goal | generates |

### Instance-Level Relationships

When the wizard actually creates work from a canvas, the **instance-level** connections (canvas item #42 generated ticket #108) are recorded in the **existing `zp_entityrelations` table**. No new table needed.

**Note to Claude Code:** Review the existing Entityrelations domain (`app/Domain/Entityrelations/`). Understand its table structure, relationship types, and service methods. Build on it. If it needs enhancement (e.g., a `generated_from` relationship type or a `source_context` metadata field), extend it rather than creating a parallel linking system.

---

## Domain Structure

### Revised File Layout

```
app/Domain/WorkStructure/
├── Models/
│   ├── WorkStructure.php              # Structure definition
│   ├── ElementDefinition.php          # Element type within a structure
│   ├── RelationshipDefinition.php     # How element types relate
│   └── StructureMapping.php           # Cross-structure element mapping
├── Services/
│   ├── WorkStructureService.php       # CRUD for structures and definitions
│   ├── StructureRegistry.php          # Plugins register structures + elements
│   └── MappingService.php             # Cross-structure mapping queries
├── Repositories/
│   └── WorkStructureRepository.php    # All table access
├── Events/
│   ├── StructureRegistered.php        # A new structure was registered
│   ├── ElementTypeRegistered.php      # A new element type was added
│   └── MappingCreated.php             # A cross-structure mapping was defined
└── register.php                       # Registers "Project" as system structure
```

### What's Gone

These models from the original spec are **removed** — they were domain-specific:

- ~~ProposedMilestone.php~~ → Tickets domain concern
- ~~ProposedTask.php~~ → Tickets domain concern
- ~~ProposedGoal.php~~ → Goalcanvas domain concern
- ~~ProposedResource.php~~ → future ResourceStructure concern
- ~~SourceMeta.php~~ → adapter concern (stays in plugin)
- ~~RoutingDecision.php~~ → wizard concern (stays in plugin)
- ~~EntityLink.php~~ → use existing Entityrelations domain
- ~~CompletenessReport.php~~ → adapter concern (stays in plugin)
- ~~WorkGenerator.php~~ → moves to plugin (see below)
- ~~WorkStructureReader.php~~ → moves to plugin (see below)
- ~~EntityLinkRepository.php~~ → use existing Entityrelations
- ~~zp_entity_links table~~ → use existing zp_entityrelations

### What Moves to StrategyPro Plugin

The WorkGenerator and WorkStructureReader were doing domain-specific entity creation (calling `Tickets::quickAddMilestone()`, `Goalcanvas::createGoal()`). That logic belongs in the plugin, not core. Core defines the structure; the plugin knows how to realize elements as domain entities.

```
app/Plugins/StrategyPro/
├── Services/
│   ├── LogicModelAdapter.php          # Reads canvas, uses mappings to propose work
│   ├── WorkGenerator.php              # Creates domain entities from mapped elements
│   ├── WizardStateService.php         # Cache-based wizard state
│   └── (CompletenessReport, RoutingDecision, SourceMeta as needed)
```

The generator reads the cross-structure mapping (Logic Model → Project), iterates the canvas items, and for each mapped element type, calls the appropriate domain service. It records instance-level relationships in `zp_entityrelations`.

---

## Impact on Other Documents

### 07-WORKSTRUCTURE.md

**Section 2 (Core Domain):** Replace entirely with the architecture described in this addendum. The core domain is a schema definition layer with four tables and a registry, not a data container with typed models.

**Section 3 (Events):** Replace the entity-level events (WorkGenerationStarted, etc.) with structure-level events (StructureRegistered, ElementTypeRegistered, MappingCreated). Generation events move to the plugin.

**Sections 4-9:** Mostly unchanged. The wizard, components, Copilot, future consumers, phasing, and telemetry all work the same way. They just interact with WorkStructure as a definition layer rather than a data container.

### 08-WORKSTRUCTURE-PROMPT.md

**Deliverable 1:** Rewrite to create the four tables, four models, three services, and system "Project" structure registration.

**Deliverable 2:** Mostly unchanged. The LogicModelAdapter now reads structure mappings to know output→milestone. The WorkGenerator moves from core to plugin. The wizard UX is identical.

### 09-ADDENDUM-MAPPING-CORRECTION.md

Still valid. The corrected mapping (Activities→Tasks, Outputs→Milestones) is now expressed as rows in `zp_work_structure_mappings` rather than as code in the adapter. Same mapping, different storage.

---

## What Ships First (Minimum Viable)

1. **Four tables:** `zp_work_structures`, `zp_work_structure_elements`, `zp_work_structure_relationships`, `zp_work_structure_mappings`

2. **Four models:** `WorkStructure`, `ElementDefinition`, `RelationshipDefinition`, `StructureMapping`

3. **Core services:** `WorkStructureService` (CRUD), `StructureRegistry` (plugin registration), `MappingService` (cross-structure queries)

4. **System structure:** "Project" registered in `register.php` with element types: milestone, task, goal and their relationships

5. **Plugin structure:** "Logic Model" registered by StrategyPro with element types: input, activity, output, outcome, impact and their relationships plus cross-structure mappings to Project

6. **Instance relationships:** Use existing `zp_entityrelations` for canvas-item-to-entity links after generation

### Deferred

- State machine definitions per element type (future)
- Custom user-defined structures (future)
- Dynamic MCP/JSON-RPC tool generation from registered element types (future)
- ResourceStructure as a parallel core definition (future, see separate spec)
- API query patterns for LLM navigation (future)

---

## Caching Considerations

WorkStructure definitions are read-heavy, write-rare. Structure definitions, element types, relationships, and mappings should be cached aggressively:

- Cache all structures + their element definitions on first load
- Invalidate on structure registration (rare — only happens when plugins activate)
- Instance-level queries (entityrelations) follow existing caching patterns

**Note to Claude Code:** Use Laravel cache (file or Redis depending on config). Check how other domains handle caching — look for `Cache::remember()` patterns in existing services. Structure definitions are perfect candidates for `Cache::rememberForever()` with manual invalidation on registration events.

---

## API/MCP Future Direction (Not Built Now, But Design For It)

When element types are registered with `domain_reference` and metadata including descriptions, the system has enough information to dynamically generate API tools:

```
Element type "milestone" registered by Tickets domain
→ Auto-generate: get_all_milestones, create_milestone, update_milestone
→ Tool description pulled from ElementDefinition.description
→ Parameters inferred from domain service method signatures
```

This means installing a plugin that registers new element types automatically expands the API surface. An LLM querying Leantime via MCP sees `get_all_activities` appear when StrategyPro is activated, without anyone writing a new MCP tool definition.

**Design implication:** Element definitions should carry enough metadata (description, domain_reference, and eventually parameter schemas) to support this future auto-generation. The `meta` JSON column on `zp_work_structure_elements` is where this lives.
