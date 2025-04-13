<?php

namespace Leantime\Core\Support\Attributes;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class Parameter
{
    public function __construct(
        public string $name,
        public string $type,
        public string $description,
        public bool $required = false
    ) {}
}
