<?php

namespace Leantime\Domain\Connector\Repositories;

use Leantime\Domain\Connector\Models\Integration;
use Leantime\Infrastructure\Database\Repository;

class Integrations extends Repository
{
    public function __construct()
    {
        $this->entity = 'integration';
        $this->model = Integration::class;
    }
}
