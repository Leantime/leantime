<?php

/**
 * export class - Template - Export canvas as XML file
 */

namespace leantime\domain\controllers\canvas {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\repositories;

    /**
     * Template class for exporting class as XML file
     */
    class export extends controller
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = '??';
        protected const CANVAS_TYPE = 'canvas';


        // Internal variables
        protected core\config $config;
        protected core\language $language;
        protected $canvasRepo;
        protected array $canvasTypes;
        protected array $statusLabels;
        protected array $relatesLabels;
        protected array $dataLabels;


        /***
         * Constructor
         */
        public function init()
        {

            $this->config = \leantime\core\environment::getInstance();
            $this->language = core\language::getInstance();
            $canvasRepoName = "\\leantime\\domain\\repositories\\" . static::CANVAS_NAME . static::CANVAS_TYPE;
            $this->canvasRepo = new $canvasRepoName();

            $this->canvasTypes = $this->canvasRepo->getCanvasTypes();
            $this->statusLabels = $this->canvasRepo->getStatusLabels();
            $this->relatesLabels = $this->canvasRepo->getRelatesLabels();
            $this->dataLabels = $this->canvasRepo->getDataLabels();
        }

        /**
         * run - Generate XML file
         */
        public function run()
        {

            // Retrieve id of canvas to print
            if (isset($_GET['id']) === true) {
                $canvasId = (int)$_GET['id'];
            } elseif (isset($_SESSION['current' . strtoupper(static::CANVAS_NAME) . 'Canvas'])) {
                $canvasId = $_SESSION['current' . strtoupper(static::CANVAS_NAME) . 'Canvas'];
            } else {
                return;
            }

            // Generate XML code
            $exportData = $this->export($canvasId);

            // Service report
            clearstatcache();
            header("Content-type: application/xml");
            header('Content-Disposition: attachment; filename="' . static::CANVAS_NAME . static::CANVAS_TYPE . '-' . $canvasId . '.xml"');
            header('Cache-Control: no-cache');
            echo $exportData;
        }

        /***
         * export - Generate XML file
         *
         * @access protected
         * @param  int    $id Canvas identifier
         * @return string XML data
         */
        protected function export(int $id): string
        {

            // Retrieve canvas data
            $canvasAry = $this->canvasRepo->getSingleCanvas($id);
            !empty($canvasAry) || throw new \Exception("Cannot find canvas with id '$id'");
            $projectId = $canvasAry[0]['projectId'];
            $recordsAry = $this->canvasRepo->getCanvasItemsById($id);
            $projectsRepo = new repositories\projects();
            $projectAry = $projectsRepo->getProject($projectId);
            !empty($projectAry) || throw new \Exception("Cannot retrieve project id '$projectId'");

            // Generate XML data
            $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>' . PHP_EOL . PHP_EOL;
            $xml .= $this->xmlExport(static::CANVAS_NAME . static::CANVAS_TYPE, $canvasAry[0]['title'], $recordsAry);
            return $xml;
        }

        /**
         * xmlExport - Generate XML for specific data
         *
         * @access protected
         * @param  string $canvasKey    Encoded canvas name
         * @param  string $canvcasTitle Canvas title
         * @param  array  $recordsAry   Array of canvas entry records
         * @param  int    $indent       Indent level to use;
         * @return string XML data
         */
        protected function xmlExport(string $canvasKey, string $canvasTitle, array $recordsAry, int $indent = 0): string
        {
            $is = str_repeat(' ', 4 * $indent);
            $tab = str_repeat(' ', 4);
            $xml = $is . '<canvas key="' . $canvasKey . '">' . PHP_EOL;
            $xml .= $is . $tab . '<title>' . $canvasTitle . '</title>' . PHP_EOL;
            $xml .= $is . $tab . '<content>' . PHP_EOL;

            foreach ($this->canvasTypes as $key => $data) {
                $xml .= $is . $tab . $tab . '<element key="' . $key . '">' . PHP_EOL;

                foreach ($recordsAry as $record) {
                    if ($record['box'] === $key) {
                        $xml .= $is . $tab . $tab . $tab . '<item>' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<created>' . (isset($record['created']) ? $record['created'] : '') . '</created>' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<modified>' . (isset($record['modified']) ? $record['modified'] : '') . '</modified>' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<author id="' . $record['author'] . '" firstname="' . (isset($record['authorFirstname']) ? $record['authorFirstname'] : '') . '" ' .
                             'lastname="' . (isset($record['authorLastname']) ? $record['authorLastname'] : '') . '"/>' . PHP_EOL;

                        $xml .= $is . $tab . $tab . $tab . $tab . '<description>' . (isset($record['description']) ? $record['description'] : '') . '</description>' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<status key="' . (isset($record['status']) ? $record['status'] : '') . '" />' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<relates key="' . (isset($record['relates']) ? $record['relates'] : '') . '" />' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<assumptions>' . (isset($record['assumptions']) ? $record['assumptions'] : '') . '</assumptions>' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<data>' . (isset($record['data']) ? $record['data'] : '') . '</data>' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<conclusion>' . (isset($record['conclusion']) ? $record['conclusion'] : '') . '</conclusion>' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . '</item>' . PHP_EOL;
                    }
                }

                $xml .= $is . $tab . $tab . '</element>' . PHP_EOL;
            }
            $xml .= $is . $tab . '</content>' . PHP_EOL;
            $xml .= $is . '</canvas>' . PHP_EOL;

            return $xml;
        }
    }
}
