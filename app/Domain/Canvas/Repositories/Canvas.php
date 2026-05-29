<?php

/**
 * canvas class - DEPRECATED backwards-compatibility adapter.
 */

namespace Leantime\Domain\Canvas\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Blueprints\Repositories\Blueprints as BlueprintsRepository;
use Leantime\Domain\Tickets\Repositories\Tickets;

/**
 * Thin backwards-compatibility shim over the Blueprints repository.
 *
 * The canvas system was consolidated into Leantime\Domain\Blueprints. This class
 * is kept ONLY so plugins (and any code) that still extend or instantiate the old
 * canvas repository keep working: its data methods now delegate to the Blueprints
 * repository, deriving the canvas type from the subclass CANVAS_NAME constant.
 *
 * @deprecated Use Leantime\Domain\Blueprints\Repositories\Blueprints instead.
 *             Do not build new features on this class.
 */
class Canvas extends BlueprintsRepository
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
     * __construct - get db connection and initialise the underlying Blueprints repository
     */
    public function __construct(
        DbCore $db,
        LanguageCore $language,
        Tickets $ticketRepo,
        DatabaseHelper $dbHelper
    ) {
        // Initialise the Blueprints parent so the delegated methods have a connection.
        parent::__construct($db, $language, $ticketRepo, $dbHelper);

        // Keep local copies for the config accessors and goal-specific queries below.
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

    /**
     * @deprecated delegate to Blueprints repository
     */
    public function getAllCanvas($projectId, $type = null): false|array
    {
        $canvasType = ($type === null || $type === '') ? static::CANVAS_NAME.'canvas' : $type;

        return parent::getAllCanvas((int) $projectId, $canvasType);
    }

    /**
     * @deprecated delegate to Blueprints repository
     */
    public function getSingleCanvas($canvasId, $canvasType = null): false|array
    {
        return parent::getSingleCanvas((int) $canvasId, $canvasType ?: static::CANVAS_NAME.'canvas');
    }

    /**
     * @deprecated delegate to Blueprints repository
     */
    public function addCanvas($values, $type = null): false|string
    {
        $canvasType = ($type === null || $type === '') ? static::CANVAS_NAME.'canvas' : $type;

        return parent::addCanvas($values, $canvasType);
    }

    /**
     * @deprecated delegate to Blueprints repository
     */
    public function getCanvasItemsById($id, $commentModule = null): false|array
    {
        return parent::getCanvasItemsById((int) $id, $commentModule ?: static::CANVAS_NAME.'canvasitem');
    }

    /**
     * @deprecated delegate to Blueprints repository
     */
    public function getNumberOfCanvasItems($projectId = null, $canvasType = null): mixed
    {
        return parent::getNumberOfCanvasItems($projectId !== null ? (int) $projectId : null, $canvasType ?: static::CANVAS_NAME.'canvas');
    }

    /**
     * @deprecated delegate to Blueprints repository
     */
    public function getNumberOfBoards($projectId = null, $canvasType = null): mixed
    {
        return parent::getNumberOfBoards($projectId !== null ? (int) $projectId : null, $canvasType ?: static::CANVAS_NAME.'canvas');
    }

    /**
     * existCanvas - return if a canvas exists with a given title in the specified project
     *
     * @param  int  $projectId  Project identifier
     * @param  string  $canvasTitle  Canvas title
     * @return bool True if canvas exists
     *
     * @deprecated delegate to Blueprints repository
     */
    public function existCanvas(int $projectId, string $canvasTitle, $canvasType = null): bool
    {
        return parent::existCanvas($projectId, $canvasTitle, $canvasType ?: static::CANVAS_NAME.'canvas');
    }

    /***
     * copyCanvas - create a copy of an existing canvas
     *
     * @deprecated delegate to Blueprints repository
     */
    public function copyCanvas(int $projectId, int $canvasId, int $authorId, string $canvasTitle, $canvasType = null): int
    {
        return parent::copyCanvas($projectId, $canvasId, $authorId, $canvasTitle, $canvasType ?: static::CANVAS_NAME.'canvas');
    }

    /**
     * getCanvasItemsByKPI - goal-specific KPI hierarchy query (not part of Blueprints).
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
