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

            $stmn = $this->db->{'database'}->prepare($sql);

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

            $sql = "SELECT id 
				FROM zp_tickets as tickets";

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->execute();
            $tickets = $stmn->fetchAll();
            $stmn->closeCursor();

            $sql = "SELECT hours FROM zp_timesheets WHERE ticketId=:ticketId";
            $stmn = $this->db->{'database'}->prepare($sql);
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

            if($allHours != '' AND $allHours >0) {

                return $allHours / count($tickets);

            }
        }

        public function getHoursBugFixing()
        {

            $sql = "SELECT id 
				FROM zp_tickets as tickets WHERE type = 'Error Report'";

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->execute();
            $tickets = $stmn->fetchAll();
            $stmn->closeCursor();

            $sql = "SELECT hours FROM zp_timesheets WHERE ticketId=:ticketId";
            $stmn = $this->db->{'database'}->prepare($sql);
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

        public function getWidgets()
        {

            $query = "SELECT * FROM zp_widget";

            $stmn = $this->db->{'database'}->prepare($query);

            $stmn->execute();
            $returnValues= $stmn->fetchAll();
            $stmn->closeCursor();

            return $returnValues;
        }

        public function getWidgetsValue($userId)
        {

            $query = "SELECT value FROM wpd_dashboard_widgets WHERE user_id= :user_id LIMIT 1";

            $stmn = $this->db->{'database'}->prepare($query);

            $stmn->bindValue(':user_id', $userId, PDO::PARAM_INT);

            $stmn->execute();
            $returnValues= $stmn->fetch();
            $stmn->closeCursor();

            return $returnValues;

        }

        public function addWidget($submoduleAlias, $title)
        {

            $query = "INSERT INTO zp_widget (submoduleAlias, title) VALUES (:subAlias, :title)";

            $stmn = $this->db->{'database'}->prepare($query);

            $stmn->bindValue(':subAlias', $submoduleAlias, PDO::PARAM_INT);
            $stmn->bindValue(':title', $title, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->closeCursor();

        }

        public function updateWidgets($userId, $widgetIds)
        {

            $delete = 'DELETE FROM zp_widgetrelation WHERE userId = :userId';
            $insert = 'INSERT INTO zp_widgetrelation (userId, widgetId) VALUES (:userId, :widgetId)';

            $this->db->{'database'}->beginTransaction();

            $stmn = $this->db->{'database'}->prepare($delete);

            $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();

            $stmn = $this->db->{'database'}->prepare($insert);

            foreach ($widgetIds as $widgetId) {

                $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);
                $stmn->bindValue(':widgetId', $widgetId, PDO::PARAM_STR);

                $stmn->execute();
            }

            $stmn->closeCursor();

            $this->db->{'database'}->commit();
        }

        public function userHasWidgets($userId)
        {

            $sql = 'SELECT * FROM zp_widgetrelation WHERE userId=:userId';

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            $return = false;
            if (count($values)) {
                $return = $values;
            }

            return $return;
        }

        public function setDefaultWidgets($userId)
        {

            $sql = 'INSERT INTO zp_widgetrelation (userId,widgetId) VALUES (:userId,:widgetId)';

            $stmn = $this->db->{'database'}->prepare($sql);

            foreach ($this->defaultWidgets as $widget) {
                $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);
                $stmn->bindValue(':widgetId', $widget, PDO::PARAM_STR);

                $stmn->execute();
            }

            $stmn->closeCursor();
        }

        public function getNotes($userId)
        {

            $sql = 'SELECT * FROM zp_note WHERE userId=:userId';

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function addNote($userId, $values)
        {

            $sql = 'INSERT INTO zp_note (title,description,userId) VALUES (:title,:description,:userId)';

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->bindValue(':userId', $userId, PDO::PARAM_STR);
            $stmn->bindValue(':title', $values['title'], PDO::PARAM_STR);
            $stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);

            $stmn->execute();
            $stmn->closeCursor();
        }

    }


}