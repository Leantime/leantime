<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class sqcanvas extends \leantime\domain\pdf\canvas {

		protected const CANVAS_NAME = 'sq';
        
        /***
         * reportGenerate - Generate report for module
         *
         * @access public
         * @param  int    $id     Canvas identifier
         * @param  string $filter Filter value
         * @return string PDF filename
         */
        public function reportGenerate(int $id, array $filter = []): string
        {

            // Retrieve canvas data
            $sqCanvasRepo = new repositories\sqcanvas();
            $sqCanvasAry = $sqCanvasRepo->getSingleCanvas($id);
            !empty($sqCanvasAry) || die("Cannot find canvas with id '$id'");
            $projectId = $sqCanvasAry[0]['projectId'];
            $recordsAry = $sqCanvasRepo->getCanvasItemsById($id);
            $projectsRepo = new repositories\projects();
            $projectAry = $projectsRepo->getProject($projectId);
            !empty($projectAry) || die("Cannot retrieve project id '$projectId'");
            
            // Configuration
            $options = [ 'canvasShow' => false ];
            
            // Generate PDF content
            $pdf = new \YetiForcePDF\Document();
            $pdf->init();
            $pdf->loadHtml($this->htmlReport($projectAry['name'], $sqCanvasAry[0]['title'], $recordsAry, $filter, $options));
            return $pdf->render();

        }
    
    }
}
?>
