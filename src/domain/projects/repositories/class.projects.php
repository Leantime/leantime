<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use pdo;
    use DateInterval;
    use DatePeriod;

    class projects
    {

        /**
         * @access public
         * @var    string
         */
        public $name = '';

        /**
         * @access public
         * @var    int
         */
        public $id = '';

        /**
         * @access public
         * @var    int
         */
        public $clientId = '';

        /**
         * @access private
         * @var    object
         */
        private $db = '';

        /**
         * @access public
         * @var    object
         */
        public $result = '';

        /**
         * @access public
         * @var    array state for projects
         */
        public $state = array(0 => 'OPEN', 1 => 'CLOSED', null => 'OPEN');

        /**
         * __construct - get database connection
         *
         * @access public
         */

        public function __construct()
        {
            $config = new core\config();
            $this->db = core\db::getInstance();

        }

        /**
         * getAll - get all projects open and closed
         *
         * @access public
         * @param  $onlyOpen
         * @return array
         */
        public function getAll()
        {


            $query = "SELECT
					project.id,
					project.name,
					project.clientId,
					project.hourBudget,
					project.dollarBudget,
					project.state,
					SUM(case when ticket.type <> 'milestone' AND ticket.type <> 'subtask' then 1 else 0 end) as numberOfTickets,
					client.name AS clientName,
					client.id AS clientId 
				FROM zp_projects as project
				LEFT JOIN zp_clients as client ON project.clientId = client.id
				LEFT JOIN zp_tickets as ticket ON project.id = ticket.projectId  
				WHERE project.active > '-1' OR project.active IS NULL
				GROUP BY 
					project.id,
					project.name,
					project.clientId
				ORDER BY clientName, project.name";

            $stmn = $this->db->database->prepare($query);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }


        public function getNumberOfProjects($clientId = null)
        {

            $sql = "SELECT COUNT(id) AS projectCount FROM `zp_projects`";

            if($clientId != null && is_numeric($clientId)){
                $sql .= " WHERE clientId = :clientId";
            }

            $stmn = $this->db->database->prepare($sql);

            if ($clientId != null && is_numeric($clientId)) {
                $stmn->bindValue(':clientId', $clientId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if(isset($values['projectCount']) === true) {
                return $values['projectCount'];
            }else{
                return 0;
            }
        }


        // Get all open user projects /param: open, closed, all

        public function getUserProjects($userId, $status = "all", $clientId = "")
        {

            $query = "SELECT
					project.id,
					project.name,
					project.clientId,
					project.state,
					project.hourBudget,
					project.dollarBudget,
					SUM(case when ticket.type <> 'milestone' AND ticket.type <> 'subtask' then 1 else 0 end) as numberOfTickets,
					client.name AS clientName,
					client.id AS clientId 
				FROM zp_relationuserproject AS relation
				LEFT JOIN zp_projects as project ON project.id = relation.projectId
				LEFT JOIN zp_clients as client ON project.clientId = client.id
				LEFT JOIN zp_tickets as ticket ON project.id = ticket.projectId  
				WHERE relation.userId = :id AND (project.active > '-1' OR project.active IS NULL)";

            if ($status == "open") {
                $query .= " AND (project.state <> '-1' OR project.state IS NULL)";
            } else if ($status == "closed") {
                $query .= " AND (project.state = -1)";
            }

            if ($clientId != "") {
                $query .= " AND project.clientId = :clientId";
            }

            $query .= " GROUP BY 
					project.id
				ORDER BY clientName, project.name";


            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $_SESSION['userdata']['id'], PDO::PARAM_STR);
            if ($clientId != "") {
                $stmn->bindValue(':clientId', $clientId, PDO::PARAM_STR);
            }


            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function getProjectsUserHasAccessTo($userId, $status = "all", $clientId = "")
        {

            $query = "SELECT
					project.id,
					project.name,
					project.clientId,
					project.state,
					project.hourBudget,
					project.dollarBudget,
					SUM(case when ticket.type <> 'milestone' AND ticket.type <> 'subtask' then 1 else 0 end) as numberOfTickets,
					client.name AS clientName,
					client.id AS clientId 
				FROM zp_projects AS project
				LEFT JOIN zp_relationuserproject as relation ON project.id = relation.projectId
				LEFT JOIN zp_clients as client ON project.clientId = client.id
				LEFT JOIN zp_tickets as ticket ON project.id = ticket.projectId  
				WHERE 
				    (   relation.userId = :id 
				        OR project.psettings = 'all'
				        OR (project.psettings = 'client' AND project.clientId = :clientId)
				    )
				  
				  AND (project.active > '-1' OR project.active IS NULL)";

            if ($status == "open") {
                $query .= " AND (project.state <> '-1' OR project.state IS NULL)";
            } else if ($status == "closed") {
                $query .= " AND (project.state = -1)";
            }

            $query .= " GROUP BY 
					project.id
				ORDER BY clientName, project.name";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $userId, PDO::PARAM_STR);
            $stmn->bindValue(':clientId', $clientId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function getClientProjects($clientId)
        {

            $sql = "SELECT
					project.id,
					project.name,
					project.clientId,
					project.hourBudget,
					project.dollarBudget,
					project.state,
					SUM(case when ticket.type <> 'milestone' AND ticket.type <> 'subtask' then 1 else 0 end) as numberOfTickets,
					client.name AS clientName,
					client.id AS clientId 
				FROM zp_projects as project
				LEFT JOIN zp_clients as client ON project.clientId = client.id
				LEFT JOIN zp_tickets as ticket ON project.id = ticket.projectId  
				WHERE 
				  (project.active > '-1' OR project.active IS NULL)
				  AND clientId = :clientId
				GROUP BY 
					project.id,
					project.name,
					project.clientId
				ORDER BY clientName, project.name";


            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':clientId', $clientId, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function getProjectTickets($projectId)
        {

            $sql = "SELECT zp_tickets.id,
		zp_tickets.headline,
		zp_tickets.editFrom,
		zp_tickets.editTo,
		zp_user.firstname,
		zp_user.lastname
		 FROM zp_tickets 
		LEFT JOIN zp_user ON zp_tickets.editorId = zp_user.id
		WHERE projectId=:projectId ORDER BY zp_tickets.editFrom";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * getProject - get one project
         *
         * @access public
         * @param  $id
         * @return mixed
         */
        public function getProject($id)
        {

            $query = "SELECT
					zp_projects.id, 
					zp_projects.name, 
					zp_projects.clientId, 
					zp_projects.details,
					zp_projects.state,
					zp_projects.hourBudget,
					zp_projects.dollarBudget,
					zp_projects.psettings,
					zp_clients.name AS clientName,
					SUM(case when zp_tickets.type <> 'milestone' AND zp_tickets.type <> 'subtask' then 1 else 0 end) as numberOfTickets
				FROM zp_projects 
				  LEFT JOIN zp_tickets ON zp_projects.id = zp_tickets.projectId 
				  LEFT JOIN zp_clients ON zp_projects.clientId = zp_clients.id
				WHERE zp_projects.id = :projectId
				GROUP BY 
					zp_projects.id, 
					zp_projects.name, 
					zp_projects.clientId, 
					zp_projects.details
				LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':projectId', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();


            return $values;
        }

        /**
         * getProject - get one project
         *
         * @access public
         * @param  $id
         * @return array
         */
        public function getUsersAssignedToProject($id)
        {

            $query = "SELECT
					DISTINCT zp_user.id,
					zp_user.firstname,
					zp_user.lastname,
					zp_user.username,
					zp_user.notifications,
					zp_user.profileId
				FROM zp_relationuserproject 
				LEFT JOIN zp_user ON zp_relationuserproject.userId = zp_user.id
				WHERE zp_relationuserproject.projectId = :projectId && zp_user.id IS NOT NULL
				ORDER BY zp_user.lastname";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':projectId', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;

        }


        public function getProjectBookedHours($id)
        {

            $query = "SELECT zp_tickets.projectId, SUM(zp_timesheets.hours) AS totalHours
				FROM zp_tickets
				INNER JOIN zp_timesheets ON zp_timesheets.ticketId = zp_tickets.id
				WHERE projectId = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;

        }

        public function recursive_array_search($needle, $haystack)
        {
            foreach ($haystack as $key => $value) {
                $current_key = $key;
                if ($needle === $value or (is_array($value) && $this->recursive_array_search($needle, $value) !== false)) {
                    return $current_key;
                }
            }
            return false;
        }

        public function getProjectBookedHoursArray($id)
        {

            $query = "SELECT 	zp_tickets.projectId, 
			SUM(zp_timesheets.hours) AS totalHours,
			 DATE_FORMAT(zp_timesheets.workDate,'%Y-%m-%d') AS workDate
				FROM zp_tickets
				INNER JOIN zp_timesheets ON zp_timesheets.ticketId = zp_tickets.id
				WHERE projectId =  :id GROUP BY zp_timesheets.workDate	
				ORDER BY workDate";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $results = $stmn->fetchAll();
            $stmn->closeCursor();

            $chartArr = array();

            if (count($results) > 0) {
                $begin = date_create($results[0]["workDate"]);
                $begin->sub(new DateInterval('P1D'));

                $end = date_create($results[(count($results) - 1)]["workDate"]);
                $end->add(new DateInterval('P1D'));

                $i = new DateInterval('P1D');

                $period = new DatePeriod($begin, $i, $end);

                $total = 0;

                foreach ($period as $d) {


                    $day = $d->format('Y-m-d');
                    $dayKey = $d->getTimestamp();

                    $key = $this->recursive_array_search($day, $results);


                    if ($key === false) {
                        $value = 0;
                    } else {
                        $value = $results[$key]['totalHours'];
                    }

                    $total = $total + $value;
                    $chartArr[$dayKey] = $total;

                }
            }


            return $chartArr;

        }

        public function getProjectBookedDollars($id)
        {

            $query = "SELECT zp_tickets.projectId, SUM(zp_timesheets.hours*zp_timesheets.rate) AS totalDollars
				FROM zp_tickets
				INNER JOIN zp_timesheets ON zp_timesheets.ticketId = zp_tickets.id
				WHERE projectId = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;

        }

        /**
         * getOpenTickets - get all open tickets related to a project
         *
         * @access public
         * @param  $id
         * @return array
         */
        public function getOpenTickets($id)
        {

            $query = "SELECT COUNT(zp_tickets.status) AS openTickets FROM zp_tickets WHERE zp_tickets.projectId = :id AND zp_tickets.status > 0";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;

        }

        /**
         * addProject - add a project to a client
         *
         * @access public
         * @param array $values
         */
        public function addProject($values)
        {

            $query = "INSERT INTO `zp_projects` (
				`name`, `details`, `clientId`, `hourBudget`, `dollarBudget`, `psettings`
			) VALUES (
				:name,
				:details,
				:clientId,
				:hourBudget,
				:dollarBudget,
			    :psettings
			)";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue('name', $values['name'], PDO::PARAM_STR);
            $stmn->bindValue('details', $values['details'], PDO::PARAM_STR);
            $stmn->bindValue('clientId', $values['clientId'], PDO::PARAM_STR);
            $stmn->bindValue('hourBudget', $values['hourBudget'], PDO::PARAM_STR);
            $stmn->bindValue('dollarBudget', $values['dollarBudget'], PDO::PARAM_STR);
            $stmn->bindValue('psettings', $values['psettings'], PDO::PARAM_STR);

            $stuff = $stmn->execute();


            $projectId = $this->db->database->lastInsertId();
            $stmn->closeCursor();

            //Add author to project
            $this->addProjectRelation($_SESSION["userdata"]["id"], $projectId, "");

            //Add users to relation
            /*if (is_array($values['assignedUsers']) === true && count($values['assignedUsers']) > 0) {

                foreach ($values['assignedUsers'] as $userId) {
                    $this->addProjectRelation($userId, $projectId);
                }

            }*/

            return $projectId;

        }

        /**
         * editProject - edit a project
         *
         * @access public
         * @param array $values
         * @param  $id
         */
        public function editProject(array $values, $id)
        {

            $query = "UPDATE zp_projects SET
				name = :name, 
				details = :details, 
				clientId = :clientId,
				state = :state,
				hourBudget = :hourBudget,
				dollarBudget = :dollarBudget,
				psettings = :psettings
				WHERE id = :id 
				
				LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue('name', $values['name'], PDO::PARAM_STR);
            $stmn->bindValue('details', $values['details'], PDO::PARAM_STR);
            $stmn->bindValue('clientId', $values['clientId'], PDO::PARAM_STR);
            $stmn->bindValue('state', $values['state'], PDO::PARAM_STR);
            $stmn->bindValue('hourBudget', $values['hourBudget'], PDO::PARAM_STR);
            $stmn->bindValue('dollarBudget', $values['dollarBudget'], PDO::PARAM_STR);
            $stmn->bindValue('psettings', $values['psettings'], PDO::PARAM_STR);
            $stmn->bindValue('id', $id, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();

        }

        /**
         * editProject - edit a project
         *
         * @access public
         * @param array $values
         * @param  $id
         */
        public function editProjectRelations(array $values, $projectId)
        {

            $this->deleteAllUserRelations($projectId);

            //Add users to relation
            if (is_array($values['assignedUsers']) === true && count($values['assignedUsers']) > 0) {

                foreach ($values['assignedUsers'] as $userId) {

                    $projectRole = null;
                    if(isset($values['projectRoles']['userProjectRole-'.$userId]) && $values['projectRoles']['userProjectRole-'.$userId] != "40" && $values['projectRoles']['userProjectRole-'.$userId] != "50"){
                        $projectRole = (int) $values['projectRoles']['userProjectRole-'.$userId];
                    }

                    $this->addProjectRelation($userId, $projectId, $projectRole);
                }

            }

        }



        /**
         * deleteProject - delete a project
         *
         * @access public
         * @param  $id
         */
        public function deleteProject($id)
        {

            $query = "DELETE FROM zp_projects WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $stmn->closeCursor();

        }

        /**
         * hasTickets - check if there are Tickets related to a project
         *
         * @access public
         * @param  $id
         * @return bool
         */
        public function hasTickets($id)
        {

            $query = "SELECT id FROM zp_tickets WHERE projectId = :id 
                      AND zp_tickets.type <> 'subtask' AND
                       zp_tickets.type <> 'milestone' LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            if (count($values) == 0) {

                return false;

            } else {

                return true;

            }

        }

        /**
         * getUserProjectRelation - get all projects related to a user
         *
         * @access public
         * @param  $id
         * @return array
         */
        public function getUserProjectRelation($id, $projectId = null)
        {

            $query = "SELECT
				zp_relationuserproject.userId, 
				zp_relationuserproject.projectId,
				zp_projects.name,
				zp_relationuserproject.projectRole
			FROM zp_relationuserproject JOIN zp_projects 
				ON zp_relationuserproject.projectId = zp_projects.id
			WHERE userId = :id";

            if($projectId != null){
                $query .= " AND  zp_projects.id = :projectId";
            }

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            if($projectId != null) {
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function isUserAssignedToProject($userId, $projectId)
        {

            $userRepo = new users();
            $user = $userRepo->getUser($userId);

            //admins owners and managers can access everything
            if(roles::getRoles()[$user['role']] == roles::$admin
                || roles::getRoles()[$user['role']] == roles::$owner
                || roles::getRoles()[$user['role']] == roles::$manager) {
                return true;
            }

            $project = $this->getProject($projectId);

            //Everyone in org is allowed to see the project
            if($project['psettings'] == 'all') {
                return true;
            }

            //Everyone in client is allowed to see project
            if($project['psettings'] == 'client'){
                if($user['clientId'] == $project['clientId']) {
                    return true;
                }
            }

            //Select users are allowed to see project
            $query = "SELECT
				zp_relationuserproject.userId, 
				zp_relationuserproject.projectId,
				zp_projects.name 
			FROM zp_relationuserproject JOIN zp_projects 
				ON zp_relationuserproject.projectId = zp_projects.id
			WHERE userId = :userId AND zp_relationuserproject.projectId = :projectId LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if ($values && count($values) > 1) {
                return true;
            }

            return false;
        }

        public function getProjectUserRelation($id)
        {

            $query = "SELECT
				zp_relationuserproject.userId, 
				zp_relationuserproject.projectId,
				zp_relationuserproject.projectRole,
				zp_projects.name,
				zp_user.firstname,
				zp_user.lastname,
				zp_user.profileId,
				zp_user.role
			FROM zp_relationuserproject 
			    LEFT JOIN zp_projects ON zp_relationuserproject.projectId = zp_projects.id
			    LEFT JOIN zp_user ON zp_relationuserproject.userId = zp_user.id
			WHERE projectId = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $results = $stmn->fetchAll();
            $stmn->closeCursor();


            $users = array();
            foreach ($results as $row) {
                $users[$row['userId']] = $row;
            }

            return $users;
        }

        /**
         * getUserProjectRelation - get all projects related to a user
         *
         * @access public
         * @param  $id
         * @return array
         */
        public function editUserProjectRelations($id, $projects)
        {

            $sql = "SELECT id,userId,projectId,projectRole FROM zp_relationuserproject WHERE userId=:id";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            // Add relations that don't exist
            foreach ($projects as $project) {
                $exists = false;
                if (count($values)) {
                    foreach ($values as $value) {
                        if ($project == $value['projectId']) {
                            $exists = true;
                        }
                    }
                }
                if (!$exists) {
                    $this->addProjectRelation($id, $project, '');
                }
            }

            // Delete relations that were removed in select
            if (count($values)) {
                foreach ($values as $value) {
                    if (in_array($value['projectId'], $projects) !== true) {
                        $this->deleteProjectRelation($id, $value['projectId']);
                    }
                }
            }
        }

        public function deleteProjectRelation($userId, $projectId)
        {

            $sql = "DELETE FROM zp_relationuserproject WHERE projectId=:projectId AND userId=:userId";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();

            $stmn->closeCursor();
        }

        public function deleteAllProjectRelations($userId)
        {

            $sql = "DELETE FROM zp_relationuserproject WHERE userId=:userId";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);

            $stmn->execute();

            $stmn->closeCursor();
        }

        public function deleteAllUserRelations($projectId)
        {

            $sql = "DELETE FROM zp_relationuserproject WHERE projectId=:projectId";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);

            $stmn->execute();

            $stmn->closeCursor();
        }

        public function addProjectRelation($userId, $projectId, $projectRole)
        {

            $sql = "INSERT INTO zp_relationuserproject (
					userId,
					projectId,
                    projectRole                
				) VALUES (
					:userId,
					:projectId,
					:projectRole
				)";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':userId', $userId, PDO::PARAM_INT);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            $stmn->bindValue(':projectRole', $projectRole, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();

        }

    }

}