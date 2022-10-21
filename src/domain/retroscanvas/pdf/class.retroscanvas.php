<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class retroscanvas extends \leantime\domain\pdf\canvas {

		protected const CANVAS_NAME = 'retros';
        
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

            $options = [ 'canvasShow' => false ];
			return parent::reportGenerate($id, $filter, $options);

        }
    
    }
}
?>
