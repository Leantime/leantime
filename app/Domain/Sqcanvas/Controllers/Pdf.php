<?php

/**
 * HTML code for PDF report
 */

namespace Leantime\Domain\Sqcanvas\Controllers {

    use Leantime\Domain\Canvas\Controllers\Pdf as PdfController;

    /**
     *
     */
    class Pdf extends PdfController
    {
        protected const CANVAS_NAME = 'sq';

        /***
         * reportGenerate - Generate report for module
         *
         * @access public
         * @param integer $id      Canvas identifier
         * @param array   $filter  Filter value
         * @param array   $options Options
         * @return string PDF filename
         */
        public function reportGenerate(int $id, array $filter = [], array $options = []): string
        {

            $options = ['canvasShow' => false];
            return parent::reportGenerate($id, $filter, $options);
        }
    }
}
