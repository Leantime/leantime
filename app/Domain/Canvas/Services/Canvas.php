<?php

namespace Leantime\Domain\Canvas\Services {

    use DOMDocument;
    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Domain\Users\Repositories\Users as UserRepository;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\App;

    /**
     *
     *
     * @api
     */
    class Canvas
    {
        /**
         * import - Import canvas from XML file
         *
         * @access public
         * @param string $filename   File to import
         * @param string $canvasName
         * @param int    $projectId  Project identifier
         * @param int    $authorId
         * @return bool|int False if import failed and the id of the newly created canvas otherwise
         * @throws BindingResolutionException
         *
     * @api
     */
        public function import(string $filename, string $canvasName, int $projectId, int $authorId): bool|int
        {

            $dom = new DOMDocument('1.0', 'UTF-8');
            $users = app()->make(UserRepository::class);

            // Read file
            $canvasData = file_get_contents($filename);
            if ($canvasData === false) {
                return false;
            }

            // Convert read data into XML DOM structure
            $old_error_reporting = error_reporting(error_reporting() & ~E_WARNING);
            $status = $dom->loadXML($canvasData);
            error_reporting($old_error_reporting);
            if ($status === false) {
                return false;
            }

            // Decode XMP data
            $canvasAry = ['projectId' => $projectId, 'author' => $authorId];
            $recordsAry = [];

            // - Canvas
            $canvasNodeList = $dom->getElementsByTagName('canvas');
            if ($canvasNodeList->count() !== 1) {
                return false;
            }
            $importedCanvasName = $canvasNodeList->item(0)->getAttribute('key');

            // - Canvas / Title
            $titleNodeList = $canvasNodeList->item(0)->getElementsByTagName('title');
            if ($titleNodeList->count() !== 1) {
                return false;
            }
            $canvasAry['title'] = $titleNodeList->item(0)->nodeValue;

            // - Canvas / Data
            $dataNodeList = $canvasNodeList->item(0)->getElementsByTagName('content');
            if ($dataNodeList->count() !== 1) {
                return false;
            }

            // - Data / Element*
            $elementNodeList = $dataNodeList->item(0)->getElementsByTagName('element');

            foreach ($elementNodeList as $elementNode) {
                if (!$elementNode->hasAttribute('key')) {
                    return false;
                }
                $elementKey = $elementNode->getAttribute('key');

                $itemNodeList = $elementNode->getElementsByTagName('item');
                foreach ($itemNodeList as $itemName) {
                    $authorNodeList = $itemName->getElementsByTagName('author');
                    if ($authorNodeList->count() !== 1) {
                        return false;
                    }
                    if (!$authorNodeList->item(0)->hasAttribute('firstname')) {
                        return false;
                    }
                    $authorFirstname = $authorNodeList->item(0)->getAttribute('firstname');
                    if (!$authorNodeList->item(0)->hasAttribute('lastname')) {
                        return false;
                    }
                    $authorLastname = $authorNodeList->item(0)->getAttribute('lastname');
                    $author = $users->getUserIdByName($authorFirstname, $authorLastname);
                    if ($author === false) {
                        $author = $authorId;
                    }

                    $descriptionNodeList = $itemName->getElementsByTagName('description');
                    if ($descriptionNodeList->count() !== 1) {
                        return false;
                    }
                    $description = $descriptionNodeList->item(0)->nodeValue;

                    $statusNodeList = $itemName->getElementsByTagName('status');
                    if ($statusNodeList->count() !== 1) {
                        return false;
                    }
                    if (!$statusNodeList->item(0)->hasAttribute('key')) {
                        return false;
                    }
                    $status = $statusNodeList->item(0)->getAttribute('key');

                    $relatesNodeList = $itemName->getElementsByTagName('relates');
                    if ($relatesNodeList->count() !== 1) {
                        return false;
                    }
                    if (!$relatesNodeList->item(0)->hasAttribute('key')) {
                        return false;
                    }
                    $relates = $relatesNodeList->item(0)->getAttribute('key');

                    $assumptionsNodeList = $itemName->getElementsByTagName('assumptions');
                    if ($assumptionsNodeList->count() !== 1) {
                        return false;
                    }
                    $assumptions = empty($assumptionsNodeList->item(0)->nodeValue) ? '' :
                        $dom->saveHTML($assumptionsNodeList->item(0)->firstChild);

                    $dataNodeList = $itemName->getElementsByTagName('data');
                    if ($dataNodeList->count() !== 1) {
                        return false;
                    }
                    $data = empty($dataNodeList->item(0)->nodeValue) ? '' :
                        $dom->saveHTML($dataNodeList->item(0)->firstChild);

                    $conclusionNodeList = $itemName->getElementsByTagName('conclusion');
                    if ($conclusionNodeList->count() !== 1) {
                        return false;
                    }
                    $conclusion = empty($conclusionNodeList->item(0)->nodeValue) ? '' :
                        $dom->saveHTML($conclusionNodeList->item(0)->firstChild);

                    $recordsAry[] = [
                    'description' => $description,
                                      'assumptions' => $assumptions,
                                      'data' => $data,
                                      'conclusion' => $conclusion,
                                      'box' => $elementKey,
                                      'author' => $author,
                                      'status' => $status,
                                      'relates' => $relates,
                                      'milestoneId' => '',
                    ];
                }
            }

            // Check if canvas is consistently named
            if ($canvasName !== $importedCanvasName) {
                return false;
            }

            $canvasName = Str::studly($canvasName);

            $canvasRepoName = app()->getNamespace() . "Domain\\$canvasName\\Repositories\\$canvasName";
            $canvasRepo = app($canvasRepoName);

            // Check if canvas already exists?
            $canvasAry['title'] .= ' [imported]';
            if ($canvasRepo->existCanvas($projectId, $canvasAry['title'])) {
                return false;
            }

            // Save new canvas and return id
            $canvasId = $canvasRepo->addCanvas($canvasAry);
            if ($canvasId === false) {
                return false;
            }

            foreach ($recordsAry as $record) {
                $record['canvasId'] = $canvasId;
                $canvasRepo->addCanvasItem($record);
            }

            return $canvasId;
        }

        /**
         * getBoardProgress - gets the progress of canvas types. counts items in each box-type and calculates percent done if each box type has at least 1 item.
         *
         * @access public
         * @param string $projectId projectId (optional)
         * @param array  $boards    Array of project board types
         * @return array List of boards with a progress percentage
         * @throws BindingResolutionException
         *
     * @api
     */
        public function getBoardProgress(string $projectId = '', array $boards = array()): array
        {

            $canvasRepo = app()->make(\Leantime\Domain\Canvas\Repositories\Canvas::class);
            $values = $canvasRepo->getCanvasProgressCount($projectId, $boards);

            $results = array();

            foreach ($values as $row) {
                if (!isset($results[$row['canvasType']])) {
                    $results[$row['canvasType']] = array();
                }

                if (!isset($results[$row['canvasType']][$row['canvasId']])) {
                    $repoName = Str::studly($row['canvasType']);
                    $classname = app()->getNamespace() . "Domain\\$repoName\\Repositories\\$repoName";

                    $canvasTypeRepo = app()->make($classname);
                    $results[$row['canvasType']][$row['canvasId']] = array();

                    foreach ($canvasTypeRepo->getCanvasTypes() as $type => $box) {
                        $results[$row['canvasType']][$row['canvasId']][$type] = 0;
                    }
                }

                if ($row['box'] != '' && $row['boxItems'] > 0) {
                    $results[$row['canvasType']][$row['canvasId']][$row['box']]++;
                }
            }

            $progressResults = array();

            //Once the count is done calculate progress per canvastype Id
            foreach ($results as $key => &$canvas) {
                $repoName = Str::studly($key);
                $classname = app()->getNamespace() . "Domain\\$key\\Repositories\\$key";
                $canvasTypeRepo = app()->make($classname);

                $numOfBoxes = count($canvasTypeRepo->getCanvasTypes());

                if (!isset($progressResults[$key])) {
                    $progressResults[$key] = '';
                }

                $maxProgress = 0;
                foreach ($canvas as $canvasId => $singleCanvas) {
                    $numOfBoxesFilled = 0;
                    foreach ($singleCanvas as $box) {
                        if ($box > 0) {
                            $numOfBoxesFilled++;
                        }
                    }
                    $progress = $numOfBoxesFilled / $numOfBoxes;
                    if ($progress > $maxProgress) {
                        $maxProgress = $progress;
                    }
                }

                $progressResults[$key] = $maxProgress;
            }

            return $progressResults;
        }


        /**
         * getLastUpdatedCanvas - gets the list of canvas boards ordered by last updated item
         *
         * @access public
         * @param string $projectId projectId (optional)
         * @param array  $boards    Array of project board types
         * @return array List of boards with a progress percentage
         * @throws BindingResolutionException
         *
     * @api
     */
        public function getLastUpdatedCanvas(string $projectId = '', array $boards = array()): array
        {
            $canvasRepo = app()->make(\Leantime\Domain\Canvas\Repositories\Canvas::class);
            return $canvasRepo->getLastUpdatedCanvas($projectId, $boards);
        }
    }

}
