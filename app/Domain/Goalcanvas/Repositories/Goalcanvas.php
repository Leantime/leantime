<?php

/**
 * Repository
 */

namespace Leantime\Domain\Goalcanvas\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Language as LanguageCore;
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
     * @param  DbCore  $db  Database connection
     * @param  LanguageCore  $language  Language service
     * @param  Tickets  $ticketRepo  Ticket repository
     * @param  DatabaseHelper  $dbHelper  Database helper
     */
    public function __construct(DbCore $db, LanguageCore $language, Tickets $ticketRepo, DatabaseHelper $dbHelper)
    {
        parent::__construct($db, $language, $ticketRepo, $dbHelper);
        $this->dbConnection = $db->getConnection();
        $this->canvasLanguage = $language;
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
            ->where('milestoneId', (string) $milestoneId)
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
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
}
