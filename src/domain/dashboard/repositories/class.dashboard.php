<?php

/**
 * dashboard class
 *
 * @author  Jacob Jensen <jjensen@colibrisdesign.com>
 * @version 1.0
 * @package classes
 */
namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class dashboard
    {

        /**
         * @access public
         * @var    object
         */
        public $db;

        /**
         * @access private
         * @var    array
         */
        private $defaultWidgets = array( 1, 3, 9 );

        /**
         * __construct - neu db connection
         *
         * @access public
         * @return
         */
        public function __construct()
        {

            $this->db = core\db::getInstance();

        }

        public function getMyHours()
        {

        }

        public function getClosedTicketsPerWeek()
        {

            $sql = "SELECT * FROM zp_tickets WHERE status = 0 AND date < DATE_SUB(NOW(),INTERVAL 1 WEEK);";

            $stmn = $this->db->database->prepare($sql);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            $weeks = array();
            foreach ($values as $value) {
                $date = strtotime($value['date']);
                $weeksInYear = round($date / 60 / 60 / 24 / 7);
                //$date = $date / 60 / 60 / 24 / 7;

                if (!isset($weeks[$weeksInYear])) {
                    $weeks[$weeksInYear] =  1;
                } else {
                    $weeks[$weeksInYear] += 1;
                }
            }

            return $weeks;
            /*
            $closedTickets = 0;
            $count = count($values);
            $first = false;
            foreach ($values as $value) {
                if (!$first) {
                    $first = true;
                    $start = $value['date'];
                }

                if ($value['status'] == 0)  // closed tickets
                    $closedTickets++;

            }

            $start     = strtotime($start) / 60 / 60 / 24 / 7;
            $end     = strtotime('NOW') / 60 / 60 / 24 / 7;

            return round(( $end - $start ) / $closedTickets, 3);
            */
        }



        public function getHoursPerTicket()
        {


            $sql = "SELECT 
                        SUM(hours) AS sum,
                        COUNT(DISTINCT zp_tickets.id) AS numTickets 
                    FROM zp_tickets LEFT JOIN zp_timesheets on zp_timesheets.ticketId = zp_tickets.id
                    WHERE zp_tickets.projectId = :projectId AND type <> 'milestone' and type <> 'subtask'
                    GROUP BY zp_tickets.projectId";


            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':projectId', $_SESSION["currentProject"], PDO::PARAM_INT);

            $stmn->execute();
            $tickets = $stmn->fetchAll();
            $stmn->closeCursor();

            if(isset($tickets['sum']) && isset($tickets['numTickets'])){
                return     $tickets['sum']/$tickets['numTickets'];
            }else{
                return false;
            }

        }

        public function getHoursBugFixing()
        {

            $sql = "SELECT id 
				FROM zp_tickets as tickets WHERE type = 'Error Report'";

            $stmn = $this->db->database->prepare($sql);

            $stmn->execute();
            $tickets = $stmn->fetchAll();
            $stmn->closeCursor();

            $sql = "SELECT hours FROM zp_timesheets WHERE ticketId=:ticketId";
            $stmn = $this->db->database->prepare($sql);
            $allHours = 0;
            foreach ($tickets as $ticket) {
                $stmn->bindValue(':ticketId', $ticket['id'], PDO::PARAM_INT);
                $stmn->execute();
                $times = $stmn->fetchAll();

                foreach ($times as $time) {
                    if ($time['hours']) {
                        $allHours += $time['hours'];
                    }
                }

            }

            $stmn->closeCursor();

            return $allHours;
        }

        public function getNotes($userId)
        {

            $sql = 'SELECT * FROM zp_note WHERE userId=:userId';

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function addNote($userId, $values)
        {

            $sql = 'INSERT INTO zp_note (title,description,userId) VALUES (:title,:description,:userId)';

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);
            $stmn->bindValue(':title', $values['title'], PDO::PARAM_STR);
            $stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();
        }

    }


}