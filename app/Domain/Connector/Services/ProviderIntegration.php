<?php

namespace Leantime\Domain\Connector\Services;

use Leantime\Domain\Connector\Models\Entity;

interface ProviderIntegration
{
    /**
     * @return mixed
     */
    public function connect(): mixed;

    /**
     * @param Entity $entity
     *
     * @return mixed
     */
    public function sync(Entity $entity): mixed;

    /**
     * @return mixed
     */
    public function getFields(): mixed;

    /**
     * @return mixed
     */
    public function getEntities(): mixed;

    /**
     * @param Entity $entity
     *
     * @return mixed
     */
    public function getValues(Entity $entity): mixed;
}
