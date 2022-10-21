<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class emcanvas extends \leantime\domain\pdf\canvas {

		protected const CANVAS_NAME = 'em';
        
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
				'  <td class="canvas-elt-title" style="width: 25%;" colspan="4">'.$this->htmlCanvasTitle('box.em.header.goal', 'fa-bullseye').'</td>'.
                '</tr><tr>'.
				'  <td class="canvas-elt-title" colspan="2">'.$this->htmlCanvasTitle($this->canvasTypes['em_who']['title'], 
                    $this->canvasTypes['em_who']['icon']).'</td>'.
				'  <td class="canvas-elt-title" colspan="2">'.$this->htmlCanvasTitle($this->canvasTypes['em_what']['title'], 
                    $this->canvasTypes['em_what']['icon']).'</td>'.
				'</tr><tr>'.
				'  <td class="canvas-elt-box" style="height: 100px;" colspan="2">'.$this->htmlCanvasElements($recordsAry, 'em_who').'</td>'.
				'  <td class="canvas-elt-box" style="height: 100px;" colspan="2">'.$this->htmlCanvasElements($recordsAry, 'em_what').'</td>'.
				'</tr><tr>'.
				'  <td class="canvas-elt-title" colspan="4">'.$this->htmlCanvasTitle('box.em.header.empathy', 'fa-heart').'</td>'.
                '</tr><tr>'.
				'  <td class="canvas-elt-title">'.$this->htmlCanvasTitle($this->canvasTypes['em_see']['title'], 
                    $this->canvasTypes['em_see']['icon']).'</td>'.
				'  <td class="canvas-elt-title">'.$this->htmlCanvasTitle($this->canvasTypes['em_say']['title'], 
                    $this->canvasTypes['em_say']['icon']).'</td>'.
				'  <td class="canvas-elt-title">'.$this->htmlCanvasTitle($this->canvasTypes['em_do']['title'], 
                    $this->canvasTypes['em_do']['icon']).'</td>'.
				'  <td class="canvas-elt-title">'.$this->htmlCanvasTitle($this->canvasTypes['em_hear']['title'], 
                    $this->canvasTypes['em_hear']['icon']).'</td>'.
				'</tr><tr>'.
				'  <td class="canvas-elt-box" style="height: 100px;">'.$this->htmlCanvasElements($recordsAry, 'em_see').'</td>'.
				'  <td class="canvas-elt-box" style="height: 100px;">'.$this->htmlCanvasElements($recordsAry, 'em_say').'</td>'.
				'  <td class="canvas-elt-box" style="height: 100px;">'.$this->htmlCanvasElements($recordsAry, 'em_do').'</td>'.
				'  <td class="canvas-elt-box" style="height: 100px;">'.$this->htmlCanvasElements($recordsAry, 'em_hear').'</td>'.
				'</tr><tr>'.
				'  <td class="canvas-elt-title" colspan="4">'.$this->htmlCanvasTitle('box.em.header.think_feel', 'fa-7').'</td>'.
				'</tr><tr>'.
				'  <td class="canvas-elt-title" colspan="2">'.$this->htmlCanvasTitle($this->canvasTypes['em_pains']['title'], 
                    $this->canvasTypes['em_pains']['icon']).'</td>'.
				'  <td class="canvas-elt-title"colspan="2">'.$this->htmlCanvasTitle($this->canvasTypes['em_gains']['title'], 
                    $this->canvasTypes['em_gains']['icon']).'</td>'.
				'</tr><tr>'.
				'  <td class="canvas-elt-box" style="height: 100px;" colspan="2">'.$this->htmlCanvasElements($recordsAry, 'em_pains').'</td>'.
				'  <td class="canvas-elt-box" style="height: 100px;" colspan="2">'.$this->htmlCanvasElements($recordsAry, 'em_gains').'</td>'.
				'</tr><tr>'.
				'  <td class="canvas-elt-title" colspan="4">'.$this->htmlCanvasTitle($this->canvasTypes['em_motives']['title'], 
                    $this->canvasTypes['em_motives']['icon']).'</td>'.
				'</tr><tr>'.
				'  <td class="canvas-elt-box" style="height: 100px;" colspan="4">'.$this->htmlCanvasElements($recordsAry, 'em_motives').'</td>'.
                '</tr>'.
				'</tbody></table>';

			return $html;

        }

        /**
         * htmlListBoxTitleTop -  Typeset title of element box in list view
         *
         * @access protected
         * @param  string $text Canvas element title
         * @param  string $icon Optional: Icon associated with canvas element (FontAwesome code)
         * @return string HTML code
         */
        protected function htmlListTitleTop(string $text, string $icon = ''): string
        {
            
            return '<div style="font-size: '.$this->fontSizeLarge.'px; text-align: center; background-color: black; '.
                'font-weight: bold; width: 100%; color: white; padding: 4px; margin-bottom: 2px; margin-top: 5px;">'.
                (!empty($icon) ? $this->htmlIcon($icon).' ' : '').'<strong>'.$this->language->__($text).'</strong></div>';

        }
		
        /**
         * htmlList - Layout element list in a detailed form
         *
         * @access public
         * @param  array  $recordsAry Array of canvas data records
         * @return string HTML code
         */
        protected function htmlListDetailed(array $recordsAry): string
        {
			$html = '';
			$html .= '<div>'.$this->htmlListTitleTop($this->language->__('box.em.header.goal'), 'fa-bullseye').'</div>';
			$html .= '<div>'.$this->htmlListTitle($this->canvasTypes['em_who']['title'], $this->canvasTypes['em_who']['icon']).'</div>';
			$html .= '<div>'.$this->htmlListElementsDetailed($recordsAry, 'em_who').'</div>';
			$html .= '<div>'.$this->htmlListTitle($this->canvasTypes['em_what']['title'], $this->canvasTypes['em_what']['icon']).'</div>';
			$html .= '<div>'.$this->htmlListElementsDetailed($recordsAry, 'em_what').'</div>';
            $html .= '<hr />';

			$html .= '<div>'.$this->htmlListTitleTop($this->language->__('box.em.header.empathy'), 'fa-heart').'</div>';
			$html .= '<div>'.$this->htmlListTitle($this->canvasTypes['em_see']['title'], $this->canvasTypes['em_see']['icon']).'</div>';
			$html .= '<div>'.$this->htmlListElementsDetailed($recordsAry, 'em_see').'</div>';
			$html .= '<div>'.$this->htmlListTitle($this->canvasTypes['em_say']['title'], $this->canvasTypes['em_say']['icon']).'</div>';
			$html .= '<div>'.$this->htmlListElementsDetailed($recordsAry, 'em_say').'</div>';
			$html .= '<div>'.$this->htmlListTitle($this->canvasTypes['em_do']['title'], $this->canvasTypes['em_do']['icon']).'</div>';
			$html .= '<div>'.$this->htmlListElementsDetailed($recordsAry, 'em_do').'</div>';
			$html .= '<div>'.$this->htmlListTitle($this->canvasTypes['em_hear']['title'], $this->canvasTypes['em_hear']['icon']).'</div>';
			$html .= '<div>'.$this->htmlListElementsDetailed($recordsAry, 'em_hear').'</div>';
			$html .= '<hr>';

			$html .= '<div>'.$this->htmlListTitleTop($this->language->__('box.em.header.think_feel'), 'fa-7').'</div>';
			$html .= '<div>'.$this->htmlListTitle($this->canvasTypes['em_pains']['title'], $this->canvasTypes['em_pains']['icon']).'</div>';
			$html .= '<div>'.$this->htmlListElementsDetailed($recordsAry, 'em_pains').'</div>';
			$html .= '<div>'.$this->htmlListTitle($this->canvasTypes['em_gains']['title'], $this->canvasTypes['em_gains']['icon']).'</div>';
			$html .= '<div>'.$this->htmlListElementsDetailed($recordsAry, 'em_gains').'</div>';
			$html .= '<div>'.$this->htmlListTitle($this->canvasTypes['em_motives']['title'], $this->canvasTypes['em_motives']['icon']).'</div>';
			$html .= '<div>'.$this->htmlListElementsDetailed($recordsAry, 'em_motives').'</div>';
			$html .= '<hr>';

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
