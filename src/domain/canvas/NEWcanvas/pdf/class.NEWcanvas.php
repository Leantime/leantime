<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class NEWcanvas extends \leantime\domain\pdf\canvas {

		protected const CANVAS_NAME = 'NEW';
        
        /**
         * htmlCanvas -  Layout canvas (must be implemented)
         *
         * @access public
         * @param  array  $recordsAry Array of canvas data records
         * @return string HTML code
         */
        protected function htmlCanvas(array $recordsAry): string
        {
			
            return 'NOT IMPLEMENTED';

        }
        
        /**
         * htmlList - Layout element list (must be implemented)
         *
         * @access public
         * @param  array  $recordsAry Array of canvas data records
         * @return string HTML code
         */
        protected function htmlList(array $recordsAry): string
        {
			
            return 'NOT IMPLEMENTED';

        }
		
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
            $sbCanvasRepo = new repositories\sbcanvas();
            $sbCanvasAry = $sbCanvasRepo->getSingleCanvas($id);
            !empty($sbCanvasAry) || die("Cannot find canvas with id '$id'");
            $projectId = $sbCanvasAry[0]['projectId'];
            $recordsAry = $sbCanvasRepo->getCanvasItemsById($id);
            $projectsRepo = new repositories\projects();
            $projectAry = $projectsRepo->getProject($projectId);
            !empty($projectAry) || die("Cannot retrieve project id '$projectId'");
            
            // Configuration
            $options = [ ];
            
            // Generate PDF content
            $pdf = new \YetiForcePDF\Document();
            $pdf->init();
            $pdf->loadHtml($this->htmlReport($projectAry['name'], $sbCanvasAry[0]['title'], $recordsAry, $filter, $options));
            return $pdf->render();

        }
    
    }
}
?>
