<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class dbmcanvas extends \leantime\domain\pdf\canvas {

		protected const CANVAS_NAME = 'dbm';

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
				'  <td class="canvas-elt-title" style="width: 3.33%;" colspan="6">'.
                $this->htmlCanvasTitle($this->canvasTypes['dbm_cs']['title'], $this->canvasTypes['dbm_cs']['icon']).'</td>'.
				'  <td class="canvas-elt-title" style="width: 3.33%;" colspan="6">'.
                $this->htmlCanvasTitle($this->canvasTypes['dbm_cr']['title'], $this->canvasTypes['dbm_cr']['icon']).'</td>'.
				'  <td class="canvas-elt-title" style="width: 3.33%;" colspan="6">'.
                $this->htmlCanvasTitle($this->canvasTypes['dbm_ovp']['title'], $this->canvasTypes['dbm_ovp']['icon']).'</td>'.
				'  <td class="canvas-elt-title" style="width: 3.33%;" colspan="4">'.
                $this->htmlCanvasTitle($this->canvasTypes['dbm_kac']['title'], $this->canvasTypes['dbm_kad']['icon']).'</td>'.
				'  <td class="canvas-elt-title" style="width: 3.33%;" colspan="4">'.
                $this->htmlCanvasTitle($this->canvasTypes['dbm_kac']['title'], $this->canvasTypes['dbm_kac']['icon']).'</td>'.
				'  <td class="canvas-elt-title" style="width: 3.33%;" colspan="4">'.
                $this->htmlCanvasTitle($this->canvasTypes['dbm_kao']['title'], $this->canvasTypes['dbm_kao']['icon']).'</td>'.
				'</tr><tr>'.
				'  <td class="canvas-elt-box" style="height: 200px;" colspan="6">'.$this->htmlCanvasElements($recordsAry, 'dbm_cs').'</td>'.
				'  <td class="canvas-elt-box" style="height: 200px;" colspan="6">'.$this->htmlCanvasElements($recordsAry, 'dbm_cr').'</td>'.
                '  <td class="canvas-elt-box" style="height: 200px;" colspan="6">'.$this->htmlCanvasElements($recordsAry, 'dbm_ops').'</td>'.
                '  <td class="canvas-elt-box" style="height: 200px;" colspan="4">'.$this->htmlCanvasElements($recordsAry, 'dbm_kad').'</td>'.
                '  <td class="canvas-elt-box" style="height: 200px;" colspan="4">'.$this->htmlCanvasElements($recordsAry, 'dbm_kac').'</td>'.
                '  <td class="canvas-elt-box" style="height: 200px;" colspan="4">'.$this->htmlCanvasElements($recordsAry, 'dbm_kao').'</td>'.
				'  </tr><tr>'.
				'  <td class="canvas-elt-title" colspan="6">'.
                $this->htmlCanvasTitle($this->canvasTypes['dbm_cj']['title'], $this->canvasTypes['dbm_cj']['icon']).'</td>'.
				'  <td class="canvas-elt-title" colspan="6">'.
                $this->htmlCanvasTitle($this->canvasTypes['dbm_cd']['title'], $this->canvasTypes['dbm_cd']['icon']).'</td>'.
				'  <td class="canvas-elt-title" colspan="6">'.
                $this->htmlCanvasTitle($this->canvasTypes['dbm_ops']['title'], $this->canvasTypes['dbm_ops']['icon']).'</td>'.
				'  <td class="canvas-elt-title" colspan="6">'.
                $this->htmlCanvasTitle($this->canvasTypes['dbm_krp']['title'], $this->canvasTypes['dbm_krp']['icon']).'</td>'.
				'  <td class="canvas-elt-title" colspan="6">'.
                $this->htmlCanvasTitle($this->canvasTypes['dbm_krc']['title'], $this->canvasTypes['dbm_krc']['icon']).'</td>'.
				'</tr><tr>'.
				'  <td class="canvas-elt-box" style="height: 200px;" colspan="6">'.$this->htmlCanvasElements($recordsAry, 'dbm_cj').'</td>'.
                '  <td class="canvas-elt-box" style="height: 200px;" colspan="6">'.$this->htmlCanvasElements($recordsAry, 'dbm_cd').'</td>'.
				'  <td class="canvas-elt-box" style="height: 200px;" colspan="6">'.$this->htmlCanvasElements($recordsAry, 'dbm_ovp').'</td>'.
				'  <td style="height: 200px;" colspan="12">'.
				'    <table class="canvas" style="width: 100%"><tbody>'.
				'      <tr>'.
				'        <td class="canvas-elt-box" style="height: 88px; width: 50%;">'.$this->htmlCanvasElements($recordsAry, 'dbm_krp').'</td>'.
				'        <td class="canvas-elt-box" style="height: 88px; width: 50%;">'.$this->htmlCanvasElements($recordsAry, 'dbm_krc').'</td>'.
				'      </tr><tr>'.
				'        <td class="canvas-elt-title">'.$this->htmlCanvasTitle($this->canvasTypes['dbm_krl']['title'], $this->canvasTypes['dbm_krl']['icon']).'</td>'.
				'        <td class="canvas-elt-title">'.$this->htmlCanvasTitle($this->canvasTypes['dbm_krs']['title'], $this->canvasTypes['dbm_krs']['icon']).'</td>'.
				'      </tr><tr>'.
				'        <td class="canvas-elt-box" style="height: 88px;">'.$this->htmlCanvasElements($recordsAry, 'dbm_krl').'</td>'.
				'        <td class="canvas-elt-box" style="height: 88px;">'.$this->htmlCanvasElements($recordsAry, 'dbm_krs').'</td>'.
				'      </tr>'.
				'    </tbody></table>'.
				'  </td>'.
                '</tr><tr>'.
				'  <td class="canvas-elt-title" colspan="15">'.
                $this->htmlCanvasTitle($this->canvasTypes['dbm_fr']['title'], $this->canvasTypes['dbm_fr']['icon']).'</td>'.
				'  <td class="canvas-elt-title" colspan="15">'.
                $this->htmlCanvasTitle($this->canvasTypes['dbm_fc']['title'], $this->canvasTypes['dbm_fc']['icon']).'</td>'.
                '</tr><tr>'.
                '  <td class="canvas-elt-box" style="height: 200px;" colspan="15">'.$this->htmlCanvasElements($recordsAry, 'dbm_fr').'</td>'.
				'  <td class="canvas-elt-box" style="height: 200px;" colspan="15">'.$this->htmlCanvasElements($recordsAry, 'dbm_fc').'</td>'.
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
