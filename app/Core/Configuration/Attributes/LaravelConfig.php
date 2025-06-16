<?php

namespace Leantime\Core\Configuration\Attributes;

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
