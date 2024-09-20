<?php

namespace Leantime\Domain\Sprints\Models;

class Sprints
{
    public $id;
    public $name;
    public $startDate;
    public $endDate;
    public $projectId;

    public function __construct()
    {
    }
}
