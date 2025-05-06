<?php

namespace Leantime\Domain\Calendar\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Infrastructure\i18n\Language as LanguageCore;
use Ramsey\Uuid\Uuid;
use Spatie\IcalendarGenerator\Components\Calendar as IcalCalendar;
use Spatie\IcalendarGenerator\Components\Event as IcalEvent;
use Spatie\IcalendarGenerator\Enums\Display;

class Calendar
{
    private CalendarRepository $calendarRepo;

    private LanguageCore $language;

    private Setting $settingsRepo;

    private Environment $config;

    public function __construct(
        CalendarRepository $calendarRepo,
        LanguageCore $language,
        Setting $settingsRepo,
        Environment $config,
    ) {
        $this->calendarRepo = $calendarRepo;
        $this->language = $language;
        $this->settingsRepo = $settingsRepo;
        $this->config = $config;
    }

    /**
     * Deletes a Google Calendar.
     *
     * @param  int  $id  The ID of the Google Calendar to delete.
     * @return bool Returns true if the Google Calendar was successfully deleted, false otherwise.
     *
     * @api
     */
    public function deleteGCal(int $id): bool
    {
        return $this->calendarRepo->deleteGCal($id);
    }

    /**
     * Patches calendar event
     *
     *
     * @params $id id of event to be updated (only events can be updated. Tickets need to be updated via ticket api
     * @params $params key value array of columns to be updated
     *
     * @return bool true on success, false on failure
     *
     * @api
     */
    public function patch($id, $params): bool
    {
        // Admins can always change anything.
        // Otherwise user has to own the event
        if ($this->userIsAllowedToUpdate($id)) {
            return $this->calendarRepo->patch($id, $params);
        }

        return false;
    }

    /**
     * Checks if user is allowed to make changes to event
     *
     *
     * @params int $eventId Id of event to be checked
     *
     * @return bool true on success, false on failure
     *
     * @api
     */
    private function userIsAllowedToUpdate($eventId): bool
    {

        if (Auth::userIsAtLeast(Roles::$admin)) {
            return true;
        } else {
            $event = $this->calendarRepo->getEvent($eventId);
            if ($event && $event['userId'] == session('userdata.id')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Adds a new event to the user's calendar
     *
     *
     * @params array $values array of event values
     *
     * @return int|false returns the id on success, false on failure
     *
     * @api
     */
    public function addEvent(array $values): int|false
    {
        $values['allDay'] = $values['allDay'] ?? false;

        if (isset($values['dateFrom'])) {
            try {
                $timeFrom = $values['timeFrom'] ?? null;
                $values['dateFrom'] = dtHelper()->parseUserDateTime(
                    $values['dateFrom'],
                    $timeFrom
                )->formatDateTimeForDb();
            } catch (\Exception $e) {
                // Silent exception handling
            }
        }

        if (isset($values['dateTo'])) {
            try {
                $timeTo = $values['timeTo'] ?? null;
                $values['dateTo'] = dtHelper()->parseUserDateTime(
                    $values['dateTo'],
                    $timeTo
                )->formatDateTimeForDb();
            } catch (\Exception $e) {
                // Silent exception handling
            }
        }

        if ($values['description'] !== '') {
            $result = $this->calendarRepo->addEvent($values);

            return $result;
        } else {
            return false;
        }
    }

    /**
     * @api
     */
    public function getEvent(int $eventId): mixed
    {
        return $this->calendarRepo->getEvent($eventId);
    }

    /**
     * edits an event on the user's calendar
     * Important: Time needs to come in as user formatted time value.
     *
     *
     * @params array $values array of event values
     *
     * @return bool returns true on success, false on failure
     *
     * @api
     */
    public function editEvent(array $values): bool
    {
        if (isset($values['id']) === true) {
            $id = $values['id'];

            $row = $this->calendarRepo->getEvent($id);

            if ($row === false) {
                return false;
            }

            if (isset($values['allDay']) === true) {
                $allDay = 'true';
            } else {
                $allDay = 'false';
            }

            $values['allDay'] = $allDay;

            if (isset($values['dateFrom'])) {
                try {
                    $timeFrom = $values['timeFrom'] ?? null;
                    $values['dateFrom'] = dtHelper()->parseUserDateTime(
                        $values['dateFrom'],
                        $timeFrom
                    )->formatDateTimeForDb();
                } catch (\Exception $e) {
                    // Silent exception handling
                }
            }

            if (isset($values['dateTo'])) {
                try {
                    $timeTo = $values['timeTo'] ?? null;
                    $values['dateTo'] = dtHelper()->parseUserDateTime(
                        $values['dateTo'],
                        $timeTo
                    )->formatDateTimeForDb();
                } catch (\Exception $e) {
                    // Silent exception handling
                }
            }

            if ($values['description'] !== '') {
                $this->calendarRepo->editEvent($values, $id);

                return true;
            }
        }

        return false;
    }

    /**
     * deletes an event on the user's calendar
     *
     *
     *
     * @return int|false returns the id on success, false on failure
     *
     * @api
     */
    public function delEvent(int $id): int|false
    {
        return $this->calendarRepo->delPersonalEvent($id);
    }

    /**
     * @return array|false
     *
     * @api
     */
    public function getExternalCalendar(int $id, int $userId): bool|array
    {
        return $this->calendarRepo->getExternalCalendar($id, $userId);
    }

    /**
     * @api
     */
    public function editExternalCalendar(array $values, int $id): void
    {
        $this->calendarRepo->editGUrl($values, $id);
    }

    /**
     * Retrieves iCal calendar by user hash and calendar hash.
     *
     * @param  string  $userHash  The hash of the user.
     * @param  string  $calHash  The hash of the calendar.
     * @return IcalCalendar The iCal calendar generated from the calendar events.
     *
     * @throws MissingParameterException If either user hash or calendar hash is empty.
     *
     * @api
     */
    public function getIcalByHash(string $userHash, string $calHash): IcalCalendar
    {

        if (empty($userHash) || empty($calHash)) {
            throw new MissingParameterException('userHash and calendar hash are required');
        }

        $calendarEvents = $this->calendarRepo->getCalendarBySecretHash($userHash, $calHash);

        if (! $calendarEvents) {
            throw new \Exception('Calendar could not be retrieved');
        }

        $eventObjects = [];
        // Create array of event objects for ical generator
        foreach ($calendarEvents as $event) {

            try {

                $description = str_replace("\r\n", '\\n', strip_tags($event['description']));

                $currentEvent = IcalEvent::create()
                    ->image(BASE_URL.'/dist/images/favicon.png', 'image/png', Display::badge())
                    ->startsAt(dtHelper()->parseDbDateTime($event['dateFrom'])->setToUserTimezone())
                    ->endsAt(dtHelper()->parseDbDateTime($event['dateTo'])->setToUserTimezone())
                    ->name($event['title'])
                    ->description($description)
                    ->uniqueIdentifier($event['id'])
                    ->url($event['url'] ?? '');

                if ($event['allDay'] === true) {
                    $currentEvent->fullDay();
                }

                if ($event['eventType'] == 'ticket' && $event['dateContext'] == 'due') {
                    $currentEvent->alertMinutesBefore(30, $this->language->__('text.ical.todo_is_due'));
                }

                if ($event['eventType'] == 'ticket' && $event['dateContext'] == 'edit') {
                    $currentEvent->alertMinutesBefore(5, $this->language->__('text.ical.todo_start_alert'));
                }

                $eventObjects[] = $currentEvent;

            } catch (\Exception $e) {
                // Do not include event in ical
                Log::error($e);
            }
        }

        $icalCalendar = IcalCalendar::create($this->language->__('text.ical_title'))->event($eventObjects);

        return $icalCalendar;
    }

    public function getCalendar(int $userId, null|string|CarbonImmutable $from = null, null|string|CarbonImmutable $until = null): array
    {
        // Convert date parameters to Carbon instances if they're strings
        if (is_string($from)) {
            $from = CarbonImmutable::parse($from);
        }
        if (is_string($until)) {
            $until = CarbonImmutable::parse($until);
        }

        // Get tickets and filter by date range
        $ticketService = app()->make(Tickets::class);
        $dbTickets = $ticketService->getOpenUserTicketsThisWeekAndLater($userId, '', true);

        $tickets = [];
        if (isset($dbTickets['thisWeek']['tickets'])) {
            $tickets = array_merge($tickets, $dbTickets['thisWeek']['tickets']);
        }

        if (isset($dbTickets['later']['tickets'])) {
            $tickets = array_merge($tickets, $dbTickets['later']['tickets']);
        }

        if (isset($dbTickets['overdue']['tickets'])) {
            $tickets = array_merge($tickets, $dbTickets['overdue']['tickets']);
        }

        $dbUserEvents = $this->calendarRepo->getAll($userId, $from, $until);

        $newValues = [];
        foreach ($dbUserEvents as $value) {
            $allDay = filter_var($value['allDay'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            // Filter events by date range if specified
            if ($from || $until) {
                $eventStart = CarbonImmutable::parse($value['dateFrom']);
                $eventEnd = CarbonImmutable::parse($value['dateTo']);

                if ($from && $eventEnd < $from) {
                    continue;
                }
                if ($until && $eventStart > $until) {
                    continue;
                }
            }

            $newValues[] = [
                'title' => $value['description'],
                'allDay' => $allDay,
                'description' => '',
                'dateFrom' => $value['dateFrom'],
                'dateTo' => $value['dateTo'],
                'id' => $value['id'],
                'projectId' => '',
                'eventType' => 'calendar',
                'dateContext' => 'plan',
                'backgroundColor' => 'var(--accent1)',
                'borderColor' => 'var(--accent1)',
                'url' => BASE_URL.'/calendar/showMyCalendar/#/calendar/editEvent/'.$value['id'],
            ];
        }

        if (count($tickets)) {
            $statusLabelsArray = [];

            foreach ($tickets as $ticket) {
                if (! isset($statusLabelsArray[$ticket['projectId']])) {
                    $statusLabelsArray[$ticket['projectId']] = $ticketService->getStatusLabels(
                        $ticket['projectId']
                    );
                }

                if (isset($statusLabelsArray[$ticket['projectId']][$ticket['status']])) {
                    $statusName = $statusLabelsArray[$ticket['projectId']][$ticket['status']]['name'];
                    $statusColor = $this->calendarRepo->classColorMap[$statusLabelsArray[$ticket['projectId']][$ticket['status']]['class']];
                } else {
                    $statusName = '';
                    $statusColor = 'var(--grey)';
                }

                $backgroundColor = 'var(--accent2)';

                if ($ticket['dateToFinish'] !== '0000-00-00 00:00:00' && $ticket['dateToFinish'] !== '1969-12-31 00:00:00') {
                    $context = '❕ '.$this->language->__('label.due_todo');

                    $newValues[] = $this->mapEventData(
                        title: $context.$ticket['headline'].' ('.$statusName.')',
                        description: $ticket['description'],
                        allDay: false,
                        id: $ticket['id'],
                        projectId: $ticket['projectId'],
                        eventType: 'ticket',
                        dateContext: 'due',
                        backgroundColor: $backgroundColor,
                        borderColor: $statusColor,
                        dateFrom: $ticket['dateToFinish'],
                        dateTo: $ticket['dateToFinish']
                    );
                }

                if (dtHelper()->isValidDateString($ticket['editFrom'])) {
                    // Set ticket to all-day ticket when no time is set
                    $dateFrom = dtHelper()->parseDbDateTime($ticket['editFrom']);
                    $dateTo = dtHelper()->parseDbDateTime($ticket['editTo']);

                    $allDay = false;
                    if ($dateFrom->diffInDays($dateTo) >= 1) {
                        $allDay = true;
                    }

                    $context = $this->language->__('label.planned_edit');

                    $newValues[] = $this->mapEventData(
                        title: $context.$ticket['headline'].' ('.$statusName.')',
                        description: $ticket['description'],
                        allDay: $allDay,
                        id: $ticket['id'],
                        projectId: $ticket['projectId'],
                        eventType: 'ticket',
                        dateContext: 'edit',
                        backgroundColor: $backgroundColor,
                        borderColor: $statusColor,
                        dateFrom: $ticket['editFrom'],
                        dateTo: $ticket['editTo']
                    );
                }
            }
        }

        return $newValues;
    }

    public function getICalUrl()
    {
        $userId = -1;
        if (! empty(session('userdata.id'))) {
            $userId = session('userdata.id');
        }

        $userHash = hash('sha1', $userId.$this->config->sessionPassword);
        $icalHash = $this->settingsRepo->getSetting('usersettings.'.$userId.'.icalSecret');

        if (empty($icalHash)) {
            throw new \Exception('User has no ical hash');
        }

        return BASE_URL.'/calendar/ical/'.$icalHash.'_'.$userHash;
    }

    /**
     * Gets all events from external calendars for a user
     *
     * @param  int  $userId  The user ID to get external calendar events for
     * @param  null|string|CarbonImmutable  $from  The start date to filter events by
     * @param  null|string|CarbonImmutable  $until  The end date to filter events by
     * @return array Array of calendar events from all external calendars
     */
    public function getExternalCalendarEvents(null|string|CarbonImmutable $from = null, null|string|CarbonImmutable $until = null): array
    {
        //        if (Cache::has('calendar.external.'.session('userdata.id'))) {
        //            return Cache::get('calendar.external.'.session('userdata.id'));
        //        }
        // Get all external calendars for the user
        $externalCalendars = $this->calendarRepo->getMyExternalCalendars(session('userdata.id'));

        if (empty($externalCalendars)) {
            return [];
        }

        $allEvents = [];

        // Convert date parameters to Carbon instances if they're strings
        try {
            if (is_string($from)) {
                $from = CarbonImmutable::parse($from);
            }
            if (is_string($until)) {
                $until = CarbonImmutable::parse($until);
            }
        } catch (\Exception $e) {
            Log::error('Error converting date parameters to Carbon instances: '.$e->getMessage());
            Log::error($e);

            return [];
        }

        foreach ($externalCalendars as $calendar) {
            try {
                // Load the iCal data using existing functionality
                $icalContent = $this->loadIcalUrl($calendar['url']);

                // Parse the iCal data into events
                $parser = new \ICal\ICal;
                $parser->initString($icalContent);

                $events = $parser->events();

                // Filter events by date range if specified
                if ($from || $until) {
                    $events = array_filter($events, function ($event) use ($from, $until) {
                        $eventStart = CarbonImmutable::parse($event->dtstart);
                        $eventEnd = isset($event->dtend) ? CarbonImmutable::parse($event->dtend) : $eventStart;

                        if ($from && $eventEnd < $from) {
                            return false;
                        }
                        if ($until && $eventStart > $until) {
                            return false;
                        }

                        return true;
                    });
                }

                // Transform each event into our standard format
                foreach ($events as $event) {
                    $allEvents[] = [
                        'title' => $event->summary,
                        'description' => $event->description ?? '',
                        'dateFrom' => dtHelper()->parseUserDateTime($event->dtstart)->formatDateTimeForDb(),
                        'dateTo' => dtHelper()->parseUserDateTime($event->dtend)->formatDateTimeForDb(),
                        'allDay' => isset($event->dtstart_array[3]) ? false : true,
                        'id' => $event->uid,
                        'projectId' => '',
                        'eventType' => 'external',
                        'dateContext' => 'plan',
                        'backgroundColor' => $calendar['colorClass'],
                        'borderColor' => $calendar['colorClass'],
                        'url' => $event->url ?? '',
                        'source' => $calendar['name'],
                    ];
                }

            } catch (\Exception $e) {
                // Log error but continue with other calendars
                Log::error("Error fetching calendar {$calendar['name']}: ".$e->getMessage());

                continue;
            }
        }

        Cache::put('calendar.external.'.session('userdata.id'), $allEvents, 240);

        return $allEvents;
    }

    /**
     * Load an iCal URL and return its contents
     *
     * @param  string  $url  The URL of the iCal feed
     * @return string The iCal content
     *
     * @throws \Exception If there is an error loading the URL
     */
    private function loadIcalUrl(string $url): string
    {
        if (str_contains($url, 'webcal://')) {
            $url = str_replace('webcal://', 'https://', $url);
        }

        $client = new \GuzzleHttp\Client;

        try {
            $response = $client->get($url, [
                'headers' => [
                    'Accept' => 'text/calendar',
                    'User-Agent' => 'Leantime Calendar Integration v'.$this->config->appVersion,
                ],
            ]);

            if ($response->getStatusCode() == 200) {
                return (string) $response->getBody();
            }

            throw new \Exception('Failed to load iCal feed: HTTP '.$response->getStatusCode());
        } catch (\Exception $e) {
            throw new \Exception('Error loading iCal feed: '.$e->getMessage());
        }
    }

    public function generateIcalHash()
    {

        if (empty(session('userdata.id'))) {
            throw new \Exception('Session id is not set.');
        }

        $uuid = Uuid::uuid4();
        $icalHash = $uuid->toString();

        $this->settingsRepo->saveSetting('usersettings.'.session('userdata.id').'.icalSecret', $icalHash);

    }

    /**
     * Generates an event array for fullcalendar.io frontend.
     *
     * @param  int|null  $dateFrom
     * @param  int|null  $dateTo
     *
     * @api
     */
    private function mapEventData(
        string $title,
        string $description,
        bool $allDay,
        int $id,
        int $projectId,
        string $eventType,
        string $dateContext,
        string $backgroundColor,
        string $borderColor,
        string $dateFrom,
        string $dateTo
    ): array {
        return [
            'title' => $title,
            'allDay' => $allDay,
            'description' => $description,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'id' => $id,
            'projectId' => $projectId,
            'eventType' => $eventType,
            'dateContext' => $dateContext,
            'backgroundColor' => $backgroundColor,
            'borderColor' => $borderColor,
            'url' => BASE_URL.'/dashboard/home/#/tickets/showTicket/'.$id,
        ];
    }
}
