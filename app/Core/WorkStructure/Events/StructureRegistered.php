<?php

namespace Leantime\Core\WorkStructure\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when a new work structure is registered.
 */
class StructureRegistered
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  int  $structureId  The registered structure ID
     * @param  string  $title  The structure title
     * @param  string  $type  The structure type (system, plugin, custom)
     */
    public function __construct(
        public int $structureId,
        public string $title,
        public string $type
    ) {}
}
