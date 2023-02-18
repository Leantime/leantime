<?php

namespace leantime\domain\services\connector;

use leantime\domain\models\connector\entity;

interface providerIntegration
{
    public function connect($name, $var);

    public function sync(entity $entity);

    public function getFields();

    public function getEntities();

    public function getValues(entity $entity);
}
