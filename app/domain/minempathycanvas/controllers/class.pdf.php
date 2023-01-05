<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\controllers {

	use leantime\domain\repositories;

    class pdf extends \leantime\domain\controllers\canvas\pdf {

		protected const CANVAS_NAME = 'minempathy';

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

			$html .= '<td class="canvas-elt-title" style="width: 50%;">'.$this->htmlCanvasTitle($this->canvasTypes['minempathy_who']['title'], $this->canvasTypes['minempathy_who']['icon']).'</td>';
			$html .= '<td class="canvas-elt-title" style="width: 50%;">'.$this->htmlCanvasTitle($this->canvasTypes['minempathy_struggles']['title'], $this->canvasTypes['minempathy_struggles']['icon']).'</td>';
			$html .= '</tr><tr>';
			$html .= '<td class="canvas-elt-box" style="height: 295px;">'.$this->htmlCanvasElements($recordsAry, 'minempathy_who').'</td>';
			$html .= '<td class="canvas-elt-box" style="height: 295px;">'.$this->htmlCanvasElements($recordsAry, 'minempathy_struggles').'</td>';

			$html .= '</tr><tr>';
            $html .= '<td class="canvas-elt-title" style="width: 50%;" colspan="2">'.$this->htmlCanvasTitle($this->canvasTypes['minempathy_where']['title'], $this->canvasTypes['minempathy_where']['icon']).'</td>';
            $html .= '</tr><tr>';
            $html .= '<td class="canvas-elt-box" style="height: 295px;" colspan="2">'.$this->htmlCanvasElements($recordsAry, 'minempathy_where').'</td>';
            $html .= '</tr><tr>';


			$html .= '<td class="canvas-elt-title" style="width: 50%;">'.$this->htmlCanvasTitle($this->canvasTypes['minempathy_why']['title'], $this->canvasTypes['minempathy_why']['icon']).'</td>';
			$html .= '<td class="canvas-elt-title" style="width: 50%;">'.$this->htmlCanvasTitle($this->canvasTypes['minempathy_how']['title'], $this->canvasTypes['minempathy_how']['icon']).'</td>';
			$html .= '</tr><tr>';
			$html .= '<td class="canvas-elt-box" style="height: 295px;">'.$this->htmlCanvasElements($recordsAry, 'minempathy_why').'</td>';
			$html .= '<td class="canvas-elt-box" style="height: 295px;">'.$this->htmlCanvasElements($recordsAry, 'minempathy_how').'</td>';

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

