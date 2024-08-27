<?php

namespace Leantime\Domain\Ideas\Repositories {

    use Leantime\Core\Db\Db as DbCore;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Domain\Tickets\Repositories\Tickets;
    use PDO;

    /**
     *
     */
    class Ideas
    {
        /**
         * @access public
         * @var    object|null
         */
        public ?object $result = null;

        /**
         * @access public
         * @var    object|null
         */
        public ?object $tickets = null;

        /**
         * @access private
         * @var    DbCore|null
         */
        private ?DbCore $db = null;

        public array $canvasTypes = array(
            "idea" => "status.ideation",
            "research" => "status.discovery",
            "prototype" => "status.delivering",
            "validation" => "status.inreview",
            "implemented" => "status.accepted",
            "deferred" => "status.deferred",
        );

        public array $statusClasses = array('idea' => 'label-info', 'validation' => 'label-warning', 'prototype' => 'label-warning', 'research' => 'label-warning', 'implemented' => 'label-success', "deferred" => "label-default");

        private LanguageCore $language;

        private Tickets $ticketRepo;

        /**
         * __construct - get db connection
         *
         * @access public
         * @return void
         */
        public function __construct(DbCore $db, LanguageCore $language, Tickets $ticketRepo)
        {
            $this->db = $db;
            $this->language = $language;
            $this->ticketRepo = $ticketRepo;
        }

        /**
         * @param $canvasId
         * @return array|false
         */
        public function getSingleCanvas($canvasId): false|array
        {
            $sql = "SELECT
                        zp_canvas.id,
                        zp_canvas.title,
                        zp_canvas.author,
                        zp_canvas.created,
                        zp_canvas.projectId,
                        t1.firstname AS authorFirstname,
                        t1.lastname AS authorLastname

                FROM
                zp_canvas
                LEFT JOIN zp_user AS t1 ON zp_canvas.author = t1.id
                WHERE type = 'idea' AND zp_canvas.id = :canvasId
                ORDER BY zp_canvas.title, zp_canvas.created";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':canvasId', $canvasId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @return array|mixed
         */
        public function getCanvasLabels(): mixed
        {
            if (session()->exists("projectsettings.idealabels")) {
                return session("projectsettings.idealabels");
            } else {
                $sql = "SELECT
						value
				FROM zp_settings WHERE `key` = :key
				LIMIT 1";

                $stmn = $this->db->database->prepare($sql);
                $stmn->bindvalue(':key', "projectsettings." . session("currentProject") . ".idealabels", PDO::PARAM_STR);

                $stmn->execute();
                $values = $stmn->fetch();
                $stmn->closeCursor();

                $labels = array();

                //preseed state labels with default values
                foreach ($this->canvasTypes as $key => $label) {
                    $labels[$key] = array(
                        "name" => $this->language->__($label),
                        "class" => $this->statusClasses[$key],
                    );
                }

                if ($values !== false) {
                    foreach (unserialize($values['value']) as $key => $label) {
                        $labels[$key] = array(
                            "name" => $label,
                            "class" => $this->statusClasses[$key],
                        );
                    }
                }

                session(["projectsettings.idealabels" => $labels]);

                return $labels;
            }
        }

        /**
         * @param $projectId
         * @return array|false
         */
        public function getAllCanvas($projectId): false|array
        {

            $sql = "SELECT
						zp_canvas.id,
						zp_canvas.title,
						zp_canvas.author,
						zp_canvas.created,
						t1.firstname AS authorFirstname,
						t1.lastname AS authorLastname

				FROM
				zp_canvas
				LEFT JOIN zp_user AS t1 ON zp_canvas.author = t1.id
				WHERE type = 'idea' AND projectId = :projectId
				ORDER BY zp_canvas.title, zp_canvas.created";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $id
         * @return void
         */
        public function deleteCanvas($id): void
        {

            $query = "DELETE FROM zp_canvas_items WHERE canvasId = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();

            $query = "DELETE FROM zp_canvas WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();

            $stmn->closeCursor();
        }

        /**
         * @param $values
         * @return false|string
         */
        public function addCanvas($values): false|string
        {

            $query = "INSERT INTO zp_canvas (
						title,
						author,
						created,
						type,
						projectId
				) VALUES (
						:title,
						:author,
						NOW(),
						'idea',
						:projectId
				)";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':title', $values['title'], PDO::PARAM_STR);
            $stmn->bindValue(':projectId', $values['projectId'], PDO::PARAM_STR);
            $stmn->bindValue(':author', $values['author'], PDO::PARAM_STR);


            $stmn->execute();

            $stmn->closeCursor();

            return $this->db->database->lastInsertId();
        }

        /**
         * @param $values
         * @return mixed
         */
        public function updateCanvas($values): mixed
        {

            $query = "UPDATE zp_canvas SET
						title = :title
				WHERE id = :id";

            $stmn = $this->db->{'database'}->prepare($query);

            $stmn->bindValue(':title', $values['title'], PDO::PARAM_STR);
            $stmn->bindValue(':id', $values['id'], PDO::PARAM_INT);

            $result = $stmn->execute();

            $stmn->closeCursor();

            return $result;
        }

        /**
         * @param $values
         * @return void
         */
        public function editCanvasItem($values): void
        {
            $sql = "UPDATE zp_canvas_items SET
					description = :description,
					assumptions =		:assumptions,
					data =			:data,
					conclusion =			:conclusion,
					modified =			NOW(),
					status =			:status,
					milestoneId =			:milestoneId,
					tags = :tags
					WHERE id = :id LIMIT 1	";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':id', $values['itemId'], PDO::PARAM_STR);
            $stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);
            $stmn->bindValue(':assumptions', $values['assumptions'], PDO::PARAM_STR);
            $stmn->bindValue(':data', $values['data'], PDO::PARAM_STR);
            $stmn->bindValue(':conclusion', $values['conclusion'], PDO::PARAM_STR);
            $stmn->bindValue(':status', $values['status'], PDO::PARAM_STR);
            $stmn->bindValue(':milestoneId', $values['milestoneId'], PDO::PARAM_STR);
            $stmn->bindValue(':tags', $values['tags'], PDO::PARAM_STR);


            $stmn->execute();
            $stmn->closeCursor();
        }

        /**
         * @param $id
         * @param $params
         * @return bool
         */
        public function patchCanvasItem($id, $params): bool
        {

            $sql = "UPDATE zp_canvas_items SET ";

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
         * @param $sortingArray
         * @return bool
         */
        public function updateIdeaSorting($sortingArray): bool
        {

            $sql = "INSERT INTO zp_canvas_items (id, sortindex) VALUES ";

            $sqlPrep = array();
            foreach ($sortingArray as $idea) {
                $sqlPrep[] = "(" . (int)$idea['id'] . ", " . (int)$idea['sortIndex'] . ")";
            }

            $sql .= implode(",", $sqlPrep);

            $sql .= " ON DUPLICATE KEY UPDATE sortindex = VALUES(sortindex)";

            $stmn = $this->db->database->prepare($sql);

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;
        }

        /**
         * @param $id
         * @return array|false
         */
        public function getCanvasItemsById($id): false|array
        {

            $statusGroups = $this->ticketRepo->getStatusListGroupedByType(session("currentProject"));

            $sql = "SELECT
						zp_canvas_items.id,
						zp_canvas_items.description,
						zp_canvas_items.assumptions,
						zp_canvas_items.data,
						zp_canvas_items.conclusion,
						zp_canvas_items.box,
						zp_canvas_items.author,
						zp_canvas_items.created,
						zp_canvas_items.modified,
						zp_canvas_items.canvasId,
						zp_canvas_items.sortindex,
						zp_canvas_items.milestoneId,
						IF(zp_canvas_items.status IS NULL, 'idea', zp_canvas_items.status) as status,
						t1.firstname AS authorFirstname,
						t1.lastname AS authorLastname,
						t1.profileId AS authorProfileId,
						milestone.headline as milestoneHeadline,
						milestone.editTo as milestoneEditTo,
						COUNT(DISTINCT zp_comment.id) AS commentCount

				FROM
				zp_canvas_items

				LEFT JOIN zp_user AS t1 ON zp_canvas_items.author = t1.id
			    LEFT JOIN zp_tickets AS milestone ON milestone.id = zp_canvas_items.milestoneId
			    LEFT JOIN zp_comment ON zp_canvas_items.id = zp_comment.moduleId and zp_comment.module = 'idea'
				WHERE zp_canvas_items.canvasId = :id
				GROUP BY zp_canvas_items.id
				ORDER BY zp_canvas_items.sortindex";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $id
         * @return mixed
         */
        public function getSingleCanvasItem($id): mixed
        {

            $statusGroups = $this->ticketRepo->getStatusListGroupedByType(session("currentProject"));

            $sql = "SELECT
						zp_canvas_items.id,
						zp_canvas_items.description,
						zp_canvas_items.assumptions,
						zp_canvas_items.data,
						zp_canvas_items.conclusion,
						zp_canvas_items.box,
						zp_canvas_items.author,
						zp_canvas_items.created,
						zp_canvas_items.modified,
						zp_canvas_items.canvasId,
						zp_canvas_items.sortindex,
						zp_canvas_items.status,
						zp_canvas_items.tags,
						zp_canvas_items.milestoneId,
						t1.firstname AS authorFirstname,
						t1.lastname AS authorLastname,
						zp_canvas_items.milestoneId,
						milestone.headline as milestoneHeadline,
						milestone.editTo as milestoneEditTo
				FROM
				zp_canvas_items
			    LEFT JOIN zp_tickets AS milestone ON milestone.id = zp_canvas_items.milestoneId
				LEFT JOIN zp_user AS t1 ON zp_canvas_items.author = t1.id
				WHERE zp_canvas_items.id = :id
				";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $values
         * @return false|string
         */
        public function addCanvasItem($values): false|string
        {

            $query = "INSERT INTO zp_canvas_items (
						description,
						assumptions,
						data,
						conclusion,
						box,
						author,
						created,
						modified,
						canvasId,
						status,
						milestoneId
				) VALUES (
						:description,
						:assumptions,
						:data,
						:conclusion,
						:box,
						:author,
						NOW(),
						NOW(),
						:canvasId,
						:status,
						:milestoneId
				)";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);
            $stmn->bindValue(':assumptions', $values['assumptions'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':data', $values['data'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':conclusion', $values['conclusion'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':box', $values['box'] ?? "idea", PDO::PARAM_STR);
            $stmn->bindValue(':author', $values['author'] ?? session("userdata.id"), PDO::PARAM_INT);
            $stmn->bindValue(':canvasId', $values['canvasId'], PDO::PARAM_INT);
            $stmn->bindValue(':status', $values['status'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':milestoneId', $values['milestoneId'] ?? "", PDO::PARAM_STR);

            $stmn->execute();
            $id = $this->db->database->lastInsertId();
            $stmn->closeCursor();

            return $id;
        }


        /**
         * @param $id
         * @return void
         */
        public function delCanvasItem($id): void
        {
            $query = "DELETE FROM zp_canvas_items WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();
        }


        /**
         * @param $ideaId
         * @param $status
         * @return bool
         */
        public function updateIdeaStatus($ideaId, $status): bool
        {

            $query = "UPDATE zp_canvas_items SET
					    box = :status
					  WHERE id = :id LIMIT 1
                ";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':status', $status, PDO::PARAM_STR);

            $stmn->bindValue(':id', $ideaId, PDO::PARAM_INT);
            $result = $stmn->execute();

            $stmn->closeCursor();

            return $result;
        }

        /**
         * @param $projectId
         * @return int|mixed
         */
        public function getNumberOfIdeas($projectId = null): mixed
        {

            $sql = "SELECT
					count(zp_canvas_items.id) AS ideaCount
				FROM
				zp_canvas_items
				LEFT JOIN zp_canvas AS canvasBoard ON zp_canvas_items.canvasId = canvasBoard.id
				WHERE canvasBoard.type = 'idea'  ";

            if (!is_null($projectId)) {
                $sql .= " AND canvasBoard.projectId = :projectId";
            }

            $stmn = $this->db->database->prepare($sql);

            if (!is_null($projectId)) {
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if (isset($values['ideaCount']) === true) {
                return $values['ideaCount'];
            } else {
                return 0;
            }
        }

        /**
         * @param $projectId
         * @return int|mixed
         */
        public function getNumberOfBoards($projectId = null): mixed
        {

            $sql = "SELECT
                        count(zp_canvas.id) AS boardCount
                FROM
                    zp_canvas
                  WHERE zp_canvas.type = 'idea'
                ";

            if (!is_null($projectId)) {
                $sql .= " WHERE zp_canvas.projectId = :projectId";
            }

            $stmn = $this->db->database->prepare($sql);

            if (!is_null($projectId)) {
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if (isset($values['boardCount'])) {
                return $values['boardCount'];
            }

            return 0;
        }

        /**
         * @param $params
         * @return bool
         */
        public function bulkUpdateIdeaStatus($params): bool
        {



            //Jquery sortable serializes the array for kanban in format
            //statusKey: item[]=X&item[]=X2...,
            //statusKey2: item[]=X&item[]=X2...,
            //This represents status & kanban sorting
            foreach ($params as $status => $ideaList) {
                $ideas = explode("&", $ideaList);


                if (is_array($ideas) === true && count($ideas) > 0) {
                    foreach ($ideas as $key => $ideaString) {
                        if (strlen($ideaString) > 0) {
                            $id = substr($ideaString, 7);

                            if ($this->updateIdeaStatus($id, $status) === false) {
                                return false;
                            }
                        }
                    }
                }
            }

            return true;
        }

        public function getAllIdeas(?int $projectId, ?int $boardId): array|false
        {
            $sql = "SELECT zp_canvas_items.id,
                zp_canvas_items.description,
                zp_canvas_items.assumptions,
                zp_canvas_items.data,
                zp_canvas_items.conclusion,
                zp_canvas_items.box,
                zp_canvas_items.author,
                zp_canvas_items.created,
                zp_canvas_items.modified,
                zp_canvas_items.canvasId,
                zp_canvas_items.sortindex,
                zp_canvas_items.status,
                zp_canvas_items.tags,
                zp_canvas_items.milestoneId,
                zp_canvas.projectId
                FROM zp_canvas_items
                LEFT JOIN zp_canvas ON zp_canvas_items.canvasId = zp_canvas.id
                LEFT JOIN zp_projects ON zp_canvas.projectId = zp_projects.id
                WHERE zp_canvas_items.box = 'idea' AND (
                    zp_canvas.projectId IN (SELECT projectId FROM zp_relationuserproject WHERE zp_relationuserproject.userId = :userId)
                    OR zp_projects.psettings = 'all'
                    OR (zp_projects.psettings = 'client' AND zp_projects.clientId = :clientId)
                    OR (:requesterRole = 'admin' OR :requesterRole = 'manager')
                )
                ";

            if (isset($projectId) && $projectId  > 0) {
                $sql .= " AND (zp_canvas.projectId = :projectId)";
            }

            if (isset($boardId) && $boardId  > 0) {
                $sql .= " AND (zp_canvas.id = :boardId)";
            }

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':clientId', session("userdata.clientId") ?? '-1', PDO::PARAM_INT);
            $stmn->bindValue(':userId', session("userdata.id") ?? '-1', PDO::PARAM_INT);

            if (session()->exists("userdata")) {
                $stmn->bindValue(':requesterRole', session("userdata.role"), PDO::PARAM_INT);
            } else {
                $stmn->bindValue(':requesterRole', -1, PDO::PARAM_INT);
            }


            if (isset($projectId) && $projectId  > 0) {
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            }

            if (isset($boardId) && $boardId  > 0) {
                $stmn->bindValue(':boardId', $boardId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetchAll(PDO::FETCH_ASSOC);
            $stmn->closeCursor();

            return $values;
        }
    }
}
