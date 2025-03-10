<?php

namespace Leantime\Domain\Sprints\Repositories {

    use Leantime\Core\Db\Db as DbCore;
    use PDO;

    class Sprints
    {
        private DbCore $db;

        /**
         * __construct - get database connection
         */
        public function __construct(DbCore $db)
        {
            $this->db = $db;
        }

        /**
         * getSprint - get single sprint
         *
         * @param  int  $id
         */
        public function getSprint($id): \Leantime\Domain\Sprints\Models\Sprints|false
        {

            $query = 'SELECT
					sprint.id,
					sprint.name,
					sprint.projectId,
					sprint.startDate,
					sprint.endDate,
					sprint.modified
				FROM zp_sprints as sprint
				WHERE sprint.id = :id
				LIMIT 1';

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_INT);
            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "Leantime\Domain\Sprints\Models\Sprints");
            $value = $stmn->fetch();

            $stmn->closeCursor();

            return $value;
        }

        /**
         * getAllSprints - get all sprints for a project
         */
        public function getAllSprints($projectId = null): array
        {

            $query = 'SELECT
					zp_sprints.id,
					zp_sprints.name,
					zp_sprints.projectId,
					zp_sprints.startDate,
					zp_sprints.endDate,
					zp_sprints.modified
				FROM zp_sprints';

            if ($projectId != null) {
                $query .= ' WHERE zp_sprints.projectId = :id';
            }
            $query .= ' ORDER BY zp_sprints.startDate DESC';

            $stmn = $this->db->database->prepare($query);

            if ($projectId != null) {
                $stmn->bindValue(':id', $projectId, PDO::PARAM_INT);
            }

            $stmn->execute();

            $value = $stmn->fetchAll(PDO::FETCH_CLASS, "Leantime\Domain\Sprints\Models\Sprints");

            $stmn->closeCursor();

            return $value;
        }

        /**
         * getAllSprints - get all sprints for a project
         */
        public function getAllFutureSprints($projectId): array
        {

            $query = 'SELECT
					zp_sprints.id,
					zp_sprints.name,
					zp_sprints.projectId,
					zp_sprints.startDate,
					zp_sprints.endDate,
					zp_sprints.modified
				FROM zp_sprints
				WHERE zp_sprints.projectId = :id AND zp_sprints.endDate > NOW()
				ORDER BY zp_sprints.startDate DESC';

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $projectId, PDO::PARAM_INT);
            $stmn->execute();

            $value = $stmn->fetchAll(PDO::FETCH_CLASS, "Leantime\Domain\Sprints\Models\Sprints");

            $stmn->closeCursor();

            return $value;
        }

        /**
         * getCurrentSprintId - get current sprint for a project
         */
        public function getCurrentSprint($projectId): mixed
        {

            $query = 'SELECT
					zp_sprints.id,
					zp_sprints.name,
					zp_sprints.projectId,
					zp_sprints.startDate,
					zp_sprints.endDate,
					zp_sprints.modified
				FROM zp_sprints
				WHERE zp_sprints.projectId = :id
				AND zp_sprints.startDate < NOW() AND zp_sprints.endDate > NOW() ORDER BY zp_sprints.startDate  LIMIT 1';

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $projectId, PDO::PARAM_INT);
            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "Leantime\Domain\Sprints\Models\Sprints");

            $value = $stmn->fetch();

            $stmn->closeCursor();

            return $value;
        }

        /**
         * getUpcomingSprint - gets the next upcoming sprint
         */
        public function getUpcomingSprint($projectId): array
        {

            $query = 'SELECT
					zp_sprints.id,
					zp_sprints.name,
					zp_sprints.projectId,
					zp_sprints.startDate,
					zp_sprints.endDate,
					zp_sprints.modified
				FROM zp_sprints
				WHERE zp_sprints.projectId = :id
				AND zp_sprints.startDate > NOW() ORDER BY zp_sprints.startDate ASC LIMIT 1';

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $projectId, PDO::PARAM_INT);
            $stmn->execute();
            $stmn->setFetchMode(PDO::FETCH_CLASS, "Leantime\Domain\Sprints\Models\Sprints");

            $value = $stmn->fetch();

            $stmn->closeCursor();

            return $value;
        }

        public function addSprint($sprint): bool|int
        {

            $query = 'INSERT INTO zp_sprints (name, projectId, startDate, endDate, modified) VALUES (:name, :projectId, :startDate, :endDate, NOW())';

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':name', $sprint->name, PDO::PARAM_STR);
            $stmn->bindValue(':projectId', $sprint->projectId, PDO::PARAM_STR);
            $stmn->bindValue(':startDate', $sprint->startDate, PDO::PARAM_STR);
            $stmn->bindValue(':endDate', $sprint->endDate, PDO::PARAM_STR);

            $execution = $stmn->execute();
            $id = $this->db->database->lastInsertId();
            $stmn->closeCursor();

            return $execution ? $id : false;
        }

        public function editSprint($sprint): bool
        {

            $query = 'UPDATE zp_sprints
                      SET
                        name = :name,
                        projectId = :projectId,
                        startDate = :startDate,
                        endDate = :endDate,
                        modified = NOW()
                        WHERE id = :id';

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

        public function delSprint($id): void
        {

            $query = "UPDATE zp_tickets
                SET
                    sprint = ''
                WHERE sprint = :id";

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();

            $query = 'DELETE FROM zp_sprints WHERE id = :id';

            $stmn = $this->db->database->prepare($query);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->execute();
        }
    }

}
