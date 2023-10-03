<?php

namespace Leantime\Domain\Connector\Models {

    /**
     *
     */
    class Integration
    {
        #[DbColumn(type: "int")]
        public int $id;

        #[DbColumn(type: "varchar")]
        public ?string $providerId;

        #[DbColumn(type: "varchar")]
        public ?string $method;

        #[DbColumn(type: "varchar")]
        public ?string $entity;

        #[DbColumn(type: "text")]
        public ?string $fields;

        #[DbColumn(type: "text")]
        public ?string $schedule;

        #[DbColumn(type: "text")]
        public ?string $notes;

        #[DbColumn(type: "text")]
        public ?string $auth;

        #[DbColumn(type: "text")]
        public ?string $meta;

        #[DbColumn(type: "datetime")]
        public ?string $createdOn;

        #[DbColumn(type: "int")]
        public ?string $createdBy;

        #[DbColumn(type: "datetime")]
        public ?string $lastSync;


        public function __construct()
        {
        }
    }

}
