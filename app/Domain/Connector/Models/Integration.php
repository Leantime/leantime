<?php

namespace Leantime\Domain\Connector\Models {

    class Integration
    {
        public int $id;

        public ?string $providerId;

        public ?string $method;

        public ?string $entity;

        public ?string $fields;

        public ?string $schedule;

        public ?string $notes;

        public ?string $auth;

        public ?string $meta;

        public ?string $createdOn;

        public ?string $createdBy;

        public ?string $lastSync;

        public function __construct() {}
    }

}
