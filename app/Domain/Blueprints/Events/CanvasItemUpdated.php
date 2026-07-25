<?php

namespace Leantime\Domain\Blueprints\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a canvas item was updated — for ANY canvas type (goal, idea,
 * wiki, logic model, …), since all canvas items share the zp_canvas_items
 * table and the same update chokepoints.
 *
 * The event is deliberately generic: consumers that only care about a specific
 * canvas (e.g. the strategy Logic Model, which propagates edits down to the
 * work it generated) resolve the item's canvas and filter themselves.
 * `changedFields` is best-effort — precise for patches (the patched keys),
 * over-inclusive for full updates — so listeners should still no-op when the
 * field they mirror is unchanged.
 */
final class CanvasItemUpdated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $canvasItemId  The updated canvas item id.
     * @param  array<int, string>  $changedFields  Best-effort names of the fields written.
     * @param  string|null  $legacyHook  TEMPORARY (migration window): the emitting method name —
     *                                   pass __FUNCTION__ — used to rebuild the historical string
     *                                   name for legacy string-based listeners.
     */
    public function __construct(
        public readonly int $canvasItemId,
        public readonly array $changedFields = [],
        private readonly ?string $legacyHook = null,
    ) {}

    /**
     * The exact historical string name of the emitting site. Remove with the migration window.
     *
     * @return array<int, string>
     */
    public function legacyHooks(): array
    {
        if ($this->legacyHook === null) {
            return [];
        }

        return ['leantime.domain.blueprints.services.blueprints.'.$this->legacyHook.'.canvas_item_updated'];
    }
}
