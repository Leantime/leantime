<?php

namespace Leantime\Domain\Sprints\Models;

class Sprints
{
    public $id;

    public $name;

    public $startDate;

    public $endDate;

    public $projectId;

    public $modified;

    /**
     * Runtime flag set by plugins (PgmPro) when this sprint is inherited by the current
     * project from its parent program, rather than owned by the project itself. Drives
     * UI affordances (badge, hidden edit/delete). Not persisted.
     */
    public bool $isInherited = false;

    /**
     * The program id this sprint is inherited from, when $isInherited is true. Not persisted.
     */
    public ?int $ownerProgramId = null;

    public function __construct() {}
}
