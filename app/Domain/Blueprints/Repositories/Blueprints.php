<?php

namespace Leantime\Domain\Blueprints\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Db\Repository;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Tickets\Repositories\Tickets;

/**
 * @api
 */
class Blueprints extends Repository
{
    /**
     * Columns on zp_canvas_items that may be written via patchCanvasItem().
     * Acts as a mass-assignment allowlist for the inline-update API.
     */
    private const PATCHABLE_COLUMNS = [
        'title', 'description', 'assumptions', 'data', 'conclusion',
        'box', 'status', 'relates', 'milestoneId', 'kpi', 'data1',
        'startDate', 'endDate', 'setting', 'metricType', 'startValue',
        'currentValue', 'endValue', 'impact', 'effort', 'probability',
        'action', 'assignedTo', 'parent', 'tags', 'sortindex',
    ];

    protected ConnectionInterface $connection;

    protected DatabaseHelper $dbHelper;

    private LanguageCore $language;

    private Tickets $ticketRepo;

    /**
     * @param  DbCore  $db  Database connection
     * @param  LanguageCore  $language  Language service
     * @param  Tickets  $ticketRepo  Ticket repository
     * @param  DatabaseHelper  $dbHelper  Database helper
     */
    public function __construct(
        DbCore $db,
        LanguageCore $language,
        Tickets $ticketRepo,
        DatabaseHelper $dbHelper
    ) {
        $this->connection = $db->getConnection();
        $this->language = $language;
        $this->ticketRepo = $ticketRepo;
        $this->dbHelper = $dbHelper;
    }

    /**
     * @param  int  $projectId  Project ID
     * @param  string  $canvasType  Database type (e.g., "swotcanvas")
     * @return false|array<int, array<string, mixed>>
     */
    public function getAllCanvas(int $projectId, string $canvasType): false|array
    {
        $results = $this->connection->table('zp_canvas')
            ->select([
                'zp_canvas.id',
                'zp_canvas.title',
                'zp_canvas.author',
                'zp_canvas.created',
                'zp_canvas.description',
                't1.firstname as authorFirstname',
                't1.lastname as authorLastname',
            ])
            ->selectRaw('COUNT(zp_canvas_items.id) AS '.$this->dbHelper->wrapColumn('boxItems'))
            ->leftJoin('zp_user as t1', 'zp_canvas.author', '=', 't1.id')
            ->leftJoin('zp_canvas_items', 'zp_canvas.id', '=', 'zp_canvas_items.canvasId')
            ->where('type', $canvasType)
            ->where('projectId', $projectId)
            ->groupBy(['zp_canvas.id', 'zp_canvas.title', 'zp_canvas.created', 'zp_canvas.author', 'zp_canvas.description', 't1.firstname', 't1.lastname'])
            ->orderBy('zp_canvas.title')
            ->orderBy('zp_canvas.created')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * @param  int  $canvasId  Canvas board ID
     * @param  string  $canvasType  Database type (e.g., "swotcanvas")
     * @return false|array<int, array<string, mixed>>
     */
    public function getSingleCanvas(int $canvasId, string $canvasType): false|array
    {
        $results = $this->connection->table('zp_canvas')
            ->select([
                'zp_canvas.id',
                'zp_canvas.title',
                'zp_canvas.author',
                'zp_canvas.created',
                'zp_canvas.projectId',
                't1.firstname as authorFirstname',
                't1.lastname as authorLastname',
            ])
            ->leftJoin('zp_user as t1', 'zp_canvas.author', '=', 't1.id')
            ->where('type', $canvasType)
            ->where('zp_canvas.id', $canvasId)
            ->orderBy('zp_canvas.title')
            ->orderBy('zp_canvas.created')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * Resolve the project id a canvas ITEM ultimately belongs to (item → board → project),
     * optionally constrained to a canvas $canvasType. Returns null when the item does not exist
     * OR its board is of a different type.
     *
     * This is a fail-CLOSED primitive: the service layer uses it to authorize by-id item
     * operations against the item's REAL project, never the caller's session project. A
     * `null` return must be treated as "deny" — never as "fall back to currentProject".
     *
     * @param  int  $itemId  Canvas item id
     * @param  string|null  $canvasType  Constrain to this board type (e.g. "swotcanvas"); null = any type
     */
    public function getCanvasItemProjectId(int $itemId, ?string $canvasType = null): ?int
    {
        $query = $this->connection->table('zp_canvas_items')
            ->leftJoin('zp_canvas', 'zp_canvas.id', '=', 'zp_canvas_items.canvasId')
            ->where('zp_canvas_items.id', $itemId);

        if ($canvasType !== null) {
            $query->where('zp_canvas.type', $canvasType);
        }

        $projectId = $query->value('zp_canvas.projectId');

        return $projectId !== null ? (int) $projectId : null;
    }

    /**
     * Resolve the project id a canvas BOARD belongs to, optionally constrained to a canvas
     * $canvasType. Returns null when the board does not exist OR is of a different type.
     *
     * Fail-CLOSED companion to {@see getCanvasItemProjectId()} for by-id board operations.
     *
     * @param  int  $canvasId  Canvas board id
     * @param  string|null  $canvasType  Constrain to this board type; null = any type
     */
    public function getCanvasProjectId(int $canvasId, ?string $canvasType = null): ?int
    {
        $query = $this->connection->table('zp_canvas')
            ->where('id', $canvasId);

        if ($canvasType !== null) {
            $query->where('type', $canvasType);
        }

        $projectId = $query->value('projectId');

        return $projectId !== null ? (int) $projectId : null;
    }

    /**
     * @param  int  $id  Canvas board ID
     */
    public function deleteCanvas(int $id): void
    {
        $this->connection->table('zp_canvas_items')
            ->where('canvasId', $id)
            ->delete();

        $this->connection->table('zp_canvas')
            ->where('id', $id)
            ->delete();
    }

    /**
     * @param  array<string, mixed>  $values  Canvas values
     * @param  string  $canvasType  Database type (e.g., "swotcanvas")
     */
    public function addCanvas(array $values, string $canvasType): false|string
    {
        $insertId = $this->connection->table('zp_canvas')->insertGetId([
            'title' => $values['title'],
            'description' => $values['description'] ?? '',
            'author' => $values['author'],
            'created' => now(),
            'type' => $canvasType,
            'projectId' => $values['projectId'],
        ]);

        return $insertId !== false ? (string) $insertId : false;
    }

    /**
     * @param  array<string, mixed>  $values  Canvas values
     */
    public function updateCanvas(array $values): mixed
    {
        return $this->connection->table('zp_canvas')
            ->where('id', $values['id'])
            ->update([
                'title' => $values['title'],
                'description' => $values['description'] ?? '',
            ]);
    }

    /**
     * @param  array<string, mixed>  $values  Item values
     */
    public function editCanvasItem(array $values): void
    {
        $this->connection->table('zp_canvas_items')
            ->where('id', $values['itemId'] ?? $values['id'])
            ->update([
                'title' => $values['title'] ?? '',
                'description' => $values['description'],
                'assumptions' => $values['assumptions'] ?? '',
                'data' => $values['data'] ?? '',
                'conclusion' => $values['conclusion'] ?? '',
                'modified' => now(),
                'status' => $values['status'] ?? '',
                'relates' => $values['relates'] ?? '',
                'milestoneId' => $values['milestoneId'] ?? '',
                'kpi' => $values['kpi'] ?? '',
                'data1' => $values['data1'] ?? '',
                'startDate' => $values['startDate'] ?? '',
                'endDate' => $values['endDate'] ?? '',
                'setting' => $values['setting'] ?? '',
                'metricType' => $values['metricType'] ?? '',
                'startValue' => $values['startValue'] ?? '',
                'currentValue' => $values['currentValue'] ?? '',
                'endValue' => $values['endValue'] ?? '',
                'impact' => $values['impact'] ?? '',
                'effort' => $values['effort'] ?? '',
                'probability' => $values['probability'] ?? '',
                'action' => $values['action'] ?? '',
                'assignedTo' => $values['assignedTo'] ?? '',
                'parent' => $values['parent'] ?? '',
                'tags' => $values['tags'] ?? '',
            ]);
    }

    /**
     * @param  int  $id  Item ID
     * @param  array<string, mixed>  $params  Fields to patch
     */
    public function patchCanvasItem(int $id, array $params): bool
    {
        $updates = [];
        foreach ($params as $key => $value) {
            if (in_array($key, self::PATCHABLE_COLUMNS, true)) {
                $updates[$key] = $value;
            }
        }

        if (empty($updates)) {
            return false;
        }

        return (bool) $this->connection->table('zp_canvas_items')
            ->where('id', $id)
            ->update($updates);
    }

    /**
     * @param  int  $id  Canvas board ID
     * @param  string  $commentModule  Comment module name (e.g., "swotcanvasitem")
     * @return false|array<int, array<string, mixed>>
     */
    public function getCanvasItemsById(int $id, string $commentModule): false|array
    {
        $statusGroups = $this->ticketRepo->getStatusListGroupedByType(session('currentProject'));

        $results = $this->connection->table('zp_canvas_items')
            ->select([
                'zp_canvas_items.id',
                'zp_canvas_items.description',
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
                'zp_canvas_items.parent',
                'zp_canvas_items.title',
                'zp_canvas_items.tags',
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
                't1.firstname as authorFirstname',
                't1.lastname as authorLastname',
                't1.profileId as authorProfileId',
                'milestone.headline as milestoneHeadline',
                'milestone.editTo as milestoneEditTo',
            ])
            ->selectRaw('COUNT(DISTINCT zp_comment.id) AS '.$this->dbHelper->wrapColumn('commentCount'))
            ->selectRaw('0 AS '.$this->dbHelper->wrapColumn('percentDone'))
            ->leftJoin('zp_user as t1', 'zp_canvas_items.author', '=', 't1.id')
            ->leftJoin('zp_tickets as milestone', function ($join) {
                $join->on('zp_canvas_items.milestoneId', '=', $this->connection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('milestone.id'), 'text')));
            })
            ->leftJoin('zp_comment', function ($join) use ($commentModule) {
                $join->on('zp_canvas_items.id', '=', 'zp_comment.moduleId')
                    ->where('zp_comment.module', '=', $commentModule);
            })
            ->where('zp_canvas_items.canvasId', $id)
            ->groupBy(['zp_canvas_items.id', 'zp_canvas_items.box', 'zp_canvas_items.sortindex', 't1.firstname', 't1.lastname', 't1.profileId', 'milestone.headline', 'milestone.editTo'])
            ->orderBy('zp_canvas_items.box')
            ->orderBy('zp_canvas_items.sortindex')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * @param  int  $id  Canvas item ID
     */
    public function getSingleCanvasItem(int $id): mixed
    {
        $statusGroups = $this->ticketRepo->getStatusListGroupedByType(session('currentProject'));

        $result = $this->connection->table('zp_canvas_items')
            ->select([
                'zp_canvas_items.id',
                'zp_canvas_items.title',
                'zp_canvas_items.description',
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
                'parentGoal.title as parentGoalDescription',
                't1.firstname as authorFirstname',
                't1.lastname as authorLastname',
                'milestone.headline as milestoneHeadline',
                'milestone.editTo as milestoneEditTo',
            ])
            ->selectRaw('COUNT('.$this->dbHelper->wrapColumn('progressTickets.id').') AS '.$this->dbHelper->wrapColumn('allTickets'))
            ->selectSub(function ($query) use ($statusGroups) {
                $progressSubId = $this->dbHelper->wrapColumn('progressSub.id');
                $progressSubStatus = $this->dbHelper->wrapColumn('progressSub.status');
                $progressSubStorypoints = $this->dbHelper->wrapColumn('progressSub.storypoints');
                $query->from('zp_tickets as progressSub')
                    ->selectRaw('(
                        CASE WHEN
                          COUNT(DISTINCT '.$progressSubId.') > 0
                        THEN
                          ROUND(
                            (
                              SUM(CASE WHEN '.$progressSubStatus.' '.$statusGroups['DONE'].' THEN CASE WHEN '.$progressSubStorypoints.' = 0 THEN 3 ELSE '.$progressSubStorypoints.' END ELSE 0 END) /
                              SUM(CASE WHEN '.$progressSubStorypoints.' = 0 THEN 3 ELSE '.$progressSubStorypoints.' END)
                            ) *100)
                        ELSE
                          0
                        END)
                    ')
                    ->whereColumn(
                        $this->connection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('progressSub.milestoneid'), 'text')),
                        '=',
                        'zp_canvas_items.milestoneId'
                    )
                    ->where('progressSub.type', '<>', 'milestone');
            }, 'percentDone')
            ->leftJoin('zp_canvas_items as parentKPI', 'zp_canvas_items.kpi', '=', 'parentKPI.id')
            ->leftJoin('zp_canvas as board', 'board.id', '=', 'zp_canvas_items.canvasId')
            ->leftJoin('zp_canvas_items as parentGoal', 'zp_canvas_items.parent', '=', 'parentGoal.id')
            ->leftJoin('zp_tickets as progressTickets', function ($join) {
                $join->on(
                    $this->connection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('progressTickets.milestoneid'), 'text')),
                    '=',
                    'zp_canvas_items.milestoneId'
                )
                    ->where('progressTickets.type', '<>', 'milestone')
                    ->where('progressTickets.type', '<>', 'subtask');
            })
            ->leftJoin('zp_tickets as milestone', function ($join) {
                $join->on('zp_canvas_items.milestoneId', '=', $this->connection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('milestone.id'), 'text')));
            })
            ->leftJoin('zp_user as t1', 'zp_canvas_items.author', '=', 't1.id')
            ->where('zp_canvas_items.id', $id)
            ->groupBy([
                'zp_canvas_items.id',
                'board.title',
                'parentKPI.description',
                'parentGoal.title',
                't1.firstname',
                't1.lastname',
                'milestone.headline',
                'milestone.editTo',
            ])
            ->first();

        if ($result !== null && $result->id != null) {
            return (array) $result;
        } else {
            return false;
        }
    }

    /**
     * @param  array<string, mixed>  $values  Item values
     */
    public function addCanvasItem(array $values): false|string
    {
        $id = $this->connection->table('zp_canvas_items')->insertGetId([
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

        return $id !== false ? (string) $id : false;
    }

    /**
     * @param  int  $id  Item ID
     */
    public function delCanvasItem(int $id): void
    {
        $this->connection->table('zp_canvas_items')
            ->where('id', $id)
            ->delete();
    }

    /**
     * @param  int|null  $projectId  Project ID
     * @param  string  $canvasType  Database type (e.g., "swotcanvas")
     */
    public function getNumberOfCanvasItems(?int $projectId, string $canvasType): mixed
    {
        $query = $this->connection->table('zp_canvas_items')
            ->selectRaw('COUNT(zp_canvas_items.id) AS '.$this->dbHelper->wrapColumn('canvasCount'))
            ->leftJoin('zp_canvas as canvasBoard', 'zp_canvas_items.canvasId', '=', 'canvasBoard.id')
            ->where('canvasBoard.type', $canvasType);

        if (! is_null($projectId)) {
            $query->where('canvasBoard.projectId', $projectId);
        }

        $result = $query->first();

        return $result->canvasCount ?? 0;
    }

    /**
     * @param  int|null  $projectId  Project ID
     * @param  string  $canvasType  Database type (e.g., "swotcanvas")
     */
    public function getNumberOfBoards(?int $projectId, string $canvasType): mixed
    {
        $query = $this->connection->table('zp_canvas')
            ->selectRaw('COUNT(zp_canvas.id) AS '.$this->dbHelper->wrapColumn('boardCount'))
            ->where('zp_canvas.type', $canvasType);

        if (! is_null($projectId)) {
            $query->where('zp_canvas.projectId', $projectId);
        }

        $result = $query->first();

        return $result->boardCount ?? 0;
    }

    /**
     * @param  int  $projectId  Project ID
     * @param  string  $canvasTitle  Canvas title
     * @param  string  $canvasType  Database type (e.g., "swotcanvas")
     */
    public function existCanvas(int $projectId, string $canvasTitle, string $canvasType): bool
    {
        $result = $this->connection->table('zp_canvas')
            ->selectRaw('COUNT(id) as '.$this->dbHelper->wrapColumn('nbCanvas'))
            ->where('projectId', $projectId)
            ->where('title', $canvasTitle)
            ->where('type', $canvasType)
            ->first();

        return isset($result->nbCanvas) && $result->nbCanvas > 0;
    }

    /**
     * @param  int  $projectId  Project ID
     * @param  int  $canvasId  Source canvas ID
     * @param  int  $authorId  Author ID
     * @param  string  $canvasTitle  New canvas title
     * @param  string  $canvasType  Database type (e.g., "swotcanvas")
     * @return int New canvas ID
     */
    public function copyCanvas(int $projectId, int $canvasId, int $authorId, string $canvasTitle, string $canvasType): int
    {
        $values = ['title' => $canvasTitle, 'author' => $authorId, 'projectId' => $projectId];
        $newCanvasId = $this->addCanvas($values, $canvasType);

        $columns = [
            'title', 'description', 'assumptions', 'data', 'conclusion', 'box', 'author',
            'created', 'modified', 'canvasId', 'status', 'relates', 'milestoneId', 'kpi',
            'data1', 'startDate', 'endDate', 'setting', 'metricType', 'impact', 'effort',
            'probability', 'action', 'assignedTo', 'startValue', 'currentValue', 'endValue',
        ];

        $selectQuery = $this->connection->table('zp_canvas_items')
            ->select([
                'title', 'description', 'assumptions', 'data', 'conclusion', 'box', 'author',
            ])
            ->selectRaw($this->dbHelper->currentTimestamp().' as created')
            ->selectRaw($this->dbHelper->currentTimestamp().' as modified')
            ->selectRaw('? as '.$this->dbHelper->wrapColumn('canvasId'), [$newCanvasId])
            ->select(['status', 'relates'])
            ->selectRaw("'' as ".$this->dbHelper->wrapColumn('milestoneId'))
            ->select([
                'kpi', 'data1', 'startDate', 'endDate', 'setting', 'metricType', 'impact',
                'effort', 'probability', 'action', 'assignedTo', 'startValue', 'currentValue', 'endValue',
            ])
            ->where('canvasId', $canvasId);

        $this->connection->table('zp_canvas_items')->insertUsing($columns, $selectQuery);

        return $newCanvasId;
    }

    /**
     * @param  int  $canvasId  Target canvas ID
     * @param  string  $mergeId  Source canvas ID
     */
    public function mergeCanvas(int $canvasId, string $mergeId): bool
    {
        $columns = [
            'title', 'description', 'assumptions', 'data', 'conclusion', 'box', 'author',
            'created', 'modified', 'canvasId', 'status', 'relates', 'milestoneId', 'kpi',
            'data1', 'startDate', 'endDate', 'setting', 'metricType', 'impact', 'effort',
            'probability', 'action', 'assignedTo', 'startValue', 'currentValue', 'endValue',
        ];

        $selectQuery = $this->connection->table('zp_canvas_items')
            ->select([
                'title', 'description', 'assumptions', 'data', 'conclusion', 'box', 'author',
            ])
            ->selectRaw($this->dbHelper->currentTimestamp().' as created')
            ->selectRaw($this->dbHelper->currentTimestamp().' as modified')
            ->selectRaw('? as '.$this->dbHelper->wrapColumn('canvasId'), [$canvasId])
            ->select(['status', 'relates'])
            ->selectRaw("'' as ".$this->dbHelper->wrapColumn('milestoneId'))
            ->select([
                'kpi', 'data1', 'startDate', 'endDate', 'setting', 'metricType', 'impact',
                'effort', 'probability', 'action', 'assignedTo', 'startValue', 'currentValue', 'endValue',
            ])
            ->where('canvasId', $mergeId);

        $this->connection->table('zp_canvas_items')->insertUsing($columns, $selectQuery);

        return true;
    }

    /**
     * @param  int  $projectId  Project ID
     * @param  array<int, string>  $boards  Board types to query
     * @return array<int, array<string, mixed>>|bool
     */
    public function getCanvasProgressCount(int $projectId, array $boards): array|bool
    {
        $query = $this->connection->table('zp_canvas')
            ->select([
                'zp_canvas.id as canvasId',
                'zp_canvas.type as canvasType',
                'zp_canvas_items.box',
            ])
            ->selectRaw('COUNT(zp_canvas_items.id) AS '.$this->dbHelper->wrapColumn('boxItems'))
            ->leftJoin('zp_canvas_items', 'zp_canvas.id', '=', 'zp_canvas_items.canvasId');

        if ($projectId != '') {
            $query->where('projectId', $projectId);
        }

        if (count($boards) > 0) {
            $query->whereIn('type', $boards);
        }

        $results = $query->groupBy(['zp_canvas.id', 'zp_canvas.type', 'zp_canvas_items.box'])
            ->orderBy('zp_canvas.title')
            ->orderBy('zp_canvas.created')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * @param  int  $projectId  Project ID
     * @param  array<int, string>  $boards  Board types to query
     * @return array<int, array<string, mixed>>
     */
    public function getLastUpdatedCanvas(int $projectId, array $boards): array
    {
        $query = $this->connection->table('zp_canvas')
            ->select([
                'zp_canvas.id as id',
                'zp_canvas.type as type',
                'zp_canvas.title as title',
            ])
            ->selectRaw('COALESCE(MAX(zp_canvas_items.modified), zp_canvas.created) AS modified')
            ->leftJoin('zp_canvas_items', 'zp_canvas.id', '=', 'zp_canvas_items.canvasId');

        if ($projectId > 0) {
            $query->where('projectId', $projectId);
        }

        if (count($boards) > 0) {
            $query->whereIn('type', $boards);
        }

        $results = $query->groupBy(['zp_canvas.id', 'zp_canvas.type', 'zp_canvas.title', 'zp_canvas.created'])
            ->orderByDesc('modified')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * @param  int  $projectId  Project ID
     * @return array<int, array<string, mixed>>
     */
    public function getTags(int $projectId): array
    {
        $results = $this->connection->table('zp_canvas_items')
            ->select('zp_canvas_items.tags')
            ->leftJoin('zp_canvas', 'zp_canvas.id', '=', 'zp_canvas_items.canvasId')
            ->where('zp_canvas.projectId', $projectId)
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }
}
