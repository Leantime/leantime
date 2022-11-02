<?php
/**
 * HTML code for PDF report
 */
namespace leantime\domain\controllers {
  
    class NEWcanvas extends \leantime\domain\controllers\canvas\pdf {

		protected const CANVAS_NAME = 'NEW';
        
        /**
         * htmlCanvas -  Layout canvas (must be implemented)
         *
         * @access public
         * @param  array  $recordsAry Array of canvas data records
         * @return string HTML code
         */
        protected function htmlCanvas(array $recordsAry): string
        {
			
            return 'NOT IMPLEMENTED';

        }
        
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

            $options = [ ];
			return parent::reportGenerate($id, $filter, $options);

        }
    
    }
}
?>
