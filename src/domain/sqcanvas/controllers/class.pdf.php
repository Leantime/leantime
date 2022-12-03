<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\controllers {
  
	use leantime\domain\repositories;
	
    class pdf extends \leantime\domain\controllers\canvas\pdf {

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
