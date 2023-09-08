<?php

namespace Leantime\Domain\Connector\Services;

use Leantime\Domain\Connector\Models\Entity;

interface ProviderIntegration
{
    public function connect();

    public function sync(Entity $entity);

    public function getFields();

    public function getEntities();

    public function getValues(Entity $entity);
}
