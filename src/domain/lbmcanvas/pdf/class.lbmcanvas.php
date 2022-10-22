<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class lbmcanvas extends \leantime\domain\pdf\canvas {

		protected const CANVAS_NAME = 'lbm';

        /**
         * htmlCanvas -  Layout canvas (must be implemented)
         *
         * @access public
         * @param  array  $recordsAry Array of canvas data records
         * @return string HTML code
         */
        protected function htmlCanvas(array $recordsAry): string
        {
			
			$html = '<table class="canvas" style="width: 100%"><tbody>'.
				'<tr>'.
				'  <td class="canvas-elt-title" style="width: 33%;">'.
                $this->htmlCanvasTitle($this->canvasTypes['lbm_customers']['title'], $this->canvasTypes['lbm_customers']['icon']).'</td>'.
				'  <td class="canvas-elt-title" style="width: 33%;">'.
                $this->htmlCanvasTitle($this->canvasTypes['lbm_offerings']['title'], $this->canvasTypes['lbm_offerings']['icon']).'</td>'.
				'  <td class="canvas-elt-title" style="width: 33%;">'.
                $this->htmlCanvasTitle($this->canvasTypes['lbm_capabilities']['title'], 
                                       $this->canvasTypes['lbm_capabilities']['icon']).'</td>'.
				'</tr>'.
				'<tr>'.
				'  <td class="canvas-elt-box" style="height: 345px;">'.$this->htmlCanvasElements($recordsAry, 'lbm_customers').'</td>'.
				'  <td class="canvas-elt-box" style="height: 345px;">'.$this->htmlCanvasElements($recordsAry, 'lbm_offerings').'</td>'.
				'  <td class="canvas-elt-box" style="height: 345px;">'.$this->htmlCanvasElements($recordsAry, 'lbm_capabilities').'</td>'.
				'</tr>'.
				'<tr>'.
				'  <td class="canvas-elt-title" colspan="3">'.
                $this->htmlCanvasTitle($this->canvasTypes['lbm_financials']['title'], $this->canvasTypes['lbm_financials']['icon']).'</td>'.
				'</tr>'.
				'<tr>'.
				'  <td class="canvas-elt-box" style="height: 245px;" colspan="3">'.
                $this->htmlCanvasElements($recordsAry, 'lbm_financials').'</td>'.
				'</tr>'.
				'</tbody></table>';
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
        public function reportGenerate(int $id, array $filter = [], array $options = []): string
        {

            $options = [ 'disclaimer' => $this->canvasRepo->getDisclaimer() ];
			return parent::reportGenerate($id, $filter, $options);

        }
    
    }
}
?>
