<?php

/**
 * HTML code for PDF report
 */

namespace leantime\domain\controllers {

    use leantime\domain\repositories;

    class pdf extends \leantime\domain\controllers\canvas\pdf
    {
        protected const CANVAS_NAME = 'swot';

        /**
         * htmlCanvas -  Layout canvas (must be implemented)
         *
         * @access public
         * @param  array $recordsAry Array of canvas data records
         * @return string HTML code
         */
        protected function htmlCanvas(array $recordsAry): string
        {

            $html = '<table class="canvas" style="width: 100%"><tbody><tr>';

            $html .= '<td class="canvas-elt-title" style="width: 50%;">' . $this->htmlCanvasTitle($this->canvasTypes['swot_strengths']['title'], $this->canvasTypes['swot_strengths']['icon']) . '</td>';
            $html .= '<td class="canvas-elt-title" style="width: 50%;">' . $this->htmlCanvasTitle($this->canvasTypes['swot_weaknesses']['title'], $this->canvasTypes['swot_weaknesses']['icon']) . '</td>';
            $html .= '</tr><tr>';
            $html .= '<td class="canvas-elt-box" style="height: 290px;">' . $this->htmlCanvasElements($recordsAry, 'swot_strengths') . '</td>';
            $html .= '<td class="canvas-elt-box" style="height: 290px;">' . $this->htmlCanvasElements($recordsAry, 'swot_weaknesses') . '</td>';

            $html .= '</tr><tr>';

            $html .= '<td class="canvas-elt-title" style="width: 50%;">' . $this->htmlCanvasTitle($this->canvasTypes['swot_opportunities']['title'], $this->canvasTypes['swot_opportunities']['icon']) . '</td>';
            $html .= '<td class="canvas-elt-title" style="width: 50%;">' . $this->htmlCanvasTitle($this->canvasTypes['swot_threats']['title'], $this->canvasTypes['swot_threats']['icon']) . '</td>';
            $html .= '</tr><tr>';
            $html .= '<td class="canvas-elt-box" style="height: 290px;">' . $this->htmlCanvasElements($recordsAry, 'swot_opportunities') . '</td>';
            $html .= '<td class="canvas-elt-box" style="height: 290px;">' . $this->htmlCanvasElements($recordsAry, 'swot_threats') . '</td>';

            $html .= '</tr></tbody></table>';

            return $html;
        }

        /***
         * reportGenerate - Generate report for module
         *
         * @access public
         * @param  integer $id     Canvas identifier
         * @param  string  $filter Filter value
         * @return string PDF filename
         */
        public function reportGenerate(int $id, array $filter = [], array $options = []): string
        {

            return parent::reportGenerate($id, $filter, []);
        }
    }
}
