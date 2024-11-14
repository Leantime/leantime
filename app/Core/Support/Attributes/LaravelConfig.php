<?php

namespace Leantime\Core\Support\Attributes;

use Attribute;

#[Attribute]
class LaravelConfig
{
    public function __construct(
        public string $config,
    ) {
        //
    }
}
