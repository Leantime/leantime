<?php

namespace Leantime\Domain\Connector\Services;

use Leantime\Domain\Connector\Models\Entity;

interface ProviderIntegration
{
    public function connect(): mixed;

    public function sync(Entity $entity): mixed;

    public function getFields(): mixed;

    public function getEntities(): mixed;

    public function getValues(Entity $entity): mixed;
}
