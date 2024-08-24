<?php

/**
 * canvas class - Generic / Tempalate of canvas repository class
 */

namespace Leantime\Domain\Canvas\Repositories {

    use Leantime\Core\Db\Db as DbCore;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Domain\Tickets\Repositories\Tickets;
    use PDO;

    /**
     *
     */
    class Canvas
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = '??';

        /***
         * icon - Icon associated with canvas (must be extended)
         *
         * @access protected
         * @var    string Fontawesome icone
         */
        protected string $icon = 'fa-x';

        /***
         * disclaimer - Disclaimer (may be extended)
         *
         * @access protected
         * @var    string Disclaimer (including href)
         */
        protected string $disclaimer = '';

        /**
         * canvasTypes - Canvas elements / boxes (must be extended)
         *
         * @acces protected
         * @var   array
         */
        protected array $canvasTypes = [
            // '??_' => [ 'icon' => 'fa-????', 'title' => 'box.??.????' ],
        ];

        /**
         * statusLabels - Status labels (may be extended)
         *
         * @acces protected
         * @var   array
         */
        protected array $statusLabels = [
            'status_draft' =>   ['icon' => 'fa-circle-question',    'color' => 'blue',   'title' => 'status.draft',  'dropdown' => 'info',    'active' => true],
            'status_review' =>  ['icon' => 'fa-circle-exclamation', 'color' => 'orange', 'title' => 'status.review', 'dropdown' => 'warning', 'active' => true],
            'status_valid' =>   ['icon' => 'fa-circle-check',       'color' => 'green',  'title' => 'status.valid',  'dropdown' => 'success', 'active' => true],
            'status_hold' =>    ['icon' => 'fa-circle-h',           'color' => 'red',    'title' => 'status.hold',   'dropdown' => 'danger',  'active' => true],
            'status_invalid' => ['icon' => 'fa-circle-xmark',       'color' => 'red',    'title' => 'status.invalid','dropdown' => 'danger',  'active' => true],
        ];

        /**
         * relatesLabels - Relates to label (same structure as `statusLabels`)
         *
         * @acces public
         * @var   array
         */
        protected array $relatesLabels = [
            'relates_none'        => ['icon' => 'fa-border-none', 'color' => 'grey',      'title' => 'relates.none',         'dropdown' => 'default', 'active' => true],
            'relates_customers'   => ['icon' => 'fa-users',       'color' => 'green',     'title' => 'relates.customers',    'dropdown' => 'success', 'active' => true],
            'relates_offerings'   => ['icon' => 'fa-barcode',     'color' => 'red',       'title' => 'relates.offerings',    'dropdown' => 'danger',  'active' => true],
            'relates_capabilities' => ['icon' => 'fa-pen-ruler',   'color' => 'blue',      'title' => 'relates.capabilities', 'dropdown' => 'info',    'active' => true],
            'relates_financials'  => ['icon' => 'fa-money-bill',  'color' => 'yellow',    'title' => 'relates.financials',   'dropdown' => 'warning', 'active' => true],
            'relates_markets'     => ['icon' => 'fa-shop',        'color' => 'brown',     'title' => 'relates.markets',      'dropdown' => 'default', 'active' => true],
            'relates_environment' => ['icon' => 'fa-tree',        'color' => 'darkgreen', 'title' => 'relates.environment',  'dropdown' => 'default', 'active' => true],
            'relates_firm'        => ['icon' => 'fa-building',    'color' => 'darkblue',  'title' => 'relates.firm',         'dropdown' => 'info',    'active' => true],
        ];

        /**
         * dataLabels - Data labels (may be extended)
         *
         * @acces protected
         * @var   array
         */
        protected array $dataLabels = [
            1 => ['title' => 'label.assumptions', 'field' => 'assumptions', 'active' => true],
            2 => ['title' => 'label.data',        'field' => 'data',        'active' => true],
            3 => ['title' => 'label.conclusion',  'field' => 'conclusion',  'active' => true],
        ];

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
        protected ?DbCore $db = null;


        private LanguageCore $language;

        private Tickets $ticketRepo;

        /**
         * __construct - get db connection
         *
         * @access public
         * @return void
         */
        public function __construct(
            DbCore $db,
            LanguageCore $language,
            Tickets $ticketRepo
        ) {
            $this->db = $db;
            $this->language = $language;
            $this->ticketRepo = $ticketRepo;
        }

        /**
         * getIcon() - Retrieve canvas icon
         *
         * @access public
         * @return string Canvas icon
         */
        public function getIcon(): string
        {

            return $this->icon;
        }

        /**
         * getDisclaimer() - Retrieve disclaimer
         *
         * @access public
         * @return string Canvas disclaimer
         */
        public function getDisclaimer(): string
        {

            if (empty($this->disclaimer)) {
                return '';
            }
            return $this->language->__($this->disclaimer);
        }

        /**
         * getCanvasTypes() - Retrieve translated canvaas items
         *
         * @access public
         * @return array  Array of data
         */
        public function getCanvasTypes(): array
        {

            $canvasTypes = $this->canvasTypes;
            foreach ($canvasTypes as $key => $data) {
                if (isset($data['title'])) {
                    $canvasTypes[$key]['title'] = $this->language->__($data['title']);
                }
            }

            return $canvasTypes;
        }

        /**
         * getStatusLabels() - Retrieve translated status labels
         *
         * @access public
         * @return array  Array of data
         */
        public function getStatusLabels(): array
        {

            $statusLabels = $this->statusLabels;

            foreach ($statusLabels as $key => $data) {
                if (isset($data['title'])) {
                    $statusLabels[$key]['title'] = $this->language->__($data['title']);
                }
            }

            return $statusLabels;
        }

        /**
         * getRelatesLabels() - Retrieve translated relates labels
         *
         * @access public
         * @return array  Array of data
         */
        public function getRelatesLabels(): array
        {

            $relatesLabels = $this->relatesLabels;
            foreach ($relatesLabels as $key => $data) {
                if (isset($data['title'])) {
                    $relatesLabels[$key]['title'] = $this->language->__($data['title']);
                }
            }

            return $relatesLabels;
        }

        /**
         * getDataLabels() - Retrieve translated data labels
         *
         * @access public
         * @return array  Array of data
         */
        public function getDataLabels(): array
        {

            $dataLabels = $this->dataLabels;
            foreach ($dataLabels as $key => $data) {
                if (isset($data['title'])) {
                    $dataLabels[$key]['title'] = $this->language->__($data['title']);
                }
            }

            return $dataLabels;
        }


        /**
         * @param $projectId
         * @param $type
         * @return array|false
         */
        public function getAllCanvas($projectId, $type = null): false|array
        {

            if ($type == null || $type == '') {
                $canvasType = static::CANVAS_NAME . 'canvas';
            } else {
                $canvasType = $type;
            }

            $sql = "SELECT
                        zp_canvas.id,
                        zp_canvas.title,
                        zp_canvas.author,
                        zp_canvas.created,
                        zp_canvas.description,
                        t1.firstname AS authorFirstname,
                        t1.lastname AS authorLastname,
                        count(zp_canvas_items.id) AS boxItems
                FROM
                    zp_canvas
                    LEFT JOIN zp_user AS t1 ON zp_canvas.author = t1.id
                    LEFT JOIN zp_canvas_items ON zp_canvas.id = zp_canvas_items.canvasId
                WHERE type = :type AND projectId = :projectId
                GROUP BY
					zp_canvas.id, zp_canvas.title, zp_canvas.created
                ORDER BY zp_canvas.title, zp_canvas.created";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_STR);
            $stmn->bindValue(':type', $canvasType, PDO::PARAM_STR);

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
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $id
         * @return void
         */
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
         * @param $type
         * @return false|string
         */
        /**
         * @param $values
         * @param $type
         * @return false|string
         */
        public function addCanvas($values, $type = null): false|string
        {

            if ($type == null || $type == '') {
                $canvasType = static::CANVAS_NAME . 'canvas';
            } else {
                $canvasType = $type;
            }

            $query = "INSERT INTO zp_canvas (
                        title,
                       description,
                        author,
                        created,
                        type,
                        projectId
                ) VALUES (
                        :title,
                          :description,
                        :author,
                        NOW(),
                        :type,
                        :projectId
                )";

            $stmn = $this->db->database->prepare($query);

            $stmn->bindValue(':title', $values['title'], PDO::PARAM_STR);
            $stmn->bindValue(':author', $values['author'], PDO::PARAM_STR);
            $stmn->bindValue(':description', $values['description'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':projectId', $values['projectId'], PDO::PARAM_STR);
            $stmn->bindValue(':type', $canvasType, PDO::PARAM_STR);

            $stmn->execute();

            $stmn->closeCursor();

            return $this->db->database->lastInsertId();
        }

        /**
         * @param $values
         * @return mixed
         */
        /**
         * @param $values
         * @return mixed
         */
        public function updateCanvas($values): mixed
        {

            $query = "UPDATE zp_canvas SET
                        title = :title,
                        description = :description
                WHERE id = :id";

            $stmn = $this->db->{'database'}->prepare($query);

            $stmn->bindValue(':title', $values['title'], PDO::PARAM_STR);
            $stmn->bindValue(':description', $values['description'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':id', $values['id'], PDO::PARAM_INT);

            $result = $stmn->execute();

            $stmn->closeCursor();

            return $result;
        }

        /**
         * @param $values
         * @return void
         */
        /**
         * @param $values
         * @return void
         */
        public function editCanvasItem($values): void
        {
            $sql = "UPDATE zp_canvas_items SET
                           title = :title,
                    description = :description,
                    assumptions =        :assumptions,
                    data =            :data,
                    conclusion =            :conclusion,
                    modified =            NOW(),
                    status =            :status,
                    relates =            :relates,
                    milestoneId =            :milestoneId,
                    kpi =            :kpi,
                    data1 =            :data1,
                    startDate =            :startDate,
                    endDate =            :endDate,
                    setting =            :setting,
                    metricType =            :metricType,
                    startValue =            :startValue,
                    currentValue =            :currentValue,
                    endValue =            :endValue,
                    impact=            :impact,
                    effort=            :effort,
                    probability=            :probability,
                    action=            :action,
                    assignedTo=            :assignedTo,
                    parent = :parent,
                    tags = :tags

                    WHERE id = :id LIMIT 1    ";


            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':id', $values['itemId'], PDO::PARAM_STR);
            $stmn->bindValue(':title', $values['title'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':description', $values['description'], PDO::PARAM_STR);
            $stmn->bindValue(':assumptions', $values['assumptions'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':data', $values['data'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':conclusion', $values['conclusion'] ?? '', PDO::PARAM_STR);
            $stmn->bindValue(':status', $values['status'], PDO::PARAM_STR);
            $stmn->bindValue(':relates', $values['relates'], PDO::PARAM_STR);
            $stmn->bindValue(':milestoneId', $values['milestoneId'] ?? '', PDO::PARAM_STR);
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
            $stmn->closeCursor();
        }


        /**
         * @param $id
         * @param $params
         * @return bool
         */
        /**
         * @param $id
         * @param $params
         * @return bool
         */
        public function patchCanvasItem($id, $params): bool
        {

            $sql = "UPDATE zp_canvas_items SET";

            foreach ($params as $key => $value) {
                $sql .= " " . DbCore::sanitizeToColumnString($key) . "=:" . DbCore::sanitizeToColumnString($key) . ", ";
            }

            $sql .= "id=:id WHERE id=:whereId LIMIT 1";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);
            $stmn->bindValue(':whereId', $id, PDO::PARAM_STR);

            foreach ($params as $key => $value) {
                $stmn->bindValue(':' . DbCore::sanitizeToColumnString($key), $value, PDO::PARAM_STR);
            }

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
                        zp_canvas_items.status,
                        zp_canvas_items.relates,
                        zp_canvas_items.milestoneId,
                        zp_canvas_items.parent,
                        zp_canvas_items.title,
                        zp_canvas_items.tags,
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
                        t1.firstname AS authorFirstname,
                        t1.lastname AS authorLastname,
                        t1.profileId AS authorProfileId,
                        milestone.headline as milestoneHeadline,
                        milestone.editTo as milestoneEditTo,
                        COUNT(DISTINCT zp_comment.id) AS commentCount,
                        0 AS percentDone
                FROM
                zp_canvas_items

                LEFT JOIN zp_user AS t1 ON zp_canvas_items.author = t1.id
                LEFT JOIN zp_tickets AS milestone ON milestone.id = zp_canvas_items.milestoneId
                LEFT JOIN zp_comment ON zp_canvas_items.id = zp_comment.moduleId and zp_comment.module = '" . static::CANVAS_NAME . "canvasitem'
                WHERE zp_canvas_items.canvasId = :id
                GROUP BY zp_canvas_items.id, zp_canvas_items.box, zp_canvas_items.sortindex
                ORDER BY zp_canvas_items.box, zp_canvas_items.sortindex";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }


        /**
         * @param $id
         * @return array|false
         */
        public function getCanvasItemsByKPI($id): false|array
        {

            $sql = "SELECT
                        zp_canvas_items.id,

                        zp_canvas_items.title,
                        zp_canvas_items.kpi,
                        zp_canvas_items.startDate,
                        zp_canvas_items.endDate,
                        zp_canvas_items.setting,
                        zp_canvas_items.metricType,
                        zp_canvas_items.startValue,
                        zp_canvas_items.currentValue,
                        zp_canvas_items.endValue,
						zp_canvas_items.canvasId,
                        zp_canvas.title as boardTitle,
                        zp_canvas.projectId as projectId,
                        zp_projects.name as projectName,

                        childrenLvl1.id as childId,
                        childrenLvl1.title as childTitle,
                        childrenLvl1.kpi as childKpi,
                        childrenLvl1.startDate as childStartDate,
                        childrenLvl1.endDate as childEndDate,
                        childrenLvl1.setting as childSetting,
                        childrenLvl1.metricType as childMetricType,
                        childrenLvl1.startValue as childStartValue,
                        childrenLvl1.currentValue as childCurrentValue,
                        childrenLvl1.endValue as childEndValue,
                        childrenLvl1.canvasId as childCanvasId,
                        childrenLvl1Board.title as childBoardTitle,
                        childrenLvl1Project.name as childProjectName

                FROM
                zp_canvas_items
                LEFT JOIN zp_canvas ON zp_canvas_items.canvasId = zp_canvas.id
                LEFT JOIN zp_projects ON zp_canvas.projectId = zp_projects.id
                LEFT JOIN zp_canvas_items as childrenLvl1 ON childrenLvl1.kpi = zp_canvas_items.id
				LEFT JOIN zp_canvas as childrenLvl1Board ON childrenLvl1.canvasId = childrenLvl1Board.id
                LEFT JOIN zp_projects as childrenLvl1Project ON childrenLvl1Board.projectId = childrenLvl1Project.id
                WHERE zp_canvas_items.box = 'goal'


                AND zp_canvas_items.kpi = :id";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }

        /**
         * @param $projectId
         * @return array|false
         */
        public function getAllAvailableParents($projectId): false|array
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
                        board.title AS boardTitle,
                        parentKPI.description AS parentKPIDescription,
                        parentGoal.description AS parentGoalDescription,
                        t1.firstname AS authorFirstname,
                        t1.lastname AS authorLastname,
                        milestone.headline as milestoneHeadline,
                        milestone.editTo as milestoneEditTo

                FROM
                zp_canvas_items
                LEFT JOIN zp_canvas AS board ON board.id = zp_canvas_items.canvasId
                LEFT JOIN zp_canvas_items AS parentKPI ON zp_canvas_items.kpi = parentKPI.id
                LEFT JOIN zp_canvas_items AS parentGoal ON zp_canvas_items.parent = parentGoal.id
				LEFT JOIN zp_tickets AS milestone ON milestone.id = zp_canvas_items.milestoneId
                LEFT JOIN zp_user AS t1 ON zp_canvas_items.author = t1.id
                WHERE board.projectId = :id
                GROUP BY id, board.id
                ORDER BY board.id
                ";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $projectId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();
            return $values;
        }

        /**
         * @param $projectId
         * @return array|false
         */
        public function getAllAvailableKPIs($projectId): false|array
        {
            $sql = "SELECT
                        zp_canvas_items.id,
                        zp_canvas_items.description,
                        project.name as projectName,
                        zp_canvas.title AS boardTitle

                FROM zp_canvas_items
                LEFT JOIN zp_canvas ON zp_canvas.id = zp_canvas_items.canvasId
                LEFT JOIN zp_projects AS project ON zp_canvas.projectId = project.id
                WHERE

                    FIND_IN_SET(zp_canvas.projectId, (
                                    SELECT
                                        CONCAT(zp_projects.parent, ',', IF(parents.parent IS NOT NULL, parents.parent, 0)) AS parentslist
                                    FROM zp_projects
                                    LEFT JOIN zp_projects as parents ON zp_projects.parent = parents.id
                                    WHERE
                                        zp_projects.id = :id AND
                                        (project.type = 'strategy' OR project.type = 'program')
                                    )
                            )
                    AND (zp_canvas_items.setting = 'linkAndReport' OR zp_canvas_items.setting = 'linkonly')
                ORDER BY zp_canvas.id
                ";

            // programs


            // project
            //boards
            //goals


            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $projectId, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();
            return $values;
        }


        /**
         * @param $id
         * @return false|mixed
         */
        public function getSingleCanvasItem($id): mixed
        {

            $statusGroups = $this->ticketRepo->getStatusListGroupedByType(session("currentProject"));

            $sql = "SELECT
                        zp_canvas_items.id,
                        zp_canvas_items.title,
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
                        board.title as boardTitle,
                        parentKPI.description AS parentKPIDescription,
                        parentGoal.title AS parentGoalDescription,
                        t1.firstname AS authorFirstname,
                        t1.lastname AS authorLastname,
                        milestone.headline as milestoneHeadline,
                        milestone.editTo as milestoneEditTo,
                        COUNT(progressTickets.id) AS allTickets,
                        (SELECT (
                            CASE WHEN
                              COUNT(DISTINCT progressSub.id) > 0
                            THEN
                              ROUND(
                                (
                                  SUM(CASE WHEN progressSub.status " . $statusGroups["DONE"] . " THEN IF(progressSub.storypoints = 0, 3, progressSub.storypoints) ELSE 0 END) /
                                  SUM(IF(progressSub.storypoints = 0, 3, progressSub.storypoints))
                                ) *100)
                            ELSE
                              0
                            END) AS percentDone
                        FROM zp_tickets AS progressSub WHERE progressSub.milestoneid = zp_canvas_items.milestoneId AND progressSub.type <> 'milestone') AS percentDone

                FROM
                zp_canvas_items
                LEFT JOIN zp_canvas_items AS parentKPI ON zp_canvas_items.kpi = parentKPI.id
                LEFT JOIN zp_canvas AS board ON board.id = zp_canvas_items.canvasId
                LEFT JOIN zp_canvas_items AS parentGoal ON zp_canvas_items.parent = parentGoal.id
                LEFT JOIN zp_tickets AS progressTickets ON progressTickets.milestoneid = zp_canvas_items.milestoneId AND progressTickets.type <> 'milestone' AND progressTickets.type <> 'subtask'
                LEFT JOIN zp_tickets AS milestone ON milestone.id = zp_canvas_items.milestoneId
                LEFT JOIN zp_user AS t1 ON zp_canvas_items.author = t1.id
                WHERE zp_canvas_items.id = :id
                ";

            $stmn = $this->db->database->prepare($sql);
            $stmn->bindValue(':id', $id, PDO::PARAM_STR);

            $stmn->execute();
            $values = $stmn->fetch();
            $stmn->closeCursor();
            if ($values !== false && $values['id'] != null) {
                return $values;
            } else {
                return false;
            }
        }

        /**
         * @param $values
         * @return false|string
         */
        public function addCanvasItem($values): false|string
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


        /**
         * @param $id
         * @return void
         */
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
         * @param $projectId
         * @return int|mixed
         */
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
                WHERE canvasBoard.type = '" . static::CANVAS_NAME . "canvas'  ";

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

        /**
         * @param $projectId
         * @return int|mixed
         */
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
                WHERE zp_canvas.type = '" . static::CANVAS_NAME . "canvas' ";

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
         * existCanvas - return if a canvas exists with a given title in the specified project
         *
         * @access public
         * @param int    $projectId   Project identifier
         * @param  string $canvasTitle Canvas title
         * @return bool   True if canvas exists
         */
        public function existCanvas(int $projectId, string $canvasTitle): bool
        {

            $sql = "SELECT COUNT(id) as nbCanvas " .
                "FROM zp_canvas " .
                "WHERE projectId = :projectId AND title = :canvasTitle AND type = '" . static::CANVAS_NAME . "canvas'";
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
         * @param int    $projectId   Project identifier
         * @param int    $canvasId    Original canvas identifier
         * @param int    $authorId    Author identifier
         * @param  string $canvasTitle New canvas title
         * @return int    Identifier of new Canvas
         */
        public function copyCanvas(int $projectId, int $canvasId, int $authorId, string $canvasTitle): int
        {

            // Create new Canvas
            $values = ["title" => $canvasTitle, "author" => $authorId, "projectId" => $projectId, "type" => static::CANVAS_NAME . "canvas"];
            $newCanvasId = $this->addCanvas($values);

            // Copy elements from existing canvas to new Canvas
            $sql = "INSERT INTO " .
              "zp_canvas_items (title,description,assumptions,data,conclusion,box,author,created,modified,canvasId,status,relates,milestoneId,kpi,
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
                        endValue,title) " .
              "SELECT title,description, assumptions, data, conclusion, box, author, NOW(), NOW(), $newCanvasId, status, relates, '',kpi,
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
                        endValue,title " .
              "FROM zp_canvas_items WHERE canvasId = $canvasId";
            $stmn = $this->db->database->prepare($sql);

            $stmn->execute();
            $stmn->closeCursor();

            return $newCanvasId;
        }

        /***
         * mergeCanvas - merge canvas into existing canvas
         *
         * @access public
         * @param int    $canvasId Original canvas identifier
         * @param string $mergeId  Canvas to perge into existing one
         * @return bool Status of merge
         */
        public function mergeCanvas(int $canvasId, string $mergeId): bool
        {

            // Copy elements from merge canvas into current canvas
            $sql = "INSERT INTO " .
              "zp_canvas_items (title,description,assumptions,data,conclusion,box,author,created,modified,canvasId,status,relates,milestoneId,kpi,
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
                        endValue, title) " .
              "SELECT title,description, assumptions, data, conclusion, box, author, NOW(), NOW(), $canvasId, status, relates, '',kpi,
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
                        endValue, title " .
              "FROM zp_canvas_items WHERE canvasId = $mergeId";
            $stmn = $this->db->database->prepare($sql);

            $stmn->execute();
            $stmn->closeCursor();

            return true;
        }

        /***
         * getCanvasProgressCount - gets canvases by type and counts number of items per box
         *
         * @access public
         * @param int   $projectId Project od
         * @param  array $boards    List of board types to pull
         * @return array|bool list of boards or false
         */
        public function getCanvasProgressCount(int $projectId, array $boards): array|bool
        {

            $sql = "SELECT
                    zp_canvas.id AS canvasId,
                    zp_canvas.type AS canvasType,
                    zp_canvas_items.box,
                    count(zp_canvas_items.id) AS boxItems
                FROM
                    zp_canvas
                    LEFT JOIN zp_canvas_items ON zp_canvas.id = zp_canvas_items.canvasId

                ";

            if ($projectId != '' && count($boards) > 0) {
                $sql .= " WHERE 1=1 ";
            }

            if ($projectId != '') {
                $sql .= "AND projectId = :projectId ";
            }

            if (count($boards) > 0) {
                $boardString = implode("','", $boards);
                $sql .= "AND type IN ('" . $boardString . "') ";
            }



            $sql .= "
                GROUP BY
                    zp_canvas.id,
                    zp_canvas.type,
                    zp_canvas_items.box
                ORDER BY zp_canvas.title, zp_canvas.created";

            $stmn = $this->db->database->prepare($sql);

            if ($projectId != '') {
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_STR);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }


        /***
         * getLastUpdateCanvas - gets the list of canvas that have been updated recently
         *
         * @access public
         * @param int   $projectId Project od
         * @param  array $boards    List of board types to pull
         * @return array    array of canvas boards sorted by last update date
         */
        public function getLastUpdatedCanvas(int $projectId, array $boards): array
        {

            $sql = "SELECT
                    zp_canvas.id AS id,
                    zp_canvas.type AS type,
                    zp_canvas.title AS title,
                    IF(MAX(zp_canvas_items.modified) IS NULL, zp_canvas.created, MAX(zp_canvas_items.modified)) AS modified
                FROM
                    zp_canvas
                    LEFT JOIN zp_canvas_items ON zp_canvas.id = zp_canvas_items.canvasId

                ";

            if ($projectId != '' && count($boards) > 0) {
                $sql .= " WHERE 1=1 ";
            }

            if ($projectId != '') {
                $sql .= "AND projectId = :projectId ";
            }

            if (count($boards) > 0) {
                $boardString = implode("','", $boards);
                $sql .= "AND type IN ('" . $boardString . "') ";
            }

            $sql .= "
                GROUP BY
                    zp_canvas.id,
                    zp_canvas.type,
                     zp_canvas.title
                ORDER BY modified DESC";

            $stmn = $this->db->database->prepare($sql);

            if ($projectId != '') {
                $stmn->bindValue(':projectId', $projectId, PDO::PARAM_STR);
            }

            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }



        /***
         * getTags - gets the list of tags across all canvas items in a given project
         *
         * @access public
         * @param int $projectId Project od
         * @return array    array of canvas boards sorted by last update date
         */
        public function getTags(int $projectId): array
        {

            $sql = "SELECT
                    zp_canvas_items.tags
                FROM
                    zp_canvas_items
                    LEFT JOIN zp_canvas ON zp_canvas.id = zp_canvas_items.canvasId
                    WHERE zp_canvas.projectId = :projectId
                ";

            $stmn = $this->db->database->prepare($sql);

            $stmn->bindValue(':projectId', $projectId, PDO::PARAM_STR);


            $stmn->execute();
            $values = $stmn->fetchAll();
            $stmn->closeCursor();

            return $values;
        }
    }
}
