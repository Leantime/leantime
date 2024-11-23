<?php

namespace Leantime\Core\Db;

use Attribute;

#[Attribute]
class DbColumn
{
    public function __construct(
        public string $name,
    ) {
        //
    }
}
