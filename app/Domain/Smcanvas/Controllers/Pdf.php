<?php

/**
 * HTML code for PDF report
 */

namespace Leantime\Domain\Smcanvas\Controllers {

    use Leantime\Domain\Canvas\Controllers\Pdf as PdfController;

    class Pdf extends PdfController
    {
        protected const CANVAS_NAME = 'sm';

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

            $options = ['canvasShow' => false];
            return parent::reportGenerate($id, $filter, $options);
        }
    }
}
