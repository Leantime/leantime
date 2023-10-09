<?php

/**
 * HTML code for PDF report
 */

namespace Leantime\Domain\Eacanvas\Controllers {

    use Leantime\Domain\Canvas\Controllers\Pdf as CanvasPdf;

    /**
     *
     */
    class Pdf extends CanvasPdf
    {
        protected const CANVAS_NAME = 'ea';

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

            $html .= '<td class="canvas-elt-title" style="width: 33.33%;">' .
                $this->htmlCanvasTitle($this->canvasTypes['ea_political']['title'], $this->canvasTypes['ea_political']['icon']) . '</td>';
            $html .= '<td class="canvas-elt-title" style="width: 33.33%;">' .
                $this->htmlCanvasTitle($this->canvasTypes['ea_economic']['title'], $this->canvasTypes['ea_economic']['icon']) . '</td>';
            $html .= '<td class="canvas-elt-title" style="width: 33.33%;">' .
                $this->htmlCanvasTitle($this->canvasTypes['ea_societal']['title'], $this->canvasTypes['ea_societal']['icon']) . '</td>';

            $html .= '</tr><tr>';

            $html .= '<td class="canvas-elt-box" style="height: 295px;">' . $this->htmlCanvasElements($recordsAry, 'ea_political') . '</td>';
            $html .= '<td class="canvas-elt-box" style="height: 295px;">' . $this->htmlCanvasElements($recordsAry, 'ea_economic') . '</td>';
            $html .= '<td class="canvas-elt-box" style="height: 295px;">' . $this->htmlCanvasElements($recordsAry, 'ea_societal') . '</td>';

            $html .= '</tr><tr>';

            $html .= '<td class="canvas-elt-title" style="width: 33.33%;">' .
                $this->htmlCanvasTitle(
                    $this->canvasTypes['ea_technological']['title'],
                    $this->canvasTypes['ea_technological']['icon']
                ) . '</td>';
            $html .= '<td class="canvas-elt-title" style="width: 33.33%;">' .
                $this->htmlCanvasTitle($this->canvasTypes['ea_legal']['title'], $this->canvasTypes['ea_legal']['icon']) . '</td>';
            $html .= '<td class="canvas-elt-title" style="width: 33.33%;">' .
                $this->htmlCanvasTitle($this->canvasTypes['ea_ecological']['title'], $this->canvasTypes['ea_ecological']['icon']) . '</td>';

            $html .= '</tr><tr>';

            $html .= '<td class="canvas-elt-box" style="height: 295px;">' . $this->htmlCanvasElements($recordsAry, 'ea_technological') . '</td>';
            $html .= '<td class="canvas-elt-box" style="height: 295px;">' . $this->htmlCanvasElements($recordsAry, 'ea_legal') . '</td>';
            $html .= '<td class="canvas-elt-box" style="height: 295px;">' . $this->htmlCanvasElements($recordsAry, 'ea_ecological') . '</td>';

            $html .= '</tr><tr>';

            $html .= '</tr></tbody></table>';

            return $html;
        }

        /***
         * reportGenerate - Generate report for module
         *
         * @access public
         * @param int   $id      Canvas identifier
         * @param array $filter  Filter value
         * @param array $options
         * @return string PDF filename
         */
        public function reportGenerate(int $id, array $filter = [], array $options = []): string
        {

            $options = [];
            return parent::reportGenerate($id, $filter, []);
        }
    }
}
