<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class smcanvas extends \leantime\domain\pdf\canvas {

		protected const CANVAS_NAME = 'sm';
        
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
            $smCanvasRepo = new repositories\smcanvas();
            $smCanvasAry = $smCanvasRepo->getSingleCanvas($id);
            !empty($smCanvasAry) || die("Cannot find canvas with id '$id'");
            $projectId = $smCanvasAry[0]['projectId'];
            $recordsAry = $smCanvasRepo->getCanvasItemsById($id);
            $projectsRepo = new repositories\projects();
            $projectAry = $projectsRepo->getProject($projectId);
            !empty($projectAry) || die("Cannot retrieve project id '$projectId'");
            
            // Configuration
            $options = [ 'canvasShow' => false ];
            
            // Generate PDF content
            $pdf = new \YetiForcePDF\Document();
            $pdf->init();
            $pdf->loadHtml($this->htmlReport($projectAry['name'], $smCanvasAry[0]['title'], $recordsAry, $filter, $options));
            return $pdf->render();

        }
    
    }
}
?>
