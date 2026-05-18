<?php

namespace Leantime\Domain\ClientPortal\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\ClientPortal\Models\ClientRequest;
use Leantime\Domain\ClientPortal\Models\ClientRequestResponse;

/**
 * ClientPortal repository — all DB access for the client-facing portal.
 */
class ClientPortal
{
    private ConnectionInterface $db;

    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    /**
     * Get all active projects belonging to the client org that the given user is assigned to.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getProjectsForClient(int $userId): array
    {
        $clientId = $this->getUserClientId($userId);

        if ($clientId === 0) {
            return [];
        }

        $rows = $this->db->table('zp_projects as p')
            ->select('p.id', 'p.name', 'p.clientId', 'p.details', 'p.modified')
            ->where('p.clientId', $clientId)
            ->where(function ($q) {
                $q->where('p.active', '>', -1)->orWhereNull('p.active');
            })
            ->orderBy('p.name')
            ->get();

        return array_map(fn ($r) => (array) $r, $rows->toArray());
    }

    /**
     * Get the clientId (org) for a given user.
     */
    public function getUserClientId(int $userId): int
    {
        $row = $this->db->table('zp_user')->select('clientId')->where('id', $userId)->first();

        return $row ? (int) $row->clientId : 0;
    }

    /**
     * Check whether a project belongs to a given client org.
     */
    public function isProjectInClientOrg(int $clientId, int $projectId): bool
    {
        return $this->db->table('zp_projects')
            ->where('id', $projectId)
            ->where('clientId', $clientId)
            ->exists();
    }

    /**
     * Get a single project by ID.
     *
     * @return array<string, mixed>|null
     */
    public function getProject(int $projectId): ?array
    {
        $row = $this->db->table('zp_projects')
            ->select('id', 'name', 'clientId', 'details')
            ->where('id', $projectId)
            ->first();

        return $row ? (array) $row : null;
    }

    /**
     * Count total and completed (status=0) tickets for a project.
     *
     * @return array{total: int, done: int}
     */
    public function getProjectProgress(int $projectId): array
    {
        $base = $this->db->table('zp_tickets')
            ->where('projectId', $projectId)
            ->where('type', '!=', 'milestone')
            ->where('type', '!=', 'subtask')
            ->where('status', '!=', -1);

        $total = (clone $base)->count();
        $done = (clone $base)->where('status', 0)->count();

        return ['total' => $total, 'done' => $done];
    }

    /**
     * Get milestones for a project, ordered by due date.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMilestones(int $projectId): array
    {
        $rows = $this->db->table('zp_tickets')
            ->select('id', 'headline', 'status', 'editTo', 'description')
            ->where('projectId', $projectId)
            ->where('type', 'milestone')
            ->orderBy('editTo')
            ->get();

        return array_map(fn ($r) => (array) $r, $rows->toArray());
    }

    /**
     * Get users assigned to a project with teamlead or manager role (team contacts).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTeamContacts(int $projectId): array
    {
        // Role values in zp_user are numeric: 25 = teamlead, 30 = manager.
        // The template wants a string label, so we map the int here at the
        // DB → app boundary instead of leaking the integer to view code.
        $rows = $this->db->table('zp_relationuserproject as pr')
            ->select('u.id', 'u.firstname', 'u.lastname', 'u.role', 'u.profileId')
            ->join('zp_user as u', 'u.id', '=', 'pr.userId')
            ->where('pr.projectId', $projectId)
            ->whereIn('u.role', [25, 30])
            ->get();

        return array_map(function ($r) {
            $row = (array) $r;
            $row['role'] = (int) ($row['role'] ?? 0) === 25 ? 'teamlead' : 'manager';

            return $row;
        }, $rows->toArray());
    }

    /**
     * Get all requests submitted by a client user, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRequestsForUser(int $userId): array
    {
        $rows = $this->db->table('zp_client_requests as r')
            ->select('r.*', 'p.name as projectName')
            ->leftJoin('zp_projects as p', 'p.id', '=', 'r.projectId')
            ->where('r.clientUserId', $userId)
            ->orderByDesc('r.createdAt')
            ->get();

        return array_map(fn ($r) => (array) $r, $rows->toArray());
    }

    /**
     * Get all requests for a specific project, newest first.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRequestsForProject(int $projectId): array
    {
        $rows = $this->db->table('zp_client_requests as r')
            ->select('r.*', 'u.firstname', 'u.lastname')
            ->leftJoin('zp_user as u', 'u.id', '=', 'r.clientUserId')
            ->where('r.projectId', $projectId)
            ->orderByDesc('r.createdAt')
            ->get();

        return array_map(fn ($r) => (array) $r, $rows->toArray());
    }

    /**
     * Get a single request by ID.
     *
     * @return array<string, mixed>|null
     */
    public function getRequest(int $id): ?array
    {
        $row = $this->db->table('zp_client_requests')->where('id', $id)->first();

        return $row ? (array) $row : null;
    }

    /**
     * Insert a new client request. Returns the new ID.
     */
    public function createRequest(ClientRequest $req): int
    {
        return (int) $this->db->table('zp_client_requests')->insertGetId([
            'projectId' => $req->projectId,
            'clientUserId' => $req->clientUserId,
            'title' => $req->title,
            'description' => $req->description,
            'filePath' => $req->filePath,
            'status' => 'open',
            'createdAt' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get the latest response for a request (most recent createdAt).
     *
     * @return array<string, mixed>|null
     */
    public function getResponseForRequest(int $requestId): ?array
    {
        $row = $this->db->table('zp_client_request_responses as rr')
            ->select('rr.*', 'u.firstname', 'u.lastname')
            ->leftJoin('zp_user as u', 'u.id', '=', 'rr.respondedByUserId')
            ->where('rr.requestId', $requestId)
            ->orderByDesc('rr.createdAt')
            ->orderByDesc('rr.id')
            ->first();

        return $row ? (array) $row : null;
    }

    /**
     * Get all responses for a request, oldest first (full thread history).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getResponsesForRequest(int $requestId): array
    {
        $rows = $this->db->table('zp_client_request_responses as rr')
            ->select('rr.*', 'u.firstname', 'u.lastname')
            ->leftJoin('zp_user as u', 'u.id', '=', 'rr.respondedByUserId')
            ->where('rr.requestId', $requestId)
            ->orderBy('rr.createdAt')
            ->orderBy('rr.id')
            ->get();

        return array_map(fn ($r) => (array) $r, $rows->toArray());
    }

    /**
     * Record a client's review decision on a request and set the resulting request status.
     * Accept and Reject are terminal (closed) states; only "changes_requested" re-opens
     * the request so the TL/CM can submit a revised response.
     */
    public function recordClientReview(int $requestId, string $action, ?string $reason): bool
    {
        $newStatus = match ($action) {
            'accepted' => 'accepted',
            'rejected' => 'rejected',
            'changes_requested' => 'open',
            default => null,
        };

        if ($newStatus === null) {
            return false;
        }

        return $this->db->table('zp_client_requests')
            ->where('id', $requestId)
            ->update([
                'clientReviewAction' => $action,
                'clientReviewReason' => $reason,
                'clientReviewedAt' => date('Y-m-d H:i:s'),
                'status' => $newStatus,
            ]) > 0;
    }

    /**
     * Insert a TL/CM response. Returns the new ID.
     */
    public function createResponse(ClientRequestResponse $resp): int
    {
        return (int) $this->db->table('zp_client_request_responses')->insertGetId([
            'requestId' => $resp->requestId,
            'respondedByUserId' => $resp->respondedByUserId,
            'driveLink' => $resp->driveLink,
            'documentPath' => $resp->documentPath,
            'notes' => $resp->notes,
            'createdAt' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Mark a request as reviewed. Returns true when a matching row was updated.
     */
    public function markRequestReviewed(int $requestId): bool
    {
        return $this->db->table('zp_client_requests')
            ->where('id', $requestId)
            ->update(['status' => 'reviewed']) > 0;
    }

    /**
     * Check whether a user is directly assigned to a project.
     */
    public function isUserAssignedToProject(int $userId, int $projectId): bool
    {
        return $this->db->table('zp_relationuserproject')
            ->where('userId', $userId)
            ->where('projectId', $projectId)
            ->exists();
    }

    /**
     * Get all requests across all projects, optionally restricted to projects a user is on.
     * Includes submitter name and project name.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllRequests(int $userId, bool $isAdmin, int $filterProjectId = 0): array
    {
        $query = $this->db->table('zp_client_requests as r')
            ->select('r.*', 'u.firstname', 'u.lastname', 'p.name as projectName')
            ->leftJoin('zp_user as u', 'u.id', '=', 'r.clientUserId')
            ->leftJoin('zp_projects as p', 'p.id', '=', 'r.projectId')
            ->orderByDesc('r.createdAt');

        if ($filterProjectId > 0) {
            $query->where('r.projectId', $filterProjectId);
        }

        if (! $isAdmin) {
            // TL/CM: only show requests for projects they are assigned to
            $query->whereExists(function ($sub) use ($userId) {
                $sub->select($this->db->raw(1))
                    ->from('zp_relationuserproject as rp')
                    ->whereColumn('rp.projectId', 'r.projectId')
                    ->where('rp.userId', $userId);
            });
        }

        return array_map(fn ($r) => (array) $r, $query->get()->toArray());
    }

    /**
     * Get all TL/CM user IDs assigned to a project (for notifications).
     *
     * @return int[]
     */
    public function getTeamContactIds(int $projectId): array
    {
        // Role values in zp_user are numeric: 25 = teamlead, 30 = manager
        $rows = $this->db->table('zp_relationuserproject as pr')
            ->select('u.id')
            ->join('zp_user as u', 'u.id', '=', 'pr.userId')
            ->where('pr.projectId', $projectId)
            ->whereIn('u.role', [25, 30])
            ->get();

        return array_map(fn ($r) => (int) $r->id, $rows->toArray());
    }
}
