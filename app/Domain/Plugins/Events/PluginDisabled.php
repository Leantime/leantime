<?php

namespace Leantime\Domain\Plugins\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a plugin was disabled.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class PluginDisabled implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  string  $plugin  The plugin identifier (folder name).
     */
    public function __construct(
        public readonly string $plugin,
    ) {}
}
