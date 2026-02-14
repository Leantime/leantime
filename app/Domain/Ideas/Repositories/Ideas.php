<?php

namespace Leantime\Domain\Ideas\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Tickets\Repositories\Tickets;

class Ideas
{
    public ?object $result = null;

    public ?object $tickets = null;

    private ConnectionInterface $db;

    public array $canvasTypes = [
        'idea' => 'status.ideation',
        'research' => 'status.discovery',
        'prototype' => 'status.delivering',
        'validation' => 'status.inreview',
        'implemented' => 'status.accepted',
        'deferred' => 'status.deferred',
    ];

    public array $statusClasses = ['idea' => 'label-info', 'validation' => 'label-warning', 'prototype' => 'label-warning', 'research' => 'label-warning', 'implemented' => 'label-success', 'deferred' => 'label-default'];

    private LanguageCore $language;

    private Tickets $ticketRepo;

    /**
     * __construct - get db connection
     *
     * @return void
     */
    public function __construct(DbCore $db, LanguageCore $language, Tickets $ticketRepo)
    {
        $this->db = $db->getConnection();
        $this->language = $language;
        $this->ticketRepo = $ticketRepo;
    }

    public function getSingleCanvas(int $canvasId): false|array
    {
        $results = $this->db->table('zp_canvas')
            ->select(
                'zp_canvas.id',
                'zp_canvas.title',
                'zp_canvas.author',
                'zp_canvas.created',
                'zp_canvas.projectId',
                't1.firstname AS authorFirstname',
                't1.lastname AS authorLastname'
            )
            ->leftJoin('zp_user AS t1', 'zp_canvas.author', '=', 't1.id')
            ->where('type', 'idea')
            ->where('zp_canvas.id', $canvasId)
            ->orderBy('zp_canvas.title')
            ->orderBy('zp_canvas.created')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * @return array|mixed
     */
    public function getCanvasLabels(): mixed
    {
        if (session()->exists('projectsettings.idealabels')) {
            return session('projectsettings.idealabels');
        } else {
            $result = $this->db->table('zp_settings')
                ->select('value')
                ->where('key', 'projectsettings.'.session('currentProject').'.idealabels')
                ->limit(1)
                ->first();

            $labels = [];

            // preseed state labels with default values
            foreach ($this->canvasTypes as $key => $label) {
                $labels[$key] = [
                    'name' => $this->language->__($label),
                    'class' => $this->statusClasses[$key],
                ];
            }

            if ($result !== null) {
                foreach (unserialize($result->value) as $key => $label) {
                    $labels[$key] = [
                        'name' => $label,
                        'class' => $this->statusClasses[$key],
                    ];
                }
            }

            session(['projectsettings.idealabels' => $labels]);

            return $labels;
        }
    }

    public function getAllCanvas(int $projectId): false|array
    {
        $results = $this->db->table('zp_canvas')
            ->select(
                'zp_canvas.id',
                'zp_canvas.title',
                'zp_canvas.author',
                'zp_canvas.created',
                't1.firstname AS authorFirstname',
                't1.lastname AS authorLastname'
            )
            ->leftJoin('zp_user AS t1', 'zp_canvas.author', '=', 't1.id')
            ->where('type', 'idea')
            ->where('projectId', $projectId)
            ->orderBy('zp_canvas.title')
            ->orderBy('zp_canvas.created')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function deleteCanvas(int $id): void
    {
        $this->db->table('zp_canvas_items')
            ->where('canvasId', $id)
            ->delete();

        $this->db->table('zp_canvas')
            ->where('id', $id)
            ->limit(1)
            ->delete();
    }

    public function addCanvas(array $values): false|string
    {
        $id = $this->db->table('zp_canvas')->insertGetId([
            'title' => $values['title'],
            'author' => $values['author'],
            'created' => now(),
            'type' => 'idea',
            'projectId' => $values['projectId'],
        ]);

        return (string) $id;
    }

    public function updateCanvas(array $values): mixed
    {
        return $this->db->table('zp_canvas')
            ->where('id', $values['id'])
            ->update(['title' => $values['title']]);
    }

    public function editCanvasItem(array $values): void
    {
        $this->db->table('zp_canvas_items')
            ->where('id', $values['itemId'])
            ->limit(1)
            ->update([
                'description' => $values['description'],
                'assumptions' => $values['assumptions'],
                'data' => $values['data'],
                'conclusion' => $values['conclusion'],
                'modified' => now(),
                'status' => $values['status'],
                'milestoneId' => $values['milestoneId'],
                'tags' => $values['tags'],
            ]);
    }

    public function patchCanvasItem(int $id, array $params): bool
    {
        if (isset($params['act'])) {
            unset($params['act']);
        }

        $updateData = [];
        foreach ($params as $key => $value) {
            $sanitizedKey = DbCore::sanitizeToColumnString($key);
            $updateData[$sanitizedKey] = $value;
        }

        return $this->db->table('zp_canvas_items')
            ->where('id', $id)
            ->limit(1)
            ->update($updateData) >= 0;
    }

    public function updateIdeaSorting(array $sortingArray): bool
    {
        foreach ($sortingArray as $idea) {
            $this->db->table('zp_canvas_items')
                ->updateOrInsert(
                    ['id' => (int) $idea['id']],
                    ['sortindex' => (int) $idea['sortIndex']]
                );
        }

        return true;
    }

    public function getCanvasItemsById(int $id): false|array
    {
        $results = $this->db->table('zp_canvas_items')
            ->select(
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
                'zp_canvas_items.milestoneId',
                't1.firstname AS authorFirstname',
                't1.lastname AS authorLastname',
                't1.profileId AS authorProfileId',
                'milestone.headline as milestoneHeadline',
                'milestone.editTo as milestoneEditTo'
            )
            ->selectRaw("CASE WHEN zp_canvas_items.status IS NULL THEN 'idea' ELSE zp_canvas_items.status END as status")
            ->selectRaw('COUNT(DISTINCT zp_comment.id) AS "commentCount"')
            ->leftJoin('zp_user AS t1', 'zp_canvas_items.author', '=', 't1.id')
            ->leftJoin('zp_tickets AS milestone', function ($join) {
                $join->on('zp_canvas_items.milestoneId', '=', $this->db->raw('CAST("milestone"."id" AS CHAR)'));
            })
            ->leftJoin('zp_comment', function ($join) {
                $join->on('zp_canvas_items.id', '=', 'zp_comment.moduleId')
                    ->where('zp_comment.module', '=', 'idea');
            })
            ->where('zp_canvas_items.canvasId', $id)
            ->groupBy(
                'zp_canvas_items.id',
                't1.firstname',
                't1.lastname',
                't1.profileId',
                'milestone.headline',
                'milestone.editTo',
                'zp_canvas_items.status'
            )
            ->orderBy('zp_canvas_items.sortindex')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function getSingleCanvasItem(int $id): mixed
    {
        $result = $this->db->table('zp_canvas_items')
            ->select(
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
                'zp_canvas_items.tags',
                'zp_canvas_items.milestoneId',
                't1.firstname AS authorFirstname',
                't1.lastname AS authorLastname',
                'milestone.headline as milestoneHeadline',
                'milestone.editTo as milestoneEditTo'
            )
            ->leftJoin('zp_tickets AS milestone', function ($join) {
                $join->on('zp_canvas_items.milestoneId', '=', $this->db->raw('CAST("milestone"."id" AS CHAR)'));
            })
            ->leftJoin('zp_user AS t1', 'zp_canvas_items.author', '=', 't1.id')
            ->where('zp_canvas_items.id', $id)
            ->first();

        return $result ? (array) $result : false;
    }

    public function addCanvasItem(array $values): false|string
    {
        $id = $this->db->table('zp_canvas_items')->insertGetId([
            'description' => $values['description'],
            'assumptions' => $values['assumptions'] ?? '',
            'data' => $values['data'] ?? '',
            'conclusion' => $values['conclusion'] ?? '',
            'box' => $values['box'] ?? 'idea',
            'author' => $values['author'] ?? session('userdata.id'),
            'created' => now(),
            'modified' => now(),
            'canvasId' => $values['canvasId'],
            'status' => $values['status'] ?? '',
            'milestoneId' => $values['milestoneId'] ?? '',
        ]);

        return (string) $id;
    }

    public function delCanvasItem(int $id): void
    {
        $this->db->table('zp_canvas_items')
            ->where('id', $id)
            ->limit(1)
            ->delete();
    }

    public function updateIdeaStatus(int $ideaId, string $status): bool
    {
        return $this->db->table('zp_canvas_items')
            ->where('id', $ideaId)
            ->limit(1)
            ->update(['box' => $status]) >= 0;
    }

    /**
     * @return int|mixed
     */
    public function getNumberOfIdeas(?int $projectId = null): mixed
    {
        $query = $this->db->table('zp_canvas_items')
            ->leftJoin('zp_canvas AS canvasBoard', 'zp_canvas_items.canvasId', '=', 'canvasBoard.id')
            ->where('canvasBoard.type', 'idea');

        if ($projectId !== null) {
            $query->where('canvasBoard.projectId', $projectId);
        }

        return $query->count('zp_canvas_items.id');
    }

    /**
     * @return int|mixed
     */
    public function getNumberOfBoards(?int $projectId = null): mixed
    {
        $query = $this->db->table('zp_canvas')
            ->where('type', 'idea');

        if ($projectId !== null) {
            $query->where('projectId', $projectId);
        }

        return $query->count();
    }

    public function bulkUpdateIdeaStatus(array $params): bool
    {
        // Jquery sortable serializes the array for kanban in format
        // statusKey: item[]=X&item[]=X2...,
        // statusKey2: item[]=X&item[]=X2...,
        // This represents status & kanban sorting
        foreach ($params as $status => $ideaList) {
            $ideas = explode('&', $ideaList);

            if (is_array($ideas) === true && count($ideas) > 0) {
                foreach ($ideas as $key => $ideaString) {
                    if (strlen($ideaString) > 0) {
                        $id = substr($ideaString, 7);

                        if ($this->updateIdeaStatus((int) $id, $status) === false) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    public function getAllIdeas(?int $projectId, ?int $boardId): array|false
    {
        $userId = session('userdata.id') ?? -1;
        $clientId = session('userdata.clientId') ?? -1;
        $requesterRole = session()->exists('userdata') ? session('userdata.role') : -1;

        $query = $this->db->table('zp_canvas_items')
            ->select(
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
                'zp_canvas_items.tags',
                'zp_canvas_items.milestoneId',
                'zp_canvas.projectId'
            )
            ->leftJoin('zp_canvas', 'zp_canvas_items.canvasId', '=', 'zp_canvas.id')
            ->leftJoin('zp_projects', 'zp_canvas.projectId', '=', 'zp_projects.id')
            ->where('zp_canvas_items.box', 'idea')
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
}
