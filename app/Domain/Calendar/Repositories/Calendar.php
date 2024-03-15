<?php

namespace Leantime\Domain\Calendar\Repositories;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Environment;
use Leantime\Core\Language;
use Leantime\Core\Repository as RepositoryCore;
use Leantime\Core\Db as DbCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Users\Repositories\Users;
use PDO;
use PHPUnit\Exception;

/**
 *
 */
class Calendar extends RepositoryCore
{
    private array $classColorMap = array(
         "label-warning" => "var(--yellow)",
         "label-purple" => "var(--purple)",
         "label-pink" => "var(--pink)",
         "label-darker-blue" => "var(--darker-blue)",
         "label-info" => "var(--dark-blue)",
         "label-blue" => "var(--blue)",
         "label-dark-blue" => "var(--dark-blue)",
         "label-success" => "var(--green)",
         "label-brown" => "var(--brown)",
         "label-danger" => "var(--dark-red)",
         "label-important" => "var(--red)",
         "label-green" => "var(--green)",
         "label-default" => "var(--grey)",
         "label-dark-green" => "var(--dark-green)",
         "label-red" => "var(--red)",
         "label-dark-red" => "var(--dark-red)",
         "label-grey" => "var(--grey)",
    );

    /**
     * Class constructor.
     *
     * @param DbCore         $db             The DbCore object.
     * @param LanguageCore   $language       The LanguageCore object.
     * @param DateTimeHelper $dateTimeHelper The DateTimeHelper object.
     * @param Environment    $config         The Environment object.
     *
     * @return void
     */
    public function __construct(
        private DbCore $db,
        private LanguageCore $language,
        private DateTimeHelper $dateTimeHelper,
        private Environment $config
    ) {
    }

    /**
     * @param string $dateFrom
     * @param string $dateTo
     *
     * @return array|false
     */
    public function getAllDates(string $dateFrom, string $dateTo): false|array
    {
        $query = "SELECT * FROM zp_calendar WHERE userId = :userId ORDER BY zp_calendar.dateFrom";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

        $stmn->execute();

        return $stmn->fetchAll();
    }

    /**
     * @param string $dateFrom
     * @param string $dateTo
     *
     * @return false|array
     */
    public function getAll(string $dateFrom, string $dateTo): false|array
    {
        return $this->getAllDates($dateFrom, $dateTo);
    }

    /**
     * @param int $userId
     *
     * @return array
     *
     * @throws BindingResolutionException
     */
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

        $sql = "SELECT * FROM zp_calendar WHERE userId = :userId";

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);

        $stmn->execute();
        $values = $stmn->fetchAll();
        $stmn->closeCursor();

        $newValues = array();
        foreach ($values as $value) {

            try {
                $dateFrom = $this->dateTimeHelper->parseDbDateTime($value['dateFrom'])->getTimestamp();
                $dateTo = $this->dateTimeHelper->parseDbDateTime($value['dateTo'])->getTimestamp();
            }catch(\Exception $e) {
                error_log($e);
                continue;
            }

            $allDay = filter_var($value['allDay'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

            $newValues[] = array(
                'title'  => $value['description'],
                'allDay' => $allDay,
                'dateFrom' => array(
                    'y' => date('Y', $dateFrom),
                    'm' => date('m', $dateFrom),
                    'd' => date('d', $dateFrom),
                    'h' => $allDay ? "00" : date('H', $dateFrom),
                    'i' => $allDay ? "00" : date('i', $dateFrom),
                'ical' => date('Ymd\THis', $dateFrom),
                ),
                'dateTo' => array(
                    'y' => date('Y', $dateTo),
                    'm' => date('m', $dateTo),
                    'd' => $allDay ? date('d', ($dateFrom + (60 * 60 * 24))) : date('d', $dateTo),
                    'h' => $allDay ? "00" : date('H', $dateTo),
                    'i' => $allDay ? "00" : date('i', $dateTo),
                    'ical' => date('Ymd\THis', $dateTo),
                ),
                'id' => $value['id'],
                'projectId' => '',
                'eventType' => "calendar",
                'dateContext' => 'plan',
                'backgroundColor' => 'var(--accent1)',
                'borderColor' => 'var(--accent1)',
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
                    $statusColor = $this->classColorMap[$statusLabelsArray[$ticket['projectId']][$ticket['status']]["class"]];
                } else {
                    $statusName = "";
                    $statusColor = "var(--grey)";
                }

                $backgroundColor = "var(--accent2)";

                if ($ticket['dateToFinish'] != "0000-00-00 00:00:00" && $ticket['dateToFinish'] != "1969-12-31 00:00:00") {
                    $dateFrom = $this->dateTimeHelper->parseDbDateTime($ticket['dateToFinish'])->getTimestamp();
                    $dateTo = $this->dateTimeHelper->parseDbDateTime($ticket['dateToFinish'])->getTimestamp();
                    $context = '❕ ' . $this->language->__("label.due_todo");

                    $newValues[] = $this->mapEventData(
                        title:$context . $ticket['headline'] . " (" . $statusName . ")",
                        allDay:false,
                        id: $ticket['id'],
                        projectId: $ticket['projectId'],
                        eventType: "ticket",
                        dateContext: "edit",
                        backgroundColor: $backgroundColor,
                        borderColor: $statusColor,
                        dateFrom: $dateFrom,
                        dateTo: $dateTo
                    );
                }

                if ($this->dateTimeHelper->isValidDateString($ticket['editFrom'])) {

                    // Set ticket to all-day ticket when no time is set
                    $dateFrom = $this->dateTimeHelper->parseDbDateTime($ticket['editFrom']);
                    $dateTo = $this->dateTimeHelper->parseDbDateTime($ticket['editTo']);

                    if($dateFrom->diffInDays($dateTo) >= 1) {
                        $allDay = true;
                    }

                    $context = $this->language->__("label.planned_edit");

                    $newValues[] = $this->mapEventData(
                        title: $context . $ticket['headline'] . " (" . $statusName . ")",
                        allDay:$allDay,
                        id: $ticket['id'],
                        projectId: $ticket['projectId'],
                        eventType: "ticket",
                        dateContext: "edit",
                        backgroundColor: $backgroundColor,
                        borderColor: $statusColor,
                        dateFrom: $dateFrom->getTimestamp(),
                        dateTo: $dateTo->getTimestamp()
                    );
                }
            }
        }

        return $newValues;
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
     */
    private function mapEventData(
        string $title,
        bool $allDay,
        int $id,
        int $projectId,
        string $eventType,
        string $dateContext,
        string $backgroundColor,
        string $borderColor,
        ?int $dateFrom,
        ?int $dateTo
    ): array {
        return array(
            'title'  => $title,
            'allDay' => $allDay,
            'dateFrom' => array(
                'y' => date('Y', $dateFrom),
                'm' => date('m', $dateFrom),
                'd' => date('d', $dateFrom),
                'h' => date('H', $dateFrom),
                'i' => date('i', $dateFrom),
                'ical' => date('Ymd\THis', $dateFrom),
            ),
            'dateTo' => array(
                'y' => date('Y', $dateTo),
                'm' => date('m', $dateTo),
                'd' => date('d', $dateTo),
                'h' => date('H', $dateTo),
                'i' => date('i', $dateTo),
                'ical' => date('Ymd\THis', $dateTo),
            ),
            'id' => $id,
            'projectId' => $projectId,
            'eventType' => $eventType,
            'dateContext' => $dateContext,
            'backgroundColor' => $backgroundColor,
            'borderColor' => $borderColor,
        );
    }

    /**
     * @param string $userHash
     * @param string $calHash
     *
     * @return array|false
     *
     * @throws BindingResolutionException
     */
    public function getCalendarBySecretHash(string $userHash, string $calHash): false|array
    {
        // get user
        $userRepo = app()->make(Users::class);
        $user = $userRepo->getUserBySha($userHash);

        if (!isset($user['id'])) {
            return false;
        }

        // Check if setting exists
        $settingService = app()->make(Setting::class);
        $hash = $settingService->getSetting("usersettings." . $user['id'] . ".icalSecret");

        $_SESSION['usersettings.timezone'] ??= $settingService->getSetting("usersettings." . $user['id'] . ".timezone") ?: $this->config->defaultTimezone;
        date_default_timezone_set($_SESSION['usersettings.timezone']);

        if ($hash !== false && $calHash == $hash) {
            return $this->getCalendar($user['id']);
        } else {
            return false;
        }
    }

    /**
     * @param int $id
     *
     * @return array
     */
    public function getCalendarEventsForToday(int $id): array
    {
        $userTickets = "SELECT
                tickets.dateToFinish,
                tickets.headline,
                tickets.id,
                tickets.editFrom,
                tickets.editTo
            FROM zp_tickets AS tickets
            WHERE
                (tickets.userId = :userId OR tickets.editorId = :userId)
                AND
                (
                    TO_DAYS(tickets.dateToFinish) = TO_DAYS(CURDATE()) OR
                    (TO_DAYS(tickets.editFrom) <= TO_DAYS(CURDATE()) AND TO_DAYS(tickets.editTo) >= TO_DAYS(CURDATE()) )
                )";

        $stmn = $this->db->database->prepare($userTickets);
        $stmn->bindValue(':userId', $id, PDO::PARAM_INT);

        $stmn->execute();
        $tickets = $stmn->fetchAll();
        $stmn->closeCursor();

        $sql = "SELECT * FROM zp_calendar WHERE userId = :userId AND TO_DAYS(dateFrom) = TO_DAYS(CURDATE())";

        $stmn = $this->db->database->prepare($sql);
        $stmn->bindValue(':userId', $id, PDO::PARAM_INT);

        $stmn->execute();
        $values = $stmn->fetchAll();
        $stmn->closeCursor();

        $newValues = array();
        foreach ($values as $value) {
            $dateFrom     = strtotime($value['dateFrom']);
            $dateTo     = strtotime($value['dateTo']);

            $newValues[] = array(
                'title'  => $value['description'],
                'allDay' => $value['allDay'],
                'dateFrom' => array(
                    'y' => date('Y', $dateFrom),
                    'm' => date('m', $dateFrom),
                    'd' => date('d', $dateFrom),
                    'h' => date('H', $dateFrom),
                    'i' => date('i', $dateFrom),
                ),
                'dateTo' => array(
                    'y' => date('Y', $dateTo),
                    'm' => date('m', $dateTo),
                    'd' => date('d', $dateTo),
                    'h' => date('H', $dateTo),
                    'i' => date('i', $dateTo),
                ),
                'id' => $value['id'],
                'eventType' => "calendar",
            );
        }

        if (count($tickets)) {
            foreach ($tickets as $ticket) {
                if ($ticket['dateToFinish'] != "0000-00-00 00:00:00") {
                    $current = strtotime(date("Y-m-d"));
                    $date    = strtotime(date("Y-m-d", strtotime($ticket['dateToFinish'])));

                    $datediff = $date - $current;
                    $difference = floor($datediff / (60 * 60 * 24));
                } else {
                    $difference = 1;
                }

                if ($difference == 0) {
                    $dateFrom = strtotime($ticket['dateToFinish']);
                    $dateTo = strtotime($ticket['dateToFinish']);
                } else {
                    $dateFrom = strtotime($ticket['editFrom']);
                    $dateTo     = strtotime($ticket['editTo']);
                }

                $newValues[] = array(
                    'title'  => 'To-Do: ' . $ticket['headline'],
                    'allDay' => false,
                    'dateFrom' => array(
                        'y' => date('Y', $dateFrom),
                        'm' => date('m', $dateFrom),
                        'd' => date('d', $dateFrom),
                        'h' => date('H', $dateFrom),
                        'i' => date('i', $dateFrom),
                    ),
                    'dateTo' => array(
                        'y' => date('Y', $dateTo),
                        'm' => date('m', $dateTo),
                        'd' => date('d', $dateTo),
                        'h' => date('H', $dateTo),
                        'i' => date('i', $dateTo),
                    ),
                    'id' => $ticket['id'],
                    'eventType' => "ticket",
                );
            }
        }

        return $newValues;
    }

    /**
     * @return array|false
     */
    public function getTicketWishDates(): false|array
    {
        $query = "SELECT id, headline, dateToFinish FROM zp_tickets WHERE (userId = :userId OR editorId = :userId) AND dateToFinish <> '000-00-00 00:00:00'";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

        $stmn->execute();
        $values = $stmn->fetchAll();
        $stmn->closeCursor();

        return $values;
    }

    /**
     * @return array|false
     */
    public function getTicketEditDates(): false|array
    {
        $query = "SELECT id, headline, editFrom, editTo FROM zp_tickets WHERE (userId = :userId OR editorId = :userId) AND editFrom <> '000-00-00 00:00:00'";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

        $stmn->execute();
        $values = $stmn->fetchAll();
        $stmn->closeCursor();

        return $values;
    }

    /**
     * @param array $values
     *
     * @return false|string
     */
    public function addEvent(array $values): false|string
    {
        $query = "INSERT INTO zp_calendar (userId, dateFrom, dateTo, description, allDay) VALUES (:userId, :dateFrom, :dateTo, :description, :allDay)";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);
        $stmn->bindValue(':dateFrom', $values['dateFrom'], PDO::PARAM_STR);
        $stmn->bindValue(':dateTo', $values['dateTo'], PDO::PARAM_STR);
        $stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);
        $stmn->bindValue(':allDay', $values['allDay'], PDO::PARAM_STR);

        if ($stmn->execute()) {
            $id = $this->db->database->lastInsertId();
            $stmn->closeCursor();

            return $id;
        } else {
            $stmn->closeCursor();

            return false;
        }
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function getEvent(int $id): mixed
    {
        $query = "SELECT * FROM zp_calendar WHERE id = :id";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':id', $id, PDO::PARAM_INT);

        $stmn->execute();
        $values = $stmn->fetch();
        $stmn->closeCursor();

        return $values;
    }

    /**
     * @param array $values
     * @param int   $id
     *
     * @return void
     */
    public function editEvent(array $values, int $id): void
    {

        $query = "UPDATE zp_calendar SET
            dateFrom = :dateFrom,
            dateTo = :dateTo,
            description = :description,
            allDay = :allDay
        WHERE id = :id AND userId = :userId LIMIT 1";

        $stmn = $this->db->database->prepare($query);

        $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);
        $stmn->bindValue(':id', $id, PDO::PARAM_INT);
        $stmn->bindValue(':dateFrom', $values['dateFrom'], PDO::PARAM_STR);
        $stmn->bindValue(':dateTo', $values['dateTo'], PDO::PARAM_STR);
        $stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);
        $stmn->bindValue(':allDay', $values['allDay'], PDO::PARAM_STR);

        $stmn->execute();
        $stmn->closeCursor();
    }

    /**
     * @param int $id
     *
     * @return int|false
     */
    public function delPersonalEvent(int $id): int|false
    {
        $query = "DELETE FROM zp_calendar WHERE id = :id AND userId = :userId LIMIT 1";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':id', $id, PDO::PARAM_INT);
        $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

        $value = $stmn->execute();
        $stmn->closeCursor();

        return $value;
    }

    /**
     * @param int $userId
     *
     * @return array|false
     */
    public function getMyExternalCalendars(int $userId): false|array
    {
        $query = "SELECT id, url, name, colorClass FROM zp_gcallinks WHERE userId = :userId";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);

        $stmn->execute();
        $values = $stmn->fetchAll();
        $stmn->closeCursor();

        return $values;
    }

    /**
     * @param int $calendarId
     * @param int $userId
     *
     * @return array|false
     */
    public function getExternalCalendar(int $calendarId, int $userId): false|array
    {
        $query = "SELECT id, url, name, colorClass FROM zp_gcallinks WHERE userId = :userId AND id = :id LIMIT 1";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmn->bindValue(':id', $calendarId, PDO::PARAM_INT);

        $stmn->execute();
        $values = $stmn->fetch();
        $stmn->closeCursor();

        return $values;
    }

    /**
     * @param int $id
     *
     * @return mixed
     */
    public function getGCal(int $id): mixed
    {
        $query = "SELECT id, url, name, colorClass FROM zp_gcallinks WHERE userId = :userId AND id = :id LIMIT 1";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);
        $stmn->bindValue(':id', $id, PDO::PARAM_INT);

        $stmn->execute();
        $values = $stmn->fetch();
        $stmn->closeCursor();

        return $values;
    }

    /**
     * @param array $values
     * @param int   $id
     *
     * @return void
     */
    public function editGUrl(array $values, int $id): void
    {

        $query = "UPDATE zp_gcallinks SET
            url = :url,
            name = :name,
            colorClass = :colorClass
        WHERE userId = :userId AND id = :id LIMIT 1";

        $stmn = $this->db->database->prepare($query);
        $stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
        $stmn->bindValue(':url', $values['url'], PDO::PARAM_STR);
        $stmn->bindValue(':colorClass', $values['colorClass'], PDO::PARAM_STR);
        $stmn->bindValue(':id', $id, PDO::PARAM_INT);
        $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

        $stmn->execute();
        $stmn->closeCursor();
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function deleteGCal(int $id): bool
    {
        $query = "DELETE FROM zp_gcallinks WHERE userId = :userId AND id = :id LIMIT 1";

        $stmn = $this->db->database->prepare($query);

        $stmn->bindValue(':id', $id, PDO::PARAM_INT);
        $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

        $result = $stmn->execute();
        $stmn->closeCursor();

        return $result;
    }

    /**
     * @param array $values
     * @return void
     */
    public function addGUrl(array $values): void
    {

        $query = "INSERT INTO zp_gcallinks (userId, name, url, colorClass) VALUES (:userId, :name, :url, :colorClass)";

        $stmn = $this->db->database->prepare($query);

        $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);
        $stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
        $stmn->bindValue(':url', $values['url'], PDO::PARAM_STR);
        $stmn->bindValue(':colorClass', $values['colorClass'], PDO::PARAM_STR);

        $stmn->execute();
        $stmn->closeCursor();
    }
}
