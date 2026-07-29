# Resource Allocation — Claude Code Implementation Prompt

**For Claude Code Execution**

| Field | Value |
|---|---|
| Project | Leantime — PgmPro Plugin |
| Scope | Resource Allocation View with canvas-backed storage |
| Architecture | ResourceStructure (schema) + Canvas (storage) + PgmPro (view) |
| Depends on | WorkStructure core, Canvas base domain, StrategyPro wizard, timesheets |
| Risk | MEDIUM — new schema domain, new canvas type, shared components |

---

## Pre-Read Documents (Read These First)

1. `CLAUDE.md` — project conventions, DI patterns, Blade rules
2. `Docs/07-WORKSTRUCTURE.md` — WorkStructure overview
3. `Docs/10-ADDENDUM-WORKSTRUCTURE-ARCHITECTURE.md` — **Critical:** schema-definition pattern to mirror
4. `Docs/11-ADDENDUM-RESOURCE-DECISIONS.md` — PgmPro owns resource management
5. `Docs/12-RESOURCE-ALLOCATION.md` — **The PRD for this build**
6. `ResourceV1V2.jsx` — **Pixel-accurate visual reference**

Additionally, before writing any code:
- Read an existing canvas domain (e.g., `app/Domain/Ideas/`) to understand the canvas pattern: how `zp_canvas` and `zp_canvas_items` work, how `type` differentiates board variants, how `box` categorizes items
- Read `app/Domain/Goalcanvas/` for a more complex canvas example with JSON data fields
- Read `app/Domain/Entityrelations/` to understand instance-level linking

---

## Critical Constraints

### DO

- Use `zp_canvas` and `zp_canvas_items` tables for resource data storage — **NO new tables**
- Register resource board type with `type = 'resource'` on `zp_canvas`
- Use `box` column for resource categories: `'people'`, `'budget'`, `'dependency'`
- Use `data` column for structured JSON (hours, amounts, allocations)
- Use `status` column for `'stub'` / `'active'` / `'archived'`
- Use `zp_entityrelations` for linking resource items to source canvas inputs
- Follow the WorkStructure schema pattern from addendum 10
- Use `tw:` prefix for all Tailwind classes
- Use CSS custom properties for colors
- Follow HxController pattern for HTMX interactions
- Use DI via `init()` not `__construct()`
- Use `@props` in Blade components
- Build shared components in `app/Views/Templates/components/`
- Test each deliverable before proceeding to the next

### DO NOT

- Create `zp_resource_allocations` or `zp_resource_budgets` tables — those are **removed from v1 spec**
- Modify existing canvas domain services or repositories
- Modify Logic Model Canvas board code
- Modify existing Projects, Tickets, Timesheets, or Users services
- Create `.tpl.php` files (Blade only)
- Add jQuery (HTMX + vanilla JS only)

---

## Deliverable 1: Shared Blade Components

Build four reusable components in `app/Views/Templates/components/`. These are core infrastructure — not plugin code.

### 1A: Collapsible Section

**File:** `app/Views/Templates/components/collapsible.blade.php`

```blade
@props([
    'title' => '',
    'icon' => null,
    'defaultOpen' => true,
    'id' => null,
])

<div
    x-data="{ open: {{ $defaultOpen ? 'true' : 'false' }} }"
    @if($id) id="{{ $id }}" @endif
    class="tw:bg-white tw:rounded-xl tw:border tw:border-[#E8ECF0] tw:overflow-hidden"
>
    {{-- Header --}}
    <button
        @click="open = !open"
        class="tw:w-full tw:flex tw:items-center tw:gap-3 tw:px-5 tw:py-3.5 tw:cursor-pointer tw:select-none"
        :class="open ? 'tw:border-b tw:border-[#F0F1F3]' : ''"
    >
        @if($icon)
            <span class="tw:text-sm">{{ $icon }}</span>
        @endif
        <span class="tw:text-sm tw:font-bold tw:text-[#1A1A2E]">{{ $title }}</span>

        {{-- Collapsed summary slot --}}
        <span x-show="!open" x-cloak class="tw:flex tw:items-center tw:gap-2 tw:ml-2">
            {{ $collapsedSummary ?? '' }}
        </span>

        <svg
            class="tw:ml-auto tw:w-4 tw:h-4 tw:text-[#9CA3AF] tw:transition-transform tw:duration-200"
            :class="open ? 'tw:rotate-180' : ''"
            fill="none" stroke="currentColor" viewBox="0 0 24 24"
        >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Content --}}
    <div
        x-show="open"
        x-collapse
        class="tw:px-5 tw:py-4"
    >
        {{ $slot }}
    </div>
</div>
```

### 1B: Proportion Bar

**File:** `app/Views/Templates/components/proportion-bar.blade.php`

```blade
@props([
    'segments' => [],
    'total' => null,
    'height' => '8px',
    'radius' => '4px',
    'showLabels' => false,
    'trackColor' => '#F0F1F3',
])

@php
    $computedTotal = $total ?? collect($segments)->sum('value');
    if ($computedTotal <= 0) $computedTotal = 1;
@endphp

<div
    class="tw:w-full tw:overflow-hidden tw:flex"
    style="height: {{ $height }}; border-radius: {{ $radius }}; background: {{ $trackColor }}"
>
    @foreach($segments as $seg)
        @php
            $pct = ($seg['value'] / $computedTotal) * 100;
            $overlayPct = isset($seg['overlay']) ? ($seg['overlay']['value'] / max($seg['value'], 1)) * 100 : 0;
        @endphp
        <div
            class="tw:relative tw:h-full tw:flex tw:items-center tw:justify-center"
            style="width: {{ $pct }}%; background: {{ $seg['color'] }}"
        >
            @if($showLabels && $pct > 12 && isset($seg['label']))
                <span class="tw:text-[10px] tw:font-bold tw:text-white tw:relative tw:z-10">
                    {{ $seg['label'] }}
                </span>
            @endif

            {{-- V2 overlay --}}
            @if(isset($seg['overlay']))
                <div
                    class="tw:absolute tw:bottom-0 tw:left-0 tw:w-full"
                    style="height: {{ min($overlayPct, 100) }}%; background: {{ $seg['overlay']['color'] }}"
                ></div>
            @endif
        </div>
    @endforeach
</div>
```

### 1C: Avatar Stack

**File:** `app/Views/Templates/components/avatar-stack.blade.php`

```blade
@props([
    'people' => [],
    'size' => 28,
    'overlap' => -6,
    'maxShow' => 8,
])

@php
    $visible = array_slice($people, 0, $maxShow);
    $remaining = count($people) - $maxShow;
    $statusColors = [
        'full' => '#059669',
        'partial' => '#4A85B5',
        'open' => 'transparent',
    ];
@endphp

<div class="tw:flex tw:items-center">
    @foreach($visible as $i => $person)
        @php
            $bg = $statusColors[$person['status'] ?? 'partial'] ?? '#4A85B5';
            $isOpen = ($person['status'] ?? '') === 'open';
        @endphp
        <div
            class="tw:rounded-full tw:flex tw:items-center tw:justify-center tw:font-bold tw:text-white tw:flex-shrink-0
                   {{ $isOpen ? 'tw:border-2 tw:border-dashed tw:border-[#D1D5DB]' : '' }}"
            style="width: {{ $size }}px; height: {{ $size }}px;
                   font-size: {{ $size * 0.32 }}px;
                   background: {{ $bg }};
                   {{ $i > 0 ? 'margin-left: ' . $overlap . 'px;' : '' }}
                   {{ !$isOpen ? 'border: 2px solid white;' : '' }}"
        >
            {{ $person['initials'] ?? '?' }}
        </div>
    @endforeach

    @if($remaining > 0)
        <div
            class="tw:rounded-full tw:flex tw:items-center tw:justify-center tw:font-bold tw:text-[#9CA3AF] tw:bg-[#F0F1F3] tw:flex-shrink-0"
            style="width: {{ $size }}px; height: {{ $size }}px;
                   font-size: {{ $size * 0.32 }}px;
                   margin-left: {{ $overlap }}px;
                   border: 2px solid white;"
        >
            +{{ $remaining }}
        </div>
    @endif
</div>
```

### 1D: Metric Cell

**File:** `app/Views/Templates/components/metric-cell.blade.php`

```blade
@props([
    'label' => '',
    'value' => '',
    'suffix' => null,
    'secondary' => null,
    'color' => '#1A1A2E',
    'secondaryColor' => '#9CA3AF',
])

<div class="tw:px-4 tw:py-3.5">
    <div class="tw:text-[11px] tw:font-medium tw:text-[#9CA3AF] tw:mb-1">
        {{ $label }}
    </div>
    <div class="tw:flex tw:items-baseline tw:gap-1">
        <span class="tw:text-[22px] tw:font-bold" style="color: {{ $color }}">
            {{ $value }}
        </span>
        @if($suffix)
            <span class="tw:text-xs tw:font-normal tw:text-[#9CA3AF]">{{ $suffix }}</span>
        @endif
    </div>
    @if($secondary)
        <div class="tw:text-[11px] tw:mt-0.5" style="color: {{ $secondaryColor }}">
            {{ $secondary }}
        </div>
    @endif
    {{-- Optional slot for inline visualizations --}}
    {{ $slot ?? '' }}
</div>
```

### Test Deliverable 1

Create a test view that uses all four components with hardcoded data. Verify:
- Collapsible toggles with animation
- Proportion bar renders segments correctly with and without overlays
- Avatar stack overlaps and shows "+N" overflow
- Metric cell displays all variants

---

## Deliverable 2: ResourceStructure Core Domain

### 2A: Directory Structure

```
app/Domain/ResourceStructure/
├── Models/
│   └── ResourceFieldContract.php
├── Services/
│   ├── ResourceStructureService.php
│   └── ResourceRegistrar.php
├── Repositories/
│   └── ResourceStructureRepository.php
└── register.php
```

### 2B: ResourceRegistrar

Registers the "Resource" structure in WorkStructure's registry. Uses the same `StructureRegistry` interface from addendum 10.

```php
class ResourceRegistrar
{
    public function register(StructureRegistry $registry): void
    {
        $structureId = $registry->registerStructure([
            'title' => 'Resource',
            'description' => 'People, budget, and dependency allocations for a program',
            'type' => 'plugin', // registered by PgmPro
        ]);

        $registry->registerElementType($structureId, [
            'type_key' => 'people',
            'label' => 'People',
            'description' => 'Person allocated with weekly hours per project',
            'domain_reference' => 'Leantime\\Domain\\Canvas', // stored in canvas tables
            'sort_order' => 1,
            'meta' => json_encode(['box' => 'people', 'canvas_type' => 'resource']),
        ]);

        $registry->registerElementType($structureId, [
            'type_key' => 'budget',
            'label' => 'Budget',
            'description' => 'Budget line item allocated to a project',
            'domain_reference' => 'Leantime\\Domain\\Canvas',
            'sort_order' => 2,
            'meta' => json_encode(['box' => 'budget', 'canvas_type' => 'resource']),
        ]);

        $registry->registerElementType($structureId, [
            'type_key' => 'dependency',
            'label' => 'Dependency',
            'description' => 'External partnership or facility required',
            'domain_reference' => 'Leantime\\Domain\\Canvas',
            'sort_order' => 3,
            'meta' => json_encode(['box' => 'dependency', 'canvas_type' => 'resource']),
        ]);

        // Relationships within Resource structure
        $registry->registerRelationship($structureId, 'people', 'project', 'assigned_to');
        $registry->registerRelationship($structureId, 'budget', 'project', 'funds');
        $registry->registerRelationship($structureId, 'dependency', 'project', 'required_by');
    }
}
```

### 2C: ResourceStructureRepository

Wraps canvas table queries with resource-specific filters.

```php
class ResourceStructureRepository
{
    private ConnectionInterface $db;

    public function init(DbCore $db): void
    {
        $this->db = $db->getConnection();
    }

    /**
     * Get the resource canvas board for a program.
     * Creates one if it doesn't exist.
     */
    public function getOrCreateResourceCanvas(int $programProjectId): int
    {
        $existing = $this->db->table('zp_canvas')
            ->where('type', 'resource')
            ->where('projectId', $programProjectId)
            ->first();

        if ($existing) {
            return $existing->id;
        }

        return $this->db->table('zp_canvas')->insertGetId([
            'title' => 'Resource Allocation',
            'type' => 'resource',
            'projectId' => $programProjectId,
            'author' => session('userdata.id'),
            'created' => now(),
        ]);
    }

    /**
     * Get resource items by category (box).
     */
    public function getItemsByBox(int $canvasId, string $box): array
    {
        $results = $this->db->table('zp_canvas_items')
            ->where('canvasId', $canvasId)
            ->where('box', $box)
            ->orderBy('sortindex')
            ->get();

        return array_map(function ($item) {
            $item = (array) $item;
            // Parse the JSON data field
            $item['parsedData'] = json_decode($item['data'] ?? '{}', true) ?: [];
            return $item;
        }, $results->toArray());
    }

    /**
     * Get all resource items for a canvas.
     */
    public function getAllItems(int $canvasId): array
    {
        $results = $this->db->table('zp_canvas_items')
            ->where('canvasId', $canvasId)
            ->orderBy('box')
            ->orderBy('sortindex')
            ->get();

        return array_map(function ($item) {
            $item = (array) $item;
            $item['parsedData'] = json_decode($item['data'] ?? '{}', true) ?: [];
            return $item;
        }, $results->toArray());
    }

    /**
     * Create a resource canvas item.
     */
    public function addItem(int $canvasId, array $values): int
    {
        return $this->db->table('zp_canvas_items')->insertGetId([
            'canvasId' => $canvasId,
            'box' => $values['box'],
            'description' => $values['description'] ?? '',
            'assumptions' => $values['assumptions'] ?? '',
            'data' => json_encode($values['data'] ?? []),
            'conclusion' => $values['conclusion'] ?? '',
            'status' => $values['status'] ?? 'stub',
            'author' => $values['author'] ?? session('userdata.id'),
            'created' => now(),
            'modified' => now(),
        ]);
    }

    /**
     * Update a resource canvas item's data.
     */
    public function updateItemData(int $itemId, array $data): bool
    {
        return $this->db->table('zp_canvas_items')
            ->where('id', $itemId)
            ->update([
                'data' => json_encode($data),
                'modified' => now(),
            ]) >= 0;
    }

    /**
     * Update item status.
     */
    public function updateItemStatus(int $itemId, string $status): bool
    {
        return $this->db->table('zp_canvas_items')
            ->where('id', $itemId)
            ->update([
                'status' => $status,
                'modified' => now(),
            ]) >= 0;
    }

    /**
     * Get actual hours from timesheets, grouped by user + project.
     * Returns: [{userId, projectId, totalHours}]
     */
    public function getActualHours(array $projectIds, string $dateFrom, string $dateTo): array
    {
        $results = $this->db->table('zp_timesheets')
            ->select(
                'userId',
                'projectId',
                $this->db->raw('SUM(hours) as totalHours')
            )
            ->whereIn('projectId', $projectIds)
            ->whereBetween('workDate', [$dateFrom, $dateTo])
            ->groupBy('userId', 'projectId')
            ->get();

        return array_map(fn ($r) => (array) $r, $results->toArray());
    }

    /**
     * Get stubs that need completion.
     */
    public function getStubs(int $canvasId): array
    {
        return $this->getItemsByBox($canvasId, 'people')
            + $this->getItemsByBox($canvasId, 'budget')
            + $this->getItemsByBox($canvasId, 'dependency');
        // Filter to status='stub' in service layer
    }
}
```

### 2D: ResourceStructureService

```php
class ResourceStructureService
{
    private ResourceStructureRepository $repo;

    public function init(ResourceStructureRepository $repo): void
    {
        $this->repo = $repo;
    }

    /**
     * Get complete resource summary for a program.
     * This is the main method the PgmPro view controller calls.
     */
    public function getProgramResourceSummary(int $programProjectId, array $childProjectIds): array
    {
        $canvasId = $this->repo->getOrCreateResourceCanvas($programProjectId);

        $people = $this->repo->getItemsByBox($canvasId, 'people');
        $budget = $this->repo->getItemsByBox($canvasId, 'budget');
        $dependencies = $this->repo->getItemsByBox($canvasId, 'dependency');

        return [
            'canvasId' => $canvasId,
            'people' => $this->hydratePeople($people),
            'budget' => $this->hydrateBudget($budget),
            'dependencies' => $dependencies,
            'projects' => $childProjectIds,
        ];
    }

    /**
     * Parse people items' JSON data into structured allocation arrays.
     */
    private function hydratePeople(array $items): array
    {
        return array_map(function ($item) {
            $data = $item['parsedData'];
            return [
                'id' => $item['id'],
                'name' => $item['description'],
                'role' => $item['assumptions'],
                'userId' => $data['userId'] ?? 0,
                'capacity' => $data['capacity'] ?? 40,
                'allocations' => $data['allocations'] ?? [],
                'status' => $item['status'],
                'sourceCanvasItemId' => $item['milestoneId'] ?? null,
            ];
        }, $items);
    }

    /**
     * Parse budget items' JSON data into structured budget arrays.
     */
    private function hydrateBudget(array $items): array
    {
        return array_map(function ($item) {
            $data = $item['parsedData'];
            return [
                'id' => $item['id'],
                'name' => $item['description'],
                'category' => $item['assumptions'],
                'projectId' => $data['projectId'] ?? 0,
                'budgeted' => $data['budgeted'] ?? 0,
                'spent' => $data['spent'] ?? 0,
                'color' => $data['color'] ?? '#9CA3AF',
                'status' => $item['status'],
            ];
        }, $items);
    }

    /**
     * Get actual hours for V2 mode.
     */
    public function getActualHours(array $projectIds, string $period = 'this_week'): array
    {
        [$dateFrom, $dateTo] = $this->getPeriodDates($period);
        return $this->repo->getActualHours($projectIds, $dateFrom, $dateTo);
    }

    /**
     * Seed resource canvas from Logic Model Inputs.
     * Called by StrategyPro wizard during program creation.
     */
    public function seedFromCanvasInputs(int $programProjectId, array $canvasInputs): array
    {
        $canvasId = $this->repo->getOrCreateResourceCanvas($programProjectId);
        $created = [];

        foreach ($canvasInputs as $input) {
            $text = $input['description'] ?? '';
            $classification = $this->classifyInput($text);

            if (!$classification) continue;

            $itemData = $this->extractDataFromText($text, $classification);

            $itemId = $this->repo->addItem($canvasId, [
                'box' => $classification,
                'description' => $text,
                'assumptions' => $itemData['role'] ?? $itemData['category'] ?? '',
                'data' => $itemData['data'],
                'status' => 'stub',
            ]);

            $created[] = [
                'itemId' => $itemId,
                'sourceCanvasItemId' => $input['id'],
                'classification' => $classification,
            ];
        }

        return $created;
    }

    /**
     * Classify a Logic Model input text into a resource category.
     */
    private function classifyInput(string $text): ?string
    {
        if (preg_match('/funding|grant|budget|allocation|\$\d/i', $text)) {
            return 'budget';
        }
        if (preg_match('/staff|people|manager|coordinator|specialist|director|volunteer/i', $text)) {
            return 'people';
        }
        if (preg_match('/partner|organization|vendor|facility|agreement|community center/i', $text)) {
            return 'dependency';
        }
        return null;
    }

    /**
     * Extract structured data from input text.
     */
    private function extractDataFromText(string $text, string $classification): array
    {
        $result = ['data' => []];

        if ($classification === 'budget') {
            // "$250K annual budget" → 250000
            if (preg_match('/\$(\d+(?:\.\d+)?)\s*[kK]/', $text, $m)) {
                $result['data']['budgeted'] = (float) $m[1] * 1000;
            } elseif (preg_match('/\$(\d[\d,]*)/', $text, $m)) {
                $result['data']['budgeted'] = (float) str_replace(',', '', $m[1]);
            }
            $result['category'] = 'general';
        }

        if ($classification === 'people') {
            // "3 case managers" → count 3, role "case manager"
            if (preg_match('/(\d+)\s+(.+)/i', $text, $m)) {
                $result['data']['count'] = (int) $m[1];
                $result['role'] = trim($m[2]);
            }
            $result['data']['capacity'] = 40;
            $result['data']['allocations'] = [];
        }

        return $result;
    }

    /**
     * Check stub completion status for Setting Up → Ongoing transition.
     */
    public function getStubCompletionStatus(int $programProjectId): array
    {
        $canvasId = $this->repo->getOrCreateResourceCanvas($programProjectId);
        $allItems = $this->repo->getAllItems($canvasId);

        $stubs = array_filter($allItems, fn ($i) => $i['status'] === 'stub');
        $active = array_filter($allItems, fn ($i) => $i['status'] === 'active');

        return [
            'totalItems' => count($allItems),
            'stubCount' => count($stubs),
            'activeCount' => count($active),
            'isComplete' => count($stubs) === 0 && count($allItems) > 0,
            'stubs' => array_map(function ($stub) {
                $prompt = match ($stub['box']) {
                    'people' => 'Who? Hours?',
                    'budget' => 'Amount?',
                    'dependency' => 'Status?',
                    default => 'Complete this',
                };
                return [
                    'id' => $stub['id'],
                    'type' => $stub['box'],
                    'name' => $stub['description'],
                    'prompt' => $prompt,
                ];
            }, array_values($stubs)),
        ];
    }

    private function getPeriodDates(string $period): array
    {
        return match ($period) {
            'this_week' => [
                now()->startOfWeek()->format('Y-m-d'),
                now()->endOfWeek()->format('Y-m-d'),
            ],
            'last_week' => [
                now()->subWeek()->startOfWeek()->format('Y-m-d'),
                now()->subWeek()->endOfWeek()->format('Y-m-d'),
            ],
            'this_month' => [
                now()->startOfMonth()->format('Y-m-d'),
                now()->endOfMonth()->format('Y-m-d'),
            ],
            default => [
                now()->startOfWeek()->format('Y-m-d'),
                now()->endOfWeek()->format('Y-m-d'),
            ],
        };
    }
}
```

### Test Deliverable 2

Use tinker or a test route to verify:
- `getOrCreateResourceCanvas()` creates a canvas with `type='resource'`
- `addItem()` creates canvas items in correct `box` categories
- `getProgramResourceSummary()` returns hydrated people/budget/dependency arrays
- `seedFromCanvasInputs()` correctly classifies and creates stubs
- `getStubCompletionStatus()` reports stub counts and prompts

---

## Deliverable 3: PgmPro Route, Controller, and V1 View

### 3A: Route

Add to PgmPro plugin routes:

```php
Route::get('/pgmpro/program/{programId}/resources', [
    ResourceAllocationController::class, 'get'
])->name('pgmpro.resources');
```

### 3B: Controller

```php
namespace Leantime\Plugins\PgmPro\Controllers;

class ResourceAllocation
{
    private ResourceStructureService $resourceService;
    private ProjectsService $projectsService;

    public function init(
        ResourceStructureService $resourceService,
        ProjectsService $projectsService
    ): void {
        $this->resourceService = $resourceService;
        $this->projectsService = $projectsService;
    }

    public function get(int $programId): mixed
    {
        // Get child projects under this program
        $childProjects = $this->projectsService->getProjectsByParent($programId);
        $childProjectIds = array_column($childProjects, 'id');

        // Get resource summary
        $resources = $this->resourceService->getProgramResourceSummary(
            $programId,
            $childProjectIds
        );

        // Get stub status
        $stubStatus = $this->resourceService->getStubCompletionStatus($programId);

        return $this->tpl->display('pgmpro::resources.index', [
            'programId' => $programId,
            'projects' => $childProjects,
            'resources' => $resources,
            'stubStatus' => $stubStatus,
            'version' => 'v1', // default
        ]);
    }
}
```

### 3C: Blade View Structure

```
resources/views/pgmpro/resources/
├── index.blade.php
├── partials/
│   ├── header.blade.php
│   ├── summary-strip.blade.php
│   ├── project-rows.blade.php
│   ├── people-section.blade.php
│   ├── budget-section.blade.php
│   ├── auto-fill.blade.php
│   └── reading-guide.blade.php
└── components/
    ├── person-container.blade.php     (resource-view specific)
    ├── project-row.blade.php          (resource-view specific)
    ├── budget-bar.blade.php           (uses x-proportion-bar)
    ├── budget-line.blade.php          (uses x-proportion-bar)
    └── version-toggle.blade.php
```

### 3D: Person Container (Critical Visual)

This is the signature component. **Match `ResourceV1V2.jsx` exactly.**

```blade
{{-- pgmpro::resources.components.person-container --}}
@props([
    'person' => [],
    'projects' => [],
    'version' => 'v1',
    'actuals' => [],
])

@php
    $capacity = $person['capacity'] ?? 40;
    $allocations = $person['allocations'] ?? [];
    $totalAllocated = array_sum($allocations);
    $fillPct = $capacity > 0 ? min(($totalAllocated / $capacity) * 100, 100) : 0;
    $isEmpty = $totalAllocated === 0;
    $isFull = $fillPct >= 95;
    $isOver = $totalAllocated > $capacity;
@endphp

<div class="tw:flex tw:flex-col tw:items-center tw:gap-1.5">
    {{-- Container --}}
    <div
        class="tw:relative tw:w-16 tw:rounded-lg tw:overflow-hidden
               {{ $isEmpty ? 'tw:border-2 tw:border-dashed tw:border-[#D1D5DB]' : 'tw:bg-[#F0F2F5]' }}"
        style="height: 200px;"
    >
        @if($isEmpty)
            {{-- Empty state --}}
            <div class="tw:absolute tw:inset-0 tw:flex tw:flex-col tw:items-center tw:justify-center tw:gap-1">
                <span class="tw:text-xl tw:text-[#D1D5DB]">+</span>
                <span class="tw:text-[10px] tw:text-[#9CA3AF]">Assign</span>
            </div>
        @else
            {{-- Tick marks --}}
            @foreach([25, 50, 75] as $tick)
                <div
                    class="tw:absolute tw:left-0 tw:w-full tw:z-[1]"
                    style="bottom: {{ $tick }}%; border-top: 1px solid rgba(0,0,0,0.04);"
                ></div>
            @endforeach

            {{-- Segments (fill from bottom) --}}
            @php $currentBottom = 0; @endphp
            @foreach($allocations as $projectId => $hours)
                @php
                    $segPct = ($hours / $capacity) * 100;
                    $project = collect($projects)->firstWhere('id', $projectId);
                    $color = $project['color'] ?? '#9CA3AF';
                    $actualHours = $actuals[$projectId] ?? 0;
                    $actualPct = $hours > 0 ? ($actualHours / $hours) * 100 : 0;
                @endphp
                <div
                    class="tw:absolute tw:left-0 tw:w-full tw:z-[2] tw:flex tw:items-center tw:justify-center"
                    style="bottom: {{ $currentBottom }}%; height: {{ $segPct }}%; background: {{ $color }};"
                    x-bind:style="segmentOpacity('{{ $projectId }}')"
                >
                    {{-- Label --}}
                    @if($segPct > 14)
                        <span class="tw:relative tw:z-[4] tw:text-white tw:font-bold
                                     {{ $version === 'v2' ? 'tw:text-[10px]' : 'tw:text-xs' }}">
                            @if($version === 'v2')
                                {{ $actualHours }}/{{ $hours }}
                            @else
                                {{ $hours }}h
                            @endif
                        </span>
                    @endif

                    {{-- V2 actual overlay --}}
                    @if($version === 'v2')
                        <div
                            class="tw:absolute tw:bottom-0 tw:left-0 tw:w-full tw:z-[3]"
                            style="height: {{ min($actualPct, 100) }}%;
                                   background: rgba(255,255,255,0.22);
                                   {{ $actualPct < 100 ? 'border-top: 2px solid rgba(255,255,255,0.6);' : '' }}"
                        ></div>
                    @endif
                </div>

                {{-- Separator between segments --}}
                @if(!$loop->first)
                    <div
                        class="tw:absolute tw:left-0 tw:w-full tw:z-[3]"
                        style="bottom: {{ $currentBottom }}%; border-top: 1.5px solid rgba(255,255,255,0.5);"
                    ></div>
                @endif

                @php $currentBottom += $segPct; @endphp
            @endforeach

            {{-- Percentage badge --}}
            <div
                class="tw:absolute tw:left-1/2 tw:-translate-x-1/2 tw:z-[5]
                       tw:px-2 tw:py-0.5 tw:rounded-full
                       tw:text-[10px] tw:font-bold tw:text-white tw:whitespace-nowrap"
                style="bottom: {{ min($fillPct, 100) }}%;
                       transform: translateX(-50%) translateY(50%);
                       background: {{ $isFull ? '#059669' : ($isOver ? '#DC2626' : '#1A1A2E') }};"
            >
                {{ round($fillPct) }}%
            </div>
        @endif
    </div>

    {{-- Name --}}
    <div class="tw:text-xs tw:font-semibold tw:text-[#1A1A2E] tw:text-center tw:truncate tw:max-w-16">
        {{ $person['name'] ?? '?' }}
    </div>

    {{-- Role --}}
    <div class="tw:text-[10px] tw:text-[#9CA3AF] tw:text-center tw:truncate tw:max-w-16">
        {{ $person['role'] ?? '' }}
    </div>

    {{-- Footer --}}
    <div class="tw:text-xs tw:font-bold tw:text-[#1A1A2E]">
        @if($version === 'v2')
            {{ array_sum($actuals) }}/{{ $totalAllocated }}h
        @else
            {{ $totalAllocated }}/{{ $capacity }}h
        @endif
    </div>

    {{-- V2 variance --}}
    @if($version === 'v2')
        @php
            $totalActual = array_sum($actuals);
            $variance = $totalActual - $totalAllocated;
            $varianceColor = $variance > 2 ? '#DC2626' : ($variance < -5 ? '#D97706' : '#9CA3AF');
            $varianceText = $variance > 2 ? abs($variance) . 'h over'
                          : ($variance < -5 ? abs($variance) . 'h under' : 'On track');
        @endphp
        <div class="tw:text-[10px]" style="color: {{ $varianceColor }}">
            {{ $varianceText }}
        </div>
    @endif
</div>
```

### 3E: JavaScript — Unified Hover

```javascript
// Alpine.js component for the resource view page
function resourceView() {
    return {
        version: 'v1',
        hoveredProject: null,
        showAutoFill: false,
        peopleExpanded: true,
        budgetExpanded: true,

        setVersion(v) { this.version = v; },
        hoverProject(id) { this.hoveredProject = id; },

        // Opacity helpers
        projectRowOpacity(projectId) {
            if (!this.hoveredProject) return 'opacity: 1';
            return this.hoveredProject === projectId ? 'opacity: 1' : 'opacity: 0.4';
        },
        segmentOpacity(projectId) {
            if (!this.hoveredProject) return 'opacity: 1';
            return this.hoveredProject === projectId ? 'opacity: 1' : 'opacity: 0.12';
        },
        budgetLineOpacity(projectId) {
            if (!this.hoveredProject) return 'opacity: 1';
            return this.hoveredProject === projectId ? 'opacity: 1' : 'opacity: 0.3';
        },
    }
}
```

---

## Deliverable 4: V1 Complete

Build all V1 partials — summary-strip, project-rows, people-section, budget-section.

Use shared components: `x-collapsible` for sections, `x-proportion-bar` for all bars, `x-avatar-stack` for collapsed people, `x-metric-cell` for summary strip cells.

Use `person-container` for each person in the People section.

**Test:** Full V1 view renders matching `ResourceV1V2.jsx`. Hover highlights work across sections. Collapsible toggles work. All measurements match spec.

---

## Deliverable 5: V2 Overlays

Add `getActualHours()` call in controller when `version === 'v2'`. Pass actuals to all components.

Build:
- V2 summary cell (Actual this week)
- V2 project row columns (planned/actual/variance)
- V2 person container overlays (already in component, activated by `version='v2'`)
- V2 budget overlays (proportion bar overlay support)
- Reading guide partial

**Test:** Toggle V1 ↔ V2. V1 is unchanged. V2 shows overlays, variance badges, split columns. Reading guide appears/disappears.

---

## Deliverable 6: Canvas Auto-Fill + Stub Seeding

Build `auto-fill.blade.php` partial — two-column layout showing classification results.

Wire `seedFromCanvasInputs()` call into StrategyPro wizard after program creation.

Record entityrelations: `canvas_input_id → resource_item_id` with type `seeded_from`.

Build Setting Up indicator when stubs exist.

**Test:** Create a program from Logic Model. Verify Inputs items produce resource stubs. Verify auto-fill preview shows classification. Verify stubs show completion prompts.

---

## Implementation Order

| Step | Deliverable | Test gate |
|---|---|---|
| 1 | Shared components (collapsible, proportion-bar, avatar-stack, metric-cell) | All render correctly with test data |
| 2 | ResourceStructure core (registrar, repository, service) | Canvas items CRUD works, seeding classifies correctly |
| 3 | PgmPro route + controller + index.blade.php skeleton | Route loads, data reaches view |
| 4 | V1 summary strip + project rows | Visual match to prototype |
| 5 | V1 people section with person containers + hover | Expand/collapse, hover highlights |
| 6 | V1 budget section | Full V1 complete |
| 7 | V2 overlays + reading guide | V1 ↔ V2 toggle, overlays render |
| 8 | Canvas auto-fill + stub seeding | End-to-end: Logic Model → Resource stubs |

---

## Quick Reference

### Colors

| Token | Value | Usage |
|---|---|---|
| Project green | `#3E937A` | First project |
| Project amber | `#C09035` | Second project |
| Project purple | `#8E6AAD` | Third project |
| Good | `#059669` | At capacity, on track |
| Warning | `#D97706` | Under-allocated |
| Critical | `#DC2626` | Over-allocated |
| Neutral | `#9CA3AF` | Labels, empty |
| Page bg | `#F8F9FB` | Body |
| Card bg | `#ffffff` | Cards |
| Track bg | `#F0F1F3` | Bars, inactive |
| Border | `#E8ECF0` | Card borders |
| Text primary | `#1A1A2E` | Headers, values |

### V2 Overlays

| Element | Value |
|---|---|
| Container actual fill | `rgba(255,255,255,0.22)` |
| Container actual border | `2px solid rgba(255,255,255,0.6)` |
| Budget bar spent | `rgba(0,0,0,0.12)` |
| Budget line spent | `rgba(0,0,0,0.15)` |

### Variance Thresholds

| Context | Green | Amber | Red |
|---|---|---|---|
| Hours | within ±2h | < -5h under | > +2h over |
| Person footer | within 20% | > 20% under | over |
| Summary | ≥ 85% | < 85% | over |
| Budget | < 90% spent | — | > 90% spent |

### Measurements

| Element | Spec |
|---|---|
| Person container | 64px × 200px, 8px gap, 8px radius |
| Cards | 12px radius |
| Capacity bar | 100px × 8px × 4px radius |
| Budget bar | 28px × 8px radius |
| Summary bar | 6px × 3px radius |
| Swatches | 14×14 (row), 10×10 (budget) |
| Avatar stack | 28px, -6px overlap |
| Max width | 960px |

### Canvas Field Mapping

| Canvas column | People | Budget | Dependency |
|---|---|---|---|
| `box` | `'people'` | `'budget'` | `'dependency'` |
| `description` | Person name | Budget line name | Dependency name |
| `assumptions` | Role title | Category | Type |
| `data` (JSON) | `{userId, capacity, allocations}` | `{projectId, budgeted, spent, color}` | `{partnerName, type, confirmed}` |
| `conclusion` | Notes | Notes | Status notes |
| `status` | stub / active | stub / active | stub / active |
