<?php

/**
 * HTML code for PDF report
 */

namespace Leantime\Domain\Cpcanvas\Controllers {

    use Leantime\Domain\Canvas\Controllers\Pdf as CanvasPdf;

    class Pdf extends CanvasPdf
    {
        protected const CANVAS_NAME = 'cp';

        /**
         * htmlCanvas -  Layout canvas (must be implemented)
         *
         * @access public
         * @param  array $recordsAry Array of canvas data records
         * @return string HTML code
         */
        protected function htmlCanvas(array $recordsAry): string
        {

            $html = '<table class="canvas" style="width: 100%"><tbody><tr>' .
                '  <td class="canvas-elt-titleX" style="width: 16%;">&nbsp;</td>' .
                '  <td class="canvas-elt-title" style="width: 28%;" colspan="3"><strong>' .
                    $this->htmlIcon('fa-user-doctor') . ' ' . $this->language->__('box.header.cp.cj') . '</strong></td>' .
                '</tr><tr>' .
                '  <td class="canvas-elt-titleX">&nbsp;</td>' .
                '  <td class="canvas-elt-title">' .
                    $this->htmlCanvasTitle($this->canvasTypes['cp_cj_rv']['title'], $this->canvasTypes['cp_cj_rv']['icon']) . '</td>' .
                '  <td class="canvas-elt-title">' .
                    $this->htmlCanvasTitle($this->canvasTypes['cp_cj_rc']['title'], $this->canvasTypes['cp_cj_rc']['icon']) . '</td>' .
                '  <td class="canvas-elt-title">' .
                    $this->htmlCanvasTitle($this->canvasTypes['cp_cj_e']['title'], $this->canvasTypes['cp_cj_e']['icon']) . '</td>' .
                '</tr><tr>' .
                '  <td class="canvas-elt-box" style="height: 110px; vertical-align: middle; text-align: center;"><strong>' .
                    $this->language->__('box.label.cp.need') . '</strong></td>' .
                '  <td class="canvas-elt-box" style="height: 110px">' . $this->htmlCanvasElements($recordsAry, 'cp_cj_rv') . '</td>' .
                '  <td class="canvas-elt-box" style="height: 110px">' . $this->htmlCanvasElements($recordsAry, 'cp_cj_rc') . '</td>' .
                '  <td class="canvas-elt-box" style="height: 110px">' . $this->htmlCanvasElements($recordsAry, 'cp_cj_e') . '</td>' .
                '</tr><tr>' .
                '  <td style="text-align: center; height: 40px">&nbsp;</td>' .
                '  <td style="text-align: center; height: 40px">' . $this->htmlIcon('fa-arrows-up-down') . '</td>' .
                '  <td style="text-align: center; height: 40px">' . $this->htmlIcon('fa-arrows-up-down') . '</td>' .
                '  <td style="text-align: center; height: 40px">' . $this->htmlIcon('fa-arrows-up-down') . '</td>' .
                '</tr><tr>' .
                '  <td class="canvas-elt-titleX">&nbsp;</td>' .
                '  <td class="canvas-elt-title" style="width: 28%;" colspan="3"><strong>' .
                    $this->htmlIcon('fa-barcode') . ' ' . $this->language->__('box.header.cp.ovp') . '</strong></td>' .
                '</tr><tr>' .
                '  <td class="canvas-elt-titleX">&nbsp;</td>' .
                '  <td class="canvas-elt-title">' .
                    $this->htmlCanvasTitle($this->canvasTypes['cp_ou_rv']['title'], $this->canvasTypes['cp_ou_rv']['icon']) . '</td>' .
                '  <td class="canvas-elt-title">' .
                    $this->htmlCanvasTitle($this->canvasTypes['cp_ou_rc']['title'], $this->canvasTypes['cp_ou_rc']['icon']) . '</td>' .
                '  <td class="canvas-elt-title">' .
                    $this->htmlCanvasTitle($this->canvasTypes['cp_ou_e']['title'], $this->canvasTypes['cp_ou_e']['icon']) . '</td>' .
                '</tr><tr>' .
                '  <td class="canvas-elt-box" style="height: 110px; vertical-align: middle; text-align: center;"><strong>' .
                    $this->language->__('box.label.cp.unique') . '</strong></td>' .
                '  <td class="canvas-elt-box" style="height: 110px">' . $this->htmlCanvasElements($recordsAry, 'cp_ou_rv') . '</td>' .
                '  <td class="canvas-elt-box" style="height: 110px">' . $this->htmlCanvasElements($recordsAry, 'cp_ou_rc') . '</td>' .
                '  <td class="canvas-elt-box" style="height: 110px">' . $this->htmlCanvasElements($recordsAry, 'cp_ou_e') . '</td>' .
                '</tr><tr>' .
                '  <td class="canvas-elt-titleX">&nbsp;</td>' .
                '  <td class="canvas-elt-title">' .
                    $this->htmlCanvasTitle($this->canvasTypes['cp_os_rv']['title'], $this->canvasTypes['cp_os_rv']['icon']) . '</td>' .
                '  <td class="canvas-elt-title">' .
                    $this->htmlCanvasTitle($this->canvasTypes['cp_os_rc']['title'], $this->canvasTypes['cp_os_rc']['icon']) . '</td>' .
                '  <td class="canvas-elt-title">' .
                    $this->htmlCanvasTitle($this->canvasTypes['cp_os_e']['title'], $this->canvasTypes['cp_os_e']['icon']) . '</td>' .
                '</tr><tr>' .
                '  <td class="canvas-elt-box" style="height: 110px; vertical-align: middle; text-align: center;"><strong>' .
                    $this->language->__('box.label.cp.superior') . '</strong></td>' .
                '  <td class="canvas-elt-box" style="height: 110px">' . $this->htmlCanvasElements($recordsAry, 'cp_os_rv') . '</td>' .
                '  <td class="canvas-elt-box" style="height: 110px">' . $this->htmlCanvasElements($recordsAry, 'cp_os_rc') . '</td>' .
                '  <td class="canvas-elt-box" style="height: 110px">' . $this->htmlCanvasElements($recordsAry, 'cp_os_e') . '</td>' .
                '</tr><tr>' .
                '  <td class="canvas-elt-titleX">&nbsp;</td>' .
                '  <td class="canvas-elt-title">' .
                    $this->htmlCanvasTitle($this->canvasTypes['cp_oi_rv']['title'], $this->canvasTypes['cp_oi_rv']['icon']) . '</td>' .
                '  <td class="canvas-elt-title">' .
                    $this->htmlCanvasTitle($this->canvasTypes['cp_oi_rc']['title'], $this->canvasTypes['cp_oi_rc']['icon']) . '</td>' .
                '  <td class="canvas-elt-title">' .
                    $this->htmlCanvasTitle($this->canvasTypes['cp_oi_e']['title'], $this->canvasTypes['cp_oi_e']['icon']) . '</td>' .
                '</tr><tr>' .
                '  <td class="canvas-elt-box" style="height: 110px; vertical-align: middle; text-align: center;"><strong>' .
                    $this->language->__('box.label.cp.indifferent') . '</strong></td>' .
                '  <td class="canvas-elt-box" style="height: 110px">' . $this->htmlCanvasElements($recordsAry, 'cp_oi_rv') . '</td>' .
                '  <td class="canvas-elt-box" style="height: 110px">' . $this->htmlCanvasElements($recordsAry, 'cp_oi_rc') . '</td>' .
                '  <td class="canvas-elt-box" style="height: 110px">' . $this->htmlCanvasElements($recordsAry, 'cp_oi_e') . '</td>' .
                '</tr></tbody></table>';

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

            $options = ['disclaimer' => $this->canvasRepo->getDisclaimer()];
            return parent::reportGenerate($id, $filter, $options);
        }
    }
}
