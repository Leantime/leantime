<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class sqcanvas extends \leantime\domain\pdf\canvas {

		protected const CANVAS_NAME = 'sq';
        
        /***
         * reportGenerate - Generate report for module
         *
         * @access public
         * @param  int    $id      Canvas identifier
         * @param  string $filter  Filter value
		 * @param  string $options Options
         * @return string PDF filename
         */
        public function reportGenerate(int $id, array $filter = [], array $options = []): string
        {

            $options = [ 'canvasShow' => false ];
			return parent::reportGenerate($id, $filter, $options);

        }
    
    }
}
?>
