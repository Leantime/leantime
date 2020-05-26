<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class ideas
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

        public $canvasTypes = array(
            "idea"=> "status.ideation",
            "research" => "status.discovery",
            "prototype" => "status.delivering",
            "validation" => "status.inreview",
            "implemented" => "status.accepted",
            "deferred" => "status.deferred"
        );

        public $statusClasses = array('idea' => 'label-info', 'validation' => 'label-warning', 'prototype' => 'label-warning', 'research' => 'label-warning', 'implemented' => 'label-success', "deferred" =>"label-default");

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

        public function getCanvasLabels()
        {

            //Todo: Remove!
            unset($_SESSION["projectsettings"]["idealabels"]);

            if(isset($_SESSION["projectsettings"]["idealabels"])) {


                return $_SESSION["projectsettings"]["idealabels"];

            }else{

                $sql = "SELECT
						value
				FROM zp_settings WHERE `key` = :key
				LIMIT 1";

                $stmn = $this->db->database->prepare($sql);
                $stmn->bindvalue(':key', "projectsettings.".$_SESSION['currentProject'].".idealabels", PDO::PARAM_STR);

                $stmn->execute();
                $values = $stmn->fetch();
                $stmn->closeCursor();

                $labels = array();

                //preseed state labels with default values
                foreach($this->canvasTypes as $key=>$label) {
                    $labels[$key] = array(
                        "name" => $this->language->__($label),
                        "class" => $this->statusClasses[$key]
                    );
                }

                if($values !== false) {

                    foreach(unserialize($values['value']) as $key=>$label) {

                        $labels[$key] = array(
                            "name" => $label,
                            "class" => $this->statusClasses[$key]
                        );
                    }
                }

                $_SESSION["projectsettings"]["idealabels"] = $labels;

                return $labels;

            }
        }

        public function getAllCanvas($projectId)
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

        public function deleteCanvas($id)
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

        public function addCanvas($values)
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

        public function updateCanvas($values)
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

        public function editCanvasItem($values)
        {
            $sql = "UPDATE zp_canvas_items SET
					description = :description,
					assumptions =		:assumptions,
					data =			:data,
					conclusion =			:conclusion,
					modified =			NOW(),
					status =			:status,
					milestoneId =			:milestoneId
					WHERE id = :id LIMIT 1	";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':id', $values['itemId'], PDO::PARAM_STR);
            $stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);
            $stmn->bindValue(':assumptions', $values['assumptions'], PDO::PARAM_STR);
            $stmn->bindValue(':data', $values['data'], PDO::PARAM_STR);
            $stmn->bindValue(':conclusion', $values['conclusion'], PDO::PARAM_STR);
            $stmn->bindValue(':status', $values['status'], PDO::PARAM_STR);
            $stmn->bindValue(':milestoneId', $values['milestoneId'], PDO::PARAM_STR);


            $stmn->execute();
            $stmn->closeCursor();

        }

        public function patchCanvasItem($id, $params)
        {

            $sql = "UPDATE zp_canvas_items SET ";

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

        public function updateIdeaSorting($sortingArray) {

            $sql = "INSERT INTO zp_canvas_items (id, sortindex) VALUES ";

            $sqlPrep = array();
            foreach($sortingArray as $idea){
                $sqlPrep[] = "(".(int)$idea['id'].", ".(int)$idea['sortIndex'].")";
            }

            $sql.= implode(",", $sqlPrep);

            $sql.= " ON DUPLICATE KEY UPDATE sortindex = VALUES(sortindex)";

            $stmn = $this->db->database->prepare($sql);

            $return = $stmn->execute();
            $stmn->closeCursor();

            return $return;

        }

        public function getCanvasItemsById($id)
        {

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
						COUNT(DISTINCT zp_comment.id) AS commentCount,			
						SUM(CASE WHEN progressTickets.status < 1 THEN 1 ELSE 0 END) AS doneTickets,
						SUM(CASE WHEN progressTickets.status < 1 THEN 0 ELSE IF(progressTickets.storypoints = 0, 3, progressTickets.storypoints)  END) AS openTicketsEffort,
						SUM(CASE WHEN progressTickets.status < 1 THEN IF(progressTickets.storypoints = 0, 3, progressTickets.storypoints) ELSE 0 END) AS doneTicketsEffort,
						SUM(IF(progressTickets.storypoints = 0, 3, progressTickets.storypoints)) AS allTicketsEffort,
						COUNT(progressTickets.id) AS allTickets,
						
						CASE WHEN 
						  COUNT(progressTickets.id) > 0 
						THEN 
						  ROUND(
						    (
						      SUM(CASE WHEN progressTickets.status < 1 THEN IF(progressTickets.storypoints = 0, 3, progressTickets.storypoints) ELSE 0 END) / 
						      SUM(IF(progressTickets.storypoints = 0, 3, progressTickets.storypoints))
						    ) *100) 
						ELSE 
						  0 
						END AS percentDone
						
						
						
				
				FROM 
				zp_canvas_items
			
				LEFT JOIN zp_user AS t1 ON zp_canvas_items.author = t1.id
				LEFT JOIN zp_tickets AS progressTickets ON progressTickets.dependingTicketId = zp_canvas_items.milestoneId AND progressTickets.type <> 'milestone' AND progressTickets.type <> 'subtask'
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

        public function getSingleCanvasItem($id)
        {

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
						zp_canvas_items.milestoneId,				
						t1.firstname AS authorFirstname, 
						t1.lastname AS authorLastname,
						zp_canvas_items.milestoneId,
						milestone.headline as milestoneHeadline,
						milestone.editTo as milestoneEditTo,
						SUM(CASE WHEN progressTickets.status < 1 THEN 1 ELSE 0 END) AS doneTickets,
						SUM(CASE WHEN progressTickets.status < 1 THEN 0 ELSE IF(progressTickets.storypoints = 0, 3, progressTickets.storypoints)  END) AS openTicketsEffort,
						SUM(CASE WHEN progressTickets.status < 1 THEN IF(progressTickets.storypoints = 0, 3, progressTickets.storypoints) ELSE 0 END) AS doneTicketsEffort,
						SUM(IF(progressTickets.storypoints = 0, 3, progressTickets.storypoints)) AS allTicketsEffort,
						COUNT(progressTickets.id) AS allTickets,
						
						CASE WHEN 
						  COUNT(progressTickets.id) > 0 
						THEN 
						  ROUND(
						    (
						      SUM(CASE WHEN progressTickets.status < 1 THEN IF(progressTickets.storypoints = 0, 3, progressTickets.storypoints) ELSE 0 END) / 
						      SUM(IF(progressTickets.storypoints = 0, 3, progressTickets.storypoints))
						    ) *100) 
						ELSE 
						  0 
						END AS percentDone
				FROM 
				zp_canvas_items
			    LEFT JOIN zp_tickets AS progressTickets ON progressTickets.dependingTicketId = zp_canvas_items.milestoneId AND progressTickets.type <> 'milestone' AND progressTickets.type <> 'subtask'
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

        public function addCanvasItem($values)
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
            $stmn->bindValue(':assumptions', $values['assumptions'], PDO::PARAM_STR);
            $stmn->bindValue(':data', $values['data'], PDO::PARAM_STR);
            $stmn->bindValue(':conclusion', $values['conclusion'], PDO::PARAM_STR);
            $stmn->bindValue(':box', $values['box'], PDO::PARAM_STR);
            $stmn->bindValue(':author', $values['author'], PDO::PARAM_INT);
            $stmn->bindValue(':canvasId', $values['canvasId'], PDO::PARAM_INT);
            $stmn->bindValue(':status', $values['status'], PDO::PARAM_STR);
            $stmn->bindValue(':milestoneId', $values['milestoneId']??"", PDO::PARAM_STR);

            $stmn->execute();
            $id = $this->db->database->lastInsertId();
            $stmn->closeCursor();

            return $id;
        }


        public function delCanvasItem($id)
        {
            $query = "DELETE FROM zp_canvas_items WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();
        }


        public function updateIdeaStatus($ideaId, $status)
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

        public function bulkUpdateIdeaStatus($params)
        {



            //Jquery sortable serializes the array for kanban in format
            //statusKey: item[]=X&item[]=X2...,
            //statusKey2: item[]=X&item[]=X2...,
            //This represents status & kanban sorting
            foreach($params as $status=>$ideaList){




                $ideas = explode("&", $ideaList);


                if (is_array($ideas) === true && count($ideas) > 0) {
                    foreach ($ideas as $key => $ideaString) {
                        if(strlen($ideaString) > 0) {

                            $id = substr($ideaString, 7);

                            if ($this->updateIdeaStatus($id, $status) === false) {

                                return false;
                            };
                        }

                    }
                }

            }

            return true;


        }

    }
}
