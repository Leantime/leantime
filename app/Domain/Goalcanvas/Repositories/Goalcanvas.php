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
        public function getAllAccountGoals(): false|array
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

            WHERE zp_canvas_items.box = 'goal'

            ";

            $stmn = $this->db->database->prepare($sql);
            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();
            return $values;
        }
    }
}
