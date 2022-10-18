<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class swotcanvas extends \leantime\domain\pdf\canvas {

		protected const CANVAS_NAME = 'swot';
        
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

			$html .= '<td class="canvas-elt-title" style="width: 50%;">'.$this->htmlCanvasTitle($this->canvasTypes['swot_strengths']['title'], $this->canvasTypes['swot_strengths']['icon']).'</td>';
			$html .= '<td class="canvas-elt-title" style="width: 50%;">'.$this->htmlCanvasTitle($this->canvasTypes['swot_weaknesses']['title'], $this->canvasTypes['swot_weaknesses']['icon']).'</td>';
			$html .= '</tr><tr>';
			$html .= '<td class="canvas-elt-box" style="height: 310px;">'.$this->htmlCanvasElements($recordsAry, 'swot_strengths').'</td>';
			$html .= '<td class="canvas-elt-box" style="height: 310px;">'.$this->htmlCanvasElements($recordsAry, 'swot_weaknesses').'</td>';

			$html .= '</tr><tr>';

			$html .= '<td class="canvas-elt-title" style="width: 50%;">'.$this->htmlCanvasTitle($this->canvasTypes['swot_opportunities']['title'], $this->canvasTypes['swot_opportunities']['icon']).'</td>';
			$html .= '<td class="canvas-elt-title" style="width: 50%;">'.$this->htmlCanvasTitle($this->canvasTypes['swot_threats']['title'], $this->canvasTypes['swot_threats']['icon']).'</td>';
			$html .= '</tr><tr>';
			$html .= '<td class="canvas-elt-box" style="height: 310px;">'.$this->htmlCanvasElements($recordsAry, 'swot_opportunities').'</td>';
			$html .= '<td class="canvas-elt-box" style="height: 310px;">'.$this->htmlCanvasElements($recordsAry, 'swot_threats').'</td>';

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
            $swotCanvasRepo = new repositories\swotcanvas();
            $swotCanvasAry = $swotCanvasRepo->getSingleCanvas($id);
            !empty($swotCanvasAry) || die("Cannot find canvas with id '$id'");
            $projectId = $swotCanvasAry[0]['projectId'];
            $recordsAry = $swotCanvasRepo->getCanvasItemsById($id);
            $projectsRepo = new repositories\projects();
            $projectAry = $projectsRepo->getProject($projectId);
            !empty($projectAry) || die("Cannot retrieve project id '$projectId'");
            
            // Configuration
            $options = [ ];
            
            // Generate PDF content
            $pdf = new \YetiForcePDF\Document();
            $pdf->init();
            $pdf->loadHtml($this->htmlReport($projectAry['name'], $swotCanvasAry[0]['title'], $recordsAry, $filter, $options));
            return $pdf->render();

        }
    
    }
}
?>
