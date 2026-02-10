<?php

namespace Leantime\Domain\Projects\Repositories;

use DateInterval;
use DatePeriod;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Db\DatabaseHelper;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Events\DispatchesEvents as EventhelperCore;
use Leantime\Core\Support\Avatarcreator;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use SVG\SVG;

class Projects
{
    use EventhelperCore;

    public string $name = '';

    public int $id = 0; // WAS: '';

    public int $clientId = 0;

    public object $result; // WAS: = '';

    /**
     * @var array state for projects
     */
    public array $state = [0 => 'OPEN', 1 => 'CLOSED', null => 'OPEN'];

    private ConnectionInterface $connection;

    public function __construct(
        protected Environment $config,
        protected DbCore $db,
        protected Avatarcreator $avatarcreator,
        protected DatabaseHelper $dbHelper
    ) {
        $this->config = $config;
        $this->db = $db;
        $this->connection = $db->getConnection();
    }

    /**
     * getAll - get all projects open and closed
     */
    public function getAll(bool $showClosedProjects = false): array
    {
        $query = $this->connection->table('zp_projects as project')
            ->select([
                'project.id',
                'project.name',
                'project.clientId',
                'project.hourBudget',
                'project.dollarBudget',
                'project.state',
                'project.menuType',
                'project.type',
                'project.modified',
                'client.name as clientName',
                'client.id as clientId',
                'project.start',
                'project.end',
            ])
            ->leftJoin('zp_clients as client', 'project.clientId', '=', 'client.id');

        if ($showClosedProjects === false) {
            $query->where(function ($q) {
                $q->whereNull('project.state')
                    ->orWhere('project.state', '<>', -1);
            });
        }

        $query->groupBy([
            'project.id',
            'project.name',
            'project.clientId',
            'project.hourBudget',
            'project.dollarBudget',
            'project.state',
            'project.menuType',
            'project.type',
            'project.modified',
            'client.name',
            'client.id',
            'project.start',
            'project.end',
        ])
            ->orderBy('clientName')
            ->orderBy('project.name');

        $results = $query->get();

        // Get project IDs for fetching latest comment statuses
        $projectIds = $results->pluck('id')->toArray();

        // Fetch latest comment status per project using a separate query
        $latestComments = [];
        if (! empty($projectIds)) {
            $comments = $this->connection->table('zp_comment')
                ->select('moduleId', 'status', 'date')
                ->where('module', 'project')
                ->whereIn('moduleId', $projectIds)
                ->orderByDesc('date')
                ->get();

            // Group by moduleId and take only the first (latest) per project
            foreach ($comments as $comment) {
                if (! isset($latestComments[$comment->moduleId])) {
                    $latestComments[$comment->moduleId] = $comment->status;
                }
            }
        }

        // Merge comment status into results
        return $results->map(function ($item) use ($latestComments) {
            $arr = (array) $item;
            $arr['status'] = $latestComments[$arr['id']] ?? null;

            return $arr;
        })->toArray();
    }

    /**
     * Get projects by type
     *
     * @param  string  $type  The project type (project, strategy, program)
     * @return array Projects of the specified type
     */
    public function getProjectsByType(string $type): array
    {
        $query = $this->connection->table('zp_projects as project')
            ->select([
                'project.id',
                'project.name',
                'project.clientId',
                'project.hourBudget',
                'project.dollarBudget',
                'project.state',
                'project.menuType',
                'project.type',
                'project.modified',
                'client.name as clientName',
                'client.id as clientId',
                'project.start',
                'project.end',
            ])
            ->leftJoin('zp_clients as client', 'project.clientId', '=', 'client.id')
            ->where('project.type', $type)
            ->groupBy([
                'project.id',
                'project.name',
                'project.clientId',
                'project.hourBudget',
                'project.dollarBudget',
                'project.state',
                'project.menuType',
                'project.type',
                'project.modified',
                'client.name',
                'client.id',
                'project.start',
                'project.end',
            ])
            ->orderBy('clientName')
            ->orderBy('project.name');

        $results = $query->get();

        // Get project IDs for fetching latest comment statuses
        $projectIds = $results->pluck('id')->toArray();

        // Fetch latest comment status per project using a separate query
        $latestComments = [];
        if (! empty($projectIds)) {
            $comments = $this->connection->table('zp_comment')
                ->select('moduleId', 'status', 'date')
                ->where('module', 'project')
                ->whereIn('moduleId', $projectIds)
                ->orderByDesc('date')
                ->get();

            foreach ($comments as $comment) {
                if (! isset($latestComments[$comment->moduleId])) {
                    $latestComments[$comment->moduleId] = $comment->status;
                }
            }
        }

        // Merge comment status into results
        return $results->map(function ($item) use ($latestComments) {
            $arr = (array) $item;
            $arr['status'] = $latestComments[$arr['id']] ?? null;

            return $arr;
        })->toArray();
    }

    /**
     * Gets all users that have access to a project.
     * For direct access only set the teamOnly flag to true
     */
    public function getUsersAssignedToProject($id, $includeApiUsers = false): array|bool
    {
        $query = $this->connection->table('zp_relationuserproject')
            ->select([
                'zp_user.id',
                'zp_user.firstname',
                'zp_user.lastname',
                'zp_user.username',
                'zp_user.notifications',
                'zp_user.profileId',
                'zp_user.jobTitle',
                'zp_user.source',
                'zp_user.status',
                'zp_user.modified',
                'zp_user.role',
                'zp_relationuserproject.projectRole',
            ])
            ->distinct()
            ->leftJoin('zp_user', 'zp_relationuserproject.userId', '=', 'zp_user.id')
            ->leftJoin('zp_projects', 'zp_relationuserproject.projectId', '=', 'zp_projects.id')
            ->where('zp_relationuserproject.projectId', $id)
            ->whereNotNull('zp_user.id');

        if ($includeApiUsers === false) {
            $query->where(function ($q) {
                $q->whereNull('zp_user.source')
                    ->orWhere('zp_user.source', '<>', 'api');
            });
        }

        $query->orderBy('zp_user.lastname');

        $results = $query->get();

        // Post-process to use firstname if available, otherwise username
        return $results->map(function ($item) {
            $arr = (array) $item;
            $arr['firstname'] = $arr['firstname'] ?? $arr['username'];

            return $arr;
        })->toArray();
    }

    /**
     * Retrieves the relationship of users assigned to a specific project.
     *
     * @param  int  $id  The ID of the project.
     * @param  bool  $includeApiUsers  Flag to determine whether to include API users. Default is false.
     * @return array|bool Returns an array of users assigned to the project or false on failure.
     *
     * @Deprecated
     */
    public function getProjectUserRelation($id, $includeApiUsers = false): array|bool
    {
        return $this->getUsersAssignedToProject($id, $includeApiUsers);
    }

    public function getUserProjects(int $userId, string $projectStatus = 'all', ?int $clientId = null, string $accessStatus = 'assigned', string $projectTypes = 'all'): false|array
    {
        $query = $this->connection->table('zp_projects as project')
            ->select([
                'project.id',
                'project.name',
                'project.details',
                'project.clientId',
                'project.state',
                'project.hourBudget',
                'project.dollarBudget',
                'project.menuType',
                'project.type',
                'project.parent',
                'project.modified',
                'project.start',
                'project.end',
                'client.name as clientName',
                'client.id as clientId',
                'parent.id as parentId',
                'parent.name as parentName',
            ])
            ->selectRaw('CASE WHEN favorite.id IS NULL THEN false ELSE true END as "isFavorite"')
            ->leftJoin('zp_relationuserproject as relation', 'project.id', '=', 'relation.projectId')
            ->leftJoin('zp_projects as parent', 'parent.id', '=', 'project.parent')
            ->leftJoin('zp_clients as client', 'project.clientId', '=', 'client.id')
            ->leftJoin('zp_user as user', 'relation.userId', '=', 'user.id')
            ->leftJoin('zp_reactions as favorite', function ($join) use ($userId) {
                $join->on('project.id', '=', 'favorite.moduleId')
                    ->where('favorite.module', 'project')
                    ->where('favorite.reaction', 'favorite')
                    ->where('favorite.userId', $userId);
            })
            ->leftJoin('zp_user as requestingUser', function ($join) use ($userId) {
                $join->on('requestingUser.id', '=', $this->connection->raw((int) $userId));
            })
            ->where(function ($q) {
                $q->where('project.active', '>', -1)
                    ->orWhereNull('project.active');
            });

        // All Projects this user has access to
        if ($accessStatus == 'all') {
            $query->where(function ($q) use ($userId) {
                $q->where('relation.userId', $userId)
                    ->orWhere(function ($q2) {
                        $q2->where('project.psettings', 'clients')
                            ->whereColumn('project.clientId', 'requestingUser.clientId');
                    })
                    ->orWhere('project.psettings', 'all')
                    ->orWhere('requestingUser.role', '>=', 40);
            });
        } elseif ($accessStatus == 'clients') {
            $query->where(function ($q) use ($userId) {
                $q->where('relation.userId', $userId)
                    ->orWhere(function ($q2) {
                        $q2->where('project.psettings', 'clients')
                            ->whereColumn('project.clientId', 'requestingUser.clientId');
                    });
            });
        } else {
            $query->where('relation.userId', $userId);
        }

        if ($projectStatus == 'open') {
            $query->where(function ($q) {
                $q->where('project.state', '<>', -1)
                    ->orWhereNull('project.state');
            });
        } elseif ($projectStatus == 'closed') {
            $query->where('project.state', -1);
        }

        if ($clientId != '' && $clientId != null && $clientId > 0) {
            $query->where('project.clientId', $clientId);
        }

        if ($projectTypes != 'all' && $projectTypes != 'project') {
            $types = explode(',', $projectTypes);
            $query->whereIn('project.type', $types);
        }

        if ($projectTypes == 'project') {
            $query->where(function ($q) {
                $q->where('project.type', 'project')
                    ->orWhereNull('project.type');
            });
        }

        $query->groupBy([
            'project.id',
            'project.name',
            'project.details',
            'project.clientId',
            'project.state',
            'project.hourBudget',
            'project.dollarBudget',
            'project.menuType',
            'project.type',
            'project.parent',
            'project.modified',
            'project.start',
            'project.end',
            'client.name',
            'client.id',
            'parent.id',
            'parent.name',
            'favorite.id',
        ])
            ->orderBy('clientName')
            ->orderBy('project.name');

        $results = $query->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    // This populates the projects show all tab and shows users all the projects that they could access
    public function getProjectsUserHasAccessTo($userId, string $status = 'all', string $clientId = ''): false|array
    {
        $query = $this->connection->table('zp_projects as project')
            ->select([
                'project.id',
                'project.name',
                'project.clientId',
                'project.state',
                'project.hourBudget',
                'project.dollarBudget',
                'project.menuType',
                'project.type',
                'project.parent',
                'project.modified',
                'client.name as clientName',
                'client.id as clientId',
            ])
            ->selectRaw('CASE WHEN favorite.id IS NULL THEN false ELSE true END as "isFavorite"')
            ->leftJoin('zp_relationuserproject as relation', 'project.id', '=', 'relation.projectId')
            ->leftJoin('zp_clients as client', 'project.clientId', '=', 'client.id')
            ->leftJoin('zp_reactions as favorite', function ($join) use ($userId) {
                $join->on('project.id', '=', 'favorite.moduleId')
                    ->where('favorite.module', 'project')
                    ->where('favorite.reaction', 'favorite')
                    ->where('favorite.userId', $userId);
            })
            ->where(function ($q) use ($userId, $clientId) {
                $q->where('relation.userId', $userId)
                    ->orWhere('project.psettings', 'all')
                    ->orWhere(function ($q2) use ($clientId) {
                        $q2->where('project.psettings', 'clients')
                            ->where('project.clientId', $clientId);
                    });
            })
            ->where(function ($q) {
                $q->where('project.active', '>', -1)
                    ->orWhereNull('project.active');
            });

        if ($status == 'open') {
            $query->where(function ($q) {
                $q->where('project.state', '<>', -1)
                    ->orWhereNull('project.state');
            });
        } elseif ($status == 'closed') {
            $query->where('project.state', -1);
        }

        $query->groupBy([
            'project.id',
            'project.name',
            'project.clientId',
            'project.state',
            'project.hourBudget',
            'project.dollarBudget',
            'project.menuType',
            'project.type',
            'project.parent',
            'project.modified',
            'client.name',
            'client.id',
            'favorite.id',
        ])
            ->orderBy('clientName')
            ->orderBy('project.name');

        $results = $query->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * @return int|mixed
     */
    public function getNumberOfProjects($clientId = null, $type = null): mixed
    {
        $query = $this->connection->table('zp_projects')
            ->where('id', '>', 0);

        if ($clientId != null && is_numeric($clientId)) {
            $query->where('clientId', $clientId);
        }

        if ($type != null) {
            $query->where('type', $type);
        }

        return $query->count();
    }

    // Get all open user projects /param: open, closed, all

    public function getClientProjects($clientId): false|array
    {
        $results = $this->connection->table('zp_projects as project')
            ->select([
                'project.id',
                'project.name',
                'project.clientId',
                'project.hourBudget',
                'project.dollarBudget',
                'project.state',
                'project.menuType',
                'project.modified',
                'project.type',
                'client.name as clientName',
                'client.id as clientId',
            ])
            ->leftJoin('zp_clients as client', 'project.clientId', '=', 'client.id')
            ->where(function ($q) {
                $q->where('project.active', '>', -1)
                    ->orWhereNull('project.active');
            })
            ->where('clientId', $clientId)
            ->groupBy([
                'project.id',
                'project.name',
                'project.clientId',
                'project.hourBudget',
                'project.dollarBudget',
                'project.state',
                'project.menuType',
                'project.modified',
                'project.type',
                'client.name',
                'client.id',
            ])
            ->orderBy('clientName')
            ->orderBy('project.name')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function getProjectTickets($projectId): false|array
    {
        $results = $this->connection->table('zp_tickets')
            ->select([
                'zp_tickets.id',
                'zp_tickets.headline',
                'zp_tickets.editFrom',
                'zp_tickets.editTo',
                'zp_user.firstname',
                'zp_user.lastname',
            ])
            ->leftJoin('zp_user', function ($join) {
                $join->on('zp_tickets.editorId', '=', $this->connection->raw('CAST("zp_user"."id" AS CHAR)'));
            })
            ->where('projectId', $projectId)
            ->orderBy('zp_tickets.editFrom')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * getProject - get one project
     */
    public function getProject($id): array|bool
    {
        $userId = session('userdata.id');

        $result = $this->connection->table('zp_projects')
            ->select([
                'zp_projects.id',
                'zp_projects.name',
                'zp_projects.clientId',
                'zp_projects.details',
                'zp_projects.state',
                'zp_projects.hourBudget',
                'zp_projects.dollarBudget',
                'zp_projects.psettings',
                'zp_projects.menuType',
                'zp_projects.avatar',
                'zp_projects.cover',
                'zp_projects.type',
                'zp_projects.parent',
                'zp_projects.modified',
                'zp_clients.name as clientName',
                'zp_projects.start',
                'zp_projects.end',
            ])
            ->selectRaw('CASE WHEN favorite.id IS NULL THEN false ELSE true END as "isFavorite"')
            ->leftJoin('zp_clients', 'zp_projects.clientId', '=', 'zp_clients.id')
            ->leftJoin('zp_reactions as favorite', function ($join) use ($userId) {
                $join->on('zp_projects.id', '=', 'favorite.moduleId')
                    ->where('favorite.module', 'project')
                    ->where('favorite.reaction', 'favorite')
                    ->where('favorite.userId', $userId);
            })
            ->leftJoin('zp_user as requestingUser', function ($join) use ($userId) {
                $join->on('requestingUser.id', '=', $this->connection->raw((int) $userId));
            })
            ->where('zp_projects.id', $id)
            ->groupBy([
                'zp_projects.id',
                'zp_projects.name',
                'zp_projects.clientId',
                'zp_projects.details',
                'zp_projects.state',
                'zp_projects.hourBudget',
                'zp_projects.dollarBudget',
                'zp_projects.psettings',
                'zp_projects.menuType',
                'zp_projects.avatar',
                'zp_projects.cover',
                'zp_projects.type',
                'zp_projects.parent',
                'zp_projects.modified',
                'zp_clients.name',
                'zp_projects.start',
                'zp_projects.end',
                'favorite.id',
            ])
            ->limit(1)
            ->first();

        return $result ? (array) $result : false;
    }

    public function getProjectBookedHours($id): array|bool
    {
        $result = $this->connection->table('zp_tickets')
            ->selectRaw('"zp_tickets"."projectId", SUM(zp_timesheets.hours) AS "totalHours"')
            ->join('zp_timesheets', 'zp_timesheets.ticketId', '=', 'zp_tickets.id')
            ->where('projectId', $id)
            ->first();

        return $result ? (array) $result : false;
    }

    public function recursive_array_search($needle, $haystack): false|int|string
    {
        foreach ($haystack as $key => $value) {
            $current_key = $key;
            if ($needle === $value or (is_array($value) && $this->recursive_array_search($needle, $value) !== false)) {
                return $current_key;
            }
        }

        return false;
    }

    public function getProjectBookedHoursArray($id): array|bool
    {
        $dateFormatSql = match ($this->dbHelper->getDriverName()) {
            'mysql' => "DATE_FORMAT(zp_timesheets.\"workDate\", '%Y-%m-%d')",
            'pgsql' => "TO_CHAR(zp_timesheets.\"workDate\", 'YYYY-MM-DD')",
            default => "DATE_FORMAT(zp_timesheets.\"workDate\", '%Y-%m-%d')",
        };

        $results = $this->connection->table('zp_tickets')
            ->select([
                'zp_tickets.projectId',
            ])
            ->selectRaw('SUM(zp_timesheets.hours) AS "totalHours"')
            ->selectRaw("{$dateFormatSql} AS \"workDate\"")
            ->join('zp_timesheets', 'zp_timesheets.ticketId', '=', 'zp_tickets.id')
            ->where('projectId', $id)
            ->groupByRaw($dateFormatSql)
            ->orderBy('workDate')
            ->get();

        $results = array_map(fn ($item) => (array) $item, $results->toArray());

        $chartArr = [];

        if (count($results) > 0) {
            $begin = date_create($results[0]['workDate']);
            $begin->sub(new DateInterval('P1D'));

            $end = date_create($results[(count($results) - 1)]['workDate']);
            $end->add(new DateInterval('P1D'));

            $i = new DateInterval('P1D');

            $period = new DatePeriod($begin, $i, $end);

            $total = 0;

            foreach ($period as $d) {
                $day = $d->format('Y-m-d');
                $dayKey = $d->getTimestamp();

                $key = $this->recursive_array_search($day, $results);

                if ($key === false) {
                    $value = 0;
                } else {
                    $value = $results[$key]['totalHours'];
                }

                $total = $total + $value;
                $chartArr[$dayKey] = $total;
            }
        }

        return $chartArr;
    }

    public function getProjectBookedDollars($id): mixed
    {
        $result = $this->connection->table('zp_tickets')
            ->selectRaw('"zp_tickets"."projectId", SUM(zp_timesheets.hours * zp_timesheets.rate) AS "totalDollars"')
            ->join('zp_timesheets', 'zp_timesheets.ticketId', '=', 'zp_tickets.id')
            ->where('projectId', $id)
            ->first();

        return $result ? (array) $result : false;
    }

    /**
     * addProject - add a project to a client
     *
     * @return int|bool returns new project id on success, false on failure.
     */
    public function addProject(bool|array $values): int|bool
    {
        $startDate = null;
        if (isset($values['start']) && $values['start'] !== false && $values['start'] != '') {
            $startDate = $values['start'];
        }

        $endDate = null;
        if (isset($values['end']) && $values['end'] !== false && $values['end'] != '') {
            $endDate = $values['end'];
        }

        $projectId = $this->connection->table('zp_projects')->insertGetId([
            'name' => $values['name'],
            'details' => $values['details'],
            'clientId' => $values['clientId'],
            'hourBudget' => $values['hourBudget'],
            'dollarBudget' => $values['dollarBudget'],
            'psettings' => $values['psettings'],
            'menuType' => $values['menuType'] ?? '',
            'type' => $values['type'] ?? 'project',
            'parent' => $values['parent'] ?? null,
            'start' => $startDate,
            'end' => $endDate,
            'created' => date('Y-m-d H:i:s'),
            'modified' => date('Y-m-d H:i:s'),
        ]);

        // Add author to project
        if (session()->exists('userdata.id')) {
            $this->addProjectRelation(session('userdata.id'), $projectId, '');
        }

        // Add users to relation
        if (is_array($values['assignedUsers']) === true && count($values['assignedUsers']) > 0) {
            foreach ($values['assignedUsers'] as $user) {
                if (is_array($user) && isset($user['id']) && isset($user['projectRole'])) {
                    $this->addProjectRelation($user['id'], $projectId, $user['projectRole']);
                }
            }
        }

        return is_numeric($projectId) ? (int) $projectId : false;
    }

    /**
     * editProject - edit a project
     */
    public function editProject(array $values, $id): void
    {
        $oldProject = $this->getProject($id);

        $startDate = null;
        if (isset($values['start']) && $values['start'] !== false && $values['start'] != '') {
            $startDate = $values['start'];
        }

        $endDate = null;
        if (isset($values['end']) && $values['end'] !== false && $values['end'] != '') {
            $endDate = $values['end'];
        }

        $this->connection->table('zp_projects')
            ->where('id', $id)
            ->limit(1)
            ->update([
                'name' => $values['name'],
                'details' => $values['details'] ?? '',
                'clientId' => $values['clientId'] ?? '',
                'state' => $values['state'] ?? '',
                'hourBudget' => $values['hourBudget'] ?? '',
                'dollarBudget' => $values['dollarBudget'] ?? '',
                'psettings' => $values['psettings'] ?? '',
                'menuType' => $values['menuType'] ?? 'default',
                'type' => $values['type'] ?? 'project',
                'parent' => $values['parent'] ?? null,
                'start' => $startDate,
                'end' => $endDate,
                'modified' => date('Y-m-d H:i:s'),
            ]);

        static::dispatch_event('editProject', ['values' => $values, 'oldProject' => $oldProject]);
    }

    /**
     * editProject - edit a project
     */
    public function editProjectRelations(array $values, $projectId): void
    {

        $this->deleteAllUserRelations($projectId);

        // Add users to relation
        if (is_array($values['assignedUsers']) === true && count($values['assignedUsers']) > 0) {
            foreach ($values['assignedUsers'] as $userId) {
                $projectRole = null;
                if (isset($values['projectRoles']['userProjectRole-'.$userId]) && $values['projectRoles']['userProjectRole-'.$userId] != '40' && $values['projectRoles']['userProjectRole-'.$userId] != '50') {
                    $projectRole = (int) $values['projectRoles']['userProjectRole-'.$userId];
                }

                $this->addProjectRelation($userId, $projectId, $projectRole);
            }
        }
    }

    /**
     * deleteProject - delete a project
     */
    public function deleteProject($id): void
    {
        $this->connection->table('zp_projects')
            ->where('id', $id)
            ->limit(1)
            ->delete();

        $this->connection->table('zp_tickets')
            ->where('projectId', $id)
            ->delete();
    }

    /**
     * hasTickets - check if there are Tickets related to a project
     */
    public function hasTickets($id): bool
    {
        return $this->connection->table('zp_tickets')
            ->where('projectId', $id)
            ->where('zp_tickets.type', '<>', 'subtask')
            ->where('zp_tickets.type', '<>', 'milestone')
            ->exists();
    }

    /**
     * getUserProjectRelation - get all projects related to a user
     *
     * @param  null  $projectId
     */
    public function getUserProjectRelation($id, $projectId = null): array
    {
        $query = $this->connection->table('zp_relationuserproject')
            ->select([
                'zp_relationuserproject.userId',
                'zp_relationuserproject.projectId',
                'zp_projects.name',
                'zp_projects.modified',
                'zp_relationuserproject.projectRole',
            ])
            ->join('zp_projects', 'zp_relationuserproject.projectId', '=', 'zp_projects.id')
            ->where('userId', $id);

        if ($projectId != null) {
            $query->where('zp_projects.id', $projectId);
        }

        $results = $query->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * @throws BindingResolutionException
     */
    /**
     * @throws BindingResolutionException
     */
    public function isUserAssignedToProject($userId, $projectId): bool
    {

        $userRepo = app()->make(UserRepository::class);
        $user = $userRepo->getUser($userId);

        if ($user === false) {
            return false;
        }

        // admins owners and managers can access everything
        if (in_array(Roles::getRoleString($user['role']), [Roles::$admin, Roles::$owner])) {
            return true;
        }

        $project = $this->getProject($projectId);

        if ($project === false) {
            return false;
        }

        // Everyone in org is allowed to see the project
        if ($project['psettings'] == 'all') {
            return true;
        }

        // Everyone in client is allowed to see project
        if ($project['psettings'] === 'clients') {
            if ($user['clientId'] == $project['clientId']) {
                return true;
            }
        }

        // Select users are allowed to see project
        return $this->connection->table('zp_relationuserproject')
            ->join('zp_projects', 'zp_relationuserproject.projectId', '=', 'zp_projects.id')
            ->where('userId', $userId)
            ->where('zp_relationuserproject.projectId', $projectId)
            ->exists();
    }

    /**
     * @throws BindingResolutionException
     */
    /**
     * @throws BindingResolutionException
     */
    public function isUserMemberOfProject($userId, $projectId): bool
    {
        $userRepo = app()->make(UserRepository::class);
        $user = $userRepo->getUser($userId);

        if ($user === false) {
            return false;
        }

        // admins owners and managers can access everything

        $project = $this->getProject($projectId);

        if ($project === false) {
            return false;
        }

        // Select users are allowed to see project
        return $this->connection->table('zp_relationuserproject')
            ->join('zp_projects', 'zp_relationuserproject.projectId', '=', 'zp_projects.id')
            ->where('userId', $userId)
            ->where('zp_relationuserproject.projectId', $projectId)
            ->exists();
    }

    /**
     * getUserProjectRelation - get all projects related to a user
     */
    public function editUserProjectRelations($id, $projects): bool
    {
        $results = $this->connection->table('zp_relationuserproject')
            ->select(['id', 'userId', 'projectId', 'projectRole'])
            ->where('userId', $id)
            ->get();

        $values = array_map(fn ($item) => (array) $item, $results->toArray());

        // Add relations that don't exist
        foreach ($projects as $project) {
            $exists = false;
            if (count($values)) {
                foreach ($values as $value) {
                    if ($project == $value['projectId']) {
                        $exists = true;
                    }
                }
            }
            if (! $exists) {
                $this->addProjectRelation($id, $project, '');
            }
        }

        // Delete relations that were removed in select
        if (count($values)) {
            foreach ($values as $value) {
                if (in_array($value['projectId'], $projects) !== true) {
                    $this->deleteProjectRelation($id, $value['projectId']);
                }
            }
        }

        return true;
    }

    public function deleteProjectRelation($userId, $projectId): void
    {
        $this->connection->table('zp_relationuserproject')
            ->where('projectId', $projectId)
            ->where('userId', $userId)
            ->delete();
    }

    public function deleteAllProjectRelations($userId): void
    {
        $this->connection->table('zp_relationuserproject')
            ->where('userId', $userId)
            ->delete();
    }

    public function deleteAllUserRelations($projectId): void
    {
        $this->connection->table('zp_relationuserproject')
            ->where('projectId', $projectId)
            ->delete();
    }

    public function addProjectRelation($userId, $projectId, $projectRole): void
    {
        $oldProject = $this->getProject($projectId);

        $this->connection->table('zp_relationuserproject')->insert([
            'userId' => $userId,
            'projectId' => $projectId,
            'projectRole' => $projectRole,
        ]);

        static::dispatch_event('userAddedToProject', ['userId' => $userId, 'projectId' => $projectId, 'projectRole' => $projectRole, 'oldProject' => $oldProject]);
    }

    public function patch($id, $params): bool
    {
        unset($params['act']);

        $updateData = [];
        foreach ($params as $key => $value) {
            $updateData[DbCore::sanitizeToColumnString($key)] = $value;
        }

        $updateData['modified'] = date('Y-m-d H:i:s');

        return $this->connection->table('zp_projects')
            ->where('id', $id)
            ->limit(1)
            ->update($updateData) >= 0;
    }

    /**
     * setPicture - set the profile picture for an individual
     *
     * @throws BindingResolutionException
     */
    public function setPicture($fileId, $id): bool
    {
        return $this->connection->table('zp_projects')
            ->where('id', $id)
            ->update([
                'avatar' => $fileId,
                'modified' => date('Y-m-d H:i:s'),
            ]) >= 0;
    }

    /**
     * @return string[]|SVG
     *
     * @throws BindingResolutionException
     */
    /**
     * @return array|SVG
     *
     * @throws BindingResolutionException
     */
    public function getProjectAvatar($id): array|false
    {
        if ($id === false) {
            return false;
        }

        $result = $this->connection->table('zp_projects')
            ->select(['avatar', 'name'])
            ->where('id', $id)
            ->limit(1)
            ->first();

        return $result ? (array) $result : false;
    }
}
