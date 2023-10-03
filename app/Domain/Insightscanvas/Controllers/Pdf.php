<?php

/**
 * HTML code for PDF report
 */

namespace Leantime\Domain\Insightscanvas\Controllers {

    use Leantime\Domain\Canvas\Controllers\Pdf as PdfController;

    /**
     *
     */

    /**
     *
     */
    class Pdf extends PdfController
    {
        protected const CANVAS_NAME = 'insights';

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

            foreach ($this->canvasTypes as $key => $box) {
                $html .= '<td class="canvas-elt-title" style="width: 20%;">' . $this->htmlCanvasTitle($box['title'], $box['icon']) . '</td>';
            }

            $html .= '</tr><tr>';

            foreach ($this->canvasTypes as $key => $box) {
                $html .= '<td class="canvas-elt-box" style="height: 620px;">' . $this->htmlCanvasElements($recordsAry, $key) . '</td>';
            }

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
