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
        public $statusClasses = array('3' => 'label-info', '1' => 'label-important', '4' => 'label-warning', '2' => 'label-warning', '0' => 'label-success', "-1" =>"label-default");

        /**
         * @access public
         * @var    array
         */
        public $statusNumByKey = array('NEW' => 3, 'ERROR' => 1, 'INPROGRESS' => 4, 'APPROVAL' => 2, 'FINISHED' => 0, "ARCHIVED" =>-1);


        /**
         * @access public
         * @var    array
         */
        public $statusList = array(
            '3' => 'status.new', //New
            '1' => 'status.blocked', //In Progress
            '4' => 'status.in_progress', //In Progress
            '2' => 'status.waiting_for_approval', //In Progress
            '0' => 'status.done', //Done
            '-1' => 'status.archived' //Done
        );

        /**
         * @access public
         * @var    array
         */
        public $priority = array('1' => 'Critical', '2' => 'High', '3' => 'Medium', '4' => 'Low');

        /**
         * @access public
         * @var    array
         */
        public $efforts = array('1' => 'XS', '2' => 'S', 3=>"M", "5"=>"L", 8 => "XL", 13 => "XXL");

        /**
         * @access public
         * @var    array
         */
        public $type = array('task', 'story', 'bug');

        /**
         * @access public
         * @var    array
         */
        public $typeIcons = array('story' => 'fa-book', 'task' => 'fa-check-square', 'bug' => 'fa-bug');

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

        private $language = "";

        /**
         * __construct - get db connection
         *
         * @access public
         * @return unknown_type
         */
        public function __construct()
        {

            $this->db = core\db::getInstance();
            $this->language = new core\language();

        }

        public function getStateLabels()
        {

            if(isset($_SESSION["projectsettings"]["ticketlabels"])) {

                return $_SESSION["projectsettings"]["ticketlabels"];

            }else{

                $sql = "SELECT
						value
				FROM zp_settings WHERE `key` = :key
				LIMIT 1";

                $stmn = $this->db->database->prepare($sql);
                $stmn->bindvalue(':key', "projectsettings.".$_SESSION['currentProject'].".ticketlabels", PDO::PARAM_STR);

                $stmn->execute();
                $values = $stmn->fetch();
                $stmn->closeCursor();

                $labels = array();

                //preseed state labels with default values
                foreach($this->statusList as $key=>$label) {
                    $labels[$key] = array(
                        "name" => $this->language->__($label),
                        "class" => $this->statusClasses[$key]
                    );
                }

                //Override the state values that are in the db
                if($values !== false) {

                    foreach(unserialize($values['value']) as $key=>$label) {

                        //Custom key in the database represents the string value. Needs to be translated to numeric status value
                        if(!is_int($key)) {
                            $numericKey = $this->statusNumByKey[$key];
                        }else{
                            $numericKey = $key;
                        }

                        $labels[$numericKey] = array(
                            "name" => $label,
                            "class" => $this->statusClasses[$numericKey]
                        );
                    }

                }

                $_SESSION["projectsettings"]["ticketlabels"] = $labels;

                return $labels;

            }
        }

        public function getStatusList() {
            return $this->statusList;
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

            $values = $this->getUsersTickets($id, $limit);

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
				GROUP BY ticket.id
				ORDER BY ticket.id DESC";

            if($limit > -1) {
                $sql .= " LIMIT :limit";
            }

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            if($limit > -1) {
                $stmn->bindValue(':limit', $limit, PDO::PARAM_INT);
            }
            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function getAvailableUsersForTicket()
        {

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

            $stmn = $this->db->database->prepare($sql);


            $stmn->execute();
            $admin = $stmn->fetchAll();
            $stmn->closeCursor();

            return $admin;
        }

        /**
         * getAllBySearchCriteria - get Tickets by a serach term and/or a filter
         *
         * @access public
         * @param  $searchCriteria array
         * @param  $sort
         * @return array | bool
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
							(SELECT ROUND(SUM(hours), 2) FROM zp_timesheets WHERE zp_tickets.id = zp_timesheets.ticketId) AS bookedHours,
							zp_projects.name AS projectName,
							zp_clients.name AS clientName,
							zp_clients.id AS clientId,
							t1.lastname AS authorLastname,
							t1.firstname AS authorFirstname, 
							t1.profileId AS authorProfileId,
							t2.firstname AS editorFirstname,
							t2.lastname AS editorLastname,
							t2.profileId AS editorProfileId,
							milestone.headline AS milestoneHeadline,
							IF((milestone.tags IS NULL OR milestone.tags = ''), '#1b75bb', milestone.tags) AS milestoneColor,
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
						LEFT JOIN zp_timesheets AS timesheets ON zp_tickets.id = timesheets.ticketId
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

                $statusArray = explode(",", $searchCriteria['status']);

                if(array_search("not_done", $statusArray) !== false) {
                    $query .= " AND zp_tickets.status > 0";
                }else {
                    $statusIn = core\db::arrayToPdoBindingString("status", count(explode(",", $searchCriteria["status"])));
                    $query .= " AND zp_tickets.status IN(".$statusIn.")";
                }

            }else{

                $query .= " AND zp_tickets.status <> -1";

            }

            if($searchCriteria["type"]  != "") {
                $query .= " AND LOWER(zp_tickets.type) = LOWER(:searchType) ";
            }

            if($searchCriteria["term"]  != "") {
                $query .= " AND (FIND_IN_SET(:termStandard, zp_tickets.tags) OR zp_tickets.headline LIKE :termWild OR zp_tickets.description LIKE :termWild OR zp_tickets.id LIKE :termWild)";
            }

            if($searchCriteria["sprint"]  > 0 && $searchCriteria["sprint"]  != "all") {
                $sprintIn = core\db::arrayToPdoBindingString("sprint", count(explode(",", $searchCriteria["sprint"])));
                $query .= " AND zp_tickets.sprint IN(".$sprintIn.")";
            }

            if($searchCriteria["sprint"]  == "backlog" ) {
                $query .= " AND (zp_tickets.sprint IS NULL OR zp_tickets.sprint = '' OR zp_tickets.sprint = -1)";
            }

            $query .= " GROUP BY zp_tickets.id ";

            if($sort == "standard") {
                $query .= " ORDER BY zp_tickets.sortindex ASC, zp_tickets.id DESC";
            }else if($sort == "kanbansort") {
                $query .= " ORDER BY zp_tickets.kanbanSortIndex ASC, zp_tickets.id DESC";
            }else if($sort == "duedate") {
                $query .= " ORDER BY zp_tickets.dateToFinish ASC, zp_tickets.sortindex ASC, zp_tickets.id DESC";
            }

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':userId', $_SESSION['userdata']['id'], PDO::PARAM_INT);

            if($_SESSION['currentProject'] != "") {

                $stmn->bindValue(':projectId', $_SESSION['currentProject'], PDO::PARAM_INT);
            }

            if($searchCriteria["milestone"]  != "") {
                $stmn->bindValue(':milestoneId', $searchCriteria["milestone"], PDO::PARAM_INT);
            }

            if($searchCriteria["type"]  != "") {
                $stmn->bindValue(':searchType', $searchCriteria["type"], PDO::PARAM_STR);
            }

            if($searchCriteria["users"]  != "") {
                foreach(explode(",", $searchCriteria["users"]) as $key => $user) {
                    $stmn->bindValue(":users" . $key, $user, PDO::PARAM_STR);
                }
            }

            $statusArray = explode(",", $searchCriteria['status']);
            if($searchCriteria["status"]  != "" && array_search("not_done", $statusArray) === false) {
                foreach(explode(",", $searchCriteria["status"]) as $key => $status) {
                    $stmn->bindValue(":status" . $key, $status, PDO::PARAM_STR);
                }
            }

            if($searchCriteria["sprint"]  > 0 && $searchCriteria["sprint"]  != "all") {
                foreach(explode(",", $searchCriteria["sprint"]) as $key => $sprint) {
                    $stmn->bindValue(":sprint" . $key, $sprint, PDO::PARAM_STR);
                }
            }

            if($searchCriteria["term"]  != "") {
                $termWild = "%".$searchCriteria["term"]."%";
                $stmn->bindValue(':termWild', $termWild, PDO::PARAM_STR);
                $stmn->bindValue(':termStandard', $searchCriteria["term"], PDO::PARAM_STR);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;

        }

        public function getAllByProjectId($projectId)
        {

            $query = "SELECT
						zp_tickets.id,
						zp_tickets.headline, 
						zp_tickets.type,
						zp_tickets.description,
						zp_tickets.date,
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
						zp_tickets.projectId = :projectId
					GROUP BY
						zp_tickets.id";


            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll(PDO::FETCH_CLASS, '\leantime\domain\models\tickets');
            $stmn->closeCursor();

            return $values;

        }

        /**
         * getTicket - get a specific Ticket depending on the role
         *
         * @access public
         * @param  $id
         * @return \leantime\domain\models\tickets|bool
         */
        public function getTicket($id)
        {

            $query = "SELECT
						zp_tickets.id,
						zp_tickets.headline, 
						zp_tickets.type,
						zp_tickets.description,
						zp_tickets.date,
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
						zp_tickets.id = :ticketId
					GROUP BY
						zp_tickets.id						
					LIMIT 1";


            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':ticketId', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchObject('\leantime\domain\models\tickets');
            $stmn->closeCursor();

            return $values;

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

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':ticketId', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;

        }

        public function getAllMilestones($projectId, $includeArchived =false, $sortBy="headline")
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
						depMilestone.headline AS milestoneHeadline,
						IF((depMilestone.tags IS NULL OR depMilestone.tags = ''), '#1b75bb', depMilestone.tags) AS milestoneColor,
						zp_tickets.userId,
						zp_tickets.editorId,
						zp_tickets.planHours,
						IF((zp_tickets.tags IS NULL OR zp_tickets.tags = ''), '#1b75bb', zp_tickets.tags) AS tags,
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
						t3.profileId AS editorProfileId,

						(SELECT SUM(progressSub.planHours) FROM zp_tickets as progressSub WHERE progressSub.dependingTicketId = zp_tickets.id) AS planHours,
						(SELECT SUM(progressSub.hourRemaining) FROM zp_tickets as progressSub WHERE progressSub.dependingTicketId = zp_tickets.id) AS hourRemaining,
						SUM(ROUND(timesheets.hours, 2)) AS bookedHours,						
						
						COUNT(DISTINCT progressTickets.id) AS allTickets,
						
						(SELECT (
                            CASE WHEN 
                              COUNT(DISTINCT progressSub.id) > 0 
                            THEN 
                              ROUND(
                                (
                                  SUM(CASE WHEN progressSub.status < 1 THEN IF(progressSub.storypoints = 0, 3, progressSub.storypoints) ELSE 0 END) / 
                                  SUM(IF(progressSub.storypoints = 0, 3, progressSub.storypoints))
                                ) *100) 
                            ELSE 
                              0 
                            END) AS percentDone
                        FROM zp_tickets AS progressSub WHERE progressSub.dependingTicketId = zp_tickets.id AND progressSub.type <> 'milestone') AS percentDone
					FROM 
						zp_tickets 
						LEFT JOIN zp_projects ON zp_tickets.projectId = zp_projects.id
						LEFT JOIN zp_tickets AS depMilestone ON zp_tickets.dependingTicketId = depMilestone.id 
						LEFT JOIN zp_clients ON zp_projects.clientId = zp_clients.id
						LEFT JOIN zp_user ON zp_tickets.userId = zp_user.id
						LEFT JOIN zp_user AS t3 ON zp_tickets.editorId = t3.id
						LEFT JOIN zp_tickets AS progressTickets ON progressTickets.dependingTicketId = zp_tickets.id AND progressTickets.type <> 'Milestone' AND progressTickets.type <> 'Subtask'
						LEFT JOIN zp_timesheets AS timesheets ON progressTickets.id = timesheets.ticketId
					WHERE 
						zp_tickets.type = 'milestone' AND zp_tickets.projectId = :projectId";

            if($includeArchived === false) {
                $query .= " AND zp_tickets.status > -1 ";
            }

				$query .= "	GROUP BY
						zp_tickets.id, progressTickets.dependingTicketId";

                if($sortBy == "date") {
                    $query .= "	ORDER BY zp_tickets.editFrom ASC";
                }else if($sortBy == "headline") {
                    $query .= "	ORDER BY zp_tickets.headline ASC";
                }



            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll(PDO::FETCH_CLASS, 'leantime\domain\models\tickets');
            $stmn->closeCursor();

            return $values;

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
         * Checks whether a user has access to a ticket or not
         */
        public function getAccessRights($id)
        {

            $sql = "SELECT 
				
				zp_relationuserproject.userId
				
			FROM zp_tickets
			
			LEFT JOIN zp_relationuserproject ON zp_tickets.projectId = zp_relationuserproject.projectId
			
			WHERE zp_tickets.id=:id AND zp_relationuserproject.userId = :user";

            $stmn = $this->db->database->prepare($sql);

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

            $stmn = $this->db->database->prepare($query);
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

            $stmn = $this->db->database->prepare($query);
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

            $stmn = $this->db->database->prepare($query);
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

            $stmn = $this->db->database->prepare($query);
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

            $stmn = $this->db->database->prepare($query);
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

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();

            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values['avgSize'];
        }


        /**
         * addTicket - add a Ticket with postback test
         *
         * @access public
         * @param  array $values
         * @return boolean|int
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
						dependingTicketId,
						sortindex,
						kanbanSortindex
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
						:dependingTicketId,
						0,
						0
				)";

            $stmn = $this->db->database->prepare($query);

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

            return $this->db->database->lastInsertId();

        }


        public function patchTicket($id,$params)
        {

            $this->addTicketChange($_SESSION['userdata']['id'], $id, $params);

            $sql = "UPDATE zp_tickets SET ";

            foreach($params as $key=>$value){
                $sql .= "".core\db::sanitizeToColumnString($key)."=:".core\db::sanitizeToColumnString($key).", ";
            }

            $sql .= "id=:id WHERE id=:id LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            foreach($params as $key=>$value){
                $stmn->bindValue(':'.core\db::sanitizeToColumnString($key), $value, PDO::PARAM_STR);
            }

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;
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
				dependingTicketId = :dependingTicketId
			WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);

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


            $result = $stmn->execute();

            $stmn->closeCursor();

            return $result;
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


                $stmn = $this->db->database->prepare($query);
                $stmn->bindValue(':status', $status, PDO::PARAM_INT);
                $stmn->bindValue(':sortIndex', $ticketSorting, PDO::PARAM_INT);
                $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
                return $stmn->execute();

            }else{

                $query = "UPDATE zp_tickets
					SET 
						status = :status
					WHERE id = :ticketId
					LIMIT 1";


                $stmn = $this->db->database->prepare($query);
                $stmn->bindValue(':status', $status, PDO::PARAM_INT);
                $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
                return $stmn->execute();

            }

            $stmn->closeCursor();

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

            $stmn = $this->db->database->prepare($sql);
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

            $stmn = $this->db->database->prepare($sql);

            foreach ($changedFields as $field => $value) {

                $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
                $stmn->bindValue(':ticketId', $ticketId, PDO::PARAM_INT);
                $stmn->bindValue(':changeType', $field, PDO::PARAM_STR);
                $stmn->bindValue(':changeValue', $value, PDO::PARAM_STR);
                $stmn->execute();
            }

            $stmn->closeCursor();

        }

        /**
         * delTicket - delete a Ticket and all dependencies
         *
         * @access public
         * @param  $id
         */
        public function delticket($id)
        {

            $query = "DELETE FROM zp_tickets WHERE id = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $result = $stmn->execute();
            $stmn->closeCursor();

            return $result;

        }

        public function delMilestone($id)
        {

            $query = "UPDATE zp_tickets
                SET 
                    dependingTicketId = ''
                WHERE dependingTicketId = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();


            $query = "UPDATE zp_canvas_items
                SET 
                    milestoneId = ''
                WHERE milestoneId = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();


            $query = "DELETE FROM zp_tickets WHERE id = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();

            return true;

        }

    }

}
