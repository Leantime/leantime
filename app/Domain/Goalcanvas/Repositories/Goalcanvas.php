<?php

/**
 * Repository
 */

namespace Leantime\Domain\Goalcanvas\Repositories {

    use Leantime\Domain\Canvas\Repositories\Canvas;
    use PDO;

    /**
     *
     */
    class Goalcanvas extends Canvas
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = 'goal';

        /***
         * icon - Icon associated with canvas (must be extended)
         *
         * @access public
         * @var    string Fontawesome icone
         */
        protected string $icon = 'fa-bullseye';

        /**
         * canvasTypes - Must be extended
         *
         * @acces protected
         * @var   array
         */
        protected array $canvasTypes = [
            'goal' =>     ['icon' => 'fa-bullseye', 'title' => 'box.goal'],
        ];

        /**
         * statusLabels - Status labels (may be extended)
         *
         * @acces protected
         * @var   array
         */
        protected array $statusLabels = [
            'status_ontrack' => ['icon' => 'fa-circle-check', 'color' => 'green',       'title' => 'status.goal.ontrack', 'dropdown' => 'success',    'active' => true],
            'status_atrisk' => ['icon' => 'fa-triangle-exclamation', 'color' => 'yellow',       'title' => 'status.goal.atrisk', 'dropdown' => 'warning',    'active' => true],
            'status_miss' => ['icon' => 'fa-circle-xmark', 'color' => 'red',       'title' => 'status.goal.miss', 'dropdown' => 'danger',    'active' => true],

        ];


        protected array $relatesLabels = [];


        /**
         * dataLabels - Data labels (may be extended)
         *
         * @acces protected
         * @var   array
         */
        protected array $dataLabels = [
            1 => ['title' => 'label.what_are_you_measuring', 'field' => 'assumptions',  'type' => 'string', 'active' => true],
            2 => ['title' => 'label.current_value', 'field' => 'data', 'type' => 'int', 'active' => true],
            3 => ['title' => 'label.goal_value', 'field' => 'conclusion', 'type' => 'int', 'active' => true],

        ];


        /**
         * Gets all goals related to a milestone
         *
         * @param $milestoneId
         * @return array|false
         */
        public function getGoalsByMilestone(int $milestoneId): false|array
        {
            $sql = "SELECT
                        zp_canvas_items.id,
                        zp_canvas_items.description,
                        zp_canvas_items.title,
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
                        zp_canvas_items.relates,
                        zp_canvas_items.milestoneId,
                        zp_canvas_items.kpi,
                        zp_canvas_items.data1,
                        zp_canvas_items.data2,
                        zp_canvas_items.data3,
                        zp_canvas_items.data4,
                        zp_canvas_items.data5,
                        zp_canvas_items.startDate,
                        zp_canvas_items.endDate,
                        zp_canvas_items.setting,
                        zp_canvas_items.metricType,
                        zp_canvas_items.startValue,
                        zp_canvas_items.currentValue,
                        zp_canvas_items.endValue,
                        zp_canvas_items.impact,
                        zp_canvas_items.effort,
                        zp_canvas_items.probability,
                        zp_canvas_items.action,
                        zp_canvas_items.assignedTo,
                        zp_canvas_items.parent,
                        zp_canvas_items.tags
                FROM
                zp_canvas_items

                WHERE zp_canvas_items.box = 'goal' AND zp_canvas_items.milestoneId = :id

                ";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $milestoneId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();
            return $values;
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
                        zp_canvas.description,
                        zp_canvas.created,
                        zp_canvas.projectId,
                        t1.firstname AS authorFirstname,
                        t1.lastname AS authorLastname

                FROM
                zp_canvas
                LEFT JOIN zp_user AS t1 ON zp_canvas.author = t1.id
                WHERE type = '" . static::CANVAS_NAME . "canvas' AND zp_canvas.id = :canvasId
                ORDER BY zp_canvas.title, zp_canvas.created";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':canvasId', $canvasId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * Gets all goals related to a milestone
         *
         * @return array|false
         */
        public function getAllAccountGoals(?int $projectId, ?int $boardId): false|array
        {
            $sql = "SELECT
                    zp_canvas_items.id,
                    zp_canvas_items.description,
                    zp_canvas_items.title,
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
                    zp_canvas_items.relates,
                    zp_canvas_items.milestoneId,
                    zp_canvas_items.kpi,
                    zp_canvas_items.data1,
                    zp_canvas_items.data2,
                    zp_canvas_items.data3,
                    zp_canvas_items.data4,
                    zp_canvas_items.data5,
                    zp_canvas_items.startDate,
                    zp_canvas_items.endDate,
                    zp_canvas_items.setting,
                    zp_canvas_items.metricType,
                    zp_canvas_items.startValue,
                    zp_canvas_items.currentValue,
                    zp_canvas_items.endValue,
                    zp_canvas_items.impact,
                    zp_canvas_items.effort,
                    zp_canvas_items.probability,
                    zp_canvas_items.action,
                    zp_canvas_items.assignedTo,
                    zp_canvas_items.parent,
                    zp_canvas_items.tags,
                    zp_canvas.projectId
            FROM
            zp_canvas_items
            LEFT JOIN zp_canvas ON zp_canvas_items.canvasId = zp_canvas.id
            LEFT JOIN zp_projects ON zp_canvas.projectId = zp_projects.id
            WHERE zp_canvas_items.box = 'goal' AND (
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

            if (session()->exists("userdata")) {
                $stmn->bindValue(':requesterRole', session("userdata.role"), PDO::PARAM_INT);
            } else {
                $stmn->bindValue(':requesterRole', -1, PDO::PARAM_INT);
            }

            $stmn->bindValue(':clientId', session("userdata.clientId") ?? '-1', PDO::PARAM_INT);
            $stmn->bindValue(':userId', session("userdata.id") ?? '-1', PDO::PARAM_INT);

            if (isset($projectId) && $projectId  > 0) {
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_INT);
            }

            if (isset($boardId) && $boardId  > 0) {
                $stmn->bindValue(':boardId', $boardId, PDO::PARAM_INT);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();
            return $values;
        }

        /**
         * @param $values
         * @return false|string
         */
        public function createGoal($values): false|string
        {

            $query = "INSERT INTO zp_canvas_items (
                        description,
                            title,
                        assumptions,
                        data,
                        conclusion,
                        box,
                        author,
                        created,
                        modified,
                        canvasId,
                        status,
                        relates,
                        milestoneId,
                        kpi,
                        data1,
                        startDate,
                        endDate,
                        setting,
                        metricType,
                        impact,
                        effort,
                        probability,
                        action,
                        assignedTo,
                        startValue,
                        currentValue,
                        endValue,
                            parent,
                            tags
                ) VALUES (
                        :description,
                        :title,
                        :assumptions,
                        :data,
                        :conclusion,
                        :box,
                        :author,
                        NOW(),
                        NOW(),
                        :canvasId,
                        :status,
                        :relates,
                        :milestoneId,
                        :kpi,
                        :data1,
                        :startDate,
                        :endDate,
                        :setting,
                        :metricType,
                        :impact,
                        :effort,
                        :probability,
                        :action,
                        :assignedTo,
                        :startValue,
                        :currentValue,
                        :endValue,
                        :parent,
                        :tags
                )";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':description', $values['description'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':title', $values['title'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':assumptions', $values['assumptions'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':data', $values['data'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':conclusion', $values['conclusion'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':box', $values['box'], PDO::PARAM_STR);
            $stmn->bindValue(':author', $values['author'], PDO::PARAM_INT);
            $stmn->bindValue(':canvasId', $values['canvasId'], PDO::PARAM_INT);
            $stmn->bindValue(':status', $values['status'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':relates', $values['relates'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':milestoneId', $values['milestoneId'] ?? "", PDO::PARAM_STR);
            $stmn->bindValue(':kpi', $values['kpi'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':data1', $values['data1'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':startDate', $values['startDate'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':endDate', $values['endDate'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':setting', $values['setting'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':metricType', $values['metricType'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':impact', $values['impact'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':effort', $values['effort'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':probability', $values['probability'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':action', $values['action'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':assignedTo', $values['assignedTo'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':startValue', $values['startValue'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':currentValue', $values['currentValue'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':endValue', $values['endValue'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':parent', $values['parent'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':tags', $values['tags'] ?? '', PDO::PARAM_STR);

            $stmn->execute();
            $id = $this->db->database->lastInsertId();
            $stmn->closeCursor();

            return $id;
        }

    }
}
