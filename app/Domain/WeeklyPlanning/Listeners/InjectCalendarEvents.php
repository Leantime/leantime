<?php

namespace Leantime\Domain\WeeklyPlanning\Listeners;

use Leantime\Domain\WeeklyPlanning\Services\WeeklyPlanning as WeeklyPlanningService;

/**
 * Injects weekly-plan task assignments into the Leantime calendar as all-day events.
 *
 * Registered via the filter:
 *   leantime.domain.calendar.services.calendar.getCalendar.calendar_events
 */
class InjectCalendarEvents
{
    /**
     * @param  array<int, array<string, mixed>>  $events
     * @param  array<string, mixed>              $params  {userId, from, until}
     * @return array<int, array<string, mixed>>
     */
    public function handle(array $events, array $params): array
    {
        $userId = (int) ($params['userId'] ?? session('userdata.id'));

        // Only inject events for the currently logged-in user.
        if (! $userId || $userId !== (int) session('userdata.id')) {
            return $events;
        }

        /** @var WeeklyPlanningService $service */
        $service = app()->make(WeeklyPlanningService::class);
        $plans   = $service->getPlansForEmployee($userId);

        $from  = $params['from']  ?? null;
        $until = $params['until'] ?? null;

        foreach ($plans as $plan) {
            $weekStart = \Carbon\CarbonImmutable::parse($plan['weekStart']);
            $weekEnd   = \Carbon\CarbonImmutable::parse($plan['weekEnd']);

            // Skip if the plan's week is outside the requested calendar range.
            if ($from && $weekEnd->lt($from)) {
                continue;
            }
            if ($until && $weekStart->gt($until)) {
                continue;
            }

            $items = $service->getItemsForPlan((int) $plan['id']);

            foreach ($items as $item) {
                $title = trim('[Plan] ' . ($item['ticketHeadline'] ?? $item['expectedOutcome'] ?? 'Weekly Plan Task'));

                $events[] = [
                    'title'           => $title,
                    'allDay'          => true,
                    'description'     => '',
                    'dateFrom'        => $plan['weekStart'] . ' 00:00:00',
                    'dateTo'          => $plan['weekEnd'] . ' 23:59:59',
                    'id'              => (int) $item['id'],
                    'projectId'       => 0,
                    'eventType'       => 'weeklyplan',
                    'dateContext'     => 'plan',
                    'backgroundColor' => '#8b5cf6',
                    'borderColor'     => '#7c3aed',
                    'url'             => BASE_URL . '/weeklyplanning/showMy',
                ];
            }
        }

        return $events;
    }
}
