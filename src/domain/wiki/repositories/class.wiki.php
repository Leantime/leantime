<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use leantime\domain\models\wiki\article;
    use pdo;

    class wiki
    {


        /**
         * __construct - get database connection
         *
         * @access public
         */
        public function __construct()
        {
            $this->db = core\db::getInstance();
        }


        public function getArticle($id, $projectId)
        {
            $query = "SELECT
					zp_canvas_items.id,
                    zp_canvas_items.title,
                    zp_canvas_items.description,
                    zp_canvas_items.canvasId,
                    zp_canvas_items.parent,
                    zp_canvas_items.tags,
                    zp_canvas_items.data,
                    zp_canvas_items.status,
                    zp_canvas_items.created,
                    zp_canvas_items.modified,
                    zp_canvas_items.author,
                    zp_canvas_items.milestoneId,
                    zp_user.firstname,
                    zp_user.lastname,
                    zp_user.profileId,
                    zp_canvas_items.sortindex,
                    zp_canvas.projectId,
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

				FROM zp_canvas_items 
				LEFT JOIN zp_canvas ON zp_canvas.id = zp_canvas_items.canvasId
				LEFT JOIN zp_user ON zp_canvas_items.author = zp_user.id
				LEFT JOIN zp_tickets AS progressTickets ON progressTickets.dependingTicketId = zp_canvas_items.milestoneId AND progressTickets.type <> 'milestone' AND progressTickets.type <> 'subtask' 
			    LEFT JOIN zp_tickets AS milestone ON milestone.id = zp_canvas_items.milestoneId
				WHERE zp_canvas.projectId = :projectId AND zp_canvas_items.box = 'article'";

            if($id>0) {
                $query .= " AND zp_canvas_items.id = :id";
            }else if($id==-1) {
                $query .= " AND featured = 1";
            }

            $query .= " LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            if($id>0) {
                $stmn->bindValue(':id', $id, PDO::PARAM_INT);
            }

            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "leantime\domain\models\wiki\article");
            $value = $stmn->fetch();

            $stmn->closeCursor();

            return $value;
        }

        public function getAllProjectWikis($projectId)
        {
            $query = "SELECT
					
                    zp_canvas.id,
                    zp_canvas.title,
                    zp_canvas.author,
                    zp_canvas.created
                
				FROM zp_canvas 
			
				WHERE zp_canvas.projectId = :projectId AND zp_canvas.type = 'wiki'";


            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);


            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "leantime\domain\models\wiki");
            $values = $stmn->fetchAll();

            $stmn->closeCursor();

            return $values;
        }

        public function getWiki($id)
        {
            $query = "SELECT
					
                    zp_canvas.id,
                    zp_canvas.title,
                    zp_canvas.author,
                    zp_canvas.created
                
				FROM zp_canvas 
			
				WHERE zp_canvas.id = :id AND zp_canvas.type = 'wiki'";


            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);


            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "leantime\domain\models\wiki");
            $values = $stmn->fetch();

            $stmn->closeCursor();

            return $values;
        }

        public function getAllWikiHeadlines($canvasId, $userId) {
            $query = "SELECT
					
                    id,
                    title,
                    parent,
                    sortindex,
                    status,
                    data
                    
				FROM zp_canvas_items 
			
				WHERE canvasId = :canvasId 
				  AND box = 'article' AND (status = 'published' OR (status = 'draft' AND author = :authorId) )
				ORDER BY parent DESC, sortindex DESC";


            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':canvasId', $canvasId, PDO::PARAM_INT);
            $stmn->bindValue(':authorId', $userId, PDO::PARAM_INT);


            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "leantime\domain\models\wiki\article");
            $values = $stmn->fetchAll();

            $stmn->closeCursor();

            return $values;
        }

        public function createWiki($wiki) {

            $query = "INSERT INTO zp_canvas 
                    (title, 
                     projectId, 
                     author,
                     created,
                     type) VALUES 
                     (:title, 
                      :projectId, 
                      :author, 
                      :created,
                      'wiki')";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':title', $wiki->title, PDO::PARAM_STR);
            $stmn->bindValue(':projectId', $wiki->projectId, PDO::PARAM_STR);
            $stmn->bindValue(':author', $wiki->author, PDO::PARAM_STR);
            $stmn->bindValue(':created',date("Y-m-d"), PDO::PARAM_STR);

            $execution = $stmn->execute();


            $stmn->closeCursor();

            return $this->db->database->lastInsertId();
        }

        public function updateWiki($wiki, $wikiId){

            $query = "UPDATE zp_canvas
                     
                        SET 
                     title = :title

                        WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':title', $wiki->title, PDO::PARAM_STR);
            $stmn->bindValue(':id', $wikiId, PDO::PARAM_STR);

            $execution = $stmn->execute();

            $stmn->closeCursor();

            return $execution;
        }

        public function createArticle(article $article) {

            $query = "INSERT INTO zp_canvas_items 
                    (title, 
                     description, 
                     data,
                     box,
                     author,
                     canvasId,
                     parent,
                     tags,
                     status,
                     created,
                     modified,
                     sortIndex
                     ) VALUES 
                     (
                     :title, 
                     :description, 
                     :data,
                     'article',
                     :author,
                     :canvasId,
                     :parent,
                     :tags,
                     :status,
                     :created,
                     :modified,
                     :sortIndex
                      )";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':title', $article->title, PDO::PARAM_STR);
            $stmn->bindValue(':description', $article->description, PDO::PARAM_STR);
            $stmn->bindValue(':data', $article->data, PDO::PARAM_STR);
            $stmn->bindValue(':author', $article->author, PDO::PARAM_STR);
            $stmn->bindValue(':canvasId', $article->canvasId, PDO::PARAM_INT);
            $stmn->bindValue(':parent', $article->parent, PDO::PARAM_INT);
            $stmn->bindValue(':tags', $article->tags, PDO::PARAM_STR);
            $stmn->bindValue(':status', $article->status, PDO::PARAM_STR);
            $stmn->bindValue(':created',date("Y-m-d"), PDO::PARAM_STR);
            $stmn->bindValue(':modified',date("Y-m-d"), PDO::PARAM_STR);
            $stmn->bindValue(':sortIndex', "10", PDO::PARAM_STR);

            $execution = $stmn->execute();

            $stmn->closeCursor();

            return $this->db->database->lastInsertId();
        }

        public function updateArticle(article $article) {

            $query = "UPDATE zp_canvas_items 
                     
                        SET 
                     title = :title,
                     description = :description, 
                     data = :data,
                     parent = :parent,
                     tags = :tags,
                     status = :status, 
                     modified = :modified,
                     milestoneId = :milestoneId

                        WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':title', $article->title, PDO::PARAM_STR);
            $stmn->bindValue(':description', $article->description, PDO::PARAM_STR);
            $stmn->bindValue(':data', $article->data, PDO::PARAM_STR);
            $stmn->bindValue(':parent', $article->parent, PDO::PARAM_INT);
            $stmn->bindValue(':tags', $article->tags, PDO::PARAM_STR);
            $stmn->bindValue(':status', $article->status, PDO::PARAM_STR);
            $stmn->bindValue(':modified',date("Y-m-d"), PDO::PARAM_STR);
            $stmn->bindValue(':id', $article->id, PDO::PARAM_STR);
            $stmn->bindValue(':milestoneId', $article->milestoneId, PDO::PARAM_STR);

            $execution = $stmn->execute();

            $stmn->closeCursor();

            return $execution;

        }

        public function delArticle($id)
        {
            $query = "DELETE FROM zp_canvas_items WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();
        }

        public function delWiki($id)
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

    }
}