<?php
/**
 * Generic / Tempalate of canvas repository class
 */
namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class canvas
    {

        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = '??';

        /**
         * canvasTypes - Must be extended
         *
         * @acces protected
         * @var   array
         */
		protected $canvasTypes = [
	        // 'xx_' => [ 'icon' => 'faX fa-', 'color' => 'white', 'title' => 'box.xx.yy', 'active' => true], 
		];

        /**
         * statusLabels - May be cextended
         *
         * @acces protected
         * @var   array
         */
		protected $statusLabelsAll = [ 'icon' => 'fas fa-fw fa-globe', 'color' => '', 'title' => 'status.all', 'dropdown' => '', 'active' => true];
		protected $statusLabels = [
	        'status_draft' => 
                [ 'icon' => 'fas fa-fw fa-file-lines','color' => 'white','title' => 'status.draft','dropdown' => 'info','active' => true],
			'status_review' => 
                [ 'icon' => 'fas fa-fw fa-magnify-glass','color' => 'white','title' => 'status.review','dropdown' => 'warning','active' => true],
			'status_valid' => 
                [ 'icon' => 'fas fa-fw fa-check','color' => 'white','title' => 'status.valid','dropdown' => 'success','active' => true],
			'status_invalid' => 
                [ 'icon' => 'fas fa-fw fa-triangle-questions','color' => 'white','title' => 'status.invalid','dropdown' => 'danger','active'=>true]
	    ];

		/**
		 * dataLabels - May be extended
         *
         * @acces protected
         * @var   array
		 */
		protected $dataLabels = [
		    1 => [ 'title' => 'label.assumptions', 'field' => 'assumptions', 'placeholder' => 'input.placeholders.describe_assumption', 'active' => true],
			2 => [ 'title' => 'label.data',        'field' => 'data',        'placeholder' => 'input.placeholders.describe_data', 'active' => true],
			3 => [ 'title' => 'label.conclusion',  'field' => 'conclusion',  'placeholder' => 'input.placeholders.describe_conclusion', 'active' => true]];

		/**
		 * relationLabels - Max be extended (same structure as `statusLabels`)
		 *
         * @acces public
         * @var   array
		 */
		public $relationLabels = [];

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

		/**
		 * getCanvasTypes() - Retrieve translated data
		 *
		 * @access public
		 * @return array  Array of data
		 */
		public function getCanvasTypes(): array
		{
			
			$canvasTypes = $this->canvasTypes;
			foreach($canvasTypes as $key => $data) {
				if(isset($data['title'])) {
				    $data['title'] = $this->language->__($data['title']);
				}
			}
			return $canvasTypes;
			
		}
		
		/**
		 * getStatusLabelsAll() - Retrieve translated data
		 *
		 * @access public
		 * @return array  Array of data
		 */
		public function getStatusLabelsAll(): array
		{
			
			$statusLabelsAll = $this->statusLabelsAll;
			if(isset($statusLabelsAll['title'])) {
				    $$statusLabelsAll['title'] = $this->language->__($$statusLabelsAll['title']);
				}
			}
		    return $statusLabelsAll;
			
		}
		/**
		 * getStatusLabels() - Retrieve translated data
		 *
		 * @access public
		 * @return array  Array of data
		 */
		public function getStatusLabels(): array
		{
			
			$statusLabel = $this->statusLabel;
			foreach($statusLabel as $key => $data) {
				if(isset($data['title'])) {
				    $data['title'] = $this->language->__($data['title']);
				}
			}
			
		}

		/**
		 * getDataLabels() - Retrieve translated data
		 *
		 * @access public
		 * @return array  Array of data
		 */
		public function getDataLabels(): array
		{
			
			$dataLabels = $this->dataLabels;
			foreach($dataLabels as $key => $data) {
				if(isset($data['title'])) {
				    $data['title'] = $this->language->__($data['title']);
				}
			}
			return $dataLabels;
			
		}

	    /**
		 * getRelationLabels() - Retrieve translated data
		 *
		 * @access public
		 * @return array  Array of data
		 */
		public function getRelationLabels(): array
		{
			
			$relationLabels = $this->relationLabels;
			foreach($relationLabels as $key => $data) {
				if(isset($data['title'])) {
				    $data['title'] = $this->language->__($data['title']);
				}
			}
			return $relationLabels;
			
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
				WHERE type = '".static::CANVAS_NAME."canvas' AND projectId = :projectId
				ORDER BY zp_canvas.title, zp_canvas.created";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;

        }

        public function getSingleCanvas($canvasId)
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
                WHERE type = '".static::CANVAS_NAME."canvas' AND zp_canvas.id = :canvasId
                ORDER BY zp_canvas.title, zp_canvas.created";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':canvasId', $canvasId, PDO::PARAM_STR);

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
                        '".static::CANVAS_NAME."canvas',
						:projectId
				)";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':title', $values['title'], PDO::PARAM_STR);
            $stmn->bindValue(':author', $values['author'], PDO::PARAM_STR);
            $stmn->bindValue(':projectId', $values['projectId'], PDO::PARAM_STR);


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
						zp_canvas_items.status,
						zp_canvas_items.milestoneId,						
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
			    LEFT JOIN zp_comment ON zp_canvas_items.id = zp_comment.moduleId and zp_comment.module = '".static::CANVAS_NAME."canvasitem'
				WHERE zp_canvas_items.canvasId = :id 
				GROUP BY zp_canvas_items.id
				ORDER BY zp_canvas_items.box, zp_canvas_items.sortindex";

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
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

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

        public function getNumberOfCanvasItems($projectId = null)
        {

            $sql = "SELECT
					count(zp_canvas_items.id) AS canvasCount	
				FROM 
				zp_canvas_items
				LEFT JOIN zp_canvas AS canvasBoard ON zp_canvas_items.canvasId = canvasBoard.id
				WHERE canvasBoard.type = '".static::CANVAS_NAME."canvas'  ";

            if(!is_null($projectId)){
                $sql.=" AND canvasBoard.projectId = :projectId";
            }

            $stmn = $this->db->database->prepare($sql);

            if(!is_null($projectId)){
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if(isset($values['canvasCount']) === true) {
                return $values['canvasCount'];
            }else{
                return 0;
            }
        }

        public function getNumberOfBoards($projectId=null)
        {

            $sql = "SELECT
						count(zp_canvas.id) AS boardCount
				FROM 
				    zp_canvas
				";

            if(!is_null($projectId)){
                $sql.=" WHERE canvasBoard.projectId = :projectId and canvasBoard.type = '".static::CANVAS_NAME."canvas' ";
            }

            $stmn = $this->db->database->prepare($sql);

            if(!is_null($projectId)){
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            if(isset($values['boardCount'])) {
                return $values['boardCount'];
            }

            return 0;

        }
        /**
         * existCanvas - return if a canvas exists with a given title in the specified project
         *
         * @access public
         * @param  int    $projectId Project identifier
         * @param  string $canvasTitle Canvas title
         * @return bool   True if canvas exists
         */
        public function existCanvas(int $projectId, string $canvasTitle): bool
        {

            $sql = "SELECT COUNT(id) as nbCanvas ".
                "FROM zp_canvas ".
                "WHERE projectId = :projectId AND title = :canvasTitle AND type = '".static::CANVAS_NAME."canvas'";
            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            $stmn->bindValue(':canvasTitle', $canvasTitle, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return isset($values['nbCanvas']) && $values['nbCanvas'] > 0;

        }

        /***
         * copyCanvas - create a copy of an existing canvas
         *
         * @access public
         * @param  int    $projectId   Project identifier
         * @param  int    $canvasId    Original canvas identifier
         * @param  int    $authorId    Author identifier
         * @param  string $canvasTitle New canvas title
         * @return int    Identifier of new canvas
         */
        public function copyCanvas(int $projectId, int $canvasId, int $authorId, string $canvasTitle): int
        {

            // Create new canvas
            $values = ["title" => $canvasTitle, "author" => $authorId, "projectId" => $projectId, "type" => static::CANVAS_NAME."canvas"];
            $newCanvasId = $this->addCanvas($values);
            
            // Copy elements from existing canvas to new canvas
            $sql = "INSERT INTO ".
              "zp_canvas_items (description,assumptions,data,conclusion,box,author,created,modified,canvasId,status,milestoneId) ".
              "SELECT description, assumptions, data, conclusion, box, author, NOW(), NOW(), $newCanvasId, status, '' ".
              "FROM zp_canvas_items WHERE canvasId = $canvasId";
            $stmn = $this->db->database->prepare($sql);

            $stmn->execute();
            $stmn->closeCursor();

            return $newCanvasId;

        }

    }
}
