<?php
/**
 * Strategy Brief - HTML code for PDF report
 */
namespace leantime\domain\controllers {
  
	use leantime\domain\repositories;
	
    class pdf extends \leantime\domain\controllers\canvas\pdf {

		protected const CANVAS_NAME = 'sb';
        
        /**
         * htmlList - Layout element list / Lightweight Business Model
         *
         * @access protected
         * @param  array  $recordsAry Array of canvas data records
         * @return string HTML code
         */
        protected function htmlList(array $recordsAry): string
        {
			
			// Adjust status of record (only stakeholders have a status)
			foreach($recordsAry as $key => $data) {
				if(!in_array($data['box'], [ 'sb_st_design', 'sb_st_decision', 'sb_st_experts', 'sb_st_support' ])) {
					$recordsAry[$key]['status'] = '';
				}
			}
            
            $html = '';
            $html .= '<div>'.$this->htmlListTitle("headline.".static::CANVAS_NAME.".board", $this->canvasRepo->getIcon()).'</div>';
			$html .= '<div style="margin-top: 5px; margin-bottom: 5px;">'.$_SESSION['currentProjectName'].'</div><hr class="hr-black"/>';

			$html .= $this->htmlListCompact($recordsAry);

            $html .= '<div>'.$this->htmlListTitle('box.sb.risks', 'fa-person-falling').'</div>';
            $html .= '<div style="margin-top: 5px; margin-bottom: 5px;">'.
                sprintf($this->language->__('text.sb.risks_analysis'), $this->config->appUrl).'</div><hr class="hr-black"/>';

            return $html;
            
        }

        /***
         * reportGenerate - Generate report for module  / Porter's Startegy Questions
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
