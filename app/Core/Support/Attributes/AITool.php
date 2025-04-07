<?php

namespace Leantime\Core\Support\Attributes;

use Attribute;

#[Attribute]
class AITool
{
    /**
     * Constructor for the AITool attribute
     *
     * @param  string  $name  The name of the tool as it will be exposed to the AI
     * @param  string  $description  A description of what the tool does
     */
    public function __construct(
        public string $name,
        public string $description,
    ) {
        //
    }
}
