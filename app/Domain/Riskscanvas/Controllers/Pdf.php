<?php

/**
 * HTML code for PDF report
 */

namespace Leantime\Domain\Riskscanvas\Controllers {

    use Leantime\Domain\Canvas\Controllers\Pdf as PdfController;

    /**
     *
     */

    /**
     *
     */
    class Pdf extends PdfController
    {
        protected const CANVAS_NAME = 'risks';

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

            $html .= '<td class="canvas-elt-title" style="width: 50%;">' . $this->htmlCanvasTitle($this->canvasTypes['risks_imp_low_pro_high']['title'], $this->canvasTypes['risks_imp_low_pro_high']['icon']) . '</td>';
            $html .= '<td class="canvas-elt-title" style="width: 50%;">' . $this->htmlCanvasTitle($this->canvasTypes['risks_imp_high_pro_high']['title'], $this->canvasTypes['risks_imp_high_pro_high']['icon']) . '</td>';
            $html .= '</tr><tr>';
            $html .= '<td class="canvas-elt-box" style="height: 295px;">' . $this->htmlCanvasElements($recordsAry, 'risks_imp_low_pro_high') . '</td>';
            $html .= '<td class="canvas-elt-box" style="height: 295px;">' . $this->htmlCanvasElements($recordsAry, 'risks_imp_high_pro_high') . '</td>';

            $html .= '</tr><tr>';

            $html .= '<td class="canvas-elt-title" style="width: 50%;">' . $this->htmlCanvasTitle($this->canvasTypes['risks_imp_low_pro_low']['title'], $this->canvasTypes['risks_imp_low_pro_low']['icon']) . '</td>';
            $html .= '<td class="canvas-elt-title" style="width: 50%;">' . $this->htmlCanvasTitle($this->canvasTypes['risks_imp_high_pro_low']['title'], $this->canvasTypes['risks_imp_high_pro_low']['icon']) . '</td>';
            $html .= '</tr><tr>';
            $html .= '<td class="canvas-elt-box" style="height: 295px;">' . $this->htmlCanvasElements($recordsAry, 'risks_imp_low_pro_low') . '</td>';
            $html .= '<td class="canvas-elt-box" style="height: 295px;">' . $this->htmlCanvasElements($recordsAry, 'risks_imp_high_pro_low') . '</td>';

            $html .= '</tr></tbody></table>';

            return $html;
        }

        /***
         * reportGenerate - Generate report for module
         *
         * @access public
         * @param integer $id      Canvas identifier
         * @param array   $filter  Filter value
         * @param array   $options
         * @return string PDF filename
         */
        public function reportGenerate(int $id, array $filter = [], array $options = []): string
        {

            return parent::reportGenerate($id, $filter, []);
        }
    }
}
