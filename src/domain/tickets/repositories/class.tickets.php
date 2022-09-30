<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class tickets
    {

        /**
         * @access public
         * @var    object
         */
        public $result = null;

        /**
         * @access public
         * @var    object
         */
        public $tickets = null;

        /**
         * @access private
         * @var    object
         */
        private $db='';

        /**
         * @access public
         * @var    array
         */
        public $state = array('3' => 'label-important', '1' => 'label-important', '4' => 'label-warning', '2' => 'label-warning', '0' => 'label-success', "-1" =>"label-default");

        /**
         * @access public
         * @var    array
         */
        public $statePlain = array(
            '3' => 'NEW', //New
            '1' => 'ERROR', //In Progress
            '4' => 'INPROGRESS', //In Progress
            '2' => 'APPROVAL', //In Progress
            '0' => 'FINISHED', //Done
            '-1' => "ARCHIVED" //Done
        ); // '5' => 'TAKEN', '6' => 'ERROR');

        public $stateLabels = array(
            'NEW' => 'New', //New
            'ERROR' => 'Blocked', //In Progress
            'INPROGRESS' => 'In Progress', //In Progress
            'APPROVAL' => 'Waiting for Approval', //In Progress
            'FINISHED' => 'Done', //Done
            'ARCHIVED' => "Archived" //Done
        ); // '5' => 'TAKEN', '6' => 'ERROR');

        /**
         * @access public
         * @var    array
         */
        public $os = array('NOT_SPECIFIED','WINDOWS', 'MAC', 'LINUX');

        /**
         * @access public
         * @var    array
         */
        public $browser = array('NOT_SPECIFIED','IE6', 'IE7', 'IE8', 'FIREFOX2', 'FIREFOX3', 'OPERA10', 'KONQUEROR', 'SAFARI9', 'SAFARI10');

        /**
         * @access public
         * @var    array
         */
        public $res = array('NOT_SPECIFIED', '800x600', '1024x768', '1152x864', '1280x1024');

        /**
         * @access public
         * @var    array
         */
        public $priority = array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6');

        /**
         * @access public
         * @var    array
         */
        public $pushed = array('1' => 'PUSHED_TO_STAGING', '2' => 'PUSHED_TO_PROD');

        /**
         * @access public
         * @var    array
         */
        public $efforts = array('1' => 'XS', '2' => 'S', 3=>"M", "5"=>"L", 8 => "XL", 13 => "XXL");

        /**
         * @access public
         * @var    array
         */
        public $type = array('Task', 'Story', 'Bug');

        /**
         * @access private
         * @var    integer
         */
        private $page = 0;

        /**
         * @access public
         * @var    integer
         */
        public $rowsPerPage = 10;

        /**
         * @access private
         * @var    string
         */
        private $limitSelect = "";

        /**
         * @access numPages
         * @var    unknown_type
         */
        public $numPages='';

        /**
         * @access public
         * @var    string
         */
        public $sortBy = 'date';

        /**
         * __construct - get db connection
         *
         * @access public
         * @return unknown_type
         */
        public function __construct()
        {

            $this->db = core\db::getInstance();

            $this->stateLabels = $this->getStateLabels();

        }

        public function getStateLabels()
        {

            //Todo in 2.1: Do not call stateLabels in constructor
            if(isset($_SESSION['currentProject']) === false) return;

            if(isset($_SESSION["projectsettings"]["ticketlabels"])) {

                return $_SESSION["projectsettings"]["ticketlabels"];

            }else{

                $sql = "SELECT
						value
				FROM zp_settings WHERE `key` = :key
				LIMIT 1";

                $stmn = $this->db->{'database'}->prepare($sql);
                $stmn->bindvalue(':key', "projectsettings.".$_SESSION['currentProject'].".ticketlabels", PDO::PARAM_STR);

                $stmn->execute();
                $values = $stmn->fetch();
                $stmn->closeCursor();

                $labels = $this->stateLabels;
                if($values !== false) {
                    $labels = unserialize($values['value']);
                    $_SESSION["projectsettings"]["ticketlabels"] = $labels;
                }else{
                    $_SESSION["projectsettings"]["ticketlabels"] = $this->stateLabels;
                }

                return $labels;

            }
        }

        public function getUnreadTickets($userId,$limit = 9999)
        {

            $read = new read();
            $unreadTickets = array();
            $count = 0;
            $values = $this->getAllBySearch("", "", 0);

            foreach ($values as $ticket) {
                if (!$read->isRead('ticket', $ticket['id'], $userId) && $count < $limit) {
                    $unreadTickets[] = $ticket;
                    $count++;
                }
            }

            return $unreadTickets;
        }

        /**
         * getAll - get all Tickets, depending on userrole
         *
         * @access public
         * @return array
         */
        public function getAll($limit = 9999)
        {

            $id = $_SESSION['userdata']['id'];
            $users = new users();

            if ($users->isAdmin($id)) {
                $values = $this->getAdminTickets($limit);
            } else {
                $values = $this->getUsersTickets($id, $limit);
            }

            return $values;
        }

        public function getUsersTickets($id,$limit)
        {

            $sql = "SELECT
						ticket.id,
						ticket.headline,
						ticket.type, 
						ticket.description,
						ticket.date,
						ticket.dateToFinish,
						ticket.projectId,
						ticket.priority,
						ticket.status,
						project.name as projectName,
						client.name as clientName,
						client.name as clientName,
						t1.firstname AS authorFirstname, 
						t1.lastname AS authorLastname,
						t2.firstname AS editorFirstname,
						t2.lastname AS editorLastname
				FROM 
				zp_tickets AS ticket
				LEFT JOIN zp_relationuserproject ON ticket.projectId = zp_relationuserproject.projectId
				LEFT JOIN zp_projects as project ON ticket.projectId = project.id  
				LEFT JOIN zp_clients as client ON project.clientId = client.id
				LEFT JOIN zp_user AS t1 ON ticket.userId = t1.id
				LEFT JOIN zp_user AS t2 ON ticket.editorId = t2.id
								
				WHERE zp_relationuserproject.userId = :id AND ticket.type <> 'Milestone' AND ticket.type <> 'Subtask'
				
				ORDER BY ticket.id DESC";

            if($limit > -1) {
                $sql .= " LIMIT :limit";
            }

            $stmn = $this->db->{'database'}->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            if($limit > -1) {
                $stmn->bindValue(':limit', $limit, PDO::PARAM_INT);
            }
            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function getAdminTickets($limit)
        {

            $sql = "SELECT
						Distinct(ticket.id),
						ticket.headline,
						ticket.type, 
						ticket.description,
						ticket.date,
						ticket.dateToFinish,
						ticket.projectId,
						ticket.priority,
						ticket.status,
						project.name as projectName,
						client.name as clientName,
						client.name as clientName,
						t1.firstname AS authorFirstname, 
						t1.lastname AS authorLastname,
						t2.firstname AS editorFirstname,
						t2.lastname AS editorLastname
				FROM zp_user as user
				INNER JOIN zp_clients as client ON user.clientId = client.id
					RIGHT JOIN zp_projects as project ON client.id = project.clientId  
					RIGHT JOIN zp_tickets as ticket ON project.id = ticket.projectId 
						LEFT JOIN zp_user AS t1 ON ticket.userId = t1.id
						LEFT JOIN zp_user AS t2 ON ticket.editorId = t2.id
				ORDER BY ticket.id DESC
				LIMIT :limit";

            $stmn = $this->db->{'database'}->prepare($sql);
            $stmn->bindvalue(':limit', $limit, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;

        }

        public function buildJSONTimeline($id)
        {

            $path = $_SERVER['DOCUMENT_ROOT']."/userdata/timeline/";
            $file = "timeline-". $id .".json";
            $sql = "SELECT 
						th.changeType, th.changeValue, th.dateModified, th.userId, 
						ticket.headline, ticket.description,
						user.firstname, user.lastname 
					FROM zp_tickethistory as th
					INNER JOIN zp_user as user ON th.userId = user.id
					INNER JOIN zp_tickets as ticket ON th.ticketId = ticket.id
					WHERE ticketId = :ticketId";

            $stmn = $this->db->{'database'}->prepare($sql);
            $stmn->bindValue(':ticketId', $id, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            $response = array();
            $posts = array();
            foreach ($values as $value) {
                $description = $value['description'];
                $posts[] = array(
                    'headline'     => $value['headline'],
                    'text'        => $value['description'],
                    'startDate'    => $value['dateModified'],
                    'asset' => array('caption' => 'Test', 'media' => '', 'credit' => '')
                );
            }

            $response['timeline'] = array('headline' => 'Ticket #'.$id, 'type' => 'default', 'text' => $description, 'date' => $posts);

            $fh = fopen($path.$file, 'w');
            fwrite($fh, json_encode($response));
            fclose($fh);

        }

        public function changeStatus($id,$status)
        {

            $newValues = array();
            $newValues['status'] = $status;
            $this->addTicketChange($_SESSION['userdata']['id'], $id, $newValues);


            $sql = "UPDATE zp_tickets SET status=:status WHERE id=:id";

            $stmn = $this->db->{'database'}->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->bindValue(':status', $status, PDO::PARAM_STR);

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;
        }

        public function updateDates($id,$start,$end)
        {



            $sql = "UPDATE zp_tickets SET editFrom=:start, editTo=:end WHERE id=:id";

            $stmn = $this->db->{'database'}->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->bindValue(':start', $start, PDO::PARAM_STR);
            $stmn->bindValue(':end', $end, PDO::PARAM_STR);

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;
        }

        public function patchTicket($id,$params)
        {

            $sql = "UPDATE zp_tickets SET ";

            foreach($params as $key=>$value){
                $sql .= "".$key."=:".$key.", ";
            }

            $sql .= "id=:id WHERE id=:id LIMIT 1";

            $stmn = $this->db->{'database'}->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            foreach($params as $key=>$value){
                $stmn->bindValue(':'.$key, $value, PDO::PARAM_STR);
            }

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;
        }





        /**
         * getTicketCost - adds up how much the current cost of the ticket is
         *
         * @param  id
         * @return int
         */
        public function getTicketCost($id)
        {

            $query = "SELECT * FROM `zp_timesheets` WHERE ticketId='$id'";

            $this->db->dbQuery($query);

            $total = 0;

            foreach($this->db->dbFetchResults() as $times) {

                $users = new users();

                $user = $users->getUser($times['userId']);

                $wage = $user['wage'];

                $total += ($wage * $times['hours']);

            }

            return $total;
        }

        public function countMyTickets($id)
        {

            $sql = 'SELECT count(*) as count FROM zp_tickets 
				WHERE editorId LIKE "%'.$id.'%"';

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values['count'];
        }

        public function getAssignedTickets($id,$limit=null)
        {

            $sql = 'SELECT id, headline, dateToFinish FROM zp_tickets 
				WHERE editorId LIKE "%'.$id.'%" ORDER BY id DESC';

            if($limit!=null) {
                $sql .= " LIMIT :limit";
            }

            $stmn = $this->db->{'database'}->prepare($sql);

            if ($limit!=null) {
                $stmn->bindValue(':limit', $limit, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * getUSerTickets - get Tickets related to a user and state
         *
         * @param  $status
         * @param  $id
         * @return array
         */
        public function getUserTickets($status, $id)
        {
            $query = "SELECT
						SQL_CALC_FOUND_ROWS
						zp_tickets.id,
						zp_tickets.headline, 
						zp_tickets.description,
						zp_tickets.date,
						zp_tickets.dateToFinish,
						zp_tickets.projectId,
						zp_tickets.priority,
						zp_tickets.status,
						zp_projects.name AS projectName,
						zp_clients.name AS clientName,
						t1.lastname AS authorLastname,
						t1.firstname AS authorFirstname, 
						t2.firstname AS editorFirstname,
						t2.lastname AS editorLastname
					FROM 
						zp_tickets JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
						JOIN zp_clients ON zp_projects.clientId = zp_clients.id
						LEFT JOIN zp_user AS t1 ON zp_tickets.userId = t1.id
						LEFT JOIN zp_user AS t2 ON zp_tickets.editorId = t2.id
						
					WHERE 
						(zp_tickets.userId = '".$id."'
							OR
						zp_tickets.editorId = '".$id."')
					AND zp_tickets.status IN( ".$status." )
					GROUP BY
						zp_tickets.id
						
					ORDER BY ".$this->sortBy." DESC ".$this->limitSelect."";

            $this->db->dbQuery($query);

            return $this->db->dbFetchResults();
        }

        public function getAvailableUsersForTicket()
        {
            /*
             *  A user is not an "editor"
            $sql = "SELECT
                        Distinct(projectRelation.userId),
                        user.username, user.firstname, user.lastname, user.id
                    FROM zp_relationuserproject as projectRelation
                    INNER JOIN zp_user as user ON projectRelation.userId = user.id
                    WHERE projectId=:id";

            $stmn = $this->db->{'database'}->prepare($sql);
            $stmn->bindValue(':id', $projectId, PDO::PARAM_INT);

            $stmn->execute();
            $users = $stmn->fetchAll();
            $stmn->closeCursor();
            */

            //Get the projects the current user is assigned to

            $sql = "SELECT 
					DISTINCT user.username, 
					user.firstname, 
					user.lastname, 
					user.id 
				FROM zp_user as user 
				JOIN zp_relationuserproject ON user.id = zp_relationuserproject.userId
				
				WHERE zp_relationuserproject.projectId IN 
				(
					SELECT 
						zp_relationuserproject.projectId 
					FROM zp_relationuserproject WHERE userId = ".$_SESSION['userdata']["id"]."
				)";

            $stmn = $this->db->{'database'}->prepare($sql);


            $stmn->execute();
            $admin = $stmn->fetchAll();
            $stmn->closeCursor();

            return $admin;
        }

        /**
         * getNumPages - get REAL number of pages even with LIMIT in SELECT-statement
         *
         * @access public
         * @return integer
         */
        public function getNumPages()
        {

            $row = $this->db->dbQuery("SELECT FOUND_ROWS()")->dbFetchResults();

            $numRows = (int)$row[0]['FOUND_ROWS()'];

            $numPages = ceil($numRows / $this->rowsPerPage);

            return $numPages;
        }

        /**
         * pageLimiter - set the LIMIT-statement for a query
         *
         * @access public
         */
        public function pageLimiter()
        {

            $this->limitSelect = " LIMIT 0, 10";

            $begin = $this->page * $this->rowsPerPage;

            $end = $this->rowsPerPage;

            $this->limitSelect = " LIMIT ".$begin.", ".$end."";

        }

        /**
         * setPage - set the page that is displayed
         *
         * @access public
         * @param  $page
         */
        public function setPage($page)
        {
            if(is_numeric($page) && $page > 0) {
                $this->page = $page - 1;
            }else{
                $this->page = 0;
            }
        }

        /**
         * setRowsPerPage - set the rows that are displayed per page
         *
         * @access public
         * @param  $rows
         */
        public function setRowsPerPage($rows)
        {

            if(is_numeric($page) === true) {

                $this->rowsPerPage = $rows;

            }
        }

        /**
         * getAllBySearch - get Tickets by a serach term and/or a filter
         *
         * @access public
         * @param  $term
         * @param  $filter
         * @return array
         */
        public function getAllBySearch($term, $filter, $closedTickets = 1)
        {

            if($filter == ''  && $term != '') {

                $whereClause = "AND (zp_tickets.id LIKE '%".$term."%' OR '".$term."' IN(zp_tickets.tags) OR zp_tickets.headline LIKE '%".$term."%' OR zp_tickets.description LIKE '%".$term."%')";

            }elseif($filter != '' && $term == '') {

                $whereClause = "AND (zp_tickets.projectId = '".$filter."')";

            }elseif($filter != '' && $term != '') {

                $whereClause = "AND ((zp_tickets.id LIKE '%".$term."%' OR '".$term."' IN(zp_tickets.tags) OR zp_tickets.headline LIKE '%".$term."%' OR zp_tickets.description LIKE '%".$term."%') AND (zp_tickets.projectId = '".$filter."'))";
            }else {
                $whereClause = "";
            }

            if($closedTickets == 0) {
                $whereClause .= " AND zp_tickets.status <> 0 ";
            }


            $query = "SELECT
							zp_tickets.id,
							zp_tickets.headline, 
							zp_tickets.description,
							zp_tickets.date,
							zp_tickets.sprint,
							zp_tickets.storypoints,
							zp_tickets.sortindex,
							zp_tickets.dateToFinish,
							zp_tickets.projectId,
							zp_tickets.priority,
							zp_tickets.type,
							zp_tickets.status,
							zp_tickets.dependingTicketId,
							zp_projects.name AS projectName,
							zp_clients.name AS clientName,
							zp_clients.id AS clientId,
							t1.lastname AS authorLastname,
							t1.firstname AS authorFirstname, 
							t2.firstname AS editorFirstname,
							t2.lastname AS editorLastname,
							COUNT(DISTINCT zp_comment.id) AS commentCount,
							COUNT(DISTINCT zp_file.id) AS fileCount
						FROM 
							zp_tickets 
							LEFT JOIN zp_relationuserproject USING (projectId)
							LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
							LEFT JOIN zp_clients ON zp_projects.clientId = zp_clients.id
							LEFT JOIN zp_user AS t1 ON zp_tickets.userId = t1.id
							LEFT JOIN zp_user AS t2 ON zp_tickets.editorId = t2.id
							LEFT JOIN zp_comment ON zp_tickets.id = zp_comment.moduleId and zp_comment.module = 'ticket'
							LEFT JOIN zp_file ON zp_tickets.id = zp_file.moduleId and zp_file.module = 'ticket'
							WHERE zp_relationuserproject.userId = ".$_SESSION['userdata']['id']." AND (zp_projects.state > '-1' OR zp_projects.state IS NULL)
						 ".$whereClause ."
						
						GROUP BY zp_tickets.id ORDER BY ".$this->sortBy." DESC";



            return $this->db->dbQuery($query)->dbFetchResultsUnmasked();

        }

        /**
         * getAllBySearchCriteria - get Tickets by a serach term and/or a filter
         *
         * @access public
         * @param  $criteria array
         * @param  $filter
         * @return array
         */
        public function getAllBySearchCriteria($searchCriteria, $sort='standard')
        {


            $query = "SELECT
							zp_tickets.id,
							zp_tickets.headline, 
							zp_tickets.description,
							zp_tickets.date,
							zp_tickets.sprint,
							zp_sprints.name as sprintName,
							zp_tickets.storypoints,
							zp_tickets.sortindex,
							zp_tickets.dateToFinish,
							zp_tickets.projectId,
							zp_tickets.priority,
							zp_tickets.type,
							zp_tickets.status,
							zp_tickets.tags,
							zp_tickets.editorId,
							zp_tickets.dependingTicketId,
							zp_tickets.planHours,
							zp_tickets.hourRemaining,
							
							zp_projects.name AS projectName,
							zp_clients.name AS clientName,
							zp_clients.id AS clientId,
							t1.lastname AS authorLastname,
							t1.firstname AS authorFirstname, 
							t1.profileId AS authorProfilePicture,
							t2.firstname AS editorFirstname,
							t2.lastname AS editorLastname,
							milestone.headline AS milestoneHeadline,
							milestone.tags AS milestoneColor,
							COUNT(DISTINCT zp_comment.id) AS commentCount,
							COUNT(DISTINCT zp_file.id) AS fileCount
						FROM 
							zp_tickets 
						LEFT JOIN zp_relationuserproject USING (projectId)
						LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
						LEFT JOIN zp_clients ON zp_projects.clientId = zp_clients.id
						LEFT JOIN zp_user AS t1 ON zp_tickets.userId = t1.id
						LEFT JOIN zp_user AS t2 ON zp_tickets.editorId = t2.id
						LEFT JOIN zp_comment ON zp_tickets.id = zp_comment.moduleId and zp_comment.module = 'ticket'
						LEFT JOIN zp_file ON zp_tickets.id = zp_file.moduleId and zp_file.module = 'ticket'
						LEFT JOIN zp_sprints ON zp_tickets.sprint = zp_sprints.id
						LEFT JOIN zp_tickets AS milestone ON zp_tickets.dependingTicketId = milestone.id AND zp_tickets.dependingTicketId > 0 AND milestone.type = 'milestone'
						
						WHERE zp_relationuserproject.userId = :userId AND zp_tickets.type <> 'subtask' AND zp_tickets.type <> 'milestone'";

            if($_SESSION['currentProject']  != "") {
                $query .= " AND zp_tickets.projectId = :projectId";
            }


            if($searchCriteria["users"]  != "") {
                $editorIdIn = core\db::arrayToPdoBindingString("users", count(explode(",", $searchCriteria["users"])));
                $query .= " AND zp_tickets.editorId IN(" . $editorIdIn. ")";
            }

            if($searchCriteria["milestone"]  != "") {
                $query .= " AND zp_tickets.dependingTicketId = :milestoneId";
            }


            if($searchCriteria["status"]  != "") {
                if($searchCriteria["status"] == "not_done") {
                    $query .= " AND zp_tickets.status > 0";
                }else{
                    $query .= " AND zp_tickets.status IN('".implode("','", explode(",", strip_tags($searchCriteria["status"])))."')";
                }
            }

            if($searchCriteria["searchType"]  != "") {
                $query .= " AND zp_tickets.type = :searchType ";
            }

            if($searchCriteria["searchterm"]  != "") {
                $query .= " AND (FIND_IN_SET(:termStandard, zp_tickets.tags) OR zp_tickets.headline LIKE :termWild OR zp_tickets.description LIKE :termWild)";
            }



            if($searchCriteria["sprint"]  != "" && $searchCriteria["sprint"]  != "all" && $searchCriteria["sprint"]  != "none") {
                $query .= " AND zp_tickets.sprint IN(".strip_tags($searchCriteria["sprint"]).")";
            }

            if($searchCriteria["sprint"]  == "all") {
                $query .= " AND (zp_tickets.sprint <> NULL AND zp_tickets.sprint <> '' AND zp_tickets.sprint IS NOT NULL AND zp_tickets.sprint <> 0)";
            }

            //TODO: Deprecated: Should be -1 for all items not assigned to a sprint
            if($searchCriteria["sprint"]  == "none") {
                $query .= " AND (zp_tickets.sprint IS NULL OR zp_tickets.sprint = '' OR zp_tickets.sprint = 0 OR zp_tickets.sprint = -1)";
            }


            $query .= " GROUP BY zp_tickets.id ";

            if($sort == "standard") {
                $query .= " ORDER BY zp_tickets.sortindex ASC";
            }else if($sort == "kanbansort") {
                $query .= " ORDER BY zp_tickets.kanbanSortIndex ASC";
            }

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

            if($_SESSION['currentProject'] != "") {

                $stmn->bindValue(':projectId', $_SESSION['currentProject'], PDO::PARAM_INT);
            }

            if($searchCriteria["milestone"]  != "") {
                $stmn->bindValue(':milestoneId', $searchCriteria["milestone"], PDO::PARAM_INT);
            }

            if($searchCriteria["searchType"]  != "") {
                $stmn->bindValue(':searchType', $searchCriteria["searchType"], PDO::PARAM_STR);
            }

            if($searchCriteria["users"]  != "") {
                foreach(explode(",", $searchCriteria["users"]) as $key => $user) {
                    $stmn->bindValue(":users" . $key, $user, PDO::PARAM_STR);
                }
            }

            if($searchCriteria["searchterm"]  != "") {
                $termWild = "%".$searchCriteria["searchterm"]."%";
                $stmn->bindValue(':termWild', $termWild, PDO::PARAM_STR);
                $stmn->bindValue(':termStandard', $searchCriteria["searchterm"], PDO::PARAM_STR);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;

        }

        public function getAllSprintsByProject($projectId)
        {

            $query = "SELECT
							zp_tickets.sprint
					FROM 
						zp_tickets 
					WHERE  
						zp_tickets.projectId = :projectId AND zp_tickets.sprint <> ''
					GROUP BY zp_tickets.sprint ORDER BY zp_tickets.sprint ASC";


            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;

        }

        public function updateTicketSorting($project, $ticketSorting)
        {

            $query = "UPDATE zp_tickets
					SET sortindex = :sortIndex,
					sprint = :sprint
					WHERE projectId = :projectId AND id = :ticketId
					LIMIT 1";

            foreach($ticketSorting as $ticket){
                $stmn = $this->db->{'database'}->prepare($query);
                $stmn->bindValue(':projectId', $project, PDO::PARAM_INT);
                $stmn->bindValue(':sortIndex', $ticket["sortIndex"], PDO::PARAM_INT);
                $stmn->bindValue(':sprint', $ticket["sprint"], PDO::PARAM_INT);
                $stmn->bindValue(':ticketId', $ticket["id"], PDO::PARAM_INT);
                $stmn->execute();
            }

            $stmn->closeCursor();

        }

        public function updateTicketStatus($ticketId, $status, $ticketSorting=-1)
        {

            if($ticketSorting > -1) {

                $query = "UPDATE zp_tickets
					SET 
						kanbanSortIndex = :sortIndex,
						status = :status
					WHERE id = :ticketId
					LIMIT 1";


                $stmn = $this->db->{'database'}->prepare($query);
                $stmn->bindValue(':status', $status, PDO::PARAM_INT);
                $stmn->bindValue(':sortIndex', $ticketSorting, PDO::PARAM_INT);
                $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
                $stmn->execute();
            }else{

                $query = "UPDATE zp_tickets
					SET 
						status = :status
					WHERE id = :ticketId
					LIMIT 1";


                $stmn = $this->db->{'database'}->prepare($query);
                $stmn->bindValue(':status', $status, PDO::PARAM_INT);
                $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
                $stmn->execute();

            }

            $stmn->closeCursor();

        }

        /**
         *
         * @access public
         * @param  id
         */
        public function sendAlert($id)
        {

            $mail = new mailer();
            $user = new users();

            // send alert email !
            $row = $user->getUser($id);

            $emailTo = $row['user'];

            $to[] = $emailTo;

            $subject = "Alert: Hours spent have exceeded planned hours";

            $mail->setSubject($subject);

            $text = "Hello ".$emailTo.",
								
			This is a friendly reminder that you have surpassed
								
			the estimated hours for this project. While we 
									
			understand it is impossible to meet every deadline
									
			we encourage you to be as diligent as possible with
									
			your workload.";

            $mail->setText($text);

            $mail->sendMail($to);

        }

        public function addTicketChange($userId,$ticketId,$values)
        {

            $fields = array(
                'headline' => 'headline',
                'type' => 'type',
                'description' => 'description',
                'project' => 'projectId',
                'priority' => 'priority',
                'deadline' => 'dateToFinish',
                'editors' => 'editorId',
                'fromDate' => 'editFrom',
                'toDate' => 'editTo',
                'staging' => 'staging',
                'production' => 'production',
                'planHours'    => 'planHours',
                'status' => 'status');

            $changedFields = array();

            $sql = "SELECT * FROM zp_tickets WHERE id=:ticketId LIMIT 1";

            $stmn = $this->db->{'database'}->prepare($sql);
            $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);

            $stmn->execute();
            $oldValues = $stmn->fetch();
            $stmn->closeCursor();


            // compare table
            foreach($fields as $enum => $dbTable) {

                if (isset($values[$dbTable]) === true && ($oldValues[$dbTable] != $values[$dbTable]) && ($values[$dbTable] != "")) {
                    $changedFields[$enum] = $values[$dbTable];
                }

            }


            $sql = "INSERT INTO zp_tickethistory (
					userId, ticketId, changeType, changeValue, dateModified
				) VALUES (
					:userId, :ticketId, :changeType, :changeValue, NOW()
				)";

            $stmn = $this->db->{'database'}->prepare($sql);

            foreach ($changedFields as $field => $value) {

                $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
                $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
                $stmn->bindValue(':changeType', $field, PDO::PARAM_INT);
                $stmn->bindValue(':changeValue', $value, PDO::PARAM_INT);
                $stmn->execute();
            }

            $stmn->closeCursor();

        }

        /**
         * getTicket - get a specific Ticket depending on the role
         *
         * @access public
         * @param  $id
         * @return array
         */
        public function getTicket($id)
        {

            $query = "SELECT
						zp_tickets.id,
						zp_tickets.headline, 
						zp_tickets.type,
						zp_tickets.description,
						zp_tickets.date,
						DATE_FORMAT(zp_tickets.date, '%Y,%m,%e') AS timelineDate, 
						DATE_FORMAT(zp_tickets.dateToFinish, '%Y,%m,%e') AS timelineDateToFinish, 
						zp_tickets.dateToFinish,
						zp_tickets.projectId,
						zp_tickets.priority,
						zp_tickets.status,
						zp_tickets.sprint,
						zp_tickets.storypoints,
						zp_tickets.hourRemaining,
						zp_tickets.acceptanceCriteria,
						zp_tickets.userId,
						zp_tickets.editorId,
						zp_tickets.planHours,
						zp_tickets.tags,
						zp_tickets.url,
						zp_tickets.editFrom,
						zp_tickets.editTo,
						zp_tickets.dependingTicketId,					
						zp_projects.name AS projectName,
						zp_clients.name AS clientName,
						zp_user.firstname AS userFirstname,
						zp_user.lastname AS userLastname,
						t3.firstname AS editorFirstname,
						t3.lastname AS editorLastname
					FROM 
						zp_tickets LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
						LEFT JOIN zp_clients ON zp_projects.clientId = zp_clients.id
						LEFT JOIN zp_user ON zp_tickets.userId = zp_user.id
						LEFT JOIN zp_user AS t3 ON zp_tickets.editorId = t3.id
					WHERE 
						zp_tickets.id = '".$id."'
					GROUP BY
						zp_tickets.id						
					LIMIT 1";

            return $this->db->dbQuery($query)->dbFetchRowUnmasked();

        }

        public function getAllSubtasks($id)
        {

            $query = "SELECT
						zp_tickets.id,
						zp_tickets.headline, 
						zp_tickets.type,
						zp_tickets.description,
						zp_tickets.date,
						DATE_FORMAT(zp_tickets.date, '%Y,%m,%e') AS timelineDate, 
						DATE_FORMAT(zp_tickets.dateToFinish, '%Y,%m,%e') AS timelineDateToFinish, 
						zp_tickets.dateToFinish,
						zp_tickets.projectId,
						zp_tickets.priority,
						zp_tickets.status,
						zp_tickets.sprint,
						zp_tickets.storypoints,
						zp_tickets.hourRemaining,
						zp_tickets.acceptanceCriteria,
						zp_tickets.userId,
						zp_tickets.editorId,
						zp_tickets.planHours,
						zp_tickets.tags,
						zp_tickets.url,
						zp_tickets.editFrom,
						zp_tickets.editTo,
						zp_tickets.dependingTicketId,					
						zp_projects.name AS projectName,
						zp_clients.name AS clientName,
						zp_user.firstname AS userFirstname,
						zp_user.lastname AS userLastname,
						t3.firstname AS editorFirstname,
						t3.lastname AS editorLastname
					FROM 
						zp_tickets LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
						LEFT JOIN zp_clients ON zp_projects.clientId = zp_clients.id
						LEFT JOIN zp_user ON zp_tickets.userId = zp_user.id
						LEFT JOIN zp_user AS t3 ON zp_tickets.editorId = t3.id
					WHERE 
						zp_tickets.dependingTicketId = :ticketId AND zp_tickets.type = 'subtask'
					GROUP BY
						zp_tickets.id";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':ticketId', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;

        }

        public function getAllMilestones($projectId)
        {

            $query = "SELECT
						zp_tickets.id,
						zp_tickets.headline, 
						zp_tickets.type,
						zp_tickets.description,
						zp_tickets.date,
						DATE_FORMAT(zp_tickets.date, '%Y,%m,%e') AS timelineDate, 
						DATE_FORMAT(zp_tickets.dateToFinish, '%Y,%m,%e') AS timelineDateToFinish, 
						zp_tickets.dateToFinish,
						zp_tickets.projectId,
						zp_tickets.priority,
						zp_tickets.status,
						zp_tickets.sprint,
						zp_tickets.storypoints,
						zp_tickets.hourRemaining,
						zp_tickets.acceptanceCriteria,
						zp_tickets.userId,
						zp_tickets.editorId,
						zp_tickets.planHours,
						zp_tickets.tags,
						zp_tickets.url,
						zp_tickets.editFrom,
						zp_tickets.editTo,
						zp_tickets.dependingTicketId,					
						zp_projects.name AS projectName,
						zp_clients.name AS clientName,
						zp_user.firstname AS userFirstname,
						zp_user.lastname AS userLastname,
						t3.firstname AS editorFirstname,
						t3.lastname AS editorLastname,
						SUM(CASE WHEN progressTickets.status = 0 THEN 1 ELSE 0 END) AS doneTickets,
						COUNT(progressTickets.id) AS allTickets,
						CASE WHEN 
						  COUNT(progressTickets.id) > 0 
						THEN 
						  ROUND((SUM(CASE WHEN progressTickets.status = 0 THEN 1 ELSE 0 END) / COUNT(progressTickets.id)) *100) 
						ELSE 
						  0 
						END AS percentDone
					FROM 
						zp_tickets LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
						LEFT JOIN zp_clients ON zp_projects.clientId = zp_clients.id
						LEFT JOIN zp_user ON zp_tickets.userId = zp_user.id
						LEFT JOIN zp_user AS t3 ON zp_tickets.editorId = t3.id
						LEFT JOIN zp_tickets AS progressTickets ON progressTickets.dependingTicketId = zp_tickets.id AND progressTickets.type <> 'Milestone' AND progressTickets.type <> 'Subtask'
					WHERE 
						zp_tickets.type = 'milestone' AND zp_tickets.projectId = :projectId
					
					GROUP BY
						progressTickets.dependingTicketId, zp_tickets.id
					ORDER BY zp_tickets.editFrom ASC";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll(PDO::FETCH_CLASS, 'leantime\domain\models\tickets');
            $stmn->closeCursor();

            return $values;

        }

        public function getTicketHistory($id)
        {

            $sql = "SELECT 
		 	ticket.headline, history.userId, history.ticketId, history.changeType, history.changeValue, history.ticketId, history.dateModified,
		 	user.firstname, user.lastname
		 FROM zp_tickethistory as history
		  	INNER JOIN zp_user as user ON history.userId = user.id 
		  	INNER JOIN zp_tickets as ticket ON history.ticketId = ticket.id
		 WHERE history.ticketId = :ticketId ORDER BY history.id DESC";

            $stmn = $this->db->{'database'}->prepare($sql);
            $stmn->bindValue(':ticketId', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * getStatus - get the Status from the status array
         *
         * @access public
         * @param  $status
         * @return string
         */
        public function getStatus($status)
        {

            if($status !== null && $status !== '') {

                if(array_key_exists($status, $this->state)=== true) {
                    return $this->state[$status];
                }else{
                    return $this->state[3];
                }

            }else{

                return $this->state[3];
            }
        }

        public function getStatusPlain($status)
        {

            if($status !== null && $status !== '') {

                return $this->statePlain[$status];

            }else{

                return $this->statePlain[3];
            }
        }

        /**
         * getType - get the Type from the type array
         *
         * @access public
         * @param  $type
         * @return string
         */
        public function getType()
        {
            return $this->type;
        }


        /**
         * getPriority - get the priority from the priority array
         *
         * @access public
         * @param  $priority
         * @return string
         */
        public function getPriority($priority)
        {

            if($priority !== null && $priority !== '') {

                return $this->priority[$priority];

            }else{

                return $this->priority[1];

            }
        }

        /**
         * addTicket - add a Ticket with postback test
         *
         * @access public
         * @param  array $values
         */
        public function addTicket(array $values)
        {


            $query = "INSERT INTO zp_tickets (
						headline, 
						type, 
						description, 
						date, 
						dateToFinish, 
						projectId, 
						status, 
						userId, 
						tags, 
						sprint,
						storypoints,
						hourRemaining,
						planHours,
						acceptanceCriteria,
						editFrom, 
						editTo, 
						editorId,
						dependingTicketId
				) VALUES (
						:headline,
						:type,
						:description,
						:date,
						:dateToFinish,
						:projectId,
						:status,
						:userId,
						:tags,
						:sprint,
						:storypoints,
						:hourRemaining,
						:planHours,
						:acceptanceCriteria,
						:editFrom,
						:editTo,
						:editorId,
						:dependingTicketId
				)";

            $stmn = $this->db->{'database'}->prepare($query);

            $stmn->bindValue(':headline', $values['headline'], PDO::PARAM_STR);
            $stmn->bindValue(':type', $values['type'], PDO::PARAM_STR);
            $stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);
            $stmn->bindValue(':date', $values['date'], PDO::PARAM_STR);
            $stmn->bindValue(':dateToFinish', $values['dateToFinish'], PDO::PARAM_STR);
            $stmn->bindValue(':projectId', $values['projectId'], PDO::PARAM_STR);
            $stmn->bindValue(':status', $values['status'], PDO::PARAM_STR);
            $stmn->bindValue(':userId', $values['userId'], PDO::PARAM_STR);
            $stmn->bindValue(':tags', $values['tags'], PDO::PARAM_STR);

            $stmn->bindValue(':sprint', $values['sprint'], PDO::PARAM_STR);
            $stmn->bindValue(':storypoints', $values['storypoints'], PDO::PARAM_STR);
            $stmn->bindValue(':hourRemaining', $values['hourRemaining'], PDO::PARAM_STR);
            $stmn->bindValue(':planHours', $values['planHours'], PDO::PARAM_STR);
            $stmn->bindValue(':acceptanceCriteria', $values['acceptanceCriteria'], PDO::PARAM_STR);

            $stmn->bindValue(':editFrom', $values['editFrom'], PDO::PARAM_STR);
            $stmn->bindValue(':editTo', $values['editTo'], PDO::PARAM_STR);
            $stmn->bindValue(':editorId', $values['editorId'], PDO::PARAM_STR);

            if(isset($values['dependingTicketId'])) {
                $depending = $values['dependingTicketId'];
            }else{
                $depending = "";
            }

            $stmn->bindValue(':dependingTicketId', $depending, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();

            return $this->db->{'database'}->lastInsertId();

        }

        /**
         * updateTicket - Update Ticketinformation
         *
         * @access public
         * @param  array $values
         * @param  $id
         */
        public function updateTicket(array $values, $id)
        {

            $this->addTicketChange($_SESSION['userdata']['id'], $id, $values);

            $query = "UPDATE zp_tickets
			SET 
				headline = :headline,
				type = :type,
				description=:description,
				projectId=:projectId, 
				status = :status,			
				dateToFinish = :dateToFinish,
				sprint = :sprint,
				storypoints = :storypoints,
				hourRemaining = :hourRemaining,
				planHours = :planHours,
				tags = :tags,
				editorId = :editorId,
				editFrom = :editFrom,
				editTo = :editTo,
				acceptanceCriteria = :acceptanceCriteria,
				dependingTicketId = :dependingTicketId,
                updatedAt = UTC_TIMESTAMP()
			WHERE id = :id LIMIT 1";

            $stmn = $this->db->{'database'}->prepare($query);

            $stmn->bindValue(':headline', $values['headline'], PDO::PARAM_STR);
            $stmn->bindValue(':type', $values['type'], PDO::PARAM_STR);
            $stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);
            $stmn->bindValue(':projectId', $values['projectId'], PDO::PARAM_STR);
            $stmn->bindValue(':status', $values['status'], PDO::PARAM_STR);
            $stmn->bindValue(':dateToFinish', $values['dateToFinish'], PDO::PARAM_STR);
            $stmn->bindValue(':sprint', $values['sprint'], PDO::PARAM_STR);
            $stmn->bindValue(':storypoints', $values['storypoints'], PDO::PARAM_STR);
            $stmn->bindValue(':hourRemaining', $values['hourRemaining'], PDO::PARAM_STR);
            $stmn->bindValue(':acceptanceCriteria', $values['acceptanceCriteria'], PDO::PARAM_STR);
            $stmn->bindValue(':planHours', $values['planHours'], PDO::PARAM_STR);
            $stmn->bindValue(':tags', $values['tags'], PDO::PARAM_STR);
            $stmn->bindValue(':editorId', $values['editorId'], PDO::PARAM_STR);
            $stmn->bindValue(':editFrom', $values['editFrom'], PDO::PARAM_STR);
            $stmn->bindValue(':editTo', $values['editTo'], PDO::PARAM_STR);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->bindValue(':dependingTicketId', $values['dependingTicketId'], PDO::PARAM_STR);


            $stmn->execute();

            $stmn->closeCursor();

            return true;
        }

        /**
         * delTicket - delete a Ticket and all dependencies
         *
         * @access public
         * @param  $id
         */
        public function delticket($id)
        {

            $query = "DELETE FROM zp_tickets WHERE id = '".$id."'";

            $this->db->dbQuery($query);

        }

        public function delMilestone($id)
        {

            $query = "UPDATE zp_tickets
                SET 
                    dependingTicketId = ''
                WHERE dependingTicketId = :id";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();


            $query = "UPDATE zp_canvas_items
                SET 
                    milestoneId = ''
                WHERE milestoneId = :id";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();


            $query = "DELETE FROM zp_tickets WHERE id = :id";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();

        }

        /**
         * punchIn - clock in on a specified ticket
         *
         * @access public
         * @param  $ticketId
         */
        public function punchIn($ticketId)
        {

            $sql = "INSERT INTO `zp_punch_clock` (id,userId,punchIn) VALUES ('$ticketId','".$_SESSION['userdata']['id']."',UNIX_TIMESTAMP(CURRENT_TIMESTAMP))";

            $this->db->dbQuery($sql);
        }

        /**
         * punchOut - clock out on whatever ticket is open for the user
         *
         * @access public
         */
        public function punchOut($ticketId)
        {

            $sql = "SELECT * FROM `zp_punch_clock` WHERE userId='".$_SESSION['userdata']['id']."' AND id = '".$ticketId."' LIMIT 1";

            foreach ($this->db->dbQuery($sql)->dbFetchResults() as $time) {
                $inTimestamp = $time['punchIn'];
                $ticketId = $time['id'];
            }

            $outTimestamp = time();

            $seconds =  ( $outTimestamp - $inTimestamp );

            $totalMinutesWorked = $seconds / 60;

            $hoursWorked = round(($totalMinutesWorked / 60), 2);

            $sql = "DELETE FROM `zp_punch_clock` WHERE userId='".$_SESSION['userdata']['id']."' AND id = '".$ticketId."' LIMIT 1 ";
            $this->db->dbQuery($sql);

            //At least 6 minutes
            if($hoursWorked >= 0.10) {

                $sql = "INSERT INTO `zp_timesheets` (userId,ticketId,workDate,hours,kind) 
				VALUES
                ('".$_SESSION['userdata']['id']."','".addslashes($ticketId)."',CURRENT_TIMESTAMP,'$hoursWorked','GENERAL_BILLABLE');";

                $this->db->dbQuery($sql);

            }else{
                $hoursWorked = 0;
            }

            return $hoursWorked;
        }

        /**
         * isClocked - Checks to see whether a user is clocked in
         *
         * @access public
         * @param  id
         */
        public function isClocked($id)
        {

            $sql = "SELECT 
                     zp_punch_clock.id,
                     zp_punch_clock.userId,
                     zp_punch_clock.minutes,
                     zp_punch_clock.hours,
                     zp_punch_clock.punchIn,
                     zp_tickets.headline
                  FROM `zp_punch_clock` 
                  LEFT JOIN zp_tickets ON zp_punch_clock.id = zp_tickets.id WHERE zp_punch_clock.userId='".$id."' LIMIT 1";

            $onTheClock = false;
            $query = $this->db->dbQuery($sql)->dbFetchResults();

            if (count($query) > 0) {
                $onTheClock["id"] = $query[0]["id"];
                $onTheClock["since"] = $query[0]["punchIn"];
                $onTheClock["headline"] = $query[0]["headline"];
                $start_date = new \DateTime();
                $start_date->setTimestamp($query[0]["punchIn"]);
                $since_start = $start_date->diff(new \DateTime('NOW'));

                $r = $since_start->format('%H:%I');

                $onTheClock["totalTime"] = $r;
            }

            return $onTheClock;
        }

        /**
         * Checks whether a user has access to a ticket or not
         */
        public function getAccessRights($id)
        {

            $sql = "SELECT 
				
				zp_relationuserproject.userId
				
			FROM zp_tickets
			
			LEFT JOIN zp_relationuserproject ON zp_tickets.projectId = zp_relationuserproject.projectId
			
			WHERE zp_tickets.id=:id AND zp_relationuserproject.userId = :user";

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->bindValue(':user', $_SESSION['userdata']['id'], PDO::PARAM_STR);


            $stmn->execute();
            $result = $stmn->fetchAll();
            $stmn->closeCursor();

            if (count($result) > 0) {
                return true;
            }else{
                return false;
            }

        }

        public function getTimelineHistory($id)
        {

            $sql = "SELECT 
						th.changeType, 
						th.changeValue, 
						DATE_FORMAT(th.dateModified, '%Y,%m,%e') AS date, 
						th.userId, 
						ticket.id as ticketId, 
						ticket.headline, 
						ticket.description,
						user.firstname, 
						user.lastname,
						user.id AS userId
					FROM zp_tickethistory as th
					INNER JOIN zp_user as user ON th.userId = user.id
					INNER JOIN zp_tickets as ticket ON th.ticketId = ticket.id
					WHERE ticketId = :id";

            $stmn = $this->db->{'database'}->prepare($sql);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();
            $result = $stmn->fetchAll();
            $stmn->closeCursor();

            return $result;

        }

        public function getTicketUsers($id)
        {

            $sql = "SELECT 	
						user.username AS authorEditor,
						editors.username AS editorEmail
					
					FROM zp_tickets AS tickets
					LEFT JOIN zp_user AS user ON tickets.userId = user.id AND (user.notifications = 1 || user.notifications IS NULL)
					LEFT JOIN zp_user AS editors ON tickets.editorId = editors.id AND (editors.notifications = 1 || editors.notifications IS NULL)
					
					WHERE tickets.id = :id LIMIT 1";

            $stmn = $this->db->{'database'}->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();
            $result = $stmn->fetch();
            $stmn->closeCursor();

            $to = array();

            if($result["authorEditor"] != "") {
                $to[] = $result["authorEditor"];
            }

            if($result["editorEmail"] != "" && $result["editorEmail"] != $result["authorEditor"]) {
                $to[] = $result["editorEmail"];
            }

            return $to;
        }

        public function getFirstTicket($projectId)
        {

            $query = "SELECT
						zp_tickets.id,
						zp_tickets.headline, 
						zp_tickets.type,
						zp_tickets.description,
						zp_tickets.date,
						DATE_FORMAT(zp_tickets.date, '%Y,%m,%e') AS timelineDate, 
						DATE_FORMAT(zp_tickets.dateToFinish, '%Y,%m,%e') AS timelineDateToFinish, 
						zp_tickets.dateToFinish,
						zp_tickets.projectId,
						zp_tickets.priority,
						zp_tickets.status,
						zp_tickets.sprint,
						zp_tickets.storypoints,
						zp_tickets.hourRemaining,
						zp_tickets.acceptanceCriteria,
						zp_tickets.userId,
						zp_tickets.editorId,
						zp_tickets.planHours,
						zp_tickets.tags,
						zp_tickets.url,
						zp_tickets.editFrom,
						zp_tickets.editTo,
						zp_tickets.dependingTicketId

					FROM 
						zp_tickets
					WHERE 
						zp_tickets.type <> 'milestone' AND zp_tickets.type <> 'subtask' AND zp_tickets.projectId = :projectId
                    ORDER BY
					    zp_tickets.date ASC
					LIMIT 1";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, 'leantime\domain\models\tickets');
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;

        }


        public function getNumberOfAllTickets($projectId)
        {

            $query = "SELECT
						COUNT(zp_tickets.id) AS allTickets
					FROM 
						zp_tickets
					WHERE 
						zp_tickets.type <> 'milestone' AND zp_tickets.type <> 'subtask' AND zp_tickets.projectId = :projectId
                    ORDER BY
					    zp_tickets.date ASC
					LIMIT 1";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();

            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values['allTickets'];

        }

        public function getNumberOfClosedTickets($projectId)
        {

            $query = "SELECT
						COUNT(zp_tickets.id) AS allTickets
					FROM 
						zp_tickets
					WHERE 
						zp_tickets.type <> 'milestone' AND zp_tickets.type <> 'subtask' AND zp_tickets.projectId = :projectId
						AND zp_tickets.status < 1
                    ORDER BY
					    zp_tickets.date ASC
					LIMIT 1";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();

            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values['allTickets'];

        }

        public function getEffortOfClosedTickets($projectId, $averageStorySize)
        {

            $query = "SELECT
						SUM(CASE when zp_tickets.storypoints <> '' then zp_tickets.storypoints else :avgStorySize end) AS allEffort
					FROM 
						zp_tickets
					WHERE 
						zp_tickets.type <> 'milestone' AND zp_tickets.type <> 'subtask' AND zp_tickets.projectId = :projectId
						AND zp_tickets.status < 1
                    ORDER BY
					    zp_tickets.date ASC
					LIMIT 1";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            $stmn->bindValue(':avgStorySize', $averageStorySize, PDO::PARAM_INT);


            $stmn->execute();

            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values['allEffort'];

        }

        public function getEffortOfAllTickets($projectId, $averageStorySize)
        {

            $query = "SELECT
						SUM(CASE when zp_tickets.storypoints <> '' then zp_tickets.storypoints else :avgStorySize end) AS allEffort
					FROM 
						zp_tickets
					WHERE 
						zp_tickets.type <> 'milestone' AND zp_tickets.type <> 'subtask' AND zp_tickets.projectId = :projectId
                    ORDER BY
					    zp_tickets.date ASC
					LIMIT 1";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            $stmn->bindValue(':avgStorySize', $averageStorySize, PDO::PARAM_INT);

            $stmn->execute();

            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values['allEffort'];

        }


        public function getAverageTodoSize($projectId)
        {
            $query = "SELECT
						AVG(zp_tickets.storypoints) as avgSize
					FROM 
						zp_tickets
					WHERE 
						zp_tickets.type <> 'milestone' AND zp_tickets.type <> 'subtask' AND 
						(zp_tickets.storypoints <> '' AND zp_tickets.storypoints IS NOT NULL) AND zp_tickets.projectId = :projectId
                    ORDER BY
					    zp_tickets.date ASC
					LIMIT 1";

            $stmn = $this->db->{'database'}->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();

            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values['avgSize'];
        }




    }

}
