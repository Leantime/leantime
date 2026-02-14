<?php

/**
 * canvas class - Generic / Tempalate of canvas repository class
 */

namespace Leantime\Domain\Canvas\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Tickets\Repositories\Tickets;

class Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = '??';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access protected
     * @var    string Fontawesome icone
     */
    protected string $icon = 'fa-x';

    /***
     * disclaimer - Disclaimer (may be extended)
     *
     * @access protected
     * @var    string Disclaimer (including href)
     */
    protected string $disclaimer = '';

    /**
     * canvasTypes - Canvas elements / boxes (must be extended)
     *
     * @acces protected
     */
    protected array $canvasTypes = [
        // '??_' => [ 'icon' => 'fa-????', 'title' => 'box.??.????' ],
    ];

    /**
     * statusLabels - Status labels (may be extended)
     *
     * @acces protected
     */
    protected array $statusLabels = [
        'status_draft' => ['icon' => 'fa-circle-question',    'color' => 'blue',   'title' => 'status.draft',  'dropdown' => 'info',    'active' => true],
        'status_review' => ['icon' => 'fa-circle-exclamation', 'color' => 'orange', 'title' => 'status.review', 'dropdown' => 'warning', 'active' => true],
        'status_valid' => ['icon' => 'fa-circle-check',       'color' => 'green',  'title' => 'status.valid',  'dropdown' => 'success', 'active' => true],
        'status_hold' => ['icon' => 'fa-circle-h',           'color' => 'red',    'title' => 'status.hold',   'dropdown' => 'danger',  'active' => true],
        'status_invalid' => ['icon' => 'fa-circle-xmark',       'color' => 'red',    'title' => 'status.invalid', 'dropdown' => 'danger',  'active' => true],
    ];

    /**
     * relatesLabels - Relates to label (same structure as `statusLabels`)
     *
     * @acces public
     */
    protected array $relatesLabels = [
        'relates_none' => ['icon' => 'fa-border-none', 'color' => 'grey',      'title' => 'relates.none',         'dropdown' => 'default', 'active' => true],
        'relates_customers' => ['icon' => 'fa-users',       'color' => 'green',     'title' => 'relates.customers',    'dropdown' => 'success', 'active' => true],
        'relates_offerings' => ['icon' => 'fa-barcode',     'color' => 'red',       'title' => 'relates.offerings',    'dropdown' => 'danger',  'active' => true],
        'relates_capabilities' => ['icon' => 'fa-pen-ruler',   'color' => 'blue',      'title' => 'relates.capabilities', 'dropdown' => 'info',    'active' => true],
        'relates_financials' => ['icon' => 'fa-money-bill',  'color' => 'yellow',    'title' => 'relates.financials',   'dropdown' => 'warning', 'active' => true],
        'relates_markets' => ['icon' => 'fa-shop',        'color' => 'brown',     'title' => 'relates.markets',      'dropdown' => 'default', 'active' => true],
        'relates_environment' => ['icon' => 'fa-tree',        'color' => 'darkgreen', 'title' => 'relates.environment',  'dropdown' => 'default', 'active' => true],
        'relates_firm' => ['icon' => 'fa-building',    'color' => 'darkblue',  'title' => 'relates.firm',         'dropdown' => 'info',    'active' => true],
    ];

    /**
     * dataLabels - Data labels (may be extended)
     *
     * @acces protected
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.assumptions', 'field' => 'assumptions', 'active' => true],
        2 => ['title' => 'label.data',        'field' => 'data',        'active' => true],
        3 => ['title' => 'label.conclusion',  'field' => 'conclusion',  'active' => true],
    ];

    public ?object $result = null;

    public ?object $tickets = null;

    protected ?DbCore $db = null;

    protected ConnectionInterface $connection;

    protected DatabaseHelper $dbHelper;

    private LanguageCore $language;

    private Tickets $ticketRepo;

    /**
     * __construct - get db connection
     *
     * @return void
     */
    public function __construct(
        DbCore $db,
        LanguageCore $language,
        Tickets $ticketRepo,
        DatabaseHelper $dbHelper
    ) {
        $this->db = $db;
        $this->connection = $db->getConnection();
        $this->language = $language;
        $this->ticketRepo = $ticketRepo;
        $this->dbHelper = $dbHelper;
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

        return $this->language->__($this->disclaimer);
    }

    /**
     * getCanvasTypes() - Retrieve translated canvaas items
     *
     * @return array Array of data
     */
    public function getCanvasTypes(): array
    {

        $canvasTypes = $this->canvasTypes;
        foreach ($canvasTypes as $key => $data) {
            if (isset($data['title'])) {
                $canvasTypes[$key]['title'] = $this->language->__($data['title']);
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
                $statusLabels[$key]['title'] = $this->language->__($data['title']);
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
                $relatesLabels[$key]['title'] = $this->language->__($data['title']);
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
                $dataLabels[$key]['title'] = $this->language->__($data['title']);
            }
        }

        return $dataLabels;
    }

    public function getAllCanvas($projectId, $type = null): false|array
    {
        if ($type == null || $type == '') {
            $canvasType = static::CANVAS_NAME.'canvas';
        } else {
            $canvasType = $type;
        }

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
            ->selectRaw('COUNT(zp_canvas_items.id) AS "boxItems"')
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

    public function getSingleCanvas($canvasId): false|array
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
            ->where('type', static::CANVAS_NAME.'canvas')
            ->where('zp_canvas.id', $canvasId)
            ->orderBy('zp_canvas.title')
            ->orderBy('zp_canvas.created')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function deleteCanvas($id): void
    {
        // Delete canvas items first
        $this->connection->table('zp_canvas_items')
            ->where('canvasId', $id)
            ->delete();

        // Then delete the canvas
        $this->connection->table('zp_canvas')
            ->where('id', $id)
            ->limit(1)
            ->delete();
    }

    public function addCanvas($values, $type = null): false|string
    {
        if ($type == null || $type == '') {
            $canvasType = static::CANVAS_NAME.'canvas';
        } else {
            $canvasType = $type;
        }

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

    public function updateCanvas($values): mixed
    {
        return $this->connection->table('zp_canvas')
            ->where('id', $values['id'])
            ->update([
                'title' => $values['title'],
                'description' => $values['description'] ?? '',
            ]);
    }

    public function editCanvasItem($values): void
    {
        $this->connection->table('zp_canvas_items')
            ->where('id', $values['itemId'] ?? $values['id'])
            ->limit(1)
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

    public function patchCanvasItem($id, $params): bool
    {
        if (isset($params['act'])) {
            unset($params['act']);
        }

        $updates = [];
        foreach ($params as $key => $value) {
            $sanitizedKey = DbCore::sanitizeToColumnString($key);
            $updates[$sanitizedKey] = $value;
        }

        $updates['id'] = $id;

        return $this->connection->table('zp_canvas_items')
            ->where('id', $id)
            ->limit(1)
            ->update($updates);
    }

    public function getCanvasItemsById($id): false|array
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
            ->selectRaw('COUNT(DISTINCT zp_comment.id) AS "commentCount"')
            ->selectRaw('0 AS "percentDone"')
            ->leftJoin('zp_user as t1', 'zp_canvas_items.author', '=', 't1.id')
            ->leftJoin('zp_tickets as milestone', function ($join) {
                $join->on('zp_canvas_items.milestoneId', '=', $this->connection->raw('CAST("milestone"."id" AS CHAR)'));
            })
            ->leftJoin('zp_comment', function ($join) {
                $join->on('zp_canvas_items.id', '=', 'zp_comment.moduleId')
                    ->where('zp_comment.module', '=', static::CANVAS_NAME.'canvasitem');
            })
            ->where('zp_canvas_items.canvasId', $id)
            ->groupBy(['zp_canvas_items.id', 'zp_canvas_items.box', 'zp_canvas_items.sortindex', 't1.firstname', 't1.lastname', 't1.profileId', 'milestone.headline', 'milestone.editTo'])
            ->orderBy('zp_canvas_items.box')
            ->orderBy('zp_canvas_items.sortindex')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

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
                $join->on('zp_canvas_items.milestoneId', '=', $this->connection->raw('CAST("milestone"."id" AS CHAR)'));
            })
            ->leftJoin('zp_user as t1', 'zp_canvas_items.author', '=', 't1.id')
            ->where('board.projectId', $projectId)
            ->groupBy(['id', 'board.id', 'board.title', 'parentKPI.description', 'parentGoal.description', 't1.firstname', 't1.lastname', 'milestone.headline', 'milestone.editTo'])
            ->orderBy('board.id')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function getAllAvailableKPIs($projectId): false|array
    {
        // First, get parent project IDs (cross-database compatible approach)
        $parentProjectIds = [];

        $parentProject = $this->connection->table('zp_projects')
            ->select('parent')
            ->where('id', $projectId)
            ->whereIn('type', ['strategy', 'program'])
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
     * @return false|mixed
     */
    public function getSingleCanvasItem($id): mixed
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
            ->selectRaw('COUNT("progressTickets".id) AS "allTickets"')
            ->selectSub(function ($query) use ($statusGroups) {
                $query->from('zp_tickets as progressSub')
                    ->selectRaw('(
                        CASE WHEN
                          COUNT(DISTINCT "progressSub".id) > 0
                        THEN
                          ROUND(
                            (
                              SUM(CASE WHEN "progressSub".status '.$statusGroups['DONE'].' THEN CASE WHEN "progressSub".storypoints = 0 THEN 3 ELSE "progressSub".storypoints END ELSE 0 END) /
                              SUM(CASE WHEN "progressSub".storypoints = 0 THEN 3 ELSE "progressSub".storypoints END)
                            ) *100)
                        ELSE
                          0
                        END)
                    ')
                    ->whereColumn('progressSub.milestoneid', 'zp_canvas_items.milestoneId')
                    ->where('progressSub.type', '<>', 'milestone');
            }, 'percentDone')
            ->leftJoin('zp_canvas_items as parentKPI', 'zp_canvas_items.kpi', '=', 'parentKPI.id')
            ->leftJoin('zp_canvas as board', 'board.id', '=', 'zp_canvas_items.canvasId')
            ->leftJoin('zp_canvas_items as parentGoal', 'zp_canvas_items.parent', '=', 'parentGoal.id')
            ->leftJoin('zp_tickets as progressTickets', function ($join) {
                $join->on('progressTickets.milestoneid', '=', 'zp_canvas_items.milestoneId')
                    ->where('progressTickets.type', '<>', 'milestone')
                    ->where('progressTickets.type', '<>', 'subtask');
            })
            ->leftJoin('zp_tickets as milestone', function ($join) {
                $join->on('zp_canvas_items.milestoneId', '=', $this->connection->raw('CAST("milestone"."id" AS CHAR)'));
            })
            ->leftJoin('zp_user as t1', 'zp_canvas_items.author', '=', 't1.id')
            ->where('zp_canvas_items.id', $id)
            ->first();

        if ($result !== null && $result->id != null) {
            return (array) $result;
        } else {
            return false;
        }
    }

    public function addCanvasItem($values): false|string
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

    public function delCanvasItem($id): void
    {
        $this->connection->table('zp_canvas_items')
            ->where('id', $id)
            ->limit(1)
            ->delete();
    }

    /**
     * @return int|mixed
     */
    /**
     * @return int|mixed
     */
    public function getNumberOfCanvasItems($projectId = null): mixed
    {
        $query = $this->connection->table('zp_canvas_items')
            ->selectRaw('COUNT(zp_canvas_items.id) AS "canvasCount"')
            ->leftJoin('zp_canvas as canvasBoard', 'zp_canvas_items.canvasId', '=', 'canvasBoard.id')
            ->where('canvasBoard.type', static::CANVAS_NAME.'canvas');

        if (! is_null($projectId)) {
            $query->where('canvasBoard.projectId', $projectId);
        }

        $result = $query->first();

        return $result->canvasCount ?? 0;
    }

    /**
     * @return int|mixed
     */
    /**
     * @return int|mixed
     */
    public function getNumberOfBoards($projectId = null): mixed
    {
        $query = $this->connection->table('zp_canvas')
            ->selectRaw('COUNT(zp_canvas.id) AS "boardCount"')
            ->where('zp_canvas.type', static::CANVAS_NAME.'canvas');

        if (! is_null($projectId)) {
            $query->where('zp_canvas.projectId', $projectId);
        }

        $result = $query->first();

        return $result->boardCount ?? 0;
    }

    /**
     * existCanvas - return if a canvas exists with a given title in the specified project
     *
     * @param  int  $projectId  Project identifier
     * @param  string  $canvasTitle  Canvas title
     * @return bool True if canvas exists
     */
    public function existCanvas(int $projectId, string $canvasTitle): bool
    {
        $result = $this->connection->table('zp_canvas')
            ->selectRaw('COUNT(id) as "nbCanvas"')
            ->where('projectId', $projectId)
            ->where('title', $canvasTitle)
            ->where('type', static::CANVAS_NAME.'canvas')
            ->first();

        return isset($result->nbCanvas) && $result->nbCanvas > 0;
    }

    /***
     * copyCanvas - create a copy of an existing canvas
     *
     * @access public
     * @param int    $projectId   Project identifier
     * @param int    $canvasId    Original canvas identifier
     * @param int    $authorId    Author identifier
     * @param  string $canvasTitle New canvas title
     * @return int    Identifier of new Canvas
     */
    public function copyCanvas(int $projectId, int $canvasId, int $authorId, string $canvasTitle): int
    {
        // Create new Canvas
        $values = ['title' => $canvasTitle, 'author' => $authorId, 'projectId' => $projectId, 'type' => static::CANVAS_NAME.'canvas'];
        $newCanvasId = $this->addCanvas($values);

        // Copy elements from existing canvas to new Canvas
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
            ->selectRaw('? as "canvasId"', [$newCanvasId])
            ->select(['status', 'relates'])
            ->selectRaw("'' as \"milestoneId\"")
            ->select([
                'kpi', 'data1', 'startDate', 'endDate', 'setting', 'metricType', 'impact',
                'effort', 'probability', 'action', 'assignedTo', 'startValue', 'currentValue', 'endValue',
            ])
            ->where('canvasId', $canvasId);

        $this->connection->table('zp_canvas_items')->insertUsing($columns, $selectQuery);

        return $newCanvasId;
    }

    /***
     * mergeCanvas - merge canvas into existing canvas
     *
     * @access public
     * @param int    $canvasId Original canvas identifier
     * @param string $mergeId  Canvas to perge into existing one
     * @return bool Status of merge
     */
    public function mergeCanvas(int $canvasId, string $mergeId): bool
    {
        // Copy elements from merge canvas into current canvas
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
            ->selectRaw('? as "canvasId"', [$canvasId])
            ->select(['status', 'relates'])
            ->selectRaw("'' as \"milestoneId\"")
            ->select([
                'kpi', 'data1', 'startDate', 'endDate', 'setting', 'metricType', 'impact',
                'effort', 'probability', 'action', 'assignedTo', 'startValue', 'currentValue', 'endValue',
            ])
            ->where('canvasId', $mergeId);

        $this->connection->table('zp_canvas_items')->insertUsing($columns, $selectQuery);

        return true;
    }

    /***
     * getCanvasProgressCount - gets canvases by type and counts number of items per box
     *
     * @access public
     * @param int   $projectId Project od
     * @param  array $boards    List of board types to pull
     * @return array|bool list of boards or false
     */
    public function getCanvasProgressCount(int $projectId, array $boards): array|bool
    {
        $query = $this->connection->table('zp_canvas')
            ->select([
                'zp_canvas.id as canvasId',
                'zp_canvas.type as canvasType',
                'zp_canvas_items.box',
            ])
            ->selectRaw('COUNT(zp_canvas_items.id) AS "boxItems"')
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

    /***
     * getLastUpdateCanvas - gets the list of canvas that have been updated recently
     *
     * @access public
     * @param int   $projectId Project od
     * @param  array $boards    List of board types to pull
     * @return array    array of canvas boards sorted by last update date
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

    /***
     * getTags - gets the list of tags across all canvas items in a given project
     *
     * @access public
     * @param int $projectId Project od
     * @return array    array of canvas boards sorted by last update date
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
