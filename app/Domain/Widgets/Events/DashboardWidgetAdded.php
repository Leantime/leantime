<?php

namespace Leantime\Domain\Widgets\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a widget was added to the user's dashboard (became visible; drags and resizes do not fire).
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class DashboardWidgetAdded implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  string  $widgetId  The widget id.
     */
    public function __construct(
        public readonly string $widgetId,
    ) {}
}
