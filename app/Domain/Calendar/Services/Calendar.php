<?php

namespace Leantime\Domain\Calendar\Services {

    use Leantime\Core\Language as LanguageCore;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Calendar\Repositories\Calendar as CalendarRepository;

    /**
     *
     */
    class Calendar
    {
        private CalendarRepository $calendarRepo;
        private LanguageCore $language;

        /**
         * @param CalendarRepository $calendarRepo
         * @param LanguageCore       $language
         */
        public function __construct(CalendarRepository $calendarRepo, LanguageCore $language)
        {
            $this->calendarRepo = $calendarRepo;
            $this->language = $language;
        }

        /**
         * Deletes a Google Calendar.
         *
         * @param int $id The ID of the Google Calendar to delete.
         * @return bool Returns true if the Google Calendar was successfully deleted, false otherwise.
         */
        public function deleteGCal(int $id): bool
        {
            return $this->calendarRepo->deleteGCal($id);
        }

        /**
         * Patches calendar event
         *
         * @access public
         * @params $id id of event to be updated (only events can be updated. Tickets need to be updated via ticket api
         * @params $params key value array of columns to be updated
         *
         * @return bool true on success, false on failure
         */
        public function patch($id, $params): bool
        {

            //Admins can always change anything.
            //Otherwise user has to own the event
            if ($this->userIsAllowedToUpdate($id)) {
                return $this->calendarRepo->patch($id, $params);
            }

            return false;
        }

        /**
         * Checks if user is allowed to make changes to event
         *
         * @access public
         * @params int $eventId Id of event to be checked
         *
         * @return bool true on success, false on failure
         */
        private function userIsAllowedToUpdate($eventId): bool
        {

            if (Auth::userIsAtLeast(Roles::$admin)) {
                return true;
            } else {
                $event = $this->calendarRepo->getEvent($eventId);
                if ($event && $event["userId"] == $_SESSION['userdata']['id']) {
                    return true;
                }
            }

            return false;
        }


        /**
         * Adds a new event to the users calendar
         *
         * @access public
         * @params array $values array of event values
         *
         * @return int|false returns the id on success, false on failure
         */
        public function addEvent(array $values): int|false
        {


            $values['allDay'] = $values['allDay'] ?? false;

            $dateFrom = null;
            if (isset($values['dateFrom']) === true && isset($values['timeFrom']) === true) {
                $dateFrom = format($values['dateFrom'], $values['timeFrom'])->isoDateTimeFrom24h();
            }
            $values['dateFrom'] = $dateFrom;

            $dateTo = null;
            if (isset($values['dateTo']) === true && isset($values['timeTo']) === true) {
                $dateTo =  format($values['dateTo'], $values['timeTo'])->isoDateTimeFrom24h();
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
         * @param $eventId
         * @return mixed
         */
        /**
         * @param $eventId
         * @return mixed
         */
        public function getEvent($eventId): mixed
        {
            return $this->calendarRepo->getEvent($eventId);
        }

        /**
         * edits an event on the users calendar
         *
         * @access public
         * @params array $values array of event values
         *
         * @return bool returns true on success, false on failure
         */
        public function editEvent(array $values): bool
        {
            $id = null;
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
                    $dateFrom = format($values['dateFrom'], $values['timeFrom'])->isoDateTime();
                }
                $values['dateFrom'] = $dateFrom;

                $dateTo = null;
                if (isset($values['dateTo']) === true && isset($values['timeTo']) === true) {
                    $dateTo = format($values['dateTo'], $values['timeTo'])->isoDateTime();
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
         * deletes an event on the users calendar
         *
         * @access public
         * @params array $values array of event values
         *
         * @return int|false returns the id on success, false on failure
         */
        public function delEvent($id): int|false
        {
            $result = $this->calendarRepo->delPersonalEvent($id);
            return $result;
        }

        public function getExternalCalendar($id, $userId) {
            return $this->calendarRepo->getExternalCalendar($id, $userId);
        }

        public function editExternalCalendar($values, $id) {
            return $this->calendarRepo->editGUrl($values, $id);
        }
    }
}
