<?php

namespace Leantime\Domain\Connector\Models;

class Entity
{
    public int $id;

    public string $name;

    public string $authData;

    public string $notes;

    //Leantime domain object
    public mixed $leantimeEntity;

    //Array of field objects
    public array $fieldMappings = [];

    //External domain object
    public mixed $providerEntity;

    public function __construct()
    {
    }
}
