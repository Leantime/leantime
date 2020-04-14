<?php

namespace leantime\domain\repositories {

    use leantime\core;
    use pdo;

    class sprints
    {



        /**
         * __construct - get database connection
         *
         * @access public
         */
        function __construct()
        {

            $this->db = core\db::getInstance();

        }

        /**
         * getSprint - get single sprint
         *
         * @access public
         * @return array
         */
        public function getSprint($id)
        {

            $query = "SELECT
					sprint.id,
					sprint.name,
					sprint.projectId,
					sprint.startDate,
					sprint.endDate
				FROM zp_sprints as sprint
				WHERE sprint.id = :id
				LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);
            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "leantime\domain\models\sprints");
            $value = $stmn->fetch();

            $stmn->closeCursor();

            return $value;
        }

        /**
         * getAllSprints - get all sprints for a project
         *
         * @access public
         * @return array
         */
        public function getAllSprints($projectId)
        {


            $query = "SELECT
					zp_sprints.id,
					zp_sprints.name,
					zp_sprints.projectId,
					zp_sprints.startDate,
					zp_sprints.endDate
				FROM zp_sprints 
				WHERE zp_sprints.projectId = :id
				ORDER BY zp_sprints.startDate DESC";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $projectId, PDO::PARAM_INT);
            $stmn->execute();

            $value = $stmn->fetchAll(PDO::FETCH_CLASS, "leantime\domain\models\sprints");

            $stmn->closeCursor();

            return $value;
        }

        /**
         * getAllSprints - get all sprints for a project
         *
         * @access public
         * @return array
         */
        public function getAllFutureSprints($projectId)
        {


            $query = "SELECT
					zp_sprints.id,
					zp_sprints.name,
					zp_sprints.projectId,
					zp_sprints.startDate,
					zp_sprints.endDate
				FROM zp_sprints 
				WHERE zp_sprints.projectId = :id AND zp_sprints.endDate > NOW() 
				ORDER BY zp_sprints.startDate DESC";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $projectId, PDO::PARAM_INT);
            $stmn->execute();

            $value = $stmn->fetchAll(PDO::FETCH_CLASS, "leantime\domain\models\sprints");

            $stmn->closeCursor();

            return $value;
        }

        /**
         * getCurrentSprintId - get current sprint for a project
         *
         * @access public
         * @return array
         */
        public function getCurrentSprint($projectId)
        {

            $query = "SELECT
					zp_sprints.id,
					zp_sprints.name,
					zp_sprints.projectId,
					zp_sprints.startDate,
					zp_sprints.endDate
				FROM zp_sprints 
				WHERE zp_sprints.projectId = :id
				AND zp_sprints.startDate < NOW() && zp_sprints.endDate > NOW() ORDER BY zp_sprints.startDate  LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $projectId, PDO::PARAM_INT);
            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "leantime\domain\models\sprints");

            $value = $stmn->fetch();

            $stmn->closeCursor();

            return $value;
        }

        /**
         * getUpcomingSprint - gets the next upcoming sprint
         *
         * @access public
         * @return array
         */
        public function getUpcomingSprint($projectId)
        {

            $query = "SELECT
					zp_sprints.id,
					zp_sprints.name,
					zp_sprints.projectId,
					zp_sprints.startDate,
					zp_sprints.endDate
				FROM zp_sprints 
				WHERE zp_sprints.projectId = :id
				AND zp_sprints.startDate > NOW() ORDER BY zp_sprints.startDate ASC LIMIT 1";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $projectId, PDO::PARAM_INT);
            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "leantime\domain\models\sprints");

            $value = $stmn->fetch();

            $stmn->closeCursor();

            return $value;
        }

        public function addSprint($sprint)
        {

            $query = "INSERT INTO zp_sprints (name, projectId, startDate, endDate) VALUES (:name, :projectId, :startDate, :endDate)";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':name', $sprint->name, PDO::PARAM_STR);
            $stmn->bindValue(':projectId', $sprint->projectId, PDO::PARAM_STR);
            $stmn->bindValue(':startDate', $sprint->startDate, PDO::PARAM_STR);
            $stmn->bindValue(':endDate', $sprint->endDate, PDO::PARAM_STR);

            $execution = $stmn->execute();

            $stmn->closeCursor();

            return $execution;
        }

        public function editSprint($sprint)
        {

            $query = "UPDATE zp_sprints 
                      SET 
                        name = :name, 
                        projectId = :projectId, 
                        startDate = :startDate, 
                        endDate = :endDate
                        WHERE id = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':name', $sprint->name, PDO::PARAM_STR);
            $stmn->bindValue(':projectId', $sprint->projectId, PDO::PARAM_STR);
            $stmn->bindValue(':startDate', $sprint->startDate, PDO::PARAM_STR);
            $stmn->bindValue(':endDate', $sprint->endDate, PDO::PARAM_STR);
            $stmn->bindValue(':id', $sprint->id, PDO::PARAM_STR);

            $execution = $stmn->execute();

            $stmn->closeCursor();

            return $execution;
        }

        public function delSprint($id)
        {

            $query = "UPDATE zp_tickets
                SET 
                    sprint = ''
                WHERE sprint = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();


            $query = "DELETE FROM zp_sprints WHERE id = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();

        }


    }

}?>