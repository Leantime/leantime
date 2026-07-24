<?php

/**
 * Repository
 */

namespace Leantime\Domain\Goalcanvas\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\EntityRelationshipEnum;
use Leantime\Domain\Blueprints\Repositories\Blueprints;
use Leantime\Domain\Tickets\Repositories\Tickets;

class Goalcanvas extends Blueprints
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'goal';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access protected
     * @var    string Fontawesome icone
     */
    protected string $icon = 'fa-bullseye';

    /***
     * disclaimer - Disclaimer (may be extended)
     *
     * @access protected
     * @var    string Disclaimer (including href)
     */
    protected string $disclaimer = '';

    /**
     * canvasTypes - Must be extended
     *
     * @acces protected
     */
    protected array $canvasTypes = [
        'goal' => ['icon' => 'fa-bullseye', 'title' => 'box.goal'],
    ];

    /**
     * statusLabels - Status labels (may be extended)
     *
     * @acces protected
     */
    protected array $statusLabels = [
        'status_ontrack' => ['icon' => 'fa-circle-check', 'color' => 'green',       'title' => 'status.goal.ontrack', 'dropdown' => 'success',    'active' => true],
        'status_atrisk' => ['icon' => 'fa-triangle-exclamation', 'color' => 'yellow',       'title' => 'status.goal.atrisk', 'dropdown' => 'warning',    'active' => true],
        'status_miss' => ['icon' => 'fa-circle-xmark', 'color' => 'red',       'title' => 'status.goal.miss', 'dropdown' => 'danger',    'active' => true],

    ];

    protected array $relatesLabels = [];

    /**
     * dataLabels - Data labels (may be extended)
     *
     * @acces protected
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.what_are_you_measuring', 'field' => 'assumptions',  'type' => 'string', 'active' => true],
        2 => ['title' => 'label.current_value', 'field' => 'data', 'type' => 'int', 'active' => true],
        3 => ['title' => 'label.goal_value', 'field' => 'conclusion', 'type' => 'int', 'active' => true],

    ];

    protected ConnectionInterface $dbConnection;

    /**
     * Language service used by the config-label accessors below.
     */
    protected LanguageCore $canvasLanguage;

    /**
     * Ticket repository — the base keeps its own private copy, so we retain
     * one here for goal-milestone progress reads.
     */
    protected Tickets $ticketRepository;

    /**
     * @param  DbCore  $db  Database connection
     * @param  LanguageCore  $language  Language service
     * @param  Tickets  $ticketRepo  Ticket repository
     * @param  DatabaseHelper  $dbHelper  Database helper
     */
    public function __construct(DbCore $db, LanguageCore $language, Tickets $ticketRepo, DatabaseHelper $dbHelper)
    {
        parent::__construct($db, $ticketRepo, $dbHelper);
        $this->dbConnection = $db->getConnection();
        $this->canvasLanguage = $language;
        $this->ticketRepository = $ticketRepo;
    }

    /**
     * getIcon() - Retrieve canvas icon
     *
     * @return string Canvas icon
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * getDisclaimer() - Retrieve disclaimer
     *
     * @return string Canvas disclaimer
     */
    public function getDisclaimer(): string
    {
        if (empty($this->disclaimer)) {
            return '';
        }

        return $this->canvasLanguage->__($this->disclaimer);
    }

    /**
     * getCanvasTypes() - Retrieve translated canvas items
     *
     * @return array Array of data
     */
    public function getCanvasTypes(): array
    {
        $canvasTypes = $this->canvasTypes;
        foreach ($canvasTypes as $key => $data) {
            if (isset($data['title'])) {
                $canvasTypes[$key]['title'] = $this->canvasLanguage->__($data['title']);
            }
        }

        return $canvasTypes;
    }

    /**
     * getStatusLabels() - Retrieve translated status labels
     *
     * @return array Array of data
     */
    public function getStatusLabels(): array
    {
        $statusLabels = $this->statusLabels;
        foreach ($statusLabels as $key => $data) {
            if (isset($data['title'])) {
                $statusLabels[$key]['title'] = $this->canvasLanguage->__($data['title']);
            }
        }

        return $statusLabels;
    }

    /**
     * getRelatesLabels() - Retrieve translated relates labels
     *
     * @return array Array of data
     */
    public function getRelatesLabels(): array
    {
        $relatesLabels = $this->relatesLabels;
        foreach ($relatesLabels as $key => $data) {
            if (isset($data['title'])) {
                $relatesLabels[$key]['title'] = $this->canvasLanguage->__($data['title']);
            }
        }

        return $relatesLabels;
    }

    /**
     * getDataLabels() - Retrieve translated data labels
     *
     * @return array Array of data
     */
    public function getDataLabels(): array
    {
        $dataLabels = $this->dataLabels;
        foreach ($dataLabels as $key => $data) {
            if (isset($data['title'])) {
                $dataLabels[$key]['title'] = $this->canvasLanguage->__($data['title']);
            }
        }

        return $dataLabels;
    }

    /**
     * getAllCanvas - thin override defaulting the canvas type to "goalcanvas".
     *
     * Keeps the parent's optional type parameter so the signature stays compatible.
     *
     * @param  int  $projectId  Project identifier
     * @param  string|null  $canvasType  Canvas type override (defaults to "goalcanvas")
     * @return false|array<int, array<string, mixed>>
     */
    public function getAllCanvas($projectId, $canvasType = null): false|array
    {
        $type = ($canvasType === null || $canvasType === '') ? static::CANVAS_NAME.'canvas' : $canvasType;

        return parent::getAllCanvas((int) $projectId, $type);
    }

    /**
     * addCanvas - thin override defaulting the canvas type to "goalcanvas".
     *
     * Keeps the parent's optional type parameter so the signature stays compatible.
     *
     * @param  array<string, mixed>  $values  Canvas values
     * @param  string|null  $canvasType  Canvas type override (defaults to "goalcanvas")
     */
    public function addCanvas($values, $canvasType = null): false|string
    {
        $type = ($canvasType === null || $canvasType === '') ? static::CANVAS_NAME.'canvas' : $canvasType;

        return parent::addCanvas($values, $type);
    }

    /**
     * getCanvasItemsById - thin override defaulting the comment module to "goalcanvasitem".
     *
     * Keeps the parent's optional module parameter so the signature stays compatible.
     *
     * @param  int  $id  Canvas board identifier
     * @param  string|null  $commentModule  Comment module override (defaults to "goalcanvasitem")
     * @return false|array<int, array<string, mixed>>
     */
    public function getCanvasItemsById($id, $commentModule = null): false|array
    {
        $module = ($commentModule === null || $commentModule === '') ? static::CANVAS_NAME.'canvasitem' : $commentModule;

        return parent::getCanvasItemsById((int) $id, $module);
    }

    /**
     * existCanvas - thin override defaulting the canvas type to "goalcanvas".
     *
     * Keeps the parent's optional type parameter so the signature stays compatible.
     *
     * @param  int  $projectId  Project identifier
     * @param  string  $canvasTitle  Canvas title
     * @param  string|null  $canvasType  Canvas type override (defaults to "goalcanvas")
     * @return bool True if a canvas with the given title exists
     */
    public function existCanvas(int $projectId, string $canvasTitle, $canvasType = null): bool
    {
        $type = ($canvasType === null || $canvasType === '') ? static::CANVAS_NAME.'canvas' : $canvasType;

        return parent::existCanvas($projectId, $canvasTitle, $type);
    }

    /**
     * copyCanvas - thin override defaulting the canvas type to "goalcanvas".
     *
     * Keeps the parent's optional type parameter so the signature stays compatible.
     *
     * @param  int  $projectId  Project identifier
     * @param  int  $canvasId  Source canvas identifier
     * @param  int  $authorId  Author identifier
     * @param  string  $canvasTitle  New canvas title
     * @param  string|null  $canvasType  Canvas type override (defaults to "goalcanvas")
     * @return int New canvas identifier
     */
    public function copyCanvas(int $projectId, int $canvasId, int $authorId, string $canvasTitle, $canvasType = null): int
    {
        $type = ($canvasType === null || $canvasType === '') ? static::CANVAS_NAME.'canvas' : $canvasType;

        return parent::copyCanvas($projectId, $canvasId, $authorId, $canvasTitle, $type);
    }

    /**
     * getNumberOfBoards - thin override defaulting the canvas type to "goalcanvas".
     *
     * Keeps the parent's optional type parameter so the signature stays compatible.
     *
     * @param  int|null  $projectId  Project identifier
     * @param  string|null  $canvasType  Canvas type override (defaults to "goalcanvas")
     */
    public function getNumberOfBoards($projectId = null, $canvasType = null): mixed
    {
        $type = ($canvasType === null || $canvasType === '') ? static::CANVAS_NAME.'canvas' : $canvasType;

        return parent::getNumberOfBoards($projectId !== null ? (int) $projectId : null, $type);
    }

    /**
     * getNumberOfCanvasItems - thin override defaulting the canvas type to "goalcanvas".
     *
     * Keeps the parent's optional type parameter so the signature stays compatible.
     *
     * @param  int|null  $projectId  Project identifier
     * @param  string|null  $canvasType  Canvas type override (defaults to "goalcanvas")
     */
    public function getNumberOfCanvasItems($projectId = null, $canvasType = null): mixed
    {
        $type = ($canvasType === null || $canvasType === '') ? static::CANVAS_NAME.'canvas' : $canvasType;

        return parent::getNumberOfCanvasItems($projectId !== null ? (int) $projectId : null, $type);
    }

    /**
     * Gets all goals related to a milestone
     */
    public function getGoalsByMilestone(int $milestoneId): false|array
    {
        // Reverse lookup via the tracked_by edge graph — replaces the legacy
        // `WHERE milestoneId = ?` column filter, and now returns a goal linked
        // to this milestone by ANY of its (possibly many) edges.
        $goalIds = $this->getGoalIdsForMilestone($milestoneId);
        if ($goalIds === []) {
            return [];
        }

        $results = $this->dbConnection->table('zp_canvas_items')
            ->select(
                'id',
                'description',
                'title',
                'assumptions',
                'data',
                'conclusion',
                'box',
                'author',
                'created',
                'modified',
                'canvasId',
                'sortindex',
                'status',
                'relates',
                'milestoneId',
                'kpi',
                'data1',
                'data2',
                'data3',
                'data4',
                'data5',
                'startDate',
                'endDate',
                'setting',
                'metricType',
                'startValue',
                'currentValue',
                'endValue',
                'impact',
                'effort',
                'probability',
                'action',
                'assignedTo',
                'parent',
                'tags'
            )
            ->where('box', 'goal')
            ->whereIn('id', $goalIds)
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    // ─── Goal↔milestone edges (tracked_by on zp_entity_relationship) ──────
    //
    // Many-to-many replacement for the legacy single milestoneId column.
    // Mirrors the collaborator relationship pattern (Tickets::addCollaborators
    // / getCollaborators / removeCollaborators). Direction convention:
    // entityA = goal (GoalItem), entityB = milestone (Ticket).

    /**
     * Link a milestone to a goal (idempotent — skips an existing edge).
     *
     * @api
     */
    public function addGoalMilestoneLink(int $goalId, int $milestoneId, int $userId): bool
    {
        if ($goalId <= 0 || $milestoneId <= 0) {
            return false;
        }

        $exists = $this->dbConnection->table('zp_entity_relationship')
            ->where('entityA', $goalId)
            ->where('entityAType', 'GoalItem')
            ->where('entityB', $milestoneId)
            ->where('entityBType', 'Ticket')
            ->where('relationship', EntityRelationshipEnum::TrackedBy->value)
            ->exists();

        if ($exists) {
            return true;
        }

        $this->dbConnection->table('zp_entity_relationship')->insert([
            'entityA' => $goalId,
            'entityAType' => 'GoalItem',
            'entityB' => $milestoneId,
            'entityBType' => 'Ticket',
            'relationship' => EntityRelationshipEnum::TrackedBy->value,
            'createdOn' => now(),
            'createdBy' => $userId,
        ]);

        return true;
    }

    /**
     * Remove a single goal↔milestone link.
     *
     * @api
     */
    public function removeGoalMilestoneLink(int $goalId, int $milestoneId): bool
    {
        return $this->dbConnection->table('zp_entity_relationship')
            ->where('entityA', $goalId)
            ->where('entityAType', 'GoalItem')
            ->where('entityB', $milestoneId)
            ->where('entityBType', 'Ticket')
            ->where('relationship', EntityRelationshipEnum::TrackedBy->value)
            ->delete() > 0;
    }

    /**
     * Remove every milestone link from a goal (goal delete / full reset).
     */
    public function removeAllGoalMilestoneLinks(int $goalId): bool
    {
        return $this->dbConnection->table('zp_entity_relationship')
            ->where('entityA', $goalId)
            ->where('entityAType', 'GoalItem')
            ->where('entityBType', 'Ticket')
            ->where('relationship', EntityRelationshipEnum::TrackedBy->value)
            ->delete() > 0;
    }

    /**
     * Remove every goal link pointing at a milestone — the milestone-deletion
     * cascade. Mirror of removeAllGoalMilestoneLinks, keyed on the milestone
     * (entityB) side.
     *
     * @api
     */
    public function removeMilestoneFromAllGoals(int $milestoneId): bool
    {
        return $this->dbConnection->table('zp_entity_relationship')
            ->where('entityAType', 'GoalItem')
            ->where('entityB', $milestoneId)
            ->where('entityBType', 'Ticket')
            ->where('relationship', EntityRelationshipEnum::TrackedBy->value)
            ->delete() > 0;
    }

    /**
     * Milestone ids this goal is tracked by.
     *
     * @return array<int, int>
     *
     * @api
     */
    public function getMilestoneIdsForGoal(int $goalId): array
    {
        return $this->dbConnection->table('zp_entity_relationship')
            ->where('entityA', $goalId)
            ->where('entityAType', 'GoalItem')
            ->where('entityBType', 'Ticket')
            ->where('relationship', EntityRelationshipEnum::TrackedBy->value)
            ->pluck('entityB')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Goal ids tracked_by the given milestone — the edge-based replacement for
     * the old `WHERE milestoneId = ?` reverse lookup.
     *
     * @return array<int, int>
     *
     * @api
     */
    public function getGoalIdsForMilestone(int $milestoneId): array
    {
        return $this->dbConnection->table('zp_entity_relationship')
            ->where('entityAType', 'GoalItem')
            ->where('entityB', $milestoneId)
            ->where('entityBType', 'Ticket')
            ->where('relationship', EntityRelationshipEnum::TrackedBy->value)
            ->pluck('entityA')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * The milestone chips for a set of goals — each goal's tracked_by
     * milestones with name, color, due date, and progress fill. Three queries
     * total (edges, milestone details, progress), no N+1. Edges pointing at a
     * deleted or non-milestone ticket are dropped.
     *
     * @param  array<int, int>  $goalIds
     * @return array<int, array<int, array{id: int, headline: string, color: string, editTo: mixed, percentDone: int}>>
     *
     * @api
     */
    public function getMilestonesForGoals(array $goalIds): array
    {
        $goalIds = array_values(array_unique(array_filter(array_map('intval', $goalIds))));
        if ($goalIds === []) {
            return [];
        }

        $edges = $this->dbConnection->table('zp_entity_relationship')
            ->whereIn('entityA', $goalIds)
            ->where('entityAType', 'GoalItem')
            ->where('entityBType', 'Ticket')
            ->where('relationship', EntityRelationshipEnum::TrackedBy->value)
            ->select('entityA', 'entityB')
            ->get();

        if ($edges->isEmpty()) {
            return [];
        }

        $goalToMilestones = [];
        foreach ($edges as $e) {
            $goalToMilestones[(int) $e->entityA][] = (int) $e->entityB;
        }
        $milestoneIds = array_values(array_unique(array_merge(...array_values($goalToMilestones))));

        $details = [];
        $projectIds = [];
        foreach (
            $this->dbConnection->table('zp_tickets')
                ->whereIn('id', $milestoneIds)
                ->where('type', 'milestone')
                ->select('id', 'headline', 'tags', 'editTo', 'status', 'projectId')
                ->get() as $m
        ) {
            $details[(int) $m->id] = [
                'id' => (int) $m->id,
                'headline' => (string) $m->headline,
                'color' => ($m->tags === null || $m->tags === '') ? 'var(--grey)' : (string) $m->tags,
                'editTo' => $m->editTo,
                'status' => (int) $m->status,
                'projectId' => (int) $m->projectId,
            ];
            $projectIds[(int) $m->projectId] = true;
        }

        // Resolve each milestone's statusType (NEW/INPROGRESS/DONE) from its
        // project's status labels — cached per project (usually just one).
        $statusTypeByProject = [];
        foreach (array_keys($projectIds) as $pid) {
            $map = [];
            foreach ($this->ticketRepository->getStateLabels($pid) as $sid => $label) {
                $map[(int) $sid] = (string) ($label['statusType'] ?? 'NEW');
            }
            $statusTypeByProject[$pid] = $map;
        }

        $progress = $this->getMilestoneProgressForIds(array_keys($details));

        // Chip order: in-progress -> not-started -> done, then due date asc.
        $rank = ['INPROGRESS' => 0, 'NEW' => 1, 'DONE' => 2];

        $result = [];
        foreach ($goalToMilestones as $goalId => $mids) {
            $chips = [];
            foreach ($mids as $mid) {
                if (! isset($details[$mid])) {
                    continue;
                }
                $d = $details[$mid];
                $chips[] = [
                    'id' => $d['id'],
                    'headline' => $d['headline'],
                    'color' => $d['color'],
                    'editTo' => $d['editTo'],
                    'status' => $d['status'],
                    'statusType' => $statusTypeByProject[$d['projectId']][$d['status']] ?? 'NEW',
                    'percentDone' => $progress[$mid] ?? 0,
                ];
            }

            usort($chips, function ($a, $b) use ($rank) {
                $ra = $rank[$a['statusType']] ?? 1;
                $rb = $rank[$b['statusType']] ?? 1;
                if ($ra !== $rb) {
                    return $ra <=> $rb;
                }
                $da = ($a['editTo'] === null || $a['editTo'] === '') ? '9999-12-31' : (string) $a['editTo'];
                $db = ($b['editTo'] === null || $b['editTo'] === '') ? '9999-12-31' : (string) $b['editTo'];

                return strcmp($da, $db);
            });

            if ($chips !== []) {
                $result[$goalId] = $chips;
            }
        }

        return $result;
    }

    /**
     * Per-milestone progress — the same storypoint-weighted "done" ratio the
     * single-milestone goal card used, batched across many milestone ids
     * (GROUP BY) so a goal shows N progress fills without N queries. Milestones
     * with no child tickets resolve to 0.
     *
     * @param  array<int, int>  $milestoneIds
     * @return array<int, int> milestoneId => percent (0-100)
     */
    public function getMilestoneProgressForIds(array $milestoneIds): array
    {
        $milestoneIds = array_values(array_unique(array_filter(array_map('intval', $milestoneIds))));
        if ($milestoneIds === []) {
            return [];
        }

        $statusGroups = $this->ticketRepository->getStatusListGroupedByType(session('currentProject'));
        $sp = $this->dbHelper->wrapColumn('storypoints');
        $st = $this->dbHelper->wrapColumn('status');
        $id = $this->dbHelper->wrapColumn('id');

        $rows = $this->dbConnection->table('zp_tickets')
            ->select('milestoneid')
            ->selectRaw('ROUND(
                CASE WHEN COUNT('.$id.') > 0 THEN (
                    SUM(CASE WHEN '.$st.' '.$statusGroups['DONE'].' THEN CASE WHEN '.$sp.' = 0 THEN 3 ELSE '.$sp.' END ELSE 0 END) /
                    SUM(CASE WHEN '.$sp.' = 0 THEN 3 ELSE '.$sp.' END)
                ) * 100 ELSE 0 END
            ) AS '.$this->dbHelper->wrapColumn('percentDone'))
            ->whereIn('milestoneid', $milestoneIds)
            ->where('type', '<>', 'milestone')
            ->groupBy('milestoneid')
            ->get();

        $progress = [];
        foreach ($rows as $r) {
            $progress[(int) $r->milestoneid] = (int) $r->percentDone;
        }
        foreach ($milestoneIds as $mid) {
            $progress[$mid] ??= 0;
        }

        return $progress;
    }

    /**
     * getSingleCanvas - returns a single goal canvas board row.
     *
     * Keeps the parent's optional type parameter so the signature stays compatible;
     * the goal query is always scoped to the goal canvas type.
     *
     * @param  int  $canvasId  Canvas board identifier
     * @param  string|null  $canvasType  Canvas type override (unused; query is goal-scoped)
     * @return false|array<string, mixed>
     */
    public function getSingleCanvas($canvasId, $canvasType = null): false|array
    {
        $result = $this->dbConnection->table('zp_canvas')
            ->select(
                'zp_canvas.id',
                'zp_canvas.title',
                'zp_canvas.author',
                'zp_canvas.description',
                'zp_canvas.created',
                'zp_canvas.projectId',
                't1.firstname AS authorFirstname',
                't1.lastname AS authorLastname'
            )
            ->leftJoin('zp_user AS t1', 'zp_canvas.author', '=', 't1.id')
            ->where('type', static::CANVAS_NAME.'canvas')
            ->where('zp_canvas.id', $canvasId)
            ->orderBy('zp_canvas.title')
            ->orderBy('zp_canvas.created')
            ->first();

        return $result ? (array) $result : false;
    }

    /**
     * Gets all goals related to a milestone
     */
    public function getAllAccountGoals(?int $projectId, ?int $boardId): false|array
    {
        $userId = session('userdata.id') ?? -1;
        $clientId = session('userdata.clientId') ?? -1;
        $requesterRole = session()->exists('userdata') ? session('userdata.role') : -1;

        $query = $this->dbConnection->table('zp_canvas_items')
            ->select(
                'zp_canvas_items.id',
                'zp_canvas_items.description',
                'zp_canvas_items.title',
                'zp_canvas_items.assumptions',
                'zp_canvas_items.data',
                'zp_canvas_items.conclusion',
                'zp_canvas_items.box',
                'zp_canvas_items.author',
                'zp_canvas_items.created',
                'zp_canvas_items.modified',
                'zp_canvas_items.canvasId',
                'zp_canvas_items.sortindex',
                'zp_canvas_items.status',
                'zp_canvas_items.relates',
                'zp_canvas_items.milestoneId',
                'zp_canvas_items.kpi',
                'zp_canvas_items.data1',
                'zp_canvas_items.data2',
                'zp_canvas_items.data3',
                'zp_canvas_items.data4',
                'zp_canvas_items.data5',
                'zp_canvas_items.startDate',
                'zp_canvas_items.endDate',
                'zp_canvas_items.setting',
                'zp_canvas_items.metricType',
                'zp_canvas_items.startValue',
                'zp_canvas_items.currentValue',
                'zp_canvas_items.endValue',
                'zp_canvas_items.impact',
                'zp_canvas_items.effort',
                'zp_canvas_items.probability',
                'zp_canvas_items.action',
                'zp_canvas_items.assignedTo',
                'zp_canvas_items.parent',
                'zp_canvas_items.tags',
                'zp_canvas.projectId'
            )
            ->leftJoin('zp_canvas', 'zp_canvas_items.canvasId', '=', 'zp_canvas.id')
            ->leftJoin('zp_projects', 'zp_canvas.projectId', '=', 'zp_projects.id')
            ->where('zp_canvas_items.box', 'goal')
            ->where(function ($q) use ($userId, $clientId, $requesterRole) {
                $q->whereIn('zp_canvas.projectId', function ($subquery) use ($userId) {
                    $subquery->select('projectId')
                        ->from('zp_relationuserproject')
                        ->where('userId', $userId);
                })
                    ->orWhere('zp_projects.psettings', 'all')
                    ->orWhere(function ($q2) use ($clientId) {
                        $q2->where('zp_projects.psettings', 'clients')
                            ->where('zp_projects.clientId', $clientId);
                    });
                // Admin and manager roles have access to all projects
                if (in_array($requesterRole, ['admin', 'manager'])) {
                    $q->orWhereRaw('1=1');
                }
            });

        if (isset($projectId) && $projectId > 0) {
            $query->where('zp_canvas.projectId', $projectId);
        }

        if (isset($boardId) && $boardId > 0) {
            $query->where('zp_canvas.id', $boardId);
        }

        $results = $query->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function createGoal(array $values): false|string
    {
        $id = $this->dbConnection->table('zp_canvas_items')->insertGetId([
            'description' => $values['description'] ?? '',
            'title' => $values['title'] ?? '',
            'assumptions' => $values['assumptions'] ?? '',
            'data' => $values['data'] ?? '',
            'conclusion' => $values['conclusion'] ?? '',
            'box' => $values['box'],
            'author' => $values['author'],
            'created' => now(),
            'modified' => now(),
            'canvasId' => $values['canvasId'],
            'status' => $values['status'] ?? '',
            'relates' => $values['relates'] ?? '',
            'milestoneId' => $values['milestoneId'] ?? '',
            'kpi' => $values['kpi'] ?? '',
            'data1' => $values['data1'] ?? '',
            'startDate' => $values['startDate'] ?? '',
            'endDate' => $values['endDate'] ?? '',
            'setting' => $values['setting'] ?? '',
            'metricType' => $values['metricType'] ?? '',
            'impact' => $values['impact'] ?? '',
            'effort' => $values['effort'] ?? '',
            'probability' => $values['probability'] ?? '',
            'action' => $values['action'] ?? '',
            'assignedTo' => $values['assignedTo'] ?? '',
            'startValue' => $values['startValue'] ?? '',
            'currentValue' => $values['currentValue'] ?? '',
            'endValue' => $values['endValue'] ?? '',
            'parent' => $values['parent'] ?? '',
            'tags' => $values['tags'] ?? '',
        ]);

        $this->recordGoalValueHistory((int) $id, $values['currentValue'] ?? null);

        return (string) $id;
    }

    /**
     * getCanvasItemsByKPI - goal-specific KPI hierarchy query (not part of Blueprints).
     *
     * @param  int  $id  KPI (parent) canvas item identifier
     * @return false|array<int, array<string, mixed>>
     */
    public function getCanvasItemsByKPI($id): false|array
    {
        $results = $this->connection->table('zp_canvas_items')
            ->select([
                'zp_canvas_items.id',
                'zp_canvas_items.title',
                'zp_canvas_items.kpi',
                'zp_canvas_items.startDate',
                'zp_canvas_items.endDate',
                'zp_canvas_items.setting',
                'zp_canvas_items.metricType',
                'zp_canvas_items.startValue',
                'zp_canvas_items.currentValue',
                'zp_canvas_items.endValue',
                'zp_canvas_items.canvasId',
                'zp_canvas.title as boardTitle',
                'zp_canvas.projectId as projectId',
                'zp_projects.name as projectName',
                'childrenLvl1.id as childId',
                'childrenLvl1.title as childTitle',
                'childrenLvl1.kpi as childKpi',
                'childrenLvl1.startDate as childStartDate',
                'childrenLvl1.endDate as childEndDate',
                'childrenLvl1.setting as childSetting',
                'childrenLvl1.metricType as childMetricType',
                'childrenLvl1.startValue as childStartValue',
                'childrenLvl1.currentValue as childCurrentValue',
                'childrenLvl1.endValue as childEndValue',
                'childrenLvl1.canvasId as childCanvasId',
                'childrenLvl1Board.title as childBoardTitle',
                'childrenLvl1Project.name as childProjectName',
            ])
            ->leftJoin('zp_canvas', 'zp_canvas_items.canvasId', '=', 'zp_canvas.id')
            ->leftJoin('zp_projects', 'zp_canvas.projectId', '=', 'zp_projects.id')
            ->leftJoin('zp_canvas_items as childrenLvl1', 'childrenLvl1.kpi', '=', 'zp_canvas_items.id')
            ->leftJoin('zp_canvas as childrenLvl1Board', 'childrenLvl1.canvasId', '=', 'childrenLvl1Board.id')
            ->leftJoin('zp_projects as childrenLvl1Project', 'childrenLvl1Board.projectId', '=', 'childrenLvl1Project.id')
            ->where('zp_canvas_items.box', 'goal')
            ->where('zp_canvas_items.kpi', $id)
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * getAllAvailableParents - goal-specific parent lookup (not part of Blueprints).
     *
     * @param  int  $projectId  Project identifier
     * @return false|array<int, array<string, mixed>>
     */
    public function getAllAvailableParents($projectId): false|array
    {
        $results = $this->connection->table('zp_canvas_items')
            ->select([
                'zp_canvas_items.id',
                'zp_canvas_items.description',
                'zp_canvas_items.title',
                'zp_canvas_items.assumptions',
                'zp_canvas_items.data',
                'zp_canvas_items.conclusion',
                'zp_canvas_items.box',
                'zp_canvas_items.author',
                'zp_canvas_items.created',
                'zp_canvas_items.modified',
                'zp_canvas_items.canvasId',
                'zp_canvas_items.sortindex',
                'zp_canvas_items.status',
                'zp_canvas_items.relates',
                'zp_canvas_items.milestoneId',
                'zp_canvas_items.kpi',
                'zp_canvas_items.data1',
                'zp_canvas_items.data2',
                'zp_canvas_items.data3',
                'zp_canvas_items.data4',
                'zp_canvas_items.data5',
                'zp_canvas_items.startDate',
                'zp_canvas_items.endDate',
                'zp_canvas_items.setting',
                'zp_canvas_items.metricType',
                'zp_canvas_items.startValue',
                'zp_canvas_items.currentValue',
                'zp_canvas_items.endValue',
                'zp_canvas_items.impact',
                'zp_canvas_items.effort',
                'zp_canvas_items.probability',
                'zp_canvas_items.action',
                'zp_canvas_items.assignedTo',
                'zp_canvas_items.parent',
                'zp_canvas_items.tags',
                'board.title as boardTitle',
                'parentKPI.description as parentKPIDescription',
                'parentGoal.description as parentGoalDescription',
                't1.firstname as authorFirstname',
                't1.lastname as authorLastname',
                'milestone.headline as milestoneHeadline',
                'milestone.editTo as milestoneEditTo',
            ])
            ->leftJoin('zp_canvas as board', 'board.id', '=', 'zp_canvas_items.canvasId')
            ->leftJoin('zp_canvas_items as parentKPI', 'zp_canvas_items.kpi', '=', 'parentKPI.id')
            ->leftJoin('zp_canvas_items as parentGoal', 'zp_canvas_items.parent', '=', 'parentGoal.id')
            ->leftJoin('zp_tickets as milestone', function ($join) {
                $join->on('zp_canvas_items.milestoneId', '=', $this->connection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('milestone.id'), 'text')));
            })
            ->leftJoin('zp_user as t1', 'zp_canvas_items.author', '=', 't1.id')
            ->where('board.projectId', $projectId)
            ->groupBy(['id', 'board.id', 'board.title', 'parentKPI.description', 'parentGoal.description', 't1.firstname', 't1.lastname', 'milestone.headline', 'milestone.editTo'])
            ->orderBy('board.id')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * getAllAvailableKPIs - goal-specific KPI lookup across parent projects (not part of Blueprints).
     *
     * @param  int  $projectId  Project identifier
     * @return false|array<int, array<string, mixed>>
     */
    public function getAllAvailableKPIs($projectId): false|array
    {
        // First, get parent project IDs (cross-database compatible approach)
        $parentProjectIds = [];

        $parentProject = $this->connection->table('zp_projects')
            ->select('parent')
            ->where('id', $projectId)
            ->first();

        if ($parentProject && $parentProject->parent) {
            $parentProjectIds[] = $parentProject->parent;

            // Check for grandparent
            $grandparent = $this->connection->table('zp_projects')
                ->select('parent')
                ->where('id', $parentProject->parent)
                ->first();

            if ($grandparent && $grandparent->parent) {
                $parentProjectIds[] = $grandparent->parent;
            }
        }

        // If no parent projects found, return empty array
        if (empty($parentProjectIds)) {
            return [];
        }

        // Now query canvas items from parent projects
        $results = $this->connection->table('zp_canvas_items')
            ->select([
                'zp_canvas_items.id',
                'zp_canvas_items.description',
                'project.name as projectName',
                'zp_canvas.title as boardTitle',
            ])
            ->leftJoin('zp_canvas', 'zp_canvas.id', '=', 'zp_canvas_items.canvasId')
            ->leftJoin('zp_projects as project', 'zp_canvas.projectId', '=', 'project.id')
            ->whereIn('zp_canvas.projectId', $parentProjectIds)
            ->where(function ($query) {
                $query->where('zp_canvas_items.setting', 'linkAndReport')
                    ->orWhere('zp_canvas_items.setting', 'linkonly');
            })
            ->orderBy('zp_canvas.id')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * {@inheritDoc}
     *
     * Goal boards additionally record the initial metric value so KPI trends start at creation.
     */
    public function addCanvasItem(array $values): false|string
    {
        $id = parent::addCanvasItem($values);

        if ($id !== false && ($values['box'] ?? '') === 'goal') {
            $this->recordGoalValueHistory((int) $id, $values['currentValue'] ?? null);
        }

        return $id;
    }

    /**
     * {@inheritDoc}
     *
     * Goal boards additionally record metric value changes to zp_goal_history.
     */
    public function editCanvasItem(array $values): void
    {
        $itemId = (int) ($values['itemId'] ?? $values['id'] ?? 0);
        $previousValue = $this->getCurrentGoalValue($itemId);

        parent::editCanvasItem($values);

        if (
            $previousValue !== false
            && array_key_exists('currentValue', $values)
            && is_numeric($values['currentValue'])
            && ($previousValue === null || (float) $values['currentValue'] !== $previousValue)
        ) {
            $this->recordGoalValueHistory($itemId, $values['currentValue']);
        }
    }

    /**
     * {@inheritDoc}
     *
     * Goal boards additionally record metric value changes to zp_goal_history.
     */
    public function patchCanvasItem(int $id, array $params): bool
    {
        $previousValue = array_key_exists('currentValue', $params) ? $this->getCurrentGoalValue($id) : false;

        $result = parent::patchCanvasItem($id, $params);

        if (
            $result
            && $previousValue !== false
            && is_numeric($params['currentValue'])
            && ($previousValue === null || (float) $params['currentValue'] !== $previousValue)
        ) {
            $this->recordGoalValueHistory($id, $params['currentValue']);
        }

        return $result;
    }

    /**
     * Daily safety net behind the write-path hooks: records a history row for every goal whose
     * current value differs from its latest recorded value (covers writes that bypassed this
     * repository). Returns the number of rows written.
     */
    public function snapshotGoalValues(): int
    {
        $goals = $this->dbConnection->table('zp_canvas_items')
            ->select(['zp_canvas_items.id', 'zp_canvas_items.currentValue'])
            ->join('zp_canvas', 'zp_canvas_items.canvasId', '=', 'zp_canvas.id')
            ->where('zp_canvas.type', '=', static::CANVAS_NAME.'canvas')
            ->where('zp_canvas_items.box', '=', 'goal')
            ->get();

        $latestValues = [];
        $latestDates = $this->dbConnection->table('zp_goal_history')
            ->selectRaw('itemId, MAX(dateRecorded) as maxDate')
            ->groupBy('itemId');
        $latestRows = $this->dbConnection->table('zp_goal_history')
            ->select(['zp_goal_history.itemId', 'zp_goal_history.value'])
            ->joinSub($latestDates, 'latest', function ($join) {
                $join->on('zp_goal_history.itemId', '=', 'latest.itemId')
                    ->on('zp_goal_history.dateRecorded', '=', 'latest.maxDate');
            })
            ->get();
        foreach ($latestRows as $row) {
            $latestValues[(int) $row->itemId] = (float) $row->value;
        }

        $inserts = [];
        // History reads normalize to UTC — write explicit UTC, not the app timezone.
        $now = dtHelper()->dbNow()->formatDateTimeForDb();
        foreach ($goals as $goal) {
            // Goals without a numeric value have no trend point to record — casting NULL
            // to 0.0 would fabricate history (mirrors recordGoalValueHistory's guard).
            if ($goal->currentValue === null || $goal->currentValue === '' || ! is_numeric($goal->currentValue)) {
                continue;
            }

            $currentValue = (float) $goal->currentValue;
            if (! array_key_exists((int) $goal->id, $latestValues) || $latestValues[(int) $goal->id] !== $currentValue) {
                $inserts[] = [
                    'itemId' => (int) $goal->id,
                    'value' => $currentValue,
                    'userId' => null,
                    'dateRecorded' => $now,
                ];
            }
        }

        if ($inserts !== []) {
            $this->dbConnection->table('zp_goal_history')->insert($inserts);
        }

        return count($inserts);
    }

    /**
     * The recorded value of a goal at a point in time — the last snapshot on
     * or before {@see $asOf}. Returns null when the goal has no history at or
     * before that date.
     *
     * Periods are a query, never stored: a period's value is whatever the
     * last snapshot on or before its end date says. Missing days don't matter
     * because the last known value carries — the write path is intentionally
     * sparse (only fires when the value changes) and this read is what makes
     * that sparseness invisible to callers.
     *
     * `$asOf` is normalized to UTC before comparison because `zp_goal_history`
     * rows are always written in UTC (per the CLAUDE.md db-time convention);
     * callers can pass a user-tz DateTime without silently drifting by hours.
     */
    public function getGoalValueAt(int $itemId, \DateTimeInterface $asOf): ?float
    {
        if ($itemId <= 0) {
            return null;
        }

        $asOfUtc = (new \DateTimeImmutable('@'.$asOf->getTimestamp()))
            ->setTimezone(new \DateTimeZone('UTC'));

        $row = $this->dbConnection->table('zp_goal_history')
            ->select('value')
            ->where('itemId', $itemId)
            ->where('dateRecorded', '<=', $asOfUtc->format('Y-m-d H:i:s'))
            ->orderByDesc('dateRecorded')
            ->orderByDesc('id')
            ->first();

        return $row === null ? null : (float) $row->value;
    }

    /**
     * The recorded values of a set of goals at a series of dates. Returned as
     * `[goalId => [YYYY-MM-DD_HH:MM:SS => ?float]]`, one entry per requested
     * `(goalId, date)` pair. Null carries "no snapshot on or before that
     * date" through unchanged — callers decide how to render an unmeasured
     * period-end (Page 4 "How far we've come" draws it as a ghost bar).
     *
     * One SELECT per goal (not per goal×date). We fetch every snapshot up to
     * the latest requested date once and bucket in PHP — a 20-goal × 12-date
     * arc goes from 240 round-trips to 20. The date keys in the returned
     * array are formatted in UTC to match the read semantic.
     *
     * @param  int[]  $itemIds  Goal (canvas item) ids.
     * @param  array<\DateTimeInterface>  $dates  Period-end (or arbitrary) dates.
     * @return array<int, array<string, ?float>>
     */
    public function getGoalValuesAtSeries(array $itemIds, array $dates): array
    {
        $itemIds = array_values(array_unique(array_map('intval', $itemIds)));
        $itemIds = array_values(array_filter($itemIds, static fn (int $id): bool => $id > 0));
        if ($itemIds === [] || $dates === []) {
            return [];
        }

        // Normalize dates to UTC and sort ascending — we walk snapshots
        // forward per goal and advance the requested-date cursor in lockstep.
        $utcTz = new \DateTimeZone('UTC');
        $normalizedDates = array_map(
            static fn (\DateTimeInterface $d) => (new \DateTimeImmutable('@'.$d->getTimestamp()))->setTimezone($utcTz),
            $dates
        );
        usort($normalizedDates, static fn ($a, $b) => $a <=> $b);
        $dateKeys = array_map(static fn ($d) => $d->format('Y-m-d H:i:s'), $normalizedDates);
        $latestDate = end($normalizedDates)->format('Y-m-d H:i:s');

        // Prime output with nulls in the original (sorted) key order.
        $out = [];
        foreach ($itemIds as $id) {
            $out[$id] = array_fill_keys($dateKeys, null);
        }

        // One SELECT per goal, capped at the latest requested date. Rows come
        // back date-ASC so we can carry-forward the last value through gap
        // dates in a single pass.
        foreach ($itemIds as $id) {
            $rows = $this->dbConnection->table('zp_goal_history')
                ->select(['value', 'dateRecorded', 'id'])
                ->where('itemId', $id)
                ->where('dateRecorded', '<=', $latestDate)
                ->orderBy('dateRecorded')
                ->orderBy('id')
                ->get();

            $cursor = 0;
            $lastValue = null;
            foreach ($rows as $row) {
                $rowDate = (string) $row->dateRecorded;
                // Advance any date-keys we've now passed, stamping the value
                // that was current on-or-before each one.
                while ($cursor < count($dateKeys) && $rowDate > $dateKeys[$cursor]) {
                    $out[$id][$dateKeys[$cursor]] = $lastValue;
                    $cursor++;
                }
                $lastValue = (float) $row->value;
            }
            // Any remaining date-keys are at or after every snapshot — stamp
            // the last-known value for each.
            while ($cursor < count($dateKeys)) {
                $out[$id][$dateKeys[$cursor]] = $lastValue;
                $cursor++;
            }
        }

        return $out;
    }

    /**
     * Bulk-insert history rows from an already-parsed set of tuples. Used by
     * the CSV backfill command; rows are indistinguishable from ones written
     * by {@see recordGoalValueHistory} or {@see snapshotGoalValues} — same
     * table, same columns.
     *
     * NOT `@api`. This is a mass-write repository method with no permission
     * check by design — callers (the CSV backfill CLI command) are trusted
     * shell-level admin actions. Do NOT expose over JSON-RPC or any user-
     * reachable surface without a service wrapper that enforces authz.
     *
     * Chunks to 500 rows per INSERT to stay under `max_allowed_packet` on
     * default MySQL configs, and wraps the whole run in a transaction so a
     * mid-import failure rolls back cleanly.
     *
     * @param  array<int, array{itemId: int, value: float, dateRecorded: string, userId?: ?int}>  $rows
     * @return int Rows written.
     */
    public function insertGoalHistoryRows(array $rows): int
    {
        if ($rows === []) {
            return 0;
        }

        $written = 0;
        $this->dbConnection->transaction(function () use ($rows, &$written): void {
            foreach (array_chunk($rows, 500) as $chunk) {
                $this->dbConnection->table('zp_goal_history')->insert($chunk);
                $written += count($chunk);
            }
        });

        return $written;
    }

    /**
     * Given a set of `zp_canvas_items.id` values, return the subset that are
     * actually goals (i.e. `box='goal'` on a goal-type canvas). Used by the
     * CSV backfill command to reject rows targeting non-goal item ids before
     * writing to `zp_goal_history` — an integrity check on write, since the
     * history table has no foreign key to enforce this.
     *
     * @param  int[]  $itemIds
     * @return int[] Sorted, de-duplicated valid goal item ids.
     */
    public function filterGoalItemIds(array $itemIds): array
    {
        $itemIds = array_values(array_unique(array_map('intval', $itemIds)));
        $itemIds = array_values(array_filter($itemIds, static fn (int $id): bool => $id > 0));
        if ($itemIds === []) {
            return [];
        }

        $rows = $this->dbConnection->table('zp_canvas_items')
            ->select('zp_canvas_items.id')
            ->join('zp_canvas', 'zp_canvas_items.canvasId', '=', 'zp_canvas.id')
            ->where('zp_canvas.type', '=', static::CANVAS_NAME.'canvas')
            ->where('zp_canvas_items.box', '=', 'goal')
            ->whereIn('zp_canvas_items.id', $itemIds)
            ->get();

        $valid = array_map(static fn ($r) => (int) $r->id, $rows->all());
        sort($valid);

        return $valid;
    }

    /**
     * The stored metric value of a goal item, three-state: false when the item isn't a
     * goal (or is unknown) — record nothing; null when the goal has no numeric value yet —
     * a first value (including an explicit 0) must record; otherwise the float value.
     */
    private function getCurrentGoalValue(int $itemId): float|null|false
    {
        if ($itemId === 0) {
            return false;
        }

        $item = $this->dbConnection->table('zp_canvas_items')
            ->select(['currentValue', 'box'])
            ->where('id', $itemId)
            ->first();

        if ($item === null || $item->box !== 'goal') {
            return false;
        }

        if ($item->currentValue === null || $item->currentValue === '' || ! is_numeric($item->currentValue)) {
            return null;
        }

        return (float) $item->currentValue;
    }

    /**
     * Appends a zp_goal_history row so metric changes stay chartable over time.
     */
    private function recordGoalValueHistory(int $itemId, mixed $value): void
    {
        if ($itemId === 0 || $value === null || $value === '' || ! is_numeric($value)) {
            return;
        }

        $this->dbConnection->table('zp_goal_history')->insert([
            'itemId' => $itemId,
            'value' => (float) $value,
            'userId' => session()->exists('userdata') ? (int) session('userdata.id') : null,
            // History reads normalize to UTC — write explicit UTC, not the app timezone.
            'dateRecorded' => dtHelper()->dbNow()->formatDateTimeForDb(),
        ]);
    }
}
