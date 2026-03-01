<?php

namespace Leantime\Domain\Tickets\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Facades\Cache;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Events\DispatchesEvents as EventhelperCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\EntityRelationshipEnum;
use Leantime\Domain\Users\Services\Users;

class Tickets
{
    use EventhelperCore;

    public ?object $result = null;

    public ?object $tickets = null;

    private DbCore $db;

    private ConnectionInterface $connection;

    private DatabaseHelper $dbHelper;

    public array $statusClasses = ['3' => 'label-info', '1' => 'label-important', '4' => 'label-warning', '2' => 'label-warning', '0' => 'label-success', '-1' => 'label-default'];

    public array $statusListSeed = [
        3 => [
            'name' => 'status.new',
            'class' => 'label-info',
            'statusType' => 'NEW',
            'kanbanCol' => true,
            'sortKey' => 1,
        ],
        1 => [
            'name' => 'status.blocked',
            'class' => 'label-important',
            'statusType' => 'INPROGRESS',
            'kanbanCol' => true,
            'sortKey' => 2,
        ],
        4 => [
            'name' => 'status.in_progress',
            'class' => 'label-warning',
            'statusType' => 'INPROGRESS',
            'kanbanCol' => true,
            'sortKey' => 3,
        ],
        2 => [
            'name' => 'status.waiting_for_approval',
            'class' => 'label-warning',
            'statusType' => 'INPROGRESS',
            'kanbanCol' => true,
            'sortKey' => 4,
        ],
        0 => [
            'name' => 'status.done',
            'class' => 'label-success',
            'statusType' => 'DONE',
            'kanbanCol' => true,
            'sortKey' => 5,
        ],
        -1 => [
            'name' => 'status.archived',
            'class' => 'label-default',
            'statusType' => 'DONE',
            'kanbanCol' => false,
            'sortKey' => 6,
        ],
    ];

    public array $priority = ['1' => 'Critical', '2' => 'High', '3' => 'Medium', '4' => 'Low', '5' => 'Lowest'];

    public array $efforts = ['0.5' => '< 2min', '1' => 'XS', '2' => 'S', '3' => 'M', '5' => 'L', '8' => 'XL', '13' => 'XXL'];

    public array $type = ['task', 'subtask', 'story', 'bug'];

    public array $typeIcons = ['story' => 'auto_stories', 'task' => 'check_box', 'subtask' => 'account_tree', 'bug' => 'bug_report'];

    /**
     * @var bool
     */
    private int|bool $page = 0;

    /**
     * @var bool
     */
    public int|bool $rowsPerPage = 10;

    private string $limitSelect = '';

    public string $numPages = '';

    public string $sortBy = 'date';

    private LanguageCore $language;

    /**
     * __construct - get db connection
     *
     * @return void
     */
    public function __construct(DbCore $db, LanguageCore $language, DatabaseHelper $dbHelper)
    {
        $this->db = $db;
        $this->connection = $db->getConnection();
        $this->language = $language;
        $this->dbHelper = $dbHelper;
    }

    /**
     * @api
     * Get Ticket Status List
     */
    public function getStateLabels($projectId = null): array
    {
        if (Cache::has('projectsettings.'.$projectId.'.ticketlabels')) {
            return Cache::get('projectsettings.'.$projectId.'.ticketlabels');
        }

        if ($projectId == null) {
            $projectId = session('currentProject');
        }

        $result = $this->connection->table('zp_settings')
            ->select('value')
            ->where('key', 'projectsettings.'.$projectId.'.ticketlabels')
            ->first();

        $labels = [];

        $statusList = $this->statusListSeed;

        // Override the state values that are in the db
        if ($result !== null) {

            $statusList = [];

            // Archive is required and protected.
            // Adding the original version back in case folks removed it
            $statusList[-1] = $this->statusListSeed[-1];

            foreach (unserialize($result->value) as $key => $status) {
                if (is_int($key)) {
                    // Backwards Compatibility with existing labels in db
                    // Prior to 2.1.9 labels were stored as <<statuskey>>:<<labelString>>
                    // Afterwards labelString was replaced with an array to include all different status attributes needed for custom status types
                    if (! is_array($status)) {
                        $statusList[$key] = $this->statusListSeed[$key];

                        if (is_array($statusList[$key]) && isset($statusList[$key]['name']) && $key !== -1) {
                            $statusList[$key]['name'] = $status;
                        }
                    } else {
                        $statusList[$key] = $status;
                    }
                }
            }
        } else {
            // If the values are not coming from the db, we need to translate the label strings
            foreach ($statusList as &$status) {
                $status['name'] = $this->language->__($status['name']);
            }
        }

        // Sort by order number
        uasort($statusList, function ($a, $b) {
            return $a['sortKey'] <=> $b['sortKey'];
        });

        Cache::put('projectsettings.'.$projectId.'.ticketlabels', $statusList, 3600);

        return $statusList;
    }

    public function getStatusList(): mixed
    {
        return $this->statusListSeed;
    }

    /**
     * @return string[]
     */
    public function getStatusListGroupedByType($projectId): array
    {

        // Ignoring status type NONE by design
        $statusByType = [
            'DONE' => [],
            'INPROGRESS' => [],
            'NEW' => [],
        ];
        $states = $this->getStateLabels($projectId);

        foreach ($states as $key => $value) {
            $statusByType[$value['statusType']][] = $key;
        }

        $doneQuery = 'IN('.implode(',', $statusByType['DONE']).')';
        $inProgressQuery = 'IN('.implode(',', $statusByType['INPROGRESS']).')';
        $newQuery = 'IN('.implode(',', $statusByType['NEW']).')';
        $openTodos = 'IN('.implode(',', array_merge($statusByType['NEW'], $statusByType['INPROGRESS'])).')';

        if ($doneQuery == 'IN()') {
            $doneQuery = 'IN(FALSE)';
        }
        if ($inProgressQuery == 'IN()') {
            $inProgressQuery = 'IN(FALSE)';
        }
        if ($newQuery == 'IN()') {
            $newQuery = 'IN(FALSE)';
        }
        if ($openTodos == 'IN()') {
            $openTodos = 'IN(FALSE)';
        }

        $statusByTypeQuery = [
            'DONE' => $doneQuery,
            'INPROGRESS' => $inProgressQuery,
            'NEW' => $newQuery,
            'ALLOPEN' => $openTodos,
        ];

        return $statusByTypeQuery;
    }

    public function getStatusIdByName($statusLabel, $projectId): int|false
    {
        $statusList = $this->getStateLabels($projectId);

        foreach ($statusList as $key => $status) {
            if ($status['name'] == $statusLabel) {
                return $key;
            }
        }

        return false;
    }

    /**
     * getAll - get all Tickets, depending on userrole
     *
     * @throws BindingResolutionException
     */
    public function getAll(int $limit = 9999): false|array
    {

        $id = session('userdata.id');

        $values = $this->getUsersTickets($id, $limit);

        return $values;
    }

    /**
     * @throws BindingResolutionException
     */
    public function getUsersTickets($id, $limit): false|array
    {
        $users = app()->make(Users::class);
        $user = $users->getUser($id);

        $query = $this->connection->table('zp_tickets as ticket')
            ->select([
                'ticket.id',
                'ticket.headline',
                'ticket.type',
                'ticket.description',
                'ticket.date',
                'ticket.dateToFinish',
                'ticket.projectId',
                'ticket.priority',
                'ticket.status',
                'project.name as projectName',
                'client.name as clientName',
                't1.id as authorId',
                't1.firstname as authorFirstname',
                't1.lastname as authorLastname',
                't2.id as editorId',
                't2.firstname as editorFirstname',
                't2.lastname as editorLastname',
            ])
            ->leftJoin('zp_projects as project', 'ticket.projectId', '=', 'project.id')
            ->leftJoin('zp_clients as client', 'project.clientId', '=', 'client.id')
            ->leftJoin('zp_user as t1', 'ticket.userId', '=', 't1.id')
            ->leftJoin('zp_user as t2', function ($join) {
                $join->on('ticket.editorId', '=', $this->connection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('t2.id'), 'text')));
            })
            ->where(function ($q) use ($id, $user) {
                $q->whereIn('ticket.projectId', function ($subquery) use ($id) {
                    $subquery->select('projectId')
                        ->from('zp_relationuserproject')
                        ->where('zp_relationuserproject.userId', $id);
                })
                    ->orWhere('project.psettings', 'all')
                    ->orWhere(function ($q2) use ($user) {
                        $q2->where('project.psettings', 'clients')
                            ->where('project.clientId', $user['clientId'] ?? '');
                    });
            })
            ->where('ticket.type', '<>', 'milestone')
            ->orderByDesc('ticket.id');

        if ($limit > -1) {
            $query->limit($limit);
        }

        $results = $query->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * getAllBySearchCriteria - get Tickets by search criteria array
     *
     * @param  null  $limit
     */
    /**
     * getAllBySearchCriteria - get Tickets by search criteria array
     *
     * @param  null  $limit
     */
    public function getAllBySearchCriteria(array $searchCriteria, string $sort = 'standard', $limit = null, $includeCounts = true, $offset = null): bool|array
    {
        $requestorId = session()->exists('userdata') ? session('userdata.id') : -1;
        $userId = $searchCriteria['currentUser'] ?? session('userdata.id') ?? '-1';
        $clientId = $searchCriteria['currentClient'] ?? session('userdata.clientId') ?? '-1';

        $query = $this->connection->table('zp_tickets')
            ->select([
                'zp_tickets.id',
                'zp_tickets.headline',
                'zp_tickets.description',
                'zp_tickets.date',
                'zp_tickets.sprint',
                'zp_tickets.storypoints',
                'zp_tickets.sortindex',
                'zp_tickets.dateToFinish',
                'zp_tickets.projectId',
                'zp_tickets.priority',
                'zp_tickets.status',
                'zp_tickets.tags',
                'zp_tickets.editorId',
                'zp_tickets.dependingTicketId',
                'zp_tickets.milestoneid',
                'zp_tickets.planHours',
                'zp_tickets.editFrom',
                'zp_tickets.editTo',
                'zp_tickets.hourRemaining',
                'zp_sprints.name as sprintName',
                'zp_projects.name as projectName',
                'zp_clients.name as clientName',
                'zp_clients.id as clientId',
                't1.id as authorId',
                't1.lastname as authorLastname',
                't1.firstname as authorFirstname',
                't1.profileId as authorProfileId',
                't2.firstname as editorFirstname',
                't2.lastname as editorLastname',
                't2.profileId as editorProfileId',
                'milestone.headline as milestoneHeadline',
                'parent.headline as parentHeadline',
            ])
            ->selectRaw("CASE WHEN zp_tickets.type <> '' THEN zp_tickets.type ELSE 'task' END AS type")
            ->selectRaw('CASE WHEN ('.$this->dbHelper->wrapColumn('milestone.tags').' IS NULL OR '.$this->dbHelper->wrapColumn('milestone.tags')." = '') THEN 'var(--grey)' ELSE ".$this->dbHelper->wrapColumn('milestone.tags').' END AS '.$this->dbHelper->wrapColumn('milestoneColor'))
            ->selectRaw('COALESCE(ROUND(timesheet_agg.total_hours, 2), 0) AS '.$this->dbHelper->wrapColumn('bookedHours'));

        if ($includeCounts) {
            $query->selectRaw('COALESCE(comment_agg.comment_count, 0) AS '.$this->dbHelper->wrapColumn('commentCount'))
                ->selectRaw('COALESCE(file_agg.file_count, 0) AS '.$this->dbHelper->wrapColumn('fileCount'))
                ->selectRaw('COALESCE(subtask_agg.subtask_count, 0) AS '.$this->dbHelper->wrapColumn('subtaskCount'));
        } else {
            $query->selectRaw('0 AS '.$this->dbHelper->wrapColumn('commentCount'))
                ->selectRaw('0 AS '.$this->dbHelper->wrapColumn('fileCount'))
                ->selectRaw('0 AS '.$this->dbHelper->wrapColumn('subtaskCount'));
        }

        $query->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->leftJoin('zp_clients', 'zp_projects.clientId', '=', 'zp_clients.id')
            ->leftJoin('zp_user as t1', 'zp_tickets.userId', '=', 't1.id')
            ->leftJoin('zp_user as t2', function ($join) {
                $join->on('zp_tickets.editorId', '=', $this->connection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('t2.id'), 'text')));
            })
            ->leftJoin('zp_user as requestor', function ($join) use ($requestorId) {
                $join->on('requestor.id', '=', $this->connection->raw((int) $requestorId));
            })
            ->leftJoin('zp_sprints', 'zp_tickets.sprint', '=', 'zp_sprints.id')
            ->leftJoin('zp_tickets as milestone', function ($join) {
                $join->on('zp_tickets.milestoneid', '=', 'milestone.id')
                    ->where('zp_tickets.milestoneid', '>', 0)
                    ->where('milestone.type', '=', 'milestone');
            })
            ->leftJoin('zp_tickets as parent', 'zp_tickets.dependingTicketId', '=', 'parent.id')
            ->leftJoinSub(
                $this->connection->table('zp_timesheets')
                    ->select('ticketId')
                    ->selectRaw('SUM(hours) as total_hours')
                    ->groupBy('ticketId'),
                'timesheet_agg',
                'zp_tickets.id',
                '=',
                'timesheet_agg.ticketId'
            );

        if ($includeCounts) {
            $query->leftJoinSub(
                $this->connection->table('zp_comment')
                    ->select('moduleId')
                    ->selectRaw('COUNT(*) as comment_count')
                    ->where('module', 'ticket')
                    ->groupBy('moduleId'),
                'comment_agg',
                'zp_tickets.id',
                '=',
                'comment_agg.moduleId'
            )
                ->leftJoinSub(
                    $this->connection->table('zp_file')
                        ->select('moduleId')
                        ->selectRaw('COUNT(*) as file_count')
                        ->where('module', 'ticket')
                        ->groupBy('moduleId'),
                    'file_agg',
                    'zp_tickets.id',
                    '=',
                    'file_agg.moduleId'
                )
                ->leftJoinSub(
                    $this->connection->table('zp_tickets')
                        ->select('dependingTicketId')
                        ->selectRaw('COUNT(*) as subtask_count')
                        ->where('dependingTicketId', '>', 0)
                        ->groupBy('dependingTicketId'),
                    'subtask_agg',
                    'zp_tickets.id',
                    '=',
                    'subtask_agg.dependingTicketId'
                );
        }

        $query->leftJoin('zp_relationuserproject as rup', function ($join) use ($userId) {
            $join->on('zp_tickets.projectId', '=', 'rup.projectId')
                ->where('rup.userId', '=', $userId);
        })
            ->leftJoin('zp_entity_relationship as er', function ($join) {
                $join->on('er.entityAType', '=', $this->connection->raw("'Ticket'"))
                    ->on('er.entityBType', '=', $this->connection->raw("'User'"))
                    ->on('er.entityA', '=', 'zp_tickets.id')
                    ->on('er.relationship', '=', $this->connection->raw("'".EntityRelationshipEnum::Collaborator->value."'"));
            })
            ->where(function ($q) use ($clientId) {
                $q->whereNotNull('rup.projectId')
                    ->orWhere('zp_projects.psettings', 'all')
                    ->orWhere(function ($q2) use ($clientId) {
                        $q2->where('zp_projects.psettings', 'clients')
                            ->where('zp_projects.clientId', $clientId);
                    })
                    ->orWhere('requestor.role', '>=', 40);
            });

        // Apply search criteria filters
        if (isset($searchCriteria['dateFrom']) && $searchCriteria['dateFrom'] != '') {
            $query->where('zp_tickets.date', '>', $searchCriteria['dateFrom']);
        }

        if (isset($searchCriteria['dateTo']) && $searchCriteria['dateTo'] != '') {
            $query->where('zp_tickets.date', '<', $searchCriteria['dateTo']);
        }

        if (isset($searchCriteria['excludeType']) && $searchCriteria['excludeType'] != '') {
            $query->where('zp_tickets.type', '<>', $searchCriteria['excludeType']);
        }

        if (isset($searchCriteria['currentProject']) && $searchCriteria['currentProject'] != '') {
            $query->where('zp_tickets.projectId', $searchCriteria['currentProject']);
        }

        if (isset($searchCriteria['users']) && $searchCriteria['users'] != '') {
            $userIds = explode(',', $searchCriteria['users']);
            $query->where(function ($q) use ($userIds) {
                $q->whereIn('zp_tickets.editorId', $userIds)
                    ->orWhereIn('er.entityB', $userIds);
            });
        }

        if (isset($searchCriteria['milestone']) && $searchCriteria['milestone'] != '') {
            $milestoneIds = explode(',', $searchCriteria['milestone']);
            $query->whereIn('zp_tickets.milestoneid', $milestoneIds);
        }

        if (isset($searchCriteria['status']) && $searchCriteria['status'] == 'all') {
            // No filter
        } elseif (isset($searchCriteria['status']) && $searchCriteria['status'] != '') {
            $statusArray = explode(',', $searchCriteria['status']);

            if (array_search('not_done', $statusArray) !== false) {
                if ($searchCriteria['currentProject'] != '') {
                    $statusLabels = $this->getStateLabels($searchCriteria['currentProject']);
                    $statusList = [];
                    foreach ($statusLabels as $key => $status) {
                        if ($status['statusType'] !== 'DONE') {
                            $statusList[] = $key;
                        }
                    }
                    if (! empty($statusList)) {
                        $query->whereIn('zp_tickets.status', $statusList);
                    }
                }
            } elseif (array_search('done', $statusArray) !== false) {
                if ($searchCriteria['currentProject'] != '') {
                    $statusLabels = $this->getStateLabels($searchCriteria['currentProject']);
                    $statusList = [];
                    foreach ($statusLabels as $key => $status) {
                        if ($status['statusType'] === 'DONE') {
                            $statusList[] = $key;
                        }
                    }
                    if (! empty($statusList)) {
                        $query->whereIn('zp_tickets.status', $statusList);
                    }
                }
            } else {
                $statuses = array_map('intval', explode(',', $searchCriteria['status']));
                $query->whereIn('zp_tickets.status', $statuses);
            }
        } else {
            $query->where('zp_tickets.status', '<>', -1);
        }

        if (isset($searchCriteria['type']) && $searchCriteria['type'] != '') {
            $types = array_map('strtolower', explode(',', $searchCriteria['type']));
            $query->whereIn($this->connection->raw('LOWER(zp_tickets.type)'), $types);
        }

        if (isset($searchCriteria['priority']) && $searchCriteria['priority'] != '') {
            $priorities = array_map('strtolower', explode(',', $searchCriteria['priority']));
            $query->whereIn($this->connection->raw('LOWER(zp_tickets.priority)'), $priorities);
        }

        if (isset($searchCriteria['term']) && $searchCriteria['term'] != '') {
            $term = $searchCriteria['term'];
            $termWild = '%'.$term.'%';
            $findInSetSql = $this->dbHelper->findInSet('?', 'zp_tickets.tags');
            $query->where(function ($q) use ($term, $termWild, $findInSetSql) {
                $q->whereRaw($findInSetSql, [$term])
                    ->orWhere('zp_tickets.headline', 'LIKE', $termWild)
                    ->orWhere('zp_tickets.description', 'LIKE', $termWild)
                    ->orWhere('zp_tickets.id', 'LIKE', $termWild);
            });
        }

        if (isset($searchCriteria['sprint']) && $searchCriteria['sprint'] > 0 && $searchCriteria['sprint'] != 'all') {
            $sprintIds = explode(',', $searchCriteria['sprint']);
            $query->whereIn('zp_tickets.sprint', $sprintIds);
        }

        if (isset($searchCriteria['sprint']) && $searchCriteria['sprint'] == 'backlog') {
            $query->where(function ($q) {
                $q->whereNull('zp_tickets.sprint')
                    ->orWhere('zp_tickets.sprint', 0)
                    ->orWhere('zp_tickets.sprint', -1);
            });
        }

        $groupByColumns = [
            'zp_tickets.id',
            'zp_sprints.name',
            'zp_projects.name',
            'zp_clients.name',
            'zp_clients.id',
            't1.id',
            't1.lastname',
            't1.firstname',
            't1.profileId',
            't2.firstname',
            't2.lastname',
            't2.profileId',
            'milestone.headline',
            'milestone.tags',
            'parent.headline',
            'zp_tickets.type',
            'timesheet_agg.total_hours',
        ];

        if ($includeCounts) {
            $groupByColumns[] = 'comment_agg.comment_count';
            $groupByColumns[] = 'file_agg.file_count';
            $groupByColumns[] = 'subtask_agg.subtask_count';
        }

        $query->groupBy($groupByColumns);

        // Apply sorting
        if ($sort == 'standard') {
            $query->orderBy('zp_tickets.sortindex', 'ASC')
                ->orderByDesc('zp_tickets.id');
        } elseif ($sort == 'kanbansort') {
            $query->orderBy('zp_tickets.kanbanSortIndex', 'ASC')
                ->orderByDesc('zp_tickets.id');
        } elseif ($sort == 'duedate') {
            $query->orderByRaw('('.$this->dbHelper->wrapColumn('zp_tickets.dateToFinish').' IS NULL)')
                ->orderBy('zp_tickets.dateToFinish', 'ASC')
                ->orderBy('zp_tickets.sortindex', 'ASC')
                ->orderByDesc('zp_tickets.id');
        } elseif ($sort == 'priority') {
            $query->orderBy('zp_tickets.priority', 'ASC')
                ->orderBy('zp_tickets.dateToFinish', 'ASC')
                ->orderBy('zp_tickets.sortindex', 'ASC')
                ->orderByDesc('zp_tickets.id');
        } elseif ($sort == 'date') {
            $query->orderByDesc('zp_tickets.date')
                ->orderBy('zp_tickets.sortindex', 'ASC')
                ->orderByDesc('zp_tickets.id');
        }

        if ($limit !== null && $limit > 0) {
            $query->limit($limit);
            if ($offset !== null && $offset > 0) {
                $query->offset($offset);
            }
        }

        $results = $query->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function simpleTicketQuery(?int $userId, ?int $projectId, array $types = []): array|false
    {
        $requestorId = session()->exists('userdata') ? session('userdata.id') : -1;
        $clientId = session('userdata.clientId') ?? '-1';

        $query = $this->connection->table('zp_tickets')
            ->select([
                'zp_tickets.id',
                'zp_tickets.headline',
                'zp_tickets.description',
                'zp_tickets.date',
                'zp_tickets.sprint',
                'zp_tickets.storypoints',
                'zp_tickets.sortindex',
                'zp_tickets.dateToFinish',
                'zp_tickets.projectId',
                'zp_tickets.priority',
                'zp_tickets.status',
                'zp_tickets.tags',
                'zp_tickets.userId',
                'zp_tickets.editorId',
                'zp_tickets.dependingTicketId',
                'zp_tickets.milestoneid',
                'zp_tickets.planHours',
                'zp_tickets.editFrom',
                'zp_tickets.editTo',
                'zp_tickets.hourRemaining',
                'milestones.headline as milestoneHeadline',
                'zp_projects.name as projectName',
                'zp_projects.details as projectDescription',
            ])
            ->selectRaw("CASE WHEN zp_tickets.type <> '' THEN zp_tickets.type ELSE 'task' END AS type")
            ->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->leftJoin('zp_user as requestor', function ($join) use ($requestorId) {
                $join->on('requestor.id', '=', $this->connection->raw((int) $requestorId));
            })
            ->leftJoin('zp_tickets as milestones', 'zp_tickets.milestoneid', '=', 'milestones.id')
            ->leftJoin('zp_entity_relationship as er', function ($join) {
                $join->on('er.entityAType', '=', $this->connection->raw("'Ticket'"))
                    ->on('er.entityBType', '=', $this->connection->raw("'User'"))
                    ->on('er.relationship', '=', $this->connection->raw("'Collaborator'"))
                    ->on('er.entityA', '=', 'zp_tickets.id');
            })
            ->where(function ($q) use ($requestorId, $clientId) {
                $q->whereIn('zp_tickets.projectId', function ($subquery) use ($requestorId) {
                    $subquery->select('projectId')
                        ->from('zp_relationuserproject')
                        ->where('zp_relationuserproject.userId', $requestorId);
                })
                    ->orWhere('zp_projects.psettings', 'all')
                    ->orWhere(function ($q2) use ($clientId) {
                        $q2->where('zp_projects.psettings', 'clients')
                            ->where('zp_projects.clientId', $clientId);
                    })
                    ->orWhere('requestor.role', '>=', 40);
            });

        if (isset($projectId) && $projectId > 0) {
            $query->where('zp_tickets.projectId', $projectId);
        }

        if (isset($userId) && $userId > 0) {
            $query->where(function ($q) use ($userId) {
                $q->where('zp_tickets.editorId', (string) $userId)
                    ->orWhere('er.entityB', $userId);
            });
        }

        if (count($types) > 0) {
            $query->whereIn('zp_tickets.type', $types);
        }

        $results = $query->orderByDesc('zp_tickets.dateToFinish')
            ->orderBy('zp_tickets.sortindex', 'ASC')
            ->orderByDesc('zp_tickets.id')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function getScheduledTasks(CarbonImmutable $dateFrom, CarbonImmutable $dateTo, ?int $userId = null)
    {
        $requestorId = session()->exists('userdata') ? session('userdata.id') : -1;
        $clientId = session('userdata.clientId') ?? '-1';
        $activeUserId = $userId ?? (session('userdata.id') ?? '-1');

        $query = $this->connection->table('zp_tickets')
            ->select([
                'zp_tickets.id',
                'zp_tickets.headline',
                'zp_tickets.description',
                'zp_tickets.date',
                'zp_tickets.sprint',
                'zp_tickets.storypoints',
                'zp_tickets.sortindex',
                'zp_tickets.dateToFinish',
                'zp_tickets.projectId',
                'zp_tickets.priority',
                'zp_tickets.status',
                'zp_tickets.tags',
                'zp_tickets.editorId',
                'zp_tickets.dependingTicketId',
                'zp_tickets.milestoneid',
                'zp_tickets.planHours',
                'zp_tickets.editFrom',
                'zp_tickets.editTo',
                'zp_tickets.hourRemaining',
            ])
            ->selectRaw("CASE WHEN zp_tickets.type <> '' THEN zp_tickets.type ELSE 'task' END AS type")
            ->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->leftJoin('zp_user as requestor', function ($join) use ($requestorId) {
                $join->on('requestor.id', '=', $this->connection->raw((int) $requestorId));
            })
            ->where(function ($q) use ($activeUserId, $clientId) {
                $q->whereIn('zp_tickets.projectId', function ($subquery) use ($activeUserId) {
                    $subquery->select('projectId')
                        ->from('zp_relationuserproject')
                        ->where('zp_relationuserproject.userId', $activeUserId);
                })
                    ->orWhere('zp_projects.psettings', 'all')
                    ->orWhere(function ($q2) use ($clientId) {
                        $q2->where('zp_projects.psettings', 'clients')
                            ->where('zp_projects.clientId', $clientId);
                    })
                    ->orWhere('requestor.role', '>=', 40);
            })
            ->where('zp_tickets.type', '<>', 'milestone');

        if (isset($userId)) {
            $query->where('zp_tickets.editorId', (string) $userId);
        }

        $query->where(function ($q) use ($dateFrom, $dateTo) {
            $q->whereBetween('zp_tickets.editFrom', [$dateFrom->formatDateTimeForDb(), $dateTo->formatDateTimeForDb()])
                ->orWhereBetween('zp_tickets.editTo', [$dateFrom->formatDateTimeForDb(), $dateTo->formatDateTimeForDb()]);
        });

        $results = $query->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function getAllByProjectId($projectId): false|array
    {
        $results = $this->connection->table('zp_tickets')
            ->select([
                'zp_tickets.id',
                'zp_tickets.headline',
                'zp_tickets.description',
                'zp_tickets.date',
                'zp_tickets.dateToFinish',
                'zp_tickets.projectId',
                'zp_tickets.priority',
                'zp_tickets.status',
                'zp_tickets.sprint',
                'zp_tickets.storypoints',
                'zp_tickets.hourRemaining',
                'zp_tickets.acceptanceCriteria',
                'zp_tickets.userId',
                'zp_tickets.editorId',
                'zp_tickets.planHours',
                'zp_tickets.tags',
                'zp_tickets.url',
                'zp_tickets.editFrom',
                'zp_tickets.editTo',
                'zp_tickets.dependingTicketId',
                'zp_tickets.milestoneid',
                'zp_projects.name as projectName',
                'zp_clients.name as clientName',
                'zp_user.firstname as userFirstname',
                'zp_user.lastname as userLastname',
                't3.firstname as editorFirstname',
                't3.lastname as editorLastname',
            ])
            ->selectRaw("CASE WHEN zp_tickets.type <> '' THEN zp_tickets.type ELSE 'task' END AS type")
            ->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->leftJoin('zp_clients', 'zp_projects.clientId', '=', 'zp_clients.id')
            ->leftJoin('zp_user', 'zp_tickets.userId', '=', 'zp_user.id')
            ->leftJoin('zp_user as t3', function ($join) {
                $join->on('zp_tickets.editorId', '=', $this->connection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('t3.id'), 'text')));
            })
            ->where('zp_tickets.projectId', $projectId)
            ->get();

        // Convert stdClass objects to Tickets model instances
        $tickets = [];
        foreach ($results as $row) {
            $ticket = new \Leantime\Domain\Tickets\Models\Tickets;
            foreach ((array) $row as $key => $value) {
                if (property_exists($ticket, $key)) {
                    $ticket->$key = $value;
                }
            }
            $tickets[] = $ticket;
        }

        return $tickets;
    }

    public function getTags($projectId): false|array
    {
        $results = $this->connection->table('zp_tickets')
            ->select('zp_tickets.tags')
            ->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->where('zp_tickets.projectId', $projectId)
            ->where('zp_tickets.type', '<>', 'milestone')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * getTicket - get a specific Ticket depending on the role
     */
    public function getTicket($id): \Leantime\Domain\Tickets\Models\Tickets|bool
    {
        $result = $this->connection->table('zp_tickets')
            ->select([
                'zp_tickets.id',
                'zp_tickets.headline',
                'zp_tickets.description',
                'zp_tickets.date',
                'zp_tickets.dateToFinish',
                'zp_tickets.projectId',
                'zp_tickets.priority',
                'zp_tickets.status',
                'zp_tickets.sprint',
                'zp_tickets.storypoints',
                'zp_tickets.hourRemaining',
                'zp_tickets.acceptanceCriteria',
                'zp_tickets.userId',
                'zp_tickets.editorId',
                'zp_tickets.planHours',
                'zp_tickets.tags',
                'zp_tickets.url',
                'zp_tickets.editFrom',
                'zp_tickets.editTo',
                'zp_tickets.dependingTicketId',
                'zp_tickets.milestoneid',
                'milestones.headline as milestoneHeadline',
                'zp_projects.name as projectName',
                'zp_projects.details as projectDescription',
                'zp_clients.name as clientName',
                'zp_user.firstname as userFirstname',
                'zp_user.lastname as userLastname',
                't3.firstname as editorFirstname',
                't3.lastname as editorLastname',
                'parent.headline as parentHeadline',
            ])
            ->selectRaw("CASE WHEN zp_tickets.type <> '' THEN zp_tickets.type ELSE 'task' END AS type")
            ->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->leftJoin('zp_clients', 'zp_projects.clientId', '=', 'zp_clients.id')
            ->leftJoin('zp_user', 'zp_tickets.userId', '=', 'zp_user.id')
            ->leftJoin('zp_user as t3', function ($join) {
                $join->on('zp_tickets.editorId', '=', $this->connection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('t3.id'), 'text')));
            })
            ->leftJoin('zp_tickets as parent', 'zp_tickets.dependingTicketId', '=', 'parent.id')
            ->leftJoin('zp_tickets as milestones', 'zp_tickets.milestoneid', '=', 'milestones.id')
            ->where('zp_tickets.id', $id)
            ->limit(1)
            ->first();

        if (! $result) {
            return false;
        }

        $values = new \Leantime\Domain\Tickets\Models\Tickets;
        foreach ((array) $result as $key => $value) {
            $values->$key = $value;
        }

        $values->collaborators = $this->getCollaborators($id);

        return $values;
    }

    public function getAllSubtasks($id): false|array
    {
        $dateFormatSql = match ($this->dbHelper->getDriverName()) {
            'mysql' => "DATE_FORMAT(zp_tickets.date, '%Y,%m,%e')",
            'pgsql' => "TO_CHAR(zp_tickets.date, 'YYYY,MM,DD')",
            default => "DATE_FORMAT(zp_tickets.date, '%Y,%m,%e')",
        };

        $wrappedDateToFinish = $this->dbHelper->wrapColumn('zp_tickets.dateToFinish');
        $dateToFinishFormatSql = match ($this->dbHelper->getDriverName()) {
            'mysql' => "DATE_FORMAT({$wrappedDateToFinish}, '%Y,%m,%e')",
            'pgsql' => "TO_CHAR({$wrappedDateToFinish}, 'YYYY,MM,DD')",
            default => "DATE_FORMAT({$wrappedDateToFinish}, '%Y,%m,%e')",
        };

        $results = $this->connection->table('zp_tickets')
            ->select([
                'zp_tickets.id',
                'zp_tickets.headline',
                'zp_tickets.description',
                'zp_tickets.date',
                'zp_tickets.dateToFinish',
                'zp_tickets.projectId',
                'zp_tickets.priority',
                'zp_tickets.status',
                'zp_tickets.sprint',
                'zp_tickets.storypoints',
                'zp_tickets.acceptanceCriteria',
                'zp_tickets.userId',
                'zp_tickets.editorId',
                'zp_tickets.tags',
                'zp_tickets.url',
                'zp_tickets.editFrom',
                'zp_tickets.editTo',
                'zp_tickets.dependingTicketId',
                'zp_tickets.milestoneid',
                'zp_projects.name as projectName',
                'zp_clients.name as clientName',
                'zp_user.firstname as userFirstname',
                'zp_user.lastname as userLastname',
                't3.firstname as editorFirstname',
                't3.lastname as editorLastname',
            ])
            ->selectRaw("CASE WHEN zp_tickets.type <> '' THEN zp_tickets.type ELSE 'task' END AS type")
            ->selectRaw("{$dateFormatSql} AS ".$this->dbHelper->wrapColumn('timelineDate'))
            ->selectRaw("{$dateToFinishFormatSql} AS ".$this->dbHelper->wrapColumn('timelineDateToFinish'))
            ->selectRaw('COALESCE('.$this->dbHelper->wrapColumn('zp_tickets.hourRemaining').', 0) AS '.$this->dbHelper->wrapColumn('hourRemaining'))
            ->selectRaw('COALESCE('.$this->dbHelper->wrapColumn('zp_tickets.planHours').', 0) AS '.$this->dbHelper->wrapColumn('planHours'))
            ->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->leftJoin('zp_clients', 'zp_projects.clientId', '=', 'zp_clients.id')
            ->leftJoin('zp_user', 'zp_tickets.userId', '=', 'zp_user.id')
            ->leftJoin('zp_user as t3', function ($join) {
                $join->on('zp_tickets.editorId', '=', $this->connection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('t3.id'), 'text')));
            })
            ->where('zp_tickets.dependingTicketId', $id)
            ->orderByDesc('zp_tickets.date')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function getAllPossibleParents(\Leantime\Domain\Tickets\Models\Tickets $ticket, $projectId): false|array
    {
        $query = $this->connection->table('zp_tickets')
            ->select([
                'zp_tickets.id',
                'zp_tickets.headline',
                'zp_tickets.description',
                'zp_tickets.date',
                'zp_tickets.dateToFinish',
                'zp_tickets.projectId',
                'zp_tickets.priority',
                'zp_tickets.status',
                'zp_tickets.sprint',
                'zp_tickets.storypoints',
                'zp_tickets.acceptanceCriteria',
                'zp_tickets.userId',
                'zp_tickets.editorId',
                'zp_tickets.tags',
                'zp_tickets.url',
                'zp_tickets.editFrom',
                'zp_tickets.editTo',
                'zp_tickets.dependingTicketId',
                'zp_tickets.milestoneid',
                'zp_projects.name as projectName',
                'zp_clients.name as clientName',
                'zp_user.firstname as userFirstname',
                'zp_user.lastname as userLastname',
                't3.firstname as editorFirstname',
                't3.lastname as editorLastname',
            ])
            ->selectRaw("CASE WHEN zp_tickets.type <> '' THEN zp_tickets.type ELSE 'task' END AS type")
            ->selectRaw($this->dbHelper->formatDate('zp_tickets.date', '%Y,%m,%e').' AS '.$this->dbHelper->wrapColumn('timelineDate'))
            ->selectRaw($this->dbHelper->formatDate($this->dbHelper->wrapColumn('zp_tickets.dateToFinish'), '%Y,%m,%e').' AS '.$this->dbHelper->wrapColumn('timelineDateToFinish'))
            ->selectRaw('COALESCE('.$this->dbHelper->wrapColumn('zp_tickets.hourRemaining').', 0) AS '.$this->dbHelper->wrapColumn('hourRemaining'))
            ->selectRaw('COALESCE('.$this->dbHelper->wrapColumn('zp_tickets.planHours').', 0) AS '.$this->dbHelper->wrapColumn('planHours'))
            ->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->leftJoin('zp_clients', 'zp_projects.clientId', '=', 'zp_clients.id')
            ->leftJoin('zp_user', 'zp_tickets.userId', '=', 'zp_user.id')
            ->leftJoin('zp_user as t3', function ($join) {
                $join->on('zp_tickets.editorId', '=', $this->connection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('t3.id'), 'text')));
            })
            ->where('zp_tickets.id', '<>', $ticket->id ?? 0)
            ->where('zp_tickets.type', '<>', 'milestone')
            ->where(function ($q) use ($ticket) {
                $q->where('zp_tickets.dependingTicketId', '<>', $ticket->id ?? 0)
                    ->orWhereNull('zp_tickets.dependingTicketId');
            });

        if ($projectId !== 0) {
            $query->where('zp_tickets.projectId', $projectId);
        }

        $results = $query->orderByDesc('zp_tickets.date')->get();

        // Convert stdClass objects to Tickets model instances
        $tickets = [];
        foreach ($results as $row) {
            $ticket = new \Leantime\Domain\Tickets\Models\Tickets;
            foreach ((array) $row as $key => $value) {
                if (property_exists($ticket, $key)) {
                    $ticket->$key = $value;
                }
            }
            $tickets[] = $ticket;
        }

        return $tickets;
    }

    /**
     * Gets all tasks grouped around milestones for timeline views
     */
    public function getAllMilestones(array $searchCriteria, string $sort = 'standard'): false|array
    {
        $statusGroups = $this->getStatusListGroupedByType($searchCriteria['currentProject'] ?? session('currentProject'));

        $requestorId = session('userdata.id') ?? '-1';
        $userId = $searchCriteria['currentUser'] ?? session('userdata.id') ?? '-1';
        $clientId = $searchCriteria['currentClient'] ?? session('userdata.clientId') ?? '-1';

        $query = $this->connection->table('zp_tickets')
            ->select([
                'zp_tickets.id',
                'zp_tickets.headline',
                'zp_tickets.description',
                'zp_tickets.date',
                'zp_tickets.dateToFinish',
                'zp_tickets.projectId',
                'zp_tickets.priority',
                'zp_tickets.status',
                'zp_tickets.sprint',
                'zp_tickets.storypoints',
                'zp_tickets.hourRemaining',
                'zp_tickets.acceptanceCriteria',
                'zp_tickets.userId',
                'zp_tickets.editorId',
                'zp_tickets.planHours',
                'zp_tickets.url',
                'zp_tickets.editFrom',
                'zp_tickets.editTo',
                'zp_tickets.sortindex',
                'zp_tickets.dependingTicketId',
                'zp_tickets.milestoneid',
                'zp_projects.name as projectName',
                'zp_clients.name as clientName',
                'zp_user.firstname as userFirstname',
                'zp_user.lastname as userLastname',
                't3.firstname as editorFirstname',
                't3.lastname as editorLastname',
                't3.profileId as editorProfileId',
                'depMilestone.headline as milestoneHeadline',
            ])
            ->selectRaw("CASE WHEN zp_tickets.type <> '' THEN zp_tickets.type ELSE 'task' END AS type")
            ->selectRaw($this->dbHelper->formatDate('zp_tickets.date', '%Y,%m,%e').' AS '.$this->dbHelper->wrapColumn('timelineDate'))
            ->selectRaw($this->dbHelper->formatDate($this->dbHelper->wrapColumn('zp_tickets.dateToFinish'), '%Y,%m,%e').' AS '.$this->dbHelper->wrapColumn('timelineDateToFinish'))
            ->selectRaw('CASE WHEN ('.$this->dbHelper->wrapColumn('depMilestone.tags').' IS NULL OR '.$this->dbHelper->wrapColumn('depMilestone.tags')." = '') THEN 'var(--grey)' ELSE ".$this->dbHelper->wrapColumn('depMilestone.tags').' END AS '.$this->dbHelper->wrapColumn('milestoneColor'))
            ->selectRaw("CASE WHEN (zp_tickets.tags IS NULL OR zp_tickets.tags = '') THEN 'var(--grey)' ELSE zp_tickets.tags END AS tags")
            ->leftJoin('zp_projects', 'zp_tickets.projectId', '=', 'zp_projects.id')
            ->leftJoin('zp_tickets as depMilestone', 'zp_tickets.milestoneid', '=', 'depMilestone.id')
            ->leftJoin('zp_clients', 'zp_projects.clientId', '=', 'zp_clients.id')
            ->leftJoin('zp_user', 'zp_tickets.userId', '=', 'zp_user.id')
            ->leftJoin('zp_user as t3', function ($join) {
                $join->on('zp_tickets.editorId', '=', $this->connection->raw($this->dbHelper->castAs($this->dbHelper->wrapColumn('t3.id'), 'text')));
            })
            ->leftJoin('zp_user as requestor', function ($join) use ($requestorId) {
                $join->on('requestor.id', '=', $this->connection->raw((int) $requestorId));
            })
            ->where(function ($q) {
                $q->where('zp_projects.state', '<>', -1)
                    ->orWhereNull('zp_projects.state');
            })
            ->where(function ($q) use ($userId, $clientId) {
                $q->whereIn('zp_tickets.projectId', function ($subquery) use ($userId) {
                    $subquery->select('projectId')
                        ->from('zp_relationuserproject')
                        ->where('zp_relationuserproject.userId', $userId);
                })
                    ->orWhere('zp_projects.psettings', 'all')
                    ->orWhere(function ($q2) use ($clientId) {
                        $q2->where('zp_projects.psettings', 'clients')
                            ->where('zp_projects.clientId', $clientId);
                    })
                    ->orWhere('requestor.role', '>=', 40);
            });

        // Apply search criteria filters
        if (isset($searchCriteria['currentProject']) && $searchCriteria['currentProject'] != '') {
            $query->where('zp_tickets.projectId', $searchCriteria['currentProject']);
        }

        if (isset($searchCriteria['clients']) && $searchCriteria['clients'] != 0 && $searchCriteria['clients'] != '') {
            $clientIds = explode(',', $searchCriteria['clients']);
            $query->whereIn('zp_projects.clientId', $clientIds);
        }

        if (isset($searchCriteria['users']) && $searchCriteria['users'] != '') {
            $userIds = explode(',', $searchCriteria['users']);
            $query->whereIn('zp_tickets.editorId', $userIds);
        }

        if (isset($searchCriteria['milestone']) && $searchCriteria['milestone'] != '') {
            $milestoneIds = explode(',', $searchCriteria['milestone']);
            $query->whereIn('zp_tickets.milestoneid', $milestoneIds);
        }

        if (isset($searchCriteria['status']) && $searchCriteria['status'] == 'all') {
            // No filter
        } elseif (isset($searchCriteria['status']) && $searchCriteria['status'] != '') {
            $statusArray = explode(',', $searchCriteria['status']);

            if (array_search('not_done', $statusArray) !== false) {
                if ($searchCriteria['currentProject'] != '') {
                    $statusLabels = $this->getStateLabels($searchCriteria['currentProject']);
                    $statusList = [];
                    foreach ($statusLabels as $key => $status) {
                        if ($status['statusType'] !== 'DONE') {
                            $statusList[] = $key;
                        }
                    }
                    if (! empty($statusList)) {
                        $query->whereIn('zp_tickets.status', $statusList);
                    }
                }
            } elseif (array_search('done', $statusArray) !== false) {
                if ($searchCriteria['currentProject'] != '') {
                    $statusLabels = $this->getStateLabels($searchCriteria['currentProject']);
                    $statusList = [];
                    foreach ($statusLabels as $key => $status) {
                        if ($status['statusType'] === 'DONE') {
                            $statusList[] = $key;
                        }
                    }
                    if (! empty($statusList)) {
                        $query->whereIn('zp_tickets.status', $statusList);
                    }
                }
            } else {
                $statuses = array_map('intval', explode(',', $searchCriteria['status']));
                $query->whereIn('zp_tickets.status', $statuses);
            }
        } else {
            $query->where('zp_tickets.status', '<>', -1);
        }

        if (isset($searchCriteria['type']) && $searchCriteria['type'] != '') {
            $types = array_map('strtolower', explode(',', $searchCriteria['type']));
            $query->whereIn($this->connection->raw('LOWER(zp_tickets.type)'), $types);
        }

        if (isset($searchCriteria['priority']) && $searchCriteria['priority'] != '') {
            $priorities = array_map('strtolower', explode(',', $searchCriteria['priority']));
            $query->whereIn($this->connection->raw('LOWER(zp_tickets.priority)'), $priorities);
        }

        if (isset($searchCriteria['term']) && $searchCriteria['term'] != '') {
            $term = $searchCriteria['term'];
            $termWild = '%'.$term.'%';
            $findInSetSql = $this->dbHelper->findInSet('?', 'zp_tickets.tags');
            $query->where(function ($q) use ($term, $termWild, $findInSetSql) {
                $q->whereRaw($findInSetSql, [$term])
                    ->orWhere('zp_tickets.headline', 'LIKE', $termWild)
                    ->orWhere('zp_tickets.description', 'LIKE', $termWild)
                    ->orWhere('zp_tickets.id', 'LIKE', $termWild);
            });
        }

        if (isset($searchCriteria['sprint']) && $searchCriteria['sprint'] > 0 && $searchCriteria['sprint'] != 'all') {
            $sprintIds = explode(',', $searchCriteria['sprint']);
            $query->where(function ($q) use ($sprintIds) {
                $q->whereIn('zp_tickets.sprint', $sprintIds)
                    ->orWhere('zp_tickets.type', 'milestone');
            });
        }

        if (isset($searchCriteria['sprint']) && $searchCriteria['sprint'] == 'backlog') {
            $query->where(function ($q) {
                $q->whereNull('zp_tickets.sprint')
                    ->orWhere('zp_tickets.sprint', 0)
                    ->orWhere('zp_tickets.sprint', -1)
                    ->orWhere('zp_tickets.type', 'milestone');
            });
        }

        $query->groupBy([
            'zp_tickets.id',
            'zp_projects.name',
            'zp_clients.name',
            'zp_user.firstname',
            'zp_user.lastname',
            't3.firstname',
            't3.lastname',
            't3.profileId',
            'depMilestone.headline',
            'depMilestone.tags',
        ]);

        // Apply sorting
        if ($sort == 'standard') {
            $query->orderBy('zp_tickets.sortindex', 'ASC')
                ->orderBy('zp_tickets.editFrom', 'ASC')
                ->orderByDesc('zp_tickets.id');
        } elseif ($sort == 'kanbansort') {
            $query->orderBy('zp_tickets.kanbanSortIndex', 'ASC')
                ->orderByDesc('zp_tickets.id');
        } elseif ($sort == 'duedate') {
            $query->orderByRaw('('.$this->dbHelper->wrapColumn('zp_tickets.dateToFinish').' IS NULL)')
                ->orderBy('zp_tickets.dateToFinish', 'ASC')
                ->orderBy('zp_tickets.sortindex', 'ASC')
                ->orderByDesc('zp_tickets.id');
        } elseif ($sort == 'date') {
            $query->orderByDesc('zp_tickets.date')
                ->orderBy('zp_tickets.sortindex', 'ASC')
                ->orderByDesc('zp_tickets.id');
        }

        $results = $query->get();

        // Convert stdClass objects to Tickets model instances
        $tickets = [];
        foreach ($results as $row) {
            $ticket = new \Leantime\Domain\Tickets\Models\Tickets;
            foreach ((array) $row as $key => $value) {
                if (property_exists($ticket, $key)) {
                    $ticket->$key = $value;
                }
            }
            $tickets[] = $ticket;
        }

        return $tickets;
    }

    /**
     * getType - get the Type from the type array
     */
    public function getType(): array
    {
        return $this->type;
    }

    /**
     * getPriority - get the priority from the priority array
     */
    public function getPriority($priority): string
    {

        if ($priority !== null && $priority !== '') {
            return $this->priority[$priority];
        } else {
            return $this->priority[1];
        }
    }

    public function getFirstTicket($projectId): mixed
    {
        $result = $this->connection->table('zp_tickets')
            ->select([
                'zp_tickets.id',
                'zp_tickets.headline',
                'zp_tickets.description',
                'zp_tickets.date',
                'zp_tickets.dateToFinish',
                'zp_tickets.projectId',
                'zp_tickets.priority',
                'zp_tickets.status',
                'zp_tickets.sprint',
                'zp_tickets.storypoints',
                'zp_tickets.hourRemaining',
                'zp_tickets.acceptanceCriteria',
                'zp_tickets.userId',
                'zp_tickets.editorId',
                'zp_tickets.planHours',
                'zp_tickets.tags',
                'zp_tickets.url',
                'zp_tickets.editFrom',
                'zp_tickets.editTo',
                'zp_tickets.dependingTicketId',
                'zp_tickets.milestoneid',
            ])
            ->selectRaw("CASE WHEN zp_tickets.type <> '' THEN zp_tickets.type ELSE 'task' END AS type")
            ->selectRaw($this->dbHelper->formatDate('zp_tickets.date', '%Y,%m,%e').' AS '.$this->dbHelper->wrapColumn('timelineDate'))
            ->selectRaw($this->dbHelper->formatDate($this->dbHelper->wrapColumn('zp_tickets.dateToFinish'), '%Y,%m,%e').' AS '.$this->dbHelper->wrapColumn('timelineDateToFinish'))
            ->where('zp_tickets.type', '<>', 'milestone')
            ->where('zp_tickets.projectId', $projectId)
            ->orderBy('zp_tickets.date', 'ASC')
            ->limit(1)
            ->first();

        if (! $result) {
            return false;
        }

        $ticket = new \Leantime\Domain\Tickets\Models\Tickets;
        foreach ((array) $result as $key => $value) {
            if (property_exists($ticket, $key)) {
                $ticket->$key = $value;
            }
        }

        return $ticket;
    }

    public function getNumberOfAllTickets($projectId = null): mixed
    {
        $query = $this->connection->table('zp_tickets')
            ->where('zp_tickets.type', '<>', 'milestone');

        if (! is_null($projectId)) {
            $query->where('zp_tickets.projectId', $projectId);
        }

        return $query->count();
    }

    public function getNumberOfMilestones($projectId = null): mixed
    {
        $query = $this->connection->table('zp_tickets')
            ->where('zp_tickets.type', 'milestone');

        if (! is_null($projectId)) {
            $query->where('zp_tickets.projectId', $projectId);
        }

        return $query->count();
    }

    public function getNumberOfClosedTickets($projectId): mixed
    {
        $statusGroupsSQL = $this->getStatusListGroupedByType($projectId);
        $statusGroups = $this->dbHelper->parseStatusGroups($statusGroupsSQL);

        $query = $this->connection->table('zp_tickets')
            ->where('zp_tickets.type', '<>', 'milestone')
            ->where('zp_tickets.projectId', $projectId);

        if (! empty($statusGroups['DONE'])) {
            $query->whereIn('zp_tickets.status', $statusGroups['DONE']);
        } else {
            $query->whereRaw('1=0'); // Empty status group = no matches
        }

        return $query->count();
    }

    public function getEffortOfClosedTickets($projectId, $averageStorySize): mixed
    {
        $statusGroupsSQL = $this->getStatusListGroupedByType($projectId);
        $statusGroups = $this->dbHelper->parseStatusGroups($statusGroupsSQL);

        $query = $this->connection->table('zp_tickets')
            ->selectRaw('SUM(CASE WHEN zp_tickets.storypoints IS NOT NULL AND zp_tickets.storypoints <> 0 THEN zp_tickets.storypoints ELSE ? END) AS '.$this->dbHelper->wrapColumn('allEffort'), [$averageStorySize])
            ->where('zp_tickets.type', '<>', 'milestone')
            ->where('zp_tickets.projectId', $projectId);

        if (! empty($statusGroups['DONE'])) {
            $query->whereIn('zp_tickets.status', $statusGroups['DONE']);
        } else {
            $query->whereRaw('1=0'); // Empty status group = no matches
        }

        $result = $query->first();

        return $result->allEffort ?? 0;
    }

    public function getEffortOfAllTickets($projectId, $averageStorySize): mixed
    {
        $result = $this->connection->table('zp_tickets')
            ->selectRaw('SUM(CASE WHEN zp_tickets.storypoints IS NOT NULL AND zp_tickets.storypoints <> 0 THEN zp_tickets.storypoints ELSE ? END) AS '.$this->dbHelper->wrapColumn('allEffort'), [$averageStorySize])
            ->where('zp_tickets.type', '<>', 'milestone')
            ->where('zp_tickets.projectId', $projectId)
            ->first();

        return $result->allEffort ?? 0;
    }

    public function getAverageTodoSize($projectId): mixed
    {
        $result = $this->connection->table('zp_tickets')
            ->selectRaw('AVG(zp_tickets.storypoints) as '.$this->dbHelper->wrapColumn('avgSize'))
            ->where('zp_tickets.type', '<>', 'milestone')
            ->where('zp_tickets.storypoints', '<>', 0)
            ->whereNotNull('zp_tickets.storypoints')
            ->where('zp_tickets.projectId', $projectId)
            ->first();

        return $result->avgSize ?? null;
    }

    /**
     * addTicket - add a Ticket with postback test
     */
    public function addTicket(array $values): bool|int
    {
        $ticketId = $this->connection->table('zp_tickets')->insertGetId([
            'headline' => $values['headline'],
            'type' => $values['type'],
            'description' => $values['description'],
            'date' => $values['date'],
            'dateToFinish' => $values['dateToFinish'],
            'projectId' => $values['projectId'],
            'status' => $values['status'],
            'userId' => $values['userId'],
            'tags' => $values['tags'],
            'sprint' => $values['sprint'],
            'storypoints' => $values['storypoints'],
            'priority' => $values['priority'],
            'hourRemaining' => $values['hourRemaining'],
            'planHours' => $values['planHours'],
            'acceptanceCriteria' => $values['acceptanceCriteria'],
            'editFrom' => $values['editFrom'],
            'editTo' => $values['editTo'],
            'editorId' => $values['editorId'],
            'dependingTicketId' => $values['dependingTicketId'] ?? null,
            'milestoneid' => $values['milestoneid'] ?? null,
            'sortindex' => $values['sortIndex'] ?? null,
            'kanbanSortIndex' => 0,
            'modified' => dtHelper()->userNow()->formatDateTimeForDb(),
        ]);

        if ($ticketId !== false) {
            $ticketId = intval($ticketId);
            if (! empty($values['collaborators'])) {
                $this->addCollaborators($ticketId, $values['collaborators'], $values['userId']);
            }

            return $ticketId;
        }

        return false;
    }

    public function patchTicket($id, array $params): bool
    {
        $this->addTicketChange(session('userdata.id'), $id, $params);

        // Sanitize params to use only valid column names
        $updates = [];
        foreach ($params as $key => $value) {
            $sanitizedKey = DbCore::sanitizeToColumnString($key);
            $updates[$sanitizedKey] = $value;

            // send status update event
            if ($key == 'status') {
                static::dispatch_event('ticketStatusUpdate', ['ticketId' => $id, 'status' => $value, 'action' => 'ticketStatusUpdate']);
            }
        }

        $updates['modified'] = dtHelper()->userNow()->formatDateTimeForDb();

        return $this->connection->table('zp_tickets')
            ->where('id', $id)
            ->update($updates);
    }

    /**
     * updateTicket - Update Ticketinformation
     */
    public function updateTicket(array $values, $id): bool
    {
        $this->addTicketChange(session('userdata.id'), $id, $values);

        $result = $this->connection->table('zp_tickets')
            ->where('id', $id)
            ->update([
                'headline' => $values['headline'],
                'type' => $values['type'],
                'description' => $values['description'],
                'projectId' => $values['projectId'],
                'status' => $values['status'],
                'date' => $values['date'],
                'dateToFinish' => $values['dateToFinish'],
                'sprint' => $values['sprint'],
                'storypoints' => $values['storypoints'],
                'priority' => $values['priority'],
                'hourRemaining' => $values['hourRemaining'],
                'planHours' => $values['planHours'],
                'tags' => $values['tags'],
                'editorId' => $values['editorId'],
                'editFrom' => $values['editFrom'],
                'editTo' => $values['editTo'],
                'acceptanceCriteria' => $values['acceptanceCriteria'],
                'dependingTicketId' => $values['dependingTicketId'],
                'milestoneid' => $values['milestoneid'],
                'modified' => dtHelper()->userNow()->formatDateTimeForDb(),
            ]);

        $this->removeCollaborators($id);

        // Add new collaborators
        $this->addCollaborators($id, $values['collaborators'] ?? [], session('userdata.id'));

        return $result;
    }

    public function updateTicketStatus($ticketId, $status, int $ticketSorting = -1, $handler = null): bool
    {
        $this->addTicketChange(session('userdata.id'), $ticketId, ['status' => $status]);

        $updates = [
            'status' => $status,
            'modified' => dtHelper()->userNow()->formatDateTimeForDb(),
        ];

        if ($ticketSorting > -1) {
            $updates['kanbanSortIndex'] = $ticketSorting;
        }

        static::dispatch_event('ticketStatusUpdate', ['ticketId' => $ticketId, 'status' => $status, 'action' => 'ticketStatusUpdate', 'handler' => $handler]);

        return $this->connection->table('zp_tickets')
            ->where('id', $ticketId)
            ->update($updates);
    }

    /**
     * Records ticket field changes in the history table.
     * Uses a single batch insert instead of individual inserts per changed field.
     *
     * @param  int  $userId  The user making the change.
     * @param  int  $ticketId  The ticket being changed.
     * @param  array  $values  The new values being applied.
     */
    public function addTicketChange($userId, $ticketId, $values): void
    {
        if (empty($ticketId)) {
            return;
        }

        $fields = [
            'headline' => 'headline',
            'type' => 'type',
            'description' => 'description',
            'project' => 'projectId',
            'priority' => 'priority',
            'deadline' => 'dateToFinish',
            'editors' => 'editorId',
            'fromDate' => 'editFrom',
            'toDate' => 'editTo',
            'staging' => 'staging',
            'production' => 'production',
            'planHours' => 'planHours',
            'status' => 'status',
        ];

        // Only select the columns we actually need to compare, not the entire row
        $trackedColumns = array_unique(array_values($fields));
        $oldValues = $this->connection->table('zp_tickets')
            ->select($trackedColumns)
            ->where('id', $ticketId)
            ->first();

        if (! $oldValues) {
            return;
        }

        $oldValues = (array) $oldValues;
        $now = date('Y-m-d H:i:s');
        $historyRows = [];

        // Compare tracked fields
        foreach ($fields as $enum => $dbTable) {
            if (
                isset($values[$dbTable]) === true &&
                isset($oldValues[$dbTable]) === true &&
                ($oldValues[$dbTable] != $values[$dbTable]) &&
                ($values[$dbTable] != '')
            ) {
                $historyRows[] = [
                    'userId' => $userId,
                    'ticketId' => $ticketId,
                    'changeType' => $enum,
                    'changeValue' => $values[$dbTable],
                    'dateModified' => $now,
                ];
            }
        }

        // Single batch insert instead of N individual inserts
        if (! empty($historyRows)) {
            $this->connection->table('zp_tickethistory')->insert($historyRows);
        }
    }

    /**
     * Get all tasks (and optionally subtasks) that belong to a milestone
    /**
     * Get all tasks (and optionally subtasks) that belong to a milestone
     *
     * @param  int  $milestoneId  The milestone ID
     * @param  int|null  $projectId  The project ID (defaults to current project)
     * @return array Array of tickets
     */
    public function getTasksByMilestone(int $milestoneId, ?int $projectId = null): array
    {
        if ($projectId === null) {
            $projectId = session('currentProject');
        }

        $results = $this->connection->table('zp_tickets')
            ->where('milestoneid', $milestoneId)
            ->where('projectId', $projectId)
            ->orderBy('sortindex', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * Get all subtasks that have a parent task
     *
     * @param  int  $parentTicketId  The parent ticket ID
     * @return array Array of subtasks
     */
    public function getSubtasksByParent(int $parentTicketId): array
    {
        $results = $this->connection->table('zp_tickets')
            ->where('dependingTicketId', $parentTicketId)
            ->orderBy('sortindex', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * delTicket - delete a Ticket and all dependencies
     */
    public function delticket($id): bool
    {
        $this->connection->table('zp_tickets')
            ->where('id', $id)
            ->delete();

        return true;
    }

    /**
     * @return true
     */
    public function delMilestone($id): bool
    {
        // Clear milestoneid from tickets
        $this->connection->table('zp_tickets')
            ->where('milestoneid', $id)
            ->update([
                'milestoneid' => '',
                'modified' => dtHelper()->userNow()->formatDateTimeForDb(),
            ]);

        // Clear milestoneid from canvas items
        $this->connection->table('zp_canvas_items')
            ->where('milestoneid', $id)
            ->update(['milestoneid' => '']);

        // Delete the milestone
        $this->connection->table('zp_tickets')
            ->where('id', $id)
            ->delete();

        return true;
    }

    /**
     * Adds collaborators to a ticket.
     *
     * @param  int  $ticketId  The ID of the ticket.
     * @param  array  $collaborators  An array of user IDs to add as collaborators.
     * @param  int  $createdBy  The ID of the user adding the collaborators.
     * @return bool Returns true if the operation is successful.
     */
    /**
     * Adds collaborators to a ticket using a single batch insert.
     *
     * @param  int  $ticketId  The ID of the ticket.
     * @param  array  $collaborators  An array of user IDs to add as collaborators.
     * @param  int  $createdBy  The ID of the user adding the collaborators.
     * @return bool Returns true if the operation is successful.
     */
    public function addCollaborators(int $ticketId, array $collaborators, int $createdBy): bool
    {
        if (empty($collaborators)) {
            return true;
        }

        $now = now();
        $rows = array_map(fn ($userId) => [
            'entityA' => $ticketId,
            'entityAType' => 'Ticket',
            'entityB' => $userId,
            'entityBType' => 'User',
            'relationship' => EntityRelationshipEnum::Collaborator->value,
            'createdOn' => $now,
            'createdBy' => $createdBy,
        ], $collaborators);

        // Single batch insert instead of N individual inserts
        $this->connection->table('zp_entity_relationship')->insert($rows);

        return true;
    }

    /**
     * Retrieves all collaborators for a ticket.
     *
     * @param  int  $ticketId  The ID of the ticket.
     * @return array An array of user IDs who are collaborators.
     */
    public function getCollaborators(int $ticketId): array
    {
        return $this->connection->table('zp_entity_relationship')
            ->select('entityB AS userId')
            ->where('entityA', $ticketId)
            ->where('entityAType', 'Ticket')
            ->where('relationship', EntityRelationshipEnum::Collaborator->value)
            ->pluck('userId')
            ->toArray();
    }

    /**
     * Removes all collaborators from a ticket.
     *
     * @param  int  $ticketId  The ID of the ticket.
     * @return bool Returns true if the operation is successful.
     */
    public function removeCollaborators(int $ticketId): bool
    {
        return $this->connection->table('zp_entity_relationship')
            ->where('entityA', $ticketId)
            ->where('entityAType', 'Ticket')
            ->where('relationship', EntityRelationshipEnum::Collaborator->value)
            ->delete();
    }

    /**
     * Bulk update sortindex values for multiple tickets in a single transaction.
     *
     * @param  array<int, int>  $updates  Associative array of ticketId => sortIndex
     * @return bool True on success, false on failure
     */
    public function bulkUpdateSortIndex(array $updates): bool
    {
        if (empty($updates)) {
            return true;
        }

        try {
            $this->connection->beginTransaction();

            foreach ($updates as $ticketId => $sortIndex) {
                $this->connection->table('zp_tickets')
                    ->where('id', (int) $ticketId)
                    ->update([
                        'sortindex' => (int) $sortIndex,
                        'modified' => dtHelper()->userNow()->formatDateTimeForDb(),
                    ]);
            }

            $this->connection->commit();

            return true;
        } catch (\Exception $e) {
            $this->connection->rollBack();
            \Illuminate\Support\Facades\Log::error($e);

            return false;
        }
    }
}
