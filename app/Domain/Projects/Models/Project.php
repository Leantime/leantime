<?php

namespace Leantime\Domain\Projects\Models;

use DateTime;

/**
 * Project Model
 *
 * @property int|string $id
 * @property string $name
 * @property string|null $projectKey
 * @property int|string|null $clientId
 * @property DateTime|string|null $start
 * @property DateTime|string|null $end
 */
class Project
{
    public int|string $id;

    public $name;

    public null|string $projectKey;

    public null|int|string $clientId;

    public $start;

    public $end;

    public int|string $projectId;

    public $type;

    public $state;

    public $menuType;

    public $numberOfTickets;

    public $sortIndex;

    public $progress;

    public $milestones;

    public $lastUpdate;

    public $report;

    public $status;

    public $clientName;

    public $isFavorite;

    /**
     * Create a new Project instance from array data
     *
     * @param  array|null  $data  Array of project data
     */
    public function __construct(?array $data = null)
    {
        if ($data === null) {
            return;
        }

        // Map array data to object properties
        $this->id = $data['id'] ?? 0;
        $this->name = $data['name'] ?? '';
        $this->projectKey = $data['projectKey'] ?? null;
        $this->clientId = $data['clientId'] ?? null;
        $this->projectId = $data['projectId'] ?? $this->id;

        // Handle dates
        $this->start = ! empty($data['start']) ? $data['start'] : null;
        $this->end = ! empty($data['end']) ? $data['end'] : null;

        // Project metadata
        $this->type = $data['type'] ?? '';
        $this->state = $data['state'] ?? '';
        $this->menuType = $data['menuType'] ?? '';
        $this->status = $data['status'] ?? '';

        // Project metrics
        $this->numberOfTickets = $data['numberOfTickets'] ?? 0;
        $this->progress = $data['progress'] ?? 0;
        $this->sortIndex = $data['sortIndex'] ?? 0;

        // Related data
        $this->milestones = $data['milestones'] ?? null;
        $this->report = $data['report'] ?? null;
        $this->clientName = $data['clientName'] ?? '';

        // Additional properties
        $this->lastUpdate = $data['lastUpdate'] ?? null;
        $this->isFavorite = $data['isFavorite'] ?? false;
    }
}
