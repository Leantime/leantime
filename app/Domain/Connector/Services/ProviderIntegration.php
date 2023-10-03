<?php

namespace Leantime\Domain\Connector\Services;

use Leantime\Domain\Connector\Models\Entity;

/**
 *
 */

/**
 *
 */
interface ProviderIntegration
{
    /**
     * @return mixed
     */
    public function connect();

    /**
     * @param Entity $entity
     * @return mixed
     */
    public function sync(Entity $entity);

    /**
     * @return mixed
     */
    public function getFields();

    /**
     * @return mixed
     */
    public function getEntities();

    /**
     * @param Entity $entity
     * @return mixed
     */
    public function getValues(Entity $entity);
}
