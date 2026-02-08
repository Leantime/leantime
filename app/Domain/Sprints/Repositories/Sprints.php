<?php

namespace Leantime\Domain\Sprints\Repositories;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Sprints\Models\Sprints as SprintsModel;

class Sprints
{
    private ConnectionInterface $db;

    /**
     * __construct - get database connection
     */
    public function __construct(DbCore $db)
    {
        $this->db = $db->getConnection();
    }

    /**
     * getSprint - get single sprint
     */
    public function getSprint(int $id): SprintsModel|false
    {
        $result = $this->db->table('zp_sprints as sprint')
            ->select(
                'sprint.id',
                'sprint.name',
                'sprint.projectId',
                'sprint.startDate',
                'sprint.endDate',
                'sprint.modified'
            )
            ->where('sprint.id', $id)
            ->limit(1)
            ->first();

        if ($result === null) {
            return false;
        }

        $sprint = new SprintsModel;
        $sprint->id = $result->id;
        $sprint->name = $result->name;
        $sprint->projectId = $result->projectId;
        $sprint->startDate = $result->startDate;
        $sprint->endDate = $result->endDate;
        $sprint->modified = $result->modified;

        return $sprint;
    }

    /**
     * getAllSprints - get all sprints for a project
     */
    public function getAllSprints(?int $projectId = null): array
    {
        $query = $this->db->table('zp_sprints')
            ->select(
                'id',
                'name',
                'projectId',
                'startDate',
                'endDate',
                'modified'
            );

        if ($projectId !== null) {
            $query->where('projectId', $projectId);
        }

        $results = $query->orderBy('startDate', 'desc')->get();

        return $results->map(function ($row) {
            $sprint = new SprintsModel;
            $sprint->id = $row->id;
            $sprint->name = $row->name;
            $sprint->projectId = $row->projectId;
            $sprint->startDate = $row->startDate;
            $sprint->endDate = $row->endDate;
            $sprint->modified = $row->modified;

            return $sprint;
        })->toArray();
    }

    /**
     * getAllFutureSprints - get all future sprints for a project
     */
    public function getAllFutureSprints(int $projectId): array
    {
        $results = $this->db->table('zp_sprints')
            ->select(
                'id',
                'name',
                'projectId',
                'startDate',
                'endDate',
                'modified'
            )
            ->where('projectId', $projectId)
            ->where('endDate', '>', now())
            ->orderBy('startDate', 'desc')
            ->get();

        return $results->map(function ($row) {
            $sprint = new SprintsModel;
            $sprint->id = $row->id;
            $sprint->name = $row->name;
            $sprint->projectId = $row->projectId;
            $sprint->startDate = $row->startDate;
            $sprint->endDate = $row->endDate;
            $sprint->modified = $row->modified;

            return $sprint;
        })->toArray();
    }

    /**
     * getCurrentSprint - get current sprint for a project
     */
    public function getCurrentSprint(int $projectId): mixed
    {
        $result = $this->db->table('zp_sprints')
            ->select(
                'id',
                'name',
                'projectId',
                'startDate',
                'endDate',
                'modified'
            )
            ->where('projectId', $projectId)
            ->where('startDate', '<', now())
            ->where('endDate', '>', now())
            ->orderBy('startDate')
            ->limit(1)
            ->first();

        if ($result === null) {
            return false;
        }

        $sprint = new SprintsModel;
        $sprint->id = $result->id;
        $sprint->name = $result->name;
        $sprint->projectId = $result->projectId;
        $sprint->startDate = $result->startDate;
        $sprint->endDate = $result->endDate;
        $sprint->modified = $result->modified;

        return $sprint;
    }

    /**
     * getUpcomingSprint - gets the next upcoming sprint
     */
    public function getUpcomingSprint(int $projectId): SprintsModel|false
    {
        $result = $this->db->table('zp_sprints')
            ->select(
                'id',
                'name',
                'projectId',
                'startDate',
                'endDate',
                'modified'
            )
            ->where('projectId', $projectId)
            ->where('startDate', '>', now())
            ->orderBy('startDate', 'asc')
            ->limit(1)
            ->first();

        if ($result === null) {
            return false;
        }

        $sprint = new SprintsModel;
        $sprint->id = $result->id;
        $sprint->name = $result->name;
        $sprint->projectId = $result->projectId;
        $sprint->startDate = $result->startDate;
        $sprint->endDate = $result->endDate;
        $sprint->modified = $result->modified;

        return $sprint;
    }

    public function addSprint(SprintsModel $sprint): bool|int
    {
        $id = $this->db->table('zp_sprints')->insertGetId([
            'name' => $sprint->name,
            'projectId' => $sprint->projectId,
            'startDate' => $sprint->startDate,
            'endDate' => $sprint->endDate,
            'modified' => now(),
        ]);

        return $id ?: false;
    }

    public function editSprint(SprintsModel $sprint): bool
    {
        return $this->db->table('zp_sprints')
            ->where('id', $sprint->id)
            ->update([
                'name' => $sprint->name,
                'projectId' => $sprint->projectId,
                'startDate' => $sprint->startDate,
                'endDate' => $sprint->endDate,
                'modified' => now(),
            ]) >= 0;
    }

    public function delSprint(int|string $id): void
    {
        // Clear sprint from tickets
        $this->db->table('zp_tickets')
            ->where('sprint', $id)
            ->update(['sprint' => null]);

        // Delete the sprint
        $this->db->table('zp_sprints')
            ->where('id', $id)
            ->delete();
    }
}
