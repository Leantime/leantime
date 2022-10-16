<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class insightscanvas extends \leantime\domain\pdf\canvas {

		protected const CANVAS_NAME = 'insights';
        
        /**
         * htmlCanvas -  Layout canvas (must be implemented)
         *
         * @access public
         * @param  array  $recordsAry Array of canvas data records
         * @return string HTML code
         */
        protected function htmlCanvas(array $recordsAry): string
        {
			
			$html = '<table class="canvas" style="width: 100%"><tbody><tr>';

            foreach($this->canvasTypes as $key => $box) {
				$html .= '<td class="canvas-elt-title" style="width: 20%;">'.$this->htmlCanvasTitle($box['title'], $box['icon']).'</td>';
			}

			$html .= '</tr><tr>';

            foreach($this->canvasTypes as $key => $box) {
				$html .= '<td class="canvas-elt-box" style="height: 650px;">'.$this->htmlCanvasElements($recordsAry, $key).'</td>';
			}

			$html .= '</tr></tbody></table>';

			return $html;

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
            $insightsCanvasRepo = new repositories\insightscanvas();
            $insightsCanvasAry = $insightsCanvasRepo->getSingleCanvas($id);
            !empty($insightsCanvasAry) || die("Cannot find canvas with id '$id'");
            $projectId = $insightsCanvasAry[0]['projectId'];
            $recordsAry = $insightsCanvasRepo->getCanvasItemsById($id);
            $projectsRepo = new repositories\projects();
            $projectAry = $projectsRepo->getProject($projectId);
            !empty($projectAry) || die("Cannot retrieve project id '$projectId'");
            
            // Configuration
            $options = [ ];
            
            // Generate PDF content
            $pdf = new \YetiForcePDF\Document();
            $pdf->init();
            $pdf->loadHtml($this->htmlReport($projectAry['name'], $insightsCanvasAry[0]['title'], $recordsAry, $filter, $options));
            return $pdf->render();

        }
    
    }
}
?>
