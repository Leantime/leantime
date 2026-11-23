<?php

namespace Leantime\Domain\Calendar\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Db\Repository as RepositoryCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\EntityRelationshipEnum;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketsRepository;
use Leantime\Domain\Tickets\Support\WorkStart;
use Leantime\Domain\Users\Repositories\Users;

class Calendar extends RepositoryCore
{
    public array $classColorMap = [
        'label-warning' => 'var(--yellow)',
        'label-purple' => 'var(--purple)',
        'label-pink' => 'var(--pink)',
        'label-darker-blue' => 'var(--darker-blue)',
        'label-info' => 'var(--dark-blue)',
        'label-blue' => 'var(--blue)',
        'label-dark-blue' => 'var(--dark-blue)',
        'label-success' => 'var(--green)',
        'label-brown' => 'var(--brown)',
        'label-danger' => 'var(--dark-red)',
        'label-important' => 'var(--red)',
        'label-green' => 'var(--green)',
        'label-default' => 'var(--grey)',
        'label-dark-green' => 'var(--dark-green)',
        'label-red' => 'var(--red)',
        'label-dark-red' => 'var(--dark-red)',
        'label-grey' => 'var(--grey)',
    ];

    protected string $entity = 'calendar';

    private ConnectionInterface $db;

    /**
     * Class constructor.
     *
     * @param  DbCore  $dbCore  The DbCore object.
     * @param  LanguageCore  $language  The LanguageCore object.
     * @param  Environment  $config  The Environment object.
     * @return void
     */
    public function __construct(
        DbCore $dbCore,
        private LanguageCore $language,
        private Environment $config
    ) {
        $this->db = $dbCore->getConnection();
    }

    public function getAllDates(?CarbonImmutable $dateFrom, ?CarbonImmutable $dateTo): false|array
    {
        $query = $this->db->table('zp_calendar')
            ->where('userId', session('userdata.id'))
            ->whereNotNull('dateFrom');

        if (! empty($dateFrom)) {
            $query->where('dateFrom', '>=', $dateFrom->format('Y-m-d H:i:s'));
        }

        if (! empty($dateTo)) {
            $query->where('dateTo', '<=', $dateTo->format('Y-m-d H:i:s'));
        }

        $results = $query->orderBy('dateFrom')->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * Retrieves one user's calendar events, optionally limited to a date window.
     *
     * Events are personal: only rows owned by $userId are returned. Without a user id there is
     * no owner to match, so the result is empty rather than every user's events.
     *
     * @param  int|null  $userId  The user whose events to return.
     * @param  CarbonImmutable|null  $dateFrom  The minimum date and time of the events.
     * @param  CarbonImmutable|null  $dateTo  The maximum date and time of the events.
     * @return false|array Returns an array of calendar events if successful, otherwise false.
     */
    public function getAll(?int $userId, ?CarbonImmutable $dateFrom, ?CarbonImmutable $dateTo): false|array
    {
        if (empty($userId)) {
            return [];
        }

        $query = $this->db->table('zp_calendar')
            ->where('userId', $userId)
            ->whereNotNull('dateFrom');

        if (! empty($dateFrom)) {
            $query->where('dateFrom', '>=', $dateFrom->formatDateTimeForDb());
        }

        if (! empty($dateTo)) {
            $query->where('dateTo', '<=', $dateTo->formatDateTimeForDb());
        }

        $results = $query->orderBy('dateFrom')->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    /**
     * Builds the iCal feed events (personal calendar events plus To-Do due and planned-edit
     * events) for the feed owner.
     *
     * The feed is fetched without a session, so the To-Dos are scoped to the OWNER's project
     * access explicitly. The session-scoped Tickets service would scope them to nobody and the
     * feed lost every To-Do (#4018).
     *
     * Date-only Work Starts (planned days) go out as all-day events, unless the owner turned
     * "Show planned days" off.
     *
     * @param  int  $userId  The feed owner, already authenticated by the feed hashes
     * @param  int|null  $clientId  The feed owner's client (grants client-visible projects)
     * @param  bool  $showPlannedDays  Whether the owner shows planned days on their calendar
     * @param  string  $timezone  The owner's timezone, which decides what counts as a planned day
     * @return array<int, array<string, mixed>> Event arrays
     *
     * @throws BindingResolutionException
     */
    public function getCalendar(int $userId, ?int $clientId = null, bool $showPlannedDays = true, string $timezone = 'UTC'): array
    {
        $ticketRepo = app()->make(TicketsRepository::class);
        $tickets = $ticketRepo->getAllBySearchCriteria(
            searchCriteria: [
                'currentProject' => '',
                'users' => (string) $userId,
                'status' => 'all',
                'sprint' => '',
                'excludeType' => 'milestone',
            ],
            sort: 'duedate',
            includeCounts: false,
            scopeUserId: $userId,
            scopeClientId: $clientId,
        );

        if (! is_array($tickets)) {
            $tickets = [];
        }

        $results = $this->db->table('zp_calendar')
            ->where('userId', $userId)
            ->whereNotNull('dateFrom')
            ->get();

        $values = array_map(fn ($item) => (array) $item, $results->toArray());

        $newValues = [];
        foreach ($values as $value) {
            $allDay = filter_var($value['allDay'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

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
                    $statusLabelsArray[$ticket['projectId']] = $ticketRepo->getStateLabels($ticket['projectId']);
                }

                // A To-Do whose status was removed from its project is left out, as the
                // My Work lists do.
                $statusLabel = $statusLabelsArray[$ticket['projectId']][$ticket['status']] ?? null;
                if ($statusLabel === null) {
                    continue;
                }

                $statusName = $statusLabel['name'];
                $statusColor = $this->classColorMap[$statusLabel['class'] ?? ''] ?? 'var(--grey)';

                $backgroundColor = 'var(--accent2)';

                if (dtHelper()->isValidDateString($ticket['dateToFinish'])) {
                    $context = '❕ '.$this->language->__('label.due_todo');

                    // Detect if the due date has no specific time set (stored as end-of-day 23:59:59).
                    // If so, treat it as an all-day event to avoid timezone boundary issues
                    // that cause the event to appear on two days in the calendar.
                    $dueDate = dtHelper()->parseDbDateTime($ticket['dateToFinish']);
                    $isEndOfDay = $dueDate->format('H:i:s') === '23:59:59';
                    $allDay = $isEndOfDay;

                    $newValues[] = $this->mapEventData(
                        title: $context.$ticket['headline'].' ('.$statusName.')',
                        description: $ticket['description'],
                        allDay: $allDay,
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

                $hasWorkStart = dtHelper()->isValidDateString($ticket['editFrom']);
                $hasWorkEnd = dtHelper()->isValidDateString($ticket['editTo']);

                // A date-only Work Start is a plan for the day. It goes out as all-day, unless the
                // owner turned "Show planned days" off.
                $isPlannedDay = $hasWorkStart
                    && WorkStart::isDateOnly($ticket['editFrom'], $ticket['editTo'] ?? null, $timezone);

                if ($hasWorkStart && ($hasWorkEnd || $isPlannedDay) && (! $isPlannedDay || $showPlannedDays)) {
                    $dateFrom = dtHelper()->parseDbDateTime($ticket['editFrom']);
                    $dateTo = $hasWorkEnd ? dtHelper()->parseDbDateTime($ticket['editTo']) : $dateFrom;

                    $allDay = $isPlannedDay || $dateFrom->diffInDays($dateTo) >= 1;

                    $context = $isPlannedDay
                        ? $this->language->__('label.planned_day_prefix')
                        : $this->language->__('label.planned_edit');

                    $event = $this->mapEventData(
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
                        dateTo: $hasWorkEnd ? $ticket['editTo'] : $ticket['editFrom']
                    );
                    // Lets the feed tell a planned day (no start time to alert about) from a
                    // time box that happens to span whole days.
                    $event['plannedDay'] = $isPlannedDay;
                    $newValues[] = $event;
                }
            }
        }

        return $newValues;
    }

    /**
     * Generates an event array for fullcalendar.io frontend.
     */
    private function mapEventData(
        string $title,
        ?string $description,
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

    /**
     * The iCal feed events of the user identified by the feed hashes, or false when the hashes
     * don't match a user and that user's iCal secret. The hashes are the credential: the feed
     * is served without a session.
     *
     * @param  string  $userHash  sha1 of the user id + session password
     * @param  string  $calHash  The user's iCal secret
     * @return false|array<int, array<string, mixed>> Event arrays (possibly empty), or false
     *
     * @throws BindingResolutionException
     */
    public function getCalendarBySecretHash(string $userHash, string $calHash): false|array
    {
        // get user
        $userRepo = app()->make(Users::class);
        $user = $userRepo->getUserBySha($userHash);

        if (! isset($user['id'])) {
            return false;
        }

        // Check if setting exists
        $settingService = app()->make(Setting::class);
        $hash = $settingService->getSetting('usersettings.'.$user['id'].'.icalSecret');

        $timezone = (string) ($settingService->getSetting('usersettings.'.$user['id'].'.timezone') ?: $this->config->defaultTimezone);

        session([
            'usersettings.timezone' => $timezone,
        ]);

        if (empty($hash) || ! hash_equals((string) $hash, $calHash)) {
            return false;
        }

        $showPlannedDays = (string) $settingService->getSetting(
            'usersettings.'.$user['id'].'.'.\Leantime\Domain\Calendar\Services\Calendar::SHOW_PLANNED_DAYS_SETTING
        ) !== '0';

        return $this->getCalendar(
            (int) $user['id'],
            isset($user['clientId']) ? (int) $user['clientId'] : null,
            $showPlannedDays,
            $timezone,
        );
    }

    public function getTicketWishDates(): false|array
    {
        $results = $this->db->table('zp_tickets')
            ->select('id', 'headline', 'dateToFinish')
            ->where(function ($query) {
                $query->where('userId', session('userdata.id'))
                    ->orWhere('editorId', (string) session('userdata.id'))
                    ->orWhereExists(function ($subquery) {
                        $subquery->selectRaw('1')
                            ->from('zp_entity_relationship')
                            ->whereColumn('zp_entity_relationship.entityA', 'zp_tickets.id')
                            ->where('zp_entity_relationship.entityAType', 'Ticket')
                            ->where('zp_entity_relationship.entityBType', 'User')
                            ->where('zp_entity_relationship.relationship', EntityRelationshipEnum::Collaborator->value)
                            ->where('zp_entity_relationship.entityB', session('userdata.id'));
                    });
            })
            ->where('dateToFinish', '<>', '000-00-00 00:00:00')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function getTicketEditDates(): false|array
    {
        $results = $this->db->table('zp_tickets')
            ->select('id', 'headline', 'editFrom', 'editTo')
            ->where(function ($query) {
                $query->where('userId', session('userdata.id'))
                    ->orWhere('editorId', (string) session('userdata.id'))
                    ->orWhereExists(function ($subquery) {
                        $subquery->selectRaw('1')
                            ->from('zp_entity_relationship')
                            ->whereColumn('zp_entity_relationship.entityA', 'zp_tickets.id')
                            ->where('zp_entity_relationship.entityAType', 'Ticket')
                            ->where('zp_entity_relationship.entityBType', 'User')
                            ->where('zp_entity_relationship.relationship', EntityRelationshipEnum::Collaborator->value)
                            ->where('zp_entity_relationship.entityB', session('userdata.id'));
                    });
            })
            ->where('editFrom', '<>', '000-00-00 00:00:00')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function addEvent(array $values): false|string
    {
        $id = $this->db->table('zp_calendar')->insertGetId([
            'userId' => session('userdata.id'),
            'dateFrom' => $values['dateFrom'],
            'dateTo' => $values['dateTo'],
            'description' => $values['description'],
            'allDay' => $values['allDay'],
        ]);

        return $id ? (string) $id : false;
    }

    public function getEvent(int $id): mixed
    {
        $result = $this->db->table('zp_calendar')
            ->where('id', $id)
            ->first();

        return $result ? (array) $result : false;
    }

    public function editEvent(array $values, int $id): void
    {
        $this->db->table('zp_calendar')
            ->where('id', $id)
            ->where('userId', session('userdata.id'))
            ->update([
                'dateFrom' => $values['dateFrom'],
                'dateTo' => $values['dateTo'],
                'description' => $values['description'],
                'allDay' => $values['allDay'],
            ]);
    }

    public function delPersonalEvent(int $id): int|false
    {
        return $this->db->table('zp_calendar')
            ->where('id', $id)
            ->where('userId', session('userdata.id'))
            ->delete();
    }

    public function getMyExternalCalendars(int $userId): false|array
    {
        $results = $this->db->table('zp_gcallinks')
            ->select('id', 'url', 'name', 'colorClass')
            ->where('userId', $userId)
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function getExternalCalendar(int $calendarId, int $userId): false|array
    {
        $result = $this->db->table('zp_gcallinks')
            ->select('id', 'url', 'name', 'colorClass')
            ->where('userId', $userId)
            ->where('id', $calendarId)
            ->limit(1)
            ->first();

        return $result ? (array) $result : false;
    }

    public function getGCal(int $id): mixed
    {
        $result = $this->db->table('zp_gcallinks')
            ->select('id', 'url', 'name', 'colorClass')
            ->where('userId', session('userdata.id'))
            ->where('id', $id)
            ->limit(1)
            ->first();

        return $result ? (array) $result : false;
    }

    public function editGUrl(array $values, int $id): void
    {
        $this->db->table('zp_gcallinks')
            ->where('userId', session('userdata.id'))
            ->where('id', $id)
            ->update([
                'url' => $values['url'],
                'name' => $values['name'],
                'colorClass' => $values['colorClass'],
            ]);
    }

    public function deleteGCal(int $id): bool
    {
        return $this->db->table('zp_gcallinks')
            ->where('id', $id)
            ->where('userId', session('userdata.id'))
            ->delete() > 0;
    }

    public function addGUrl(array $values): void
    {
        $this->db->table('zp_gcallinks')->insert([
            'userId' => session('userdata.id'),
            'name' => $values['name'],
            'url' => $values['url'],
            'colorClass' => $values['colorClass'],
        ]);
    }
}
