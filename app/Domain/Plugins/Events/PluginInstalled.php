<?php

namespace Leantime\Domain\Plugins\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a plugin was installed (registered in the plugin table).
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class PluginInstalled implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  string  $plugin  The plugin identifier (folder name).
     * @param  string  $format  How the plugin is delivered: 'folder' or 'phar'.
     */
    public function __construct(
        public readonly string $plugin,
        public readonly string $format,
    ) {}
}
