<?php

namespace Leantime\Domain\Calendar\Services;

use Illuminate\Support\Str;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\FromFormat;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Tickets\Services\Tickets;
use Ramsey\Uuid\Uuid;
use Spatie\IcalendarGenerator\Components\Calendar as IcalCalendar;
use Spatie\IcalendarGenerator\Components\Event as IcalEvent;
use Spatie\IcalendarGenerator\Enums\Display;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Calendar
{
    private CalendarRepository $calendarRepo;
    private LanguageCore $language;

    private Setting $settingsRepo;

    private Environment $config;

    /**
     * @param CalendarRepository $calendarRepo
     * @param LanguageCore       $language
     *
     */
    public function __construct(
        CalendarRepository $calendarRepo,
        LanguageCore $language,
        Setting $settingsRepo,
        Environment $config,
    )
    {
        $this->calendarRepo = $calendarRepo;
        $this->language = $language;
        $this->settingsRepo = $settingsRepo;
        $this->config = $config;
    }

    /**
     * Deletes a Google Calendar.
     *
     * @param int $id The ID of the Google Calendar to delete.
     *
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
     * @access public
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
     * @access public
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
            if ($event && $event["userId"] == session("userdata.id")) {
                return true;
            }
        }

        return false;
    }


    /**
     * Adds a new event to the user's calendar
     *
     * @access public
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

        $dateFrom = null;
        if (isset($values['dateFrom']) === true && isset($values['timeFrom']) === true) {
            $dateFrom = format($values['dateFrom'], $values['timeFrom'], FromFormat::UserDateTime)->isoDateTime();
        }
        $values['dateFrom'] = $dateFrom;

        $dateTo = null;
        if (isset($values['dateTo']) === true && isset($values['timeTo']) === true) {
            $dateTo =  format($values['dateTo'], $values['timeTo'], FromFormat::UserDateTime)->isoDateTime();
        }
        $values['dateTo'] = $dateTo;

        if ($values['description'] !== '') {
            $result = $this->calendarRepo->addEvent($values);

            return $result;
        } else {
            return false;
        }
    }

    /**
     * @param int $eventId
     *
     * @return mixed
     *
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
     * @access public
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

            $dateFrom = null;
            if (isset($values['dateFrom']) === true && isset($values['timeFrom']) === true) {
                $dateFrom = format($values['dateFrom'], $values['timeFrom'], FromFormat::UserDateTime)->isoDateTime();
            }
            $values['dateFrom'] = $dateFrom;

            $dateTo = null;
            if (isset($values['dateTo']) === true && isset($values['timeTo']) === true) {
                $dateTo = format($values['dateTo'], $values['timeTo'], FromFormat::UserDateTime)->isoDateTime();
            }
            $values['dateTo'] = $dateTo;

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
     * @access public
     *
     * @param int $id
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
     * @param int $id
     * @param int $userId
     *
     * @return array|false
     *
     * @api
     */
    public function getExternalCalendar(int $id, int $userId): bool|array
    {
        return $this->calendarRepo->getExternalCalendar($id, $userId);
    }

    /**
     * @param array $values
     * @param int   $id
     *
     * @return void
     *
     * @api
     */
    public function editExternalCalendar(array $values, int $id): void
    {
        $this->calendarRepo->editGUrl($values, $id);
    }

    /**
     * Retrieves iCal calendar by user hash and calendar hash.
     *
     * @param string $userHash The hash of the user.
     * @param string $calHash  The hash of the calendar.
     *
     * @return IcalCalendar The iCal calendar generated from the calendar events.
     * @throws MissingParameterException If either user hash or calendar hash is empty.
     *
     *
     * @api
     */
    public function getIcalByHash(string $userHash, string $calHash): IcalCalendar
    {

        if (empty($userHash) || empty($calHash)) {
            throw new MissingParameterException("userHash and calendar hash are required");
        }

        $calendarEvents = $this->calendarRepo->getCalendarBySecretHash($userHash, $calHash);

        if(!$calendarEvents) {
            throw new \Exception("Calendar could not be retrieved");
        }

        $eventObjects = [];
        //Create array of event objects for ical generator
        foreach ($calendarEvents as $event) {

            try {

                $description = str_replace("\r\n", "\\n", strip_tags($event['description']));

                $currentEvent = IcalEvent::create()
                    ->image(BASE_URL . '/dist/images/favicon.png', 'image/png', Display::badge())
                    ->startsAt(dtHelper()->parseDbDateTime($event['dateFrom'])->setToUserTimezone())
                    ->endsAt(dtHelper()->parseDbDateTime($event['dateTo'])->setToUserTimezone())
                    ->name($event['title'])
                    ->description($description)
                    ->uniqueIdentifier($event['id'])
                    ->url($event['url'] ?? '');

                if ($event['allDay'] === true) {
                    $currentEvent->fullDay();
                }

                if ($event['eventType'] == 'ticket' && $event['dateContext'] == "due") {
                    $currentEvent->alertMinutesBefore(30, $this->language->__('text.ical.todo_is_due'));
                }

                if ($event['eventType'] == 'ticket' && $event['dateContext'] == "edit") {
                    $currentEvent->alertMinutesBefore(5, $this->language->__('text.ical.todo_start_alert'));
                }

                $eventObjects[] = $currentEvent;

            }catch(\Exception $e) {
                //Do not include event in ical
                report($e);
            }
        }

        $icalCalendar = IcalCalendar::create($this->language->__('text.ical_title'))->event($eventObjects);


        return $icalCalendar;
    }

    public function getCalendar(int $userId): array
    {
        $ticketService = app()->make(Tickets::class);
        $dbTickets =  $ticketService->getOpenUserTicketsThisWeekAndLater($userId, "", true);

        $tickets = array();
        if (isset($dbTickets["thisWeek"]["tickets"])) {
            $tickets = array_merge($tickets, $dbTickets["thisWeek"]["tickets"]);
        }

        if (isset($dbTickets["later"]["tickets"])) {
            $tickets = array_merge($tickets, $dbTickets["later"]["tickets"]);
        }

        if (isset($dbTickets["overdue"]["tickets"])) {
            $tickets = array_merge($tickets, $dbTickets["overdue"]["tickets"]);
        }

        $dbUserEvents = $this->calendarRepo->getAll($userId);

        $newValues = array();
        foreach ($dbUserEvents as $value) {
            $allDay = filter_var($value['allDay'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $newValues[] = array(
                'title'  => $value['description'],
                'allDay' => $allDay,
                'description' => '',
                'dateFrom' => $value['dateFrom'],
                'dateTo' => $value['dateTo'],
                'id' => $value['id'],
                'projectId' => '',
                'eventType' => "calendar",
                'dateContext' => 'plan',
                'backgroundColor' => 'var(--accent1)',
                'borderColor' => 'var(--accent1)',
                'url' => BASE_URL . '/calendar/showMyCalendar/#/calendar/editEvent/' . $value['id'],
            );
        }

        if (count($tickets)) {
            $statusLabelsArray = array();

            foreach ($tickets as $ticket) {
                if (!isset($statusLabelsArray[$ticket['projectId']])) {
                    $statusLabelsArray[$ticket['projectId']] = $ticketService->getStatusLabels(
                        $ticket['projectId']
                    );
                }

                if (isset($statusLabelsArray[$ticket['projectId']][$ticket['status']])) {
                    $statusName = $statusLabelsArray[$ticket['projectId']][$ticket['status']]["name"];
                    $statusColor = $this->calendarRepo->classColorMap[$statusLabelsArray[$ticket['projectId']][$ticket['status']]["class"]];
                } else {
                    $statusName = "";
                    $statusColor = "var(--grey)";
                }

                $backgroundColor = "var(--accent2)";

                if ($ticket['dateToFinish'] != "0000-00-00 00:00:00" && $ticket['dateToFinish'] != "1969-12-31 00:00:00") {
                    $context = '❕ ' . $this->language->__("label.due_todo");

                    $newValues[] = $this->mapEventData(
                        title:$context . $ticket['headline'] . " (" . $statusName . ")",
                        description: $ticket['description'],
                        allDay:false,
                        id: $ticket['id'],
                        projectId: $ticket['projectId'],
                        eventType: "ticket",
                        dateContext: "due",
                        backgroundColor: $backgroundColor,
                        borderColor: $statusColor,
                        dateFrom: $ticket['dateToFinish'],
                        dateTo: $ticket['dateToFinish']
                    );
                }

                if (dtHelper()->isValidDateString($ticket['editFrom'])) {
                    // Set ticket to all-day ticket when no time is set
                    $dateFrom =  dtHelper()->parseDbDateTime($ticket['editFrom']);
                    $dateTo =  dtHelper()->parseDbDateTime($ticket['editTo']);

                    $allDay = false;
                    if ($dateFrom->diffInDays($dateTo) >= 1) {
                        $allDay = true;
                    }

                    $context = $this->language->__("label.planned_edit");

                    $newValues[] = $this->mapEventData(
                        title: $context . $ticket['headline'] . " (" . $statusName . ")",
                        description: $ticket['description'],
                        allDay:$allDay,
                        id: $ticket['id'],
                        projectId: $ticket['projectId'],
                        eventType: "ticket",
                        dateContext: "edit",
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

    public function getICalUrl() {

        $userId = -1;
        if(!empty(session("userdata.id"))) {
            $userId = session("userdata.id");
        }

        $userHash = hash('sha1', $userId . $this->config->sessionPassword);
        $icalHash = $this->settingsRepo->getSetting("usersettings." . $userId. ".icalSecret");

        if(empty($icalHash)) {
            throw new \Exception("User has no ical hash");
        }

        return  BASE_URL . "/calendar/ical/" . $icalHash . "_" . $userHash;

    }

    public function generateIcalHash() {

        if(empty(session("userdata.id"))) {
            throw new \Exception("Session id is not set.");
        }

        $uuid = Uuid::uuid4();
        $icalHash = $uuid->toString();

        $this->settingsRepo->saveSetting("usersettings." . session("userdata.id") . ".icalSecret", $icalHash);

    }

    /**
     * Generates an event array for fullcalendar.io frontend.
     *
     * @param string   $title
     * @param bool     $allDay
     * @param int      $id
     * @param int      $projectId
     * @param string   $eventType
     * @param string   $dateContext
     * @param string   $backgroundColor
     * @param string   $borderColor
     * @param int|null $dateFrom
     * @param int|null $dateTo
     *
     * @return array
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
        return array(
            'title'  => $title,
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
            'url' => BASE_URL . "/dashboard/home/#/tickets/showTicket/" . $id,
        );
    }
}
