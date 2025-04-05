<?php

namespace Leantime\Domain\Connector\Models;

use Leantime\Core\Db\DbColumn;

class Integration
{
    #[DbColumn('id')]
    public int $id;

    #[DbColumn('providerId')]
    public ?string $providerId;

    #[DbColumn('method')]
    public ?string $method;

    #[DbColumn('entity')]
    public ?string $entity;

    #[DbColumn('fields')]
    public ?string $fields;

    #[DbColumn('schedule')]
    public ?string $schedule;

    #[DbColumn('notes')]
    public ?string $notes;

    #[DbColumn('auth')]
    public ?string $auth;

    #[DbColumn('meta')]
    public ?string $meta;

    #[DbColumn('createdOn')]
    public ?string $createdOn;

    #[DbColumn('createdBy')]
    public ?string $createdBy;

    #[DbColumn('lastSync')]
    public ?string $lastSync;

    public function __construct() {}
}
