<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\controllers {
  
	use leantime\domain\repositories;
	
    class pdf extends \leantime\domain\controllers\canvas\pdf {

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
				$html .= '<td class="canvas-elt-box" style="height: 620px;">'.$this->htmlCanvasElements($recordsAry, $key).'</td>';
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
        public function reportGenerate(int $id, array $filter = [], array $options = []): string
        {

			return parent::reportGenerate($id, $filter, []);

        }
    
    }
}
?>
