<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;

    class calendar
    {
        private repositories\calendar $calendarRepo;
        private core\language $language;

        public function __construct(repositories\calendar $calendarRepo, core\language $language)
        {
            $this->calendarRepo = $calendarRepo;
            $this->language = $language;
        }


        /**
         * Patches calendar event
         *
         * @access public
         * @params $id id of event to be updated (only events can be updated. Tickets need to be updated via ticket api
         * @params $params key value array of columns to be updated
         *
         * @return boolean true on success, false on failure
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
         * @return boolean true on success, false on failure
         */
        private function userIsAllowedToUpdate($eventId)
        {

            if (auth::userIsAtLeast(roles::$admin)) {
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
         * @return integer|false returns the id on success, false on failure
         */
        public function addEvent(array $values): int|false
        {

            if (isset($values['allDay']) === true) {
                $allDay = 'true';
            } else {
                $allDay = 'false';
            }
            $values['allDay'] = $allDay;

            $dateFrom = null;
            if (isset($values['dateFrom']) === true && isset($values['timeFrom']) === true) {
                $dateFrom = $this->language->getISODateTimeString($values['dateFrom'], $values['timeFrom']);
            }
            $values['dateFrom'] = $dateFrom;

            $dateTo = null;
            if (isset($values['dateTo']) === true && isset($values['timeTo']) === true) {
                $dateTo =  $this->language->getISODateTimeString($values['dateTo'], $values['timeTo']);
            }
            $values['dateTo'] = $dateTo;

            if ($values['description'] !== '') {
                $result = $this->calendarRepo->addEvent($values);

                return $result;
            } else {
                return false;
            }
        }
    }

}
