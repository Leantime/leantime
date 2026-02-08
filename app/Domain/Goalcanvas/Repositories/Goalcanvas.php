<?php

/**
 * Repository
 */

namespace Leantime\Domain\Goalcanvas\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Canvas\Repositories\Canvas;
use Leantime\Domain\Tickets\Repositories\Tickets;

class Goalcanvas extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'goal';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'fa-bullseye';

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

    public function __construct(DbCore $db, LanguageCore $language, Tickets $ticketRepo, DatabaseHelper $dbHelper)
    {
        parent::__construct($db, $language, $ticketRepo, $dbHelper);
        $this->dbConnection = $db->getConnection();
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

    public function getSingleCanvas($canvasId): false|array
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
}
