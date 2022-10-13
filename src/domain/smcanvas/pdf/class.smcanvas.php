<?php
/**
 * Porder 's Five Strategy Questions - HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class smcanvas extends \leantime\library\pdf\template {
        
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
            $html .= '<div>'.$this->htmlListTitle('print.sm.a', 'fa-clipboard-question').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sm_a').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sm.b', 'fa-clipboard-question').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sm_b').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sm.c', 'fa-clipboard-question').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sm_c').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sm.d', 'fa-clipboard-question').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sm_d').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sm.e', 'fa-clipboard-question').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sm_e').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sm.f', 'fa-clipboard-question').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sm_f').'</div>';
            $html .= '<div>'.$this->htmlListTitle('print.sm.g', 'fa-clipboard-question').'</div>';
            $html .= '<div>'.$this->htmlListElementsShort($recordsAry, 'sm_g').'</div>';
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
        public function reportGenerate(int $id, string $filter, string $template = ''): string
        {
            // Retrieve canvas data
            $smCanvasRepo = new repositories\smcanvas();
            $smCanvasAry = $smCanvasRepo->getSingleCanvas($id);
            !empty($smCanvasAry) || die("Cannot find canvas with id '$id'");
            $projectId = $smCanvasAry[0]['projectId'];
            $recordsAry = $smCanvasRepo->getCanvasItemsById($id);
            $projectsRepo = new repositories\projects();
            $projectAry = $projectsRepo->getProject($projectId);
            !empty($projectAry) || die("Cannot retrieve project id '$projectId'");
            
            // Configuration
            $options = [ 'canvasShow' => false, 'labelDescription' => 'label.sm.element_title', 
                         'labelStatus' => 'label.sm.status', 'labelConclusion' => 'label.description' ];
            
            // Generate PDF content
            $pdf = new \YetiForcePDF\Document();
            $pdf->init();
            $pdf->loadHtml($this->htmlReport('print.sm.title', $projectAry['name'], $smCanvasAry[0]['title'], $recordsAry, $filter, 
                           $options));
            return $pdf->render();
        }
    
    }
}
?>
