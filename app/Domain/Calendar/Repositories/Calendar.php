<?php

namespace Leantime\Domain\Calendar\Repositories;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Db\Repository as RepositoryCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Tickets\Services\Tickets;
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
     * @param  DateTimeHelper  $dateTimeHelper  The DateTimeHelper object.
     * @param  Environment  $config  The Environment object.
     * @return void
     */
    public function __construct(
        DbCore $dbCore,
        private LanguageCore $language,
        private DateTimeHelper $dateTimeHelper,
        private Environment $config
    ) {
        $this->db = $dbCore->getConnection();
    }

    /**
     * @param  string  $dateFrom
     * @param  string  $dateTo
     */
    public function getAllDates(?CarbonImmutable $dateFrom, ?CarbonImmutable $dateTo): false|array
    {
        $query = $this->db->table('zp_calendar')
            ->where('userId', session('userdata.id'))
            ->where('dateFrom', '<>', '0000-00-00 00:00:00');

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
     * Retrieves calendar events based on optional filters.
     *
     * @param  int|null  $userId  The user ID to filter the results by.
     * @param  CarbonImmutable|null  $dateFrom  The minimum date and time of the events.
     * @param  CarbonImmutable|null  $dateTo  The maximum date and time of the events.
     * @return bool|array Returns an array of calendar events if successful, otherwise false.
     */
    public function getAll(?int $userId, ?CarbonImmutable $dateFrom, ?CarbonImmutable $dateTo): false|array
    {
        $query = $this->db->table('zp_calendar')
            ->where('dateFrom', '<>', '0000-00-00 00:00:00');

        if (! empty($userId)) {
            $query->where('userId', '>=', $userId);
        }

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
     * @throws BindingResolutionException
     */
    public function getCalendar(int $userId): array
    {
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

        $results = $this->db->table('zp_calendar')
            ->where('userId', $userId)
            ->where('dateFrom', '<>', '0000-00-00 00:00:00')
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
                    $statusLabelsArray[$ticket['projectId']] = $ticketService->getStatusLabels(
                        $ticket['projectId']
                    );
                }

                if (isset($statusLabelsArray[$ticket['projectId']][$ticket['status']])) {
                    $statusName = $statusLabelsArray[$ticket['projectId']][$ticket['status']]['name'];
                    $statusColor = $this->classColorMap[$statusLabelsArray[$ticket['projectId']][$ticket['status']]['class']];
                } else {
                    $statusName = '';
                    $statusColor = 'var(--grey)';
                }

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

                if (
                    dtHelper()->isValidDateString($ticket['editFrom'])
                    && dtHelper()->isValidDateString($ticket['editTo'])
                ) {
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

    /**
     * Generates an event array for fullcalendar.io frontend.
     *
     * @param  int|null  $dateFrom
     * @param  int|null  $dateTo
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

    /**
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

        session([
            'usersettings.timezone' => $settingService->getSetting('usersettings.'.$user['id'].'.timezone') ?: $this->config->defaultTimezone,
        ]);
        date_default_timezone_set(session('usersettings.timezone'));

        if ($hash !== false && $calHash == $hash) {
            return $this->getCalendar($user['id']);
        } else {
            return false;
        }
    }

    public function getTicketWishDates(): false|array
    {
        $results = $this->db->table('zp_tickets')
            ->select('id', 'headline', 'dateToFinish')
            ->where(function ($query) {
                $query->where('userId', session('userdata.id'))
                    ->orWhere('editorId', session('userdata.id'));
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
                    ->orWhere('editorId', session('userdata.id'));
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
            ->limit(1)
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
            ->limit(1)
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
            ->limit(1)
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
            ->limit(1)
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
