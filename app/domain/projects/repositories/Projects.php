<?php

namespace Leantime\Domain\Projects\Repositories {

    use Leantime\Core\Eventhelpers as EventhelperCore;
    use Leantime\Core\Db as DbCore;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use DateInterval;
    use DatePeriod;
    use PDO;

    class Projects
    {
        use EventhelperCore;

        /**
         * @access public
         * @var    string
         */
        public $name = '';

        /**
         * @access public
         * @var    integer
         */
        public $id = 0; // WAS: '';

        /**
         * @access public
         * @var    integer
         */
        public $clientId = 0; // WAS: '';

        /**
         * @access private
         * @var    object
         */
        private $db; // WAS: = '';

        /**
         * @access public
         * @var    object
         */
        public $result; // WAS: = '';

        /**
         * @access public
         * @var    array state for projects
         */
        public $state = array(0 => 'OPEN', 1 => 'CLOSED', null => 'OPEN');

        public function __construct(
            \Leantime\Core\Environment $config,
            \Leantime\Core\Db $db
        ) {
            $this->config = $config;
            $this->db = $db;
        }


        /**
         * getAll - get all projects open and closed
         *
         * @access public
         * @param  $onlyOpen
         * @return array
         */
        public function getAll($showClosedProjects = false)
        {


            $query = "SELECT
					project.id,
					project.name,
					project.clientId,
					project.hourBudget,
					project.dollarBudget,
					project.state,
                    project.menuType,
					SUM(case when ticket.type <> 'milestone' AND ticket.type <> 'subtask' then 1 else 0 end) as numberOfTickets,
					client.name AS clientName,
					client.id AS clientId
				FROM zp_projects as project
				LEFT JOIN zp_clients as client ON project.clientId = client.id
				LEFT JOIN zp_tickets as ticket ON project.id = ticket.projectId
				";

            if ($showClosedProjects === false) {
                $query .= " WHERE project.state IS NULL OR project.state <> -1 ";
            }

            $query .= "
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

        public function getNumberOfProjects($clientId = null, $type=null)
        {

            $sql = "SELECT COUNT(id) AS projectCount FROM `zp_projects` WHERE id >0";

            if ($clientId != null && is_numeric($clientId)) {
                $sql .= " AND clientId = :clientId";
            }

            if ($type != null) {
                $sql .= " AND type = :type";
            }

            $stmn = $this->db->database->prepare($sql);

            if ($clientId != null && is_numeric($clientId)) {
                $stmn->bindValue(':clientId', $clientId, PDO::PARAM_INT);
            }

            if ($type != null) {
                $stmn->bindValue(':type', $type, PDO::PARAM_STR);
            }

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if (isset($values['projectCount']) === true) {
                return $values['projectCount'];
            } else {
                return 0;
            }
        }

        // Get all open user projects /param: open, closed, all

        public function getUserProjects($userId, $status = "all", $clientId = "", $hierarchy = array())
        {

            $query = "SELECT
					project.id,
					project.name,
					project.clientId,
					project.state,
					project.hourBudget,
					project.dollarBudget,
				    project.menuType,
				    project.type,
				    project.parent,
					SUM(case when ticket.type <> 'milestone' then 1 else 0 end) as numberOfTickets,
					client.name AS clientName,
					client.id AS clientId,
					parent.id AS parentId,
					parent.name as parentName,
					comments.status as status
				FROM zp_projects as project
				LEFT JOIN zp_relationuserproject AS relation ON project.id = relation.projectId
				LEFT JOIN zp_projects as parent ON parent.id = project.parent
				LEFT JOIN zp_clients as client ON project.clientId = client.id
				LEFT JOIN zp_tickets as ticket ON project.id = ticket.projectId
				LEFT JOIN zp_user as `user` ON relation.userId = user.id
				LEFT JOIN zp_comment as comments ON comments.id = (
                      SELECT
						id
                      FROM zp_comment
                      WHERE module = 'project' AND moduleId = project.id
                      ORDER BY date DESC LIMIT 1
                    )
                LEFT JOIN zp_user as requestingUser ON requestingUser.id = :id
				WHERE

				(       relation.userId = :id
				        OR project.psettings = 'all'
				        OR (project.psettings = 'clients' AND project.clientId = requestingUser.clientId)
				    )

				    AND (project.active > '-1' OR project.active IS NULL)
				    ";

            if ($status == "open") {
                $query .= " AND (project.state <> '-1' OR project.state IS NULL)";
            } elseif ($status == "closed") {
                $query .= " AND (project.state = -1)";
            }

            if ($clientId != "") {
                $query .= " AND project.clientId = :clientId";
            }

            if ((isset($hierarchy['program']) && $hierarchy['program']['enabled'] == true) || (isset($hierarchy['strategy']) && $hierarchy['strategy']['enabled'] == true)) {
                $query .= " GROUP BY
					project.id
				    ORDER BY parentName, clientName, project.name";
            } else {
                $query .= " GROUP BY
					project.id
				ORDER BY clientName, parentName, project.name";
            }

            $stmn = $this->db->database->prepare($query);
            if ($userId == '') {
                $stmn->bindValue(':id', $_SESSION['userdata']['id'], PDO::PARAM_STR);
            } else {
                $stmn->bindValue(':id', $userId, PDO::PARAM_STR);
            }

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
				    project.menuType,
				    project.type,
					SUM(case when ticket.type <> 'milestone' then 1 else 0 end) as numberOfTickets,
					client.name AS clientName,
					client.id AS clientId,
					IF(favorite.id IS NULL, false, true) as isFavorite
				FROM zp_projects AS project
				LEFT JOIN zp_relationuserproject as relation ON project.id = relation.projectId
				LEFT JOIN zp_clients as client ON project.clientId = client.id
				LEFT JOIN zp_tickets as ticket ON project.id = ticket.projectId
				LEFT JOIN zp_reactions as favorite ON project.id = favorite.moduleId AND favorite.module = 'project' AND favorite.reaction = 'favorite' AND favorite.userId = :id
				WHERE
				    (   relation.userId = :id
				        OR project.psettings = 'all'
				        OR (project.psettings = 'clients' AND project.clientId = :clientId)
				    )
				  AND (project.active > '-1' OR project.active IS NULL)";

            if ($status == "open") {
                $query .= " AND (project.state <> '-1' OR project.state IS NULL)";
            } elseif ($status == "closed") {
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
				    project.menuType,
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
        public function getProject($id): array|bool
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
				    zp_projects.menuType,
				    zp_projects.avatar,
				    zp_projects.cover,
				    zp_projects.type,
				    zp_projects.parent,
					zp_clients.name AS clientName,
					 zp_projects.start,
					  zp_projects.end,
					SUM(case when zp_tickets.type <> 'milestone' then 1 else 0 end) as numberOfTickets,
                    SUM(case when zp_tickets.type = 'milestone' then 1 else 0 end) as numberMilestones,
                    COUNT(relation.projectId) AS numUsers,
                    COUNT(definitionCanvas.id) AS numDefinitionCanvas
				FROM zp_projects
				  LEFT JOIN zp_tickets ON zp_projects.id = zp_tickets.projectId
				  LEFT JOIN zp_clients ON zp_projects.clientId = zp_clients.id
				  LEFT JOIN zp_relationuserproject as relation ON zp_projects.id = relation.projectId
				  LEFT JOIN zp_canvas as definitionCanvas ON zp_projects.id = definitionCanvas.projectId AND definitionCanvas.type NOT IN('idea', 'retroscanvas', 'goalcanvas', 'wiki')
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
        public function getUsersAssignedToProject($id): array|bool
        {

            $query = "SELECT
					DISTINCT zp_user.id,
					IF(zp_user.firstname IS NOT NULL, zp_user.firstname, zp_user.username) AS firstname,
					zp_user.lastname,
					zp_user.username,
					zp_user.notifications,
					zp_user.profileId,
                    zp_user.status,
                    zp_relationuserproject.projectRole
				FROM zp_relationuserproject
				LEFT JOIN zp_user ON zp_relationuserproject.userId = zp_user.id
				WHERE zp_relationuserproject.projectId = :projectId AND
                !(zp_user.source <=> 'api') AND zp_user.id IS NOT NULL
				ORDER BY zp_user.lastname";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':projectId', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function getProjectBookedHours($id): array|bool
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

        public function getProjectBookedHoursArray($id): array|bool
        {

            $query = "SELECT
                        zp_tickets.projectId,
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
         * addProject - add a project to a client
         *
         * @access public
         * @param array|boolean $values
         * @return integer|boolean returns new project id on success, false on failure.
         */
        public function addProject($values): int|bool
        {

            $query = "INSERT INTO `zp_projects` (
				            `name`,
                           `details`,
                           `clientId`,
                           `hourBudget`,
                           `dollarBudget`,
                           `psettings`,
                           `menuType`,
                           `type`,
                           `parent`,
                           `start`,
                           `end`

			) VALUES (
				:name,
				:details,
				:clientId,
				:hourBudget,
				:dollarBudget,
			    :psettings,
                :menuType,
			    :type,
			    :parent,
			          :start,
			          :end
			)";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue('name', $values['name'], PDO::PARAM_STR);
            $stmn->bindValue('details', $values['details'], PDO::PARAM_STR);
            $stmn->bindValue('clientId', $values['clientId'], PDO::PARAM_STR);
            $stmn->bindValue('hourBudget', $values['hourBudget'], PDO::PARAM_STR);
            $stmn->bindValue('dollarBudget', $values['dollarBudget'], PDO::PARAM_STR);
            $stmn->bindValue('psettings', $values['psettings'], PDO::PARAM_STR);
            $stmn->bindValue('menuType', $values['menuType'], PDO::PARAM_STR);
            $stmn->bindValue('type', $values['type'] ?? 'project', PDO::PARAM_STR);
            $stmn->bindValue('parent', $values['parent'] ?? null, PDO::PARAM_STR);

            $startDate = null;
            if (isset($values['start']) && $values['start'] !== false && $values['start'] != '') {
                $startDate = $values['start'];
            }
            $stmn->bindValue('start', $startDate, PDO::PARAM_STR);


            $endDate = null;
            if (isset($values['end']) && $values['end'] !== false && $values['end'] != '') {
                $endDate = $values['end'];
            }

            $stmn->bindValue('end', $endDate, PDO::PARAM_STR);
            $stuff = $stmn->execute();

            $projectId = $this->db->database->lastInsertId();
            $stmn->closeCursor();

            //Add author to project
            $this->addProjectRelation($_SESSION["userdata"]["id"], $projectId, "");

            //Add users to relation
            if (is_array($values['assignedUsers']) === true && count($values['assignedUsers']) > 0) {
                foreach ($values['assignedUsers'] as $user) {
                    if (is_array($user) && isset($user["id"]) && isset($user["projectRole"])) {
                        $this->addProjectRelation($user["id"], $projectId, $user["projectRole"]);
                    }
                }
            }

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
				psettings = :psettings,
				menuType = :menuType,
				type = :type,
				parent = :parent,
				start = :start,
				end = :end
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
            $stmn->bindValue('menuType', $values['menuType'], PDO::PARAM_STR);
            $stmn->bindValue('type', $values['type'] ?? 'project', PDO::PARAM_STR);
            $stmn->bindValue('id', $id, PDO::PARAM_STR);
            $stmn->bindValue('parent', $values['parent'] ?? null, PDO::PARAM_STR);

            $startDate = null;
            if (isset($values['start']) && $values['start'] !== false && $values['start'] != '') {
                $startDate = $values['start'];
            }
            $stmn->bindValue('start', $startDate, PDO::PARAM_STR);


            $endDate = null;
            if (isset($values['end']) && $values['end'] !== false && $values['end'] != '') {
                $endDate = $values['end'];
            }
            $stmn->bindValue('end', $endDate, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();

            static::dispatch_event("editProject", array("values" => $values));
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
                    if (isset($values['projectRoles']['userProjectRole-' . $userId]) && $values['projectRoles']['userProjectRole-' . $userId] != "40" && $values['projectRoles']['userProjectRole-' . $userId] != "50") {
                        $projectRole = (int) $values['projectRoles']['userProjectRole-' . $userId];
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
         * @return boolean
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

            if ($projectId != null) {
                $query .= " AND  zp_projects.id = :projectId";
            }

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            if ($projectId != null) {
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        public function isUserAssignedToProject($userId, $projectId)
        {

            $userRepo = app()->make(UserRepository::class);
            $user = $userRepo->getUser($userId);

            if ($user === false) {
                return false;
            }

            //admins owners and managers can access everything
            if (in_array(Roles::getRoleString($user['role']), array(Roles::$admin, Roles::$owner, Roles::$manager))) {
                return true;
            }

            $project = $this->getProject($projectId);

            if ($project === false) {
                return false;
            }

            //Everyone in org is allowed to see the project
            if ($project['psettings'] == 'all') {
                return true;
            }

            //Everyone in client is allowed to see project
            if ($project['psettings'] == 'client') {
                if ($user['clientId'] == $project['clientId']) {
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

        public function isUserMemberOfProject($userId, $projectId)
        {

            $userRepo = app()->make(UserRepository::class);
            $user = $userRepo->getUser($userId);

            if ($user === false) {
                return false;
            }

            //admins owners and managers can access everything


            $project = $this->getProject($projectId);

            if ($project === false) {
                return false;
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
				zp_user.username,
				IF(zp_user.firstname <> '', zp_user.firstname, zp_user.username) AS firstname,
				zp_user.lastname,
				zp_user.jobTitle,
				zp_user.jobLevel,
				zp_user.department,
				zp_user.profileId,
				zp_user.role,
				zp_user.status
			FROM zp_relationuserproject
			    LEFT JOIN zp_projects ON zp_relationuserproject.projectId = zp_projects.id
			    LEFT JOIN zp_user ON zp_relationuserproject.userId = zp_user.id
			WHERE projectId = :id AND zp_user.id IS NOT NULL";

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
         * @return boolean
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

            return true;
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

            static::dispatch_event("userAddedToProject", array("userId" => $userId, "projectId" => $projectId, "projectRole" => $projectRole));
        }

        public function patch($id, $params)
        {

            $sql = "UPDATE zp_projects SET ";

            foreach ($params as $key => $value) {
                $sql .= "" . DbCore::sanitizeToColumnString($key) . "=:" . DbCore::sanitizeToColumnString($key) . ", ";
            }

            $sql .= "id=:id WHERE id=:id LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            foreach ($params as $key => $value) {
                $stmn->bindValue(':' . DbCore::sanitizeToColumnString($key), $value, PDO::PARAM_STR);
            }

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;
        }

        /**
         * setPicture - set the profile picture for an individual
         *
         * @access public
         * @param  string
         */
        public function setPicture($_FILE, $id)
        {

            $project = $this->getProject($id);

            $files = app()->make(Files::class);

            if (isset($values['profileId']) && $values['profileId'] > 0) {
                $file = $files->getFile($values['profileId']);
                $img = 'userdata/' . $file['encName'] . $file['extension'];

                $files->deleteFile($values['avatar']);
            }


            $lastId = $files->upload($_FILE, 'project', $id, true, 300, 300);

            if (isset($lastId['fileId'])) {
                $sql = 'UPDATE `zp_projects` SET avatar = :fileId WHERE id = :userId';

                $stmn = $this->db->database->prepare($sql);
                $stmn->bindValue(':fileId', $lastId['fileId'], PDO::PARAM_INT);
                $stmn->bindValue(':userId', $id, PDO::PARAM_INT);

                $stmn->execute();
                $stmn->closeCursor();
            }
        }

        public function getProjectAvatar($id)
        {

            $value = false;

            if ($id !== false) {
                $sql = "SELECT avatar, name FROM `zp_projects` WHERE id = :id LIMIT 1";

                $stmn = $this->db->database->prepare($sql);
                $stmn->bindValue(':id', $id, PDO::PARAM_INT);

                $stmn->execute();
                $value = $stmn->fetch();
                $stmn->closeCursor();
            }

            if ($value !== false && $value['avatar'] != '') {
                $files = app()->make(Files::class);
                $file = $files->getFile($value['avatar']);

                if ($file) {
                    $filePath = $file['encName'] . "." . $file['extension'];
                    $type = $file['extension'];

                    return array("filename" => $filePath, "type" => "uploaded");
                } else {
                    $avatar = new \LasseRafn\InitialAvatarGenerator\InitialAvatar();
                    $image = $avatar
                        ->name("🦄")
                        ->font(ROOT . '/fonts/roboto/Roboto-Medium-webfont.woff')
                        ->fontName("Verdana")
                        ->background('#555555')->color("#fff")
                        ->generateSvg();

                    return $image;
                }
            } elseif ($value !== false && ($value['avatar'] === '' || $value['avatar'] == null)) {
                $imagename = md5($value['name']);

                if (file_exists(APP_ROOT . "/cache/avatars/" . $imagename . ".png")) {
                    return array("filename" => APP_ROOT . "/cache/avatars/" . $imagename . ".png", "type" => "generated");
                } else {
                    $avatar = new \LasseRafn\InitialAvatarGenerator\InitialAvatar();
                    $image = $avatar
                        ->name($value['name'])
                        ->font(ROOT . '/dist/fonts/roboto/Roboto-Regular.woff2')
                        ->fontSize(0.5)
                        ->size(96)
                        ->background('#555555')->color("#fff");

                    if (is_writable(APP_ROOT . "/cache/avatars/")) {
                        $image->generate()->save(APP_ROOT . "/cache/avatars/" . $imagename . ".png", 100, "png");
                        return array("filename" => APP_ROOT . "/cache/avatars/" . $imagename . ".png", "type" => "generated");
                    } else {
                        return $image->generateSVG();
                        ;
                    }
                }
            } else {
                $avatar = new \LasseRafn\InitialAvatarGenerator\InitialAvatar();
                $image = $avatar
                    ->name("🦄")
                    ->font(ROOT . '/dist/fonts/roboto/Roboto-Medium-webfont.woff')
                    ->fontName("Verdana")
                    ->background('#555555')->color("#fff")
                    ->generateSvg();

                return $image;
            }
        }
    }

}
