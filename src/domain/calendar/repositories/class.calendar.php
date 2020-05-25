<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class calendar
    {

        /**
         * @access public
         * @var    object
         */
        private $db='';

        /**
         * __construct - get database connection
         *
         * @access public
         */
        public function __construct()
        {

            $this->db = core\db::getInstance();
            $this->language = new core\language();

        }

        public function getAllDates($dateFrom, $dateTo)
        {

            $query = "SELECT * FROM zp_calendar WHERE 
					userId = :userId ORDER BY zp_calendar.dateFrom";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

            $stmn->execute();
            $allDates = $stmn->fetchAll();

            return $allDates;

        }

        public function getCalendar($id)
        {


            $userTickets = "SELECT 
					tickets.dateToFinish, 
					tickets.headline, 
					tickets.id,
					tickets.projectId,
					tickets.editFrom,
					tickets.editTo
				FROM zp_tickets AS tickets
				WHERE (tickets.editorId = :userId OR tickets.userId = :userId) AND tickets.type <> 'Milestone' AND tickets.type <> 'Subtask'";

            $stmn = $this->db->database->prepare($userTickets);
            $stmn->bindValue(':userId', $id, PDO::PARAM_INT);

            $stmn->execute();
            $tickets = $stmn->fetchAll();
            $stmn->closeCursor();

            $sql = "SELECT * FROM zp_calendar WHERE userId = :userId";

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
                        'i' => date('i', $dateFrom)
                    ),
                    'dateTo' => array(
                        'y' => date('Y', $dateTo),
                        'm' => date('m', $dateTo),
                        'd' => date('d', $dateTo),
                        'h' => date('H', $dateTo),
                        'i' => date('i', $dateTo)
                    ),
                    'id' => $value['id'],
                    'projectId' => '',
                    'eventType' => "calendar"
                );
            }

            if (count($tickets)) {
                foreach ($tickets as $ticket) {
                    $context = "";
                    if($ticket['dateToFinish'] != "0000-00-00 00:00:00" && $ticket['dateToFinish'] != "1969-12-31 00:00:00") {

                        $dateFrom = strtotime($ticket['dateToFinish']);
                        $dateTo = strtotime($ticket['dateToFinish']);
                        $context = $this->language->__("label.due_todo");
                    }else{
                        $dateFrom = strtotime($ticket['editFrom']);
                        $dateTo     = strtotime($ticket['editTo']);
                        $context =  $this->language->__("label.planned_edit");
                    }



                    $newValues[] = array(
                        'title'  => $context.$ticket['headline'],
                        'allDay' => true,
                        'dateFrom' => array(
                            'y' => date('Y', $dateFrom),
                            'm' => date('m', $dateFrom),
                            'd' => date('d', $dateFrom),
                            'h' => date('H', $dateFrom),
                            'i' => date('i', $dateFrom)
                        ),
                        'dateTo' => array(
                            'y' => date('Y', $dateTo),
                            'm' => date('m', $dateTo),
                            'd' => date('d', $dateTo),
                            'h' => date('H', $dateTo),
                            'i' => date('i', $dateTo)
                        ),
                        'id' => $ticket['id'],
                        'projectId' => $ticket['projectId'],
                        'eventType' => "ticket"
                    );
                }
            }

            return $newValues;
        }

        public function getCalendarEventsForToday($id)
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
                        'i' => date('i', $dateFrom)
                    ),
                    'dateTo' => array(
                        'y' => date('Y', $dateTo),
                        'm' => date('m', $dateTo),
                        'd' => date('d', $dateTo),
                        'h' => date('H', $dateTo),
                        'i' => date('i', $dateTo)
                    ),
                    'id' => $value['id'],
                    'eventType' => "calendar"
                );
            }

            if (count($tickets)) {
                foreach ($tickets as $ticket) {

                    if($ticket['dateToFinish'] != "0000-00-00 00:00:00") {

                        $current = strtotime(date("Y-m-d"));
                        $date    = strtotime(date("Y-m-d", strtotime($ticket['dateToFinish'])));

                        $datediff = $date - $current;
                        $difference = floor($datediff/(60*60*24));
                    }else{
                        $difference = 1;
                    }

                    if($difference==0) {
                        $dateFrom = strtotime($ticket['dateToFinish']);
                        $dateTo = strtotime($ticket['dateToFinish']);
                    }else{
                        $dateFrom = strtotime($ticket['editFrom']);
                        $dateTo     = strtotime($ticket['editTo']);
                    }

                    $newValues[] = array(
                        'title'  => 'To-Do: ' . $ticket['headline'],
                        'dateFrom' => array(
                            'y' => date('Y', $dateFrom),
                            'm' => date('m', $dateFrom),
                            'd' => date('d', $dateFrom),
                            'h' => date('H', $dateFrom),
                            'i' => date('i', $dateFrom)
                        ),
                        'dateTo' => array(
                            'y' => date('Y', $dateTo),
                            'm' => date('m', $dateTo),
                            'd' => date('d', $dateTo),
                            'h' => date('H', $dateTo),
                            'i' => date('i', $dateTo)
                        ),
                        'id' => $ticket['id'],
                        'eventType' => "ticket"
                    );
                }
            }

            return $newValues;
        }


        public function getTicketWishDates()
        {

            $query = "SELECT id, headline, dateToFinish FROM zp_tickets WHERE (userId = :userId OR editorId = :userId) AND dateToFinish <> '000-00-00 00:00:00'";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }



        public function getTicketEditDates()
        {

            $query = "SELECT id, headline, editFrom, editTo FROM zp_tickets WHERE (userId = :userId OR editorId = :userId) AND editFrom <> '000-00-00 00:00:00'";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function addEvent($values)
        {

            $query = "INSERT INTO zp_calendar (userId, dateFrom, dateTo, description, allDay) 
		VALUES (:userId, :dateFrom, :dateTo, :description, :allDay)";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);
            $stmn->bindValue(':dateFrom', $values['dateFrom'], PDO::PARAM_STR);
            $stmn->bindValue(':dateTo', $values['dateTo'], PDO::PARAM_STR);
            $stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);
            $stmn->bindValue(':allDay', $values['allDay'], PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();

        }

        public function getEvent($id)
        {

            $query = "SELECT * FROM zp_calendar WHERE id = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;

        }

        public function editEvent($values, $id)
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

        public function delPersonalEvent($id)
        {

            $query = "DELETE FROM zp_calendar WHERE id = :id AND userId = :userId LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);
            $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

            $value = $stmn->execute();
            $stmn->closeCursor();

            return $value;

        }

        public function getMyGoogleCalendars()
        {

            $query = "SELECT id, url, name, colorClass FROM zp_gcallinks WHERE userId = :userId";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;

        }

        public function getGCal($id)
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

        public function editGUrl($values, $id)
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

        public function deleteGCal($id)
        {

            $query = "DELETE FROM zp_gcallinks WHERE userId = :userId AND id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_INT);
            $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

            $stmn->execute();
            $stmn->closeCursor();

        }

        public function addGUrl($values)
        {

            $query = "INSERT INTO zp_gcallinks (userId, name, url, colorClass) 
					VALUES 
				(:userId, :name, :url, :colorClass)";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);
            $stmn->bindValue(':name', $values['name'], PDO::PARAM_STR);
            $stmn->bindValue(':url', $values['url'], PDO::PARAM_STR);
            $stmn->bindValue(':colorClass', $values['colorClass'], PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();

        }

    }
}
