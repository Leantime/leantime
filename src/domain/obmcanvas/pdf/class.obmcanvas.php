<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class obmcanvas extends \leantime\domain\pdf\canvas {

		protected const CANVAS_NAME = 'obm';

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
				'  <td class="canvas-elt-title" style="width: 5%;" colspan="2">'.
                $this->htmlCanvasTitle($this->canvasTypes['obm_kp']['title'], $this->canvasTypes['obm_kp']['icon']).'</td>'.
				'  <td class="canvas-elt-title" style="width: 5%;" colspan="2">'.
                $this->htmlCanvasTitle($this->canvasTypes['obm_ka']['title'], $this->canvasTypes['obm_ka']['icon']).'</td>'.
				'  <td class="canvas-elt-title" style="width: 5%;" colspan="2">'.
                $this->htmlCanvasTitle($this->canvasTypes['obm_vp']['title'], $this->canvasTypes['obm_vp']['icon']).'</td>'.
				'  <td class="canvas-elt-title" style="width: 5%;" colspan="2">'.
                $this->htmlCanvasTitle($this->canvasTypes['obm_cr']['title'], $this->canvasTypes['obm_cr']['icon']).'</td>'.
				'  <td class="canvas-elt-title" style="width: 5%;" colspan="2">'.
                $this->htmlCanvasTitle($this->canvasTypes['obm_cs']['title'], $this->canvasTypes['obm_cs']['icon']).'</td>'.
				'</tr><tr>'.
				'  <td class="canvas-elt-box" style="height: 420px;" colspan="2">'.$this->htmlCanvasElements($recordsAry, 'obm_kp').'</td>'.
				'  <td style="height: 220px;" colspan="2" style="border: 0px">'.
				'    <table class="canvas" style="width: 100%"><tbody>'.
				'      <tr>'.
				'        <td class="canvas-elt-box" style="height: 197px; width: 100%;">'.
                $this->htmlCanvasElements($recordsAry, 'obm_ka').'</td>'.
				'      </tr><tr>'.
				'        <td class="canvas-elt-title">'.
                $this->htmlCanvasTitle($this->canvasTypes['obm_kr']['title'], $this->canvasTypes['obm_kr']['icon']).'</td>'.
				'      </tr><tr>'.
				'        <td class="canvas-elt-box" style="height: 198px;">'.$this->htmlCanvasElements($recordsAry, 'obm_kr').'</td>'.
				'      </tr>'.
				'   </tbody></table>'.
				'  </td>'.
				'  <td class="canvas-elt-box" style="height: 420px;" colspan="2">'.$this->htmlCanvasElements($recordsAry, 'obm_vp').'</td>'.
				'  <td style="height: 220px;" colspan="2">'.
				'    <table class="canvas" style="width: 100%"><tbody>'.
				'      <tr>'.
				'        <td class="canvas-elt-box" style="height: 197px; width: 50%;">'.
                $this->htmlCanvasElements($recordsAry, 'obm_cr').'</td>'.
				'      </tr><tr>'.
				'        <td class="canvas-elt-title">'.
                $this->htmlCanvasTitle($this->canvasTypes['obm_ch']['title'], $this->canvasTypes['obm_ch']['icon']).'</td>'.
				'      </tr><tr>'.
				'        <td class="canvas-elt-box" style="height: 198px;">'.$this->htmlCanvasElements($recordsAry, 'obm_ch').'</td>'.
				'      </tr>'.
				'    </tbody></table>'.
				'  </td>'.
				'  <td class="canvas-elt-box" style="height: 420px;" colspan="2">'.$this->htmlCanvasElements($recordsAry, 'obm_cs').'</td>'.
                '</tr><tr>'.
				'  <td class="canvas-elt-title" colspan="5">'.
                $this->htmlCanvasTitle($this->canvasTypes['obm_fc']['title'], $this->canvasTypes['obm_fc']['icon']).'</td>'.
				'  <td class="canvas-elt-title" colspan="5">'.
                $this->htmlCanvasTitle($this->canvasTypes['obm_fr']['title'], $this->canvasTypes['obm_fr']['icon']).'</td>'.
                '</tr><tr>'.
                '  <td class="canvas-elt-box" style="height: 210px;" colspan="5">'.$this->htmlCanvasElements($recordsAry, 'obm_fc').'</td>'.
				'  <td class="canvas-elt-box" style="height: 210px;" colspan="5">'.$this->htmlCanvasElements($recordsAry, 'obm_fr').'</td>'.
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
        public function reportGenerate(int $id, array $filter = []): string
        {

            // Retrieve canvas data
            $obmCanvasRepo = new repositories\obmcanvas();
            $obmCanvasAry = $obmCanvasRepo->getSingleCanvas($id);
            !empty($obmCanvasAry) || die("Cannot find canvas with id '$id'");
            $projectId = $obmCanvasAry[0]['projectId'];
            $recordsAry = $obmCanvasRepo->getCanvasItemsById($id);
            $projectsRepo = new repositories\projects();
            $projectAry = $projectsRepo->getProject($projectId);
            !empty($projectAry) || die("Cannot retrieve project id '$projectId'");
            
            // Configuration
            $options = [ 'disclaimer' => $obmCanvasRepo->getDisclaimer() ];
            
            // Generate PDF content
            $pdf = new \YetiForcePDF\Document();
            $pdf->init();
            $pdf->loadHtml($this->htmlReport($projectAry['name'], $obmCanvasAry[0]['title'], $recordsAry, $filter, $options));
            return $pdf->render();

        }
    
    }
}
?>
