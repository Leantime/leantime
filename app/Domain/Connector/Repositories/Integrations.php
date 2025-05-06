<?php

namespace Leantime\Domain\Connector\Repositories;

use Leantime\Infrastructure\Database\Repository;
use Leantime\Domain\Connector\Models\Integration;

class Integrations extends Repository
{
    public function __construct()
    {
        $this->entity = 'integration';
        $this->model = Integration::class;
    }
}
