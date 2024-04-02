<?php

namespace Leantime\Domain\Wiki\Repositories {

    use Leantime\Domain\Canvas\Repositories\Canvas;
    use Leantime\Domain\Wiki\Models\Article;
    use PDO;

    /**
     *
     */
    class Wiki extends Canvas
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = 'wiki';

        /**
         * @param $id
         * @param $projectId
         * @return mixed
         */
        public function getArticle($id, $projectId): mixed
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
                    milestone.editTo as milestoneEditTo

				FROM zp_canvas_items
				LEFT JOIN zp_canvas ON zp_canvas.id = zp_canvas_items.canvasId
				LEFT JOIN zp_user ON zp_canvas_items.author = zp_user.id
			    LEFT JOIN zp_tickets AS milestone ON milestone.id = zp_canvas_items.milestoneId
				WHERE zp_canvas.projectId = :projectId AND zp_canvas_items.box = 'article'";

            if ($id > 0) {
                $query .= " AND zp_canvas_items.id = :id";
            } elseif ($id == -1) {
                $query .= " AND featured = 1";
            }

            $query .= " LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            if ($id > 0) {
                $stmn->bindValue(':id', $id, PDO::PARAM_INT);
            }

            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "Leantime\Domain\Wiki\Models\Article");
            $value = $stmn->fetch();

            $stmn->closeCursor();

            return $value;
        }

        /**
         * @param $projectId
         * @return array|false
         */
        public function getAllProjectWikis($projectId): array|false
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
            $stmn->setFetchMode(PDO::FETCH_CLASS, "Leantime\Domain\Wiki\Models\Wiki");
            $values = $stmn->fetchAll();

            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $id
         * @return mixed
         */
        public function getWiki($id): mixed
        {
            $query = "SELECT

                    zp_canvas.id,
                    zp_canvas.title,
                    zp_canvas.author,
                    zp_canvas.created,
                    zp_canvas.projectId

				FROM zp_canvas

				WHERE zp_canvas.id = :id AND zp_canvas.type = 'wiki'";


            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);


            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "Leantime\Domain\Wiki\Models\Wiki");
            $values = $stmn->fetch();

            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $canvasId
         * @param $userId
         * @return array|false
         */
        public function getAllWikiHeadlines($canvasId, $userId): false|array
        {
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
            $stmn->setFetchMode(PDO::FETCH_CLASS, "Leantime\Domain\Wiki\Models\Article");
            $values = $stmn->fetchAll();

            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $wiki
         * @return false|string
         */
        public function createWiki($wiki): false|string
        {

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
            $stmn->bindValue(':created', date("Y-m-d"), PDO::PARAM_STR);

            $execution = $stmn->execute();


            $stmn->closeCursor();

            return $this->db->database->lastInsertId();
        }

        /**
         * @param $wiki
         * @param $wikiId
         * @return bool
         */
        public function updateWiki($wiki, $wikiId): bool
        {

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

        /**
         * @param Article $article
         * @return false|string
         */
        public function createArticle(Article $article): false|string
        {

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
            $stmn->bindValue(':created', date("Y-m-d"), PDO::PARAM_STR);
            $stmn->bindValue(':modified', date("Y-m-d"), PDO::PARAM_STR);
            $stmn->bindValue(':sortIndex', "10", PDO::PARAM_STR);

            $execution = $stmn->execute();

            $stmn->closeCursor();

            return $this->db->database->lastInsertId();
        }

        /**
         * @param Article $article
         * @return bool
         */
        public function updateArticle(Article $article): bool
        {

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
            $stmn->bindValue(':modified', date("Y-m-d"), PDO::PARAM_STR);
            $stmn->bindValue(':id', $article->id, PDO::PARAM_STR);
            $stmn->bindValue(':milestoneId', $article->milestoneId, PDO::PARAM_STR);

            $execution = $stmn->execute();

            $stmn->closeCursor();

            return $execution;
        }

        /**
         * @param $id
         * @return void
         */
        public function delArticle($id): void
        {
            $query = "DELETE FROM zp_canvas_items WHERE id = :id LIMIT 1";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();
        }

        /**
         * @param $id
         * @return void
         */
        public function delWiki($id): void
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
         * @param $projectId
         * @return int|mixed
         */
        public function getNumberOfBoards($projectId = null): mixed
        {

            $sql = "SELECT
                        count(zp_canvas.id) AS boardCount
                FROM
                    zp_canvas
                WHERE zp_canvas.type = 'wiki'";

            if (!is_null($projectId)) {
                $sql .= " AND zp_canvas.projectId = :projectId ";
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
         * @param $projectId
         * @return int|mixed
         */
        public function getNumberOfCanvasItems($projectId = null): mixed
        {

            $sql = "SELECT
                    count(zp_canvas_items.id) AS canvasCount
                FROM
                zp_canvas_items
                LEFT JOIN zp_canvas AS canvasBoard ON zp_canvas_items.canvasId = canvasBoard.id
                WHERE canvasBoard.type = 'wiki'  ";

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

            if (isset($values['canvasCount']) === true) {
                return $values['canvasCount'];
            }

            return 0;
        }
    }
}
