<?php

namespace leantime\domain\services {

    use leantime\core;
    use leantime\domain\repositories;

    class canvas
    {

        /***
         * import - Import canvas from XML file
         *
         * @access public
         * @param  string   $filename    File to import
         * @param  string   $canvcasName Canvas name
         * @param  int      $projectId   Project identifier
         * @param  int      $auhtorId    Author identifier
         * @return bool|int False if import failed and the id of the newly created canvas otherwise
         */
        public function import(string $filename, string $canvasName, int $projectId, int $authorId): bool|int
        {

            $dom = new \DOMDocument('1.0', 'UTF-8');

            // Read file
            $canvasData = file_get_contents($filename);
            if($canvasData === false) return false;

            // Convert read data into XML DOM structure
            $old_error_reporting = error_reporting(error_reporting() & ~E_WARNING);
            $status = $dom->loadXML($canvasData);
            if($status === false) return false;

            // Decode XMP data
            $canvasAry = [ 'projectId' => $projectId, 'author' => $authorId ];
            $recordsAry = [];
            
            // - Canvas
            $canvasNodeList = $dom->getElementsByTagName('canvas');
            if($canvasNodeList->count() !== 1) return false;
            $importedCanvasName = $canvasNodeList->item(0)->getAttribute('key');
            
            // - Canvas / Title
            $titleNodeList = $canvasNodeList->item(0)->getElementsByTagName('title');
            if($titleNodeList->count() !== 1) return false;
            $canvasAry['title'] = $titleNodeList->item(0)->nodeValue;

            // - Canvas / Data
            $dataNodeList = $canvasNodeList->item(0)->getElementsByTagName('content');
            if($dataNodeList->count() !== 1) return false;

            // - Data / Element*
            $elementNodeList = $dataNodeList->item(0)->getElementsByTagName('element');
            
            foreach($elementNodeList as $elementNode) {
                
                if(!$elementNode->hasAttribute('key')) return false;
                $elementKey = $elementNode->getAttribute('key');
                
                $itemNodeList = $elementNode->getElementsByTagName('item');
                foreach($itemNodeList as $itemName) {
                    
                    $authorNodeList = $itemName->getElementsByTagName('author');
                    if($authorNodeList->count() !== 1) return false;
                    if(!$authorNodeList->item(0)->hasAttribute('id')) return false;
                    $author = $authorNodeList->item(0)->getAttribute('id');
                    
                    $descriptionNodeList = $itemName->getElementsByTagName('description');
                    if($descriptionNodeList->count() !== 1) return false;
                    $description = $descriptionNodeList->item(0)->nodeValue;
                    
                    $statusNodeList = $itemName->getElementsByTagName('status');
                    if($statusNodeList->count() !== 1) return false;
                    if(!$statusNodeList->item(0)->hasAttribute('key')) return false;
                    $status = $statusNodeList->item(0)->getAttribute('key');
                    
                    $relatesNodeList = $itemName->getElementsByTagName('relates');
                    if($relatesNodeList->count() !== 1) return false;
                    if(!$relatesNodeList->item(0)->hasAttribute('key')) return false;
                    $relates = $relatesNodeList->item(0)->getAttribute('key');
                    
                    $assumptionsNodeList = $itemName->getElementsByTagName('assumptions');
                    if($assumptionsNodeList->count() !== 1) return false;
                    $assumptions = $assumptionsNodeList->item(0)->nodeValue;
                    
                    $dataNodeList = $itemName->getElementsByTagName('data');
                    if($dataNodeList->count() !== 1) return false;
                    $data = $dataNodeList->item(0)->nodeValue;
                    
                    $conclusionNodeList = $itemName->getElementsByTagName('conclusion');
                    if($conclusionNodeList->count() !== 1) return false;
                    $conclusion = $conclusionNodeList->item(0)->nodeValue;

                    $recordsAry[] = [ 'description' => $description,
                                      'assumptions' => $assumptions,
                                      'data' => $data,
                                      'conclusion' => $conclusion,
                                      'box' => $elementKey,
                                      'author' => 0000,
                                      'status' => $status,
                                      'relates' => $relates,
                                      'milestoneId' => '' ];
                }
                    
            }

            // Check if canvas is consistently named
            if($canvasName !== $importedCanvasName) return false;

            $canvasRepoName = "\\leantime\\domain\\repositories\\$canvasName";
            $canvasRepo = new $canvasRepoName();
            
            // Check if canvas already exists?
            $canvasAry['title'] .= ' [imported]';
            if($canvasRepo->existCanvas($projectId, $canvasAry['title'])) return false;
            
            // Save new canvas and return id
            $canvasId = $canvasRepo->addCanvas($canvasAry);
            if($canvasId === false) return false;

            foreach($recordsAry as $record) {
                
                $record['canvasId'] = $canvasId;
                $canvasRepo->addCanvasItem($record);
                
            }

            return $canvasId;
        }

    }

}
