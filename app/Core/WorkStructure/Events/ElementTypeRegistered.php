<?php

namespace Leantime\Core\WorkStructure\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when element types are added to a structure.
 */
class ElementTypeRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  int  $structureId  The structure ID
     * @param  string  $typeKey  The element type key
     * @param  string  $label  The element label
     */
    public function __construct(
        public int $structureId,
        public string $typeKey,
        public string $label
    ) {}
}
