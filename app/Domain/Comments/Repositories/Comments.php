<?php

namespace Leantime\Domain\Comments\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;

class Comments
{
    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    public function getComments(string $module, int $moduleId, int $parent = 0, string $orderByState = '0'): false|array
    {
        $orderBy = $orderByState == '1' ? 'asc' : 'desc';

        $query = $this->db->table('zp_comment as comment')
            ->select(
                'comment.id',
                'comment.text',
                'comment.date',
                'comment.moduleId',
                'comment.userId',
                'comment.commentParent',
                'comment.status',
                'user.firstname',
                'user.lastname',
                'user.profileId',
                'user.modified AS userModified'
            )
            ->addSelect('comment.date AS rawDate')
            ->join('zp_user as user', 'comment.userId', '=', 'user.id')
            ->where('comment.moduleId', $moduleId)
            ->where('comment.module', $module);

        if ($parent >= 0) {
            $query->where('comment.commentParent', $parent);
        }

        $results = $query->orderBy('comment.date', $orderBy)->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * @return int|mixed
     */
    public function countComments(?string $module = null, ?int $moduleId = null): mixed
    {
        $query = $this->db->table('zp_comment as comment');

        if ($module !== null) {
            $query->where('module', $module);
        }

        if ($moduleId !== null) {
            $query->where('moduleId', $moduleId);
        }

        return $query->count();
    }

    public function getReplies(int $id): false|array
    {
        $results = $this->db->table('zp_comment as comment')
            ->select(
                'comment.id',
                'comment.text',
                'comment.date',
                'comment.moduleId',
                'comment.userId',
                'comment.commentParent',
                'user.firstname',
                'user.lastname',
                'user.profileId',
                'user.modified AS userModified'
            )
            ->join('zp_user as user', 'comment.userId', '=', 'user.id')
            ->where('comment.commentParent', $id)
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function getComment(int $id): void
    {
        $this->db->table('zp_comment as comment')
            ->select(
                'comment.id',
                'comment.text',
                'comment.date',
                'comment.moduleId',
                'comment.userId',
                'comment.commentParent',
                'comment.status',
                'user.firstname',
                'user.lastname'
            )
            ->join('zp_user as user', 'comment.userId', '=', 'user.id')
            ->where('comment.id', $id)
            ->first();
    }

    public function addComment(array $values, string $module): false|string
    {
        $id = $this->db->table('zp_comment')->insertGetId([
            'text' => $values['text'],
            'userId' => $values['userId'],
            'date' => $values['date'],
            'moduleId' => $values['moduleId'],
            'module' => $module,
            'commentParent' => $values['commentParent'],
            'status' => $values['status'] ?? '',
        ]);

        return $id ? (string) $id : false;
    }

    public function deleteComment(int $id): bool
    {
        return $this->db->table('zp_comment')
            ->where('id', $id)
            ->delete() > 0;
    }

    public function editComment(string $text, int $id): bool
    {
        return $this->db->table('zp_comment')
            ->where('id', $id)
            ->update(['text' => $text]) >= 0;
    }

    public function getAllAccountComments(?int $projectId, ?int $moduleId): array|false
    {
        $userId = session('userdata.id') ?? -1;
        $clientId = session('userdata.clientId') ?? -1;
        $requesterRole = session()->exists('userdata') ? session('userdata.role') : -1;

        $query = $this->db->table('zp_comment as comment')
            ->select(
                'comment.id',
                'comment.module',
                'comment.text',
                'comment.date',
                'comment.moduleId',
                'comment.userId',
                'comment.commentParent',
                'comment.status',
                'zp_projects.id AS projectId'
            )
            ->leftJoin('zp_tickets', 'comment.moduleId', '=', 'zp_tickets.id')
            ->leftJoin('zp_canvas_items', 'comment.moduleId', '=', 'zp_canvas_items.id')
            ->leftJoin('zp_canvas', 'zp_canvas.id', '=', 'zp_canvas_items.canvasId')
            ->leftJoin('zp_projects', function ($join) {
                $join->on('zp_canvas.projectId', '=', 'zp_projects.id')
                    ->orOn('zp_tickets.projectId', '=', 'zp_projects.id');
            })
            ->where(function ($q) use ($userId, $clientId, $requesterRole) {
                $q->whereIn('zp_projects.id', function ($subquery) use ($userId) {
                    $subquery->select('projectId')
                        ->from('zp_relationuserproject')
                        ->where('userId', $userId);
                })
                    ->orWhere('zp_projects.psettings', 'all')
                    ->orWhere(function ($q2) use ($clientId) {
                        $q2->where('zp_projects.psettings', 'clients')
                            ->where('zp_projects.clientId', $clientId);
                    })
                    ->orWhere(function ($q3) use ($requesterRole) {
                        if (in_array($requesterRole, ['admin', 'manager'])) {
                            $q3->whereRaw('1=1');
                        }
                    });
            });

        if (isset($projectId) && $projectId > 0) {
            $query->where('zp_projects.id', $projectId);
        }

        if (isset($moduleId) && $moduleId > 0) {
            $query->where('comment.moduleId', $moduleId);
        }

        $results = $query->groupBy('comment.id')->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }
}
