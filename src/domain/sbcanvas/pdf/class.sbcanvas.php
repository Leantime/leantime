<?php
/**
 * PESTLE Analysis Canvas - HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class sbcanvas extends \leantime\library\pdf\template {
        
        /** 
         * filterToStatus - Convert a filter to a value of the status field
         *
         * @access protected
         * @param  string $filter Filter name
         * @return string Status value associated with filter
         */
        protected function filterToStatus(string $filter): string
        {
            
            return match($filter)
                {'valid' => 'success', 'invalid' => 'danger', 'draft'  => 'info', 'review' => 'warning', default => 'all' };

        }

        /**
         * htmlListStatus - Return HTML code showing status of list element
         *
         * @access protected
         * @param  string $status Element status key
         * @return string HTML code
         */
        protected function htmlListStatus(string $status): string
        {
            
            return  '<span style="color: '.(match($status) { 
                    'info' => '#3a87ad', 'danger' => 'red', 'warning' => 'orange', 
                    default => 'green' }).';">'.(match($status) { 
                            'info' => $this->language->__("print.draft"), 
                            'danger' => $this->language->__("print.invalid"), 
                            'warning' => $this->language->__("print.review"), 
                            default => $this->language->__("print.valid") }).'</span>';
            
        }
    
        /**
         * htmlList - Layout element list / Lightweight Business Model
         *
         * @access protected
         * @param  array  $recordsAry Array of canvas data records
         * @return string HTML code
         */
        protected function htmlList(array $recordsAry): string
        {
            
            $html = '';
            $html .= '<div>'.$this->htmlListTitle('print.sb.title', 'fa-list-check').'</div>';
			$html .= '<div style="margin-top: 5px; margin-bottom: 5px;">'.$_SESSION['currentProjectName'].'</div><hr class="hr-black"/>';
            $html .= '<div>'.$this->htmlListTitle('print.sb.industry', 'fa-industry').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sb_industry').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sb.description', 'fa-file-lines').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sb_description').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sb.st_design', 'fa-people-group').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sb_st_design').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sb.st_decision', 'fa-sitemap').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sb_st_decision').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sb.st_experts', 'fa-chalkboard-users').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sb_st_experts').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sb.st_support', 'fa-person-digging').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sb_st_support').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sb.budget', 'fa-money-bills').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sb_budget').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sb.time', 'fa-business-time').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sb_time').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sb.culture', 'fa-masks-theater').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sb_culture').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sb.change', 'fa-book-skull').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sb_change').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sb.principles', 'fa-ruler-combined').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sb_principles').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sb.risks', 'fa-person-falling').'</div>';
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
        public function reportGenerate(int $id, string $filter): string
        {
            // Retrieve canvas data
            $sbCanvasRepo = new repositories\sbcanvas();
            $sbCanvasAry = $sbCanvasRepo->getSingleCanvas($id);
            !empty($sbCanvasAry) || die("Cannot find canvas with id '$id'");
            $projectId = $sbCanvasAry[0]['projectId'];
            $recordsAry = $sbCanvasRepo->getCanvasItemsById($id);
            $projectsRepo = new repositories\projects();
            $projectAry = $projectsRepo->getProject($projectId);
            !empty($projectAry) || die("Cannot retrieve project id '$projectId'");
            
            // Configuration
            $options = [ 'canvasShow' => false, 'labelDescription' => 'label.sb.element_title', 
                         'labelStatus' => 'label.sb.status', 'labelConclusion' => 'label.description' ];
            
            // Generate PDF content
            $pdf = new \YetiForcePDF\Document();
            $pdf->init();
            $pdf->loadHtml($this->htmlReport('print.sb.title', $projectAry['name'], $sbCanvasAry[0]['title'], $recordsAry, $filter, 
                           $options));
            return $pdf->render();
        }
    
    }
}
?>
