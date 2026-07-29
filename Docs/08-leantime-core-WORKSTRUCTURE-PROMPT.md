# WorkStructure Core + Content-to-Work Wizard — Implementation Prompt

## For Claude Code Execution

**Project:** Logic Model Canvas — Phase 2: Strategy Plugin
**Scope:** Core WorkStructure domain + StrategyPro wizard for Logic Model → work generation
**Depends on:** Phase 1 Core Board complete, existing canvas board architecture, existing service layer
**Risk Level:** MEDIUM (new core domain + new plugin feature, no existing code changes)
**Generated:** February 20, 2026

---

## Pre-Read Documents

```
Read these documents in order before starting:
1. CLAUDE.md — Architecture overview, domain patterns, HTMX patterns, component system
2. Docs/00-IMPLEMENTATION-GUIDE.md — Logic Model phasing overview
3. Docs/01-CORE.md — Phase 1 core board spec (what's already built)
4. Docs/02-PLUGIN.md — Phase 2 plugin spec (what's shipping alongside this)
5. Docs/04-DATA-MODEL.md — Existing data model (canvas tables, item structure)
6. Docs/07-WORKSTRUCTURE.md — Full PRD for this feature (architecture, contracts, components)
```

---

## What This Is

Two deliverables that ship together:

1. **WorkStructure Core Domain** (`app/Domain/WorkStructure/`) — A normalized interchange format for describing work (milestones, tasks, goals, relationships) and services for creating entities from it or reading existing entities into it. This is the anchor that any plugin hooks into.

2. **StrategyPro Wizard** (`app/Plugins/StrategyPro/`) — The first plugin consumer. Reads a Logic Model Canvas, produces a WorkStructure, walks the user through a 4-step confirmation wizard, and generates Leantime work entities.

---

## CRITICAL CONSTRAINTS

```
- DO NOT modify existing service classes (Projects, Tickets, Goalcanvas). Call their existing public methods.
- DO NOT modify existing canvas board code. The adapter READS from the Logic Model; it does not change it.
- DO NOT create .tpl.php files. All new templates are Blade (.blade.php).
- DO NOT add jQuery dependencies. Use HTMX for async, vanilla JS only for interactivity.
- DO compose from existing shared components in app/Views/Templates/components/.
- DO use class-based events (not string-based) for all new events.
- DO use the tw: prefix for all Tailwind classes (required by Leantime's Tailwind config).
- DO use CSS custom properties (--accent1, --primary-color, etc.) not hardcoded colors.
- DO follow the HxController pattern from CLAUDE.md for all HTMX controllers.
- DO use DI via init() method in HxControllers, NOT __construct().
- Tables use zp_ prefix (e.g., zp_entity_links).
- All new Blade components use @props for their interface.
- Test each step in browser before proceeding to next.
```

---

## Deliverable 1: WorkStructure Core Domain

### 1.1 Create Domain Directory Structure

```
app/Domain/WorkStructure/
├── Contracts/
│   └── WorkStructureAdapter.php
├── Models/
│   ├── WorkStructure.php
│   ├── ProposedMilestone.php
│   ├── ProposedTask.php
│   ├── ProposedGoal.php
│   ├── ProposedResource.php
│   ├── SourceMeta.php
│   ├── RoutingDecision.php
│   ├── EntityLink.php
│   └── CompletenessReport.php
├── Services/
│   ├── WorkGenerator.php
│   ├── WorkStructureReader.php
│   └── AdapterRegistry.php
├── Repositories/
│   └── EntityLinkRepository.php
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

### 1.2 Models

**`WorkStructure.php`** — The normalized work bundle:

```php
namespace Leantime\Domain\WorkStructure\Models;

class WorkStructure
{
    public ?string $id = null;
    public string $title = '';
    public string $objective = '';
    public SourceMeta $source;
    public RoutingDecision $routing;
    /** @var ProposedMilestone[] */
    public array $milestones = [];
    /** @var ProposedTask[] */
    public array $tasks = [];
    /** @var ProposedGoal[] */
    public array $goals = [];
    /** @var ProposedResource[] */
    public array $resources = [];
    /** @var EntityLink[] */
    public array $links = [];
    public array $metadata = [];

    public function __construct()
    {
        $this->source = new SourceMeta();
        $this->routing = new RoutingDecision();
    }
}
```

**`SourceMeta.php`**:

```php
namespace Leantime\Domain\WorkStructure\Models;

class SourceMeta
{
    public string $boardType = '';
    public int $boardId = 0;
    public string $adapterClass = '';
    public ?\DateTime $generatedAt = null;
    public ?int $generatedBy = null;
}
```

**`RoutingDecision.php`**:

```php
namespace Leantime\Domain\WorkStructure\Models;

class RoutingDecision
{
    /** @var 'in_place'|'project'|'program'|'strategy' */
    public string $type = 'project';
    public ?int $parentId = null;
    public ?int $projectId = null;
}
```

**`ProposedMilestone.php`**:

```php
namespace Leantime\Domain\WorkStructure\Models;

class ProposedMilestone
{
    public ?string $tempId = null;       // Temporary ID for task grouping before generation
    public ?int $entityId = null;        // Populated after generation
    public string $title = '';
    public string $description = '';
    public ?string $startDate = null;    // Y-m-d format
    public ?string $endDate = null;      // Y-m-d format
    public ?int $sortIndex = 0;
    public ?int $sourceItemId = null;    // Canvas item ID this came from
    public array $metadata = [];
}
```

**`ProposedTask.php`**:

```php
namespace Leantime\Domain\WorkStructure\Models;

class ProposedTask
{
    public ?string $tempId = null;
    public ?int $entityId = null;
    public string $title = '';
    public string $description = '';
    public ?string $milestoneTempId = null;  // Links to ProposedMilestone::tempId
    public ?int $milestoneEntityId = null;
    public ?string $dateToFinish = null;
    public ?int $editorId = null;            // Assigned user
    public ?int $sortIndex = 0;
    public ?int $sourceItemId = null;
    public array $metadata = [];
}
```

**`ProposedGoal.php`**:

```php
namespace Leantime\Domain\WorkStructure\Models;

class ProposedGoal
{
    public ?string $tempId = null;
    public ?int $entityId = null;
    public string $title = '';
    public string $description = '';
    public ?string $metricType = null;       // 'numeric', 'percentage', 'currency', 'qualitative'
    public mixed $startValue = null;
    public mixed $endValue = null;
    public ?string $milestoneTempId = null;   // Optional link to a milestone
    public ?int $sortIndex = 0;
    public ?int $sourceItemId = null;
    public array $metadata = [];
}
```

**`ProposedResource.php`**:

```php
namespace Leantime\Domain\WorkStructure\Models;

class ProposedResource
{
    public string $title = '';
    public string $type = '';                // 'budget', 'team', 'dependency', 'note'
    public string $description = '';
    public ?float $amount = null;
    public ?int $sourceItemId = null;
    public array $metadata = [];
}
```

**`EntityLink.php`**:

```php
namespace Leantime\Domain\WorkStructure\Models;

class EntityLink
{
    public ?int $id = null;
    public string $sourceType = '';          // 'logicmodel', 'ideas', 'goalcanvas'
    public int $sourceItemId = 0;
    public string $targetEntityType = '';    // 'project', 'milestone', 'ticket', 'goal'
    public int $targetEntityId = 0;
    public string $linkType = 'generated_from';  // 'generated_from', 'maps_to', 'tracks'
    public ?int $createdBy = null;
    public ?string $createdAt = null;
}
```

**`CompletenessReport.php`**:

```php
namespace Leantime\Domain\WorkStructure\Models;

class CompletenessReport
{
    /** @var array<string, int> Stage name => item count */
    public array $stageCounts = [];
    public int $totalItems = 0;
    public int $populatedStages = 0;
    public int $totalStages = 0;

    /** @var 'rich'|'moderate'|'sparse'|'empty' */
    public string $level = 'empty';

    /** @var string[] Human-readable notes about what's missing */
    public array $gaps = [];
}
```

### 1.3 Contracts

**`WorkStructureAdapter.php`**:

```php
namespace Leantime\Domain\WorkStructure\Contracts;

use Leantime\Domain\WorkStructure\Models\WorkStructure;
use Leantime\Domain\WorkStructure\Models\CompletenessReport;

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

### 1.4 Services

**`AdapterRegistry.php`** — Discovers registered adapters:

```php
namespace Leantime\Domain\WorkStructure\Services;

use Leantime\Domain\WorkStructure\Contracts\WorkStructureAdapter;

class AdapterRegistry
{
    /** @var WorkStructureAdapter[] */
    private array $adapters = [];

    public function register(WorkStructureAdapter $adapter): void
    {
        $this->adapters[$adapter->getSourceType()] = $adapter;
    }

    public function get(string $sourceType): ?WorkStructureAdapter
    {
        return $this->adapters[$sourceType] ?? null;
    }

    /** @return WorkStructureAdapter[] */
    public function all(): array
    {
        return $this->adapters;
    }

    public function has(string $sourceType): bool
    {
        return isset($this->adapters[$sourceType]);
    }
}
```

**`WorkGenerator.php`** — Write direction: WorkStructure → Leantime entities:

```php
namespace Leantime\Domain\WorkStructure\Services;

use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Projects\Services\Projects as ProjectsService;
use Leantime\Domain\Tickets\Services\Tickets as TicketsService;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvasService;
use Leantime\Domain\WorkStructure\Models\WorkStructure;
use Leantime\Domain\WorkStructure\Models\EntityLink;
use Leantime\Domain\WorkStructure\Events\WorkGenerationStarted;
use Leantime\Domain\WorkStructure\Events\WorkGenerationCompleted;
use Leantime\Domain\WorkStructure\Events\WorkGenerationFailed;
use Leantime\Domain\WorkStructure\Repositories\EntityLinkRepository;

class WorkGenerator
{
    use DispatchesEvents;

    public function __construct(
        private ProjectsService $projectsService,
        private TicketsService $ticketsService,
        private GoalcanvasService $goalcanvasService,
        private EntityLinkRepository $entityLinkRepository,
    ) {}

    /**
     * Generate all Leantime entities from a WorkStructure.
     * Respects the routing decision for entity type.
     */
    public function generate(WorkStructure $structure): WorkStructure
    {
        // Dispatch started event
        // See CLAUDE.md for class-based event pattern: new event class, dispatch via event()
        event(new WorkGenerationStarted($structure));

        try {
            $projectId = $this->resolveProjectId($structure);

            // Create milestones, mapping tempId → entityId
            $milestoneMap = $this->createMilestones($projectId, $structure->milestones);

            // Create tasks, resolving milestone references
            $this->createTasks($projectId, $structure->tasks, $milestoneMap);

            // Create goals
            $this->createGoals($projectId, $structure->goals, $milestoneMap);

            // Record entity links
            $this->recordLinks($structure);

            event(new WorkGenerationCompleted($structure));

            return $structure;

        } catch (\Throwable $e) {
            event(new WorkGenerationFailed($structure, $e));
            throw $e;
        }
    }

    private function resolveProjectId(WorkStructure $structure): int
    {
        if ($structure->routing->type === 'in_place') {
            return $structure->routing->projectId;
        }

        // Create new project via existing service
        // Projects::addProject() expects an array with 'name', 'details', 'clientId', 'type', 'parent'
        $projectValues = [
            'name' => $structure->title,
            'details' => $structure->objective,
            'clientId' => '', // inherits from session
            'type' => $structure->routing->type === 'strategy' ? 'strategy'
                     : ($structure->routing->type === 'program' ? 'program' : 'project'),
            'parent' => $structure->routing->parentId,
        ];

        $projectId = $this->projectsService->addProject($projectValues);
        $structure->routing->projectId = $projectId;

        return $projectId;
    }

    /**
     * @return array<string, int> Map of tempId → entityId
     */
    private function createMilestones(int $projectId, array &$milestones): array
    {
        $map = [];

        foreach ($milestones as $milestone) {
            // Tickets::quickAddMilestone expects: headline, projectId, editFrom, editTo
            $values = [
                'headline' => $milestone->title,
                'description' => $milestone->description,
                'projectId' => $projectId,
                'editFrom' => $milestone->startDate ?? '',
                'editTo' => $milestone->endDate ?? '',
                'sortIndex' => $milestone->sortIndex,
            ];

            $entityId = $this->ticketsService->quickAddMilestone($values);
            $milestone->entityId = $entityId;

            if ($milestone->tempId) {
                $map[$milestone->tempId] = $entityId;
            }
        }

        return $map;
    }

    private function createTasks(int $projectId, array &$tasks, array $milestoneMap): void
    {
        foreach ($tasks as $task) {
            $milestoneId = null;
            if ($task->milestoneTempId && isset($milestoneMap[$task->milestoneTempId])) {
                $milestoneId = $milestoneMap[$task->milestoneTempId];
            } elseif ($task->milestoneEntityId) {
                $milestoneId = $task->milestoneEntityId;
            }

            // Tickets::quickAddTicket expects: headline, projectId, description, milestoneId, editorId, dateToFinish
            $values = [
                'headline' => $task->title,
                'description' => $task->description,
                'projectId' => $projectId,
                'milestoneid' => $milestoneId,
                'editorId' => $task->editorId ?? '',
                'dateToFinish' => $task->dateToFinish ?? '',
                'sortindex' => $task->sortIndex,
            ];

            $entityId = $this->ticketsService->quickAddTicket($values);
            $task->entityId = $entityId;
        }
    }

    private function createGoals(int $projectId, array &$goals, array $milestoneMap): void
    {
        if (empty($goals)) {
            return;
        }

        // Create a goal board for this project
        $goalBoardId = $this->goalcanvasService->createGoalboard([
            'title' => 'Goals: ' . '',
            'projectId' => $projectId,
        ]);

        foreach ($goals as $goal) {
            $values = [
                'title' => $goal->title,
                'description' => $goal->description,
                'canvasId' => $goalBoardId,
                'metricType' => $goal->metricType ?? 'qualitative',
                'startValue' => $goal->startValue,
                'endValue' => $goal->endValue,
                'sortindex' => $goal->sortIndex,
            ];

            $entityId = $this->goalcanvasService->createGoal($values);
            $goal->entityId = $entityId;
        }
    }

    private function recordLinks(WorkStructure $structure): void
    {
        $links = [];

        // Link milestones to source items
        foreach ($structure->milestones as $m) {
            if ($m->sourceItemId && $m->entityId) {
                $links[] = $this->buildLink($structure, $m->sourceItemId, 'milestone', $m->entityId);
            }
        }

        // Link tasks to source items
        foreach ($structure->tasks as $t) {
            if ($t->sourceItemId && $t->entityId) {
                $links[] = $this->buildLink($structure, $t->sourceItemId, 'ticket', $t->entityId);
            }
        }

        // Link goals to source items
        foreach ($structure->goals as $g) {
            if ($g->sourceItemId && $g->entityId) {
                $links[] = $this->buildLink($structure, $g->sourceItemId, 'goal', $g->entityId);
            }
        }

        foreach ($links as $link) {
            $this->entityLinkRepository->create($link);
            $structure->links[] = $link;
        }
    }

    private function buildLink(WorkStructure $structure, int $sourceItemId, string $targetType, int $targetId): EntityLink
    {
        $link = new EntityLink();
        $link->sourceType = $structure->source->boardType;
        $link->sourceItemId = $sourceItemId;
        $link->targetEntityType = $targetType;
        $link->targetEntityId = $targetId;
        $link->linkType = 'generated_from';
        $link->createdBy = $structure->source->generatedBy;
        return $link;
    }
}
```

**`WorkStructureReader.php`** — Read direction: existing entities → WorkStructure:

```php
namespace Leantime\Domain\WorkStructure\Services;

use Leantime\Domain\Projects\Services\Projects as ProjectsService;
use Leantime\Domain\Tickets\Services\Tickets as TicketsService;
use Leantime\Domain\Goalcanvas\Services\Goalcanvas as GoalcanvasService;
use Leantime\Domain\WorkStructure\Models\WorkStructure;
use Leantime\Domain\WorkStructure\Models\ProposedMilestone;
use Leantime\Domain\WorkStructure\Models\ProposedTask;
use Leantime\Domain\WorkStructure\Models\ProposedGoal;
use Leantime\Domain\WorkStructure\Models\SourceMeta;
use Leantime\Domain\WorkStructure\Models\RoutingDecision;
use Leantime\Domain\WorkStructure\Repositories\EntityLinkRepository;
use Leantime\Domain\WorkStructure\Events\WorkStructureRead;

class WorkStructureReader
{
    public function __construct(
        private ProjectsService $projectsService,
        private TicketsService $ticketsService,
        private GoalcanvasService $goalcanvasService,
        private EntityLinkRepository $entityLinkRepository,
    ) {}

    /**
     * Read a full project into a WorkStructure.
     */
    public function fromProject(int $projectId): WorkStructure
    {
        $project = $this->projectsService->getProject($projectId);

        $structure = new WorkStructure();
        $structure->id = (string) $projectId;
        $structure->title = $project['name'] ?? '';
        $structure->objective = $project['details'] ?? '';

        $structure->routing = new RoutingDecision();
        $structure->routing->type = 'in_place';
        $structure->routing->projectId = $projectId;

        // Read milestones
        $milestones = $this->ticketsService->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => $projectId]);
        foreach ($milestones as $m) {
            $pm = new ProposedMilestone();
            $pm->entityId = (int) $m['id'];
            $pm->title = $m['headline'] ?? '';
            $pm->description = $m['description'] ?? '';
            $pm->startDate = $m['editFrom'] ?? null;
            $pm->endDate = $m['editTo'] ?? null;
            $pm->sortIndex = (int) ($m['sortindex'] ?? 0);
            $structure->milestones[] = $pm;
        }

        // Read tasks
        $tickets = $this->ticketsService->getAll(['sprint' => '', 'type' => 'task', 'currentProject' => $projectId]);
        foreach ($tickets as $t) {
            $pt = new ProposedTask();
            $pt->entityId = (int) $t['id'];
            $pt->title = $t['headline'] ?? '';
            $pt->description = $t['description'] ?? '';
            $pt->milestoneEntityId = !empty($t['milestoneid']) ? (int) $t['milestoneid'] : null;
            $pt->editorId = !empty($t['editorId']) ? (int) $t['editorId'] : null;
            $pt->dateToFinish = $t['dateToFinish'] ?? null;
            $pt->sortIndex = (int) ($t['sortindex'] ?? 0);
            $structure->tasks[] = $pt;
        }

        // Read entity links
        $structure->links = $this->entityLinkRepository->getByProject($projectId);

        event(new WorkStructureRead($structure));

        return $structure;
    }
}
```

**Note to Claude Code:** The `fromProject` method above is a starting scaffold. The exact method signatures for `getAll`, `getAllMilestones`, `getProject` etc. should be verified against the actual service classes before implementation. Check `app/Domain/Tickets/Services/Tickets.php` and `app/Domain/Projects/Services/Projects.php` for actual parameter names and return types.

### 1.5 Repository

**`EntityLinkRepository.php`**:

```php
namespace Leantime\Domain\WorkStructure\Repositories;

use Leantime\Core\Db\Repository;
use Leantime\Domain\WorkStructure\Models\EntityLink;

class EntityLinkRepository extends Repository
{
    /**
     * Create entity links table if it doesn't exist.
     * Call this from install/migration.
     */
    public function createTable(): void
    {
        // Use SchemaBuilder for database-agnostic table creation
        // Table: zp_entity_links
        // Columns: id (PK auto), source_type varchar(50), source_item_id int,
        //          target_entity_type varchar(50), target_entity_id int,
        //          link_type varchar(30) default 'generated_from',
        //          created_by int, created_at datetime
        // Indexes: (source_type, source_item_id), (target_entity_type, target_entity_id)
    }

    public function create(EntityLink $link): int
    {
        // INSERT into zp_entity_links
        // Return inserted ID
    }

    public function delete(int $id): void
    {
        // DELETE from zp_entity_links WHERE id = :id
    }

    /**
     * Get all links for a given source item.
     * @return EntityLink[]
     */
    public function getBySource(string $sourceType, int $sourceItemId): array
    {
        // SELECT * FROM zp_entity_links WHERE source_type = :type AND source_item_id = :id
    }

    /**
     * Get all links pointing to a given entity.
     * @return EntityLink[]
     */
    public function getByTarget(string $targetType, int $targetId): array
    {
        // SELECT * FROM zp_entity_links WHERE target_entity_type = :type AND target_entity_id = :id
    }

    /**
     * Get all links for entities within a project.
     * Joins against zp_tickets and zp_canvas_items to find project-scoped links.
     * @return EntityLink[]
     */
    public function getByProject(int $projectId): array
    {
        // Complex query — join zp_entity_links against target entity tables
        // to find all links where target entities belong to given project
    }
}
```

**Note to Claude Code:** Implement the repository methods following the existing repository pattern in the codebase. Check `app/Domain/Tickets/Repositories/Tickets.php` for the query style (`$this->db->`, prepared statements, etc.). Use `dbcall()` wrapper for event dispatching around SQL execution.

### 1.6 Events

Create class-based events following the pattern in `app/Domain/Files/Events/FileUploaded.php` (the one existing class-based event):

```php
namespace Leantime\Domain\WorkStructure\Events;

class WorkGenerationStarted
{
    public function __construct(
        public readonly \Leantime\Domain\WorkStructure\Models\WorkStructure $structure,
    ) {}
}
```

Create the same pattern for all 7 events listed in section 1.1. Each event class takes the relevant payload as constructor arguments with `public readonly` properties.

### 1.7 Register File

**`register.php`** — Register the adapter registry listener:

```php
use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\WorkStructure\Services\AdapterRegistry;

// Listen for adapter registrations from plugins
EventDispatcher::add_event_listener(
    'leantime.domain.workstructure.services.adapterregistry.register',
    function ($params) {
        // Plugins dispatch this event with their adapter instance
    }
);
```

**Note to Claude Code:** The exact registration pattern depends on how plugins currently register themselves. Check `app/Domain/Plugins/Services/Registration.php` and existing plugin `register.php` files for the pattern. The AdapterRegistry should be resolvable via `app()->make(AdapterRegistry::class)` so plugins can register adapters in their own `register.php`.

### 1.8 Database Migration

Create the `zp_entity_links` table. Follow the installation pattern used by other domains:

| Column | Type | Constraints |
|---|---|---|
| id | INT | PRIMARY KEY, AUTO_INCREMENT |
| source_type | VARCHAR(50) | NOT NULL |
| source_item_id | INT | NOT NULL |
| target_entity_type | VARCHAR(50) | NOT NULL |
| target_entity_id | INT | NOT NULL |
| link_type | VARCHAR(30) | NOT NULL, DEFAULT 'generated_from' |
| created_by | INT | NULL |
| created_at | DATETIME | NOT NULL, DEFAULT CURRENT_TIMESTAMP |

Indexes:
- `idx_entity_links_source` on `(source_type, source_item_id)`
- `idx_entity_links_target` on `(target_entity_type, target_entity_id)`

**Note to Claude Code:** Check how the Logic Model canvas tables were created in Phase 1. Follow the same migration/installation pattern (SchemaBuilder, version check, etc.).

### Validation Checklist — Core Domain
- [ ] All model classes instantiate without errors
- [ ] `WorkStructureAdapter` interface is implementable (create a test stub)
- [ ] `AdapterRegistry` can register, retrieve, and list adapters
- [ ] `EntityLinkRepository` can CRUD entity links
- [ ] `WorkGenerator` can create a project with milestones, tasks, and goals from a WorkStructure (test with hardcoded WorkStructure)
- [ ] `WorkStructureReader::fromProject()` returns a populated WorkStructure for an existing project
- [ ] All 7 events fire at the correct points
- [ ] `zp_entity_links` table creates successfully on fresh install

---

## Deliverable 2: StrategyPro Logic Model Wizard

### 2.1 Plugin File Structure

Add these files to the existing StrategyPro plugin:

```
app/Plugins/StrategyPro/
├── Hxcontrollers/
│   └── Wizard/
│       ├── Start.php
│       ├── ScopeStep.php
│       ├── DeliverablesStep.php
│       ├── CriteriaStep.php
│       ├── ReviewStep.php
│       └── Routing.php
├── Templates/
│   ├── components/
│   │   ├── wizard-shell.blade.php
│   │   ├── wizard-step.blade.php
│   │   ├── milestone-row.blade.php
│   │   ├── task-row.blade.php
│   │   ├── goal-row.blade.php
│   │   ├── resource-note.blade.php
│   │   ├── entity-tree.blade.php
│   │   ├── routing-picker.blade.php
│   │   ├── completeness-badge.blade.php
│   │   └── entity-link-badge.blade.php
│   └── partials/
│       ├── wizard-scope.blade.php
│       ├── wizard-deliverables.blade.php
│       ├── wizard-criteria.blade.php
│       └── wizard-review.blade.php
├── Services/
│   ├── LogicModelAdapter.php
│   └── WizardStateService.php
├── Events/
│   └── Htmx/
│       └── HtmxWizardEvents.php
└── register.php                          ← UPDATE existing file
```

### 2.2 LogicModelAdapter

Implements `WorkStructureAdapter`. Reads from the Logic Model canvas tables and maps stages to WorkStructure properties:

| Canvas Stage | DB Source | WorkStructure Target | Mapping Logic |
|---|---|---|---|
| Impact (stage 5) | `zp_canvas_items` where `box = 'box5'` | `$structure->objective` | First item's title → objective. Additional items → metadata. |
| Outcomes (stage 4) | `zp_canvas_items` where `box = 'box4'` | `$structure->goals[]` | Each item → ProposedGoal. Use item description for metric details. |
| Activities (stage 2) | `zp_canvas_items` where `box = 'box2'` | `$structure->milestones[]` | Each item → ProposedMilestone. tempId = 'ms-' + item ID. |
| Outputs (stage 3) | `zp_canvas_items` where `box = 'box3'` | `$structure->tasks[]` | Each item → ProposedTask. Use `relates_to` field to link to parent Activity's milestone. |
| Inputs (stage 1) | `zp_canvas_items` where `box = 'box1'` | `$structure->resources[]` | Each item → ProposedResource. Infer type from content. |

**Note to Claude Code:** Check the actual Logic Model canvas table structure from `Docs/04-DATA-MODEL.md` and the Phase 1 implementation. The box names, field names, and relationships may differ. The `relates_to` field for Output→Activity relationships is a key mapping; verify how parent-child relationships are stored on the canvas.

**`assessCompleteness()`** logic:

```
Rich (4-5 stages with items) → level = 'rich'
Moderate (2-3 stages) → level = 'moderate'
Sparse (0-1 stages) → level = 'sparse'
Empty (0 items) → level = 'empty'
```

Gaps array populated with messages like "No outcomes defined — goals won't be created" or "No activities — milestones won't be created."

### 2.3 WizardStateService

Cache-based state management. Keys follow pattern: `wizard.{userId}.logicmodel.{boardId}.{key}`

```php
namespace Leantime\Plugins\StrategyPro\Services;

class WizardStateService
{
    public function __construct(private \Illuminate\Contracts\Cache\Repository $cache) {}

    public function getStructure(int $userId, int $boardId): ?WorkStructure
    {
        return $this->cache->get("wizard.{$userId}.logicmodel.{$boardId}.structure");
    }

    public function saveStructure(int $userId, int $boardId, WorkStructure $structure): void
    {
        $this->cache->put("wizard.{$userId}.logicmodel.{$boardId}.structure", $structure, 3600);
    }

    public function getStep(int $userId, int $boardId): int
    {
        return $this->cache->get("wizard.{$userId}.logicmodel.{$boardId}.step", 1);
    }

    public function saveStep(int $userId, int $boardId, int $step): void
    {
        $this->cache->put("wizard.{$userId}.logicmodel.{$boardId}.step", $step, 3600);
    }

    public function clear(int $userId, int $boardId): void
    {
        $this->cache->forget("wizard.{$userId}.logicmodel.{$boardId}.structure");
        $this->cache->forget("wizard.{$userId}.logicmodel.{$boardId}.step");
    }
}
```

### 2.4 HTMX Event Enum

```php
namespace Leantime\Plugins\StrategyPro\Events\Htmx;

enum HtmxWizardEvents: string
{
    case STEP_CHANGED = 'wizard_step_changed';
    case STRUCTURE_UPDATED = 'wizard_structure_updated';
    case GENERATION_COMPLETE = 'wizard_generation_complete';
}
```

### 2.5 HxControllers

Each wizard step is an HxController. Follow the pattern from CLAUDE.md:

**`Wizard/Start.php`** — Entry point. Runs the adapter, stores initial structure, renders wizard shell:

```php
namespace Leantime\Plugins\StrategyPro\Hxcontrollers\Wizard;

use Leantime\Core\Controller\HtmxController;
use Leantime\Plugins\StrategyPro\Services\LogicModelAdapter;
use Leantime\Plugins\StrategyPro\Services\WizardStateService;

class Start extends HtmxController
{
    protected static string $view = 'strategypro::partials.wizard-scope';

    private LogicModelAdapter $adapter;
    private WizardStateService $stateService;

    public function init(LogicModelAdapter $adapter, WizardStateService $stateService): void
    {
        $this->adapter = $adapter;
        $this->stateService = $stateService;
    }

    public function get(array $params): void
    {
        $boardId = (int) ($params['id'] ?? 0);
        $userId = session('userdata.id');

        // Run adapter
        $structure = $this->adapter->adapt($boardId);
        $completeness = $this->adapter->assessCompleteness($boardId);

        // Store in cache
        $this->stateService->saveStructure($userId, $boardId, $structure);
        $this->stateService->saveStep($userId, $boardId, 1);

        $this->tpl->assign('structure', $structure);
        $this->tpl->assign('completeness', $completeness);
        $this->tpl->assign('boardId', $boardId);
        $this->tpl->assign('currentStep', 1);
    }
}
```

**`Wizard/ScopeStep.php`** — Step 1: milestone confirmation. POST saves milestone edits, GET renders step.

**`Wizard/DeliverablesStep.php`** — Step 2: task mapping under milestones.

**`Wizard/CriteriaStep.php`** — Step 3: goal mapping from outcomes.

**`Wizard/ReviewStep.php`** — Step 4: review tree + generate button. POST triggers WorkGenerator.

**`Wizard/Routing.php`** — Handles routing decision selection. Returns inline in Step 4 or as a standalone step for lightweight paths.

**Note to Claude Code:** Each HxController follows this exact pattern:
- Extends `HtmxController`
- Static `$view` points to a Blade partial in `strategypro::partials.*`
- DI via `init()`, NOT `__construct()`
- `get()` loads data and assigns template vars
- POST actions (save step data) use a semantic method name, update cached state, and set HTMX events via `$this->setHTMXEvent()`

### 2.6 Blade Components

Build these components composing from existing shared components. Reference the inventory in `app/Views/Templates/components/`:

**Components that compose from shared UI (see 07-WORKSTRUCTURE.md Section 5.2 for full mapping):**

| Plugin Component | Composes From |
|---|---|
| `wizard-shell` | `<x-global::progress>` for step bar |
| `wizard-step` | `<x-global::elements.card>` with header/actions slots, `<x-global::elements.button-group>` for nav |
| `milestone-row` | `<x-global::forms.input>` for title edit, `<x-global::forms.date>` for timeframe |
| `task-row` | `<x-global::forms.input>`, `<x-global::forms.select>` for assignee, `<x-global::avatar>` |
| `goal-row` | `<x-global::forms.input>`, `<x-global::forms.select>` for metric type |
| `routing-picker` | `<x-global::selectable>` cards in a grid |
| `entity-tree` | `<x-global::accordion>` for milestone groups, `<x-global::badge>` for entity types |
| `completeness-badge` | `<x-global::elements.status-indicator>` |
| `entity-link-badge` | `<x-global::badge>` with link icon |
| `resource-note` | `<x-global::elements.card compact>` |

**Example markup patterns are in 07-WORKSTRUCTURE.md Section 5.3.** Follow those exactly.

### 2.7 Step Partials

Each partial is the content that swaps into `#wizardStep` via HTMX:

**`wizard-scope.blade.php`** (Step 1):
- Renders `<x-strategypro::wizard-step step="1">` wrapping a list of `<x-strategypro::milestone-row>` components
- Each row shows: Activity title (editable), description (expandable), start/end date pickers, drag handle for reorder, checkbox to include/exclude
- HTMX: `hx-get="/hx/strategypro/wizard/deliverablesStep/{{ $boardId }}"` on Next button, targeting `#wizardStep`
- POST data: milestone order, title edits, date changes, include/exclude state

**`wizard-deliverables.blade.php`** (Step 2):
- Groups tasks under their parent milestones
- Each milestone section is an `<x-global::accordion>` item
- Inside: list of `<x-strategypro::task-row>` components
- "Add task" button per milestone section
- HTMX: Back goes to Step 1, Next goes to Step 3

**`wizard-criteria.blade.php`** (Step 3):
- List of `<x-strategypro::goal-row>` components
- Each row: Outcome title (read-only source), goal title (editable), metric type dropdown, start/end value inputs
- Optional: link goal to a specific milestone
- HTMX: Back to Step 2, Next to Step 4

**`wizard-review.blade.php`** (Step 4):
- `<x-strategypro::routing-picker>` at the top
- `<x-strategypro::entity-tree>` showing the full proposed structure
- Generate button with `hx-post`, `hx-indicator` for loading state
- On success: `HtmxWizardEvents::GENERATION_COMPLETE` triggers redirect to new project

### 2.8 Toolbar Action

Add a "Create Work from Canvas" button to the Logic Model board toolbar. This is a toolbar action that only appears when StrategyPro plugin is active.

**Note to Claude Code:** Check how existing plugin toolbar actions are added. Look at the register.php patterns and menu filter hooks. The button should:
- Only appear on Logic Model board pages
- Only appear when StrategyPro is active
- Link to `/hx/strategypro/wizard/start/{boardId}` (opens in modal or navigates)
- Show a `<x-strategypro::completeness-badge>` indicating canvas readiness

### 2.9 Entity Link Display

After generation, canvas item cards should show an `<x-strategypro::entity-link-badge>` in their footer. This requires hooking into the canvas card rendering.

**Note to Claude Code:** Check how the Logic Model canvas card template renders items. It likely extends the base Canvas card component. Add a filter or event hook that appends the entity link badge when links exist for that item. Query `EntityLinkRepository::getBySource('logicmodel', $itemId)` to check for links.

### 2.10 Register Adapter

Update StrategyPro's `register.php` to register the LogicModelAdapter with the core AdapterRegistry:

```php
// In StrategyPro register.php
use Leantime\Domain\WorkStructure\Services\AdapterRegistry;
use Leantime\Plugins\StrategyPro\Services\LogicModelAdapter;

// Register adapter when WorkStructure domain is available
EventDispatcher::add_event_listener(
    'leantime.core.middleware.loadplugins.handle.plugins_loaded',
    function () {
        $registry = app()->make(AdapterRegistry::class);
        $adapter = app()->make(LogicModelAdapter::class);
        $registry->register($adapter);
    }
);
```

**Note to Claude Code:** Verify the correct event name for plugin loading. Check existing plugin register.php files for the pattern.

### Validation Checklist — Wizard
- [ ] LogicModelAdapter reads a populated Logic Model and produces a valid WorkStructure
- [ ] `assessCompleteness()` correctly reports rich/moderate/sparse/empty states
- [ ] "Create Work from Canvas" button appears on Logic Model board when StrategyPro is active
- [ ] Button does NOT appear when StrategyPro is inactive
- [ ] Clicking button launches wizard with Step 1 showing milestones from Activities
- [ ] Step 1: Can reorder milestones, edit titles, set dates, exclude items
- [ ] Step 1 → Step 2: tasks appear grouped under their parent milestones
- [ ] Step 2: Can add/remove tasks, edit titles, assign users
- [ ] Step 2 → Step 3: goals appear with Outcome source context
- [ ] Step 3: Can set metric types, target values, link goals to milestones
- [ ] Step 3 → Step 4: review tree shows complete proposed structure
- [ ] Step 4: routing picker shows correct options based on active plugins
- [ ] Step 4: Generate button creates project with milestones, tasks, and goals
- [ ] Entity links are recorded in zp_entity_links after generation
- [ ] Canvas item cards show entity link badges after generation
- [ ] Wizard state persists when navigating back/forward between steps
- [ ] Wizard state clears on completion or cancellation
- [ ] Wizard handles sparse canvas gracefully (shows gaps, allows proceeding with partial data)
- [ ] Wizard handles empty canvas (shows message, offers lightweight escape)
- [ ] All HTMX step transitions work without full page reload
- [ ] Loading indicator shows during generation
- [ ] After generation, user is navigated to the new project

---

## Execution Order

```
1. Core domain models          [0.5 day]   — All model classes
2. Entity links table          [0.5 day]   — Migration + repository
3. WorkGenerator service       [1 day]     — Write direction, test with hardcoded data
4. WorkStructureReader service [0.5 day]   — Read direction
5. Core events                 [0.5 day]   — All 7 event classes
6. AdapterRegistry             [0.5 day]   — Registration mechanism
7. LogicModelAdapter           [1 day]     — Canvas → WorkStructure mapping
8. WizardStateService          [0.5 day]   — Cache-based state
9. Blade components            [2 days]    — All 10 plugin components
10. HxControllers + partials   [2 days]    — All 6 controllers + 4 step partials
11. Toolbar action             [0.5 day]   — Button + completeness badge
12. Entity link display        [0.5 day]   — Badge on canvas cards
13. Adapter registration       [0.5 day]   — register.php hookup
14. Integration testing        [1 day]     — Full flow: canvas → wizard → generated project
```

**Total: ~10-11 days**

---

## Risk Register

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Canvas table structure differs from assumed box naming | MEDIUM | HIGH | Verify against actual Phase 1 implementation before writing adapter |
| Service method signatures don't match assumed params | MEDIUM | HIGH | Check actual Tickets, Projects, Goalcanvas service methods |
| Plugin register.php event timing issues | LOW | MEDIUM | Test adapter registration fires after plugin load |
| Cache serialization of WorkStructure objects | LOW | MEDIUM | Ensure all model properties are serializable (no closures, no resource handles) |
| HTMX step swapping breaks form state | MEDIUM | MEDIUM | Cache all step data server-side, don't rely on client form state |
| Existing shared components missing features | LOW | MEDIUM | Extend with slots/props rather than forking |
| Entity link queries slow on large projects | LOW | LOW | Indexes on both source and target columns |

---

## What This Document Does NOT Cover

- Living Link change detection and notifications (Phase 3)
- Copilot guided flow and canvas population (Phase 3)
- Diff view and regeneration flow (Phase 3)
- Ideas Board or Goal Canvas adapters (Phase 4)
- Stakeholder View plugin (Phase 4)
- Template system for pre-populated canvases (Phase 4)
- WorkStructure nesting for programs (open question)
- Business model decisions (free/bundled/premium)
