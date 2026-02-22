<?php

namespace Leantime\Domain\WorkStructure\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired when cross-structure mappings are defined.
 */
class MappingCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param  int  $sourceStructureId  The source structure ID
     * @param  int  $targetStructureId  The target structure ID
     * @param  string  $mappingType  The mapping type (generates, equivalent, informs)
     */
    public function __construct(
        public int $sourceStructureId,
        public int $targetStructureId,
        public string $mappingType
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
