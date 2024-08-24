<?php

/**
 * export class - Template - Export canvas as XML file
 */

namespace Leantime\Domain\Canvas\Controllers {

    use Exception;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Illuminate\Support\Str;
    use Leantime\Core\Configuration\Environment as EnvironmentCore;
    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Language as LanguageCore;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Symfony\Component\HttpFoundation\Response;

    /**
     * Template class For exporting class as XML file
     */
    class Export extends Controller
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = '??';
        protected const CANVAS_TYPE = 'canvas';


        // Internal variables
        protected EnvironmentCore $config;
        protected LanguageCore $language;
        protected mixed $canvasRepo;
        protected array $canvasTypes;
        protected array $statusLabels;
        protected array $relatesLabels;
        protected array $dataLabels;


        /***
         * Constructor
         */
        public function init(
            EnvironmentCore $config,
            LanguageCore $language,
        ) {

            $this->config = $config;
            $this->language = $language;
            $canvasName = Str::studly(static::CANVAS_NAME) . static::CANVAS_TYPE;
            $repoName = app()->getNamespace() . "Domain\\$canvasName\\Repositories\\$canvasName";
            $this->canvasRepo = app()->make($repoName);

            $this->canvasTypes = $this->canvasRepo->getCanvasTypes();
            $this->statusLabels = $this->canvasRepo->getStatusLabels();
            $this->relatesLabels = $this->canvasRepo->getRelatesLabels();
            $this->dataLabels = $this->canvasRepo->getDataLabels();
        }

        /**
         * run - Generate XML file
         */
        public function run(): Response
        {
            // Retrieve id of canvas to print
            if (isset($_GET['id']) === true) {
                $canvasId = (int)$_GET['id'];
            } elseif (session()->exists("current" . strtoupper(static::CANVAS_NAME) . "Canvas")) {
                $canvasId = session("current" . strtoupper(static::CANVAS_NAME) . "Canvas");
            } else {
                return new Response();
            }

            // Generate XML code
            $exportData = $this->export($canvasId);

            // Service report
            clearstatcache();
            $response = new Response($exportData);
            $response->headers->set('Content-type', 'application/xml');
            $response->headers->set('Content-Disposition', 'attachment; filename="' . static::CANVAS_NAME . static::CANVAS_TYPE . '-' . $canvasId . '.xml"');
            $response->headers->set('Cache-Control', 'no-cache');

            return $response;
        }

        /***
         * export - Generate XML file
         *
         * @access protected
         * @param int $id Canvas identifier
         * @return string XML data
         * @throws BindingResolutionException
         */
        protected function export(int $id): string
        {

            // Retrieve canvas data
            $canvasAry = $this->canvasRepo->getSingleCanvas($id);
            !empty($canvasAry) || throw new Exception("Cannot find canvas with id '$id'");
            $projectId = $canvasAry[0]['projectId'];
            $recordsAry = $this->canvasRepo->getCanvasItemsById($id);
            $projectsRepo = app()->make(ProjectRepository::class);
            $projectAry = $projectsRepo->getProject($projectId);
            !empty($projectAry) || throw new Exception("Cannot retrieve project id '$projectId'");

            // Generate XML data
            $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>' . PHP_EOL . PHP_EOL;
            $xml .= $this->xmlExport(static::CANVAS_NAME . static::CANVAS_TYPE, $canvasAry[0]['title'], $recordsAry);
            return $xml;
        }

        /**
         * xmlExport - Generate XML for specific data
         *
         * @access protected
         * @param string $canvasKey   Encoded canvas name
         * @param string $canvasTitle
         * @param array  $recordsAry  Array of canvas entry records
         * @param int    $indent      Indent level to use;
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
                        $xml .= $is . $tab . $tab . $tab . $tab . '<created>' . ($record['created'] ?? '') . '</created>' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<modified>' . ($record['modified'] ?? '') . '</modified>' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<author id="' . $record['author'] . '" firstname="' . ($record['authorFirstname'] ?? '') . '" ' .
                             'lastname="' . ($record['authorLastname'] ?? '') . '"/>' . PHP_EOL;

                        $xml .= $is . $tab . $tab . $tab . $tab . '<description>' . ($record['description'] ?? '') . '</description>' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<status key="' . ($record['status'] ?? '') . '" />' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<relates key="' . ($record['relates'] ?? '') . '" />' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<assumptions>' . ($record['assumptions'] ?? '') . '</assumptions>' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<data>' . ($record['data'] ?? '') . '</data>' . PHP_EOL;
                        $xml .= $is . $tab . $tab . $tab . $tab . '<conclusion>' . ($record['conclusion'] ?? '') . '</conclusion>' . PHP_EOL;
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
