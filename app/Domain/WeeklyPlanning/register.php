<?php

use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\WeeklyPlanning\Listeners\InjectCalendarEvents;

/**
 * WeeklyPlanning domain — event/filter listener registration.
 */

// Inject weekly plan tasks into the Leantime calendar view.
EventDispatcher::add_filter_listener(
    'leantime.domain.calendar.services.calendar.getCalendar.calendar_events',
    InjectCalendarEvents::class
);
